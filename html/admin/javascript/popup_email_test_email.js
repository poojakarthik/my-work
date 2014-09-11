var Popup_Email_Test_Email	= Class.create(Reflex_Popup, 
{
	initialize	: function($super, oData, iTemplateId)
	{
		$super(35);
		this._oLoadingPopup	= new Reflex_Popup.Loading();		
		this._oData 		= oData;
		this._iTemplateId 	= iTemplateId;
		this._getCachedAddresses(this._buildUI.bind(this));
	},
		
	_buildUI	: function(aAddresses)
	{
		this._oRecipientInput	= Control_Field.factory('text', {mAutoTrim: true, sLabel: 'Recipient Email', fnValidate: this._validEmail, mEditable	: true, bDisableValidationStyling	: false});		
		this._oRecipientInput.addOnChangeCallback(this._recipientChange.bind(this));
		this._oRecipientInput.setRenderMode(true);
		
		this._oTo	= $T.div({class: 'popup-email-test-to'});	
		
		var oTable	= new Email_Template_Table({}, {}, {class: 'reflex input'});
		oTable.appendRow(
			$T.tr(
				$T.th(
					$T.span('Add Email Address:')
				),
				$T.td(this._oRecipientInput.getElement(), 
				$T.img({src: Popup_Email_Text_Editor.ADD_IMAGE_SOURCE, class: 'add-icon', title: 'Add Address'}).observe('click', this._addAddressFromText.bind(this)))
			)
		);
		oTable.appendRow(
			$T.tr(
				$T.th(
					$T.span({class: 'email-address-list'},
						'To:'
					)
				),
				$T.td(this._oTo)
			)
		);
		
		var sButtonText	= 	'Send' + (this._oData.html == null ? ' Text Only Email' : ' HTML Email');
		var oButton		= 	$T.button({class: 'icon-button'},											
								$T.img({src: Popup_Email_Text_Editor.EMAIL_IMAGE_SOURCE, alt: sButtonText, title: sButtonText}),
								$T.span(sButtonText)																
							);
		oButton.observe('click', this._sendMail.bind(this,null));
		
		var oContent 	= 	$T.div(	{class: 'popup-email-test'},
								$T.div({class: 'recipient'}, oTable.getElement()),
								$T.div({class: 'footer'},
									$T.div({class: 'buttons'},
										oButton, 
										$T.button({class: 'icon-button'},
											$T.img({src: Popup_Email_Text_Editor.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel'}),
											$T.span('Cancel')
										).observe('click',this._close.bind(this))
									)
								)													
							);
		
		this._oSendButton			= oContent.select('div.footer .icon-button').first();
		this._oSendButton.disabled	= true;
		
		// Pre-load cached addresses
		if (aAddresses && (aAddresses.length))
		{
			for (var i = 0; i < aAddresses.length; i++)
			{
				this._addAddress(aAddresses[i]);
			}
		}
		
		this.setTitle('Send Test Email');
		this.addCloseButton();
		this.setContent(oContent);
		this.display();	
	},
	
	_recipientChange	: function()
	{
		this._oRecipientInput.validate();
	},
	
	_sendMail	: function(oResponse)
	{
		if (typeof oResponse == 'undefined' || oResponse == null)
		{		
			this._oLoadingPopup.display();
			
			var aTo	= [];
			for (var i = 0; i < this._oTo.childNodes.length; i++)
			{
				aTo.push(this._oTo.childNodes[i].childNodes[0].wholeText);
			}
			
			sSubject 	= this._oData.html==null?'[Flex Test Email - Text Only] ':'[Flex Test Email] ';
			var oData 	=	{	text	: this._oData.text,
								html	: this._oData.html, 
								subject	: sSubject + this._oData.subject,
								to		: aTo,
								from	: this._oData.from
							};
			var fnRequest	= jQuery.json.jsonFunction(this._sendMail.bind(this), Popup_Email_Text_Editor.errorCallback.bind(this), 'Email_Text_Editor', 'sendTestEmail');
			fnRequest(oData, this._iTemplateId);
			
			// Sends the addresses to back so that they are stored in session
			this._cacheAddresses(aTo);
		}
		else
		{
			if (oResponse.Success)
			{
			
				Reflex_Popup.alert("Your test email was sent successfully", {sTitle: 'Send Test Email'});
				this._oLoadingPopup.hide();
				this._close();
			}
			else
			{		
				Popup_Email_Text_Editor.serverErrorMessage.bind(this,oResponse.message, 'Email Template Test Mail Error')();			
			}
		}	
	},
	
	_validEmail	: function(strEmail)
	{
		var expEmail	= /^([a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[A-Z]{2}|com|org|net|gov|mil|biz|info|mobi|name|aero|jobs|museum),?)+$/i;
		return expEmail.test(strEmail);	
	},
	
	_addAddressFromText	: function()
	{
		var sAddress	= this._oRecipientInput.getElementValue();
		var aEmails		= sAddress.split(',');
		for (var i = 0; i < aEmails.length; i++)
		{
			this._addAddress(aEmails[i]);
		}
	},
	
	_addAddress	: function(sAddress)
	{
		if (this._validEmail(sAddress))
		{
			var oImg 	= 	$T.img({src: Popup_Email_Text_Editor.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel', class: 'remove-icon'})
			var oSpan	= 	$T.span(
								sAddress,
								oImg,
								"; "		
							);
			oImg.observe('click', this._removeAddress.bind(this, oSpan));
			this._oRecipientInput.clearValue();
			this._oTo.appendChild(oSpan);			
			(this._oTo.childNodes.length > 0 ? this._oSendButton.disabled = false : this._oSendButton.disabled = true);	
		}
	},
	
	_removeAddress	: function(span)
	{
		this._oTo.removeChild(span);
		this._oTo.childNodes.length>0?this._oSendButton.disabled = false:this._oSendButton.disabled = true;	
	},
	
	_close	: function ()
	{
		this.hide();
	},
	
	_getCachedAddresses	: function(fnCallback, oResponse)
	{
		if (Object.isUndefined(oResponse))
		{
			// Make request to get addresses
			var fnResponse	= this._getCachedAddresses.bind(this, fnCallback);
			var fnRequest	= jQuery.json.jsonFunction(fnResponse, fnResponse, 'Email_Text_Editor', 'getEmailAddressCache');
			fnRequest();
		}
		else
		{
			// Done, return the addresses, if any
			var aAddresses	= (oResponse.aEmailAddresses ? oResponse.aEmailAddresses : []); 
			fnCallback(aAddresses);
		}
	},
	
	// Sends the addresses to back so that they are stored in session
	_cacheAddresses	: function(aAddresses, oResponse)
	{
		if (Object.isUndefined(oResponse))
		{
			var fnResponse	= this._cacheAddresses.bind(this, aAddresses);
			var fnRequest	= jQuery.json.jsonFunction(fnResponse, fnResponse, 'Email_Text_Editor', 'setEmailAddressCache');
			fnRequest(aAddresses);
		}
		else
		{
			// Done!
		}
	}
});