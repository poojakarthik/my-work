
var Popup_Record_Edit	= Class.create(Reflex_Popup,
{
	initialize	: function($super, sORMName, sHumanName, sIconSource, iId, hFieldDefinitions, fnOnClose)
	{
		$super(35);
		
		this._iId				= iId;
		this._sORMName			= sORMName;
		this._sHumanName		= sHumanName;
		this._sIconSource		= sIconSource;
		this._fnOnClose			= fnOnClose;
		this._hFieldDefinitions	= hFieldDefinitions;
		this._hControls			= {};
		
		if (iId)
		{
			// Edit... fetch the record details
			var fnRecordDetails	=	jQuery.json.jsonFunction(
											this._buildUI.bind(this),
											this._ajaxError.bind(this),
											this._sORMName,
											'getForId'
										);
			fnRecordDetails(iId);
		}
		else
		{
			// New
			this._buildUI();
		}
	},
	
	_buildUI	: function(oResponse)
	{
		// Build UI
		this._oContent 	=	$T.div({class: 'record-edit'},
								$T.div({class: 'section'},
									$T.div({class: 'section-header'},
										$T.div({class: 'section-header-title'},
											'Details'
										)
									),
									$T.div({class: 'section-content section-content-fitted'},
										$T.table({class: 'input record-edit-properties'},
											$T.colgroup(
												$T.col({style: 'width: 23%'}),
												$T.col({style: 'width: 77%'})
											),
											$T.tbody(
												// Controls added below
											)
										)
									)
								),
								$T.div ({class: 'record-edit-buttons'},
									$T.button({class: 'icon-button'},
										$T.img({src: Popup_Record_Edit.SAVE_IMAGE_SOURCE, alt: '', title: 'Save'}),
										$T.span('Save')
									),
									$T.button({class: 'icon-button'},
										$T.img({src: Popup_Record_Edit.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel'}),
										$T.span('Cancel')
									)
								)
							);
		
		// Set the save buttons event handler
		var oSaveButton	= this._oContent.select( 'button' ).first();
		oSaveButton.observe('click', this._save.bind(this, null));
		
		// Set the cancel buttons event handler
		var oCancelButton	= this._oContent.select( 'button' ).last();
		oCancelButton.observe('click', this._showCancelConfirmation.bind(this));
		
		this._attachControls(oResponse ? oResponse.oRecord : null);
		
		this.setTitle((this._iId ? 'Edit ' : 'Add ') + this._sHumanName);
		this.setIcon(this._sIconSource);
		this.setContent(this._oContent);
		this.display();
	},
	
	_attachControls	: function(oRecord)
	{
		var oField		= null;
		var oControl	= null;
		var sSelector	= null;
		var oTBody		= this._oContent.select('table.record-edit-properties > tbody').first();
		for (var sFieldName in this._hFieldDefinitions)
		{
			oField							= this._hFieldDefinitions[sFieldName];
			oField.oDefinition.mEditable	= true;
			oControl						= Control_Field.factory(oField.sType, oField.oDefinition);
			if (oRecord && oRecord[sFieldName])
			{
				oControl.setValue(oRecord[sFieldName]);
			}
			else if (oField.mDefaultValue)
			{
				oControl.setValue(oField.mDefaultValue);
			}
			
			oControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
			oTBody.appendChild(oControl.generateInputTableRow().oElement);
			this._hControls[sFieldName]	= oControl;
		}
	},
	
	_save	: function(oResponse)
	{
		if (!oResponse)
		{
			// Validate fields & make save request
			var aValidationErrors	= [];
			var oDetails			= {};
			for (var sFieldName in this._hControls)
			{
				try
				{
					// If valid, record the value
					if (this._hControls[sFieldName].validate(false))
					{
						oDetails[sFieldName]	= this._hControls[sFieldName].getElementValue();
					}
				}
				catch (ex)
				{
					aValidationErrors.push(ex);
				}
			}
			
			if (aValidationErrors.length)
			{
				Popup_Record_Edit.showValidationErrors(aValidationErrors);
				return;
			}
			
			this.oLoading = new Reflex_Popup.Loading('Saving...');
			this.oLoading.display();
			
			// Make AJAX request
			this._saveChargeType = 	jQuery.json.jsonFunction(
										this._save.bind(this), 
										this._ajaxError.bind(this), 
										this._sORMName, 
										'save'
									);
			this._saveChargeType(this._iId, oDetails);
		}
		else if (oResponse.Success)
		{
			// Hide loading
			this.oLoading.hide();
			delete this.oLoading;
			
			// On close callback
			if (this._fnOnClose)
			{
				this._fnOnClose();
			}
			
			// Hide this
			this.hide();
			
			// Confirmation
			Reflex_Popup.alert(this._sHumanName + ' succesfully saved', {sTitle: 'Save Successful'});
		}	
		else
		{
			// Error
			this._ajaxError(oResponse);
		}
	},
		
	_ajaxError : function(oResponse) {
		// Hide loading
		if (this.oLoading) {
			this.oLoading.hide();
			delete this.oLoading;
		}
		
		if (oResponse.aValidationErrors) {
			Popup_Record_Edit.showValidationErrors(oResponse.aValidationErrors);
		} else {
			jQuery.json.errorPopup(oResponse);
		}
	},
	
	_showCancelConfirmation	: function()
	{
		var sText	= (this._iId ? 'Are you sure you want to cancel and revert all changes?' : 'Are you sure you want to cancel?')
		Reflex_Popup.yesNoCancel(sText, {fnOnYes: this.hide.bind(this)});
	}
});

Popup_Record_Edit.CANCEL_IMAGE_SOURCE 	= '../admin/img/template/delete.png';
Popup_Record_Edit.SAVE_IMAGE_SOURCE 	= '../admin/img/template/tick.png';

Popup_Record_Edit.showValidationErrors	= function(aErrors)
{
	// Create a UL to list the errors and then show a reflex alert
	var oAlertDom	=	$T.div({class: 'record-edit-validation-errors'},
							$T.div('There were errors in the ' + this._sHumanName + ' information: '),
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
};

