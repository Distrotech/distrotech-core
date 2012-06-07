<?php

// this file contains all possible actions of GoLogin and is mainly called by index.php
// you will find all HTML that gologin spits out here.
// every action is one function of class "actions"
// this class needs the file languages.php and the helper-function "_t" for i18n...


class actions {

	// replaces top.inc:
	function head() {
		global $js_args, $context;
		$baseurl = BASE_URL;
		$hotspotname = _t('title', HOTSPOT_NAME);
		$onlinetime = _t('onlinetime');
		$remainingtime = _t('remainingtime');

		echo <<<EOT
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>$hotspotname</title>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-15">
<meta http-equiv="cache-control" content="no-cache">
<meta http-equiv="pragma" content="no-cache">
<link href="${baseurl}styles.css" rel="stylesheet" type="text/css">
<script language="javascript" type="text/javascript">
<!--
	var onlinetime='$onlinetime'; var remainingtime='$remainingtime';
//-->
</script>
<script language="javascript" type="text/javascript" src="${baseurl}fct.js"></script>
</head>
<body onload="handler($js_args)" onblur="javascript:doOnBlur('$context')">
<div class="logo"><img src="${baseurl}wireless_logo.png" alt="$hotspotname"></div>
EOT;
	}


	// replaces tail.inc:
	function foot() {
		if ( DEBUG_MODE ) {
			echo '_GET:<pre>'; print_r($_GET); echo '</pre>_POST:<pre>'; print_r($_POST); echo '</pre>';
			// for more logging:
			// $f = 'gologin.log';
			// error_log("\r\n".date('Y-m-d H:i:s')."\t$context\t$_SERVER[REQUEST_URI]", 3, $f);
		}

		echo '</body></html>';
	}


	function already() {
		$this->head();
		echo '<p class="msg">'._t('already', HOTSPOT_NAME).'</p>';
		echo '<p class="act"><a href="'.UAM_URL."/logoff\" onclick=\"popUpWindow('','GLS','272','262',0,0,0,0);closeGLP();\">"._t('logout').'</a></p>';
		$this->foot();
	}


	function error() {
		$this->head();
		echo '<p class="msg">'._t('loginfailed', HOTSPOT_NAME).'</p>';
		echo '<p class="act"><a href="'.UAM_URL.'/prelogin">'._t('login').'</a></p>';
		echo '<p class="box">'._t('chillispotonly').'</p>';
		$this->foot();
	}


	function failed() {
		$this->head();
		echo '<p class="msg">'._t('loginfailedtryagain').'</p>';
		include(INC_DIR . 'login_form.php');
		$this->foot();
	}


	function login() {
		$hex_chal	= pack('H32', $_GET['chal']);
		$newchal	= defined('UAMSECRET') ? pack('H*', md5($hex_chal.UAMSECRET)) : $hex_chal;
		$response	= md5("\0" . $_GET['pwd'] . $newchal);
		$newpwd		= pack('a32', $_GET['pwd']);
		$password	= implode ('', unpack('H32', ($newpwd ^ $newchal)));

		if ( defined('UAMSECRET') && defined('USERPASSWORD') )
			$query = '?username='.$_GET['uid'].'&password='.$password.'&userurl='.urlencode($_GET['userurl']);
		else
			$query = '?username='.$_GET['uid'].'&response='.$response.'&userurl='.urlencode($_GET['userurl']);

		header('Location: '.UAM_URL.'/logon'.$query);
		$this->head();
		echo '<p class="msg">'._t('loggingin').'</p>';
		$this->foot();
	}


	function logoff () {
		$this->head();
		echo '<p class="msg">'._t('loggedoff').'</p>';
		echo '<p class="act"><a href="'.UAM_URL.'/prelogin">'._t('login').'</a></p>';
		$this->foot();
	}


	function notyet() {
		$this->head();
		echo '<p class="msg">'._t('title', HOTSPOT_NAME).'</p>';
		include(INC_DIR . 'login_form.php');
		$this->foot();
	}


	function success() {
		global $js_args;
		if ( ( ! empty($_GET['userurl']) ) && ( ereg(UAM_URL, $_GET['userurl']) == 0 ) && ( ereg(BASE_URL, $_GET['userurl']) == 0 ) ) {
			$userurl = $_GET['userurl'];
		} else {
			$userurl = '';
		}

		// For our javascript, we need 2 extra vars: the popup url, and the userurl we cooked above.
		$js_args .= ",'" . LOGINPATH . '?res=popup2&uamip=' . $_GET['uamip']; 
		$js_args .= '&uamport=' . $_GET['uamport'];
		$js_args .= '&timeleft=' . $_GET['timeleft']; 
		$js_args .= '&redirurl=' . $_GET['redirurl'] . "',"; 
		$js_args .= "'".$userurl."'"; 

		$this->head();
		echo '<p class="msg">'._t('welcome').'</p>';
		echo '<p class="act"><a href="'.UAM_URL.'/logoff">'._t('logout').'</a></p>';
		echo '<div id="stat"></div>';
		$this->foot();
	}


	function popup1() {
		$this->head();
		echo '<p class="msg">'._t('logginginto', HOTSPOT_NAME).'</p>';
		echo '<p class="box">'._t('pleasewait').'</p>';
		$this->foot();
	}


	function popup2() {
		global $js_args;

		$js_args .= ",'".$_GET['redirurl']."'";			// For our javascript, we need 1 extra arg: redirurl.

		$this->head();
		echo '<p class="msg">'._t('loggedinto', HOTSPOT_NAME).'</p>';
		echo '<p class="act"><a href="'.UAM_URL.'/logoff">'._t('logout').'</a></p>';
		echo '<div id="stat"></div>';
		$this->foot();
	}


	function popup3() {
		$this->head();
		echo '<p class="msg">'._t('loggedout', HOTSPOT_NAME).'</p>';
		echo '<p class="act"><a href="'.UAM_URL.'/prelogin">'._t('login').'</a></p>';
		$this->foot();
	}
}


?>
