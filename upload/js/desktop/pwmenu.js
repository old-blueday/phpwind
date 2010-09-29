/**
 *include core.js menu.js inherit from menu
 */
~
function()
{
    PW.PWMenu = PW.Menu.extend({
        handler: function(items)
        {
            PW.Dialog(items);
        },
        _createClass: function() //sub menu
        {
            return new PW.PWMenu();
        }
    });

} ();