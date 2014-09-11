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
			'spacing': 5,
			'backgroundColor': "#FFFFFF"
		},
		'Level2': 
		{
			'left': 0,
			'width': 400,
			'minWidth' : 100,
			'height': 24,
			'spacing': 5,
			'backgroundColor': "#D5E0F8"
		},
		'waitOpen': 0,
		'waitCloseLevel': 500,
		'waitClose': 1500,
		'waitCloseWhenSelected': 400,
		'highlightColor': "#FFFFCC"
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
			objNode = document.createElement('a');
			objNode.setAttribute('className', 'ContextMenuItem');
			objNode.setAttribute('class', 'ContextMenuItem');
			objNode.setAttribute('Id', 'VixenMenu_' + strKey);

			//Build the image for the new element
			objNodeImage = document.createElement('img');
			objNodeImage.setAttribute('src', 'img/template/' + strKey.toLowerCase().replace(/ /g, '_') + '.png');

			objNode.appendChild(objNodeImage);

			//Attach to elmMenu
			elmMenu.appendChild(objNode);
			elmNode = document.getElementById('VixenMenu_' + strKey);
			
			//Add styles
			//new_node.style[c_attrib] = value
			elmNode.style['top'] 				= top;
			elmNode.style['left'] 				= this.config.Level1.left;
			elmNode.style['width'] 				= this.config.Level1.width;
			elmNode.style['height'] 			= this.config.Level1.height;
			elmNode.style['backgroundColor']	= this.config.Level1.backgroundColor;
			elmNode.style['position'] 			= 'absolute';
			elmNode.style['zIndex']				= 2;
			elmNode.DefaultBackgroundColor		= this.config.Level1.backgroundColor;
			
			top = top + this.config.Level1.height + this.config.Level1.spacing;
			
			//Add events
			elmNode.onclick			= function(event) {Vixen.Menu.HandleClick(this, event)};
			elmNode.onmouseover		= function(event) {Vixen.Menu.HandleMouseOver(this)};
			elmNode.onmouseout		= function(event) {Vixen.Menu.HandleMouseOut(this)};
			elmNode.style.cursor	= "default";
			
			//Add some more crap
			elmNode.action = this.objMenu[strKey];
			if (typeof(elmNode.action) == 'string')
			{
				// Don't set the link if the action opens a popup
				if (elmNode.action.substr(0, 11) == "javascript:")
				{
				}
				else
				{
					// The item is an action, not a menu.  Set the link
					elmNode.setAttribute('href', this.objMenu[strKey]);
				}
			}
			elmNode.level = 1;
		}
	}
	
	this.RenderSubMenu = function(elmMenuItem)
	{
		var strKey;
		var objTextNode;
		var elmNode;
		var top = 0;

		elmMenuItem	= $ID(elmMenuItem);	

		//var elmOldSubMenu	= $ID('VixenMenu__' + elmMenuItem.level);
		var object	= $ID('VixenMenu__' + elmMenuItem.level);
		if (object)
		{
			object.parentNode.removeChild(object);
		}
		
		
		//Create and attach the container div for the rest of the submenu to sit in
		var objContainer = document.createElement('div');
		objContainer.setAttribute('Id', 'VixenMenu__' + elmMenuItem.level);
		objContainer.style.top				= this.RemovePx(elmMenuItem.style['top']);
		objContainer.style.left				= this.RemovePx(elmMenuItem.style['left']) + this.RemovePx(elmMenuItem.style['width']) + this.config.Level2.spacing;
		objContainer.style.position			= 'absolute';
		objContainer.style.overflow			= 'visible';
		objContainer.style.backgroundColor	= "#FFFFFF";
		objContainer.style.width			= this.config.Level2.width + this.config.Level2.spacing;
		objContainer.style.zIndex			= 2;
		objContainer.style.visibility		= "hidden";
		
		elmMenuItem.parentNode.appendChild(objContainer);
		var elmContainer = $ID('VixenMenu__' + elmMenuItem.level);
		//elmContainer.style['top'] = this.RemovePx(elmMenuItem.style['top']);
		//elmContainer.style['left'] = this.RemovePx(elmMenuItem.style['left']) + this.RemovePx(elmMenuItem.style['width']) + this.config.Level2.spacing;
		//elmContainer.style['position'] = 'absolute';
		//elmContainer.style['overflow'] = 'visible';

		var intContainerHeight = 0;

		var intMaxScrollWidth = 0;
		var arrElements = new Array;
		//Render the menu
		for (strKey in elmMenuItem.action)
		{
			//Build new element
			elmNode = document.createElement('div');
			elmNode.setAttribute('Id', elmMenuItem.id + "_" + strKey);
			
			objTextNode = document.createTextNode(strKey);
			
			// Add an anchor element to the menu item div if the action of the menu item is a string and does not envoke any javascript
			// This is done, so the user has the option of opening the page in a new tab
			// It is also done in a very hacky fashion as the user has to right click on the text; it can't just be anywhere on the menu item
			if ((typeof(elmMenuItem.action[strKey]) == 'string') && (elmMenuItem.action[strKey].substr(0, 11) != "javascript:"))
			{
				elmLink = document.createElement('a');
				elmLink.setAttribute('href', elmMenuItem.action[strKey]);
				elmLink.setAttribute('id', "ContextMenuItemLink");
				//elmLink.appendChild(objTextNode);
				elmLink.innerHTML = strKey;
				elmNode.appendChild(elmLink);
				//elmLink.style['color']	= "#000000";
			}
			else
			{
				// Add text to the node
				elmNode.appendChild(objTextNode);
			}

			//Add styles
			//new_node.style[c_attrib] = value
			elmNode.style['top'] 			= top;
			elmNode.style['left'] 			= this.config.Level2.left; 
			elmNode.style['width'] 			= "auto";//this.config.Level2.width;
			elmNode.style['height'] 		= this.config.Level2.height;
			elmNode.style['backgroundColor'] = this.config.Level2.backgroundColor;
			elmNode.style['position']		= 'absolute';
			elmNode.style['zIndex']			= 3;
			elmNode.DefaultBackgroundColor	= this.config.Level2.backgroundColor;

			top = top + this.config.Level2.height + this.config.Level2.spacing;
			
			//Add events
			elmNode.onclick			= function(event) {Vixen.Menu.HandleClick(this, event)};
			elmNode.onmouseover		= function(event) {Vixen.Menu.HandleMouseOver(this)};
			elmNode.onmouseout		= function(event) {Vixen.Menu.HandleMouseOut(this)};
			
			//Add some more crap
			elmNode.action			= elmMenuItem.action[strKey];
			elmNode.level			= elmMenuItem.level + 1;
			elmNode.style.cursor	= "default";

			// set the class
			elmNode.className 	= 'ContextMenuItem';
			elmNode.Class 		= 'ContextMenuItem';
			
			// Add the menu item element to the container
			elmContainer.appendChild(elmNode);
			
			// Update the MaxScrollWidth encountered
			intMaxScrollWidth = (elmNode.scrollWidth > intMaxScrollWidth) ? elmNode.scrollWidth : intMaxScrollWidth;

			arrElements.push(elmNode);
		}
		
		// Work out what width to make each element
		var intWidth = (intMaxScrollWidth > this.config.Level2.minWidth)? intMaxScrollWidth : this.config.Level2.minWidth;
		elmContainer.style.width = intWidth;
		for (i in arrElements)
		{
			arrElements[i].style.width = intWidth;
		}
		
		// Show the menu
		elmContainer.style.visibility = "visible";
	}
	
	this.Close = function(intLevel)
	{
		var object = document.getElementById('VixenMenu__' + intLevel);
		if (object)
		{
			object.parentNode.removeChild(object);
		}
	}
	
	this.HandleClick = function(objMenuItem, objEvent)
	{
		clearTimeout(this.timeoutOpen);
		clearTimeout(this.timeoutClose);

		// If the menu item action is wrapped in an anchor element, prevent the default action, so that it isn't executed twice
		if (objEvent != undefined)
		{
			objEvent.preventDefault();
		}
		
		if (typeof(objMenuItem.action) == 'string')
		{
			// Check if the menu item is a href or a call to javascript code
			if (objMenuItem.action.substr(0, 11) == "javascript:")
			{
				// Execute objMenuItem.action as javascript
				eval(objMenuItem.action.substr(11));
			} 
			else
			{
				// Follow the link
				document.location.href = objMenuItem.action;
			}
			this.timeoutClose = setTimeout("Vixen.Menu.Close(1)", this.config.waitCloseWhenSelected);
		}
		else if (typeof(objMenuItem.action) == 'object')
		{
			//display submenu
			// no need, it adds unnecessary overhead
			//this.RenderSubMenu(objMenuItem);			
		}
	}
	
	this.HandleMouseOver = function(objMenuItem)
	{
		clearTimeout(this.timeoutClose);
		
		//objMenuItem.setAttribute('className', 'ContextMenuItemHighlight');
		//objMenuItem.setAttribute('class', 'ContextMenuItemHighlight');
		
		objMenuItem.style['backgroundColor'] = this.config.highlightColor;
		
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
		
		//objMenuItem.setAttribute('className', 'ContextMenuItem');
		//objMenuItem.setAttribute('class', 'ContextMenuItem');
		objMenuItem.style['backgroundColor'] = objMenuItem.DefaultBackgroundColor;
		
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
