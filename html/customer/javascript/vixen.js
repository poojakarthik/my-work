var FALSE		= false;
var TRUE		= true;
var DEBUG_MODE	= FALSE;
var VIXEN_APPLICATION_NAME = "Flex Internal System";

//----------------------------------------------------------------------------//
// VixenRootClass
//----------------------------------------------------------------------------//
/**
 * VixenRootClass
 *
 * Vixen root Javascript class
 *
 * Vixen root Javascript class
 *
 *
 * @prefix	Vixen
 *
 * @package	framework_ui
 * @class	Vixen
 */
function VixenRootClass()
{
	this.initCommands = Array();
	this.debug = FALSE;
	this.table = Object();
	
	// Vixen Login
	this.Login = function(username, password)
	{
		// AJAX transaction to login in user
		// SHA1.js has already been included, used to hash the password
		
	}
	
	// Vixen Logout
	this.Logout = function()
	{
		var x = window.confirm("Are you sure you would like to Logout?");
		if (x)
		{
			return TRUE;
		}
		return FALSE;
	}
	
	this.Init =function()
	{
		// An optional way to run code when the page loads
		//  this code does not execute until bodyload
		//    -- maybe change it so it can be called at any time, but will only
		//       ever run once
		if (TRUE == TRUE)
		{
			for (var i = 0; i < this.initCommands.length; i++)
			{
				eval (this.initCommands[i]);
			}
		}
		else
		{
			window.setTimeout('Vixen.Init()',5);
		}
	}
	
	this.AddCommand =function(strCommand)
	{
		var strParameters="";
		for (var i=1; i<arguments.length; i++)
		{
			strParameters += arguments[i] + ", ";
		}
		strParameters = strParameters.substr(0, strParameters.length - 2);
		
		this.initCommands.push (strCommand + "(" + strParameters + ")");
	}
	
	// --------------------------------------------------------------------------------------------------------------//
	// BROWSER BEHAVIOUR
	// --------------------------------------------------------------------------------------------------------------//
	
	//------------------------------------------------------------------------//
	// EnterKiller
	//------------------------------------------------------------------------//
	/**
	 * EnterKiller()
	 *
	 * stop enter key from submiting a form
	 *
	 * stop enter key from submiting a form
	 *
	 * @param	object	evt		browser event object
	 * @return	void
	 * @private
	 */
	this.EnterKiller = function(evt) 
	{		
		// get the event object
		if (!evt)
		{
			var evt = window.event;
		}
		
		if (evt.target.type == "textarea")
		{
			return TRUE;	
		}
		
		if (evt.KeyCode)
		{
			var keycode = evt.KeyCode;
		}
		else if (evt.which)
		{
			var keycode = evt.which;
		}

		// prevent enter key being pressed, unless it is on a button or submit button
		if ((keycode == 13) && (evt.target.type != "button") && (evt.target.type != "submit"))
		{
			// stupid browsers
			if (evt.srcElement && !evt.srcElement.aphplix_id)
			{
				evt.returnValue = FALSE;
			}
			// real browsers
			if (evt.preventDefault)
			{
				evt.preventDefault();
			}
		}
	}
	
	/*this.FixFocus = function(div) 
	{
		var objInputs = div.getElementsByTagName("form");
		for (var theform in objInputs)
		{
			DumperWrite(theform);
			//alert(theform.id);
			for (var objElement in theform.elements)
			{
				//alert("hey");
			}
		}
		if (theform.elements[theform.length - 1].focus)
			{
				alert "sadjasdgjhkdhjd";
			}
		
		
		for (var i=0;i<theform.length;i++)
  		{
  			if (theform.elements[theform.length - 1].focus)
			{
				alert "sadjasdgjhkdhjd";
			}
  		}
	}*/
	
}

// Create an instance of the Vixen root class
if (Vixen == undefined)
{
	var Vixen = new VixenRootClass();
}

// Wrapper for the Vixen.Popup.Alert function
function $Alert(strMessage, strSize, strTitle)
{
	Vixen.Popup.Alert(strMessage, strSize, strTitle);
}


//----------------------------------------------------------------------------//
// Library functions
//----------------------------------------------------------------------------//
/**
 * $A
 *
 * Returns an array of items for an iterable object
 *
 * Returns an array of items for an iterable object
 *
 * @param itterable Object iterable object to return an array of elements for
 *
 * @return Array Items of the iterable object
 *
 * @package	library
 */
function $A(iterable)
{
	if (!iterable) return [];
	if (iterable.toArray) return iterable.toArray();
	var length = iterable.length, results = new Array(length);
	while (length--)
	{
		results[length] = iterable[length];
	}
	return results;
}

/**
 * $ID
 *
 * Returns an element or array of elements for (a) given ID(s)
 *
 * Returns an element or array of elements for (a) given ID(s).
 * For each non-string object passed the same object will also be returned.
 * For each string passed the corresponding element is returned as found by
 * document.getElementById(ID);
 * 
 * Allows interchangeable use of IDs and HTMLElement objects in that $ID(id) 
 * and $ID(element_with_id) equate to the same thing.
 *
 * @param id(s) mixed One or more String IDs or objects
 *
 * @return	mixed Either the element (if found) or array of elements for the passed ID(s)
 *
 * @package	library
 */
function $ID(element) 
{
	if (arguments.length > 1) 
	{
		for (var i = 0, elements = [], length = arguments.length; i < length; i++)
		{
      		elements.push($ID(arguments[i]));
      	}
		return elements;
	}
	if (typeof element == "string")
	{
		element = document.getElementById(element);
		return element;
	}
}

