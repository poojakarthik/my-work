// MODIFIED BY: Sean
// CHANGES: completely rooted up the lovely JSON code, so it will debug out
// 			a mutli-leveled javascript object nicely

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
var DEBUG = function () {
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
						var cr = '<br />';
						var ntb = tb + "&nbsp;";
					}
					else
					{
						var cr = '';
						var tb = '';
						var ntb = '';
					}
                    var a = [], b, f, i, l, v;
                    if (x instanceof Array) {
                        a[0] = '[' + cr + tb;
                        l = x.length;
                        for (i = 0; i < l; i += 1) {
                            v = x[i];
                            f = s[typeof v];
                            if (f) {
                                v = f(v, fmt, ntb);
                                if (typeof v == 'string') {
                                    if (b) {
                                        a[a.length] = cr + tb;
                                    }
                                    a[a.length] = v;
                                    b = true;
                                }
                            }
                        }
                        a[a.length] = cr + tb + ']' + cr;
                    } else if (x instanceof Object) {
                        a[0] = ' {' + cr + tb;
                        for (i in x) {
                            v = x[i];
                            f = s[typeof v];
                            if (f) {
                                v = f(v, fmt, ntb);
                                if (typeof v == 'string') {
                                    if (b) {
                                        a[a.length] = cr + tb;
                                    }
                                    a.push(ntb + s.string(i), ' : ', v);
                                    b = true;
                                }
                            }
                        }
                        a[a.length] = tb + '}';
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
