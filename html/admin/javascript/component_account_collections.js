
var Component_Account_Collections = Class.create(
{
	initialize : function(iAccountId, oContainerDiv, iDaysBefore, iDaysAfter)
	{
		this._register();
		
		this._iAccountId 	= iAccountId;
		this._oContainerDiv	= oContainerDiv;
		iDaysBefore 		= iDaysBefore ? iDaysBefore : Component_Account_Collections.DEFAULT_TIMELINE_DAYS_BEFORE;
		iDaysAfter 			= iDaysAfter ? iDaysAfter : Component_Account_Collections.DEFAULT_TIMELINE_DAYS_AFTER;
		
		this._bPutDateInNextRow = false;
		
		this._oOverlay = new Reflex_Loading_Overlay();
		
		var oStart = new Date();
		oStart.setDate(oStart.getDate() - iDaysBefore);
		this._iTimelineStart = oStart.getTime();
		this._iTimelineStart -= this._iTimelineStart % Component_Account_Collections.MS_IN_DAY;
		
		var oEnd = new Date();
		oEnd.setDate(oEnd.getDate() + iDaysAfter);
		this._iTimelineEnd = oEnd.getTime();
		this._iTimelineEnd -= this._iTimelineEnd % Component_Account_Collections.MS_IN_DAY;
		
		Flex.Constant.loadConstantGroup(Component_Account_Collections.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
	},
	
	// Public
	
	deregister : function()
	{
		var iIndex = Component_Account_Collections._aInstances.indexOf(this);
		Component_Account_Collections._aInstances.splice(iIndex, 1);
	},
	
	refresh : function()
	{
		this._refresh();
	},
	
	// Protected
	
	_register : function()
	{
		Component_Account_Collections._aInstances.push(this);
	},
		
	_buildUI : function()
	{
		this._oEventsContainer =	$T.div({class: 'component-account-collections-events'},
										$T.table(
											$T.tbody()	
										)
									);
		this._oEventsTBody 		= 	this._oEventsContainer.select('tbody').first();
		this._oPromiseContainer	=	$T.div({class: 'component-account-collections-promise'},
										$T.table({class: 'reflex input'},
											$T.tbody(
												$T.tr(
													$T.th('Created'),
													$T.td({class: 'component-account-collections-promise-created'})
												),
												$T.tr(
													$T.th('Reason'),
													$T.td({class: 'component-account-collections-promise-reason'})
												),
												$T.tr(
													$T.th('Using Direct Debit'),
													$T.td({class: 'component-account-collections-promise-directdebit'})
												),
												$T.tr(
													$T.th('Instalments'),
													$T.td(
														$T.table({class: 'component-account-collections-promise-instalmentstable'},
															$T.tbody({class: 'component-account-collections-promise-instalments'})
														)	
													)
												)
											)	
										),
										$T.span('There is no active Promise to Pay for this Account.')
									);
		this._oPromiseContainer.select('table').first().hide();
		
		this._oButtonContainer	= 	$T.div({class: 'component-account-collections-footer-buttons'});
		var oTabGroupContainer	= 	$T.div();
		this._oElement 			= 	$T.div({class: 'component-account-collections'},
										$T.h2('Collections'),
										oTabGroupContainer,
										this._oButtonContainer
									);
		
		var oTabGroup = new Control_Tab_Group(oTabGroupContainer, true);
		oTabGroup.addTab('events', new Control_Tab('Events', this._oEventsContainer));
		oTabGroup.addTab('promise', new Control_Tab('Promise to Pay', this._oPromiseContainer));
		this._oTabGroup = oTabGroup;
		
		if (this._oContainerDiv)
		{
			this._oContainerDiv.appendChild(this._oElement);
		}

		this._refresh();
	},
	
	_getEventTimeline : function(oResponse)
	{
		if (Object.isUndefined(oResponse))
		{
			// Attach loading overlay
			if (this._oTabGroup.getSelectedTab().getName() === 'Events')
			{
				this._oOverlay.attachTo(this._oEventsContainer);
			}
			
			// Request
			var fnResp	= this._getEventTimeline.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collections', 'getEventSummaryForAccount');
			fnReq(this._iAccountId, this._iTimelineStart, this._iTimelineEnd);
			return;
		}

		// Detach loading overlay
		if (this._oTabGroup.getSelectedTab().getName() === 'Events')
		{
			this._oOverlay.detach()
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Component_Account_Collections._ajaxError(oResponse);
			return;
		}
		
		// Clear the table
		this._oEventsTBody.innerHTML = '';
		
		// Success
		if (Object.isArray(oResponse.aEvents))
		{
			// No events
			this._oEventsTBody.appendChild(
				$T.tr(
					$T.td({colspan: 0},
						'There is no recent collections activity for this Account.'
					)
				)
			);
			return;
		}
		
		// Add a row for each day, show any events that occured/are to occur
		var iTime 	= this._iTimelineEnd;
		var oNow	= new Date();
		var sToday	= oNow.$format('Y-m-d');
		oNow.setDate(oNow.getDate() + 1);
		var sTomorrow = oNow.$format('Y-m-d');
		
		while (iTime >= this._iTimelineStart)
		{
			var sDate 				= new Date(iTime).$format('Y-m-d');
			var iRowCount			= 0;
			this._bPutDateInNextRow = true;
			
			if (oResponse.aEvents[sDate])
			{
				// Promise instalments
				var aInstalments = oResponse.aEvents[sDate].collection_promise_instalment;
				if (aInstalments)
				{
					for (var i = 0; i < aInstalments.length; i++)
					{
						this._oEventsTBody.appendChild(this._createPromiseInstalmentEventItem(sDate, aInstalments[i]));
						iRowCount++;
					}
				}

				// Scenario Event instances (unscheduled events, this will only be the next future event)
				var aEvents = oResponse.aEvents[sDate].collection_scenario_collection_event;
				if (aEvents)
				{
					for (var i = 0; i < aEvents.length; i++)
					{

						this._oEventsTBody.appendChild(this._createScenarioEventItem(sDate, aEvents[i]));
						iRowCount++;
					}
				}
				
				// Event instances
				var aEvents = oResponse.aEvents[sDate].account_collection_event_history;
				if (aEvents)
				{
					for (var i = 0; i < aEvents.length; i++)
					{
						this._oEventsTBody.appendChild(this._createEventInstanceEventItem(sDate, aEvents[i]));
						iRowCount++;
					}
				}
				
				// Suspensions
				var aSuspensions = oResponse.aEvents[sDate].collection_suspension;
				if (aSuspensions)
				{
					for (var i = 0; i < aSuspensions.length; i++)
					{
						this._oEventsTBody.appendChild(this._createSuspensionEventItem(sDate, aSuspensions[i]));
						iRowCount++;
					}
				}
			}
			
			if ((iRowCount == 0) && ((sDate == sToday) || (sDate == sTomorrow)))
			{
				this._oEventsTBody.appendChild(this._createEventItem(sDate));
			}
			
			iTime -= Component_Account_Collections.MS_IN_DAY; 
		}
	},
	
	_getPromiseDetails : function(oResponse)
	{
		if (Object.isUndefined(oResponse))
		{
			// Attach loading overlay
			if (this._oTabGroup.getSelectedTab().getName() === 'Promise to Pay')
			{
				this._oOverlay.attachTo(this._oPromiseContainer);
			}
			
			// Request
			var fnResp	= this._getPromiseDetails.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collections', 'getExtendedPromiseDetailsForAccount');
			fnReq(this._iAccountId);
			return;
		}
		
		// Clear the promise summary interface
		var oCreatedTD 			= this._oPromiseContainer.select('.component-account-collections-promise-created').first();
		var oReasonTD 			= this._oPromiseContainer.select('.component-account-collections-promise-reason').first();
		var oUseDirectDebitTD 	= this._oPromiseContainer.select('.component-account-collections-promise-directdebit').first();
		var oInstalmentsTBody 	= this._oPromiseContainer.select('.component-account-collections-promise-instalments').first();
		
		oCreatedTD.innerHTML 		= '';
		oReasonTD.innerHTML 		= '';
		oUseDirectDebitTD.innerHTML	= '';
		oInstalmentsTBody.innerHTML	= '';
		
		// Detach loading overlay
		if (this._oTabGroup.getSelectedTab().getName() === 'Promise to Pay')
		{
			this._oOverlay.detach()
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Component_Account_Collections._ajaxError(oResponse);
			return;
		}
		
		this._oPromiseContainer.select('table').first().hide();
		this._oPromiseContainer.select('span').first().hide();
		
		if (!oResponse.oPromise)
		{
			// Show 'empty' message
			this._oPromiseContainer.select('span').first().show();
			return;
		}
		
		// Show table
		this._oPromiseContainer.select('table').first().show();
		
		// Success
		
		// Simple data
		var oPromise 				= oResponse.oPromise;
		oCreatedTD.innerHTML		= Date.$parseDate(oPromise.created_datetime, 'Y-m-d H:i:s').$format('d/m/y g:i A') + ' by ' + oPromise.created_employee_name;
		oReasonTD.innerHTML 		= oPromise.collection_promise_reason.name;
		oUseDirectDebitTD.innerHTML	= (oPromise.use_direct_debit_actual ? 'Yes' : 'No');
		
		// Instalments
		for (var i = 0; i < oPromise.collection_promise_instalments.length; i++)
		{
			var oInstalment = oPromise.collection_promise_instalments[i];
			var sIconSrc	= Component_Account_Collections.PROMISE_INSTALMENT_UNPAID_IMAGE_SOURCE;
			var sIconClass	= 'component-account-collections-promise-instalment-unpaid';
			var sIconAlt	= 'Unpaid';
			if (oInstalment.balance == 0)
			{
				sIconSrc 	= Component_Account_Collections.PROMISE_INSTALMENT_PAID_IMAGE_SOURCE;
				sIconAlt	= 'Paid';
				sIconClass	= null;
			}
			else if (oInstalment.balance != oInstalment.amount)
			{
				sIconSrc 	= Component_Account_Collections.PROMISE_INSTALMENT_PARTIALLY_PAID_IMAGE_SOURCE;
				sIconAlt	= 'Partially Paid';
				sIconClass	= null;
			}
			
			sIconAlt = 'Balance: $' + new Number(oInstalment.balance).toFixed(2) + ' (' + sIconAlt + ')';
			oInstalmentsTBody.appendChild(
				$T.tr(
					$T.td(Date.$parseDate(oInstalment.due_date, 'Y-m-d').$format('d/m/y')),
					$T.td('$' + new Number(oInstalment.amount).toFixed(2)),
					$T.td(
						$T.img({class: sIconClass, src: sIconSrc, alt: sIconAlt, title: sIconAlt})
					)
				)
			);
		}
	},
	
	_getFooterButtons : function(oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp	= this._getFooterButtons.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Suspension', 'getSuspensionAvailabilityInfo');
			fnReq(this._iAccountId);
			return;
		}
		
		// Clear the button container
		this._oButtonContainer.innerHTML = '';
		
		if (!oResponse.bSuccess)
		{
			// Error
			Component_Account_Collections._ajaxError(oResponse);
			return;
		}
		
		// Suspension button
		if (oResponse.oSuspension)
		{
			// End Suspension
			this._oButtonContainer.appendChild(
				$T.button('End Collections Suspension').observe('click', this._endSuspension.bind(this))	
			);
		}
		else if (!oResponse.oPromise)
		{
			// Start Suspension
			this._oButtonContainer.appendChild(
				$T.button('Suspend From Collections').observe('click', this._startSuspension.bind(this))	
			);
		}
		
		// Promise button
		if (oResponse.oPromise)
		{
			// Cancel Promise
			this._oButtonContainer.appendChild(
				$T.button('Cancel Promise to Pay').observe('click', this._cancelPromise.bind(this, false))	
			);
		}
		else
		{
			// Create Promise
			this._oButtonContainer.appendChild(
				$T.button('Create Promise to Pay').observe('click', this._createPromise.bind(this))	
			);
		}
	},
	
	_createPromiseInstalmentEventItem : function(sDate, oData)
	{
		return	this._createEventItem(
					sDate, 
					Component_Account_Collections.PROMISE_INSTALMENT_IMAGE_SOURCE, 
					'Promise to Pay Instalment', 
					'Instalment Due: $' + new Number(oData.amount).toFixed(2),
					[{sIconSrc: Component_Account_Collections.VIEW_IMAGE_SOURCE, sAlt: 'View Instalment Details', fnOnClick: this._viewPromiseInstalmentDetails.bind(this, oData)}]
				);
	},

	_createScenarioEventItem : function (sDate, oData)
	{
		// Different completion icon, depending on the event invocation
		if (oData.collection_event_invocation_id == $CONSTANT.COLLECTION_EVENT_INVOCATION_MANUAL)
		{
			sIcon 		= Component_Account_Collections.EVENT_COMPLETED_MANUAL_IMAGE_SOURCE;
			sIconAlt	= 'To Be Completed Manually';
		}
		else
		{
			sIcon 		= Component_Account_Collections.EVENT_COMPLETED_AUTO_IMAGE_SOURCE;
			sIconAlt	= 'To Be Completed Automatically';
		}

		sDetails = oData.collection_event_name;

		return	this._createEventItem(
					sDate,
					sIcon,
					sIconAlt,
					sDetails,
					[{sIconSrc: Component_Account_Collections.VIEW_IMAGE_SOURCE, sAlt: 'View Event Details', fnOnClick: this._viewScenarioEventDetails.bind(this, oData)}]
				);
	},
	
	_createEventInstanceEventItem : function(sDate, oData)
	{
		
		var sDetails 	= '';
		var sIcon		= null;
		var sIconAlt	= null;
		switch (oData.sub_type)
		{
			case 'scheduled_datetime':
				// Scheduled
				sDetails 	= oData.collection_event_name;
				sIcon		= Component_Account_Collections.EVENT_SCHEDULED_IMAGE_SOURCE;
				sIconAlt	= 'Scheduled Event';
				break;
			
			case 'completed_datetime':
				// Completed
				sDetails = oData.collection_event_name;
				
				// Different completion icon, depending on the event invocation
				if (oData.collection_event_invocation_id == $CONSTANT.COLLECTION_EVENT_INVOCATION_MANUAL)
				{
					sIcon 		= Component_Account_Collections.EVENT_COMPLETED_MANUAL_IMAGE_SOURCE;
					sIconAlt	= 'Completed Manually';
				}
				else
				{
					sIcon 		= Component_Account_Collections.EVENT_COMPLETED_AUTO_IMAGE_SOURCE;
					sIconAlt	= 'Completed Automatically';
				}
				break;
		}
		
		return	this._createEventItem(
					sDate, 
					sIcon, 
					sIconAlt, 
					sDetails,
					[{sIconSrc: Component_Account_Collections.VIEW_IMAGE_SOURCE, sAlt: 'View Event Details', fnOnClick: this._viewEventDetails.bind(this, oData)}]
				);
	},
	
	_createSuspensionEventItem : function(sDate, oData)
	{
		var sDetails 	= '';
		var sIcon		= null;
		switch (oData.sub_type)
		{
			case 'start_datetime':
				sDetails 	= 'Suspension Start';
				sIcon		= Component_Account_Collections.SUSPENSION_START_IMAGE_SOURCE;
				break;
			
			case 'effective_end_datetime':
				sDetails	= 'Suspension End';
				sIcon		= Component_Account_Collections.SUSPENSION_END_IMAGE_SOURCE;
				break;
				
			case 'proposed_end_datetime':
				sDetails 	= 'Suspension Proposed End';
				sIcon		= Component_Account_Collections.SUSPENSION_END_IMAGE_SOURCE;
				break;
		}
		
		return	this._createEventItem(
					sDate, 
					sIcon, 
					sDetails, 
					sDetails,
					[{sIconSrc: Component_Account_Collections.VIEW_IMAGE_SOURCE, sAlt: 'View Suspension Details', fnOnClick: this._viewSuspensionDetails.bind(this, oData)}]
				);
	},
	
	_createEventItem : function(sDate, sIconSrc, sIconAlt, sDetails, aActions)
	{
		var oActionTD = $T.td();
		if (aActions)
		{
			for (var i = 0; i < aActions.length; i++)
			{
				var oAction = aActions[i];
				oActionTD.appendChild(
					$T.img({class: 'pointer', src: oAction.sIconSrc, alt: oAction.sAlt, title: oAction.sAlt}).observe('click', oAction.fnOnClick)
				);
			}
		}
		
		var sDateTDClass = null
		if (sDate == new Date().$format('Y-m-d'))
		{
			// Present date
			sDateTDClass = 'component-account-collections-events-current-date';
		}
		
		sIconAlt = (sIconAlt ? sIconAlt : null);
		
		var oTR	=	$T.tr(
						$T.td({class: sDateTDClass},
							this._bPutDateInNextRow ? Date.$parseDate(sDate, 'Y-m-d').$format('d/m/y') : null
						),
						$T.td(
							sIconSrc ? $T.img({src: sIconSrc, alt: sIconAlt, title: sIconAlt}) : null	
						),
						$T.td(sDetails ? sDetails : null),
						oActionTD
					);
		this._bPutDateInNextRow = false;
		return oTR;
	},
	
	_startSuspension : function()
	{
		new Popup_Account_Suspend_From_Collections(this._iAccountId, this._refresh.bind(this));
	},
	
	_endSuspension : function()
	{
		new Popup_Account_End_Collections_Suspension(this._iAccountId, this._refresh.bind(this));
	},
	
	_cancelPromise : function()
	{
		new Popup_Account_Promise_Cancel(this._iAccountId, this._refresh.bind(this));
	},
	
	_createPromise : function()
	{
		new Popup_Account_Promise_Edit(this._iAccountId);
	},
	
	_refresh : function()
	{
		this._getEventTimeline();
		this._getFooterButtons();
		this._getPromiseDetails();
	},
	
	_viewPromiseInstalmentDetails : function(oData)
	{
		new Popup_Collections_Promise_Instalment_View(oData.id);
	},

	_viewScenarioEventDetails : function (oData)
	{
		new Popup_Collections_Scenario_Event_View(oData.id);
	},
	
	_viewEventDetails : function(oData)
	{		
		new Popup_Collections_Event_Instance_View(oData.id);
	},
	
	_viewSuspensionDetails : function(oData)
	{
		new Popup_Collections_Suspension_View(oData.id);
	}
});

