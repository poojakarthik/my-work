//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// payment_terms_display.js
//----------------------------------------------------------------------------//
/**
 * payment_terms_display
 *
 * javascript required of the "PaymentTermsDsiplay" HtmlTemplate (handles both viewing and editing)
 *
 * javascript required of the "PaymentTermsDsiplay" HtmlTemplate (handles both viewing and editing)
 * 
 *
 * @file		payment_terms_display.js
 * @language	Javascript
 * @package		ui_app
 * @author		Hadrian Oliver
 * @version		0.1
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenPaymentTermsDisplayClass
//----------------------------------------------------------------------------//
/**
 * VixenPaymentTermsDisplayClass
 *
 * Encapsulates all event handling required of the "PaymentTermsDsiplay" HtmlTemplate
 *
 * Encapsulates all event handling required of the "PaymentTermsDsiplay" HtmlTemplate
 * 
 *
 * @package	ui_app
 * @class	VixenPaymentTermsDisplayClass
 * 
 */
function VixenPaymentTermsDisplayClass()
{
	this.strContainerDivId	= null;
	
	//------------------------------------------------------------------------//
	// InitialiseView
	//------------------------------------------------------------------------//
	/**
	 * InitialiseView
	 *
	 * Initialises the object for when the PaymentTermsDisplay HtmlTemplate is rendered with VIEW context
	 *  
	 * Initialises the object for when the PaymentTermsDisplay HtmlTemplate is rendered with VIEW context
	 *
	 * @param 	string	strTableContainerDivId		Id of the div that stores the PaymentTermsDisplay HtmlTemplate
	 *
	 * @return	void
	 * @method
	 */
	this.InitialiseView = function(strContainerDivId)
	{
		// Save the parameters
		this.strContainerDivId	= strContainerDivId;

		// Register Event Listeners
		Vixen.EventHandler.AddListener("OnPaymentTermsUpdate", this.OnUpdate);
	}

	this.ChangeHandler = function()
	{
		var intInvoiceDay = parseInt(this.invoiceDay.value);
		if (intInvoiceDay < 0) intInvoiceDay = 0;
		var place = '?';
		if (!isNaN(intInvoiceDay))
		{
			var pos = intInvoiceDay % 10;
			if (((intInvoiceDay%100) - pos) == 10) pos = 0;
			switch (pos)
			{
				case 1:
					place = 'st';
					break;
				case 2:
					place = 'nd';
					break;
				case 3:
					place = 'rd';
					break;
				default:
					place = 'th';
					break;
			}
		}
		this.invoiceDayDisplay.innerHTML = place;
		this.invoiceDayActual.value = intInvoiceDay;

		var intPaymentTerms = parseInt(this.paymentTerms.value);
		this.paymentTermsDisplay.innerHTML = isNaN(intPaymentTerms) ? '?' : intPaymentTerms;
		this.paymentTermsActual.value = intPaymentTerms;

		var intOverdueDays = parseInt(this.overdueDays.value) + intPaymentTerms;
		this.overdueDaysDisplay.innerHTML = isNaN(intOverdueDays) ? '?' : intOverdueDays;
		this.overdueDaysActual.value = intOverdueDays;

		var intSuspensionDays = parseInt(this.suspensionDays.value) + intOverdueDays;
		this.suspensionDaysDisplay.innerHTML = isNaN(intSuspensionDays) ? '?' : intSuspensionDays;
		this.suspensionDaysActual.value = intSuspensionDays;

		var intFinalDemandDays = parseInt(this.finalDemandDays.value) + intSuspensionDays;
		this.finalDemandDaysDisplay.innerHTML = isNaN(intFinalDemandDays) ? '?' : intFinalDemandDays;
		this.finalDemandDaysActual.value = intFinalDemandDays;

		if (!isNaN(parseFloat(this.minimumBalanceToPursue.value)))
		{
			this.minimumBalanceToPursueActual.value = parseFloat(this.minimumBalanceToPursue.value);
		}
	}

	this.InitialiseEdit = function(strContainerDivId)
	{
		// Save the parameters
		this.strContainerDivId	= strContainerDivId;

		this.invoiceDay = document.getElementById('invoice_day');
		this.paymentTerms = document.getElementById('payment_terms');
		this.overdueDays = document.getElementById('overdue_notice_days');
		this.suspensionDays = document.getElementById('suspension_notice_days');
		this.finalDemandDays = document.getElementById('final_demand_notice_days');
		this.finalDemandDays = document.getElementById('final_demand_notice_days');
		this.minimumBalanceToPursue = document.getElementById('minimum_balance_to_pursue');

		this.invoiceDayActual = document.getElementById('payment_terms.invoice_day');
		this.paymentTermsActual = document.getElementById('payment_terms.payment_terms');
		this.overdueDaysActual = document.getElementById('payment_terms.overdue_notice_days');
		this.suspensionDaysActual = document.getElementById('payment_terms.suspension_notice_days');
		this.finalDemandDaysActual = document.getElementById('payment_terms.final_demand_notice_days');
		this.minimumBalanceToPursueActual = document.getElementById('payment_terms.minimum_balance_to_pursue');

		this.invoiceDayDisplay = document.getElementById('invoice_day_display');
		this.paymentTermsDisplay = document.getElementById('payment_terms_display');
		this.overdueDaysDisplay = document.getElementById('overdue_notice_days_display');
		this.suspensionDaysDisplay = document.getElementById('suspension_notice_days_display');
		this.finalDemandDaysDisplay = document.getElementById('final_demand_notice_days_display');

		var onChange = function() { Vixen.PaymentTermsDisplay.ChangeHandler(); }
		var fields = new Array(this.invoiceDay, this.paymentTerms, this.overdueDays, this.suspensionDays, this.finalDemandDays, this.minimumBalanceToPursue);
		for (var i in fields)
		{
			var field = fields[i];
			field.addEventListener( "change", onChange, false);
			field.addEventListener( "paste", onChange, false);
			field.addEventListener( "focus", onChange, false);
			field.addEventListener( "blur", onChange, false);
			field.addEventListener( "keyup", onChange, false);
		}
	}

	this.formIsValid = function()
	{
		this.ChangeHandler();
		if (isNaN(parseInt(this.invoiceDay.value)) || parseInt(this.invoiceDay.value) <= 0)
		{
			this.alertAndFocus("Invoice Day must on or after the first day of the month.", "invoiceDay");
			return false;
		}
		if (isNaN(parseInt(this.paymentTerms.value)) || parseInt(this.paymentTerms.value) <= 0)
		{
			this.alertAndFocus("Payment Terms must allow at least one day after invoicing.", "paymentTerms");
			return false;
		}
		if (isNaN(parseInt(this.overdueDays.value)) || parseInt(this.overdueDays.value) < 0)
		{
			this.alertAndFocus("Overdue Notices should not be sent before Payment Terms have beed exceeded.", "overdueDays");
			return false;
		}
		if (isNaN(parseInt(this.suspensionDays.value)) || parseInt(this.suspensionDays.value) <= 0)
		{
			this.alertAndFocus("Suspension Notices should be issued at least one day after Overdue Notices are issued.", "suspensionDays");
			return false;
		}
		if (isNaN(parseInt(this.finalDemandDays.value)) || parseInt(this.finalDemandDays.value) <= 0)
		{
			this.alertAndFocus("Final Demands should be issued at least one day after Suspension Notices are issued.", "finalDemandDays");
			return false;
		}

		if (isNaN(parseFloat(this.minimumBalanceToPursue.value)) || parseFloat(this.minimumBalanceToPursue.value) <= 0)
		{
			this.alertAndFocus("The Minimum Balance to Pursue must be a decimal amount greater than 0 (zero).", "minimumBalanceToPursue");
			return false;
		}
		else
		{
			this.minimumBalanceToPursueActual.value = parseFloat(this.minimumBalanceToPursue.value);
		}

		return true;
	}

	this.alertAndFocus = function(message, fieldName)
	{
		var delayed = new Function("Vixen.PaymentTermsDisplay." + fieldName + ".focus(); alert(\"" + message + "\");");
		window.setTimeout(delayed, 1);
	}

	this.RenderDetailsForEditing = function()
	{
		// Organise the data to send
		var objData	=	{
							Container		:	{	Id		:	this.strContainerDivId},
							Context			:	{	Edit	:	true}
						};
		// Call the AppTemplate method which renders just the PaymentTermsDisplay HtmlTemplate
		Vixen.Ajax.CallAppTemplate("PaymentTerms", "RenderHtmlTemplatePaymentTermsDisplay", objData, null, true);
	}
	
	this.CancelEdit = function()
	{
		// Organise the data to send
		var objData	=	{
							Container		:	{	Id		:	this.strContainerDivId},
							Context			:	{	View	:	true}
						};

		// Call the AppTemplate method which renders just the PaymentTermsDisplay HtmlTemplate
		Vixen.Ajax.CallAppTemplate("PaymentTerms", "RenderHtmlTemplatePaymentTermsDisplay", objData, null, true);
		return false;
	}

	//------------------------------------------------------------------------//
	// OnUpdate
	//------------------------------------------------------------------------//
	/**
	 * OnUpdate
	 *
	 * Event handler for when the PaymentTerms details are updated in a way which would necessitate the PaymentTermsDisplay HtmlTemplate being redrawn
	 *  
	 * Event handler for when the PaymentTerms details are updated in a way which would necessitate the PaymentTermsDisplay HtmlTemplate being redrawn
	 *
	 * @return	void
	 * @method
	 */
	this.OnUpdate = function(objEvent)
	{
		if (!Vixen.PaymentTermsDisplay.formIsValid())
		{
			if (document.all)
			{
				objEvent = objEvent ? objEvent : window.event;
				objEvent.cancelBubble;
			}
			else objEvent.stopPropagation();
			return false;
		}

		// The "this" pointer does not point to this object, when it is called.
		// It points to the Window object
		var strContainerDivId	= Vixen.PaymentTermsDisplay.strContainerDivId;

		// Organise the data to send
		var objData	=	{
							Container		:	{	Id		:	strContainerDivId},
							Context			:	{	View	:	true}
						};

		// Call the AppTemplate method which renders just the PaymentTermsDisplay HtmlTemplate
		Vixen.Ajax.CallAppTemplate("PaymentTerms", "RenderHtmlTemplatePaymentTermsDisplay", objData, null, true);
	}
}

// instanciate the object
if (Vixen.PaymentTermsDisplay == undefined)
{
	Vixen.PaymentTermsDisplay = new VixenPaymentTermsDisplayClass;
}
