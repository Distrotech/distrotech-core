<?php

include "/var/spool/apache/htdocs/cshop/auth.inc";
include "../cdr/auth.inc";

$totcred=0;
$rescred=0;

foreach ($_POST as $m=>$value)
{
	//print $m . "=" . $value;

	if ($value == on)
	{
		/* Update Users to reset credit */
		$query="SELECT resetcredit,credit FROM users WHERE name = '" . $m . "'";
		$result=pg_query($db,$query);
		$row=pg_fetch_row($result);
		if ($row[0] != "")
		{
			$query="UPDATE users SET credit = '" . $row[0] . "' WHERE name = '" . $m . "'";
			$result=pg_query($db,$query);
//			print $query . "</br>";
			$rescred=$rescred+$row[0];
			$totcred=$totcred+$row[1];
		}
	        /* End */

		/* Update Resellers to reset credit */
		$query="SELECT resetcredit,credit FROM reseller WHERE id = '" . $m . "'";
                $result=pg_query($db,$query);
                $row=pg_fetch_row($result);
		if ($row[0] != "")
		{
			$query="UPDATE reseller SET credit = '" . $row[0] . "' WHERE id = '" . $m . "'";
//			print $query . "</br>";
			$result=pg_query($db,$query);
			$rescred=$rescred+$row[0];
			$totcred=$totcred+$row[1];
		}
		/* End */	
	}
}
//print $rescred . "</br>";
//print $totcred . "</br>";

/* Update Resellers to reset allocated credit */
$query="SELECT resetcredit,credit,resetallocated,rcallocated FROM reseller WHERE id = '" . $_SESSION['resellerid'] . "'";
$result=pg_query($db,$query);
$row=pg_fetch_row($result);

$allocated=($row[3]-$totcred)+$rescred;
$credit=($row[1]-$totcred)+$rescred;

//print $allocated . "</br>";
//print $credit . "</br>";

$query="UPDATE reseller SET rcallocated = '" . $allocated . "' WHERE id = '" . $_SESSION['resellerid'] . "'";
//print $query . "</br>"; 
$result=pg_query($db,$query);

$profit=$credit-$row[0];
//print $profit . "</br>";
if ($profit <= 0)
{
	$credit=(($row[1]-$totcred)+$rescred);
} else {
	$credit=(($row[1]-$totcred)+$rescred)-$profit;
}

$query="UPDATE reseller SET credit = '" . $credit . "' WHERE id = '" . $_SESSION['resellerid'] . "'";
//print $query . "</br>";
$result=pg_query($db,$query);
/* End */

header("Location: /cshop");

?>
