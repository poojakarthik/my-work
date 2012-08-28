
var Component_Collections_Scenario = Class.create( 
{
	initialize : function(oContainerDiv, bRenderMode, iScenarioId, bLoadOnly, fnOnComplete, fnOnCancel, oLoadingPopup, bShowCancelButton)
	{
		this._oContainerDiv 	= oContainerDiv;
		this._bRenderMode		= bRenderMode;
		this._iScenarioId 		= iScenarioId;
		this._bLoadOnly			= bLoadOnly;
		this._fnOnComplete		= fnOnComplete;
		this._fnOnCancel		= fnOnCancel;
		this._oLoadingPopup		= oLoadingPopup;
		this._bShowCancelButton	= !!bShowCancelButton;
		
		this._aControls	= [];
		
		Flex.Constant.loadConstantGroup(Component_Collections_Scenario.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
	},
	
	_buildUI : function()
	{
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
		oNameControl.setRenderMode(this._bRenderMode);
		this._oNameControl = oNameControl;
		this._aControls.push(oNameControl);
		
		var oDescriptionControl = 	Control_Field.factory(
										'text', 
										{
											sLabel		: 'Description',
											fnValidate	: Reflex_Validation.stringOfLength.curry(null, 256),
											mMandatory	: true,
											mEditable	: true
										}
									);
		oDescriptionControl.setRenderMode(this._bRenderMode);
		this._oDescriptionControl = oDescriptionControl;
		this._aControls.push(oDescriptionControl);

		var oStartEarlyControl =	Control_Field.factory(
										'checkbox', 
										{
											sLabel		: 'Start Early',
											mMandatory	: false,
											mEditable	: true
										}
									);
		oStartEarlyControl.setRenderMode(this._bRenderMode);
		oStartEarlyControl.addOnChangeCallback(this._startEarlyChange.bind(this, null));
		this._oStartEarlyControl = oStartEarlyControl;
		this._aControls.push(oStartEarlyControl);
		
		var oDayOffsetControl =	Control_Field.factory(
									'text', 
									{
										sLabel		: 'Description',
										fnValidate	: Component_Collections_Scenario._validateDayOffset,
										mMandatory	: false,
										mEditable	: true,
										mValue		: '0'
									}
								);
		oDayOffsetControl.setRenderMode(this._bRenderMode);
		oDayOffsetControl.addOnChangeCallback(this._dayOffsetChange.bind(this));
		this._oDayOffsetControl = oDayOffsetControl;
		this._aControls.push(oDayOffsetControl);
		
		var oThresholdPercentageControl = 	Control_Field.factory(
													'text', 
													{
														sLabel		: 'Threshold Percentage',
														fnValidate	: Reflex_Validation.float,
														mMandatory	: true,
														mEditable	: true
													}
												);
		oThresholdPercentageControl.setRenderMode(this._bRenderMode);
		this._oThresholdPercentageControl = oThresholdPercentageControl;
		this._aControls.push(oThresholdPercentageControl);
		
		var oThresholdAmountControl = 	Control_Field.factory(
												'text', 
												{
													sLabel		: 'Threshold Amount',
													fnValidate	: Reflex_Validation.float,
													mMandatory	: true,
													mEditable	: true
												}
											);
		oThresholdAmountControl.setRenderMode(this._bRenderMode);
		this._oThresholdAmountControl = oThresholdAmountControl;
		this._aControls.push(oThresholdAmountControl);
		
		var oInitialSeverityControl = 	Control_Field.factory(
											'select', 
											{
												sLabel		: 'Initial Severity',
												mMandatory	: false,
												mEditable	: true,
												fnPopulate	: Component_Collections_Scenario._getSeverityOptions.curry(null)
											}
										);
		oInitialSeverityControl.setRenderMode(this._bRenderMode);
		this._oInitialSeverityControl = oInitialSeverityControl;
		this._aControls.push(oInitialSeverityControl);
		
		var oAutomaticUnbarControl = Control_Field.factory(
										'checkbox', 
										{
											sLabel		: 'Allow Automatic Unbar',
											mMandatory	: false,
											mEditable	: true
										}
									);
		oAutomaticUnbarControl.setRenderMode(this._bRenderMode);
		this._oAutomaticUnbarControl = oAutomaticUnbarControl;
		this._aControls.push(oAutomaticUnbarControl);
		
		this._validateControls();
		this._oEventTimeline = new Component_Collections_Scenario_Event_Timeline(this._bRenderMode);
		
		// Create UI content
		this._oContentDiv = $T.div({class: 'component-collections-scenario'},
								$T.table({class: 'reflex input'},
									$T.tbody(
										$T.tr(
											$T.th('Name'),
											$T.td(oNameControl.getElement())
										),
										$T.tr(
											$T.th('Description'),
											$T.td(oDescriptionControl.getElement())
										),
										$T.tr(
											$T.th('Start Before Due Date'),
											$T.td({class: 'component-collections-scenario-start-early'},
												$T.ul({class: 'reset horizontal'},
													$T.li(
														oStartEarlyControl.getElement()
													),
													$T.li({class: 'component-collections-scenario-day-offset'},
														oDayOffsetControl.getElement()
													),
													$T.li({class: 'component-collections-scenario-day-offset-label'},
														' Days'
													)
												)
											)
										),
										$T.tr(
											$T.th('Threshold'),
											$T.td({class: 'component-collections-scenario-threshold'},
												$T.ul({class: 'reset horizontal'}, 
													$T.li({class: 'component-collections-scenario-threshold-percentage'},
														oThresholdPercentageControl.getElement()
													),
													$T.li(
														$T.span({class: 'component-collections-scenario-threshold-symbol'},
															'%'
														),
														$T.span({class: 'component-collections-scenario-threshold-info'},
															'(The percentage remaining of the original Invoice(s))'
														)
													)
												),
												$T.ul({class: 'reset horizontal'},
													$T.li({class: 'component-collections-scenario-threshold-symbol'},
														'$'
													),
													$T.li(oThresholdAmountControl.getElement()),
													$T.li({class: 'component-collections-scenario-threshold-info'},
														'(The amount overdue)'
													)
												)
											)
										),
										$T.tr(
											$T.th('Initial Severity'),
											$T.td(oInitialSeverityControl.getElement())
										),
										$T.tr(
											$T.th('Allow Automatic Unbar'),
											$T.td(oAutomaticUnbarControl.getElement())
										)
									)
								),
								$T.div({class: 'component-collections-scenario-timeline'},
									this._oEventTimeline.getElement()
								),
								$T.div({class: 'component-collections-scenario-buttons'},
									$T.button('Save as Draft').observe('click', this._saveDraft.bind(this)),
									$T.button('Save and Commit').observe('click', this._saveAndCommit.bind(this)),
									this._bShowCancelButton ? $T.button('Cancel').observe('click', this._cancel.bind(this)) : null
								)
							);
		
		this._oButtonDiv = this._oContentDiv.select('.component-collections-scenario-buttons').first();
		
		if (this._iScenarioId !== null)
		{
			// Creating from/editing/viewing a scenario
			this._oButtonDiv.hide();
			
			var iScenarioId = this._iScenarioId;
			if (this._bLoadOnly)
			{
				this._iScenarioId = null;
			}
			this._loadFromScenario(iScenarioId);
		}
		else
		{
			// Not using a scenario, close loading
			if (this._oLoadingPopup)
			{
				this._oLoadingPopup.hide();
				delete this._oLoadingPopup;
			}
		}
		
		this._startEarlyChange();
		
		// Attach content
		this._oContainerDiv.appendChild(this._oContentDiv);
	},
	
	_loadFromScenario : function(iScenarioId, oScenario)
	{
		if ((iScenarioId !== null) && !oScenario)
		{
			this._getScenarioDetails(iScenarioId, this._loadFromScenario.bind(this, iScenarioId));
			return;
		}
		
		this._oNameControl.setRenderMode(this._bRenderMode);
		this._oDescriptionControl.setRenderMode(this._bRenderMode);
		this._oDayOffsetControl.setRenderMode(this._bRenderMode);
		this._oThresholdPercentageControl.setRenderMode(this._bRenderMode);
		this._oThresholdAmountControl.setRenderMode(this._bRenderMode);
		this._oInitialSeverityControl.setRenderMode(this._bRenderMode);
		this._oAutomaticUnbarControl.setRenderMode(this._bRenderMode);
		
		// Load details from scenario
		this._oNameControl.setValue(oScenario.name);
		this._oDescriptionControl.setValue(oScenario.description);
		this._oDayOffsetControl.setValue(oScenario.day_offset);
		
		this._oThresholdPercentageControl.setValue(oScenario.threshold_percentage);
		this._oThresholdAmountControl.setValue(oScenario.threshold_amount);
		this._oInitialSeverityControl.setValue(oScenario.initial_collection_severity_id ? oScenario.initial_collection_severity_id : 0);
		
		if (oScenario.initial_collection_severity_id)
		{
			this._oInitialSeverityControl.setPopulateFunction(
				Component_Collections_Scenario._getSeverityOptions.curry(oScenario.initial_collection_severity_id)
			);
		}
		
		this._oAutomaticUnbarControl.setValue(oScenario.allow_automatic_unbar);
	
		this._validateControls();
		
		this._oEventTimeline.setRenderMode(this._bRenderMode);
		this._oEventTimeline.setStartDayOffset(oScenario.day_offset);
		this._oEventTimeline.refresh(oScenario.id);
		
		// Can edit AND (Creating a new one OR The one we're edit is a draft)
		if (this._bRenderMode && ((this._iScenarioId === null) || (oScenario.working_status_id == $CONSTANT.WORKING_STATUS_DRAFT)))
		{
			this._oButtonDiv.show();
			this._oStartEarlyControl.setValue(true);
			this._startEarlyChange();
		}
		else
		{
			this._oButtonDiv.hide();
			this._startEarlyChange(true);
		}
		
		if (this._oLoadingPopup)
		{
			this._oLoadingPopup.hide();
			delete this._oLoadingPopup;
		}
	},
	
	_getScenarioDetails : function(iScenarioId, fnCallback, oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp 	= this._getScenarioDetails.bind(this, iScenarioId, fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Scenario', 'getForId');
			fnReq(iScenarioId);
			return;
		}

		if (!oResponse.bSuccess)
		{
			// Error
			Component_Collections_Scenario._ajaxError(oResponse);
			return;
		}
		
		// Callback
		fnCallback(oResponse.oScenario);
	},
	
	_saveDraft : function()
	{
		this._save($CONSTANT.WORKING_STATUS_DRAFT);
	},
	
	_saveAndCommit : function()
	{
		this._save($CONSTANT.WORKING_STATUS_ACTIVE);
	},
	
	_save : function(iWorkingStatusId, oResponse)
	{
		if (!oResponse)
		{
			var aErrors = this._validateControls();
			if (aErrors.length)
			{
				Component_Collections_Scenario._validationError(aErrors);
				return;
			}
			
			// Start to build the details object using base controls
			var iSeverityId = parseFloat(this._oInitialSeverityControl.getElementValue());
			var oDetails = 	
			{
				id								: this._iScenarioId,
				name 							: this._oNameControl.getElementValue(),
				description 					: this._oDescriptionControl.getElementValue(),
				day_offset						: parseFloat(this._oDayOffsetControl.getElementValue()),
				threshold_percentage			: parseFloat(this._oThresholdPercentageControl.getElementValue()),
				threshold_amount				: parseFloat(this._oThresholdAmountControl.getElementValue()),
				initial_collection_severity_id	: (iSeverityId ? iSeverityId : null),
				allow_automatic_unbar			: this._oAutomaticUnbarControl.getElementValue(),
				working_status_id				: iWorkingStatusId,
				collection_event_data			: this._oEventTimeline.getData()
			};
			
			// Make request (sending the details object)
			var fnResp 	= this._save.bind(this, iWorkingStatusId);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Scenario', 'createScenario');
			fnReq(oDetails);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Component_Collections_Event._ajaxError(oResponse, 'Could not save the Scenario');
			return;
		}
		
		Reflex_Popup.alert('Scenario saved successfully');
		
		this._iScenarioId = oResponse.iScenarioId;
		
		if (iWorkingStatusId == $CONSTANT.WORKING_STATUS_ACTIVE)
		{
			// Now active, no more editing allowed
			this._bRenderMode = false;
			this._loadFromScenario(this._iScenarioId);
		}
		
		if (this._fnOnComplete)
		{
			this._fnOnComplete();
		}
	},
	
	_cancel : function()
	{
		if (this._fnOnCancel)
		{
			this._fnOnCancel();
		}
	},
	
	_validateControls : function()
	{
		// Validate base controls
		var aErrors = [];
		for (var i = 0; i < this._aControls.length; i++)
		{
			try
			{
				this._aControls[i].validate(false);
			}
			catch (oException)
			{
				aErrors.push(oException);
			}
		}
		return aErrors;
	},
	
	_startEarlyChange : function(bOverrideShow, oEvent)
	{
		var oDayOffset 		= this._oContentDiv.select('.component-collections-scenario-day-offset').first();
		var oDayOffsetLabel = this._oContentDiv.select('.component-collections-scenario-day-offset-label').first();
		if (bOverrideShow || this._oStartEarlyControl.getElementValue())
		{
			oDayOffset.show();
			oDayOffsetLabel.show();
		}
		else
		{
			oDayOffset.hide();
			oDayOffsetLabel.hide();
		}
	},
	
	_dayOffsetChange : function()
	{
		this._oEventTimeline.setStartDayOffset(parseInt(this._oDayOffsetControl.getElementValue()));
	}
});

Object.extend(Component_Collections_Scenario, 
{
	REQUIRED_CONSTANT_GROUPS : ['collection_event_invocation',
	                         	'collection_event_type_implementation',
	                         	'working_status'],
	
	_ajaxError : function(oResponse, sMessage) {
		if (oResponse.aErrors) {
			// Validation errors
			Component_Collections_Scenario._validationError(oResponse.aErrors);
		} else {
			// Exception
			jQuery.json.errorPopup(oResponse, sMessage);
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
	
	_getSeverityOptions : function(iSeverityId, fnCallback, oResponse)
	{
		if (!oResponse)
		{
			// Make request
			var fnResp 	= Component_Collections_Scenario._getSeverityOptions.curry(iSeverityId, fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Severity', 'getAll');
			fnReq(true, false, [iSeverityId]);
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
		
		// Add 'None' option
		aOptions.push(
			$T.option({value: 0},
				'None'
			)
		);
		
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
	
	_validateDayOffset : function(mValue)
	{
		var iValue = parseInt(mValue);
		if (isNaN(iValue))
		{
			throw 'Invalid number';
		}
		else if (iValue < 0)
		{
			throw 'Must be positive';
		}
		
		return true;
	}
});
