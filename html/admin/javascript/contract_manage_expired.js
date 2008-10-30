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
			for (i = 0; i < arrInputElements.length; i++)
			{
				if (arrInputElements[i].type == 'checkbox')
				{
					// It's a checkbox, so add it to our array
					this.arrCheckboxes.push(arrInputElements[i]);
				}
			}
			
			//alert("Indexed " + this.arrCheckboxes.length + " Checkboxes");
		}
	},
	
	// Function: selectAll()
	// Sets all checkboxes to checked
	selectAll	: function()
	{
		this._indexCheckboxes();
		for (intIndex = 0; intIndex < this.arrCheckboxes.length; intIndex++)
		{
			this.arrCheckboxes[intIndex].checked	= true;
		}
	},
	
	// Function: selectNone()
	// Sets all checkboxes to unchecked
	selectNone	: function()
	{
		this._indexCheckboxes();
		for (intIndex = 0; intIndex < this.arrCheckboxes.length; intIndex++)
		{
			this.arrCheckboxes[intIndex].checked	= false;
		}
	},
	
	// Function: _getContractById()
	// Sets all checkboxes to unchecked
	_getContractById	: function(intContractId)
	{
		objContract	= new Object();
		
		objContract.intId		= parseInt(intContractId);
		objContract.intAccount	= parseInt(document.getElementById("contract_account_" + objContract.intId).innerHTML);
		objContract.strFNN		= document.getElementById("contract_fnn_" + objContract.intId).innerHTML;
		objContract.fltPayout	= parseFloat(document.getElementById("contract_payout_charge_" + objContract.intId).innerHTML);
		objContract.fltExitFee	= parseFloat(document.getElementById("contract_exit_fee_" + objContract.intId).value);
		
		return objContract;
	},
	
	// Function: confirm()
	// Verifies that the user wants to Apply/Waive the fees for the selected Contracts
	confirm		: function(strAction, intContractId)
	{
		this._indexCheckboxes();
		
		var	arrContracts	= Array();
		
		// Did we get passed a Contract Id?
		if (intContractId != undefined)
		{
			// Check the Checkbox for this Contract
			$ID('contract_checkbox_' + intContractId).checked	= true;
		}
		
		// Work off the currently checked Contracts
		for (intIndex = 0; intIndex < this.arrCheckboxes.length; intIndex++)
		{
			if (this.arrCheckboxes[intIndex].checked)
			{
				arrContracts.push(this._getContractById(this.arrCheckboxes[intIndex].value));
			}
		}
		
		// Create summary and confirmation popup
		this._buildConfirmationPopup(strAction, arrContracts);
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
	},
	
	// Function: _actionSelected()
	_actionSelected	: function(strAction)
	{
		// Work off the currently checked Contracts
		arrContracts	= new Array();
		for (intIndex = 0; intIndex < this.arrCheckboxes.length; intIndex++)
		{
			if (this.arrCheckboxes[intIndex].checked)
			{
				arrContracts.push(this._getContractById(this.arrCheckboxes[intIndex].value));
			}
		}
		this._arrSelectedContracts	= arrContracts;
		this._strAction				= strAction;
		
		strActioning	= 'WTF';
		switch (strAction)
		{
			case 'apply':
				strActioning	= 'Applying';
				break;
			case 'waive':
				strActioning	= 'Waiving';
				break;
		}
		
		// Render the Popup that will monitor the AJAX reponses
		this._buildMonitorPopup();
		
		// Action the first contract
		this._actionNext();
	},

	// Function: _actionNext()
	_actionNext : function(objResponse)
	{
		// If objResponse is set, then we have already processed one
		if (objResponse != undefined)
		{
			// Update the last processed Contract Cell
			$elmLastActionedReponseCell				= document.getElementById('contract_action_response_' + objResponse.ContractId);
			
			if (objResponse.Success)
			{
				$elmLastActionedReponseCell.innerHTML	= "<b>Success</b>";
				$elmLastActionedReponseCell.style.color	= "#008000";
			}
			else
			{
				$elmLastActionedReponseCell.innerHTML	= "<b>" + objResponse.ErrorMessage + "</b>";
				$elmLastActionedReponseCell.style.color	= "#CC0000";
			}
			
			// Shift this Contract off the Array
			this._arrSelectedContracts.shift();
		}
		
		// Action the next Selected Contract
		if (this._arrSelectedContracts.length)
		{
			// Show a pretty little 'loading' icon
			$elmLastActionedReponseCell				= document.getElementById('contract_action_response_' + this._arrSelectedContracts[0].intId);
			$elmLastActionedReponseCell.innerHTML	= "<img src='img/template/loading.gif' width='16' height='16' />";
			
			// Send off the AJAX request
			jsonFunc = jQuery.json.jsonFunction(this._actionNext.bind(this), this._actionNext.bind(this), "Contract_ManageBreached", this._strAction);
			jsonFunc(this._arrSelectedContracts[0].intId, this._arrSelectedContracts[0].intAccount, this._arrSelectedContracts[0].fltPayout, this._arrSelectedContracts[0].fltExitFee);
		}
		else
		{
			// Show the OK button on the monitor popup
			document.getElementById("ContractMonitorPopup_OKButton").style.display	= 'block';
		}
	},
	
	// Function: _buildConfirmationPopup()
	_buildConfirmationPopup	: function(strAction, arrContracts)
	{
		objActionString	= new String(strAction);
		strActionTitle	= (new String(objActionString).charAt(0)).toUpperCase() + objActionString.substr(1);
		
		if (arrContracts.length == 0)
		{
			strHtml = "\n" + 
			"			<div id='PopupPageBody_ContractConfirmNone'>\n" + 
			"				<table border='0' width='100%'>\n" +
			"					<tr>\n" +
			"						<td style='text-align:center'>There are no Contracts selected</td>\n" +
			"					</tr>\n" + 
			"					<tr>\n" +
			"						<td>\n" +
			"							<div style='padding-top:3px;height:auto:width:100%;text-align:center'>\n" + 
			"								<div valign='center'>\n" + 
			"									<input type='button' id='ContractConfirmPopup_OKButton' name='ContractConfirmPopup_OKButton' value='OK' onclick='Vixen.Popup.Close(this)' />\n" + 
			"								</div>\n" + 
			"								<div style='clear:both;float:none'></div>\n" + 
			"							</div>\n" +
			"						</td>\n" +
			"					</tr>\n" +
			"				</table>" + 
			"			</div>\n";
		}
		else
		{
			// Determine Totals
			fltTotalPayout	= 0.0;
			fltTotalExitFee	= 0.0;
			arrAccounts	= new Array();
			for (i = 0; i < arrContracts.length; i++)
			{
				fltTotalPayout	+= arrContracts[i].fltPayout;
				fltTotalExitFee	+= arrContracts[i].fltExitFee;
				
				// Check to see if this Account is already in our Array of Accounts
				bolFound	= false;
				for (t = 0; t < arrAccounts.length; t++)
				{
					bolFound	= (arrAccounts[t] === arrContracts[i].intAccount) ? true : bolFound;
				}
				if (!bolFound)
				{
					arrAccounts.push(arrContracts[i].intAccount);
				}
			}
			
			// Generate HTML			
			strHtml = "\n" + 
"			<div id='PopupPageBody_ContractConfirm'>\n" + 
"				<div class='GroupedContent'>\n" + 
"					Are you sure you want to <strong>" + strAction + "</strong> the following Contract Fees?\n" + 
"				</div>\n" + 
"				<div class='GroupedContent'>\n" + 
"					<table class='form-data'>\n" + 
"						<tr>\n" + 
"							<td class='title'>Total Contracts : </td>\n" + 
"							<td>" + arrContracts.length + "</td>\n" + 
"						</tr>\n" + 
"						<tr>\n" + 
"							<td class='title'>Total Accounts : </td>\n" + 
"							<td>" + arrAccounts.length + "</td>\n" + 
"						</tr>\n" + 
"						<tr>\n" + 
"							<td class='title'>Payout Grand Total : </td>\n" + 
"							<td>$" + (new Number(fltTotalPayout)).toFixed(2) + "</td>\n" + 
"						</tr>\n" + 
"						<tr>\n" + 
"							<td class='title'>Exit Fee Grand Total : </td>\n" + 
"							<td>$" + (new Number(fltTotalExitFee)).toFixed(2) + "</td>\n" + 
"						</tr>\n" + 
"					</table>\n" + 
"				</div>\n" + 
"				<div style='padding-top:3px;height:auto:width:100%'>\n" + 
"					<div style='float:right'>\n" + 
"						<input type='button' id='ContractConfirmPopup_ApplyButton' name='ContractConfirmPopup_ApplyButton' value='" + strActionTitle + "' onclick='Flex.Contract_ManageExpired._actionSelected(\"" + strAction + "\")' style='margin-left:3px'></input>\n" + 
"						<input type='button' value='Cancel' onclick='Vixen.Popup.Close(this)' style='margin-left:3px'></input>\n" + 
"					</div>\n" + 
"					<div style='clear:both;float:none'></div>\n" + 
"				</div>\n" + 
"			</div>\n" + 
"			";
		}
		
		// Create the Popup
		Vixen.Popup.Create('ContractConfirm', strHtml, 'medium', 'centre', 'modal', strActionTitle + ' Contract Fees Confirmation');
	},
	
	// Function: _buildMonitorPopup()
	_buildMonitorPopup	: function()
	{
		strActioning	= 'WTF';
		switch (this._strAction)
		{
			case 'apply':
				strActioning	= 'Applying';
				break;
			case 'waive':
				strActioning	= 'Waiving';
				break;
		}
		
		// Generate HTML			
		strHtml = "\n" + 
"			<div id='PopupPageBody_ContractMonitor'>\n" + 
"				<div class='GroupedContent'>\n" + 
"					<table class='form-data'>\n" +
"						<thead>" +
"							<tr>\n" +
"								<th align='left'>Account</th>\n" +
"								<th align='left'>Service</th>\n" +
"								<th width='30%'>Status</th>\n" +
"							</tr>\n" +
"						</thead>\n" +
"						<tbody>";
		
		// Build a Row for each Contract
		for (i = 0; i < this._arrSelectedContracts.length; i++)
		{
			strHtml	+= "\n" + 
"							<tr>\n" + 
"								<td align='left'>" + this._arrSelectedContracts[i].intAccount + "</td>\n" + 
"								<td align='left'>" + this._arrSelectedContracts[i].strFNN + "</td>\n" + 
"								<td align='center' id='contract_action_response_" + this._arrSelectedContracts[i].intId +"'>&nbsp;</td>\n" + 
"							</tr>";
		}

		// Close off the popup
		strHtml	+= "\n" +
"						</tbody>" +
"					</table>\n" + 
"				</div>\n" + 
"				<div style='padding-top:3px;height:auto:width:100%'>\n" + 
"					<div align='center'>\n" + 
"						<input type='button' style='display:none' id='ContractMonitorPopup_OKButton' name='ContractMonitorPopup_OKButton' value='OK' onclick='window.location.reload()' />\n" + 
"					</div>\n" + 
"				</div>\n" + 
"			</div>\n" + 
"		";
		
		// Create the Popup
		Vixen.Popup.Create('ContractMonitor', strHtml, 'smallmedium', 'centre', 'modal', strActioning + ' Contract Fees...', null, false);
	}
});

// Init
if (Flex.Contract_ManageExpired == undefined)
{
	Flex.Contract_ManageExpired	= new Contract_ManageExpired();
}