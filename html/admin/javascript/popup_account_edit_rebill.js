var Popup_Account_Edit_Rebill	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iAccountId, iRebillTypeId, fnOnSave, fnOnCancel)
	{
		if (!Popup_Account_Edit_Rebill.HAS_CONSTANTS)
		{
			return;
		}

		$super(40);

		this.iAccountId		= iAccountId;
		this.iRebillTypeId	= iRebillTypeId;
		this.fnOnSave		= fnOnSave;
		this.fnOnCancel		= fnOnCancel;
		this.oRebill		= null;
		this._hFields		= {};
		this._hHiddenValues = {};
		this.oLoading		= new Reflex_Popup.Loading('Please Wait');
		this.oLoading.display();
		this._buildUI();
	},

	// Private

	_buildUI	: function(oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Make request to get the current payment and information on other methods
			var fnGetRebillForAccount	=	jQuery.json.jsonFunction(
												this._buildUI.bind(this),
												this._ajaxError.bind(this, true),
												'Account',
												'getRebill'
											);
			fnGetRebillForAccount(this.iAccountId);
		}
		else if (oResponse.Success)
		{
			// Hide the loading popup
			this.oLoading.hide();
			delete this.oLoading;

			// Cache the response
			this.oRebill	= (oResponse.oRebill ? oResponse.oRebill : false);

			// Build content
			this._oContent	= 	$T.div({class: 'popup-account-edit-rebill'},
									$T.div({class: 'tabgroup'}
										// Content to come
									),
									$T.div({class: 'buttons'},
										$T.button({class: 'icon-button'},
											$T.img({src: Popup_Account_Edit_Rebill.SAVE_IMAGE_SOURCE, alt: '', title: 'Save'}),
											$T.span('Save')
										),
										$T.button({class: 'icon-button'},
											$T.img({src: Popup_Account_Edit_Rebill.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel'}),
											$T.span('Cancel')
										)
									)
								);

			// Button events
			var oAddButton		= this._oContent.select('div.buttons > button.icon-button').first();
			oAddButton.observe('click', this._saveButtonClick.bind(this));

			var oCancelButton	= this._oContent.select('div.buttons > button.icon-button').last();
			oCancelButton.observe('click', this._cancelEdit.bind(this));

			// Create tab group
			var oTabContainer	= this._oContent.select('div.tabgroup').first();
			this._oTabGroup		= new Control_Tab_Group(oTabContainer, true);

			// Generate control fields
			var hPanels		= Popup_Account_Edit_Rebill.PANELS;
			var hFields		= Popup_Account_Edit_Rebill.FIELDS[this.iRebillTypeId];
			var mConfig		= null;
			var oControl	= null;
			var aFields		= null;
			var sFieldName	= null;
			var oTabContent	= null;
			var oTBody		= null;
			//debugger;
			for (var sPanelName in hPanels)
			{
				oTabContent	=	$T.table({class: 'reflex input'},
									oTBody = $T.tbody({class: 'popup-account-edit-rebill-fields'})
								);
				aFields		= hPanels[sPanelName].aFields;
				for (var i = 0; i < aFields.length; i++)
				{
					sFieldName	= aFields[i];
					mConfig		= hFields[sFieldName];
					if (typeof mConfig == 'string')
					{
						// Sub title
						oTBody.appendChild(
							$T.tr({class: 'subtitle'},
								$T.th(mConfig),
								$T.td()
							)
						);
					}
					else if (mConfig.bHidden)
					{

						if (this.oRebill && (typeof this.oRebill.oDetails[sFieldName] != 'undefined'))
						{
							this._hHiddenValues[sFieldName]	= this.oRebill.oDetails[sFieldName];
						}
						else
						{
							this._hHiddenValues[sFieldName]	= null;
						}
					}
					else
					{
						// Field definition
						oControl	= Control_Field.factory(mConfig.sType, mConfig.oDefinition);

						if (this.oRebill && (typeof this.oRebill.oDetails[sFieldName] != 'undefined'))
						{
							oControl.setValue(this.oRebill.oDetails[sFieldName]);
						}
						else
						{
							oControl.setValue('');
						}

						oControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
						oTBody.appendChild(oControl.generateInputTableRow().oElement);
						this._hFields[sFieldName]	= oControl;
					}
				}
				this._oTabGroup.addTab(sPanelName, new Control_Tab(sPanelName, oTabContent));
			}

			// Popup setup
			this.setTitle('Edit Rebill (' + Flex.Constant.arrConstantGroups.rebill_type[this.iRebillTypeId].Name + ')');
			this.setIcon(Popup_Account_Edit_Rebill.ICON_IMAGE_SOURCE);
			this.setContent(this._oContent);
			this.display();
		}
	},

	_ajaxError	: function(bHideOnClose, oResponse)
	{
		if (this.oLoading)
		{
			this.oLoading.hide();
			delete this.oLoading;
		}

		var oConfig	= {sTitle: 'Error', fnOnClose: (bHideOnClose ? this.hide.bind(this) : null)};

		if (oResponse.Message)
		{
			Reflex_Popup.alert(oResponse.Message, oConfig);
		}
		else if (oResponse.ERROR)
		{
			Reflex_Popup.alert(oResponse.ERROR, oConfig);
		}
		else if (oResponse.aValidationErrors)
		{
			Popup_Account_Edit_Rebill.showValidationErrors(oResponse.aValidationErrors);
		}
	},

	_saveButtonClick	: function()
	{
		this._addRebill();
	},

	_addRebill	: function(oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Validate fields
			var aValidationErrors	= [];
			var oDetails			= {};
			for (var sFieldName in this._hFields)
			{
				try
				{
					// If valid, record the value
					if (this._hFields[sFieldName].validate(false))
					{
						oDetails[sFieldName]	= this._hFields[sFieldName].getValue(true);
					}
				}
				catch (ex)
				{
					aValidationErrors.push(ex);
				}
			}

			// Add hidden field values to the details object
			for (var sFieldName in this._hHiddenValues)
			{
				oDetails[sFieldName]	= this._hHiddenValues[sFieldName];
			}

			// Check for errors, show popup if any
			if (aValidationErrors.length)
			{
				Popup_Account_Edit_Rebill.showValidationErrors(aValidationErrors);
				return;
			}

			// Make ajax request, sending rebill type and rebill details
			this.oLoading	= new Reflex_Popup.Loading('Please Wait');
			this.oLoading.display();

			var fnAddRebill	=	jQuery.json.jsonFunction(
									this._addRebill.bind(this),
									this._ajaxError.bind(this, false),
									'Account',
									'addRebill'
								);
			//debugger;
			fnAddRebill(this.iAccountId, this.iRebillTypeId, oDetails);
		}
		else if (oResponse.Success)
		{
			// Hide loading
			this.oLoading.hide();
			delete this.oLoading;

			// All good
			if (this.fnOnSave)
			{
				this.fnOnSave(oResponse.oRebill);
			}

			this.hide();
		}
		else
		{
			// Error
			this._ajaxError(false, oResponse);
		}
	},

	_cancelEdit	: function()
	{
		// Cancel callback
		if (typeof this.fnOnCancel !== 'undefined')
		{
			this.fnOnCancel();
		}

		this.hide();
	},

	_businessStructureChange	: function()
	{
		var oField	= this._hFields['account_business_structure_id'];
		var oDesc	= this._hFields['account_business_structure_description'];
		if (oField.getElementValue() == $CONSTANT.MOTORPASS_BUSINESS_STRUCTURE_OTHER)
		{
			oDesc.show();
		}
		else
		{
			oDesc.hide();
		}
	},

	_cardTypeChange	: function()
	{
		var oField	= this._hFields['card_card_type_id'];
		var oDesc	= this._hFields['card_card_type_description'];
		if (oField.getElementValue() == $CONSTANT.MOTORPASS_CARD_TYPE_OTHER)
		{
			oDesc.show();
		}
		else
		{
			oDesc.hide();
		}
	}
});


