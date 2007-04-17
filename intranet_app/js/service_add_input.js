// also create objSendData on the fly
var objSendData = new Object;
objSendData.serviceCount=0;
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
	var id4="link_" + numInput;
	var table=document.getElementById("thetable").insertRow(numInput);
	var index=table.insertCell(0);
	var box1=table.insertCell(1);
	var box2=table.insertCell(2);
	var icon=table.insertCell(3);
	var costcentres=table.insertCell(4);
	var rateplans=table.insertCell(5);
	var rateplanlink=table.insertCell(6);
	index.innerHTML=numInput + ".";
	box1.innerHTML="<input class='input-string' name='" + id1 + "' id='" + id1 + "' onkeyup='CheckInput(" + numInput + ")' style='width:120px' />";
	box2.innerHTML="<input class='input-string' name='"+ id2 + "' id='" + id2 + "' onkeyup='CheckInput(" + numInput + ")' style='width:120px' />";
	icon.innerHTML="<a href='#' id='" + id4 + "'><img id='"+ id3 + "' name='" + id3 + "' width=20 height=20 src='img/template/phonetypes/blank.png'/></a>";
	costcentres.innerHTML="<select class='input-drop' name='costcentres_" + numInput + "' id='costcentres_" + numInput + "'></select>";
	rateplans.innerHTML="<select class='input-drop' name='rateplans_" + numInput + "' id='rateplans_" + numInput + "'></select>";
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
		case 'landline':ChangeAvailablePlans(i,102);break
		case 'adsl':ChangeAvailablePlans(i,100);break
		case 'mobile':ChangeAvailablePlans(i,101);break
		case 'inbound':ChangeAvailablePlans(i,103);break
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
function Validate()
{

	// TODO! Also check if a plan has been selected, kinda important!

	// Check all the inputs to make sure they are good, return true if valid
	// otherwise return false
	// use the title of the image as reference
	
	/* $results format:
	[$results] - 	[serviceCount]
					[service1] - 	[FNN]
									[CostCentre]
									[Plan]
									[Type]
						|
					[serviceN]
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
		
		if (link.title != "None" && link.title != "" && link.title != "Invalid") 
		{
			// this particular input is valid, yay
			changeValid("confirm_" + count, 1);
			// now add this entry to objSendData
			objSendData.serviceCount++;
			objSendData["service" + count] = { 	"FNN" 			: servicebox.value, 
												"CostCentre" 	: ccinput.options[ccinput.selectedIndex].value,
												"Plan"			: rpinput.options[rpinput.selectedIndex].value};
			numGood++;
		}
		else if (link.title == "")
		{
			//document.getElementById("service_" + count).className="input-string";
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
		return CleanInput();
	}
	else 
	{	
		return true;
	}
	
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
	if (Validate())
	{
		AjaxSend("service_addbulk.php", objSendData);
	}
}


// AJAX handler
function ajaxHandler(object)
{
	// now we have the results back from php
	// do something to inform user

	alert(object);
	/*var tobealerted = "";
	for (i=1; i<=object["serviceCount"]; i++)
	{
		tobealerted = tobealerted + "\r\n" + object["service" + i]["FNN"] + ":" + object["service" + i]["CostCentre"] + ":" + object["service" + i]["Plan"] + ":" + object["service" + i]["Type"] + ";";
	}
	alert(tobealerted);*/
}

function ajaxError(er, reply)
{
	alert(reply);
}
