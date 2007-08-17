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
// BUGS
//----------------------------------------------------------------------------//
// total lack of window resizing
// textarea doesn't wrap in netscape
// nothing works at all in IE (hardly worth mentioning)
//
// windows should grab default values from aphplix
//
// data object will keep going forever backwards, but will stop at the last record
//		hmmmmm.....
// 
// windows can be dropped with the title bar outside the bounds of the screen
// area. we need to prevent this.
//
// checkboxes suck arse and need to be replaced with a js checkbox
//
// drag items : drag the cursor off the side of the window and above another window
// then drop... drop on don't work ??
//
// a lot more thought needs to go into how and when the value property is updated 
// from the actual value.
//
// there is probably a lot we could do to limit the damage caused by failure of
// a single line of code, as in spliting things up so as much other stuff as 
// possible still loads. also we need to try and catch all javascript errors and
// report them, oh and we need to add error reporting to any of the existing 
// try/catch things
//
// updating the position of an element causes it to be -1,-1 out of line ... why ?
// and will this be the same in other browsers ?
//
// date input needs to be able to work out the date entered
// 2feb
// 2-2 *fixed
// 2-2-06 *fixed
//
// should probably have some kind of session lock to prevent simultaneous saves
// of new db records from creating doubles
//
// datasource needs to be configured on the server side (for security)
//
// data control should receive a copy of the current data back from the server
// after an update and any members updated. this should also be used to update
// the controls internal dataset so it is always as current with the server as
// possible
//
// if an empty dataset is passed to the db module for insert or update a query
// is run. no data should = no query
//
// sandbox
// how do we prevent a user from writing code that will interfear with some other
// app running on the server

//----------------------------------------------------------------------------//
// FIXED BUGS
//----------------------------------------------------------------------------//
// 04-02-2006
// when a window is minimized it seems the dhtml library still thinks it is the
// full size and won't let you pick up other windows that would be under it if
// it was still full size.
//
// 08-02-2006
// need to update this.top & this.left whwn a layer is dropped
// clicking on a form should give the form focus, but it doesn't
// windows only come to the front when the titlebar is clicked, not when an
//		element on the form is clicked. we should make the focus behaviour
//		configurable, sloppy (works on mouseover), normal (works on focus) or
//		strict (works on titlebar only).
//
// 19-02-2006
// aphplix.create_object doesn't seem to be returning a valid object
//
// draging a dragable frame inside a window that is set scrollable (or not) 
// doesn't work properly. it's to do with the overflow style. also the dragable
// item should really be able to jump out of the window and be dropped on 
// another window ?? actualy they can be dropped on another window, the dragable
// object is not visible but the dhtml library still tracks the position of the
// object. when a dragable object is picked up we need to detach it from the
// parent object, then once the object is dropped we need to either attach it to
// a new object (possibly the same object), or send it back where it came from.
// need to configure, drag_externaly = TRUE|FALSE
//
// 20-02-2006
// buttons don't look like they are pushed when you push them
// datetime inputs display current date and time by default, even if value != now
// 23-02-2006
// MARGIN : conflict with style.margin !!!!
// direct style names like border-width don't match our naming conventions
//
// 09-03-2006
// maximize -> minimize -> (un)maximize causes unexpected results.
// still some other issues with min/maximize combinations
//
// 13-03-2006
// data control should only save values that have changed, but also have a mode
// to always save everything
//
// 22-03-2006
// sometimes cursor in an input box doesn't display if the input is over another
// window, sometimes it displays when another window is on top of the input box
// that has focus. IT"S THE CHILD AREA ! this is a serious problem ! the cursor
// dissapears over the top of a child area (even when minimized) of a window created
// before the focused window
// this was the mozilla caret over scrollable div bug, fixed it by removing all
// overflow:auto styles and implementing our own scroll bars in javascript
//
// initial date value 'now' would not be translated to a date code in dateedit boxes

//----------------------------------------------------------------------------//
// NOTES
//----------------------------------------------------------------------------//
/**
 * APhPLIX
 *
 * APhPLIX Toolkit Javascript library
 *
 * APhPLIX is a toolkit for building dynamic WebBrowser-based applications which 
 * look, feel and act like traditional window-based applications.  APhPLIX utilizes 
 * DHTML, AJAX, DOM, Javascript and PHP to create this unique style of application.
 *
 * @file	aphplix.js
 * @package APhPLIX_Javascript_Client
 * @author Jared 'flame' Herbohn
 * @version 6.05
 * @copyright 2005-2006 Jared 'flame' Herbohn, http://www.aphplix.org
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License (version 2)
 *
 */
 
//----------------------------------------------------------------------------//
// TODO
//----------------------------------------------------------------------------//
// add onminimize, onmaximize, onresize etc. functions to windows
// add evt object to all of our custom events
// modal/non-modal windows !!!!
// do away with the browsers event system and build our own
// failed login should return the request sent to the server
// 		this will be sent back to the server along with the login
// 'query progress' bar(s)
//		attached to a query it will show the state so user can tell what is
//		going on while they wait for something to hapen after clicking

//----------------------------------------------------------------------------//
// JAVASCRIPT FUNCTIONS
//----------------------------------------------------------------------------//

//----------------------------------------------------------------//
// TRUE
//----------------------------------------------------------------//
/**
 * TRUE
 *
 * true
 *
 * Javascript true & false are lowercase (case sensative).
 * The APhPLIX convention is to use uppercase TRUE & FALSE.
 *
 * @type	bool
 * @package aphplix
 * @variable
 */
var TRUE = true;

