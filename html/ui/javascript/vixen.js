var FALSE		= false;
var TRUE		= true;
var DEBUG_MODE	= FALSE;
var VIXEN_APPLICATION_NAME = "Vixen Internal System";

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
		var x = window.confirm ("Are you sure you would like to Logout?");
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
