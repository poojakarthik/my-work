
var Popup_Recurring_Charge_Type	= Class.create(Reflex_Popup,
{
	initialize	: function($super, fnOnClose)
	{
		$super(55);
		
		this.fnOnClose					= fnOnClose;
		this.hControls					= {};
		this._bCancellationFeeVisible	= false;
		this._buildUI();
	},
	
	_buildUI	: function()
	{
		// Build UI
		this.oContent 	=	$T.div({class: 'recurring-charge-type'},
								$T.div({class: 'section'},
									$T.div({class: 'section-header'},
										$T.div({class: 'section-header-title'},
											'Details'
										)
									),
									$T.div({class: 'section-content section-content-fitted'},
										$T.table({class: 'input recurring-charge-type-properties'},
											$T.colgroup(
												$T.col({style: 'width: 23%'}),
												$T.col({style: 'width: 77%'})
											),
											$T.tbody(
												$T.tr(
													$T.th({class: 'label'},
														'Charge Code :'
													),
													$T.td(
														$T.div({class: 'recurring-charge-type-charge-code'}
															// Control added later
														)	
													)
												),
												$T.tr(
													$T.th({class: 'label'},
														'Description :'	
													),
													$T.td({class: 'recurring-charge-type-description'}
														// Control added later
													)
												),
												$T.tr(
													$T.th({class: 'label'},
														'Approval Process :'
													),
													$T.td({class: 'recurring-charge-type-approval-process'}
														// Control added later
													)
												)
											)
										)
									)
								),
								$T.div({class: 'section'},
									$T.div({class: 'section-header'},
										$T.div({class: 'section-header-title'},
											'Charge'
										)
									),
									$T.div({class: 'section-content section-content-fitted'},
										$T.table({class: 'input input-no-fixed-width'},
											$T.colgroup(
												$T.col({style: 'width: 23%'}),
												$T.col({style: 'width: 77%'})
											),
											$T.tbody(
												$T.tr(
													$T.th({class: 'label'},
														'Definition :'
													),
													$T.td(
														$T.table({class: 'recurring-charge-type-definition'},
															$T.tbody(
																$T.tr(
																	$T.td({class: 'recurring-charge-type-definition-symbol'},
																		'$'
																	),
																	$T.td({class: 'recurring-charge-type-definition-charge'}
																		// Control added later
																	),
																	$T.td({class: 'recurring-charge-type-definition-naturepertime'},
																		$T.ul({class: 'reset horizontal'},
																			$T.li({class: 'recurring-charge-type-definition-nature'}
																				// Control added later
																			),
																			$T.li('per'),
																			$T.li({class: 'recurring-charge-type-definition-period'}
																				// Control added later
																			),
																			$T.li({class: 'recurring-charge-type-definition-period-type'}
																				// Control added later
																			)
																		)
																	)
																),
																$T.tr({class: 'recurring-charge-type-definition-over-row'},
																	$T.td({class: 'recurring-charge-type-definition-symbol'},
																		'over'
																	),
																	$T.td({class: 'recurring-charge-type-definition-time'}
																		// Control added later
																	),
																	$T.td({class: 'recurring-charge-type-definition-time-label'}
																		// Control added later
																	)
																),
																$T.tr({class: 'recurring-charge-type-definition-total-row'},
																	$T.td({class: 'recurring-charge-type-definition-symbol'},
																		'$'
																	),
																	$T.td({class: 'recurring-charge-type-definition-total'}
																		// Control added later
																	),
																	$T.td({class: 'recurring-charge-type-definition-continuing'},
																		$T.div({class: 'recurring-charge-type-definition-continuing'}
																			// Control added later
																		)
																	)
																)
															)
														),
														$T.div({class: 'recurring-charge-type-definition-result'})
													)
												),
												$T.tr(
													$T.th({class: 'label'},
														'Fixed :'
													),
													$T.td({class: 'recurring-charge-type-fixed'}
														// Control added later
													)
												),
												$T.tr(
													$T.th({class: 'label'},
														'Cancellation Fee :'
													),
													$T.td({class: 'recurring-charge-type-cancellation-fee'},
														$T.ul({class: 'reset horizontal'},
															$T.li({class: 'recurring-charge-type-cancellation-fee-checkbox'},
																$T.input({type: 'checkbox'})
															),
															$T.li({class: 'recurring-charge-type-cancellation-fee'}
																// Control added later	
															)
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
		var oSaveButton		= this.oContent.select( 'button' ).first();
		oSaveButton.observe('click', this._saveChanges.bind(this));
		
		// Set the cancel buttons event handler
		var oCancelButton	= this.oContent.select( 'button' ).last();
		oCancelButton.observe('click', this._showCancelConfirmation.bind(this));
		
		// Set the click event for the cancellation fee checkbox
		var oCheckbox		= this.oContent.select('li.recurring-charge-type-cancellation-fee-checkbox > input[type="checkbox"]').first();
		oCheckbox.observe('click', this._showCancellationFeeInput.bind(this));
		
		this._attachControls();
		this._calculateTotal();
		this._showCancellationFeeInput();
		
		this.setTitle('Add Recurring Adjustment Type');
		this.setIcon('../admin/img/template/charge_small.png');
		this.setContent(this.oContent);
		this.display();
	},
	
	_attachControls	: function()
	{
		var oField		= null;
		var oControl	= null;
		var sSelector	= null;
		
		for (var sFieldName in Popup_Recurring_Charge_Type.FIELDS)
		{
			oField							= Popup_Recurring_Charge_Type.FIELDS[sFieldName];
			oField.oDefinition.mEditable	= true;
			oControl						= Control_Field.factory(oField.sType, oField.oDefinition);
			
			if (typeof oField.mDefault != 'undefined')
			{
				oControl.setValue(oField.mDefault);
			}
			
			oControl.addOnChangeCallback(this._calculateTotal.bind(this, sFieldName));
			oControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
			sSelector						= oField.sElement + '.recurring-charge-type-' + sFieldName;
			this.oContent.select(sSelector).first().appendChild(oControl.getElement());
			this.hControls[sFieldName]		= oControl;
		}
	},
	
	_saveChanges	: function()
	{
		// Validate fields
		var aValidationErrors	= [];
		var oDetails			= {};
		
		for (var sFieldName in this.hControls)
		{
			try
			{
				// If valid, record the value
				this.hControls[sFieldName].validate(false);
			}
			catch (ex)
			{
				aValidationErrors.push(ex);
			}
		}
		
		if (aValidationErrors.length)
		{
			Popup_Recurring_Charge_Type.showValidationErrors(aValidationErrors);
			return;
		}
		
		// Build request data
		var oRequestData =	{
								iId					: null,
								sChargeType			: this.hControls['charge-code'].getValue(true), 
								sDescription		: this.hControls['description'].getValue(true), 
								fRecursionCharge	: parseFloat(this.hControls['definition-charge'].getValue(true)),
								iRecurringFreqType	: parseInt(this.hControls['definition-period-type'].getValue(true)),
								iRecurringFreq		: parseInt(this.hControls['definition-period'].getValue(true)),
								fMinCharge			: parseFloat(this.hControls['definition-total'].getValue(true)),
								fCancellationFee	: (this._bCancellationFeeVisible ? parseFloat(this.hControls['cancellation-fee'].getValue(true)) : 0),
								sNature				: this.hControls['definition-nature'].getValue(true), 
								bContinuable		: parseInt(this.hControls['definition-continuing'].getValue(true)),
								bFixed				: parseInt(this.hControls['fixed'].getValue(true)),
								iApprovalRequired	: parseInt(this.hControls['approval-process'].getValue(true))
							};
		
		this.oLoading = new Reflex_Popup.Loading('Saving...');
		this.oLoading.display();
		
		// Make AJAX request
		this._saveChargeType = 	jQuery.json.jsonFunction(
									this._saveComplete.bind(this), 
									this._saveError.bind(this), 
									'Recurring_Charge_Type', 
									'save'
								);
		this._saveChargeType(oRequestData);
	},
	
	_saveComplete	: function(oResponse)
	{
		// Hide loading
		this.oLoading.hide();
		delete this.oLoading;
		
		if (oResponse.Success)
		{
			// On close callback
			if (this.fnOnClose)
			{
				this.fnOnClose();
			}
			
			// Hide this
			this.hide();
			
			// Confirmation
			Reflex_Popup.alert('Adjustment Type \'' + oResponse.sChargeType + '\' succesfully added', {sTitle: 'Save Successful'});
		}
		else
		{
			this._saveError(oResponse);
		}
	},
	
	_saveError	: function(oResponse)
	{
		// Hide loading
		if (this.oLoading)
		{
			this.oLoading.hide();
			delete this.oLoading;
		}
		
		if (oResponse.Message)
		{
			Reflex_Popup.alert(oResponse.Message, {sTitle: 'Error'});
		}
		else if (oResponse.ERROR)
		{
			Reflex_Popup.alert(oResponse.ERROR, {sTitle: 'Error'});
		}
		else if (oResponse.aValidationErrors)
		{
			Popup_Recurring_Charge_Type.showValidationErrors(oResponse.aValidationErrors);
		}
	},
	
	_showCancelConfirmation	: function()
	{
		Reflex_Popup.yesNoCancel('Are you sure you want to cancel and revert all changes?', {sTitle: 'Revert Changes', fnOnYes: this.hide.bind(this)});
	},
	
	_calculateTotal	: function(sEditedFieldName)
	{
		// Get the values
		var fCharge			= parseFloat(this.hControls['definition-charge'].getElementValue()).toFixed(2);
		var sChargeNature	= this.hControls['definition-nature'].getElementValue();
		var iChargePeriod	= parseInt(this.hControls['definition-period'].getElementValue());
		var iPeriodType		= parseInt(this.hControls['definition-period-type'].getElementValue());
		var iPeriodsOver	= parseInt(this.hControls['definition-time'].getElementValue());
		var fTotal			= parseFloat(this.hControls['definition-total'].getElementValue()).toFixed(2);
		var iContinuing		= parseInt(this.hControls['definition-continuing'].getElementValue());
		
		if (isNaN(fCharge))
		{
			fCharge	= 0;
			this.hControls['definition-charge'].setValue(fCharge);
		}
		
		if (isNaN(fTotal))
		{
			fTotal	= 0;
			this.hControls['definition-total'].setValue(fTotal);
		}
		
		if (isNaN(iPeriodsOver))
		{
			iPeriodsOver	= 1;
		}
		
		// Don't allow 0 for charge period
		if (isNaN(iChargePeriod) || (iChargePeriod == 0))
		{
			iChargePeriod	= 1;
		}
		
		this.hControls['definition-period'].setValue(iChargePeriod);
		this.hControls['definition-time'].setValue(iPeriodsOver);
		
		var iPeriodCount	= iPeriodsOver / iChargePeriod;
		
		switch (sEditedFieldName)
		{
			case 'definition-total':
				if (fTotal)
				{
					// Update charge amount
					fCharge	= (fTotal / iPeriodCount).toFixed(2);
					
					if (isNaN(fCharge))
					{
						fCharge	= 0;
					}
					
					this.hControls['definition-charge'].setValue(fCharge);
					break;
				}
			default:
				// Update total
				fTotal	= (iPeriodCount * fCharge).toFixed(2);
				
				if (isNaN(fTotal))
				{
					fTotal	= 0;
				}
				
				this.hControls['definition-total'].setValue(fTotal);
		}
		
		// Update the time period label
		var sTimeLabel	= '';
		var sTimeSuffix	= (iPeriodsOver != 1 ? 's' : '');
		switch (iPeriodType)
		{
			case Popup_Recurring_Charge_Type.BILLING_FREQ_DAY:
				sTimeLabel	= 'Day';
				break;
			case Popup_Recurring_Charge_Type.BILLING_FREQ_MONTH:
				sTimeLabel	= 'Month';
				break;
			case Popup_Recurring_Charge_Type.BILLING_FREQ_HALF_MONTH:
				sTimeLabel	= 'Half Month';
				break;
		}
		
		var oTimeLabel			= this.oContent.select('td.recurring-charge-type-definition-time-label').first();
		oTimeLabel.innerHTML	= sTimeLabel + sTimeSuffix;
		
		// Update the human readable sentence
		var oResult	= this.oContent.select('div.recurring-charge-type-definition-result').first();
		var sResult	= (sChargeNature == Popup_Recurring_Charge_Type.NATURE_DEBIT ? 'Debit' : 'Credit') + ' $' + fCharge + ' every ' + iChargePeriod + ' ' + sTimeLabel + (iChargePeriod != 1 ? 's' : '');
		
		if (iContinuing == Popup_Recurring_Charge_Type.CONTINUING_TOTAL)
		{
			sResult	+=	' over ' + iPeriodsOver + ' ' + sTimeLabel + sTimeSuffix +
						' until a Total of $' + fTotal + ' is charged.';
		}
		else
		{
			sResult	+=	' until explicitly cancelled, with a minimum period of ' + 
						iPeriodsOver + ' ' + sTimeLabel + sTimeSuffix + ' and charge of $' + fTotal;
		}
		
		oResult.innerHTML	= sResult;
	},
	
	_showCancellationFeeInput	: function()
	{
		var oCheckbox	= this.oContent.select('li.recurring-charge-type-cancellation-fee-checkbox > input[type="checkbox"]').first();
		var oInputLI	= this.oContent.select('li.recurring-charge-type-cancellation-fee').first();
		var oControl	= this.hControls['cancellation-fee'];
		
		if (oCheckbox.checked)
		{
			oInputLI.show();
			oControl.setMandatory(true);
			this._bCancellationFeeVisible	= true;
		}
		else
		{
			oInputLI.hide();
			oControl.setMandatory(false);
			oControl.setValue('');
			this._bCancellationFeeVisible	= false;
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

Popup_Recurring_Charge_Type.CONTINUING_TOTAL		= 0;
Popup_Recurring_Charge_Type.CONTINUING_MINIMUM		= 1;

Popup_Recurring_Charge_Type.FIXED_NO				= 0;
Popup_Recurring_Charge_Type.FIXED_YES				= 1;

Popup_Recurring_Charge_Type.NATURE_DEBIT			= 'DR';
Popup_Recurring_Charge_Type.NATURE_CREDIT			= 'CR';

Popup_Recurring_Charge_Type.showValidationErrors	= function(aErrors)
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

Popup_Recurring_Charge_Type.getApprovalProcessOptions	= function(fnCallback)
{
	fnCallback(
		[
	 	 	$T.option({value: Popup_Recurring_Charge_Type.APPROVAL_REQUIRED},
	 	 		'Requests for the recurring adjustment have to go through the approval process'
	 	 	),
			$T.option({value: Popup_Recurring_Charge_Type.NO_APPROVAL_REQUIRED},
				'Requests for the recurring adjustment are automatically approved'
			)
		]
	);
};

Popup_Recurring_Charge_Type.getNatureOptions	= function(fnCallback)
{
	fnCallback(
		[
		  	$T.option({value: Popup_Recurring_Charge_Type.NATURE_DEBIT},
		  		Popup_Recurring_Charge_Type.NATURE_DEBIT
	  	 	),
	 		$T.option({value: Popup_Recurring_Charge_Type.NATURE_CREDIT},
	 			Popup_Recurring_Charge_Type.NATURE_CREDIT
	 		)
	 	]
	);
};

Popup_Recurring_Charge_Type.getPerTimeOptions	= function(fnCallback)
{
	fnCallback(
		[
		  	$T.option({value: Popup_Recurring_Charge_Type.BILLING_FREQ_MONTH},
				'Months'
			)
	 	]
	);
};

Popup_Recurring_Charge_Type.getContinuingOptions	= function(fnCallback)
{
	fnCallback(
		[
		  	$T.option({value: Popup_Recurring_Charge_Type.CONTINUING_TOTAL},
		  		'Total'
	 		),
	 		$T.option({value: Popup_Recurring_Charge_Type.CONTINUING_MINIMUM},
	 			'Minimum'
	 		)
	 	]
	);
};

Popup_Recurring_Charge_Type.getFixedOptions	= function(fnCallback)
{
	fnCallback(
		[
		  	$T.option({value: Popup_Recurring_Charge_Type.FIXED_YES},
	  			'Fixed - cannot change any part of the charge definition when applying.'
	  		),
	  		$T.option({value: Popup_Recurring_Charge_Type.FIXED_NO},
	  			'Not Fixed - can change parts of the charge definition when applying.'
	  		)
	 	]
	);
};

// Control field definitions
Popup_Recurring_Charge_Type.FIELDS							= {};
Popup_Recurring_Charge_Type.FIELDS['charge-code']			= 	{
																	sElement	: 'div',
																	sType		: 'text',
																	mDefault	: '',
																	oDefinition	:	{
																						sLabel		: 'Charge Code',
																						fnValidate	: Reflex_Validation.stringOfLength.curry(null, 6),
																						mMandatory	: true
																					}
																};
Popup_Recurring_Charge_Type.FIELDS['description']			= 	{
																	sElement	: 'td',
																	sType		: 'text',
																	mDefault	: '',
																	oDefinition	:	{
																						sLabel		: 'Description',
																						fnValidate	: Reflex_Validation.stringOfLength.curry(null, 255),
																						mMandatory	: true
																					}
																};
Popup_Recurring_Charge_Type.FIELDS['approval-process']		=	{
																	sElement	: 'td',
																	sType		: 'select',
																	mDefault	: Popup_Recurring_Charge_Type.APPROVAL_REQUIRED,
																	oDefinition	:	{
																						sLabel		: 'Approval Process',
																						fnValidate	: null,
																						fnPopulate	: Popup_Recurring_Charge_Type.getApprovalProcessOptions,
																						mMandatory	: true
																					}
																};
Popup_Recurring_Charge_Type.FIELDS['definition-charge']		= 	{
																	sElement	: 'td',
																	sType		: 'text',
																	mDefault	: '0.00',
																	oDefinition	:	{
																						sLabel		: 'Charge Amount',
																						fnValidate	: null,
																						mMandatory	: true
																					}
																};
Popup_Recurring_Charge_Type.FIELDS['definition-nature']		= 	{
																	sElement	: 'li',
																	sType		: 'select',
																	mDefault	: Popup_Recurring_Charge_Type.NATURE_DEBIT,
																	oDefinition	:	{
																						sLabel		: 'Charge Nature',
																						fnValidate	: null,
																						fnPopulate	: Popup_Recurring_Charge_Type.getNatureOptions,
																						mMandatory	: true
																					}
																};
Popup_Recurring_Charge_Type.FIELDS['definition-period']		= 	{
																	sElement	: 'li',
																	sType		: 'text',
																	mDefault	: '1',
																	oDefinition	:	{
																						sLabel		: 'Charge Period',
																						fnValidate	: null,
																						mMandatory	: true
																					}
																};
Popup_Recurring_Charge_Type.FIELDS['definition-period-type']	= 	{
																		sElement	: 'li',
																		sType		: 'select',
																		mDefault	: Popup_Recurring_Charge_Type.BILLING_FREQ_MONTH,
																		oDefinition	:	{
																							sLabel		: 'Charge Period Type',
																							fnValidate	: null,
																							fnPopulate	: Popup_Recurring_Charge_Type.getPerTimeOptions,
																							mMandatory	: true
																						}
																	};
Popup_Recurring_Charge_Type.FIELDS['definition-time']		= 	{
																	sElement	: 'td',
																	sType		: 'text',
																	mDefault	: '1',
																	oDefinition	:	{
																						sLabel		: 'Charge Total Period',
																						fnValidate	: null,
																						mMandatory	: true
																					}
																};
Popup_Recurring_Charge_Type.FIELDS['definition-total']		= 	{
																	sElement	: 'td',
																	sType		: 'text',
																	oDefinition	:	{
																						sLabel		: 'Charge Sum',
																						fnValidate	: null,
																						mMandatory	: true
																					}
																};
Popup_Recurring_Charge_Type.FIELDS['definition-continuing']	= 	{
																	sElement	: 'div',
																	sType		: 'select',
																	mDefault	: Popup_Recurring_Charge_Type.CONTINUING_TOTAL,
																	oDefinition	:	{
																						sLabel		: 'Charge Continuation',
																						fnValidate	: null,
																						fnPopulate	: Popup_Recurring_Charge_Type.getContinuingOptions,
																						mMandatory	: true
																					}
																};
Popup_Recurring_Charge_Type.FIELDS['fixed']					= 	{
																	sElement	: 'td',
																	sType		: 'select',
																	mDefault	: Popup_Recurring_Charge_Type.FIXED_YES,
																	oDefinition	:	{
																						sLabel		: 'Fixed',
																						fnValidate	: null,
																						fnPopulate	: Popup_Recurring_Charge_Type.getFixedOptions,
																						mMandatory	: true
																					}
																};
Popup_Recurring_Charge_Type.FIELDS['cancellation-fee']		= 	{
																	sElement	: 'li',
																	sType		: 'text',
																	oDefinition	:	{
																						sLabel		: 'Cancellation Fee',
																						fnValidate	: Reflex_Validation.float,
																						mMandatory	: false
																					}
																};





