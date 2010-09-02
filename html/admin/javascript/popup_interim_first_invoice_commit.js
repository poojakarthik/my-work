
var Popup_Interim_First_Invoice_Commit	= Class.create(Reflex_Popup, 
{
	initialize	: function($super, sBillingDate)
	{
		$super(26);
		
		this._bProcessing		= false;
		this._hInvoiceRunStatus	= {};
		this._getInvoiceRuns(sBillingDate);
	},
	
	// Private
	
	_buildUI	: function(oResponse)
	{
		if (!oResponse.bSuccess)
		{
			Popup_Interim_First_Invoice_Commit_All._ajaxError(oResponse);
			return;
		}
		
		// Hide loading
		this._oLoading.hide();
		delete this._oLoading;
		
		// Popup content
		var oContent	= 	$T.div({class: 'popup-interim-first-invoice-commit'},
								$T.div({class: 'invoice-run-list'},
									$T.table({class: 'reflex'},
										$T.thead(
											$T.th('Customer Group'),
											$T.th('Status')
										),
										$T.tbody()
									)
								),
								$T.div({class: 'buttons'},
									$T.button({class: 'icon-button'},
										'Commit'
									).observe('click', this._doProcess.bind(this)),
									$T.button({class: 'icon-button'},
										'Retry'
									).observe('click', this._doProcess.bind(this)),
									$T.button({class: 'icon-button'},
										'Close'
									).observe('click', this.hide.bind(this)),
									$T.button({class: 'icon-button'},
										'Cancel'
									).observe('click', this.hide.bind(this))
								)
							);
		
		this._oTBody	= oContent.select('tbody').first();
		
		var oButtons	= oContent.select('button.icon-button');
		this._oCommit	= oContent.select('button.icon-button')[0];
		this._oRetry	= oContent.select('button.icon-button')[1];
		this._oClose	= oContent.select('button.icon-button')[2];
		this._oCancel	= oContent.select('button.icon-button')[3];
		
		this._oRetry.hide();
		this._oClose.hide();
		
		// Add invoice runs to table
		var oInvoiceRun	= null;
		var oStatusTD	= null;
		for (var iId in oResponse.aInvoiceRuns)
		{
			oInvoiceRun	= oResponse.aInvoiceRuns[iId];
			oStatusTD	= $T.td(Popup_Interim_First_Invoice_Commit_All.STATUS_UNCOMMITED);
			this._oTBody.appendChild(
				$T.tr(
					$T.td(oInvoiceRun.customer_group_name),
					oStatusTD
				)
			);
			
			this._hInvoiceRunStatus[iId]	=
				{
					bCommitAttempted	: false,
					bCommitted			: false,
					bDeliverAttempted	: false,
					bDelivered			: false, 
					oStatusElement		: oStatusTD
				};
		}
		
		// Configure popup
		this.setContent(oContent);
		this.addCloseButton();
		this.setTitle('Commit and Send Interim Invoices');
		this.display();
	},
	
	_getInvoiceRuns	: function(sBillingDate)
	{
		var fnGo	=	jQuery.json.jsonFunction(
							this._buildUI.bind(this), 
							Popup_Interim_First_Invoice_Commit_All._ajaxError, 
							'Invoice_Interim', 
							'getInvoiceRunsForBillingDate'
						);
		this._oLoading	= new Reflex_Popup.Loading('Getting List of Invoice Runs...');
		this._oLoading.display();
		fnGo(sBillingDate);
	},
	
	_doProcess	: function(oEvent)
	{
		if (!this._bProcessing)
		{
			this._oCommit.hide();
			this._oRetry.hide();
			
			for (var iId in this._hInvoiceRunStatus)
			{
				this._hInvoiceRunStatus[iId].bCommitAttempted	= false;
				this._hInvoiceRunStatus[iId].bDeliverAttempted	= false;
			}
			
			this._bProcessing	= true;
			this._processNext();
		}
	},
	
	_processNext	: function()
	{
		var oInvoiceRun		= null;
		var bAllFinished	= true;
		
		// Commit
		for (var iId in this._hInvoiceRunStatus)
		{
			oInvoiceRun		= this._hInvoiceRunStatus[iId];
			bAllFinished	&= oInvoiceRun.bCommitted;
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
			if (oInvoiceRun.bCommitted && !oInvoiceRun.bDelivered && !oInvoiceRun.bDeliverAttempted)
			{
				this._deliver(iId);
				return;
			}
		}
		
		if (bAllFinished)
		{
			this._oRetry.hide();
			this._oCancel.hide();
			this._oClose.show();
		}
		else
		{
			this._oRetry.show();
			this._oCancel.show();
		}
		
		this._bProcessing	= false;
	},
	
	_commit	: function(iId, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			var fnGo	=	jQuery.json.jsonFunction(
								this._commit.bind(this, iId), 
								Popup_Interim_First_Invoice_Commit_All._ajaxError, 
								'Invoice_Run', 
								'commitInvoiceRun'
							);
			
			fnGo(iId);
			
			this._hInvoiceRunStatus[iId].bCommitAttempted			= true;
			this._hInvoiceRunStatus[iId].oStatusElement.innerHTML	= Popup_Interim_First_Invoice_Commit_All.STATUS_PROCESSING;
		}
		else 
		{
			if (oResponse.bSuccess)
			{
				this._hInvoiceRunStatus[iId].bCommitted	= true;
			}
			else
			{
				this._hInvoiceRunStatus[iId].bCommitted	= false;
				this._hInvoiceRunStatus[iId].oStatusElement.innerHTML	= Popup_Interim_First_Invoice_Commit_All.STATUS_FAILED_COMMIT;
			}
			
			this._processNext();
		}
	},
	
	_deliver	: function(iId, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			var fnGo	=	jQuery.json.jsonFunction(
								this._deliver.bind(this, iId), 
								Popup_Interim_First_Invoice_Commit_All._ajaxError, 
								'Invoice_Run', 
								'deliverInvoiceRun'
							);
			fnGo(iId);
			
			this._hInvoiceRunStatus[iId].bDeliverAttempted	= true;
		}
		else 
		{
			if (oResponse.bSuccess)
			{
				this._hInvoiceRunStatus[iId].bDelivered	= true;
				this._hInvoiceRunStatus[iId].oStatusElement.innerHTML	= Popup_Interim_First_Invoice_Commit_All.STATUS_SUCCESSFUL;
			}
			else
			{
				this._hInvoiceRunStatus[iId].bDelivered	= false;
				this._hInvoiceRunStatus[iId].oStatusElement.innerHTML	= Popup_Interim_First_Invoice_Commit_All.STATUS_FAILED_DELIVERY;
			}
			
			this._processNext();
		}
	}
});

// Static

Popup_Interim_First_Invoice_Commit_All.STATUS_UNCOMMITED		= 'Uncommited';
Popup_Interim_First_Invoice_Commit_All.STATUS_PROCESSING		= 'Processing...';
Popup_Interim_First_Invoice_Commit_All.STATUS_FAILED_COMMIT		= 'Failed to Commit';
Popup_Interim_First_Invoice_Commit_All.STATUS_FAILED_DELIVERY	= 'Failed to Deliver';
Popup_Interim_First_Invoice_Commit_All.STATUS_SUCCESSFUL		= 'Successful';

Popup_Interim_First_Invoice_Commit_All._ajaxError	= function(oResponse)
{
	// Hide loading
	if (this._oLoading)
	{
		this._oLoading.hide();
		delete this._oLoading;
	}
	
	// Show message
	var sMessage	= null;
	if (oResponse.sMessage)
	{
		sMessage	= oResponse.sMessage;
	}
	else if (oResponse.ERROR)
	{
		sMessage	= oResponse.ERROR;
	}
	else
	{
		sMessage	= 'An error occured accessing the database. Please contact YBS for assistance.';
	}
	
	Reflex_Popup.alert(sMessage);
};
