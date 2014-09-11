// also create objSendData on the fly
var objSendData = new Object;
objSendData.serviceCount = 0;
var inputCount = 0;

//Initialisation	
function Init() 
{
	if (document.getElementById ("hiddenCostCentres")) {
		inputCount = 0;
		AddManyInput(5);
		document.getElementById ("provisioningDetails").style.display="none";
		ShowBusiness();
		}
	else {
		setTimeout("Init()",100);
		}
}

//Copy costcentre box from hidden source
function RenderCostCentre(j)
{
	var source=document.getElementById("hiddenCostCentres");
	var target=document.getElementById("costcentres_" + j);
	for (k=0; k<source.length; k++)
	{
		var y=document.createElement('option');
		y.value=source.options[k].value;
		y.text=source.options[k].text;
		target.add(y,null);
	}
}

//Adding inputs to the page
function AddInput()
{
	var numInput = inputCount + 1;
	inputCount++;
	var id1="service_" + numInput;
	var id2="confirm_" + numInput;
	var id3="icon_"+ numInput;
	var id4="link_" + numInput;/*what are we linking to?? -- tooltip for images*/
	var id5="indial_" + numInput;
	var id6="elb_" + numInput;
	var table=document.getElementById("thetable").insertRow(numInput);
	var index=table.insertCell(0);
	var box1=table.insertCell(1);
	var box2=table.insertCell(2);
	var icon=table.insertCell(3);
	var costcentres=table.insertCell(4);
	var rateplans=table.insertCell(5);
	//var rateplanlink=table.insertCell(6);
	var indial100=table.insertCell(6);
	var elb=table.insertCell(7);
	index.innerHTML=numInput + ".";
	box1.innerHTML="<input class='input-string' name='" + id1 + "' id='" + id1 + "' onkeyup='CheckInput(" + numInput + ")' style='width:120px' />";
	box2.innerHTML="<input class='input-string' name='"+ id2 + "' id='" + id2 + "' onkeyup='CheckInput(" + numInput + ")' style='width:120px' />";
	icon.innerHTML="<a href='#' id='" + id4 + "'><img id='"+ id3 + "' name='" + id3 + "' width=20 height=20 src='img/template/phonetypes/blank.png'/></a>";
	costcentres.innerHTML="<select class='input-drop' name='costcentres_" + numInput + "' id='costcentres_" + numInput + "'></select>";
	rateplans.innerHTML="<select class='input-drop' name='rateplans_" + numInput + "' id='rateplans_" + numInput + "'></select>";
	indial100.innerHTML="<input type='checkbox' name='" + id5 + "' id='" + id5 + "' onclick='CheckInput(" + numInput + ")' />";
	elb.innerHTML="<input type='checkbox' name='" + id6 + "' id='" + id6 + "' />";
	document.getElementById(id5).disabled = true;
	document.getElementById(id6).disabled = true;
}

//Add multiple inputs with costcentre box filled
function AddManyInput(numInput)
{
	for (i = 1; i <= numInput; i++)
	{
		AddInput();
		RenderCostCentre(inputCount);
	}
}

//Testing for valid data and service types
function CheckInput(i)
{
	//PlaceImage(i);
	var results=CheckConfirmation(i);
	var type = GetServiceType(document.getElementById("service_" + i).value);
	if (results)
	{
		switch (type)
		{
			// get the global definitions instead of hardcoding it
			// ie, $GLOBALS['*arrConstant']	['ServiceType']	[100]	['Constant']	= 'SERVICE_TYPE_ADSL';
			// (attach to the style in service_addbulk.php)
		case 'adsl':ChangeAvailablePlans(i,100);break
		case 'mobile':ChangeAvailablePlans(i,101);break
		case 'inbound':ChangeAvailablePlans(i,103);break
		
		case 'landline':
			ChangeAvailablePlans(i,102);
			break
		}
	
		// Indial Checkbox
		if (type == 'landline')
		{
			// Is the Indial box checked?
			if (document.getElementById('indial_' + i).checked)
			{
				// Enable ELB Checkbox
				document.getElementById('elb_' + i).disabled = false;
			}
			else
			{
				document.getElementById('indial_' + i).disabled	= false;	// Enable Indial100 Checkbox
				document.getElementById('elb_' + i).disabled	= true;	// Disable ELB Checkbox
				document.getElementById('elb_' + i).checked		= false;	// Clear ELB Checkbox
			}
		}
		else
		{
			document.getElementById('indial_' + i).disabled	= true;	// Disable Indial100 Checkbox
			document.getElementById('indial_' + i).checked	= false;	// Clear Indial100 Checkbox
			document.getElementById('elb_' + i).disabled	= true;	// Disable ELB Checkbox
			document.getElementById('elb_' + i).checked		= false;	// Clear ELB Checkbox
		}
	}

	// check the entire list to see if a valid landline number is entered
	document.getElementById ("provisioningDetails").style.display="none";
	for (count=1; count <= inputCount; count++)
	{
		CheckConfirmation(count);
	}
}

