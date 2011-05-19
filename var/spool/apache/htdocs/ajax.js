/*
#    Copyright (C) 2009  <Gregory Hinton Nietsky>
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

/*
 *
 * Usage
 * This class allows the population of DIV's with the use of AJAX it can also post data from a form to a page and populate the DIV with the result of the POST
 * a second form can be set optionaly to keep the connection to the server open with a alternate POST and restart when either it or the original return data
 *
 * Methods
 *
 * Initilise a link between a forms submit method a div and a url a initial get will be made when the start method is invoked
 *
 * setupajax(divid,url,start,subform,cbform)
 *  divid = the id of the DIV to be populated
 *  url = the page to be POST/GET
 *  subform = when this form is submited the the run method is trigered
 *  cbform = when the div is loaded use this form to open a persistant connection for server push
 *
 * submit the form to the url placing the output in the divid this i a immeadiate action and start has no action
 *
 * senddata(divid,formname,url,clearblanket)
 *  divid = the id of the DIV to be populated
 *  formname = when this form is submited the the run method is trigered
 *  url = the page to be POST/GET
 *  clearblanket = set to false to not remove the blanket [popup]
 *
 * set the onsubmit action of the form to use AJAX to send the data to the url and return the results to the div
 *
 * setonsubmit(divid,formname,url)
 *  divid = the id of the DIV to be populated
 *  formname = when this form is submited the the run method is trigered
 *  url = the page to be POST/GET
 *
 * start()
 *  fires off GET to all the defined XMLHttpRequest objects above then runs the callback if it exists
 *
 */

var AJAX=new GregJAX('AJAX');

