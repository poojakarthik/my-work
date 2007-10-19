//----------------------------------------------------------------------------//
// VixenMenuClass
//----------------------------------------------------------------------------//
/**
 * VixenMenuClass
 *
 * Vixen menu class
 *
 * Vixen menu class
 * including Vixen context menu
 *
 *
 *
 * @package	framework_ui
 * @class	Vixen.Menu
 */
function VixenMenuClass(objMenu)
{
	//Config
	this.config = 
	{ 
		'Level1': 
		{
			'left': 10,
			'width': 50,
			'height': 50,
			'spacing': 5
		},
		'Level2': 
		{
			'left': 0,
			'width': 200,
			'height': 20,
			'spacing': 5
		},
		'waitOpen': 0,
		'waitCloseLevel': 500,
		'waitClose': 3000,
		'waitCloseWhenSelected': 400
	};
	
	this.objMenu = objMenu;
	
	this.SetMenu = function(objMenu)
	{
		this.objMenu = objMenu;
	}
	
	this.Render = function()
	{
		//debug ('firsttime');
		var strKey;
		var objNode;
		var elmNode;
		var top = this.config.Level1.spacing;
		
		//Find menu container
		elmMenu = document.getElementById('VixenMenu');
		elmMenu.style['overflow'] = 'visible';
		
		//Render the initial menu (top-level)
		for (strKey in this.objMenu)
		{
			
			//Build new element
			objNode = document.createElement('img');
			objNode.setAttribute('src', 'img/template/' + strKey.toLowerCase().replace(/ /, '_') + '.png');
			
			objNode.setAttribute('className', 'ContextMenuItem');
			objNode.setAttribute('class', 'ContextMenuItem');
			objNode.setAttribute('Id', 'VixenMenu_' + strKey);
			
			//Attach to elmMenu
			elmMenu.appendChild(objNode);
			elmNode = document.getElementById('VixenMenu_' + strKey);
			
			//Add styles
			//new_node.style[c_attrib] = value
			elmNode.style['top'] 				= top;
			elmNode.style['left'] 				= this.config.Level1.left; 
			elmNode.style['width'] 				= this.config.Level1.width;
			elmNode.style['height'] 			= this.config.Level1.height;
			elmNode.style['position'] 			= 'absolute';
			elmNode.style['zIndex']				= 2;
			
			top = top + this.config.Level1.height + this.config.Level1.spacing;
			
			//Add events
			elmNode.onclick = function(event) {Vixen.Menu.HandleClick(this)};
			elmNode.onmouseover = function(event) {Vixen.Menu.HandleMouseOver(this)};
			elmNode.onmouseout = function(event) {Vixen.Menu.HandleMouseOut(this)};
			elmNode.style.cursor = "default";
			
			//Add some more crap
			elmNode.action = this.objMenu[strKey];
			elmNode.level = 1;
		}
	}
	
	this.RenderSubMenu = function(elmMenuItem)
	{
		
		var strKey;
		var objNode;
		var objTextNode;
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
		objContainer.style['top'] = this.RemovePx(elmMenuItem.style['top']);
		objContainer.style['left'] = this.RemovePx(elmMenuItem.style['left']) + this.RemovePx(elmMenuItem.style['width']) + this.config.Level2.spacing;
		objContainer.style['position'] = 'absolute';
		objContainer.style['overflow'] = 'visible';

		elmMenuItem.parentNode.appendChild(objContainer);
		var elmContainer = document.getElementById('VixenMenu__' + elmMenuItem.level);
		//elmContainer.style['top'] = this.RemovePx(elmMenuItem.style['top']);
		//elmContainer.style['left'] = this.RemovePx(elmMenuItem.style['left']) + this.RemovePx(elmMenuItem.style['width']) + this.config.Level2.spacing;
		//elmContainer.style['position'] = 'absolute';
		//elmContainer.style['overflow'] = 'visible';

		
		//Render the menu
		for (strKey in elmMenuItem.action)
		{
			//Build new element
			objNode = document.createElement('div');
			objNode.setAttribute('Id', elmMenuItem.id + "_" + strKey);
			
			//Attach to elmMenu
			elmContainer.appendChild(objNode);
			elmNode = document.getElementById(elmMenuItem.id + "_" + strKey);
			
			// add text to the node
			objTextNode = document.createTextNode(strKey);
			elmNode.appendChild(objTextNode);
			
			//Add styles
			//new_node.style[c_attrib] = value
			elmNode.style['top'] 			= top;
			elmNode.style['left'] 			= this.config.Level2.left; 
			elmNode.style['width'] 			= this.config.Level2.width;
			elmNode.style['height'] 		= this.config.Level2.height;
			elmNode.style['position']		= 'absolute';
			elmNode.style['zIndex']			= 2;

			top = top + this.config.Level2.height + this.config.Level2.spacing;
			
			//Add events
			elmNode.onclick = function(event) {Vixen.Menu.HandleClick(this)};
			elmNode.onmouseover = function(event) {Vixen.Menu.HandleMouseOver(this)};
			elmNode.onmouseout = function(event) {Vixen.Menu.HandleMouseOut(this)};
			
			//Add some more crap
			elmNode.action = elmMenuItem.action[strKey];
			elmNode.level = elmMenuItem.level + 1;
			elmNode.style.cursor = "default";
			
			// set the class
			objNode.className 	= 'ContextMenuItem';
			objNode.class 		= 'ContextMenuItem';
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
			// Check if the menu item is a href or a call to javascript code
			if (objMenuItem.action.substr(0, 11) == "javascript:")
			{
				// Execute objMenuItem.action as javascript
				eval(objMenuItem.action.substr(11, objMenuItem.action.length));
			} 
			else
			{
				//Follow the link
				document.location.href = objMenuItem.action;
			}
		}
		else if (typeof(objMenuItem.action) == 'object')
		{
			//display submenu
			// no need, it adds unnecessary overhead
			//this.RenderSubMenu(objMenuItem);			
		}
		this.timeoutClose = setTimeout("Vixen.Menu.Close(1)", this.config.waitCloseWhenSelected);
	}
	
	this.HandleMouseOver = function(objMenuItem)
	{
		clearTimeout(this.timeoutClose);
		
		objMenuItem.setAttribute('className', 'ContextMenuItemHighlight');
		objMenuItem.setAttribute('class', 'ContextMenuItemHighlight');
		
		if (typeof(objMenuItem.action) == 'string')
		{
			this.timeoutOpen = setTimeout("Vixen.Menu.Close('" + objMenuItem.level + "');", this.config.waitCloseLevel);		
		}
		if (typeof(objMenuItem.action) == 'object')
		{
			clearTimeout(this.timeoutOpen);

			//display submenu
			this.timeoutOpen = setTimeout("Vixen.Menu.RenderSubMenu('" + objMenuItem.id + "');", this.config.waitOpen);			
		}
	}
	
	this.HandleMouseOut = function(objMenuItem)
	{
		clearTimeout(this.timeoutOpen);
		
		objMenuItem.setAttribute('className', 'ContextMenuItem');
		objMenuItem.setAttribute('class', 'ContextMenuItem');
		this.timeoutClose = setTimeout("Vixen.Menu.Close(1)", this.config.waitClose);

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

// Create an instance of the Vixen menu class, if it doesn't already exist
if (Vixen.Menu == undefined)
{
	Vixen.Menu = new VixenMenuClass({});
}
