
// Extends the JS Date Object
Date.DATE_INTERVAL_DAY		= 'days';
Date.DATE_INTERVAL_WEEK		= 'weeks';
Date.DATE_INTERVAL_MONTH	= 'months';
Date.DATE_INTERVAL_YEAR		= 'years';
Date.DATE_INTERVAL_HOUR		= 'hours';
Date.DATE_INTERVAL_MINUTE	= 'minutes';
Date.DATE_INTERVAL_SECOND	= 'seconds';

Date.DAYS_IN_MONTH	=	{
							0	: 31,
							1	: 28,
							2	: 31,
							3	: 30,
							4	: 31,
							5	: 30,
							6	: 31,
							7	: 31,
							8	: 30,
							9	: 31,
							10	: 30,
							11	: 31
						};

// Convenience conversion numbers
Date.MILLISECONDS_IN_SECOND	= 1000;
Date.SECONDS_IN_MINUTE		= 60;
Date.MINUTES_IN_HOUR		= 60;
Date.HOURS_IN_DAY			= 24;

Date.MILLISECONDS_IN_MINUTE	= Date.MILLISECONDS_IN_SECOND * Date.SECONDS_IN_MINUTE;
Date.MILLISECONDS_IN_HOUR	= Date.MILLISECONDS_IN_MINUTE * Date.MINUTES_IN_HOUR;
Date.MILLISECONDS_IN_DAY	= Date.MILLISECONDS_IN_HOUR * Date.HOURS_IN_DAY;

/**
 *	truncate()
 */
Date.prototype.truncate	= function(sPrecision) {
	sPrecision	= String(sPrecision).strip().toLowerCase();

	// Truncate up to the DAY precision
	switch (sPrecision) {
		// Truncating for SECOND to DAY can be implemented using a fallthrough
		case Date.DATE_INTERVAL_DAY:
		case Date.DATE_INTERVAL_WEEK:
		case Date.DATE_INTERVAL_MONTH:
		case Date.DATE_INTERVAL_YEAR:
			this.setHours(0);
		case Date.DATE_INTERVAL_HOUR:
			this.setMinutes(0);
		case Date.DATE_INTERVAL_MINUTE:
			this.setSeconds(0);
		case Date.DATE_INTERVAL_SECOND:
			this.setMilliseconds(0);
			break;
	}

	// Truncate to any precision higher than DAY
	switch (sPrecision) {
		case Date.DATE_INTERVAL_WEEK:
			this.setDate(this.getDate() - this.getDay());	// Day 0 is the start of the week
			break;

		// Truncating for MONTH to YEAR can be implemented using a fallthrough
		case Date.DATE_INTERVAL_YEAR:
			this.setMonth(0);	// Month 0 is the start of the year
		case Date.DATE_INTERVAL_MONTH:
			this.setDate(1);
			break;
	}

	return this;
};

// getDaySeconds(): Gets the number of seconds that have passed on the day
Date.prototype.getDaySeconds	= function()
{
	return (this.getHours() * 60 * 60) + (this.getMinutes() * 60) + this.getSeconds();
}

Date.prototype.isLeapYear	= function(iYear)
{
	var iYear	= (iYear ? iYear : this.getFullYear());
	return (((iYear % 4 === 0) && (iYear % 100 !== 0)) || iYear % 400 === 0);
};

Date.prototype.getDaysInMonth	= function()
{
	return Date.DAYS_IN_MONTH[this.getMonth()] + ((this.getMonth() === 1 && this.isLeapYear()) ? 1 : 0);
};

/**
 * 	shift
 * 
 *	e.g.	(new Date()).shift(1, 'days');		or	(new Date()).shift(1, Date.DATE_INTERVAL_DAY);
 *			(new Date()).shift(-6, 'months');	or	(new Date()).shift(-6, Date.DATE_INTERVAL_MONTH);
 */
