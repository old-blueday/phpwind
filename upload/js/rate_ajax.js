var rate = {

        voting : function(id, url, objectid, optionid, typeid, authorid){
            //document.onreadystatechange = function(){
                    if(is_ie && document.readyState=="complete"){
                        rate._voting(id, url, objectid, optionid, typeid, authorid);
                    }else{
                    	rate._voting(id, url, objectid, optionid, typeid, authorid);
					}		
            //}
        },

	_voting : function(id, url, objectid, optionid, typeid, authorid) {
		getObj(id).innerHTML = rate.voteLoading();
		ajax.send(url, "job=vote&objectid=" + objectid + "&typeid=" + typeid
				+ "&optionid=" + optionid + "&authorid=" + authorid,
				function() {
					getObj(id).innerHTML = ajax.request.responseText;
					initRateCredit();
				});
		return true;
	},

        getVote:function(id, url, data){
            rate._getVote(id, url, data);
        },

	_getVote : function(id, url, data) {
		getObj(id).innerHTML = rate.voteLoading();
		ajax.send(url, data, function() {
			getObj(id).innerHTML = ajax.request.responseText;
		});
		return true;
	},

	voteLoading : function() {
		return '<div class="c"></div><div class="mood-bg"><div class="mood-bg2"><div style="padding:30px;"><img src="images/loading.gif" align="absmiddle" /> 加载中...</div></div></div><div class="c"></div>';
	},
	
	showWeekMore : function(){
		if(getObj("more")){
			document.getElementById('more').style.display=(document.getElementById('more').style.display =='none')?'':'none';
		}
	}
}

timecredit=3;
var ns = (document.layers);
var ie = (document.all);
var w3 = (document.getElementById && !ie);
adCount = 0;
function initRateCredit() {
	if (!ns && !ie && !w3) {
		return;
	}
	adDiv = getObj("ratetips").style;
	showcredit();
	if (ie||w3) {
		adDiv.visibility="visible";
	} else {
		adDiv.visibility ="show";
	}
}
function showcredit() {
	if (adCount < timecredit * 10) {
		adCount+=1;
		setTimeout("showcredit()",100);
	} else {
		closecredit();
	}
}
function closecredit(){
	if (ie||w3) {
		adDiv.display = "none";
	} else {
		adDiv.visibility = "hide";
	}
}
