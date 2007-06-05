function MenuClass(objMenu)
{
	//Config
	this.config = 
	{ 
		'Level1': 
		{
			'left': 10,
			'width': 75,
			'height': 75,
			'spacing': 5
		},
		'Level2': 
		{
			'left': 10,
			'width': 100,
			'height': 20,
			'spacing': 5
		},
		'waitOpen': 250,
		'waitCloseLevel': 500,
		'waitClose': 3000
	};
	
	this.objMenu = objMenu;
	
	this.Render = function()
	{
		var strKey;
		var objNode;
		var elmNode;
		var top = this.config.Level1.spacing;
		
		//Find menu container
		elmMenu = document.getElementById('VixenMenu');
		elmMenu.style['overflow'] = 'visible';
		
		//Render the initial menu (top-level)
		for (strKey in objMenu)
		{
			//Build new element
			objNode = document.createElement('div');
			//objNode.setAttribute('className', '');
			objNode.setAttribute('Id', 'VixenMenu_' + strKey);
			
			//Attach to elmMenu
			elmMenu.appendChild(objNode);
			elmNode = document.getElementById('VixenMenu_' + strKey);
			
			//Add styles
			//new_node.style[c_attrib] = value
			elmNode.style['top'] = top;
			elmNode.style['border'] = '1px solid'; 
			elmNode.style['left'] =	this.config.Level1.left; 
			elmNode.style['width'] = this.config.Level1.width;
			elmNode.style['height'] = this.config.Level1.height;
			elmNode.style['position'] = 'absolute';
			top = top + this.config.Level1.height + this.config.Level1.spacing;
			
			//Add events
			elmNode.onclick = function(event) {vixen.menu.HandleClick(this)};
			elmNode.onmouseover = function(event) {vixen.menu.HandleMouseOver(this)};
			elmNode.onmouseout = function(event) {vixen.menu.HandleMouseOut()};
			
			//Add some more crap
			elmNode.action = objMenu[strKey];
			elmNode.level = 1;
		}
	}
	
	this.RenderSubMenu = function(elmMenuItem)
	{
		var strKey;
		var objNode;
		var elmNode;
		var top = 0;
		
		if (typeof(elmMenuItem) == 'string')
		{
			elmMenuItem = document.getElementById(elmMenuItem);	
		}
		
		var object = document.getElementById('VixenMenu__' + elmMenuItem.level);
		if (object)
		{
			object.parentNode.removeChild(object);
		}
		
		//Create and attach the container div for the rest of the submenu to sit in
		var objContainer = document.createElement('div');
		objContainer.setAttribute('Id', 'VixenMenu__' + elmMenuItem.level);
		elmMenuItem.parentNode.appendChild(objContainer);
		var elmContainer = document.getElementById('VixenMenu__' + elmMenuItem.level);
		elmContainer.style['top'] = this.RemovePx(elmMenuItem.style['top']);
		elmContainer.style['left'] = this.RemovePx(elmMenuItem.style['left']) + this.RemovePx(elmMenuItem.style['width']) + this.config.Level2.spacing;
		elmContainer.style['position'] = 'absolute';
		elmContainer.style['overflow'] = 'visible';
		
		//Render the initial menu (top-level)
		for (strKey in elmMenuItem.action)
		{
			//Build new element
			objNode = document.createElement('div');
			//objNode.setAttribute('className', '');
			objNode.setAttribute('Id', elmMenuItem.id + "_" + strKey);
			
			//Attach to elmMenu
			elmContainer.appendChild(objNode);
			elmNode = document.getElementById(elmMenuItem.id + "_" + strKey);
			
			//Add styles
			//new_node.style[c_attrib] = value
			elmNode.style['top'] = top;
			elmNode.style['border'] = '1px solid'; 
			elmNode.style['left'] =	this.config.Level2.left; 
			elmNode.style['width'] = this.config.Level2.width;
			elmNode.style['height'] = this.config.Level2.height;
			elmNode.style['position'] = 'absolute';
			top = top + this.config.Level2.height + this.config.Level2.spacing;
			
			//Add events
			elmNode.onclick = function(event) {vixen.menu.HandleClick(this)};
			elmNode.onmouseover = function(event) {vixen.menu.HandleMouseOver(this)};
			elmNode.onmouseout = function(event) {vixen.menu.HandleMouseOut()};
			
			//Add some more crap
			elmNode.action = elmMenuItem.action[strKey];
			elmNode.level = elmMenuItem.level + 1;
		}
	}
	
	this.Close = function(intLevel)
	{
		var object = document.getElementById('VixenMenu__' + intLevel);
		if (object)
		{
			object.parentNode.removeChild(object);
		}
	}
	
	this.HandleClick = function(objMenuItem)
	{
		clearTimeout(this.timeoutOpen);
		clearTimeout(this.timeoutClose);
		if (typeof(objMenuItem.action) == 'string')
		{
			//Follow the link
			document.location.href = objMenuItem.action;
			
		}
		else if (typeof(objMenuItem.action) == 'object')
		{
			//display submenu
			this.RenderSubMenu(objMenuItem);			
		}
	}
	
	this.HandleMouseOver = function(objMenuItem)
	{
		clearTimeout(this.timeoutClose);
		if (typeof(objMenuItem.action) == 'string')
		{
			this.timeoutOpen = setTimeout("vixen.menu.Close('" + objMenuItem.level + "');", this.config.waitCloseLevel);		
		}
		if (typeof(objMenuItem.action) == 'object')
		{
			clearTimeout(this.timeoutOpen);

			//display submenu
			this.timeoutOpen = setTimeout("vixen.menu.RenderSubMenu('" + objMenuItem.id + "');", this.config.waitOpen);			
		}
	}
	
	this.HandleMouseOut = function()
	{
		clearTimeout(this.timeoutOpen);
		this.timeoutClose = setTimeout("vixen.menu.Close(1)", this.config.waitClose);
	}
	
	this.RemovePx = function(value)
	{
		if (value != Number(value))
		{
				return Number(value.slice(0,-2))
		}
		else
		{
				return Number(value);
		}
	}  
}

vixen = {};
objMenu = 
{
	'Menu1': 'http://www.google.com',
	'Menu2':
	{
		'SubMenu1': 'http://www.digg.com',
		'SubMenu2': 
			{
				'Link': 'http://www.voiptelsystems.com.au'
			}
	},
	'Menu3': 
	{
		'SubMenu1': 'http://www.google.com'
	}
}
vixen.menu = new MenuClass(objMenu);
