/*
 * Copyright (C) 2004 Baron Schwartz <baron at sequent dot org>
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as published by the
 * Free Software Foundation, version 2.1.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public License for more
 * details.
 *
 * $Revision: 1.2 $
 */

Date.parseFunctions = {count:0};
Date.parseRegexes = [];
Date.formatFunctions = {count:0};

Date.prototype.dateFormat = function(format) {
	if (Date.formatFunctions[format] == null) {
		Date.createNewFormat(format);
	}
	var func = Date.formatFunctions[format];
	return this[func]();
}

Date.createNewFormat = function(format) {
	var funcName = "format" + Date.formatFunctions.count++;
	Date.formatFunctions[format] = funcName;
	var code = "Date.prototype." + funcName + " = function(){return ";
	var special = false;
	var ch = '';
	for (var i = 0; i < format.length; ++i) {
		ch = format.charAt(i);
		if (!special && ch == "\\") {
			special = true;
		}
		else if (special) {
			special = false;
			code += "'" + String.escape(ch) + "' + ";
		}
		else {
			code += Date.getFormatCode(ch);
		}
	}
	eval(code.substring(0, code.length - 3) + ";}");
}

Date.getFormatCode = function(character) {
	switch (character) {
	case "d":
		return "String.leftPad(this.getDate(), 2, '0') + ";
	case "D":
		return "Date.dayNames[this.getDay()].substring(0, 3) + ";
	case "j":
		return "this.getDate() + ";
	case "l":
		return "Date.dayNames[this.getDay()] + ";
	case "S":
		return "this.getSuffix() + ";
	case "w":
		return "this.getDay() + ";
	case "z":
		return "this.getDayOfYear() + ";
	case "W":
		return "this.getWeekOfYear() + ";
	case "F":
		return "Date.monthNames[this.getMonth()] + ";
	case "m":
		return "String.leftPad(this.getMonth() + 1, 2, '0') + ";
	case "M":
		return "Date.monthNames[this.getMonth()].substring(0, 3) + ";
	case "n":
		return "(this.getMonth() + 1) + ";
	case "t":
		return "this.getDaysInMonth() + ";
	case "L":
		return "(this.isLeapYear() ? 1 : 0) + ";
	case "Y":
		return "this.getFullYear() + ";
	case "y":
		return "('' + this.getFullYear()).substring(2, 4) + ";
	case "a":
		return "(this.getHours() < 12 ? 'am' : 'pm') + ";
	case "A":
		return "(this.getHours() < 12 ? 'AM' : 'PM') + ";
	case "g":
		return "((this.getHours() %12) ? this.getHours() % 12 : 12) + ";
	case "G":
		return "this.getHours() + ";
	case "h":
		return "String.leftPad((this.getHours() %12) ? this.getHours() % 12 : 12, 2, '0') + ";
	case "H":
		return "String.leftPad(this.getHours(), 2, '0') + ";
	case "i":
		return "String.leftPad(this.getMinutes(), 2, '0') + ";
	case "s":
		return "String.leftPad(this.getSeconds(), 2, '0') + ";
	case "O":
		return "this.getGMTOffset() + ";
	case "T":
		return "this.getTimezone() + ";
	case "Z":
		return "(this.getTimezoneOffset() * -60) + ";
	default:
		return "'" + String.escape(character) + "' + ";
	}
}

Date.parseDate = function(input, format) {
	if (Date.parseFunctions[format] == null) {
		Date.createParser(format);
	}
	var func = Date.parseFunctions[format];
	return Date[func](input);
}

