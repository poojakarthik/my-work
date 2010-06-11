
var Popup_FollowUp_Closure_Edit	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iClosureId, fnOnClose)
	{
		$super(35);
		
		this._iClosureId	= iClosureId;
		this._fnOnClose		= fnOnClose;
		this._hControls		= {};
		
		if (iClosureId)
		{
			// Edit... fetch the closure details
			var fnClosureDetails	=	jQuery.json.jsonFunction(
											this._buildUI.bind(this),
											this._ajaxError.bind(this),
											'FollowUp_Closure',
											'getForId'
										);
			fnClosureDetails(iClosureId);
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
		this._oContent 	=	$T.div({class: 'followup-closure-edit'},
								$T.div({class: 'section'},
									$T.div({class: 'section-header'},
										$T.div({class: 'section-header-title'},
											'Details'
										)
									),
									$T.div({class: 'section-content section-content-fitted'},
										$T.table({class: 'input followup-closure-edit-properties'},
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
								$T.div ({class: 'followup-closure-edit-buttons'},
									$T.button({class: 'icon-button'},
										$T.img({src: Popup_FollowUp_Closure_Edit.SAVE_IMAGE_SOURCE, alt: '', title: 'Save'}),
										$T.span('Save')
									),
									$T.button({class: 'icon-button'},
										$T.img({src: Popup_FollowUp_Closure_Edit.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel'}),
										$T.span('Cancel')
									)
								)
							);
		
		// Set the save buttons event handler
		var oSaveButton		= this._oContent.select( 'button' ).first();
		oSaveButton.observe('click', this._save.bind(this, null));
		
		// Set the cancel buttons event handler
		var oCancelButton	= this._oContent.select( 'button' ).last();
		oCancelButton.observe('click', this._showCancelConfirmation.bind(this));
		
		this._attachControls(oResponse ? oResponse.oClosure : null);
		
		this.setTitle((this._iClosureId ? 'Edit' : 'Add') + ' Follow-Up Closure Reason');
		this.setIcon('../admin/img/template/followup.png');
		this.setContent(this._oContent);
		this.display();
	},
	
	_attachControls	: function(oClosure)
	{
		var oField		= null;
		var oControl	= null;
		var sSelector	= null;
		var oTBody		= this._oContent.select('table.followup-closure-edit-properties > tbody').first();
		for (var sFieldName in Popup_FollowUp_Closure_Edit.FIELDS)
		{
			oField							= Popup_FollowUp_Closure_Edit.FIELDS[sFieldName];
			oField.oDefinition.mEditable	= true;
			oControl						= Control_Field.factory(oField.sType, oField.oDefinition);
			
			if (oClosure && oClosure[sFieldName])
			{
				oControl.setValue(oClosure[sFieldName]);
			}
			else if (sFieldName == 'status_id')
			{
				oControl.setValue($CONSTANT.STATUS_ACTIVE);
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
					this._hControls[sFieldName].validate(false);					
				}
				catch (ex)
				{
					aValidationErrors.push(ex);
				}
			}
			
			if (aValidationErrors.length)
			{
				Popup_FollowUp_Closure_Edit.showValidationErrors(aValidationErrors);
				return;
			}
			
			oDetails.sName						= this._hControls.name.getElementValue();
			oDetails.sDescription				= this._hControls.description.getElementValue();
			oDetails.iFollowUpClosureTypeId		= this._hControls.followup_closure_type_id.getElementValue();
			oDetails.iStatusId					= this._hControls.status_id.getElementValue();
			
			this.oLoading = new Reflex_Popup.Loading('Saving...');
			this.oLoading.display();
			
			// Make AJAX request
			this._saveChargeType = 	jQuery.json.jsonFunction(
										this._save.bind(this), 
										this._ajaxError.bind(this), 
										'FollowUp_Closure', 
										'save'
									);
			this._saveChargeType(this._iClosureId, oDetails);
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
			Reflex_Popup.alert('Closure Reason succesfully saved', {sTitle: 'Save Successful'});
		}	
		else
		{
			// Error
			this._ajaxError(oResponse);
		}
	},
		
	_ajaxError	: function(oResponse)
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
			Popup_FollowUp_Closure_Edit.showValidationErrors(oResponse.aValidationErrors);
		}
	},
	
	_showCancelConfirmation	: function()
	{
		var sText	= (this._iClosureId ? 'Are you sure you want to cancel and revert all changes?' : 'Are you sure you want to cancel?')
		Reflex_Popup.yesNoCancel(sText, {fnOnYes: this.hide.bind(this)});
	}
});

Popup_FollowUp_Closure_Edit.CANCEL_IMAGE_SOURCE 	= '../admin/img/template/delete.png';
Popup_FollowUp_Closure_Edit.SAVE_IMAGE_SOURCE 		= '../admin/img/template/tick.png';

Popup_FollowUp_Closure_Edit.showValidationErrors	= function(aErrors)
{
	// Create a UL to list the errors and then show a reflex alert
	var oAlertDom	=	$T.div({class: 'rebill-validation-errors'},
							$T.div('There were errors in the closure reason information: '),
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

Popup_FollowUp_Closure_Edit.getAllStatusAsSelectOptions	= function(fnCallback)
{
	var aOptions	= [];
	for (var iId in Flex.Constant.arrConstantGroups.status)
	{
		aOptions.push(
			$T.option({value: iId},
				Flex.Constant.arrConstantGroups.status[iId].Name
			)
		);
	}
	
	fnCallback(aOptions);
};

Popup_FollowUp_Closure_Edit.getAllClosureTypesAsSelectOptions	= function(fnCallback)
{
	var aOptions	= [];
	for (var iId in Flex.Constant.arrConstantGroups.followup_closure_type)
	{
		aOptions.push(
			$T.option({value: iId},
				Flex.Constant.arrConstantGroups.followup_closure_type[iId].Name
			)
		);
	}
	
	fnCallback(aOptions);
};

// Control field definitions
Popup_FollowUp_Closure_Edit.FIELDS					= {};
Popup_FollowUp_Closure_Edit.FIELDS.name			= 	{
														sType		: 'text',
														oDefinition	:	{
																			sLabel		: 'Name',
																			fnValidate	: Reflex_Validation.stringOfLength.curry(null, 128),
																			mMandatory	: true
																		}
													};
Popup_FollowUp_Closure_Edit.FIELDS.description		= 	{
															sType		: 'text',
															oDefinition	:	{
																				sLabel		: 'Description',
																				fnValidate	: Reflex_Validation.stringOfLength.curry(null, 255),
																				mMandatory	: true
																			}
														};
Popup_FollowUp_Closure_Edit.FIELDS.followup_closure_type_id		=	{
																		sType		: 'select',
																		oDefinition	:	{
																							sLabel		: 'Type',
																							fnValidate	: null,
																							fnPopulate	: Popup_FollowUp_Closure_Edit.getAllClosureTypesAsSelectOptions,
																							mMandatory	: true
																						}
																	};
Popup_FollowUp_Closure_Edit.FIELDS.status_id		=	{
															sType		: 'select',
															oDefinition	:	{
																				sLabel		: 'Status',
																				fnValidate	: null,
																				fnPopulate	: Popup_FollowUp_Closure_Edit.getAllStatusAsSelectOptions,
																				mMandatory	: true
																			}
														};
