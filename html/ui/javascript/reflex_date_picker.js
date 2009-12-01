var Reflex_Date_Picker	= Class.create
({
	initialize	: function()
	{
		// Unique Identifier
		this.sUID	= 'reflex-date-picker_' + String(Math.round((new Date()).getTime() * Math.random()));
		
		// Basic DOM Elements
		this.oContainer									= {};
		this.oContainer.domElement						= document.createElement('div');
		this.oContainer.domElement.id					= this.sUID;
		this.oContainer.domElement.oReflexDatePicker	= this;
		this.oContainer.domElement.addClassName('reflex-datepicker');
		
		// Header
		this.oContainer.oHeader				= {};
		this.oContainer.oHeader.domElement	= document.createElement('div');
		this.oContainer.oHeader.domElement.addClassName('header');
		this.oContainer.domElement.appendChild(this.oContainer.oHeader.domElement);
		
		this.oContainer.oHeader.oLabel				= {};
		this.oContainer.oHeader.oLabel.domElement	= document.createElement('div');
		
		this.oContainer.oHeader.oCloseButton			= {};
		this.oContainer.oHeader.oCloseButton.domElement	= document.createElement('div');
		
		// Content
		this.oContainer.oContent			= {};
		this.oContainer.oContent.domElement	= document.createElement('div');
		this.oContainer.oContent.domElement.addClassName('content');
		this.oContainer.domElement.appendChild(this.oContainer.oContent.domElement);
		
		// Date Picker Content
		this.oContainer.oContent.oDatePicker			= {};
		this.oContainer.oContent.oDatePicker.domElement	= document.createElement('div');
		this.oContainer.oContent.oDatePicker.domElement.addClassName('date-picker');
		this.oContainer.oContent.domElement.appendChild(this.oContainer.oContent.oDatePicker.domElement);
		
		// Time Picker Content
		this.oContainer.oContent.oTimePicker			= {};
		this.oContainer.oContent.oTimePicker.domElement	= document.createElement('div');
		this.oContainer.oContent.oTimePicker.domElement.addClassName('time-picker');
		this.oContainer.oContent.domElement.appendChild(this.oContainer.oContent.oTimePicker.domElement);
		
		this.oContainer.oContent.oTimePicker.oSlider	= new Reflex_Slider(0, (60 * 60 * 24) - 1, Reflex_Slider.SELECT_MODE_VALUE);
		this.oContainer.oContent.oTimePicker.oSlider.setStepping(60 * 15, true, true);
		this.oContainer.oContent.oTimePicker.oSlider.setValueCallback(this.setTimeFromSlider.bind(this));
		this.oContainer.oContent.oTimePicker.domElement.appendChild(this.oContainer.oContent.oTimePicker.oSlider.getElement());
		
		// Footer
		this.oContainer.oFooter				= {};
		this.oContainer.oFooter.domElement	= document.createElement('div');
		this.oContainer.oFooter.domElement.addClassName('footer');
		this.oContainer.domElement.appendChild(this.oContainer.oFooter.domElement);
		
		this.oContainer.oFooter.oDatetime				= {};
		this.oContainer.oFooter.oDatetime.domElement	= document.createElement('div');
		this.oContainer.oFooter.oDatetime.domElement.addClassName('reflex-datepicker-datetime');
		this.oContainer.oFooter.domElement.appendChild(this.oContainer.oFooter.oDatetime.domElement);
		
		this.oContainer.oFooter.oDatetime.oDate				= {};
		this.oContainer.oFooter.oDatetime.oDate.domElement	= document.createElement('div');
		this.oContainer.oFooter.oDatetime.oDate.domElement.addClassName('reflex-datepicker-datetime-date');
		this.oContainer.oFooter.oDatetime.domElement.appendChild(this.oContainer.oFooter.oDatetime.oDate.domElement);
		
		this.oContainer.oFooter.oDatetime.oTime				= {};
		this.oContainer.oFooter.oDatetime.oTime.domElement	= document.createElement('div');
		this.oContainer.oFooter.oDatetime.oTime.domElement.addClassName('reflex-datepicker-datetime-time');
		this.oContainer.oFooter.oDatetime.domElement.appendChild(this.oContainer.oFooter.oDatetime.oTime.domElement);
		
		this.oContainer.oFooter.oDatetime.oTime.oHour							= {};
		this.oContainer.oFooter.oDatetime.oTime.oHour.domElement				= document.createElement('input');
		this.oContainer.oFooter.oDatetime.oTime.oHour.domElement.type			= 'text';
		this.oContainer.oFooter.oDatetime.oTime.oHour.domElement.size			= 2;
		this.oContainer.oFooter.oDatetime.oTime.oHour.domElement.maxLength		= 2;
		this.oContainer.oFooter.oDatetime.oTime.domElement.appendChild(this.oContainer.oFooter.oDatetime.oTime.oHour.domElement);
		
		var	domColon		= document.createElement('span');
		domColon.innerHTML	= ':';
		this.oContainer.oFooter.oDatetime.oTime.domElement.appendChild(domColon);
		
		this.oContainer.oFooter.oDatetime.oTime.oMinute							= {};
		this.oContainer.oFooter.oDatetime.oTime.oMinute.domElement				= document.createElement('input');
		this.oContainer.oFooter.oDatetime.oTime.oMinute.domElement.type			= 'text';
		this.oContainer.oFooter.oDatetime.oTime.oMinute.domElement.size			= 2;
		this.oContainer.oFooter.oDatetime.oTime.oMinute.domElement.maxLength	= 2;
		this.oContainer.oFooter.oDatetime.oTime.domElement.appendChild(this.oContainer.oFooter.oDatetime.oTime.oMinute.domElement);
		
		var	domColon		= document.createElement('span');
		domColon.innerHTML	= ':';
		this.oContainer.oFooter.oDatetime.oTime.domElement.appendChild(domColon);
		
		this.oContainer.oFooter.oDatetime.oTime.oSecond						= {};
		this.oContainer.oFooter.oDatetime.oTime.oSecond.domElement			= document.createElement('input');
		this.oContainer.oFooter.oDatetime.oTime.oSecond.domElement.type		= 'text';
		this.oContainer.oFooter.oDatetime.oTime.oSecond.domElement.size		= 2;
		this.oContainer.oFooter.oDatetime.oTime.oSecond.domElement.maxLength	= 2;
		this.oContainer.oFooter.oDatetime.oTime.domElement.appendChild(this.oContainer.oFooter.oDatetime.oTime.oSecond.domElement);
		
		this.oContainer.oFooter.oNow						= {};
		this.oContainer.oFooter.oNow.domElement				= document.createElement('button');
		this.oContainer.oFooter.oNow.domElement.innerHTML	= 'Now';
		this.oContainer.oFooter.oNow.domElement.observe('click', this.setDatetime.bind(this, 'now'));
		this.oContainer.oFooter.domElement.appendChild(this.oContainer.oFooter.oNow.domElement);
		
		document.body.appendChild(this.oContainer.domElement);
		
		// Temporary
		this.oContainer.domElement.style.position	= 'fixed';
		this.oContainer.domElement.style.top		= '25%';
		this.oContainer.domElement.style.left		= '25%';
		
		// Defaults
		this.oSetDateHandlers	= {};
		
		this.aDayMutatorCallbacks	= [Reflex_Date_Picker.dayMutators.isInCurrentMonth, Reflex_Date_Picker.dayMutators.isToday, Reflex_Date_Picker.dayMutators.isSelected.curry(this)];
		
		this.iMonthsVisible		= Reflex_Date_Picker.DEFAULT_MONTHS_VISIBLE;
		this.iMonthsPerRow		= Reflex_Date_Picker.DEFAULT_MONTHS_PER_ROW;
		this.iFirstDayOfWeek	= Reflex_Date_Picker.DEFAULT_START_OF_WEEK;
		this.setDatetime();
	},
	
	setPosition	: function(sPositionType, oConfig)
	{
		
	},
	
	setSelectMode	: function(mMode)
	{
		var	oCurrentDate	= this.getDate();
		switch (mMode)
		{
			case Reflex_Date_Picker.SELECT_MODE_DATE_TIME:
				// Switch Now/Today Buttons
				this.oContainer.oFooter.oNow.domElement.innerHTML	= 'Today';
				
				// Show Time input
				this.oContainer.oFooter.oTime.domElement.show();
				
				this.sSelectMode	= Reflex_Date_Picker.SELECT_MODE_DATE_TIME;
				break;
				
			case Reflex_Date_Picker.SELECT_MODE_DATE:
			default:
				// Reset the time component of the selected Date
				this.setTime(0, 0, 0);
				
				// Switch Now/Today Buttons
			this.oContainer.oFooter.oNow.domElement.innerHTML	= 'Now';
				
				// Hide Time input
				this.oContainer.oFooter.oTime.domElement.hide();
				
				this.sSelectMode	= Reflex_Date_Picker.SELECT_MODE_DATE;
				break;
		}
	},
	
	setDateChangeCallback	: function(fnCallback)
	{
		if (typeof fnCallback === 'function')
		{
			this.fnDateChangeCallback	= fnCallback;
		}
		else
		{
			throw "fnCallback is not a Function!";
		}
	},
	
	setMonthsVisible	: function(iMonths, iMonthsPerRow)
	{
		this.iMonthsVisible	= Math.abs(parseInt(iMonths));
		
		iMonthsPerRow		= Math.abs(parseInt(iMonthsPerRow));
		this.iMonthsPerRow	= (iMonthsPerRow > 0) ? iMonthsPerRow : 1;
		
		// Re-render
		this._renderDatePicker();
	},
	
	setDate	: function(iYear, iMonth, iDay)
	{
		var	oCurrentDatetime	= this.getDate();
		this.setDatetime(new Date(iYear, iMonth, iDay, oCurrentDatetime.getHours(), oCurrentDatetime.getMinutes(), oCurrentDatetime.getSeconds()));
	},
	
	setTimeFromSlider	: function(oValues)
	{
		if (this.sSelectMode !== Reflex_Date_Picker.SELECT_MODE_DATE)
		{
			var	oCurrentDatetime	= new Date(this.getDate());
			oCurrentDatetime.setHours(0);
			oCurrentDatetime.setMinutes(0);
			oCurrentDatetime.setSeconds(0);
			oCurrentDatetime.setSeconds(oValues.iStartValue);
			this.setDatetime(oCurrentDatetime);
		}
	},
	
	setTime	: function(iHours, iMinutes, iSeconds)
	{
		var	oCurrentDatetime	= this.getDate();
		this.setDatetime(new Date(oCurrentDatetime.getFullYear(), oCurrentDatetime.getMonth(), oCurrentDatetime.getDay(), iHours, iMinutes, iSeconds));
	},
	
	setDatetime	: function(mDate)
	{
		this.oDate	= (!mDate || (mDate.toLowerCase && mDate.toLowerCase() === 'now')) ? new Date() : new Date(mDate);
		//$Alert("Date has now been set to " + Reflex_Date_Format.format("Y-m-d H:i:s", mDate));
		
		// If this is a Date only, then zero-out the time component
		switch (this.sSelectMode)
		{
			case Reflex_Date_Picker.SELECT_MODE_DATE:
				this.oDate.setHours(0);
				this.oDate.setMinutes(0);
				this.oDate.setSeconds(0);
				break;
			
			case Reflex_Date_Picker.SELECT_MODE_DATE_TIME:
				this.oContainer.oContent.oTimePicker.oSlider.setValues(
																		(this.oDate.getHours() * 60 * 60) + 
																		(this.oDate.getMinutes() * 60) + 
																		this.oDate.getSeconds() 
																	);
				break;
		}
		
		// Update Label
		//this.oContainer.oHeader.oLabel.domElement.innerHTML	= Reflex_Date_Format.format("l, j F Y H:i:s", this.oDate);
		this.oContainer.oFooter.oDatetime.oDate.domElement.innerHTML	= Reflex_Date_Format.format("l, j F Y H:i:s ", this.oDate);
		//this.oContainer.oFooter.oDatetime.oDate.domElement.innerHTML	= Reflex_Date_Format.format("l, j F Y ", this.oDate);
		
		// Update calendar
		this._renderDatePicker(this.oDate);
		
		// Callback
		if (typeof this.fnDateChangeCallback === 'function')
		{
			this.fnDateChangeCallback(this.oDate);
		}
	},
	
	getDate	: function()
	{
		return this.oDate;
	},
	
	getElement	: function()
	{
		return this.oContainer.domElement;
	},
	
	_renderDatePicker	: function(oFocusDate)
	{
		oFocusDate	= (!oFocusDate) ? this.getDate() : oFocusDate;
		
		// Purge all children
		this.oContainer.oContent.oDatePicker.domElement.childElements().invoke('remove');
		
		// Remove all Day Event Handlers
		for (sFormattedDate in this.oSetDateHandlers)
		{
			delete this.oSetDateHandlers[sFormattedDate];
		}
		
		// Render each visible month
		var iFocusMonthIndex	= Math.ceil(this.iMonthsVisible / 2) - 1;
		var oVisibleMonth		= new Date(oFocusDate);
		oVisibleMonth.shift(0 - iFocusMonthIndex, Date.DATE_INTERVAL_MONTH);
		
		var oCurrentRow			= document.createElement('div');
		this.oContainer.oContent.oDatePicker.domElement.appendChild(oCurrentRow);
		for (var i = 0; i < this.iMonthsVisible; i++)
		{
			if ((i % this.iMonthsPerRow === 0) && i !== 0)
			{
				// Create a new Row
				oCurrentRow	= document.createElement('div');
				this.oContainer.oContent.oDatePicker.domElement.appendChild(oCurrentRow);
			}
			
			oCurrentRow.appendChild(this._renderMonthView(oVisibleMonth.getMonth() + 1, oVisibleMonth.getFullYear()).domElement);
			oVisibleMonth.shift(1, Date.DATE_INTERVAL_MONTH);
		}
	},
	
	show	: function()
	{
		this._renderDatePicker();
		this.getElement().show();
	},
	
	hide	: function()
	{
		this.getElement().hide();
	},
	
	_renderMonthView	: function(iMonth, iYear)
	{
		// Containers
		var oContainer			= {};
		oContainer.domElement	= document.createElement('div');
		oContainer.domElement.addClassName('month');
		
		oContainer.oGrid			= {};
		oContainer.oGrid.domElement	= document.createElement('table');
		oContainer.domElement.appendChild(oContainer.oGrid.domElement);
		
		// Create Table innards
		//------------------------------------------------------------------------//
		var oMonthDate		= new Date(iYear, iMonth - 1, 1);
		var iDaysInMonth	= parseInt(Reflex_Date_Format.format('t', oMonthDate));
		
		// Month Label/Header
		oContainer.oGrid.oCaption				= {};
		oContainer.oGrid.oCaption.domElement	= document.createElement('caption');
		oContainer.oGrid.domElement.appendChild(oContainer.oGrid.oCaption.domElement);
		
		oContainer.oGrid.oCaption.oMonthPrevious			= {};
		oContainer.oGrid.oCaption.oMonthPrevious.domElement	= document.createElement('div');
		oContainer.oGrid.oCaption.oMonthPrevious.domElement.addClassName('month-previous');
		
		oContainer.oGrid.oCaption.oMonthYear			= {};
		oContainer.oGrid.oCaption.oMonthYear.domElement	= document.createElement('div');
		oContainer.oGrid.oCaption.domElement.appendChild(oContainer.oGrid.oCaption.oMonthYear.domElement);
		
		oContainer.oGrid.oCaption.oMonthYear.oMonth							= {};
		oContainer.oGrid.oCaption.oMonthYear.oMonth.domElement				= document.createElement('span');
		oContainer.oGrid.oCaption.oMonthYear.oMonth.domElement.innerHTML	= Reflex_Date_Format.format('F', oMonthDate);
		oContainer.oGrid.oCaption.oMonthYear.oMonth.domElement.addClassName('month-name');
		oContainer.oGrid.oCaption.oMonthYear.domElement.appendChild(oContainer.oGrid.oCaption.oMonthYear.oMonth.domElement);
		
		oContainer.oGrid.oCaption.oMonthYear.oYear						= {};
		oContainer.oGrid.oCaption.oMonthYear.oYear.domElement			= document.createElement('span');
		oContainer.oGrid.oCaption.oMonthYear.oYear.domElement.innerHTML	= Reflex_Date_Format.format('Y', oMonthDate);
		oContainer.oGrid.oCaption.oMonthYear.oYear.domElement.addClassName('month-year');
		oContainer.oGrid.oCaption.oMonthYear.domElement.appendChild(oContainer.oGrid.oCaption.oMonthYear.oYear.domElement);
		
		oContainer.oGrid.oCaption.oMonthYear.oSelectButton					= {};
		oContainer.oGrid.oCaption.oMonthYear.oSelectButton.domElement		= document.createElement('span');
		oContainer.oGrid.oCaption.oMonthYear.oSelectButton.domElement.addClassName('month-select');
		oContainer.oGrid.oCaption.oMonthYear.domElement.appendChild(oContainer.oGrid.oCaption.oMonthYear.oSelectButton.domElement);
		
		oContainer.oGrid.oCaption.oMonthNext			= {};
		oContainer.oGrid.oCaption.oMonthNext.domElement	= document.createElement('div');
		oContainer.oGrid.oCaption.oMonthNext.domElement.addClassName('month-next');
		
		// Days in Week
		oContainer.oGrid.oHeader			= {};
		oContainer.oGrid.oHeader.domElement	= document.createElement('thead');
		oContainer.oGrid.domElement.appendChild(oContainer.oGrid.oHeader.domElement);
		
		oContainer.oGrid.oHeader.oRow				= {};
		oContainer.oGrid.oHeader.oRow.domElement	= document.createElement('tr');
		oContainer.oGrid.oHeader.oRow.domElement.addClassName('days');
		oContainer.oGrid.oHeader.domElement.appendChild(oContainer.oGrid.oHeader.oRow.domElement);
		
		for (var iDayOfWeek = 0; iDayOfWeek < 7; iDayOfWeek++)
		{
			var domDay			= document.createElement('th');
			domDay.innerHTML	= (Reflex_Date_Format.oDays.oShortNames[(iDayOfWeek + this.iFirstDayOfWeek) % 7]).substr(0, 2);
			
			oContainer.oGrid.oHeader.oRow.domElement.appendChild(domDay);
		}
		
		// Dates
		oContainer.oGrid.oBody				= {};
		oContainer.oGrid.oBody.domElement	= document.createElement('tbody');
		oContainer.oGrid.domElement.appendChild(oContainer.oGrid.oBody.domElement);
		
		var oDateOfMonth	= new Date(iYear, iMonth - 1, 1);
		var iWeeks			= 0;
		while (iWeeks < Reflex_Date_Picker.MINIMUM_WEEKS_IN_MONTH)
		//while (oDateOfMonth.getDate() <= iDaysInMonth && oDateOfMonth.getMonth() === oMonthDate.getMonth())
		{
			// Add a new row for this week
			iWeeks++;
			oContainer.oGrid.oBody.oRow				= {};
			oContainer.oGrid.oBody.oRow.domElement	= document.createElement('tr');
			oContainer.oGrid.oBody.oRow.domElement.addClassName('week');
			oContainer.oGrid.oBody.domElement.appendChild(oContainer.oGrid.oBody.oRow.domElement);
			
			// Add each day
			for (var iDayOfWeek = this.iFirstDayOfWeek; iDayOfWeek < (this.iFirstDayOfWeek + 7); iDayOfWeek++)
			{
				var domDay		= document.createElement('td');
				domDay.id		= this.sUID + '_' + Reflex_Date_Format.format("Ymd", oDateOfMonth);
				
				// If the Day of the Month is the current day of the week, then add
				if (oDateOfMonth.getDay() === (iDayOfWeek % 7) && oDateOfMonth.getMonth() === oMonthDate.getMonth())
				{
					var domDaySpan	= document.createElement('span');
					domDay.appendChild(domDaySpan);
					
					//domDay.addClassName('day');
					//alert("Adding Cell for " + oDateOfMonth);
					
					var sFormattedDate	= Reflex_Date_Format.format("Y-m-d", oDateOfMonth.getFullYear(), oDateOfMonth.getMonth(), oDateOfMonth.getDay());
					this.oSetDateHandlers[sFormattedDate]	= this.setDate.bind(this, new Date(oDateOfMonth));
					
					// Add Event Listener
					domDay.addEventListener('click', this.oSetDateHandlers[sFormattedDate], false);
					
					// Set Cell Contents
					domDaySpan.innerHTML	= oDateOfMonth.getDate();
					domDay.addClassName('selectable');
					
					// Day Mutator Callbacks
					for (var i = 0; i < this.aDayMutatorCallbacks.length; i++)
					{
						var oResponse	= this.aDayMutatorCallbacks[i](oDateOfMonth, oMonthDate);
						
						// Add additional CSS Class
						if (oResponse.sCSSClass)
						{
							domDay.addClassName(oResponse.sCSSClass);
						}
						
						// Remove onClick (once removed, it cannot be re-added)
						if (oResponse.bSelectable === false)
						{
							domDay.removeEventListener('click', this.oSetDateHandlers[sFormattedDate], false);
							domDay.removeClassName('selectable');
						}
					}
					
					// Increment Date
					oDateOfMonth.shift(1, Date.DATE_INTERVAL_DAY);
				}
				
				// Add Cell to the Grid
				oContainer.oGrid.oBody.oRow.domElement.appendChild(domDay);
			}
		}
		//------------------------------------------------------------------------//
		
		return oContainer;
	}
});

