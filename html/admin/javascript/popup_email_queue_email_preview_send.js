
var Popup_Email_Queue_Email_Preview_Send = Class.create(Reflex_Popup, {
	
	initialize : function($super, iEmailId, fnOnReady) {
		$super(40);

		this._iEmailId		= iEmailId;
		this._fnOnReady		= fnOnReady;
		this._aAddresses	= null;
		
		this._buildUI();
		this._syncUI();
	},
	
	_buildUI : function() {
		var oAddressControl = Control_Field.factory(
			'text',
			{
				mEditable	: true, 
				mMandatory	: true,
				fnValidate	: Reflex_Validation.Exception.email,
				sExtraClass	: 'popup-email-queue-email-preview-send-recipient'
			}
		);
		oAddressControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._oAddressControl = oAddressControl;
		
		this._oSendButton = $T.button({class: 'icon-button'},
			$T.img({src: '../admin/img/template/email.png', alt: '', title: ''}),
			$T.span('Send Email')
		);
		this._oSendButton.observe('click', this._sendMail.bind(this));
		
		this.setTitle('Send Sample Email');
		this.addCloseButton();
		this.setFooterButtons(
			[
				this._oSendButton,
				$T.button({class: 'icon-button'},
					$T.img({src: '../admin/img/template/delete.png', alt: '', title: ''}),
					$T.span('Cancel')
				).observe('click', this.hide.bind(this))
			],
			true
		);
		this.setContent(
			$T.div({class: 'popup-email-queue-email-preview-send'},
				$T.table({class: 'reflex input'},
					$T.tbody(
						$T.tr(
							$T.th('Add Recipient'),
							$T.td(oAddressControl.getElement()),
							$T.td({class: 'popup-email-queue-email-preview-send-add-recipient'},
								$T.img({src: '../admin/img/template/new.png', alt: 'Add Address', title: 'Add Address', onclick: this._addAddress.bind(this)})
							)
						),
						$T.tr(
							$T.th('To'),
							$T.td({colspan: 2, class: 'popup-email-queue-email-preview-send-to-list'})
						)
					)
				)
			)
		);
	},
	
	_syncUI : function() {
		if (this._aAddresses === null) {
			this._load();
		} else {
			this._refreshToList();
			if (this._fnOnReady) {
				this._fnOnReady();
			}
		}
	},
	
	_load : function(oResponse) {
		if (!oResponse) {
			// Request: Get all cached sample email addresses
			var oReq = new Reflex_AJAX_Request('Email_Text_Editor', 'getEmailAddressCache', this._load.bind(this));
			oReq.send();
		} else if (oResponse.hasException()) {
			// Error
			var oException = oResponse.getException();
			Reflex_Popup.alert(oException.sMessage || 'There was a critical error accessing the Flex Server', {
				sTitle			: 'Database Error',
				sDebugContent	: oResponse.getDebugLog()
			});
			return;
		} else {
			// Success
			this._aAddresses = oResponse.get('aEmailAddresses');
			this._syncUI();
		}
	},
	
	_refreshToList : function() {
		var oToList = this.container.select('.popup-email-queue-email-preview-send-to-list').first();
		oToList.select('div').each(Element.remove);
		
		if (!this._aAddresses.length) {
			// No addresses, disallow send
			this._oSendButton.hide();
		} else {
			// Have addresses, show them, allow send
			this._oSendButton.show();
			for (var i = 0; i < this._aAddresses.length; i++) {
				oToList.appendChild(
					$T.div({class: 'popup-email-queue-email-preview-send-to-list-item'},
						$T.span(this._aAddresses[i]),
						$T.img({src: '../admin/img/template/delete.png', alt: 'Remove Address', title: 'Remove Address', onclick: this._removeAddress.bind(this, i)})
					)
				);
			}
		}
	},
	
	_removeAddress : function(iAddressIndex) {
		this._aAddresses.splice(iAddressIndex, 1);
		this._refreshToList();
	},
	
	_addAddress : function() {
		try {
			this._oAddressControl.validate(false);
			this._oAddressControl.save(true);
		} catch (oEx) {
			// Ignore, return
			return;
		}
		
		this._aAddresses.push(this._oAddressControl.getValue());
		this._refreshToList();
		this._oAddressControl.clearValue();
	},
	
	_sendMail : function(oResponse) {
		if (!oResponse || !Object.isUndefined(oResponse.target)) {
			// Request (email cache update and mail send request)
			var oReqEmailCache = new Reflex_AJAX_Request('Email_Text_Editor', 'setEmailAddressCache');
			oReqEmailCache.send(this._aAddresses);
			
			this._oLoading = new Reflex_Popup.Loading();
			this._oLoading.display();
			
			var oReqSendMail = new Reflex_AJAX_Request('Email', 'sendSampleEmail', this._sendMail.bind(this));
			oReqSendMail.send(this._aAddresses, this._iEmailId);
		} else {
			// Response
			this._oLoading.hide();
			delete this._oLoading;
			
			if (oResponse.hasException()) {
				// Error
				var oException = oResponse.getException();
				Reflex_Popup.alert(oException.sMessage || 'There was a critical error accessing the Flex Server', {
					sTitle			: 'Database Error',
					sDebugContent	: oResponse.getDebugLog()
				});
				return;
			}
			
			// Success
			this.hide();
		}
	}
});