Date.prototype.shift	= function(iInterval, sIntervalType)
{
	iInterval	= parseInt(iInterval);
	switch (sIntervalType)
	{
		case Date.DATE_INTERVAL_SECOND:
			this.setSeconds(this.getSeconds() + iInterval);
			break;
		
		case Date.DATE_INTERVAL_MINUTE:
			this.setMinutes(this.getMinutes() + iInterval);
			break;
		
		case Date.DATE_INTERVAL_HOUR:
			this.setHours(this.getHours() + iInterval);
			break;
		
		case Date.DATE_INTERVAL_DAY:
			this.setDate(this.getDate() + iInterval);
			break;

		case Date.DATE_INTERVAL_WEEK:
			this.setDate(this.getDate() + (iInterval * 7));
			break;
		
		case Date.DATE_INTERVAL_MONTH:
			var iCurrentDate	= this.getDate();
			
			this.setDate(1);
			this.setMonth(this.getMonth() + iInterval);
			
			this.setDate(Math.min(iCurrentDate, this.getDaysInMonth()));
			break;
		
		case Date.DATE_INTERVAL_YEAR:
			var iCurrentDate	= this.getDate();
			
			this.setDate(1);
			this.setYear(this.getFullYear() + iInterval);
			
			this.setDate(Math.min(iCurrentDate, this.getDaysInMonth()));
			break;
	}

	return this;
};

// Unit-ish Testing
Date.$_UNIT_TEST_DATA_			= {};
Date.$_UNIT_TEST_DATA_.shift	=	[
                            	 	 	{iInterval: 20	, sIntervalType: Date.DATE_INTERVAL_SECOND},
                            	 	 	{iInterval: -90	, sIntervalType: Date.DATE_INTERVAL_SECOND},
                            	 	 	{iInterval: 360	, sIntervalType: Date.DATE_INTERVAL_MINUTE},
                            	 	 	{iInterval: -9	, sIntervalType: Date.DATE_INTERVAL_HOUR},
                            	 	 	{iInterval: 7	, sIntervalType: Date.DATE_INTERVAL_DAY},
                            	 	 	{iInterval: 6	, sIntervalType: Date.DATE_INTERVAL_MONTH},
                            	 	 	{iInterval: -3	, sIntervalType: Date.DATE_INTERVAL_YEAR}
                            	 	];

Date.$_UNIT_TESTS_	= {};

Date.$_UNIT_TESTS_.shift	= function(oSourceDate, bProgressive)
{
	oSourceDate		= oSourceDate ? oSourceDate : new Date();
	bProgressive	= (!bProgressive) ? false : true;
	
	var oNewDate	= new Date(oSourceDate);
	for (var i = 0; i < Date.$_UNIT_TEST_DATA_.shift.length; i++)
	{
		var oDate		= bProgressive ? oNewDate : new Date(oSourceDate);
		var oNewDate	= new Date(oDate);
		
		var oTestData	= Date.$_UNIT_TEST_DATA_.shift[i];
		
		oNewDate.shift(oTestData.iInterval, oTestData.sIntervalType);
		//alert("Test " + (i + 1) + " of " + Date.$_UNIT_TEST_DATA_.shift.length + ": " + oDate + " + " + oTestData.iInterval + " " + oTestData.sIntervalType + " = " + oNewDate);
	}
}

/////////////////////////////
// Formatting functions
/////////////////////////////

Date.$format	= function(sFormat, mDate)
{
	// Accept a Date object, seconds since Epoch (1970-01-01), or no value (default to current time)
	var oDate	= null;
	if (mDate instanceof Date)
	{
		oDate	= mDate;
	}
	else
	{
		oDate	= new Date();
		if (mDate)
		{
			oDate.setTime(Number(mDate) * 1000);
		}
	}
	
	return oDate.$format(sFormat);
}

Date.prototype.$format	= function(sFormat)
{
	var oDate	= this;
	
	// Tokenise Format String
	var sOutput		= '';
	var bEscaped	= false;
	for (var i = 0; i < sFormat.length; i++)
	{
		switch (sFormat.charAt(i))
		{
			case '\\':
				if (bEscaped)
				{
					sOutput	+= '\\';
				};
				bEscaped	= !bEscaped;
				break;
			
			default:
				if (bEscaped)
				{
					// Escaped -- output the token as plain text
					sOutput		+= sFormat.charAt(i);
					bEscaped	= false;
				}
				else
				{
					// Not Escaped -- try to parse the token
					sOutput		+= oDate.$parseToken(sFormat.charAt(i));
				}
				break;
		}
	}
	
	return sOutput;
};

