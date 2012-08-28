
var Popup_Interim_First_Invoice_Commit_And_Send	= Class.create(Reflex_Popup, 
{
	initialize	: function($super)
	{
		$super(40);
		
		// Will store debug strings for each request made
		this._aDebugInfo		= [];
		
		// Are we currently trying to commit/deliver an invoice run?
		this._bProcessing		= false;
		
		// Maintains the commit status summary of each invoice run, contains:
		// 	- bCommitAttempted		: have we tried to commit the run yet?
		// 	- bCommitted			: has the run been commited?
		// 	- bDeliverAttempted		: have we tried to deliver the run yet?
		// 	- bDelivered 			: has the run been delivered?
		// 	- oStatusElement		: status display element which the textual status is put in
		this._hInvoiceRunStatus	= {};
		
		// Create the interface
		this._buildUI();
	},
	
	// Private
	
	// _buildUI: 	Creates the billing date selection field & the table to hold the invoice 
	//				runs which were billed on that date. 
	_buildUI	: function()
	{
		// Invoice run billing date control field
		var oDateSelect	= new Control_Field_Select();
		oDateSelect.setPopulateFunction(Popup_Interim_First_Invoice_Commit_And_Send._getBillingDates);
		oDateSelect.setEditable(true);
		oDateSelect.setVisible(true);
		oDateSelect.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		oDateSelect.disableValidationStyling();
		oDateSelect.addOnChangeCallback(this._billingDateChanged.bind(this));
		this._oDateSelect	= oDateSelect;
		
		// Section to contain the billing date select
		var oBillingSection	= new Section(true);
		oBillingSection.setTitleText('Billing Date');
		oBillingSection.setContent(
			$T.div({class: 'billing-section'},
				$T.div('Specify the date on which the invoices were generated:'),
				$T.div(
					this._oDateSelect.getElement()
				)
			)
		);
		
		// Section to contain the commit/deliver statuses for each invoice run
		var oCommitSection	= new Section(true);
		oCommitSection.setTitleText('Invoice Runs');
		oCommitSection.setContent(
			$T.div({class: 'popup-interim-first-invoice-commit'},
				$T.div({class: 'invoice-run-list'},
					$T.table({class: 'reflex'},
						$T.thead(
							$T.th('Customer Group'),
							$T.th('Status')
						),
						$T.tbody(
							$T.tr(
								$T.td({class: 'no-rows', colspan: 2},
									'No Billing Date selected'
								)
							)
						)
					)
				)
			)
		);
		
		// The popup content
		var oContent	= 	$T.div({class: 'popup-first-interim-invoice-commit-and-send'},
								oBillingSection.getElement(),
								oCommitSection.getElement(),
								$T.div({class: 'buttons'},
									$T.button({class: 'icon-button'},
										'Commit & Send'
									).observe('click', this._doProcess.bind(this)),
									$T.button({class: 'icon-button'},
										'Retry'
									).observe('click', this._doProcess.bind(this)),
									$T.button({class: 'icon-button'},
										'Close'
									).observe('click', this.hide.bind(this)),
									$T.button({class: 'icon-button'},
										'Cancel'
									).observe('click', this.hide.bind(this)),
									$T.button({class: 'icon-button'},
										'View Log'
									).observe('click', this._viewLog.bind(this))
								)
							);
		
		// Cache reference, for later use
		this._oInvoiceRunsTBody	= oContent.select('tbody').first();
		
		// Cache reference to all of the buttons that need to be toggled
		var oButtons	= oContent.select('button.icon-button');
		this._oCommit	= oContent.select('button.icon-button')[0];
		this._oRetry	= oContent.select('button.icon-button')[1];
		this._oClose	= oContent.select('button.icon-button')[2];
		this._oCancel	= oContent.select('button.icon-button')[3];
		this._oLog		= oContent.select('button.icon-button')[4];
		
		// Initial button states
		this._oCommit.hide();
		this._oRetry.hide();
		this._oClose.hide();
		this._oLog.hide();
		
		// Configure popup & display
		this.setContent(oContent);
		this.addCloseButton();
		this.setTitle('Commit and Send Interim Invoices');
		this.display();
	},
	
	// _billingDateChanged: Value change handler for the billing date field, invokes the fetching of invoice runs
	_billingDateChanged	: function()
	{
		var sBillingDate	= this._oDateSelect.getElementValue();
		if (sBillingDate !== null && sBillingDate !== '')
		{
			this._aDebugInfo	= [];
			this._oLog.hide();
			this._getInvoiceRuns(this._oDateSelect.getElementValue());
		}
	},
	
	// _getInvoiceRuns:	Gets the invoice runs that were created on the billing date, 
	//					json function to 'Invoice_Interim' json handler. Calls back to _invoiceRunsLoaded
	_getInvoiceRuns	: function(sBillingDate)
	{
		var fnGo	=	jQuery.json.jsonFunction(
							this._invoiceRunsLoaded.bind(this), 
							Popup_Interim_First_Invoice_Commit_And_Send._ajaxError, 
							'Invoice_Interim', 
							'getInvoiceRunsForBillingDate'
						);
		
		// Loading popup
		this._oLoading	= new Reflex_Popup.Loading('Getting List of Invoice Runs...');
		this._oLoading.display();
		
		fnGo(sBillingDate);
	},
	
	// _invoiceRunsLoaded: 	Callback for the _getInvoiceRuns json function, populates the 'Invoice Runs' section
	//						listing each and showing their status, starts at 'Uncommited' (STATUS_UNCOMMITED)
	_invoiceRunsLoaded	: function(oResponse)
	{
		this._oInvoiceRunsTBody.innerHTML	= '';
		
		// Add invoice runs to table
		var oInvoiceRun	= null;
		var oStatusTD	= null;
		for (var iId in oResponse.aInvoiceRuns)
		{
			oInvoiceRun	= oResponse.aInvoiceRuns[iId];
			
			// Add the row to represent the invoice run
			oStatusTD	= $T.td(Popup_Interim_First_Invoice_Commit_And_Send.STATUS_UNCOMMITED);
			this._oInvoiceRunsTBody.appendChild(
				$T.tr(
					$T.td(oInvoiceRun.customer_group_name),
					oStatusTD
				)
			);
			
			// Cache the runs 'status' default summary
			this._hInvoiceRunStatus[iId]	=
			{
				bCommitAttempted	: false,
				bCommitted			: false,
				bDeliverAttempted	: false,
				bDelivered			: false, 
				oStatusElement		: oStatusTD
			};
		}
		
		// Show the commit button
		this._oCommit.show();
		
		// Kill the loading
		this._oLoading.hide();
		delete this._oLoading;
	},
	
	// _doProcess: Kicks off commit/delivery of each invoice run
	_doProcess	: function(oEvent)
	{
		if (!this._bProcessing)
		{
			// Not alreay processing, GO!
			// Reset the debug strings array
			this._aDebugInfo	= [];
			
			// Hide the commit & retry buttons
			this._oCommit.hide();
			this._oRetry.hide();
			
			// Reset the 'attempt' status of each invoice run so that ones that have 
			// failed previously are tried again
			for (var iId in this._hInvoiceRunStatus)
			{
				this._hInvoiceRunStatus[iId].bCommitAttempted	= false;
				this._hInvoiceRunStatus[iId].bDeliverAttempted	= false;
			}
			
			this._bProcessing	= true;
			
			// Process next available task
			this._processNext();
		}
	},
	
	// _processNext: 	Does the next commmit/deliver in the 'queue' (not actually a queue). 
	//					Commits all the invoice runs first then, if all committed the deliver all of the invoice runs.
	//					If not all are commited & delivered, allow retry. 
	_processNext	: function()
	{
		var oInvoiceRun		= null;
		var bAllFinished	= true;
		
		// Commit
		for (var iId in this._hInvoiceRunStatus)
		{
			oInvoiceRun		= this._hInvoiceRunStatus[iId];
			bAllFinished	&= oInvoiceRun.bCommitted;
			
			// Only try and commit if the invoice run: 
			// 	- has NOT been commit successfully and 
			// 	- commit has NOT been attempted
			if (!oInvoiceRun.bCommitted && !oInvoiceRun.bCommitAttempted)
			{
				oInvoiceRun.bCommitAttempted	= true;
				this._commit(iId);
				return;
			}
		}
		
		// Deliver
		for (var iId in this._hInvoiceRunStatus)
		{
			oInvoiceRun	= this._hInvoiceRunStatus[iId];
			bAllFinished	&= oInvoiceRun.bDelivered;
			
			// Only try and deliver if the invoice run: 
			// 	- has been commited and
			// 	- has NOT been delivered successfully and 
			// 	- deliver has NOT been attempted
			if (oInvoiceRun.bCommitted && !oInvoiceRun.bDelivered && !oInvoiceRun.bDeliverAttempted)
			{
				this._deliver(iId);
				return;
			}
		}
		
		if (bAllFinished)
		{
			// All have been commited & delivered, hide all buttons except the close button
			this._oRetry.hide();
			this._oCancel.hide();
			this._oClose.show();
		}
		else
		{
			// Not all finished, a commit/delivery has failed. Show retry & cancel
			this._oRetry.show();
			this._oCancel.show();
		}
		
		if (this._aDebugInfo.length)
		{
			// There is debugging info (should only happen for god users), show the log button 
			this._oLog.show();
		}
		else
		{
			// No debugging info, hide the log button
			this._oLog.hide();
		}
		
		// No longer processing
		this._bProcessing	= false;
	},
	
	// _commit: Attempts to commit an invoice run. Once complete, updates the commit status of the invoice run
	//			and calls _processNext
	_commit	: function(iId, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Make the request to commit the invoice run
			var fnGo	=	jQuery.json.jsonFunction(
								this._commit.bind(this, iId), 
								Popup_Interim_First_Invoice_Commit_And_Send._ajaxError, 
								'Invoice_Run', 
								'commitInvoiceRun'
							);
			fnGo(iId);
			
			// Update the commit attempted status and the status display element
			this._hInvoiceRunStatus[iId].bCommitAttempted			= true;
			this._hInvoiceRunStatus[iId].oStatusElement.innerHTML	= Popup_Interim_First_Invoice_Commit_And_Send.STATUS_PROCESSING;
		}
		else 
		{
			// Got a response
			if (oResponse.bSuccess)
			{
				// Successful, update commited status
				this._hInvoiceRunStatus[iId].bCommitted	= true;
			}
			else
			{
				// Failed, update commited status and the status display element
				this._hInvoiceRunStatus[iId].bCommitted	= false;
				this._hInvoiceRunStatus[iId].oStatusElement.innerHTML	= Popup_Interim_First_Invoice_Commit_And_Send.STATUS_FAILED_COMMIT;
			}
			
			if (oResponse.sDebug)
			{
				// Debug info returned (should only be god users), cache it
				this._aDebugInfo.push(oResponse.sDebug);
			}
			
			// Do next commit/delivery
			this._processNext();
		}
	},
	
	// _commit: Attempts to deliver an invoice run. Once complete, updates the delivery status of the invoice run
	//			and calls _processNext
	_deliver	: function(iId, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Make the request to deliver the invoice run
			var fnGo	=	jQuery.json.jsonFunction(
								this._deliver.bind(this, iId), 
								Popup_Interim_First_Invoice_Commit_And_Send._ajaxError, 
								'Invoice_Run', 
								'deliverInvoiceRun'
							);
			fnGo(iId);
			
			// Update the commit attempted status (the status display element should already be STATUS_PROCESSING)
			this._hInvoiceRunStatus[iId].bDeliverAttempted	= true;
		}
		else 
		{
			// Response
			if (oResponse.bSuccess)
			{
				// Successful, update delivered status & the status display element (STATUS_SUCCESSFUL)
				this._hInvoiceRunStatus[iId].bDelivered	= true;
				this._hInvoiceRunStatus[iId].oStatusElement.innerHTML	= Popup_Interim_First_Invoice_Commit_And_Send.STATUS_SUCCESSFUL;
			}
			else
			{
				// Failed, update delivered status & the status display element (STATUS_FAILED_DELIVERY)
				this._hInvoiceRunStatus[iId].bDelivered	= false;
				this._hInvoiceRunStatus[iId].oStatusElement.innerHTML	= Popup_Interim_First_Invoice_Commit_And_Send.STATUS_FAILED_DELIVERY;
			}
			
			if (oResponse.sDebug)
			{
				// Debug info returned (should only be god users), cache it
				this._aDebugInfo.push(oResponse.sDebug);
			}
			
			// Do next delivery
			this._processNext();
		}
	},
	
	// _viewLog: Shows the cached debug log information in a popup (a debug popup which contains a text area)
	_viewLog	: function()
	{
		Reflex_Popup.debug(this._aDebugInfo.join("\n= = = = = = = = = = = = =\n"));
	}
});

