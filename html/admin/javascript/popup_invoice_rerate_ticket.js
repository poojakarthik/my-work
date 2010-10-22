
var Popup_Invoice_Rerate_Ticket	= Class.create(Reflex_Popup, 
{
	initialize	: function($super, iOriginalInvoiceId, iRerateInvoiceRunId, iAdjustmentId, fnOnComplete)
	{
		$super(40);
		
		this._iOriginalInvoiceId	= iOriginalInvoiceId;
		this._iRerateInvoiceRunId	= iRerateInvoiceRunId;
		this._iAdjustmentId			= iAdjustmentId;
		this._fnOnComplete			= fnOnComplete;
		
		this._buildUI();
	},
	
	// Private
	_buildUI	: function()
	{
		this._oAdditionalComments =	Control_Field.factory(
										'textarea', 
										{
											sLabel						: 'comments',
											mMandatory					: false,
											bDisableValidationStyling	: true,
											mEditable					: true
										}
									); 
		this._oContent	=	$T.div({class: 'popup-invoice-rerate-ticket'},
								$T.div('Additional Comments (Optional): '),
								this._oAdditionalComments.getElement(),
								$T.div({class: 'buttons'},
									$T.button({class: 'icon-button'},
										'Add Ticket'
									).observe('click', this._createTicket.bind(this))
								)
							);
		
		if (this._iAdjustmentId === null)
		{
			// No adjustment added, the ticket isn't mandatory
			this.addCloseButton();
		}
		
		// Configure popup & display
		this.setTitle('Add Rerate Ticket');
		this.setContent(this._oContent);
		this.display();
	},
	
	_showLoading	: function(sText)
	{
		if (!this._oLoading)
		{
			this._oLoading	= new Reflex_Popup.Loading(sText);
		}
		this._oLoading.display();
	},
	
	_hideLoading	: function()
	{
		if (this._oLoading)
		{
			this._oLoading.hide();
			delete this._oLoading;
		}
	},
	
	_createTicket	: function(oEvent, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			this._showLoading('Creating Ticket...');
			
			var sAdditionalComments	= this._oAdditionalComments.getElementValue();
			
			// Make request to create the ticket
			var fnHandler		= this._createTicket.bind(this, oEvent);
			var fnCreateTicket	= jQuery.json.jsonFunction(fnHandler, fnHandler, 'Invoice', 'createRerateTicket');
			fnCreateTicket(this._iOriginalInvoiceId, this._iRerateInvoiceRunId, this._iAdjustmentId, (sAdditionalComments !== '' ? sAdditionalComments : null));
		}
		else
		{
			this._hideLoading();
			
			if (oResponse.bSuccess)
			{
				// Ticket created
				Reflex_Popup.alert(
					$T.div({class: 'alert-content'},
						$T.span('Ticket '),
						$T.a({href: 'reflex.php/Ticketing/Ticket/' + oResponse.iTicketId + '/View/'},
							oResponse.iTicketId
						),
						$T.span(' created')
					)
				);
				
				// Completion callback
				if (this._fnOnComplete)
				{
					this._fnOnComplete(oResponse.iTicketId);
				}
			}
			else
			{
				// Ticket creation failed
				Reflex_Popup.alert('Ticket creation failed. ' + oResponse.sMessage);
			}
			
			this.hide();
		}
	}
});

