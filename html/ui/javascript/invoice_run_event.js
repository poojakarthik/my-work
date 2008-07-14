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
function VixenInvoiceRunEventsClass()
{
	this.strContainerDivId	= null;
	
	this.invoiceActions = null;
	this.invoiceActionsLabel = null;

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
	this.InitialiseManage = function(strContainerDivId)
	{
		// Save the parameters
		this.strContainerDivId	= strContainerDivId;
	}

	this.InitialiseView = function(strContainerDivId)
	{
		// Save the parameters
		this.strContainerDivId	= strContainerDivId;
	}

	this.InitialiseEdit = function(strContainerDivId, arrInvoiceActionIds)
	{
		// Register Event Listeners
		Vixen.EventHandler.AddListener("OnInvoiceRunEventsUpdate", this.OnInvoiceRunEventsUpdate);

		//var submitButton = document.getElementById('InvoiceRunEventsSubmit');
		//submitButton.setAttribute('onclick', 'if (Vixen.InvoiceRunEvents.formIsValid()) { ' + submitButton.getAttribute('onclick') + '; return true;} else return false;');
	}

	this.ChangeHandler = function()
	{
	}

	this.formIsValid = function()
	{
		return true;
	}

	this.alertAndFocus = function(message, fieldName)
	{
		var delayed = new Function("Vixen.InvoiceRunEvents." + fieldName + ".focus(); alert(\"" + message + "\");");
		window.setTimeout(delayed, 1);
	}

	this.RenderDetailsForEditing = function(invoiceRunId)
	{
		// Organise the data to send
		var objData	=	{
							Container		:	{	Id		:	this.strContainerDivId},
							Context			:	{	Edit	:	true},
							InvoiceRun		:	{	Id		:	invoiceRunId}
						};
		// Call the AppTemplate method which renders just the InvoiceRunEvents HtmlTemplate
		Vixen.Ajax.CallAppTemplate("InvoiceRunEvents", "RenderHtmlTemplateInvoiceRunEventsEdit", objData, null, true);
		return false;
	}
	
	this.RenderDetailsForViewing = function(invoiceRunId)
	{
		// Organise the data to send
		var objData	=	{
							Container		:	{	Id		:	this.strContainerDivId},
							Context			:	{	View	:	true},
							InvoiceRun		:	{	Id		:	invoiceRunId}
						};

		// Call the AppTemplate method which renders just the InvoiceRunEvents HtmlTemplate
		Vixen.Ajax.CallAppTemplate("InvoiceRunEvents", "RenderHtmlTemplateInvoiceRunEventsView", objData, null, true);
		return false;
	}

	this.RenderDetailsForManaging = function()
	{
		// Organise the data to send
		var objData	=	{
							Container		:	{	Id		:	this.strContainerDivId},
							Context			:	{	Default	:	true}
						};

		// Call the AppTemplate method which renders just the InvoiceRunEvents HtmlTemplate
		Vixen.Ajax.CallAppTemplate("InvoiceRunEvents", "RenderHtmlTemplateInvoiceRunEventsManage", objData, null, true);
		return false;
	}

	//------------------------------------------------------------------------//
	// OnUpdate
	//------------------------------------------------------------------------//
	/**
	 * OnUpdate
	 *
	 * Event handler for when the InvoiceRun event details are updated in a way which would necessitate the InvoiceRunEvents HtmlTemplate being redrawn
	 *  
	 * Event handler for when the InvoiceRun event details are updated in a way which would necessitate the InvoiceRunEvents HtmlTemplate being redrawn
	 *
	 * @return	void
	 * @method
	 */
	this.OnInvoiceRunEventsUpdate = function(objEvent)
	{
		var id = document.getElementById('InvoiceRun.Id').value;
		// Organise the data to send
		var objData	=	{
							Container		:	{	Id		:	Vixen.InvoiceRunEvents.strContainerDivId},
							Context			:	{	View	:	true},
							InvoiceRun		:	{	Id		:	id}
						};

		// Call the AppTemplate method which renders just the InvoiceRunEvents HtmlTemplate
		Vixen.Ajax.CallAppTemplate("InvoiceRunEvents", "RenderHtmlTemplateInvoiceRunEventsView", objData, null, true);
	}
}

// instanciate the object
if (Vixen.InvoiceRunEvents == undefined)
{
	Vixen.InvoiceRunEvents = new VixenInvoiceRunEventsClass;
}
