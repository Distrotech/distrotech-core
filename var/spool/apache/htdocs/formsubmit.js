#    Copyright (C) 2002  <Gregory Hinton Nietsky>
#    Copyright (C) 2005  <ZA Telecomunications>
#    Copyright (C) 2012  <Distrotech Solutions>
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

var sendform = new FormSubmit("main-body");

function FormSubmit(outdiv) {
  this.target = null;
  this.outdiv = outdiv;
  self = this;

  this.blanket = null;

  this.target = document.createElement("iframe");
  this.target.style.width = "0";
  this.target.style.height = "0";
  this.target.style.border = "1px solid #000";
  this.target.src = "";

  addtoform = function(form,ename,etype,ivalue) {
    for(felm=0;felm < form.elements.length;felm++) {
      if (form.elements[felm].name == ename) {
        break;
      }
    } 
    if ((form.elements[felm] == null) || (form.elements[felm].name != ename)) {
      var newformi = document.createElement('input');
      newformi.type=etype;
      newformi.name=ename;
      newformi.value=ivalue;
      form.appendChild(newformi);
    } else if ((form.elements[felm] != null) && (form.elements[felm].name == ename)) {
      form.elements[felm].value=ivalue;
    }
  }

  this.submit = function(form) {
    this.target.name = "upframe"+form.name;
    document.body.appendChild(self.target);
    form.target = self.target.name;
    self.target.onload = this.uploadDone;
    addtoform(form,'ajax','hidden','1');
    self.blanket = document.getElementById('blanket');
    if (self.blanket != null) {
      self.blanket.style.visibility = 'visible';
      self.blanket.style.cursor='wait';
    }
    form.submit();
    return false;
  }

  this.uploadDone = function() {
    var outputdiv = document.getElementById(self.outdiv);

    htmlout=self.target.contentDocument.getElementsByTagName("body")[0].innerHTML;
    outputdiv.innerHTML = htmlout;

    var scripts = outputdiv.getElementsByTagName('script');
    for (var i=0;i<scripts.length;i++) {
      eval(scripts[i].innerHTML);
    }

    if (self.blanket != null) {
      self.blanket.style.visibility = 'hidden';
      self.blanket.style.cursor='default';
    }
    this.target.src = "";
    self.target.parentNode.removeChild(self.target);
    self.target.contentWindow.close();
  }
}
