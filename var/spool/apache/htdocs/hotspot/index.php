<?php

/*
 * This is gologin, a php front end to chillispot.
 *
 *  last change 2006-06-23, v 1.24
 *
 *    (MK) All Actions are now encapsulated in one class - much smaller codebase and more security.
 *    (MK) loginscript renamed do gologin for better differentiation
 *    (MK) now the frontend speaks in english and german to you :-)
 *
 *  last change 2006-05-12, v 1.23
 *
 *    This version is a successor of 1.21, bug-fixes, enhancements.
 *
 *    Tested environment:
 *    unchanged, compare below
 *
 *    Addressed bugs:
 *    - Popup did not close after logout if done in main window.
 *    - Popup staid open in case of login error.
 *
 *    New features: 
 *    - Error page now contains direct link to the hopefully running chillispot for accessing the right URL,
 *      error text adjusted accordingly.
 *    - If no userurl different from UAM_URL or BASE_URL was requested, the browser will now be redirected to the
 *      home page the user set up in the browser himself. This only applies after successful login, after the popup
 *      opened. No Popup - no redirect. WISPr-Redirection-URL still wins if present.
 *    - Feature mentioned above works not only for IE but also for FireFox, Opera ...
 *
 *    Test Requests:
 *    - Works with chillispot 1.0 ?
 *    - Works as intended in general ?
 *    - Is the redirect to the user's home page wanted or not a good idea ?
 *
 *    Contact:
 *    - Please use http://www.chillispot.org/forum/viewtopic.php?p=3967 to provide your feedback
 *
 *  last change 2006-05-10, v 1.21
 *
 *    This version is basing on the non-register_globals - compliant version 1.20a of
 *    Brent Scheffler. (http://brentscheffler.com/blog/archives/10)
 *    It is mainly a compilation of a number of bugfixes which were necessary for me to
 *    make it run properly.
 *
 *    Tested environment:
 *    - Debian testing (etch), Kernel 2.4.27
 *    - Apache 2.0.55
 *    - PHP 5.1.2
 *    - chillispot 1.1.0 (available from http://www.chillispot.org/cvs/, 14-Feb-2006)
 *    - freeradius 1.10.0
 *    - mysql 5.0.20
 *
 *    Addressed bugs:
 *    - Various popup issues (popup won't work at all), especially for users with "Max-All-Session" attribute set
 *    - Redirection after login works now, after receiving the popup the user gets redirected to the page requested initially
 *    - WISPr-Redirection-URL - radreply attribute also works properly and it overwrites the default redirection
 *
 *    Test Requests:
 *    - Works with chillispot 1.0 ?
 *    - Works as intended in general ?
 *
 *    Contact:
 *    - For the next days/weeks - please use http://www.chillispot.org/forum/viewtopic.php?p=3967 to provide your feedback
 *
 * 	last change 2005-11-18
 *
 *		This is a slightly modified version of wlogin-1.20 by "drewb" (http://drewb.com).
 *		The only changes include slight logical additions and increased compatibility for
 *		for PHP installs that do not/cannot set the "register_globals on" in PHP.INI -
 *		If you have any questions or trouble with these scripts, DO NOT contact the original
 *		author - visit http://brentscheffler.com/blog
 *
 *
 * Re-implementation of hotspotlogin03.php by Cedric which was forked
 * from original chillispot.org's hotspotlogin.cgi by Kanne
 *
 *
 */

define('INC_DIR', 'lib/');

require_once(INC_DIR . 'config.php');
require_once(INC_DIR . 'languages.php');
require_once(INC_DIR . 'actions.php');

/*
 * possible Cases:
 *
 *  attempt to login                          login=Login
 *  1: Login successful                       res=success
 *  2: Login failed                           res=failed
 *  3: Logged out                             res=logoff
 *  4: Tried to login while already logged in res=already
 *  5: Not logged in yet                      res=notyet
 * 11: Popup                                  res=popup1
 * 12: Popup                                  res=popup2
 * 13: Popup                                  res=popup3
 *  0: It was not a form request              res=''
 *
 * Read query parameters which we care about
 *
 * $_GET['res'];
 * $_GET['challenge'];
 * $_GET['uamip'];
 * $_GET['uamport'];
 * $_GET['reply'];
 * $_GET['userurl'];
 * $_GET['timeleft'];
 * $_GET['redirurl'];
 *
 * Read form parameters which we care about
 *
 * $_GET['username'];
 * $_GET['password'];
 * $_GET['chal'];
 * $_GET['login'];
 * $_GET['logout'];
 * $_GET['prelogin'];
 * $_GET['res'];
 * $_GET['uamip'];
 * $_GET['uamport'];
 * $_GET['userurl'];
 * $_GET['timeleft'];
 * $_GET['redirurl'];
 * $_GET['store_cookie'];
 */

if (!empty($_GET['login']) && $_GET['login'] == _t('login') )
	 $context = 'login';
elseif (!empty($_GET['res']))
	 $context = $_GET['res'];
else $context = 'error';


/*
 * We need to put some standard arguments in a string for the onLoad
 * javascript function that we run on every page load.  These are:
 * context, timeleft, and next_url.
 *
 * Other arguments may be appended to these in the context specific
 * include file before the top.inc header is spit out.  In that case,
 * we'll need to remember to attach a comma before the extra args.
 */
$js_args  = "'" . $context . "',";
$js_args .= "'" . (empty($_GET['timeleft']) ? '' : $_GET['timeleft']) . "',";
$js_args .= "'" . LOGINPATH . '?res=popup3';
$js_args .= empty($_GET['uamip']) ? '' : '&uamip=' . $_GET['uamip'];
$js_args .= empty($_GET['uamport']) ? "'" : '&uamport=' . $_GET['uamport'] . "'";

// If we want to store the cookie, compose and set it...
if (isset($_GET['save_login']) && $_GET['save_login'] == 'on' ) {
	$str = $_GET['uid'] . '|' . $_GET['pwd'];
	$expire = time() + 315360000;						// expires in 10 years...
	setcookie('login', $str, $expire, '/', $_SERVER['HTTP_HOST'], true);
}

if ( isset($_COOKIE['login']) ) {
	$arr = explode('|', $_COOKIE['login']);
	$username = $arr[0];
	$password = $arr[1];
} else {
	$username = '';
	$password = '';
}

// new concept: just use methods of class "actions"
$a = new actions();
if (method_exists($a, $context))
	$a->$context();
else
	$a->error();

?>
