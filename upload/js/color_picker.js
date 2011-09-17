var color_picker_div = false;
var color_picker_form_field = false;
var rebackfunc = false;
var color_picker = '';

var slider_handle_image = imgpath+'/slider_handle.gif';
var slider_handle_image_obj = false;
var sliderObjectArray = new Array();
var slider_counter = 0;
var slideInProgress = false;
var handle_start_x;
var event_start_x;
var currentSliderIndex;

function pickReback(showid,color){
	if (!showid) showid = 'color_show';
	getObj(showid).style.backgroundColor = color;
}
function styleOnclick(e,obj){
	var objclass = e.className;
	var temp = objclass.split(' ');
	var newclass = '';
	for (var n=0; n<temp.length; n++){
		if (temp[n]=='one') {
			continue;
		}
		newclass += ' ' + temp[n];
	}
	if (objclass.match(/one/)) {
		e.className = newclass;
		getObj(obj).value = '';
	} else {
		e.className = newclass + ' one';
		getObj(obj).value = 1;
	}
}
function colorCancel(){
	getObj('color_show').style.backgroundColor='#FFFFFF';
	if(getObj('css[color]')){
		getObj('css[color]').value='';
	}
}
function checkFileType() {
	var fileName = getObj("uploadpic").value;
	if (fileName != '') {
		var regTest = /\.(jpe?g|gif|png|bmp)$/gi;
		var arrMactches = fileName.match(regTest);
		if (arrMactches == null) {
			getObj('fileTypeError').style.display = '';
			return false;
		} else {
			getObj('fileTypeError').style.display = 'none';
		}
	}
	return true;
}

