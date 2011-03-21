
var Popup_Account_Collection_Scenario = Class.create(Reflex_Popup, 
{
	initialize : function($super, iAccountId, iCurrentScenarioId, fnOnComplete)
	{
		$super(45);
		
		this._iAccountId			= iAccountId;
		this._iCurrentScenarioId	= iCurrentScenarioId;
		this._fnOnComplete			= fnOnComplete;
		
		Flex.Constant.loadConstantGroup(Popup_Account_Collection_Scenario.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
	},
	
	_buildUI : function(hEventBreakdown)
	{
		if (Object.isUndefined(hEventBreakdown))
		{
			Popup_Account_Collection_Scenario._getEventBreakdown(this._iAccountId, this._buildUI.bind(this));
			return;
		}
		
		var bHaveActivity = false;
		
		// Scheduled Events
		var oScheduledEventsDiv	= $T.div();
		var aScheduledEvents	= hEventBreakdown[$CONSTANT.ACCOUNT_COLLECTION_EVENT_STATUS_SCHEDULED];
		if (aScheduledEvents)
		{
			var oUL = $T.ul({class: 'popup-account-collection-scenario-activity-list'});
			oScheduledEventsDiv.appendChild(
				$T.div({class: 'popup-account-collection-scenario-activity-subtitle'},
					'Scheduled Events',
					$T.span({class: 'popup-account-collection-scenario-activity-sub-subtitle'},
						'These Events will be cancelled before the Scenario is changed'
					)
				)
			);
			oScheduledEventsDiv.appendChild(oUL);
			
			for (var i = 0; i < aScheduledEvents.length; i++)
			{
				oUL.appendChild($T.li(this._getEventSummaryElement(aScheduledEvents[i])));
			}
			
			bHaveActivity = true;
		}
		
		// Completed Events
		var oCompletedEventsDiv	= $T.div();
		var aCompletedEvents	= hEventBreakdown[$CONSTANT.ACCOUNT_COLLECTION_EVENT_STATUS_COMPLETED];
		if (aCompletedEvents)
		{
			var oUL = $T.ul({class: 'popup-account-collection-scenario-activity-list'});
			oCompletedEventsDiv.appendChild(
				$T.div({class: 'popup-account-collection-scenario-activity-subtitle'},
					'Completed Events'
				)
			);
			oCompletedEventsDiv.appendChild(oUL);
			
			for (var i = 0; i < aCompletedEvents.length; i++)
			{
				oUL.appendChild($T.li(this._getEventSummaryElement(aCompletedEvents[i])));
			}
			
			bHaveActivity = true;
		}
		
		// Cancelled Events
		var oCancelledEventsDiv	= $T.div();
		var aCancelledEvents	= hEventBreakdown[$CONSTANT.ACCOUNT_COLLECTION_EVENT_STATUS_CANCELLED];
		if (aCancelledEvents)
		{
			var oUL = $T.ul({class: 'popup-account-collection-scenario-activity-list'});
			oCancelledEventsDiv.appendChild(
				$T.div({class: 'popup-account-collection-scenario-activity-subtitle'},
					'Cancelled Events'
				)
			);
			oCancelledEventsDiv.appendChild(oUL);
			for (var i = 0; i < aCancelledEvents.length; i++)
			{
				oUL.appendChild($T.li(this._getEventSummaryElement(aCancelledEvents[i])));
			}
			
			bHaveActivity = true;
		}
		
		var oEventActivityDiv = $T.div({class: 'popup-account-collection-scenario-scenario-event-activity'});
		if (bHaveActivity)
		{
			var oSection = new Section();
			oSection.setTitleText('Collections Activity Today');
			oSection.setContent(
				$T.div(
					oScheduledEventsDiv,
					oCompletedEventsDiv,
					oCancelledEventsDiv
				)
			);
			oEventActivityDiv = oSection.getElement();
		}
		
		// Scenario control
		var oScenarioControl = 	Control_Field.factory(
									'select',
									{
										sLabel		: 'New Scenario',
										mEditable	: true, 
										mMandatory	: true,
										fnPopulate	: Popup_Account_Collection_Scenario._getScenarios.curry(this._iCurrentScenarioId),
										sExtraClass	: 'popup-account-collection-scenario-scenario-select'
									}
								);
		oScenarioControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._oScenarioControl = oScenarioControl;
		
		var oSection2 = new Section(false, 'popup-account-collection-scenario-scenario-section');
		oSection2.setTitleText('New Scenario');
		oSection2.setContent(oScenarioControl.getElement());
		
		var oContentDiv = 	$T.div({class: 'popup-account-collection-scenario'},
								oEventActivityDiv,
								oSection2.getElement(),
								$T.div({class: 'popup-account-collection-scenario-buttons'},
									$T.button({class: 'icon-button'},
										$T.img({src: '../admin/img/template/approve.png'}),
										$T.span('Change')
									).observe('click', this._doSave.bind(this)),
									$T.button({class: 'icon-button'},
										$T.img({src: '../admin/img/template/decline.png'}),
										$T.span('Cancel')
									).observe('click', this.hide.bind(this))
								)
							);
		
		this.setTitle(this._iAccountId + ': Change Collection Scenario');
		this.addCloseButton();
		this.setContent(oContentDiv);
		this.display();
	},
	
	_getEventSummaryElement : function(oData)
	{
		return 	$T.div({class: 'popup-account-collection-scenario-activity-event'},
					$T.span(oData.collection_event_name),
					$T.span(' (Event Type is '),
					$T.span(oData.collection_event_type_name),
					$T.span('), '),
					$T.span(Flex.Constant.arrConstantGroups.collection_event_invocation[oData.collection_event_invocation_id].Name),
					$T.span('.')
				);
	},
	
	_doSave : function()
	{
		this._save();
	},
	
	_save : function(oResponse)
	{
		if (!oResponse)
		{
			// Validate
			try
			{
				this._oScenarioControl.validate(false);
				this._oScenarioControl.save(true);
			}
			catch (oException)
			{
				Reflex_Popup.alert(oException, {sTitle: 'Error'});
				return;
			}
			
			this._oLoading = new Reflex_Popup.Loading('Saving...');
			this._oLoading.display();
			
			// Make request (sending the details object)
			var fnResp 	= this._save.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Account', 'changeCollectionScenario');
			fnReq(this._iAccountId, this._oScenarioControl.getElementValue());
			return;
		}

		this._oLoading.hide();
		delete this._oLoading;
		
		if (!oResponse.bSuccess)
		{
			Popup_Account_Collection_Scenario._ajaxError(oResponse, 'Could not change the Accounts Collection Scenario');
			return;
		}
		
		this.hide();
		if (this._fnOnComplete)
		{
			this._fnOnComplete(oResponse.iRecordId);
		}
		
		// Notify/update other account components (if defined)
		if (Component_Account_Collections)
		{
			Component_Account_Collections.refreshInstances();
		}
		
		if (Vixen && Vixen.AccountDetails)
		{
			Vixen.AccountDetails.CancelEdit();
		}
	}
});

Object.extend(Popup_Account_Collection_Scenario, 
{
	REQUIRED_CONSTANT_GROUPS : ['account_collection_event_status',
	                            'collection_event_invocation'],
	
	_ajaxError : function(oResponse, sMessage)
	{
		// Exception
		Reflex_Popup.alert(
			(sMessage ? sMessage + '. ' : '') + oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.', 
			{sTitle: 'Error', sDebugContent: oResponse.sDebug}
		);
	},
	
	_getEventBreakdown : function(iAccountId, fnCallback, oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp 	= Popup_Account_Collection_Scenario._getEventBreakdown.curry(iAccountId, fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collections', 'getTodaysEventsForAccount');
			fnReq(iAccountId);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Popup_Account_Collection_Scenario._ajaxError(oResponse);
			return;
		}
		
		if (fnCallback)
		{
			fnCallback(oResponse.aEvents);
		}
	},
	
	_getScenarios : function(iExcludeScenarioId, fnCallback, oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp 	= Popup_Account_Collection_Scenario._getScenarios.curry(iExcludeScenarioId, fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Scenario', 'getAll');
			fnReq(true);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Popup_Account_Collection_Scenario._ajaxError(oResponse);
			return;
		}
		
		var aOptions = [];
		for (var i in oResponse.aScenarios)
		{
			if (i != iExcludeScenarioId)
			{
				aOptions.push(
					$T.option({value: i},
						oResponse.aScenarios[i].name	
					)	
				);
			}
		}
		
		fnCallback(aOptions);
	}
});