<%
header('Content-type: text/xml');

print "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
%>
<settings>
  <phone-settings>
    <language perm="R">English(UK)</language>
    <dnd_on_code perm="R">*541</dnd_on_code>
    <dnd_off_code perm="R">*540</dnd_off_code>
    <utc_offset perm="R">7200</utc_offset>
    <ntp_server perm="R"><%print $domain;%></ntp_server>
    <timezone perm="R">CAT+2</timezone>
    <challenge_response perm="R">off</challenge_response>
    <filter_registrar perm="!">off</filter_registrar>
    <auto_dial perm="!">5</auto_dial>
    <admin_mode_password perm="R">1234</admin_mode_password>
    <admin_mode_password_confirm perm="R">1234</admin_mode_password_confirm>
    <tone_scheme perm="R">GBR</tone_scheme>
    <auto_connect_type perm="R">auto_connect_type_handset</auto_connect_type>
<%
if ($sipver[0] >= 8) {%>
    <guess_number perm="R">on</guess_number>
    <guess_start_length perm="R">2</guess_start_length>
    <ldap_predict_text perm="R">on</ldap_predict_text>
    <ldap_sort_results perm="R">on</ldap_sort_results>
<%
}
%>
    <display_method perm="R">display_name_number</display_method>
    <update_policy perm="R">auto_update</update_policy>