Date.createParser = function(format) {
	var funcName = "parse" + Date.parseFunctions.count++;
	var regexNum = Date.parseRegexes.length;
	var currentGroup = 1;
	Date.parseFunctions[format] = funcName;

	var code = "Date." + funcName + " = function(input){\n"
		+ "var y = -1, m = -1, d = -1, h = -1, i = -1, s = -1;\n"
		+ "var d = new Date();\n"
		+ "y = d.getFullYear();\n"
		+ "m = d.getMonth();\n"
		+ "d = d.getDate();\n"
		+ "var results = input.match(Date.parseRegexes[" + regexNum + "]);\n"
		+ "if (results && results.length > 0) {"
	var regex = "";

	var special = false;
	var ch = '';
	for (var i = 0; i < format.length; ++i) {
		ch = format.charAt(i);
		if (!special && ch == "\\") {
			special = true;
		}
		else if (special) {
			special = false;
			regex += String.escape(ch);
		}
		else {
			obj = Date.formatCodeToRegex(ch, currentGroup);
			currentGroup += obj.g;
			regex += obj.s;
			if (obj.g && obj.c) {
				code += obj.c;
			}
		}
	}

	code += "if (y > 0 && m >= 0 && d > 0 && h >= 0 && i >= 0 && s >= 0)\n"
		+ "{return new Date(y, m, d, h, i, s);}\n"
		+ "else if (y > 0 && m >= 0 && d > 0 && h >= 0 && i >= 0)\n"
		+ "{return new Date(y, m, d, h, i);}\n"
		+ "else if (y > 0 && m >= 0 && d > 0 && h >= 0)\n"
		+ "{return new Date(y, m, d, h);}\n"
		+ "else if (y > 0 && m >= 0 && d > 0)\n"
		+ "{return new Date(y, m, d);}\n"
		+ "else if (y > 0 && m >= 0)\n"
		+ "{return new Date(y, m);}\n"
		+ "else if (y > 0)\n"
		+ "{return new Date(y);}\n"
		+ "}return null;}";

	Date.parseRegexes[regexNum] = new RegExp("^" + regex + "$");
	eval(code);
}

Date.formatCodeToRegex = function(character, currentGroup) {
	switch (character) {
	case "D":
		return {g:0,
		c:null,
		s:"(?:Sun|Mon|Tue|Wed|Thu|Fri|Sat)"};
	case "j":
	case "d":
		return {g:1,
			c:"d = parseInt(results[" + currentGroup + "], 10);\n",
			s:"(\\d{1,2})"};
	case "l":
		return {g:0,
			c:null,
			s:"(?:" + Date.dayNames.join("|") + ")"};
	case "S":
		return {g:0,
			c:null,
			s:"(?:st|nd|rd|th)"};
	case "w":
		return {g:0,
			c:null,
			s:"\\d"};
	case "z":
		return {g:0,
			c:null,
			s:"(?:\\d{1,3})"};
	case "W":
		return {g:0,
			c:null,
			s:"(?:\\d{2})"};
	case "F":
		return {g:1,
			c:"m = parseInt(Date.monthNumbers[results[" + currentGroup + "].substring(0, 3)], 10);\n",
			s:"(" + Date.monthNames.join("|") + ")"};
	case "M":
		return {g:1,
			c:"m = parseInt(Date.monthNumbers[results[" + currentGroup + "]], 10);\n",
			s:"(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)"};
	case "n":
	case "m":
		return {g:1,
			c:"m = parseInt(results[" + currentGroup + "], 10) - 1;\n",
			s:"(\\d{1,2})"};
	case "t":
		return {g:0,
			c:null,
			s:"\\d{1,2}"};
	case "L":
		return {g:0,
			c:null,
			s:"(?:1|0)"};
	case "Y":
		return {g:1,
			c:"y = parseInt(results[" + currentGroup + "], 10);\n",
			s:"(\\d{4})"};
	case "y":
		return {g:1,
			c:"var ty = parseInt(results[" + currentGroup + "], 10);\n"
				+ "y = ty > Date.y2kYear ? 1900 + ty : 2000 + ty;\n",
			s:"(\\d{1,2})"};
	case "a":
		return {g:1,
			c:"if (results[" + currentGroup + "] == 'am') {\n"
				+ "if (h == 12) { h = 0; }\n"
				+ "} else { if (h < 12) { h += 12; }}",
			s:"(am|pm)"};
	case "A":
		return {g:1,
			c:"if (results[" + currentGroup + "] == 'AM') {\n"
				+ "if (h == 12) { h = 0; }\n"
				+ "} else { if (h < 12) { h += 12; }}",
			s:"(AM|PM)"};
	case "g":
	case "G":
	case "h":
	case "H":
		return {g:1,
			c:"h = parseInt(results[" + currentGroup + "], 10);\n",
			s:"(\\d{1,2})"};
	case "i":
		return {g:1,
			c:"i = parseInt(results[" + currentGroup + "], 10);\n",
			s:"(\\d{2})"};
	case "s":
		return {g:1,
			c:"s = parseInt(results[" + currentGroup + "], 10);\n",
			s:"(\\d{2})"};
	case "O":
		return {g:0,
			c:null,
			s:"[+-]\\d{4}"};
	case "T":
		return {g:0,
			c:null,
			s:"[A-Z]{3}"};
	case "Z":
		return {g:0,
			c:null,
			s:"[+-]\\d{1,5}"};
	default:
		return {g:0,
			c:null,
			s:String.escape(character)};
	}
}

