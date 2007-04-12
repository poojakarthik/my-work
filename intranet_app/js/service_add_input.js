//Initialisation	
function Init() 
{
	if (document.getElementById ("inputCount")) {
		document.getElementById ("inputCount").value = 0;
		AddManyInput(5);
		document.getElementById ("proDetails").style.display="none";
		}
	else {
		setTimeout("Init()",100);
		}
}

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

	var y=document.getElementById ("inputCount");
	var numInput = parseInt(y.value) + 1;
	y.value = numInput;
	var id1="service_" + numInput;
	var id2="confirm_" + numInput;
	var id3="icon_"+ numInput;
	var table=document.getElementById("thetable").insertRow(numInput);
	var index=table.insertCell(0);
	var box1=table.insertCell(1);
	var box2=table.insertCell(2);
	var icon=table.insertCell(3);
	var costcentres=table.insertCell(4);
	var rateplans=table.insertCell(5);
	var rateplanlink=table.insertCell(6);
	index.innerHTML=numInput + ".";
	box1.innerHTML="<input class='input-string' name='" + id1 + "' id='" + id1 + "' onkeyup='Test(" + numInput + ")'/>";
	box2.innerHTML="<input class='input-string' name='"+ id2 + "' id='" + id2 + "' onkeyup='Test(" + numInput + ")'/>";
	icon.innerHTML="<img id='"+ id3 + "' name='" + id3 + "' width=20 height=20 src='img/template/phonetypes/blank.png'/>";
	// <input type='hidden' name='servicetype_" + numInput + "' value='0'/>
	costcentres.innerHTML="<select class='input-drop' name='costcentres_" + numInput + "' id='costcentres_" + numInput + "'></select>";
	rateplans.innerHTML="<select class='input-drop' name='rateplans_" + numInput + "' id='rateplans_" + numInput + "'></select>";
}

function AddManyInput(numInput)
{
	for (i = 1; i <= numInput; i++)
	{
		AddInput();
		RenderCostCentre(document.getElementById("inputCount").value);
	}
	
}

//Testing for valid data and service types
function Test(i)
{
	PlaceImage(i);
	// tooltip for icon
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
	for (count=1; count <= document.getElementById('inputCount').value; count++)
	{
		CheckConfirmation(count);
	}
}

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

function CheckConfirmation(i)
{
	var confirmbox=document.getElementById("confirm_" + i);
	var servicebox=document.getElementById("service_" + i);
	if (confirmbox.value != servicebox.value)
	{
		confirmbox.className="input-invalid";
		document.getElementById("icon_" + i).src = "img/template/phonetypes/none.png";
	}
	else if (servicebox.value == confirmbox.value && servicebox.value != "")
	{
		confirmbox.className="input-string";
		if (GetServiceType(servicebox.value) == "landline") 
		{
			document.getElementById ("provisioningDetails").style.display="block";
		}
		return true;
	}
	else
	{
		confirmbox.className="input-string";
		document.getElementById("icon_" + i).src = "img/template/phonetypes/blank.png";
	}
	return false;
}

function PlaceImage(field)
{
	var typeservice = document.getElementById("icon_" + field);
	var box = document.getElementById ("service_" + field);
	var type = GetServiceType(box.value);
	typeservice.src = "img/template/phonetypes/" + type + ".png";
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