// Status Methods
Reflex_Date_Picker.dayMutators	= {};

Reflex_Date_Picker.dayMutators.isSelected	= function(oReflexDatePicker, oDate)
{
	var oSelectedDate	= oReflexDatePicker.getDate();
	var bEligible		= (oDate.getDate() === oSelectedDate.getDate() && oDate.getMonth() === oSelectedDate.getMonth() && oDate.getFullYear() === oSelectedDate.getFullYear());
	return	{
				bSelectable	: null,
				sCSSClass	: bEligible ? 'reflex-datepicker-day-selected' : null
			};
};


Reflex_Date_Picker.dayMutators.isToday	= function(oDate)
{
	var oCurrentDate	= new Date();
	var bEligible		= (oDate.getDate() === oCurrentDate.getDate() && oDate.getMonth() === oCurrentDate.getMonth() && oDate.getFullYear() === oCurrentDate.getFullYear());
	return	{
				bSelectable	: null,
				sCSSClass	: bEligible ? 'reflex-datepicker-day-today' : null
			};
};

Reflex_Date_Picker.dayMutators.isInCurrentMonth	= function(oDate, oMonthDate)
{
	var bEligible		= (oDate.getMonth() === oMonthDate.getMonth() && oDate.getFullYear() === oMonthDate.getFullYear());
	return	{
				bSelectable	: bEligible ? null : false,
				sCSSClass	: bEligible ? 'day' : null
			};
};

