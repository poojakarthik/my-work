
var Popup_Charge_Type	= Class.create(Reflex_Popup,
{
	initialize	: function($super)
	{
		$super(40);
		
		this._buildUI();
	},
	
	_buildUI	: function()
	{
		// Build UI
		var oContent 	=	$T.div({class: 'charge-type'},
								$T.div({class: 'charge-type-table'},
									$T.table({class: 'reflex'},
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
		this.addCloseButton();
		this.setContent(oContent);
		this.display();
	},
	
	_saveChanges	: function()
	{
		
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