Date.daysInMonth = [31,28,31,30,31,30,31,31,30,31,30,31];
Date.monthNames =
   ["January",
	"February",
	"March",
	"April",
	"May",
	"June",
	"July",
	"August",
	"September",
	"October",
	"November",
	"December"];
Date.dayNames =
   ["Sunday",
	"Monday",
	"Tuesday",
	"Wednesday",
	"Thursday",
	"Friday",
	"Saturday"];
Date.y2kYear = 50;
Date.monthNumbers = {
	Jan:0,
	Feb:1,
	Mar:2,
	Apr:3,
	May:4,
	Jun:5,
	Jul:6,
	Aug:7,
	Sep:8,
	Oct:9,
	Nov:10,
	Dec:11};
Date.patterns = {
	ISO8601LongPattern:"Y-m-d H:i:s",
	ISO8601ShortPattern:"Y-m-d",
	ShortDatePattern: "n/j/Y",
	LongDatePattern: "l, F d, Y",
	FullDateTimePattern: "l, F d, Y g:i:s A",
	MonthDayPattern: "F d",
	ShortTimePattern: "g:i A",
	LongTimePattern: "g:i:s A",
	SortableDateTimePattern: "Y-m-d\\TH:i:s",
	UniversalSortableDateTimePattern: "Y-m-d H:i:sO",
	YearMonthPattern: "F, Y"};


Date.prototype.getTimezone = function() {
	return this.toString().replace(
		/^.*? ([A-Z]{3}) [0-9]{4}.*$/, "$1").replace(
		/^.*?\(([A-Z])[a-z]+ ([A-Z])[a-z]+ ([A-Z])[a-z]+\)$/, "$1$2$3");
}

Date.prototype.getGMTOffset = function() {
	return (this.getTimezoneOffset() > 0 ? "-" : "+")
		+ String.leftPad(Math.floor(this.getTimezoneOffset() / 60), 2, "0")
		+ String.leftPad(this.getTimezoneOffset() % 60, 2, "0");
}

Date.prototype.getDayOfYear = function() {
	var num = 0;
	Date.daysInMonth[1] = this.isLeapYear() ? 29 : 28;
	for (var i = 0; i < this.getMonth(); ++i) {
		num += Date.daysInMonth[i];
	}
	return num + this.getDate() - 1;
}

Date.prototype.getWeekOfYear = function() {
	// Skip to Thursday of this week
	var now = this.getDayOfYear() + (4 - this.getDay());
	// Find the first Thursday of the year
	var jan1 = new Date(this.getFullYear(), 0, 1);
	var then = (7 - jan1.getDay() + 4);
	document.write(then);
	return String.leftPad(((now - then) / 7) + 1, 2, "0");
}

Date.prototype.isLeapYear = function() {
	var year = this.getFullYear();
	return ((year & 3) == 0 && (year % 100 || (year % 400 == 0 && year)));
}

Date.prototype.getFirstDayOfMonth = function() {
	var day = (this.getDay() - (this.getDate() - 1)) % 7;
	return (day < 0) ? (day + 7) : day;
}

Date.prototype.getLastDayOfMonth = function() {
	var day = (this.getDay() + (Date.daysInMonth[this.getMonth()] - this.getDate())) % 7;
	return (day < 0) ? (day + 7) : day;
}

Date.prototype.getDaysInMonth = function() {
	Date.daysInMonth[1] = this.isLeapYear() ? 29 : 28;
	return Date.daysInMonth[this.getMonth()];
}

