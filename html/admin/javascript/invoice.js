// Class: Invoice
// Handles the Invoices in Flex
var Invoice	= Class.create
({	
	// Function: initialize()
	// Prototype constructor
	initialize	: function()
	{
		
	},
	
	// getForId: JSON Handler wrapper
	getForId	: function(iId, fnCallback, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Make request
			var fnResponseCallback	= this.getForId.bind(this, iId, fnCallback);
			var fnGetForId			= jQuery.json.jsonFunction(fnResponseCallback, fnResponseCallback, 'Invoice_View', 'getForId');
			fnGetForId(iId);
		}
		else
		{
			if (!oResponse.Success)
			{
				Reflex_Popup.alert(oResponse.sMessage + ((oResponse.strDebug != '') ? ' -- DEBUG LOG:' + oResponse.strDebug : ''));
				fnCallback(null);
				return;
			}
			
			// Handle request
			fnCallback(oResponse.oInvoice);
		}
	},
	
	getServicesForInvoice	: function(iInvoiceId, fnCallback, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Make request
			var fnResponse				= this.getServicesForInvoice.bind(this, iInvoiceId, fnCallback);
			var fnGetServicesForInvoice	= jQuery.json.jsonFunction(fnResponse, fnResponse, 'Invoice', 'getServicesForInvoice');
			fnGetServicesForInvoice(iInvoiceId);
		}
		else
		{
			if (!oResponse.bSuccess)
			{
				Reflex_Popup.alert(oResponse.sMessage + ((oResponse.sDebug != '') ? ' -- DEBUG LOG: ' + oResponse.sDebug : ''));
				fnCallback(null);
				return;
			}
			
			// Handle request
			fnCallback(oResponse.aServices);
		}
	},
	
	// rerateInvoice: JSON Handler wrapper
	rerateInvoice	: function(iInvoiceId, hServiceRatePlans, fnCallback, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Make request
			var fnResponseCallback	= this.rerateInvoice.bind(this, iInvoiceId, hServiceRatePlans, fnCallback);
			var fnRerateInvoice		= jQuery.json.jsonFunction(fnResponseCallback, fnResponseCallback, 'Invoice', 'rerateInvoice');
			fnRerateInvoice(iInvoiceId, hServiceRatePlans);
		}
		else
		{
			if (!oResponse.bSuccess)
			{
				if (oResponse.sDebug != '')
				{
					// Have log, show message & 'View Log' button
					Reflex_Popup.yesNoCancel(
						oResponse.sMessage, 
						{
							iWidth		: 40, 
							sYesLabel	: 'OK',
							sNoLabel	: 'View Log',
							fnOnNo		: this._showLogPopup.bind(this, oResponse.sDebug)
						}
					);
				}
				else
				{
					// NO log, show message
					Reflex_Popup.alert(oResponse.sMessage);
				}
				fnCallback(null);
				return;
			}
			
			// Handle request
			fnCallback(oResponse.oNewInvoice, oResponse.oOriginalInvoice, oResponse.sDebug);
		}
	},
	
	_showLogPopup	: function(sLog)
	{
		Reflex_Popup.debug(sLog);
	},
	
	// hasUnarchivedCDRs: JSON Handler Wrapper
	hasUnarchivedCDRs	: function(iInvoiceId, fnCallback, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Make request
			var fnResponseCallback	= this.hasUnarchivedCDRs.bind(this, iInvoiceId, fnCallback);
			var fnHasUnarchivedCDRs	= jQuery.json.jsonFunction(fnResponseCallback, fnResponseCallback, 'Invoice', 'hasUnarchivedCDRs');
			fnHasUnarchivedCDRs(iInvoiceId);
		}
		else
		{
			if (!oResponse.bSuccess)
			{
				Reflex_Popup.alert(oResponse.sMessage + ((oResponse.sDebug != '') ? ' -- DEBUG LOG: ' + oResponse.sDebug : ''));
				fnCallback(null);
				return;
			}
			
			// Handle request
			fnCallback(oResponse.bHasUnarchivedCDRs);
		}
	},
	
	_getInvoiceRunType	: function(intInvoiceRunType)
	{
		var strInvoiceRunType;
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
		var fncJsonFunc		= jQuery.json.jsonFunction(Flex.Invoice._getPreGenerateValuesResponse.bind(this), null, 'Invoice_Interim', 'getPreGenerateValues');
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
		
		var strInvoiceRunType	= Flex.Invoice._getInvoiceRunType(objResponse.intInvoiceRunType);
		
		// Render Invoice Summry Popup
		var strCDRCreditNotice	=	"	<div>\n" + 
									"		<span><span style='font-weight:bold;color:#E00'>*</span> : This Account's Customer Group has been configured to suppress all CDR Credits (excluding Service & Equipment).</span>\n" + 
									"	</div>\n";
		
		var strHTML	= "\n" + 
		"<div class='GroupedContent'>\n" + 
		"	<div>\n" + 
		"		<span>The following charges will be included in the " + strInvoiceRunType + " Invoice.  Please note that this does not include any charges or credits (eg. Plan Charges) which are calculated duing Invoicing.</span>\n" + 
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
		"				<td style='vertical-align:top;text-align:left;'>Debit Charges</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>" + objResponse.intChargeDebitCount + "</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>$" + (new Number(objResponse.fltChargeDebitTotal)).toFixed(2) + "</td>\n" + 
		"			</tr>\n" + 
		"			<tr>\n" + 
		"				<td style='vertical-align:top;text-align:left;'>Credit Charges</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>" + objResponse.intChargeCreditCount + "</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>" + ((objResponse.fltChargeCreditTotal > 0) ? "- " : "") + "$" + (new Number(objResponse.fltChargeCreditTotal)).toFixed(2) + "</td>\n" + 
		"			</tr>\n" + 
		"			<tr>\n" + 
		"				<td style='vertical-align:top;text-align:left;'>Debit CDRs</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>" + objResponse.intCDRDebitCount + "</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>$" + (new Number(objResponse.fltCDRDebitTotal)).toFixed(2) + "</td>\n" + 
		"			</tr>\n" + 
		"			<tr>\n" + 
		"				<td style='vertical-align:top;text-align:left;'>Credit CDRs" + ((!objResponse.bolInvoiceCDRCredits) ? "&nbsp;<span style='font-weight:bold;color:#E00'>*</span>" : '') + "</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>" + objResponse.intCDRCreditCount + "</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>" + ((objResponse.fltCDRCreditTotal > 0) ? "- " : "") + "$" + (new Number(objResponse.fltCDRCreditTotal)).toFixed(2) + "</td>\n" + 
		"			</tr>\n" + 
		"		</tbody>\n" + 
		"	</table>\n" + 
		((!objResponse.bolInvoiceCDRCredits) ? strCDRCreditNotice : '') + 
		"</div>\n" + 
		"<div style='margin: 0pt auto; margin-top: 4px; margin-bottom: 4px; width: 100%; text-align: center;'>\n" + 
		"	<input id='Invoice_InterimInvoicePreGenerateSummary_Generate' value='Generate' onclick='Flex.Invoice.generateInterimInvoice(" + objResponse.intAccountId + ", " + objResponse.intInvoiceRunType + ");' style='margin-left: 3px;' type='button' /> \n" + 
		"	<input id='Invoice_InterimInvoicePreGenerateSummary_Cancel' value='Cancel' onclick='Vixen.Popup.Close(this);' style='margin-left: 3px;' type='button' /> \n" + 
		"</div>\n";
		
		
		Vixen.Popup.Create(
				'Invoice_InterimInvoicePreGenerateSummary', 
				strHTML, 
				'medium', 
				'centre', 
				'modal', 
				strInvoiceRunType + ' Pre-Invoice Summary'
			);
		
		return;
	},
	
	generateInterimInvoice	: function(intAccount, intInvoiceRunType)
	{
		var strInvoiceRunType	= Flex.Invoice._getInvoiceRunType(intInvoiceRunType);
		if (!strInvoiceRunType)
		{
			$Alert("There was an error when trying to generate the Invoice. ("+intInvoiceRunType+" is not a valid Invoice Run Type)");
			return;
		}
		
		// Show the Loading Splash
		Vixen.Popup.ShowPageLoadingSplash("Generating "+strInvoiceRunType+" Invoice...", null, null, null, 1);
		
		// Perform AJAX query
		var fncJsonFunc		= jQuery.json.jsonFunction(Flex.Invoice._generateInterimInvoiceResponse.bind(this), null, 'Invoice_Interim', 'generateInterimInvoice');
		fncJsonFunc(intAccount, intInvoiceRunType);
		
		return;
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

		var strInvoiceRunType	= Flex.Invoice._getInvoiceRunType(objResponse.objInvoiceRun.invoice_run_type_id);
		
		var fltInvoiceTotal		= (new Number(objResponse.objInvoice.Total)) + (new Number(objResponse.objInvoice.Tax));
		
		var strDebugButton			= '';
		if (objResponse.strDebug)
		{
			var strDebugContent	= "<textarea rows='25' cols='80' readonly='readonly'>"+objResponse.strDebug+"</textarea>";
			strDebugButton		= "	<input id='Invoice_InterimInvoiceSummary_Debug' value='View Debugging Output' style='margin-left: 3px;' type='button' /> \n";
		}
		
		// Render Invoice Summry Popup
		var strHTML	= "\n" + 
		"<div class='GroupedContent'>\n" + 
		"	<div>\n" + 
		"		<span>The "+strInvoiceRunType+" Invoice for "+objResponse.objInvoice.Account+" has been successfully generated.</span>\n" + 
		"	</div>\n" + 
		"	<table class='reflex' style='margin-top: 8px; margin-bottom: 8px;' width='100%'>\n" + 
		"		<tbody>\n" + 
		"			<tr>\n" + 
		"				<td style='vertical-align:top;text-align:left;'>Billing Period</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>"+objResponse.strBillingPeriod+"</td>\n" + 
		"			</tr>\n" + 
		"			<tr>\n" + 
		"				<td style='vertical-align:top;text-align:left;'>Account No.</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>"+objResponse.objInvoice.Account+"</td>\n" + 
		"			</tr>\n" + 
		"			<tr>\n" + 
		"				<td style='vertical-align:top;text-align:left;'>Invoice No.</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>"+objResponse.objInvoice.Id+"</td>\n" + 
		"			</tr>\n" + 
		"			<tr>\n" + 
		"				<td style='vertical-align:top;text-align:left;'>Invoice Date</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>"+objResponse.strInvoiceDate+"</td>\n" + 
		"			</tr>\n" + 
		"			<tr>\n" + 
		"				<td style='vertical-align:top;text-align:left;'>Opening Balance</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>$"+(new Number(objResponse.objInvoice.AccountBalance)).toFixed(2)+"</td>\n" + 
		"			</tr>\n" + 
		"			<tr>\n" + 
		"				<td style='vertical-align:top;text-align:left;'>Payments</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>$"+(new Number(objResponse.fltPayments)).toFixed(2)+"</td>\n" + 
		"			</tr>\n" + 
		"			<tr>\n" + 
		"				<td style='vertical-align:top;text-align:left;'>This Invoice</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>$"+(new Number(fltInvoiceTotal)).toFixed(2)+"</td>\n" + 
		"			</tr>\n" + 
		"			<tr style='font-weight:bold;'>\n" + 
		"				<td style='vertical-align:top;text-align:left;'>Total Owing</td>\n" + 
		"				<td style='vertical-align:top;text-align:right;'>$"+(new Number(objResponse.objInvoice.TotalOwing)).toFixed(2)+"</td>\n" + 
		"			</tr>\n" + 
		"		</tbody>\n" + 
		"	</table>\n" + 
		"	<div>\n" + 
		"		<span>This "+strInvoiceRunType+" Invoice will now appear in the Invoice section on both the <a href='../admin/flex.php/Account/Overview/?Account.Id="+objResponse.objInvoice.Account+"#Invoice_List'>Account</a> and <a href='../admin/flex.php/Account/InvoicesAndPayments/?Account.Id="+objResponse.objInvoice.Account+"#Invoice_List'>Invoice &amp; Payments</a> screens.</span>\n" + 
		"	</div>\n" + 
		"</div>\n" + 
		"<div style='margin: 0pt auto; margin-top: 4px; margin-bottom: 4px; width: 100%; text-align: center;'>\n" + 
		"	<input id='Invoice_InterimInvoiceSummary_OK' value='    OK    ' onclick='window.location.href=window.location.href' style='margin-left: 3px;' type='button' /> \n" +
		strDebugButton + 
		"</div>\n";
		
		// Destroy the pre-Generate Summary Popup, and replace it with the actual Invoice Summary
		Vixen.Popup.Close('Invoice_InterimInvoicePreGenerateSummary');
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
		
		if (objResponse.strDebug)
		{
			$ID("Invoice_InterimInvoiceSummary_Debug").onclick	= $Alert.curry(strDebugContent, 'large', null, 'modal');
		}
		
		return;
	},
	
	commitInterimInvoiceConfirm	: function(intInvoice, bInvoiceRunTypeIsInterimFirst)
	{
		if (bInvoiceRunTypeIsInterimFirst)
		{
			Reflex_Popup.alert(
				$T.div({style: 'margin: 0.5em;'},
					$T.p("This invoice is a First Interim Invoice, it cannot be commited manually."),
					$T.p("To commit all temporary First Interim Invoices, choose: 'Commit and Send Interim Invoices' in the Interim Invoice (Customer) menu.")
				)
			);
		}
		else
		{
			Vixen.Popup.YesNoCancel("Are you sure you want to Commit this Invoice?  This process is irreversible.",
									Flex.Invoice.commitInterimInvoice.curry(intInvoice),
									function(){},
									null);
		}
	},
	
	commitInterimInvoice	: function(intInvoice)
	{
		// Show the Loading Splash
		Vixen.Popup.ShowPageLoadingSplash("Committing Invoice...", null, null, null, 1);
		
		// Perform AJAX query
		var fncJsonFunc		= jQuery.json.jsonFunction(Flex.Invoice._commitInterimInvoiceResponse.bind(this), null, 'Invoice_Interim', 'commitInterimInvoice');
		fncJsonFunc(intInvoice);
		
		return;
	},
	
	_commitInterimInvoiceResponse	: function(objResponse)
	{
		// Close the Splash and display the Summary
		Vixen.Popup.ClosePageLoadingSplash();
		
		// Did we succeed?
		if (objResponse.Success === false)
		{
			$Alert(objResponse.ErrorMessage, null, null, 'autohide-reload', "Invoice Committed");
			return;
		}
		
		// Show confirmation Alert{
		$Alert("The Invoice has been successfully Committed.", null, null, 'autohide-reload', "Invoice Committed");
		return;
	},
	
	revokeInterimInvoiceConfirm	: function(intInvoice)
	{
		Vixen.Popup.YesNoCancel("Are you sure you want to Reject this Invoice Sample?",
								Flex.Invoice.revokeInterimInvoice.curry(intInvoice),
								function(){},
								null);
	},
	
	revokeInterimInvoice	: function(intInvoice)
	{
		// Show the Loading Splash
		Vixen.Popup.ShowPageLoadingSplash("Revoking Invoice...", null, null, null, 1);
		
		// Perform AJAX query
		var fncJsonFunc		= jQuery.json.jsonFunction(Flex.Invoice._revokeInterimInvoiceResponse.bind(this), null, 'Invoice_Interim', 'revokeInterimInvoice');
		fncJsonFunc(intInvoice);
		
		return;
	},
	
	_revokeInterimInvoiceResponse	: function(objResponse)
	{
		// Close the Splash and display the Summary
		Vixen.Popup.ClosePageLoadingSplash();
		
		// Did we succeed?
		if (objResponse.Success === false)
		{
			$Alert(objResponse.ErrorMessage, null, null, 'autohide-reload');
			return;
		}
		
		// Show confirmation Alert
		$Alert("The Invoice has been successfully Revoked.", null, null, 'autohide-reload', "Invoice Revoked");
		return;
	},
	
	buildPopupSubmitInterimInvoiceReport	: function()
	{
		var	sHtml;
		sHtml	=	"\n" +
		"			<form id='Invoice_Interim_EligibilityUpload_Form' name='Invoice_Interim_EligibilityUpload_Form' method='post' action='../admin/reflex.php/Invoice/ActionInterimInvoicesReport/' enctype='multipart/form-data'>\n" +
		"				<div class='GroupedContent'>\n" +
		"					<table>\n" +
		"						<tbody>\n" +
		"							<tr>\n" +
		"								<th>Interim Invoice Eligiblity Report&nbsp;:&nbsp;</th>\n" +
		"								<td><input id='Invoice_Interim_EligibilityUpload_File' name='Invoice_Interim_EligibilityUpload_File' type='file' accept='text/csv,text/plain' /></td>\n" +
		"							</tr>\n" +
		"						</tbody>\n" +
		"					</table>\n" +
		"				</div>\n" +
		"				<div style='text-align: center;'>\n" +
		"					<input id='Invoice_Interim_EligibilityUpload_Submit' type='button' value='  Submit  ' style='margin-right: 1em;' />\n" +
		"					<input id='Invoice_Interim_EligibilityUpload_Cancel' type='button' value='  Cancel  ' />\n" +
		"				</div>\n" +
		"			</form>\n";
		
		var oPopupContent					= {};
		oPopupContent.domElement			= document.createElement('div');
		oPopupContent.domElement.innerHTML	= sHtml;
		
		// Init the Popup
		var oPopup;
		oPopup	= new Reflex_Popup(40);
		oPopup.setTitle("Submit Interim Invoice Eligiblity Report");
		oPopup.setContent(oPopupContent.domElement);
		
		oPopup.display();
		
		document.getElementById('Invoice_Interim_EligibilityUpload_Submit').addEventListener('click', Flex.Invoice.submitInterimInvoiceReport.bind(Flex.Invoice, oPopup), false);
		document.getElementById('Invoice_Interim_EligibilityUpload_Cancel').addEventListener('click', oPopup.hide.bind(oPopup), false);
	},
	
	// getInterimFirstInvoiceEligibilityReport:	Handles the confirmation, generation and download of the interim first invoice
	//											eligibility report, ajax style.
	getInterimFirstInvoiceEligibilityReport	: function(bSure, oResponse)
	{
		if (typeof bSure == 'undefined')
		{
			// Step 1: Confirm download
			Reflex_Popup.yesNoCancel(
				'Are you sure you want to download the Interim Invoice Eligibility Report?', 
				{fnOnYes: Flex.Invoice.getInterimFirstInvoiceEligibilityReport.curry(true)}
			);
		}
		else if (bSure && typeof oResponse == 'undefined')
		{
			// Step 2: Generate the report
			// Show loading
			Flex.Invoice._oLoading	= new Reflex_Popup.Loading('Generating the report...');
			Flex.Invoice._oLoading.display();
			
			// Make the request
			var fnCallback	= Flex.Invoice.getInterimFirstInvoiceEligibilityReport.curry(true);
			var fnGetReport	= jQuery.json.jsonFunction(fnCallback, fnCallback, 'Invoice_Interim', 'generateEligibilityReport');
			fnGetReport();
		}
		else
		{
			// Step 3: Show download link or error message.
			// Hide loading
			Flex.Invoice._oLoading.hide();
			
			// Finished
			if (oResponse.bSuccess)
			{
				Reflex_Popup.alert(
					$T.div({class: 'alert-content'},
						$T.span("The report was generated successfully, click "),
						$T.a({href: "reflex.php/Invoice/DownloadInterimInvoiceEligibility/" + oResponse.sFileName},
							'here'
						),
						$T.span(" to download the report.")
					),
					{sTitle: 'Download Report'}
				);
			}
			else
			{
				var sMessage	= (oResponse.sMessage ? oResponse.sMessage : 'An error occured accessing the database, please contact YBS');
				Reflex_Popup.alert(sMessage, {sTitle: 'Error'});
			}
		}
	},
	
	submitInterimInvoiceReport	: function(oPopup)
	{
		// Ensure that a File has been selected
		if (!document.getElementById('Invoice_Interim_EligibilityUpload_File').value)
		{
			$Alert("Please select a file to submit.")
			return false;
		}
		if (document.getElementById('Invoice_Interim_EligibilityUpload_File').value.search(/\.csv$/i) === -1)
		{
			$Alert("The file you have selected is not an Interim Invoice Eligibility Report.  Please select the correct file.")
			return false;
		}

		Vixen.Popup.ShowPageLoadingSplash("Submitting Eligibility Report...", null, null, null, 1);
		if (jQuery.json.jsonIframeFormSubmit(document.getElementById('Invoice_Interim_EligibilityUpload_Form'), jQuery.json.handleResponse.bind(jQuery.json, Flex.Invoice._submitInterimInvoiceReport.bind(Flex.Invoice, oPopup))))
		{
			$ID('Invoice_Interim_EligibilityUpload_Form').submit();
			oPopup.hide();
			return true;
		}
		else
		{
			return false;
		}
	},
	
	_submitInterimInvoiceReport	: function(oPopup, oResponse)
	{
		Vixen.Popup.ClosePageLoadingSplash();

		$Alert("Your modified First Interim Invoice Eligibility Report has been successfully submitted.  You should receive a Processing Report via email shortly.")
		//alert("Response: " + oResponse);
	}
});

Flex.Invoice = (Flex.Invoice == undefined) ? new Invoice() : Flex.Invoice;