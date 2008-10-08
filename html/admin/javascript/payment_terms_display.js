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
	
	this.invoiceActionResponses = null;
	this.invoiceActionResponsesActual = null;
	this.invoiceActions = null;
	this.invoiceActionsActual = null;
	this.invoiceActionsLabel = null;
	this.customerGroupId = null,

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
	this.InitialiseView = function(strContainerDivId, customerGroupId)
	{
		// Save the parameters
		this.customerGroupId = customerGroupId;
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
		this.paymentTermsActual.value = intPaymentTerms;

		if (!isNaN(parseFloat(this.minimumBalanceToPursue.value)))
		{
			this.minimumBalanceToPursueActual.value = parseFloat(this.minimumBalanceToPursue.value);
		}

		if (!isNaN(parseFloat(this.latePaymentFee.value)))
		{
			this.latePaymentFeeActual.value = parseFloat(this.latePaymentFee.value);
		}

		for (var i=0; i < this.invoiceActions.length; i++)
		{
			this.invoiceActionsActual[i].value = parseInt(this.invoiceActions[i].value);
		}

		for (var i=0; i < this.invoiceActionResponses.length; i++)
		{
			this.invoiceActionResponsesActual[i].value = parseInt(this.invoiceActionResponses[i].value);
		}
	}

	this.InitialiseEdit = function(strContainerDivId, arrInvoiceActionIds, customerGroupId)
	{
		this.customerGroupId = customerGroupId;

		// Register Event Listeners
		Vixen.EventHandler.AddListener("OnPaymentTermsUpdate", this.OnUpdate);

		var submitButton = document.getElementById('PaymentTermsSubmit');
		submitButton.setAttribute('onclick', 'if (Vixen.PaymentTermsDisplay.formIsValid()) { ' + submitButton.getAttribute('onclick') + '; return true;} else return false;');

		// Save the parameters
		this.strContainerDivId	= strContainerDivId;

		this.invoiceDay = document.getElementById('invoice_day');
		this.paymentTerms = document.getElementById('payment_terms');
		this.minimumBalanceToPursue = document.getElementById('minimum_balance_to_pursue');
		this.latePaymentFee = document.getElementById('late_payment_fee');

		this.invoiceDayActual = document.getElementById('payment_terms.invoice_day');
		this.paymentTermsActual = document.getElementById('payment_terms.payment_terms');
		this.minimumBalanceToPursueActual = document.getElementById('payment_terms.minimum_balance_to_pursue');
		this.latePaymentFeeActual = document.getElementById('payment_terms.late_payment_fee');

		this.invoiceDayDisplay = document.getElementById('invoice_day_display');

		var onChange = function() { Vixen.PaymentTermsDisplay.ChangeHandler(); }
		var fields = new Array(this.invoiceDay, this.paymentTerms, this.minimumBalanceToPursue, this.latePaymentFee);

		this.invoiceActions = new Array();
		this.invoiceActionsActual = new Array();
		this.invoiceActionResponses = new Array();
		this.invoiceActionResponsesActual = new Array();
		this.invoiceActionsLabel = new Array();
		for (var i=0; i < arrInvoiceActionIds.length; i++)
		{
			var id = arrInvoiceActionIds[i];
			this.invoiceActions[i] = document.getElementById('invoiceActions.' + id);
			this.invoiceActionsActual[i] = document.getElementById('invoiceActions[' + id + ']');
			this.invoiceActionResponses[i] = document.getElementById('invoiceActionResponses.' + id);
			this.invoiceActionResponsesActual[i] = document.getElementById('invoiceActionResponses[' + id + ']');
			this.invoiceActionsLabel[i] = document.getElementById('invoiceActions.' + id + '.Label.Text').innerHTML;
			fields[(i*2) + 4] = this.invoiceActions[i];
			fields[(i*2) + 5] = this.invoiceActionResponses[i];
		}

		for (var i=0; i < fields.length; i++)
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
		//this.ChangeHandler();
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
		for (var i=0; i < this.invoiceActions.length; i++)
		{
			if (isNaN(parseInt(this.invoiceActions[i].value)) || parseInt(this.invoiceActions[i].value) < 0)
			{
				this.alertAndFocus(this.invoiceActionsLabel[i] + " must be set to a number of days after the invoice day.", 'invoiceActions[' + i + ']');
				return false;
			}
		}

		for (var i=0; i < this.invoiceActionResponses.length; i++)
		{
			if (isNaN(parseInt(this.invoiceActionResponses[i].value)) || parseInt(this.invoiceActionResponses[i].value) < 0)
			{
				this.alertAndFocus(this.invoiceActionsLabel[i] + " must be set to a number of days after the event.", 'invoiceActionResponses[' + i + ']');
				return false;
			}
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

		if (isNaN(parseFloat(this.latePaymentFee.value)) || parseFloat(this.latePaymentFee.value) < 0)
		{
			this.alertAndFocus("The Late Payment Fee must be a decimal amount greater than or equal to 0 (zero).", "latePaymentFee");
			return false;
		}
		else
		{
			this.latePaymentFeeActual.value = parseFloat(this.latePaymentFee.value);
		}

		return true;
	}

	this.alertAndFocus = function(message, fieldName)
	{
		var delayed = new Function("Vixen.PaymentTermsDisplay." + fieldName + ".focus(); alert(\"" + message + "\");");
		window.setTimeout(delayed, 1);
	}

	this.RenderDetailsForEditing = function(customerGroupId)
	{
		// Organise the data to send
		var objData	=	{
							Container		:	{	Id		:	this.strContainerDivId},
							Context			:	{	Edit	:	true},
							CustomerGroup	:	{	Id		:	customerGroupId}
						};
		// Call the AppTemplate method which renders just the PaymentTermsDisplay HtmlTemplate
		Vixen.Ajax.CallAppTemplate("PaymentTerms", "RenderHtmlTemplatePaymentTermsDisplay", objData, null, true);
	}
	
	this.CancelEdit = function(customerGroupId)
	{
		// Organise the data to send
		var objData	=	{
							Container		:	{	Id		:	this.strContainerDivId},
							Context			:	{	View	:	true},
							CustomerGroup	:	{	Id		:	customerGroupId}
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
		// Organise the data to send
		var objData	=	{
							Container		:	{	Id		:	Vixen.PaymentTermsDisplay.strContainerDivId},
							Context			:	{	View	:	true},
							CustomerGroup	:	{	Id		:	Vixen.PaymentTermsDisplay.customerGroupId}
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
