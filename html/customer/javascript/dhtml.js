//----------------------------------------------------------------------------//
// VixenDhtmlClass
//----------------------------------------------------------------------------//
/**
 * VixenDhtmlClass
 *
 * Vixen DHTML class
 *
 * Vixen DHTML class
 *
 *
 *
 * @package	framework_ui
 * @class	Vixen.Dhtml
 */
function VixenDhtmlClass()
{
	//------------------------------------------------------------------------//
	// this.Drag
	//------------------------------------------------------------------------//
	/**
	 * this.Drag()
	 *
	 * Drag an object
	 *
	 * Called on mousedown to start draging an object. On mouseup the object will
	 * automatically stop being dragged
	 *
	 * @param	object	evt			browser event object
	 * @param	string	object_id	optional ID of the object to drag
	 * @return	void
	 *
	 * @method
	 */
	this.Drag =function(evt, object_id)
	{
		dragStart(evt, object_id);
	}
	
	//------------------------------------------------------------------------//
	// METHOD
	//------------------------------------------------------------------------//
	/**
	 * Method_Name()
	 *
	 * short description
	 *
	 * long description
	 *
	 * @param	type	$name	[optional] [description]
	 * @return	type			[description]
	 *
	 * @method
	 * @see		method_name
	 * @private
	 */
	this.get_mouse_left =function(evt)
	{
		if (browser.isIE) {
			var x = window.event.clientX + document.documentElement.scrollLeft + document.body.scrollLeft;
		}
		else if (browser.isNS) {
			var x = evt.clientX + window.scrollX;
		}
		return x;
	}
	
	//------------------------------------------------------------------------//
	// METHOD
	//------------------------------------------------------------------------//
	/**
	 * Method_Name()
	 *
	 * short description
	 *
	 * long description
	 *
	 * @param	type	$name	[optional] [description]
	 * @return	type			[description]
	 *
	 * @method
	 * @see		method_name
	 * @private
	 */
	this.get_mouse_top =function(evt)
	{
		if (browser.isIE) {
			var y = window.event.clientY + document.documentElement.scrollTop + document.body.scrollTop;
		}
		else if (browser.isNS) {
			var y = evt.clientY + window.scrollY;
		}
		return y;
	}
}

// instanciate the dhtml object
if (Vixen.Dhtml == undefined)
{
	Vixen.Dhtml = new VixenDhtmlClass;
}


//----------------------------------------------------------------------------//
// NEW DRAG/DROP CLASS
//----------------------------------------------------------------------------//
//
//

//----------------------------------------------------------------------------//
// browser CLASS
//----------------------------------------------------------------------------//
/**
 * browser_type
 *
 * Identifies the browser
 *
 * Holds browser identification details and sets the browser mode that Vixen
 * will use. Vixen will automatically detect the browser mode to use, this 
 * browser mode (and the browser identification details) may not match the actual
 * client web browser.
 *
 * @package Vixen_Javascript_Client
 * @parent	Vixen
 * @class	browser
 */
function browser_type()
{
	//----------------------------------------------------------------//
	// this.name
	//----------------------------------------------------------------//
	/**
	 * this.name
	 *
	 * Client browser name
	 *
	 * Client browser name identification string as provided by the browser.
	 *
	 * @type	string
	 * @property
	 */
	this.name = navigator.appName;
	
	//----------------------------------------------------------------//
	// this.version
	//----------------------------------------------------------------//
	/**
	 * this.version
	 *
	 * Client browser version
	 *
	 * Client browser version identification string as provided by the browser.
	 *
	 * @type	string
	 * @property
	 */
	this.version = navigator.appVersion;
	
	//----------------------------------------------------------------//
	// this.os
	//----------------------------------------------------------------//
	/**
	 * this.os
	 *
	 * Client operating system
	 *
	 * Client operating system identification string as provided by the browser.
	 *
	 * @type	string
	 * @property
	 */
	this.os = navigator.platform;
	
	//----------------------------------------------------------------//
	// this.mode
	//----------------------------------------------------------------//
	/**
	 * this.mode
	 *
	 * Vixen browser mode
	 *
	 * identifies/sets the browser mode that Vixen is using. Normally this
	 * property would be set automatically by Vixen and used for reference only
	 *
	 *
	 * IE = Internet Explorer and Opera browsers
	 * NS = Mozilla based browsers
	 *
	 * @type	string
	 * @property
	 */
	this.mode = '';
	
	// work out the browser mode to run
	if (navigator.appVersion.indexOf("MSIE")!=-1)
	{
		// internet explorer or someone pretending to be internet explorer
		this.mode = 'IE';
	}
	else
	{
		switch (this.name.toLowerCase())
		{
			case 'internet explorer':
			case 'microsoft internet explorer':
			case 'msie':
			case 'opera':
				// ie mode
				this.mode = 'IE';
				break;
	
			default:
				// netscape mode
				this.mode = 'NS';
		}
	}
}