function GregJAX(name) {
  /*
   * Create a assosiotive array for the AJAX Requests
   */
  var ajaxhttp=new Array();

  /*
   * hasStarted the class can only be started once on load
   */
  var hasStarted=false;

  /*
   * Nested class that holds the request class and callback functions and methods
   */
  function AJAXRequest(divid,url,subformin,cbform) {
    if (divid != null) {
      this.ajax=new XMLHttpRequest();
      this.cbajax=new XMLHttpRequest();
    } else {
      return null;
    }

    this.divid=divid;

    this.url=url;
    this.subform=subformin;
    this.cbform=cbform;
    this.canstart=true;
    this.clearblanket=true;
    this.onsubmitset=false;

    var resultframe = document.createElement('iframe');
    resultframe.id=divid+'_resframe';
    resultframe.style.visibility = 'hidden';
    resultframe.style.height = '0px';
    resultframe.style.width = '0px';
    document.body.appendChild(resultframe);

    this.htmloutput = function() {
      var outputdiv=document.getElementById(divid);
      var blanket = document.getElementById('blanket');

      if (outputdiv != null) {
        outputdiv.innerHTML=this.ajax.responseText;
        this.onsubmitset=setajaxsubmit(divid);
        var scripts = outputdiv.getElementsByTagName('script');
        for (var i=0;i<scripts.length;i++) {
          eval(scripts[i].innerHTML);
        }
        if (blanket != null) {
	  if (ajaxhttp[divid].clearblanket)
  	    blanket.style.visibility = 'hidden';
          blanket.style.cursor='default';
        }
        outputdiv.style.cursor='default';
      }
    }

    this.cbhtmloutput = function() {
      var outputdiv=document.getElementById(divid);

      if (outputdiv != null) {
        outputdiv.innerHTML=this.cbajax.responseText;
        this.onsubmitset=setajaxsubmit(divid);
        var scripts = outputdiv.getElementsByTagName('script');
        for (var i=0;i<scripts.length;i++) {
          eval(scripts[i].innerHTML);
        }
        if (blanket != null) {
	  if (ajaxhttp[divid].clearblanket)
  	    blanket.style.visibility = 'hidden';
          blanket.style.cursor='default';
        }
        outputdiv.style.cursor='default';
      }
    }

    this.callback = function() {
      if ((cbform != null) && (url != null)) {
        cbrun(divid,cbform,url);
      }
    }

    this.onsubmit = function() {
      if ((this.subform != null) && (this.url != null)) {
        run(this.divid,this.subform,this.url);
        return false;
      } else {
        return true;
      }
    }
  }

  /*
   * We use a asossiative array to hold the AJAX Objects this is requiured and must be called ajaxhttp
   */
  this.setupajax = function(divid,url,subform,cbform) {
    if (ajaxhttp[divid] == null) {
      ajaxhttp[divid]=new AJAXRequest(divid,url,subform,cbform);
    } else {
      ajaxhttp[divid].url=url;
      ajaxhttp[divid].subform=subform;
      ajaxhttp[divid].cbform=cbform;
      ajaxhttp[divid].onsubmitset=setajaxsubmit(divid);
//      ajaxhttp[divid].callback();
    }
  }

  /*
   * Ok this is the swiss army knife method ...
   * Will post data from a form to any url and place the results in the div
   */
  this.senddata = function(divid,formname, url, clearblanket) {
    /*
     * if there is no requestor for this DIV set it up
     */
    if (ajaxhttp[divid] == null) {
      ajaxhttp[divid]=new AJAXRequest(divid);
    }
    if (ajaxhttp[divid] != null) {
      if (formname != null) {
        ajaxhttp[divid].subform=formname;
      }
      if (ajaxhttp[divid].canstart) {
        ajaxhttp[divid].canstart=false;
      }
      if (clearblanket != null) {
         ajaxhttp[divid].clearblanket = clearblanket;
      }
      run(divid,formname,url);
    }
  }

  /*
   * Ok this helper sets the onsubmit for the form in question if there is no requestor it creates one
   */
  this.setonsubmit = function(divid,formname, url) {
    /*
     * if there is no requestor for this DIV set it up
     */
    if (ajaxhttp[divid] == null) {
      ajaxhttp[divid]=new AJAXRequest(divid);
    }
    if (ajaxhttp[divid] != null) {
      ajaxhttp[divid].subform=formname;
      ajaxhttp[divid].url=url;
      ajaxhttp[divid].onsubmitset=setajaxsubmit(divid);
      if (ajaxhttp[divid].canstart) {
        ajaxhttp[divid].canstart=false;
      }
    }
  }

  /*
   * Function to start up all defined Requests this is done sync to stop multiple possible auth popups
   * need a timeout function perhaps ??
   */
  this.start = function() {
    if (!hasStarted) {
      for(key in ajaxhttp) {
	if (ajaxhttp[key].canstart) {
          ajaxhttp[key].ajax.open("GET",ajaxhttp[key].url,false);
          ajaxhttp[key].ajax.send(null);
          ajaxhttp[key].htmloutput();
        }
      }
      hasStarted=true;
      for(key in ajaxhttp) {
	ajaxhttp[key].callback();
      }
    }
  }

  /*
   * This opens and sends the request to be processed ASYNC by the stateChanged function
   */
  this.onsubmit = function(divid) {
    ajaxhttp[divid].onsubmit();
    return false;
  }

  this.setuponload = function(divid,olfunc) {
    ajaxhttp[divid].onload=olfunc;
  }

  function run(divid,formdata,url) {
    if (ajaxhttp[divid].ajax.readyState > 0) {
      ajaxhttp[divid].ajax.abort();
    }
    ajaxhttp[divid].ajax.onreadystatechange=stateChanged;
    if (!submitform(divid,formdata,url)) {
      ajaxhttp[divid].ajax.open("GET",url,true);
      ajaxhttp[divid].ajax.send();
    }
  }

  function cbrun(divid,formdata,url) {
    if (ajaxhttp[divid].cbajax.readyState > 0) {
      ajaxhttp[divid].cbajax.abort();
    }
    ajaxhttp[divid].cbajax.onreadystatechange=stateChanged;
    if (!submitform(divid,formdata,url)) {
      ajaxhttp[divid].cbajax.open("GET",url,true);
      ajaxhttp[divid].cbajax.send();
    }
  }

  /*
   * When the AJAX request state changes find the Object affected and process the data loading it into the DIV
   */
  function stateChanged() {
    for(key in ajaxhttp) {
      if ((ajaxhttp[key].ajax.readyState == 4) && (ajaxhttp[key].ajax.status == 200) && (ajaxhttp[key].ajax.responseText.length > 0)) {
        ajaxhttp[key].htmloutput();
        ajaxhttp[key].ajax.abort();
        for(fcheck in ajaxhttp) {
          if (!ajaxhttp[fcheck].onsubmitset) {
            ajaxhttp[fcheck].onsubmitset=setajaxsubmit(fcheck);
          }
        }
        if (ajaxhttp[key].onload != null) {
  	  ajaxhttp[key].onload();
        }
      } else if ((ajaxhttp[key].ajax.readyState == 4) && (ajaxhttp[key].ajax.status >= 0)) {
        ajaxhttp[key].ajax.abort();
      } else if ((ajaxhttp[key].cbajax.readyState == 4) && (ajaxhttp[key].cbajax.status == 200) && (ajaxhttp[key].cbajax.responseText.length > 0)) {
        ajaxhttp[key].cbhtmloutput();
        ajaxhttp[key].cbajax.abort();
	ajaxhttp[key].callback();
        for(fcheck in ajaxhttp) {
          if (!ajaxhttp[fcheck].onsubmitset) {
            ajaxhttp[fcheck].onsubmitset=setajaxsubmit(fcheck);
          }
        }
      } else if ((ajaxhttp[key].cbajax.readyState == 4) && (ajaxhttp[key].cbajax.status >= 0)) {
        ajaxhttp[key].cbajax.abort();
	ajaxhttp[key].callback();
      }
    }
  }

  /*
   *URL encode function to URL encode the name value pairs harvested from the form
   */
  function urlencode(input) {
    var encodedInputString=input.replace("+", "%2B");
/*
    encodedInputString=encodedInputString.replace(" ", "+");
    encodedInputString=encodedInputString.replace("%2B", "+");
    encodedInputString=encodedInputString.replace("/", "%2F");
*/
    encodedInputString=escape(encodedInputString);
    return encodedInputString;
  }

  /*
   * Create the post string to be sent via ajax from a named form
   */
  function submitform(ajax,frm2sub,myurl) {
    if ((frm2sub == null) || (frm2sub == '')) {
      return false;
    }
    for (dform=0;dform<document.forms.length;dform++) {
      if (document.forms[dform].name == frm2sub) {
        break;  
      }
    }

    if (document.forms[dform].name != frm2sub) {
      return false;
    }

    var dmpfrmdat=document.forms[dform].elements;

    if (document.forms[dform].method == '') {
      document.forms[dform].method='get';
    }

    var out="?";
    for(felm=0;felm<dmpfrmdat.length;felm++) {
      if (((dmpfrmdat[felm].type == "radio") && (!dmpfrmdat[felm].checked)) ||
          ((dmpfrmdat[felm].type == "checkbox") && (!dmpfrmdat[felm].checked)) ||
          ((dmpfrmdat[felm].type == "textarea") && (dmpfrmdat[felm].value == ''))) {
        continue;
      }

      if (document.forms[dform].method.toLowerCase() != 'post') {
        if (dmpfrmdat[felm].name != null) {
          out=out+urlencode(dmpfrmdat[felm].name)+'='+encodeURIComponent(dmpfrmdat[felm].value)+'&';
        }
      } else {
        if ((dmpfrmdat[felm].name != null) && (dmpfrmdat[felm].name != '')) {
          out=out+escape(dmpfrmdat[felm].name)+'='+encodeURIComponent(dmpfrmdat[felm].value)+'&';
        }
      }
    }

    if ((ajaxhttp[ajax].subform != null) && (ajaxhttp[ajax].subform != '') && (frm2sub == ajaxhttp[ajax].subform)) {
      fajax=ajaxhttp[ajax].ajax;
    } else if ((ajaxhttp[ajax].cbform != null) && (ajaxhttp[ajax].cbform != '') && (frm2sub == ajaxhttp[ajax].cbform)) {
      fajax=ajaxhttp[ajax].cbajax;
    } else {
      return false;
    }

    if (document.forms[dform].method.toLowerCase() == 'post') {
      out=out.substr(1,out.length-1);
      fajax.open(document.forms[dform].method.toLowerCase(),myurl,true);
      fajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      fajax.setRequestHeader("Content-length", out.length);
      fajax.setRequestHeader("Connection", "close");
      fajax.send(out);
    } else{
      out=out.substr(0,out.length-1);
      out=myurl+out;
      fajax.open(document.forms[dform].method.toLowerCase(),out,true);
      fajax.send(null);
    }
    return true;
  }

  /*
   * Sets a callback function for a forms on submit method
   */
  function setajaxsubmit(divid) {
    if ((name == null) || (divid == null) || (ajaxhttp[divid].subform == null) || (ajaxhttp[divid].subform == '')) {
      return false;
    }
    for (dform=0;dform<document.forms.length;dform++) {
      if (document.forms[dform].name == ajaxhttp[divid].subform) {
        break;  
      }
    }
    if ((document.forms[dform] != null) && (document.forms[dform].name == ajaxhttp[divid].subform)) {
      if ((document.forms[dform].onsubmit == null) || (document.forms[dform].onsubmit == '')) {
        document.forms[dform].onsubmit = new Function("return "+name+".onsubmit('"+divid+"')");
      }
      ajaxhttp[divid].onsubmitset=true;
      return true;
    } else {
      return false;
    }
  }
}