// Static

Object.extend(Popup_Interim_First_Invoice_Commit_And_Send, 
{
	// Invoice Run Commit/Deliver Statuses, used for display
	STATUS_UNCOMMITED		: 'Uncommited',
	STATUS_PROCESSING		: 'Processing...',
	STATUS_FAILED_COMMIT	: 'Failed to Commit',
	STATUS_FAILED_DELIVERY	: 'Failed to Deliver',
	STATUS_SUCCESSFUL		: 'Successful',
	
	// _getBillingDates: Gets the billing dates of non-commited INTERIM_FIRST invoice runs
	_getBillingDates	: function(fnCallback, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Make request to get the billing dates
			var fnGetBillingDates	=	jQuery.json.jsonFunction(
											Popup_Interim_First_Invoice_Commit_And_Send._getBillingDates.curry(fnCallback), 
											Popup_Interim_First_Invoice_Commit_And_Send._getBillingDates.curry(fnCallback), 
											'Invoice_Interim', 
											'getTemporaryFirstInterimInvoiceBillingDates'
										);
			fnGetBillingDates();
		}
		else
		{
			// Create the options
			var aOptions	= [];
			if (oResponse.bSuccess)
			{
				var aDates	= $A(oResponse.aDates);
				for (var i = 0; i < aDates.length; i++)
				{
					aOptions.push(
						$T.option({value: aDates[i]}, 
							Date.$parseDate(aDates[i], 'Y-m-d').$format('jS F Y')
						)
					);
				}
			}
			fnCallback(aOptions);
		}
	},

	// _ajaxError: 	Called in the event that something went wrong with an ajax request, shows an error message
	//				if returned in the response, failing that, a generic message is shown.
	_ajaxError	: function(oResponse) {
		// Hide loading
		if (this._oLoading) {
			this._oLoading.hide();
			delete this._oLoading;
		}
		
		// Show message
		jQuery.json.errorPopup(oResponse);
	}
});



