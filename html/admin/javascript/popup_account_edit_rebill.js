
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
		this.hControlFields	= {};
		this.oLoading		= new Reflex_Popup.Loading('Please Wait');
		this.oLoading.display();
		this._buildUI();
	},
	
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
									$T.table({class: 'reflex input'},
										$T.caption(
											$T.div({class: 'caption_bar', id: 'caption_bar'},
												$T.div({class: 'caption_title', id: 'caption_title'},
													'Details'
												)
											)
										),
										$T.tbody(
											// Rows will vary depending on rebill_type_id
										)
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
			
			// Generate control fields
			var oTBody		= this._oContent.select('table.input > tbody').first();
			var hFields		= Popup_Account_Edit_Rebill.FIELDS[this.iRebillTypeId];
			var oField		= null;
			var oControl	= null;
			
			for (var sFieldName in hFields)
			{
				oField		= hFields[sFieldName];
				oControl	= Control_Field.factory(oField.sType, oField.oDefinition);
				
				if (this.oRebill && (typeof this.oRebill[sFieldName] != 'undefined'))
				{
					oControl.setValue(this.oRebill[sFieldName]);
				}
				else
				{
					oControl.setValue('');
				}
				
				oControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				oTBody.appendChild(oControl.generateInputTableRow().oElement);
				
				this.hControlFields[sFieldName]	= oControl;
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
			
			for (var sFieldName in this.hControlFields)
			{
				try
				{
					// If valid, record the value
					if (this.hControlFields[sFieldName].validate(false))
					{
						oDetails[sFieldName]	= this.hControlFields[sFieldName].getValue(true);
					}
				}
				catch (ex)
				{
					aValidationErrors.push(ex);
				}
			}
			
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
	}
});

Popup_Account_Edit_Rebill._formatDate	= function(sDate)
{
	return Reflex_Date_Format.format('j/n/Y', Date.parse(sDate.replace(/-/g, '/')) / 1000);
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

Popup_Account_Edit_Rebill.validateAccountNumber	= function(iMaxLength, sValue)
{
	if (Reflex_Validation.digits(sValue))
	{
		if (sValue.length > iMaxLength)
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

//Check if $CONSTANT has correct constant groups loaded, if not this class won't work
if (typeof Flex.Constant.arrConstantGroups.rebill_type == 'undefined' || 
	typeof Flex.Constant.arrConstantGroups.payment_method == 'undefined')
{
	Popup_Account_Edit_Rebill.HAS_CONSTANTS	= false;
	throw ('Please load the "payment_method" & "rebill_type" constant groups before using Popup_Account_Edit_Rebill');
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
Popup_Account_Edit_Rebill.FIELDS									= {};

// Motorpass
Popup_Account_Edit_Rebill.FIELDS[$CONSTANT.REBILL_TYPE_MOTORPASS]	= {};

Popup_Account_Edit_Rebill.FIELDS[$CONSTANT.REBILL_TYPE_MOTORPASS].account_number		= {};
Popup_Account_Edit_Rebill.FIELDS[$CONSTANT.REBILL_TYPE_MOTORPASS].account_number.sType	= 'text';

Popup_Account_Edit_Rebill.FIELDS[$CONSTANT.REBILL_TYPE_MOTORPASS].account_number.oDefinition			= {};
Popup_Account_Edit_Rebill.FIELDS[$CONSTANT.REBILL_TYPE_MOTORPASS].account_number.oDefinition.sLabel		= 'Account Number';
Popup_Account_Edit_Rebill.FIELDS[$CONSTANT.REBILL_TYPE_MOTORPASS].account_number.oDefinition.mEditable	= true;
Popup_Account_Edit_Rebill.FIELDS[$CONSTANT.REBILL_TYPE_MOTORPASS].account_number.oDefinition.mMandatory	= true;
//Popup_Account_Edit_Rebill.FIELDS[$CONSTANT.REBILL_TYPE_MOTORPASS].account_number.oDefinition.iMaxLength	= 9;
Popup_Account_Edit_Rebill.FIELDS[$CONSTANT.REBILL_TYPE_MOTORPASS].account_number.oDefinition.fnValidate	= Popup_Account_Edit_Rebill.validateAccountNumber.curry(9) 



