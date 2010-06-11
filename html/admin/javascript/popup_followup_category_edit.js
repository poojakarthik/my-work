
var Popup_FollowUp_Category_Edit	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iCategoryId, fnOnClose)
	{
		$super(35);
		
		this._iCategoryId	= iCategoryId;
		this._fnOnClose		= fnOnClose;
		this._hControls		= {};
		
		if (iCategoryId)
		{
			// Edit... fetch the category details
			var fnCategoryDetails	=	jQuery.json.jsonFunction(
											this._buildUI.bind(this),
											this._ajaxError.bind(this),
											'FollowUp_Category',
											'getForId'
										);
			fnCategoryDetails(iCategoryId);
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
		this._oContent 	=	$T.div({class: 'followup-category-edit'},
								$T.div({class: 'section'},
									$T.div({class: 'section-header'},
										$T.div({class: 'section-header-title'},
											'Details'
										)
									),
									$T.div({class: 'section-content section-content-fitted'},
										$T.table({class: 'input followup-category-edit-properties'},
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
								$T.div ({class: 'followup-category-edit-buttons'},
									$T.button({class: 'icon-button'},
										$T.img({src: Popup_FollowUp_Category_Edit.SAVE_IMAGE_SOURCE, alt: '', title: 'Save'}),
										$T.span('Save')
									),
									$T.button({class: 'icon-button'},
										$T.img({src: Popup_FollowUp_Category_Edit.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel'}),
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
		
		this._attachControls(oResponse ? oResponse.oCategory : null);
		
		this.setTitle((this._iCategoryId ? 'Edit' : 'Add') + ' Follow-Up Category');
		this.setIcon('../admin/img/template/followup.png');
		this.setContent(this._oContent);
		this.display();
	},
	
	_attachControls	: function(oCategory)
	{
		var oField		= null;
		var oControl	= null;
		var sSelector	= null;
		var oTBody		= this._oContent.select('table.followup-category-edit-properties > tbody').first();
		for (var sFieldName in Popup_FollowUp_Category_Edit.FIELDS)
		{
			oField							= Popup_FollowUp_Category_Edit.FIELDS[sFieldName];
			oField.oDefinition.mEditable	= true;
			oControl						= Control_Field.factory(oField.sType, oField.oDefinition);
			
			if (oCategory && oCategory[sFieldName])
			{
				oControl.setValue(oCategory[sFieldName]);
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
				Popup_FollowUp_Category_Edit.showValidationErrors(aValidationErrors);
				return;
			}
			
			oDetails.sName			= this._hControls.name.getElementValue();
			oDetails.sDescription	= this._hControls.description.getElementValue();
			oDetails.iStatusId		= this._hControls.status_id.getElementValue();
			
			this.oLoading = new Reflex_Popup.Loading('Saving...');
			this.oLoading.display();
			
			// Make AJAX request
			this._saveChargeType = 	jQuery.json.jsonFunction(
										this._save.bind(this), 
										this._ajaxError.bind(this), 
										'FollowUp_Category', 
										'save'
									);
			this._saveChargeType(this._iCategoryId, oDetails);
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
			Reflex_Popup.alert('Category succesfully saved', {sTitle: 'Save Successful'});
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
			Popup_FollowUp_Category_Edit.showValidationErrors(oResponse.aValidationErrors);
		}
	},
	
	_showCancelConfirmation	: function()
	{
		var sText	= (this._iCategoryId ? 'Are you sure you want to cancel and revert all changes?' : 'Are you sure you want to cancel?')
		Reflex_Popup.yesNoCancel(sText, {fnOnYes: this.hide.bind(this)});
	}
});

Popup_FollowUp_Category_Edit.CANCEL_IMAGE_SOURCE 	= '../admin/img/template/delete.png';
Popup_FollowUp_Category_Edit.SAVE_IMAGE_SOURCE 		= '../admin/img/template/tick.png';

Popup_FollowUp_Category_Edit.showValidationErrors	= function(aErrors)
{
	// Create a UL to list the errors and then show a reflex alert
	var oAlertDom	=	$T.div({class: 'rebill-validation-errors'},
							$T.div('There were errors in the category information: '),
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

Popup_FollowUp_Category_Edit.getAllStatusAsSelectOptions	= function(fnCallback)
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

// Control field definitions
Popup_FollowUp_Category_Edit.FIELDS					= {};
Popup_FollowUp_Category_Edit.FIELDS.name			= 	{
															sType		: 'text',
															oDefinition	:	{
																				sLabel		: 'Name',
																				fnValidate	: Reflex_Validation.stringOfLength.curry(null, 128),
																				mMandatory	: true
																			}
														};
Popup_FollowUp_Category_Edit.FIELDS.description		= 	{
															sType		: 'text',
															oDefinition	:	{
																				sLabel		: 'Description',
																				fnValidate	: Reflex_Validation.stringOfLength.curry(null, 255),
																				mMandatory	: true
																			}
														};
Popup_FollowUp_Category_Edit.FIELDS.status_id		=	{
															sType		: 'select',
															oDefinition	:	{
																				sLabel		: 'Status',
																				fnValidate	: null,
																				fnPopulate	: Popup_FollowUp_Category_Edit.getAllStatusAsSelectOptions,
																				mMandatory	: true
																			}
														};
