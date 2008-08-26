//----------------------------------------------------------------------------//
// VixenAjaxClass
//----------------------------------------------------------------------------//
/**
 * VixenAjaxClass
 *
 * Vixen ajax class
 *
 * Vixen ajax class
 *
 *
 *
 * @package	framework_ui
 * @class	Vixen.Ajax
 */
function VixenAjaxClass()
{
	// This is used to check that a form doesn't get submitted twice, before the first submittion has recieved a reply
	this.strFormCurrentlyProcessing	= null;
	
	//------------------------------------------------------------------------//
	// CallAppTemplate
	//------------------------------------------------------------------------//
	/**
	 * CallAppTemplate()
	 *
	 * Executes an AppTemplate method via ajax
	 *
	 * Executes an AppTemplate method via ajax
	 *
	 * @param	string	strClass				Name of the AppTemplate class to use, minus the AppTemplate prefix
	 *											(ie for AppTemplateAccount, use "Account")
	 * @param	string	strMethod				Name of the AppTemplate method to use
	 * @param	object	objObjects				parameters to be passed to the server.  These will be available in the
	 *											DBO() object structure
	 *											(ie objObjects.Account.Id will be available in php as DBO()->Account->Id)
	 * @param	string	strTargetType			The type of target for the resultant output of the AppTemplate method
	 *											valid options are (Div, Popup, Page, JavaScript)
	 * @param	boolean	bolShowLoadingSplash	optional.  Set to true to have the "Please Wait" splash display if the 
	 *											ajax request takes more than 1 second
	 * @param	boolean	bolIsForm				optional.  Set to true to guarantee that this particular ajax call
	 *											is the only "form" being submitted.  This is to error trap against 
	 *											the same form being submitted multiple times
	 * @param	mixed	mixDivIdOrJSFunction	optional
	 *											string	:	id of a container div that the response will be loaded into,
	 *														if the response is raw html
	 *											function pointer	:	response handler
	 *
	 * @return	void
	 * @method
	 */
	this.CallAppTemplate = function(strClass, strMethod, objObjects, strTargetType, bolShowLoadingSplash, bolIsForm, mixDivIdOrJSFunction)
	{
		var objSend			= {};
		objSend.Class		= strClass;
		objSend.Method		= strMethod;
		objSend.Objects		= objObjects;
		objSend.TargetType	= strTargetType;
		if (mixDivIdOrJSFunction != undefined)
		{
			if (typeof mixDivIdOrJSFunction == 'string')
			{
				objSend.strContainerDivId = mixDivIdOrJSFunction;
			}
			if (typeof mixDivIdOrJSFunction == 'function')
			{
				objSend.fncResponseHandle = mixDivIdOrJSFunction;
			}
		}

		// Check if this AppTemplate call is a form submission
		// (a form submission will lock all other form submittions until it has recieved its reply from the server)
		if (bolIsForm)
		{
			if (this.strFormCurrentlyProcessing != null)
			{
				// A "form" is currently being processed, so this one cannot be executed
				return;
			}
			else
			{
				// Declare this AppTemplate call as the current form being submitted
				this.strFormCurrentlyProcessing = strClass + strMethod;
				objSend.FormId = this.strFormCurrentlyProcessing;
			}
		}

		if (bolShowLoadingSplash == true)
		{
			// Draw the Page Loading splash (this will show after 1 second)
			Vixen.Popup.ShowPageLoadingSplash("Please wait", null, null, null, 1000);
		}

		// Send object
		this.Send(objSend);
	}
	
	//------------------------------------------------------------------------//
	// SendForm
	//------------------------------------------------------------------------//
	/**
	 * SendForm()
	 *
	 * Executes an AppTemplate method via ajax, passing all input values included in the form specified
	 *
	 * Executes an AppTemplate method via ajax, passing all input values included in the form specified
	 *
	 * @param	string	strFormId				Id of the form which will be submitted to the AppTemplate Method for processing
	 * @param	string	strButton				Value of the Button used to trigger the form submittion (I think this has to be
	 *											the label displayed on the button)
	 * @param	string	strClass				Name of the AppTemplate class to use, minus the AppTemplate prefix
	 *											(ie for AppTemplateAccount, use "Account")
	 * @param	string	strMethod				Name of the AppTemplate method to use
	 * @param	string	strTargetType			optional, The type of target for the resultant output of the AppTemplate method
	 *											valid options are (Div, Popup, Page)
	 * @param	string	strId					optional, PopupId, if the form is rendered on a popup
	 * @param	string	strSize					optional, size of the popup, if the form is rendered on a popup
	 * @param	string	strContainerDivId		optional, the id of the container div that the HtmlElement sits in, which contains the form
	 *
	 * @return	void
	 * @method
	 */
	this.SendForm = function(strFormId, strButton, strClass, strMethod, strTargetType, strId, strSize, strContainerDivId)
	{
		var intKey;
		var strType;
		var objFormElement;
		var strElementName;
		var mixValue;
		var bolAsArray;

		// create object to send
		var objSend					= {};
		objSend.Class				= strClass;
		objSend.Method				= strMethod;
		objSend.ButtonId			= strButton;
		objSend.TargetType			= strTargetType;
		objSend.strId				= strId;
		objSend.strSize				= strSize;
		objSend.strContainerDivId	= strContainerDivId;
		objSend.Objects				= {};
		// Add values from form to object
		
		// Retrieve the form which is being submitted (the form as an element)
		objFormElement = document.getElementById(strFormId);

		for (intKey in objFormElement.elements)
		{
			strElementName	= objFormElement.elements[intKey].name;

			// only process input elements that have names
			if (strElementName == null)
			{
				continue;
			}
			
			// check for the special case of VixenFormId hidden input
			if (strElementName == "VixenFormId")
			{
				objSend.FormId = objFormElement.elements[intKey].value;
				continue;
			}
			
			// process the name of the element
			intDotIndex = strElementName.indexOf(".", 0);
			strObjectName = strElementName.substr(0, intDotIndex);
			strPropertyName = strElementName.substr(intDotIndex + 1, strElementName.length);
			
			bolAsArray = (strPropertyName.indexOf("[]") == (strPropertyName.length - 2));
			if (bolAsArray)
			{
				strPropertyName = strPropertyName.substring(0, strPropertyName.length - 2);
			}
			
			// if the element's name is not in the form Object.Property then do not process it further
			if ((strObjectName.length == 0) || (strPropertyName.length == 0))
			{
				continue;
			}
			
			// Check if the Object already exists in the objSend.Objects struct
			if (objSend.Objects[strObjectName] == undefined)
			{
				// Instantiate the Object
				objSend.Objects[strObjectName] = {};
			}
			
			strType = objFormElement.elements[intKey].type;
			switch (strType)
			{
				case "select-one": //not accurately working yet
					//alert("select box select singular");
					if (objFormElement.elements[intKey].getAttribute('valueIsList') == null)
					{
						mixValue = objFormElement.elements[intKey].value;
						/*
						//select only highlighted items in list
						var intSelections = 0;
						for (intInnerKey = 0; intInnerKey < objFormElement.elements[intKey].length; intInnerKey++)
						{
							if (objFormElement.elements[intKey].options[intInnerKey].selected)
							{
								intSelections = parseInt(objFormElement.elements[intKey].options[intInnerKey].value);
							}
						}
						mixValue = intSelections;
						*/
						break;
					}
					else
					{
						var intSelections = 0;
						for (intInnerKey = 0; intInnerKey < objFormElement.elements[intKey].length; intInnerKey++)
						{
							intSelections += parseInt(objFormElement.elements[intKey].options[intInnerKey].value);
						}
						mixValue = intSelections;
						break;
					}
					break;
				case "select-multiple":
					// Check if the "valueIsList" attribute has been specified for the multi-select combobox
					if (objFormElement.elements[intKey].getAttribute('valueIsList')==null)
					{
						//mixValue will be an array storing each of the highlighted values
						mixValue = new Array();
						for (intInnerKey = 0; intInnerKey < objFormElement.elements[intKey].length; intInnerKey++)
						{
							if (objFormElement.elements[intKey].options[intInnerKey].selected)
							{
								mixValue.push(objFormElement.elements[intKey].options[intInnerKey].value);
							}
						}
						break;
					}
					else
					{
						// mixValue will be an array storing the value of each item in the list
						mixValue = new Array();
						
						for (intInnerKey = 0; intInnerKey < objFormElement.elements[intKey].length; intInnerKey++)
						{
							mixValue.push(objFormElement.elements[intKey].options[intInnerKey].value);
						}
						break;
					}
				case "checkbox":
					if (objFormElement.elements[intKey].checked)
					{
						mixValue = 1;
					}
					else
					{
						mixValue = 0;
					}
					break;
				case "radio":
					// only use the value of the radio button, if it is the one that is currently selected
					if (!objFormElement.elements[intKey].checked)
					{
						// this radio button isn't selected so don't process it
						continue;
					}
					mixValue = objFormElement.elements[intKey].value;
					break;
				case "button":
					continue;
				default:
					//alert(strType);
					mixValue = objFormElement.elements[intKey].value;			
					break;
			}
			
			if (bolAsArray && objSend.Objects[strObjectName][strPropertyName] == undefined)
			{
				objSend.Objects[strObjectName][strPropertyName] = new Array();
			}
			
			if (bolAsArray)
			{
				objSend.Objects[strObjectName][strPropertyName].push(mixValue);
			}
			else
			{
				objSend.Objects[strObjectName][strPropertyName] = mixValue;
			}
		}			

		
		// Output each Object.Property stored in objSend.Objects
		/*for (strObject in objSend.Objects)
		{
			for (strProperty in objSend.Objects[strObject])
			{
				alert("objSend.Objects."+ strObject +"."+ strProperty +" = "+ objSend.Objects[strObject][strProperty]);
			}
		}*/

		if (this.strFormCurrentlyProcessing != null)
		{
			// A form is currently being processed.  Do not submit this one
			return;
		}
		else
		{
			// It is safe to submit this form
			this.strFormCurrentlyProcessing = objSend.FormId;
		}
		
		// Draw the Page Loading splash (this will show after 1 second)
		Vixen.Popup.ShowPageLoadingSplash("Please wait", null, null, null, 1000);

		// send object
		this.Send(objSend);
	}

	// AJAX Send
	this.Send = function(objObject)
	{
		// Turn the cursor into the "waiting" icon
		document.body.style.cursor = "wait";
//window.style.cursor = "wait"

		/*
		for (strObject in objObject.Objects)
		{
			for (strProperty in objObject.Objects[strObject])
			{
				alert("1). objObject.Objects."+ strObject +"."+ strProperty +" = "+ objObject.Objects[strObject][strProperty]);
			}
		}
		*/
		
		// store our object before sending, along with a transaction ID
		//this.objData = objObject;

		// set the target page
		var page_url = "ajax_link.php";
		
		// register the callbacks
		var local_handle_reply = this.HandleReply;
		var local_handle_error = this.HandleError;

		switch (objObject.TargetType)
		{
			case "Div":
				objObject.HtmlMode = TRUE;
				break;
			case "Popup":
				objObject.HtmlMode = TRUE;
				break;
			case "Page":
				objObject.HtmlMode = TRUE;
			default:
				objObject.TargetType = "Page";
				objObject.HtmlMode = TRUE;
				break;
		}

		// callback binder
		function bindcallback()
		{
			if (req.readyState == 4)
			{
				if (req.status == 200)
				{
					TEST:local_handle_reply(req.responseText, objObject, req);
					//handle_reply();
				}
				else
				{
					local_handle_error(req);
				}
			}
		}
	
		// send request to the server
		if (window.XMLHttpRequest)
		{
			//native XMLHttpRequest browsers
			var req = new XMLHttpRequest();
			req.onreadystatechange = bindcallback;
			req.open("POST", page_url, true);
			req.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
			req.send(JSON.stringify(objObject));
		}
		
		else if (window.ActiveXObject)
		{
			// IE/Windows ActiveX browsers
			var req = new ActiveXObject("Microsoft.XMLHTTP");
			if (req)
			{
				req.onreadystatechange = bindcallback;
				req.open("POST", page_url, true);
				req.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
				req.send(JSON.stringify(objObject));
			}
		}
		return TRUE;
	}
	
	// AJAX handle_reply
	this.HandleReply = function(strReply, objObject, req)
	{
		// Reset the cursor to its default
		document.body.style.cursor = null;
		
		// Remove the page loading splash
		Vixen.Popup.ClosePageLoadingSplash();
		
		// Reset the FormProcessing flag but only if this is the reply which relates to the currently processing form
		if (Vixen.Ajax.strFormCurrentlyProcessing != null)
		{
			// A form is currently being processed.  If this is the reply, then reset the FormCurrentlyProcessing variable
			if (objObject.FormId != undefined && objObject.FormId == Vixen.Ajax.strFormCurrentlyProcessing)
			{
				Vixen.Ajax.strFormCurrentlyProcessing = null;
			}
		}
		
		var objData = {};
		
		// If the reply starts with "//JSON" then this is a json object storing a list of commands
		if (strReply.substr(0, 6) == "//JSON")
		{	
			// We are working with a JSON object so convert it to a javascript object
			var strJsonCommands = strReply.substr(6);
			
			try
			{
				// Convert reply into data object
				eval("objData = " + strJsonCommands);

				if (!objData)
				{
					ajaxHandler(FALSE);
					return;
				}
					
				ajaxHandler(objData);
			}
			catch(er)
			{
				ajaxError(er, strReply);
			}
		}
		else if (objObject.fncResponseHandle != undefined && typeof objObject.fncResponseHandle == "function")
		{
			objObject.fncResponseHandle(req, objObject);
		}
		else
		{
			// The reply must be HTML code
			HandleHtmlModeReply(strReply, objObject);
		}
		
		// clean up
		delete(strReply);
		delete(objData);
	}
	
	HandleHtmlModeReply = function(strReply, objObject)
	{
			if (objObject.HtmlMode)
			{
				switch (objObject.TargetType)
				{
					case "Popup":
						elmPopup = Vixen.Popup.GetPopupElement(objObject.strId);
						if (elmPopup != null && (elmPopup.style.width == Vixen.Popup.objSizes[objObject.strSize.toLowerCase()]))
						{
							// The popup exists, and its size has not changed.  Just update the contents of the popup
							Vixen.Popup.SetContent(objObject.strId, strReply, objObject.strTitle);
						}
						else
						{
							Vixen.Popup.Create(objObject.strId, strReply, objObject.strSize, "centre", objObject.WindowType, objObject.strTitle);
						}
						break;
					case "Div":
						// retrieve the current container div element
						var id = objObject.strContainerDivId;
						var elmOldContainer = document.getElementById(id);
						if (!elmOldContainer)
						{
							alert("Error: The container div does not exist.\nContainer Div Id = '" + id +"'");
							return FALSE;
						}
						
						// Create a new container div
						var elmNewContainer = elmOldContainer.cloneNode(false);
						elmNewContainer.setAttribute('Id', id);
						elmNewContainer.innerHTML = strReply;
						
						// Replace the old div with the new one
						elmOldContainer.parentNode.replaceChild(elmNewContainer, elmOldContainer);
						break;
					case "Page":
						//FIX ME! This looks like it's working properly, but if you reload the page, none of the styling is loaded
						objDoc = document.open("text/html", "replace");
						objDoc.write(strReply);
						objDoc.close();
						break;
					default:
						ajaxError(null, strReply);
				}
			}
	}
	
	
	// Handle each command in the AJAX reply
	//Below is the orginal sig.. but it didn't work :(
	//this.ajaxHandler = function(objInput)
	function ajaxHandler(objInput)
	{
		for (intKey in objInput)
		{
			if (typeof objInput[intKey] == 'function') continue;
			switch (objInput[intKey].Type)
			{
				case "ClosePopup":
					Vixen.Popup.Close(objInput[intKey].Data);
					break;
				case "UpdateServicePopup": // I think this should be removed
					alert(objInput[intKey].Service);
					document.getElementById(objInput[intKey].Service).innerHTML=objInput[intKey].Plan;			
					break;
				case "Alert":
					Vixen.Popup.Alert(objInput[intKey].Data);
					break;
				case "ModalAlert":
					Vixen.Popup.Alert(objInput[intKey].Data, null, null, "modal");
					break;
				case "AlertReload":
					strContent = "<div align='center'><p>" + objInput[intKey].Data + "</p>" +
									"<p><input type='button' id='VixenAlertOkButton' value='OK' onClick='Vixen.Popup.Close(\"VixenAlertBox\");window.location = window.location;'></p></div>\n" +
									"<" + "script type='text/javascript'>document.getElementById('VixenAlertOkButton').focus()</" + "script>\n";
					Vixen.Popup.Create('VixenAlertBox', strContent, 'AlertSize', 'centre', 'autohide-reload');
					break;
				case "AlertAndExecuteJavascript":
					// Execute any init script immediately
					if (objInput[intKey].Data.ScriptInit) 
					{
						eval(objInput[intKey].Data.ScriptInit);
					}
					// Prepare any onClose script to be executed when the 'ok' button is clicked
					var onClose = "";
					if (objInput[intKey].Data.ScriptOnClose)
					{
						onClose += objInput[intKey].Data.ScriptOnClose;
					}
					Vixen.OnClosePopupFunction = new Function(onClose);
					// Render an alert popup with no default 'on close' behaviour other than hiding the popup
					strContent = "<div align='center'><p>" + objInput[intKey].Data.Message + "</p>" +
									"<p><input type='button' id='VixenAlertOkButton' value='OK' onClick='Vixen.Popup.Close(\"VixenAlertBox\");Vixen.OnClosePopupFunction();'></p></div>\n" +
									"<" + "script type='text/javascript'>document.getElementById('VixenAlertOkButton').focus()</" + "script>\n";
					Vixen.Popup.Create('VixenAlertBox', strContent, 'AlertSize', 'centre', false);
					break;
				case "Reload":
					window.location = window.location;
					break;
				case "LoadCurrentPage":
					// I don't even know if this is used.  It should probably do window.location = window.location instead of the reload()
					// I think this is depricated.  It is not currently used in ui_app
					window.location.reload();
					break;
				case "AlertAndRelocate":
					strContent = "<div align='center'><p>" + objInput[intKey].Data.Alert + "</p>" +
									"<p><input type='button' id='VixenAlertOkButton' value='OK' onClick='Vixen.Popup.Close(\"VixenAlertBox\");window.location = \""+ objInput[intKey].Data.Location +"\";'></p></div>\n" +
									"<" + "script type='text/javascript'>document.getElementById('VixenAlertOkButton').focus()</" + "script>\n";
					Vixen.Popup.Create('VixenAlertBox', strContent, 'AlertSize', 'centre', 'autohide', null, objInput[intKey].Data.Location);
					break;
				case "ReplaceDivContents":
					// The html code defined in objInput[intKey].Data will be placed in the declared Container Div
					// The current contents of the Container Div will be destroyed
					// retrieve the current container div element
					var elmOldContainer = document.getElementById(objInput[intKey].ContainerDivId);
					if (!elmOldContainer)
					{
						alert("Command: ReplaceDivContents\nError: The container div does not exist\nContainer Div Id = '" + objInput[intKey].ContainerDivId +"'");
						return FALSE;
					}
					
					// Create a new container div
					var elmNewContainer = document.createElement('div');
					elmNewContainer.setAttribute('Id', objInput[intKey].ContainerDivId);
					elmNewContainer.innerHTML = objInput[intKey].Data;

					// Retrieve the parent element of the current container div element
					var elmParent = elmOldContainer.parentNode;

					// Replace the element
					elmParent.replaceChild(elmNewContainer, elmOldContainer);
					break;
				case "AppendHtmlToElement":
					// The html code defined in objInput[intKey].Data will be Appended to the end of innerHtml of the parent element
					// Note that this is just appending the html to the innerHTML of the declared "Parent" element.  It may not be
					// executing any defined javascript contained within the appended html.  This will have to be tested at some stage.
					// TODO! Test that javascript is working properly, if defined in the html to append
				
					// retrieve the current Parent element
					var elmParent = document.getElementById(objInput[intKey].ElementId);
					if (!elmParent)
					{
						alert("Command: AppendHtmlToElement\nError: The element does not exist\nElement Id = '" + objInput[intKey].ElementId +"'");
						return FALSE;
					}
					
					// Append the html
					var strNewInnerHtml = elmParent.innerHTML + objInput[intKey].Data;
					elmParent.innerHTML = strNewInnerHtml;
					
					break;
				case "SetFocus":
					// Currently this is causing an exception to be thrown.  It is a well known bug in FireFox caused by having autocomplete default
					// to being on for textboxes and similar input elements.  I don't think the throwing of the exception is actually crashing anything.
					// One way of getting around this is to declare all input boxes with autocomplete="off"
					// For example: <input type="text" autocomplete="off" name="fname">blah blah, etc</input>
					var elmElement = document.getElementById(objInput[intKey].Data);
					if (!elmElement)
					{
						alert("Command: SetFocus\nError: The element does not exist\nElement Id = '" + objInput[intKey].Data +"'");
						return FALSE;
					}
					
					elmElement.focus();
					break;
				case "ExecuteJavascript":
					// This probably isn't the safest way to do this. 
					// This block of code may keep executing before the code in objInput[intKey].Data is finished executing, which may cause problems
					eval(objInput[intKey].Data);
					break;
				case "FireEvent":
					Vixen.EventHandler.FireEvent(objInput[intKey].Data.Event, objInput[intKey].Data.EventData);
					break;
				case "VerifyUser":
					Vixen.Popup.ShowAjaxPopup("LoginPopup", "medium", "Login", "User", "DisplayLoginPopup", null, "nonmodal");
					break;
				default:
					alert("Command: (default case)\nError: Don't know how to process command type '" + objInput[intKey].Type + "'");
					break;
			}
		}
	}
	
	// AJAX handle_error
	this.HandleError = function(req)
	{
	
	}
	
	this.AjaxObject = function(strClass, strMethod, objObjects)
	{
		return {
			'Class': strClass,
			'Method': strMethod,
			'Objects': objObjects,
			'FormId' : NULL,
			'ButtonId' : NULL,
			'TargetType' : NULL,
			'strId' : NULL,
			'strSize' : NULL
		};
	}
}

