
var Component_Collections_Event = Class.create( 
{
	initialize : function(oContainerDiv, fnOnComplete, fnOnCancel, oLoadingPopup)
	{
		this._oContainerDiv = oContainerDiv;
		this._fnOnComplete	= fnOnComplete;
		this._fnOnCancel	= fnOnCancel;
		this._oLoadingPopup	= oLoadingPopup;
		
		this._oTaxType			= null;
		this._aBaseControls 	= [];
		this._hDetailControls	= null;
		
		Flex.Constant.loadConstantGroup(Component_Collections_Event.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
	},
	
	_buildUI : function(bTypesLoaded, bTaxTypeLoaded, oTaxType)
	{
		if (!bTypesLoaded)
		{
			Component_Collections_Event._getAllEventTypes(this._buildUI.bind(this, true));
			return;
		}
		
		if (!bTaxTypeLoaded)
		{
			Component_Collections_Event._getGlobalTaxType(this._buildUI.bind(this, true, true));
			return;
		}
		
		this._oTaxType = oTaxType;
		
		// Create control fields
		var oNameControl = 	Control_Field.factory(
								'text', 
								{
									sLabel		: 'Name',
									fnValidate	: Reflex_Validation.stringOfLength.curry(null, 256),
									mMandatory	: true,
									mEditable	: true
								}
							);
		oNameControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._oNameControl = oNameControl;
		this._aBaseControls.push(oNameControl);
		
		var oDescriptionControl = 	Control_Field.factory(
										'text', 
										{
											sLabel		: 'Description',
											fnValidate	: Reflex_Validation.stringOfLength.curry(null, 256),
											mMandatory	: true,
											mEditable	: true
										}
									);
		oDescriptionControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._oDescriptionControl = oDescriptionControl;
		this._aBaseControls.push(oDescriptionControl);
		
		var oTypeControl = 	Control_Field.factory(
											'select', 
											{
												sLabel		: 'Type',
												mMandatory	: true,
												mEditable	: true,
												fnPopulate	: this._getTypeOptions.bind(this)
											}
										);
		oTypeControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		oTypeControl.addOnChangeCallback(this._typeChange.bind(this));
		this._oTypeControl = oTypeControl;
		this._aBaseControls.push(oTypeControl);
		
		var oInvocationControl = 	Control_Field.factory(
										'select', 
										{
											sLabel		: 'Is Manual',
											mMandatory	: true,
											mEditable	: true,
											fnPopulate	: this._getInvocationOptions.bind(this)
										}
									);
		oInvocationControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._oInvocationControl = oInvocationControl;
		this._aBaseControls.push(oInvocationControl);
		
		// Create ui content
		this._oContentDiv = $T.div({class: 'component-collections-event'},
								$T.table({class: 'reflex input'},
									$T.tbody(
										$T.tr(
											$T.th('Name'),
											$T.td(this._oNameControl.getElement())
										),
										$T.tr(
											$T.th('Description'),
											$T.td(this._oDescriptionControl.getElement())
										),
										$T.tr(
											$T.th('Type'),
											$T.td(
												$T.ul({class: 'component-collections-event-typelist reset horizontal'},
													$T.li(this._oTypeControl.getElement()),
													$T.li(
														$T.button({class: 'icon-button'},
															$T.img({src: '../admin/img/template/new.png'}),
															$T.span('Add Event Type')
														).observe('click', this._addEventType.bind(this, null))
													)
												)	
											)
										),
										$T.tr(
											$T.th('Is Manual'),
											$T.td(this._oInvocationControl.getElement())
										)
									)
								),
								$T.div({class: 'component-collections-event-buttons'},
									$T.button('Save').observe('click', this._doSave.bind(this)),
									$T.button('Cancel').observe('click', this._cancel.bind(this))
								)
							);
		
		this._oTBody = this._oContentDiv.select('tbody').first();
		
		// Attach content
		this._oContainerDiv.appendChild(this._oContentDiv);
		
		if (this._oLoadingPopup)
		{
			this._oLoadingPopup.hide();
			delete this._oLoadingPopup;
		}
	},
	
	_doSave : function()
	{
		this._save();
	},
	
	_save : function(oResponse)
	{
		if (!oResponse)
		{
			// Validate base controls
			var aErrors = [];
			for (var i = 0; i < this._aBaseControls.length; i++)
			{
				try
				{
					this._aBaseControls[i].validate(false);
				}
				catch (oException)
				{
					aErrors.push(oException);
				}
			}
			
			// Start to build the details object using base controls
			var iInvocationId = parseInt(this._oInvocationControl.getElementValue());
			if (iInvocationId === Component_Collections_Event.OPTIONAL_INVOCATION)
			{
				// NO invocation provided
				iInvocationId = null;
			}
			
			var oDetails = 	{
								name 							: this._oNameControl.getElementValue(),
								description 					: this._oDescriptionControl.getElementValue(),
								collection_event_type_id		: parseInt(this._oTypeControl.getElementValue()),
								collection_event_invocation_id 	: iInvocationId,
								implementation_details 			: {}
							};
			
			// Validate implementation detail controls (and add to the details object)
			for (var sFieldName in this._hDetailControls)
			{
				try
				{
					this._hDetailControls[sFieldName].validate(false);
					oDetails.implementation_details[sFieldName] = this._hDetailControls[sFieldName].getElementValue();
				}
				catch (oException)
				{
					aErrors.push(oException);
				}
			}
			
			if (aErrors.length)
			{
				Component_Collections_Event._validationError(aErrors);
				return;
			}
			
			// Make request (sending the details object)
			var fnResp 	= this._save.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Event', 'createEvent');
			fnReq(oDetails);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Component_Collections_Event._ajaxError(oResponse, 'Could not save the Event Type');
			return;
		}
		
		Reflex_Popup.alert('Event saved successfully');
		
		if (this._fnOnComplete)
		{
			this._fnOnComplete(oResponse.iEventId);
		}
	},
	
	_cancel : function()
	{
		if (this._fnOnCancel)
		{
			this._fnOnCancel();
		}
	},
	
	_getInvocationOptions : function(fnCallback)
	{
		var aOptions 	= [];
		var oType 		= this._getSelectedType();
		if (oType)
		{
			// Check if an invocation is enforced by types implementation or the type itself
			var iEnforcedInvocationId = null;
			if (oType.collection_event_type_implementation.enforced_collection_event_invocation_id)
			{
				// Enforced by implementation
				iEnforcedInvocationId = oType.collection_event_type_implementation.enforced_collection_event_invocation_id;
			}
			else if (oType.collection_event_invocation_id)
			{
				// Enforced by type
				iEnforcedInvocationId = oType.collection_event_invocation_id;
			}
			
			if (iEnforcedInvocationId !== null)
			{
				// Only one allowed option
				aOptions.push(
					$T.option({value: Component_Collections_Event_Type.OPTIONAL_INVOCATION},
						'Default (' + Flex.Constant.arrConstantGroups.collection_event_invocation[iEnforcedInvocationId].Name + ')'
					)
				);
			}
		}
		
		if (!aOptions.length)
		{
			// Add no (optional) invocation item
			aOptions.push(
				$T.option({value: Component_Collections_Event_Type.OPTIONAL_INVOCATION},
					'Optional'
				)
			);
			
			// Add all invocation options
			var aData = Flex.Constant.arrConstantGroups.collection_event_invocation;
			for (var i in aData)
			{
				aOptions.push(
					$T.option({value : i},
						aData[i].Name	
					)
				);
			}
		}
		
		fnCallback(aOptions);
	},
	
	_getSelectedType : function()
	{
		if (!Component_Collections_Event._hEventTypes)
		{
			return false;
		}
		
		var iTypeId = parseInt(this._oTypeControl.getElementValue());
		return Component_Collections_Event._hEventTypes[iTypeId];
	},
	
	_typeChange : function()
	{
		this._oInvocationControl.populate();
		
		// Clear all extra implementation details rows
		var aExtraControlRows = this._oTBody.select('.component-collections-event-extra-control-row');
		for (var i = 0; i < aExtraControlRows.length; i++)
		{
			aExtraControlRows[i].remove();
		}
		
		var oType = this._getSelectedType();
		if (!oType)
		{
			return;
		}
		
		// Show the implementation detail options
		this._hDetailControls = {};
		switch (oType.collection_event_type_implementation_id)
		{
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_CORRESPONDENCE:
				// correspondence_template_id
				var oTemplateControl =	Control_Field.factory(
											'select', 
											{
												sLabel		: 'Correspondence Template',
												mMandatory	: true,
												mEditable	: true,
												fnPopulate	: Component_Collections_Event._getCorrespondenceTemplateOptions
											}
										);
				oTemplateControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				oTemplateControl.addOnChangeCallback(this._correspondenceTemplateChange.bind(this));
				
				// document_template_type_id
				var oDocumentTempateTypeControl =	Control_Field.factory(
														'select', 
														{
															sLabel		: 'Document Template Type',
															mMandatory	: false,
															mEditable	: true,
															fnPopulate	: this._getDocumentTemplateTypeOptions.bind(this)
														}
													);
				oDocumentTempateTypeControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				
				this._hDetailControls.correspondence_template_id	= oTemplateControl;
				this._hDetailControls.document_template_type_id		= oDocumentTempateTypeControl;
				
				this._addControlsToTable(this._hDetailControls);
				break;
				
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_REPORT:
				// report_sql
				var oSQLControl =	Control_Field.factory(
										'textarea', 
										{
											sLabel				: 'SQL Query',
											mMandatory			: true,
											mEditable			: true,
											rows				: 15,
											fnValidate			: Component_Collections_Event._validateReportSQL,
											sValidationReason 	: 'Must be a valid SQL Query. Must also contain \'IN (<ACCOUNTS>)\' which is a place holder for Account Ids that are inserted at execution time.'
										}
									);
				oSQLControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				oSQLControl.cancelFocusShiftOnTab();
				
				// email_notification_id
				var oEmailNotificationControl =	Control_Field.factory(
													'select', 
													{
														sLabel		: 'Email Notification',
														mMandatory	: true,
														mEditable	: true,
														fnPopulate	: Component_Collections_Event._getEmailNotificationOptions
													}
												);
				oEmailNotificationControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				
				// collection_event_report_output_id
				var oReportOutputControl =	Control_Field.factory(
												'select', 
												{
													sLabel		: 'Report Output',
													mMandatory	: true,
													mEditable	: true,
													fnPopulate	: Component_Collections_Event._getConstantOptions.curry('collection_event_report_output')
												}
											);
				oReportOutputControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				
				this._hDetailControls.report_sql 						= oSQLControl;
				this._hDetailControls.email_notification_id 			= oEmailNotificationControl;
				this._hDetailControls.collection_event_report_output_id	= oReportOutputControl;
				this._addControlsToTable(this._hDetailControls);
				break;
			
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_ACTION:
				// action_type_id
				var oActionTypeControl =	Control_Field.factory(
												'select', 
												{
													sLabel		: 'Action Type',
													mMandatory	: true,
													mEditable	: true,
													fnPopulate	: Component_Collections_Event._getActionTypeOptions
												}
											);
				oActionTypeControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				this._hDetailControls.action_type_id = oActionTypeControl;
				this._addControlsToTable(this._hDetailControls);
				break;
				
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_SEVERITY:
				// collection_severity_id
				var oSeverityControl =	Control_Field.factory(
												'select', 
												{
													sLabel		: 'Severity',
													mMandatory	: true,
													mEditable	: true,
													fnPopulate	: Component_Collections_Event._getSeverityOptions
												}
											);
				oSeverityControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				this._hDetailControls.collection_severity_id = oSeverityControl;
				
				this._oTBody.appendChild(
					$T.tr({class: 'component-collections-event-extra-control-row'},
						$T.th(
							'Severity'
						),
						$T.td(
							$T.ul({class: 'component-collections-event-severitylist reset horizontal'},
								$T.li(oSeverityControl.getElement()),
								$T.li(
									$T.button({class: 'icon-button'},
										$T.img({src: '../admin/img/template/new.png'}),
										$T.span('Add Severity')
									).observe('click', this._addSeverity.bind(this, null))
								)
							)
						)
					)
				);
				break;
				
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_OCA:
				// legal_fee_charge_type_id
				var oLegalFeeChargeTypeControl =	Control_Field.factory(
														'select', 
														{
															sLabel		: 'Charge Type (Legal Fee)',
															mMandatory	: true,
															mEditable	: true,
															fnPopulate	: Component_Collections_Event._getChargeTypeOptions
														}
													);
				oLegalFeeChargeTypeControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				this._hDetailControls.legal_fee_charge_type_id = oLegalFeeChargeTypeControl;
				this._addControlsToTable(this._hDetailControls);
				break;
				
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_CHARGE:
				// charge_type_id
				var oChargeTypeControl =	Control_Field.factory(
												'select', 
												{
													sLabel		: 'Charge Type',
													mMandatory	: true,
													mEditable	: true,
													fnPopulate	: Component_Collections_Event._getChargeTypeOptions
												}
											);
				oChargeTypeControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				this._hDetailControls.charge_type_id = oChargeTypeControl;
				
				// allow_recharge
				var oAllowRechargeControl =	Control_Field.factory(
												'checkbox', 
												{
													sLabel		: 'Allow Recharge',
													mMandatory	: false,
													mEditable	: true
												}
											);
				oAllowRechargeControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				this._hDetailControls.allow_recharge = oAllowRechargeControl;
				
				// Append the incomplete list
				this._addControlsToTable(this._hDetailControls);
				
				// flat_fee
				var oFlatFeeControl =	Control_Field.factory(
											'text', 
											{
												sLabel		: 'Flat Fee',
												mMandatory	: false,
												mEditable	: true,
												fnValidate	: Reflex_Validation.float
											}
										);
				oFlatFeeControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				oFlatFeeControl.addOnChangeCallback(this._flatFeeChange.bind(this));
				this._hDetailControls.flat_fee = oFlatFeeControl;
				
				// percentage_outstanding_debt
				var oPercentageDebtControl =	Control_Field.factory(
													'text', 
													{
														sLabel		: 'Percentage of Outstanding Debt',
														mMandatory	: false,
														mEditable	: true,
														fnValidate	: Reflex_Validation.float
													}
												);
				oPercentageDebtControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				this._hDetailControls.percentage_outstanding_debt = oPercentageDebtControl;
				
				// minimum_amount
				var oMinimumAmountControl =	Control_Field.factory(
												'text', 
												{
													sLabel		: 'Minimum Amount',
													mMandatory	: false,
													mEditable	: true,
													fnValidate	: Reflex_Validation.float
												}
											);
				oMinimumAmountControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				this._hDetailControls.minimum_amount = oMinimumAmountControl;
				
				// maximum_amount
				var oMaximumAmountControl =	Control_Field.factory(
												'text', 
												{
													sLabel		: 'Maximum Amount',
													mMandatory	: false,
													mEditable	: true,
													fnValidate	: Reflex_Validation.float
												}
											);
				oMaximumAmountControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				this._hDetailControls.maximum_amount = oMaximumAmountControl;
				
				// Amount controls
				var oFlatFeeCheckbox 	= $T.input({type: 'radio', name: 'charge_amount', value: Component_Collections_Event.CHARGE_AMOUNT_TYPE_FLAT_FEE});
				var oPercentageCheckbox	= $T.input({type: 'radio', name: 'charge_amount', value: Component_Collections_Event.CHARGE_AMOUNT_TYPE_PERCENTAGE});
				oFlatFeeCheckbox.observe('click', this._chargeAmountCheckboxChange.bind(this));
				oPercentageCheckbox.observe('click', this._chargeAmountCheckboxChange.bind(this));
				
				var oAmountTR =	$T.tr({class: 'component-collections-event-extra-control-row component-collections-event-charge-amount'},
									$T.th(
										'Amount'	
									),
									$T.td(
										$T.div(
											$T.ul({class: 'reset horizontal'},
												$T.li(oFlatFeeCheckbox),
												$T.li(
													$T.span('Flat Fee: ').observe('click', this._chargeAmountCheckboxSelected.bind(this, oFlatFeeCheckbox)),
													$T.span({class: 'component-collections-event-charge-amount-symbol'},
														'$'
													),
													oFlatFeeControl.getElement()
												),
												$T.li({class: 'component-collections-event-charge-amount-tax-component'})
											)
										),
										$T.div({class: 'component-collections-event-charge-amount-option-2'},
											$T.ul({class: 'reset horizontal'},
												$T.li(oPercentageCheckbox),
												$T.li(
													$T.span('Percentage of Outstanding Debt:').observe('click', this._chargeAmountCheckboxSelected.bind(this, oPercentageCheckbox)),
													$T.div({class: 'component-collections-event-charge-amount-percentage-debt'},
														oPercentageDebtControl.getElement(),
														$T.span({class: 'component-collections-event-charge-amount-symbol'},
															'%'
														)
													),
													$T.div(
														$T.span({class: 'component-collections-event-charge-amount-symbol'},
															'$'
														),
														oMinimumAmountControl.getElement(),
														$T.span({class: 'component-collections-event-charge-amount-symbol'},
															'Min.'
														)
													),
													$T.div(
														$T.span({class: 'component-collections-event-charge-amount-symbol'},
															'$'
														),
														oMaximumAmountControl.getElement(),
														$T.span({class: 'component-collections-event-charge-amount-symbol'},
															'Max.'
														)
													)
												)
											)
										)
									)
								);
					this._oTBody.appendChild(oAmountTR);
					this._chargeAmountCheckboxSelected(oFlatFeeCheckbox);
				break;
			
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_EXIT_COLLECTIONS:
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_BARRING:
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_TDC:
				// No extra details are necessary for these implementations
				break;
		}
	},
	
	_addControlsToTable : function(hControls)
	{
		if (hControls != {})
		{
			// Add all controls into the details table
			for (var i in hControls)
			{
				this._oTBody.appendChild(
					$T.tr({class: 'component-collections-event-extra-control-row'},	
						$T.th(
							hControls[i].getLabel()	
						),
						$T.td(
							hControls[i].getElement()	
						)
					)
				);
			}
		}
	},
	
	_getTypeOptions : function(fnCallback)
	{
		if (!Component_Collections_Event._hEventTypes)
		{
			Component_Collections_Event._getAllEventTypes(this._getTypeOptions.bind(this, fnCallback));
			return;
		}
		
		var aOptions 	= [];
		var aData		= Component_Collections_Event._hEventTypes;
		for (var i in aData)
		{
			aOptions.push(
				$T.option({value : i},
					aData[i].name	
				)
			);
		}
		fnCallback(aOptions);
	},
	
	_chargeAmountCheckboxChange : function(oEvent)
	{
		var oCheckbox = oEvent.target;
		this._chargeAmountCheckboxSelected(oCheckbox);		
	},
	
	_chargeAmountCheckboxSelected : function(oCheckbox)
	{
		// Select if not already done so
		if (!oCheckbox.checked)
		{
			oCheckbox.checked = true;
		}
		
		if (oCheckbox.value == Component_Collections_Event.CHARGE_AMOUNT_TYPE_FLAT_FEE)
		{
			// Flat Fee
			// Disable percentage controls
			this._hDetailControls.percentage_outstanding_debt.disableInput();
			this._hDetailControls.minimum_amount.disableInput();
			this._hDetailControls.maximum_amount.disableInput();
			
			// Enable flat fee control
			this._hDetailControls.flat_fee.enableInput();
		}
		else
		{
			// Percentage
			// Enable percentage controls
			this._hDetailControls.percentage_outstanding_debt.enableInput();
			this._hDetailControls.minimum_amount.enableInput();
			this._hDetailControls.maximum_amount.enableInput();
			
			// Disable flat fee control
			this._hDetailControls.flat_fee.disableInput();
			
		}
	},
	
	_addEventType : function(iEventTypeId, oEvent)
	{
		if (iEventTypeId === null)
		{
			// Add event type popup
			new Popup_Collections_Event_Type(this._addEventType.bind(this));
			return;
		}
		
		if (iEventTypeId)
		{
			Component_Collections_Event._hEventTypes = null;
			this._oTypeControl.populate();
			this._oTypeControl.setValue(iEventTypeId);
		}
	},
	
	_addSeverity : function(iSeverityId, oEvent)
	{
		if (iSeverityId === null)
		{
			// Add event type popup
			new Popup_Collections_Severity(this._addSeverity.bind(this));
			return;
		}
		
		if (iSeverityId)
		{
			this._hDetailControls.collection_severity_id.populate();
			this._hDetailControls.collection_severity_id.setValue(iSeverityId);
		}
	},
	
	_getDocumentTemplateTypeOptions : function(fnCallback, oResponse)
	{
		if (!this._hDetailControls || !this._hDetailControls.correspondence_template_id)
		{
			return;
		}
		
		// Check the current correspondence template id, if it doesn't have a source type of SYSTEM, leave the document 
		// template type options empty
		var iCorrespondenceTemplateId = this._hDetailControls.correspondence_template_id.getElementValue();
		if (iCorrespondenceTemplateId !== null)
		{
			var oCorrespondenceTemplate = Component_Collections_Event._hCorrespondenceTemplates[iCorrespondenceTemplateId];
			if (oCorrespondenceTemplate.correspondence_source_type_id != $CONSTANT.CORRESPONDENCE_SOURCE_TYPE_SYSTEM)
			{
				var oNAOption = $T.option({value: null}, 
									'N/A'
								);
				fnCallback([oNAOption]);
				return;
			}
		}

		Component_Collections_Event._getConstantOptions('DocumentTemplateType', fnCallback);
	},
	
	_correspondenceTemplateChange : function()
	{
		if (!this._hDetailControls || !this._hDetailControls.document_template_type_id)
		{
			return;
		}
		
		this._hDetailControls.document_template_type_id.populate();
	},
	
	_flatFeeChange : function()
	{
		var oElement 	= this._oContentDiv.select('.component-collections-event-charge-amount-tax-component').first();
		var sFlatFee	= this._hDetailControls.flat_fee.getElementValue();
		if (sFlatFee != '')
		{
			var fFlatFee 		= parseFloat(sFlatFee);
			var fTaxDivisor		= 1 + (this._oTaxType ? parseFloat(this._oTaxType.rate_percentage) : 0);
			var fTaxComponent	= fFlatFee - (fFlatFee / fTaxDivisor);
			fTaxComponent		= (isNaN(fTaxComponent) ? 0 : fTaxComponent);
			oElement.innerHTML 	= 'Includes Tax component' + (this._oTaxType ? ' (' + this._oTaxType.name + ')' : '') + ': $' + new Number(fTaxComponent).toFixed(2);
		}
		else
		{
			oElement.innerHTML = '';
		}
	}
});

// Static

Object.extend(Component_Collections_Event, 
{
	OPTIONAL_INVOCATION : 0,
	
	CHARGE_AMOUNT_TYPE_FLAT_FEE 	: 1,
	CHARGE_AMOUNT_TYPE_PERCENTAGE	: 2,
	
	_hEventTypes				: null,
	_hCorrespondenceTemplates	: null,
	REQUIRED_CONSTANT_GROUPS 	: ['collection_event_invocation',
	                         	   'collection_event_type_implementation', 
		                           'collection_event_report_output',
		                           'DocumentTemplateType',
		                           'correspondence_source_type'],
	
	_ajaxError : function(oResponse, sMessage)
   	{
   		if (oResponse.aErrors)
   		{
   			// Validation errors
   			Component_Collections_Event._validationError(oResponse.aErrors);
   		}
   		else
   		{
   			// Exception
   			Reflex_Popup.alert(
   				(sMessage ? sMessage + '. ' : '') + oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.', 
   				{sTitle: 'Error'}
   			);
   		}
   	},
   	
   	_validationError : function(aErrors)
   	{
   		var oErrorElement = $T.ul();
   		for (var i = 0; i < aErrors.length; i++)
   		{
   			oErrorElement.appendChild($T.li(aErrors[i]));
   		}
   		
   		Reflex_Popup.alert(
   			$T.div({class: 'alert-validation-error'},
   				$T.div('There were errors in the form:'),
   				oErrorElement
   			),
   			{sTitle: 'Validation Error'}
   		);
   	},
	
	_getAllEventTypes : function(fnCallback, oResponse)
	{
		if (Component_Collections_Event._hEventTypes)
		{
			// Already loaded
			setTimeout(fnCallback, 10);
			return;
		}
		
		if (!oResponse)
		{
			var fnResp 	= Component_Collections_Event._getAllEventTypes.curry(fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Event_Type', 'getAll');
			fnReq(true);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Component_Collections_Event._ajaxError(oResponse);
			return;
		}
		
		Component_Collections_Event._hEventTypes = oResponse.aEventTypes;
		
		if (fnCallback)
		{
			fnCallback();
		}
	},
	
	_getCorrespondenceTemplateOptions : function(fnCallback, oResponse)
	{
		if (!oResponse)
		{
			// Make request
			var fnResp 	= Component_Collections_Event._getCorrespondenceTemplateOptions.curry(fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Event', 'getCorrespondenceTemplates');
			fnReq();
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Component_Collections_Event._ajaxError(oResponse);
			return;
		}
		
		Component_Collections_Event._hCorrespondenceTemplates = oResponse.aResults;
		
		// Create options & callback
		var aData 		= oResponse.aResults;
		var aOptions 	= [];
		for (var i in aData)
		{
			aOptions.push(
				$T.option({value : i},
					aData[i].name	
				)
			);
		}
		fnCallback(aOptions);
	},
	
	_getEmailNotificationOptions : function(fnCallback, oResponse)
	{
		if (!oResponse)
		{
			// Make request
			var fnResp 	= Component_Collections_Event._getEmailNotificationOptions.curry(fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Email_Notification', 'getAll');
			fnReq();
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Component_Collections_Event._ajaxError(oResponse);
			return;
		}
		
		// Create options & callback
		var aData 		= oResponse.aEmailNotifications;
		var aOptions 	= [];
		for (var i in aData)
		{
			aOptions.push(
				$T.option({value : i},
					aData[i].name	
				)
			);
		}
		fnCallback(aOptions);
	},
	
	_getActionTypeOptions : function(fnCallback, oResponse)
	{
		if (!oResponse)
		{
			// Make request
			var fnResp 	= Component_Collections_Event._getActionTypeOptions.curry(fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Event', 'getAvailableActionTypes');
			fnReq(true);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Component_Collections_Event._ajaxError(oResponse);
			return;
		}
		
		// Create options & callback
		var aData 		= oResponse.aActionTypes;
		var aOptions 	= [];
		for (var i in aData)
		{
			aOptions.push(
				$T.option({value : i},
					aData[i].name	
				)
			);
		}
		fnCallback(aOptions);
	},
	
	_getSeverityOptions : function(fnCallback, oResponse)
	{
		if (!oResponse)
		{
			// Make request
			var fnResp 	= Component_Collections_Event._getSeverityOptions.curry(fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Severity', 'getAll');
			fnReq(true);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Component_Collections_Event._ajaxError(oResponse);
			return;
		}
		
		// Create options & callback
		var aData 		= oResponse.aResults;
		var aOptions 	= [];
		for (var i in aData)
		{
			aOptions.push(
				$T.option({value : i},
					aData[i].name	
				)
			);
		}
		fnCallback(aOptions);
	},
	
	_getChargeTypeOptions : function(fnCallback, oResponse)
	{
		if (!oResponse)
		{
			// Make request
			var fnResp 	= Component_Collections_Event._getChargeTypeOptions.curry(fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Charge_Type', 'getAll');
			fnReq();
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Component_Collections_Event._ajaxError(oResponse);
			return;
		}
		
		// Create options & callback
		var aData 		= oResponse.aResults;
		var aOptions 	= [];
		for (var i in aData)
		{
			aOptions.push(
				$T.option({value : i},
					aData[i].Description	
				)
			);
		}
		fnCallback(aOptions);
	},
	
	_getConstantOptions : function(sConstantGroup, fnCallback)
	{
		// Create options & callback
		var aData 		= Flex.Constant.arrConstantGroups[sConstantGroup];
		var aOptions	= [];
		for (var i in aData)
		{
			aOptions.push(
				$T.option({value : i},
					aData[i].Name	
				)
			);
		}
		fnCallback(aOptions);
	},
	
	_validateReportSQL : function(mValue)
	{
		var sSingleLine = mValue.toString().replace(/\n/, ' ');
		if (!sSingleLine.match(/^SELECT(.|\n)*FROM(.|\n)*WHERE(.|\n)*(IN\s+\(\s?\<ACCOUNTS\>\s?\))/i))
		{
			return false;
		}
		
		return true;
	},
	
	_getGlobalTaxType : function(fnCallback, oResponse)
	{
		if (!oResponse)
		{
			// Make request
			var fnResp 	= Component_Collections_Event._getGlobalTaxType.curry(fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Tax_Type', 'getGlobalTaxType');
			fnReq();
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Component_Collections_Event._ajaxError(oResponse);
			return;
		}
		
		if (fnCallback)
		{
			fnCallback(oResponse.oTaxType);
		}
	}
});
