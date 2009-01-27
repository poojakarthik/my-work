// Class: Invoice
// Handles the Invoices in Flex
var Invoice	= Class.create
({	
	// Function: initialize()
	// Prototype constructor
	initialize	: function()
	{
		
	},
	
	_getInvoiceRunType		: function(intInvoiceRunType)
	{
		switch (intInvoiceRunType)
		{
			case 4:
				strInvoiceRunType	= 'Interim';
				break;
			case 5:
				strInvoiceRunType	= 'Final';
				break;
			default:
				strInvoiceRunType	= '';
		}
		return strInvoiceRunType;
	},
	
	generateInterimInvoice	: function(intAccount, intInvoiceRunType)
	{
		strInvoiceRunType	= this._getInvoiceRunType(intInvoiceRunType);
		if (!strInvoiceRunType)
		{
			$Alert("There was an error when trying to generate the Invoice. ("+intInvoiceRunType+" is not a valid Invoice Run Type)");
			return;
		}
		
		// Show the Loading Splash
		Vixen.Popup.ShowPageLoadingSplash("Generating "+strInvoiceRunType+" Invoice...", null, null, null, 1);
		
		// Perform AJAX query
		var fncJsonFunc		= jQuery.json.jsonFunction(this._generateInterimInvoiceResponse.bind(this), null, 'Invoice', 'generateInterimInvoice');
		fncJsonFunc(intAccount, intInvoiceRunType);
	},
	
	_generateInterimInvoiceResponse	: function(objResponse)
	{
		// Close the Splash and display the Summary
		Vixen.Popup.ClosePageLoadingSplash();
		
		// Did we succeed?
		if (objResponse.Success === false)
		{
			$Alert(objResponse.ErrorMessage);
			return;
		}

		strInvoiceRunType	= this._getInvoiceRunType(intInvoiceRunType);
		if (!strInvoiceRunType)
		{
			$Alert("There was an error when trying to generate the Invoice. ("+objResponse.intInvoiceRunType+" is not a valid Invoice Run Type)");
			return;
		}
		
		// Render Invoice Summry Popup
		var strHTML	= "\n" + 
		"<div class='GroupedContent'>\n" + 
		"	<div>\n" + 
		"		<span>The Interim Invoice for "+objResponse.intAccountId+" has been successfully generated.</span>\n" + 
		"	</div>\n" + 
		"	<table class='reflex' style='margin-top: 8px; margin-bottom: 8px;' width='100%'>\n" + 
		"		<tbody>\n" + 
		"			<tr>\n" + 
		"				<td style='vertical-align:top;text-align:left;'>Billing Period</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>"+objResponse.strBillingPeriod+"</td>\n" + 
		"			</tr>\n" + 
		"			<tr>\n" + 
		"				<td style='vertical-align:top;text-align:left;'>Account No.</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>"+objResponse.intAccountId+"</td>\n" + 
		"			</tr>\n" + 
		"			<tr>\n" + 
		"				<td style='vertical-align:top;text-align:left;'>Invoice No.</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>"+objResponse.intInvoiceId+"</td>\n" + 
		"			</tr>\n" + 
		"			<tr>\n" + 
		"				<td style='vertical-align:top;text-align:left;'>Invoice Date</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>"+objResponse.strInvoiceDate+"</td>\n" + 
		"			</tr>\n" + 
		"			<tr>\n" + 
		"				<td style='vertical-align:top;text-align:left;'>Opening Balance</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>$"+objResponse.fltOpeningBalance+"</td>\n" + 
		"			</tr>\n" + 
		"			<tr>\n" + 
		"				<td style='vertical-align:top;text-align:left;'>Payments</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>$"+objResponse.fltPayments+"</td>\n" + 
		"			</tr>\n" + 
		"			<tr>\n" + 
		"				<td style='vertical-align:top;text-align:left;'>This Invoice</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>$"+objResponse.fltInvoiceTotal+"</td>\n" + 
		"			</tr>\n" + 
		"			<tr style='font-weight:bold;'>\n" + 
		"				<td style='vertical-align:top;text-align:left;'>Total Owing</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>$"+objResponse.fltTotalOwing+"</td>\n" + 
		"			</tr>\n" + 
		"		</tbody>\n" + 
		"	</table>\n" + 
		"	<div>\n" + 
		"		<span>This Interim Invoice will now appear in the Invoice section on both the <a href='../admin/flex.php/Account/Overview/?Account.Id="+objResponse.intAccountId+"#Invoice_List'>Account</a> and <a href='https://telcoblue.yellowbilling.com.au/admin/flex.php/Account/InvoicesAndPayments/?Account.Id="+objResponse.intAccountId+"#Invoice_List'>Invoice &amp; Payments</a> screens.</span>\n" + 
		"	</div>\n" + 
		"</div>\n" + 
		"<div style='margin: 0pt auto; margin-top: 4px; margin-bottom: 4px; width: 100%; text-align: center;'>\n" + 
		"	<input id='Invoice_InterimInvoiceSummary_OK' value='    OK    ' onclick='window.location.href=window.location.href' style='margin-left: 3px;' type='button' /> \n" + 
		"</div>\n";
		
		
		Vixen.Popup.Create(
				'Invoice_InterimInvoiceSummary', 
				strHTML, 
				'medium', 
				'centre', 
				'modal', 
				strInvoiceRunType + ' Invoice Summary'
			);
		
		return;
	},
});

Flex.Invoice = (Flex.Invoice == undefined) ? new Invoice() : Flex.Invoice;