Reflex_Date_Picker.dayMutators.isWeekend	= function(oDate)
{
	var oCurrentDate	= new Date();
	var bEligible		= ([0, 6].indexOf(oDate.getDay()) > -1);
	return	{
				bSelectable	: null,
				sCSSClass	: bEligible ? 'reflex-datepicker-day-weekend' : null
			};
};

Reflex_Date_Picker.dayMutators.setWeekendInvalid	= function(oDate)
{
	var oCurrentDate	= new Date();
	var oIsWeekend		= Reflex_Date_Picker.dayMutators.isWeekend(oDate);
	return	{
				bSelectable	: oIsWeekend.sCSSClass ? false : null,
				sCSSClass	: oIsWeekend.sCSSClass
			};
};

// Class Constants
Reflex_Date_Picker.SELECT_MODE_DATE				= 'date';
Reflex_Date_Picker.SELECT_MODE_DATE_TIME		= 'datetime';

Reflex_Date_Picker.DEFAULT_START_OF_WEEK	= 1;	// Monday
Reflex_Date_Picker.DEFAULT_MONTHS_VISIBLE	= 3;
Reflex_Date_Picker.DEFAULT_MONTHS_PER_ROW	= 3;

Reflex_Date_Picker.MINIMUM_WEEKS_IN_MONTH	= 6;