Object.extend(Component_Account_Collections, 
{
	REQUIRED_CONSTANT_GROUPS : ['collection_event_invocation'],
	
	MS_IN_DAY : 1000 * 60 * 60 * 24,
	
	DEFAULT_TIMELINE_DAYS_BEFORE 	: 7,
	DEFAULT_TIMELINE_DAYS_AFTER		: 7,
	
	PROMISE_INSTALMENT_IMAGE_SOURCE					: '../admin/img/template/collection_promise_instalment.png',
	EVENT_SCHEDULED_IMAGE_SOURCE					: '../admin/img/template/collection_event.png',
	EVENT_COMPLETED_AUTO_IMAGE_SOURCE				: '../admin/img/template/collection_event_invocation_automatic.png',
	EVENT_COMPLETED_MANUAL_IMAGE_SOURCE				: '../admin/img/template/collection_event_invocation_manual.png',
	SUSPENSION_START_IMAGE_SOURCE 					: '../admin/img/template/collection_suspension_start.png',
	SUSPENSION_END_IMAGE_SOURCE 					: '../admin/img/template/collection_suspension_end.png',
	VIEW_IMAGE_SOURCE								: '../admin/img/template/magnifier.png',
	PROMISE_INSTALMENT_PAID_IMAGE_SOURCE			: '../admin/img/template/collection_promise_instalment_paid.png',
	PROMISE_INSTALMENT_UNPAID_IMAGE_SOURCE			: '../admin/img/template/collection_promise_instalment.png',
	PROMISE_INSTALMENT_PARTIALLY_PAID_IMAGE_SOURCE	: '../admin/img/template/collection_promise_instalment_partially_paid.png',
	
	_aInstances	: [],
	
	refreshInstances : function()
	{
		for (var i = 0; i < Component_Account_Collections._aInstances.length; i++)
		{
			if (Component_Account_Collections._aInstances[i] instanceof Component_Account_Collections)
			{
				Component_Account_Collections._aInstances[i].refresh();
			}
		}
	},
	
	_ajaxError : function(oResponse)
	{
		var sMessage = (oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.');
		Reflex_Popup.alert(sMessage, {sTitle: 'Error', sDebugContent: oResponse.sDebug});
	}
});