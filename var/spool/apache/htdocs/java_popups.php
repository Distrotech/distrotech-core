<%

if (isset($_GET['sesname'])) {
  ob_start('ob_gzhandler');
  include "/var/spool/apache/htdocs/session.inc";
  session_name($_GET['sesname']);
  session_set_cookie_params(180);
  session_start();
}

if (is_file($_SESSION['style'] . "/style.css")) {
  include $_SESSION['style'] . "/style.css";
} else {
  include "style.css";
}
%>
var ajaxcaller=new XMLHttpRequest();

function openexten(classi) {
  document.loadpage.showmenu.value='vstatus';
  document.loadpage.classi.value=classi;
  document.loadpage.disppage.value='cdr/anaexten.php';
  document.loadpage.print.value='';
  ajaxloadpage('vstatus');
}
function openapage(pageo,classi) {
  document.loadpage.showmenu.value='asetup';
  document.loadpage.classi.value=classi;
  document.loadpage.disppage.value=pageo;
  document.loadpage.print.value='';
  ajaxloadpage('asetup');
//  document.loadpage.submit();
}
function openupage(pageo,menuo) {
  document.loadpage.showmenu.value=menuo;
  document.loadpage.disppage.value=pageo;
  document.loadpage.utype.value='';
  document.loadpage.print.value='';
  document.loadpage.classi.value='<%print $_SESSION['userid'];%>';
  ajaxloadpage(menuo);
}
function openpage(pageo,menuo) {
  if ((pageo == null) || (pageo == "")) {
    setactivemenu(menuo);
  } else {
    document.loadpage.showmenu.value=menuo;
    document.loadpage.disppage.value=pageo;
    document.loadpage.print.value='';
    ajaxloadpage(menuo);
  }
}
function openticket(classi,mmap) {
  document.loadpage.showmenu.value="tickdep";
  document.loadpage.disppage.value="auth/tickrep.php";
  document.loadpage.classi.value=classi;
  document.loadpage.mmap.value=mmap;
  document.loadpage.print.value='';
  ajaxloadpage('tickdep');
}

function ticketwin(ticketnum) {
//leave
  newwin=window.open('','tickwin','menubar=yes,toolbar=no,scrolling=yes,scrollbars=yes,resizable=yes,top=0,left=0');
  document.tickman.target='tickwin';
  document.tickman.ticket.value=ticketnum;
  document.tickman.submit();
  newwin.focus();
}

function popupwin(url,fname) {
//leave
  window.open('<%print $_SESSION['server'];%>'+url,fname,'menubar=no,toolbar=no,scrolling=yes,scrollbars=yes,resizable=yes,top=0,left=0,width='+screen.width+',height='+screen.height);
}
function opencubit() {
  popupwin('cubitpro/doc-index.php?','theframe');
}
function opencrm() {
  popupwin('crm','crmframe');
}      
function openmail() {
  popupwin('horde','mailframe');
}
function opensogo() {
  popupwin('SOGo','mailframe');
}
function opencshop() {
  popupwin('cshop','cshopframe');
}
function openccadmin() {
  popupwin('ccadmin','cshopframe');
}
function openbb() {
  popupwin('phpBB2','bbframe');
}
function opendbadmin() {
  popupwin('dbadmin','dbadmin');
}
function openualbum(euser) {
  popupwin('photo/'+euser+'_album.html','_blank');
}
function openvoipconf() {
  exten=prompt("Enter Extension To Manage","");
  popup('<%print $_SESSION['server'];%>reception/vladmin.php?style=<%print $_SESSION['style'];%>&exten='+exten,370,700);
}
function openlsysconf() {
  exten=prompt("Enter Extension To Register","");
  popup('<%print $_SESSION['server'];%>reception/lsysreg.php?style=<%print $_SESSION['style'];%>&exten='+exten,250,700);
}

function opengsm(grouter,gchannel) {
  popup(null,300,500);
  document.gsmform.router.value=grouter;
  document.gsmform.channel.value=gchannel;
  AJAX.senddata('popUpDivContent','gsmform','index.php', false);
}
function openirate(countrycode,subcode) { 
  popup(null,150,500);
  document.irateform.countrycode.value=countrycode;
  document.irateform.subcode.value=subcode;
  AJAX.senddata('popUpDivContent','irateform','index.php', false);
}