<% 
if ($sipver[0] >= 8) {
  if (($vlantag != "") && ($vlantag > 1)) {
%>
    <vlan_id perm="R"><%print $vlantag;%></vlan_id>
    <vlan_qos perm="R">5</vlan_qos>
<%
  } else {
%>
    <vlan_id perm="!"></vlan_id>
    <vlan_qos perm="!"></vlan_qos>
<%
  }
} else {
  if (($vlantag != "") && ($vlantag > 1)) {
%>
    <vlan perm="R"><%print $vlantag . " 5";%></vlan>
<%
  } else {
%>
    <vlan perm="!"></vlan>
<%
  }
}
if (($SERVER_NAME != "") && ($phone != "") && (is_file("snom" . $phone . "-fw.php"))) {
%>
    <firmware_status><%print "http://" . $SERVER_NAME . "/snom/snom" . $phone . "-fw.php";%></firmware_status>
<%
//  print "firmware_status: http://provisioning.snom.com/update6to7/firmware.php\n";
};
%>
    <firmware_interval perm="R">1440</firmware_interval>
    <with_flash perm="R">on</with_flash>
    <eth_net perm="R"><%print $netport;%></eth_net>
    <ntp_refresh_timer perm="!">300</ntp_refresh_timer>
    <keyboard_lock_emergency perm="R"></keyboard_lock_emergency>
    <subscription_delay perm="!">5</subscription_delay>
    <web_language perm="R">English</web_language>
    <date_us_format perm="R">on</date_us_format>
    <time_24_format perm="R">on</time_24_format>
    <show_clock perm="R">on</show_clock>
    <dialnumber_us_format perm="R">on</dialnumber_us_format>
    <redirect_event perm="R">none</redirect_event>
    <redirect_time perm="R"></redirect_time>
    <redirect_number perm="R"></redirect_number>
    <redirect_busy_number perm="R"></redirect_busy_number>
    <redirect_on_timeout perm="R"></redirect_on_timeout>
    <redirect_time_number perm="R"></redirect_time_number>
    <redirect_time_on_code perm="R"></redirect_time_on_code>
    <redirect_time_off_code perm="R"></redirect_time_off_code>
    <redirect_allways perm="R"></redirect_allways>
    <redirect_always_on_code perm="R"></redirect_always_on_code>
    <redirect_always_off_code perm="R"></redirect_always_off_code>
    <redirect_on_busy perm="R"></redirect_on_busy>
    <redirect_busy_on_code perm="R"></redirect_busy_on_code>
    <redirect_busy_off_code perm="R"></redirect_busy_off_code>
    <auto_connect perm="R">off</auto_connect>
    <auto_connect_indication perm="R">on</auto_connect_indication>
    <message_led_other perm="R">on</message_led_other>
    <privacy_in perm="R">off</privacy_in>
    <privacy_out perm="R">off</privacy_out>
    <presence_timeout perm="R">15</presence_timeout>
    <keyboard_lock perm="R">off</keyboard_lock>
    <keyboard_lock_pw perm="R"></keyboard_lock_pw>
    <no_dnd perm="R"><%print ($dndsetting == "-1") ? "on" : "off";%></no_dnd>
    <call_waiting perm="R">on</call_waiting>
    <mwi_dialtone perm="R">stutter</mwi_dialtone>
    <mwi_notification perm="R">silent</mwi_notification>
    <ringer_headset_device perm="R">speaker</ringer_headset_device>
    <alert_internal_ring_text perm="R">alert-internal</alert_internal_ring_text>
    <alert_external_ring_text perm="R">alert-external</alert_external_ring_text>
    <alert_group_ring_text perm="R">alert-group</alert_group_ring_text>
    <intercom_enabled perm="R">on</intercom_enabled>
    <subscription_expiry perm="!">300</subscription_expiry>
    <user_active idx="1" perm="R">on</user_active>
    <user_active idx="2" perm="R">off</user_active>
    <user_active idx="3" perm="R">off</user_active>
    <user_active idx="4" perm="R">off</user_active>
    <user_active idx="5" perm="R">off</user_active>
    <user_active idx="6" perm="R">off</user_active>
    <user_active idx="7" perm="R">off</user_active>
    <user_active idx="8" perm="R">off</user_active>
    <user_active idx="9" perm="R">off</user_active>
    <user_active idx="10" perm="R">off</user_active>
    <user_active idx="11" perm="R">off</user_active>
    <user_active idx="12" perm="R">off</user_active>
    <dkey_record perm="R">keyevent F_REC</dkey_record>
    <dkey_retrieve perm="R">keyevent F_RETRIEVE</dkey_retrieve>
    <dkey_redial perm="R">keyevent F_REDIAL</dkey_redial>
    <dkey_help perm="R">keyevent F_HELP</dkey_help>
    <dkey_snom perm="R">keyevent F_SNOM</dkey_snom>
    <dkey_conf perm="R">keyevent F_CONFERENCE</dkey_conf>
    <dkey_transfer perm="R">keyevent F_TRANSFER</dkey_transfer>
    <dkey_hold perm="R">keyevent F_R</dkey_hold>
    <dkey_dnd perm="R">keyevent F_DND</dkey_dnd>
    <dkey_menu perm="R">keyevent F_SETTINGS</dkey_menu>
    <dkey_directory perm="R">keyevent F_DIRECTORY_SEARCH</dkey_directory>
<%

$getring=pg_query($db,"SELECT substr(key,6,1),value FROM astdb WHERE family = '" . $exten . "' AND key ~ '^SRING[0-3]$'");

for($i=0;$i < pg_num_rows($getring);$i++){
  $getdata=pg_fetch_array($getring,$i);
  $dring[$getdata[0]]=true;
  switch ($getdata[0]) {
    case 0:print "    <ring_sound perm=\"R\">Ringer" . $getdata[1] . "</ring_sound>\n";
	   $defring=$getdata[1];
           break;
    case 1:print "    <alert_internal_ring_sound perm=\"R\">Ringer" . $getdata[1] . "</alert_internal_ring_sound>\n";
           break;
    case 2:print "    <alert_group_ring_sound perm=\"R\">Ringer" . $getdata[1] . "</alert_group_ring_sound>\n";
           break;
    case 3:print "    <alert_external_ring_sound perm=\"R\">Ringer" . $getdata[1] . "</alert_external_ring_sound>\n";
           break;
  }
}