// Create an instance of the Vixen menu class
if (Vixen.Ajax == undefined)
{
	Vixen.Ajax = new VixenAjaxClass();
}



/*
Copyright (c) 2005 JSON.org

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The Software shall be used for Good, not Evil.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

/*
    The global object JSON contains two methods.

    JSON.stringify(value) takes a JavaScript value and produces a JSON text.
    The value must not be cyclical.

    JSON.parse(text) takes a JSON text and produces a JavaScript value. It will
    return false if there is an error.
*/


//----------------------------------------------------------------------------//
// CLASS
//----------------------------------------------------------------------------//
/**
 * JSON
 *
 * JSON conversion class
 *
 * Converts JSON strings to and from Javascript values
 *
 * @package APhPLIX_Javascript_Client
 * @class	JSON
 */
var JSON = function () {
    var m = {
            '\b': '\\b',
            '\t': '\\t',
            '\n': '\\n',
            '\f': '\\f',
            '\r': '\\r',
            '"' : '\\"',
            '\\': '\\\\'
        },
        s = {
            'boolean': function (x) {
                return String(x);
            },
            number: function (x,fmt,tb) {
                return isFinite(x) ? String(x) : 'null';
            },
            string: function (x) {
                if (/["\\\x00-\x1f]/.test(x)) {
                    x = x.replace(/([\x00-\x1f\\"])/g, function(a, b) {
                        var c = m[b];
                        if (c) {
                            return c;
                        }
                        c = b.charCodeAt();
                        return '\\u00' +
                            Math.floor(c / 16).toString(16) +
                            (c % 16).toString(16);
                    });
                }
                return '"' + x + '"';
            },
            object: function (x,fmt, tb) {
                if (x) {
                                        if (fmt == true)
                                        {
                                                var tb;
                                                if (typeof(tb) == 'undefined')
                                                {
                                                        tb = "";
                                                }
                                                var cr = "\n";
                                                var ntb = tb + "\t";
                                        }
                                        else
                                        {
                                                var cr = '';
                                                var tb = '';
                                                var ntb = '';
                                        }
                    var a = [], b, f, i, l, v;
                    if (x instanceof Array) {
                        a[0] = cr + tb + '[' + cr;
                        l = x.length;
                        for (i = 0; i < l; i += 1) {
                            v = x[i];
                            f = s[typeof v];
                            if (f) {
                                v = f(v, fmt, ntb);
                                if (typeof v == 'string') {
                                    if (b) {
                                        a[a.length] = ',' + cr;
                                    }
                                    a[a.length] = v;
                                    b = true;
                                }
                            }
                        }
                        a[a.length] = cr + tb + ']' + cr;
                    } else if (x instanceof Object) {
                        a[0] = cr + tb + '{' + cr;
                        for (i in x) {
                            v = x[i];
                            f = s[typeof v];
                            if (f) {
                                v = f(v, fmt, ntb);
                                if (typeof v == 'string') {
                                    if (b) {
                                        a[a.length] = ','  + cr;
                                    }
                                    a.push(ntb + s.string(i), ':', v);
                                    b = true;
                                }
                            }
                        }
                        a[a.length] = cr + tb + '}';
                    } else {
                        return;
                    }
                    return a.join('');
                }
                return 'null';
            }
        };
    return {
        copyright: '(c)2005 JSON.org',
        license: 'http://www.crockford.com/JSON/license.html',

        //------------------------------------------------------------------------//
        // stringify
        //------------------------------------------------------------------------//
        /**
         * stringify()
         *
         * convert a Javascript value to a JSON string
         *
         * convert a Javascript value to a JSON string
         *
         * @param	mixed	value	any Javascript value
         * @return	string			JSON string
         *
         * @method
         * @see		JSON.fstringify()
         * @see		JSON.parse()
         */
        stringify: function (v) {
            var f = s[typeof v];
            if (f) {
                v = f(v, false);
                if (typeof v == 'string') {
                    return v;
                }
            }
            return null;
        },

        //------------------------------------------------------------------------//
        // fstringify
        //------------------------------------------------------------------------//
        /**
         * fstringify()
         *
         * convert a Javascript value to a JSON string
         *
         * convert a Javascript value to a JSON string with formating (tabs and newlines)
         * to make the output string easier to read. The output of fstringify is slightly
         * larger then the output of stringify but is much easier to read.
         *
         * @param	mixed	value	any Javascript value
         * @return	string			formated JSON string
         *
         * @method
         * @see		JSON.stringify()
         * @see		JSON.parse()
         */
        fstringify: function (v) {
            var f = s[typeof v];
            if (f) {
                v = f(v,true);
                if (typeof v == 'string') {
                    return v;
                }
            }
            return null;
        },

        //------------------------------------------------------------------------//
        // parse
        //------------------------------------------------------------------------//
        /**
         * parse()
         *
         * Parse a JSON string into a Javascript value.
         *
         * Parse a JSON string into Javascript value(s).
         *
         * @param	string	text	JSON string to be parsed
         * @return	bool	returns FALSE on error
         *
         * @method
         * @see		JSON.stringify()
         * @see		JSON.fstringify()
         */
        parse: function (text) {
            try {
                return !(/[^,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]/.test(
                        text.replace(/"(\\.|[^"\\])*"/g, ''))) &&
                    eval('(' + text + ')');
            } catch (e) {
                return false;
            }
        }
    };
}();
