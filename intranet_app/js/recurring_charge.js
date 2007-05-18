function TimesChargedChanged()
{
	// Minumum charge and number of times charged is set
	// So calculate how much needs to be payed each time
	
	// id: EndDate
	// id: NumOfCharges
	// id: Amount
	// id: MinCharge
	// id: recurringfrequency, recurringfrequencytype
	
	// - Amount = min charge / times charged
	// - End date = times charged * recurring freq
		
	var eAmount = document.getElementById("Amount");
	var eMinCharge = document.getElementById("MinCharge");
	var eNumOfCharges = document.getElementById("NumOfCharges");
	var eEndDate = document.getElementById("EndDate");
	var eRecurFreq = document.getElementById("recurringfrequency");


	var RecurFreq = eRecurFreq.innerHTML.split(" ")[0];
	var RecurFreqType = eRecurFreq.innerHTML.split(" ")[1];

	stripDollars();
	
	eAmount.value = eMinCharge.value / eNumOfCharges.value;
	
	endDate = calculateEndDate(RecurFreq, RecurFreqType, eNumOfCharges.value);
	eEndDate.innerHTML = endDate;
	
	
	addDollars();
	
}

function AmountChanged()
{
	// Minumum charge and amount is set
	// So calculate how many times to charge
	
	// id: EndDate
	// id: NumOfCharges
	// id: Amount
	// id: MinCharge
	// id: recurringfrequency, recurringfrequencytype
	
	// - Times charged = Min Charge / amount
	// - End date = times charged * recurring freq
	
	// strip off leading dollar sign, then add it back
	
	var eAmount = document.getElementById("Amount");
	var eMinCharge = document.getElementById("MinCharge");
	var eNumOfCharges = document.getElementById("NumOfCharges");
	var eEndDate = document.getElementById("EndDate");
	var eRecurFreq = document.getElementById("recurringfrequency");
	
	var RecurFreq = eRecurFreq.innerHTML.split(" ")[0];
	var RecurFreqType = eRecurFreq.innerHTML.split(" ")[1];
	
	// check if it is fixed or not
	var fixed = document.getElementById('NumOfChargesFixed');
	if (fixed)
	{
		stripDollars();
		fixed.innerHTML = Math.ceil(eMinCharge.value / eAmount.value);
		endDate = calculateEndDate(RecurFreq, RecurFreqType, fixed.innerHTML);
		eEndDate.innerHTML = endDate;
		
		addDollars;
		
		return;
	}
	
	stripDollars();
	
	// Work out number of times charged
	eNumOfCharges.value = Math.ceil(eMinCharge.value / eAmount.value);
	
	endDate = calculateEndDate(RecurFreq, RecurFreqType, eNumOfCharges.value);
	eEndDate.innerHTML = endDate;
	
	addDollars();
}

function Init()
{
	if (document.getElementById ("EndDate")) {	
		//AddKeyUpEvents();
		AmountChanged();
		//alert('Page Loaded Succefully');
		}
	else {
		setTimeout("Init()",100);
		}
}

function stripDollars()
{
	var eAmount = document.getElementById("Amount");
	var eMinCharge = document.getElementById("MinCharge");
	
	if (eAmount.value[0] == "$")
	{
		eAmount.value = eAmount.value.split("$")[1];
	}
	if (eMinCharge.value[0] == "$")
	{
		eMinCharge.value = eMinCharge.value.split("$")[1];
	}
	//alert (eAmount.value.split("$")[0] + ":" + eAmount.value.split("$")[1]);
	//eAmount.value = eAmount.value.split("$",1)[0];
	//eMinCharge.value = eMinCharge.value.split("$",1)[0];
	
}

function addDollars()
{
	var eAmount = document.getElementById("Amount");
	var eMinCharge = document.getElementById("MinCharge");
	
	eAmount.value = "$" + eAmount.value;
	
	eMinCharge.value = "$" + eMinCharge.value;

}

function calculateEndDate(recurringfrequency, recurringfrequencytype, timescharged)
{
	// Take number of days/months/half-months, and number of times to charge
	// and return the last charge date
	var monthname= new Array("JAN","FEB","MAR","APR","MAY","JUN","JUL","AUG", "SEP","OCT","NOV","DEC");
	var now = new Date();
	var future = new Date();
	if (recurringfrequencytype == "Day(s)")
	{
		var daysinfuture = timescharged * recurringfrequency;
		
		//Add days and format
		future.setDate(now.getDate()+daysinfuture);
		var endDate = future.getDate() + " " + monthname[future.getMonth()] + ", " + future.getFullYear();
		return endDate;
	}
	else if (recurringfrequencytype == "Month(s)")
	{
		var monthsinfuture = timescharged * recurringfrequency;
		//Change the day number so that we won't have issues with being charged on say the 31st of Feb
		if (now.getDate() > 28)
		{
			future.setDate(28);
		}
		//Add months and format
		future.setMonth(now.getMonth()+monthsinfuture);
		var endDate = future.getDate() + " " + monthname[future.getMonth()] + ", " + future.getFullYear();
		return endDate;
	}
	else if (recurringfrequencytype == "Half")
	{
		// half months... umm, lets just pretend half a month if 14 days
		var halfmonthsinfuture = timescharged * recurringfrequency;
		
		//Change the day number to get around silly dates
		if (now.getDate() > 28)
		{
			future.setDate(28);
		}
		
		//If it's an odd number of half months, then add the corresponding 
		//number of whole months, and 14 days
		if ((halfmonthsinfuture % 2) == 1)
		{
			halfmonthsinfuture--;
			future.setMonth(now.getMonth()+(halfmonthsinfuture / 2));
			future.setDate(now.getDate()+14);
		}
		
		//If not, just add corresponding number of months
		else
		{
			future.setMonth(now.getMonth()+(halfmonthsinfuture / 2));
		}			
		var endDate = future.getDate() + " " + monthname[future.getMonth()] + ", " + future.getFullYear();
		return endDate;
	}
}

/*
function AddKeyUpEvents()
{
	myElement = document.getElementById ("Amount");
	myElement.addEventListener('keyup', function () { alert ('ras'); }, false);
	//alert (myElement);
}*/
