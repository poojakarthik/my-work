
var Popup_Charge_Type	= Class.create(Reflex_Popup,
{
	initialize	: function($super, fnOnClose)
	{
		$super(40);
		
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
													'Amount ($):'	
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
											)
										)
									)
								),
								$T.div ({class: 'charge-type-buttons'},
									$T.button(
										$T.img({src: Popup_Charge_Type.SAVE_IMAGE_SOURCE, alt: '', title: 'Save'}),
										$T.span('Save')
									),
									$T.button(
										$T.img({src: Popup_Charge_Type.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel'}),
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
		
		// Set the fixation checkbox event
		var oFixationCheckbox = oContent.select( 'tr.charge-type-checkbox input[type="checkbox"]' ).first();
		oFixationCheckbox.observe('click', this._updateFixationLabel.bind(this, oFixationCheckbox));
			
		// Setup validation handlers
		this.hInputs 			= {};
		var aInputs 			= oContent.select('input, select');
		aInputs[0].sFieldName 	= 'Charge Code';
		aInputs[0].bRequired	= true;
		aInputs[1].sFieldName 	= 'Description';
		aInputs[1].bRequired	= true;
		aInputs[2].sFieldName 	= 'Amount';
		aInputs[2].bRequired	= true;
		aInputs[3].sFieldName 	= 'Fixation';
		aInputs[3].bRequired	= true;
		aInputs[4].sFieldName 	= 'Nature';
		aInputs[4].bRequired	= true;
			
		for (var i = 0; i < aInputs.length; i++)
		{
			if (typeof aInputs[i].sFieldName !== 'undefined')
			{
				this.hInputs[aInputs[i].sFieldName] = aInputs[i];
			}
		}
		
		// Inputs
		this.hInputs['Charge Code'].validate 	= Popup_Charge_Type._validateInput.bind(this.hInputs['Charge Code'], 	Reflex_Validation.nonEmptyString);
		this.hInputs['Description'].validate 	= Popup_Charge_Type._validateInput.bind(this.hInputs['Description'], 	Reflex_Validation.nonEmptyString);
		this.hInputs['Amount'].validate 		= Popup_Charge_Type._validateInput.bind(this.hInputs['Amount'],			Reflex_Validation.float);
		
		// Selects
		this.hInputs['Nature'].validate 		= Popup_Charge_Type._validateInput.bind(this.hInputs['Nature'], 		Reflex_Validation.nonEmptyString);
		
		for (var sName in this.hInputs)
		{
			this.hInputs[sName].observe('keyup', this.hInputs[sName].validate);
			this.hInputs[sName].observe('change', this.hInputs[sName].validate);
		}
		
		this.oContent = oContent; 
		
		this.setTitle('Add Adjustment Type');
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
			Popup_Charge_Type._showValidationErrorPopup(aValidationErrors);
			return;
		}
		
		// Build request data
		var oRequestData =	{
							iId				: null,
							sChargeType		: this.hInputs['Charge Code'].value, 
							sDescription	: this.hInputs['Description'].value, 
							fAmount			: parseFloat(this.hInputs['Amount'].value), 
							sNature			: this.hInputs['Nature'].value, 
							bFixed			: this.hInputs['Fixation'].checked
						};
		
		var oSavePopup = new Reflex_Popup.Loading('Saving...');
		oSavePopup.display();
		this.oSavingOverlay = oSavePopup;
		
		// Make AJAX request
		this._saveChargeType = jQuery.json.jsonFunction(this._saveComplete.bind(this), this._saveError.bind(this), 'Charge_Type', 'save');
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
			if (oResponse.Message)
			{
				Reflex_Popup.alert(oResponse.Message);
			}
			
			// Hide saving overlay
			this.oSavingOverlay.hide();
			
			// Show validation errors
			if (oResponse.aValidationErrors)
			{
				Popup_Charge_Type._showValidationErrorPopup(oResponse.aValidationErrors);
			}
		}
	},
	
	_saveError	: function(oResponse)
	{
		if (oResponse.Message)
		{
			Reflex_Popup.alert(oResponse.Message, {sTitle: 'Error'});
		}
		else if (oResponse.ERROR)
		{
			Reflex_Popup.alert(oResponse.ERROR, {sTitle: 'Error'});
		}
	},
	
	_showCancelConfirmation	: function()
	{
		Reflex_Popup.yesNoCancel('Are you sure you want to cancel and revert all changes?', {sTitle: 'Revert Changes', fnOnYes: this.hide.bind(this)});
	},
	
	_updateFixationLabel	: function(oFixationCheckbox)
	{
		var oFixationOnSpan = this.oContent.select('tr.charge-type-checkbox td > span').first();
		var oFixationOffSpan = this.oContent.select('tr.charge-type-checkbox td > span').last();
		
		if (oFixationCheckbox.checked)
		{
			oFixationOnSpan.show();
			oFixationOffSpan.hide();
		}
		else
		{
			oFixationOffSpan.show();
			oFixationOnSpan.hide();
		}
	}
});

Popup_Charge_Type.CANCEL_IMAGE_SOURCE 	= '../admin/img/template/delete.png';
Popup_Charge_Type.SAVE_IMAGE_SOURCE 	= '../admin/img/template/tick.png';

Popup_Charge_Type._showValidationErrorPopup	= function(aErrors)
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

Popup_Charge_Type._validateInput	= function(fnValidate)
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
