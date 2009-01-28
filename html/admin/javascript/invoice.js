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
	
	getPreGenerateValues	: function(intAccount)
	{
		// Show the Loading Splash
		Vixen.Popup.ShowPageLoadingSplash("Getting Preliminary Invoice Data...", null, null, null, 1);
		
		// Perform AJAX query
		var fncJsonFunc		= jQuery.json.jsonFunction(this._getPreGenerateValuesResponse.bind(this), null, 'Invoice', 'getPreGenerateValues');
		fncJsonFunc(intAccount);
	},
	
	_getPreGenerateValuesResponse	: function(objResponse)
	{
		// Close the Splash and display the Summary
		Vixen.Popup.ClosePageLoadingSplash();
		
		// Did we succeed?
		if (objResponse.Success === false)
		{
			$Alert(objResponse.ErrorMessage);
			return;
		}
		
		// Render Invoice Summry Popup
		var strCDRCreditNotice	=	"	<div>\n" + 
									"		<span><span style='font-weight:bold;color:#E00'>*</span> : This Account's Customer Group has been configured to suppress all CDR Credits.</span>\n" + 
									"	</div>\n";
		
		var strHTML	= "\n" + 
		"<div class='GroupedContent'>\n" + 
		"	<div>\n" + 
		"		<span>The following charges will be included in the Interim Invoice.  Please note that this does not include any charges or credits (eg. Plan Charges) which are calculated duing Invoicing.</span>\n" + 
		"	</div>\n" + 
		"	<table class='reflex' style='margin-top: 8px; margin-bottom: 8px;' width='100%'>\n" + 
		"		<thead >\n" + 
		"			<tr>\n" + 
		"				<th style='font-size:10pt;vertical-align:top;text-align:left;'>Category</th>\n" + 
		"				<th style='font-size:10pt;vertical-align:top;text-align:right;'>Count</th>\n" + 
		"				<th style='font-size:10pt;vertical-align:top;text-align:right;'>Total ($)</th>\n" + 
		"			</tr>\n" + 
		"		</thead>\n" + 
		"		<tbody>\n" + 
		"			<tr>\n" + 
		"				<td style='vertical-align:top;text-align:left;'>Debit Adjustments</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>" + objResponse.intAdjustmentDebitCount + "</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>$" + objResponse.fltAdjustmentDebitTotal + "</td>\n" + 
		"			</tr>\n" + 
		"			<tr>\n" + 
		"				<td style='vertical-align:top;text-align:left;'>Credit Adjustments</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>" + objResponse.intAdjustmentCreditCount + "</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>" + ((objResponse.fltAdjustmentCreditTotal > 0) ? "- " : "") + "$" + objResponse.fltAdjustmentCreditTotal + "</td>\n" + 
		"			</tr>\n" + 
		"			<tr>\n" + 
		"				<td style='vertical-align:top;text-align:left;'>Debit CDRs</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>" + objResponse.intCDRDebitCount + "</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>$" + objResponse.fltCDRDebitTotal + "</td>\n" + 
		"			</tr>\n" + 
		"			<tr>\n" + 
		"				<td style='vertical-align:top;text-align:left;'>Credit CDRs" + (objResponse.bolInvoiceCDRCredits ? "&nbsp;<span style='font-weight:bold;color:#E00'>*</span>" : '') + "</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>" + objResponse.intCDRCreditCount + "</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>" + ((objResponse.fltCDRCreditTotal > 0) ? "- " : "") + "$" + objResponse.fltCDRCreditTotal + "</td>\n" + 
		"			</tr>\n" + 
		"		</tbody>\n" + 
		"	</table>\n" + 
		(objResponse.bolInvoiceCDRCredits ? strCDRCreditNotice : '') + 
		"</div>\n" + 
		"<div style='margin: 0pt auto; margin-top: 4px; margin-bottom: 4px; width: 100%; text-align: center;'>\n" + 
		"	<input id='Invoice_InterimInvoicePreGenerateSummary_Generate' value='Generate' onclick='Flex.Invoice.generateInterimInvoice(" + objResponse.intAccount + ", " + objResponse.intInvoiceRunType + ");' style='margin-left: 3px;' type='button' /> \n" + 
		"	<input id='Invoice_InterimInvoicePreGenerateSummary_Cancel' value='Cancel' onclick='Vixen.Popup.Close(this);' style='margin-left: 3px;' type='button' /> \n" + 
		"</div>\n";
		
		
		Vixen.Popup.Create(
				'Invoice_InterimInvoicePreGenerateSummary', 
				strHTML, 
				'medium', 
				'centre', 
				'modal', 
				objResponse.strInvoiceRunType + ' Pre-Invoice Summary'
			);
		
		return;
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

		strInvoiceRunType	= this._getInvoiceRunType(objResponse.intInvoiceRunType);
		if (!strInvoiceRunType)
		{
			$Alert("There was an error when trying to generate the "+strInvoiceRunType+" Invoice. ("+objResponse.intInvoiceRunType+" is not a valid Invoice Run Type)");
			return;
		}
		
		// Render Invoice Summry Popup
		var strHTML	= "\n" + 
		"<div class='GroupedContent'>\n" + 
		"	<div>\n" + 
		"		<span>The "+strInvoiceRunType+" Invoice for "+objResponse.intAccountId+" has been successfully generated.</span>\n" + 
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
		"		<span>This "+strInvoiceRunType+" Invoice will now appear in the Invoice section on both the <a href='../admin/flex.php/Account/Overview/?Account.Id="+objResponse.intAccountId+"#Invoice_List'>Account</a> and <a href='../admin/flex.php/Account/InvoicesAndPayments/?Account.Id="+objResponse.intAccountId+"#Invoice_List'>Invoice &amp; Payments</a> screens.</span>\n" + 
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
				strInvoiceRunType + ' Invoice Summary',
				null,
				false
			);
		
		return;
	},
});

Flex.Invoice = (Flex.Invoice == undefined) ? new Invoice() : Flex.Invoice;