//When a valid FNN is entered, change plans to that type
function ChangeAvailablePlans(j, planType)
{
	var source=document.getElementById("hiddenPlans" + planType);
	var target=document.getElementById("rateplans_" + j);
	target.innerHTML="";
	for (k=0; k<source.length; k++)
	{
		var y=document.createElement('option');
		y.value=source.options[k].value;
		y.text=source.options[k].text;
		target.add(y,null);
	}
}

//RENAME! Check that entered information is correct, and inform user
function CheckConfirmation(i)
{
	var confirmbox = document.getElementById("confirm_" + i);
	var servicebox = document.getElementById("service_" + i);
	var icon = document.getElementById("icon_" + i);
	var link = document.getElementById("link_" + i);
	
	if (confirmbox.value != servicebox.value)
	{
		confirmbox.className="input-invalid";
		document.getElementById("icon_" + i).src = "img/template/phonetypes/none.png";
		link.title="Invalid";
	}
	else if (servicebox.value == confirmbox.value && servicebox.value != "")
	{
		if (IsValidFNN(servicebox.value))
		{
			// code from placeimage(i) now included here
		
			var type = GetServiceType(servicebox.value);
			icon.src = "img/template/phonetypes/" + type + ".png";
			link.title=type.substring(0,1).toUpperCase() + type.substring(1,type.length);
			
			confirmbox.className="input-string-valid";
			if (type == "landline") 
			{
				document.getElementById ("provisioningDetails").style.display="block";
			}
			return true;
		}
	}
	else
	{
		confirmbox.className="input-string";
		document.getElementById("icon_" + i).src = "img/template/phonetypes/blank.png";
		link.title="";
	}
	return false;
}

//Functions to check the service type
function GetServiceType(strinput)
{
	var strFNN = this.Trim(strinput);
	var strPrefix = strFNN.substring(0,2);
	var intNine = parseInt('9' + strFNN);
	var strNine = '9' + strFNN;
	var intNineasString = intNine+'';
	
	if (IsValidFNN(strFNN))
	{
		if (intNineasString == strNine && (strPrefix == '02' || strPrefix == '03' || strPrefix == '07' || strPrefix == '08' || strPrefix == '09'))
		{
			return 'landline';
		}
		
		if (strPrefix == '04')
		{
			return 'mobile';
		}
		
		if (strPrefix == '13' || strPrefix == '18' || strPrefix == '19')
		{
			return 'inbound';
		}	
	
		var strSuffix = strFNN.substring(strFNN.length-1,strFNN.length).toLowerCase();	
		
		if (strSuffix == 'i')
		{
			return 'adsl';
		}
	}
	
	if (strFNN="")
	{
		return 'blank';
	}
	
	return 'none';
}

function IsValidFNN(strInput)
{
	exp = /^(0\d{9}[i]?$)|(13\d{4}$)|(1[389]00\d{6})$/;
	if (exp.test(strInput))
	{
		return true;
	}
	return false;
}

//Copyright Ben Nadel @ KinkySolutions.com 2006
function Trim(strText)
{
		return(strText.replace(new RegExp("^([\\s]+)|([\\s]+)$", "gm"), ""));
}

