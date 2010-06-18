
//
// DEPRECATED OBJECT, DO NOT USE, use date.js prototype extensions instead.
//
throw ('Reflex_Date_Format has been deprecated, please use the Date prototype extensions (e.g. new Date().$format("Y-m-d");)');


















// This is a Static-only Class at the moment, so we won't bother using Prototype for it
/*var Reflex_Date_Format	= Class.create
({
	
});*/

var Reflex_Date_Format	= {};

// Static Methods
Reflex_Date_Format.format	= function(sFormat, mDate)
{
	// Accept a Date object, seconds since Epoch (1970-01-01), or no value (default to current time)
	var oDate;
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
					sOutput		+= Reflex_Date_Format.parseToken(oDate, sFormat.charAt(i));
				}
				break;
		}
	}
	
	//alert("Formatted " + oDate + " as '" + sOutput + "' according to formatting string '" + sFormat + "'");
	
	return sOutput;
};

Reflex_Date_Format.parseToken	= function(oDate, sToken)
{
	switch (sToken)
	{
		// DAY
		case 'd':
			// Day of the month, 2 digits with leading zeros
			return oDate.getDate().toPaddedString(2);
			break;
		case 'D':
			// A textual representation of a day, three letters
			return Reflex_Date_Format.oDays.oShortNames[oDate.getDay()];
			break;
		case 'j':
			// Day of the month without leading zeros
			return oDate.getDate();
			break;
		case 'l':
			// A full textual representation of the day of the week
			return Reflex_Date_Format.oDays.oFullNames[oDate.getDay()];
			break;
		case 'N':
			// ISO-8601 numeric representation of the day of the week
			return Reflex_Date_Format.oDays.oISONumeric[oDate.getDay()];
			break;
		case 'S':
			// English ordinal suffix for the day of the month, 2 characters
			return Reflex_Date_Format.numberOrdinalSuffix(oDate.getDate());
			break;
		case 'w':
			// Numeric representation of the day of the week
			return oDate.getDay();
			break;
		case 'z':
			// The day of the year (starting from 0)
			var iDayOfMonth		= oDate.getDate();
			var iMonthOfYear	= oDate.getMonth() + 1;
			
			var iDayOfYear	= 0;
			
			// Months to date
			for (var i = 1; i < iMonthOfYear; i++)
			{
				iDayOfYear	+= Reflex_Date_Format.oMonths.oDaysInMonth[i];
				
				// Leap Year in Feb
				if (iMonthOfYear === 2 && Reflex_Date_Format.isLeapYear(oDate.getFullYear()))
				{
					iDayOfYear++;
				}
			}
			
			// How far we are into the month
			return iDayOfYear + iDayOfMonth;
			break;
		
		// WEEK
		case 'W':
			return Reflex_Date_Format.calculateISOWeekDate(oDate).iWeek;
			break;
		
		// MONTH
		case 'F':
			// A full textual representation of a month, such as January or March
			return Reflex_Date_Format.oMonths.oFullNames[oDate.getMonth() + 1];
			break;
		case 'm':
			// Numeric representation of a month, with leading zeros
			return (oDate.getMonth() + 1).toPaddedString(2);
			break;
		case 'M':
			// A short textual representation of a month, three letters
			return Reflex_Date_Format.oMonths.oShortNames[oDate.getMonth() + 1];
			break;
		case 'n':
			// Numeric representation of a month, without leading zeros
			return oDate.getMonth() + 1;
			break;
		case 't':
			// Number of days in the given month
			var iMonth			= oDate.getMonth() + 1;
			var iDaysInMonth	= Reflex_Date_Format.oMonths.oDaysInMonth[iMonth];
			if (iMonth === 2 && Reflex_Date_Format.isLeapYear(oDate.getFullYear()))
			{
				iDaysInMonth++;
			}
			return iDaysInMonth;
			break;
		
		// YEAR
		case 'L':
			// Whether it's a leap year
			return Reflex_Date_Format.isLeapYear(oDate.getFullYear());
			break;
		case 'o':
			// ISO-8601 year number. This has the same value as Y, except that if the ISO week number (W) belongs to the previous or next year, that year is used instead.
			return Reflex_Date_Format.calculateISOWeekDate(oDate).iYear;
			break;
		case 'Y':
			// A full numeric representation of a year, 4 digits
			return oDate.getFullYear();
			break;
		case 'y':
			// A two digit representation of a year
			var iYear		= oDate.getFullYear();
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
			var	iHours	= oDate.getHours();
			return (iHours <= 11) ? 'am' : 'pm';
			break;
		case 'A':
			// Uppercase Ante meridiem and Post meridiem
			return Reflex_Date_Format.parseToken(oDate, 'a').toUpperCase();
			break;
		case 'B':
			// Swatch Internet time
			var iSecondsInSwatchUnit	= 86400 / 1000;
			
			// Caculate Time
			var	iSeconds	= (oDate.getHours() * 3600) + (oDate.getMinutes() * 60) + oDate.getSeconds();
			return Math.floor(iSeconds / iSecondsInSwatchUnit).toPaddedString(3);
			break;
		case 'g':
			// 12-hour format of an hour without leading zeros
			var iHours	= oDate.getHours();
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
			return oDate.getHours();
			break;
		case 'h':
			// 12-hour format of an hour with leading zeros
			return Reflex_Date_Format.parseToken(oDate, 'g').toPaddedString(2);
			break;
		case 'H':
			// 24-hour format of an hour with leading zeros
			return oDate.getHours().toPaddedString(2);
			break;
		case 'i':
			// Minutes with leading zeros
			return oDate.getMinutes().toPaddedString(2);
			break;
		case 's':
			// Seconds, with leading zeros
			return oDate.getSeconds().toPaddedString(2);
			break;
		case 'u':
			// Microseconds
			return (oDate.getMilliseconds() * 1000).toPaddedString(2);
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
			var iOffset	= oDate.getTimezoneOffset();
			return ((iOffset < 0) ? '-' : '+') + Math.floor(iOffset / 60).toPaddedString(2) + (iOffset % 60).toPaddedString(2);
			break;
		case 'P':
			// Difference to Greenwich time (GMT) with colon between hours and minutes
			var iOffset	= oDate.getTimezoneOffset();
			return ((iOffset < 0) ? '-' : '+') + Math.floor(iOffset / 60).toPaddedString(2) + ':' + (iOffset % 60).toPaddedString(2);
			break;
		case 'T':
			// Timezone abbreviation
			// TODO
			throw "'T' token is not supported yet!";
			break;
		case 'Z':
			// Timezone offset in seconds. The offset for timezones west of UTC is always negative, and for those east of UTC is always positive.
			return oDate.getTimezoneOffset() * 60;
			break;
		
		// FULL DATE/TIME
		case 'c':
			// ISO 8601 date
			return Reflex_Date_Format.format(oDate, 'Y-m-d\TH:i:sP');
			break;
		case 'r':
			// RFC 2822 formatted date
			return Reflex_Date_Format.format(oDate, 'D, j M Y H:i:s O');
			break;
		case 'U':
			// Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)
			return oDate.getTime() / 1000;
			break;
		
		// DEFAULT: Return the Token
		default:
			return sToken;
			break;
	}
};

