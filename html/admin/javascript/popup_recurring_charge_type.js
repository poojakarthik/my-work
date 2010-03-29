
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
											$T.tr({class: 'charge-type-checkbox'},
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
		
		this.oContent = oContent; 
		
		this.setTitle('Add Recurring Adjustment Type');
		this.setIcon('../admin/img/template/charge_small.png');
		this.setContent(oContent);
		this.display();
	},
	
	_saveChanges	: function()
	{
		// Get the data
		var aTR 					= this.oContent.select('div.charge-type-table > table.reflex > tbody > tr');
		var sCode 					= aTR[0].select('input').first().value;
		var sDescription 			= aTR[1].select('input').first().value;
		var sRecursionCharge		= aTR[2].select('input').first().value;
		var sNature					= aTR[3].select('select').first().value;
		var sRecurringFrequency		= aTR[4].select('input').first().value;
		var sRecurringFrequencyType	= aTR[4].select('select').first().value;
		var sMinimumCharge			= aTR[5].select('input').first().value;
		var sCancellationFee		= aTR[6].select('input').first().value;
		var bContinuation			= aTR[7].select('input').first().checked;
		var bUniqueCharge			= aTR[8].select('input').first().checked;
		var bFixation				= aTR[9].select('input').first().checked;
		var sApproval				= aTR[10].select('select').first().value;
		
		// Validate the data
		var aValidationErrors = $T.ul(); 
		
		if (!sCode || sCode == '')
		{
			aValidationErrors.appendChild($T.li('Please supply a Charge Code'));
		}
		
		if (!sDescription || sDescription == '')
		{
			aValidationErrors.appendChild($T.li('Please supply a Description'));
		}
		
		if (!sRecursionCharge || sRecursionCharge == '' || isNaN(sRecursionCharge))
		{
			aValidationErrors.appendChild($T.li('Please supply a Recursion Charge in dollars'));
		}
		
		if (!sRecurringFrequency || sRecurringFrequency == '' || isNaN(sRecurringFrequency))
		{
			aValidationErrors.appendChild($T.li('Please supply a Recursion Frequency (number)'));
		}
		
		if (sMinimumCharge != '' && isNaN(sMinimumCharge))
		{
			aValidationErrors.appendChild($T.li('Please supply a Minimum Charge in dollars'));
		}
		
		if (sCancellationFee != '' && isNaN(sCancellationFee))
		{
			aValidationErrors.appendChild($T.li('Please supply a Cancellation Fee in dollars'));
		}
		
		if (aValidationErrors.childNodes.length)
		{
			Reflex_Popup.alert(
							$T.div({style: 'margin: 0.5em'},
								'The following errors have occured: ',
								aValidationErrors
							),
							{
								iWidth	: 25,
								sTitle	: 'Validation Errors'
							}
						);
			return;
		}
		
		// Build request data
		var oRequestData = 	{
							iId					: null,
							sChargeType			: sCode, 
							sDescription		: sDescription, 
							fRecursionCharge	: parseFloat(sRecursionCharge),
							iRecurringFreqType	: parseInt(sRecurringFrequencyType),
							iRecurringFreq		: parseInt(sRecurringFrequency),
							fMinCharge			: parseFloat(sMinimumCharge),
							fCancellationFee	: parseFloat(sCancellationFee),
							sNature				: sNature, 
							bContinuable		: bContinuation,
							bUniqueCharge		: bUniqueCharge,
							bFixed				: bFixation,
							iApprovalRequired	: parseInt(sApproval)
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