//Validate data before sending off to backend
function Save()
{

	// TODO! Also check if a plan has been selected, kinda important!

	// Check all the inputs to make sure they are good, return true if valid
	// otherwise return false
	// use the title of the image as reference
	
	/* $results format:
	[$results] -	[account]
				 	[serviceCount]
					[service1] - 	[FNN]
									[CostCentre]
									[Plan]
									[inputID] <- to be added
						|
					[serviceN]
					
	need to add inputID so that instead of just alerting an error, we
	can highlight the erronous service using getElementById				
	*/
	
	objSendData.serviceCount = 0;
	objSendData.account = document.getElementById("Account").value;
	var numGood = 0;
	var numBad = 0;
	for (count=1; count <= inputCount; count++)
	{
		var link=document.getElementById("link_" + count);
		var servicebox=document.getElementById("service_" + count);
		var ccinput=document.getElementById("costcentres_" + count);
		var rpinput=document.getElementById("rateplans_" + count);
		var indial=document.getElementById("indial_" + count);
		var elb=document.getElementById("elb_" + count);
		
		if (link.title != "None" && link.title != "" && link.title != "Invalid") 
		{
			// Check whether a plan has been selected
			if (rpinput.selectedIndex == 0)
			{
				alert ("Please select a plan for service '" + servicebox.value + "'.");
				return false;
			}
			
			// this particular input is valid, yay
			changeValid("confirm_" + count, 1);
			// now add this entry to objSendData
			objSendData.serviceCount++;
			objSendData["service" + objSendData.serviceCount] = { 	"FNN" 			: servicebox.value, 
														"CostCentre" 	: ccinput.options[ccinput.selectedIndex].value,
														"Plan"			: rpinput.options[rpinput.selectedIndex].value,
														"Indial100"	: indial.checked,
														"ELB"		: elb.checked
														};
			
			// if use enters in the same FNN more than once, it will successfully
			// add the first number (with provisioning, costcentre, rate) but fail
			// on the second, and not do any after that
			
			if (GetServiceType(servicebox.value) == 'landline')
			{
				SaveProvisioning();
			}
			numGood++;
		}
		else if (link.title == "")
		{
			document.getElementById("confirm_" + count).className="input-string";
		}
		else 
		{
			changeValid("confirm_" + count, 0);
			numBad++;
		}
	}
	
	// check data entered into provisioning details, only if needed
	
	if (numBad > 0 || numGood == 0)
	{
		alert ("Please enter valid Service #");
		return false;
	}
	else if (document.getElementById('provisioningDetails').style.display == "block")
	{
		var result = CleanInput();
		if (result != false)
		{
			return true;
		}
		return false;
	}
	else 
	{	
		return true;
	}
	
}

function SaveProvisioning()
{
	/* Need to change the below code to reflect the elements on the page (eg line 246)
	   plus any verification as reqd (but i'm assuming most of that has already been
	   handled by other javascript functs)*/
	var DOBday = document.getElementById('DOB').childNodes[0];	
	var DOBmonth = document.getElementById('DOB').childNodes[2];
	var DOByear = document.getElementById('DOB').childNodes[4];
	var srvAdrTyp = document.getElementById('ServiceAddressType');
	var srvStrTyp = document.getElementById('ServiceStreetType');
	var srvState = document.getElementById('ServiceState');
	
	
	objSendData["Provisioning"] = {
		'Residential'					: document.getElementById('Residential:TRUE').checked,
		'BillName'						: document.getElementById('BillName').value,
		'BillAddress1'					: document.getElementById('BillAddress1').value,
		'BillAddress2'					: document.getElementById('BillAddress2').value,
		'BillLocality'					: document.getElementById('BillLocality').value,
		'BillPostcode'					: document.getElementById('BillPostcode').value,
		'EndUserTitle'					: document.getElementById('EndUserTitle').value,
		'EndUserGivenName'				: document.getElementById('EndUserGivenName').value,
		'EndUserFamilyName'				: document.getElementById('EndUserFamilyName').value,
		'EndUserCompanyName'			: document.getElementById('EndUserCompanyName').value,
		'DateOfBirthday'				: DOBday.options[DOBday.selectedIndex].value,
		'DateOfBirthmonth'				: DOBmonth.options[DOBmonth.selectedIndex].value,
		'DateOfBirthyear'				: DOByear.options[DOByear.selectedIndex].value,
		'Employer'						: document.getElementById('Employer').value,
		'Occupation'					: document.getElementById('Occupation').value,
		'ABN'							: document.getElementById('ABN').value,
		'TradingName'					: document.getElementById('TradingName').value,
		'ServiceAddressType'			: srvAdrTyp.options[srvAdrTyp.selectedIndex].value,
		'ServiceAddressTypeNumber'		: document.getElementById('ServiceAddressTypeNumber').value,
		'ServiceAddressTypeSuffix'		: document.getElementById('ServiceAddressTypeSuffix').value,
		'ServiceStreetNumberStart'		: document.getElementById('ServiceStreetNumberStart').value,
		'ServiceStreetNumberEnd'		: document.getElementById('ServiceStreetNumberEnd').value,
		'ServiceStreetNumberSuffix'		: document.getElementById('ServiceStreetNumberSuffix').value,
		'ServiceStreetName'				: document.getElementById('ServiceStreetName').value,
		'ServiceStreetType'				: srvStrTyp.options[srvStrTyp.selectedIndex].value,
		'ServiceStreetTypeSuffix'		: document.getElementById('ServiceStreetTypeSuffix').value,
		'ServicePropertyName'			: document.getElementById('ServicePropertyName').value,
		'ServiceLocality'				: document.getElementById('ServiceLocality').value,
		'ServiceState'					: srvState.options[srvState.selectedIndex].value,
		'ServicePostcode'				: document.getElementById('ServicePostcode').value
	};
}
	

