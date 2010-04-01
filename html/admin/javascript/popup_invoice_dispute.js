
var Popup_Invoice_Dispute	= Class.create(Reflex_Popup,
{
	initialize	: function($super, oInvoice, fnClose)
	{
		$super(35);
		this.oInvoice	= oInvoice;
		this.fnClose	= fnClose;
		this._buildUI();
	},
	
	_buildUI	: function()
	{
		// Build UI given the invoice details
		var oContent 	=	$T.div({class: 'invoice-dispute'},
								$T.div(
									$T.table({class: 'reflex'},
										$T.caption(
											$T.div({class: 'caption_bar'},						
												$T.div({class: 'caption_title'},
													'Dispute Details'
												)
											)
										),
										$T.tbody(
											$T.tr(
												$T.th({class: 'label'},
													'Invoice # :'
												),
												$T.td(),
												$T.td(this.oInvoice.Id),
												$T.td()
											),
											$T.tr(
												$T.th({class: 'label'},
													'Debits :'
												),
												$T.td(),
												$T.td({class: 'currency'},
													'$' + parseFloat(this.oInvoice.Debits).toFixed(2)
												),
												$T.td()
											),
											$T.tr(
												$T.th({class: 'label'},
													'Credits :'
												),
												$T.td('-'),
												$T.td({class: 'currency'},
													'$' + parseFloat(this.oInvoice.Credits).toFixed(2)
												),
												$T.td()
											),
											$T.tr(
												$T.th({class: 'label'},
													'Tax :'
												),
												$T.td('+'),
												$T.td({class: 'currency dispute-tax'},
													'$' + parseFloat(this.oInvoice.Tax).toFixed(2)
												),
												$T.td()
											),
											$T.tr(
												$T.th({class: 'label'},
													'Amount :'
												),
												$T.td(),
												$T.td({class: 'currency'},
													'$' + parseFloat(this.oInvoice.TotalOwing).toFixed(2)
												),
												$T.td()
											),
											$T.tr(
												$T.th({class: 'label'},
													'Disputed :'
												),
												$T.td(),
												$T.td({class: 'currency dispute-currency-disputed'},
													'$' + parseFloat(this.oInvoice.Disputed).toFixed(2)
												),
												$T.td()
											),
											$T.tr(
												$T.th({class: 'label'},
													'Resolve :'
												),
												$T.td({colspan: 3},
													$T.ul({class: 'reset invoice-dispute-resolve'},
														$T.li(
															$T.input({type: 'radio', name: 'dispute-resolve', value: 1, checked: true}),
															$T.span('Customer to pay full amount')
														),
														$T.li(
															$T.input({type: 'radio', name: 'dispute-resolve', value: 2}),
															$T.span('Customer to pay '),
															$T.input({type: 'text', value: parseFloat(this.oInvoice.Disputed).toFixed(2)})
														),
														$T.li(
															$T.input({type: 'radio', name: 'dispute-resolve', value: 3}),
															$T.span('Payment NOT required')
														)
													)
												)
											)
										)
									)
								),
								$T.div({class: 'invoice-dispute-buttons'},
									$T.button(
										$T.img({src: Popup_Invoice_Dispute.RESOLVE_IMAGE_SOURCE, alt: '', title: 'Continue'}),
										$T.span('Continue')
									),
									$T.button(
										$T.img({src: Popup_Invoice_Dispute.CANCEL_IMAGE_SOURCE, alt: '', title: 'Cancel'}),
										$T.span('Cancel')
									)
								)
							);
						
		// Add button event handlers
		var oContinueButton	= oContent.select('div.invoice-dispute-buttons > button').first();
		oContinueButton.observe('click', this._resolveDispute.bind(this));
		
		var oCancelButton	= oContent.select('div.invoice-dispute-buttons > button').last();
		oCancelButton.observe('click', this.hide.bind(this));
		
		this.oContent = oContent;
		
		this.setTitle('Resolve Disputed Invoice');
		this.setIcon('../admin/img/template/invoice_small.png');
		this.setContent(oContent);
		this.display();
	},
	
	_resolveDispute	: function(oResponse)
	{
		// Get the resolve method
		var aRadios			= this.oContent.select('input[type="radio"]');
		var iResolveMethod	= null;
		var fResolveAmount	= parseFloat(this.oInvoice.Disputed);
		
		if (aRadios[0].checked)
		{
			iResolveMethod	= aRadios[0].value;
		}
		else if (aRadios[1].checked)
		{
			iResolveMethod	= aRadios[1].value;
			fResolveAmount	= parseFloat(this.oContent.select('input[type="text"]').first().value);
			
			if (isNaN(fResolveAmount))
			{
				Reflex_Popup.alert('Please enter a valid payment amount');
				return;
			}
		}
		else if (aRadios[2].checked)
		{
			iResolveMethod	= aRadios[2].value;
			fResolveAmount	= 0;
		}
		
		// Make ajax request
		var _resolve	= jQuery.json.jsonFunction(this._disputeResolved.bind(this), this._disputeResolved.bind(this), 'Invoice_View', 'resolveDispute');
		_resolve(this.oInvoice.Id, parseInt(iResolveMethod), fResolveAmount);
	},
	
	_disputeResolved	: function(oResponse)
	{
		if (oResponse.Success)
		{
			this.hide();
			
			if (this.fnClose)
			{
				this.fnClose();
			}
		}
		else
		{
			// Error
			Popup_Invoice_Dispute._ajaxError(oResponse);
		}
	}
});

/////////////////////////////
// Class constants
/////////////////////////////

// Images
Popup_Invoice_Dispute.RESOLVE_IMAGE_SOURCE 	= '../admin/img/template/tick.png';
Popup_Invoice_Dispute.CANCEL_IMAGE_SOURCE 	= '../admin/img/template/delete.png';

/////////////////////////////
// End Class constants
/////////////////////////////

Popup_Invoice_Dispute._ajaxError	= function(oResponse)
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
