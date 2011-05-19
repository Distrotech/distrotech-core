<%

include "../cdr/auth.inc";
include "../cdr/autoadd.inc";

include "../ldap/ldapcon.inc";

$mac=strtoupper($mac);

$auth_uss=ldap_bind($ds,$LDAP_ROOT_DN,$LDAP_ROOT_PW);
$auth_ussr=ldap_search($ds,"ou=snom","(&(objectClass=person)(cn=snom))");

$uadata=explode(";",$_SERVER['HTTP_USER_AGENT']);
$pdata=explode(" ",trim($uadata[1]));
$sipver=explode(".",trim($pdata[1]));

//print_r($pdata);

/*
if (count($sipver) <= 1) {
  $sipver[0]=8;
}
*/

if (ldap_count_entries($ds,$auth_ussr) <= 0 ) {
  $dn="cn=Snom,ou=Snom";
  $info["objectclass"][0]="person";
  $info["cn"]="snom";
  $info["sn"]="Snom Global Phone Book";
  $info["userpassword"]="snom";

  ldap_add($ds,$dn,$info);

  if (ldap_errno($ds) == "32") {
    $info2["objectclass"][0]="organizationalUnit";
    $info2["ou"]="snom";
    $dn2="ou=snom";
    ldap_add($ds,$dn2,$info2);
    ldap_add($ds,$dn,$info);
  }
  $auth_ussr=ldap_search($ds,"ou=snom","(&(objectClass=person)(cn=snom))");
}

$auth_ures=ldap_first_entry($ds,$auth_ussr);
$suser=ldap_get_attributes($ds,$auth_ures);

$pwlen=8;
$getphoneq="SELECT name,secret,fullname,register.value,usermode.value,nat,dtmfmode,vlanid.value,nodnd.value,
                   (name=secret OR length(secret) != " . $pwlen . " OR secret='' OR secret IS NULL OR
                    secret !~ '[0-9]' OR secret !~ '[a-z]' OR secret !~ '[A-Z]')
              FROM users 
                LEFT OUTER JOIN astdb AS nodnd ON (nodnd.family=name AND nodnd.key='CDND') 
                LEFT OUTER JOIN astdb AS register ON (register.family=name AND register.key='REGISTRAR') 
                LEFT OUTER JOIN astdb AS usermode ON (usermode.family=name AND usermode.key='SNOMLOCK') 
                LEFT OUTER JOIN astdb AS vlanid ON (vlanid.family=name AND vlanid.key='VLAN') 
                LEFT OUTER JOIN astdb ON (astdb.family=name AND astdb.key='SNOMMAC') 
              WHERE astdb.value='" . $mac . "' LIMIT 1";
$getphone=pg_query($db,$getphoneq);

if (pg_num_rows($getphone) == 0) {
  if (createexten($mac,"SNOM","","","") > 0) {
    $getphone=pg_query($db,$getphoneq);
  }
}

list($exten,$pass,$name,$domain,$usermode,$nat,$dtmfmode,$vlantag,$dndsetting,$pwchange)=pg_fetch_array($getphone,0);

if ($pwchange == "t") {
  if (! isset($agi)) {
    require_once("/var/lib/asterisk/agi-bin/phpagi/phpagi-asmanager.php");
    $agi=new AGI_AsteriskManager();
    $agi->connect("127.0.0.1","admin","admin");
  }
  $agi->command("sip prune realtime peer " . $exten);
  $agi->command("sip prune realtime user " . $exten);
  $pass=randpwgen($pwlen);
  pg_query($db,"UPDATE users SET secret='" . $pass . "' WHERE name='" . $exten . "'");
  $agi->disconnect();
}

$getnetp=pg_query($db,"SELECT value FROM astdb WHERE family='Setup' AND key='SnomNet'");

list($netport)=pg_fetch_array($getnetp,0);

if ($exten == "") {
  $usermode="0";
}

if ($netport == "") {
  $netport="auto";
}

if ($domain == "" ) {
  $domain=$SERVER_NAME;
}

$uadata=explode(";",$_SERVER['HTTP_USER_AGENT']);
$curver=trim($uadata[3]," )");
$linver="snom" . $phone . " linux 3.38";

