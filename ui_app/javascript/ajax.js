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

	// execute an app template through an ajax call, which doesn't involve form submission
	this.CallAppTemplate = function(strClass, strMethod, objObjects, strTargetType)
	{
		var objSend = {};
		objSend.Class = strClass;
		objSend.Method = strMethod;
		objSend.Objects = objObjects;
		objSend.TargetType = strTargetType;
		
		// send object
		this.Send(objSend);
	}
	

	// Send form
	this.SendForm = function(strFormId, strButton, strClass, strMethod, strTargetType, strId, strSize, strContainerDivId)
	{
		var intKey;
		var strType;
		var objFormElement;
		var strElementName;
		var mixValue;
		
		// create object to send
		var objSend = {};
		objSend.Class = strClass;
		objSend.Method = strMethod;
		//objSend.FormId = strFormId;
		objSend.ButtonId = strButton;
		objSend.TargetType = strTargetType;
		objSend.strId = strId;
		objSend.strSize = strSize;
		objSend.strContainerDivId = strContainerDivId;
		
		// HACK HACK HACK!!! ******************************************************************************************************************
		// I'm setting this to FALSE because it is not defined anywhere
		// If objSend.TargetType == 'Popup' or 'div' then Ajax.send will set HtmlMode = TRUE
		//objSend.HtmlMode = TRUE;
		//objSend.TargetType = "Div";
		// HACK HACK HACK *********************************************************************************************************************
		
		// add values from form to object
		//TODO! Find each element and load it into objSend.Objects.Object.Property
		
		// instantiate the Objects structure
		objSend.Objects = {};
		

		// retrieve the form which is being submitted (the form as an element)
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
			
			objSend.Objects[strObjectName][strPropertyName] = mixValue;
		}			

		/*
		// Output each Object.Property stored in objSend.Objects
		for (strObject in objSend.Objects)
		{
			for (strProperty in objSend.Objects[strObject])
			{
				alert("objSend.Objects."+ strObject +"."+ strProperty +" = "+ objSend.Objects[strObject][strProperty]);
			}
		}
		*/
		
		// send object
		this.Send(objSend);
	}

	// AJAX Send
	this.Send = function(objObject)
	{
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
					TEST:local_handle_reply(req.responseText, objObject);
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
	this.HandleReply = function(strReply, objObject)
	{
		var objData = {};
		
		//if the reply starts with "//JSON" then this is a json object storing a list of commands
		if (strReply.substr(0, 6) == "//JSON")
		{	
			// we are working with a JSON object so convert it to a javascript object
			var strJsonCommands = strReply.substr(6);
			
			try
			{
				// convert reply into data object
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
		else
		{
			// the reply must be HTML code
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
						if (Vixen.Popup.Exists(objObject.strId))
						{
							Vixen.Popup.SetContent(objObject.strId, strReply);
						}
						else
						{
							Vixen.Popup.Create(objObject.strId, strReply, objObject.strSize, "centre", "modal");
						}
						break;
					case "Div":
						// retrieve the current container div element
						var elmOldContainer = document.getElementById(objObject.strContainerDivId);
						if (!elmOldContainer)
						{
							alert("Error: The container div does not exist.\nContainer Div Id = '" + objObject.strContainerDivId +"'");
							return FALSE;
						}
						
						// Create a new container div
						var elmNewContainer = document.createElement('div');
						elmNewContainer.setAttribute('Id', objObject.strContainerDivId);
						elmNewContainer.innerHTML = strReply;
						
						// Retrieve the parent element of the current container div element
						var elmParent = elmOldContainer.parentNode;
						
						// Remove the old content div and add the new one
						elmParent.removeChild(elmOldContainer);
						elmParent.appendChild(elmNewContainer);
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
			switch (objInput[intKey].Type)
			{
				case "ClosePopup":
					Vixen.Popup.Close(objInput[intKey].Data);
					break;
				case "Alert":
					strContent = "<p><div align='center'>" + objInput[intKey].Data + 
									"<p><input type='button' id='VixenAlertOkButton' value='OK' onClick='Vixen.Popup.Close(\"VixenAlertBox\")'><br></div>\n" +
									"<script type='text/javascript'>document.getElementById('VixenAlertOkButton').focus()</script>\n";
					Vixen.Popup.Create('VixenAlertBox', strContent, 'medium', 'centre', 'autohide');
					break;
				case "AlertReload":
					strContent = "<p><div align='center'>" + objInput[intKey].Data + 
									"<p><input type='button' id='VixenAlertOkButton' value='OK' onClick='Vixen.Popup.Close(\"VixenAlertBox\");window.location = window.location;'><br></div>\n" +
									"<script type='text/javascript'>document.getElementById('VixenAlertOkButton').focus()</script>\n";
					Vixen.Popup.Create('VixenAlertBox', strContent, 'medium', 'centre', 'autohide-reload');
					break;
				case "Reload":
					window.location = window.location;
					break;
				case "LoadCurrentPage":
					window.location.reload();
					break;
				case "AlertAndRelocate":
					strContent = "<p><div align='center'>" + objInput[intKey].Data.Alert + 
									"<p><input type='button' id='VixenAlertOkButton' value='OK' onClick='Vixen.Popup.Close(\"VixenAlertBox\");window.location = \""+ objInput[intKey].Data.Location +"\";'><br></div>\n" +
									"<script type='text/javascript'>document.getElementById('VixenAlertOkButton').focus()</script>\n";
					Vixen.Popup.Create('VixenAlertBox', strContent, 'medium', 'centre', 'autohide', objInput[intKey].Data.Location);
					break;
				case "ReplaceDivContents":
					// The html code defined in objInput[intKey].Data will be placed in the declared Container Div
					// The current contents of the Container Div will be destroyed
					// TODO! Make sure this doesn't change the order of the elements belonging to the parent element of the one you want to change
				
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
					//alert("About to execute some javascript");
					eval(objInput[intKey].Data);
					//alert("Finished executing the javascript");
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
Vixen.Ajax = new VixenAjaxClass();



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
