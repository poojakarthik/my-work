var Popup_Account_Direct_Debit_Receipt_Email	= Class.create(Reflex_Popup, 
{
	initialize	: function($super, iAccountId, oReceipt, fnOnSent)
	{
		$super(30);
		
		this._iAccountId		= iAccountId;
		this._fnOnSent			= fnOnSent;
		this._aEmails			= [];
		this._aEmailLIs			= [];
		this._hCheckboxes		= {};
		this._oReceipt			= oReceipt;
		this._sPrimaryEmail		= null;
		this._iRecipientCount	= 0;
		
		this._oLoading	= new Reflex_Popup.Loading('Getting Account Contacts...');
		this._oLoading.display();
		
		this._buildUI();
	},
	
	// Private
	
	_buildUI	: function(oResponse)
	{
		if (Object.isUndefined(oResponse))
		{
			// Get the contact email addresses for the account
			var fnReq	= jQuery.json.jsonFunction(this._buildUI.bind(this), this._buildUI.bind(this), 'Account', 'getContactEmailAddresses');
			fnReq(this._iAccountId);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Reflex_Popup.alert(oResponse.sMessage);
			return;
		}
		
		var oCheckboxList	= $T.ul({class: 'reset'});
		var oContact		= null;
		var oItem			= null;
		var oFirstItem		= null;
		for (var sId in oResponse.aContacts)
		{
			oContact	= oResponse.aContacts[sId];
			if (oContact.sEmail)
			{
				oItem	= this._createCheckboxItem(oContact);
				if (oContact.bIsPrimary && oFirstItem)
				{
					oCheckboxList.insertBefore(oItem, oFirstItem);
				}
				else
				{
					oCheckboxList.appendChild(oItem);
				}
				oFirstItem	= oItem;
			}
		}
		
		this._oNoRecipients		= 	$T.li({class: 'popup-account-direct-debit-receipt-email-norecipients'},
										'There are no recipients'
									);
		this._oOtherEmailList	= 	$T.ul({class: 'reset'},
										this._oNoRecipients
									);
		this._oOtherEmail		= 	this._getField('other_email');
		
		var oReceiptSection	= new Section();
		oReceiptSection.setContent(
			$T.div({class: 'popup-account-direct-debit-receipt-email-receipt'},
				'Payment \'' + this._oReceipt.sTransactionId +
				'\' was paid on ' + Date.$parseDate(this._oReceipt.sPaidOn, 'Y-m-d H:i:s').$format('d/m/y') + 
				' and amounted to $' + this._oReceipt.fAmount.toFixed(2)
			)
		);
		
		var oContactSection	= new Section();
		oContactSection.setTitleText('Account Contacts');
		oContactSection.setContent(
			$T.div(
				oCheckboxList,
				$T.div({class: 'popup-account-direct-debit-receipt-email-otheremail'},
					$T.img({src: '../admin/img/template/new.png'}).observe('click', this._addOtherEmail.bind(this, null)),
					this._oOtherEmail.getElement()
				)
			)
		);
		
		var oOtherSection	= new Section();
		oOtherSection.setTitleText('Chosen Recipients');
		oOtherSection.setContent(
			this._oOtherEmailList
		);
		
		var oOtherEmail	= this._oOtherEmail.getElement().select('input').first();
		oOtherEmail.observe('keydown', this._addOtherEmailFromKeyEvent.bind(this));
		oOtherEmail.observe('focus', this._otherEmailHasFocus.bind(this, true));
		oOtherEmail.observe('blur', this._otherEmailHasFocus.bind(this, false));
		
		this.setContent(
			$T.div({class: 'popup-account-direct-debit-receipt-email'},
				$T.div({class: 'popup-account-direct-debit-receipt-email-description'},
					'Please choose which Account Contacts (plus any other email addresses) should receive a copy of the payment receipt.'
				),
				oReceiptSection.getElement(),
				oContactSection.getElement(),
				oOtherSection.getElement(),
				$T.div({class: 'buttons'},
					$T.button({class: 'icon-button'}, 
						'Send Receipt'
					).observe('click', this._sendEmail.bind(this, false)),
					$T.button({class: 'icon-button'}, 
						'Cancel'
					).observe('click', this.hide.bind(this))
				)
			)
		);
		
		this._addOtherEmail(this._sPrimaryEmail);
		
		this.addCloseButton();
		this.setTitle('Choose Recipients');
		
		this._oLoading.hide();
		delete this._oLoading;
		
		this.display();
	},
	
	_createCheckboxItem	: function(oContact)
	{
		var oCheckbox	= this._getField('email');
		oCheckbox.addOnChangeCallback(this._contactSelected.bind(this, oCheckbox, oContact.sEmail));
		
		if (oContact.bIsPrimary)
		{
			this._sPrimaryEmail	= oContact.sEmail;
			oCheckbox.setValue(true);
			oCheckbox.disableInput();
		}
		
		var oName	= $T.span(oContact.sName + (oContact.bIsPrimary ? ' (PRIMARY)' : ''));
		oName.observe('click', this._selectContact.bind(this, oCheckbox, oContact.sEmail));
		
		this._hCheckboxes[oContact.sEmail]	= oCheckbox;
		
		return 	$T.li({class: 'popup-account-direct-debit-receipt-email-contact'},
					oCheckbox.getElement(),
					oName
				);
	},
	
	_addOtherEmail	: function(sEmail)
	{
		try
		{
			debugger;
			sEmail	= sEmail ? sEmail : this._oOtherEmail.getValue(true);
			if ((sEmail != '') && (this._aEmails.indexOf(sEmail) == -1))
			{
				var oLI	= 	$T.li({class: 'popup-account-direct-debit-receipt-email-otheremail-item'},
								$T.span(sEmail)
							);
				
				if (sEmail == this._sPrimaryEmail)
				{
					// Show that this is the primary contacts email address
					oLI.appendChild($T.img({src: '../admin/img/template/primary_contact.png', alt: 'Primary Contact', title: 'Primary Contact'}));
				}
				else
				{
					// Only allow removal if not the primary contacts email
					oLI.appendChild(
						$T.img({class: 'popup-account-direct-debit-receipt-email-otheremail-item-remove', src: '../admin/img/template/delete.png', alt: 'Remove Recipient', title: 'Remove Recipient'}).observe('click', this._removeOtherEmail.bind(this, sEmail))
					);
				}
				
				this._oNoRecipients.hide();
				this._oOtherEmailList.appendChild(oLI);
				this._oOtherEmail.clearValue();
				
				var i				= this._aEmails.push(sEmail) - 1;
				this._aEmailLIs[i]	= oLI;
				this._iRecipientCount++;
			}
		}
		catch (oException)
		{
			// Ignore, invalid email address
		}
	},
	
	_removeOtherEmail	: function(sEmail)
	{
		var i	= this._aEmails.indexOf(sEmail);
		if (i !== -1)
		{
			this._aEmailLIs[i].remove();
			delete this._aEmailLIs[i];
			delete this._aEmails[i];
			
			if (this._hCheckboxes[sEmail] && this._hCheckboxes[sEmail].getValue(true))
			{
				this._hCheckboxes[sEmail].setValue(false);
			}
			
			this._iRecipientCount--;
		}
		
		if (this._iRecipientCount == 0)
		{
			this._oNoRecipients.show();
		}
	},
	
	_addOtherEmailFromKeyEvent	: function(oEvent)
	{
		if (this._bOtherEmailHasFocus && oEvent.keyCode == 13)
		{
			// Enter, add other email
			this._addOtherEmail();
		}
	},
	
	_otherEmailHasFocus	: function(bHasFocus)
	{
		this._bOtherEmailHasFocus	= bHasFocus;
	},
	
	_selectContact	: function(oCheckbox, sEmail)
	{
		if (oCheckbox.getValue(true) && !oCheckbox.isDisabled())
		{
			this._removeOtherEmail(sEmail);
		}
		else
		{
			oCheckbox.setValue(true);
			this._addOtherEmail(sEmail);
		}
	},
	
	_contactSelected	: function(oCheckbox, sEmail)
	{
		if (oCheckbox.getValue(true))
		{
			this._addOtherEmail(sEmail);
		}
		else
		{
			this._removeOtherEmail(sEmail);
		}
	},
	
	_sendEmail	: function(oResponse, oEvent)
	{
		if (!oResponse)
		{
			// Make request
			var aEmails	= [];
			for (var i = 0; i < this._aEmails.length; i++)
			{
				if (this._aEmails[i])
				{
					aEmails.push(this._aEmails[i]);
				}
			}
			
			if (aEmails.length == 0)
			{
				Reflex_Popup.alert('Please choose atleast one recipient');
				return;
			}
			
			this._oLoading	= new Reflex_Popup.Loading('Sending Receipt...');
			this._oLoading.display();
			
			var fnReq	= jQuery.json.jsonFunction(this._sendEmail.bind(this), null, 'Account', 'sendDirectDebitReceiptEmail');
			fnReq(this._oReceipt, aEmails);
			return;
		}
		
		this._oLoading.hide();
		delete this._oLoading;
		
		// Got response
		if (!oResponse.bSuccess)
		{
			// Processing error
			Reflex_Popup.alert(oResponse.sMessage);
			return;
		}
		
		// All good
		this.hide();
		if (this._fnOnSent)
		{
			this._fnOnSent();
		}
	},
	
	_getField	: function(sFieldName)
	{
		var oConfig	= Popup_Account_Direct_Debit_Receipt_Email.FIELDS[sFieldName];
		if (oConfig)
		{
			var oField	= Control_Field.factory(oConfig.sType, oConfig.oConfig);
			oField.setRenderMode(Control_Field.RENDER_MODE_EDIT);
			return oField;
		}
		return null;
	},
});

// Static

Object.extend(Popup_Account_Direct_Debit_Receipt_Email, 
{
	FIELDS	:
	{
		'other_email'	:
		{
			sType	: 'text',
			oConfig	: 
			{
				sLabel		: 'Other',
				mEditable	: true,
				mVisible	: true,
				fnValidate	: Reflex_Validation.email
			}
		},
		'email'	:
		{
			sType	: 'checkbox',
			oConfig	: 
			{
				sLabel		: 'Email',
				mEditable	: true,
				mVisible	: true
			}
		}
	}
});
