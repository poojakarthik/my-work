
var Popup_Contact_Edit	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iContactId, iAccount, fnAfterSave)
	{
		$super(50);
		this.iContactId		= iContactId;
		this.iAccount		= iAccount;
		this.oContact		= null;
		this.fnAfterSave	= fnAfterSave;
		this.hInputs		= {};
		this._buildUI();
	},

	_buildUI	: function(oResponse)
	{
		if (typeof oResponse === 'undefined')
	{
			// We're and edit popup, Make AJAX Request to prepopulate the contacts details
			var _getContact	= jQuery.json.jsonFunction(this._buildUI.bind(this), this._buildUI.bind(this), 'Contact', 'getForId');
			_getContact(this.iContactId);
			return;
		}
		else if (oResponse.Success == false)
		{
			// AJAX Error
			Popup_Contact_Edit._ajaxError(oResponse, true);
			return;
		}
		
		// Build UI
		var oContent 	=	$T.div({class: 'contact-edit-table'},
								$T.table({class: 'reflex'},
									$T.caption(
										$T.div({id: 'caption_bar', class: 'caption_bar'},
											$T.div({id: 'caption_title', class: 'caption_title'},
												'Contact Details'
											)
										)
									),
									$T.tbody(
										$T.tr(
											$T.th({class: 'label'},
												'Title :'
											),
											$T.td(
												$T.select({class: 'contact-edit-title'}
													// .. added below
												)
											)
										),
										$T.tr(
											$T.th({class: 'label'},
												'First Name :'
											),
											$T.td(
												$T.input({type: 'text'})
											)
										),
										$T.tr(
											$T.th({class: 'label'},
												'Last Name :'
											),
											$T.td(
												$T.input({type: 'text'})
											)
										),
										$T.tr(
											$T.th({class: 'label'},
												'Job Title :'
											),
											$T.td(
												$T.input({type: 'text'})
											)
										),
										$T.tr(
											$T.th({class: 'label'},
												'Date of Birth :'
											),
											$T.td(
												$T.select({class: 'contact-edit-dob-dd'}),
												$T.select({class: 'contact-edit-dob-mm'}),
												$T.select({class: 'contact-edit-dob-yyyy'})
											)
										),
										$T.tr(
											$T.th({class: 'label'},
												'Email Address :'
											),
											$T.td(
												$T.input({type: 'text'})
											)
										),
										$T.tr(
											$T.th({class: 'label'},
												'Phone Number :'
											),
											$T.td(
												$T.input({type: 'text'})
											)
										),
										$T.tr(
											$T.th({class: 'label'},
												'Mobile Number :'
											),
											$T.td(
												$T.input({type: 'text'})
											)
										),
										$T.tr(
											$T.th({class: 'label'},
												'Fax Number :'
											),
											$T.td(
												$T.input({type: 'text'})
											)
										),
										$T.tr(
											$T.th({class: 'label'},
												'Password :'
											),
											$T.td({class: 'contact-edit-password'},
												$T.div(
													$T.input({type: 'checkbox'}),
													' Change Password'
												),
												$T.ul({class: 'reset'},
													$T.li(
														$T.ul({class: 'reset horizontal'},
															$T.li('Password'),
															$T.li(
																$T.input({type: 'password'})
															)
														)
													),
													$T.li(
														$T.ul({class: 'reset horizontal'},
															$T.li('Confirm Password'),
															$T.li(
																$T.input({type: 'password'})
															)
														)
													)
												)
											)
										),
										$T.tr(
											$T.th({class: 'label'},
												'Account Access :'
											),
											$T.td(
												$T.ul({class: 'reset'},
													$T.li(
														$T.input({type: 'radio', name: 'contact-edit-access', value: '0'}),
														' Allow access to this Account only'
													),
													$T.li(
														$T.input({type: 'radio', name: 'contact-edit-access', value: '1'}),
														' Allow access to all Associated Accounts'
													)
												)
											)
										),
										$T.tr(
											$T.th({class: 'label'},
												'Status :'
											),
											$T.td(
												$T.select(
													$T.option({value: 0},
														'Active'
													),
													$T.option({value: 1},
														'Archived'
													)
												)
											)
										)
									)
								),
								$T.div({class: 'contact-edit-buttons'},
									$T.button({class: 'icon-button'},
										$T.img({src: Popup_Contact_Edit.SAVE_IMAGE_SOURCE, alt: '', title: 'Save'}),
										$T.span('Save')
									),
									$T.button({class: 'icon-button'},
										$T.img({src: Popup_Contact_Edit.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel'}),
										$T.span('Cancel')
									)
								)
							);
		
		// Save button event handler
		var oSaveButton	= oContent.select( 'button' ).last().previous();
		oSaveButton.observe('click', this._saveChanges.bind(this));
		
		// Cancel button event handler
		var oCancelButton = oContent.select( 'button' ).last();
		oCancelButton.observe('click', this._showCancelConfirmation.bind(this));
		
		// Populate the d.o.b select options
		var oDD		= oContent.select('select.contact-edit-dob-dd').first();
		var oMM		= oContent.select('select.contact-edit-dob-mm').first();
		var oYYYY	= oContent.select('select.contact-edit-dob-yyyy').first();
		
		Popup_Contact_Edit._populateNumberSelect(oDD, 1, 31, 'DD');
		Popup_Contact_Edit._populateNumberSelect(oMM, 1, 12, 'MM');
		
		var iYear	= new Date().getFullYear();
		Popup_Contact_Edit._populateNumberSelect(oYYYY, iYear - 150, iYear, 'YYYY');
		
		// Populate the title select
		var oTitleSelect	= oContent.select('select.contact-edit-title').first();
		
		// Start with 'None'
		oTitleSelect.appendChild(
			$T.option({value: ''},
				'None'
			)
		);
		
		// Add rest of valid titles
		for (var i = 0; i < oResponse.aContactTitles.length; i++)
		{
			oTitleSelect.appendChild(
				$T.option({value: oResponse.aContactTitles[i]},
					oResponse.aContactTitles[i]
				)
			);
		}
		
		// Setup input validate event handlers (selects first, then inputs)
		var aInputs				= oContent.select('select, input');
		aInputs[0].sFieldName	= 'Title';
		aInputs[0].bRequired	= false;
				
		aInputs[1].sFieldName	= 'Day of Birth';
		aInputs[1].bRequired	= true;
				
		aInputs[2].sFieldName	= 'Month of Birth';
		aInputs[2].bRequired	= true;
		
		aInputs[3].sFieldName	= 'Year of Birth';
		aInputs[3].bRequired	= true;
		
		aInputs[4].sFieldName	= 'Status';
		aInputs[4].bRequired	= false;
		
		aInputs[5].sFieldName	= 'First Name';
		aInputs[5].bRequired	= true;
				
		aInputs[6].sFieldName	= 'Last Name';
		aInputs[6].bRequired	= true;
				
		aInputs[7].sFieldName	= 'Job Title';
				
		aInputs[8].sFieldName	= 'Email Address';
		aInputs[8].bRequired	= true;
				
		aInputs[9].sFieldName	= 'Phone Number';
		aInputs[9].bRequired	= false;
				
		aInputs[10].sFieldName	= 'Mobile Number';
		aInputs[10].bRequired	= false;
				
		aInputs[11].sFieldName	= 'Fax Number';
		aInputs[11].bRequired	= false;
		
		aInputs[12].sFieldName	= 'Change Password';
		aInputs[13].sFieldName	= 'Password';
		aInputs[14].sFieldName	= 'Confirm Password';
		aInputs[15].sFieldName	= 'AccountAccess-0';
		aInputs[16].sFieldName	= 'AccountAccess-1';
		
		for (var i = 0; i < aInputs.length; i++)
		{
			if (typeof aInputs[i].sFieldName !== 'undefined')
			{
				this.hInputs[aInputs[i].sFieldName] = aInputs[i];
			}
		}
		
		this.hInputs['Title'].validate			= 	Popup_Contact_Edit._validateInput.bind(
														this.hInputs['Title'], 		
														Reflex_Validation.Exception.nonEmptyString
													);
		this.hInputs['Day of Birth'].validate	= 	Popup_Contact_Edit._validateInput.bind(
														this.hInputs['Day of Birth'], 		
														Reflex_Validation.Exception.nonEmptyDigits
													);
		this.hInputs['Month of Birth'].validate	= 	Popup_Contact_Edit._validateInput.bind(
														this.hInputs['Month of Birth'], 		
														Reflex_Validation.Exception.nonEmptyDigits
													);
		this.hInputs['Year of Birth'].validate	= 	Popup_Contact_Edit._validateInput.bind(
														this.hInputs['Year of Birth'], 		
														Reflex_Validation.Exception.nonEmptyDigits
													);
		this.hInputs['First Name'].validate		=	Popup_Contact_Edit._validateInput.bind(
														this.hInputs['First Name'], 		
														Reflex_Validation.Exception.nonEmptyString
													);
		this.hInputs['Last Name'].validate		=	Popup_Contact_Edit._validateInput.bind(
														this.hInputs['Last Name'], 		
														Reflex_Validation.Exception.nonEmptyString
													);
		this.hInputs['Email Address'].validate	=	Popup_Contact_Edit._validateInput.bind(
														this.hInputs['Email Address'], 		
														Reflex_Validation.Exception.email
													);
		this.hInputs['Phone Number'].validate	=	Popup_Contact_Edit._validateInput.bind(
														this.hInputs['Phone Number'], 		
														Reflex_Validation.Exception.fnnFixedOrInbound
													);
		this.hInputs['Mobile Number'].validate	=	Popup_Contact_Edit._validateInput.bind(
														this.hInputs['Mobile Number'], 		
														Reflex_Validation.Exception.fnnMobile
													);
		this.hInputs['Fax Number'].validate		=	Popup_Contact_Edit._validateInput.bind(
														this.hInputs['Fax Number'], 		
														Reflex_Validation.Exception.fnnFixedOrInbound
													);
		this.hInputs['Status'].validate			=	Popup_Contact_Edit._validateInput.bind(
														this.hInputs['Status'], 		
														Reflex_Validation.Exception.nonEmptyDigits
													);

		for (var sName in this.hInputs)
		{
			if (typeof this.hInputs[sName].validate !== 'undefined')
			{
				this.hInputs[sName].observe('keyup', this.hInputs[sName].validate);
				this.hInputs[sName].observe('change', this.hInputs[sName].validate);
			}
		}
		
		this.oContent	= oContent;
		
		// Pre populate the contacts data if available
		if ((typeof oResponse.oContact !== 'undefined'))
		{
			// Must be editing
			this.oContact	= oResponse.oContact;
			
			this.hInputs['Title'].value				= this.oContact.Title;
			this.hInputs['Day of Birth'].value		= this.oContact.dob_day;
			this.hInputs['Month of Birth'].value	= this.oContact.dob_month;
			this.hInputs['Year of Birth'].value		= this.oContact.dob_year;
			this.hInputs['First Name'].value		= this.oContact.FirstName;
			this.hInputs['Last Name'].value			= this.oContact.LastName;
			this.hInputs['Job Title'].value			= this.oContact.JobTitle;
			this.hInputs['Email Address'].value		= this.oContact.Email;
			this.hInputs['Phone Number'].value		= this.oContact.Phone;
			this.hInputs['Mobile Number'].value		= this.oContact.Mobile;
			this.hInputs['Fax Number'].value		= this.oContact.Fax;
			this.hInputs['Status'].value			= this.oContact.Archived;
			
			// Account access (customer contact)
			this.hInputs['AccountAccess-' + this.oContact.CustomerContact].checked = true;
			
			// Set title
			this.setTitle('Edit Contact Details');
			
			// Password checkbox event handler
			var oPasswordCheckbox = oContent.select( 'td.contact-edit-password input[type="checkbox"]' ).first();
			oPasswordCheckbox.observe('click', this._showPasswordForm.bind(this, oPasswordCheckbox));
			this._showPasswordForm(oPasswordCheckbox);
		}
		else if (this.iAccount !== null)
		{
			// New contact, create contact details object with just account and account group, for saving
			this.oContact	= {Account	: this.iAccount};
			
			this.hInputs['AccountAccess-1'].checked = true;
			
			// Hide the checkbox and 'Change Password' text
			oContent.select( 'td.contact-edit-password > div').first().style.display	= 'none';
			
			// Set title
			this.setTitle('Add Contact');
		}
		
		this.setIcon('../admin/img/template/contact_small.png');
		this.addCloseButton();
		this.setContent(oContent);
		this.display();
		this._isValid();
	},
	
	_isValid	: function()
	{
		// Build an array of error messages, after running all validation functions
		var aErrors	= [];
		var mError 	= null;
		var oInput 	= null;
		
		for (var sName in this.hInputs)
		{
			oInput = this.hInputs[sName];
			
			if (typeof oInput.validate !== 'undefined')
			{
				mError = oInput.validate();
				
				if (mError != null)
				{
					aErrors.push(mError);
				}
			}
		}
		
		// Special case for phone number and mobile number
		if ((this.hInputs['Phone Number'].value === '') && (this.hInputs['Mobile Number'].value === ''))
		{
			aErrors.push('Either a Phone Number or Mobile Number must be given');
		}
		
		// Check passwords match (if given)
		if (this.hInputs['Change Password'].checked || (this.iContactId == null))
		{
			if ((this.hInputs['Password'].value == '') && (this.hInputs['Confirm Password'].value == ''))
			{
				aErrors.push('Password and password confirmation are missing');
			}
			
			if (this.hInputs['Password'].value != this.hInputs['Confirm Password'].value)
			{
				aErrors.push('Password and password confirmation must match');
			}
		}
		
		return aErrors;
	},
	
	_showCancelConfirmation	: function()
	{
		Reflex_Popup.yesNoCancel('Are you sure you want to cancel and revert all changes?', {sTitle: 'Revert Changes', fnOnYes: this.hide.bind(this)});
	},
	
	_saveChanges	: function()
	{
		var aErrors = this._isValid();
		
		if (aErrors.length)
		{
			Popup_Contact_Edit._showValidationErrorPopup(aErrors);
			return;
		}
		
		// Create a Popup to show 'saving...' close it when save complete
		var oPopup = new Reflex_Popup.Loading('Saving...');
		oPopup.display();
		
		// Get the values
		var oContactDetails	=	{
									sTitle				: this.hInputs['Title'].value,
									sFirstName			: this.hInputs['First Name'].value,
									sLastName			: this.hInputs['Last Name'].value,
									sJobTitle			: this.hInputs['Job Title'].value,
									iDOBDay				: parseInt(this.hInputs['Day of Birth'].value),
									iDOBMonth			: parseInt(this.hInputs['Month of Birth'].value),
									iDOBYear			: parseInt(this.hInputs['Year of Birth'].value),
									sEmail				: this.hInputs['Email Address'].value,
									iPhone				: (this.hInputs['Phone Number'].value == '') ? '' : this.hInputs['Phone Number'].value,
									iMobile				: (this.hInputs['Mobile Number'].value == '') ? '' : this.hInputs['Mobile Number'].value,
									iFax				: (this.hInputs['Fax Number'].value == '') ? '' : this.hInputs['Fax Number'].value,
									iCustomerContact	: parseInt(this.hInputs['AccountAccess-0'].checked ? this.hInputs['AccountAccess-0'].value : this.hInputs['AccountAccess-1'].value),
									iArchived			: this.hInputs['Status'].value,
									iAccount			: this.oContact.Account,
									iId					: this.oContact.Id
								};
		
		// Only send password if 'Change Password' is checked
		if (this.hInputs['Change Password'].checked || (this.iContactId == null))
		{
			oContactDetails.sPassword	= this.hInputs['Password'].value;
		}
		
		// AJAX request to save changes
		var _saveContactDetails	= jQuery.json.jsonFunction(this._saveComplete.bind(this,oPopup), Popup_Contact_Edit._ajaxError.bind(this), 'Contact', 'save');
		_saveContactDetails(oContactDetails);
	},
	
	_saveComplete	: function(oPopup, oResponse)
	{
		if (oPopup)
		{
			oPopup.hide();
		}
		
		if (oResponse.Success)
		{
			// Success! Close this popup
			this.hide();
			
			// Execute after save callback
			if (this.fnAfterSave)
			{
				this.fnAfterSave();
			}
		}
		else if (oResponse.aValidationErrors)
		{
			// Validation errors
			Popup_Contact_Edit._showValidationErrorPopup(oResponse.aValidationErrors);
		}
		else
		{
			// Error
			Popup_Contact_Edit._ajaxError(oResponse);
		}
	},
	
	_showPasswordForm	: function(oCheckbox)
	{
		var oUL	= this.oContent.select('td.contact-edit-password > ul').first();
		
		if (oCheckbox.checked)
		{
			oUL.style.display	= 'block';
		}
		else
		{
			var aInputs			= this.oContent.select('td.contact-edit-password ul > li > input');
			aInputs[0].value	= '';
			aInputs[1].value	= '';
			oUL.style.display	= 'none';
		}
		
			
	}
});

