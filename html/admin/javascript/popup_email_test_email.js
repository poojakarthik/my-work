var Popup_Email_Test_Email	= Class.create(Reflex_Popup, 
{
	initialize	: function($super, oData)
	{
		$super(25);
		this._oLoadingPopup	= new Reflex_Popup.Loading();		
		this._oData = oData;	
		this._buildUI();		
	},
	
	
	_buildUI	: function()
	{
		
		var oToSpan 								= document.createElement('span');
		oToSpan .innerHTML 						= 'To:';
		
		
		var oNameSpan 								= document.createElement('span');
		oNameSpan.innerHTML 						= 'Name';
		
		
		this._oRecipientInput = Control_Field.factory('text', {mAutoTrim: true, sLabel: 'Recipient Email'});		
		this._oRecipientInput.addOnChangeCallback(this._RecipientChange.bind(this));
		
		this._oRecipientName = Control_Field.factory('text', {mAutoTrim: true, sLabel: 'Recipient Name'});	
		
		
		var oTable 								= new Email_Template_Table({}, {}, {class: 'reflex input'});
		oTable.appendRow($T.tr(
								$T.th(oToSpan),
								$T.td(this._oRecipientInput.getElement())
								)
						);
		oTable.appendRow($T.tr(
								$T.th(oNameSpan),
								$T.td(this._oRecipientName.getElement())
								)
						);
		
		
debugger;
var  button = 	$T.button({class: 'icon-button'},											
							$T.img({src: Popup_Email_Text_Editor.EMAIL_IMAGE_SOURCE, alt: '', title: 'Send'}),
							$T.span('Send')																	
							);
button.observe('click', this._sendMail.bind(this));
		
		var oContent 	= 	$T.div(	{class: 'popup-email-test'},
											
											$T.div({class: 'recipient'}, oTable.getElement()),
											$T.div({class: 'footer'},
													$T.span({class: 'send-button'},
															button
															),
															$T.span({class: 'cancel-button'},
															$T.button({class: 'icon-button'},
																$T.img({src: Popup_Email_Text_Editor.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel'}),
																$T.span('Cancel')
																	).observe('click',this._close.bind(this)))
															)
													
											);

		
		// var oRecipientSection = oContent.select('div.recipient').first();
		
		// oRecipientSection.appendChild($T.span('To:'));
		// oRecipientSection.appendChild(this._oRecipientInput.getElement());
		
		
		// oRecipientSection.appendChild($T.span('Name:'));
		// oRecipientSection.appendChild(this._oRecipientName.getElement());
		
		
		this._oSendButton = oContent.select('div.footer .icon-button').first();
		this._oSendButton.disabled = true;
		
		this.setTitle('Send Test Email');
		this.addCloseButton();
		this.setContent(oContent);
		this.display();		
	},
	
	_RecipientChange: function ()
	{
		this._oRecipientInput.getElementValue()!=null&&this._validEmail(this._oRecipientInput.getElementValue())?this._oSendButton.disabled = false:this._oSendButton.disabled = true;	
	},
	
	_sendMail: function(oResponse)
	{
		debugger;
		if (typeof oResponse == 'undefined' || typeof oResponse.Success == 'undefined')
		{		
			this._oLoadingPopup.display();
			var oData =	{	text: this._oData.text,
							html:	this._oData.html, 
							subject:"[Flex Test Email] " + this._oData.subject,
							to: this._oRecipientInput.getElementValue(),
							name: this._oRecipientName.getElementValue()
						};
			var fnRequest     = jQuery.json.jsonFunction(this._sendMail.bind(this), this._sendMail.bind(this), 'Email_Text_Editor', 'sendTestEmail');
			fnRequest(oData);
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
				Reflex_Popup.alert("There was a problem. Your test email could not be sent", {sTitle: 'Send Test Email'});
				this._oLoadingPopup.hide();
			
			}
			
		
		}
	
	},
	
	_validEmail : function(strEmail)
	{		
		var expEmail	= /^[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[A-Z]{2}|com|org|net|gov|mil|biz|info|mobi|name|aero|jobs|museum)$/i;
		return expEmail.test(strEmail);	
	},
	
	_close : function ()
	{
		
		this.hide();
	},
	
	
	});