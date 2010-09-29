var defaultmode = "divmode";
var text = "";

var head = document.getElementsByTagName("head")[0];
var script = document.createElement("script");
script.src = 'js/lang/zh_cn.js';
head.appendChild(script);

if (defaultmode == "nomode") {
        helpmode = false;
        divmode = false;
        nomode = true;
} else if (defaultmode == "helpmode") {
        helpmode = true;
        divmode = false;
        nomode = false;
} else {
        helpmode = false;
        divmode = true;
        nomode = false;
}
function checkmode(swtch){
	if (swtch == 1){
		nomode = false;
		divmode = false;
		helpmode = true;
		alert(I18N['wind_help_1']);
	} else if (swtch == 0) {
		helpmode = false;
		divmode = false;
		nomode = true;
		alert(I18N['wind_help_2']);
	} else if (swtch == 2) {
		helpmode = false;
		nomode = false;
		divmode = true;
		alert(I18N['wind_help_3']);
	}
}
function getActiveText(selectedtext) {
  text = (document.all) ? document.selection.createRange().text : document.getSelection();
  if (selectedtext.createTextRange) {	
    selectedtext.caretPos = document.selection.createRange().duplicate();	
  }
	return true;
}
function submitonce(theform)
{
	if (document.all||document.getElementById)
	{
		for (i=0;i<theform.length;i++)
		{
			var tempobj=theform.elements[i];
			if(tempobj.type.toLowerCase()=="submit"||tempobj.type.toLowerCase()=="reset")
				tempobj.disabled=true;
		}
	}
}
function checklength(theform)
{
	alert(I18N['currentbits'] + theform.atc_content.value.length);
}
function AddText(NewCode) 
{
	if (document.FORM.atc_content.createTextRange && document.FORM.atc_content.caretPos) 
	{
		var caretPos = document.FORM.atc_content.caretPos;
		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? NewCode + ' ' : NewCode;
	} 
	else 
	{
		document.FORM.atc_content.value+=NewCode
	}
	setfocus();
}
function setfocus()
{
  document.FORM.atc_content.focus();
}


function showsize(size) {
	if (helpmode) {
		alert(I18N['fontsize'] + "[size=" + size + "] " + size + I18N['word'] + "[/size]");
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[size="+size+"]"+text+"[/size]";
		AddText(AddTxt);
	} else {
		txt=prompt('',I18N['word']);
		if (txt!=null) {
			AddTxt="[size="+size+"]"+txt;
			AddText(AddTxt);
			AddTxt="[/size]";
			AddText(AddTxt);
		}
	}
}

function showfont(font) {
 	if (helpmode){
		alert(I18N['setfont']+" [font="+font+"]"+font+"[/font]");
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[font="+font+"]"+text+"[/font]";
		AddText(AddTxt);
	} else {
		txt=prompt(I18N['fontword']+font,I18N['word']);
		if (txt!=null) {
			AddTxt="[font="+font+"]"+txt;
			AddText(AddTxt);
			AddTxt="[/font]";
			AddText(AddTxt);
		}
	}
}
function showcolor(color) {
	if (helpmode) {
		alert(I18N['setcolor']+"[color="+color+"]"+color+"[/color]");
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[color="+color+"]"+text+"[/color]";
		AddText(AddTxt);
	} else {  
     	txt=prompt(I18N['color']+color,I18N['word']);
		if(txt!=null) {
			AddTxt="[color="+color+"]"+txt;
			AddText(AddTxt);
			AddTxt="[/color]";
			AddText(AddTxt);
		}
	}
}

function bold() {
	if (helpmode) {
		alert(I18N['setbold']);
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[b]"+text+"[/b]";
		AddText(AddTxt);
	} else {
		txt=prompt(I18N['blogword'],I18N['word']);
		if (txt!=null) {
			AddTxt="[b]"+txt;
			AddText(AddTxt);
			AddTxt="[/b]";
			AddText(AddTxt);
		}
	}
}

function italicize() {
	if (helpmode) {
		alert(I18N['setitalic']);
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[i]"+text+"[/i]";
		AddText(AddTxt);
	} else {
		txt=prompt(I18N['italicword'],I18N['word']);
		if (txt!=null) {
			AddTxt="[i]"+txt;
			AddText(AddTxt);
			AddTxt="[/i]";
			AddText(AddTxt);
		}
	}
}

