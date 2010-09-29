var months = new Array("一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月");
var days   = new Array(31,28,31,30,31,30,31,31,30,31,30,31);
var weeks  = new Array("日","一","二","三","四","五","六");
var today;
var pX;
var pY;
var seltd;
var prebackground;
var precolor;
//document.writeln("<div id='Calendar' style='position:absolute; z-index:9999; visibility: hidden;'></div>");

function getDays(month,year){
    if(1 == month){
        return ((0 == year % 4) && (0 != (year % 100))) || (0 == year % 400) ? 29 : 28;
    }else{
        return days[month];
	}
}
function getToday(type){
    var date  = new Date();
    this.year = date.getFullYear();
    this.month= date.getMonth();
    this.day  = date.getDate();
	if(type == 1){
		this.hour = date.getHours();
		this.minute = date.getMinutes();
		if (this.hour < 10) this.hour = '0'+String(this.hour);
		if (this.minute < 10) this.minute = '0'+String(this.minute);
	}
}
function getSelectDay(str,type){
    var str=str.split("-");
	var str2 = str[2].split(" ");

    if(type == 1){
		var str3 = trim(str2[1]).split(":");
		var date = new Date(parseFloat(str[0]),parseFloat(str[1])-1,parseFloat(str[2]),parseFloat(str3[0]),parseFloat(str3[1]));
		this.hour = date.getHours();
		this.minute = date.getMinutes();
		if (this.hour < 10) this.hour = '0'+String(this.hour);
		if (this.minute < 10) this.minute = '0'+String(this.minute);
	}else{
		var date  = new Date(parseFloat(str[0]),parseFloat(str[1])-1,parseFloat(str[2]));
	}
	this.year = date.getFullYear();
	this.month= date.getMonth();
	this.day  = date.getDate();
}

function ShowDays() {
	var obj_Year =getObj('Year');
	var obj_Month=getObj('Month');

    var parseYear = parseInt(obj_Year.options[obj_Year.selectedIndex].value);
    var Seldate = new Date(parseYear,obj_Month.selectedIndex,1);
    var day = -1;
    var startDay = Seldate.getDay();
    var daily = 0;

    if((today.year == Seldate.getFullYear()) &&(today.month == Seldate.getMonth())){
        day = today.day;
	}
    var tableDay = getObj('Day');
    var DaysNum  = getDays(Seldate.getMonth(),Seldate.getFullYear());
    for(var intWeek = 1;intWeek < tableDay.rows.length;intWeek++){
        for(var intDay = 0;intDay < tableDay.rows[intWeek].cells.length;intDay++){
            var cell = tableDay.rows[intWeek].cells[intDay];
            if(intDay == startDay && 0 == daily){
                daily = 1;
			}
            if(daily > 0 && daily <= DaysNum){
				cell.style.cssText = 'cursor:pointer;border-right:1px solid #BBBBBB; border-bottom:1px solid #BBBBBB; color:#215DC6; font-family:Verdana; font-size:12px';
				if(day==daily){
					prebackground = cell.style.background;
					precolor = cell.style.color;
					cell.style.background='#006699';
					cell.style.color='#FFFFFF';
					seltd = cell;
				} else if(intDay==6){
					cell.style.color='green';
				} else if(intDay==0){
					cell.style.color='red';
				}
				cell.innerHTML = daily;
                daily++;
            } else{
				cell.style.cssText = '';
                cell.innerHTML = '';
			}
        }
	}
}

function GetDate(idname,e,type){
    var sDate;
	var getElement = is_ie ? event.srcElement : e.target;
    if(getElement.tagName == "TD"){
        if(getElement.innerHTML != ""){
			if(type == 1){
				seltd.style.background = prebackground;
				seltd.style.color = precolor;
				prebackground = getElement.style.background;
				precolor = getElement.style.color;
				getElement.style.background='#006699';
				getElement.style.color='#FFFFFF';
				seltd = getElement;
			}else{
				sDate = getObj('Year').value + "-" + getObj('Month').value + "-" + getElement.innerHTML;
				getObj(idname).value=sDate;
				getObj(idname).onblur && getObj(idname).onblur();
           		HiddenCalendar();
			}
        }
	}
}

function GetDate2(idname){
	sDate = getObj('Year').value + "-" + getObj('Month').value + "-" + seltd.innerHTML + " " + getObj('Hour').value + ":" + getObj('Minute').value;
	getObj(idname).value=sDate;
	getObj(idname).onblur && getObj(idname).onblur();
    HiddenCalendar();
}

function HiddenCalendar(){
    getObj('Calendar').style.visibility='hidden';
}