Date.prototype.$parseToken	= function(sToken)
{
	switch (sToken)
	{
		// DAY
		case 'd':
			// Day of the month, 2 digits with leading zeros
			return this.getDate().toPaddedString(2);
			break;
		case 'D':
			// A textual representation of a day, three letters
			return this.$oDays.oShortNames[this.getDay()];
			break;
		case 'j':
			// Day of the month without leading zeros
			return this.getDate();
			break;
		case 'l':
			// A full textual representation of the day of the week
			return this.$oDays.oFullNames[this.getDay()];
			break;
		case 'N':
			// ISO-8601 numeric representation of the day of the week
			return this.$oDays.oISONumeric[this.getDay()];
			break;
		case 'S':
			// English ordinal suffix for the day of the month, 2 characters
			return this.$numberOrdinalSuffix(this.getDate());
			break;
		case 'w':
			// Numeric representation of the day of the week
			return this.getDay();
			break;
		case 'z':
			// The day of the year (starting from 0)
			var iDayOfMonth		= this.getDate();
			var iMonthOfYear	= this.getMonth() + 1;
			
			var iDayOfYear	= 0;
			
			// Months to date
			for (var i = 1; i < iMonthOfYear; i++)
			{
				iDayOfYear	+= this.$oMonths.oDaysInMonth[i];
				
				// Leap Year in Feb
				if (iMonthOfYear === 2 && this.isLeapYear())
				{
					iDayOfYear++;
				}
			}
			
			// How far we are into the month
			return iDayOfYear + iDayOfMonth;
			break;
		
		// WEEK
		case 'W':
			return this.$calculateISOWeekDate().iWeek;
			break;
		
		// MONTH
		case 'F':
			// A full textual representation of a month, such as January or March
			return this.$oMonths.oFullNames[this.getMonth() + 1];
			break;
		case 'm':
			// Numeric representation of a month, with leading zeros
			return (this.getMonth() + 1).toPaddedString(2);
			break;
		case 'M':
			// A short textual representation of a month, three letters
			return this.$oMonths.oShortNames[this.getMonth() + 1];
			break;
		case 'n':
			// Numeric representation of a month, without leading zeros
			return this.getMonth() + 1;
			break;
		case 't':
			// Number of days in the given month
			var iMonth			= this.getMonth() + 1;
			var iDaysInMonth	= this.$oMonths.oDaysInMonth[iMonth];
			if (iMonth === 2 && this.isLeapYear())
			{
				iDaysInMonth++;
			}
			return iDaysInMonth;
			break;
		
		// YEAR
		case 'L':
			// Whether it's a leap year
			return this.isLeapYear();
			break;
		case 'o':
			// ISO-8601 year number. This has the same value as Y, except that if the ISO week number (W) belongs to the previous or next year, that year is used instead.
			return this.$calculateISOWeekDate().iYear;
			break;
		case 'Y':
			// A full numeric representation of a year, 4 digits
			return this.getFullYear();
			break;
		case 'y':
			// A two digit representation of a year
			var iYear		= this.getFullYear();
			var iYear2Digit;
			
			if (iYear >= 2000 && iYear <= 2038)
			{
				iYear2Digit	= iYear - 2000;// - iYear;
			}
			else if (iYear > 1970 || iYear < 2000)
			{
				iYear2Digit	= iYear - 1900;// - iYear;
			}
			else
			{
				// This is what PHP's date() function returns
				iYear2Digit	= 69;
			}
			return iYear2Digit.toPaddedString(2);
			break;
		
		// TIME
		case 'a':
			// Lowercase Ante meridiem and Post meridiem
			var	iHours	= this.getHours();
			return (iHours <= 11) ? 'am' : 'pm';
			break;
		case 'A':
			// Uppercase Ante meridiem and Post meridiem
			return this.$parseToken('a').toUpperCase();
			break;
		case 'B':
			// Swatch Internet time
			var iSecondsInSwatchUnit	= 86400 / 1000;
			
			// Caculate Time
			var	iSeconds	= (this.getHours() * 3600) + (this.getMinutes() * 60) + this.getSeconds();
			return Math.floor(iSeconds / iSecondsInSwatchUnit).toPaddedString(3);
			break;
		case 'g':
			// 12-hour format of an hour without leading zeros
			var iHours	= this.getHours();
			if (iHours > 12)
			{
				return iHours - 12;
			}
			else if (iHours === 0)
			{
				return 12;
			}
			else
			{
				return iHours;
			}
			break;
		case 'G':
			// 24-hour format of an hour without leading zeros
			return this.getHours();
			break;
		case 'h':
			// 12-hour format of an hour with leading zeros
			return this.$parseToken('g').toPaddedString(2);
			break;
		case 'H':
			// 24-hour format of an hour with leading zeros
			return this.getHours().toPaddedString(2);
			break;
		case 'i':
			// Minutes with leading zeros
			return this.getMinutes().toPaddedString(2);
			break;
		case 's':
			// Seconds, with leading zeros
			return this.getSeconds().toPaddedString(2);
			break;
		case 'u':
			// Microseconds
			return (this.getMilliseconds() * 1000).toPaddedString(2);
			break;
		
		// TIMEZONE
		case 'e':
			// Timezone identifier
			// TODO
			throw "'e' token is not supported yet!";
			break;
		case 'I':
			// Whether or not the date is in daylight saving time
			// TODO
			throw "'I' token is not supported yet!";
			break;
		case 'O':
			// Difference to Greenwich time (GMT) in hours
			var iOffset	= this.getTimezoneOffset();
			return ((iOffset < 0) ? '-' : '+') + Math.floor(iOffset / 60).toPaddedString(2) + (iOffset % 60).toPaddedString(2);
			break;
		case 'P':
			// Difference to Greenwich time (GMT) with colon between hours and minutes
			var iOffset	= this.getTimezoneOffset();
			return ((iOffset < 0) ? '-' : '+') + Math.floor(iOffset / 60).toPaddedString(2) + ':' + (iOffset % 60).toPaddedString(2);
			break;
		case 'T':
			// Timezone abbreviation
			// TODO
			throw "'T' token is not supported yet!";
			break;
		case 'Z':
			// Timezone offset in seconds. The offset for timezones west of UTC is always negative, and for those east of UTC is always positive.
			return this.getTimezoneOffset() * 60;
			break;
		
		// FULL DATE/TIME
		case 'c':
			// ISO 8601 date
			return this.$format(this, 'Y-m-d\TH:i:sP');
			break;
		case 'r':
			// RFC 2822 formatted date
			return this.$format(this, 'D, j M Y H:i:s O');
			break;
		case 'U':
			// Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)
			return this.getTime() / 1000;
			break;
		
		// DEFAULT: Return the Token
		default:
			return sToken;
			break;
	}
};

