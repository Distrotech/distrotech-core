				$link = mysql_connect("localhost", "logview", "admin")
				mysql_select_db("networksentry_log")
				$result = mysql_query($query)
				$colums = mysql_num_fields($result);
					$colum_name = mysql_field_name($result, $i);
				while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
				print "<p>".mysql_num_rows($result)." packet(s) selected<p>";
				mysql_free_result($result);
				mysql_close($link);
