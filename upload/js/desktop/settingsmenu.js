/**
 *inherit from WPanel
 */
~
function()
{
    var getObj = function(s)
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
        getObj('settingsMenu').onclick = function()
        {

            var p = new PW.WPanel({
                width: 200,
                height: 300
            });
			_this.menu=p;
            p.render(getObj('settingsMenu')).setHTML(getObj('settingsPanel').innerHTML);
			p.element.style.zIndex=100;
        };
		return this;
    };
} ();