function secpopup(disppage,popdata,height,width) {
  popup(null,height,width);
  document.loadpage.nomenu.value='1';
  document.loadpage.disppage.value=disppage;
  document.loadpage.mmap.value=popdata;
  AJAX.senddata('popUpDivContent','loadpage','index.php', false);
  document.loadpage.nomenu.value='';
  document.loadpage.target='';
}
function secpop(winname,winprop,disppage,popdata) {
//leave
  newwin=window.open('',winname,winprop);
  document.loadpage.nomenu.value='1';
  document.loadpage.disppage.value=disppage;
  document.loadpage.mmap.value=popdata;
  document.loadpage.target=winname;
  document.loadpage.submit();
  newwin.focus();
  document.loadpage.nomenu.value='';
  document.loadpage.target='';
}
function openvoip() {
//leave
  window.open('<%print $_SESSION['server'];%>reception','_blank','menubar=no,toolbar=no,scrolling=yes,resizable=no,top=0,left=0,width=900,height=660');
}
function openc2c() {
  popup('<%print $_SESSION['server'];%>reception/c2c.php?style=<%print $_SESSION['style'];%>',130,600);
}
function openagentapp() {
  popup('<%print $_SESSION['server'];%>reception/agentapp.php?style=<%print $_SESSION['style'];%>',150,300);
}
function opengtalk() {
  popup('<%print $_SESSION['server'];%>reception/gcall.php?style=<%print $_SESSION['style'];%>',130,600);
}
function openpsfax() {
  popup('<%print $_SESSION['server'];%>reception/psfax.php?style=<%print $_SESSION['style'];%>',130,600);
}
function openman(page) {
//leave
  window.open(page,'ManPage');
}
function openphone(page) {
//leave
  window.open(page);
}
function openstatus(page) {
//leave
  window.open('<%print $_SESSION['server'];%>'+page,'StatusPage');
}
function openns() {
  popup('<%print $_SESSION['server'];%>ns.html',650,900);
}
function openmclass(menu,classi) {
  document.loadpage.classi.value=classi;
  openpage('ldap/mclass.php',menu);
}
function openmmap(menu,mmapi) {
  document.loadpage.mmap.value=mmapi;
  openpage('ldap/mmaps.php',menu);
}
function voipcsvup(menu,mmap) {
  document.loadpage.mmap.value=mmap;
  openpage('cdr/csvupload.php',menu);
}
function voipcsvdown(classi) {
  document.loadpage.classi.value=classi;
  openpage('cdr/csvdownload.php','vcsvdown');
}
function openagroup(classi) {
  document.loadpage.classi.value=classi;
  openpage('ldap/agroup.php','groups');
}
function openvgraph(classi) {
  document.loadpage.classi.value=classi;
  openpage('rrdgraph/showperv.php','vstatus');
}
function phonesetup(mmap) {
  document.loadpage.mmap.value=mmap;
  openpage('cdr/phonesetup.php','phones');
}
function usersetup(classi) {
  document.loadpage.classi.value=classi;
  document.loadpage.utype.value=classi;
  if (classi != 'snom') {
    openpage('ldap/ennav.php','users');
  } else {
    openpage('ldap/ennav.php','snomc');
  }
}
function openvirtrealm(utype) {
  document.loadpage.classi.value='system';
  document.loadpage.utype.value=utype;
  openpage('ldap/ennav.php','virtdom');
}
function edituser(classi,utype) {
  document.loadpage.classi.value=classi;
  document.loadpage.utype.value=utype;
  if (utype == 'trust') {
    openpage('ldap/trustinfo.php','users');
  } else if (utype == 'snom') {
    openpage('ldap/snominfo.php','snomc');
  } else if ((utype == 'system') || (utype == 'pdc')) {
    openpage('ldap/userinfo.php','asetup');
  } else if (utype == 'mserver') {
    openpage('ldap/mserverinfo.php','users');
  } else {
    openpage('ldap/userinfo.php','asetup');
  }
}
function openidata(mmap) {
  if (mmap == 'email') {
    document.loadpage.mmap.value='mailLocalAddress';
  } else {
    document.loadpage.mmap.value='hostedSite';
  }
  document.loadpage.classi.value='<%print $_SESSION['userid'];%>';
  openpage('auth/idata.php','setup');
}
function openaidata(mmap,classi) {
  if (mmap == 'email') {
    document.loadpage.mmap.value='mailLocalAddress';
  } else {
    document.loadpage.mmap.value='hostedSite';
  }
  document.loadpage.classi.value=classi;  
  openpage('auth/idata.php','asetup');
}
function addnewuser() {
  document.useradd.showmenu.value='asetup';
  document.useradd.classi.value=document.useradd.uid.value;
  if (document.useradd.givenName.value == '') {
    alert('The First Name Cannot Be Left Empty');
  } else if (document.useradd.uid.value == '') {
    alert('The Username Cannot Be Left Empty');
  } else if (document.useradd.pass1.value == '') {
    alert('Password Cannot Be Left Empty!!!');
  } else if ((document.useradd.pass1.value != document.useradd.pass2.value) || (document.useradd.pass1.value == '')){
    alert('Passwords Do Not Match!!!');
  } else {
    document.useradd.adduser.value='yes';
    ajaxsubmit('useradd');
//    document.useradd.submit();
  }
}
function addnewradrealm() {
  if (document.radmod.newrep.value != '') {
    document.radmod.newrepval.value=prompt("Enter Value For Reply Item "+document.radmod.newrep.value);
  }
  if (document.radmod.newcheck.value != '') {
    document.radmod.newcheckval.value=prompt("Enter Value For Check Item "+document.radmod.newcheck.value);
  }
  ajaxsubmit('radmod');
//  document.radmod.submit();
}
function voipedit(classi) {
  document.loadpage.classi.value=classi;
  openpage('cshop/getbooth.php','users');
}
function addagent(classi) {
  document.loadpage.classi.value=classi;
  openpage('cshop/addagent.php','users');
}
function voipacedit(number) {
  document.editac.number.value=number;
  ajaxsubmit('editac');
//  document.editac.submit();
}
function voipddiedit(number,ddi) {
  document.editac.newddi.value=prompt("Number To Dial : ",ddi);
  if (document.editac.newddi.value != null) {
    document.editac.number.value=number;
    AJAX.senddata('edit_'+number,'editac','/cshop/celledit.php')
  }
}
function agentonoff(channel,offon) {
  if (offon == 't') {
    document.queuemod.agentlogoff.value=channel;
  } else if (offon == 'f') {
    document.queuemod.agentlogon.value=channel;
  } else if (offon == 'p') {
    document.queuemod.agentpause.value=channel;
  }
  ajaxsubmit('queuemod');
}
function ccagentonoff(channel,offon) {
  if (offon == 't') {
    document.agentadmin.agentlogoff.value=channel;
  } else if (offon == 'f') {
    document.agentadmin.agentlogon.value=channel;
  }
  ajaxsubmit('agentadmin');
}

