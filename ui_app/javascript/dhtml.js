//----------------------------------------------------------------------------//
// APhPLIX (c) copyright 2005-2006 Jared 'flame' Herbohn (aphplix.org)
//
// APhPLIX website :
//		http://www.aphplix.org
//
// APhPLIX developers :
//		Jared 'flame' Herbohn
//		Dani 'zeemu' Prescott
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// THIS SOFTWARE IS GPL LICENSED
//----------------------------------------------------------------------------//
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License (version 2) as 
//  published by the Free Software Foundation.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU Library General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with this program; if not, write to the Free Software
//  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// NOTES
//----------------------------------------------------------------------------//
/**
 * APhPLIX DHTML CLASS
 *
 * drag/drop dhtml class
 *
 * these functions work directly with elements(nodes) in the browser DOM
 * not with aphplix objects
 *
 * @file	dhtml.js
 * @package APhPLIX_Javascript_Client
 * @author Jared 'flame' Herbohn
 * @version 6.05
 * @copyright 2005-2006 Jared 'flame' Herbohn, http://www.aphplix.org
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License (version 2)
 * @todo
 *
 */

//----------------------------------------------------------------------------//
// DHTML CLASS
//----------------------------------------------------------------------------//
/**
 * aphplix_dhtml_class()
 *
 * DHTML class
 *
 * Provides drag/drop & other DHTML services
 *
 * @package APhPLIX_Javascript_Client
 * @parent	aphplix
 * @class	dhtml
 */
