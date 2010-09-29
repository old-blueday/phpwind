/**
 *inherit from WPanel
 */
~
function()
{
    var $ = function(s)
    {
        return document.getElementById(s);
    };
    PW.SettingsMenu = function()
    {
        this.items = MAIN_BLOCK;
		this.module = '\
						<dt id="{id}-shortcut">\
							<a href="javascript:;">\
								<div>{text}</div>\
							</a>\
						</dt>';
    };
	PW.SettingsMenu.prototype.remove=function()
	{
		this.menu.hide();
	};
    PW.SettingsMenu.prototype.render = function()
    {
        var _this = this;
        $('settingsMenu').onclick = function()
        {

            var p = new PW.WPanel({
                width: 200,
                height: 300
            });
			_this.menu=p;
            p.render($('settingsMenu')).setHTML($('settingsPanel').innerHTML);
			p.element.style.zIndex=100;
        };
		return this;
    };
} ();