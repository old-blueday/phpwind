function popup()
{
    this._divid='';
    this._isIe='';
    this._isshow=false;
    this.zindex=999;
    this.init = function (divname)
    {
        try
        {
            this._isIe = document.all ? true : false;
            this._divid=divname;
           
            var fstr='<div id="'+this._divid+'" style="z-index:'+this.zindex+';position:absolute;top:-999px;left:-999px;visibility:hidden;">&nbsp;</div>';
            document.write(fstr);
        }
        catch(e)
        {

        }
    }
   
   
   
    this.showDiv = function(text,w,h,dragbarname)
    {
       
        try
        {
            if(this._isshow)
            {
                return ;
            }
            document.getElementById(this._divid).innerHTML=text;
            document.getElementById(this._divid).style.width=w+"px";
            document.getElementById(this._divid).style.height=h+"px";
            this.showbg();
           
            document.getElementById(this._divid).style.visibility="visible";
            this.reposition();
            this._isshow=true;
            try
            {
                if(document.getElementById(dragbarname)!=null)
                {
                    Drag.init(document.getElementById(dragbarname), document.getElementById(this._divid));
                }
            }
            catch(e)
            {
               
            }
        }
        catch(e)
        {

        }
       
    }
    this.hiddenDiv = function ()
    {
        try
        {
            if(!this._isshow)
            {
                return ;
            }
            this.closebg();
            document.getElementById(this._divid).style.visibility="hidden";
           
            this._isshow=false;
        }
        catch(e)
        {
           
        }
    }



    this.showbg = function ()
    {
        try
        {
            this.closebg();
            var bWidth=parseInt(document.documentElement.scrollWidth);
            var bHeight=parseInt(document.documentElement.scrollHeight);
               
            var back=document.createElement("div");
            back.id=this._divid+"_back";
            var styleStr="z-index:"+(this.zindex-1)+";top:0px;left:0px;position:absolute;background:#ccc;width:"+bWidth+"px;height:"+bHeight+"px;";
            styleStr+=(this._isIe)?"filter:alpha(opacity=40);":"opacity:0.40;";
            back.style.cssText=styleStr;
            document.body.appendChild(back);
        }
        catch(e)
        {
   
        }
    }
   
    this.closebg = function ()
    {
        if(document.getElementById(this._divid+'_back')!=null)
        {
            document.getElementById(this._divid+'_back').parentNode.removeChild(document.getElementById(this._divid+'_back'));
        }
    }
   
   
    this.reposition = function ()
    {
        try
        {
            l = (document.documentElement.clientWidth-parseInt(document.getElementById(this._divid).clientWidth))/2 +document.documentElement.scrollLeft
            t = (document.documentElement.clientHeight-parseInt(document.getElementById(this._divid).clientHeight))/2 +document.documentElement.scrollTop

            document.getElementById(this._divid).style.left=l+"px";
            document.getElementById(this._divid).style.top=t+"px";
        }
        catch(e)
        {
           
        }
    }
}
