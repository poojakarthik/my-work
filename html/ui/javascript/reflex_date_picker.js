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
		
		// Footer
		this.oContainer.oFooter				= {};
		this.oContainer.oFooter.domElement	= document.createElement('div');
		this.oContainer.oFooter.domElement.addClassName('footer');
		this.oContainer.domElement.appendChild(this.oContainer.oFooter.domElement);
		
		this.oContainer.oFooter.oTime				= {};
		this.oContainer.oFooter.oTime.domElement	= document.createElement('div');
		this.oContainer.oFooter.oTime.domElement.addClassName('time');
		this.oContainer.oFooter.domElement.appendChild(this.oContainer.oFooter.oTime.domElement);
		
		this.oContainer.oFooter.oTime.oHour							= {};
		this.oContainer.oFooter.oTime.oHour.domElement				= document.createElement('input');
		this.oContainer.oFooter.oTime.oHour.domElement.type			= 'text';
		this.oContainer.oFooter.oTime.oHour.domElement.size			= 2;
		this.oContainer.oFooter.oTime.oHour.domElement.maxLength	= 2;
		this.oContainer.oFooter.oTime.domElement.appendChild(this.oContainer.oFooter.oTime.oHour.domElement);
		
		var	domColon		= document.createElement('span');
		domColon.innerHTML	= ':';
		this.oContainer.oFooter.oTime.domElement.appendChild(domColon);
		
		this.oContainer.oFooter.oTime.oMinute						= {};
		this.oContainer.oFooter.oTime.oMinute.domElement			= document.createElement('input');
		this.oContainer.oFooter.oTime.oMinute.domElement.type		= 'text';
		this.oContainer.oFooter.oTime.oMinute.domElement.size		= 2;
		this.oContainer.oFooter.oTime.oMinute.domElement.maxLength	= 2;
		this.oContainer.oFooter.oTime.domElement.appendChild(this.oContainer.oFooter.oTime.oMinute.domElement);
		
		var	domColon		= document.createElement('span');
		domColon.innerHTML	= ':';
		this.oContainer.oFooter.oTime.domElement.appendChild(domColon);
		
		this.oContainer.oFooter.oTime.oSecond						= {};
		this.oContainer.oFooter.oTime.oSecond.domElement			= document.createElement('input');
		this.oContainer.oFooter.oTime.oSecond.domElement.type		= 'text';
		this.oContainer.oFooter.oTime.oSecond.domElement.size		= 2;
		this.oContainer.oFooter.oTime.oSecond.domElement.maxLength	= 2;
		this.oContainer.oFooter.oTime.domElement.appendChild(this.oContainer.oFooter.oTime.oSecond.domElement);
		
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
		this.iMonthsVisible		= Reflex_Date_Picker.DEFAULT_MONTHS_VISIBLE;
		this.iMonthsPerRow		= Reflex_Date_Picker.DEFAULT_MONTHS_PER_ROW;
		this.iFirstDayOfWeek	= Reflex_Date_Picker.DEFAULT_START_OF_WEEK;
		this.oDate				= new Date();
		
		this.oSetDateHandlers	= {};
		
		this.aDayMutatorCallbacks	= [Reflex_Date_Picker.dayMutators.isInCurrentMonth, Reflex_Date_Picker.dayMutators.isToday];
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
		
		iMonthsPerRow	= Math.abs(parseInt(iMonthsPerRow));
		if (iMonthsPerRow)
		{
			this.iMonthsPerRow	= iMonthsPerRow;
		}
		
		// Re-render
		this._render();
	},
	
	setDate	: function(iYear, iMonth, iDay)
	{
		var	oCurrentDatetime	= this.getDate();
		this.setDatetime(new Date(iYear, iMonth, iDay, oCurrentDatetime.getHours(), oCurrentDatetime.getMinutes(), oCurrentDatetime.getSeconds()));
	},
	
	setTime	: function(iHours, iMinutes, iSeconds)
	{
		var	oCurrentDatetime	= this.getDate();
		this.setDatetime(new Date(oCurrentDatetime.getFullYear(), oCurrentDatetime.getMonth(), oCurrentDatetime.getDay(), iHours, iMinutes, iSeconds));
	},
	
	setDatetime	: function(mDate)
	{
		this.oDate	= (!mDate || mDate.toLowerCase() === 'now') ? new Date() : new Date(mDate);
		//$Alert("Date has now been set to " + Reflex_Date_Format.format("Y-m-d H:i:s", mDate));
		
		// If this is a Date only, then zero-out the time component
		switch (this.sSelectMode)
		{
			case Reflex_Date_Picker.SELECT_MODE_DATE:
				this.oDate.setHours(0);
				this.oDate.setMinutes(0);
				this.oDate.setSeconds(0);
				break;
		}
		
		// Update Label
		this.oContainer.oHeader.oLabel.domElement.innerHTML	= Reflex_Date_Format.format("l, j F Y H:i:s", oDate);
		
		// Update calendar
		this._render(this.oDate);
		
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
	
	_render	: function(oFocusDate)
	{
		oFocusDate	= (!oFocusDate) ? this.getDate() : oFocusDate;
		
		// Purge all children
		this.oContainer.oContent.domElement.childElements().invoke('remove');
		
		// Remove all Day Event Handlers
		for (sFormattedDate in this.oSetDateHandlers)
		{
			delete this.oSetDateHandlers[sFormattedDate];
		}
		
		// Render each visible month
		var iFocusMonthIndex	= Math.ceil(this.iMonthsVisible / 2);
		var oVisibleMonth		= new Date(oFocusDate);
		oVisibleMonth.shift(-1 - iFocusMonthIndex, Date.DATE_INTERVAL_MONTH);

		var oCurrentRow			= document.createElement('div');
		this.oContainer.oContent.domElement.appendChild(oCurrentRow);
		for (var i = 1; i <= this.iMonthsVisible; i++)
		{
			if (((i - 1) % this.iMonthsPerRow === 0) && i != 1)
			{
				// Create a new Row
				oCurrentRow	= document.createElement('div');
				this.oContainer.oContent.domElement.appendChild(oCurrentRow);
			}
			
			oVisibleMonth.shift(1, Date.DATE_INTERVAL_MONTH);
			oCurrentRow.appendChild(this._renderMonthView(oVisibleMonth.getMonth() + 1, oVisibleMonth.getFullYear()).domElement);
		}

		// Add in a clearing
		var oClearing	= document.createElement('div');
		oClearing.addClassName('clear');
		this.oContainer.oContent.domElement.appendChild(oClearing);
	},
	
	show	: function()
	{
		this._render();
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
		oContainer.oGrid.oHeader.oRow.domElement.addClassName();
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
		while (oDateOfMonth.getDate() <= iDaysInMonth && oDateOfMonth.getMonth() === oMonthDate.getMonth())
		{
			// Add a new row for this week
			oContainer.oGrid.oBody.oRow				= {};
			oContainer.oGrid.oBody.oRow.domElement	= document.createElement('tr');
			oContainer.oGrid.oBody.oRow.domElement.addClassName('week');
			oContainer.oGrid.oBody.domElement.appendChild(oContainer.oGrid.oBody.oRow.domElement);
			
			// Add each day
			for (var iDayOfWeek = this.iFirstDayOfWeek; iDayOfWeek < (this.iFirstDayOfWeek + 7); iDayOfWeek++)
			{
				var domDay		= document.createElement('td');
				domDay.id		= this.sUID + '_' + Reflex_Date_Format.format("Ymd", oDateOfMonth);
				//domDay.addClassName('day');
				
				// If the Day of the Month is the current day of the week, then add
				if (oDateOfMonth.getDay() === (iDayOfWeek % 7) && oDateOfMonth.getMonth() === oMonthDate.getMonth())
				{
					//alert("Adding Cell for " + oDateOfMonth);
					
					var sFormattedDate	= Reflex_Date_Format.format("Y-m-d", oDateOfMonth);
					this.oSetDateHandlers[sFormattedDate]	= this.setDatetime.bind(this, new Date(oDateOfMonth));
					
					// Add Event Listener
					domDay.addEventListener('click', this.oSetDateHandlers[sFormattedDate], false);
					
					// Set Cell Contents
					domDay.innerHTML	= oDateOfMonth.getDate();
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