function deleteagent(channel) {
  if (confirm("Are You Sure You Want To Delete Agent "+channel)) {
    document.queuemod.delagent.value=channel;
    ajaxsubmit('queuemod');
  }
}
function deleteexten() {
  if (document.extenform.exten.value == "") {
    document.extenform.exten.value=document.extenform.prefix.value+document.extenform.cno.value
  }
  if (confirm("Are You Sure You Want To Delete "+document.extenform.exten.value)) {
    document.extenform.delext.value="1";
    ajaxsubmit('extenform');
  }
}
function agentweight(channel) {
  document.queuemod.agentwei.value=prompt("Enter New Weight For Agent : "+channel);
  if (document.queuemod.agentwei.value > 0) {
    document.queuemod.agentchan.value=channel;
    ajaxsubmit('queuemod');
  }
}
function agentignorebusy(channel) {
  if (confirm("Change Ignore Busy Status For "+channel)) {
    document.queuemod.agentignore.value=channel;
    ajaxsubmit('queuemod');
  }
}
function openrrdgraph(iface,maxlim) {
  document.openrrd.name.value=iface;
  document.openrrd.max.value=maxlim;
  document.openrrd.submit();
}
function openraddata(username,year,month,day) {
  if (day == 0) {
    document.openrdata.disppage.value="radius/radday.php";
  } else {
    document.openrdata.disppage.value="radius/radinfo.php";
  }
  document.openrdata.username.value=username;
  document.openrdata.year.value=year;
  document.openrdata.month.value=month;
  document.openrdata.day.value=day;
  document.openrdata.submit();
}
function openreception() {
  document.loadpage.mmap.value='799';
  openpage('cdr/agents.php','voip');
}
function snomkeywin(exten,type) {
  if (type == "kp") {
    yhei=347;
    xwid=259;
  } else {
    yhei=559;
    xwid=481;
  }
//leave
  window.open('<%print $_SESSION['server'];%>cdr/tempedit.php?exten='+exten+'&type='+type,'snomkeywin'+type,'menubar=yes,toolbar=yes,scrolling=no,resizable=no,scrollbars=no,top=0,left=0,width='+xwid+',height='+yhei);
}
function snomkeyview(exten,type) {
  if (type == "kp") {
    yhei=337;
    xwid=259;
  } else {
    yhei=549;
    xwid=481;
  }
//leave
  window.open('<%print $_SESSION['server'];%>images/snom-'+exten+'-'+type+'.png','snomkeyview'+type,'menubar=yes,toolbar=yes,scrolling=no,resizable=yes,scrollbars=no,top=0,left=0,width='+xwid+',height='+yhei);
}
function opencdrrep(disp,exten) {
  document.getrepform.disp.value=disp;
  document.getrepform.exten.value=exten;
  ajaxsubmit('getrepform');
}
function opencdrrep2(exten,filter) {
//leave
  newwin=window.open('','repwin','menubar=yes,toolbar=no,scrolling=yes,scrollbars=yes,resizable=yes,top=0,left=0');
  document.getrep.target='repwin';
  document.getrep.filter.value=filter;
  document.getrep.exten.value=exten;
  document.getrep.submit();
  newwin.focus();
}
function getextenrep(exten,pclass,otrunk) {
//leave
  newwin=window.open('','extenlist','menubar=yes,toolbar=no,scrolling=yes,scrollbars=yes,resizable=yes,top=0,left=0');
  document.extenrep.target='extenlist';
  document.extenrep.exten.value=exten;
  document.extenrep.pclass.value=pclass;
  document.extenrep.trunk.value=otrunk;
  document.extenrep.submit();
  newwin.focus();
}
function openextenedit(exten) {
  secpopup('cdr/mkuser.php',exten,600,800);
}
function testscript(script,htmlscript) {
  tmpact=document.loadpage.action;
  document.loadpage.action='/ccadmin/xmledit.php';
  secpop('xmlscript','menubar=no,toolbar=no,scrollbars=yes,scrolling=yes,resizable=no,top=0,left=0,width=800,height=700','/ccadmin/xmledit.php',script);
  document.loadpage.action=tmpact;
}
function calllog(callid) {
  secpopup('cdr/calllist.php',callid,470,700);
}
function openvradconf() {
  document.loadpage.utype.value=document.vzone.group.value
  document.loadpage.classi.value='';
  secpopup('auth/radius.php','',500,750);
}
function openccagent() {
  tmpact=document.loadpage.action;
  document.loadpage.action='/';
  secpop('ccagent','menubar=no,toolbar=no,scrolling=yes,resizable=no,top=0,left=0,width=800,height=700','ccagent/agent.php','');
  document.loadpage.action=tmpact;
}
function openacdpop(acdpoptype) {
//leave
  newwin=window.open('','acdpopup','menubar=yes,toolbar=no,scrolling=yes,scrollbars=yes,resizable=yes,top=0,left=0');
  document.pform.nomenu.value='1';
  document.pform.poptype.value=acdpoptype;
  document.pform.target='acdpopup';
  tmpdisp=document.pform.disppage.value;
  document.pform.disppage.value='cdr/acdpopup.php';
  document.pform.submit();
  newwin.focus();
  document.pform.nomenu.value='';
  document.pform.target='';
  document.pform.disppage=tmpdisp;
}

