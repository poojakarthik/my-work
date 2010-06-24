
var Component_Date_Picker	= Class.create(
{
	initialize	: function(oDate, bTimePicker, iStartYear, iEndYear, sDateFormat, fnOnChange)
	{
		this._oDate			= oDate;
		this._bTimePicker	= bTimePicker;
		this._iStartYear	= iStartYear;
		this._iEndYear		= iEndYear;
		
		if (sDateFormat)
		{
			this._sDateFormat	= sDateFormat;
		}
		else if (this._bTimePicker)
		{
			this._sDateFormat	= Component_Date_Picker.DEFAULT_FORMAT_DATE_TIME;
		}
		else
		{
			this._sDateFormat	= Component_Date_Picker.DEFAULT_FORMAT_DATE;
		}
		
		this._fnOnChange	= fnOnChange;
		this._bFloating		= false;
		
		this._createDatePicker();
		this._createCalendar();
		
		if (this._bTimePicker)
		{
			this._createTimePicker();
		}
	},
	
	//
	// Public functions
	//
	
	getElement	: function()
	{
		return this._oContainerDiv;
	},
	
	getDate	: function()
	{
		return this._oDate;
	},
	
	getFormattedDateString	: function()
	{
		return this._oDate.$format(this._sDateFormat);
	},
	
	hide	: function()
	{
		this._oContainerDiv.hide();
	},
	
	show	: function(mLocation)
	{
		// First... ensure that we've got a value
		if (!this._oDate)
		{
			this._oDate	= new Date();
		}
		
		var bPosition	= false;
		var iPositionX	= null;
		var iPositionY	= null;
		if (typeof mLocation != 'undefined')
		{
			if (mLocation.clientX && mLocation.clientY)
			{
				// Event object or object containing mouse coordinates
				var oCoords	= mLocation.pointer();
				iPositionX	= oCoords.x;
				iPositionY	= oCoords.y;
				bPosition	= true;
			}
			else if (mLocation.nodeName)
			{
				// An element to show next to
				var iValueT	= 0;
				var iValueL	= 0;
				var iWidth	= mLocation.offsetWidth;
				var iHeight	= mLocation.offsetHeight;
				do 
				{
					iValueT += mLocation.offsetTop || 0;
					iValueL += mLocation.offsetLeft || 0;
					mLocation = mLocation.offsetParent;
				} 
				while (mLocation);
				
				iPositionX	= iValueL + iWidth;
				iPositionY	= iValueT + iHeight;
				bPosition	= true;
			}
		}
		
		this._oContainerDiv.show();
		
		if (bPosition)
		{
			// Floating
			this._bFloating	= true;
			this._oUseDateImg.show();
			this._oCloseImg.show();
			
			if (this._bTimePicker)
			{
				this._oUseTimeImg.show();
			}
			
			// Attach to get dimensions
			this._oContainerDiv.style.visibility	= 'hidden';
			this._oContainerDiv.style.position		= 'absolute';
			this._oContainerDiv.style.zIndex		= Component_Date_Picker.Z_INDEX_FLOATING;
			this._oContainerDiv.style.left			= iPositionX + 'px';
			this._oContainerDiv.style.top			= iPositionY + 'px';
			
			if (this._oContainerDiv.up() != document.body)
			{
				document.body.appendChild(this._oContainerDiv);
			}
			
			// Ensure it hasn't left the screen
			var iWidth	= this._oContainerDiv.clientWidth;
			var iHeight	= this._oContainerDiv.clientHeight;
			var bChange	= false;
			
			if ((iPositionY + iHeight) >= (window.innerHeight + window.scrollY))
			{
				bChange		= true;
				iPositionY	-= iHeight;
			}
			
			if ((iPositionX + iWidth) >= window.innerWidth)
			{
				bChange		= true;
				iPositionX	-= iWidth;
			}
			
			this._oContainerDiv.style.visibility	= 'visible';
			
			if (bChange)
			{
				this._oContainerDiv.style.left	= iPositionX + 'px';
				this._oContainerDiv.style.top	= iPositionY + 'px';
			}
		}
		else
		{
			// Embedded
			this._bFloating	= false;
			this._oUseDateImg.hide();
			this._oCloseImg.hide();
			
			if (this._bTimePicker)
			{
				this._oUseTimeImg.hide();
			}
			
			// Position
			this._oContainerDiv.style.position	= 'relative';
			this._oContainerDiv.style.zIndex	= Component_Date_Picker.Z_INDEX_EMBEDDED;
		}
		
		this._showValue();
	},
	
	setTime	: function(iHour, iMinute) 
	{
		if (iHour && iMinute)
		{
			// Use given values
			this._oDate.setHours(iHour);
			this._oDate.setMinutes(iMinute);
		}
		else
		{
			// Use current values
			this._oDate.setHours(
				parseInt(this._getSelectValue(this._oHourSelect)) + parseInt(this._getSelectValue(this._oAMPMSelect))
			);
			this._oDate.setMinutes(parseInt(this._getSelectValue(this._oMinuteSelect)));
		}
	},
	
	setDate	: function(oDate)
	{
		this._oDate	= oDate;
		this._showValue();
	},
	
	clearDate	: function()
	{
		this._oDate	= null;
	},
	
	//
	// Private functions
	//
	
	// Creates the HTML needed for choosing the date
	_createDatePicker: function()
	{
		// Month select
		this._oMonthSelect	= $T.select();
		this._oMonthSelect.observe('change', this._dateChange.bind(this));
		
		var oOption	= null;
		for (var iMonIndex = 0; iMonIndex <= 11; iMonIndex++)
		{
			oOption	= 	$T.option({value: iMonIndex},
							this._oDate.$getMonthFullName(iMonIndex)
						);
			
			if (iMonIndex == this._oDate.getMonth())
			{
				oOption.selected	= true;
			}
			
			this._oMonthSelect.appendChild(oOption);
		}
		
		// Year select
		this._oYearSelect	= $T.select();
		this._oYearSelect.observe('change', this._dateChange.bind(this));
		
		for (var i = this._iStartYear; i <= this._iEndYear; ++i)
		{
			oOption	= 	$T.option({value: i},
							i
						);
			
			if (i == this._oDate.getFullYear())
			{
				oOption.selected	= true;
			}
			
			this._oYearSelect.appendChild(oOption);
		}
		
		// 'Use selected' icon
		var sUseDateAlt	= 'Use the selected date' + (this._bTimePicker ? ' & time' : '');
		var oUseDateImg	= $T.img({src: 'img/template/table_row_insert.png', alt: sUseDateAlt, title: sUseDateAlt});
		oUseDateImg.hide();
		oUseDateImg.observe('click', this._useSelectedDate.bind(this));
		this._oUseDateImg	= oUseDateImg;
		
		// Close icon
		var sCloseAlt	= 'Close the Date Picker';
		var oCloseImg	= $T.img({src: 'img/template/delete.png', alt: sCloseAlt, title: sCloseAlt});
		oCloseImg.hide();
		oCloseImg.observe('click', this.hide.bind(this));
		this._oCloseImg	= oCloseImg;

		this._oContainerDiv	= 	$T.div({class: 'component-date-picker select-free'},
									$T.div({class: 'bar'},
										this._oMonthSelect,
										' ',
										this._oYearSelect,
										' ',
										oUseDateImg,
										oCloseImg
									)
								);
	},

	// Creates the extra HTML needed for choosing the time
	_createTimePicker: function()
	{
		// Hour select
		this._oHourSelect 	= $T.select();
		this._oHourSelect.observe('change', this._dateChange.bind(this));
		
		var oOption	= null;
		for (var i = 1; i < 12; ++i)
		{
			oOption	= 	$T.option({value: i},
							i
						);
			
			if (i == (this._oDate.getHours() % 12))
			{
				oOption.selected	= true;
			}
			
			this._oHourSelect.appendChild(oOption);
		}

		// Add extra entry for 12:00
		oOption	= 	$T.option({value: 0},
						'12'
					);
		
		if ((this._oDate.getHours() == 0) || (this._oDate.getHours() == 12))
		{
			oOption.selected	= true;
		}
		
		this._oHourSelect.appendChild(oOption);
		
		// Minute select
		this._oMinuteSelect = $T.select();
		this._oMinuteSelect.observe('change', this._dateChange.bind(this));
		
		for (var i = 0; i < 60; ++i)
		{
			oOption	= 	$T.option({value: i},
							Component_Date_Picker.leftPadString(i, 2, '0')
						);
			
			if (i == this._oDate.getMinutes())
			{
				oOption.selected	= true;
			}
			
			this._oMinuteSelect.appendChild(oOption);
		}

		// AM/PM select
		this._oAMPMSelect 	= 	$T.select(
									$T.option({value: 0},
										'AM'
									),
									$T.option({value: 12},
										'PM'
									)
								);
		this._oAMPMSelect.observe('change', this._dateChange.bind(this));
		
		if (this._oDate.getHours() < 12)
		{
			// Select AM
			this._oAMPMSelect.options[0].selected	= true;
		}
		else
		{
			// Select PM
			this._oAMPMSelect.options[1].selected	= true;
		}
		
		var sUseTimeAlt	= 'Use the selected date & time'
		var oUseTimeImg	= $T.img({src: 'img/template/table_row_insert.png', alt: sUseTimeAlt, title: sUseTimeAlt});
		oUseTimeImg.hide();
		oUseTimeImg.observe('click', this._useSelectedDate.bind(this));
		this._oUseTimeImg	= oUseTimeImg;
		
		this._oContainerDiv.appendChild(
			$T.div({class: 'bar'},
				this._oHourSelect,
				this._oMinuteSelect,
				this._oAMPMSelect,
				' ',
				oUseTimeImg
			)
		);
	},

	// Creates the HTML for the actual calendar part of the chooser
	_createCalendar: function()
	{
		var oTable	= 	$T.table({cellspacing: 0, class: 'dateChooser'});
		var oRow	= oTable.insertRow(-1);
		oRow.insertCell(-1).appendChild(document.createTextNode("S"));
		oRow.insertCell(-1).appendChild(document.createTextNode("M"));
		oRow.insertCell(-1).appendChild(document.createTextNode("T"));
		oRow.insertCell(-1).appendChild(document.createTextNode("W"));
		oRow.insertCell(-1).appendChild(document.createTextNode("T"));
		oRow.insertCell(-1).appendChild(document.createTextNode("F"));
		oRow.insertCell(-1).appendChild(document.createTextNode("S"));
		
		// Fill up the days of the week until we get to the first day of the month
		oRow			= oTable.insertRow(-1);
		var iFirstDay 	= this._oDate.$getFirstDayOfMonth();
		var iLastDay 	= this._oDate.$getLastDayOfMonth();
		if (iFirstDay != 0)
		{
			var oCell 		= oRow.insertCell(-1);
			oCell.colSpan 	= iFirstDay;
			oCell.appendChild(document.createTextNode("\u00a0"));
		}

		// Fill in the days of the month
		var i 				= 0;
		var iDaysInMonth 	= this._oDate.$getDaysInMonth();
		var iSelectedDate 	= this._oDate.getDate();
		while (i < iDaysInMonth) {
			if (((i++ + iFirstDay) % 7) == 0)
			{
				oRow = oTable.insertRow(-1);
			}
			
			var oCell			= oRow.insertCell(-1);
			oCell.className		= "component-date-picker-active" + (i == iSelectedDate ? " component-date-picker-active-today" : "");
			oCell.iDayOfMonth	= i;
			oCell.observe('click', this._dayOfMonthSelected.bind(this, oCell));
			oCell.appendChild(document.createTextNode(i));
		}

		// Fill in any days after the end of the month
		if (iLastDay != 6) {
			var oCell		= oRow.insertCell(-1);
			oCell.colSpan	= (6 - iLastDay);
			oCell.appendChild(document.createTextNode("\u00a0"));
		}

		if (this._oDayGrid != undefined && this._oDayGrid != null)
		{
			this._oContainerDiv.replaceChild(oTable, this._oDayGrid);
		}
		else
		{
			this._oContainerDiv.appendChild(oTable);
		}

		this._oDayGrid = oTable;

		return oTable;
	},
	
	_updateSelectedCalendarDay	: function()
	{
		var aDayTDs			= this._oDayGrid.select('td.component-date-picker-active');
		var iCurrentDate	= this._oDate.getDate();
		var oTD				= null;
		var sTodayClass		= 'component-date-picker-active-today';
		for (var i = 0; i < aDayTDs.length; i++)
		{
			oTD	= aDayTDs[i];
			if (oTD.iDayOfMonth	== iCurrentDate)
			{
				if (!oTD.hasClassName(sTodayClass))
				{
					oTD.addClassName(sTodayClass);
				}
			}
			else
			{
				oTD.removeClassName(sTodayClass);
			}
		}
	},
	
	_dateChange	: function()
	{
		var oNewDate = 	new Date(
							this._getSelectValue(this._oYearSelect),
							this._getSelectValue(this._oMonthSelect),
							1
						);
		
		// Try to preserve the day of month (watch out for months with 31 days)
		oNewDate.setDate(
			Math.max(
				1, 
				Math.min(
					oNewDate.$getDaysInMonth(), 
					this._oDate.getDate()
				)
			)
		);
		
		this._oDate	= oNewDate;
		
		if (this._bTimePicker) {
			this.setTime();
		}
		
		this._showValue();
		this._executeChangeCallback(true);
	},
	
	_useSelectedDate	: function()
	{
		if (this._bFloating)
		{
			this._oDate.setMonth(this._getSelectValue(this._oMonthSelect));
			this._oDate.setFullYear(this._getSelectValue(this._oYearSelect));
			
			if (this._bTimePicker) {
				this.setTime();
			}
			
			this.hide();
			this._executeChangeCallback();
		}
	},
	
	_showValue	: function()
	{
		if (this._oYearSelect != null)
		{
			this._oYearSelect.selectedIndex	= this._oDate.getFullYear() - this._iStartYear;
		}
		
		if (this._oMonthSelect != null)
		{
			this._oMonthSelect.selectedIndex	= this._oDate.getMonth();
		}
		
		this._createCalendar();
		
		if (this._oHourSelect != null)
		{
			var iHours	= this._oDate.getHours() - 1;
			if (iHours < 0) 
			{
				iHours	+= 12;
			}
			
			this._oHourSelect.selectedIndex	= (iHours % 12);
		}
		
		if (this._oAMPMSelect != null)
		{
			this._oAMPMSelect.selectedIndex	= (this._oDate.getHours() < 12) ? 0 : 1;
		}
		
		if (this._oMinuteSelect != null)
		{
			this._oMinuteSelect.selectedIndex	= this._oDate.getMinutes();
		}
	},
	
	_dayOfMonthSelected	: function(oCell)
	{
		if (oCell.iDayOfMonth)
		{
			this._oDate.setDate(oCell.iDayOfMonth);
		}

		if (this._bFloating)
		{
			this.hide();			
		}
		else
		{
			this._showValue();
		}
		
		this._executeChangeCallback();
	},
	
	_executeChangeCallback	: function(bEmbeddedOnly)
	{
		if (this._fnOnChange && (!bEmbeddedOnly || (bEmbeddedOnly && !this._bFloating)))
		{
			this._fnOnChange(this._oDate);
		}
	},
	
	_getSelectValue	: function(oSelect)
	{
		return oSelect.options[oSelect.selectedIndex].value;
	}
});

Component_Date_Picker.Z_INDEX_FLOATING	= 999;
Component_Date_Picker.Z_INDEX_EMBEDDED	= 0;

Component_Date_Picker.DEFAULT_FORMAT_DATE_TIME	= 'Y-m-d H:i:s';
Component_Date_Picker.DEFAULT_FORMAT_DATE		= 'Y-m-d';

Component_Date_Picker.leftPadString	= function (sVal, iSize, sCh) 
{
	var sResult	= new String(sVal);
	if (sCh == null) 
	{
		sCh	= " ";
	}
	
	while (sResult.length < iSize) 
	{
		sResult	= sCh + sResult;
	}
	
	return sResult;
};

