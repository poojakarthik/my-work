var Charge_Management = Class.create
({
	initialize	: function(elmContainerDiv, intMaxRecordsPerPage, iChargeModel)
	{
		var intCacheMode = Dataset_Ajax.CACHE_MODE_NO_CACHING;
		
		// Get the JSON Handler method using the iChargeModel
		var sMethod			= '';
		var sChargeModel	= '';
		
		switch(iChargeModel)
		{
			case $CONSTANT.CHARGE_MODEL_CHARGE:
				sMethod			= 'getChargesAwaitingApproval';
				sChargeModel	= 'Charge';
				break;
			case $CONSTANT.CHARGE_MODEL_ADJUSTMENT:
				sMethod			= 'getAdjustmentsAwaitingApproval';
				sChargeModel	= 'Adjustment';
				break;
			default:
				sMethod			= 'getAllAwaitingApproval';
				sChargeModel	= 'Charge';
		}
		
		this._iChargeModel	= iChargeModel;
		this._sChargeModel	= sChargeModel;
		
		// Init Dataset & Pagination
		this.intMaxRecordsPerPage = intMaxRecordsPerPage;
		this.objDataset		= 	new Dataset_Ajax(
									intCacheMode, 
									{sObject: 'Charge', sMethod: sMethod}
								);
		this.objPagination	= 	new Pagination(
									this._updateTable.bind(this), 
									this.intMaxRecordsPerPage, 
									this.objDataset
								);
		this._objCharges	= {};

		// This will store the list of charges (just their ids) to be either approved or declined, in a single submit to the server
		this._arrChargeIdsToBeActioned = new Array();
		
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

		// Charge Description column
		objPage.objTable.objTHEAD.objChargeDescription = {};
		objPage.objTable.objTHEAD.objChargeDescription.domElement = document.createElement('th');
		objPage.objTable.objTHEAD.objChargeDescription.domElement.appendChild(document.createTextNode(this._sChargeModel + ' Type'));
		objPage.objTable.objTHEAD.objColumnTitlesTR.domElement.appendChild(objPage.objTable.objTHEAD.objChargeDescription.domElement);

		// Charge Amount column
		objPage.objTable.objTHEAD.objChargeAmount = {};
		objPage.objTable.objTHEAD.objChargeAmount.domElement = document.createElement('th');
		objPage.objTable.objTHEAD.objChargeAmount.domElement.innerHTML = 'Amount ($&nbsp;inc&nbsp;GST)';
		objPage.objTable.objTHEAD.objChargeAmount.domElement.style.textAlign = 'right';
		objPage.objTable.objTHEAD.objColumnTitlesTR.domElement.appendChild(objPage.objTable.objTHEAD.objChargeAmount.domElement);

		// Charge Nature column
		objPage.objTable.objTHEAD.objChargeNature = {};
		objPage.objTable.objTHEAD.objChargeNature.domElement = document.createElement('th');
		objPage.objTable.objTHEAD.objChargeNature.domElement.appendChild(document.createTextNode('Nature'));
		objPage.objTable.objTHEAD.objColumnTitlesTR.domElement.appendChild(objPage.objTable.objTHEAD.objChargeNature.domElement);

		// Account Id column
		objPage.objTable.objTHEAD.objAccountId = {};
		objPage.objTable.objTHEAD.objAccountId.domElement = document.createElement('th');
		objPage.objTable.objTHEAD.objAccountId.domElement.appendChild(document.createTextNode('Account Id'));
		objPage.objTable.objTHEAD.objColumnTitlesTR.domElement.appendChild(objPage.objTable.objTHEAD.objAccountId.domElement);

		// Account Name column
		objPage.objTable.objTHEAD.objAccountName = {};
		objPage.objTable.objTHEAD.objAccountName.domElement = document.createElement('th');
		objPage.objTable.objTHEAD.objAccountName.domElement.appendChild(document.createTextNode('Name'));
		objPage.objTable.objTHEAD.objColumnTitlesTR.domElement.appendChild(objPage.objTable.objTHEAD.objAccountName.domElement);

		// Service FNN column
		objPage.objTable.objTHEAD.objServiceFNN = {};
		objPage.objTable.objTHEAD.objServiceFNN.domElement = document.createElement('th');
		objPage.objTable.objTHEAD.objServiceFNN.domElement.appendChild(document.createTextNode('Service'));
		objPage.objTable.objTHEAD.objColumnTitlesTR.domElement.appendChild(objPage.objTable.objTHEAD.objServiceFNN.domElement);

		// RequestedBy column
		objPage.objTable.objTHEAD.objRequestedBy = {};
		objPage.objTable.objTHEAD.objRequestedBy.domElement = document.createElement('th');
		objPage.objTable.objTHEAD.objRequestedBy.domElement.appendChild(document.createTextNode('Requested By'));
		objPage.objTable.objTHEAD.objColumnTitlesTR.domElement.appendChild(objPage.objTable.objTHEAD.objRequestedBy.domElement);

		// RequestedOn column
		objPage.objTable.objTHEAD.objRequestedOn = {};
		objPage.objTable.objTHEAD.objRequestedOn.domElement = document.createElement('th');
		objPage.objTable.objTHEAD.objRequestedOn.domElement.appendChild(document.createTextNode('Requested On'));
		objPage.objTable.objTHEAD.objColumnTitlesTR.domElement.appendChild(objPage.objTable.objTHEAD.objRequestedOn.domElement);

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
		
		// Charge Approval Confirmation buttons
		objPopup.domApproveChargesConfirmButton = document.createElement('button');
		objPopup.domApproveChargesConfirmButton.innerHTML = 'Yes';
		Event.startObserving(objPopup.domApproveChargesConfirmButton, 'click', this._approveCharges.bind(this), false);
		
		// Charge Decline Confirmation buttons
		objPopup.domDeclineChargesConfirmButton = document.createElement('button');
		objPopup.domDeclineChargesConfirmButton.innerHTML = 'Yes';
		Event.startObserving(objPopup.domDeclineChargesConfirmButton, 'click', this._declineCharges.bind(this), false);
		
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
		objPopup.objSummary.objTotalCharges.elmTitle.appendChild(document.createTextNode('Total ' + this._sChargeModel + 's :'));
		objPopup.objSummary.objTotalCharges.elmValue = document.createElement('td');
		objPopup.objSummary.objTotalCharges.elmRow.appendChild(objPopup.objSummary.objTotalCharges.elmTitle);
		objPopup.objSummary.objTotalCharges.elmRow.appendChild(objPopup.objSummary.objTotalCharges.elmValue);

		// Summary - TotalChargeTypes
		objPopup.objSummary.objTotalChargeTypes = {};
		objPopup.objSummary.objTotalChargeTypes.elmRow = document.createElement('tr');
		objPopup.objSummary.objTotalChargeTypes.elmTitle = document.createElement('td');
		objPopup.objSummary.objTotalChargeTypes.elmTitle.className = 'title';
		objPopup.objSummary.objTotalChargeTypes.elmTitle.appendChild(document.createTextNode('Total ' + this._sChargeModel + ' Types :'));
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
		objPopup.objSummary.objTotalCredits.elmTitle.appendChild(document.createTextNode('Total Credits :'));
		objPopup.objSummary.objTotalCredits.elmValue = document.createElement('td');
		objPopup.objSummary.objTotalCredits.elmRow.appendChild(objPopup.objSummary.objTotalCredits.elmTitle);
		objPopup.objSummary.objTotalCredits.elmRow.appendChild(objPopup.objSummary.objTotalCredits.elmValue);

		// Summary - TotalDebits
		objPopup.objSummary.objTotalDebits = {};
		objPopup.objSummary.objTotalDebits.elmRow = document.createElement('tr');
		objPopup.objSummary.objTotalDebits.elmTitle = document.createElement('td');
		objPopup.objSummary.objTotalDebits.elmTitle.className = 'title';
		objPopup.objSummary.objTotalDebits.elmTitle.appendChild(document.createTextNode('Total Debits :'));
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
		objPopup.popPopup.setFooterButtons([objPopup.domDeclineChargesConfirmButton, objPopup.domApproveChargesConfirmButton, objPopup.domCancelButton], true);
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
		this._removeChargeEventListeners();
		
		while (this._objPage.objTable.objTBODY.domElement.firstChild)
		{
			this._objPage.objTable.objTBODY.domElement.removeChild(this._objPage.objTable.objTBODY.domElement.firstChild);
		}

		// Keep a temporary reference to the old page of charges, so you can check if any of them are still selected
		var objOldCharges = this._objCharges;
		this._objCharges = {};

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
			objTD.style.textAlign	= 'left';
			objTR.appendChild(objTD);
			
			this._objPage.objTable.objTBODY.domElement.appendChild(objTR);
		}
		else
		{
			var intCurrentPage = objResultSet.intCurrentPage + 1;
			this._objPage.objTable.objCaption.objCaptionBar.objTitle.domElement.innerHTML = 'Page '+ intCurrentPage +' of ' + objResultSet.intPageCount;
			
			var elmTR, elmTD, strChargeDescription, elmAnchor, intId, elmImage;
			
			var bolAlternateRow = true;
			
			for (var i in objResultSet.arrResultSet)
			{
				// Store relevent details/elements regarding the charge
				intId = objResultSet.arrResultSet[i].id;
				this._objCharges[intId] = {};
				this._objCharges[intId].objCharge = {};
				this._objCharges[intId].objCharge = objResultSet.arrResultSet[i];

				elmTR	= document.createElement('tr');
				
				bolAlternateRow = !bolAlternateRow;
				elmTR.className = 'selectable';
				if (bolAlternateRow)
				{
					elmTR.className += ' alt';
				}
				
				// The selector
				this._objCharges[intId].objSelector = {};
				this._objCharges[intId].objSelector.domElement = document.createElement('input');
				this._objCharges[intId].objSelector.domElement.type = 'checkbox';

				// If the charge was previously in the list, then retain the selector value
				if (objOldCharges[intId] != undefined)
				{
					this._objCharges[intId].objSelector.domElement.checked = objOldCharges[intId].objSelector.domElement.checked;
				}

				elmTD = document.createElement('td');
				elmTD.appendChild(this._objCharges[intId].objSelector.domElement);
				elmTR.appendChild(elmTD);
				
				// The Charge Description
				strChargeDescription = objResultSet.arrResultSet[i].description +" ("+ objResultSet.arrResultSet[i].chargeType +")";
				elmTD = document.createElement('td');
				elmTD.appendChild(document.createTextNode(strChargeDescription));
				if (objResultSet.arrResultSet[i].notes != "")
				{
					// The charge has a comment attached to it
					this._objCharges[intId].objCommentLink = {};
					this._objCharges[intId].objCommentLink.domElement = document.createElement('a');
					this._objCharges[intId].objCommentLink.domElement.appendChild(document.createTextNode('(Notes)'));
					elmTD.appendChild(document.createTextNode(' '));
					elmTD.appendChild(this._objCharges[intId].objCommentLink.domElement);
				}
				
				elmTR.appendChild(elmTD);

				// The Charge Amount (inc GST formatted to 2 dec places)
				elmTD = document.createElement('td');
				elmTD.style.textAlign = 'right';
				elmTD.appendChild(document.createTextNode(objResultSet.arrResultSet[i].amountIncGstFormatted));
				elmTR.appendChild(elmTD);
				
				// Nature
				elmTD = document.createElement('td');
				elmTD.appendChild(document.createTextNode(objResultSet.arrResultSet[i].natureFormatted));
				elmTR.appendChild(elmTD);
				
				// Account Id
				elmAnchor = document.createElement('a');
				elmAnchor.href = objResultSet.arrResultSet[i].accountViewHref;
				elmAnchor.title = "View Account";
				elmAnchor.appendChild(document.createTextNode(objResultSet.arrResultSet[i].account));
				elmTD = document.createElement('td');
				elmTD.appendChild(elmAnchor);
				elmTR.appendChild(elmTD);
				
				// Account Name
				elmTD = document.createElement('td');
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
				
				// RequestedBy
				elmTD = document.createElement('td');
				elmTD.appendChild(document.createTextNode(objResultSet.arrResultSet[i].createdByEmployeeName));
				elmTR.appendChild(elmTD);
				
				// RequestedOn
				elmTD = document.createElement('td');
				elmTD.appendChild(document.createTextNode(objResultSet.arrResultSet[i].createdOnFormated));
				elmTR.appendChild(elmTD);
				
				// Actions (store references to these elements in the _objChares object)
				elmImage = document.createElement('img');
				elmImage.src = 'img/template/approve.png';
				elmImage.alt = 'Approve';
				elmAnchor = document.createElement('a');
				elmAnchor.appendChild(elmImage);
				
				this._objCharges[intId].objApprove = {};
				this._objCharges[intId].objApprove.domElement = elmAnchor;
				
				elmImage = document.createElement('img');
				elmImage.src = 'img/template/decline.png';
				elmImage.alt = 'Reject';
				elmAnchor = document.createElement('a');
				elmAnchor.appendChild(elmImage);
				
				this._objCharges[intId].objDecline = {};
				this._objCharges[intId].objDecline.domElement = elmAnchor;
				
				elmTD = document.createElement('td');
				elmTD.setAttribute('nowrap', 'nowrap');
				elmTD.style.textAlign = 'right';
				
				elmTD.appendChild(this._objCharges[intId].objApprove.domElement);
				elmTD.appendChild(this._objCharges[intId].objDecline.domElement);

				elmTR.appendChild(elmTD);
				
				this._objPage.objTable.objTBODY.domElement.appendChild(elmTR);

				this._objCharges[intId].objRow = {};
				this._objCharges[intId].objRow.domElement = elmTR;
			}
		}

		// Register all event listeners
		this._createChargeEventListeners();
		
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
	
	_removeChargeEventListeners : function()
	{
		for (var i in this._objCharges)
		{
			Event.stopObserving(this._objCharges[i].objApprove.domElement, 'click', this._objCharges[i].objApprove.fncOnclick, false);
			Event.stopObserving(this._objCharges[i].objDecline.domElement, 'click', this._objCharges[i].objDecline.fncOnclick, false);
			if (this._objCharges[i].objCommentLink != undefined)
			{
				Event.stopObserving(this._objCharges[i].objCommentLink.domElement, 'click', this._objCharges[i].objCommentLink.fncOnclick, false);
			}
		}
	},
	
	_createChargeEventListeners : function()
	{
		for (var i in this._objCharges)
		{
			this._objCharges[i].objApprove.fncOnclick = this.approveCharge.bind(this, i);
			this._objCharges[i].objDecline.fncOnclick = this.declineCharge.bind(this, i);
			if (this._objCharges[i].objCommentLink != undefined)
			{
				this._objCharges[i].objCommentLink.fncOnclick = this.showChargeNote.bind(this, i);
				Event.startObserving(this._objCharges[i].objCommentLink.domElement, 'click', this._objCharges[i].objCommentLink.fncOnclick, false);
			}

			Event.startObserving(this._objCharges[i].objApprove.domElement, 'click', this._objCharges[i].objApprove.fncOnclick, false);
			Event.startObserving(this._objCharges[i].objDecline.domElement, 'click', this._objCharges[i].objDecline.fncOnclick, false);
		}
	},
	
	showChargeNote : function(intChargeId)
	{
		$Alert(this._objCharges[intChargeId].objCharge.notes, null, null, 'modal');
	},
	
	approveCharge : function(intChargeId)
	{
		// Update the list of charges to action
		this._arrChargeIdsToBeActioned = new Array();
		this._arrChargeIdsToBeActioned.push(intChargeId);
		
		this._promptUser(Charge_Management.APPROVE_CHARGES);
	},
	
	declineCharge : function(intChargeId)
	{
		// Update the list of charges to action
		this._arrChargeIdsToBeActioned = new Array();
		this._arrChargeIdsToBeActioned.push(intChargeId);
		
		this._promptUser(Charge_Management.DECLINE_CHARGES);
	},
	
	approveSelected : function()
	{
		// Update the list of charges to action
		this._arrChargeIdsToBeActioned = new Array();
		
		for (var i in this._objCharges)
		{
			if (this._objCharges[i].objSelector.domElement.checked)
			{
				this._arrChargeIdsToBeActioned.push(this._objCharges[i].objCharge.id);
			}
		}
		
		this._promptUser(Charge_Management.APPROVE_CHARGES);
	},
	
	declineSelected : function()
	{
		// Update the list of charges to action
		this._arrChargeIdsToBeActioned = new Array();
		
		for (var i in this._objCharges)
		{
			if (this._objCharges[i].objSelector.domElement.checked)
			{
				this._arrChargeIdsToBeActioned.push(this._objCharges[i].objCharge.id);
			}
		}
		
		this._promptUser(Charge_Management.DECLINE_CHARGES);
	},
	
	_promptUser : function(action)
	{
		if (this._bolIsSubmitting)
		{
			$Alert('Another submittion is pending with the server, please wait for it to finish');
			return;
		}

		var intChargeCount = this._arrChargeIdsToBeActioned.length;
		
		if (intChargeCount == 0)
		{
			$Alert('Please select at least one ' + this._sChargeModel);
			return;
		}


		var strPrompt = "hello";
		var strPopupTitle = "hello";
		
		var objPopup = this._objConfirmationPopup;

		// Calculate all the totals for the summary
		var fltTotalCredits = 0.0;
		var fltTotalDebits = 0.0;
		var intChargeTypeCount = 0;
		var objChargeTypes = {};
		var intAccountCount = 0;
		var objAccounts = {};
		var i, j, objCharge, objChargeData;

		for (i=0, j=intChargeCount; i<j; i++)
		{
			objCharge = this._objCharges[this._arrChargeIdsToBeActioned[i]];
			
			// Highlight the selected row
			objCharge.objRow.domElement.setAttribute('selected', 'selected');

			objChargeData = objCharge.objCharge;
			
			if (objChargeTypes[objChargeData.chargeType] === undefined)
			{
				// This charge type hasn't been encountered yet
				intChargeTypeCount++;
				objChargeTypes[objChargeData.chargeType] = objChargeData.chargeType;
			}

			if (objAccounts[objChargeData.account] === undefined)
			{
				// This account hasn't been encountered yet
				intAccountCount++;
				objAccounts[objChargeData.account] = objChargeData.account;
			}
			
			if (objChargeData.nature == 'CR')
			{
				// Credit Charge
				fltTotalCredits += objChargeData.amountIncGst;
			}
			else
			{
				// Debit Charge
				fltTotalDebits += objChargeData.amountIncGst;
			}
		}
		
		objPopup.objSummary.objTotalCharges.elmValue.innerHTML = intChargeCount;
		objPopup.objSummary.objTotalChargeTypes.elmValue.innerHTML = intChargeTypeCount;
		objPopup.objSummary.objTotalAccounts.elmValue.innerHTML = intAccountCount;
		objPopup.objSummary.objTotalCredits.elmValue.innerHTML = '$' + fltTotalCredits.toFixed(2);
		objPopup.objSummary.objTotalDebits.elmValue.innerHTML = '$' + fltTotalDebits.toFixed(2);
		
		var sIconSrc	= '';
		if (action == Charge_Management.APPROVE_CHARGES)
		{
			// User wants to approve some charges
			objPopup.objReasonForDecline.domElement.style.display = 'none';
			objPopup.domApproveChargesConfirmButton.style.display = 'inline';
			objPopup.domDeclineChargesConfirmButton.style.display = 'none';
			
			strPrompt 		= "Are you sure you want to approve these " + this._sChargeModel + " requests?";
			strPopupTitle 	= "Approve " + this._sChargeModel + " Requests";
			sIconSrc		= 'img/template/approve.png';
		}
		else if (action == Charge_Management.DECLINE_CHARGES)
		{
			// User wants to decline some charges
			objPopup.objReasonForDecline.domElmTextarea.value = '';
			objPopup.objReasonForDecline.domElement.style.display = 'block';
			objPopup.domApproveChargesConfirmButton.style.display = 'none';
			objPopup.domDeclineChargesConfirmButton.style.display = 'inline';

			strPrompt 		= "Are you sure you want to reject these " + this._sChargeModel + " requests?";
			strPopupTitle	= "Reject " + this._sChargeModel + " Requests";
			sIconSrc		= 'img/template/decline.png';
		}
		else
		{
			$Alert('Unknown action: '+ action);
			return;
		}
		
		objPopup.popPopup.setTitle(strPopupTitle);
		objPopup.popPopup.setIcon(sIconSrc);
		objPopup.objPrompt.domElement.innerHTML = strPrompt;
		
		objPopup.popPopup.display();
	},
	
	_approveCharges : function()
	{
		if (this._bolIsSubmitting)
		{
			return;
		}
		
		// Prepare details to send to the server
		// (Nothing really to do here)
		
		var jsonFunc = jQuery.json.jsonFunction(this._approveChargesResponse.bind(this), null, "Charge", "approveChargeRequests");
		
		this._bolIsSubmitting = true;
		
		jsonFunc(this._arrChargeIdsToBeActioned, this._iChargeModel);
		Vixen.Popup.ShowPageLoadingSplash("Processing", null, null, null, 100);
	},

	_approveChargesResponse : function(objResponse)
	{
		this._bolIsSubmitting = false;
		Vixen.Popup.ClosePageLoadingSplash();
		
		this._closeConfirmationPopup();
		
		if (objResponse.success && objResponse.success == true)
		{
			var strMessage = "";
			if (objResponse.intSuccessCount == 1)
			{
				strMessage = "The " + this._sChargeModel + " request has been approved";
			}
			else
			{
				strMessage = "The " + this._sChargeModel + " requests have been approved";
			}
			
			$Alert(strMessage);
			
			// Refresh the page
			this.objPagination.getCurrentPage();
		}
		else
		{
			$Alert("Approving the " + this._sChargeModel + " requests, failed" + ((objResponse.errorMessage != undefined)? "<br />" + objResponse.errorMessage : ""), null, null, 'modal');
		}
	},
	
	_declineCharges : function()
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
		
		var jsonFunc = jQuery.json.jsonFunction(this._declineChargesResponse.bind(this), null, "Charge", "rejectChargeRequests");
		
		this._bolIsSubmitting = true;
		
		jsonFunc(this._arrChargeIdsToBeActioned, strReason, this._iChargeModel);
		Vixen.Popup.ShowPageLoadingSplash("Processing", null, null, null, 100);
	},
	
	_declineChargesResponse : function(objResponse)
	{
		this._bolIsSubmitting = false;
		Vixen.Popup.ClosePageLoadingSplash();
		
		this._closeConfirmationPopup();
		
		if (objResponse.success && objResponse.success == true)
		{
			var strMessage = "";
			if (objResponse.intSuccessCount == 1)
			{
				strMessage = "The " + this._sChargeModel + " request has been rejected";
			}
			else
			{
				strMessage = "The " + this._sChargeModel + " requests have been rejected";
			}
			
			$Alert(strMessage);
			
			// Refresh the page
			this.objPagination.getCurrentPage();
		}
		else
		{
			$Alert("Rejecting the " + this._sChargeModel + " requests, failed" + ((objResponse.errorMessage != undefined)? "<br />" + objResponse.errorMessage : ""), null, null, 'modal');
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
		if (this._objCharges)
		{
			for (var i in this._objCharges)
			{
				this._objCharges[i].objSelector.domElement.checked = true;
			}
		}
	},
	
	selectNone : function()
	{
		if (this._objCharges)
		{
			for (var i in this._objCharges)
			{
				this._objCharges[i].objSelector.domElement.checked = false;
			}
		}
	},
	
	_closeConfirmationPopup : function()
	{
		// Unselect each charge that is selected
		for (var i in this._objCharges)
		{
			this._objCharges[i].objRow.domElement.setAttribute('selected', '');
		}
		
		this._objConfirmationPopup.popPopup.hide();
	},
	
});

Charge_Management.APPROVE_CHARGES = 'APPROVE_CHARGES';
Charge_Management.DECLINE_CHARGES = 'DECLINE_CHARGES';