function blockisdn(iport,iblock) {
  document.isdninf.iport.value=iport;
  document.isdninf.iblock.value=iblock;
  ajaxsubmit('isdninf');
//  document.isdninf.submit();
}
function softhang(astchan) {
  document.hangupchan.hangup.value=astchan;
  ajaxsubmit('hangupchan');
//  document.hangupchan.submit();
}
function resetisdn(iport) {
  document.isdninf.iport.value=iport;
  document.isdninf.iblock.value="reset";
  ajaxsubmit('isdninf');
//  document.isdninf.submit();
}
function openaliasedit(userid) {
  tmpclassi=document.loadpage.classi.value;
  document.loadpage.classi.value=userid;
  secpopup('auth/idata.php','mailLocalAddress',350,750);
  document.loadpage.classi.value=tmpclassi;
}
function adjchan(chanup,update) {
  if (update == "channel") {
    chandata=prompt("Adjust Channel(s)","");
  } else {
    chandata=prompt("Enter New Gain","");
  }
  if (chandata != '') {
    document.zapchan.chandata.value=chandata;
    document.zapchan.chanup.value=chanup;
    document.zapchan.update.value=update;
    ajaxsubmit('zapchan');
  }
}
function astdbupdate(dbfam,dbkey,dbtype) {
  document.sipinf.dbfam.value=dbfam;
  document.sipinf.dbkey.value=dbkey;
  if (dbtype == "0") {
    dbval=1;
  } else if (dbtype == "1") {
    dbval=0;
  } else {
    dbval=prompt(dbtype,"");
  }
  if (dbval != null) {
    document.sipinf.dbval.value=dbval;
    ajaxsubmit('sipinf');
  }
}
function gsmchanup(router,channel,dbtype,dbrow) {
  document.gsmchan.router.value=router;
  document.gsmchan.channel.value=channel;
  document.gsmchan.dbrow.value=dbrow;
  if (dbtype == "0") {
    dbval='t';
  } else if (dbtype == "1") {
    dbval='f';
  } else {
    dbval=prompt(dbtype,"");
  }
  if (dbval != null) {
    document.gsmchan.dbval.value=dbval;
    ajaxsubmit('gsmchan');
//    document.gsmchan.submit();
  }
}
function openpbxstate() {
//leave
  window.open('<%print $_SESSION['server'];%>reception/astate.php?style=<%print $_SESSION['style']%>','dndstate','menubar=no,toolbar=no,scrolling=yes,resizable=no,top=0,left=0,width=750,height=500');
}
function printpage(pform) {
//leave
  pwin=window.open('','printwin','menubar=yes,toolbar=no,scrolling=yes,scrollbars=yes,resizable=yes,top=0,left=0');
  pform.print.value='1';
  pform.target='printwin';
  pform.submit();
  pwin.focus();
  pform.target='';
  pform.print.value='';
}
function csvpage(pform) {
  pform.print.value='2';
  pform.action="/auth/" + pform.disppage.value.substring(pform.disppage.value.indexOf('/')+1,pform.disppage.value.lastIndexOf('.')) + ".csv";
  pform.submit();
  pform.target='';
  pform.print.value='';
}
function mbresize(){
  var hframe = document.getElementById('header');
  var windowheight = window.innerHeight;
  if (hframe != null) {
    windowheight=windowheight-hframe.offsetHeight;
  }

  var dwidth=window.innerWidth - 130;
  document.getElementById('main-body').style.width = dwidth + "px";
  document.getElementById('menu-bar').style.width = window.innerWidth + 'px';

  document.getElementById('logo').style.width = dwidth - 20 +"px";

  windowheight=windowheight-document.getElementById('menu-bar').offsetHeight-document.getElementById('logo').offsetHeight-50;
  document.getElementById('main-body').style.height = windowheight + "px";
  setactivemenu('<%print $_SESSION['showmenu'];%>');
}
function showdiv(divid,divform) {
  document.getElementById(divform.curdiv.value).style.visibility='hidden';
  document.getElementById(divform.curdiv.value+'_but').style.backgroundColor='<%print $menubg1;%>';
  document.getElementById(divform.curdiv.value+'_but').style.color='<%print $menufg1;%>';
  divform.curdiv.value=divid;
  document.getElementById(divid).style.visibility='visible';
  document.getElementById(divid+'_but').style.backgroundColor='<%print $menubg2;%>';;
  document.getElementById(divid+'_but').style.color='<%print $menufg2;%>';;
}
function editivrtime(ivrindex,currange) {
  timerange=prompt("Enter New Open Time Range (HH:MM-HH:MM).\nFor Public Holidays 00:00-24:00 Is Closed.\nFor Normal Days 00:00-24:00 Is Open.",currange);
  if ((timerange != '') && (timerange != currange) && (timerange != null)){
    document.officehours.index.value=ivrindex;
    document.officehours.timerange.value=timerange;
    ajaxsubmit('officehours');
//    document.officehours.submit();
  }  
}
function atapopupwin(ipaddr,pserver) {
//leave
  window.open('http://' + ipaddr + '/admin/resync?http://'+pserver+'/init-<%print urlencode("\$");%>MA.cfg','linksys','menubar=no,toolbar=no,scrolling=yes,scrollbars=yes,resizable=yes,top=0,left=0,width='+screen.width+',height='+screen.height);
}
function pushconf(ipaddrin) {
  ipaddr=prompt("Enter The Devices IP Address.\nThe Address Selected Is The Last Known Address.",ipaddrin);
  if (ipaddr) {
    atapopupwin(ipaddr);
  }
}
function delexten(etodel,enum) {
  if(confirm("Are You Sure You Want To Remove This Extension Off This Device:\n"+etodel)) {
    document.atapush.edelete.value=enum;
    document.atapush.submit();
  }
}
function vboxlogoff() {
  document.vboxlogout.submit();
}
function getrsakey(auser) {
  ppass=prompt("Enter Key Pass Phrase");
  if (ppass) {
    tmpclassi=document.loadpage.classi.value;
    document.loadpage.action="/cert/"+auser+".key";
    document.loadpage.classi.value=ppass;
    document.loadpage.submit();
    document.loadpage.action="/auth";
    document.loadpage.print.value='';
    document.loadpage.classi.value=tmpclassi;
  }
}
function getovpnconf(auser) {
  ppass=prompt("Enter Key Pass Phrase");
  if (ppass) {
    tmpclassi=document.loadpage.classi.value;
    document.loadpage.action="/openvpn/"+auser+".zip";
    document.loadpage.classi.value=ppass;
    document.loadpage.submit();
    document.loadpage.action="/auth";
    document.loadpage.print.value='';
    document.loadpage.classi.value=tmpclassi;
  }
}

function urlencode(input) {
  var encodedInputString=input.replace("+", "%2B");
  encodedInputString=encodedInputString.replace(" ", "+");
  encodedInputString=encodedInputString.replace("/", "%2F");
  encodedInputString=escape(encodedInputString);
  return encodedInputString;
}