print "language&: English(UK)\n";
print "web_language&: English\n";
print "date_us_format&: on\n";
print "time_24_format&: on\n";
print "with_flash&: on\n";
print "redirect_event&: none\n";
print "redirect_time&: \n";
print "redirect_number&: \n";
print "redirect_busy_number&: \n";
print "redirect_time_number&: \n";
print "redirect_time_on_code&: \n";
print "redirect_time_off_code&: \n";
print "redirect_always_on_code&: \n";
print "redirect_always_off_code&: \n";
print "redirect_busy_on_code&: \n";
print "redirect_busy_off_code&: \n";
print "auto_connect&: off\n";
print "auto_connect_indication&: on\n";
print "message_led_other&: on\n";
print "auto_connect_type&: auto_connect_type_handset\n";
print "privacy_in&: off\n";
print "privacy_out&: off\n";
print "presence_timeout&: 15\n";
print "keyboard_lock&: off\n";
print "keyboard_lock_pw&: \n";
print "keyboard_lock_emergency&: \n";
if ($dndsetting == "-1") {
  print "no_dnd&: on\n";
} else {
  print "no_dnd&: off\n";
}
print "dnd_on_code&: *541\n";
print "dnd_off_code&: *540\n";
print "tone_scheme&: GBR\n";
print "timezone&: CAT+2\n";
print "ntp_server&: " . $domain . "\n";
print "utc_offset&: 7200\n";
print "ntp_refresh_timer!: 300\n";
print "eth_net&: " . $netport . "\n";
print "auto_dial!: 5\n";
print "call_waiting&: on\n";
print "mwi_dialtone&: stutter\n";
print "display_method&: display_name_number\n";
print "ringer_headset_device&: speaker\n";
print "challenge_response&: off\n";
print "alert_internal_ring_text!: alert-internal\n";
print "alert_external_ring_text!: alert-external\n";
print "alert_group_ring_text!: alert-group\n";
print "intercom_enabled&: on\n";
print "subscription_delay!: 5\n";
print "subscription_expiry!: 300\n";
print "filter_registrar!: off\n";

$getring=pg_query($db,"SELECT substr(key,6,1),value FROM astdb WHERE family = '" . $exten . "' AND key ~ '^SRING[0-3]$'");

for($i=0;$i < pg_num_rows($getring);$i++){
  $getdata=pg_fetch_array($getring,$i);
  $dring[$getdata[0]]=true;
  switch ($getdata[0]) {
    case 0:print "ring_sound&: Ringer" . $getdata[1] . "\n";
	   $defring=$getdata[1];
           break;
    case 1:print "alert_internal_ring_sound&: Ringer" . $getdata[1] . "\n";
           break;
    case 2:print "alert_group_ring_sound&: Ringer" . $getdata[1] . "\n";
           break;
    case 3:print "alert_external_ring_sound&: Ringer" . $getdata[1] . "\n";
           break;
  }
}

for($gotring=0;$gotring <= 3;$gotring++) {
  if (!$dring[$gotring]) {
    switch ($gotring) {
      case 0:print "ring_sound&: Ringer6\n";
    	     $defring="6";
             break;
      case 1:print "alert_internal_ring_sound&: Ringer3\n";
             break;
      case 2:print "alert_group_ring_sound&: Ringer1\n";
             break;
      case 3:print "alert_external_ring_sound&: Ringer6\n";
             break;
    }
  }
}