//----------------------------------------------------------------//
// FALSE
//----------------------------------------------------------------//
/**
 * FALSE
 *
 * false
 *
 * Javascript true & false are lowercase (case sensative).
 * The APhPLIX convention is to use uppercase TRUE & FALSE.
 *
 * @type	bool
 * @package aphplix
 * @variable
 */
var FALSE = false;


var DESTROY = -1; // needed ?
 
//----------------------------------------------------------------------------//
// aphplix_class()
//----------------------------------------------------------------------------//
/**
 * aphplix_class()
 * 
 * aphplix main javascript class
 *
 * handles all of the client side functionality of aphplix
 *
 * @package APhPLIX_Javascript_Client
 * @class	aphplix
 */
function aphplix_class()
{
	// -----------------------------------------------------------------------//
	// CONFIG
	// -----------------------------------------------------------------------//
	
	// address of the server datalink script
	this.ajax_link = "";
	
	// display the debug window
	this.debugmode = FALSE;
	
	// display pop up message windows
	this.messagemode = FALSE;
	
	// draw objects with missing widget builder
	this.drawmissing = FALSE;
	
	// default objects array
	this.objects ={'body':{'child_area':'body', 'id':'body','children':{}}};
	this.objects.body.form = this.objects.body;
	
	// default windows object
	this.windows = { };
	
	// default clipboard object
	this.clipboard = { };
	
	// default focused object
	this.focused = { };
	
	// default system focused object
	this.system_focused = { };
	
	// default taskbar
	this.taskbar = {'height':0};
	
	// default variables
	this.variables = { };
	this.pvariables = { };
	
	// default methods
	this.methods = { };
	
	// default forms
	this.forms = { };
	
	// default input buffer
	this.input_buffer = { };
	
	// default application
	this.application = function()
	{
		this.login();
	}
	
	// start uid counter
	this.uid_count = 1;
	
	//------------------------------------------------------------------------//
	// uid
	//------------------------------------------------------------------------//
	/**
	 * aphplix.uid()
	 *
	 * returns a unique ID
	 *
	 *
	 * @method
	 * @return	string
	 */
	this.uid = function()
	{
		this.uid_count++;
		if (!this.uid_prefix)
		{
			this.uid_prefix = '_' + new Date().getTime();
		}
		var uid = 'uid' + this.uid_prefix + '_' +this.uid_count + '_' + Math.floor(Math.random() * 100);
		return uid;
	}
	
	// --------------------------------------------------------------------------------------------------------------//
	// WINDOWS
	// --------------------------------------------------------------------------------------------------------------//
	 
	//------------------------------------------------------------------------//
	// register_window
	//------------------------------------------------------------------------//
	/**
	 * aphplix.register_window()
	 *
	 * register a window
	 *
	 * used internaly
	 *
	 * @param	{string}		window_id		id of the window
	 * @param	{string}		window_name		name of the window
	 * @return	void
	 * @private
	 */
	this.register_window = function(window_id, window_name)
	{
		this.windows[window_id] = window_name;
		
		// refresh the taskbar
		if (typeof(this.taskbar.refresh) == 'function')
		{
			this.taskbar.refresh();
		}
	}
	
	//------------------------------------------------------------------------//
	// deregister_window
	//------------------------------------------------------------------------//
	/**
	 * aphplix.deregister_window()
	 *
	 * deregister a window
	 *
	 * used internaly
	 *
	 * @param	{string}		window_id		id of the window
	 * @return	void
	 * @private
	 */
	this.deregister_window = function(window_id)
	{
		delete(this.windows[window_id]);
		
		// refresh the taskbar
		if (typeof(this.taskbar.refresh) == 'function')
		{
			this.taskbar.refresh();
		}
	}

	// --------------------------------------------------------------------------------------------------------------//
	// CONFIRM BOX
	// --------------------------------------------------------------------------------------------------------------//
	
	/* aphplix.confirm()
	 *
	 * logs a user in
	 *
	 * @return	bool
	 */
	 
	//------------------------------------------------------------------------//
	// confirm
	//------------------------------------------------------------------------//
	/**
	 * aphplix.confirm()
	 *
	 * Display a confirm dialog box
	 *
	 * Alows the user to confirm or cancel an action. 
	 *
	 * @param	{string}	confirm_action	Javascript to eval on confirm
	 * @param	{string}	cancel_action	optional Javascript to eval on cancel
	 * @param	{string}	message			optional message displayed in window
	 * @param	{string}	title			optional window title
	 * @param	{string}	confirm_text	optional text on confirm button
	 * @param	{string}	cancel_text		optional text on cancel button
	 * @param	{int}		top				optional window position top
	 * @param	{int}		left			optional window position left
	 * @param	{int}		size			optional increase height of window by <i>size</>
	 * @param	{bool}		closable		optional display a widow close button
	 * @return	void
	 */
	this.confirm = function(confirm_action, cancel_action, message, title, confirm_text, cancel_text, top, left, size, closable)
	{
		//__var var confirm_action, cancel_action, message, title, confirm_text, cancel_text, top, left, size, closable;
		
		// destroy existing confirm box
		this.destroy_object('aphplix_confirm');
		
		// build confirm box
		var object = { };
		if (confirm_action)
		{
			object.confirm_action = confirm_action;
		}
		if (cancel_action)
		{
			object.cancel_action = cancel_action;
		}
		if (title)
		{
			object.title = title;
		}
		if (message)
		{
			object.message = message;
		}
		if (confirm_text)
		{
			object.confirm_text = confirm_text;
		}
		if (cancel_text)
		{
			object.cancel_text = cancel_text;
		}
		if (top)
		{
			object.top = top;
		}
		if (left)
		{
			object.left = left;
		}
		if (size)
		{
			object.size = size;
		}
		if (closable)
		{
			object.closable = closable;
		}
		
		// show confirm box
		aphplix.widget.confirm(object);
	}
	
	// --------------------------------------------------------------------------------------------------------------//
	// LOGIN ( & LOGOUT)
	// --------------------------------------------------------------------------------------------------------------//

	//------------------------------------------------------------------------//
	// login
	//------------------------------------------------------------------------//
	/**
	 * aphplix.login()
	 *
	 * Display a login dialog box
	 *
	 * Alows the user to enter user_name & password
	 *
	 * @return	void
	 */
	this.login = function()
	{
		// clear stuff
		delete(this.session_id);
		delete(this.user_name);
		delete(this.password);
		
		// display login box
		// id of the loginbox window = 'aphplix_login'
		var login = aphplix.widget.login();
		login.front();
	}

	//------------------------------------------------------------------------//
	// do_login
	//------------------------------------------------------------------------//
	/**
	 * aphplix.do_login()
	 *
	 * logs a user in
	 *
	 * do_login is called by the login dialog box to login a user. Can also be called directly.
	 * username & password are stored locally at aphplix.user_name & aphplix.password .
	 *
	 * any qued server requests that were previously rejected by the server because the user
	 * was not loged in will be re-sent along with the login request.
	 *
	 * @param	string	user_name	username
	 * @param	string	password	password
	 * @return	void
	 * @see aphplix.login
	 */
	this.do_login = function(user_name, password)
	{
		// destroy the login box
		this.destroy_object('aphplix_login');
	
		// save login details localy
		this.user_name = user_name;
		this.password = password;
		
		// send stuff to the server
		stuff = {
			'type': 'login',
	    	'user_name': user_name,
			'password': password
	    };
		// send waiting requests
		if (typeof(this.login_request) == 'object')
		{
			stuff.request = this.login_request;
			delete(this.login_request);
		}
		this.talk(stuff);
	}

	//------------------------------------------------------------------------//
	// try_login
	//------------------------------------------------------------------------//
	/**
	 * aphplix.try_login()
	 *
	 * try to login
	 *
	 * Attempts to login with the current username & password (found at aphplix.user_name & aphplix.password)
	 * alternativly displays the login dialog box.
	 *
	 * @return	void
	 */
	this.try_login = function()
	{
		// clear the session id			
		delete(this.session_id);
		if(this.user_name && this.password)
		{
			// auto login
			stuff = {
				'type': 'login',
				'user_name': this.user_name,
				'password': this.password
			};
			// send waiting requests
			if (typeof(this.login_request) == 'object')
			{
				stuff.request = this.login_request;
				delete(this.login_request);
			}
			this.talk(stuff);
		}
		else
		{
			// display login box
			this.login();
		}
	}

	//------------------------------------------------------------------------//
	// logout
	//------------------------------------------------------------------------//
	/**
	 * aphplix.logout()
	 *
	 * logs a user out
	 *
	 * @return	void
	 */
	this.logout = function()
	{
		// destroy stuff
		// TODO !!!!
	
		// send stuff to the server
		stuff = {
			'type': 'logout',
	    	'logout': this.session_id,
			'password': password
	    };
		this.talk(stuff);
		
		// kill any waiting requests
		if (typeof(this.login_request) == 'object')
		{
			delete(this.login_request);
		}
		
		// call login
		this.login
	}

	// --------------------------------------------------------------------------------------------------------------//
	// RESET & DESTROY
	// --------------------------------------------------------------------------------------------------------------//
	
	/* aphplix.reset()
	 *
	 * reset the application
	 *
	 * @return	bool
	 */
	this.reset = function()
	{
		// destroy all variables
		this.destroy_variables();
		
		// destroy all objects
		this.destroy_objects();
		
		// restart the application
		this.application();
		
	}
	
	this.destroy_variables = function()
	{
	
	}

	this.destroy_objects = function()
	{
	
	}



	// --------------------------------------------------------------------------------------------------------------//
	// EVENTS & FUNCTIONS
	// --------------------------------------------------------------------------------------------------------------//
	
	//------------------------------------------------------------------------//
	// server_function
	//------------------------------------------------------------------------//
	/**
	 * aphplix.server_function()
	 *
	 * run a PHP function on the APhPLIX server
	 *
	 * The aphplix.server_function() method sends a request to the APhPLIX server
	 * to run a server-side PHP function.
	 * 
	 * IMPORTANT NOTE : 
	 * The aphplix.server_function() method does not run arbitrary PHP functions,
	 * it will only run functions that have been defined as methods of the PHP
	 * application_functions class.
	 *
	 * @param	{string}	function_name	name of the PHP function to run
	 * @param	{object}	variables		variables to be passed to the PHP function
	 * @return	void
	 */
	this.server_function = function(function_name, variables)
	{
		var stuff = {
			'data' : variables,
			'function' : function_name,
			'type' : "function"
		}
		this.request(stuff);
	}

	/*this.trigger_event = function(type, object, evt)
	{
		// run the system event handler
		if (typeof(aphplix.objects[object]['event_' + type]) == 'function')
		{
			////__aphplix_debug('running system event handler : ' + type + ' on object :' + object);
			aphplix.objects[object]['event_' + type](evt);
		}
		
		// run the user event handler
		if (typeof(aphplix.objects[object]['on' + type]) == 'function')
		{
			////__aphplix_debug('running user event handler : ' + type + ' on object :' + object);
			aphplix.objects[object]['on' + type](evt);
		}
	}*/
	
	// --------------------------------------------------------------------------------------------------------------//
	// VARIABLES
	// --------------------------------------------------------------------------------------------------------------//
	
	//------------------------------------------------------------------------//
	// set_variable
	//------------------------------------------------------------------------//
	/**
	 * aphplix.set_variable()
	 *
	 * set a variable
	 *
	 * The variable will be saved locally & also sent to the APhPLIX server and stored
	 * in the users session. APhPLIX serverside functions can access stored variables
	 * in the users session.
	 *
	 * @param	{string}	name	name of variable to set
	 * @param	{mixed}		value	value to set
	 * @param	{int}		ttl		number of seconds before this variable expires (-1 = never expire, 0 = expire on login/logout)
	 * @return	void
	 */
	this.set_variable = function(name, value, ttl)
	{
		// save localy
		this.variables[name] = value;
		
		// send stuff to the server
		var stuff = {
			'type': 'set',
	    	'name': name,
			'value': value,
			'ttl': ttl
	    };
		this.request(stuff);
	}

	//------------------------------------------------------------------------//
	// unset_variable
	//------------------------------------------------------------------------//
	/**
	 * aphplix.unset_variable()
	 *
	 * unset a variable
	 *
	 * @param	{string}	name	name of variable to unset
	 * @return	void
	 */
	this.unset_variable = function(name)
	{
		// destroy localy
		destroy(this.variables[name]);
		
		// send stuff to the server
		var stuff = {
			'type': 'unset',
	    	'name': name
	    };
		this.request(stuff);
	}
	
	//------------------------------------------------------------------------//
	// get_variable
	//------------------------------------------------------------------------//
	/**
	 * aphplix.get_variable()
	 *
	 * get a variable
	 *
	 * @param	{string}	name	name of variable to get
	 * @return	mixed	the stored variable
	 */
	this.get_variable = function(name)
	{	
		return this.variables[name];
	}

	// --------------------------------------------------------------------------------------------------------------//
	// AJAX (SERVER COMMUNICATION)
	// --------------------------------------------------------------------------------------------------------------//
	
	//------------------------------------------------------------------------//
	// talk
	//------------------------------------------------------------------------//
	/**
	 * aphplix.talk()
	 *
	 * talk to the APhPLIX server
	 *
	 * The aphplix.talk method is the base comunication link between the APhPLIX
	 * Javascript client and the APhPLIX PHP server, All other methods that communicate
	 * with the server call the aphplix.talk method to do so. Normaly the aphplix.talk method
	 * would not be called directly.
	 *
	 * The <i>stuff</i> object has the following format:
	 * 
	 * 		'session_id'	string	(added by the talk method)
	 *		'type'			string	request type ('file_download'|'login'|'request')
	 *		'request'		array	array of request objects
	 *		'user_name'		string	only if type = 'login'
	 *		'password'		string	only if type = 'login'
	 * 
	 * @param	{object}	stuff	stuff to send back to the server
	 * @return	void
	 */
	this.talk = function(stuff)
	{	
		// add the session_id to stuff
		stuff.session_id = this.session_id;
		
		// set error count
		if (!stuff.__aphplix_error_count)
		{
			stuff.__aphplix_error_count = 1;
		}
		else
		{
			stuff.__aphplix_error_count++;
		}
		
		//this.debug(JSON.stringify(stuff));
		
		// register the callbacks
		var handle_reply = this.handle_reply;
		var handle_error = this.handle_error;
		
		// callback binder
		function bindcallback()
		{
			if (req.readyState == 4) {
				if (req.status == 200) {
					TEST:handle_reply(req.responseText, stuff);
					//handle_reply();
				} else {
					handle_error(req);
				}
			}
		}
		
		// send request to the server
		if (window.XMLHttpRequest)
		{
			//native XMLHttpRequest browsers
			var req = new XMLHttpRequest();
			req.onreadystatechange = bindcallback;
			req.open("POST", this.ajax_link, true);
			req.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
			req.send(JSON.stringify(stuff));
		}
		else if (window.ActiveXObject)
		{
			// IE/Windows ActiveX browsers
				var req = new ActiveXObject("Microsoft.XMLHTTP");
			if (req)
			{
				req.onreadystatechange = bindcallback;
				req.open("POST", this.ajax_link, true);
				req.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
				req.send(JSON.stringify(stuff));
			}
		}
	}
	
	//------------------------------------------------------------------------//
	// request
	//------------------------------------------------------------------------//
	/**
	 * aphplix.request()
	 *
	 * send a single request to the APhPLIX server
	 *
	 * Wraps a single Request Object in an array and sends it to the APhPLIX server
	 *
	 * @param	{object}	request	request to be sent to the server
	 * @return	void
	 */
	this.request = function(request)
	{
		var stuff = {
			'type': 'request',
			'request': [request]
		}
		this.talk(stuff);
		delete(stuff);
	}
	
	// --------------------------------------------------------------------------------------------------------------//
	// REPLY HANDLERS
	// --------------------------------------------------------------------------------------------------------------//
	
	//------------------------------------------------------------------------//
	// handle_error
	//------------------------------------------------------------------------//
	/**
	 * aphplix.handle_error()
	 *
	 * handle an error connecting to the APhPLIX server
	 *
	 * @param	{object}	req	XMLHttpRequest object
	 * @return	void
	 * @private
	 */
	this.handle_error = function(req)
	{
	
	}
	
	//------------------------------------------------------------------------//
	// handle_reply
	//------------------------------------------------------------------------//
	/**
	 * aphplix.handle_reply()
	 *
	 * handle a reply from the APhPLIX server
	 *
	 * @param	{string}	reply	reply from the APhPLIX server (his will be a JSON formated array of objects)
	 * @param	{object}	stuff	stuff to send to the server (this is the original object passed to the talk method)
	 * @return	void
	 * @private
	 */
	this.handle_reply = function(reply, stuff)
	{
		//return;
		var return_data = {};
		
		////__aphplix_debug(reply);
		try
		{
			var return_data = eval(reply);
		}
		catch(er)
		{
			//__aphplix_debug('INVALID REPLY FROM SERVER');
			if (typeof(reply) == 'string')
			{
				if (reply.search(/appweb/gi) == -1)
				{
					var server_error = "SERVER ERROR \n" + reply.replace(/(<([^>]+)>)/ig,"");
					
				}
				else
				{
					//var server_error = "SERVER ERROR \n\n The server has returned an unexpected response.\nIf you are waiting for a response from the server (or if you have just performed an important action eg. saving) you may need to re-perform your last action";
					// retry request
					// TODO !!!! -
					// this is a nasty hack because appweb is sometimes returning errors
					// not sure if this even works, no time to test it now, look into it later
					if (stuff.__aphplix_error_count < 5)
					{
						this.talk(stuff);
					}
				}
				aphplix.message(server_error);
			}
			return;
		}
		delete(reply);
		
		var part;
		for(part in return_data)
		{
			switch(return_data[part].type)
			{
				// run a javascript command
				case "command":
					if (typeof(return_data[part].data) == 'string')
					{
						var command_data = return_data[part].data;
						eval(command_data);
						delete(command_data);
					}
					break;
			
				// run a javascript command
				case "login":
					if (typeof(return_data[part].data) == 'object')
					{
						aphplix.login_request = new clone_object(return_data[part].data);
					}
					aphplix.login();
					break;
					
				// server returned an error
				case "error":
					if (typeof(return_data[part].data) == 'string')
					{
						var err_data = return_data[part].data;
						aphplix.message(err_data);
						delete(err_data);
					}
					break;
					
				// display a popup messagebox with copyable text
				case "cmessage":
					if (typeof(return_data[part].data) == 'string')
					{
						var widget = 
						{
							'top': 300,
							'left': 300,
							"width": 400,
							"height": 200
						}
						var popup = aphplix.widget.popup(widget);
						
						var object={
							'type': 'textarea',
							'parent': popup.id,
							'properties':{
								'id': popup.id + '__text',
								'class': 'aphplix_textarea'
							},
							'style':{
								'width': popup.width - 2,
								'height': popup.height - 2
							}
						}
						aphplix.html.create(object, popup.id);
						popup.render();
						aphplix.html.set_property(popup.id + '__text', "value", return_data[part].data);
					}
					break;
				
				// display a popup messagebox
				case "message":
					if (typeof(return_data[part].data) == 'string')
					{
						var msg_data = return_data[part].data;
						aphplix.message(msg_data);
						delete(msg_data);
					}
					break;
				
				// create a DOM object
				case "create_object":
					if (return_data[part].data.autorender !== FALSE)
					{
						return_data[part].data.autorender = TRUE;
					}
					var object = new clone_object(return_data[part].data);
					aphplix.create_object(object);
					delete(object);
					break;
					
				// edit a DOM object
				case "modify_object":
					var object = new clone_object(return_data[part].data);
					aphplix.modify_object(object);
					delete(object);
					break;
				
				// destroy a DOM object
				case "destroy_object":
					if (typeof(return_data[part].data) == 'string')
					{
						var object_data = return_data[part].data;
						aphplix.destroy_object(object_data);
						delete(object_data);
					}
					break;					
				
				// receive data
				case "data":
					var object = new clone_object(return_data[part].data);
					try
					{
						aphplix.objects[object.id].receive(object);
					}
					catch(er)
					{
						//__aphplix_debug('data receive failed for : ' + object.id);
					}
					delete(object);
					break;
					
				// receive data index
				case "data_index":
					var object = new clone_object(return_data[part].data);
					try
					{
						//alert(object.index.name.length);
						aphplix.objects[object.id].receive_index(object.index);
					}
					catch(er)
					{
						//__aphplix_debug('data receive failed for : ' + object.id);
					}
					delete(object);
					break;
					
				// receive data recordset
				case "data_recordset":
					var object = new clone_object(return_data[part].data);
					try
					{
						//alert(object.index.name.length);
						aphplix.objects[object.id].receive_recordset(object.recordset);
					}
					catch(er)
					{
						//__aphplix_debug('data receive failed for : ' + object.id);
					}
					delete(object);
					break;
					
				// receive javascript object
				case "object":
					aphplix.objects[return_data[part].data.object_id][return_data[part].data.id] = new clone_object(return_data[part].data.data);
					break;
					
				// receive JSON object
				case "json":
					aphplix.input_buffer[return_data[part].target] = JSON.parse(return_data[part].data);
					break;
				
				// receive a link to a css file
				case 'css':
					if (typeof(return_data[part].data) == 'string')
					{
						var css = document.createElement("link");
						css.setAttribute("rel", "stylesheet");
						css.setAttribute("type", "text/css");
						css.setAttribute("href", return_data[part].data);
					}
					break;
					
				// receive a file
				case "file":		
				case "download":
					
					if (typeof(return_data[part].data) == 'string')
					{
						var file_data = return_data[part].data;
						if (!aphplix.objects['aphplix_file'])
						{
							// create an iframe to open the file
							var defaults ={
								'name': 'aphplix_file',
								'type': 'iframe',
								'visible': FALSE,
								'height': 0,
								'width': 0,
								'value': ''
							}
							var object = aphplix.create_object(defaults);
							object.render();
						}
						
						if (return_data[part].type == 'download')
						{
							aphplix.objects['aphplix_file'].set_value(file_data);
						}
						else
						{
							var uid = new Date().getTime();
							aphplix.objects['aphplix_file'].set_value(aphplix.ajax_link + '?type=file_download&file_id=' + file_data + '&session_id=' + aphplix.session_id + '&uid=' + uid);
						}

						delete(file_data);
					}							
					break;
				
				// do nothing
				case "noop":
					break;
				
				// print debug data
				case "debug":
					if (typeof(return_data[part].data) == 'string')
					{
						var debug_data = return_data[part].data;
						aphplix.debug(debug_data);
						delete(debug_data);
					}
					break;
			}
			// clean up
			delete(return_data[part]);
		}
		// clean up
		delete(return_data);
	}

	// --------------------------------------------------------------------------------------------------------------//
	// DEBUGGING
	// --------------------------------------------------------------------------------------------------------------//
	
	//------------------------------------------------------------------------//
	// debug
	//------------------------------------------------------------------------//
	/**
	 * aphplix.debug()
	 *
	 * display a debug message in the debug window
	 *
	 * will display a debug message in the debug window if aphplix.debugmode is set to TRUE
	 *
	 * @param	{string}	msg	debug message
	 * @return	bool	returns TRUE if msg was displayed
	 */
	this.debug = function(msg)
	{
		var ret = FALSE;
		if (this.debugmode == TRUE)
		{
			// avoid excessive recursion while building debug
			if(this.debuglock)
			{
				return FALSE;
			}
			// create a debug node if we don't already have one
			if (!aphplix.objects.aphplix_debug)
			{
				// lock debug
				this.debuglock = TRUE;
				
				// make debug window
				debug = this.widget.debug();
				
				// unlock debug
				this.debuglock = FALSE;
			}
			
			// add debug text to the debug node
			this.objects.aphplix_debug.update(msg + "\n");
			ret = TRUE;
		}
		return ret;
	}
	
	//------------------------------------------------------------------------//
	// message
	//------------------------------------------------------------------------//
	/**
	 * aphplix.message()
	 *
	 * display a popup message box
	 *
	 * yes, this is just like the javascript alert function almost ...
	 * if aphplix.messagemode is set to FALSE then message will not be displayed
	 * if aphplix.debugmode is set to TRUE message will also be added to the debug window
	 *
	 * @param	{string}	msg	message
	 * @return	bool	returns TRUE if msg was displayed
	 */
	this.message = function(msg)
	{
		var ret = FALSE;
		if (this.messagemode == TRUE)
		{
			alert(msg);
			ret = TRUE;
		}
		this.debug(msg);
		return ret;
	}
	
	// --------------------------------------------------------------------------------------------------------------//
	// OBJECTS (WIDGETS)
	// --------------------------------------------------------------------------------------------------------------//
	
	//------------------------------------------------------------------------//
	// new_object
	//------------------------------------------------------------------------//
	/**
	 * aphplix.new_object()
	 *
	 * creates a default APhPLIX object (widget) definition
	 *
	 * @param	{string}	object		type of object
	 * @return	object	default object definition
	 */
	this.new_object = function(object)
	{
		return aphplix.widget.defaults(object);
	}
	
	//------------------------------------------------------------------------//
	// create_object
	//------------------------------------------------------------------------//
	/**
	 * aphplix.create_object()
	 *
	 * create an APhPLIX object (widget)
	 *
	 * The object will be created in aphplix.objects & also returned. If an object
	 * already exists with the same object id, the existing object will be destroyed.
	 *
	 * @param	{object}	object	widget definition
	 * @return	object		the created object
	 */
	this.create_object = function(object)
	{
		var obj = aphplix.widget.create(object);
		if (obj.autorender == TRUE)
		{
			obj.render();
		}
		return obj;
	}
	
	//------------------------------------------------------------------------//
	// modify_object
	//------------------------------------------------------------------------//
	/**
	 * aphplix.modify_object()
	 *
	 * modify an APhPLIX object (widget)
	 *
	 * The object will be modified in aphplix.objects & also returned
	 *
	 * @param	{object}	object	widget definition
	 * @return	object		the modified object
	 * @ignore
	 */
	this.modify_object = function(object)
	{
		return aphplix.widget.modify(object);
	}
	
	//------------------------------------------------------------------------//
	// destroy_object
	//------------------------------------------------------------------------//
	/**
	 * aphplix.destroy_object()
	 *
	 * destroy an APhPLIX object (widget)
	 *
	 * @param	{string}	object_id	id of object to be destroyed
	 * @return	void
	 */
	this.destroy_object = function(object_id)
	{
		return aphplix.widget.destroy(object_id);
	}
	
	//------------------------------------------------------------------------//
	// merge_objects
	//------------------------------------------------------------------------//
	/**
	 * aphplix.merge_objects()
	 *
	 * merge two objects
	 *
	 * this function calls itself recursivly to merge multi-dimentional objects.
	 * properties from object2 are merged into object1
	 *
	 * @param	{object}	object1		first object
	 * @param	{object}	object2		second object
	 * @return	object		the merged object
	 */
	this.merge_objects = function(object1, object2)
	{
		return aphplix.widget.merge(object1, object2);
	}
	
	/**
	 * 
	 * @ignore
	 */
	this.fire_event = function(target_id, type, evt)
	{
		if (aphplix.objects[target_id])
		{
			aphplix.objects[target_id].fire_event(type, evt);
		}
	}
	this.real_event = function(target_id, type, evt)
	{
		if (aphplix.objects[target_id])
		{
			aphplix.objects[target_id].real_event(type, evt);
		}
	}
	this.trigger_event = function(target_id, type, evt)
	{
		if (aphplix.objects[target_id])
		{
			aphplix.objects[target_id].trigger_event(type, evt);
		}
	}
	
	// --------------------------------------------------------------------------------------------------------------//
	// FORMS
	// --------------------------------------------------------------------------------------------------------------//
	
	//------------------------------------------------------------------------//
	// render_form
	//------------------------------------------------------------------------//
	/**
	 * aphplix.render_form()
	 *
	 * render a form from a form deffinition
	 *
	 * @param	{mixed}		iform		definition of the form to be rendered (object)
	 *									or id of a form in the aphplix forms object
	 * @param	{string}	location	optional location of forms and methods (by default application forms are used)
	 * @param	{string}	form_id		optional id for the form, this allows creation
	 *									of multiple forms from a single form definition
	 * @return	bool		TRUE if form rendered without error
	 */
	this.render_form = function(iform, location, form_id)
	{
		// define variables
		var visible = FALSE;
		//var form_id;
		//var location;
		var myform;
		
		// check what we were passed 
		if (typeof(iform) == 'object')
		{
			// it's an object, so all is good
		}
		else if (location && typeof(this.forms[location]) == 'object' && typeof(this.forms[location][iform]) == 'object')
		{
			var iform = this.forms[location][iform];
		}
		else if (typeof(this.application) == 'object' && typeof(this.application.forms) == 'object' && typeof(this.application.forms[iform]) == 'object')
		{
			var iform = this.application.forms[iform];
		}
		else
		{
			// we don't have the form, ask the server for it
			// TODO !!!! - not yet implemented on server
			var stuff = {
				'type': 'form',
				'form': iform,
				'form_id': form_id
			};
			//this.request(stuff);
			if (iform != 'frmSplash')
			{
				aphplix.message('Invalid form name or missing form definition : ' + iform);
			}
			return FALSE;
		}

		// iform must have a form object
		if (typeof(iform.form) != 'object')
		{
			aphplix.message('Invalid form object');
			return FALSE;
		}

		// clone the form
		var form = new clone_object(iform);
		
		// set the form id
		if (form_id)
		{
			form.form.id = form_id;
		}
		else
		{
			if (form.form.name)
			{
				var form_id = form.form.name;
				form.form.id = form.form.name;
			}
			else
			{
				var form_id = form.form.id;
				form.form.name = form.form.id;
			}
		}

		// for each object (widget) in the form
		var object;
		for (object in form)
		{
			// for all objects that are not the form
			if (object != 'form')
			{
				// set parent to formname if we don't have a parent
				if (!form[object].parent_id || form[object].parent_id == 'form' || form[object].parent_id == form_id || typeof(form[form[object].parent_id]) != 'object')
				{
					form[object].parent_id = form_id;
				}
				else
				{
					// add form name to the start of the parent name
					form[object].parent_id = form[form[object].parent_id].id;
				}
				
				// add form id to all ids
				form[object].name = object;
				form[object].id = form[object].parent_id + '_' + form[object].name;
			}
			
			// create the object
			if (form.form.design_mode == TRUE)
			{
				// set design mode
				form[object].design_mode = TRUE;

				try
				{
					this.widget.create(form[object]);
				}
				catch(er)
				{
					if (object == 'form')
					{
						aphplix.message('Error creating form : ' + form_id);
						return FALSE;
					}
					else
					{
						aphplix.message('Error creating object : ' + form_id + '_' + object);
					}
				}
			}
			else
			{
				this.widget.create(form[object]);
			}
			
		}

		// add the form to all the objects we just created
		if (typeof(this.objects[form_id]) == 'object')
		{
			myform = this.objects[form_id];
			myform.set_property_recursive('form', myform);
	
			if (form.form.design_mode != TRUE)
			{
				try
				{
					// add methods to form
					if (location && typeof(this.methods[location]) == 'object' && typeof(this.methods[location][form_id]) == 'function')
					{
						this.methods[location][form_id]();
						
					}
					else if (typeof(this.application) == 'object' && typeof(this.application.methods) == 'object' && typeof(this.application.methods[form_id]) == 'function')
					{
						this.application.methods[form_id]();
					}
				}
				catch(er)
				{
					aphplix.message('Error adding methods to form : ' + form_id);
				}
			}
			
			// render the form
			if (form.form.design_mode == TRUE)
			{
				try
				{
					myform.render();
				}
				catch(er)
				{
					aphplix.message('Error rendering form : ' + form_id);
					return FALSE;
				}
			}
			else
			{
				myform.render();
			}
			
			if (form.form.design_mode != TRUE)
			{
				// run the onload event
				if (typeof(myform.onload) == 'function')
				{
					myform.onload();
				}
			}
			return TRUE;
		}
		else
		{
			aphplix.message('Form was not created : ' + form_id);
			return FALSE;
		}
	}
	
	// --------------------------------------------------------------------------------------------------------------//
	// BROWSER BEHAVIOUR
	// --------------------------------------------------------------------------------------------------------------//
	
	//------------------------------------------------------------------------//
	// bs_killer
	//------------------------------------------------------------------------//
	/**
	 * aphplix.bs_killer()
	 *
	 * stop backspace key from activating the browsers 'history back' function
	 *
	 * @param	{object}	evt		browser event object
	 * @return	void
	 * @private
	 */
	this.bs_killer = function(evt) 
	{
		// get the event object
		if (!evt)
		{
			var evt = window.event;
		}
		
		if (evt.KeyCode)
		{
			var keycode = evt.KeyCode;
		}
		else if (evt.which)
		{
			var keycode = evt.which;
		}
		
		// TODO !!!!
		// fix this hack
		// for some reason we are not geting a value for keycode here in IE
		// no mater what object we are in
		// don't allow keypress in non aphplix objects (works in IE)
		if (evt.srcElement)
		{
			if (!evt.srcElement.aphplix_id)
			{
				evt.returnValue = FALSE;
			}
			else
			{
				evt.returnValue = FALSE;
			}
		}

		// prevent backspace
		if (keycode == 8)
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
	
	//------------------------------------------------------------------------//
	// oncontextmenu
	//------------------------------------------------------------------------//
	/**
	 * aphplix.oncontextmenu()
	 *
	 * catch the right click context menu and disable it if required
	 *
	 * @param	{object}	evt		browser event object
	 * @return	void
	 * @private
	 */
	this.oncontextmenu = function(evt) 
	{
		// get the event object
		if (!evt)
		{
			var evt = window.event;
		}
		// get the event target
		if (evt.target)
		{
			// we already have the target
		}
		else if (evt.srcElement)
		{
			evt.target = evt.srcElement;
		}
		
		// display the aphplix context menu
		if (aphplix.contextmenu && typeof(aphplix.contextmenu) == 'object' && typeof(aphplix.contextmenu.show) == 'function')
		{
			if (aphplix.contextmenu.show(evt) === FALSE) 
			{
				return FALSE;
			}
		}
		
		// global no contextmenu option
		if (aphplix.global_contextmenu === FALSE)
		{
			return FALSE;
		}
		
		// desktop context menu
		if (aphplix.desktop_contextmenu === FALSE)
		{
			if (evt.target.tagName == 'HTML' || evt.target.tagName == 'BODY')
			{
				return FALSE;
			}
		}
		
		// disable context menu if required
		// disable context menu for a widget by setting widget.contextmenu = FALSE
		if(evt.target.aphplix_id && aphplix.objects[evt.target.aphplix_id] && aphplix.objects[evt.target.aphplix_id].contextmenu === FALSE)
		{
			return FALSE;
		}
	}	
	
	this.body_eventhandler =function(evt, source, type)
	{
		// only run event handler if body is the target
		if (evt.target.tagName == 'HTML' || evt.target.tagName == 'BODY')
		{
			return aphplix.widget._eventhandler(aphplix.objects['body'], evt, source, type);
		}
	}
	
	this.init = function()
	{
		// build a body object
		var body = new widget_skell_class('body');
		body.form = aphplix.objects.body;
		body.child_area = 'body';
		// capture body events
		document.onblur = function(event) {aphplix.body_eventhandler(event, 'html')};
		document.onfocus = function(event) {aphplix.body_eventhandler(event, 'html')};
		document.onclick = function(event) {aphplix.body_eventhandler(event, 'html')};
		//document.onunload = function(event) {aphplix.body_eventhandler(event, 'html')};
		document.onmousedown = function(event) {aphplix.body_eventhandler(event, 'html')};
		document.onmouseup = function(event) {aphplix.body_eventhandler(event, 'html')};
		
		// run init functions
		var name;
		for (name in this.init_functions)
		{
			if (typeof(this.init_functions[name]) == 'function')
			{
				this.init_functions[name]();
			}
		}
	}
	
	this.init_functions = {};
	
// end of aphplix class
}


// instanciate the aphplix object
aphplix = new aphplix_class;

// prevent backspace
document.onkeydown = function(event) {aphplix.bs_killer(event)};
document.onkeypress = function(event) {aphplix.bs_killer(event)};
document.onkeyup = function(event) {aphplix.bs_killer(event)};

// capture right click
document.oncontextmenu = function(event) {return aphplix.oncontextmenu(event);};

// --------------------------------------------------------------------------------------------------------------//
// ERRORS
// --------------------------------------------------------------------------------------------------------------//

/**
 * JS ERROR HANDLER
 * @ignore
 */
function aphplix_error(msg,url,l)
{
	var err = '';
	// suppress some internal errors
	if (String(msg).search(/aphplix confirm triggered/g) != -1)
	{
		// firefox stop
	}
	else if (String(msg).search(/exception thrown/gi) != -1)
	{
		// ie stop
	}
	else if (String(msg).search(/object.event_target/g) != -1)
	{
	}
	// show all other errors
	else
	{
		// avoid constant repartition of error messages
		var this_error = msg + '::url::' + url + '::line::' + l;
		if (this_error == aphplix.last_error)
		{
			aphplix.last_error_count++;
			if (Number(aphplix.last_error_count) == 5)
			{
				err  = "MAX ERROR COUNT REACHED\n\nThe following error has recurred 5 times consecutively, further identical error messages will not be displayed ";
				err  += "until the error count is reset. There may be a problem with your application and you may need to reload your browser. \n\n\n";
			}
			else if (Number(aphplix.last_error_count) > 5)
			{
				return TRUE;
			}
		}
		else
		{
			// set last error
			aphplix.last_error = this_error;
			aphplix.last_error_count = 0;
		}
		
		// build error message box
		err += "An Error was encountered.\n\n"
		err += "Error: " + msg + "\n"
		err += "URL: " + url + "\n"
		err += "Line: " + l + "\n\n"
		err += "Click OK to continue.\n\n"
		
		// show error message
		aphplix.message(err);
	}
	return true
}
onerror=aphplix_error;
//window.onerror=aphplix_error;
//document.onerror=aphplix_error;