Date.prototype.getSuffix = function() {
	switch (this.getDate()) {
		case 1:
		case 21:
		case 31:
			return "st";
		case 2:
		case 22:
			return "nd";
		case 3:
		case 23:
			return "rd";
		default:
			return "th";
	}
}
/*
Date.prototype.setHours = function(intHours)
{
	
}

Date.prototype.setMinutes = function(intMinutes)
{
	
}
*/
String.escape = function(string) {
	return string.replace(/('|\\)/g, "\\$1");
}

String.leftPad = function (val, size, ch) {
	var result = new String(val);
	if (ch == null) {
		ch = " ";
	}
	while (result.length < size) {
		result = ch + result;
	}
	return result;
}


// DateChooser constructor
function DateChooser(mixInput, start, end, format, isTimeChooser, isDateChooser, useCalendar, defaultYear, defaultMonth, defaultDay)
{
	if (mixInput.id)
	{
		// DOM element
		alert('mixInput is a DOM element (id: '+mixInput.id+')');
		var input	= mixInput;
		var inputId	= mixInput.id;
	}
	else
	{
		// Id
		alert('mixInput is NOT an Object ('+(typeof mixInput)+') (id: '+mixInput+')');
		var input	= document.getElementById(mixInput);
		var inputId	= mixInput;
	}

	var divId = inputId + 'Calendar';
	var div = document.getElementById(divId);
	if (div == undefined || div == null)
	{
		div = document.createElement('div');
		div.id = divId;
		div.className = 'date-time select-free';
		div.style.position = "absolute";
		div.style.zIndex = 10000;
	}
	this._div = div;

	this._constructed = false;
	this._input = input;
	this._inputId = inputId;
	this._start = start;
	this._end = end;
	this._defaultYear = defaultYear;
	this._defaultMonth = defaultMonth;
	this._defaultDay = defaultDay;
	this._format = format;
	this.initializeDate();
	this._isTimeChooser = isTimeChooser;
	this._isDateChooser = isDateChooser;
	this._useCalendar = useCalendar;
	DateChooser.instanceCount++;
}
DateChooser.instances = {};
DateChooser.instanceCount = 0;

DateChooser.currentChooser  = null;

DateChooser.factory = function(mixInput, start, end, format, isTimeChooser, isDateChooser, useCalendar, defaultYear, defaultMonth, defaultDay)
{
	var inputId	= (mixInput.id) ? mixInput.id : mixInput;
	
	if (DateChooser.instances[inputId] == undefined)
	{
		DateChooser.instances[inputId] = new DateChooser(mixInput, start, end, format, isTimeChooser, isDateChooser, useCalendar, defaultYear, defaultMonth, defaultDay);
	}
	else
	{
		// Ensure that this is for the same input element as the last one!
		var input = document.getElementById(inputId);
		if (input != DateChooser.instances[inputId]._input)
		{
			DateChooser.instances[inputId] = new DateChooser(mixInput, start, end, format, isTimeChooser, isDateChooser, useCalendar, defaultYear, defaultMonth, defaultDay);
		}
	}
	return DateChooser.instances[inputId];
}

// Shows or hides the date chooser on the page
DateChooser.showChooser = function(inputId, start, end, format, isTimeChooser, isDateChooser, useCalendar, defaultYear, defaultMonth, defaultDay)
{

	var dateChooser = DateChooser.factory(inputId, start, end, format, isTimeChooser, isDateChooser, useCalendar, defaultYear, defaultMonth, defaultDay);

	dateChooser.updateDate();

	if (dateChooser.isVisible())
	{
		dateChooser.hide();
		DateChooser.currentChooser = null;
	}
	else
	{
		if (DateChooser.currentChooser != null)
		{
			DateChooser.currentChooser.hide();
		}
		dateChooser.show();
		DateChooser.currentChooser = dateChooser;
	}
}

DateChooser.checkClick = function(event)
{
	if (DateChooser.currentChooser == null)
	{
		return;
	}

	if (!event) event = window.event;

	// Get the target
	var target = event.target;

	// Now check to see if the target is within the div
	while (target != DateChooser.currentChooser._div && target.parentNode)
	{
		target = target.parentNode;
	}
	if (target != DateChooser.currentChooser._div)
	{
		DateChooser.currentChooser.hide();
	}
	return true;
}

document.addEventListener("click", DateChooser.checkClick, false);
document.addEventListener("keyPress", DateChooser.checkClick, false);

// The callback function for when someone changes the pulldown menus on the date chooser
DateChooser.dateChange = function (prefix) 
{
	DateChooser.instances[prefix].handleDateChange();
}

// Sets a date on the object attached to 'inputId'
DateChooser.setDate = function(prefix, value) 
{
	DateChooser.instances[prefix].setDate(value);
}



// DateChooser prototype variables
DateChooser.prototype = {

	_isVisible: false,
	
	_ampmSelect: 	null,
	_hourSelect: 	null,
	_minuteSelect: 	null,
	_monthSelect: 	null,
	_yearSelect: 	null,
	_dayGrid: 		null,
	_hiddenInput: 	null,

	handleDateChange: function()
	{
		var newDate = new Date(
			this._yearSelect.options[this._yearSelect.selectedIndex].value,
			this._monthSelect.options[this._monthSelect.selectedIndex].value,
			1);
		// Try to preserve the day of month (watch out for months with 31 days)
		newDate.setDate(Math.max(1, Math.min(newDate.getDaysInMonth(), this._date.getDate())));
		this._setDate(newDate);
		if (this.isTimeChooser()) {
			this._setTime(
				parseInt(this._hourSelect.options[this._hourSelect.selectedIndex].value)
					+ parseInt(this._ampmSelect.options[this._ampmSelect.selectedIndex].value),
				parseInt(this._minuteSelect.options[this._minuteSelect.selectedIndex].value));
		}
		this.show();
	},

	setDate: function(value)
	{
		var input = this._input;
		if (value != null) {
			this._setDate(Date.parseDate(value, this._format));
		}
		if (this.isTimeChooser()) {
			this._setTime(
				parseInt(this._hourSelect.options[this._hourSelect.selectedIndex].value)
					+ parseInt(this._ampmSelect.options[this._ampmSelect.selectedIndex].value),
				parseInt(this._minuteSelect.options[this._minuteSelect.selectedIndex].value));
		}
		input.value = this.getValue();
		this.hide();
	},

	updateDate: function()
	{
		var value = this._input.value;
		if (value != '')
		{
			var parsed = Date.parseDate(value, this._format);
			this._setDate(parsed);
			if (parsed)
			{
				if (this.isTimeChooser()) {
					this._setTime(
						parsed.getHours(),
						parsed.getMinutes()
					);
				}
			}
		}
	},

	// Returns true if the chooser is currently visible
	isVisible: function() {
		return this._isVisible;
	},

	// Returns true if the chooser is to allow choosing the time as well as the date
	isTimeChooser: function() {
		return this._isTimeChooser;
	},

	// Returns true if the chooser is to allow choosing the time as well as the date
	isDateChooser: function() {
		return this._isDateChooser;
	},

	// Returns true if the chooser is to allow choosing the time as well as the date
	useCalendar: function() {
		return this._useCalendar;
	},

	// Gets the value, as a formatted string, of the date attached to the chooser
	getValue: function() {
		return this._date.dateFormat(this._format);
	},

	// Hides the chooser
	hide: function() {
		if (this._isVisible)
		{
			this._isVisible = false;
			this._div.parentNode.removeChild(this._div);
		}
	},

	cumulativeOffset: function(element) {
		var valueT = 0, valueL = 0;
		do {
			valueT += element.offsetTop  || 0;
			valueL += element.offsetLeft || 0;
			element = element.offsetParent;
		} while (element);
		return {left: valueL, top: valueT};
	},

	// Shows the chooser on the page
	show: function() {
		if (!this._constructed)
		{
			this.createChooserHtml();
			// Keep it hidden until it has been repositioned
			var position = this.cumulativeOffset(this._input);
			this._div.style.left = (position.left + this._input.clientWidth + 24) + 'px';
			this._div.style.top = (position.top + this._input.clientHeight - 18) + 'px';
			this._constructed = true;
		}
		else
		{
			try
			{
				if (this._yearSelect != null)
				{
					this._yearSelect.selectedIndex = this._date.getFullYear() - this._start;
				}
				if (this._monthSelect != null)
				{
					this._monthSelect.selectedIndex = this._date.getMonth();
				}
				if (this._isDateChooser && this._useCalendar)
				{
					this.createCalendarHtml();
				}
				if (this._hourSelect != null)
				{
					var hours = this._date.getHours() - 1;
					if (hours < 0) hours += 12;
					this._hourSelect.selectedIndex = (hours % 12);
				}
				if (this._ampmSelect != null)
				{
					this._ampmSelect.selectedIndex = (this._date.getHours() < 12) ? 0 : 1;
				}
				if (this._minuteSelect != null)
				{
					this._minuteSelect.selectedIndex = this._date.getMinutes();
				}
			}
			catch(exception)
			{
				throw exception;
			}
		}
	
		document.body.appendChild(this._div);
		this._isVisible = true;
	},

	// Sets the date to what is in the input box
	initializeDate: function() {
		if (this._input.value != null && this._input.value != "") {
			this._date = Date.parseDate(this._input.value, this._format);
		}
		else {
			this._date = this.getDefaultDate();
		}
	},

	// Sets the date attached to the chooser
	_setDate: function(date) {
		this._date = date ? date : this.getDefaultDate();
	},

	// Sets the time portion of the date attached to the chooser
	_setTime: function(hour, minute) {
		this._date.setHours(hour);
		this._date.setMinutes(minute);
	},

	// Determines the default date with which to populate the chooser
	getDefaultDate: function() {
		var defaultDate = new Date();
		if (this._defaultYear != undefined)
		{
			defaultDate.setFullYear(this._defaultYear);
		}
		if (this._defaultMonth != undefined)
		{
			defaultDate.setMonth(this._defaultMonth - 1);
		}
		if (this._defaultDay != undefined)
		{
			defaultDate.setDate(this._defaultDay);
		}
		return defaultDate;
	},

	// Creates the HTML for the whole chooser
	createChooserHtml: function() {
	
		var input = document.createElement("INPUT");
		input.type = "hidden";
		input.id = this._inputId + "inputId";
		input.value = this._input.getAttribute('id');
		this._div.appendChild(input);
		this._hiddenInput = input;

		if (this._isDateChooser)
		{
			this.createDateChooserHtml();
		}

		if (this._isTimeChooser)
		{
			this.createTimeChooserHtml();
		}

	},

	// Creates the HTML needed for choosing the date
	createDateChooserHtml: function()
	{
		var div = document.createElement("DIV");
		div.className = "bar";

		var select = document.createElement("SELECT");
		select.id = this._inputId + "month";
		select.addEventListener("change", new Function("DateChooser.dateChange('" + this._inputId + "');"), false);
		div.appendChild(select);
		this._monthSelect = select;

		for (var monIndex = 0; monIndex <= 11; monIndex++)
		{
			var option = document.createElement("OPTION");
			option.value = monIndex;
			option.selected = monIndex == this._date.getMonth();
			option.appendChild(document.createTextNode(Date.monthNames[monIndex]));
			select.appendChild(option);
		}

		div.appendChild(document.createTextNode(" "));

		select = document.createElement("SELECT");
		select.id = this._inputId + "year";
		select.addEventListener("change", new Function("DateChooser.dateChange('" + this._inputId + "');"), false);
		div.appendChild(select);
		this._yearSelect = select;

		for (var i = this._start; i <= this._end; ++i)
		{
			var option = document.createElement("OPTION");
			option.value = i;
			option.selected = i == this._date.getFullYear();
			option.appendChild(document.createTextNode(i));
			select.appendChild(option);
		}
		div.appendChild(select);

		div.appendChild(document.createTextNode(" "));

		var img = document.createElement("IMG");
		img.src = "img/template/table_row_insert.png";
		img.addEventListener("click", new Function("DateChooser.setDate('" + this._inputId + "', null);"), false);
		img.style.position = "relative";
		img.style.top = "3px";
		div.appendChild(img);

		this._div.appendChild(div);

		if (this._useCalendar)
		{
			this.createCalendarHtml();
		}
	},

	// Creates the extra HTML needed for choosing the time
	createTimeChooserHtml: function()
	{
		var div = document.createElement("DIV");
		div.className = "bar";
		
		// Add hours
		var select = document.createElement("SELECT");
		select.id = this._inputId + "hour";
		div.appendChild(select);
		this._hourSelect = select;

		for (var i = 1; i < 12; ++i)
		{
			var option = document.createElement("OPTION");
			option.value = i;
			option.selected = (this._date.getHours() % 12) == i;
			option.appendChild(document.createTextNode(i));
			select.appendChild(option);
		}

		// Add extra entry for 12:00
		option = document.createElement("OPTION");
		option.value = 0;
		option.appendChild(document.createTextNode(12));
		select.appendChild(option);

		// Add minutes
		select = document.createElement("SELECT");
		select.id = this._inputId + "min";
		div.appendChild(select);
		this._minuteSelect = select;

		for (var i = 0; i < 60; ++i)
		{
			var option = document.createElement("OPTION");
			option.value = i;
			option.selected = this._date.getMinutes() == i;
			option.appendChild(document.createTextNode(String.leftPad(i, 2, '0')));
			select.appendChild(option);
		}

		// Add AM/PM
		select = document.createElement("SELECT");
		select.id = this._inputId + "ampm";
		div.appendChild(select);
		this._ampmSelect = select;

		var option = document.createElement("OPTION");
		option.value = 0;
		option.selected = this._date.getHours() < 12;
		option.appendChild(document.createTextNode("AM"));
		select.appendChild(option);

		var option = document.createElement("OPTION");
		option.value = 12;
		option.selected = this._date.getHours() >= 12;
		option.appendChild(document.createTextNode("PM"));
		select.appendChild(option);

		var img = document.createElement("IMG");
		img.src = "img/template/table_row_insert.png";
		img.addEventListener("click", new Function("DateChooser.setDate('" + this._inputId + "', null);"), false);
		img.style.position = "relative";
		img.style.top = "3px";
		div.appendChild(img);

		this._div.appendChild(div);
	},

	// Creates the HTML for the actual calendar part of the chooser
	createCalendarHtml: function()
	{
		var table = document.createElement("TABLE");
		table.cellspacing = 0;
		table.className = "dateChooser";
		var row = table.insertRow(-1);
		row.insertCell(-1).appendChild(document.createTextNode("S"));
		row.insertCell(-1).appendChild(document.createTextNode("M"));
		row.insertCell(-1).appendChild(document.createTextNode("T"));
		row.insertCell(-1).appendChild(document.createTextNode("W"));
		row.insertCell(-1).appendChild(document.createTextNode("T"));
		row.insertCell(-1).appendChild(document.createTextNode("F"));
		row.insertCell(-1).appendChild(document.createTextNode("S"));

		row = table.insertRow(-1);

		// Fill up the days of the week until we get to the first day of the month
		var firstDay = this._date.getFirstDayOfMonth();
		var lastDay = this._date.getLastDayOfMonth();
		if (firstDay != 0)
		{
			var cell = row.insertCell(-1);
			cell.colSpan = firstDay;
			cell.appendChild(document.createTextNode("\u00a0"));
		}

		// Fill in the days of the month
		var i = 0;
		var daysInMonth = this._date.getDaysInMonth()
		var selectedDate = this._date.getDate();
		while (i < daysInMonth) {
			if (((i++ + firstDay) % 7) == 0)
			{
				row = table.insertRow(-1);
			}
			var thisDay = new Date(this._date.getFullYear(), this._date.getMonth(), i);
			var js = "DateChooser.setDate('" + this._inputId + "', '" + thisDay.dateFormat(this._format) + "');";
			var cell = row.insertCell(-1);
			cell.className = "date-time-active" + (i == selectedDate ? " date-time-active-today" : "");
			cell.appendChild(document.createTextNode(i));
			cell.addEventListener("click", new Function(js), false);
		}

		// Fill in any days after the end of the month
		if (lastDay != 6) {
			var cell = row.insertCell(-1);
			cell.colSpan = (6 - lastDay);
			cell.appendChild(document.createTextNode("\u00a0"));
		}

		if (this._dayGrid != undefined && this._dayGrid != null)
		{
			this._div.replaceChild(table, this._dayGrid);
		}
		else
		{
			this._div.appendChild(table);
		}

		this._dayGrid = table;

		return table;
	}
}