// ----------------
// STATIC MEMBERS
// ----------------


Popup_Account_Edit_Rebill._formatDate	= function(sDate)
{
	return Date.$format('j/n/Y', Date.parse(sDate.replace(/-/g, '/')) / 1000);
}

Popup_Account_Edit_Rebill.showValidationErrors	= function(aErrors)
{
	// Create a UL to list the errors and then show a reflex alert
	var oAlertDom	=	$T.div({class: 'rebill-validation-errors'},
							$T.div('There were errors in the rebill information: '),
							$T.ul(
								// Added here...
							)
						);
	var oUL	= oAlertDom.select('ul').first();

	for (var i = 0; i < aErrors.length; i++)
	{
		oUL.appendChild($T.li(aErrors[i]));
	}

	Reflex_Popup.alert(oAlertDom, {iWidth: 30});
}

Popup_Account_Edit_Rebill.validateWithLength	= function(fnValidate, iLength, sValue)
{
	if ((fnValidate === null) || fnValidate(sValue))
	{
		if (sValue.length > iLength)
		{
			return false;
		}

		return true;
	}
	else
	{
		return false;
	}
};

Popup_Account_Edit_Rebill.getDaysInMonth	= function(iMonth, iYear)
{
	var oDate	= new Date(iYear, iMonth, 0);
	return oDate.getDate();
};