function dumpform(frm2dmp) {
  for (dform=0;dform<document.forms.length;dform++) {
    if (document.forms[dform].name == frm2dmp) {
      break;
    }
  }
  var dmpfrmdat=document.forms[dform].elements;
  var out="?";

  for(felm=0;felm<dmpfrmdat.length;felm++) {
    if (((dmpfrmdat[felm].type == "radio") && (!dmpfrmdat[felm].checked)) ||
        ((dmpfrmdat[felm].type == "checkbox") && (!dmpfrmdat[felm].checked)) ||
        ((dmpfrmdat[felm].type == "text") && (dmpfrmdat[felm].value == '')) ||
        ((dmpfrmdat[felm].type == "textarea") && (dmpfrmdat[felm].value == ''))) {
      continue;
    }
    if ((dmpfrmdat[felm].name != null) && (dmpfrmdat[felm].name != "")) {
      out=out+urlencode(dmpfrmdat[felm].name)+'='+urlencode(dmpfrmdat[felm].value)+'&';
    } else {
      out=out+urlencode(dmpfrmdat[felm].value)+'=&';
    }
  }
  out=out.substr(0,out.length-1);
  alert(out);
}
/**
 * Function : dump()
 * Arguments: The data - array,hash(associative array),object
 *    The level - OPTIONAL
 * Returns  : The textual representation of the array.
 * This function was inspired by the print_r function of PHP.
 * This will accept some data as the argument and return a
 * text that will be a more readable version of the
 * array/hash/object that is given.
 * Docs: http://www.openjs.com/scripts/others/dump_function_php_print_r.php
 */
function dump(arr,level) {
	var dumped_text = "";
	if(!level) level = 0;
	
	//The padding given at the beginning of the line.
	var level_padding = "";
	for(var j=0;j<level+1;j++) level_padding += "    ";
	
	if(typeof(arr) == 'object') { //Array/Hashes/Objects 
		for(var item in arr) {
			var value = arr[item];
			
			if(typeof(value) == 'object') { //If it is an array,
				dumped_text += level_padding + "'" + item + "' ...\n";
				dumped_text += dump(value,level+1);
			} else {
				dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
			}
		}
	} else { //Stings/Chars/Numbers etc.
		dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
	}
	return dumped_text;
}

function setactivemenu(vmenu) {
	if ((activemenu != vmenu) && (activemenu != '')){
          document.getElementById('main-body').innerHTML='';
        }
        if (menu_list['main_menu'] == null) {
          menu_list['main_menu']=new menu(menu_items_list['main_menu'],menu_horiz);
        }
        if ((vmenu != null) && (menu_items_list[vmenu+'_menu'] != null) && (vmenu != "") && (menu_list[vmenu+'_menu'] == null)) {
          menu_list[vmenu+'_menu']=new menu(menu_items_list[vmenu+'_menu'],menu_vert);
        }
	if (menu_list[vmenu+'_menu'] != null) {
          document.getElementById('menu-bar').innerHTML=menu_list['main_menu'].mframe.contentDocument.body.innerHTML+menu_list[vmenu+'_menu'].mframe.contentDocument.body.innerHTML;
          menu_list['main_menu'].show();
          menu_list[vmenu+'_menu'].show();
          activemenu=vmenu;
	} else if (activemenu != vmenu)  {
          document.getElementById('menu-bar').innerHTML=menu_list['main_menu'].mframe.contentDocument.body.innerHTML;
          menu_list['main_menu'].show();
          activemenu='';
        }
}

function getformbyname(formname) {
  for (dform=0;dform<document.forms.length;dform++) {
    if (document.forms[dform].name == formname) {
      break;
    }
  }
  if (document.forms[dform].name != formname) {
    return null;
  } else {
    return document.forms[dform];
  }
}

function setaddtoform(formname,ename,etype,ivalue) {
  var formobj=getformbyname(formname);

  if (formobj == null) {
    return false;
  }

  for(felm=0;felm < formobj.elements.length;felm++) {
    if (formobj.elements[felm].name == ename) {
      break;
    }
  }

  if ((formobj.elements[felm] == null) || (formobj.elements[felm].name != ename)) {
    var newformi = document.createElement('input');
    newformi.type=etype;
    newformi.name=ename;
    newformi.value=ivalue;
    formobj.appendChild(newformi);
  } else if ((formobj.elements[felm] != null) || (formobj.elements[felm].name == ename)) {
    formobj.elements[felm].value=ivalue;
  }
}

function ajaxloadpage(showmenu) {
  ajaxsubmit('loadpage');
  document.loadpage.mmap.value='';
  if (showmenu != activemenu) {
    setactivemenu(showmenu);
  }
}

function ajaxsubmit(formname) {
  if ((formname != null) && (formname != '')) {
    acbox = document.getElementById('acshadow');
    if (acbox != null) {
      while (acbox.hasChildNodes()) {
        while (acbox.firstChild.hasChildNodes()) {
          noten=acbox.firstChild.firstChild;
          acbox.firstChild.removeChild(noten);
        }
        noten=acbox.firstChild;
        acbox.removeChild(noten);
      }
      acbox.style.visibility='hidden';
      document.body.removeChild(acbox);
    }
    setaddtoform(formname,'ajax','hidden','1');
    blanket=document.getElementById('blanket');
    if (blanket != null) {
      blanket.style.visibility='visible';
      blanket.style.cursor='wait';
    } else {
      document.getElementById('main-body').style.cursor='wait';
    }
    var popup = document.getElementById('popUpDiv');
    if (popup != null) {
      if (popup.style.visibility == 'visible') {
        AJAX.senddata('popUpDivContent',formname,'index.php',false);
        setaddtoform(formname,'ajax','hidden','0');
//	popup.style.visibility='hidden';
        return;
      }
    }
    AJAX.senddata('main-body',formname,'index.php');
    setaddtoform(formname,'ajax','hidden','0');
  }
}

function ajaxssl(showt) {
  setactivemenu('sslrev');
  setaddtoform('loadpage','ajax','hidden','1');
  setaddtoform('loadpage','show','hidden',showt);
  AJAX.senddata('main-body','loadpage','/cgi-bin/admin/sslman');
  setaddtoform('loadpage','ajax','hidden','0');
  setaddtoform('loadpage','show','hidden','');
}

function ajaxrevoke(revid) {
  setactivemenu('sslrev');
  document.sslrevf.revoke.value=revid;
  AJAX.senddata('main-body','sslrevf','/cgi-bin/admin/sslman');
}

function getrepsort(orderby) {
  document.sortform.sortby.value=orderby;
  document.sortform.submit() 
}

function openqueue(queuenum) {
  document.pform.fqueue.value=queuenum;
  ajaxsubmit('pform');
//  document.pform.submit();
}