// Create the Browser object, if it has not already been created
if (Vixen.Browser == undefined)
{
	Vixen.Browser = new browser_type();
}

// DRAGDROP
// originaly based on code from brainjar.com

//*****************************************************************************
// Do not remove this notice.
//
// Copyright 2001 by Mike Hall.
// See http://www.brainjar.com for terms of use.
//*****************************************************************************

// Determine browser and version.
var browser = Vixen.Browser;
if (Vixen.Browser.mode == 'IE')
{
	browser.isIE = TRUE;
	browser.isNS = FALSE;
}
else
{
	browser.isIE = FALSE;
	browser.isNS = TRUE;
}

// Global object to hold drag information.
if (dragObj == undefined)
{
	var dragObj = new Object();
	dragObj.zIndex = 2;
}

function dragStart(event, id) {
	if (Vixen.dragging_now === TRUE)
	{
		dragStop(event);
		return FALSE;
	}
	else
	{

	}
	Vixen.dragging_now = TRUE;
	Vixen.Dhtml.dragging = TRUE;
	
	
  var el;
  var x, y;
  
  // If an element id was given, find it. Otherwise use the element being
  // clicked on.
  
  if (id)
    dragObj.elNode = document.getElementById(id);
	
  else {
    if (browser.isIE)
      dragObj.elNode = window.event.srcElement;
    if (browser.isNS)
      dragObj.elNode = event.target;

    // If this is a text node, use its parent element.

    if (dragObj.elNode.nodeType == 3)
      dragObj.elNode = dragObj.elNode.parentNode;
  }
  // popup width/height  + hardcoded border width * 2
  var popup_width = dragObj.elNode.style.width.substr(0, dragObj.elNode.style.width.length - 2) * 1 + 2;
  var popup_height = dragObj.elNode.clientHeight + 2;
  
  // not used
  // HACK HACK HACK This is hardcoded to be the height of the top bar of the popup (18px currently) + a little bit
  var header_height = 25; 
  
  
  // Get cursor position with respect to the page.
  if (browser.isIE) {
    x = window.event.clientX + document.documentElement.scrollLeft
      + document.body.scrollLeft;
    y = window.event.clientY + document.documentElement.scrollTop
      + document.body.scrollTop;
  }
  if (browser.isNS) {
    x = event.clientX + window.scrollX;
    y = event.clientY + window.scrollY;
  }
	
	
  // Save starting positions of cursor and element.
  dragObj.cursorStartX = x;
  dragObj.cursorStartY = y;
  dragObj.elStartLeft  = parseInt(dragObj.elNode.offsetLeft, 10);
  dragObj.elStartTop   = parseInt(dragObj.elNode.offsetTop,  10);
	
  if (isNaN(dragObj.elStartLeft)) dragObj.elStartLeft = 0;
  if (isNaN(dragObj.elStartTop))  dragObj.elStartTop  = 0;

  // Finding page width...
  var divPageBody = document.getElementById("PageBody");
  var intPageWidth = divPageBody.offsetWidth + divPageBody.offsetLeft;
  intPageWidth = Math.max(document.body.offsetWidth, intPageWidth);
  
  // Set limits of movement
  dragObj.elNode.limits = Object();
  dragObj.elNode.limits.drag_horizontal = TRUE;
  dragObj.elNode.limits.drag_vertical = TRUE;
  dragObj.elNode.limits.drag_left = 1;
  dragObj.elNode.limits.drag_top = document.body.scrollTop + 1;
  // dragObj.elNode.limits.drag_right = document.body.offsetWidth - popup_width;
  dragObj.elNode.limits.drag_right = intPageWidth - popup_width;
  // dragObj.elNode.limits.drag_bottom = document.body.scrollTop + window.innerHeight - header_height; 
  dragObj.elNode.limits.drag_bottom = Math.max(document.body.offsetHeight, window.innerHeight) - popup_height;
  
  // Update element's z-index.
  //dragObj.elNode.style.zIndex = ++dragObj.zIndex;
  
  // Capture mousemove and mouseup events on the page.

  if (browser.isIE) {
  	document.attachEvent("onmouseup", dragStop);
    document.attachEvent("onmousemove", dragGo);
    window.event.cancelBubble = true;
    window.event.returnValue = false;
  }
  if (browser.isNS) {
    document.addEventListener("mousemove", dragGo,   true);
    document.addEventListener("mouseup",   dragStop, true);
    event.preventDefault();
  }
}

