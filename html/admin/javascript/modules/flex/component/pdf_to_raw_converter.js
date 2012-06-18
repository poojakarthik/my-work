
var H					= require('fw/dom/factory'), // HTML
	S					= H.S, // SVG
	Class				= require('fw/class'),
	Component			= require('fw/component'),
	Overlay				= require('fw/component/overlay'),
	Control				= require('fw/component/control'),
	Form				= require('fw/component/form'),
	Text				= require('fw/component/control/text'),
	Hidden				= require('fw/component/control/hidden'),
	XHRRequest			= require('fw/xhrrequest');


var self = new Class({
	'extends' : Component,

	construct	: function() {
		this.CONFIG = Object.extend({}, this.CONFIG || {});
		this._super.apply(this, arguments);
		this.NODE.addClassName('flex-pdf-to-raw-converter');
	},

	// ----------------------------------------------------------------------------------- //
	// Build UI
	// ----------------------------------------------------------------------------------- //
	_buildUI	: function() {		
		this.NODE = H.section(
			H.button({onclick:this._showFileUpoader.bind(this)},'PDF to Raw file converter')
		)
	},


	// ----------------------------------------------------------------------------------- //
	// Sync UI
	// ----------------------------------------------------------------------------------- //
	_syncUI	: function() {
		try {		
			if (!this._bInitialised) {
				// File Saver
				JsAutoLoader.loadScript(['../ui/javascript/file_saver.js'], function() {}.bind(this), null, null, null);
				// Blob Builder
				JsAutoLoader.loadScript(['../ui/javascript/blob_builder.js'], function() {}.bind(this), null, null, null);
			} else {

			}
			this._onReady();
		} catch (oException) {
			// Fail
			this._handleException(oException);
		}
	},


	// ----------------------------------------------------------------------------------- //
	// File Uploader
	// ----------------------------------------------------------------------------------- //
	_resetFileUploader : function() {
		this._oFile.value = null;
		this._oSubmitButton.enable();
		this._oSubmitButton.select('span')[0].update('Convert');
		// Cache file details
		this._oFilename.set('mValue', null);
		this._oName.set('mValue', null);
		this._oMimeTypeHidden.set('mValue', null);
	},
	_hideFileUploader : function() {
		// Hide
		this._oFileUpoaderPopup.hide();
		// Reset popup, so when it gets launched again.
		this._resetFileUploader();
	},
	_showFileUpoader : function() {
		if(!this._oFileUpoaderPopup) {
			var oPopupContent = H.section({'class':'flex-pdf-to-raw-converter-uploader'},
				this._oForm = new Form({onsubmit: function() {
						this._handleSubmit();
					}.bind(this)},
					H.table({'class':'reflex'},
						H.caption(
							H.div({'id':'caption_bar', 'class':'caption_bar'},
								H.div({'id':'caption_title', 'class':'caption_title'},"Upload")
							)
						),
						H.tbody(
							H.tr(
								H.th({'class':"label"},"File :"),
								H.td(
									this._oName = new Hidden({'sExtraClass':'flex-pdf-to-raw-converter-uploader-file-name'}),
									this._oFileButton = H.span({'class': 'flex-pdf-to-raw-converter-uploader-document-button', type: 'button'},
										this._oFile = H.input({'class': 'flex-pdf-to-raw-converter-uploader-document-file', type: 'file', onchange: this._handleFileUpoaderFileChange.bind(this)})
									),			
									this._oContent = new Hidden(),
									this._oFilename = new Hidden(),
									this._oMimeTypeHidden = new Hidden()
								)
							)
						)
					)
				),
				H.footer(
					this._oSubmitButton = H.button({'type':'button', 'class':'icon-button'},
						H.img({src: '/admin/img/template/tick.png','width':'16','height':'16'}),
						H.span('Convert')
					).observe('click', function() {
						this._handleSubmit();
					}.bind(this)),
					H.button({'type':'button', 'class':'icon-button'},
						H.img({src: '/admin/img/template/delete.png','width':'16','height':'16'}),
						H.span('Cancel')
					).observe('click', this._hideFileUploader.bind(this))
				)
			);

			this._oFileUpoaderPopup = Reflex_Popup.factory(oPopupContent, {
				iWidth			: 30,
				aFooterButtons	: [],
				bClosable		: true,
				bAutoDisplay	: true,
				sTitle			: 'PDF to RAW converter',
				sIcon			: '/admin/img/template/page_break.png'
			});

		} else {
			this._oFileUpoaderPopup.display();
		}
	},
	_handleFileUpoaderFileChange : function(oEvent) {
		var aFiles	= oEvent.target.files;
		var oFile	= aFiles[0];
		
		// Cache file details
		this._oFilename.set('mValue', oFile.name);
		this._oName.set('mValue', oFile.name);
		this._oMimeTypeHidden.set('mValue', oFile.type);
		
		// Read file contents
		var oFileReader 	= new FileReader();
		oFileReader.onload 	= function(oEvent) {
			this._oContent.set('mValue', oEvent.target.result);
		}.bind(this);
		
		oFileReader.onerror = function(oEvent) {
			new Alert("There was an error reading the contents of the file.");
		}
		
		oFileReader.readAsDataURL(oFile);
	},


	// ----------------------------------------------------------------------------------- //
	// Submit, Save and Convert
	// ----------------------------------------------------------------------------------- //
	_handleSubmit : function() {
		try {
			var mIsInputInvalid = this._isInputInvalid();
			if (mIsInputInvalid !== false) {
				throw mIsInputInvalid;
			}
			this._oSubmitButton.disable();
			this._oSubmitButton.select('span')[0].update('Converting...');
			this._convertFile(
				// Data to send to server
				{
					'sName' : this._oName.getValue(),
					'mContent' : this._oContent.getValue(),
					'sMimeType' : this._oMimeTypeHidden.getValue()
				}, 
				// Callback function
				this._saveFile
			);
		} catch (sError) {
			// Alert
			this._handleException({'message':sError});
		}
	},
	_convertFile : function(oData, fnCallback) {
		new Ajax.Request('/admin/reflex_json.php/File/convertPDFToRaw', {
			method		: 'post',
			contentType	: 'application/x-www-form-urlencoded',
			postBody	: "json="+encodeURIComponent(JSON.stringify([oData])),
			onSuccess: function (oResponse){
				var oServerResponse = JSON.parse(oResponse.responseText);
				this._hideFileUploader();
				if (fnCallback) {
					// Once the data is retrieved from the server, run the callback function to render the data.
					fnCallback(oServerResponse);
				} else {
					return (oServerResponse) ? oServerResponse : null;
				}
			}.bind(this)
		});
	},
	_saveFile : function(oData) {
		// Decode data from server
		var sFile = self.base64_decode(oData.sRawData);
		
		// Create a new blob from the data
		var oBB = new BlobBuilder;
		oBB.append(sFile);

		// Save file
		saveAs(oBB.getBlob("text/plain;charset=utf-8"), oData.sName + ".raw");
	},


	// ----------------------------------------------------------------------------------- //
	// Validation and Error Handling
	// ----------------------------------------------------------------------------------- //
	_isInputInvalid : function() {
		if (this._oFilename.getValue() === '') {
			return 'Invalid file name';
		}
		if (this._oName.getValue() === '') {
			return 'Invalid file name';
		}
		if (this._oMimeTypeHidden.getValue() === '') {
			return 'Invalid mime type';
		}
		// FIXME FIXME FIXME, HACK HACK HACK, make a list of valid mime types.
		if (this._oMimeTypeHidden.getValue() !== 'application/pdf') {
			return 'The file must be of type application/pdf';
		}
		if (this._oFile.getValue() === '') {
			return 'Invalid file specified';
		}
		return false;
	},
	_handleException : function(oException) {
		if (oException && oException.message) {
			Reflex_Popup.alert(oException.message, {});
		} else {
			Reflex_Popup.alert('An unknown error has occurred.', {});
		}
	},


	// ----------------------------------------------------------------------------------- //
	// Statics
	// ----------------------------------------------------------------------------------- //
	statics : {

		utf8_decode : function (str_data) {
			// Converts a UTF-8 encoded string to ISO-8859-1  
			// 
			// version: 1109.2015
			// discuss at: http://phpjs.org/functions/utf8_decode
			// +   original by: Webtoolkit.info (http://www.webtoolkit.info/)
			// +      input by: Aman Gupta
			// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
			// +   improved by: Norman "zEh" Fuchs
			// +   bugfixed by: hitwork
			// +   bugfixed by: Onno Marsman
			// +      input by: Brett Zamir (http://brett-zamir.me)
			// +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
			// *     example 1: utf8_decode('Kevin van Zonneveld');
			// *     returns 1: 'Kevin van Zonneveld'
			var tmp_arr = [],
			i = 0,
			ac = 0,
			c1 = 0,
			c2 = 0,
			c3 = 0;

			str_data += '';

			while (i < str_data.length) {
					c1 = str_data.charCodeAt(i);
					if (c1 < 128) {
					tmp_arr[ac++] = String.fromCharCode(c1);
					i++;
				} else if (c1 > 191 && c1 < 224) {
					c2 = str_data.charCodeAt(i + 1);
					tmp_arr[ac++] = String.fromCharCode(((c1 & 31) << 6) | (c2 & 63));
					i += 2;
				} else {
					c2 = str_data.charCodeAt(i + 1);
					c3 = str_data.charCodeAt(i + 2);
					tmp_arr[ac++] = String.fromCharCode(((c1 & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
					i += 3;
				}
			}

			return tmp_arr.join('');
		},

		base64_decode : function (data) {
			// Decodes string using MIME base64 algorithm  
			// 
			// version: 1109.2015
			// discuss at: http://phpjs.org/functions/base64_decode
			// +   original by: Tyler Akins (http://rumkin.com)
			// +   improved by: Thunder.m
			// +      input by: Aman Gupta
			// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
			// +   bugfixed by: Onno Marsman
			// +   bugfixed by: Pellentesque Malesuada
			// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
			// +      input by: Brett Zamir (http://brett-zamir.me)
			// +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
			// -    depends on: utf8_decode
			// *     example 1: base64_decode('S2V2aW4gdmFuIFpvbm5ldmVsZA==');
			// *     returns 1: 'Kevin van Zonneveld'
			// mozilla has this native
			// - but breaks in 2.0.0.12!
			//if (typeof this.window['btoa'] == 'function') {
			//    return btoa(data);
			//}
			var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
			var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
			ac = 0,
			dec = "",
			tmp_arr = [];

			if (!data) {
				return data;
			}

			data += '';

			do { // unpack four hexets into three octets using index points in b64
				h1 = b64.indexOf(data.charAt(i++));
				h2 = b64.indexOf(data.charAt(i++));
				h3 = b64.indexOf(data.charAt(i++));
				h4 = b64.indexOf(data.charAt(i++));

				bits = h1 << 18 | h2 << 12 | h3 << 6 | h4;

				o1 = bits >> 16 & 0xff;
				o2 = bits >> 8 & 0xff;
				o3 = bits & 0xff;

				if (h3 == 64) {
					tmp_arr[ac++] = String.fromCharCode(o1);
				} else if (h4 == 64) {
					tmp_arr[ac++] = String.fromCharCode(o1, o2);
				} else {
					tmp_arr[ac++] = String.fromCharCode(o1, o2, o3);
				}
			} while (i < data.length);

			dec = tmp_arr.join('');
			dec = self.utf8_decode(dec);

			return dec;
		}
	}

});

return self;
