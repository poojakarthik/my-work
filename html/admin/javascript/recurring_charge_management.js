var Recurring_Charge_Management = Class.create
({
	initialize	: function(elmContainerDiv, intMaxRecordsPerPage)
	{
		var intCacheMode = Dataset_Ajax.CACHE_MODE_NO_CACHING;

		// Init Dataset & Pagination
		this.intMaxRecordsPerPage = intMaxRecordsPerPage;
		this.objDataset		= 	new Dataset_Ajax(
									intCacheMode, 
									{sObject: 'Recurring_Charge', sMethod: 'getRecurringChargesAwaitingApproval'}
								);
		this.objPagination	= 	new Pagination(
									this._updateTable.bind(this), 
									this.intMaxRecordsPerPage, 
									this.objDataset
								);
		this._objRecCharges	= {};

		// This will store the list of recurring charges (just their ids) to be either approved or declined, in a single submit to the server
		this._arrRecChargeIdsToBeActioned = new Array();
		
		this._bolIsSubmitting = false;

		// Build the page
		var objPage			= {};
		objPage.domElement	= document.createElement('div');
		elmContainerDiv.appendChild(objPage.domElement);

		// Initialise pagination buttons
		objPage.objPagination = {};
		objPage.objPagination.domElement = document.createElement('div');
		
		objPage.objPagination.objFirst = {};
		objPage.objPagination.objFirst.domElement = document.createElement('button');
		objPage.objPagination.objFirst.domElement.appendChild(document.createTextNode('<<'));
		objPage.objPagination.objFirst.fncCallback = this.objPagination.firstPage.bind(this.objPagination);
		
		objPage.objPagination.objPrevious = {};
		objPage.objPagination.objPrevious.domElement = document.createElement('button');
		objPage.objPagination.objPrevious.domElement.appendChild(document.createTextNode('<'));
		objPage.objPagination.objPrevious.fncCallback = this.objPagination.previousPage.bind(this.objPagination);

		objPage.objPagination.objNext = {};
		objPage.objPagination.objNext.domElement = document.createElement('button');
		objPage.objPagination.objNext.domElement.appendChild(document.createTextNode('>'));
		objPage.objPagination.objNext.fncCallback = this.objPagination.nextPage.bind(this.objPagination);

		objPage.objPagination.objLast = {};
		objPage.objPagination.objLast.domElement = document.createElement('button');
		objPage.objPagination.objLast.domElement.appendChild(document.createTextNode('>>'));
		objPage.objPagination.objLast.fncCallback = this.objPagination.lastPage.bind(this.objPagination);

		objPage.objPagination.domElement.appendChild(objPage.objPagination.objFirst.domElement);
		objPage.objPagination.domElement.appendChild(objPage.objPagination.objPrevious.domElement);
		objPage.objPagination.domElement.appendChild(objPage.objPagination.objNext.domElement);
		objPage.objPagination.domElement.appendChild(objPage.objPagination.objLast.domElement);

		Event.startObserving(objPage.objPagination.objFirst.domElement, 'click', objPage.objPagination.objFirst.fncCallback, false);
		Event.startObserving(objPage.objPagination.objPrevious.domElement, 'click', objPage.objPagination.objPrevious.fncCallback, false);
		Event.startObserving(objPage.objPagination.objNext.domElement, 'click', objPage.objPagination.objNext.fncCallback, false);
		Event.startObserving(objPage.objPagination.objLast.domElement, 'click', objPage.objPagination.objLast.fncCallback, false);

		// Initialise selection buttons
		objPage.objSelection = {};
		objPage.objSelection.domElement = document.createElement('div');
		
		objPage.objSelection.objSelectAll = {};
		objPage.objSelection.objSelectAll.domElement = document.createElement('a');
		objPage.objSelection.objSelectAll.domElement.appendChild(document.createTextNode('All'));
		
		objPage.objSelection.objSelectNone = {};
		objPage.objSelection.objSelectNone.domElement = document.createElement('a');
		objPage.objSelection.objSelectNone.domElement.appendChild(document.createTextNode('None'));
		
		objPage.objSelection.objApproveSelected = {};
		objPage.objSelection.objApproveSelected.domElement = document.createElement('a');
		objPage.objSelection.objApproveSelected.domElement.appendChild(document.createTextNode('Approve'));
		
		objPage.objSelection.objDeclineSelected = {};
		objPage.objSelection.objDeclineSelected.domElement = document.createElement('a');
		objPage.objSelection.objDeclineSelected.domElement.appendChild(document.createTextNode('Reject'));

		objPage.objSelection.domElement.appendChild(document.createTextNode('Select: '));
		objPage.objSelection.domElement.appendChild(objPage.objSelection.objSelectAll.domElement);
		objPage.objSelection.domElement.appendChild(document.createTextNode(' | '));
		objPage.objSelection.domElement.appendChild(objPage.objSelection.objSelectNone.domElement);
		objPage.objSelection.domElement.appendChild(document.createTextNode(' With Selected: '));
		objPage.objSelection.domElement.appendChild(objPage.objSelection.objApproveSelected.domElement);
		objPage.objSelection.domElement.appendChild(document.createTextNode(' | '));
		objPage.objSelection.domElement.appendChild(objPage.objSelection.objDeclineSelected.domElement);

		Event.startObserving(objPage.objSelection.objSelectAll.domElement, 'click', this.selectAll.bind(this), false);
		Event.startObserving(objPage.objSelection.objSelectNone.domElement, 'click', this.selectNone.bind(this), false);
		Event.startObserving(objPage.objSelection.objApproveSelected.domElement, 'click', this.approveSelected.bind(this), false);
		Event.startObserving(objPage.objSelection.objDeclineSelected.domElement, 'click', this.declineSelected.bind(this), false);

		// Initialise selection buttons (second set)
		objPage.objSelection2 = {};
		objPage.objSelection2.domElement = document.createElement('div');
		
		objPage.objSelection2.objSelectAll = {};
		objPage.objSelection2.objSelectAll.domElement = document.createElement('a');
		objPage.objSelection2.objSelectAll.domElement.appendChild(document.createTextNode('All'));
		
		objPage.objSelection2.objSelectNone = {};
		objPage.objSelection2.objSelectNone.domElement = document.createElement('a');
		objPage.objSelection2.objSelectNone.domElement.appendChild(document.createTextNode('None'));
		
		objPage.objSelection2.objApproveSelected = {};
		objPage.objSelection2.objApproveSelected.domElement = document.createElement('a');
		objPage.objSelection2.objApproveSelected.domElement.appendChild(document.createTextNode('Approve'));
		
		objPage.objSelection2.objDeclineSelected = {};
		objPage.objSelection2.objDeclineSelected.domElement = document.createElement('a');
		objPage.objSelection2.objDeclineSelected.domElement.appendChild(document.createTextNode('Reject'));

		objPage.objSelection2.domElement.appendChild(document.createTextNode('Select: '));
		objPage.objSelection2.domElement.appendChild(objPage.objSelection2.objSelectAll.domElement);
		objPage.objSelection2.domElement.appendChild(document.createTextNode(' | '));
		objPage.objSelection2.domElement.appendChild(objPage.objSelection2.objSelectNone.domElement);
		objPage.objSelection2.domElement.appendChild(document.createTextNode(' With Selected: '));
		objPage.objSelection2.domElement.appendChild(objPage.objSelection2.objApproveSelected.domElement);
		objPage.objSelection2.domElement.appendChild(document.createTextNode(' | '));
		objPage.objSelection2.domElement.appendChild(objPage.objSelection2.objDeclineSelected.domElement);

		Event.startObserving(objPage.objSelection2.objSelectAll.domElement, 'click', this.selectAll.bind(this), false);
		Event.startObserving(objPage.objSelection2.objSelectNone.domElement, 'click', this.selectNone.bind(this), false);
		Event.startObserving(objPage.objSelection2.objApproveSelected.domElement, 'click', this.approveSelected.bind(this), false);
		Event.startObserving(objPage.objSelection2.objDeclineSelected.domElement, 'click', this.declineSelected.bind(this), false);



		//----------------------------------------------------------------//
		// Table
		//----------------------------------------------------------------//
		objPage.objTable						= {};
		objPage.objTable.domElement				= document.createElement('table');
		objPage.objTable.domElement.className	= 'reflex highlight-rows';
		objPage.domElement.appendChild(objPage.objTable.domElement);

		
		//----------------------------------------------------------------//
		// Table Caption
		//----------------------------------------------------------------//
		objPage.objTable.objCaption = {};
		objPage.objTable.objCaption.domElement = document.createElement('caption');
		objPage.objTable.domElement.appendChild(objPage.objTable.objCaption.domElement);
		
		objPage.objTable.objCaption.objCaptionBar = {};
		objPage.objTable.objCaption.objCaptionBar.domElement = document.createElement('div');
		objPage.objTable.objCaption.objCaptionBar.domElement.className = 'caption_bar';
		objPage.objTable.objCaption.domElement.appendChild(objPage.objTable.objCaption.objCaptionBar.domElement);
		
		objPage.objTable.objCaption.objCaptionBar.objTitle = {};
		objPage.objTable.objCaption.objCaptionBar.objTitle.domElement = document.createElement('div');
		objPage.objTable.objCaption.objCaptionBar.objTitle.domElement.className = 'caption_title';
		objPage.objTable.objCaption.objCaptionBar.objTitle.domElement.style.minHeight = '1.55em';
		objPage.objTable.objCaption.objCaptionBar.objTitle.domElement.innerHTML = 'No records';
		objPage.objTable.objCaption.objCaptionBar.domElement.appendChild(objPage.objTable.objCaption.objCaptionBar.objTitle.domElement);
		
		objPage.objTable.objCaption.objCaptionBar.objOptions = {};
		objPage.objTable.objCaption.objCaptionBar.objOptions.domElement = document.createElement('div');
		objPage.objTable.objCaption.objCaptionBar.objOptions.domElement.className = 'caption_options';
		
		var elmDiv = document.createElement('div');
		elmDiv.style.cssFloat = 'none';
		elmDiv.style.clear = 'both';
		objPage.objSelection2.domElement.style.cssFloat = 'left';
		objPage.objSelection2.domElement.style.marginTop = '0.75em';
		objPage.objPagination.domElement.style.cssFloat = 'right';
		objPage.objPagination.domElement.style.marginLeft = '2em';
		objPage.objTable.objCaption.objCaptionBar.objOptions.domElement.appendChild(objPage.objPagination.domElement);
		objPage.objTable.objCaption.objCaptionBar.objOptions.domElement.appendChild(objPage.objSelection2.domElement);
		objPage.objTable.objCaption.objCaptionBar.objOptions.domElement.appendChild(elmDiv);
		
		objPage.objTable.objCaption.objCaptionBar.domElement.appendChild(objPage.objTable.objCaption.objCaptionBar.objOptions.domElement);
		
		//----------------------------------------------------------------//
		// Table Header
		//----------------------------------------------------------------//
		objPage.objTable.objTHEAD				= {};
		objPage.objTable.objTHEAD.domElement	= document.createElement('thead');
		objPage.objTable.domElement.appendChild(objPage.objTable.objTHEAD.domElement);

		objPage.objTable.objTHEAD.objColumnTitlesTR			= {};
		objPage.objTable.objTHEAD.objColumnTitlesTR.domElement = document.createElement('tr');
		objPage.objTable.objTHEAD.domElement.appendChild(objPage.objTable.objTHEAD.objColumnTitlesTR.domElement);
		

		// Selector column
		objPage.objTable.objTHEAD.objSelector					= {};
		objPage.objTable.objTHEAD.objSelector.domElement		= document.createElement('th');
		objPage.objTable.objTHEAD.objColumnTitlesTR.domElement.appendChild(objPage.objTable.objTHEAD.objSelector.domElement);

		// RecCharge Description column
		objPage.objTable.objTHEAD.objRecChargeDescription = {};
		objPage.objTable.objTHEAD.objRecChargeDescription.domElement = document.createElement('th');
		objPage.objTable.objTHEAD.objRecChargeDescription.domElement.appendChild(document.createTextNode('Charge Type'));
		objPage.objTable.objTHEAD.objColumnTitlesTR.domElement.appendChild(objPage.objTable.objTHEAD.objRecChargeDescription.domElement);

		// RecCharge MinCharge column
		objPage.objTable.objTHEAD.objRecChargeMinCharge = {};
		objPage.objTable.objTHEAD.objRecChargeMinCharge.domElement = document.createElement('th');
		objPage.objTable.objTHEAD.objRecChargeMinCharge.domElement.innerHTML = 'Min Charge ($&nbsp;inc&nbsp;GST)';
		objPage.objTable.objTHEAD.objRecChargeMinCharge.domElement.style.textAlign = 'right';
		objPage.objTable.objTHEAD.objColumnTitlesTR.domElement.appendChild(objPage.objTable.objTHEAD.objRecChargeMinCharge.domElement);

		// RecCharge RecursionCharge column
		objPage.objTable.objTHEAD.objRecChargeRecursionCharge = {};
		objPage.objTable.objTHEAD.objRecChargeRecursionCharge.domElement = document.createElement('th');
		objPage.objTable.objTHEAD.objRecChargeRecursionCharge.domElement.appendChild(document.createTextNode('Rec Charge'));
		objPage.objTable.objTHEAD.objRecChargeRecursionCharge.domElement.style.textAlign = 'right';
		objPage.objTable.objTHEAD.objColumnTitlesTR.domElement.appendChild(objPage.objTable.objTHEAD.objRecChargeRecursionCharge.domElement);

		// RecCharge Nature column
		objPage.objTable.objTHEAD.objRecChargeNature = {};
		objPage.objTable.objTHEAD.objRecChargeNature.domElement = document.createElement('th');
		objPage.objTable.objTHEAD.objRecChargeNature.domElement.appendChild(document.createTextNode('Nature'));
		objPage.objTable.objTHEAD.objColumnTitlesTR.domElement.appendChild(objPage.objTable.objTHEAD.objRecChargeNature.domElement);

		// RecCharge Period column
		objPage.objTable.objTHEAD.objRecChargePeriod = {};
		objPage.objTable.objTHEAD.objRecChargePeriod.domElement = document.createElement('th');
		objPage.objTable.objTHEAD.objRecChargePeriod.domElement.appendChild(document.createTextNode('Charged'));
		objPage.objTable.objTHEAD.objColumnTitlesTR.domElement.appendChild(objPage.objTable.objTHEAD.objRecChargePeriod.domElement);

		// Account column
		objPage.objTable.objTHEAD.objAccount = {};
		objPage.objTable.objTHEAD.objAccount.domElement = document.createElement('th');
		objPage.objTable.objTHEAD.objAccount.domElement.appendChild(document.createTextNode('Account'));
		objPage.objTable.objTHEAD.objColumnTitlesTR.domElement.appendChild(objPage.objTable.objTHEAD.objAccount.domElement);

		// Service FNN column
		objPage.objTable.objTHEAD.objServiceFNN = {};
		objPage.objTable.objTHEAD.objServiceFNN.domElement = document.createElement('th');
		objPage.objTable.objTHEAD.objServiceFNN.domElement.appendChild(document.createTextNode('Service'));
		objPage.objTable.objTHEAD.objColumnTitlesTR.domElement.appendChild(objPage.objTable.objTHEAD.objServiceFNN.domElement);

		// RequestedOn column
		objPage.objTable.objTHEAD.objRequestedOn = {};
		objPage.objTable.objTHEAD.objRequestedOn.domElement = document.createElement('th');
		objPage.objTable.objTHEAD.objRequestedOn.domElement.appendChild(document.createTextNode('Requested'));
		objPage.objTable.objTHEAD.objColumnTitlesTR.domElement.appendChild(objPage.objTable.objTHEAD.objRequestedOn.domElement);

		// First Installment ChargedOn column
		objPage.objTable.objTHEAD.objFirstChargedOn = {};
		objPage.objTable.objTHEAD.objFirstChargedOn.domElement = document.createElement('th');
		objPage.objTable.objTHEAD.objFirstChargedOn.domElement.appendChild(document.createTextNode('First Charge'));
		objPage.objTable.objTHEAD.objColumnTitlesTR.domElement.appendChild(objPage.objTable.objTHEAD.objFirstChargedOn.domElement);

		// Final Installment ChargedOn column
		objPage.objTable.objTHEAD.objFinalChargedOn = {};
		objPage.objTable.objTHEAD.objFinalChargedOn.domElement = document.createElement('th');
		objPage.objTable.objTHEAD.objFinalChargedOn.domElement.appendChild(document.createTextNode('Final Charge'));
		objPage.objTable.objTHEAD.objColumnTitlesTR.domElement.appendChild(objPage.objTable.objTHEAD.objFinalChargedOn.domElement);


		// Actions column
		objPage.objTable.objTHEAD.objActions = {};
		objPage.objTable.objTHEAD.objActions.domElement = document.createElement('th');
		objPage.objTable.objTHEAD.objColumnTitlesTR.domElement.appendChild(objPage.objTable.objTHEAD.objActions.domElement);
		
		//----------------------------------------------------------------//
		// Table Body
		//----------------------------------------------------------------//
		objPage.objTable.objTBODY				= {};
		objPage.objTable.objTBODY.domElement	= document.createElement('tbody');
		objPage.objTable.domElement.appendChild(objPage.objTable.objTBODY.domElement);
		//----------------------------------------------------------------//

		//----------------------------------------------------------------//
		// Table Footer
		//----------------------------------------------------------------//
		objPage.objTable.objTFOOT				= {};
		objPage.objTable.objTFOOT.domElement	= document.createElement('tfoot');
		objPage.objTable.domElement.appendChild(objPage.objTable.objTFOOT.domElement);
		
		objPage.objTable.objTFOOT.objTR = {};
		objPage.objTable.objTFOOT.objTR.domElement = document.createElement('tr');
		objPage.objTable.objTFOOT.domElement.appendChild(objPage.objTable.objTFOOT.objTR.domElement);
		
		objPage.objTable.objTFOOT.objTR.objTD = {};
		objPage.objTable.objTFOOT.objTR.objTD.domElement = document.createElement('th');
		objPage.objTable.objTFOOT.objTR.objTD.domElement.colSpan = objPage.objTable.objTHEAD.objColumnTitlesTR.domElement.childNodes.length;
		objPage.objTable.objTFOOT.objTR.objTD.domElement.style.textAlign = 'left';
		objPage.objTable.objTFOOT.objTR.domElement.appendChild(objPage.objTable.objTFOOT.objTR.objTD.domElement);
		objPage.objTable.objTFOOT.objTR.objTD.domElement.appendChild(objPage.objSelection.domElement);
		
		//----------------------------------------------------------------//
		// Debug "Console"
		//----------------------------------------------------------------//
		objPage.objDebugConsole	= {};
		objPage.objDebugConsole.domElement	= document.createElement('div');
		objPage.objDebugConsole.domElement.style.height				= '10em';
		objPage.objDebugConsole.domElement.style.width				= '100%';
		objPage.objDebugConsole.domElement.style.backgroundColor	= '#fff';
		objPage.objDebugConsole.domElement.style.overflowY			= 'scroll';
		objPage.objDebugConsole.domElement.style.borderColor		= '#000';
		objPage.objDebugConsole.domElement.style.borderWidth		= '1px';
		objPage.objDebugConsole.domElement.style.fontFamily			= '"Courier New", Courier, monospace, sans-serif';
		objPage.objDebugConsole.domElement.style.display			= "none";
		objPage.domElement.appendChild(objPage.objDebugConsole.domElement);
		//----------------------------------------------------------------//
		
		// Set the Page Object
		this._objPage = objPage;


		//----------------------------------------------------------------//
		// Confirmation Popup
		//----------------------------------------------------------------//
		var objPopup = {};
		var elmDiv, elmSpan;
		objPopup.objBody = {};
		objPopup.objBody.domElement = document.createElement('div');
		
		// Cancel button
		objPopup.domCancelButton = document.createElement('button');
		objPopup.domCancelButton.innerHTML = 'No';
		Event.startObserving(objPopup.domCancelButton, 'click', this._closeConfirmationPopup.bind(this), false);
		
		// RecCharge Approval Confirmation buttons
		objPopup.domApproveRecChargesConfirmButton = document.createElement('button');
		objPopup.domApproveRecChargesConfirmButton.innerHTML = 'Yes';
		Event.startObserving(objPopup.domApproveRecChargesConfirmButton, 'click', this._approveRecCharges.bind(this), false);
		
		// RecCharge Decline Confirmation buttons
		objPopup.domDeclineRecChargesConfirmButton = document.createElement('button');
		objPopup.domDeclineRecChargesConfirmButton.innerHTML = 'Yes';
		Event.startObserving(objPopup.domDeclineRecChargesConfirmButton, 'click', this._declineRecCharges.bind(this), false);
		
		// Prompt
		objPopup.objPrompt = {}
		objPopup.objPrompt.domElement = document.createElement('div');
		objPopup.objPrompt.domElement.style.marginBottom = '0.5em';
		objPopup.objPrompt.domElement.innerHTML = '[INSERT PROMPT HERE]';

		// Summary
		objPopup.objSummary = {}
		objPopup.objSummary.elmTable = document.createElement('table');
		objPopup.objSummary.elmTable.className = 'form-data';
		
		// Summary - TotalCharges
		objPopup.objSummary.objTotalCharges = {};
		objPopup.objSummary.objTotalCharges.elmRow = document.createElement('tr');
		objPopup.objSummary.objTotalCharges.elmTitle = document.createElement('td');
		objPopup.objSummary.objTotalCharges.elmTitle.className = 'title';
		objPopup.objSummary.objTotalCharges.elmTitle.appendChild(document.createTextNode('Total Recurring Charges :'));
		objPopup.objSummary.objTotalCharges.elmValue = document.createElement('td');
		objPopup.objSummary.objTotalCharges.elmRow.appendChild(objPopup.objSummary.objTotalCharges.elmTitle);
		objPopup.objSummary.objTotalCharges.elmRow.appendChild(objPopup.objSummary.objTotalCharges.elmValue);

		// Summary - TotalChargeTypes
		objPopup.objSummary.objTotalChargeTypes = {};
		objPopup.objSummary.objTotalChargeTypes.elmRow = document.createElement('tr');
		objPopup.objSummary.objTotalChargeTypes.elmTitle = document.createElement('td');
		objPopup.objSummary.objTotalChargeTypes.elmTitle.className = 'title';
		objPopup.objSummary.objTotalChargeTypes.elmTitle.appendChild(document.createTextNode('Total Recurring Charge Types :'));
		objPopup.objSummary.objTotalChargeTypes.elmValue = document.createElement('td');
		objPopup.objSummary.objTotalChargeTypes.elmRow.appendChild(objPopup.objSummary.objTotalChargeTypes.elmTitle);
		objPopup.objSummary.objTotalChargeTypes.elmRow.appendChild(objPopup.objSummary.objTotalChargeTypes.elmValue);

		// Summary - TotalAccountsAffected
		objPopup.objSummary.objTotalAccounts = {};
		objPopup.objSummary.objTotalAccounts.elmRow = document.createElement('tr');
		objPopup.objSummary.objTotalAccounts.elmTitle = document.createElement('td');
		objPopup.objSummary.objTotalAccounts.elmTitle.className = 'title';
		objPopup.objSummary.objTotalAccounts.elmTitle.appendChild(document.createTextNode('Total Accounts Affected :'));
		objPopup.objSummary.objTotalAccounts.elmValue = document.createElement('td');
		objPopup.objSummary.objTotalAccounts.elmRow.appendChild(objPopup.objSummary.objTotalAccounts.elmTitle);
		objPopup.objSummary.objTotalAccounts.elmRow.appendChild(objPopup.objSummary.objTotalAccounts.elmValue);

		// Summary - TotalCredits
		objPopup.objSummary.objTotalCredits = {};
		objPopup.objSummary.objTotalCredits.elmRow = document.createElement('tr');
		objPopup.objSummary.objTotalCredits.elmTitle = document.createElement('td');
		objPopup.objSummary.objTotalCredits.elmTitle.className = 'title';
		objPopup.objSummary.objTotalCredits.elmTitle.appendChild(document.createTextNode('Total Minimum Credits :'));
		objPopup.objSummary.objTotalCredits.elmValue = document.createElement('td');
		objPopup.objSummary.objTotalCredits.elmRow.appendChild(objPopup.objSummary.objTotalCredits.elmTitle);
		objPopup.objSummary.objTotalCredits.elmRow.appendChild(objPopup.objSummary.objTotalCredits.elmValue);

		// Summary - TotalDebits
		objPopup.objSummary.objTotalDebits = {};
		objPopup.objSummary.objTotalDebits.elmRow = document.createElement('tr');
		objPopup.objSummary.objTotalDebits.elmTitle = document.createElement('td');
		objPopup.objSummary.objTotalDebits.elmTitle.className = 'title';
		objPopup.objSummary.objTotalDebits.elmTitle.appendChild(document.createTextNode('Total Minimum Debits :'));
		objPopup.objSummary.objTotalDebits.elmValue = document.createElement('td');
		objPopup.objSummary.objTotalDebits.elmRow.appendChild(objPopup.objSummary.objTotalDebits.elmTitle);
		objPopup.objSummary.objTotalDebits.elmRow.appendChild(objPopup.objSummary.objTotalDebits.elmValue);


		objPopup.objSummary.elmTable.appendChild(objPopup.objSummary.objTotalCharges.elmRow);
		objPopup.objSummary.elmTable.appendChild(objPopup.objSummary.objTotalChargeTypes.elmRow);
		objPopup.objSummary.elmTable.appendChild(objPopup.objSummary.objTotalAccounts.elmRow);
		objPopup.objSummary.elmTable.appendChild(objPopup.objSummary.objTotalCredits.elmRow);
		objPopup.objSummary.elmTable.appendChild(objPopup.objSummary.objTotalDebits.elmRow);

		
		// Reason for declining the charge(s)
		objPopup.objReasonForDecline = {};
		objPopup.objReasonForDecline.domElement = document.createElement('div');
		objPopup.objReasonForDecline.domElement.style.marginTop = '0.5em';
		objPopup.objReasonForDecline.domElmTextarea = document.createElement('textarea');
		objPopup.objReasonForDecline.domElmTextarea.rows = '5';
		objPopup.objReasonForDecline.domElmTextarea.style.width = '98%';
		
		elmSpan = document.createElement('span');
		elmSpan.innerHTML = 'Reason for rejection :';
		objPopup.objReasonForDecline.domElement.appendChild(elmSpan);
		objPopup.objReasonForDecline.domElement.appendChild(document.createElement('br'));
		objPopup.objReasonForDecline.domElement.appendChild(objPopup.objReasonForDecline.domElmTextarea);

		// Build the popup body
		elmDiv = document.createElement('div');
		//elmDiv.className = 'GroupedContent';
		elmDiv.style.margin = '0.75em';
		
		elmDiv.appendChild(objPopup.objPrompt.domElement);
		elmDiv.appendChild(objPopup.objSummary.elmTable);
		elmDiv.appendChild(objPopup.objReasonForDecline.domElement);
		
		objPopup.objBody.domElement.appendChild(elmDiv);
		
		objPopup.popPopup = new Reflex_Popup(40);
		objPopup.popPopup.setTitle('Are you sure you want to do that?');
		objPopup.popPopup.setFooterButtons([objPopup.domDeclineRecChargesConfirmButton, objPopup.domApproveRecChargesConfirmButton, objPopup.domCancelButton], true);
		objPopup.popPopup.setContent(objPopup.objBody.domElement);
		
		this._objConfirmationPopup = objPopup;
		
		// Load the data
		this.objPagination.getCurrentPage();
	},
	
	_updateTable : function(objResultSet)
	{
		// Dump existing content
		this._objPage.objDebugConsole.domElement.innerHTML	+= "Updating Table content...<br />";
		
		// Formally destroy all event listeners
		this._removeRecChargeEventListeners();
		
		while (this._objPage.objTable.objTBODY.domElement.firstChild)
		{
			this._objPage.objTable.objTBODY.domElement.removeChild(this._objPage.objTable.objTBODY.domElement.firstChild);
		}

		// Keep a temporary reference to the old page of recurring charges, so you can check if any of them are still selected
		var objOldRecCharges = this._objRecCharges;
		this._objRecCharges = {};

		// Update content
		if (!objResultSet || objResultSet.intTotalResults == 0 || objResultSet.arrResultSet.length == 0)
		{
			this._objPage.objDebugConsole.domElement.innerHTML	+= "&nbsp;&nbsp;&nbsp;&nbsp;[-] No Records to Display<br />";
			
			// No records
			this._objPage.objTable.objCaption.objCaptionBar.objTitle.domElement.innerHTML = 'No Records';
			
			var objTR	= document.createElement('tr');
			
			var objTD				= document.createElement('td');
			objTD.colSpan			= this._objPage.objTable.objTHEAD.objColumnTitlesTR.domElement.childNodes.length;
			objTD.appendChild(document.createTextNode('There are no records to display'));
			objTD.style.textAlign	= 'center';
			objTR.appendChild(objTD);
			
			this._objPage.objTable.objTBODY.domElement.appendChild(objTR);
		}
		else
		{
			var intCurrentPage = objResultSet.intCurrentPage + 1;
			this._objPage.objTable.objCaption.objCaptionBar.objTitle.domElement.innerHTML = 'Page '+ intCurrentPage +' of ' + objResultSet.intPageCount;
			
			var elmTR, elmTD, strRecChargeDescription, elmAnchor, intId, elmImage, elmSpan;
			
			var bolAlternateRow = true;
			
			var strChargePeriodDescription;
			
			for (var i in objResultSet.arrResultSet)
			{
				// Store relevent details/elements regarding the recurring charge
				intId = objResultSet.arrResultSet[i].id;
				this._objRecCharges[intId] = {};
				this._objRecCharges[intId].objRecCharge = {};
				this._objRecCharges[intId].objRecCharge = objResultSet.arrResultSet[i];

				elmTR	= document.createElement('tr');
				
				bolAlternateRow = !bolAlternateRow;
				elmTR.className = 'selectable';
				if (bolAlternateRow)
				{
					elmTR.className += ' alt';
				}

				// The selector
				this._objRecCharges[intId].objSelector = {};
				this._objRecCharges[intId].objSelector.domElement = document.createElement('input');
				this._objRecCharges[intId].objSelector.domElement.type = 'checkbox';

				// If the charge was previously in the list, then retain the selector value
				if (objOldRecCharges[intId] != undefined)
				{
					this._objRecCharges[intId].objSelector.domElement.checked = objOldRecCharges[intId].objSelector.domElement.checked;
				}

				elmTD = document.createElement('td');
				elmTD.appendChild(this._objRecCharges[intId].objSelector.domElement);
				elmTR.appendChild(elmTD);
				
				// The RecCharge Description
				strRecChargeDescription = objResultSet.arrResultSet[i].description +" ("+ objResultSet.arrResultSet[i].chargeType +")";
				elmTD = document.createElement('td');
				elmTD.appendChild(document.createTextNode(strRecChargeDescription));
				elmTR.appendChild(elmTD);

				// The RecCharge MinCharge (inc GST formatted to 2 dec places)
				elmTD = document.createElement('td');
				elmTD.style.textAlign = 'right';
				elmTD.appendChild(document.createTextNode(objResultSet.arrResultSet[i].minChargeIncGstFormatted));
				elmTR.appendChild(elmTD);
				
				// The RecCharge RecursionCharge (inc GST formatted to 2 dec places)
				elmTD = document.createElement('td');
				elmTD.style.textAlign = 'right';
				elmTD.appendChild(document.createTextNode(objResultSet.arrResultSet[i].recursionChargeIncGstFormatted));
				elmTR.appendChild(elmTD);
				
				// Nature
				elmTD = document.createElement('td');
				elmTD.appendChild(document.createTextNode(objResultSet.arrResultSet[i].natureFormatted));
				elmTR.appendChild(elmTD);

				// In Advance / In Arrears - period - times to charge
				strChargePeriodDescription = 'In&nbsp;'+ objResultSet.arrResultSet[i].inAdvanceFormatted +' every ';
				if (objResultSet.arrResultSet[i].recurringFreq != 1)
				{
					strChargePeriodDescription += objResultSet.arrResultSet[i].recurringFreq +"&nbsp;";
				}
				strChargePeriodDescription += objResultSet.arrResultSet[i].frequencyTypeFormatted +' ('+ objResultSet.arrResultSet[i].timesToCharge +'&nbsp;times)';
				
				if (objResultSet.arrResultSet[i].hasPartialFinalCharge)
				{
					// The final installment charge is a partial one
					strChargePeriodDescription += " Final Charge: $"+ objResultSet.arrResultSet[i].partialFinalChargeInGSTFormatted;
				}
				
				elmTD = document.createElement('td');
				elmTD.innerHTML = strChargePeriodDescription;
				elmTR.appendChild(elmTD);

				// Account
				elmAnchor = document.createElement('a');
				elmAnchor.href = objResultSet.arrResultSet[i].accountViewHref;
				elmAnchor.title = "Invoices and Payments";
				elmAnchor.appendChild(document.createTextNode(objResultSet.arrResultSet[i].account));
				elmTD = document.createElement('td');
				elmTD.appendChild(elmAnchor);
				elmTD.appendChild(document.createElement('br'));
				elmTD.appendChild(document.createTextNode(objResultSet.arrResultSet[i].accountName));
				elmTR.appendChild(elmTD);
				
				// Service FNN
				elmTD = document.createElement('td');
				if (objResultSet.arrResultSet[i].service != null)
				{
					elmAnchor = document.createElement('a');
					elmAnchor.href = objResultSet.arrResultSet[i].serviceViewHref;
					elmAnchor.title = "View Service";
					elmAnchor.appendChild(document.createTextNode(objResultSet.arrResultSet[i].serviceFNN));
					elmTD.appendChild(elmAnchor);
				}
				elmTR.appendChild(elmTD);
				
				// RequestedOn
				elmTD = document.createElement('td');
				elmTD.appendChild(document.createTextNode(objResultSet.arrResultSet[i].createdOnFormated));
				elmTD.appendChild(document.createElement('br'));
				elmTD.appendChild(document.createTextNode("("+ objResultSet.arrResultSet[i].createdByEmployeeName +")"));
				if (objResultSet.arrResultSet[i].startedOn != objResultSet.arrResultSet[i].createdOn)
				{
					// The official starting date is different to the createdOn date (if creating recurring charges on the 29th - 31st of the month, we snap them to the 28th or 1st of next month)
					elmTD.appendChild(document.createElement('br'));
					elmTD.appendChild(document.createTextNode("(Starts "+ objResultSet.arrResultSet[i].startedOnFormated +")"));
					
				}
				
				elmTR.appendChild(elmTD);

				// Proposed date for First Installment
				elmTD = document.createElement('td');
				elmTD.appendChild(document.createTextNode(objResultSet.arrResultSet[i].chargedOnForFirstInstallmentFormatted));
				elmTR.appendChild(elmTD);

				// Proposed date for Last Installment
				elmTD = document.createElement('td');
				elmTD.appendChild(document.createTextNode(objResultSet.arrResultSet[i].chargedOnForFinalInstallmentFormatted));
				if (objResultSet.arrResultSet[i].continuable != 0)
				{
					// The recurring charge will continue after this time
					elmTD.appendChild(document.createElement('br'));
					elmTD.appendChild(document.createTextNode('(continuable)'));
				}
				if (objResultSet.arrResultSet[i].hasPartialFinalCharge)
				{
					// The last installment charge will be less than the normal installment charge
					elmSpan = document.createElement('span');
					elmSpan.appendChild(document.createTextNode("($"+ objResultSet.arrResultSet[i].partialFinalChargeInGSTFormatted +")"));
					elmSpan.style.color = '#FF0000';
					elmTD.appendChild(document.createElement('br'));
					elmTD.appendChild(elmSpan);
				}
				elmTR.appendChild(elmTD);
				
				// Actions (store references to these elements in the _objChares object)
				elmImage = document.createElement('img');
				elmImage.src = 'img/template/approve.png';
				elmImage.alt = 'Approve';
				elmAnchor = document.createElement('a');
				elmAnchor.appendChild(elmImage);
				
				this._objRecCharges[intId].objApprove = {};
				this._objRecCharges[intId].objApprove.domElement = elmAnchor;
				
				elmImage = document.createElement('img');
				elmImage.src = 'img/template/decline.png';
				elmImage.alt = 'Reject';
				elmAnchor = document.createElement('a');
				elmAnchor.appendChild(elmImage);
				
				this._objRecCharges[intId].objDecline = {};
				this._objRecCharges[intId].objDecline.domElement = elmAnchor;
				
				elmTD = document.createElement('td');
				elmTD.setAttribute('nowrap', 'nowrap');
				elmTD.style.textAlign = 'right';
				
				elmTD.appendChild(this._objRecCharges[intId].objApprove.domElement);
				elmTD.appendChild(this._objRecCharges[intId].objDecline.domElement);

				elmTR.appendChild(elmTD);
				
				this._objPage.objTable.objTBODY.domElement.appendChild(elmTR);

				this._objRecCharges[intId].objRow = {};
				this._objRecCharges[intId].objRow.domElement = elmTR;
			}
		}

		// Register all event listeners
		this._createRecChargeEventListeners();
		
		// Update pagination navigation
		this._updatePagination();
	},
	
	_updatePagination : function(intPageCount)
	{
		// Disable all pagination buttons
		this._objPage.objPagination.objFirst.domElement.disabled = true;
		this._objPage.objPagination.objPrevious.domElement.disabled = true;
		this._objPage.objPagination.objNext.domElement.disabled = true;
		this._objPage.objPagination.objLast.domElement.disabled = true;
		
		if (intPageCount == undefined)
		{
			// Get the page count
			this.objPagination.getPageCount(this._updatePagination.bind(this));
		}
		else
		{
			if (this.objPagination.intCurrentPage != Pagination.PAGE_FIRST)
			{
				// Enable the first and previous buttons
				this._objPage.objPagination.objFirst.domElement.disabled = false;
				this._objPage.objPagination.objPrevious.domElement.disabled = false;
			}
			if (this.objPagination.intCurrentPage < (intPageCount - 1) && intPageCount)
			{
				// Enable the next and last buttons
				this._objPage.objPagination.objNext.domElement.disabled = false;
				this._objPage.objPagination.objLast.domElement.disabled = false;
			}
		}
	},
	
	_removeRecChargeEventListeners : function()
	{
		for (var i in this._objRecCharges)
		{
			Event.stopObserving(this._objRecCharges[i].objApprove.domElement, 'click', this._objRecCharges[i].objApprove.fncOnclick, false);
			Event.stopObserving(this._objRecCharges[i].objDecline.domElement, 'click', this._objRecCharges[i].objDecline.fncOnclick, false);
		}
	},
	
	_createRecChargeEventListeners : function()
	{
		for (var i in this._objRecCharges)
		{
			this._objRecCharges[i].objApprove.fncOnclick = this.approveRecCharge.bind(this, i);
			this._objRecCharges[i].objDecline.fncOnclick = this.declineRecCharge.bind(this, i);			
			
			Event.startObserving(this._objRecCharges[i].objApprove.domElement, 'click', this._objRecCharges[i].objApprove.fncOnclick, false);
			Event.startObserving(this._objRecCharges[i].objDecline.domElement, 'click', this._objRecCharges[i].objDecline.fncOnclick, false);
		}
	},
	
	approveRecCharge : function(intRecChargeId)
	{
		if (!this._objRecCharges[intRecChargeId])
		{
			$Alert('The charge could not be found');
			return;
		}
		
		// Update the list of charges to action
		this._arrRecChargeIdsToBeActioned = new Array();
		this._arrRecChargeIdsToBeActioned.push(intRecChargeId);
		
		this._promptUser(Recurring_Charge_Management.APPROVE_CHARGES);
	},
	
	declineRecCharge : function(intRecChargeId)
	{
		if (!this._objRecCharges[intRecChargeId])
		{
			$Alert('The charge could not be found');
			return;
		}
		
		// Update the list of charges to action
		this._arrRecChargeIdsToBeActioned = new Array();
		this._arrRecChargeIdsToBeActioned.push(intRecChargeId);
		
		this._promptUser(Recurring_Charge_Management.DECLINE_CHARGES);
	},
	
	approveSelected : function()
	{
		// Update the list of charges to action
		this._arrRecChargeIdsToBeActioned = new Array();
		
		for (var i in this._objRecCharges)
		{
			if (this._objRecCharges[i].objSelector.domElement.checked)
			{
				this._arrRecChargeIdsToBeActioned.push(this._objRecCharges[i].objRecCharge.id);
			}
		}
		
		this._promptUser(Recurring_Charge_Management.APPROVE_CHARGES);
	},
	
	declineSelected : function()
	{
		// Update the list of charges to action
		this._arrRecChargeIdsToBeActioned = new Array();
		
		for (var i in this._objRecCharges)
		{
			if (this._objRecCharges[i].objSelector.domElement.checked)
			{
				this._arrRecChargeIdsToBeActioned.push(this._objRecCharges[i].objRecCharge.id);
			}
		}
		
		this._promptUser(Recurring_Charge_Management.DECLINE_CHARGES);
	},
	
	_promptUser : function(action)
	{
		if (this._bolIsSubmitting)
		{
			$Alert('Another submittion is pending with the server, please wait for it to finish');
			return;
		}
		
		var intRecChargeCount = this._arrRecChargeIdsToBeActioned.length;		
		
		if (intRecChargeCount == 0)
		{
			$Alert('Please select at least one recurring charge');
			return;
		}

		var strPrompt = "hello";
		var strPopupTitle = "hello";
		
		var objPopup = this._objConfirmationPopup;
		
		var elmTitle = document.createElement('span');
		var elmTitleImage = document.createElement('img');
		
		// Calculate all the totals for the summary
		var fltTotalCredits = 0.0;
		var fltTotalDebits = 0.0;
		var intRecChargeTypeCount = 0;
		var objRecChargeTypes = {};
		var intAccountCount = 0;
		var objAccounts = {};
		var i, j, objRecCharge, objRecChargeData;

		for (i=0, j=intRecChargeCount; i<j; i++)
		{
			objRecCharge = this._objRecCharges[this._arrRecChargeIdsToBeActioned[i]];
			
			// Highlight the selected row
			objRecCharge.objRow.domElement.setAttribute('selected', 'selected');

			objRecChargeData = objRecCharge.objRecCharge;
			
			if (objRecChargeTypes[objRecChargeData.chargeType] === undefined)
			{
				// This rec charge type hasn't been encountered yet
				intRecChargeTypeCount++;
				objRecChargeTypes[objRecChargeData.chargeType] = objRecChargeData.chargeType;
			}

			if (objAccounts[objRecChargeData.account] === undefined)
			{
				// This account hasn't been encountered yet
				intAccountCount++;
				objAccounts[objRecChargeData.account] = objRecChargeData.account;
			}
			
			if (objRecChargeData.nature == 'CR')
			{
				// Credit recurring Charge
				fltTotalCredits += objRecChargeData.minChargeIncGst;
			}
			else
			{
				// Debit recurrin Charge
				fltTotalDebits += objRecChargeData.minChargeIncGst;
			}
		}
		
		objPopup.objSummary.objTotalCharges.elmValue.innerHTML = intRecChargeCount;
		objPopup.objSummary.objTotalChargeTypes.elmValue.innerHTML = intRecChargeTypeCount;
		objPopup.objSummary.objTotalAccounts.elmValue.innerHTML = intAccountCount;
		objPopup.objSummary.objTotalCredits.elmValue.innerHTML = '$' + fltTotalCredits.toFixed(2);
		objPopup.objSummary.objTotalDebits.elmValue.innerHTML = '$' + fltTotalDebits.toFixed(2);
		
		
		if (action == Recurring_Charge_Management.APPROVE_CHARGES)
		{
			// User wants to approve some charges
			objPopup.objReasonForDecline.domElement.style.display = 'none';
			objPopup.domApproveRecChargesConfirmButton.style.display = 'inline';
			objPopup.domDeclineRecChargesConfirmButton.style.display = 'none';
			
			strPrompt = "Are you sure you want to approve these recurring charge requests?";
			strPopupTitle = "Approve Recurring Charge Requests";
			
			elmTitleImage.src = 'img/template/approve.png';
			elmTitleImage.alt = 'Approve';
		}
		else if (action == Recurring_Charge_Management.DECLINE_CHARGES)
		{
			// User wants to decline some charges
			objPopup.objReasonForDecline.domElmTextarea.value = '';
			objPopup.objReasonForDecline.domElement.style.display = 'block';
			objPopup.domApproveRecChargesConfirmButton.style.display = 'none';
			objPopup.domDeclineRecChargesConfirmButton.style.display = 'inline';

			strPrompt = "Are you sure you want to reject these recurring charge requests?";
			strPopupTitle = "Reject Recurring Charge Requests";

			elmTitleImage.src = 'img/template/decline.png';
			elmTitleImage.alt = 'Reject';
		}
		else
		{
			$Alert('Unknown action: '+ action);
			return;
		}
		
		elmTitle.appendChild(elmTitleImage);
		elmTitle.appendChild(document.createTextNode(strPopupTitle));
		objPopup.popPopup.setTitleElement(elmTitle);
		objPopup.objPrompt.domElement.innerHTML = strPrompt;
		
		objPopup.popPopup.display();
	},
	
	_approveRecCharges : function()
	{
		if (this._bolIsSubmitting)
		{
			return;
		}
		
		// Prepare details to send to the server
		// (Nothing really to do here)
		
		var jsonFunc = jQuery.json.jsonFunction(this._approveRecChargesResponse.bind(this), null, "Recurring_Charge", "approveRecurringChargeRequests");
		
		this._bolIsSubmitting = true;
		
		jsonFunc(this._arrRecChargeIdsToBeActioned);
		Vixen.Popup.ShowPageLoadingSplash("Processing", null, null, null, 100);
	},

	_approveRecChargesResponse : function(objResponse)
	{
		this._bolIsSubmitting = false;
		Vixen.Popup.ClosePageLoadingSplash();
		
		this._closeConfirmationPopup();
		
		if (objResponse.success && objResponse.success == true)
		{
			var strMessage = "";
			if (objResponse.intSuccessCount == 1)
			{
				strMessage = "The recurring charge request has been approved";
			}
			else
			{
				strMessage = "The recurring charge requests have been approved";
			}
			
			$Alert(strMessage);
			
			// Refresh the page
			this.objPagination.getCurrentPage();
		}
		else
		{
			jQuery.json.errorPopup(objResponse, "Approving the recurring charge requests, failed");
		}
	},
	
	_declineRecCharges : function()
	{
		if (this._bolIsSubmitting)
		{
			return;
		}
		
		// Prepare details to send to the server
		var strReason = this._getReasonForDeclination();
		if (strReason.length == 0)
		{
			// A reason has not been specified
			$Alert("Please specify a reason for the rejection");
			return;
		}
		
		var jsonFunc = jQuery.json.jsonFunction(this._declineRecChargesResponse.bind(this), null, "Recurring_Charge", "rejectRecurringChargeRequests");
		
		this._bolIsSubmitting = true;
		
		jsonFunc(this._arrRecChargeIdsToBeActioned, strReason);
		Vixen.Popup.ShowPageLoadingSplash("Processing", null, null, null, 100);
	},
	
	_declineRecChargesResponse : function(objResponse)
	{
		this._bolIsSubmitting = false;
		Vixen.Popup.ClosePageLoadingSplash();
		
		this._closeConfirmationPopup();
		
		if (objResponse.success && objResponse.success == true)
		{
			var strMessage = "";
			if (objResponse.intSuccessCount == 1)
			{
				strMessage = "The recurring charge request has been rejected";
			}
			else
			{
				strMessage = "The recurring charge requests have been rejected";
			}
			
			$Alert(strMessage);
			
			// Refresh the page
			this.objPagination.getCurrentPage();
		}
		else
		{
			jQuery.json.errorPopup(objResponse, "Rejecting the recurring charge requests, failed");
		}
	},
	
	// This will be used for validation.  If an empty string is returned, then it is clearly invalid
	_getReasonForDeclination : function()
	{
		var strReason = new String(this._objConfirmationPopup.objReasonForDecline.domElmTextarea.value);
		return strReason.replace(/^\s*/, "").replace(/\s*$/, "");
	},
	
	
	selectAll : function()
	{
		if (this._objRecCharges)
		{
			for (var i in this._objRecCharges)
			{
				this._objRecCharges[i].objSelector.domElement.checked = true;
			}
		}
	},
	
	selectNone : function()
	{
		if (this._objRecCharges)
		{
			for (var i in this._objRecCharges)
			{
				this._objRecCharges[i].objSelector.domElement.checked = false;
			}
		}
	},
	
	_closeConfirmationPopup : function()
	{
		// Unselect each recurring charge that is selected
		for (var i in this._objRecCharges)
		{
			this._objRecCharges[i].objRow.domElement.setAttribute('selected', '');
		}
	
		this._objConfirmationPopup.popPopup.hide();
	},
	
});

Recurring_Charge_Management.APPROVE_CHARGES = 'APPROVE_CHARGES';
Recurring_Charge_Management.DECLINE_CHARGES = 'DECLINE_CHARGES';
