
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
											$T.tr({class: 'charge-type-fixation'},
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
		var oFixationCheckbox = oContent.select( 'tr.charge-type-fixation input[type="checkbox"]' ).first();
		oFixationCheckbox.observe('click', this._updateFixationLabel.bind(this, oFixationCheckbox));
		
		this.oContent = oContent; 
		
		this.setTitle('Add Adjustment Type');
		this.setIcon('../admin/img/template/charge_small.png');
		this.setContent(oContent);
		this.display();
	},
	
	_saveChanges	: function()
	{
		// Get the data
		var aTR 			= this.oContent.select('div.charge-type-table > table.reflex > tbody > tr');
		var sCode 			= aTR[0].select('input').first().value;
		var sDescription 	= aTR[1].select('input').first().value;
		var sAmount 		= aTR[2].select('input').first().value;
		var sNature			= aTR[3].select('select').first().value;
		var bFixed			= aTR[4].select('input').first().checked;
		
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
		
		if (!sAmount || sAmount == '' || isNaN(sAmount))
		{
			aValidationErrors.appendChild($T.li('Please supply an Amount in dollars'));
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
							iId				: null,
							sChargeType		: sCode, 
							sDescription	: sDescription, 
							fAmount			: parseFloat(sAmount), 
							sNature			: sNature, 
							bFixed			: bFixed
						};
		
		var oSavePopup = new Reflex_Popup.Loading('Saving...');
		oSavePopup.display();
		this.oSavingOverlay = oSavePopup;
		
		// Make AJAX request
		this._saveChargeType = jQuery.json.jsonFunction(this._saveComplete.bind(this), this._saveCostCentresError.bind(this), 'Charge_Type', 'save');
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
	
	_saveCostCentresError	: function(oResponse)
	{
		Reflex_Popup.alert('There was an error saving the Adjustment Type' + (oResponse.ErrorMessage ? ' (' + oResponse.ErrorMessage + ')' : ''), {sTitle: 'Save Error'});
	},
	
	_showCancelConfirmation	: function()
	{
		Reflex_Popup.yesNoCancel('Are you sure you want to cancel and revert all changes?', {sTitle: 'Revert Changes', fnOnYes: this.hide.bind(this)});
	},
	
	_updateFixationLabel	: function(oFixationCheckbox)
	{
		var oFixationOnSpan = this.oContent.select('tr.charge-type-fixation td > span').first();
		var oFixationOffSpan = this.oContent.select('tr.charge-type-fixation td > span').last();
		
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