
var Popup_Interim_First_Invoice_Commit_And_Send	= Class.create(Reflex_Popup, 
{
	initialize	: function($super)
	{
		$super(40);
		
		this._aDebugInfo		= [];
		this._bProcessing		= false;
		this._hInvoiceRunStatus	= {};
		
		this._buildUI();
	},
	
	// Private
	
	_buildUI	: function(oResponse)
	{
		// Control field
		var oDateSelect	= new Control_Field_Select();
		oDateSelect.setPopulateFunction(Popup_Interim_First_Invoice_Commit_And_Send._getBillingDates);
		oDateSelect.setEditable(true);
		oDateSelect.setVisible(true);
		oDateSelect.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		oDateSelect.disableValidationStyling();
		oDateSelect.addOnChangeCallback(this._billingDateChanged.bind(this));
		this._oDateSelect	= oDateSelect;
		
		var oBillingSection	= new Section(true);
		oBillingSection.setTitleText('Billing Date');
		oBillingSection.setContent(
			$T.div({class: 'billing-section'},
				$T.p('Specify the date on which the invoices were generated:'),
				$T.p(
					this._oDateSelect.getElement()
				)
			)
		);
		
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
							this._createNoRecordsRow()
						)
					)
				)
			)
		);
		
		var oContent	= 	$T.div({class: 'popup-first-interim-invoice-commit-and-send'},
								oBillingSection.getElement(),
								oCommitSection.getElement(),
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
									).observe('click', this.hide.bind(this)),
									$T.button({class: 'icon-button'},
										'View Log'
									).observe('click', this._viewLog.bind(this))
								)
							);
		
		this._oInvoiceRunsTBody	= oContent.select('tbody').first();
		
		var oButtons	= oContent.select('button.icon-button');
		this._oCommit	= oContent.select('button.icon-button')[0];
		this._oRetry	= oContent.select('button.icon-button')[1];
		this._oClose	= oContent.select('button.icon-button')[2];
		this._oCancel	= oContent.select('button.icon-button')[3];
		this._oLog		= oContent.select('button.icon-button')[4];
		
		this._oRetry.hide();
		this._oClose.hide();
		this._oLog.hide();
		
		// Configure popup
		this.setContent(oContent);
		this.addCloseButton();
		this.setTitle('Commit and Send Interim Invoices');
		this.display();
	},
	
	_createNoRecordsRow	: function()
	{
		return	$T.tr(
					$T.td({class: 'no-rows', colspan: 2},
						'No Billing Date selected'
					)
				);
	},
	
	_invoiceRunsLoaded	: function(oResponse)
	{
		this._oInvoiceRunsTBody.innerHTML	= '';
		
		// Add invoice runs to table
		var oInvoiceRun	= null;
		var oStatusTD	= null;
		for (var iId in oResponse.aInvoiceRuns)
		{
			oInvoiceRun	= oResponse.aInvoiceRuns[iId];
			oStatusTD	= $T.td(Popup_Interim_First_Invoice_Commit_And_Send.STATUS_UNCOMMITED);
			this._oInvoiceRunsTBody.appendChild(
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
		
		this._oLoading.hide();
		delete this._oLoading;
	},
	
	_getInvoiceRuns	: function(sBillingDate)
	{
		var fnGo	=	jQuery.json.jsonFunction(
							this._invoiceRunsLoaded.bind(this), 
							Popup_Interim_First_Invoice_Commit_And_Send._ajaxError, 
							'Invoice_Interim', 
							'getInvoiceRunsForBillingDate'
						);
		this._oLoading	= new Reflex_Popup.Loading('Getting List of Invoice Runs...');
		this._oLoading.display();
		fnGo(sBillingDate);
	},
	
	_billingDateChanged	: function()
	{
		var sBillingDate	= this._oDateSelect.getElementValue();
		if (sBillingDate !== null && sBillingDate !== '')
		{
			this._getInvoiceRuns(this._oDateSelect.getElementValue());
		}
	},
	
	_refreshPage	: function()
	{
		window.location	= window.location;
	},
	
	_doProcess	: function(oEvent)
	{
		if (!this._bProcessing)
		{
			this._aDebug	= [];
			
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
		
		if (this._aDebugInfo.length)
		{
			this._oLog.show();
		}
		else
		{
			this._oLog.hide();
		}
		
		this._bProcessing	= false;
	},
	
	_commit	: function(iId, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			var fnGo	=	jQuery.json.jsonFunction(
								this._commit.bind(this, iId), 
								Popup_Interim_First_Invoice_Commit_And_Send._ajaxError, 
								'Invoice_Run', 
								'commitInvoiceRun'
							);
			
			fnGo(iId);
			
			this._hInvoiceRunStatus[iId].bCommitAttempted			= true;
			this._hInvoiceRunStatus[iId].oStatusElement.innerHTML	= Popup_Interim_First_Invoice_Commit_And_Send.STATUS_PROCESSING;
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
				this._hInvoiceRunStatus[iId].oStatusElement.innerHTML	= Popup_Interim_First_Invoice_Commit_And_Send.STATUS_FAILED_COMMIT;
			}
			
			if (oResponse.sDebug)
			{
				this._aDebugInfo.push(oResponse.sDebug);
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
								Popup_Interim_First_Invoice_Commit_And_Send._ajaxError, 
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
				this._hInvoiceRunStatus[iId].oStatusElement.innerHTML	= Popup_Interim_First_Invoice_Commit_And_Send.STATUS_SUCCESSFUL;
			}
			else
			{
				this._hInvoiceRunStatus[iId].bDelivered	= false;
				this._hInvoiceRunStatus[iId].oStatusElement.innerHTML	= Popup_Interim_First_Invoice_Commit_And_Send.STATUS_FAILED_DELIVERY;
			}
			
			if (oResponse.sDebug)
			{
				this._aDebugInfo.push(oResponse.sDebug);
			}
			
			this._processNext();
		}
	},
	
	_viewLog	: function()
	{
		var oTextArea	=	$T.textarea({class: 'log-text'},
								this._aDebugInfo.join("\n= = = = = = = = = = = = =\n")
							);
		Reflex_Popup.alert(oTextArea, {sTitle: 'Log', iWidth: 61, bOverrideStyles: false});
	}
});

// Static

Object.extend(Popup_Interim_First_Invoice_Commit_And_Send, 
{
	STATUS_UNCOMMITED		: 'Uncommited',
	STATUS_PROCESSING		: 'Processing...',
	STATUS_FAILED_COMMIT	: 'Failed to Commit',
	STATUS_FAILED_DELIVERY	: 'Failed to Deliver',
	STATUS_SUCCESSFUL		: 'Successful',
	
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

	_ajaxError	: function(oResponse)
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
	}
});



