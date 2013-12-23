<%

include_once "/var/spool/apache/htdocs/cdr/auth.inc";

function randid($size) {
	$chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$rlen=strlen($chars)-1;
        $str = "";
        for($i = 0; $i < $size; $i++) {
                $str .= substr($chars, rand(0,$rlen), 1);
        }
        return $str;
}

function valicsv($filename, $data_tb, $offset) {
  global $db;

  $csvin = fopen($filename, "r");
  if (!$csvin) {
    print "Bad File";
    retun;
  }

  $rowinfo=array();

  /*check and create if needed the input data table*/
  $testdb=pg_query($db,"SELECT * from information_schema.tables where table_catalog='asterisk' and table_name='" . $data_tb . "'");
  if (pg_num_rows($testdb) < 1) {
    pg_query($db,"CREATE TABLE " . $data_tb . " (id bigserial,leadid bigint)");
    pg_query($db,"ALTER TABLE " . $data_tb . " ADD CONSTRAINT key_" . $data_tb . " PRIMARY KEY (id)");
    pg_query($db,"CREATE UNIQUE INDEX " . $data_tb . "_contact ON " . $data_tb . " USING btree (leadid)");
  }

  /* load existing fields into array to test*/
  $currowq=pg_query($db, "SELECT field,fname FROM field_names WHERE tablename='" . $data_tb . "'");
  for($cr=0;$cr < pg_num_rows($currowq);$cr++) {
    $rd=pg_fetch_array($currowq, $cr, PGSQL_NUM);
    $currow[$rd[1]] = $rd[0];
  }

  /* read first line and gather info */
  if (($head = fgetcsv($csvin, 0, ",")) !== FALSE) {
    for($hcnt = $offset;$hcnt < count($head);$hcnt++) {
      $rowinfo[$hcnt]["desc"] = $head[$hcnt];
      $rowinfo[$hcnt]["min"] = 0;
      $rowinfo[$hcnt]["max"] = 0;
      $rowinfo[$hcnt]["len"] = 0;
      if (isset($currow[$head[$hcnt]])) {
        $rowinfo[$hcnt]["name"] = $currow[$head[$hcnt]];
        $rowinfo[$hcnt]["new"] = 0;
      } else {
        do {
           $newid = strtolower("csv_" . randid(7));
        } while (isset($doneid[$newid]));
        $doneid[$newid]=1;
        $rowinfo[$hcnt]["name"] = $newid;
        $rowinfo[$hcnt]["new"] = 1;
      }
    }
  }

  $row = 0;
  while (($data = fgetcsv($csvin, 0, ",")) !== FALSE) {
    for($c = $offset;$c < count($data);$c++) {
      if (isset($data[$c])) {
        $rowinfo[$c]["count"]++;
      } else {
        continue;
      }
      $rowinfo[$c]["len"] = ($rowinfo[$c]["len"] < $len) ? $len : $rowinfo[$c]["len"]; 
      if (is_numeric($data[$c])) {
        if (is_float($data[$c])) {
          $rowinfo[$c]["float"]++;
        } else if (is_int((int)$data[$c])) {
          $rowinfo[$c]["int"]++;
          $rowinfo[$c]["float"]++;
/*
	  if (($data[$c] == "0") || ($data[$c] == "1")) {
            $rowinfo[$c]["bool"]++;
          }
*/
          $rowinfo[$c]["max"] = ($data[$c] > $rowinfo[$c]["max"]) ? $data[$c] : $rowinfo[$c]["max"];
          $rowinfo[$c]["min"] = ($data[$c] < $rowinfo[$c]["min"]) ? $data[$c] : $rowinfo[$c]["min"];
        } else if (is_bool(filter_var($data[$c], FILTER_VALIDATE_BOOLEAN))) {
          $rowinfo[$c]["bool"]++;
        }
      } else {
        if (preg_match('/^(\d|0\d|1[0-2]):(\d|[0-5]\d|60)\s*([ap]m|[ap])$/i', $data[$c], $match)) {
          $rowinfo[$c]["time"]++;
        } else if (preg_match('/^(\d|0\d|1[0-2]):(\d|[0-5]\d|60):(\d|[0-5]\d|60)\s*([ap]m|[ap])$/i', $data[$c], $match)) {
          $rowinfo[$c]["time"]++;
        } else if (preg_match('/^(\d|[01]\d|2[0-4]):(\d|[0-5]\d|60):(\d|[0-5]\d|60)$/', $data[$c], $match)) {
          $rowinfo[$c]["time"]++;
        } else if (preg_match('/^(\d|[01]\d|2[0-4]):(\d|[0-5]\d|60)$/', $data[$c], $match)) {
          $rowinfo[$c]["time"]++;
        } else if (strtotime($data[$c])) {
          $rowinfo[$c]["date"]++;
/*
        } else if (is_bool(filter_var($data[$c], FILTER_VALIDATE_BOOLEAN))) {
          $rowinfo[$c]["bool"]++;
*/
        } else {
          $rowinfo[$c]["text"]++;
          $len = strlen($data[$c]);
        }
      }
    }
  }
  $row++;

  fclose($csvin);

  $dtype=array("int","float","bool","time","date","text");

  /* set the field type*/
  for($rcnt = 0;$rcnt < count($rowinfo);$rcnt++) {
    $rlast=0;
    $r=$rcnt+$offset;
    for($dt = 0;$dt < count($dtype);$dt++) {
      if (isset($rowinfo[$r][$dtype[$dt]])) {
        $curp = ($rowinfo[$r][$dtype[$dt]] / $rowinfo[$r]["count"]) * 100;
        $rformat[$r] = ($curp == 100) ? $dtype[$dt] : $rformat[$r];
        $rlast = $curp;
      }
    }
    if (!isset($rformat[$r])) {
      $rformat[$r] = "text";
    }
  }

  /*add any additional fields*/
  for($c=0;$c < count($rformat);$c++) {
    $col=$c+$offset;
    if (!$rowinfo[$col]["new"]) {
      continue;
    }

    $newfield="INSERT INTO field_names (tablename,field,fname) VALUES ('" . $data_tb . "','" . $rowinfo[$col]["name"] . "','" . $rowinfo[$col]["desc"] . "')";
    $newcol="ALTER TABLE " . $data_tb . " ADD " . $rowinfo[$col]["name"] . " ";
    switch ($rformat[$col]) {
      case "text":if ($rowinfo[$col]["len"] < 511) {
          $newcol .= "varchar(" . ($rowinfo[$col]["len"]  + 1). ")";
        } else {
          $newcol .= "text";
	}
        break;
      case "int":if (($rowinfo[$col]["min"] > -2147483647) && ($rowinfo[$col]["max"] < 2147483647)) {
          $newcol .= "integer";
        } else {
          $newcol .= "bigint" . $rowinfo[$col]["min"] . " - " . $rowinfo[$col]["max"];
        }	
        break;
      case "float": $newcol .= "float8";
        break;
      case "time": $newcol .= "time with time zone";
        break;
      case "date": $newcol .= "timestamp with time zone";
        break;
      case "bool": $newcol .= "boolean";
        break;
    }
    pg_query($db, $newfield);
    pg_query($db, $newcol);
  }
}
%>