Date.prototype.$calculateISOWeekDate	= function()
{
	// ISO-8601 week number of year, weeks starting on Monday
	var iDayOfYear		= this.$parseToken('z');
	var iDayOfWeekISO	= this.$parseToken('N');
	
	// Algorithm from -- http://www.personal.ecu.edu/mccartyr/ISOwdALG.txt
	var bThisYearIsLeap	= this.isLeapYear();
	var bLastYearIsLeap	= this.isLeapYear(this.getFullYear() - 1);
	
	var oThisThursday	= new Date(this.valueOf()).setDate(this.getDate() - iDayOfWeekISO + 3);
	var oJanuary1st		= new Date(oThisThursday.getFullYear(), 0, 1);
	var iJan1stWeekday	= oJanuary1st.$parseToken('N');
	
	var iYearNumber;
	var iWeekNumber;
	
	// Check if in week 52/53
	if (iDayOfYear <= (8 - iJan1stWeekday) && iJan1stWeekday > 4)
	{
		// Previous Year
		iYearNumber	= this.getFullYear() - 1;
		if (iJan1stWeekday === 5 || (iJan1stWeekday === 6 && bLastYearIsLeap))
		{
			iWeekNumber	= 53;
		}
		else
		{
			iWeekNumber	= 52;
		}
	}
	else
	{
		iYearNumber	= this.getFullYear();
	}
	
	// Check if in Week 1
	if (iYearNumber === this.getFullYear())
	{
		var iDaysInThisYear	= bThisYearIsLeap ? 366 : 365;
		if ((iDaysInThisYear - iDayOfYear) < (4 - iDayOfWeekISO))
		{
			iYearNumber	= this.getFullYear() + 1;
			iWeekNumber	= 1;
		}
	}
	
	// Check if in this year and Week 1 through 53
	if (iYearNumber	=== this.getFullYear())
	{
		iWeekNumber	= (iDayOfYear + (7 - iDayOfWeekISO) + (iJan1stWeekday - 1)) / 7;
		if (iJan1stWeekday > 4)
		{
			iWeekNumber--;
		}
	}
	
	return {iYear: iYearNumber, iWeek: iWeekNumber};
};