function quoteme() {
	if (helpmode){
		alert(I18N['quote']);
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[quote]"+text+"[/quote]";
		AddText(AddTxt);
	} else {
		txt=prompt(I18N['quoteword'],I18N['word']);
		if(txt!=null) {
			AddTxt="[quote]"+txt;
			AddText(AddTxt);
			AddTxt="[/quote]";
			AddText(AddTxt);
		}
	}
}
function setfly() {
 	if (helpmode){
		alert(I18N['setfly']);
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[fly]"+text+"[/fly]";
		AddText(AddTxt);
	} else {
		txt=prompt(I18N['flyword'],I18N['word']);
		if (txt!=null) {
			AddTxt="[fly]"+txt;
			AddText(AddTxt);
			AddTxt="[/fly]";
			AddText(AddTxt);
		}
	}
}

function movesign() {
	if (helpmode) {
		alert(I18N['setmove']);
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[move]"+text+"[/move]";
		AddText(AddTxt);
	} else {
		txt=prompt(I18N['moveword'],I18N['word']);
		if (txt!=null) {
			AddTxt="[move]"+txt;
			AddText(AddTxt);
			AddTxt="[/move]";
			AddText(AddTxt);
		}
	}
}

function shadow() {
	if (helpmode) {
		alert(I18N['setshadow']);
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[SHADOW=255,blue,1]"+text+"[/SHADOW]";
		AddText(AddTxt);
	} else {
		txt2=prompt(I18N['shadowsize'],"255,blue,1");
		if (txt2!=null) {
			txt=prompt(I18N['shadowword'],I18N['word']);
			if (txt!=null) {
				if (txt2=="") {
					AddTxt="[shadow=255, blue, 1]"+txt;
					AddText(AddTxt);
					AddTxt="[/shadow]";
					AddText(AddTxt);
				} else {
					AddTxt="[shadow="+txt2+"]"+txt;
					AddText(AddTxt);
					AddTxt="[/shadow]";
					AddText(AddTxt);
				}
			}
		}
	}
}

function glow() {
	if (helpmode) {
		alert(I18N['setglow']);
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[glow=255,red,2]"+text+"[/glow]";
		AddText(AddTxt);
	} else {
		txt2=prompt(I18N['glowsize'],"255,red,2");
		if (txt2!=null) {
			txt=prompt(I18N['glowword'],I18N['word']);
			if (txt!=null) {
				if (txt2=="") {
					AddTxt="[glow=255,red,2]"+txt;
					AddText(AddTxt);
					AddTxt="[/glow]";
					AddText(AddTxt);
				} else {
					AddTxt="[glow="+txt2+"]"+txt;
					AddText(AddTxt);
					AddTxt="[/glow]";
					AddText(AddTxt);
				}
			}
		}
	}
}

function center() {
 	if (helpmode) {
		alert(I18N['align']);
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[align=center]"+text+"[/align]";
		AddText(AddTxt);
	} else {
		txt2=prompt(I18N['alignway'],"center");
		while ((txt2!="") && (txt2!="center") && (txt2!="left") && (txt2!="right") && (txt2!=null)) {
			txt2=prompt(I18N['alignerror'],"");
		}
		txt=prompt(I18N['aligntext'],I18N['word']);
		if (txt!=null) {
			AddTxt="[align="+txt2+"]"+txt;
			AddText(AddTxt);
			AddTxt="[/align]";
			AddText(AddTxt);
		}
	}
}
/*
function rming() {
	if (helpmode) {
		alert(I18N['setrm']);
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[rm]"+text+"[/rm]";
		AddText(AddTxt);
	} else {
		txt=prompt('URL:',"http://");
		if(txt!=null) {
			AddTxt="[rm]"+txt;
			AddText(AddTxt);
			AddTxt="[/rm]";
			AddText(AddTxt);
		}
	}
}*/
function rming() {
	if (helpmode){
		alert(I18N['setrm']);
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[rm=316,241,1]"+text+"[/rm]";
		AddText(AddTxt);
	} else {
        txt2=prompt(I18N['wha'],"316,241,1");
		if (txt2!=null) {
			txt=prompt('URL:',"http://");
			if (txt!=null) {
				if (txt2=="") {
					AddTxt="[rm]"+txt;
					AddText(AddTxt);
					AddTxt="[/rm]";
					AddText(AddTxt);
				} else {
					AddTxt="[rm="+txt2+"]"+txt;
					AddText(AddTxt);
					AddTxt="[/rm]";
					AddText(AddTxt);
				}
			}
		}
	}
}

