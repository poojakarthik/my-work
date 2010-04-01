
var Popup_Invoice_View	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iInvoiceId)
	{
		$super(70);
		this.iInvoiceId	= iInvoiceId;
		this.oInvoice	= null;
		this._buildUI();
	},
	
	_buildUI	: function(oResponse)
	{
		if (typeof oResponse === 'undefined')
		{
			// Show loading
			this.oLoadingPopup	= new Reflex_Popup.Loading('Getting Invoice details...');
			this.oLoadingPopup.display();
			
			// AJAX request to get invoice details
			this._getDataReportForId	= jQuery.json.jsonFunction(this._buildUI.bind(this), this._buildUI.bind(this), 'Invoice_View', 'getForId');
			this._getDataReportForId(this.iInvoiceId);
		}
		else if (oResponse.Success) 
		{
			// Hide loading
			this.oLoadingPopup.hide();
			delete this.oLoadingPopup;
			
			// Build UI given the invoice details
			var oInvoice	= oResponse.oInvoice;
			var oContent 	=	$T.div({class: 'invoice-view'},
									$T.div({class: 'invoice-view-tables'},
										$T.table({class: 'reflex'},
											$T.caption(
												$T.div({class: 'caption_bar'},						
													$T.div({class: 'caption_title'},
														'Invoice Details'
													)
												)
											),
											$T.tbody({class: 'invoice-view-details'},
												$T.tr(
													$T.th({class: 'label'},
														'Account ID :'
													),
													$T.td(oInvoice.Account)
												),
												$T.tr(
													$T.th({class: 'label'},
														'Business Name :'
													),
													$T.td(oInvoice.business_name)
												),
												$T.tr(
													$T.th({class: 'label'},
														'Trading Name :'
													),
													$T.td(oInvoice.trading_name)
												),
												$T.tr(
													$T.th({class: 'label'},
														'Invoice # :'
													),
													$T.td(oInvoice.Id)
												),
												$T.tr(
													$T.th({class: 'label'},
														'Created On :'
													),
													$T.td(oInvoice.CreatedOn)
												),
												$T.tr(
													$T.th({class: 'label'},
														'Credits :'
													),
													$T.td('$' + oInvoice.Credits)
												),
												$T.tr(
													$T.th({class: 'label'},
														'Debits :'
													),
													$T.td('$' + oInvoice.Debits)
												),
												$T.tr(
													$T.th({class: 'label'},
														'Amount :'
													),
													$T.td('$' + oInvoice.Total)
												),
												$T.tr(
													$T.th({class: 'label'},
														'Tax :'
													),
													$T.td('$' + oInvoice.Tax)
												),
												$T.tr(
													$T.th({class: 'label'},
														'Balance :'
													),
													$T.td('$' + oInvoice.TotalOwing)
												)
											)
										),
										$T.table({class: 'reflex'},
											$T.caption(
												$T.div({class: 'caption_bar'},						
													$T.div({class: 'caption_title'},
														'Services'
													)
												)
											),
											$T.thead(
												$T.tr(
													$T.th('Service #'),
													$T.th('Charges'),
													$T.th('Credit'),
													$T.th('Debit')
												)
											)
										),
										$T.div({class: 'invoice-view-services'},
											$T.table({class: 'reflex'},
												$T.tbody({class: 'alternating'}
													// ...
												)
											)
										),
										$T.div ({class: 'invoice-view-buttons'},
											$T.button(
												$T.img({src: Popup_Invoice_View.CLOSE_IMAGE_SOURCE, alt: '', title: 'Close'}),
												$T.span('Close')
											)
										)		
									)
								);
			
			// Fill the services table
			if (oResponse.aServiceTotals)
			{
				var oServiceTotals	= jQuery.json.arrayAsObject(oResponse.aServiceTotals);
				var oServiceTBody	= oContent.select('div.invoice-view-services table > tbody').first();
				
				for (var iId in oServiceTotals)
				{
					oServiceTBody.appendChild(this._createServiceRow(oServiceTotals[iId]));
				}
			}
			
			// Add close button event handler
			var oCloseButton	= oContent.select('div.invoice-view-buttons > button').first();
			oCloseButton.observe('click', this.hide.bind(this));
			
			// Add dispute resolve button if necessary
			if (oInvoice.allow_resolve)
			{
				var oDetailsTBody	= oContent.select('tbody.invoice-view-details').first();
				oDetailsTBody.appendChild(
					$T.tr(
						$T.th({class: 'label'},
							'Dispute :'
						),
						$T.td({class: 'invoice-view-dispute'},
							$T.span('$' + parseFloat(oInvoice.Disputed).toFixed(2)),
							$T.button(
								$T.img({src: Popup_Invoice_View.RESOLVE_IMAGE_SOURCE, alt: '', title: 'Resolve Dispute'}),
								$T.span('Resolve Dispute')
							)
						)
					)
				);
				
				var oDisputeButton	= oDetailsTBody.select('button').first();
				oDisputeButton.observe('click', this._resolveDispute.bind(this));
			}
			
			this.oInvoice	= oInvoice;
			this.oContent 	= oContent; 
			
			this.setTitle('View Invoice Details');
			this.setIcon('../admin/img/template/invoice_small.png');
			this.setContent(oContent);
			this.addCloseButton();
			this.display();
		}
		else
		{
			// Hide loading
			this.oLoadingPopup.hide();
			delete this.oLoadingPopup;
			
			// Error in AJAX request
			Popup_Invoice_View._ajaxError(oResponse);
		}
	},
	
	_createServiceRow	: function(oServiceTotal)
	{
		return 	$T.tr(
					$T.td(
						$T.a({href: '/admin/reflex.php/Invoice/Service/' + oServiceTotal.Id},
							oServiceTotal.FNN
						)
					),
					$T.td(oServiceTotal.TotalCharge),
					$T.td(oServiceTotal.Credit),
					$T.td(oServiceTotal.Debit)
				);
	},
	
	_resolveDispute	: function()
	{
		// Load the js file and show dispute popup
		var fnClose	= function()
		{
			// Show loading
			var oLoading 	= new Reflex_Popup.Loading('Refreshing page...');
			oLoading.display();
			
			// Kill popup
			this.hide();
			
			// Refresh page
			window.location = window.location;
		}
		
		var fnAutoLoadHandler	= function()
		{
			new Popup_Invoice_Dispute(this.oInvoice, fnClose.bind(this));
		}
		
		JsAutoLoader.loadScript(
			'javascript/popup_invoice_dispute.js', 
			fnAutoLoadHandler.bind(this)
		);
	}
});

/////////////////////////////
// Class constants
/////////////////////////////

// Images
Popup_Invoice_View.RESOLVE_IMAGE_SOURCE = '../admin/img/template/tick.png';
Popup_Invoice_View.CLOSE_IMAGE_SOURCE	= '../admin/img/template/delete.png';
	
/////////////////////////////
// End Class constants
/////////////////////////////

Popup_Invoice_View._ajaxError	= function(oResponse)
{
	if (oResponse.Message)
	{
		Reflex_Popup.alert(oResponse.Message, {sTitle: 'Error'});
	}
	else if (oResponse.ERROR)
	{
		Reflex_Popup.alert(oResponse.ERROR, {sTitle: 'Error'});
	}
}