function blurt(testtoblurt) {
  alert(testtoblurt);
}

function opendnsadmin(formobj) {
  if (formobj.otherdns.value == "") {
    alert("A Domain Name Must Be Entered");
  } else {   
    AJAX.senddata('main-body',formobj.name,formobj.action);
  }
}

function deleteconf(etext,dform,flag) {
  if (confirm('Are You Sure You Want To Delete '+etext+' ?')) {
    flag.value="1";
    ajaxsubmit(dform.name);
  }
}

function ldapautodata(dom) {
  var tmparray=new Array();
  var userdat=dom.getElementsByTagName("user");
  for(var i=0;i < userdat.length;i++) {
    tmparray[userdat[i].getAttribute('id')]=userdat[i].firstChild.nodeValue;
  }
  return tmparray;
}

function setldappopurl(pform) {
  return 'type='+pform.type.value+'&what='+pform.what.value+'&baseou='+pform.baseou.value+'&search';
}

function setautosearchurl(pform) {
  return 'search';
}

function directdial() {
  var extento=prompt('Enter number to dial ?')
  if ((extento != null) && (extento != '')) {
    document.ccagentf.dialbut.disabled=true;
    document.ccagentf.ddialbut.disabled=true;
    document.ccagentf.subme.disabled=true;
    document.ccagentf.transbut.disabled=false;
    ajaxcall(document.caller,'directdial='+escape(extento),ajaxcaller);
  }
}

function ccdial_settrans() {
  document.ccagentf.transbut.disabled=false;
}

function ccdial_setdial() {
  document.ccagentf.ddialbut.disabled=false;
}

function ccdial(transok,dialok) {
  document.ccagentf.dialbut.disabled=true;
  document.ccagentf.subme.disabled=false;
  if (transok)
    ccdial_settrans();
  if (dialok)
    ccdial_setdial();
  ajaxcall(document.caller,'numtocall='+escape(document.caller.numtocall.value)+'&contactnum='+escape(document.caller.contactnum.value),ajaxcaller);
}

function ccaccept() {
  if (confirm('Click OK To Accept Call')) {
    ccdial();
  } else {
//    document.ccagentf.enabled=false;
//    document.ccagentf.subme.name='abortcall';
//    setTimeout("ccaccept();",15000);
  }
}

function transcall(contid) {
  var extento=prompt('Enter extension to transfer to ?')
  if ((extento != null) && (extento != '')) {
    ajaxcall(document.caller,'transfer='+contid+'&extento='+escape(extento),ajaxcaller);
    if ((document.ccagentf.dialbut.disabled) && (document.ccagentf.ddialbut.disabled) && (document.ccagentf.subme.disabled)) {
      document.ccagentf.dialbut.disabled=false;
      document.ccagentf.ddialbut.disabled=false;
    }
  }
}

function ajaxcall(callform,formdat,ajax) {
  if (ajax.readyState > 0) {
    ajax.abort();
  }
  ajax.onreadystatechange=ajaxcallresp;
  ajax.open(callform.method,callform.action,true);
  ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  ajax.setRequestHeader("Content-length", formdat.length);
  ajax.setRequestHeader("Connection", "close");
  ajax.send(formdat);
}

function ajaxcallresp() {
  if ((ajaxcaller.readyState == 4) && (ajaxcaller.status == 200)) {
//    alert(ajaxcaller.responseText);
  }
}

function ip2long(ip) {
  var ips = ip.split('.');
  with (Math)
    return ips[0]*pow(256,3)+ips[1]*pow(256,2)+ips[2]*256+ips[3]*1;
}

function long2ip(l) {
  var ip = new Array();
  var rem = 0;
  var left = l;
  with (Math) {
    for (i=3;i >= 0;i--) {
      rem = left % pow(256,i);
      ip.push((left - rem)/ pow(256,i));
      left=rem;
    }
  }
  return ip.join(".");
}

function verifyNet(ipaddr,snbits,ipaddr2) {
  return ((ip2long(ipaddr) >> (32-snbits)) == (ip2long(ipaddr2) >> (32-snbits)));
}

function verifyIP (IPvalue) {
  var ipArray = IPvalue.match(/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/);
  if (ipArray == null)
    return false;
  else {
    for (i = 1; i <= 4; i++) {
      if (ipArray[i] > 255)
        return false;
    }
  }
  return true;
}

function verifyHost (Hostvalue) {
  var hostParts=Hostvalue.split(".");
  for (i = 0;i < hostParts.length; i++) {
    if ((! hostParts[i].match("^[a-zA-Z0-9-]+$")) || (hostParts[i].length > 63))
      return false;
  }
  return true;
}

function verifyIP2 (IPvalue, IPMask) {
  if ((IPvalue == "0.0.0.0") || (IPvalue == "255.255.255.255") || (IPMask > 32) || (IPMask < 0))
    return false;
  if (!verifyIP(IPvalue))
    return false;
  var mask=Math.pow(2,(32-IPMask))-1;
  var ipaddr=ip2long(IPvalue) & mask;
  if ((ipaddr == 0) || (ipaddr == mask))
    return false;
  else 
    return true;
}

function getelementbyname(formobj,elname) {
  for(felm=0;felm < formobj.elements.length;felm++) {
    if (formobj.elements[felm].name == elname) {
      if ((formobj.elements[felm].type == "radio") && (! formobj.elements[felm].checked))
        continue;
      break;
    }
  }
  return formobj.elements[felm];
}