Reflex_Date_Format.calculateISOWeekDate	= function(oDate)
{
	// ISO-8601 week number of year, weeks starting on Monday
	var iDayOfYear		= Reflex_Date_Format.parseToken(oDate, 'z');
	var iDayOfWeekISO	= Reflex_Date_Format.parseToken(oDate, 'N');
	
	// Algorithm from -- http://www.personal.ecu.edu/mccartyr/ISOwdALG.txt
	var bThisYearIsLeap	= Reflex_Date_Format.isLeapYear(oDate.getFullYear());
	var bLastYearIsLeap	= Reflex_Date_Format.isLeapYear(oDate.getFullYear() - 1);
	
	var oThisThursday	= new Date(oDate.valueOf()).setDate(oDate.getDate() - iDayOfWeekISO + 3);
	var oJanuary1st		= new Date(oThisThursday.getFullYear(), 0, 1);
	var iJan1stWeekday	= Reflex_Date_Format.parseToken(oJanuary1st, 'N');
	
	var iYearNumber;
	var iWeekNumber;
	
	// Check if in week 52/53
	if (iDayOfYear <= (8 - iJan1stWeekday) && iJan1stWeekday > 4)
	{
		// Previous Year
		iYearNumber	= oDate.getFullYear() - 1;
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
		iYearNumber	= oDate.getFullYear();
	}
	
	// Check if in Week 1
	if (iYearNumber === oDate.getFullYear())
	{
		var iDaysInThisYear	= bThisYearIsLeap ? 366 : 365;
		if ((iDaysInThisYear - iDayOfYear) < (4 - iDayOfWeekISO))
		{
			iYearNumber	= oDate.getFullYear() + 1;
			iWeekNumber	= 1;
		}
	}
	
	// Check if in this year and Week 1 through 53
	if (iYearNumber	=== oDate.getFullYear())
	{
		iWeekNumber	= (iDayOfYear + (7 - iDayOfWeekISO) + (iJan1stWeekday - 1)) / 7;
		if (iJan1stWeekday > 4)
		{
			iWeekNumber--;
		}
	}
	
	return {iYear: iYearNumber, iWeek: iWeekNumber};
};

Reflex_Date_Format.isLeapYear	= function(iYear)
{
	return (((iYear % 4 === 0) && (iYear % 100 !== 0)) || iYear % 400 === 0);
};

Reflex_Date_Format.numberOrdinalSuffix	= function(iNumber)
{
	if (iNumber == 1)
	{
		return 'st';
	}
	else if (iNumber == 2)
	{
		return 'nd';
	}
	else if (iNumber == 3)
	{
		return 'rd';
	}
	else
	{
		return 'th';
	}
};

Reflex_Date_Format.stringPad	= function(sString, iLength, sPadString, sDirection)
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

Reflex_Date_Format.getFirstDayOfMonth	= function(oDate) 
{
	var day = (oDate.getDay() - (oDate.getDate() - 1)) % 7;
	return (day < 0) ? (day + 7) : day;
}

Reflex_Date_Format.getLastDayOfMonth	= function(oDate) 
{
	var day = (oDate.getDay() + (Reflex_Date_Format.oMonths.oDaysInMonth[oDate.getMonth()] - oDate.getDate())) % 7;
	return (day < 0) ? (day + 7) : day;
}

Reflex_Date_Format.getDaysInMonth	= function(oDate) 
{
	Reflex_Date_Format.oMonths.oDaysInMonth[1] = Reflex_Date_Format.isLeapYear(oDate.getFullYear()) ? 29 : 28;
	return Reflex_Date_Format.oMonths.oDaysInMonth[oDate.getMonth()];
}

// Formatting Data
Reflex_Date_Format.oMonths	=	{
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
															12	: 'December',
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
															12	: 'Dec',
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
															12	: 31,
														}
								};
Reflex_Date_Format.oDays	=	{
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