
var H					= require('fw/dom/factory'), // HTML
	S					= H.S, // SVG
	Class				= require('fw/class'),
	Component			= require('fw/component'),
	Control				= require('fw/component/control'),
	Overlay				= require('fw/component/window'),
	Form				= require('fw/component/form'),
	Hidden				= require('fw/component/control/hidden'),
	XHRRequest			= require('fw/xhrrequest');


var self = new Class({
	'extends' : Component,

	construct	: function() {
		console.log('DEBUG: logo constructor run....');
		this.CONFIG = Object.extend({}, this.CONFIG || {});
		this._super.apply(this, arguments);
		this.NODE.addClassName('flex-system-settings-manage-logo');
	},

	// ----------------------------------------------------------------------------------- //
	// Build UI
	// ----------------------------------------------------------------------------------- //
	_buildUI	: function() {
		this.NODE = H.section(
			H.section({'class':'flex-system-settings-logo-uploader'},
				this._oForm = new Form({onsubmit: this._handleSubmit.bind(this)},
					H.table({'class':'reflex'},
						H.caption(
							H.div({'id':'caption_bar', 'class':'caption_bar'},
								H.div({'id':'caption_title', 'class':'caption_title'}, 'Upload')
							)
						),
						H.tbody(
							H.tr(
								H.th({'class':'label'}, 'File :'),
								H.td(
									H.span({'class':'flex-system-settings-logo-uploader-document-button', 'type':'button'},
										this._oName = new Hidden({'sExtraClass':'flex-system-settings-logo-uploader-document-file-name'}),
										this._oFileButton = H.span({'class': 'flex-system-settings-logo-uploader-document-button', type: 'button'},
											this._oFile = H.input({'class': 'flex-system-settings-logo-uploader-document-file', type: 'file', onchange: this._handleFileUpoaderFileChange.bind(this)})
										),
										this._oContent = new Hidden(),
										this._oFilename = new Hidden(),
										this._oMimeTypeHidden = new Hidden()
									)
								)
							)
						)
					)
				),
				H.footer(
					this._oSubmitButton = H.button({'type':'button', 'class':'icon-button'},
						H.img({src: '/admin/img/template/tick.png','width':'16','height':'16'}),
						H.span('Upload')
					).observe('click', this._handleSubmit.bind(this)),
					H.button({'type':'button', 'class':'icon-button'},
						H.img({src: '/admin/img/template/delete.png','width':'16','height':'16'}),
						H.span('Cancel')
					).observe('click', this._cancel.bind(this))
				)
			)
		);
	},


	// ----------------------------------------------------------------------------------- //
	// Sync UI
	// ----------------------------------------------------------------------------------- //
	_syncUI	: function() {
		try {		
			if (!this._bInitialised) {
				// First run
			} else {
				// Subsequent run
			}
			this._onReady();
		} catch (oException) {
			// Fail
			this._handleException(oException);
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
	_cancel : function() {
		this.fire('cancel');
	},
	_hideFileUploader : function() {
		this._cancel();
	},
	

	// ----------------------------------------------------------------------------------- //
	// Submit, Save
	// ----------------------------------------------------------------------------------- //
	_handleSubmit : function() {
		try {
			var mIsInputInvalid = this._isInputInvalid();
			if (mIsInputInvalid !== false) {
				throw mIsInputInvalid;
			}
			this._oSubmitButton.disable();
			this._oSubmitButton.select('span')[0].update('Uploading...');
			this._saveFile(
			// Data to send to server
			{
				'sName' : this._oName.getValue(),
				'mContent' : this._oContent.getValue(),
				'sMimeType' : this._oMimeTypeHidden.getValue()
			},
			// Callback function
			this._uploadCompleted);
		} catch (sError) {
			// Alert
			this._handleException({'message':sError});
		}
	},
	_saveFile : function(oData, fnCallback) {
		new Ajax.Request('/admin/reflex_json.php/Flex_Config/setLogo', {
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
	_uploadCompleted : function() {
		Reflex_Popup.yesNoCancel('Would you like to reload the page now?', {
			'iWidth'		: '30',
			'sTitle'		: 'Upload complete.',
			'fnOnYes'		: function() {
				// Reload Page
				window.location.reload();
			},
			'fnOnNo'		: function() {
				// Do nothing
			}
		});
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
		/*
		if (this._oMimeTypeHidden.getValue() !== 'application/pdf') {
			return 'The file must be of type application/pdf';
		}
		*/
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
		createAsOverlay : function() {
			var oComponent = self.applyAsConstructor($A(arguments)),
				oOverlay = new Overlay({
						sExtraClass : 'flex-popup-system-settings-manage-logo',
						sTitle: 'Manage Logo'
					},
					oComponent.getNode()
				);
			return oOverlay;
		}
	}

});

return self;
