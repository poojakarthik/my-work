
// Extends the JS Date Object
Date.DATE_INTERVAL_DAY		= 'days';
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

Date.prototype.isLeapYear	= function()
{
	var iYear	= this.getFullYear();
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
		alert("Test " + (i + 1) + " of " + Date.$_UNIT_TEST_DATA_.shift.length + ": " + oDate + " + " + oTestData.iInterval + " " + oTestData.sIntervalType + " = " + oNewDate);
	}
}

