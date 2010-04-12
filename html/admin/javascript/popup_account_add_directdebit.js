
var Popup_Account_Add_DirectDebit	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iAccountId, fnOnSave, fnOnCancel)
	{
		$super(35);
		
		this.hInputs	= {};
		this.iAccountId	= iAccountId;
		this.fnOnSave	= fnOnSave;
		this.fnOnCancel	= fnOnCancel;
		this._buildUI();
	},
	
	_buildUI	: function()
	{
		var oContent	=	$T.div({class: 'popup-add-direct-debit'},
								$T.table({class: 'reflex'},
									$T.caption(
										$T.div({class: 'caption_bar', id: 'caption_bar'},
											$T.div({class: 'caption_title', id: 'caption_title'},
												'Details'
											)
										)
									),
									$T.tbody(
										$T.tr(
											$T.th({class: 'label'},
												'Bank Name :'
											),
											$T.td(
												$T.input({type: 'text', bRequired: true, sValidationFunction: 'nonEmptyString'})
											)
										),
										$T.tr(
											$T.th({class: 'label'},
												'BSB # :'
											),
											$T.td(
												$T.input({type: 'text', bRequired: true, sValidationFunction: 'bsb'})
											)
										),
										$T.tr(
											$T.th({class: 'label'},
													'Account # :'
											),
											$T.td(
												$T.input({type: 'text', bRequired: true, sValidationFunction: 'bankAccountNumber'})
											)
										),
										$T.tr(
											$T.th({class: 'label'},
												'Account Name :'
											),
											$T.td(
												$T.input({type: 'text', bRequired: true, sValidationFunction: 'nonEmptyString'})
											)
										)
									)
								),
								$T.div({class: 'buttons'},
									$T.button({class: 'icon-button'},
										$T.img({src: Popup_Account_Add_DirectDebit.SAVE_IMAGE_SOURCE, alt: '', title: 'Save'}),
										$T.span('Save')
									),
									$T.button({class: 'icon-button'},
										$T.img({src: Popup_Account_Add_DirectDebit.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel'}),
										$T.span('Cancel')
									)
								)
							);
		
		// Set save & cancel event handlers
		var oSaveButton		= oContent.select('button.icon-button').first();
		oSaveButton.observe('click', this._save.bind(this));
		
		var oCancelButton	= oContent.select('button.icon-button').last();
		oCancelButton.observe('click', this._cancel.bind(this));	
		
		// Setup input validate event handlers (selects first, then inputs)
		var aInputs		= oContent.select('select, input');
		var oInput		= null;
		
		for (var i = 0; i < aInputs.length; i++)
		{
			oInput				= aInputs[i];
			oInput.bRequired	= (oInput.getAttribute('bRequired') == 'true' ? true : false);
			
			if (oInput.getAttribute('sValidationFunction'))
			{
				oInput.validate	=	Popup_Account_Add_DirectDebit._validateInput.bind(
										oInput, 		
										Reflex_Validation.Exception[oInput.getAttribute('sValidationFunction')]
									);
			}
			
			oInput.sFieldName	= 	oInput.parentNode.parentNode.select('th').first().innerHTML.replace(/^(.*)\s:$/, '$1')
									+ (oInput.getAttribute('sFieldNameExtra') ? oInput.getAttribute('sFieldNameExtra') : '');
			this.hInputs[oInput.sFieldName]	= oInput;
		}
		
		for (var sName in this.hInputs)
		{
			if (typeof this.hInputs[sName].validate !== 'undefined')
			{
				this.hInputs[sName].observe('keyup', this.hInputs[sName].validate);
				this.hInputs[sName].observe('change', this.hInputs[sName].validate);
			}
		}
		
		// Display Popup
		this.setTitle("Add Bank Account Details");
		this.addCloseButton();
		this.setIcon("../admin/img/template/payment.png");
		this.setContent(oContent);
		this.display();
		
		this.oContent	= oContent;
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
		
		return aErrors;
	},
	
	_save	: function()
	{
		var aErrors = this._isValid();
		
		if (aErrors.length)
		{
			Popup_Account_Add_DirectDebit._showValidationErrorPopup(aErrors);
			return;
		}
		
		// Build request data
		var oDetails	=	{
								sBankName		: this.hInputs['Bank Name'].value,
								sBSB			: this.hInputs['BSB #'].value.replace(/-/, ''),
								sAccountNumber	: this.hInputs['Account #'].value,
								sAccountName	: this.hInputs['Account Name'].value
							};
		
		// Create a Popup to show 'saving...' close it when save complete
		this.oLoading = new Reflex_Popup.Loading('Saving...');
		this.oLoading.display();
		
		this._addDirectDebit	= jQuery.json.jsonFunction(this._saveResponse.bind(this), this._ajaxError.bind(this), 'Account', 'addDirectDebit');
		this._addDirectDebit(this.iAccountId, oDetails);
	},
	
	_saveResponse	: function(oResponse)
	{
		this.oLoading.hide();
		delete this.oLoading;
		
		if (oResponse.Success)
		{
			this.hide();
			
			if (this.fnOnSave)
			{
				this.fnOnSave(oResponse.oDirectDebit);
			}
		}
		else if (oResponse.aValidationErrors)
		{
			// Validation errors
			Popup_Account_Add_DirectDebit._showValidationErrorPopup(oResponse.aValidationErrors);
		}
		else
		{
			this._ajaxError(oResponse);
		}
	},
	
	_ajaxError	: function(oResponse, bHideOnClose)
	{
		if (this.oLoading)
		{
			this.oLoading.hide();
			delete this.oLoading;
		}
		
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
	},
	
	_cancel	: function()
	{
		if (typeof this.fnOnCancel !== 'undefined')
		{
			this.fnOnCancel();
		}
		
		this.hide();
	}
});

// Image paths
Popup_Account_Add_DirectDebit.CANCEL_IMAGE_SOURCE 	= '../admin/img/template/delete.png';
Popup_Account_Add_DirectDebit.SAVE_IMAGE_SOURCE 	= '../admin/img/template/tick.png';

Popup_Account_Add_DirectDebit._showValidationErrorPopup	= function(aErrors)
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

Popup_Account_Add_DirectDebit._validateInput	= function(fnValidate)
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