Date.prototype.$numberOrdinalSuffix	= function(iNumber)
{
	var iModResult	= iNumber % 10;
	if (iModResult == 1)
	{
		return 'st';
	}
	else if (iModResult == 2)
	{
		return 'nd';
	}
	else if (iModResult == 3)
	{
		return 'rd';
	}
	else
	{
		return 'th';
	}
};

Date.prototype.$stringPad	= function(sString, iLength, sPadString, sDirection)
{
	sString		= String(sString);
	iLength		= (iLength === undefined)		? 0			: iLength;
	sPadString	= (sPadString === undefined)	? ' '		: sPadString;
	sDirection	= (sPadString === undefined)	? 'right'	: sDirection;
	
	var aPattern		= sPadString.toArray();
	var iPadCharsLeft	= 0;
	var iPadCharsRight	= 0;
	var iPadCharsTotal	= sString.length - iLength;
	switch (sString.charAt(0).toLowerCase())
	{
		case 'l':
			iPadCharsLeft	= iPadCharsTotal;
			break;
			
		case 'b':
			// If pad length is uneven, pad 1 more to the right than left
			iPadCharsLeft		= Math.floor(iPadCharsTotal / 2);
			iPadCharsRight		= Math.ceil(iPadCharsTotal / 2);
			break;
			
		case 'r':
		default:
			iPadCharsRight	= iPadCharsTotal;
			break;
	}
	
	// Pad the Left
	var sLeftPad	= '';
	for (var i = 0; i < iPadCharsLeft; i++)
	{
		sLeftPad	+= aPattern[i % aPattern.length];
	}
	
	// Pad the Right
	var sRightPad	= '';
	for (var i = 0; i < iPadCharsRight; i++)
	{
		sRightPad	+= aPattern[i % aPattern.length];
	}
	
	// Return the padded string
	return sLeftPad + sString + sRightPad;
};

Date.prototype.$getFirstDayOfMonth	= function() 
{
	var day	= (this.getDay() - (this.getDate() - 1)) % 7;
	return (day < 0) ? (day + 7) : day;
};

Date.prototype.$getLastDayOfMonth	= function() 
{
	var day	= (this.getDay() + (this.$oMonths.oDaysInMonth[this.getMonth() + 1] - this.getDate())) % 7;
	return (day < 0) ? (day + 7) : day;
};

Date.prototype.$getDaysInMonth	= function() 
{
	this.$oMonths.oDaysInMonth[2]	= (this.isLeapYear() ? 29 : 28);
	return this.$oMonths.oDaysInMonth[this.getMonth() + 1];
};

Date.prototype.$getMonthFullName	= function(iMonthIndex)
{
	iMonthIndex	= ((typeof iMonthIndex != 'undefined') ? iMonthIndex : this.getMonth()) + 1; 
	return this.$oMonths.oFullNames[iMonthIndex];
};

// Formatting Data
Date.prototype.$oMonths	=	{
								oFullNames		:	{
														1	: 'January',
														2	: 'February',
														3	: 'March',
														4	: 'April',
														5	: 'May',
														6	: 'June',
														7	: 'July',
														8	: 'August',
														9	: 'September',
														10	: 'October',
														11	: 'November',
														12	: 'December'
														//13	: 'Smarch'	// Known for its lousy weather
													},
								oShortNames		:	{
														1	: 'Jan',
														2	: 'Feb',
														3	: 'Mar',
														4	: 'Apr',
														5	: 'May',
														6	: 'Jun',
														7	: 'Jul',
														8	: 'Aug',
														9	: 'Sep',
														10	: 'Oct',
														11	: 'Nov',
														12	: 'Dec'
														//13	: 'Sma'
													},
								oDaysInMonth	:	{
														1	: 31,
														2	: 28,
														3	: 31,
														4	: 30,
														5	: 31,
														6	: 30,
														7	: 31,
														8	: 31,
														9	: 30,
														10	: 31,
														11	: 30,
														12	: 31
													}
							};