Popup_Account_Edit_Rebill.validateCardExpiry	= function(sValue)
{
	var aSplit	= sValue.split(Control_Field_Combo_Date.DATE_SEPARATOR);
	var oDate	= new Date();
	var iNow	= oDate.getTime();
	oDate.setFullYear(parseInt(aSplit[0]));
	oDate.setMonth(parseInt(aSplit[1]) - 1);

	// No day, use the end of the month
	oDate.setDate(Popup_Account_Edit_Rebill.getDaysInMonth(oDate.getMonth() + 1, oDate.getFullYear()));

	if (oDate.getTime() > iNow)
	{
		return true;
	}

	return false;
};

Popup_Account_Edit_Rebill.validatePastDate	= function(mValue)
{
	var oDate	= Date.$parseDate(mValue, 'Y-m-d');
	if (oDate)
	{
		if (oDate.getTime() >= new Date().getTime())
		{
			// Future or present
			return false;
		}
		else
		{
			return true;
		}
	}

	// Not a valid date
	return false;
};

Popup_Account_Edit_Rebill.getOptions	= function(sConstantGroup, fnCallback)
{
	var oConstantGroup	= Flex.Constant.arrConstantGroups[sConstantGroup];
	if (oConstantGroup)
	{
		var aOptions	= [];
		for (var i in oConstantGroup)
		{
			aOptions.push(
				$T.option({value: i},
					oConstantGroup[i].Name
				)
			);
		}
		fnCallback(aOptions);
	}
};

//Check if $CONSTANT has correct constant groups loaded, if not this class won't work
if (typeof Flex.Constant.arrConstantGroups.rebill_type == 'undefined' ||
	typeof Flex.Constant.arrConstantGroups.payment_method == 'undefined' ||
	typeof Flex.Constant.arrConstantGroups.motorpass_business_structure == 'undefined' ||
	typeof Flex.Constant.arrConstantGroups.motorpass_card_type == 'undefined')
{
	Popup_Account_Edit_Rebill.HAS_CONSTANTS	= false;
	throw ('Please load the correct constant groups before using Popup_Account_Edit_Rebill (see source for more info).');
}
else
{
	Popup_Account_Edit_Rebill.HAS_CONSTANTS	= true;
}

