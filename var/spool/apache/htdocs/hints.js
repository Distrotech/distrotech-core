// Title: Tigra Hints
// URL: http://www.softcomplex.com/products/tigra_hints/
// Version: 1.4
// Date: 04/16/2006
// Note: Permission given to use this script in ANY kind of applications if
//    header lines are left unchanged.

var THintsS = [];
function THints (o_cfg, items) {
	this.n_id = THintsS.length;
	THintsS[this.n_id] = this;
	this.top = o_cfg.top ? o_cfg.top : 0;
	this.left = o_cfg.left ? o_cfg.left : 0;
	this.n_dl_show = o_cfg.show_delay;
	this.n_dl_hide = o_cfg.hide_delay;
	this.b_wise = o_cfg.wise;
	this.b_follow = o_cfg.follow;
	this.x = 0;
	this.y = 0;
	this.divs = [];
	this.iframes = [];
	this.show  = TTipShow;
	this.showD = TTipShowD;
	this.hide = TTipHide;
	this.move = TTipMove;
	// register the object in global collection
	this.n_id = THintsS.length;
	THintsS[this.n_id] = this;
	// filter Netscape 4.x out
	if (document.layers) return;
	var b_IE = navigator.userAgent.indexOf('MSIE') > -1,
	s_tag = ['<iframe frameborder="0" scrolling="No" id="TTifip%name%" style="visibility:hidden;position:absolute;top:0px;left:0px;',   b_IE ? 'width:1px;height:1px;' : '', o_cfg['z-index'] != null ? 'z-index:' + o_cfg['z-index'] : '', '" width=1 height=1></iframe><div id="TTip%name%" style="visibility:hidden;position:absolute;top:0px;left:0px;',   b_IE ? 'width:1px;height:1px;' : '', o_cfg['z-index'] != null ? 'z-index:' + o_cfg['z-index'] : '', '"><table cellpadding="0" cellspacing="0" border="0"><tr><td class="', o_cfg.css, '" nowrap>%text%</td></tr></table></div>'].join('');


	this.getElem = 
		function (id) { return document.all ? document.all[id] : document.getElementById(id); };
	this.showElem = 
		function (id, hide) { 
		this.divs[id].o_css.visibility = hide ? 'hidden' : 'visible'; 
		this.iframes[id].o_css.visibility = hide ? 'hidden' : 'visible'; 
		};
	
	document.onmousemove = f_onMouseMove;
	if (window.opera)
		this.getSize = function (id, b_hight) { 
			return this.divs[id].o_css[b_hight ? 'pixelHeight' : 'pixelWidth']
		};
	else
		this.getSize = function (id, b_hight) { 
			return this.divs[id].o_obj[b_hight ? 'offsetHeight' : 'offsetWidth'] 
		};
	for (i in items) {
		document.write (s_tag.replace(/%text%/g, items[i]).replace(/%name%/g, i));
		this.divs[i] = { 'o_obj' : this.getElem('TTip' + i) };
		this.divs[i].o_css = this.divs[i].o_obj.style;
		this.iframes[i] = { 'o_obj' : this.getElem('TTifip' + i) };
		this.iframes[i].o_css = this.iframes[i].o_obj.style;
		
	}
}
function TTipShow (id) {
	if (document.layers) return;
	this.hide();
	if (this.divs[id]) {
		if (this.n_dl_show) this.divs[id].timer = setTimeout('THintsS[' + this.n_id + '].showD("' + id + '")', this.n_dl_show);
		else this.showD(id);
		this.visible = id;
	}
}

function TTipShowD (id) {
	this.move(id);
	this.showElem(id);
	if (this.n_dl_hide) this.timer = setTimeout("THintsS[" + this.n_id + "].hide()", this.n_dl_hide);
}

function TTipMove (id) {
	var	n_win_l = f_scrollLeft(),
		n_win_t = f_scrollTop();

	var n_x = window.n_mouseX + n_win_l + this.left,
		n_y = window.n_mouseY + n_win_t + this.top;
		
	window.status = n_x;
	if (this.b_wise) {
		var n_w = this.getSize(id), n_h = this.getSize(id, true),
		n_win_w = f_clientWidth(), n_win_h = f_clientHeight();

		if (n_x + n_w > n_win_w + n_win_l) n_x = n_win_w + n_win_l - n_w;
		if (n_x < n_win_l) n_x = n_win_l;
		if (n_y + n_h > n_win_h + n_win_t) n_y = n_win_h + n_win_t - n_h;
		if (n_y < n_win_t) n_y = n_win_t;
	}
	this.divs[id].o_css.left = n_x + 'px';
	this.divs[id].o_css.top = n_y + 'px';
	this.iframes[id].o_css.left = n_x + 'px';
	this.iframes[id].o_css.top = n_y + 'px';
	this.iframes[id].o_css.height = (n_h-this.top) + 'px';
	this.iframes[id].o_css.width = (this.getSize(id, false)-this.left) + 'px';
}

function TTipHide () {
	if (this.timer) clearTimeout(this.timer);
	if (this.visible != null) {
		if (this.divs[this.visible].timer) clearTimeout(this.divs[this.visible].timer);
		setTimeout('THintsS[' + this.n_id + '].showElem("' + this.visible + '", true)', 10);
		this.visible = null;
	}
}

function f_onMouseMove(e_event) {
	if (!e_event && window.event) e_event = window.event;
	if (e_event) {
		window.n_mouseX = e_event.clientX;
		window.n_mouseY = e_event.clientY;
	}
	return true;
}
function f_clientWidth() {
	if (typeof(window.innerWidth) == 'number')
		return window.innerWidth;
	if (document.documentElement && document.documentElement.clientWidth)
		return document.documentElement.clientWidth;
	if (document.body && document.body.clientWidth)
		return document.body.clientWidth;
	return null;
}
function f_clientHeight() {
	if (typeof(window.innerHeight) == 'number')
		return window.innerHeight;
	if (document.documentElement && document.documentElement.clientHeight)
		return document.documentElement.clientHeight;
	if (document.body && document.body.clientHeight)
		return document.body.clientHeight;
	return null;
}
function f_scrollLeft() {
	if (typeof(window.pageXOffset) == 'number')
		return window.pageXOffset;
	if (document.body && document.body.scrollLeft)
		return document.body.scrollLeft;
	if (document.documentElement && document.documentElement.scrollLeft)
		return document.documentElement.scrollLeft;
	return 0;
}
function f_scrollTop() {
	if (typeof(window.pageYOffset) == 'number')
		return window.pageYOffset;
	if (document.body && document.body.scrollTop)
		return document.body.scrollTop;
	if (document.documentElement && document.documentElement.scrollTop)
		return document.documentElement.scrollTop;
	return 0;
}