function savereconfchanges() {
  var err="";
  if (! verifyHost(document.confform.HN_ADDR.value+'.'+document.confform.DOM_ADDR.value))
    err=err+'Invalid Hostname And Or Domain\r\n';
  if ((! verifyHost(document.confform.SMTP_FWD.value)) && (document.confform.SMTP_FWD.value != ''))
    err=err+'Invalid SMTP Gateway\r\n';

  var ntpserv=document.confform.NTP_SERV.value.split(" ");
  if (ntpserv.length == 1)
    err=err+'NTP Server[s] Not Set';
  else {
    for(ni = 0;ni < ntpserv.length;ni++) {
      if (! verifyIP(ntpserv[ni])) 
        err=err+'Invalid NTP Server IP Address ('+ntpserv[ni]+')\r\n';
    }
  }

  if ((document.confform.DYN_SERV.value != '') && (! verifyIP(document.confform.DYN_SERV.value)) && (!verifyHost(document.confform.DYN_SERV.value)))
    err=err+'Invalid Dynamic DNS Server Address\r\n';
  if ((! verifyHost(document.confform.DYN_ZONE.value)) && (document.confform.DYN_ZONE.value != ''))
    err=err+'Invalid Dynamic DNS Zone Address\r\n';

  if ((! verifyIP(document.confform.DNS_SERV1.value)) && (document.confform.DNS_SERV1.value != ''))
    err=err+'Invalid Primary DNS IP Address\r\n';
  if ((! verifyIP(document.confform.DNS_SERV2.value)) && (document.confform.DNS_SERV2.value != ''))
    err=err+'Invalid Secondarybeen DNS IP Address\r\n';

  if ((! verifyIP(document.confform.WINS_SERV1.value)) && (document.confform.WINS_SERV1.value != ''))
    err=err+'Invalid Primary WINS IP Address\r\n';
  if ((! verifyIP(document.confform.WINS_SERV2.value)) && (document.confform.WINS_SERV2.value != ''))
    err=err+'Invalid Secondary WINS IP Address\r\n';

  if ((! verifyHost(document.confform.DNS_MX1.value)) && (document.confform.DNS_MX1.value != ''))
    err=err+'Invalid Primary MX Record\r\n';
  if ((! verifyHost(document.confform.DNS_MX2.value)) && (document.confform.DNS_MX2.value != ''))
    err=err+'Invalid Secondary MX Record\r\n';

  var intips=new Array();
  var allints=new Array();

  var icnt=1;
  var ethint = document.getElementById('ETH:'+icnt);
  var intdat=new Array();
  while(ethint) {
    intdat[0]=getelementbyname(document.confform,'IP_ADDR:'+icnt).value;
    intdat[1]=getelementbyname(document.confform,'SN_ADDR:'+icnt).value;

    dhcps=getelementbyname(document.confform,'IP_SDHCP:'+icnt);
    if (dhcps.value == '')
      dhcps.value='-';
    intdat[2]=dhcps.value;

    dhcpe=getelementbyname(document.confform,'IP_EDHCP:'+icnt);
    if (dhcpe.value == '')
      dhcpe.value='-';
    intdat[3]=dhcpe.value;

    if ((!verifyIP(intdat[0])) || 
        ((!verifyIP2(intdat[0],intdat[1])) && (intdat[1] != '32')) ||
        ((!verifyNet(intdat[0],intdat[1],intdat[2])) && (intdat[2] != '-')) || 
        ((!verifyNet(intdat[0],intdat[1],intdat[3])) && (intdat[3] != '-')) || 
        ((!verifyIP2(intdat[2],intdat[1])) && (intdat[2] != '-')) ||
        ((!verifyIP2(intdat[3],intdat[1])) && (intdat[3] != '-')))
      err=err+'Invalid '+ethint.innerHTML+'\r\n';
    else if (intdat[1] != 32)
      intips.push(intdat[0]+'/'+intdat[1]);
    allints.push(intdat[0]+'/'+intdat[1]);

    icnt++;
    ethint = document.getElementById('ETH:'+icnt);
  }

  var vcnt=1;
  var vint = document.getElementById('VLAN:'+vcnt);
  var vintdat=new Array();
  while(vint) {
    vintdat[0]=getelementbyname(document.confform,'IP_ADDRV:'+vcnt).value;
    vintdat[1]=getelementbyname(document.confform,'SN_ADDRV:'+vcnt).value;

    dhcps=getelementbyname(document.confform,'IP_SDHCPV:'+vcnt);
    if (dhcps.value == '')
      dhcps.value='-';
    vintdat[2]=dhcps.value;

    dhcpe=getelementbyname(document.confform,'IP_EDHCPV:'+vcnt);
    if (dhcpe.value == '')
      dhcpe.value='-';
    vintdat[3]=dhcpe.value;

    if ((! verifyIP(vintdat[0])) || 
        ((!verifyIP2(vintdat[0],vintdat[1])) && (vintdat[1] != '32')) ||
        ((!verifyNet(vintdat[0],vintdat[1],vintdat[2])) && (vintdat[2] != '-')) || 
        ((!verifyNet(vintdat[0],vintdat[1],vintdat[3])) && (vintdat[3] != '-')) || 
        ((!verifyIP2(vintdat[2],vintdat[1])) && (vintdat[2] != '-')) ||
        ((!verifyIP2(vintdat[3],vintdat[1])) && (vintdat[3] != '-')))
      err=err+'Invalid '+vint.innerHTML+'\r\n';
    else if (vintdat[1] != 32)
      intips.push(vintdat[0]+'/'+vintdat[1]);
    allints.push(vintdat[0]+'/'+vintdat[1]);

    vcnt++;
    vint = document.getElementById('VLAN:'+vcnt);
  }

  var acnt=1;
  var aint = document.getElementById('ALIAS:'+acnt);
  var aintdat=new Array();
  while(aint) {
    aintdat[0]=getelementbyname(document.confform,'IP_ADDRA:'+acnt).value;
    aintdat[1]=getelementbyname(document.confform,'SN_ADDRA:'+acnt).value;

    if ((!verifyIP(aintdat[0])) || 
        ((!verifyIP2(aintdat[0],aintdat[1])) && (aintdat[1] != '32')))
      err=err+'Invalid '+aint.innerHTML+'\r\n';
    else if (aintdat[1] != 32)
      intips.push(aintdat[0]+'/'+aintdat[1]);

    acnt++;
    aint = document.getElementById('ALIAS:'+acnt);
  }

  var validgw=false;

  if (document.confform.GW_ADDR.value == '')
    validgw=true;


  for(intcnt=0;intcnt < intips.length;intcnt++) {
    curip=intips[intcnt].match("([0-9\.]+)/([0-9]+)");
    if ((! validgw) && (verifyNet(curip[1],curip[2],document.confform.GW_ADDR.value)))
      validgw=true;
    for(imcnt=intcnt+1;imcnt < intips.length;imcnt++) {
      testip=intips[imcnt].match("([0-9\.]+)/([0-9]+)");
      if ((verifyNet(curip[1],curip[2],testip[1])) || (verifyNet(curip[1],testip[2],testip[1])))
        err=err+'Subnet '+curip[1]+'/'+curip[2]+' Overlaps With '+testip[1]+'/'+testip[2]+'\r\n';
    }
  }

  if ((! verifyIP(document.confform.GW_ADDR.value)) && (document.confform.GW_ADDR.value != ''))
    err=err+'Invalid Gateway IP Address\r\n';
  else if (! validgw)
    err=err+'Invalid Gateway Address (Gateway Not On Any Configured Subnets)\r\n';
  else if ((!document.confform.EXTPPPOE.checked) && (document.confform.GW_ADDR.value != '')) {
    gwnet=allints[document.confform.MDM_CONN.selectedIndex-1].match("([0-9\.]+)/([0-9]+)");
    if (!verifyNet(gwnet[1],gwnet[2],document.confform.GW_ADDR.value))
      err=err+'Invalid Gateway Address (Gateway Not In External Device Subnet)\r\n';
  }

  if (err != '') {
    alert(err);
  } else {
    document.confform.saved.value='1';
    oksave=confirm("Continue ??\n\nThe system will save the settings and reboot when complete.\nThis will take upto 5 minutes.\nPlease do not proceed unless you are sure.\n\n");
    if (oksave) {
      ajaxsubmit('confform');
    }
  }
}