Popup_Contact_Edit.CANCEL_IMAGE_SOURCE 	= '../admin/img/template/delete.png';
Popup_Contact_Edit.SAVE_IMAGE_SOURCE 	= '../admin/img/template/tick.png';

Popup_Contact_Edit._ajaxError	= function(oResponse, bHideOnClose)
{
	if (oResponse.Success == false)
	{
		var oConfig	= {sTitle: 'Error', fnOnClose: (bHideOnClose ? this.hide.bind(this) : null)};
		
		if (oResponse.Message)
		{
			Reflex_Popup.alert(oResponse.Message, oConfig);
		}
		else if (oResponse.ERROR)
		{
			Reflex_Popup.alert(oResponse.ERROR, oConfig);
		}
	}
};

Popup_Contact_Edit._populateNumberSelect	= function(oSelect, iLowest, iHighest, sFirstItem)
{
	// Add optional first item
	if (sFirstItem)
	{
		oSelect.appendChild(
			$T.option({value: ''},
				sFirstItem
			)
		);
	}
	
	// Add numbers within bounds
	for (var iValue = iLowest; iValue <= iHighest; iValue++)
	{
		oSelect.appendChild(
			$T.option({value: iValue},
				(iValue < 10 ? '0' + iValue : iValue)
			)
		);
	}
};

