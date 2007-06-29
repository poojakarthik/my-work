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
	// Send form
	this.SendForm = function(strFormId, strButton, strClass, strMethod, strTargetType, strId, strSize)
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
		
		// add values from form to object
		//TODO! Find each element and load it into objSend.Objects.Object.Property
		
		
		objSend.Objects = {};
		
		//objSend.Objects.Employee = {};
		//objSend.Objects.Employee.Id = 7;
		
		//beginning of for loop to pull from form name, create new array in opjects named that and then add its value;
		
			//for (i = 0;i < = document.
			//strObject = object    "Employee"
			//strProperty = property   "Id"
			//strValue = value  "29"
			//alert(objSend.Objects.Employee);
			//Step1: For each element in the form...
			objFormElement = document.getElementById(strFormId);
			//alert(objFormElement.elements.length);
			//return;
			for (intKey in objFormElement.elements)
			{
				strElementName	= objFormElement.elements[intKey].name;
				//strElementValue = objFormElement.elements[intKey].value;
				strType			= objFormElement.elements[intKey].type;
				
				// check for the special case of VixenFormId hidden input
				if (strElementName == "VixenFormId")
				{
					objSend.FormId = objFormElement.elements[intKey].value;
					continue;
				}
				
				intDotIndex = strElementName.indexOf(".", 0);
				strObjectName = strElementName.substr(0, intDotIndex);
				strPropertyName = strElementName.substr(intDotIndex + 1, strElementName.length);
				
				if (strObjectName.length!=0 || strPropertyName!=0)
				{				
					if (objSend.Objects[strObjectName]==undefined)
					{
						objSend.Objects[strObjectName] = {};
					}
					
					switch (strType)
					{
						case "select_multiple":
							
							break;
						case "checkbox":
							mixValue = objFormElement.elements[intKey].checked;
							break;
						case "listbox":
							break;
						case "undefined":
						case "button":
							break;
						default:
							mixValue = objFormElement.elements[intKey].value;			
							break;
					}
					objSend.Objects[strObjectName][strPropertyName] = mixValue;	
				}}
			}			
			
		
		// send object
		this.Send(objSend);
	}
	
	
        // AJAX Send
        this.Send = function(objObject)
        {
                // store our object before sending, along with a transaction ID
                //this.objData = objObject;
                //alert("Vixen.Ajax.Send() has been called. objObject.Class = " + objObject.Class);
				// set the target page
                var page_url = "ajax_link.php";
                
				// register the callbacks
                var local_handle_reply = this.HandleReply;
                var local_handle_error = this.HandleError;
        
				switch (objObject.TargetType)
				{
					case "Div":
					case "Popup":
						objObject.HtmlMode = TRUE;
						break;
					default:
				}
		
                // callback binder
                function bindcallback()
                {
                        if (req.readyState == 4) {
                                if (req.status == 200) {
                                        TEST:local_handle_reply(req.responseText, objObject);
                                        //handle_reply();
                                } else {
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
                //alert(strReply);
				// the reply is a JSON string, need to eval it to get an object
                if (objObject.HtmlMode)
				{
					switch (objObject.TargetType)
					{
						case "Popup":
							//strContent, strId, strSize, mixPosition, strModal						
							Vixen.Popup.Create(strReply, objObject.strId, objObject.strSize);
							break;
						case "Div":
							break;
						default:
							ajaxError(null, strReply);
					}
				}
                var objData = {};
                try
                {
                        // convert reply into data object
                        eval("objData = " + strReply);
                       
                        if (objData)
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
        
                // clean up
                delete(strReply);
                delete(objData);
        }	
                
        this.ajaxHandler = function(objInput)
		{
			
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
