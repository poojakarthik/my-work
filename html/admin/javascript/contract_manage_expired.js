// Class: Contract_ManageExpired
// Handles the 'Manage Expired Contracts' page in Flex
var Contract_ManageExpired	= Class.create
({
	// Function: initialize()
	// Prototype constructor
	initialize	: function()
	{
		
	},
	
	// Function: _indexCheckboxes
	_indexCheckboxes	: function()
	{
		if (this.arrCheckboxes == undefined)
		{
			this.arrCheckboxes		= new Array();
			
			// Get the list of Checkbox elements
			var arrInputElements	= document.getElementsByTagName('input');
			//alert(document.getElementsByTagName('input').length);
			//alert(arrInputElements.length);
			for (i = 0; i < arrInputElements.length; i++)
			{
				//alert(i + " is a " + arrInputElements[i].type);
				if (arrInputElements[i].type == 'checkbox')
				{
					// It's a checkbox, so add it to our array
					this.arrCheckboxes.push(arrInputElements[i]);
				}
			}
			
			alert("Indexed " + this.arrCheckboxes.length + " Checkboxes");
		}
	},
	
	// Function: selectAll()
	// Sets all checkboxes to checked
	selectAll	: function()
	{
		this._indexCheckboxes();
		for (intIndex in this.arrCheckboxes)
		{
			this.arrCheckboxes[intIndex].checked	= true;
		}
	},
	
	// Function: selectNone()
	// Sets all checkboxes to unchecked
	selectNone	: function()
	{
		this._indexCheckboxes();
		for (intIndex in this.arrCheckboxes)
		{
			this.arrCheckboxes[intIndex].checked	= false;
		}
	},
	
	// Function: confirm()
	// Verifies that the user wants to Apply/Waive the fees for the selected Contracts
	confirm		: function(strAction, intContractId)
	{
		var	arrContractIds	= Array();
		
		// Did we get passed a Contract Id?
		if (intContractId == undefined)
		{
			// No, work off the currently checked Contracts
			for (intIndex in this.arrCheckboxes)
			{
				alert("arrCheckboxes Index: " + intIndex);
				if (this.arrCheckboxes[intIndex].checked)
				{
					arrContractIds.push(this.arrCheckboxes[intIndex].value);
				}
			}
		}
		else
		{
			// Yes, only use supplied Contract
			arrContractIds.push(intContractId);
		}
		
		alert(strAction + "ing " + arrContractIds.length + " Contracts");
		
		// Create summary and confirmation popup
		// TODO
		if (bolResult)
		{
			// Confirmed, apply the fees
			// TODO
			
			alert("Confirmed!");
		}
		else
		{
			// Cancelled, act as if nothing happened
			alert("Cancelled!");
			return;
		}
	},
	
	// Function: calculatePayout()
	calculatePayout	: function(intContractId)
	{
		// Find the Input to calculate from, and the Span to output it to
		elmPercentageText	= document.getElementById("contract_payout_percentage_" + intContractId);
		elmPayoutSpan		= document.getElementById("contract_payout_charge_" + intContractId);
		elmMinMonthlySpan	= document.getElementById("contract_min_monthly_" + intContractId);
		elmMonthsLeftSpan	= document.getElementById("contract_months_left_" + intContractId);
		
		if (elmPercentageText && elmPayoutSpan && elmMinMonthlySpan && elmMonthsLeftSpan)
		{
			fltPercentage	= (parseFloat(elmPercentageText.value) > 0) ? (parseFloat(elmPercentageText.value) / 100) : 0;
			fltPayout		= new Number(parseFloat(elmMinMonthlySpan.innerHTML) * parseFloat(elmMonthsLeftSpan.innerHTML) * fltPercentage);
			
			// Output to the page
			elmPayoutSpan.innerHTML	= fltPayout.toFixed(2);
		}
	}
});

// Init
if (Flex.Contract_ManageExpired == undefined)
{
	Flex.Contract_ManageExpired	= new Contract_ManageExpired();
}