for($gotring=0;$gotring <= 3;$gotring++) {
  if (!$dring[$gotring]) {
    switch ($gotring) {
      case 0:print "    <ring_sound perm=\"R\">Ringer6</ring_sound>\n";
  	     $defring=6;
             break;
      case 1:print "    <alert_internal_ring_sound perm=\"R\">Ringer3</alert_internal_ring_sound>\n";
             break;
      case 2:print "    <alert_group_ring_sound perm=\"R\">Ringer1</alert_group_ring_sound>\n";
             break;
      case 3:print "    <alert_external_ring_sound perm=\"R\">Ringer6</alert_external_ring_sound>\n";
             break;
    }
  }
}

if ($domain != "") {
%>
    <ldap_server perm="R"><% print$SERVER_NAME;%></ldap_server>
    <ldap_port perm="R">389</ldap_port>
    <ldap_base perm="R"></ldap_base>
    <ldap_username perm="R">cn=Snom,ou=Snom</ldap_username>
    <ldap_password perm="R"><%print $suser["userPassword"][0];%></ldap_password>
    <ldap_search_filter perm="R"><%print htmlentities("(&(telephoneNumber=*)(cn=%))");%></ldap_search_filter>
    <ldap_number_filter perm="R"><%print htmlentities("(&(telephoneNumber=%)(cn=*))");%></ldap_number_filter>
    <ldap_name_attributes perm="R">cn</ldap_name_attributes>
    <ldap_number_attributes perm="R">telephoneNumber</ldap_number_attributes>
    <ldap_display_name perm="R">%cn</ldap_display_name>
    <ldap_max_hits perm="R">50</ldap_max_hits>
<%
  $port = ($transport == "tls") ? "5061" : "5060";
  $proxy = ($transport == "udp" || $transport == "") ? $domain . ":" . $port : $domain . ":" . $port . ";transport=" . $transport;
  print "    <user_host idx=\"1\" perm=\"R\">" . $domain . "</user_host>\n";
  print "    <user_outbound idx=\"1\" perm=\"R\">" . $proxy . "</user_outbound>\n";
  print "    <retry_after_failed_subscribe idx=\"1\" perm=\"R\">30</retry_after_failed_subscribe>\n";
  if ($nat == "yes") {
    print "    <stun_server idx=\"1\" perm=\"R\">" . $domain . "</stun_server>\n";
  } else {
    print "    <stun_server idx=\"1\" perm=\"R\"></stun_server>\n";
  }
%>
    <user_expiry idx="1" perm="R">600</user_expiry>
    <user_ringer idx="1" perm="R"><%print "Ringer" . $defring;%></user_ringer>
    <user_sipusername_as_line idx="1" perm="R">on</user_sipusername_as_line>
    <codec1_name idx="1" perm="R">18</codec1_name>
    <codec2_name idx="1" perm="R">3</codec2_name>
    <codec3_name idx="1" perm="R">2</codec3_name>
    <codec4_name idx="1" perm="R">0</codec4_name>
    <codec5_name idx="1" perm="R">8</codec5_name>
<%
  if ($mac != "") {
    if ($exten != "") {
      print "    <phone_name perm=\"R\">exten-" . $exten . "</phone_name>\n";
      print "    <user_idle_text idx=\"1\" perm=\"R\">" . $exten . "</user_idle_text>\n"; 
      print "    <user_symmetrical_rtp idx=\"1\" perm=\"R\">on</user_symmetrical_rtp>\n";
      if ($encrypt != "no") {
        print "    <user_srtp idx=\"1\" perm=\"R\">on</user_srtp>\n";
        $encdat=explode(",",$encrypt);
        print "    <user_savp idx=\"1\" perm=\"R\">";
        print ($encdat[0] == "yes") ? "mandatory" : "optional";
	print "</user_savp>\n";
	if ($encdat[1] ==  "32bit") {
          print "    <user_auth_tag idx=\"1\" perm=\"R\">on</user_auth_tag>\n";
        } else {
          print "    <user_auth_tag idx=\"1\" perm=\"R\">off</user_auth_tag>\n";
	}
      } else {
        print "    <user_srtp idx=\"1\" perm=\"R\">off</user_srtp>\n";
        print "    <user_savp idx=\"1\" perm=\"R\">off</user_savp>\n";
      }
      print "    <user_dtmf_info idx=\"1\" perm=\"R\">";
      print ($dtmfmode == "info") ? "on" : "off";
      print "</user_dtmf_info>\n";
      print "    <user_name idx=\"1\" perm=\"R\">" . $exten . "</user_name>\n";
      print "    <http_user perm=\"R\">" . $exten . "</http_user>\n";
    }
    if ($pass != "") {
      print "    <user_pass idx=\"1\" perm=\"R\">" . $pass . "</user_pass>\n";
      print "    <http_pass perm=\"R\">" . $pass . "</http_pass>\n";
    }
    if ($name != "") {
      print "    <user_realname idx=\"1\" perm=\"R\">" . $name . "</user_realname>\n";
    }
    print "    <admin_mode perm=\"R\">";
    print ($usermode == "0") ? "on" : "off";
    print "</admin_mode>\n";
  }
}

