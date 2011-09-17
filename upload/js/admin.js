function displayElement(elementId,buttonId,isDisplay) {
	if (undefined == isDisplay && typeof buttonId === 'string') {
		getObj(elementId).style.display = getObj(elementId).style.display == 'none' ? '' : 'none';	
		getObj(buttonId).innerHTML=getObj(elementId).style.display == ''?'简单搜索' : '高级搜索';
	} else {
		getObj(elementId).style.display = isDisplay ? '' : 'none';
	}
}

function atccheck()
{
	if(document.FORM.atc_title.value==''){
		alert('标题为空');
		document.FORM.atc_title.focus();
		return false;
	} else if(document.FORM.fid.value==''){
		alert('没有选择文章所属分类');
		document.FORM.fid.focus();
		return false;
	}
	_submit();
}

function checkhackset(chars)
{
	if(!confirm("确定要卸载此插件吗? 如果您卸载了此插件,程序将自动删除插件相关文件，请确认!"))
		return false;
	location.href=chars;
}

function level_jump(admin_file)
{
	var URL=document.mod.selectfid.options[document.mod.selectfid.selectedIndex].value;
	location.href=admin_file+"?adminjob=level&action=editgroup&gid="+URL;selectfid='_self';
}

function checkgroupset(chars)
{
	if(!confirm("确定删除吗? 如果您删除了此用户组,请到论坛缓存数据管理更新用户头衔缓冲!"))
		return false;
	window.location.href=chars;return false;
}

function report_jump(admin_file){
	var URL=document.form1.type.options[document.form1.type.selectedIndex].value;
	location.href=admin_file+"?adminjob=report&type="+URL;
}