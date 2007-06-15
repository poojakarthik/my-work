var FALSE = false;
var TRUE = true;

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
	
	this.table = Object();
	
	// Vixen Login
	this.Login = function(username, password)
	{
		//document.getElementById('LoginBox').style.visibility = 'visible';
		// AJAX transaction to login in user
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
		if (debug)
		{
			for (var i = 0; i < this.initCommands.length; i++)
			{
				eval (this.initCommands[i]);
				//debug (this.initCommands[i]);
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
}

// Create an instance of the Vixen root class
Vixen = new VixenRootClass();

var dwin = null;
function debug(msg, bolFullShow) {
	if ((dwin == null) || (dwin.closed))
	{
		dwin = window.open("","debugconsole","scrollbars=yes,resizable=yes,height=100,width=500, menubar=yes");
		//dwin.document.close();
		
		dwin.title = "debugconsole";
		dwin.document.open("text/html", "replace");
		dwin.document.writeln('<title>Debug Console</title>');
	}
	if (bolFullShow == TRUE)
	{
		strDebug = DEBUG.fstringify(msg);
	}
	else
	{
		strDebug = msg;
	}
	dwin.document.writeln('<br />'+strDebug + '');
	dwin.scrollTo(0,10000);
	//dwin.focus();
	//dwin.document.close();  // uncomment this if you want to see only last message , not all the previous messages
}