// Image paths
Popup_Account_Edit_Rebill.ICON_IMAGE_SOURCE 	= '../admin/img/template/rebill.png';
Popup_Account_Edit_Rebill.CANCEL_IMAGE_SOURCE 	= '../admin/img/template/delete.png';
Popup_Account_Edit_Rebill.SAVE_IMAGE_SOURCE 	= '../admin/img/template/tick.png';

// Editing fields
var oNow	= new Date();
Popup_Account_Edit_Rebill.FIELDS	= {};
Popup_Account_Edit_Rebill.FIELDS[$CONSTANT.REBILL_TYPE_MOTORPASS]	=
{
	account_id	:
	{
		bHidden	: true
	},
	account_account_number	:
	{
		sType		: 'text',
		oDefinition	:
		{
			sLabel		: 'Account Number',
			mEditable	: true,
			mMandatory	: true,
			fnValidate	: Popup_Account_Edit_Rebill.validateWithLength.curry(Reflex_Validation.digits, 9)
		}
	},
	account_account_name	:
	{
		sType		: 'text',
		oDefinition	:
		{
			sLabel		: 'Account Name',
			mEditable	: true,
			mMandatory	: true,
			fnValidate	: Popup_Account_Edit_Rebill.validateWithLength.curry(null, 256)
		}
	},
	card_card_expiry_date	:
	{
		sType		: 'combo_date',
		oDefinition	:
		{
			sLabel				: 'Card Expiry Month',
			mEditable			: true,
			mMandatory			: true,
			iMinYear			: oNow.getFullYear(),
			iMaxYear			: oNow.getFullYear() + 10,
			fnValidate			: Popup_Account_Edit_Rebill.validateCardExpiry,
			sValidationReason	: 'It must be in the future.',
			iFormat				: Control_Field_Combo_Date.FORMAT_M_Y
		}
	},
	account_motorpass_promotion_code_id	:
	{
		sType		: 'select',
		oDefinition	:
		{
			sLabel		: 'Promotion Code',
			mEditable	: true,
			mMandatory	: true,
			fnPopulate	: Motorpass_Promotion_Code.getAllAsSelectOptions
		}
	},

	account_business_commencement_date	:
	{
		sType	: 'date-picker',
		oDefinition	:
		{
			sLabel		: 'Business Commencement Date',
			iMinYear	: 1900,
			iMaxYear	: new Date().getFullYear(),
			Mandatory	: true,
			mEditable	: true,
			fnValidate	: Popup_Account_Edit_Rebill.validatePastDate
		}
	},
	account_motorpass_business_structure_id	:
	{
		sType	: 'select',
		oDefinition	:
		{
			sLabel		: 'Business Structure',
			mMandatory	: true,
			mEditable	: true,
			fnPopulate	: Popup_Account_Edit_Rebill.getOptions.curry('motorpass_business_structure')
		}
	},
	account_business_structure_description	:
	{
		sType	: 'text',
		oDefinition	:
		{
			sLabel		: 'Business Structure Description',
			mEditable	: true
		}
	},
	card_motorpass_card_type_id	:
	{
		sType	: 'select',
		oDefinition	:
		{
			sLabel		: 'Type',
			mEditable	: true,
			mMandatory	: true,
			fnPopulate	: Popup_Account_Edit_Rebill.getOptions.curry('motorpass_card_type')
		}
	},
	card_card_type_description	:
	{
		sType	: 'text',
		oDefinition	:
		{
			sLabel		: 'Description',
			mEditable	: true
		}
	},
	card_shared	:
	{
		sType	: 'checkbox',
		oDefinition	:
		{
			sLabel		: 'Shared',
			mEditable	: true
		}
	},
	card_holder_label				: 'Card Holder',
	card_holder_contact_title_id	:
	{
		sType	: 'select',
		oDefinition	:
		{
			sLabel		: 'Title',
			mEditable	: true,
			fnPopulate	: Contact_Title.getAllAsSelectOptions
		}
	},
	card_holder_first_name	:
	{
		sType	: 'text',
		oDefinition	:
		{
			sLabel		: 'First Name',
			mEditable	: true
		}
	},
	card_holder_last_name	:
	{
		sType	: 'text',
		oDefinition	:
		{
			sLabel		: 'Last Name',
			mEditable	: true
		}
	},
	card_vehicle_model	:
	{
		sType	: 'text',
		oDefinition	:
		{
			sLabel		: 'Model',
			mEditable	: true
		}
	},
	card_vehicle_rego	:
	{
		sType	: 'text',
		oDefinition	:
		{
			sLabel		: 'Rego',
			mEditable	: true
		}
	},
	card_vehicle_make	:
	{
		sType	: 'text',
		oDefinition	:
		{
			sLabel		: 'Make',
			mEditable	: true
		}
	},
	street_address_label	: 'Street Address',
	street_address_line_1	:
	{
		sType	: 'text',
		oDefinition	:
		{
			sLabel		: 'Address 1',
			mMandatory	: true,
			mEditable	: true
		}
	},
	street_address_line_2	:
	{
		sType	: 'text',
		oDefinition	:
		{
			sLabel		: 'Address 2',
			mEditable	: true
		}
	},
	street_address_suburb	:
	{
		sType	: 'text',
		oDefinition	:
		{
			sLabel		: 'Suburb',
			mMandatory	: true,
			mEditable	: true
		}
	},
	street_address_state_id	:
	{
		sType	: 'select',
		oDefinition	:
		{
			sLabel		: 'State',
			mMandatory	: true,
			mEditable	: true,
			fnPopulate	: State.getAllAsSelectOptions
		}
	},
	street_address_postcode	:
	{
		sType	: 'text',
		oDefinition	:
		{
			sLabel		: 'Postcode',
			mMandatory	: true,
			mEditable	: true
		}
	},
	postal_address_label	: 'Postal Address',
	/*postal_address_same	:
	{
		sType	: 'checkbox',
		oDefinition	:
		{
			sLabel		: 'Same as Street Address',
			mEditable	: true
		}
	},*/
	postal_address_line_1	:
	{
		sType	: 'text',
		oDefinition	:
		{
			sLabel		: 'Address 1',
			mEditable	: true
		}
	},
	postal_address_line_2	:
	{
		sType	: 'text',
		oDefinition	:
		{
			sLabel		: 'Address 2',
			mEditable	: true
		}
	},
	postal_address_suburb	:
	{
		sType	: 'text',
		oDefinition	:
		{
			sLabel		: 'Suburb',
			mEditable	: true
		}
	},
	postal_address_state_id	:
	{
		sType	: 'select',
		oDefinition	:
		{
			sLabel		: 'State',
			fnPopulate	: State.getAllAsSelectOptions,
			mEditable	: true
		}
	},
	postal_address_postcode	:
	{
		sType	: 'text',
		oDefinition	:
		{
			sLabel		: 'Postcode',
			mEditable	: true
		}
	},
	contact_contact_title_id	:
	{
		sType	: 'select',
		oDefinition	:
		{
			sLabel		: 'Title',
			mEditable	: true,
			fnPopulate	: Contact_Title.getAllAsSelectOptions
		}
	},
	contact_first_name	:
	{
		sType	: 'text',
		oDefinition	:
		{
			sLabel		: 'First Name',
			mEditable	: true,
			mMandatory	: true
		}
	},
	contact_last_name	:
	{
		sType	: 'text',
		oDefinition	:
		{
			sLabel		: 'Last Name',
			mEditable	: true,
			mMandatory	: true
		}
	},
	contact_dob	:
	{
		sType	: 'date-picker',
		oDefinition	:
		{
			sLabel		: 'D.O.B',
			mEditable	: true,
			mMandatory	: true,
			iMinYear	: 1900,
			iMaxYear	: new Date().getFullYear(),
			fnValidate	: Popup_Account_Edit_Rebill.validatePastDate
		}
	},
	contact_drivers_license	:
	{
		sType	: 'text',
		oDefinition	:
		{
			sLabel		: 'Drivers License Number',
			mEditable	: true
		}

	},
	contact_position	:
	{
		sType	: 'text',
		oDefinition	:
		{
			sLabel		: 'Position',
			mEditable	: true,
			mMandatory	: true
		}
	},
	contact_landline_number	:
	{
		sType	: 'text',
		oDefinition	:
		{
			sLabel		: 'Landline',
			mEditable	: true,
			mMandatory	: true,
			fnValidate	: Reflex_Validation.fnnFixedLine
		}
	},
	reference1_label		: 'Trade Reference 1',
	reference1_company_name	:
	{
		sType	: 'text',
		oDefinition	:
		{
			sLabel		: 'Company Name',
			mEditable	: true,
			mMandatory	: true
		}
	},
	reference1_contact_person	:
	{
		sType	: 'text',
		oDefinition	:
		{
			sLabel		: 'Contact Person',
			mEditable	: true,
			mMandatory	: true
		}
	},
	reference1_phone_number	:
	{
		sType	: 'text',
		oDefinition	:
		{
			sLabel		: 'Landline',
			mEditable	: true,
			mMandatory	: true,
			fnValidate	: Reflex_Validation.fnnFixedLine
		}
	},
	reference2_label		: 'Trade Reference 2',
	reference2_company_name	:
	{
		sType	: 'text',
		oDefinition	:
		{
			sLabel		: 'Company Name',
			mEditable	: true,
			mMandatory	: true
		}
	},
	reference2_contact_person	:
	{
		sType	: 'text',
		oDefinition	:
		{
			sLabel		: 'Contact Person',
			mEditable	: true,
			mMandatory	: true
		}
	},
	reference2_phone_number	:
	{
		sType	: 'text',
		oDefinition	:
		{
			sLabel		: 'Landline',
			mEditable	: true,
			mMandatory	: true,
			fnValidate	: Reflex_Validation.fnnFixedLine
		}
	},
};