function ShowCalendar(idname,type){
    var x,y,i,intWeeks,intDays;
    var table;
    var year,month,day;
    var obj=getObj(idname);
    var thisyear;

    thisyear=new Date();
    thisyear=thisyear.getFullYear();

    today = obj.value;
    if(isDate(today,type)){
        today = new getSelectDay(today,type);
	}else{
        today = new getToday(type);
	}

    /*x=obj.offsetLeft;
    y=obj.offsetTop;
    while(obj=obj.offsetParent){
        x+=obj.offsetLeft;
        y+=obj.offsetTop;
    }*/
	if (getObj('Calendar')) {
		var Cal=getObj('Calendar');
	} else {
		var Cal = elementBind('div','Calendar','','position:absolute; z-index:9999; visibility: hidden;');
		document.body.appendChild(Cal);
	}

	//var Cal=getObj('Calendar');
	var rect = obj.getBoundingClientRect();
    Cal.style.left=rect.left+2+ietruebody().scrollLeft+'px';
    Cal.style.top=rect.top+ietruebody().scrollTop+20+'px';
    Cal.style.visibility="visible";

    table ="<iframe frameborder='0' style='position:absolute;top:0;width:250px;height:200px;filter:Alpha(opacity=0);_filter:Alpha(opacity=0);opacity:.0;'></iframe>";
    table+="<table border='0' cellspacing='0' style='position:absolute;width:250px;background:#fff;line-height:1.5;border:1px solid #a9d5e9;'>";
    table+="<tr>";
    table+="<td class=\"h\">";
    table+="<span class=\"fr cp\" title='关闭' onClick='javascript:HiddenCalendar()'><img src=\""+ imgpath +"/close.gif\" alt=\"close\" /></span>";
    table+="<select name='Year' id='Year' onChange='ShowDays()' style='font-family:Verdana; font-size:12px'>";
    for(i = thisyear - 35;i < (thisyear + 5);i++){
        table+="<option value=" + i + " " + (today.year == i ? "Selected" : "") + ">" + i + "</option>";
	}
	table+="</select>";

    table+="<select name='Month' id='Month' onChange='ShowDays()' style='font-family:Verdana; font-size:12px'>";
    for(i = 0;i < months.length;i++){
        table+="<option value= " + (i + 1) + " " + (today.month == i ? "Selected" : "") + ">" + months[i] + "</option>";
	}

	table+="</select>";
    table+="</td>";
    table+="</tr>";
    table+="<tr><td align='center' style=\"padding:5px;vertical-align:top;\">";
    table+="<table id='Day' border='0' width='100%'>";
    table+="<tr>";

    for(i = 0;i < weeks.length;i++){
        table+="<td align='center' style='font-size:12px;'>" + weeks[i] + "</td>";
	}
	table+="</tr>";

    for(intWeeks = 0;intWeeks < 6;intWeeks++){
        table+="<tr>";
        if (type == 1) {
        	for (intDays = 0;intDays < weeks.length;intDays++){
	        	table+="<td onClick='GetDate(\"" + idname + "\",event,\"1\")' ondblclick='GetDate2(\"" + idname + "\")' align='center'></td>";
			}
	    } else {
        	for (intDays = 0;intDays < weeks.length;intDays++){
	        	table+="<td onClick='GetDate(\"" + idname + "\",event)' align='center'></td>";
			}
        }

        table+="</tr>";
    }
    table+="</table></td></tr>";
	if(type == 1){
		table+="<tr><td align=\"center\" style=\"padding:5px;\"><input class=\"input\" type=\"text\" name=\"Hour\" id=\"Hour\" size=\"1\" value=\""+today.hour+"\"> 点 <input class=\"input\" type=\"text\" name=\"minute\" id=\"Minute\" size=\"1\" value=\""+today.minute+"\"> 分 <input class=\"btn\" type=\"button\" name=\"submit\" value=\"确定\" onClick='GetDate2(\"" + idname + "\")'></td></tr>";
	}
	table+="</table>";
    Cal.innerHTML=table;
    ShowDays();
}

function isDate(dateStr,type){
	if(type == 1){
		var datePat = /^(\d{4})(\-)(\d{1,2})(\-)(\d{1,2})(\s{1})(\d{1,2})(\:)(\d{1,2})/;
		var matchArray = dateStr.match(datePat);
		if (matchArray == null) return false;
		var hour = matchArray[7];
		var minute = matchArray[9];
		if (hour <0 || hour >23) return false;
		if (minute < 0 || minute > 59) return false;
	}else{
		 var datePat = /^(\d{4})(\-)(\d{1,2})(\-)(\d{1,2})/;
		 var matchArray = dateStr.match(datePat);
		 if (matchArray == null) return false;
	}
    var month = matchArray[3];
    var day = matchArray[5];
    var year = matchArray[1];
    if (month < 1 || month > 12) return false;
    if (day < 1 || day > 31) return false;
    if ((month==4 || month==6 || month==9 || month==11) && day==31) return false;
    if (month == 2){
        var isleap = (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0));
        if (day > 29 || (day==29 && !isleap)) return false;
    }
    return true;
}

function trim(s){
	s = s.replace(/^\s+/, '');
	return s.replace(/\s+$/, '');
}