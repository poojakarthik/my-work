var Reflex_Date_Picker	= Class.create
({
	initialize	: function()
	{
		// Unique Identifier	
		this.sUID	= 'reflex-date-picker_' + str_sha1(parseString(Date().getTime() * Math.random()));
		
		// Basic DOM Elements
		this.oContainer					= {};
		this.oContainer.domElement		= document.createElement('div');
		this.oContainer.domElement.id	= this.sUID;
		
		this.aDayMutatorCallbacks	= [Reflex_Date_Picker.dayMutators.isToday];
	},
	
	setPosition	: function(sPositionType, oConfig)
	{
		
	},
	
	setSelectMode	: function(mMode)
	{
		
	},
	
	setMonthsVisible	: function(iMonths)
	{
		this.iMonthsVisible	= Math.abs(parseInt(iMonths));
		
		// Re-render
		this._render();
	},
	
	setDate	: function(mDate)
	{
		
	},
	
	getDate	: function()
	{
		
	},
	
	getElement	: function()
	{
		
	},
	
	_render	: function()
	{
		
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
		
		oContainer.oGrid			= {};
		oContainer.oGrid.domElement	= document.createElement('table');
		
		// Create Table innards
		//------------------------------------------------------------------------//
		var oMonthDate		= new Date(iYear.toString()+'-'+iMonth.toPaddedString(2)+'-01');
		var iDaysInMonth	= parseInt(Reflex_Date_Format.format('t', oMonthDate));
		
		// Month Label/Header
		oContainer.oGrid.oCaption				= {};
		oContainer.oGrid.oCaption.domElement	= document.createElement('caption');
		
		oContainer.oGrid.oCaption.oMonthPrevious			= {};
		oContainer.oGrid.oCaption.oMonthPrevious.domElement	= document.createElement('div');
		oContainer.oGrid.oCaption.oMonthPrevious.domElement.addClassName('reflex-datepicker-month-previous');
		
		oContainer.oGrid.oCaption.oMonthYear			= {};
		oContainer.oGrid.oCaption.oMonthYear.domElement	= document.createElement('div');
		oContainer.oGrid.oCaption.oMonthYear.domElement.addClassName('reflex-datepicker-month');
		
		oContainer.oGrid.oCaption.oMonthYear.oMonth						= {};
		oContainer.oGrid.oCaption.oMonthYear.oMonth.domElement			= document.createElement('span');
		oContainer.oGrid.oCaption.oMonthYear.oMonth.domElement.value	= Reflex_Date_Format.format('F', oMonthDate);
		oContainer.oGrid.oCaption.oMonthYear.oMonth.domElement.addClassName('reflex-datepicker-month-name');
		
		oContainer.oGrid.oCaption.oMonthYear.oYear					= {};
		oContainer.oGrid.oCaption.oMonthYear.oYear.domElement		= document.createElement('span');
		oContainer.oGrid.oCaption.oMonthYear.oYear.domElement.value	= Reflex_Date_Format.format('Y', oMonthDate);
		oContainer.oGrid.oCaption.oMonthYear.oYear.domElement.addClassName('reflex-datepicker-month-year');
		
		oContainer.oGrid.oCaption.oMonthYear.oSelectButton					= {};
		oContainer.oGrid.oCaption.oMonthYear.oSelectButton.domElement		= document.createElement('span');
		oContainer.oGrid.oCaption.oMonthYear.oSelectButton.domElement.addClassName('reflex-datepicker-month-select');
		
		oContainer.oGrid.oCaption.oMonthNext			= {};
		oContainer.oGrid.oCaption.oMonthNext.domElement	= document.createElement('div');
		oContainer.oGrid.oCaption.oMonthNext.domElement.addClassName('reflex-datepicker-month-next');
		
		// Days in Week
		oContainer.oGrid.oHeader			= {};
		oContainer.oGrid.oHeader.domElement	= document.createElement('thead');
		
		oContainer.oGrid.oHeader.oRow				= {};
		oContainer.oGrid.oHeader.oRow.domElement	= document.createElement('tr');
		oContainer.oGrid.oHeader.domElement.appendChild(oContainer.oGrid.oHeader.oRow.domElement);
		
		for (var iDayOfWeek = 0; iDayOfWeek < 7; iDayOfWeek++)
		{
			var domDay		= document.createElement('th');
			domDay.value	= (Reflex_Date_Format.oDays.oShortNames[(iDayOfWeek + this.iFirstDayOfWeek) % 7]).substr(0, 2);
			
			oContainer.oGrid.oHeader.oRow.domElement.appendChild(domDay);
		}
		
		// Dates
		oContainer.oGrid.oBody				= {};
		oContainer.oGrid.oBody.domElement	= document.createElement('tbody');
		
		var oDateOfMonth	= new Date(iYear.toString()+'-'+iMonth.toPaddedString(2)+'-'+iMonth.toPaddedString(1));
		while (oDateOfMonth.getDate() <= iDaysInMonth)
		{
			// Add a new row for this week
			oContainer.oGrid.oBody.oRow				= {};
			oContainer.oGrid.oBody.oRow.domElement	= document.createElement('tr');
			oContainer.oGrid.oBody.domElement.appendChild(oContainer.oGrid.oHeader.oRow.domElement);
			
			// Add each day
			for (var iDayOfWeek = 0; iDayOfWeek < 7; iDayOffset++)
			{
				var domDay		= document.createElement('td');
				domDay.id		= this.sUID + '_' + Reflex_Date_Format.format("Ymd", oDateOfMonth);
				
				// If the Day of the Month is the current day of the week, then add
				if (oDateOfMonth.getDay() === iDayOfWeek)
				{
					// Add Event Listener
					domDay.addEventListener();
					
					// Set Cell Contents
					domDay.value	= (Reflex_Date_Format.oDays.oShortNames[(iDayOfWeek + this.iFirstDayOfWeek) % 7]).substr(0, 2);
					
					// Day Mutator Callbacks
					for (var i = 0; i < this.aDayMutatorCallbacks.length; i++)
					{
						var oResponse	= this.aDayMutatorCallbacks[i](oDateOfMonth);
						
						// Remove onClick (once removed, it cannot be re-added)
						if (oResponse.bSelectable === false)
						{
							domDay.removeEventListener();
						}
						
						// Add additional CSS Class
						if (oResponse.sCSSClass)
						{
							domDay.addClassName(oResponse.sCSSClass);
						}
					}
					
					// Increment Date
					oDateOfMonth.setDate(oDateOfMonth.getDate() + 1);
				}
				
				// Add Cell to the Grid
				oContainer.oGrid.oHeader.oRow.domElement.appendChild(domDay);
			}
		}
		//------------------------------------------------------------------------//
	}
});

// Status Methods
Reflex_Date_Picker.dayMutators	= {};

Reflex_Date_Picker.dayMutators.isToday	= function(oDate)
{
	var oCurrentDate	= Date();
	return	{
				bSelectable	: (oDate.getDate() == oCurrentDate.getDate() && oDate.getMonth() == oCurrentDate.getMonth() && oDate.getFullYear() == oCurrentDate.getFullYear()),
				sCSSClass	: 'reflex-datepicker-day-today'
			};
};

// Class Constants
Reflex_Date_Picker.MONTHS_PER_ROW	= 3;

Reflex_Date_Picker.SELECT_MODE_DATE			= 'date';
Reflex_Date_Picker.SELECT_MODE_DATE_TIME	= 'datetime';

Reflex_Date_Picker.DEFAULT_START_OF_WEEK	= 0;	// Sunday
Reflex_Date_Picker.DEFAULT_MONTHS_VISIBLE	= 1;