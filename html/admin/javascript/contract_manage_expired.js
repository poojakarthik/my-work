// Class: Contract_ManageExpired
// Handles the 'Manage Expired Contracts' page in Flex
var Contract_ManageExpired	= Class.create
({
	// Function: initialize()
	// Prototype constructor
	initialize	: function()
	{
		alert("Initialising Contract_ManageExpired...");
		
		// Properties
		this.arrCheckboxes	= new Array();
		
		// Get the list of Checkbox elements
		var	arrInputElements	= document.getElementsByTagName('input');
		for (intIndex in arrInputElements)
		{
			alert(intIndex + " is a " + arrInputElements[intIndex].type);	
			if (arrInputElements[intIndex].type == 'checkbox')
			{
				// It's a checkbox, so add it to our array
				this.arrCheckboxes.push(arrInputElements[intIndex]);
			}
		}
	},
	
	// Function: selectAll()
	// Sets all checkboxes to checked
	selectAll	: function()
	{
		alert("Selecting All...");
		for (intIndex in this.arrCheckboxes)
		{
			this.arrCheckboxes[intIndex].checked	= true;
		}
	},
	
	// Function: selectNone()
	// Sets all checkboxes to unchecked
	selectNone	: function()
	{
		alert("Selecting None...");
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
			// TODO
		}
		else
		{
			// Yes, only use supplied Contract
			arrContractIds.push(intContractId);
		}
		
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
		// TODO
	}
});

// Init
if (Flex.Contract_ManageExpired == undefined)
{
	Flex.Contract_ManageExpired	= new Contract_ManageExpired();
}