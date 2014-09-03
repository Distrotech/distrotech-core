var GLP;	// means "GoLoginPopUp"
var blur = 0;
var starttime = new Date();
var startclock = starttime.getTime();
var mytimeleft = 0;
var context = '';
var next_url = '';
var timeleft_str = '';

function handler() {
	if (handler.arguments.length==0) return null;

	context = handler.arguments[0];								// context is always our first argument.
	if (handler.arguments[1]) mytimeleft = handler.arguments[1];// timeleft is always our second argument (might be empty).
	if (handler.arguments[2]) next_url = handler.arguments[2];	// next_url is always our third argument (mostly null).
	switch ( context ) {										// context is always our first argument.

	case 'success':
		{
		success(handler.arguments[3], handler.arguments[4]);
		break;
		}
	case 'failed':
		{
		failed();
		break;
		}
	case 'notyet':
		{
		notyet();
		break;
		}
	case 'popup2':
		{
		popup2(handler.arguments[3]);
		break;
		}
	case 'popup3':
		{
		popup3();
		break;
		}
	default:
		{
		break;
		}
	}
	return null;
}

function success(url,userurl) {
	if (self.name == 'GLS') {
		doTime();
		self.location = url;
	} else {
		popUpWindow(url,'GLS','410','325',0,0,0,0);

		if ( userurl != '' ) {
			GLP.opener.location = userurl;
		} else {
			var isIE = new Boolean(navigator.userAgent.indexOf("MSIE") != -1);
			if ( isIE == true )
				GLP.opener.location = 'about:home';
			else
				GLP.opener.home();
		}
	}
}

function failed() {
	document.f.uid.focus();
	if (self.name != 'GLS') {
		popUpWindow('','GLS','410','325',0,0,0,0);	// GLS means "GoLoginStatus"
		closeGLP();
	}
}

function notyet() {document.f.uid.focus();}

function popup2(redirurl) {
	if (self.name == 'GLS') {
		doTime();

		if (redirurl) opener.location = redirurl;

		self.focus();
		blur = 0;
	}
}

function popup3() {
	if ( self.name == 'GLS' ) {self.focus(); blur = 1;}
}

function doTime() {
	window.setTimeout('doTime()', 1000);
	t = new Date();
	time = Math.round((t.getTime() - starttime.getTime())/1000);

	if (mytimeleft) {
		time = mytimeleft - time;
		if (time <= 0) window.location = next_url;
	}

	if (time < 0) time = 0;

	hours = (time - (time % 3600)) / 3600;
	time = time - (hours * 3600);
	mins = (time - (time % 60)) / 60;
	secs = time - (mins * 60);

	if (hours < 10) hours = '0' + hours;
	if (mins < 10)  mins = '0' + mins;
	if (secs < 10)  secs = '0' + secs;

	if (mytimeleft)
		title = remainingtime + hours + ':' + mins + ':' + secs;
	else
		title = onlinetime + hours + ':' + mins + ':' + secs;

	if (context=='popup2' && self.name=='GLS')
		document.getElementById('stat').innerHTML = title;
}

function doOnBlur(context) {
	if (context == 'popup2' && self.name == 'GLS') {
		if ( blur == 0 ) {blur = 1; self.focus();}
	}
}

function closeGLP() {
	if (GLP && GLP.open && !GLP.closed) GLP.close();
}

function popUpWindow(url,
	name,
	width,
	height,
	show_location,
	show_directories,
	show_menubar,
	show_toolbar) {
	var features = 
	'width=' + width + ',' +
	'height=' + height + ',' +
	'screenx=20,' +
	'screeny=20,' +
	'location=' + show_location + ',' +
	'menubar=' + show_menubar + ',' +
	'toolbar=' + show_toolbar + ',' +
	'directories=' + show_directories + ',' + 
	'scrollbars=yes,' +
	'dependent=no';

	if (GLP && GLP.open && !GLP.closed) GLP.close();

	GLP = window.open(url, name, features);
}