function image() {
	if (helpmode){
		alert(I18N['setimg']);
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[img]"+text+"[/img]";
		AddText(AddTxt);
	} else {
		txt=prompt('URL:',"http://");
		if(txt!=null) {
			AddTxt="[img]"+txt;
			AddText(AddTxt);
			AddTxt="[/img]";
			AddText(AddTxt);
		}
	}
}
/*
function wmv() {
	if (helpmode){
		alert(I18N['setwmv']);
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[wmv]"+text+"[/wmv]";
		AddText(AddTxt);
	} else {
		txt=prompt('URL:',"http://");
		if(txt!=null) {
			AddTxt="[wmv]"+txt;
			AddText(AddTxt);
			AddTxt="[/wmv]";
			AddText(AddTxt);
		}
	}
}*/
function wmv() {
	if (helpmode){
	    alert(I18N['setwmv']);
	} else if (nomode || document.selection && document.selection.type == "Text") {
	    AddTxt="[wmv=314,256,1]"+text+"[/wmv]";
	    AddText(AddTxt);
	} else {
	    txt2=prompt(I18N['wha'],"314,256,1");
	    if (txt2!=null) {
		    txt=prompt('URL:',"http://");
		    if (txt!=null) {
			    if (txt2=="") {
				    AddTxt="[wmv]"+txt;
				    AddText(AddTxt);
				    AddTxt="[/wmv]";
				    AddText(AddTxt);
			    } else {
				    AddTxt="[wmv="+txt2+"]"+txt;
				    AddText(AddTxt);
				    AddTxt="[/wmv]";
				    AddText(AddTxt);
			    }
		    }
	    }
	}
}


function showurl() {
 	if (helpmode){
		alert(I18N['seturl']);
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[url="+text+"]"+text+"[/url]";
		AddText(AddTxt);
	} else {
			txt2=prompt(I18N['urlname'],"");
		if (txt2!=null) {
			txt=prompt('URL:',"http://");
			if (txt2!=null) {
				if (txt2=="") {
					AddTxt="[url]"+txt;
					AddText(AddTxt);
					AddTxt="[/url]";
					AddText(AddTxt);
				} else {
					if(txt==""){
						AddTxt="[url]"+txt2;
						AddText(AddTxt);
						AddTxt="[/url]";
						AddText(AddTxt);
					} else{
						AddTxt="[url="+txt+"]"+txt2;
						AddText(AddTxt);
						AddTxt="[/url]";
						AddText(AddTxt);
					}
				}
			}
		}
	}
}

function showcode() {
	if (helpmode) {
		alert(I18N['setcode']);
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[code]"+text+"[/code]";
		AddText(AddTxt);
	} else {
		txt=prompt(I18N['codeword'],"");
		if (txt!=null) { 
			AddTxt="[code]"+txt;
			AddText(AddTxt);
			AddTxt="[/code]";
			AddText(AddTxt);
		}
	}
}

function list() {
	if (helpmode) {
		alert(I18N['setlist']);
	} else if (nomode) {
		AddTxt="[list][li][/li][li][/li][/list]";
		AddText(AddTxt);
	} else {
		txt=prompt(I18N['listtype'],"");
		while ((txt!="") && (txt!="A") && (txt!="a") && (txt!="1") && (txt!=null)) {
			txt=prompt(I18N['listerror'],"");
		}
		if (txt!=null) {
			if (txt==""){
				AddTxt="[list]";
			} else if (txt=="1") {
				AddTxt="[list=1]";
			} else if(txt=="a") {
				AddTxt="[list=a]";
			}
			ltxt="1";
			while ((ltxt!="") && (ltxt!=null)) {
				ltxt=prompt(I18N['listitem'],"");
				if (ltxt!="") {
					AddTxt+="[li]"+ltxt+"[/li]";
				}
			}
			AddTxt+="[/list]";
			AddText(AddTxt);
		}
	}
}
function underline() {
  	if (helpmode) {
		alert(I18N['underline']);
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[u]"+text+"[/u]";
		AddText(AddTxt);
	} else {
		txt=prompt(I18N['underlineword'],I18N['word']);
		if (txt!=null) {
			AddTxt="[u]"+txt;
			AddText(AddTxt);
			AddTxt="[/u]";
			AddText(AddTxt);
		}
	}
}

