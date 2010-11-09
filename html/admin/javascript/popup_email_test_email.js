var Popup_Email_Test_Email	= Class.create(Reflex_Popup, 
{
	initialize	: function($super, oData, iTemplateId)
	{
		$super(35);
		this._oLoadingPopup	= new Reflex_Popup.Loading();		
		this._oData = oData;
		this._iTemplateId = iTemplateId;
		this._buildUI();		
	},
	
	
	_buildUI	: function()
	{
		
		var oAddressSpan 							= document.createElement('span');
		oAddressSpan.innerHTML 						= 'Add Email Address:';
		
		
		var oToSpan 							= document.createElement('span');
		oToSpan.innerHTML 						= 'To:';
		oToSpan.className 						= 'email-address-list';
		
		this._oRecipientInput = Control_Field.factory('text', {mAutoTrim: true, sLabel: 'Recipient Email', fnValidate: this._validEmail, mEditable	: true, bDisableValidationStyling	: false});		
		this._oRecipientInput.addOnChangeCallback(this._RecipientChange.bind(this));
		this._oRecipientInput.setRenderMode(true);
		
		this._oTo = $T.div({class: 'popup-email-test-to'});	
		
		
		var oTable 								= new Email_Template_Table({}, {}, {class: 'reflex input'});
		oTable.appendRow($T.tr(
								$T.th(oAddressSpan),
								$T.td(this._oRecipientInput.getElement(), 
								$T.img({src: Popup_Email_Text_Editor.ADD_IMAGE_SOURCE ,class:'add-icon', title: 'Add Address' }).observe('click', this._addAddress.bind(this)))
								)
						);
		oTable.appendRow($T.tr(
								$T.th(oToSpan),
								$T.td(this._oTo)
								)
						);
		
		var sEmailType = this._oData.html==null?' Text Only Email':' HTML Email';		

		var  button = 	$T.button({class: 'icon-button'},											
									$T.img({src: Popup_Email_Text_Editor.EMAIL_IMAGE_SOURCE, alt: '', title: 'Send'+ sEmailType}),
									$T.span('Send'+ sEmailType)																	
									);
		button.observe('click', this._sendMail.bind(this,null));
		
		var oContent 	= 	$T.div(	{class: 'popup-email-test'},
											
											$T.div({class: 'recipient'}, oTable.getElement()),
											$T.div({class: 'footer'},
													$T.div({class: 'buttons'},
															button, 
															$T.button({class: 'icon-button'},
																$T.img({src: Popup_Email_Text_Editor.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel'}),
																$T.span('Cancel')
																	).observe('click',this._close.bind(this)))
															)													
											);

		

		
		
		this._oSendButton = oContent.select('div.footer .icon-button').first();
		this._oSendButton.disabled = true;
		
		this.setTitle('Send Test Email');
		this.addCloseButton();
		this.setContent(oContent);
		this.display();		
	},
	
	_RecipientChange: function ()
	{
		
		this._oRecipientInput.validate();
		
	},
	
	_sendMail: function(oResponse)
	{
	
	
		if (typeof oResponse == 'undefined' || oResponse == null)
		{		
			this._oLoadingPopup.display();
			var aTo = [];
			
			for (var i=0;i<this._oTo.childNodes.length;i++)
			{
				aTo.push(this._oTo.childNodes[i].childNodes[0].wholeText);
			
			}
			
			sSubject = this._oData.html==null?'[Flex Test Email - Text Only] ':'[Flex Test Email] ';
			var oData =	{	text: this._oData.text,
							html:	this._oData.html, 
							subject: sSubject + this._oData.subject,
							to: aTo							
						};
			var fnRequest     = jQuery.json.jsonFunction(this._sendMail.bind(this), Popup_Email_Text_Editor.errorCallback.bind(this), 'Email_Text_Editor', 'sendTestEmail');
			fnRequest(oData, this._iTemplateId);
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
	
	_validEmail : function(strEmail)
	{		
		var expEmail	= /^[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[A-Z]{2}|com|org|net|gov|mil|biz|info|mobi|name|aero|jobs|museum)$/i;
		return expEmail.test(strEmail);	
	},
	
	_addAddress: function()
	{
		
		var address = this._oRecipientInput.getElementValue();
		if (this._validEmail(address))
		{
			var img = $T.img({src: Popup_Email_Text_Editor.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel', class: 'remove-icon'})
			
			var span = $T.span(	address,
								img,
								"; "		
								);
			img.observe('click',this._removeAddress.bind(this,span));
			this._oRecipientInput.clearValue();
			this._oTo.appendChild(span);			
			this._oTo.childNodes.length>0?this._oSendButton.disabled = false:this._oSendButton.disabled = true;	
		
		}
		else
		{
			Reflex_Popup.alert('That\'s not a valid email address', {sTitle: 'Email Address Error'})
			
		
		}
	
	},
	
	_removeAddress: function(span)
	{
		this._oTo.removeChild(span);
		this._oTo.childNodes.length>0?this._oSendButton.disabled = false:this._oSendButton.disabled = true;	
	},
	_close : function ()
	{
		
		this.hide();
	},
	
	
	});