Popup_Account_Edit_Rebill.PANELS	=
{
	'Account'	:
	{
		aFields	:
		[
			'account_account_number',
			'account_account_name',
			'account_business_commencement_date',
			'account_motorpass_business_structure_id',
			'account_business_structure_description',
			'account_motorpass_promotion_code_id',
			'account_id'
		]
	},
	'Card'	:
	{
		aFields	:
		[
			'card_motorpass_card_type_id',
			'card_card_type_description',
			'card_card_expiry_date',
			'card_shared',
			'card_holder_label',
			'card_holder_contact_title_id',
			'card_holder_first_name',
			'card_holder_last_name',
			'card_vehicle_model',
			'card_vehicle_rego',
			'card_vehicle_make'
		]
	},
	'Address'	:
	{
		aFields	:
		[
		 	'street_address_label',
			'street_address_line_1',
			'street_address_line_2',
			'street_address_suburb',
			'street_address_state_id',
			'street_address_postcode',
			'postal_address_label',
			//'postal_address_same',
			'postal_address_line_1',
			'postal_address_line_2',
			'postal_address_suburb',
			'postal_address_state_id',
			'postal_address_postcode'
		]
	},
	'Contact'	:
	{
		aFields	:
		[
			'contact_contact_title_id',
			'contact_first_name',
			'contact_last_name',
			'contact_dob',
			'contact_drivers_license',
			'contact_position',
			'contact_landline_number'
		]
	},
	'Reference'	:
	{
		aFields	:
		[
		 	'reference1_label',
			'reference1_company_name',
			'reference1_contact_person',
			'reference1_phone_number',
			'reference2_label',
			'reference2_company_name',
			'reference2_contact_person',
			'reference2_phone_number'
		]
	}
}
