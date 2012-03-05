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
