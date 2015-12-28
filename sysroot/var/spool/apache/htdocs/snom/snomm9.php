<?php
include "../cdr/auth.inc";
include "../cdr/autoadd.inc";

$mac=strtoupper($mac);

if ($mac == "") {
  exit;
}

$getphoneq="SELECT name,secret,fullname,registrar,snomlock,nat,vlan,cdnd from users 
              LEFT OUTER JOIN features ON (exten=name) WHERE snommac='" . $mac . "' ORDER BY name";

$getphone=pg_query($db,$getphoneq);
$num=pg_num_rows($getphone);

if ($num < 4) {
  for($newe=$num+1;$newe <= 4;$newe++) {
    createexten($mac,"SNOM_M9","","","");
  }
  $getphone=pg_query($db,$getphoneq);
  $num=pg_num_rows($getphone);
}

print "<?";
?>
xml version="1.0" encoding="utf-8"?>
<settings>
  <phone-settings>
<?php
  for ($cnt=0;$cnt<$num;$cnt++) {
    list($exten,$pass,$name,$domain,$usermode,$nat,$vlantag,$dndsetting)=pg_fetch_array($getphone,$cnt);
    if ($cnt == 0) {
      if ($vlantag > 1) {
        $vlanprio=5;
      } else {
        $vlantag=0;
        $vlanprio=0;
      }
?>
  <base_name perm="RW"><?php print $exten;?></base_name>
  <setting_server perm="RW">http://<?php print $domain;?>/m9/<?php print $mac;?>.cfg</setting_server>
  <ntp_server perm="RW"><?php print $domain;?></ntp_server>
  <vlan_id perm="RW"><?php print $vlantag;?></vlan_id>
  <vlan_prio perm="RW"><?php print $vlanprio;?></vlan_prio>
  <gmt_offset perm="RW">7200</gmt_offset>
  <tones perm="RW">2</tones>
<?php
    }
    if ($domain == "" ) {
      $domain=$SERVER_NAME;
    }
    $cnt2=$cnt+1;
?>
  <codec1_name perm="RW" idx="<?php print $cnt2;?>">3</codec1_name>
  <codec2_name perm="RW" idx="<?php print $cnt2;?>">4</codec2_name>
  <codec3_name perm="RW" idx="<?php print $cnt2;?>">6</codec3_name>
  <codec4_name perm="RW" idx="<?php print $cnt2;?>">2</codec4_name>
  <codec5_name perm="RW" idx="<?php print $cnt2;?>">1</codec5_name>
  <codec6_name perm="RW" idx="<?php print $cnt2;?>">5</codec6_name>
  <codec7_name perm="RW" idx="<?php print $cnt2;?>">0</codec7_name>
  <user_active perm="RW" idx="<?php print $cnt2;?>">true</user_active>
  <user_pbxtype perm="RW" idx="<?php print $cnt2;?>">asterisk</user_pbxtype>
  <user_authname perm="RW" idx="<?php print $cnt2;?>"><?php print $exten;?></user_authname>
  <user_host perm="RW" idx="<?php print $cnt2;?>"><?php print $domain;?></user_host>
  <user_name perm="RW" idx="<?php print $cnt2;?>"><?php print $exten;?></user_name>
  <user_pass perm="RW" idx="<?php print $cnt2;?>"><?php print $pass;?></user_pass>
  <user_realname perm="RW" idx="<?php print $cnt2;?>"><?php print $name;?></user_realname>
  <user_mailbox perm="RW" idx="<?php print $cnt2;?>"><?php print $exten;?></user_mailbox>
  <user_sip_info perm="RW" idx="<?php print $cnt2;?>">1</user_sip_info>
<?php
  }
?>
 </phone-settings>
</settings>