if ($phone != "300") {
%>
    <gui_fkey1 perm="R">F_REGS</gui_fkey1>
    <gui_fkey2 perm="R">F_CALL_LIST</gui_fkey2>
    <gui_fkey3 perm="R">F_DIRECTORY_SEARCH</gui_fkey3>
    <gui_fkey4 perm="R">F_MISSED_LIST</gui_fkey4>
<%
}
%>
  </phone-settings>
  <functionKeys>
<%
if ($phone != "300") {
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
            print "    <fkey idx=\"" . $lkey . "\" perm=\"R\">" . $kdef[$lkey] . "</fkey>\n";
            $kdone[$lkey]=1;
          } else {
            print "    <fkey idx=\"" . $lkey . "\" perm=\"R\">line</fkey>\n";
          }
        }
        $lastfk=$rdat[0]+1;
        if ($rdat[1] == "1") {
          print "    <fkey idx=\"" . $rdat[0] . "\" perm=\"R\">line</fkey>\n";
        } else if ($rdat[1] == "700") {
          print "    <fkey idx=\"" . $rdat[0] . "\" perm=\"R\">orbit 700</fkey>\n";
        } else if ((($rdat[1] > 700) && ($rdat[1] < 750)) || (($rdat[1] >= 900) && ($rdat[1] <= 999))) {
          print "    <fkey idx=\"" . $rdat[0] . "\" perm=\"R\">dest " . $rdat[1] . "</fkey>\n";
        } else {
          print "    <fkey idx=\"" . $rdat[0] . "\" perm=\"R\">";
          print htmlentities("blf <sip:" . $rdat[1] . "@" . $domain . ";user=phone>|*8");
          print "</fkey>\n";
        }
      }
      for($lkey=$lastfk;$lkey< 54;$lkey++) {
          print "    <fkey idx=\"" . $lkey . "\" perm=\"R\">line</fkey>\n";
      }
      for($kcnt=0;$kcnt<12;$kcnt++) {
        if (! $kdone[$kcnt]) {
          if ($kdef[$kcnt]) {
            print "    <fkey idx=\"" . $kcnt . "\" perm=\"R\">" . $kdef[$kcnt] . "</fkey>\n";
          } else {
            print "    <fkey idx=\"" . $kcnt . "\" perm=\"R\">line</fkey>\n";
          }
        }
      }
    }
  } else {
%>
    <fkey idx="0" perm="R">line</fkey>
    <fkey idx="1" perm="R">line</fkey>
    <fkey idx="2" perm="R">keyevent F_REDIAL</fkey>
    <fkey idx="3" perm="R">keyevent F_DIRECTORY_SEARCH</fkey>
    <fkey idx="4" perm="R">keyevent F_TRANSFER</fkey>
    <fkey idx="5" perm="R">keyevent F_MUTE</fkey>
<%
  }
}
%>
  </functionKeys>
<%
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
</settings>
