
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
		// var sFile = window.atob(oData.sRawData);

		// In most browsers, calling window.btoa on a Unicode string will cause a Character Out Of Range exception.
		// To avoid this, consider this pattern
		// https://developer.mozilla.org/en/DOM/window.btoa
		var sFile = decodeURIComponent(escape(window.atob(oData.sRawData)));
		
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
	}

});

return self;
