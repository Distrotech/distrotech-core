<%
/*
#    Copyright (C) 2002  <Gregory Hinton Nietsky>
#    Copyright (C) 2005  <ZA Telecomunications>
#
#    This program is free software; you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation; either version 2 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program; if not, write to the Free Software
#    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
%>
<html>
<!-- **********************************************************************
                           ulog_query.php
                         -------------------
   version		: 0.01
   begin                : Mon July 3 18:50:00 CET 2002
   copyright            : (C) 2002 by Nils Ohlmeier
   email                : develop@ohlmeier.org
***************************************************************************

***************************************************************************
*                                                                         *
*   This program is free software; you can redistribute it and/or modify  *
*   it under the terms of the GNU General Public License as published by  *
*   the Free Software Foundation; either version 2 of the License, or     *
*   (at your option) any later version.                                   *
*                                                                         *
*********************************************************************** -->
	<head>
		<title>Ulog database query</title>
	</head>
	<body>
		<form action="ulog_query.php" method="post">
			<h3>Query:</h3>
			Prefix: <input type="text" name="oob_prefix"><p>
			Input interface: <input type="text" name="oob_in">
			Output interface: <input type="text" name="oob_out"><p>
			Protocol (name or number): <input type="text" name="ip_protocol"><p>
			Source IP address: <input type="text" name="ip_saddr">
			Destination IP address: <input type="text" name="ip_daddr"><p>
			TCP source port: <input type="text" name="tcp_sport">
			TCP destination port: <input type="text" name="tcp_dport"><p>
			UDP source port: <input type="text" name="udp_sport">
			UDP destination port: <input type="text" name="udp_dport"><p>
			min date: <input type="text" name="min_date">
			max date: <input type="text" name="max_date"><p>
			<h3>Selection:</h3>
			<input type="checkbox" name="c_all" value="on"><b>all</b><br>
			<input type="checkbox" name="c_all_oob" value="on"><b>all_oob</b>
			<input type="checkbox" name="c_oob_time_sec" value="on">oob_time_sec
			<input type="checkbox" name="c_oob_time_usec" value="on">oob_time_usec
			<input type="checkbox" name="c_oob_prefix" value="on">obb_prefix
			<input type="checkbox" name="c_oob_mark" value="on">oob_mark
			<input type="checkbox" name="c_oob_in" value="on">oob_in
			<input type="checkbox" name="c_oob_out" value="on">oob_out<br>
			<input type="checkbox" name="c_all_ip" value="on"><b>all_ip</b>
			<input type="checkbox" name="c_ip_saddr" value="on">ip_saddr
			<input type="checkbox" name="c_ip_daddr" value="on">ip_daddr
			<input type="checkbox" name="c_ip_protocol" value="on">ip_protocol
			<input type="checkbox" name="c_ip_tos" value="on">ip_tos
			<input type="checkbox" name="c_ip_ttl" value="on">ip_ttl
			<input type="checkbox" name="c_ip_totlen" value="on">ip_totlen
			<input type="checkbox" name="c_ip_ihl" value="on">ip_ihl
			<input type="checkbox" name="c_ip_csum" value="on">ip_csum
			<input type="checkbox" name="c_ip_id" value="on">ip_id
			<input type="checkbox" name="c_ip_fragoff" value="on">ip_fragoff<br>
			<input type="checkbox" name="c_all_tcp" value="on"><b>all_tcp</b>
			<input type="checkbox" name="c_tcp_sport" value="on">tcp_sport
			<input type="checkbox" name="c_tcp_dport" value="on">tcp_dport
			<input type="checkbox" name="c_tcp_seq" value="on">tcp_seq
			<input type="checkbox" name="c_tcp_ackseq" value="on">tcp_ackseq
			<input type="checkbox" name="c_tcp_window" value="on">tcp_window
			<input type="checkbox" name="c_tcp_urg" value="on">tcp_urg
			<input type="checkbox" name="c_tcp_urgp" value="on">tcp_urgp
			<input type="checkbox" name="c_tcp_ack" value="on">tcp_ack
			<input type="checkbox" name="c_tcp_psh" value="on">tcp_psh
			<input type="checkbox" name="c_tcp_rst" value="on">tcp_rst
			<input type="checkbox" name="c_tcp_syn" value="on">tcp_syn
			<input type="checkbox" name="c_tcp_fin" value="on">tcp_fin<br>
			<input type="checkbox" name="c_all_udp" value="on"><b>all_udp</b>
			<input type="checkbox" name="c_udp_sport" value="on">udp_sport
			<input type="checkbox" name="c_udp_dport" value="on">udp_dport
			<input type="checkbox" name="c_udp_len" value="on">udp_len<br>
			<input type="checkbox" name="c_all_icmp" value="on"><b>all_icmp</b>
			<input type="checkbox" name="c_icmp_type" value="on">icmp_type
			<input type="checkbox" name="c_icmp_code" value="on">icmp_code
			<input type="checkbox" name="c_icmp_echoid" value="on">icmp_echoid
			<input type="checkbox" name="c_icmp_echoseq" value="on">icmp_echoseq
			<input type="checkbox" name="c_icmp_gateway" value="on">icmp_gateway
			<input type="checkbox" name="c_icmp_fragmtu" value="on">icmp_fragmtu<br>
			<input type="checkbox" name="c_all_other" value="on"><b>all_other</b>
			<input type="checkbox" name="c_id" value="on">id
			<input type="checkbox" name="c_raw_mac" value="on">raw_mac
			<input type="checkbox" name="c_pwsniff_user" value="on">pwsniff_user
			<input type="checkbox" name="c_pwsniff_pass" value="on">pwsniff_pass
			<input type="checkbox" name="c_ahesp_spi" value="on">ahesp_spi<p>
			<h3>Options:</h3>
			<input type="checkbox" name="dns" value="on">IP reverse name resolution (can increase processing time dramaticly !!!)<p>
			<input type="submit"><p>
		</form>
		<?php

			if ($_POST["oob_prefix"]) $where = " oob_prefix='" . $_POST["oob_prefix"] . "'";
			if ($_POST["oob_in"]) {
				if ($where) {
					$where = $where . " AND oob_in='" . $_POST["oob_in"] . "'";
				} else {
					$where = "oob_in='" . $_POST["oob_in"] . "'";
				}
			}
			if ($_POST["oob_out"]) {
				if ($where) {
					$where = $where . " AND oob_out='" . $_POST["oob_out"] . "'";
				} else {
					$where = "oob_out='" . $_POST["oob_out"] . "'";
				}
			}
			if ($_POST["ip_protocol"]) {
				$ip_proto = $_POST["ip_protocol"];
				if (!is_int($ip_proto)) $ip_proto = getprotobyname($ip_proto);
				if ($where) {
					$where = $where . " AND ip_protocol=" . $ip_proto;
				} else {
					$where = "ip_protocol=" . $ip_proto;
				}
				if ($ip_proto==1) $_POST["c_all_icmp"]="on";
				elseif ($ip_proto==6) $_POST["c_all_tcp"]="on";
				elseif ($ip_proto==17) $_POST["c_all_udp"]="on";
			}
			if ($_POST["ip_saddr"]) {
				$where_saddr = ip2long($_POST["ip_saddr"]);
				if ($where_saddr < 0) $where_saddr += pow(2,32);
				if ($where) {
					$where = $where . " AND ip_saddr=" . $where_saddr;
				} else {
					$where = "ip_saddr=" . $where_saddr;
				}
			}
			if ($_POST["ip_daddr"]) {
				$where_daddr = ip2long($_POST["ip_daddr"]);
				if ($where_daddr < 0) $where_daddr += pow(2,32);
				if ($where) {
					$where = $where . " AND ip_daddr=" . $where_daddr;
				} else {
					$where = "ip_daddr=" . $where_daddr;
				}
			}
			if ($_POST["tcp_sport"]) {
				if ($where) {
					$where = $where . " AND tcp_sport=" . $_POST["tcp_sport"];
				} else {
					$where = "tcp_sport=" . $_POST["tcp_sport"];
				}
			}
			if ($_POST["tcp_dport"]) {
				if ($where) {
					$where = $where . " AND tcp_dport=" . $_POST["tcp_dport"];
				} else {
					$where = "tcp_dport=" . $_POST["tcp_dport"];
				}
			}
			if ($_POST["udp_sport"]) {
				if ($where) {
					$where = $where . " AND udp_sport=" . $_POST["udp_sport"];
				} else {
					$where = "udp_sport=" . $_POST["udp_sport"];
				}
			}
			if ($_POST["udp_dport"]) {
				if ($where) {
					$where = $where . " AND udp_dport=" . $_POST["udp_dport"];
				} else {
					$where = "udp_dport=" . $_POST["udp_dport"];
				}
			}
			if ($_POST["min_date"]) {
				$sepDate = explode(".", $_POST["min_date"]);
				$unix_min_date = mktime(0, 0, 0, $sepDate[1], $sepDate[0], $sepDate[2]);
				if ($where) {
					$where = $where . " AND oob_time_sec>=" . $unix_min_date;
				} else {
					$where = "oob_time_sec>=" . $unix_min_date;
				}
			}
			if ($_POST["max_date"]) {
				$sepDate = explode(".", $_POST["max_date"]);
				$unix_max_date = mktime(0, 0, 0, $sepDate[1], $sepDate[0], $sepDate[2]);
				if ($where) {
					$where = $where . " AND oob_time_sec<=" . $unix_max_date;
				} else {
					$where = "oob_time_sec<=" . $unix_max_date;
				}
			}

			if ($_POST["c_all"]) {
				$select = "*,";
			} else {
				if ($_POST["c_all_oob"]) {
					$select = "oob_time_sec,oob_time_usec,oob_prefix,oob_mark,oob_in,oob_out,";
				} else {
					if ($_POST["c_oob_time_sec"]) $select = "oob_time_sec,";
					if ($_POST["c_oob_time_usec"]) $select = $select."oob_time_usec,";
					if ($_POST["c_oob_prefix"]) $select = $select."oob_prefix,";
					if ($_POST["c_oob_mark"]) $select = $select."oob_mark,";
					if ($_POST["c_oob_in"]) $select = $select."oob_in,";
					if ($_POST["c_oob_out"]) $select = $select."oob_out,";
				}
				if ($_POST["c_all_ip"]) {
					$select = $select."ip_saddr,ip_daddr,ip_protocol,ip_tos,ip_ttl,ip_totlen,ip_ihl,ip_csum,ip_id,ip_fragoff,";
				} else {
					if ($_POST["c_ip_saddr"]) $select = $select."ip_saddr,";
					if ($_POST["c_ip_daddr"]) $select = $select."ip_daddr,";
					if ($_POST["c_ip_protocol"]) $select = $select."ip_protocol,";
					if ($_POST["c_ip_tos"]) $select = $select."ip_tos,";
					if ($_POST["c_ip_ttl"]) $select = $select."ip_ttl,";
					if ($_POST["c_ip_totlen"]) $select = $select."ip_totlen,";
					if ($_POST["c_ip_ihl"]) $select = $select."ip_ihl,";
					if ($_POST["c_ip_csum"]) $select = $select."ip_csum,";
					if ($_POST["c_ip_id"]) $select = $select."ip_id,";
					if ($_POST["c_ip_fragoff"]) $select = $select."ip_fragoff,";
				}
				if ($_POST["c_all_tcp"]) {
					$select = $select."tcp_sport,tcp_dport,tcp_seq,tcp_ackseq,tcp_window,tcp_urg,tcp_urgp,tcp_ack,tcp_psh,tcp_rst,tcp_syn,tcp_fin,";
				} else {
					if ($_POST["c_tcp_sport"]) $select = $select."tcp_sport,";
					if ($_POST["c_tcp_dport"]) $select = $select."tcp_dport,";
					if ($_POST["c_tcp_seq"]) $select = $select."tcp_seq,";
					if ($_POST["c_tcp_ackseq"]) $select = $select."tcp_ackseq,";
					if ($_POST["c_tcp_window"]) $select = $select."tcp_window,";
					if ($_POST["c_tcp_urg"]) $select = $select."tcp_urg,";
					if ($_POST["c_tcp_urgp"]) $select = $select."tcp_urgp,";
					if ($_POST["c_tcp_ack"]) $select = $select."tcp_ack,";
					if ($_POST["c_tcp_psh"]) $select = $select."tcp_psh,";
					if ($_POST["c_tcp_rst"]) $select = $select."tcp_rst,";
					if ($_POST["c_tcp_syn"]) $select = $select."tcp_syn,";
					if ($_POST["c_tcp_fin"]) $select = $select."tcp_fin,";
				}
				if ($_POST["c_all_udp"]) {
					$select = $select."udp_sport,udp_dport,udp_len,";
				} else {
					if ($_POST["c_udp_sport"]) $select = $select."udp_sport,";
					if ($_POST["c_udp_dport"]) $select = $select."udp_dport,";
					if ($_POST["c_udp_len"]) $select = $select."udp_len,";
				}
				if ($_POST["c_all_icmp"]) {
					$select = $select."icmp_type,icmp_code,icmp_echoid,icmp_echoseq,icmp_gateway,icmp_fragmtu,";
				} else {
					if ($_POST["c_icmp_type"]) $select = $select."icmp_type,";
					if ($_POST["c_icmp_code"]) $select = $select."icmp_code,";
					if ($_POST["c_icmp_echoid"]) $select = $select."icmp_echoid,";
					if ($_POST["c_icmp_echoseq"]) $select = $select."icmp_echoseq,";
					if ($_POST["c_icmp_gateway"]) $select = $select."icmp_gateway,";
					if ($_POST["c_icmp_fragmtu"]) $select = $select."icmp_fragmtu,";
				}
				if ($_POST["c_all_other"]) {
					$select = $select."id,raw_mac,pwsniff_user,pwsniff_pass,ahesp_spi,";
				} else {
					if ($_POST["c_id"]) $select = $select."id,";
					if ($_POST["c_raw_mac"]) $select = $select."raw_mac,";
					if ($_POST["c_pwsniff_user"]) $select = $select."pwsniff_user,";
					if ($_POST["c_pwsniff_pass"]) $select = $select."pwsniff_pass,";
					if ($_POST["c_ahesp_spi"]) $select = $select."ahesp_spi,";
				}
			} /* end else c_all */

			if ($select) {
				$link = mysql_connect("localhost", "logview", "admin")
				or die("No database connection possible!");
				mysql_select_db("networksentry_log")
				or die("Database selection failed!");

				$select{strlen($select)-1}=" ";
				$query = "SELECT ".$select." FROM packet_filter";
				if ($where) $query = $query . " WHERE " . $where;
//debugging output: echo "<br>Select: $select";
//debugging output: echo "<p>Query: $query<br>";
				$result = mysql_query($query)
				or die("<br>$query<br>Selection failed!");

				print "<table border=\"1\">\n";

				$colums = mysql_num_fields($result);
				$saddr_col=$daddr_col=$proto_col=$ob_time=-1;
				print "\t<thead><tr>\n";
				for ($i=0; $i < $colums; $i++) {
					$colum_name = mysql_field_name($result, $i);
					print "\t\t<td><H4>$colum_name</H4></td>\n";
					if ($colum_name=="ip_saddr") {
						$saddr_col = $i;
					} elseif ($colum_name=="ip_daddr") {
						$daddr_col = $i;
					} elseif ($colum_name=="ip_protocol") {
						$proto_col = $i;
					} elseif ($colum_name=="oob_time_sec") {
						$ob_time = $i;
					}
				}
				print "\t</tr></thead>\n";

				while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
					print "\t<tr>\n";
					$j = 0;
					foreach ($line as $col_value) {
						if ($j==$ob_time) {
							if ($col_value > 0)
								$pck_date = date("d\.m\.y\ H\:i\:s", $col_value);
							else
								$pck_date = 0;
							print "\t\t<td>$pck_date</td>\n";
						} elseif ($j==$saddr_col or $j==$daddr_col) {
							settype($col_value, "double");
							$host_name = long2ip($col_value);
							if ($_POST["dns"]=="on") $host_name = gethostbyaddr($host_name);
							print "\t\t<td>$host_name</td>\n";
						} elseif ($j==$proto_col) {
							$proto = getprotobynumber($col_value);
							print "\t\t<td>$proto</td>\n";
						} else {
							print "\t\t<td>$col_value</td>\n";
						}
						$j++;
					}
					print "\t</tr>\n";
				}

				print "</table>\n";
				print "<p>".mysql_num_rows($result)." packet(s) selected<p>";

				mysql_free_result($result);
				mysql_close($link);
			}
		?>
	</body>
</html>