if ($domain != "") {
  if ($usermode == "0") {
    print "user_host1&: " . $domain . "\n";
    print "user_outbound1&: " . $domain . "\n";
    if ($nat == "yes") {
      print "stun_server1&: " . $domain . "\n";
    } else {
      print "stun_server1&: \n";
    }
  } else {
    print "user_host1&: " . $domain . "\n";
    print "user_outbound1&: " . $domain . "\n";
    if ($nat == "yes") {
      print "stun_server1&: " . $domain . "\n";
    } else {
      print "stun_server1&: \n";
    }
  }
  print "ldap_server&: " . $SERVER_NAME . "\n";
  print "ldap_port&: 389\n";
  print "ldap_base&: \n";
  print "ldap_username&: cn=Snom,ou=Snom\n";
  print "ldap_password&: " . $suser["userPassword"][0] . "\n";
  print "ldap_search_filter&: (&(telephoneNumber=*)(cn=%))\n";
  print "ldap_number_filter&: (&(telephoneNumber=%)(cn=*))\n";
  print "ldap_name_attributes&: cn\n";
  print "ldap_number_attributes&: telephoneNumber\n";
  print "ldap_display_name&: %cn\n";
  print "ldap_max_hits&: 50\n";

  if ($sipver[0] >= 8) {
    print "guess_number&: on\n";
    print "guess_start_length&: 2\n";
    print "ldap_predict_text&: on\n";
    print "ldap_sort_results&: on\n";
  }

  if ($mac != "") {
    if ($exten != "") {
      print "phone_name&: exten-" . $exten . "\n";
      print "user_symmetrical_rtp1&: on\n";
      if ($dtmfmode == "info") {
        print "user_dtmf_info1&: on\n";
      } else {
        print "user_dtmf_info1&: off\n";
      }
      print "user_name1&: " . $exten . "\n";
      print "http_user&: " . $exten . "\n";
    }
    if ($pass != "") {
      print "user_pass1&: " . $pass . "\n";
      print "http_pass&: " . $pass . "\n";
    }
    if ($name != "") {
      print "user_realname1&: " . $name . "\n";
    }
    if ($usermode == "0") {
      print "admin_mode&: on\n";
    } else {
      print "admin_mode&: off\n";
    }    
  }
}
print "admin_mode_password&: 1234\n";
print "user_expiry1&: 600\n";
print "user_ringer1&: Ringer" . $defring . "\n";
print "user_sipusername_as_line1&: on\n";
print "codec1_name1&: 18\n";
print "codec2_name1&: 3\n";
print "codec3_name1&: 2\n";
print "codec4_name1&: 0\n";
print "codec5_name1&: 8\n";
print "mwi_notification&: silent\n";
print "update_policy&: auto_update\n";
print "firmware_interval&: 1440\n";

if (($SERVER_NAME != "") && ($phone != "") && (is_file("snom" . $phone . "-fw.php"))) {
    print "firmware_status&: http://" . $SERVER_NAME . "/snom/snom" . $phone . "-fw.php\n";
//  print "firmware_status: http://provisioning.snom.com/update6to7/firmware.php\n";
};


if ($sipver[0] < 8) {
  if (($vlantag != "") && ($vlantag > 1)) {
    print "vlan&: " . $vlantag . " 5\n";
  } else {
    print "vlan!: \n";
  }
  print "user_active1&: on\n";
  print "user_active2&: off\n";
  print "user_active3&: off\n";
  print "user_active4&: off\n";
  print "user_active5&: off\n";
  print "user_active6&: off\n";
  print "user_active7&: off\n";
  print "user_active8&: off\n";
  print "user_active9&: off\n";
  print "user_active10&: off\n";
  print "user_active11&: off\n";
  print "user_active12&: off\n";
%>
dkey_record&: keyevent F_REC
dkey_retrieve&: keyevent F_RETRIEVE
dkey_redial&: keyevent F_REDIAL
dkey_help&: keyevent F_HELP
dkey_snom&: keyevent F_SNOM
dkey_conf&: keyevent F_CONFERENCE
dkey_transfer&: keyevent F_TRANSFER
dkey_hold&: keyevent F_R
dkey_dnd&: keyevent F_DND
dkey_directory&: keyevent F_ADR_BOOK
dkey_menu&: keyevent F_SETTINGS
<%
  if ($phone == "300") {%>
fkey0&: line
fkey1&: line
fkey2&: keyevent F_REDIAL
fkey3&: keyevent F_ADR_BOOK
fkey4&: keyevent F_TRANSFER
fkey5&: keyevent F_MUTE
fkey_context0&: 1
fkey_context1&: 1
fkey_context2&: 1
fkey_context3&: 1
fkey_context4&: 1
fkey_context5&: 1
<%
  }
} else {
  if (($vlantag != "") && ($vlantag > 1)) {
    print "vlan_id&: " . $vlantag  . "\n";
    print "vlan_qos&: 5\n";
  } else {
    print "vlan_id!: \n";
    print "vlan_qos!: \n";
  }
  if ($phone == "300") {%>
fkey0&: line
fkey1&: line
fkey2&: keyevent F_REDIAL
fkey3&: keyevent F_DIRECTORY_SEARCH
fkey4&: keyevent F_TRANSFER
fkey5&: keyevent F_MUTE
fkey_context0&: 1
fkey_context1&: 1
fkey_context2&: 1
fkey_context3&: 1
fkey_context4&: 1
fkey_context5&: 1
<%
  } else {%>
dkey_record&: keyevent F_REC
dkey_retrieve&: keyevent F_RETRIEVE
dkey_redial&: keyevent F_REDIAL
dkey_help&: keyevent F_HELP
dkey_snom&: keyevent F_SNOM
dkey_conf&: keyevent F_CONFERENCE
dkey_transfer&: keyevent F_TRANSFER
dkey_hold&: keyevent F_R
dkey_dnd&: keyevent F_DND
dkey_directory&: keyevent F_DIRECTORY_SEARCH
dkey_menu&: keyevent F_SETTINGS
gui_fkey1&: F_REGS
gui_fkey2&: F_CALL_LIST
gui_fkey3&: F_DIRECTORY_SEARCH
gui_fkey4&: F_MISSED_LIST
<%
  }
}

