// class

function VixenValidateAdjustmentClass()
{
	// internal input array
	this._objChargeTypeData = {};
	this._objAdjustmentData = {};
	var this._strErrorMsg;
	
	this.SetChargeTypes = function(objChargeTypeData)
	{
		this._objChargeTypeData = objChargeTypeData;
	}
	
	this.SetAdjustmentData = function(objAdjustmentData)
	{
		this._objAdjustmentData = objAdjustmentData;
	}
	
	// This uses the ChargeType selected to set a value for Charge.ChargeType, Charge.Nature, Charge.Amount
	this.DeclareChargeType = function(objObject)
	{
		var strChargeType;
		var strDefaultAmount;
		var strDescription;
		var strNature;
		var strFixed;
		
		// make sure there
		if (!objObject.value)
		{
			return;
		}
		
		strChargeType = objObject.value;
		strDefaultAmount = this._objChargeTypeData[strChargeType].Amount;
		strDescription = this._objChargeTypeData[strChargeType].Description;
		
		if (this._objChargeTypeData[strChargeType].Nature == "CR")
		{
			strNature = "Credit";
		}
		else
		{
			strNature = "Debit";
		}
		
		// setup values on the form
		document.getElementById('Charge.ChargeType').innerHTML = strChargeType;
		document.getElementById('ChargeType.Description').innerHTML = strDescription;
		document.getElementById('Charge.Nature').innerHTML = strNature;
		document.getElementById('Charge.Amount').value = strDefaultAmount;
		
		// If the charge type has a fixed amount then disable the amount textbox, else enable it
		if (this._objChargeTypeData[strChargeType].Fixed)
		{
			// disable the charge amount textbox
			document.getElementById('Charge.Amount').disabled = true;
			document.getElementById('InvoiceComboBox').focus();
		}
		else
		{	
			// enable the charge amount textbox
			document.getElementById('Charge.Amount').disabled = false;
			document.getElementById('Charge.Amount').focus();
		}

		// set the charge type details for this._objAdjustmentData
		this._objAdjustmentData.ChargeType = strChargeType;
		this._objAdjustmentData.Description = strDescription;
		this._objAdjustmentData.Nature = this._objChargeTypeData[strChargeType].Nature;
		
		

		return;
	}

	this.IsValidForm = function()
	{
		var mixAmount;
		
		// check that a charge type has been declared
		if (this._objAdjustmentData.ChargeType == null)
		{
			this._strErrorMsg = "Charge Type must be specified";
			document.getElementById("ChargeType.ChargeType").focus();
			return false;
		}
		
		// check that the adjustment amount is valid
		mixAmount = document.getElementById("Charge.Amount").value;
		
		
		return true;
	}
	
	this.AddAdjustment = function()
	{
		// Validate the data in the form
		if (!this.IsValidForm())
		{
			// The data in the form is currenly invalid
			// Output some sort of error message within the form, to that effect
			document.getElementById('StatusMsg').innerHTML = this._strErrorMsg;
			document.getElementById('StatusMsg').class = "DefaultElement";  //this line currently isn't working
			
			alert(this._strErrorMsg);
			return;
		}
		
		// retrieve all data necessary to add a charge record
		intAccountId = document.getElementById('Account.Id').value;
		
	}
}


// instanciate the object
Vixen.ValidateAdjustment = new VixenValidateAdjustmentClass;

/*
window.addEventListener (
	'load',
	function ()
	{
		if (document.getElementById ('ChargeType.ChargeType'))
		{
			ValidateAdjustment.DeclareChargeType(document.getElementById ('ChargeType.ChargeType'));
		}
	},
	true
);
*/
