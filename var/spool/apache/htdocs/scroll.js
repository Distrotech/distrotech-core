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

function TableScroll(prefix) {
  var cells=new Array();
  var myself=this;
  var pos=0;
  var interval=setInterval(runscroll,2000);
 
  function arrsetup(start) {
    cnt=start-1;
    idx=cnt-start;
    do {
      cnt++;
      idx++;
      if ((document.getElementById(prefix+cnt) == null) && (start != 0)) {
        cnt=0;
      }
      cells[idx]=document.getElementById(prefix+cnt);

/*
      if (cells[idx] != null) {
        alert(idx+" "+cnt+" "+start+"\n"+cells[idx].innerHTML);
      }
*/
    } while ((cells[idx] != null) && ((cnt != start) || (idx == 0))) 
  }

  this.do_jump = function() {
    var lastdiv=cells.length-2;
    arrsetup(0);
    
    for(runcnt=pos;runcnt < lastdiv;runcnt++) {
      runscroll();
    }
  }

  this.do_scroll = function() {
    var tmpstr=cells[0].innerHTML;
    var lastdiv=cells.length-2;

    if (pos > lastdiv) {
      pos=0;
    }
    pos++;

    for(var key=0;key < lastdiv;key++){ 
      key2=key+1;
      cells[key].innerHTML=cells[key2].innerHTML;
    }
    cells[lastdiv].innerHTML=tmpstr;
  }
  function runscroll() {
    myself.do_scroll();
  }
  arrsetup(0);
}

/***********************************************
* Cross browser Marquee II- © Dynamic Drive (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit http://www.dynamicdrive.com/ for this script and 100s more.
***********************************************/

var marqueespeed=2

function scrollmarquee(cellname){
  cross_marquee=document.getElementById("cell"+cellname)
  actualheight=cross_marquee.offsetHeight
  if (parseInt(cross_marquee.style.top)>(actualheight*(-1)+8))
    cross_marquee.style.top=parseInt(cross_marquee.style.top)-marqueespeed+"px"
   else
    cross_marquee.style.top=parseInt(marqueeheight)+8+"px"
}

function initializemarquee(cellname){
  cross_marquee=document.getElementById("cell"+cellname)
  cross_marquee.style.top=0
  marqueeheight=document.getElementById("ccell"+cellname).offsetHeight
  actualheight=cross_marquee.offsetHeight
  if (marqueeheight > actualheight)
    return;
  if (window.opera || navigator.userAgent.indexOf("Netscape/7")!=-1){
    cross_marquee.style.height=marqueeheight+"px"
    cross_marquee.style.overflow="scroll"
    return
  }
  cmd='setInterval("scrollmarquee('+cellname+')",30)'
  setTimeout(cmd, 0)
}