/**
 * Function.prototype.bind
 *
 * Binds the function to a passed object
 *
 * Returns a function that, when called, invokes this function on that object
 * (as though this function were a member function of the passed object) with
 * the arguments passed to the bind function in addition to those passed to 
 * the return function. 
 *
 * Example:
 *
 * 		function sayILikeSomething(strength, feeling)
 * 		{
 * 			if (feeling == undefined)
 * 			{
 * 				feeling = strength;
 * 				strength = "";
 * 			}
 * 			else
 * 			{
 * 				strength += " ";
 * 			}
 * 
 * 			alert("I " + strength + feeling + " " + this);
 * 		}
 * 
 * 		var func = sayILikeSomething.bind("cats", "like");
 * 		func(); 		// Alerts "I like cats"
 * 		func("really"); // Alerts "I really like cats"
 *
 *
 * @param object	Object	Object to invoke this function on
 * @param arg1	 	mixed	Supplemental arguments to pass to this function
 *							appended to the arguments passed to the returned
 *							function (optional)
 *
 * @package	library
 */
Function.prototype.bind = function()
{
	var args = $A(arguments);
	var obj = args.shift();
	var func = this;
	return function() { return func.apply(obj, $A(arguments).concat(args)); }
}

/**
 * Function.prototype.bindAsEventListener
 *
 * Binds the function to a passed object
 *
 * Returns a function that, when called, invokes this function on that object
 * (as though this function were a member function of the passed object) with
 * the Event object of the handled event in addition to those passed to 
 * the return function.
 *
 * Example:
 *
 * 		function nameThatEvent(event)
 * 		{
 * 			alert(event.type);
 * 		}
 * 
 *		// The following would cause "load" to be alerted when the document loads
 * 		Event.startObserving(window, 'load', alertEventType.bindAsEventListener(document), true);
 *
 *
 * @param object	Object	Object to invoke this function on. This can be null,
 *							which is useful if coding for IE where you would otherwise
 *							have to check for 'window.event'.
 * @param arg1	 	mixed	Supplemental arguments to pass to this function
 *							appended to the Event object for the handled event.
 *							(optional)
 *
 * @package	library
 */
Function.prototype.bindAsEventListener = function()
{
	var __method = this, args = $A(arguments), object = args.shift();
	return function(event) 
	{
		return __method.apply(object, [event || window.event].concat(args));
    }
}


//----------------------------------------------------------------------------//
// Event
//----------------------------------------------------------------------------//
var Event = {};

/**
 * startObserving
 *
 * Cross-browser function for doing an 'addEventListener'
 *
 * Cross-browser function for doing an 'addEventListener'
 *
 * @param element		DOMElement	to listen to handle events for
 * @param strName		String		name of the event (without 'on' prefix)
 * @param func			Function	event handler function
 * @param useCapture	Boolean		whether or not to use event capturing
 *
 * @return void
 *
 * @package	library
 */
Event.startObserving = function (element, strName, func, useCapture) 
{
	if (element.addEventListener != undefined)
	{
		element.addEventListener(strName, func, useCapture);
	}
	else
	{
		element.attachEvent("on" + strName, func);
	}
} // end of addEventListener


/**
 * stopObserving
 *
 * Cross-browser function for doing an 'addEventLister'
 *
 * Cross-browser function for doing an 'addEventLister'
 *
 * @param element		DOMElement	to listen to handle events for
 * @param strName		String		name of the event (without 'on' prefix)
 * @param func			Function	event handler function
 * @param useCapture	Boolean		whether or not to use event capturing
 *
 * @return void
 *
 * @package	library
 */
Event.stopObserving = function(element, strName, func, useCapture) 
{
	if (element.removeEventListener != undefined)
	{
		element.removeEventListener(strName, func, useCapture);
	}
	else
	{
		element.detachEvent("on" + strName, func);
	}
} // end of removeEventListener

//----------------------------------------------------------------------------//
// Debug
//----------------------------------------------------------------------------//
/**
 * Debug
 *
 * Debug Javascript function
 *
 * Debug Javascript function
 *
 *
 *
 * @package	framework_ui
 */
if (dwin == undefined)
{
	var dwin = null;
}
function debug(mixMsg, bolFullShow)
{
	// Check for debug mode (set when page loads by php, check vixen_header) 
	if (!Vixen.debug)
	{
		return;
	}

	if ((dwin == null) || (dwin.closed))
	{
		dwin = window.open("","debugconsole","scrollbars=yes,resizable=yes,height=100,width=500, menubar=yes");		
		dwin.title = "debugconsole";
		dwin.document.open("text/html", "replace");
		dwin.document.writeln("<title>Debug Console</title>");
	}
	if (bolFullShow == TRUE)
	{
		// Optional, show full tree listing of object (recursive)
		//  uses a chopped version of JSON stringify
		//  will die if object is cyclic
		strDebug = DEBUG.fstringify(mixMsg);
	}
	else
	{
		// Otherwise, just show whatever it is passed in
		strDebug = mixMsg;
	}
	dwin.document.writeln('<br />'+strDebug + '');
	dwin.scrollTo(0,10000);
	//dwin.focus();				// giving it focus is annoying, just let it sit on your third monitor with javascript console
	//dwin.document.close();    // uncomment this if you want to see only last message , not all the previous messages
}

// prevent Enter key from being pressed
document.onkeydown	= function(event) {Vixen.EnterKiller(event)};
document.onkeypress	= function(event) {Vixen.EnterKiller(event)};
document.onkeyup	= function(event) {Vixen.EnterKiller(event)};