if ($phone != "300") {
  for ($key=0;$key < 54;$key++) {
    print "fkey_context" . $key . "&: 1\n";
  }

  if ($mac != "") {
    if ($exten != "") {
      $kdef[2]="speed 9";
      $kdef[3]="orbit 700";
      $kdef[4]="dest 701";
      $kdef[5]="dest 702";
      $kdef[6]="dest 900";
      $kdef[7]="dest 901";

      $fkeys=pg_query($db,"SELECT substr(key,5),value FROM astdb WHERE family='" . $exten . "' AND key ~ '^fkey[0-9]+\$' ORDER BY lpad(substr(key,5),3,0)");
      $lastfk=0;
      for($row=0;$row < pg_num_rows($fkeys);$row++) {
        $rdat=pg_fetch_array($fkeys,$row);
        if ($rdat[0] < 12) {
          $kdone[$rdat[0]]=1;
        }
        for($lkey=$lastfk;$lkey<=$rdat[0]-1;$lkey++) {
  	  if (($lkey < 12) && ($kdef[$lkey] != "")) {
            print "fkey" . $lkey . "&: " . $kdef[$lkey] . "\n";
            $kdone[$lkey]=1;
          } else {
            print "fkey" . $lkey . "&: line \n";
          }
        }
        $lastfk=$rdat[0]+1;
        if ($rdat[1] == "1") {
          print "fkey" . $rdat[0] . "&: line \n";
        } else if ($rdat[1] == "700") {
          print "fkey" . $rdat[0] . "&: orbit 700\n"; 
        } else if ((($rdat[1] > 700) && ($rdat[1] < 750)) || (($rdat[1] >= 900) && ($rdat[1] <= 999))) {
          print "fkey" . $rdat[0] . "&: dest " . $rdat[1] . "\n"; 
        } else {
          print "fkey" . $rdat[0] . "&: blf <sip:" . $rdat[1] . "@" . $domain . ";user=phone>|*8\n";
        }
      }
      if ($phone != "300") {
        for($lkey=$lastfk;$lkey< 54;$lkey++) {
            print "fkey" . $lkey . "&: line \n";
        }
      }
      for($kcnt=0;$kcnt<12;$kcnt++) {
        if (! $kdone[$kcnt]) {
          if ($kdef[$kcnt]) {
            print "fkey" . $kcnt . "&: " . $kdef[$kcnt] . "\n";
          } else {
            print "fkey" . $kcnt . "&: line \n";
          }
        }
      }
    }
  }
}

$pbook=pg_query($db,"SELECT name,number,type FROM snom_pbook WHERE exten='" . $exten . "' LIMIT 100");
for($row=0;$row < pg_num_rows($pbook);$row++) {
  $rdat=pg_fetch_array($pbook,$row);
  print "tn_" . $row . "&: " . $rdat[0] . "\n";
  print "tu_" . $row . "&: " . $rdat[1] . "\n";
  print "tc_" . $row . "&: " . $rdat[2] . "\n";
  print "to_" . $row . "&: line1\n";
}  

$gdbquery="SELECT lpad(substr(key,7),2,'0'),value FROM astdb WHERE family='" . $exten . "' AND key ~ '^speed\-([0-9]+)|([*#])' ORDER BY lpad(substr(key,7),2,'0')";
$qgetdata=pg_query($db,$gdbquery);


for($i=0;$i < pg_num_rows($qgetdata);$i++){
  $getdata=pg_fetch_array($qgetdata,$i);
  if (($getdata[0][1] != "*") && ($getdata[0][1] != "#")) {
    $sdpos=sprintf("%d",$getdata[0]);
    if ($sdpos > 9) {
      $sdpos=$sdpos+2;
    }
  } else if ($getdata[0][1] == "#"){
    $sdpos=10;
  } else if ($getdata[0][1] == "*"){
    $sdpos=11;
  }
  print "speed" . $sdpos . "&: " . $getdata[1] . "\n";
}

%>