Date.prototype.$oDays	=	{
								oFullNames	:	{
													0	: 'Sunday',
													1	: 'Monday',
													2	: 'Tuesday',
													3	: 'Wednesday',
													4	: 'Thursday',
													5	: 'Friday',
													6	: 'Saturday'
												},
								oShortNames	:	{
													0	: 'Sun',
													1	: 'Mon',
													2	: 'Tue',
													3	: 'Wed',
													4	: 'Thu',
													5	: 'Fri',
													6	: 'Sat'
												},
								oISONumeric	:	{
													0	: 7,
													1	: 1,
													2	: 2,
													3	: 3,
													4	: 4,
													5	: 5,
													6	: 6
												}
							};

// Aliases
Date.$oMonths	= Date.prototype.$oMonths;
Date.$oDays		= Date.prototype.$oDays;



// Parsing functions
Date.$parseFunctions 	= {count:0};
Date.$parseRegexes 		= [];

Date.$parseDate = function(input, format) {
	if (Date.$parseFunctions[format] == null) {
		Date.$createParser(format);
	}
	var func = Date.$parseFunctions[format];
	return Date[func](input);
}

Date.$createParser = function(format) {
	var funcName = "$parse" + Date.$parseFunctions.count++;
	var regexNum = Date.$parseRegexes.length;
	var currentGroup = 1;
	Date.$parseFunctions[format] = funcName;

	var code = "Date." + funcName + " = function(input){\n"
		+ "var y = -1, m = -1, d = -1, h = -1, i = -1, s = -1;\n"
		+ "var d = new Date();\n"
		+ "y = d.getFullYear();\n"
		+ "m = d.getMonth();\n"
		+ "d = d.getDate();\n"
		+ "var results = input.match(Date.$parseRegexes[" + regexNum + "]);\n"
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
			obj = Date.$formatCodeToRegex(ch, currentGroup);
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
	Date.$parseRegexes[regexNum] = new RegExp("^" + regex + "$");
	eval(code);
}

Date.$formatCodeToRegex = function(character, currentGroup) {
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
			s:"(?:" + Date.$oDays.oFullNames.join("|") + ")"};
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
			s:"(" + Date.$oMonths.oFullNames.join("|") + ")"};
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


// Date related string functions

String.escape	= function(string) 
{
	return string.replace(/('|\\)/g, "\\$1");
}

String.leftPad	= function(val, size, ch) 
{
	var result	= new String(val);
	if (ch == null) 
	{
		ch	= " ";
	}
	
	while (result.length < size) 
	{
		result	= ch + result;
	}
	
	return result;
}


/*
// DEBUG: Unit tests for Date.prototype.truncate()
debugger;
(function (oDate) {
	console.log('As provided: '	+oDate.$format('Y-m-d H:i:s.u')										+' ('+oDate.getTime()+')');
	console.log('Second: '		+oDate.truncate(Date.DATE_INTERVAL_SECOND).$format('Y-m-d H:i:s.u')	+' ('+oDate.getTime()+')');
	console.log('Minute: '		+oDate.truncate(Date.DATE_INTERVAL_MINUTE).$format('Y-m-d H:i:s.u')	+' ('+oDate.getTime()+')');
	console.log('Hour: '		+oDate.truncate(Date.DATE_INTERVAL_HOUR).$format('Y-m-d H:i:s.u')	+' ('+oDate.getTime()+')');
	console.log('Day: '			+oDate.truncate(Date.DATE_INTERVAL_DAY).$format('Y-m-d H:i:s.u')	+' ('+oDate.getTime()+')');
	console.log('Week: '		+oDate.truncate(Date.DATE_INTERVAL_WEEK).$format('Y-m-d H:i:s.u')	+' ('+oDate.getTime()+')');
	console.log('Month: '		+oDate.truncate(Date.DATE_INTERVAL_MONTH).$format('Y-m-d H:i:s.u')	+' ('+oDate.getTime()+')');
	console.log('Year: '		+oDate.truncate(Date.DATE_INTERVAL_YEAR).$format('Y-m-d H:i:s.u')	+' ('+oDate.getTime()+')');
})(new Date());
*/
