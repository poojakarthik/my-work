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
	// Vixen Login
	this.Login = function(username, password)
	{
		//document.getElementById('LoginBox').style.visibility = 'visible';
		// AJAX transaction to login in user
	}
	
	// Vixen Logout
	this.Logout = function()
	{
		//alert ('logging out');
		
	}
}

// Create an instance of the Vixen root class
Vixen = new VixenRootClass();

var FALSE = 0;
var TRUE = 1;



var dwin = null;
function debug(msg) {
	if ((dwin == null) || (dwin.closed))
	{
		dwin = window.open("","debugconsole","scrollbars=yes,resizable=yes,height=100,width=300");
		dwin.title = "debugconsole";
		dwin.document.open("text/html", "replace");
	}
	dwin.document.writeln('<br />-'+msg + '-');
	dwin.scrollTo(0,10000);
	//dwin.focus();
	//dwin.document.close();  // uncomment this if you want to see only last message , not all the previous messages
}
