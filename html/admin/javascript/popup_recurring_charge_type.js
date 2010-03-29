
var Popup_Recurring_Charge_Type	= Class.create(Reflex_Popup,
{
	initialize	: function($super, fnOnClose)
	{
		$super(55);
		
		this.fnOnClose = fnOnClose;
		this._buildUI();
	},
	
	_buildUI	: function()
	{
		// Build UI
		var oContent 	=	$T.div({class: 'charge-type'},
								$T.div({class: 'charge-type-table'},
									$T.table({class: 'reflex'},
										$T.caption(
											$T.div({class: 'caption_bar'},						
												$T.div({class: 'caption_title'},
													'Details'
												)
											)
										),
										$T.colgroup(
											$T.col(),
											$T.col()
										),
										$T.tbody(
											$T.tr(
												$T.th({class: 'label'},
													'Charge Code :'
												),
												$T.td(
													$T.input({type:'text'})
												)
											),
											$T.tr(
												$T.th({class: 'label'},
													'Description :'	
												),
												$T.td(
													$T.input({type:'text'})
												)
											),
											$T.tr(
												$T.th({class: 'label'},
													'Recursion Charge ($):'	
												),
												$T.td(
													$T.input({type:'text', value: '0.00'})
												)
											),
											$T.tr(
												$T.th({class: 'label'},
													'Nature :'
												),
												$T.td(
													$T.select(
														$T.option({value:'DR'},
															'Debit'
														),
														$T.option({value:'CR'},
															'Credit'
														)
													)
												)
											),
											$T.tr(
												$T.th({class: 'label'},
													'Recurring Frequency :'
												),
												$T.td (
													$T.input({type: 'text'}),
													$T.select(
														$T.option({value: Popup_Recurring_Charge_Type.BILLING_FREQ_MONTH},
															'Months'
														)
													)
												)
											),
											$T.tr(
												$T.th({class: 'label'},
													'Minimum Charge ($):'	
												),
												$T.td(
													$T.input({type:'text', value: '0.00'})
												)
											),
											$T.tr(
												$T.th({class: 'label'},
													'Cancellation Fee ($):'	
												),
												$T.td(
													$T.input({type:'text', value: '0.00'})
												)
											),
											$T.tr({class: 'charge-type-checkbox'},
												$T.th({class: 'label'},
													'Continuation :'
												),
												$T.td(
													$T.input({type: 'checkbox'}),
													$T.span({style: 'display: none'},
														'Will keep charging when the minimum charge is reached.'
													),
													$T.span('Will stop charging when the minimum charge is reached.')
												)
											),
											/*$T.tr({class: 'charge-type-checkbox'},
												$T.th({class: 'label'},
													'Unique Charge :'
												),
												$T.td(
													$T.input({type: 'checkbox'}),
													$T.span({style: 'display: none'},
														'This is a unique adjustment.'
													),
													$T.span(
														$T.span('This is '),
														$T.span({style: 'font-weight: bold;'},
															'not'
														),
														' a unique adjustment.'
													)
												)
											),*/
											$T.tr({class: 'charge-type-checkbox'},
												$T.th({class: 'label'},
													'Fixation :'
												),
												$T.td(
													$T.input({type: 'checkbox'}),
													$T.span({style: 'display: none'},
														'Can ',
														$T.span({style: 'font-weight: bold;'},
															'not'
														),
														' be changed at application time.'
													),
													$T.span('Can be changed at application time.')
												)
											),
											$T.tr(
												$T.th({class: 'label'},
													'Approval Process :'
												),
												$T.td(
													$T.select(
														$T.option({value: Popup_Recurring_Charge_Type.NO_APPROVAL_REQUIRED},
															'Requests for the recurring adjustment are automatically approved'
														),
														$T.option({value: Popup_Recurring_Charge_Type.APPROVAL_REQUIRED},
															'Requests for the recurring adjustment have to go through the approval process'
														)
													)
												)
											)
										)
									)
								),
								$T.div ({class: 'charge-type-buttons'},
									$T.button(
										$T.img({src: Popup_Recurring_Charge_Type.SAVE_IMAGE_SOURCE, alt: '', title: 'Save'}),
										$T.span('Save')
									),
									$T.button(
										$T.img({src: Popup_Recurring_Charge_Type.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel'}),
										$T.span('Cancel')
									)
								)
							);
		
		// Set the save buttons event handler
		var oSaveButton	= oContent.select( 'button' ).first();
		oSaveButton.observe('click', this._saveChanges.bind(this));
		
		// Set the cancel buttons event handler
		var oCancelButton = oContent.select( 'button' ).last();
		oCancelButton.observe('click', this._showCancelConfirmation.bind(this));
		
		// Set the checkbox label update event handlers
		var aCheckboxTRs 	= oContent.select( 'tr.charge-type-checkbox' );
		var checkbox 		= null;
		
		for (var i = 0; i < aCheckboxTRs.length; i++)
		{
			var checkbox = aCheckboxTRs[i].select( 'input[type="checkbox"]' ).first();
			checkbox.observe('click', this._updateCheckboxLabel.bind(this, aCheckboxTRs[i]));
		}
		
		// Setup validation handlers
		this.hInputs 			= {};		
		var aInputs 			= oContent.select('input, select');
		aInputs[0].sFieldName 	= 'Charge Code';
		aInputs[0].bRequired	= true;
		aInputs[1].sFieldName 	= 'Description';
		aInputs[1].bRequired	= true;
		aInputs[2].sFieldName 	= 'Recursion Charge';
		aInputs[2].bRequired	= true;
		aInputs[3].sFieldName 	= 'Recurring Frequency';
		aInputs[3].bRequired	= true;
		aInputs[4].sFieldName 	= 'Minimum Charge';
		aInputs[4].bRequired	= true;
		aInputs[5].sFieldName 	= 'Cancellation Fee';
		aInputs[5].bRequired	= true;
		aInputs[6].sFieldName 	= 'Continuation';
		aInputs[6].bRequired	= true;
		aInputs[7].sFieldName 	= 'Fixation';
		aInputs[7].bRequired	= true;
		aInputs[8].sFieldName 	= 'Nature';
		aInputs[8].bRequired	= true;
		aInputs[9].sFieldName 	= 'Recurring Frequency Type';
		aInputs[9].bRequired	= true;
		aInputs[10].sFieldName 	= 'Approval Process';
		aInputs[10].bRequired	= true;
		
		for (var i = 0; i < aInputs.length; i++)
		{
			if (typeof aInputs[i].sFieldName !== 'undefined')
			{
				this.hInputs[aInputs[i].sFieldName] = aInputs[i];
			}
		}
		
		// Inputs
		this.hInputs['Charge Code'].validate 				= Popup_Recurring_Charge_Type._validateInput.bind(this.hInputs['Charge Code'], 				Reflex_Validation.nonEmptyString);
		this.hInputs['Description'].validate 				= Popup_Recurring_Charge_Type._validateInput.bind(this.hInputs['Description'],			 	Reflex_Validation.nonEmptyString);
		this.hInputs['Recursion Charge'].validate 			= Popup_Recurring_Charge_Type._validateInput.bind(this.hInputs['Recursion Charge'], 		Reflex_Validation.float);
		this.hInputs['Recurring Frequency'].validate 		= Popup_Recurring_Charge_Type._validateInput.bind(this.hInputs['Recurring Frequency'],		Reflex_Validation.nonEmptyDigits);
		this.hInputs['Minimum Charge'].validate 			= Popup_Recurring_Charge_Type._validateInput.bind(this.hInputs['Minimum Charge'], 			Reflex_Validation.float);
		this.hInputs['Cancellation Fee'].validate 			= Popup_Recurring_Charge_Type._validateInput.bind(this.hInputs['Cancellation Fee'], 		Reflex_Validation.float);
		
		// Selects
		this.hInputs['Nature'].validate 					= Popup_Recurring_Charge_Type._validateInput.bind(this.hInputs['Nature'], 					Reflex_Validation.nonEmptyString);
		this.hInputs['Recurring Frequency Type'].validate	= Popup_Recurring_Charge_Type._validateInput.bind(this.hInputs['Recurring Frequency Type'],	Reflex_Validation.digits);
		this.hInputs['Approval Process'].validate 			= Popup_Recurring_Charge_Type._validateInput.bind(this.hInputs['Approval Process'], 		Reflex_Validation.digits);
		
		for (var sName in this.hInputs)
		{
			this.hInputs[sName].observe('keyup', this.hInputs[sName].validate);
			this.hInputs[sName].observe('change', this.hInputs[sName].validate);
		}
		
		this.oContent = oContent; 
		
		this.setTitle('Add Recurring Adjustment Type');
		this.setIcon('../admin/img/template/charge_small.png');
		this.setContent(oContent);
		this.display();
		
		// Run initial validation to show required fields
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
	
	_saveChanges	: function()
	{
		// Validate the data
		var aValidationErrors = this._isValid();
		
		if (aValidationErrors.length)
		{
			Popup_Recurring_Charge_Type._showValidationErrorPopup(aValidationErrors);
			return;
		}
		
		// Build request data
		var oRequestData = 	{
							iId					: null,
							sChargeType			: this.hInputs['Charge Code'].value, 
							sDescription		: this.hInputs['Description'].value, 
							fRecursionCharge	: parseFloat(this.hInputs['Recursion Charge'].value),
							iRecurringFreqType	: parseInt(this.hInputs['Recurring Frequency Type'].value),
							iRecurringFreq		: parseInt(this.hInputs['Recurring Frequency'].value),
							fMinCharge			: parseFloat(this.hInputs['Minimum Charge'].value),
							fCancellationFee	: parseFloat(this.hInputs['Cancellation Fee'].value),
							sNature				: this.hInputs['Nature'].value, 
							bContinuable		: this.hInputs['Continuation'].checked,
							//bUniqueCharge		: this.hInputs['Unique Charge'].checked,
							bFixed				: this.hInputs['Fixation'].checked,
							iApprovalRequired	: parseInt(this.hInputs['Approval Process'].value)
						};
		
		var oSavePopup = new Reflex_Popup.Loading('Saving...');
		oSavePopup.display();
		this.oSavingOverlay = oSavePopup;
		
		// Make AJAX request
		this._saveChargeType = jQuery.json.jsonFunction(this._saveComplete.bind(this), this._saveError.bind(this), 'Recurring_Charge_Type', 'save');
		this._saveChargeType(oRequestData);
	},
	
	_saveComplete	: function(oResponse)
	{
		if (oResponse.Success)
		{
			// On close callback
			if (this.fnOnClose)
			{
				this.fnOnClose();
			}
			
			// Hide saving overlay
			this.oSavingOverlay.hide();
			
			// Hide this
			this.hide();
			
			// Confirmation
			Reflex_Popup.alert('Adjustment Type \'' + oResponse.sChargeType + '\' succesfully added', {sTitle: 'Save Successful'});
		}
		else
		{
			// Hide saving overlay
			this.oSavingOverlay.hide();
			
			// Show validation errors
			Popup_Recurring_Charge_Type._showValidationErrorPopup(oResponse.aValidationErrors);
		}
	},
	
	_saveError	: function(oResponse)
	{
		Reflex_Popup.alert('There was an error saving the Adjustment Type' + (oResponse.ErrorMessage ? ' (' + oResponse.ErrorMessage + ')' : ''), {sTitle: 'Save Error'});
	},
	
	_showCancelConfirmation	: function()
	{
		Reflex_Popup.yesNoCancel('Are you sure you want to cancel and revert all changes?', {sTitle: 'Revert Changes', fnOnYes: this.hide.bind(this)});
	},
	
	_updateCheckboxLabel	: function(oTR)
	{
		var oOnSpan 	= oTR.select('td > span').first();
		var oOffSpan 	= oTR.select('td > span').last();
		var oCheckbox 	= oTR.select('input[type="checkbox"]').first();
		
		if (oCheckbox.checked)
		{
			oOnSpan.show();
			oOffSpan.hide();
		}
		else
		{
			oOffSpan.show();
			oOnSpan.hide();
		}
	}
});

Popup_Recurring_Charge_Type.CANCEL_IMAGE_SOURCE 	= '../admin/img/template/delete.png';
Popup_Recurring_Charge_Type.SAVE_IMAGE_SOURCE 		= '../admin/img/template/tick.png';

Popup_Recurring_Charge_Type.BILLING_FREQ_DAY		= 1;
Popup_Recurring_Charge_Type.BILLING_FREQ_MONTH		= 2;
Popup_Recurring_Charge_Type.BILLING_FREQ_HALF_MONTH	= 3;

Popup_Recurring_Charge_Type.APPROVAL_REQUIRED		= 1;
Popup_Recurring_Charge_Type.NO_APPROVAL_REQUIRED	= 0;

Popup_Recurring_Charge_Type._showValidationErrorPopup	= function(aErrors)
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
}

Popup_Recurring_Charge_Type._validateInput	= function(fnValidate)
{
	// This is to be bound to the scope of an input
	try
	{
		this.removeClassName('valid');
		this.removeClassName('invalid');
		
		// Check required validation first
		if (this.bRequired && (this.value == '' || this.value === null))
		{
			throw('Required field');
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
