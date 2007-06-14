var FALSE = 0;
var TRUE = 1;

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
	
	this.table = 
	{ 
		'AccountPayments': 
		{
			'totalRows': 9,
			'collapseAll' : TRUE,
			'linked': TRUE,
			'link':
			{
				'AccountInvoices' :
				[
					'Invoice'
				]
			},
			'row' :
			[
				{
					'selected' : FALSE,
					'up' : TRUE,
					'index' : 
					{
						'Invoice' :'3000308781',
						'Service' :'6123'
					}
				},
				{
					'selected' : FALSE,
					'up' : FALSE,
					'index' : 
					{
						'Invoice' :'3000213123',
						'Service' :'6123'
					}
				},
				{
					'selected' : FALSE,
					'up' : TRUE,
					'index' : 
					{
						'Invoice' :'3000295048',
						'Service' :'7209'
					}
				},
				{
					'selected' : FALSE,
					'up' : FALSE,
					'index' : 
					{
						'Invoice' :'3000213123',
						'Service' :'6123'
					}
				},
				{
					'selected' : FALSE,
					'up' : TRUE,
					'index' : 
					{
						'Invoice' :'3000213123',
						'Service' :'7209'
					}
				},
				{
					'selected' : FALSE,
					'up' : TRUE,
					'index' : 
					{
						'Invoice' :'3000308781',
						'Service' :'7209'
					}
				},
				{
					'selected' : FALSE,
					'up' : TRUE,
					'index' : 
					{
						'Invoice' :'3000213123',
						'Service' :'7209'
					}
				},
				{
					'selected' : FALSE,
					'up' : TRUE,
					'index' : 
					{
						'Invoice' :'3000308781',
						'Service' :'7209'
					}
				},
				{
					'selected' : FALSE,
					'up' : TRUE,
					'index' : 
					{
						'Invoice' :'3000308781',
						'Service' :'7209'
					}
				}
			]
		},
		'AccountInvoices': 
		{
			'totalRows': 8,
			'selected': 0,
			'collapseAll' : TRUE,
			'linked': TRUE,
			'link':
			{
				'AccountPayments' :
				[
					'Invoice'
				]
			},
			'row' :
			[
				{
					'selected' : FALSE,
					'up' : TRUE,
					'index' : 
					{
						'Invoice' :'3000308781',
						'Service' :'6123'
					}
				},
				{
					'selected' : FALSE,
					'up' : FALSE,
					'index' : 
					{
						'Invoice' :'3000295048',
						'Service' :'6123'
					}
				},
				{
					'selected' : FALSE,
					'up' : TRUE,
					'index' : 
					{
						'Invoice' :'3000281455',
						'Service' :'7209'
					}
				},
				{
					'selected' : FALSE,
					'up' : FALSE,
					'index' : 
					{
						'Invoice' :'3000268045',
						'Service' :'6123'
					}
				},
				{
					'selected' : FALSE,
					'up' : FALSE,
					'index' : 
					{
						'Invoice' :'3000213123',
						'Service' :'6123'
					}
				},
				{
					'selected' : FALSE,
					'up' : FALSE,
					'index' : 
					{
						'Invoice' :'3000213123',
						'Service' :'6123'
					}
				},
				{
					'selected' : FALSE,
					'up' : FALSE,
					'index' : 
					{
						'Invoice' :'3000213123',
						'Service' :'6123'
					}
				},
				{
					'selected' : FALSE,
					'up' : FALSE,
					'index' : 
					{
						'Invoice' :'3000213123',
						'Service' :'6123'
					}
				}
			]
		}
	}
	
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
	
	this.Init =function()
	{
		debug ('Page has loaded');
		if (debug && Vixen.Highlight && document.getElementById('AccountPayments'))
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
		//debug (this.table, 1);
		
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
		dwin = window.open("","debugconsole","scrollbars=yes,resizable=yes,height=100,width=500");
		dwin.title = "debugconsole";
		dwin.document.open("text/html", "replace");
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
