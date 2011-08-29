/**
 * 模态select控件
 */
Breeze.namespace('ui.selector', function(B){
Breeze.require('dom', 'event', function(B){
	
	B.ui || (B.ui={});
	
	function Selector(sel){
		if( !(sel && sel.tagName == 'SELECT') ){
			return;
		}
		var self = this,
		vir = B.createElement('div', {
			class: 'dropselectbox'
			//innerHTML: '<button type="button"></button><ul><li></li></ul>'
		}),
		input = B.createElement('input',{type:'button'},{}),
		list = B.createElement('ul',{},{display:'none','background-color': '#ccc'});
		this.sel = sel;
		
		input.value = this.getSelectedText();
		vir.appendChild(input);
		this.input = input;
		
		vir.appendChild(list);
		this.list = list;
		
		B.insertBefore(vir, sel);
		
		input.style.width = vir.style.width = sel.clientWidth +20 + 'px';
		B.css(sel, 'display', 'none');
		
		B.addEvent( input,'click', self.showOptions.bind(self) );
		B.css(vir,'display','');
	}
	Selector.prototype = {
		getSelectedText: function(){
			var index = this.sel.selectedIndex;
			return this.sel.options[index].text;
		},
		showOptions: function(){
			var opts= '',  ul = this.list;
			ul.style.width = this.input.clientWidth*((this.sel.length>>4)+1)+'px';
			var w = Math.floor(parseInt(ul.style.width)/((this.sel.length>>4)+1))+'px';
			for(var i=0;i<this.sel.length;i++)
			{
				opts += '<li style="width:'+w+';">'+this.sel.options[i].text+'</li>';
			}
			
			ul.innerHTML=opts;
			ul.getElementsByTagName('li')[this.sel.selectedIndex].className='over';
			ul.style.display='block';
			ul.onclick = this.select.bind(this);
			ul.onmouseover = this.mouseOver.bind(this);
			document.body.onmouseover= this.cancel.bind(this);
		},
		findValue: function(txt){
			for(var i=0;i<this.sel.length;i++)
			{
				if(txt==this.sel.options[i].text)
				{
					return this.sel.options[i].value;
				}
			}
			return null;
		},
		select: function(evt)
		{
			evt = evt || window.event;
			var target = evt.target||evt.srcElement;
			if(target.tagName!='LI')
				return false;
			this.sel.value = this.findValue(target.innerHTML);
			this.sel.onchange && this.sel.onchange();
			this.input.value=target.innerHTML;
			this.cancel(evt);
		},
		mouseOver: function(evt)
		{
			var target = evt.target;
			evt.stopPropagation();
			
			if(target.tagName!='LI')
				return false;
			var selli=B.$('li.over',this.list);
			selli && (selli.className='');
			target.className='over';
		},
		cancel: function(evt)
		{
			var ul = this.list;
			document.body.onmousemove=null;
			ul.style.display='none';
			ul.innerHTML='';
			
		}
	}
	B.ui.selector = function(sel){
	 return new Selector(sel);
	}
});
});