function showColorPicker(inputObj,formField,reback){
	if(!color_picker_div){
		color_picker_div = document.createElement('DIV');
		color_picker_div = elementBind('div','','pw_menu','display:none;z-index:4000;position:absolute;width:250px;padding-bottom:3px;');
		if(is_ie6){
			var ie6fixed=document.createElement("iframe");
			ie6fixed.style.cssText="z-index:-1;width:250px;height:150px;position:absolute;top:0;left:-1px;filter:Alpha(opacity=0);_filter:Alpha(opacity=0);opacity:.0;";
			color_picker_div.appendChild(ie6fixed);
		}
		document.body.appendChild(color_picker_div);
		createTopRow(color_picker_div);			
		var contentDiv = elementBind('div','color_picker_content');
		color_picker_div.appendChild(contentDiv);			
		createAllColorDiv(contentDiv);
		createBottomRow(color_picker_div);
	}
	if (typeof formField == 'string') {
		formField = getObj(formField);
	}
	if (formField.value && formField.value.match(/\#[0-9A-F]{6}/ig)){
		document.getElementById('color_code').value= formField.value.toUpperCase();
		document.getElementById('color_code').onchange();
	} else {
		document.getElementById('color_code').value='#000000';
		document.getElementById('color_code').onchange();
	}
	var pos = colorPickerGetElementPos(inputObj);
	color_picker_div.style.left =pos.x+"px";
	color_picker_div.style.top = pos.y+inputObj.offsetHeight+"px";
	
	if(color_picker_div.style.display=='none') color_picker_div.style.display='block'; else color_picker_div.style.display='none';
	color_picker_form_field = formField;
	rebackfunc = reback;
}

var colorPickerGetElementPos=function(el){ 
    var ua = navigator.userAgent.toLowerCase(); 
    var isOpera = (ua.indexOf('opera') != -1); 
    var isIE = (ua.indexOf('msie') != -1 && !isOpera);
    if(el.parentNode === null || el.style.display == 'none'){ 
        return false; 
    } 
    var parent = null; 
    var pos = []; 
    var box; 
    if(el.getBoundingClientRect){ 
        box = el.getBoundingClientRect(); 
        var scrollTop = Math.max(document.documentElement.scrollTop, document.body.scrollTop); 
        var scrollLeft = Math.max(document.documentElement.scrollLeft, document.body.scrollLeft); 
 
        return {x:box.left + scrollLeft, y:box.top + scrollTop}; 
    } 
    else if(document.getBoxObjectFor){ 
        box = document.getBoxObjectFor(el); 
            
        var borderLeft = (el.style.borderLeftWidth)?parseInt(el.style.borderLeftWidth):0; 
        var borderTop = (el.style.borderTopWidth)?parseInt(el.style.borderTopWidth):0; 
 
        pos = [box.x - borderLeft, box.y - borderTop]; 
    } else{ 
        pos = [el.offsetLeft, el.offsetTop]; 
        parent = el.offsetParent; 
        if (parent != el) { 
            while (parent) { 
                pos[0] += parent.offsetLeft; 
                pos[1] += parent.offsetTop; 
                parent = parent.offsetParent; 
            } 
        } 
        if (ua.indexOf('opera') != -1  
            || ( ua.indexOf('safari') != -1 && el.style.position == 'absolute' ))  
        { 
                pos[0] -= document.body.offsetLeft; 
                pos[1] -= document.body.offsetTop; 
        }  
    } 
    if (el.parentNode) { parent = el.parentNode; } else { parent = null; } 
    while (parent && parent.tagName != 'BODY' && parent.tagName != 'HTML'){ 
        pos[0] -= parent.scrollLeft; 
        pos[1] -= parent.scrollTop; 
   
        if (parent.parentNode) { parent = parent.parentNode; }  
        else { parent = null; } 
    } 
    return {x:pos[0], y:pos[1]}; 
}

function createTopRow(inputObj){
	var tabDiv = document.createElement('div');
	tabDiv.style.left = '0px';
	tabDiv.style.position = 'absolute';
	tabDiv.style.cssText = 'height:14px;padding-left:4px;';
	var closeButton = elementBind('div','','','position: absolute; right: 1px; top: 1px; cursor: pointer; width: 18px; height: 18px;background:url('+imgpath+'/close.gif) no-repeat');
	closeButton.onclick = function(){color_picker_div.style.display='none';};
	tabDiv.appendChild(closeButton);
	inputObj.appendChild(tabDiv);
}

var fast_color = new Array('#FF0000','#008000','#0000FF','#FFA500','#FF00FF','#FF1493','#FFFF00','#D8BFD8','#FFC0CB','#FFE4E1','#00FFFF','#808080','#008080','#6B8E23','#4B0082','#000000');
function createBottomRow(inputObj){
	var tabDiv = document.createElement('DIV');
	tabDiv.style.left = '0px';
	tabDiv.style.position = 'absolute';
	tabDiv.style.cssText = 'height:16px;padding-left:4px;';
	for (var no =0 ; no<fast_color.length ; no++) {
		var div = document.createElement('DIV');
		var color = fast_color[no];
		div.style.cssText = 'background-color:'+color+';margin-left:1px;margin-bottom:1px;float:left;border:1px solid #000;cursor:pointer;width: 12px;height: 12px;';
		div.title = fast_color[no];	
		div.onclick = chooseColor;
		tabDiv.appendChild(div);
	}
	inputObj.appendChild(tabDiv);
}

function chooseColor(){
	color_picker_form_field.value = this.title;
	color_picker = this.title;
	color_picker_div.style.display='none';
	goBackFunction();
}

function goBackFunction(){
	if (rebackfunc && typeof rebackfunc == 'function') {
		rebackfunc();	
	}
}

function pickAjaxReback(color){
	getObj('color_show').style.backgroundColor = color;
}

function createAllColorDiv(inputObj){
	var allColorDiv = addChild(inputObj,'div','','','padding:5px 0px 5px 3px');
	var labelDiv = elementBind('div','','','width:15px;height:20px;float:left;font-size:11px;font-weight:bold;');
	labelDiv.innerHTML = 'R';
	allColorDiv.appendChild(labelDiv);
	var innerDiv = addChild(allColorDiv,'div','sliderRedColor','','width:175px;height:20px;float:left;');

	var innerDivInput = elementBind('div','','','width:45px;height:20px;float:left;');
	
	var input = elementBind('input','red_color','input','width:45px;font-size:11px;');
	input.maxlength = 3;
	input.name = 'redColor';
	input.value = 0;
	
	innerDivInput.appendChild(input);
	allColorDiv.appendChild(innerDivInput);
	var labelDiv = elementBind('div','','','width:15px;height:20px;float:left;font-size:11px;font-weight:bold;');
	labelDiv.innerHTML = 'G';
	allColorDiv.appendChild(labelDiv);
	var innerDiv = addChild(allColorDiv,'div','sliderGreenColor','','width:175px;height:20px;float:left;');
	var innerDivInput = elementBind('div','','','width:45px;height:20px;float:left;');
	

	var input = elementBind('input','green_color','input','width:45px;font-size:11px;');
	input.maxlength = 3;
	input.name = 'GreenColor';
	input.value = 0;
	
	innerDivInput.appendChild(input);
	allColorDiv.appendChild(innerDivInput);
	var labelDiv = elementBind('div','','','width:15px;height:20px;float:left;font-size:11px;font-weight:bold;');
	labelDiv.innerHTML = 'B';
	allColorDiv.appendChild(labelDiv);			
	var innerDiv = addChild(allColorDiv,'div','sliderBlueColor','','width:175px;height:20px;float:left;');
	var innerDivInput = elementBind('div','','','width:45px;height:20px;float:left;');
	
	var input = elementBind('input','blue_color','input','width:45px;font-size:11px;');
	input.maxlength = 3;
	input.name = 'BlueColor';
	input.value = 0;
	
	innerDivInput.appendChild(input);
	allColorDiv.appendChild(innerDivInput);

	var colorPreview = elementBind('div','colorPreview','','background:#000000;width:186px;margin-right:2px;margin-top:1px;border:1px solid #CCC;float:left;cursor:pointer;height:20px;');
	colorPreview.innerHTML = '<span></span>';
	colorPreview.title = '点击选择颜色';
	allColorDiv.appendChild(colorPreview);
	colorPreview.onclick = chooseColorSlider;
	var colorCodeDiv = elementBind('div','','','width:50px;height:20px;float:left;');
	var input = elementBind('input','color_code','input','width:45px;font-size:11px;');
	colorCodeDiv.appendChild(input);
	input.maxLength = 7;
	input.value = '#000000';
	input.onchange = setPreviewColorFromTxt;
	input.onblur = setPreviewColorFromTxt;


	allColorDiv.appendChild(colorCodeDiv);
	var clearingDiv = document.createElement('DIV');
	clearingDiv.style.clear = 'both';
	allColorDiv.appendChild(clearingDiv);
	form_widget_amount_slider('sliderRedColor',document.getElementById('red_color'),170,0,255,"setColorByRGB()");
	form_widget_amount_slider('sliderGreenColor',document.getElementById('green_color'),170,0,255,"setColorByRGB()");
	form_widget_amount_slider('sliderBlueColor',document.getElementById('blue_color'),170,0,255,"setColorByRGB()");
}

function form_widget_amount_slider(targetElId,formTarget,width,min,max,onchangeAction){
	if(!slider_handle_image_obj){
		getImageSliderHeight();
	}
	slider_counter = slider_counter +1;
	sliderObjectArray[slider_counter] = new Array();
	sliderObjectArray[slider_counter] = {"width":width-9,"min":min,"max":max,"formTarget":formTarget,"onchangeAction":onchangeAction};
	formTarget.setAttribute('sliderIndex',slider_counter);

	var parentObj = addChild(document.getElementById(targetElId),'div','slider_container' + slider_counter,'','position:relative;height:12px;width:'+width+'px;');
	
	var obj = elementBind('div','slider_slider' + slider_counter,'','border-top:1px solid #9d9c99;border-left:1px solid #9d9c99;border-bottom:1px solid #eee;border-right:1px solid #eee;background-color:#f0ede0;position:absolute;bottom:0px;width:3px;height:3px;position:absolute;bottom:0px;width:'+width+'px;');
	obj.innerHTML = '<span></span>';
	parentObj.appendChild(obj);

	var handleImg = elementBind('IMG','slider_handle' + slider_counter,'','position:absolute;z-index:5');
	handleImg.style.left = '0px';
	handleImg.src = slider_handle_image_obj.src;
	handleImg.onmousedown = initMoveSlider;
	if(document.body.onmouseup){
		if(document.body.onmouseup.toString().indexOf('stopMoveSlider')==-1){
			alert('You allready have an onmouseup event assigned to the body tag');
		}
	}else{
		document.body.onmouseup = stopMoveSlider;
		document.body.onmousemove = startMoveSlider;
	}
	handleImg.ondragstart = form_widget_cancel_event;
	parentObj.appendChild(handleImg);
	positionSliderImage(false,slider_counter);
	onpropertychange_f(formTarget,positionSliderImage);
}

function onpropertychange_f(e_, func_f){
	if (is_ie) {
		e_.onpropertychange = func_f;
		return;
	}
	if (document.addEventListener) {
		e_.addEventListener("input", func_f, false);
		return;
	} else if (document.attachEvent) {
		e_.attachEvent("onpropertychange",func_f);
		return;
	}
}

function form_widget_cancel_event(){
	return false;
}

function chooseColorSlider(){
	color_picker_form_field.value = document.getElementById('color_code').value;
	color_picker = color_picker_form_field.value;
	color_picker_div.style.display='none';
	goBackFunction();
}
function setPreviewColorFromTxt(){
	if(this.value.match(/\#[0-9A-F]{6}/g)){
		document.getElementById('colorPreview').style.backgroundColor=this.value;
		var r = this.value.substr(1,2);
		var g = this.value.substr(3,2);
		var b = this.value.substr(5,2);
		document.getElementById('red_color').value = baseConverter(r,16,10);
		document.getElementById('green_color').value = baseConverter(g,16,10);
		document.getElementById('blue_color').value = baseConverter(b,16,10);
		
		positionSliderImage(false,1,document.getElementById('red_color'));
		positionSliderImage(false,2,document.getElementById('green_color'));
		positionSliderImage(false,3,document.getElementById('blue_color'));
	}
}
function positionSliderImage(e,theIndex,inputObj){
	if(this)inputObj = this;
	if(!theIndex)theIndex = inputObj.getAttribute('sliderIndex');
	var handleImg = document.getElementById('slider_handle' + theIndex);
	var ratio = sliderObjectArray[theIndex]['width'] / (sliderObjectArray[theIndex]['max']-sliderObjectArray[theIndex]['min']);
	var temp = sliderObjectArray[theIndex]['formTarget'].value - 0;
	if (temp > 255) {
		sliderObjectArray[theIndex]['formTarget'].value = 255;
	} else if (temp < 0) {
		sliderObjectArray[theIndex]['formTarget'].value = 0;
	}
	var currentValue = sliderObjectArray[theIndex]['formTarget'].value-sliderObjectArray[theIndex]['min'];
	handleImg.style.left = currentValue * ratio + 'px';
	setColorByRGB();
}

function baseConverter (number,ob,nb){
	number = number + "";
	number = number.toUpperCase();
	var list = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	var dec = 0;
	for (var i = 0; i <=  number.length; i++) {
		dec += (list.indexOf(number.charAt(i))) * (Math.pow(ob , (number.length - i - 1)));
	}
	number = "";
	var magnitude = Math.floor((Math.log(dec))/(Math.log(nb)));
	for (var i = magnitude; i >= 0; i--) {
		var amount = Math.floor(dec/Math.pow(nb,i));
		number = number + list.charAt(amount); 
		dec -= amount*(Math.pow(nb,i));
	}
	if(number.length==0)number=0;
	return number;
}
function setColorByRGB(){
	var formObj = document.forms[0];	
	var r = document.getElementById('red_color').value.replace(/[^\d]/,'');
	var g = document.getElementById('green_color').value.replace(/[^\d]/,'');
	var b = document.getElementById('blue_color').value.replace(/[^\d]/,'');		
	if(r/1>255)r=255;
	if(g/1>255)g=255;
	if(b/1>255)b=255;
	r = baseConverter(r,10,16) + '';
	g = baseConverter(g,10,16) + '';
	b = baseConverter(b,10,16) + '';
	if(r.length==1)r = '0' + r;
	if(g.length==1)g = '0' + g;
	if(b.length==1)b = '0' + b;

	document.getElementById('colorPreview').style.backgroundColor = '#' + r + g + b;
	document.getElementById('color_code').value = '#' + r + g + b;		
}

function initMoveSlider(e){
	if(document.all)e = event;
	slideInProgress = true;
	event_start_x = e.clientX;
	handle_start_x = this.style.left.replace('px','');
	currentSliderIndex = this.id.replace(/[^\d]/g,'');
	return false;
}

function startMoveSlider(e){
	if(document.all)e = event;
	if(!slideInProgress)return;
	var leftPos = handle_start_x/1 + e.clientX/1 - event_start_x;
	if(leftPos<0)leftPos = 0;
	if(leftPos/1>sliderObjectArray[currentSliderIndex]['width'])leftPos = sliderObjectArray[currentSliderIndex]['width'];
	document.getElementById('slider_handle' + currentSliderIndex).style.left = leftPos + 'px';
	adjustFormValue(currentSliderIndex);
	if(sliderObjectArray[currentSliderIndex]['onchangeAction']){
		eval(sliderObjectArray[currentSliderIndex]['onchangeAction']);
	}
}

function stopMoveSlider(){
	slideInProgress = false;
}

function getImageSliderHeight(){
	if(!slider_handle_image_obj){
		slider_handle_image_obj = new Image();
		slider_handle_image_obj.src = slider_handle_image;
	}
	if(slider_handle_image_obj.width>0){
		return;
	}else{
		setTimeout('getImageSliderHeight()',50);
	}
}
function adjustFormValue(theIndex){
	var handleImg = document.getElementById('slider_handle' + theIndex);
	var ratio = sliderObjectArray[theIndex]['width'] / (sliderObjectArray[theIndex]['max']-sliderObjectArray[theIndex]['min']);
	var currentPos = handleImg.style.left.replace('px','');
	sliderObjectArray[theIndex]['formTarget'].value = Math.round(currentPos / ratio) + sliderObjectArray[theIndex]['min'];
}