function setswf() {
 	if (helpmode){
		alert(I18N['setflash']);
	} else if (nomode || document.selection && document.selection.type == "Text") {
		AddTxt="[flash=400,300]"+text+"[/flash]";
		AddText(AddTxt);
	} else {
			txt2=prompt(I18N['flashsize'],"400,300");
		if (txt2!=null) {
			txt=prompt('URL:',"http://");
			if (txt!=null) {
				if (txt2=="") {
					AddTxt="[flash=400,300]"+txt;
					AddText(AddTxt);
					AddTxt="[/flash]";
					AddText(AddTxt);
				} else {
					AddTxt="[flash="+txt2+"]"+txt;
					AddText(AddTxt);
					AddTxt="[/flash]";
					AddText(AddTxt);
				}
			}
		}
	}
}

function add_title(addTitle)
{ 
	var revisedTitle; 
	var currentTitle = document.FORM.atc_title.value; 
	revisedTitle =addTitle+ currentTitle; 
	document.FORM.atc_title.value=revisedTitle; 
	document.FORM.atc_title.focus(); 
	return;
}

function copytext(theField){
	var tempval=eval("document."+theField);
	tempval.focus();
	tempval.select();
	therange=tempval.createTextRange();
	therange.execCommand("Copy");
}

function replac(){
	if (helpmode)
	{
		alert(I18N['setsearch']);
	}
	else
	{
		txt2=prompt(I18N['search'],"");
		if (txt2 != null)
		{
			if (txt2 != "") 
			{
				txt=prompt(I18N['replace'],txt2);
			}
			else
			{
				replac();
			}
			var Rtext = txt2; var Itext = txt;
			Rtext = new RegExp(Rtext,"g");
			document.FORM.atc_content.value =document.FORM.atc_content.value.replace(Rtext,Itext);
		}
	}
}
function addattach(aid){
	AddTxt=' [attachment='+aid+'] ';
	AddText(AddTxt);
}
function addsmile(NewCode){
    document.FORM.atc_content.value += ' [s:'+NewCode+'] ';
}
cnt = 0;
function quickpost(event){
	if((event.ctrlKey && event.keyCode == 13)||(event.altKey && event.keyCode == 83)){
		 cnt++;
		 if(cnt==1){
			 this.document.FORM.submit();
		 }else{
			 alert('Submission Processing. Please Wait');
		 }
	}
}
function Dialog(url, action, init) {
	if (typeof init == "undefined") {
		init = window;
	}
	Dialog._geckoOpenModal(url, action, init);
};
Dialog._parentEvent = function(ev) {
	if (Dialog._modal && !Dialog._modal.closed) {
		Dialog._modal.focus();
		WYSIWYD._stopEvent(ev);
	}
};
Dialog._return = null;
Dialog._modal = null;
Dialog._arguments = null;

Dialog._geckoOpenModal = function(url, action, init) {
	var dlg = window.open(url, "hadialog",
			      "toolbar=no,menubar=no,personalbar=no,top=200,left=300,width=10,height=10," +
			      "scrollbars=no,resizable=yes");
	Dialog._modal = dlg;
	Dialog._arguments = init;
	Dialog._return = function (val) {
		if (val && action) {
			action(val);
		}
		Dialog._modal = null;
	};
};
function saletable(url) {
	Dialog(url, function(param) {
		AddText(param);
		return true;
	}, null);
}
function softtable(url){
	Dialog(url, function(param) {
		AddText(param);
		return true;
	}, null);
}