//Change appearance of input to valid/invalid
function changeValid(inputID, bolValid)
{
	var inputBox = document.getElementById (inputID);
	if (bolValid)
	{
		// Change display properties to show that this input is valid
		inputBox.className = "input-string-valid";
	}	
	else
	{
		// Change display properties to show that this input is invalid
		inputBox.className = "input-invalid";
	}
}

//Submit
function Submit()
{
	if (Save())
	{
		//alert("Number of services to be added: " + objSendData.serviceCount);
		AjaxSend("service_addbulk.php", objSendData);
	}
}


// AJAX handler
function ajaxHandler(object)
{
	/* Reply to AJAX format:
		[$arrReply] -	[serviceCount]
						[service1] - 	[saved]
										[inputID] <- to be added
							|
						[serviceN]
						[errorCount]
						[error1] -		[errorDescription]
										[inputID] <- to be added
							|
						[errorN]
	
	
	
	
	*/
	// now we have the results back from php
	// do something to inform user
	
	/*
	//alert ('Errors: ' + object.errorCount);
	var alertString="";
	for (attrib in object)
	{
		alertString = alertString + "\n" + attrib + ":" + object[attrib];
	
	}
	alert(alertString);
	
	*/

	if (object.errorCount == 0)
	{
		// redirect to another page
		//alert ("All services were added successfully");
		window.location = "./account_view.php?Id=" + document.getElementById("Account").value;
		return;
	}
	else
	{
		var alertString = "";
		for (count=1; count <= object.errorCount; count++)
		{
			alertString = alertString + "\n" + "Error: " + object["error" + count];
		}	
		
		
		// If for some unforseen reason, some are saved and some error, handle that
		// here.  For instance, add flag on input saying 'already added', and ignore
		// next time submit() is run
		/*for (count=1; count <= object.serviceCount; count++)
		{
			if (object["service" + count])
			{
				//alertString = alertString + "\n" + "Success: " + "service" + count + " " + object["service" + count];
			}
			else
			{
				//alertString = alertString + "\n" + "Failure: " + "service" + count;
			}
		}*/
	
		alert(alertString);
	}
}

function ajaxError(er, reply)
{
	alert(reply);
}


// TO BE DELEDTED
// ... but not yet

function Test()
{
/*
	var DOBday = document.getElementById('DOB').childNodes[0];	
	var DOBmonth = document.getElementById('DOB').childNodes[2];
	var DOByear = document.getElementById('DOB').childNodes[4];
	var srvAdrTyp = document.getElementById('ServiceAddressType');
	var srvStrTyp = document.getElementById('ServiceStreetType');
	var srvState = document.getElementById('ServiceState');
	alert (DOBday.parentNode);
	alert (DOBmonth);
*/

//alert (document.getElementById('DOB').firstChild.selectedIndex + ":" + document.getElementById('DOB').childNodes[2].selectedIndex);
//alert (document.getElementById('DOB').childNodes[0].selectedIndex);
	//document.getElementById('Residential:TRUE').checked;
	document.getElementById('BillName').value = 'MeBill';
	document.getElementById('BillAddress1').value = 'Somewhere';
	document.getElementById('BillAddress2').value = '';
	document.getElementById('BillLocality').value = 'MyTown';
	document.getElementById('BillPostcode').value = '4056';
	document.getElementById('EndUserTitle').value = '';
	document.getElementById('EndUserGivenName').value = 'Bob';
	document.getElementById('EndUserFamilyName').value = 'Marleyt';
	document.getElementById('EndUserCompanyName').value = 'MyCompany';
	//document.getElementById('DateOfBirth']['day').value = '';
	//document.getElementById('DateOfBirth']['month').value = '';
	//document.getElementById('DateOfBirth']['year').value = '';
	document.getElementById('Employer').value = '';
	document.getElementById('Occupation').value = '';
	document.getElementById('ABN').value = '65108228191';
	document.getElementById('TradingName').value = '';
	document.getElementById('ServiceAddressType').selectedIndex = 6;
	document.getElementById('ServiceAddressTypeNumber').value = '2';
	document.getElementById('ServiceAddressTypeSuffix').value = '';
	document.getElementById('ServiceStreetNumberStart').value = '3';
	document.getElementById('ServiceStreetNumberEnd').value = '';
	document.getElementById('ServiceStreetNumberSuffix').value = '';
	document.getElementById('ServiceStreetName').value = 'MyStreet';
	document.getElementById('ServiceStreetType').selectedIndex = 3;
	document.getElementById('ServiceStreetTypeSuffix').value = '';
	document.getElementById('ServicePropertyName').value = '';
	document.getElementById('ServiceLocality').value = 'Somewhereovertherainnbow';
	document.getElementById('ServiceState').selectedIndex = 4;
	document.getElementById('ServicePostcode').value = '4032';

}