Popup_Contact_Edit._showValidationErrorPopup	= function(aErrors)
{
	// Build UL of error messages
	var oValidationErrors = $T.ul();
	
	for (var i = 0; i < aErrors.length; i++)
	{
		oValidationErrors.appendChild(
							$T.li(aErrors[i])
						);
	}
	
	// Show a popup containing the list
	Reflex_Popup.alert(
					$T.div({style: 'margin: 0.5em'},
						'The following errors have occured: ',
						oValidationErrors
					),
					{
						iWidth	: 30,
						sTitle	: 'Validation Errors'
					}
				);
};

Popup_Contact_Edit._validateInput	= function(fnValidate)
{
	// This is to be bound to the scope of an input
	try
	{
		this.removeClassName('valid');
		this.removeClassName('invalid');
		
		// Check required validation first
		if (this.value == '' || this.value === null)
		{
			if (this.bRequired)
			{
				throw('Required field');
			}
			
			return null;
		}
		else
		{
			if (fnValidate(this.value))
			{
				this.addClassName('valid');
			}
			
			return null;
		}
	}
	catch (e)
	{
		this.addClassName('invalid');
		return this.sFieldName + ': ' + e; 
	}
};

Popup_Contact_Edit._goToPage	= function(sUrl)
{
	window.location = (sUrl ? sUrl : window.location); 
}