function dragGo(event) {
	
  var x, y;

  // Get cursor position with respect to the page.

  if (browser.isIE) {
    x = window.event.clientX + document.documentElement.scrollLeft
      + document.body.scrollLeft;
    y = window.event.clientY + document.documentElement.scrollTop
      + document.body.scrollTop;
  }
  if (browser.isNS) {
    x = event.clientX + window.scrollX;
    y = event.clientY + window.scrollY;
  }

	// Move drag element by the same amount the cursor has moved.
	var drag_left = dragObj.elStartLeft + x - dragObj.cursorStartX;
	var drag_top = dragObj.elStartTop  + y - dragObj.cursorStartY;
	
	if (!dragObj.elNode.limits)
	{
		dragObj.elNode.style.left = drag_left + "px";
		dragObj.elNode.style.top  = drag_top + "px";
	}
	else
	{
		//window.status = "bottom : " + drag_top + " - " + dragObj.elNode.limits.drag_bottom + " right : " + drag_left + " - " + dragObj.elNode.limits.drag_right;
		if (dragObj.elNode.limits.drag_horizontal !== FALSE)
		{
			if (dragObj.elNode.limits.drag_left && drag_left < dragObj.elNode.limits.drag_left)
			{
				drag_left = dragObj.elNode.limits.drag_left + "px";
			}
			else if (dragObj.elNode.limits.drag_right && drag_left > dragObj.elNode.limits.drag_right)
			{
				drag_left = dragObj.elNode.limits.drag_right;
			}
			dragObj.elNode.style.left = drag_left;
		}
		
		if(dragObj.elNode.limits.drag_vertical !== FALSE)
		{
			if (dragObj.elNode.limits.drag_top && drag_top < dragObj.elNode.limits.drag_top)
			{
				drag_top = dragObj.elNode.limits.drag_top;
			}
			else if (dragObj.elNode.limits.drag_bottom && drag_top > dragObj.elNode.limits.drag_bottom)
			{
				drag_top = dragObj.elNode.limits.drag_bottom;
			}
			dragObj.elNode.style.top  = drag_top + "px";
		}
	}
	
  if (browser.isIE) {
    window.event.cancelBubble = true;
    window.event.returnValue = false;
  }
  if (browser.isNS)
    event.preventDefault();
}

function dragStop(evt) {
	Vixen.dragging_now = FALSE;
	Vixen.Dhtml.dragging = FALSE;
	
	// Stop capturing mousemove and mouseup events.
	if (browser.isIE)
	{
		document.detachEvent("onmousemove", dragGo);
		document.detachEvent("onmouseup",   dragStop);
	}
	if (browser.isNS)
	{
		document.removeEventListener("mousemove", dragGo,   true);
		document.removeEventListener("mouseup",   dragStop, true);
	}
}
