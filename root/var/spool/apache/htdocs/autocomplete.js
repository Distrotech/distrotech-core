/*
 * Based on code from http://www.vonloesch.de/index.html
 *
 * text The text input object to attach to
 * datain either a array or a function that will return a array directly or via the returned DOM from AJAX call to url
 * url to call with AJAX it will have '=<SEARCH TEXT>' added onto it before been called if there is no post info bellow
 * postinf if this is not null the url above will be sent via POST with '=<SEARCH TEXT>' added onto the postinf instead
 *
 */

function TextComplete(text,datain,url,postinf,pindata,onselect) {
  if (typeof(datain) != 'function') {
    this.datain=datain;
    this.datafunc=null;
  } else {
    this.datain=null;
    this.datafunc=datain;
  }

  if (typeof(onselect) == 'function') {
    this.onselect=onselect;
  } else {
    this.onselect=null;
  }

  var posi = -1;
  var self=this;

  this.text=text;
  var outp=document.createElement('div');
  var shadow=document.createElement('div');

  shadow.id='acshadow';
  shadow.style.backgroundColor='#555000';
  shadow.style.position='absolute';
  shadow.style.visibility='hidden';
  shadow.style.width=this.text.offsetWidth;

  outp.id='output';
  outp.style.fontFamily='Arial';
  outp.style.fontSize='10pt';
  outp.style.color='black';
  outp.style.border='1px solid #000000';
  outp.style.backgroundColor='#FFFFFF';
  outp.style.marginRight='2px';
  outp.style.marginBottom='2px';
  outp.style.overflow='auto';

  shadow.appendChild(outp);
  document.body.appendChild(shadow);

  this.url=url;
  if (typeof(postinf) == "string") {
    this.postinf=postinf;
    this.postfunc=null;
  } else if (typeof(postinf) == "function") {
    this.postinf=null;
    this.postfunc=postinf;
    this.pindata=pindata;
  }
  var ajax=null;

  /*
   * Allow a url to be submited to be dealt with async 
   */
  function ajaxdata(searchs) {
    var out=null;
    if (ajax == null) {
      ajax=new XMLHttpRequest();
    }
    if (ajax.readyState > 0) {
      ajax.abort();
    }
    ajax.onreadystatechange=stateChanged;
    if (self.postfunc != null) {
      self.postinf=self.postfunc(pindata);
    }
    if (self.postinf == null) {
      ajax.open('GET',self.url+'='+searchs,true);
    } else {
      out=self.postinf+'='+searchs;
      ajax.open('POST',self.url,true);
      ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      ajax.setRequestHeader("Content-length", out.length);
      ajax.setRequestHeader("Connection", "close");
    }
    ajax.send(out);
  }

  function stateChanged() {
/*
    if ((ajax.readyState == 4) && (ajax.status == 200))
     alert(ajax.responseXML+'\n\r'+ajax.responseText);
*/
    if ((ajax.readyState == 4) && (ajax.status == 200) && (ajax.responseXML != null)) {
      matched = self.datafunc(ajax.responseXML);
      configlist(matched);
    }
  }

  /*
   * Get the current leftmost position of a object
   */
  function findPosX(obj) {
    var curleft = 0;
    if (obj.offsetParent) {
      while (obj.offsetParent) {
        curleft += obj.offsetLeft;
        obj = obj.offsetParent;
      }
    } else if (obj.x) {
      curleft += obj.x;
    }
    return curleft;
  }

  /*
   * Get the current topmost postition of a object
   */
  function findPosY(obj) {
    var curtop = 0;
    if (obj.offsetParent) {
      curtop += obj.offsetHeight;
      while (obj.offsetParent) {
        curtop += obj.offsetTop;
        obj = obj.offsetParent;
      }
    } else if (obj.y) {
      curtop += obj.y;
      curtop += obj.height;
    }
    return curtop;
  }

  /*
   * Build a list to show based on the current input
   */
  function getList(beginning) {
    if (posi >= 0) {
      setColor(posi, output.style.backgroundColor, output.style.color);
      posi = -1;
    }
    if ((beginning.length >= 0) && (beginning.length != self.text.value.length)) {
      if (self.datafunc != null) {
        if (self.url != null) {
          ajaxdata(beginning);
        } else {
          matched = self.datafunc(beginning);
          configlist(matched);
        }
      } else {
        matched = new Array();
        for (key in self.datain) {
          if (beginning.toLowerCase() == self.datain[key].substring(0,beginning.length).toLowerCase()) {
            matched[key] = self.datain[key];
          }
        }
        configlist(matched);
      }
    } else {
      shadow.style.visibility = 'hidden';
    }
  }

  /*
   * Rebuild the popup list this is called from the callback/AJAX
   */
  function configlist(words) {
    while (outp.hasChildNodes()) {
      noten=outp.firstChild;
      outp.removeChild(noten);
    }
    var itmcnt=0;
    outheight=0;
    for (key in words) {
      sp = document.createElement('div');
      sp.style.cursor='pointer';
      sp.id=key;
      sp.appendChild(document.createTextNode(words[key]));
      sp.onkeypress = keyHandler;
      sp.onmouseover = mouseHandler;
      sp.onclick = mouseClick;
      outp.appendChild(sp);
      if (itmcnt < 10) {
        outheight=outheight+sp.offsetHeight;
      }
      itmcnt++;
    }
    outp.style.height=outheight;
    if (outp.childNodes.length > 0) {
      shadow.style.visibility = 'visible';
      shadow.style.top=findPosY(self.text)+'px';
      shadow.style.left=findPosX(self.text)+'px';
    } else {
      shadow.style.visibility = 'hidden';
    }
  }

  /*
   * Helper to set the color of a item
   */
  function setColor (_posi, _color, _forg) {
    outp.childNodes[_posi].style.background = _color;
    outp.childNodes[_posi].style.color = _forg;
  }

  /*
   * Respond to different keystrokes
   */
  function keyHandler(event) {
    if (event.charCode > 0) {
      key = event.charCode;
    } else {
      key=event.keyCode;
    }
    if ((key == 40) || (key == 9)) { //Key down / TAB
      if (posi >= 0) {
        setColor(posi, output.style.backgroundColor, output.style.color);
      }
      if ((outp.childNodes.length > 0) && (posi == outp.childNodes.length-1)) {
        posi=0;
        setColor(posi, 'blue', 'white');
      } else if ((outp.childNodes.length > 0) && (posi < outp.childNodes.length-1)) {
        setColor(++posi, 'blue', 'white');
      } else {
        return true;
      }
      return false;
    } else if (key == 38) { //Key up
      if (posi >= 0) {
        setColor(posi, output.style.backgroundColor, output.style.color);
      }
      if ((outp.childNodes.length > 0) && (posi > 0)) {
        if (posi >=1) {
          setColor(--posi, 'blue', 'white');
        }
      } else {
        posi=outp.childNodes.length-1;
        setColor(posi, 'blue', 'white');
      }
      return false;
    } else if (key == 27) { // Esc
      if (posi >= 0) {
        setColor(posi, output.style.backgroundColor, output.style.color);
      }
      posi=-1;
      self.text.focus();
      shadow.style.visibility = 'hidden';
      return false;
    } else if (key == 13) { // Enter
      if (posi > -1) {
        if (outp.childNodes[posi].id) {
          var tmpwrd=outp.childNodes[posi].id;
          getList(outp.childNodes[posi].firstChild.nodeValue);
          self.text.value = tmpwrd;
        }
        posi = -1;
        self.text.focus();
        shadow.style.visibility = 'hidden';
        if (self.onselect != null) {
          self.onselect();
        }
      }
      return false;
    }  else if (key == 8) { // Backspace
      getList(self.text.value.substr(0,self.text.value.length-1));
    } else {
      getList(self.text.value+String.fromCharCode(key));
    }
  }

  /*
   * What to do if a item is moused over [Change colors]
   */
  var mouseHandler=function(){
    for(var i=0;i < outp.childNodes.length;i++) {
      if (this.id == outp.childNodes[i].id) {
	posi=i;
        setColor (i, 'blue', 'white');
        continue;
      }
      setColor (i, output.style.backgroundColor, output.style.color);
    }
  }

  /*
   * On moue click hide the box and fill in the chosen item
   */	
  var mouseClick=function() {
    self.text.value = this.id;
    shadow.style.visibility = 'hidden';
    posi = -1;
    if (self.onselect != null) {
      self.onselect();
    }
  }
  this.text.onkeypress = keyHandler;

  this.close = function() {
    while (outp.hasChildNodes()) {
      noten=outp.firstChild;
      outp.removeChild(noten);
    }
    shadow.style.visibility='hidden';
    shadow.removeChild(outp);
    document.body.removeChild(shadow);
  }
}