function aphplix_dhtml_class()
{
	//----------------------------------------------------------------//
	// grid_x
	//----------------------------------------------------------------//
	/**
	 * this.grid_x
	 *
	 * x axis grid spacing
	 *
	 * x axis grid spacing, used if aphplix.dhtml.snap_to_grid = TRUE
	 *
	 * @type	int
	 * @property
	 * @see		aphplix.dhtml.grid_y
	 * @see		aphplix.dhtml.snap_to_grid
	 * @private
	 */
	this.grid_x = 5;
	
	//----------------------------------------------------------------//
	// grid_y
	//----------------------------------------------------------------//
	/**
	 * this.grid_y
	 *
	 * y axis grid spacing
	 *
	 * y axis grid spacing, used if aphplix.dhtml.snap_to_grid = TRUE
	 *
	 * @type	int
	 * @property
	 * @see		aphplix.dhtml.grid_x
	 * @see		aphplix.dhtml.snap_to_grid
	 * @private
	 */
	this.grid_y = 5;
	
	//----------------------------------------------------------------//
	// snap_to_grid
	//----------------------------------------------------------------//
	/**
	 * this.snap_to_grid
	 *
	 * snap to grid
	 *
	 * snap dragged objects to a grid defined by aphplix.dhtml.grid_x and
	 * aphplix.dhtml.grid_y
	 *
	 * @type	bool
	 * @property
	 * @see		aphplix.dhtml.grid_x
	 * @see		aphplix.dhtml.grid_y
	 * @private
	 */
	this.snap_to_grid = FALSE;
	
	//----------------------------------------------------------------//
	// front_object
	//----------------------------------------------------------------//
	/**
	 * this.front_object
	 *
	 * ID of the current front object
	 *
	 * ID of the current front object
	 *
	 * @type	string
	 * @property
	 * @see		aphplix.dhtml.front()
	 */
	this.front_object = '';
	
	//----------------------------------------------------------------//
	// front_form
	//----------------------------------------------------------------//
	/**
	 * this.front_form
	 *
	 * ID of the current front form
	 *
	 * ID of the current front form
	 *
	 * @type	string
	 * @property
	 */
	this.front_form = '';
	
	//------------------------------------------------------------------------//
	// this.drag
	//------------------------------------------------------------------------//
	/**
	 * this.drag()
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
	this.drag = function(evt, object_id)
	{
		dragStart(evt, object_id);
	}
	
	//------------------------------------------------------------------------//
	// this.front
	//------------------------------------------------------------------------//
	/**
	 * this.front()
	 *
	 * Bring an object to the front
	 *
	 * Bring an object to the front
	 *
	 * @param	string	$name	[optional] [description]
	 * @return	void
	 *
	 * @method
	 * @see		aphplix.dhtml.front_object
	 */
	this.front = function(object_id)
	{
		aphplix.html.set_style(object_id, 'zIndex', ++dragObj.zIndex);
		this.front_object = object_id;
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
	this.zindex =function()
	{
		return ++dragObj.zIndex;
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
aphplix.dhtml = new aphplix_dhtml_class;

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
 * Holds browser identification details and sets the browser mode that APhPLIX
 * will use. APhPLIX will automatically detect the browser mode to use, this 
 * browser mode (and the browser identification details) may not match the actual
 * client web browser.
 *
 * @package APhPLIX_Javascript_Client
 * @parent	aphplix
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
	 * APhPLIX browser mode
	 *
	 * identifies/sets the browser mode that APhPLIX is using. Normally this
	 * property would be set automatically by APhPLIX and used for reference only
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
aphplix.browser = new browser_type();

// DRAGDROP
// originaly based on code from brainjar.com

//*****************************************************************************
// Do not remove this notice.
//
// Copyright 2001 by Mike Hall.
// See http://www.brainjar.com for terms of use.
//*****************************************************************************

// Determine browser and version.
var browser = aphplix.browser;
if (aphplix.browser.mode == 'IE')
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

var dragObj = new Object();
dragObj.zIndex = 0;

function dragStart(event, id) {
	if (aphplix.dragging_now === TRUE)
	{
		dragStop(event);
		return FALSE;
	}
	else
	{

	}
	aphplix.dragging_now = TRUE;
	aphplix.dhtml.dragging = TRUE;
	
	
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
  
	// check if we need to detach the element
	if (typeof(aphplix.objects[dragObj.elNode.id]) == 'object')
	{
		if (aphplix.objects[dragObj.elNode.id].detach_on_drag === TRUE)
		{
			aphplix.html.detach(dragObj.elNode.id);
		}
	}

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
  dragObj.elStartLeft  = parseInt(dragObj.elNode.style.left, 10);
  dragObj.elStartTop   = parseInt(dragObj.elNode.style.top,  10);

  if (isNaN(dragObj.elStartLeft)) dragObj.elStartLeft = 0;
  if (isNaN(dragObj.elStartTop))  dragObj.elStartTop  = 0;

  // Update element's z-index.

  dragObj.elNode.style.zIndex = ++dragObj.zIndex;

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
  
  aphplix.fire_event(dragObj.elNode.event_target, 'pickup');
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
	
	if (!dragObj.elNode.dhtml)
	{
		dragObj.elNode.style.left = drag_left + "px";
		dragObj.elNode.style.top  = drag_top + "px";
	}
	else
	{
		if (dragObj.elNode.dhtml.drag_horizontal !== FALSE)
		{
			if (dragObj.elNode.dhtml.drag_left && drag_left < dragObj.elNode.dhtml.drag_left)
			{
				drag_left = dragObj.elNode.dhtml.drag_left;
			}
			else if (dragObj.elNode.dhtml.drag_right && drag_left > dragObj.elNode.dhtml.drag_right)
			{
				drag_left = dragObj.elNode.dhtml.drag_right;
			}
			dragObj.elNode.style.left = drag_left + "px";
		}
		
		if(dragObj.elNode.dhtml.drag_vertical !== FALSE)
		{
			if (dragObj.elNode.dhtml.drag_top && drag_top < dragObj.elNode.dhtml.drag_top)
			{
				drag_top = dragObj.elNode.dhtml.drag_top;
			}
			else if (dragObj.elNode.dhtml.drag_bottom && drag_top > dragObj.elNode.dhtml.drag_bottom)
			{
				drag_top = dragObj.elNode.dhtml.drag_bottom;
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
	
	// TODO !!!! where is the event object ?
	aphplix.fire_event(dragObj.elNode.event_target, 'drag');
}

function dragStop(evt) {
	aphplix.dragging_now = FALSE;
	aphplix.dhtml.dragging = FALSE;
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
	// get the event object
	if (!evt)
	{
		var evt = window.event;
	}

	if (!evt.target && evt.srcElement)
	{
		evt.target = evt.srcElement;
	}
	aphplix.real_event(dragObj.elNode.event_target, 'drop', evt);
}
