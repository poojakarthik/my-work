
var Popup_Charge_Type = Class.create(Reflex_Popup, {
	initialize: function($super, fnOnClose, iChargeModel) {
		$super(40);

		this.fnOnClose = fnOnClose;
		this._iChargeModel = iChargeModel;
		this._sChargeModel = Flex.Constant.arrConstantGroups.charge_model[this._iChargeModel].Name;
		this._buildUI();
	},

	_buildUI: function() {
		this.hInputs = {};

		// Build UI
		var oContent = $T.div({class: 'charge-type'},
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
									this._sChargeModel + ' Code :'
							),
							$T.td(
								this.hInputs[this._sChargeModel + ' Code'] = $T.input({type: 'text', name: this._sChargeModel + ' Code'})
							)
						),
						$T.tr(
							$T.th({class: 'label'},
								'Description :'
							),
							$T.td(
								this.hInputs.Description = $T.input({type: 'text', name: 'Description'})
							)
						),
						$T.tr(
							$T.th({class: 'label'},
								'Amount ($):'
							),
							$T.td(
								this.hInputs.Amount = $T.input({type: 'text', name: 'Amount', value: '0.00'})
							)
						),
						$T.tr(
							$T.th({class: 'label'},
								'Nature :'
							),
							$T.td(
								this.hInputs.Nature = $T.select({name: 'Nature', class: 'popup-charge-type-nature'},
									$T.option({value: 'DR'},
										'Debit'
									),
									$T.option({value: 'CR'},
										'Credit'
									)
								),
								$T.span({class: 'popup-charge-type-nature'},
									'Credit'
								)
							)
						),
						$T.tr({class: 'charge-type-checkbox'},
							$T.th({class: 'label'},
								'Fixation :'
							),
							$T.td(
								this.hInputs.Fixation = $T.input({type: 'checkbox', name: 'Fixation'}),
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

		// Treat the nature option differently depending on the charge model
		var oNatureSelect = oContent.select('select.popup-charge-type-nature').first();
		var oNatureSpan = oContent.select('span.popup-charge-type-nature').first();
		switch (this._iChargeModel) {
			case $CONSTANT.CHARGE_MODEL_CHARGE:
				oNatureSelect.show();
				oNatureSpan.hide();
				break;

			case $CONSTANT.CHARGE_MODEL_ADJUSTMENT:
				Reflex_Popup.alert('Deprecated Functionality, please inform YBS.', {sTitle: 'Error'});

				oNatureSelect.hide();
				oNatureSpan.show();

				// Select 'Credit'
				oNatureSelect.value = 'CR';
				break;
		}

		// Set the save buttons event handler
		var oSaveButton = oContent.select('button').first();
		oSaveButton.observe('click', this._saveChanges.bind(this));

		// Set the cancel buttons event handler
		var oCancelButton = oContent.select('button').last();
		oCancelButton.observe('click', this._showCancelConfirmation.bind(this));

		// Set the fixation checkbox event
		var oFixationCheckbox = oContent.select('tr.charge-type-checkbox input[type="checkbox"]').first();
		oFixationCheckbox.observe('click', this._updateFixationLabel.bind(this, oFixationCheckbox));

		// Setup validation handlers
		// var aInputs = oContent.select('input, select');
		// aInputs[0].sFieldName = this._sChargeModel + ' Code';
		// aInputs[0].bRequired = true;
		// aInputs[1].sFieldName = 'Description';
		// aInputs[1].bRequired = true;
		// aInputs[2].sFieldName = 'Amount';
		// aInputs[2].bRequired = true;
		// aInputs[3].sFieldName = 'Fixation';
		// aInputs[3].bRequired = true;
		// aInputs[4].sFieldName = 'Nature';
		// aInputs[4].bRequired = true;

		// for (var i = 0; i < aInputs.length; i++)
		// {
		// 	if (typeof aInputs[i].sFieldName !== 'undefined')
		// 	{
		// 		this.hInputs[aInputs[i].sFieldName] = aInputs[i];
		// 	}
		// }
		this.hInputs[this._sChargeModel + ' Code'].bRequired = true;
		this.hInputs.Description.bRequired = true;
		this.hInputs.Amount.bRequired = true;
		this.hInputs.Fixation.bRequired = true;
		this.hInputs.Nature.bRequired = true;

		// Inputs
		this.hInputs[this._sChargeModel + ' Code'].validate = Popup_Charge_Type._validateInput.bind(
			this.hInputs[this._sChargeModel + ' Code'],
			Reflex_Validation.Exception.nonEmptyString
		);
		this.hInputs.Description.validate = Popup_Charge_Type._validateInput.bind(
			this.hInputs.Description,
			Reflex_Validation.Exception.nonEmptyString
		);
		this.hInputs.Amount.validate = Popup_Charge_Type._validateInput.bind(
			this.hInputs.Amount,
			Reflex_Validation.Exception.float
		);

		// Selects
		this.hInputs.Nature.validate = Popup_Charge_Type._validateInput.bind(
			this.hInputs.Nature,
			Reflex_Validation.Exception.nonEmptyString
		);
		Object.keys(this.hInputs).forEach(function (sName) {
			if (this.hInputs[sName].validate) {
				this.hInputs[sName].observe('keyup', this.hInputs[sName].validate);
				this.hInputs[sName].observe('change', this.hInputs[sName].validate);
			}
		}.bind(this));

		this.oContent = oContent;

		this.setTitle('Add ' + this._sChargeModel + ' Type');
		this.setIcon('../admin/img/template/charge_small.png');
		this.setContent(oContent);
		this.display();

		// Run initial validation to show required fields
		this._isValid();
	},

	_isValid: function() {
		// Build an array of error messages, after running all validation functions
		var aErrors = [];
		var mError = null;
		var oInput = null;

		for (var sName in this.hInputs) {
			oInput = this.hInputs[sName];

			if (typeof oInput.validate !== 'undefined') {
				mError = oInput.validate();

				if (mError != null) {
					aErrors.push(mError);
				}
			}
		}

		if (['CR', 'DR'].indexOf(this.hInputs.Nature.value) === -1) {
			aErrors.push(new Error('No Nature selected'));
		}

		return aErrors;
	},

	_saveChanges: function() {
		// Validate the data
		var aValidationErrors = this._isValid();

		if (aValidationErrors.length) {
			Popup_Charge_Type._showValidationErrorPopup(aValidationErrors);
			return;
		}

		// Build request data
		var oRequestData =	{
			iId: null,
			sChargeType: this.hInputs[this._sChargeModel + ' Code'].value,
			sDescription: this.hInputs.Description.value,
			fAmount: parseFloat(this.hInputs.Amount.value),
			sNature: this.hInputs.Nature.value,
			bFixed: this.hInputs.Fixation.checked,
			iChargeModel: this._iChargeModel
		};

		var oSavePopup = new Reflex_Popup.Loading('Saving...');
		oSavePopup.display();
		this.oSavingOverlay = oSavePopup;

		// Make AJAX request
		this._saveChargeType = jQuery.json.jsonFunction(this._saveComplete.bind(this), this._saveError.bind(this), 'Charge_Type', 'save');
		this._saveChargeType(oRequestData);
	},

	_saveComplete: function(oResponse) {
		if (oResponse.Success) {
			// On close callback
			if (this.fnOnClose) {
				this.fnOnClose();
			}

			// Hide saving overlay
			this.oSavingOverlay.hide();

			// Hide this
			this.hide();

			// Confirmation
			Reflex_Popup.alert(this._sChargeModel + ' Type \'' + oResponse.sChargeType + '\' succesfully added', {sTitle: 'Save Successful'});
		} else {
			if (oResponse.Message) {
				Reflex_Popup.alert(oResponse.Message);
			}

			// Hide saving overlay
			this.oSavingOverlay.hide();

			// Show validation errors
			if (oResponse.aValidationErrors) {
				Popup_Charge_Type._showValidationErrorPopup(oResponse.aValidationErrors);
			}
		}
	},

	_saveError: function(oResponse) {
		jQuery.json.errorPopup(oResponse);
	},

	_showCancelConfirmation: function() {
		Reflex_Popup.yesNoCancel('Are you sure you want to cancel and revert all changes?', {sTitle: 'Revert Changes', fnOnYes: this.hide.bind(this)});
	},

	_updateFixationLabel: function(oFixationCheckbox) {
		var oFixationOnSpan = this.oContent.select('tr.charge-type-checkbox td > span').first();
		var oFixationOffSpan = this.oContent.select('tr.charge-type-checkbox td > span').last();

		if (oFixationCheckbox.checked) {
			oFixationOnSpan.show();
			oFixationOffSpan.hide();
		} else {
			oFixationOffSpan.show();
			oFixationOnSpan.hide();
		}
	}
});

Popup_Charge_Type.CANCEL_IMAGE_SOURCE = '../admin/img/template/delete.png';
Popup_Charge_Type.SAVE_IMAGE_SOURCE = '../admin/img/template/tick.png';

Popup_Charge_Type._showValidationErrorPopup = function(aErrors) {
	// Build UL of error messages
	var oValidationErrors = $T.ul();

	for (var i = 0; i < aErrors.length; i++) {
		oValidationErrors.appendChild($T.li(aErrors[i]));
	}

	// Show a popup containing the list
	Reflex_Popup.alert(
		$T.div({style: 'margin: 0.5em'},
			'The following errors have occured: ',
			oValidationErrors
		),
		{
			iWidth: 30,
			sTitle: 'Validation Errors'
		}
	);
};

Popup_Charge_Type._validateInput = function(fnValidate) {
	// This is to be bound to the scope of an input
	try {
		this.removeClassName('valid');
		this.removeClassName('invalid');

		// Check required validation first
		if (this.bRequired && (this.value === '' || this.value === null)) {
			throw('Required field');
		} else {
			if (fnValidate(this.value)) {
				this.addClassName('valid');
			}

			return null;
		}
	} catch (e) {
		this.addClassName('invalid');
		return this.name + ': ' + e;
	}
};
