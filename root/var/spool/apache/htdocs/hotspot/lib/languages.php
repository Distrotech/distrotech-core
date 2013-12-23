<?php
/**
 * GoLogin language file.
 */

$GL_LANG = array();


switch ($lg) {
	case 'de':
		$GL_LANG['login'] = 'Login';
		$GL_LANG['title'] = '%s Login';
		$GL_LANG['already'] = 'Sie sind schon in %s eingeloggt.';
		$GL_LANG['loginfailed'] = 'Login zu %s fehlgeschlagen.';
		$GL_LANG['loginfailedtryagain'] = 'Sorry, Login fehlgeschlagen. Bitte versuchen Sie es noch einmal.';
		$GL_LANG['loggingin'] = 'Einloggen...';
		$GL_LANG['logginginto'] = 'Einloggen in %s';
		$GL_LANG['loggedinto'] = 'Eingeloggt in %s';
		$GL_LANG['loggedoff'] = 'Sie sind jetzt ausgeloggt.';
		$GL_LANG['loggedout'] = 'Ausgeloggt von %s';
		$GL_LANG['logout'] = 'Logout';
		$GL_LANG['chillispotonly'] = 'Login muss durch den Hotspot erfolgen. Folgen Sie einfach dem Link oben!';
		$GL_LANG['welcome'] = 'Willkommen';
		$GL_LANG['pleasewait'] = 'Bitte warten...';
		$GL_LANG['labelLogin'] = 'Login:';
		$GL_LANG['labelPassword'] = 'Passwort:';
		$GL_LANG['rememberlogin'] = 'Login merken?';
		$GL_LANG['onlinetime'] = 'Online seit: ';
		$GL_LANG['remainingtime'] = 'Verbleibende Zeit: ';
		break;
	case 'en':
	default:
		$GL_LANG['login'] = 'Login';
		$GL_LANG['title'] = '%s Login';
		$GL_LANG['already'] = 'Already logged in to %s.';
		$GL_LANG['loginfailed'] = 'Login to %s failed.';
		$GL_LANG['loginfailedtryagain'] = 'Sorry, login failed. Please try again.';
		$GL_LANG['loggingin'] = 'Logging in...';
		$GL_LANG['logginginto'] = 'Logging in to %s';
		$GL_LANG['loggedinto'] = 'Logged in to %s';
		$GL_LANG['loggedoff'] = 'You are now logged off.';
		$GL_LANG['loggedout'] = 'Logged out from %s';
		$GL_LANG['logout'] = 'Logout';
		$GL_LANG['chillispotonly'] = 'Login must be performed through ChilliSpot daemon, try following the link above!';
		$GL_LANG['welcome'] = 'Welcome';
		$GL_LANG['pleasewait'] = 'Please wait...';
		$GL_LANG['labelLogin'] = 'Login:';
		$GL_LANG['labelPassword'] = 'Password:';
		$GL_LANG['rememberlogin'] = 'Remember Login?';
		$GL_LANG['onlinetime'] = 'Online time: ';
		$GL_LANG['remainingtime'] = 'Remaining time: ';
}

// little helper-function
function _t() {											// translates a string with vars
	global $GL_LANG;
	$args = func_get_args();
	$key = array_shift($args);
	if (isset($GL_LANG[$key])) {
		if (!empty($args)) {						// use vsprintf to replace stuff inside translated string
			return vsprintf($GL_LANG[$key], $args);
		} else {
			return $GL_LANG[$key];
		}
	} else {
		return 'Translation of &raquo;'.$key.'&laquo; not found.';
	}
}

?>