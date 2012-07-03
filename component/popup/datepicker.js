
var	Class	= require('../../class'),
	$D		= require('../../dom/factory'),
	Popup	= require('../popup');

var self = new Class({
	extends : Popup,

	construct : function() {
		this.CONFIG = Object.extend({
			oDate : {
				fnGetter : function(mValue) {
					return (mValue || new Date());
				}
			},
			bTimePicker : {},
			iYearStart 	: {},
			iYearEnd 	: {}
		}, this.CONFIG || {});
		
		this._bFloating 		= false;
		this._bInterfaceCreated	= false;
		
		this._super.apply(this, arguments);
		
		this.NODE.addClassName('fw-datepicker');
		this.NODE.addClassName('select-free');
		this.set('bModal', false);
	},
	
	_buildUI : function() {
		this._super();
		document.body.observe('mousedown', this._mouseDown.bind(this));
	},

	_mouseDown : function(oEvent) {
		if (this.NODE.visible() && this.NODE.up()) {
			var bHide	= true;
			var oTarget	= oEvent.target;
			while (oTarget && (oTarget !== document.body)) {
				if ((oTarget === this.NODE) || (oTarget.oFWComponent && (oTarget.oFWComponent instanceof Popup))) {
					bHide = false;
					break;
				}
				oTarget	= oTarget.parentNode;
			}
			
			if (bHide) {
				this.hide();
			}
		}
	},
	
	_syncUI : function() {
		if (!this._bInterfaceCreated) {
			this._createDatePicker();
			this._createCalendar();
			
			if (this.get('bTimePicker')) {
				this._createTimePicker();
			}
			
			this._bInterfaceCreated = true;
		}
		
		this._onReady();
	},
	
	//
	// Public functions
	//
	
	show : function(mLocation) {
		// First... ensure that we've got a value
		if (!this.get('oDate')) {
			this.set('oDate', new Date());
		}
		
		var bPosition	= false;
		var iPositionX	= null;
		var iPositionY	= null;
		if (typeof mLocation != 'undefined') {
			if (mLocation.clientX && mLocation.clientY) {
				// Event object or object containing mouse coordinates
				var oCoords	= mLocation.pointer();
				iPositionX	= oCoords.x;
				iPositionY	= oCoords.y;
				bPosition	= true;
			} else if (mLocation.nodeName) {
				// An element to show next to
				var iValueT	= 0;
				var iValueL	= 0;
				var iWidth	= mLocation.offsetWidth;
				var iHeight	= mLocation.offsetHeight;
				do {
					iValueT += mLocation.offsetTop || 0;
					iValueL += mLocation.offsetLeft || 0;
					mLocation = mLocation.offsetParent;
				} while (mLocation);
				
				iPositionX	= iValueL + iWidth;
				iPositionY	= iValueT + iHeight;
				bPosition	= true;
			}
		}
		
		this.display();
		
		if (bPosition) {
			// Floating
			this._bFloating	= true;
			this._oUseDateImg.show();
			this._oCloseImg.show();
			
			if (this.get('bTimePicker')) {
				this._oUseTimeImg.show();
			}
			
			// Attach to get dimensions
			this.NODE.style.visibility	= 'hidden';
			this.NODE.style.position	= 'absolute';
			this.NODE.style.zIndex		= self.Z_INDEX_FLOATING;
			this.NODE.style.left		= iPositionX + 'px';
			this.NODE.style.top			= iPositionY + 'px';
			
			if (this.NODE.up() != document.body) {
				document.body.appendChild(this.NODE);
			}
			
			// Ensure it hasn't left the screen
			var iWidth	= this.NODE.clientWidth;
			var iHeight	= this.NODE.clientHeight;
			var bChange	= false;
			
			if ((iPositionY + iHeight) >= (window.innerHeight + window.scrollY)) {
				bChange		= true;
				iPositionY	-= iHeight;
			}
			
			if ((iPositionX + iWidth) >= window.innerWidth) {
				bChange		= true;
				iPositionX	-= iWidth;
			}
			
			this.NODE.style.visibility	= 'visible';
			
			if (bChange) {
				this.NODE.style.left	= iPositionX + 'px';
				this.NODE.style.top		= iPositionY + 'px';
			}
		} else {
			// Embedded
			this._bFloating	= false;
			this._oUseDateImg.hide();
			this._oCloseImg.hide();
			
			if (this.get('bTimePicker')) {
				this._oUseTimeImg.hide();
			}
			
			// Position
			this.NODE.style.position	= 'relative';
			this.NODE.style.zIndex		= self.Z_INDEX_EMBEDDED;
		}
		
		this._showValue();
	},
	
	setTime	: function(iHour, iMinute) {
		var oDate = this.get('oDate');
		if (iHour && iMinute) {
			// Use given values
			oDate.setHours(iHour);
			oDate.setMinutes(iMinute);
		} else {
			// Use current values
			oDate.setHours(
				parseInt(this._getSelectValue(this._oHourSelect)) + parseInt(this._getSelectValue(this._oAMPMSelect))
			);
			oDate.setMinutes(parseInt(this._getSelectValue(this._oMinuteSelect)));
		}
		
		this.set('oDate', oDate);
	},
	
	//
	// Private functions
	//
	
	// Creates the HTML needed for choosing the date
	_createDatePicker: function() {
		// Month select
		this._oMonthSelect	= $D.select();
		this._oMonthSelect.observe('change', this._dateChange.bind(this));
		
		var oOption	= null;
		for (var iMonIndex = 0; iMonIndex <= 11; iMonIndex++) {
			oOption	= $D.option({value: iMonIndex},
				this.get('oDate').$getMonthFullName(iMonIndex)
			);
			
			if (iMonIndex == this.get('oDate').getMonth()) {
				oOption.selected = true;
			}
			
			this._oMonthSelect.appendChild(oOption);
		}
		
		// Year select
		this._oYearSelect = $D.select();
		this._oYearSelect.observe('change', this._dateChange.bind(this));
		
		for (var i = this.get('iYearStart'); i <= this.get('iYearEnd'); ++i) {
			oOption	= $D.option({value: i},
				i
			);
			
			if (i == this.get('oDate').getFullYear()) {
				oOption.selected = true;
			}
			
			this._oYearSelect.appendChild(oOption);
		}
		
		// 'Use selected' icon
		var sUseDateAlt	= 'Use the selected date' + (this.get('bTimePicker') ? ' & time' : '');
		var oUseDateImg	= $D.img({src: self.COMPLETE_ICON, alt: sUseDateAlt, title: sUseDateAlt});
		oUseDateImg.hide();
		oUseDateImg.observe('click', this._useSelectedDate.bind(this));
		this._oUseDateImg = oUseDateImg;
		
		// Close icon
		var sCloseAlt	= 'Close the Date Picker';
		var oCloseImg	= $D.img({src: self.CLOSE_ICON, alt: sCloseAlt, title: sCloseAlt});
		oCloseImg.hide();
		oCloseImg.observe('click', this.hide.bind(this));
		this._oCloseImg	= oCloseImg;

		this.getAttachmentNode().appendChild(
			$D.div({class: 'bar'},
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
	_createTimePicker: function() {
		// Hour select
		this._oHourSelect = $D.select();
		this._oHourSelect.observe('change', this._dateChange.bind(this));
		
		var oOption	= null;
		for (var i = 1; i < 12; ++i) {
			oOption	= $D.option({value: i},
				i
			);
			
			if (i == (this.get('oDate').getHours() % 12)) {
				oOption.selected = true;
			}
			
			this._oHourSelect.appendChild(oOption);
		}

		// Add extra entry for 12:00
		oOption	= $D.option({value: 0},
			'12'
		);
		
		if ((this.get('oDate').getHours() == 0) || (this.get('oDate').getHours() == 12)) {
			oOption.selected	= true;
		}
		
		this._oHourSelect.appendChild(oOption);
		
		// Minute select
		this._oMinuteSelect = $D.select();
		this._oMinuteSelect.observe('change', this._dateChange.bind(this));
		
		for (var i = 0; i < 60; ++i) {
			oOption	= $D.option({value: i},
				self.leftPadString(i, 2, '0')
			);
			
			if (i == this.get('oDate').getMinutes()) {
				oOption.selected = true;
			}
			
			this._oMinuteSelect.appendChild(oOption);
		}

		// AM/PM select
		this._oAMPMSelect = $D.select(
			$D.option({value: 0},
				'AM'
			),
			$D.option({value: 12},
				'PM'
			)
		);
		this._oAMPMSelect.observe('change', this._dateChange.bind(this));
		
		if (this.get('oDate').getHours() < 12) {
			// Select AM
			this._oAMPMSelect.options[0].selected = true;
		} else {
			// Select PM
			this._oAMPMSelect.options[1].selected = true;
		}
		
		var sUseTimeAlt	= 'Use the selected date & time'
		var oUseTimeImg	= $D.img({src: self.COMPLETE_ICON, alt: sUseTimeAlt, title: sUseTimeAlt});
		oUseTimeImg.hide();
		oUseTimeImg.observe('click', this._useSelectedDate.bind(this));
		this._oUseTimeImg = oUseTimeImg;
		
		this.getAttachmentNode().appendChild(
			$D.div({class: 'bar'},
				this._oHourSelect,
				this._oMinuteSelect,
				this._oAMPMSelect,
				' ',
				oUseTimeImg
			)
		);
	},

	// Creates the HTML for the actual calendar part of the chooser
	_createCalendar : function() {
		var oTable	= $D.table({cellspacing: 0, class: 'dateChooser'});
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
		var iFirstDay 	= this.get('oDate').$getFirstDayOfMonth();
		var iLastDay 	= this.get('oDate').$getLastDayOfMonth();
		if (iFirstDay != 0) {
			var oCell 		= oRow.insertCell(-1);
			oCell.colSpan 	= iFirstDay;
			oCell.appendChild(document.createTextNode("\u00a0"));
		}

		// Fill in the days of the month
		var i 				= 0;
		var iDaysInMonth 	= this.get('oDate').$getDaysInMonth();
		var iSelectedDate 	= this.get('oDate').getDate();
		while (i < iDaysInMonth) {
			if (((i++ + iFirstDay) % 7) == 0) {
				oRow = oTable.insertRow(-1);
			}
			
			var oCell			= oRow.insertCell(-1);
			oCell.className		= "fw-datepicker-active" + (i == iSelectedDate ? " fw-datepicker-active-today" : "");
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

		if (this._oDayGrid != undefined && this._oDayGrid != null) {
			this.getAttachmentNode().replaceChild(oTable, this._oDayGrid);
		} else {
			this.getAttachmentNode().appendChild(oTable);
		}

		this._oDayGrid = oTable;

		return oTable;
	},
	
	_updateSelectedCalendarDay	: function() {
		var aDayTDs			= this._oDayGrid.select('td.fw-datepicker-active');
		var iCurrentDate	= this.get('oDate').getDate();
		var oTD				= null;
		var sTodayClass		= 'fw-datepicker-active-today';
		for (var i = 0; i < aDayTDs.length; i++) {
			oTD	= aDayTDs[i];
			if (oTD.iDayOfMonth	== iCurrentDate) {
				if (!oTD.hasClassName(sTodayClass)) {
					oTD.addClassName(sTodayClass);
				}
			} else {
				oTD.removeClassName(sTodayClass);
			}
		}
	},
	
	_dateChange	: function() {
		var oNewDate = new Date(
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
					this.get('oDate').getDate()
				)
			)
		);
		
		this.set('oDate', oNewDate);
		
		if (this.get('bTimePicker')) {
			this.setTime();
		}
		
		this._showValue();
		this._executeChangeCallback(true);
	},
	
	_useSelectedDate : function() {
		if (this._bFloating) {
			var oDate = this.get('oDate');
			oDate.setMonth(this._getSelectValue(this._oMonthSelect));
			oDate.setFullYear(this._getSelectValue(this._oYearSelect));
			this.set('oDate', oDate);
			
			if (this.get('bTimePicker')) {
				this.setTime();
			}
			
			this.hide();
			this._executeChangeCallback();
		}
	},
	
	_showValue	: function() {
		if (this._oYearSelect != null) {
			this._oYearSelect.selectedIndex	= this.get('oDate').getFullYear() - this.get('iYearStart');
		}
		
		if (this._oMonthSelect != null) {
			this._oMonthSelect.selectedIndex	= this.get('oDate').getMonth();
		}
		
		this._createCalendar();
		
		if (this._oHourSelect != null) {
			var iHours	= this.get('oDate').getHours() - 1;
			if (iHours < 0) {
				iHours	+= 12;
			}
			
			this._oHourSelect.selectedIndex	= (iHours % 12);
		}
		
		if (this._oAMPMSelect != null) {
			this._oAMPMSelect.selectedIndex	= (this.get('oDate').getHours() < 12) ? 0 : 1;
		}
		
		if (this._oMinuteSelect != null) {
			this._oMinuteSelect.selectedIndex = this.get('oDate').getMinutes();
		}
	},
	
	_dayOfMonthSelected	: function(oCell) {
		if (oCell.iDayOfMonth) {
			var oDate = this.get('oDate');
			oDate.setDate(oCell.iDayOfMonth);
			this.set('oDate', oDate);
		}
		
		if (this._bFloating) {
			this.hide();
		} else {
			this._showValue();
		}
		
		this._executeChangeCallback();
	},
	
	_executeChangeCallback	: function(bEmbeddedOnly) {
		if (!bEmbeddedOnly || (bEmbeddedOnly && !this._bFloating)) {
			this.fire('change');
		}
	},
	
	_getSelectValue	: function(oSelect) {
		return oSelect.options[oSelect.selectedIndex].value;
	}
});

Object.extend(self, {
	Z_INDEX_FLOATING			: 999,
	Z_INDEX_EMBEDDED			: 0,
	DEFAULT_FORMAT_DATE_TIME	: 'Y-m-d H:i:s',
	DEFAULT_FORMAT_DATE			: 'Y-m-d',
	
	CLOSE_ICON		: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIhSURBVDjLlZPrThNRFIWJicmJz6BWiYbIkYDEG0JbBiitDQgm0PuFXqSAtKXtpE2hNuoPTXwSnwtExd6w0pl2OtPlrphKLSXhx07OZM769qy19wwAGLhM1ddC184+d18QMzoq3lfsD3LZ7Y3XbE5DL6Atzuyilc5Ciyd7IHVfgNcDYTQ2tvDr5crn6uLSvX+Av2Lk36FFpSVENDe3OxDZu8apO5rROJDLo30+Nlvj5RnTlVNAKs1aCVFr7b4BPn6Cls21AWgEQlz2+Dl1h7IdA+i97A/geP65WhbmrnZZ0GIJpr6OqZqYAd5/gJpKox4Mg7pD2YoC2b0/54rJQuJZdm6Izcgma4TW1WZ0h+y8BfbyJMwBmSxkjw+VObNanp5h/adwGhaTXF4NWbLj9gEONyCmUZmd10pGgf1/vwcgOT3tUQE0DdicwIod2EmSbwsKE1P8QoDkcHPJ5YESjgBJkYQpIEZ2KEB51Y6y3ojvY+P8XEDN7uKS0w0ltA7QGCWHCxSWWpwyaCeLy0BkA7UXyyg8fIzDoWHeBaDN4tQdSvAVdU1Aok+nsNTipIEVnkywo/FHatVkBoIhnFisOBoZxcGtQd4B0GYJNZsDSiAEadUBCkstPtN3Avs2Msa+Dt9XfxoFSNYF/Bh9gP0bOqHLAm2WUF1YQskwrVFYPWkf3h1iXwbvqGfFPSGW9Eah8HSS9fuZDnS32f71m8KFY7xs/QZyu6TH2+2+FAAAAABJRU5ErkJggg==',
	COMPLETE_ICON	: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAITSURBVDjLpVNLaBNRFD2TjIq/TdGM0OJGXPhBFENq0aUrtQYJCJaC4GdZxI1IF1XBdiEuXSooBNKKisXfTkGtFqsIdmOxdaEUaSIlTtp05v2u902aTgIuKr3wuOfdee/c88685xARVhJu/k25jznOazJtxhhoAyibtcUExTkeGloR181Yf/f2TERgiHpymY2b/qfr1aHJPUsKmC3aPPz9HndW3EVBcpZaxplr9W+XO/ohpV7TQFDzoGvn2WV1nw+YVOnYA3tWG4W3xWURHE+3QDQSqEUCG6cOpXB/ZAYnD3pLtYejM8gdiOe//aBZgWQCNhJukhe/LyKZTODRaBFOAkgsLhr+wOp4zSoX2NG6DkLGBAl7BOuCm3SQ60jB5V13P3fjRCaFLA8bNmfbPRzZ79V+rTLNCojnduPTTyXc/tgFJVSEH09fgBQSD/ISYRAiXBAIqiECxulLgmzNlcxmb2NnejOO3TqMLS0eS5S48bwTSipcPzPAXTWqsoo5OYdK6KMifMbzGMwPwekbnKKLR9swNuXDYUkDL7LcVeFK9hnujJ9r7lytYVsTgYzUoTc/QbOVkF5+KZGNV+Mlau/dR/VgY6kxvv4o0+mb7yyMlNc8YLB76wb8ml3ANm8tCj2vMTntR4btal2NiZ9/mu6CMWQaLhKNXCt82yu0WW//rx2afZHR41H/vEzlSvCkjp2VPue/lFt5YsuGFGsAAAAASUVORK5CYII=',
	
	leftPadString	: function (sVal, iSize, sCh) {
		var sResult	= new String(sVal);
		if (sCh == null)  {
			sCh	= " ";
		}
		
		while (sResult.length < iSize) {
			sResult	= sCh + sResult;
		}
		
		return sResult;
	}
});

return self;

