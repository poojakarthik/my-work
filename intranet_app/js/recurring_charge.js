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
	
	stripDollars();
	
	// Work out number of times charged
	eNumOfCharges.value = Math.ceil(eMinCharge.value / eAmount.value);
	
	endDate = calculateEndDate(RecurFreq, RecurFreqType, eNumOfCharges.value);
	eEndDate.innerHTML = endDate;
	
	addDollars();
}

function MinChargeChanged()
{
	// Minumum charge is set, assume amount is also set
	// So calculate how many times to charge
	// ie AmountChanged();
}

function Init()
{
	if (document.getElementById ("Amount")) {	
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
	var now = new Date();
	if (recurringfrequencytype == "Day(s)")
	{
		//var daysinfuture = eNumOfCharges.value * RecurFreq;
		alert ('asdf');
	}
	else if (recurringfrequencytype == "Month(s)")
	{
		var monthsinfuture = timescharged * recurringfrequency;
		
		var targetyear = now.getFullYear() + Math.floor((monthsinfuture + now.getMonth()) / 12);
		var targetmonth = (now.getMonth() + monthsinfuture) % 12;
		var targetday = now.getDay();
		
		//alert (eEndDate);
		var monthname= new Array("JAN","FEB","MAR","APR","MAY","JUN","JUL","AUG", "SEP","OCT","NOV","DEC");
		endDate = targetday + " " + monthname[targetmonth] + ", " + targetyear;
		return endDate;	
	}
	else if (recurringfrequencytype == "Half Month(s)")
	{
		// half months... umm, lets just pretend half a month if 14 days
		var monthsinfuture = eNumOfCharges.value * Math.floor(RecurFreq / 2);
		// floor (/2) to get full month
		// add 14 days for half month (if needed, use mod)
		
		// if odd number of half-months, 
		// if in first half (<15)
			//  add 14 days
		// else second half
			// add month  then minus 14 days
		// when adding full months
		// if date > day of target month
			// day = last day of month
	}
	else
	{
		alert ('ahh, why wont you work');
	}
}

/*
function AddKeyUpEvents()
{
	myElement = document.getElementById ("Amount");
	myElement.addEventListener('keyup', function () { alert ('ras'); }, false);
	//alert (myElement);
}*/
