function addpara(xmldom) {
  root=xmldom.documentElement;

  nodes=xmldom.getElementsByTagName("para")

  para=xmldom.createElement("para");
  id = nodes.length+1;
  para.setAttribute("id",id);
  texte=xmldom.createElement("text");
  if (document.scriptform.newtext.value != "") {
    text=xmldom.createTextNode(document.scriptform.newtext.value);
  } else {
    text=xmldom.createTextNode("Not Set");
  }
  texte.appendChild(text);
  para.appendChild(texte);
  input=xmldom.createElement("input");
  input.setAttribute("type",document.scriptform.inputtype.value);
  input.setAttribute("name","xml_"+randomID(7)+id);
  desc=document.scriptform.inputdesc.value;
  if (desc !=  "") {
    input.setAttribute("description", desc);
  }
  para.appendChild(input);

  root.appendChild(para);
  loadhtml("script", xmldom, true);
}

function loadXMLDoc(scriptid) {
  var out="xmlscriptid="+scriptid;
  xhttp = new XMLHttpRequest();
  xhttp.open("post","loadxml.php",false);
  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhttp.setRequestHeader("Content-length", out.length);
  xhttp.setRequestHeader("Connection", "close");
  xhttp.send(out);
  return xhttp.responseXML; 
}

function makehtml(text) {
  var textneu = text.replace(/&/,"&amp;");
  textneu = textneu.replace(/</,"&lt;");
  textneu = textneu.replace(/>/,"&gt;");
  textneu = textneu.replace(/\"/,"&quot;");
  return textneu;
}

function getrawxml(domobj) {
  root=domobj.documentElement;
  var xmlpara = new Array();
  var xmlid = new Array();

  var xmlout="<?xml version=\""+domobj.xmlVersion+"\" encoding=\""+domobj.xmlEncoding+"\"?>\n<"+root.nodeName+">\n";
  if (root.nodeValue) {
    xmlout+="    "+root.nodeValue+"\n";
  }

  x=root.childNodes;
  for (i=0;i<x.length;i++) {
    if (x[i].nodeType == 1) {
      id=parseInt(x[i].getAttribute("id"));
      xmlarr="  <"+x[i].nodeName;
      for (l=0;l<x[i].attributes.length;l++) {
        xmlarr+=" "+x[i].attributes[l].name+"=\""+x[i].attributes[l].value+"\"";
      }
      xmlarr+=">\n";
//      if (x[i].childNodes.length > 0) {
//      xmlarr+=x[i].childNodes[0].nodeValue;
//      }
      y=x[i].childNodes;
      for (j=0;j<y.length;j++) {
        if (y[j].nodeType == 1) {
        xmlarr+="    <"+y[j].nodeName;
        for (m=0;m<y[j].attributes.length;m++) {
          xmlarr+=" "+y[j].attributes[m].name+"=\""+y[j].attributes[m].value+"\"";
        }
        xmlarr+=">";
        if (y[j].nodeName != "input") {
          if (y[j].childNodes.length > 0) {
            xmlarr+=makehtml(y[j].childNodes[0].nodeValue);
          }
        } else {
          type = y[j].getAttribute("type");
          name = y[j].getAttribute("name");
          if (name == "")
            name="field"+id;
          name = "SCRIPT_"+name;
          frmel=getelementbyname(document.scriptform,name);
          if (type == "checkbox") {
            if (frmel.checked) {
              xmlarr+=makehtml("t");
            } else {
              xmlarr+=makehtml("f");
            }
          } else {
            if (frmel != null)
              xmlarr+=makehtml(frmel.value);
            }
          }
          xmlarr+="</"+y[j].nodeName+">\n";
        }
      }
      xmlarr+="  </"+x[i].nodeName+">\n";
      xmlid.push(id);
      xmlpara.push(xmlarr);
    }
  }
  for(i=0;i < xmlpara.length;i++) {
    id=xmlid[i]-1;
    xmlout+=xmlpara[id];
  }
  xmlout+="</"+root.nodeName+">\n";
  return xmlout;
}

function savescript(domobj, scriptid) {
  var out="xmlscript="+encodeURIComponent(getrawxml(domobj))+"&xmlscriptid="+scriptid;
  ajax = new XMLHttpRequest();
  ajax.open("post","savexml.php",true);
  ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  ajax.setRequestHeader("Content-length", out.length);
  ajax.setRequestHeader("Connection", "close");
  ajax.send(out);
}

function sortNumber(a,b) {
  return a - b;
}

function getElementsByAttribute(xmlDOM, TagName, AttrName, AttrVal) {
  var tags=xmlDOM.getElementsByTagName(TagName);
  for(el=0;el < tags.length;el++) {
    if (tags[el].nodeType == 1) {
      id=parseInt(tags[el].getAttribute(AttrName));
      if ((id != null) && (AttrVal == id)) {
        return tags[el];
      }
    }
  }
  return null;
}

function addselopt() {
  newopt=document.celledit.newopt;
  opt=new Option(newopt.value, newopt.value, false, false);

  opts=document.celledit.opts;
  if (opts.selectedIndex+1 < opts.options.length) {
    aftopt=opts.options[opts.selectedIndex+1];
  } else {
    aftopt=null;
  }
  opts.add(opt,aftopt);
  newopt.value="";
  opts.selectedIndex++;
}

function delselopt() {
  document.celledit.newopt.value="";
  document.celledit.opts.remove(document.celledit.opts.selectedIndex);
}

function moveup(xmlid) {
  root=xmlDoc.documentElement;

  cell=getElementsByAttribute(xmlDoc, "para", "id", xmlid);
  cell.parentNode.removeChild(cell);
  cell2=getElementsByAttribute(xmlDoc, "para", "id", xmlid-1);

  cell.setAttribute("id",xmlid-1);
  cell2.setAttribute("id",xmlid);

  root.insertBefore(cell,cell2);

  loadhtml("script", xmlDoc, true);
  popdown();
}

function movedown(xmlid) {
  root=xmlDoc.documentElement;

  cell=getElementsByAttribute(xmlDoc, "para", "id", xmlid);
  cell.parentNode.removeChild(cell);
  cell2=getElementsByAttribute(xmlDoc, "para", "id", xmlid+1);

  cell.setAttribute("id",xmlid+1);
  cell2.setAttribute("id",xmlid);

  x=xmlDoc.getElementsByTagName("para")
  if (xmlid+2 < x.length) {
    cell3=getElementsByAttribute(xmlDoc, "para", "id", xmlid+2);
    root.insertBefore(cell,cell3);
  } else {
    root.appendChild(cell);
  }
  loadhtml("script", xmlDoc, true);
  popdown();
}

function delopt(xmlid) {
  cell=getElementsByAttribute(xmlDoc, "para", "id", xmlid);
  cell.parentNode.removeChild(cell);

  x=xmlDoc.getElementsByTagName("para")
  for (i=0;i < x.length;i++) {
    if (x[i].nodeType == 1) {
      id=parseInt(x[i].getAttribute("id"));
      if (xmlid < id) {
        id--;
        x[i].setAttribute("id",id);
      }
    }
  }

  loadhtml("script", xmlDoc, true);
  popdown();
}

function saveopt(xmlid) {
  cell=getElementsByAttribute(xmlDoc, "para", "id", xmlid);
  text = cell.getElementsByTagName("text")[0];
  if (text.childNodes.length > 0) {
    text.childNodes[0].nodeValue=document.celledit.newtext.value;
  }

  input = cell.getElementsByTagName("input");
  if (input != null) {
    type = input[0].getAttribute("type");
    if ((type == "select") || (type == "radio")) {
      if (input[0].childNodes.length > 0) {
        input[0].childNodes[0].nodeValue=document.celledit.opts.value;
      } else {
        itext=xmlDoc.createTextNode(document.celledit.opts.value);
        input[0].appendChild(itext);
      }
    }

    if (type == "select") {
      name = input[0].getAttribute("name");
      origsel=getelementbyname(document.scriptform,"SCRIPT_"+name);
      while(origsel.length > 0) {
        origsel.remove(0);
      }
    } else if (type == "radio") {
      for(x=0;x < document.scriptform.length;x++) {
        if (document.scriptform.elements[x].name == "SCRIPT_"+name) {
          butel=document.scriptform.elements[x];
	  butel.parentNode.removeChild(butel);
	  x--;
        }
      }
    }
  }

  opts=document.celledit.opts;
  if (opts != null) {
    option = cell.getElementsByTagName("option");
    while (option.length > 0) {
      cell.removeChild(option[0]);
    }
    while(opts.length > 0) {
      opte=xmlDoc.createElement("option");
      optt=xmlDoc.createTextNode(opts.options[0].value);
      opte.appendChild(optt);
      cell.appendChild(opte);
      opts.remove(0);
    }
  }
  loadhtml("script", xmlDoc, true);
  popdown();
}

function xmlpopup(xmlid) {
  popup(null, 350, 600);
  var popcont = document.getElementById("popUpDivContent");
  htmlout="<CENTER><FORM NAME=celledit><TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>";
  htmlout+="<TR CLASS=list-color2><TH CLASS=heading-body COLSPAN=2>Editing Paragraph</TH></TR>";
  htmlout+="<TR CLASS=list-color1><TD>Script Text</TD><TD><TEXTAREA NAME=newtext ROWS=10 COLS=60>";

  cell=getElementsByAttribute(xmlDoc, "para", "id", xmlid);
  text = cell.getElementsByTagName("text")[0];
  if (text.childNodes.length > 0) {
    htmlout+=text.childNodes[0].nodeValue;
  }

  htmlout+="</TEXTAREA></TD></TR>";

  input = cell.getElementsByTagName("input");
  if (input != null) {
    type = input[0].getAttribute("type");
    name = input[0].getAttribute("name");
    if ((type == "select") || (type == "radio")) {
      htmlout+="<TR CLASS=list-color2><TD>Add Options</TD><TD>";
      htmlout+="<TABLE WIDTH=90% CELLPADDING=0 CELLSPACING=0>";
      htmlout+="<TR><TD>Current Option</TD><TD><SELECT NAME=opts></SELECT></TD></TR>";
      htmlout+="<TR><TD>Option</TD><TD><INPUT TYPE=TEXT NAME=newopt></TD></TR>";
      htmlout+="<TR><TD>&nbsp;</TD><TD><INPUT TYPE=BUTTON VALUE=\"Add (After)\" ONCLICK=addselopt()><INPUT TYPE=BUTTON VALUE=Del ONCLICK=delselopt()></TD></TR>";
      htmlout+="</TABLE>";
      htmlout+="</TD></TR>";
      htmlout+="<TR CLASS=list-color1><TD ALIGN=MIDDLE COLSPAN=2>";
    } else {
      htmlout+="<TR CLASS=list-color2><TD ALIGN=MIDDLE COLSPAN=2>";
    }
    if (xmlid > 1) {
      htmlout+="<INPUT TYPE=BUTTON VALUE=Up ONCLICK=moveup("+xmlid+")>";
    }
    htmlout+="<INPUT TYPE=BUTTON VALUE=Save ONCLICK=saveopt("+xmlid+")>";
    htmlout+="<INPUT TYPE=BUTTON VALUE=Del ONCLICK=delopt("+xmlid+")>";
    x=xmlDoc.getElementsByTagName("para");
    if (x.length > xmlid) {
      htmlout+="<INPUT TYPE=BUTTON VALUE=Down ONCLICK=movedown("+xmlid+")>";
    }
    htmlout+="</TD></TR>";
  }
  htmlout+="</TABLE></FORM>";
  popcont.innerHTML=htmlout;

  if (type == "select") {
    origsel=getelementbyname(document.scriptform,"SCRIPT_"+name);
    for(x=0;x < origsel.options.length;x++) {
      opt=new Option(origsel.options[x].text, origsel.options[x].text, false, false);
      document.celledit.opts.add(opt,null);
    }
    document.celledit.opts.selectedIndex=origsel.selectedIndex;
  } else if (type == "radio") {
    for(x=0;x < document.scriptform.length;x++) {
      if (document.scriptform.elements[x].name == "SCRIPT_"+name) {
        opt=new Option(document.scriptform.elements[x].value, document.scriptform.elements[x].value, document.scriptform.elements[x].checked, document.scriptform.elements[x].checked);
        document.celledit.opts.add(opt,null);
      }
    }
  }
}

function loadhtml(divid, domobj, edit) {
	scriptdiv = document.getElementById(divid);
	root=domobj.documentElement;
	var htmlout = "";
	var htmlarr = new Array();
	var idarr = new Array();

	x=root.childNodes;
	for (i=0;i < x.length;i++) {
		if (x[i].nodeType == 1) {
			id=parseInt(x[i].getAttribute("id"));
			htmlout="<div id=\""+id+"\">";

			text = x[i].getElementsByTagName("text")[0];
			if (text.childNodes.length > 0) {
          			htmlout+="<div";
				if (edit) {
					htmlout+=" style=\"cursor: pointer\" onclick=\"xmlpopup("+id+")\"";
				}
				htmlout+=">"+text.childNodes[0].nodeValue+"</div>";
			} else if (edit) {
          			htmlout+="<div style=\"cursor: pointer\" onclick=\"xmlpopup("+id+")\">Not Set</div>";
			}
			input = x[i].getElementsByTagName("input")[0];
			if (input == null) {
				htmlout+="</div>";
				idarr.push(id);
				htmlarr.push(htmlout);
				continue;
			}
			type = input.getAttribute("type");
			name = input.getAttribute("name");
			if (name == "")
				name="field"+id;
			if (input.childNodes.length > 0) {
				defval = input.childNodes[0].nodeValue;
			} else {
				defval = null;
			}
			switch (type) {
				case "checkbox":htmlout+="<INPUT TYPE=CHECKBOX NAME=\"SCRIPT_"+name+"\"";
					if (defval != null)
						if (defval == "t")
							htmlout+=" CHECKED";
					htmlout+=">";
					break;
				case "select":htmlout+="<SELECT NAME=\"SCRIPT_"+name+"\">";
					option = x[i].getElementsByTagName("option");
					for (j=0;j < option.length;j++) {
						if (option[j].childNodes.length > 0) {
							htmlout+="<OPTION";
							opt = option[j].childNodes[0].nodeValue;
							if (defval != null)
								if (defval == opt)
									htmlout+=" SELECTED";
							htmlout+=">"+opt+"</OPTION>";
						}
					}
					htmlout+="</SELECT>";
					break;
				case "radio":option = x[i].getElementsByTagName("option");
					for (j=0;j < option.length;j++) {
						opt = option[j].childNodes[0].nodeValue;
						htmlout+="<INPUT TYPE=RADIO NAME=\"SCRIPT_"+name+"\" VALUE=\""+opt+"\"";
							if (defval != null)
								if (defval == opt)
									htmlout+=" CHECKED";
						htmlout+=">"+opt+"</OPTION>";
					}
					break;
				case "input":htmlout+="<INPUT TYPE=INPUT NAME=\"SCRIPT_"+name+"\"";
					if (defval != null)
						htmlout+=" VALUE=\""+defval+"\"";
					htmlout+=">";
					break;
				case "text":htmlout+="<TEXTAREA NAME=\"SCRIPT_"+name+"\"";
					cols = input.getAttribute("cols");
					if (cols != null)
						htmlout+=" COLS=\""+cols+"\"";
					else
						htmlout+=" COLS=\"50\"";
					rows = input.getAttribute("rows");
					if (rows != null)
						htmlout+=" ROWS=\""+rows+"\"";
					else
						htmlout+=" ROWS=\"15\"";
					htmlout+=">";
					if (defval != null)
						htmlout+=defval;
					htmlout+="</TEXTAREA>";

			}
			htmlout+="</div><P>";
			idarr.push(id);
			htmlarr.push(htmlout);
	      	}
    	}
	htmlout="";
	for(i=0;i < htmlarr.length;i++) {
		id=idarr[i]-1;
		htmlout+=htmlarr[id];
	}
	scriptdiv.innerHTML=htmlout.replace(/\n/g, "<br>");
}