function toplimit(topupacc) {
  topupval=prompt("Enter Ammount To Topup","");
  if ((topupval != null) && (topupval != '') && (topupval > 0)) {
    document.topupexten.account.value=topupacc;
    document.topupexten.ammount.value=topupval;
    ajaxsubmit('topupexten');
  }
}
function del_user() {
  if (confirm("Are You Sure You Want To Delete\n"+document.uinfo.cn.value)) {
    document.uinfo.deltex.value="delete";
    ajaxsubmit('uinfo');
  }
}

function fix_user() {
  if (confirm("This Will Reset The Users Windows SID\nShould I Proceed Repairing\n"+document.uinfo.cn.value)) {
    document.uinfo.fixtex.value="repair";
    ajaxsubmit('uinfo');
  }
}
function ShowIpData(IPsaddr,IPdaddr,Proto){
  document.ipdata.saddr.value=IPsaddr;
  document.ipdata.daddr.value=IPdaddr;
  document.ipdata.sproto.value=Proto;
  ajaxsubmit('ipdata');
}

function ShowMsgData(MSGid){
  document.msgdata.msgid.value=MSGid;
  ajaxsubmit('msgdata');
}

function popup(loadurl,height,width) {
	if ((window.innerHeight > document.body.parentNode.scrollHeight) && (window.innerHeight > document.body.parentNode.clientHeight)) {
		window_height = window.innerHeight;
	} else {
		if (document.body.parentNode.clientHeight > document.body.parentNode.scrollHeight) {
			window_height = document.body.parentNode.clientHeight;
		} else {
			window_height = document.body.parentNode.scrollHeight;
		}
	}

	if ((window.innerWidth > document.body.parentNode.scrollWidth) && (window.innerWidth > document.body.parentNode.clientWidth)) {
		window_width = window.innerWidth;
	} else {
		if (document.body.parentNode.clientWidth > document.body.parentNode.scrollWidth) {
			window_width = document.body.parentNode.clientWidth;
		} else {
			window_width = document.body.parentNode.scrollWidth;
		}
	}

	var popup = document.getElementById("popUpDiv");
	var blanket = document.getElementById('blanket');
	popup.style.width = width+'px';
	popup.style.height = height+'px';

	blanket.style.height = window_height + 'px';
	blanket.style.width = window_width + 'px';
	blanket.style.top = '0px';
	blanket.style.left = '0px';

	if (window_width > popup.offsetWidth) {
		popup.style.left = (window_width-popup.offsetWidth)/2;
	} else {
		popup.style.width = window_width;
		popup.style.left = 0;
	}

	if (window_height > popup.offsetHeight) {
		popup.style.top = (window_height-popup.offsetHeight)/2;
	} else {
		popup.style.height = window_height;
		popup.style.top = 0;
	}

	if ((popup.style.top == 0) || (popup.style.left == 0)) {
		popup.style.overflow = "auto";
	}
	popup.style.left = popup.style.left + 'px';
	popup.style.top = popup.style.top + 'px';

	blanket.style.visibility='visible';

        var popcont = document.getElementById("popUpDivContent");
	popcont.style.visibility='hidden';
        if (loadurl != null) {
	  frameht=height-20;
	  htmlout="<IFRAME";
          if (loadurl != '')
            htmlout=htmlout+" SRC=\""+loadurl+"\"";
          htmlout=htmlout+" id=popupiframe NAME=popupiframe FRAMEBORDER=0 HEIGHT="+frameht+" WIDTH=100%>";
	} else {
	  htmlout="";  
        }
        popcont.innerHTML=htmlout;

	popup.style.visibility='visible';
	popcont.style.visibility='visible';
}

function popdown() {
	var blanket = document.getElementById("blanket");
	blanket.style.visibility='hidden';
	var popup = document.getElementById("popUpDiv");
	popup.style.visibility='hidden';
	var popcont = document.getElementById("popUpDivContent");
	popcont.innerHTML="";
}

function randomID(size) {
        var chars = "0123456789abcdefghijklmnopqurstuvwxyzABCDEFGHIJKLMNOPQURSTUVWXYZ";
        var str = "";
        for(var i = 0; i < size; i++) {
                str += chars.substr(Math.floor(Math.random() * 62),1);
        }
        return str;
}
