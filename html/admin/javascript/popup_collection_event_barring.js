
var Popup_Collection_Event_Barring = Class.create(Reflex_Popup,
{
	initialize : function($super, hEventInstances, fnOnComplete, fnOnCancel)
	{
		$super(30);
		
		this._hEventInstances 	= hEventInstances;
		this._fnOnComplete 		= fnOnComplete;
		this._fnOnCancel 		= (fnOnCancel ? fnOnCancel : fnOnComplete);
		this._aEventInstanceIds	= [];
		
		// Count the event instances (accounts)
		for (var iId in this._hEventInstances)
		{
			this._aEventInstanceIds.push(parseInt(iId));
		}
		
		this._buildUI();
	},
	
	_buildUI : function()
	{
		var oNow			= 	new Date();
		this._oDateField 	=	Control_Field.factory(
									'date-picker', 
									{
										mMandatory 	: true,
										mEditable	: true,
										fnValidate	: Popup_Collection_Event_Barring._validateDate,
										bTimePicker	: false,
										iYearStart	: oNow.getFullYear(),
										iYearEnd	: (oNow.getMonth == 1 ? oNow.getFullYear() + 1 : oNow.getFullYear()) 
									}
								);
		this._oDateField.setRenderMode(Control_Field.RENDER_MODE_EDIT)
		this._oDateField.addOnChangeCallback(this._dateChanged.bind(this, null));
		
		var sHelpText = 'Choose a day to schedule barring for the ';
		if (this._aEventInstanceIds.length == 1)
		{
			sHelpText += 'Account:';
		}
		else
		{
			sHelpText += this._aEventInstanceIds.length + ' Accounts:';
		}
		
		var oContent =	$T.div({class: 'popup-collection-event-barring'},
							$T.div({class: 'popup-collection-event-barring-help'},
								sHelpText
							),
							$T.div({class: 'popup-collection-event-barring-field'},
								$T.label('Date: '),
								this._oDateField.getElement()
							),
							$T.div({class: 'popup-collection-event-barring-day-info'},
								$T.span('There is '),
								$T.span({class: 'popup-collection-event-barring-day-barring-count'}),
								$T.span(' Accounts already scheduled for Barring on that day.')
							),
							$T.div({class: 'popup-collection-event-barring-buttons'},
								$T.button('Schedule').observe('click', this._schedule.bind(this)),
								$T.button('Cancel').observe('click', this._cancel.bind(this))
							)
						);
		
		this._oDayInfoElement 			= oContent.select('.popup-collection-event-barring-day-info').first();
		this._oDayBarringCountElement 	= oContent.select('.popup-collection-event-barring-day-barring-count').first();
		this._oDayInfoElement.hide();
		
		this.setContent(oContent);
		this.setTitle('Complete Collections Event: Schedule Barring');
		this.display();
	},
	
	_dateChanged : function(iRequestCount, oEvent)
	{
		if (this._oDateField.isValid())
		{
			this._oDateField.save(true);
			
			if (iRequestCount === null)
			{
				// Get the scheduled barring request count for that date
				Popup_Collection_Event_Barring._getNumberOfScheduledBarringsOnDay(
					this._oDateField.getValue(), 
					this._dateChanged.bind(this)
				);
				return;
			}
			
			// Show the request count
			this._oDayBarringCountElement.innerHTML = iRequestCount;
			this._oDayInfoElement.show();
		}
		else
		{
			this._oDayInfoElement.hide();
		}
	},
	
	_cancel : function()
	{
		if (this._fnOnCancel)
		{
			this._fnOnCancel();
		}
		this.hide();
	},
	
	_schedule : function()
	{
		if (!this._oDateField.isValid())
		{
			Reflex_Popup.alert('Please choose a date on or after today.');
			return;
		}

		// Make the request to invoke all events given the scheduled date. 
		Popup_Collection_Event_Barring._invokeEvent(
			this._aEventInstanceIds, 
			this._oDateField.getValue(), 
			this._complete.bind(this)
		);
	},
	
	_complete : function()
	{
		if (this._fnOnComplete)
		{
			this._fnOnComplete();
		}
		this.hide();
	}
});

Object.extend(Popup_Collection_Event_Barring, 
{
	_getNumberOfScheduledBarringsOnDay : function(sDate, fnCallback, oResponse)
	{
		if (!oResponse)
		{
			var fnResp	= Popup_Collection_Event_Barring._getNumberOfScheduledBarringsOnDay.curry(sDate, fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Event', 'getBarringRequestCountForDate');
			fnReq(sDate);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Collection_Event_Type.ajaxError(oResponse);
			return;
		}
		
		if (fnCallback)
		{
			fnCallback(oResponse.iRequestCount);
		}
	},
	
	_validateDate : function(sDate)
	{
		if (!sDate || (sDate == ''))
		{
			return false;
		}
		
		var oDate 	= Date.$parseDate(sDate, 'Y-m-d');
		var oToday	= new Date();
		oToday.setHours(0);
		oToday.setMinutes(0);
		oToday.setSeconds(0);
		oToday.setMilliseconds(0);
		
		return (oDate.getTime() >= oToday.getTime());
	},
	
	_invokeEvent : function(aEventInstanceIds, sDate, fnCallback, oResponse)
	{
		if (!oResponse)
		{
			var fnResp	= Popup_Collection_Event_Barring._invokeEvent.bind(this, aEventInstanceIds, sDate, fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Event', 'invokeBarringEvent');
			fnReq(aEventInstanceIds, sDate);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Collection_Event_Type.ajaxError(oResponse);
			return;
		}
		
		Collection_Event_Type._displayInvokeInformation(oResponse, fnCallback);
	}
});