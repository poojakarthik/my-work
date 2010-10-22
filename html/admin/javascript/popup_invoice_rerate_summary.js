
var Popup_Invoice_Rerate_Summary	= Class.create(Reflex_Popup, 
{
	initialize	: function($super, oNewInvoice, oOriginalInvoice, mDebugLog, bAllowAdjustmentAndTicket)
	{
		$super(85);
		
		// Summary of the regenerated invoice
		this._oNewInvoice				= oNewInvoice;
		
		// Summary of the original invoice
		this._oOriginalInvoice			= oOriginalInvoice;
		
		// The debugging log information, optional
		this._mDebugLog					= mDebugLog;
		
		// Stores info about toggleable rows
		this._hToggleRows				= {};
		
		// Can an adjustment/ticket be added from this popup?
		this._bAllowAdjustmentAndTicket	= (typeof bAllowAdjustmentAndTicket == 'undefined') ? true : !!bAllowAdjustmentAndTicket;
		
		// Create the interface
		this._buildUI();
	},
	
	// Public
	
	// Override
	hide	: function($super)
	{
		$super();
		
		// Remove the cached reference to this instance
		delete Popup_Invoice_Rerate_Summary._hInstances[this._oOriginalInvoice.Id];
	},
	
	// Override
	display	: function($super)
	{
		$super();
		
		// Cache a reference to this instance statically.
		// Used to disable adjustment buttons on completion of an adjustment because the adjustment popup
		// is framework 2 and thus quite hard to pass a callback to.
		Popup_Invoice_Rerate_Summary._hInstances[this._oOriginalInvoice.Id]	= this;
	},
	
	// Private
	
	// _buildUI: Creates the initial interface
	_buildUI	: function()
	{
		// One main section which contains to invoice summarys, in a horizontal UL
		var oSection	= new Section(false);
		oSection.setContent(
			$T.ul({class: 'reset horizontal'},
				$T.li(this._buildInvoiceSummary(this._oOriginalInvoice, 'Original Invoice')),
				$T.li(this._buildInvoiceSummary(this._oNewInvoice, 'Rerated Invoice', this._oOriginalInvoice.oSummaryData))
			)
		);
		
		// The amount that the invoice totals differ, used when adding an adjustment
		this._fAdjustmentAmount	= this._oNewInvoice.oSummaryData.fInvoiceTotal - this._oOriginalInvoice.oSummaryData.fInvoiceTotal;
		
		// Buttons, shown depending on the state of the popup
		var oLogButton			= 	$T.button({class: 'icon-button'},
										$T.img({src: Popup_Invoice_Rerate_Summary.VIEW_LOG_SRC}),
										$T.span('View Log')
									).observe('click', this._showDebugLog.bind(this, this._mDebugLog));
		var oAdjustmentButton	= 	$T.button({class: 'icon-button'},
										$T.img({src: Popup_Invoice_Rerate_Summary.ADD_ADJUSTMENT_SRC}),
										$T.span('Add Adjustment')
									).observe('click', this._doAddAdjustment.bind(this));
		var oTicketButton		= 	$T.button({class: 'icon-button'},
										$T.img({src: Popup_Invoice_Rerate_Summary.ADD_TICKET_SRC}),
										$T.span('Add Ticket')
									).observe('click', this._addTicket.bind(this));
		this._oContent			=	$T.div({class: 'popup-invoice-rerate-summary'},
										oSection.getElement(),
										$T.div({class: 'buttons-left'},
											oAdjustmentButton,
											oTicketButton
										),
										$T.div({class: 'buttons-right'},
											oLogButton,
											$T.button({class: 'icon-button'},
												$T.img({src: Popup_Invoice_Rerate_Summary.CLOSE_SRC}),
												$T.span('Close')
											).observe('click', this.hide.bind(this))
										)
									);
		
		// Cache references to these for use elsewhere
		this.oAdjustmentButton	= oAdjustmentButton;
		this.oTicketButton		= oTicketButton;
		
		if (!this._mDebugLog || this._mDebugLog === '')
		{
			// Hide log button, no log returned
			oLogButton.hide();
		}
		
		if (!this._bAllowAdjustmentAndTicket)
		{
			// Hide adjustment & ticket buttons 
			oAdjustmentButton.hide();
			oTicketButton.hide();
		}
		
		// Hide all service details
		for (var sId in this._hToggleRows)
		{
			if (sId.match(/(service_\d+)|(shared_plans)/))
			{
				this._toggleRows(sId);
			}
		}
		
		// Configure popup & display
		this.setTitle('Rerate Invoice Complete');
		this.addCloseButton();
		this.setContent(this._oContent);
		this.display();
		this._hideLoading();
	},
	
	// _buildInvoiceSummary: Creates a table showing the invoice summary data.
	_buildInvoiceSummary	: function(oInvoice, sTitle, oCompareTo)
	{
		// Create an object to store all comparable values, stored against 
		// oInvoice to be accessible after this function returns
		var oData	= {aServices: {}};
		
		// If given, this is a comparable values object created by the other summary 
		oCompareTo	= (oCompareTo ? oCompareTo : {});
		
		// Create the table and tbody
		var oTBody	= $T.tbody();
		var oTable	= 	$T.table({class: 'invoice-summary'},
							oTBody
						);
		
		// Invoice title row
		var fInvoiceTotal	= parseFloat(oInvoice.Total) + parseFloat(oInvoice.Tax);
		oData.fInvoiceTotal	= fInvoiceTotal;
		
		var oPDFImage		= $T.img({class: 'pdf-link', src: '../admin/img/template/pdf_small.png', title: 'Download Invoice PDF', alt: 'Download Invoice PDF'});
		oPDFImage.observe('click', this._downloadPDF.bind(this, oInvoice, false));
		
		oTBody.appendChild(
			$T.tr(
				$T.td({colspan :4, class: 'underline title'},
					$T.span(sTitle),
					oPDFImage
				),
				Popup_Invoice_Rerate_Summary._getAmountTD(fInvoiceTotal, 'underline total title'),
				Popup_Invoice_Rerate_Summary._getNatureTD(fInvoiceTotal, 'title'),
				Popup_Invoice_Rerate_Summary._getDifferenceTD(fInvoiceTotal, oCompareTo.fInvoiceTotal)
			)
		);
		
		// New Charges row
		var oNewChargesRow	= 	$T.tr({class: 'toggle-row new-charges'},
									$T.td({class: 'padding-cell'}),
									$T.td({colspan: 3, class: 'underline'},
										$T.img({src: Popup_Invoice_Rerate_Summary.TOGGLE_OPEN}),
										'New Charges'
									)
								);
		oTBody.appendChild(oNewChargesRow);
		
		// A set of rows for each service collapsable by the service (first) row
		var fNewChargesTotal	= 0;
		var iNewChargesRows		= 0;
		for (var iId in oInvoice.service_totals)
		{
			var oServiceTotal	= oInvoice.service_totals[iId];
			var iServiceId		= oServiceTotal.Service;
			
			var fUsage						= (oInvoice.cdr_usage[iServiceId] ? parseFloat(oInvoice.cdr_usage[iServiceId]) : 0);
			var fPlanCredits				= 0;
			var fPlanCharges				= 0;
			var fServiceChargesAndCredits	= 0;
			for (var sChargeType in oInvoice.charges)
			{
				if (oInvoice.charges[sChargeType].service_totals && oInvoice.charges[sChargeType].service_totals[iServiceId])
				{
					var fServiceTotal	= oInvoice.charges[sChargeType].service_totals[iServiceId];
					switch (sChargeType)
					{
						case 'PCAR':
						case 'PCAD':
							fPlanCharges	+= fServiceTotal;
							break;
						case 'PDCR':
						case 'PCR':
							fPlanCredits	+= fServiceTotal;
							break;
						default:
							fServiceChargesAndCredits	+= fServiceTotal;
							break;
					}
				}
			}
			
			var fDBServiceTotal		= parseFloat(oServiceTotal.TotalCharge) + parseFloat(oServiceTotal.Debit) - parseFloat(oServiceTotal.Credit);
			var fCalcServiceTotal	= fUsage + fPlanCharges + fPlanCredits;
			
			// Build service summary
			var sRatePlanNameExtraClass		= '';
			if (oCompareTo && oCompareTo.aServices && oCompareTo.aServices[iServiceId] && (oCompareTo.aServices[iServiceId].iRatePlan != oServiceTotal.RatePlan))
			{
				// Rate plan is different for this service in this invoice summary
				sRatePlanNameExtraClass	= ' different-rate-plan';
			}
			
			// Service/Toggle row
			var oToggleRow		= 	$T.tr({class: 'toggle-row service-item'},
										$T.td({class: 'padding-cell'}),
										$T.td({class: 'padding-cell'}),
										$T.td({colspan: 2, class: 'underline'},
											$T.img({src: Popup_Invoice_Rerate_Summary.TOGGLE_OPEN}),
											$T.span(oServiceTotal.FNN),
											$T.span({class: 'rate-plan-name' + sRatePlanNameExtraClass},
												' (' + Popup_Invoice_Rerate_Summary._limitString(oServiceTotal.rate_plan_name, 35) + ')'
											)
										),
										Popup_Invoice_Rerate_Summary._getAmountTD(fDBServiceTotal, 'underline subtotal'),
										Popup_Invoice_Rerate_Summary._getNatureTD(fDBServiceTotal)
									);
			oData.fDBServiceTotal	= fDBServiceTotal;
			this._registerToggleRow('service_' + iServiceId, oToggleRow, 5);
			oTBody.appendChild(oToggleRow);
			
			// Other rows
			var oUsageRow			= Popup_Invoice_Rerate_Summary._getServiceSummaryRow('Usage', fUsage, 'highlight subitem', 'highlight');
			var oPlanChargesRow		= Popup_Invoice_Rerate_Summary._getServiceSummaryRow('Plan Charges', fPlanCharges, 'highlight subitem', 'highlight');
			var oPlanCreditsRow		= Popup_Invoice_Rerate_Summary._getServiceSummaryRow('Plan Discounts', fPlanCredits, 'highlight subitem', 'highlight');
			var oServiceChargesRow	= Popup_Invoice_Rerate_Summary._getServiceSummaryRow('Service Charges & Discounts', fServiceChargesAndCredits, 'underline highlight subitem', 'underline highlight');
			var oServiceTotalRow	= Popup_Invoice_Rerate_Summary._getServiceSummaryRow('', fCalcServiceTotal, 'subitem', 'underline subtotal');
			
			if (oCompareTo && oCompareTo.aServices && oCompareTo.aServices[iServiceId])
			{
				// There is service data to compare to, show the differences
				var oCompareService	= oCompareTo.aServices[iServiceId];
				oToggleRow.appendChild			(Popup_Invoice_Rerate_Summary._getDifferenceTD(fDBServiceTotal, 			oCompareService.fDBServiceTotal));
				oUsageRow.appendChild			(Popup_Invoice_Rerate_Summary._getDifferenceTD(fUsage, 						oCompareService.fUsage));
				oPlanChargesRow.appendChild		(Popup_Invoice_Rerate_Summary._getDifferenceTD(fPlanCharges, 				oCompareService.fPlanCharges));
				oPlanCreditsRow.appendChild		(Popup_Invoice_Rerate_Summary._getDifferenceTD(fPlanCredits, 				oCompareService.fPlanCredits));
				oServiceChargesRow.appendChild	(Popup_Invoice_Rerate_Summary._getDifferenceTD(fServiceChargesAndCredits, 	oCompareService.fServiceChargesAndCredits));
				oServiceTotalRow.appendChild	(Popup_Invoice_Rerate_Summary._getDifferenceTD(fCalcServiceTotal,		 	oCompareService.fCalcServiceTotal));
			}
			
			oTBody.appendChild(oUsageRow);
			oTBody.appendChild(oPlanChargesRow);
			oTBody.appendChild(oPlanCreditsRow);
			oTBody.appendChild(oServiceChargesRow);
			oTBody.appendChild(oServiceTotalRow);
			
			fNewChargesTotal	+= fCalcServiceTotal;
			iNewChargesRows		+= 6;
			
			// Cache the comparable data for the service
			oData.aServices[iServiceId]	=	{
												fUsage						: fUsage,
												fPlanCharges				: fPlanCharges,
												fPlanCredits				: fPlanCredits,
												fServiceChargesAndCredits	: fServiceChargesAndCredits,
												fCalcServiceTotal			: fCalcServiceTotal,
												fDBServiceTotal				: fDBServiceTotal,
												iRatePlan					: oServiceTotal.RatePlan
											};
		}
		
		// Account level charges row
		oTBody.appendChild(
			$T.tr(
				$T.td({class: 'padding-cell'}),
				$T.td({class: 'padding-cell'}),
				$T.td({colspan: 2, class: 'underline'},
					'Account Charges & Discounts'
				),
				Popup_Invoice_Rerate_Summary._getAmountTD(oInvoice.account_charges_and_credits, 'underline subtotal'),
				Popup_Invoice_Rerate_Summary._getNatureTD(oInvoice.account_charges_and_credits),
				Popup_Invoice_Rerate_Summary._getDifferenceTD(oInvoice.account_charges_and_credits, oCompareTo.fAccountChargesAndCredits)
			)
		);
		oData.fAccountChargesAndCredits	= parseFloat(oInvoice.account_charges_and_credits);
		fNewChargesTotal				+= parseFloat(oInvoice.account_charges_and_credits);
		iNewChargesRows++;
		
		// Invoice GST row
		oTBody.appendChild(
			$T.tr(
				$T.td({class: 'padding-cell'}),
				$T.td({class: 'padding-cell'}),
				$T.td({colspan: 2, class: 'underline'},
					'GST'
				),
				Popup_Invoice_Rerate_Summary._getAmountTD(oInvoice.charge_tax, 'underline subtotal'),
				Popup_Invoice_Rerate_Summary._getNatureTD(oInvoice.charge_tax),
				Popup_Invoice_Rerate_Summary._getDifferenceTD(oInvoice.charge_tax, oCompareTo.fChargeTax)
			)
		);
		
		oData.fChargeTax	= parseFloat(oInvoice.charge_tax);
		fNewChargesTotal	+= parseFloat(oInvoice.charge_tax);
		iNewChargesRows++;
		
		// Shared Plans rows, collapsable at the top (title) row
		var fSharedPlans			= parseFloat(oInvoice.shared_plan_charges) + parseFloat(oInvoice.shared_plan_discounts);
		oData.fSharedPlans			= fSharedPlans;
		fNewChargesTotal			+= fSharedPlans;
		var oSharedPlansToggleRow	= 	$T.tr({class: 'toggle-row'},
											$T.td({class: 'padding-cell'}),
											$T.td({class: 'padding-cell'}),
											$T.td({colspan: 2, class: 'underline'},
												$T.img({src: Popup_Invoice_Rerate_Summary.TOGGLE_OPEN}),
												$T.span('Shared Plans')
											),
											Popup_Invoice_Rerate_Summary._getAmountTD(fSharedPlans, 'underline subtotal'),
											Popup_Invoice_Rerate_Summary._getNatureTD(fSharedPlans),
											Popup_Invoice_Rerate_Summary._getDifferenceTD(fSharedPlans, oCompareTo.fSharedPlans)
										);
		this._registerToggleRow('shared_plans', oSharedPlansToggleRow, 3);
		
		var oSharedPlanChargesRow	= Popup_Invoice_Rerate_Summary._getServiceSummaryRow('Shared Plan Charges', oInvoice.shared_plan_charges, 'highlight subitem', 'highlight');
		var oSharedPlanDiscountsRow	= Popup_Invoice_Rerate_Summary._getServiceSummaryRow('Shared Plan Discounts', oInvoice.shared_plan_discounts, 'highlight subitem underline', 'highlight underline');
		var oSharedPlanDTotalRow	= Popup_Invoice_Rerate_Summary._getServiceSummaryRow('', fSharedPlans, 'subitem', 'underline subtotal');
		
		oData.fSharedPlanCharges	= parseFloat(oInvoice.shared_plan_charges);
		oData.fSharedPlanDiscounts	= parseFloat(oInvoice.shared_plan_discounts);
		
		oSharedPlanChargesRow.appendChild(Popup_Invoice_Rerate_Summary._getDifferenceTD(oInvoice.shared_plan_charges, oCompareTo.fSharedPlanCharges));
		oSharedPlanDiscountsRow.appendChild(Popup_Invoice_Rerate_Summary._getDifferenceTD(oInvoice.shared_plan_discounts, oCompareTo.fSharedPlanDiscounts));
		oSharedPlanDTotalRow.appendChild(Popup_Invoice_Rerate_Summary._getDifferenceTD(fSharedPlans, oCompareTo.fSharedPlans));
		
		oTBody.appendChild(oSharedPlansToggleRow);
		oTBody.appendChild(oSharedPlanChargesRow);
		oTBody.appendChild(oSharedPlanDiscountsRow);
		oTBody.appendChild(oSharedPlanDTotalRow);
		
		iNewChargesRows	+= 4;
		
		// New Charges row, collapses the service list, gst, account charges & shared plans
		oData.fNewChargesTotal	= fNewChargesTotal;
		oNewChargesRow.appendChild(Popup_Invoice_Rerate_Summary._getAmountTD(fNewChargesTotal, 'underline total'));
		oNewChargesRow.appendChild(Popup_Invoice_Rerate_Summary._getNatureTD(fNewChargesTotal));
		oNewChargesRow.appendChild(Popup_Invoice_Rerate_Summary._getDifferenceTD(fNewChargesTotal, oCompareTo.fNewChargesTotal));
		this._registerToggleRow('new_charges', oNewChargesRow, iNewChargesRows);
		
		// Adjustments row, collapsable on the title
		var fAdjustments			= parseFloat(oInvoice.adjustment_total) + parseFloat(oInvoice.adjustment_tax);
		oData.fAdjustments			= fAdjustments;
		var oAdjustmentsToggleRow	= 	$T.tr({class: 'toggle-row'},
											$T.td({class: 'padding-cell'}),
											$T.td({colspan: 3, class: 'underline'},
												$T.img({src: Popup_Invoice_Rerate_Summary.TOGGLE_OPEN}),
												$T.span('Adjustments')
											),
											Popup_Invoice_Rerate_Summary._getAmountTD(fAdjustments, 'underline total'),
											Popup_Invoice_Rerate_Summary._getNatureTD(fAdjustments),
											Popup_Invoice_Rerate_Summary._getDifferenceTD(fAdjustments, oCompareTo.fAdjustments)
										);
		this._registerToggleRow('adjustments', oAdjustmentsToggleRow, 2);
		oTBody.appendChild(oAdjustmentsToggleRow);
		
		// Adjustment Total row
		oData.fAdjustmentTotal	= parseFloat(oInvoice.adjustment_total);
		oTBody.appendChild(
			$T.tr(
				$T.td({class: 'padding-cell'}),
				$T.td({class: 'padding-cell'}),
				$T.td({colspan: 2, class: 'underline'},
					'Adjustment Total'
				),
				Popup_Invoice_Rerate_Summary._getAmountTD(oInvoice.adjustment_total, 'underline subtotal'),
				Popup_Invoice_Rerate_Summary._getNatureTD(oInvoice.adjustment_total),
				Popup_Invoice_Rerate_Summary._getDifferenceTD(oInvoice.adjustment_total, oCompareTo.fAdjustmentTotal)
			)
		);
		
		// Adjustment Tax row
		oData.fAdjustmentTax	= parseFloat(oInvoice.adjustment_tax);
		oTBody.appendChild(
			$T.tr(
				$T.td({class: 'padding-cell'}),
				$T.td({class: 'padding-cell'}),
				$T.td({colspan: 2, class: 'underline'},
					'Adjustment Tax'
				),
				Popup_Invoice_Rerate_Summary._getAmountTD(oInvoice.adjustment_tax, 'underline subtotal'),
				Popup_Invoice_Rerate_Summary._getNatureTD(oInvoice.adjustment_tax),
				Popup_Invoice_Rerate_Summary._getDifferenceTD(oInvoice.adjustment_tax, oCompareTo.fAdjustmentTax)
			)
		);
		
		// Cache the comparable summary data
		oInvoice.oSummaryData	= oData;
		
		// Return the table (so it can be attached to the dom)
		return 	oTable;
	},
	
	// _downloadPDF: Redirects to the Invoice/PDF Application handler which outputs the contents of the pdf
	_downloadPDF	: function(oInvoice, bRedirectNow)
	{
		if (!bRedirectNow)
		{
			// Show alert
			Reflex_Popup.alert('Generating the PDF for Invoice ' + oInvoice.Id + '. This may take a few moments to complete');
			
			// Delay 1/4 sec
			setTimeout(this._downloadPDF.bind(this, oInvoice, true), 250);
		}
		
		// Redirect to the invoice pdf generation application handler
		var oCreatedOn	= Date.$parseDate(oInvoice.CreatedOn, 'Y-m-d'); 
		window.location	= 	'/admin/reflex.php/Invoice/PDF/' + oInvoice.Id + 
							'/?Account=' + oInvoice.Account + 
							'&Invoice_Run_Id=' + oInvoice.invoice_run_id + 
							'&Year=' + oCreatedOn.getFullYear() + 
							'&Month=' + (oCreatedOn.getMonth() + 1);
	},
	
	// _doAddAdjustment: Click event handler for the 'Add Adjustment' button
	_doAddAdjustment	: function()
	{
		if (Popup_Invoice_Rerate_Summary._hAdjustments[this._oNewInvoice.invoice_run_id])
		{
			Reflex_Popup.alert('You have already added an adjustment for this Invoice');
			return;
		}
		
		this._addAdjustment(false);
	},
	
	// _addAdjustment: 
	_addAdjustment	: function(bForceIfNoDifference)
	{
		if ((this._fAdjustmentAmount >= Popup_Invoice_Rerate_Summary.MIN_ADJUSTMENT) && !bForceIfNoDifference)
		{
			// Adjustment amount is greater than the minimum allowed (meant to be a credit)
			this._fAdjustmentAmount	= 0;
			
			Reflex_Popup.yesNoCancel(
				'There difference between the original Invoice and the rerated Invoice totals is not a Credit amount. Do you still want to add an adjustment?', 
				{fnOnYes: this._addAdjustment.bind(this, true)}
			);
			return;
		}
		
		// Build Vixen popup request data
		var oData	= 	{
							Account	:
							{
								// Account id to apply the adjustment to
								Id	: this._oNewInvoice.Account
							},
							AmountOverride	:
							{
								// Amount override for the adjustment
								Amount	: Math.abs(this._fAdjustmentAmount)
							},
							Charge	:
							{
								// Invoice the adjustment is related to
								Invoice	: this._oOriginalInvoice.Id
							},
							Rerate	:
							{
								// Flag that tells the adjustment popup where we're coming from
								IsRerateAdjustment	: true
							}
						};
		
		if (!Popup_Invoice_Rerate_Summary._hTickets[this._oNewInvoice.invoice_run_id])
		{
			// Haven't already added a ticket, send through the rerated invoice id so that one is added on adjustment request completion
			oData.RerateInvoiceRun	= {Id: this._oNewInvoice.invoice_run_id};
		}
		
		Vixen.Popup.ShowAjaxPopup('AddAdjustmentPopupId', 'medium', 'Request Adjustment', 'Adjustment', 'Add', oData);
	},
	
	// _showLoading: Shows a loading popup
	_showLoading	: function()
	{
		if (!this._oLoading)
		{
			this._oLoading	= new Reflex_Popup.Loading();
		}
		this._oLoading.display();
	},
	
	// _showLoading: Hides the loading popup, if visible
	_hideLoading	: function()
	{
		if (this._oLoading)
		{
			this._oLoading.hide();
			delete this._oLoading;
		}
	},
	
	// _registerToggleRow: 	Sets up the given row element, so that it will toggle the given number of rows below it.
	//						Registers the row against the given id.
	_registerToggleRow	: function(sId, oToggleRow, iRowsToToggle)
	{
		if (!this._hToggleRows[sId])
		{
			this._hToggleRows[sId]	= {bVisible: true, aRows: [], iRowsToToggle: iRowsToToggle};
		}
		this._hToggleRows[sId].aRows.push(oToggleRow);
		oToggleRow.observe('click', this._toggleRows.bind(this, sId));
	},
	
	// _toggleRows: Activates the toggle row registered against the given id
	_toggleRows	: function(sId)
	{
		var aRows			= this._hToggleRows[sId].aRows;
		var bVisible		= !this._hToggleRows[sId].bVisible;
		var iRowsToToggle	= this._hToggleRows[sId].iRowsToToggle;
		for (var i = 0; i < aRows.length; i++)
		{
			// Toggle the affected rows
			var iCount			= iRowsToToggle;
			var oRow			= aRows[i];
			var oToggleImage	= oRow.select('img').first();
			while (iCount > 0)
			{
				oRow	= oRow.nextSibling;
				if (oRow.visible() || oRow.sToggledBy == sId)
				{
					if (bVisible)
					{
						oRow.show();
					}
					else
					{
						oRow.hide();
					}
					oRow.sToggledBy	= sId;
				}
				iCount--;
			}
			
			// Update the toggle image
			if (oToggleImage)
			{
				oToggleImage.src	= Popup_Invoice_Rerate_Summary[bVisible ? 'TOGGLE_OPEN' : 'TOGGLE_CLOSED'];
			}
		}
		this._hToggleRows[sId].bVisible	= bVisible;
	},
	
	// _toggleRows: Event handler for the 'View Log' buttons, shows a debug (textarea) popup containing the given text.
	_showDebugLog	: function(sText)
	{
		Reflex_Popup.debug(sText);
	},
	
	// _addTicket: 	Event handler for the 'Add Ticket' button, calls the static addTicket function if a ticket
	//				has not already been added for the rerated invoice run id
	_addTicket	: function()
	{
		var iRerateInvoiceRunId	= this._oNewInvoice.invoice_run_id;
		if (Popup_Invoice_Rerate_Summary._hTickets[iRerateInvoiceRunId])
		{
			Reflex_Popup.alert('You have already added a ticket for this Invoice');
			return;
		}
		
		// Record that a ticket has been (is to be) added for the rerated invoice
		Popup_Invoice_Rerate_Summary._hTickets[iRerateInvoiceRunId]	= true;
		
		// Create the ticket
		Popup_Invoice_Rerate_Summary.addTicket(this._oOriginalInvoice.Id, iRerateInvoiceRunId, null);
	}
});

// Static members

Object.extend(Popup_Invoice_Rerate_Summary,
{
	// Image sources
	TOGGLE_CLOSED		: '../admin/img/template/tree_closed.png',
	TOGGLE_OPEN			: '../admin/img/template/tree_open.png',
	ADD_TICKET_SRC		: '../admin/img/template/ticket_add.png',
	ADD_ADJUSTMENT_SRC	: '../admin/img/template/charge_add.png',
	VIEW_LOG_SRC		: '../admin/img/template/view.png',
	CLOSE_SRC			: '../admin/img/template/delete.png',
	
	// Minimum invoice total difference (credit) required for an adjustment addition to be allowed without warning
	MIN_ADJUSTMENT		: -1,
	
	// Caches Popup_Invoice_Rerate_Summary instances (against the original invoice id tied to the popup)
	_hInstances		: {},
	
	// Caches whether or not an adjustment has been added against an invoice run id (of a rerated invoices)
	_hAdjustments	: {},
	
	// Caches whether or not a ticket has been added against an invoice run id (of a rerated invoices)
	_hTickets		: {},
	
	// Public
	
	// adjustmentAdded:	Static callback for use when an adjustment request has been made for the rerate.  
	//					Once this has been called, the 'Add Adjustment' button is disabled
	adjustmentAdded	: function(sPopupMessage, iOriginalInvoiceId, iRerateInvoiceRunId, iAdjustmentId, bAddTicket, bShowTicketPopup)
	{
		// Record that an adjustment has been added for the rerated invoice
		Popup_Invoice_Rerate_Summary._hAdjustments[iRerateInvoiceRunId]	= true;
		
		// Disable the 'add adjustment' button on the instance that the adjustment was added from
		Popup_Invoice_Rerate_Summary._hInstances[iOriginalInvoiceId].oAdjustmentButton.disabled	= true;
		
		if (!bShowTicketPopup)
		{
			// Show an alert with given message, on close comes back to this function with 'bShowTicketPopup' set to true, (if ticket to be added)
			Reflex_Popup.alert(
				sPopupMessage, 
				{fnClose: (bAddTicket ? Popup_Invoice_Rerate_Summary.addTicket.curry(iOriginalInvoiceId, iRerateInvoiceRunId, iAdjustmentId) : null)}
			);
		}
		else if (bShowTicketPopup)
		{
			// Create the ticket
			Popup_Invoice_Rerate_Summary.addTicket(iOriginalInvoiceId, iRerateInvoiceRunId, iAdjustmentId);
		}
	},
	
	// addTicket: Allows creation of a ticket which will be tied to the rerating of an invoice
	addTicket	: function(iOriginalInvoiceId, iRerateInvoiceRunId, iAdjustmentId)
	{	
		// Show the 'add ticket' popup
		new Popup_Invoice_Rerate_Ticket(iOriginalInvoiceId, iRerateInvoiceRunId, iAdjustmentId, Popup_Invoice_Rerate_Summary.ticketAdded.curry(iOriginalInvoiceId, iRerateInvoiceRunId));
	},
	
	// ticketAdded: This is used as a callback for the Invoice Rerate Ticket popup, is only called on successful ticket creation.
	//				Disallows anymore ticket creation for the popup (as well after any adjustments that are added for the invoice)
	ticketAdded	: function(iOriginalInvoiceId, iRerateInvoiceRunId, iTicketId)
	{
		// Record that a ticket has been (is to be) added for the rerated invoice
		Popup_Invoice_Rerate_Summary._hTickets[iRerateInvoiceRunId]	= true;
		
		// Disable the 'add ticket' button on the instance that the ticket is being added from
		Popup_Invoice_Rerate_Summary._hInstances[iOriginalInvoiceId].oTicketButton.disabled	= true;
	},
	
	// Private
	
	// _getAmountTD: Returns a TD element containing a formatted amount value given the raw amount
	_getAmountTD	: function(mValue, sExtraClass)
	{
		// Parse and round to 2 decimal places
		var fValue	= Popup_Invoice_Rerate_Summary._getCurrency(mValue);
		if (fValue < 0)
		{
			// Credit
			return 	$T.td({class: 'amount' + (sExtraClass ? ' ' + sExtraClass : '')},
						'$' + Math.abs(fValue).toFixed(2)
					);
		}
		else
		{
			// Debit
			return 	$T.td({class: 'amount' + (sExtraClass ? ' ' + sExtraClass : '')},
						'$' + fValue.toFixed(2)
					);
		}
	},
	
	// _getAmountTD: Returns a TD element containing an amount nature value (credit or debit) given the raw amount
	_getNatureTD	: function(mValue, sExtraClass)
	{
		// Parse and round to 2 decimal places
		var fValue	= Popup_Invoice_Rerate_Summary._getCurrency(mValue);
		if (fValue < 0)
		{
			// Credit
			return 	$T.td({class: 'amount-credit' + (sExtraClass ? ' ' + sExtraClass : '')},
						'CR'
					);
		}
		else
		{
			// Debit
			return $T.td({class: (sExtraClass ? sExtraClass : '')});
		}
	},
	
	// _getServiceSummaryRow: Returns a service summary/title TR given the service name, amount & extra class names
	_getServiceSummaryRow	: function(sName, mValue, sExtraClass, sAmountExtraClass)
	{
		var fValue	= Popup_Invoice_Rerate_Summary._getCurrency(mValue);
		var oTR		=	$T.tr(
							$T.td({class: 'padding-cell'}),
							$T.td({class: 'padding-cell'}),
							$T.td({class: 'padding-cell'}),
							$T.td({class: (sExtraClass ? sExtraClass : '')},
								sName
							)
						);
		
		if (fValue < 0)
		{
			// Service total is a Credit
			oTR.appendChild(
				$T.td({class: 'amount' + (sAmountExtraClass ? ' ' + sAmountExtraClass : '')},
					'$' + Math.abs(fValue).toFixed(2)
				)
			);
			
			oTR.appendChild(
				$T.td({class: 'amount-credit'},
					'CR'
				)
			);
		}
		else
		{
			// Service total is a Debit
			oTR.appendChild(
				$T.td({class: 'amount' + (sAmountExtraClass ? ' ' + sAmountExtraClass : '')},
					'$' + fValue.toFixed(2)
				)
			);
			
			oTR.appendChild(
				$T.td()
			);
		}

		return oTR;
	},
	
	// _getDifferenceTD: Returns a TD element, with the difference between the two values as a formatted amount
	_getDifferenceTD	: function(mValue, mCompareTo)
	{
		if (typeof mCompareTo !== 'undefined')
		{
			var fValue		= Popup_Invoice_Rerate_Summary._getCurrency(mValue);
			var fCompareTo	= Popup_Invoice_Rerate_Summary._getCurrency(mCompareTo);
			var fDifference	= Popup_Invoice_Rerate_Summary._getCurrency(fValue - fCompareTo);
			var sExtraClass	= '';
			var sPrefix		= '';
			if (fDifference > 0)
			{
				// Debit
				sExtraClass	= 'difference-debit';
			}
			else if (fDifference < 0)
			{
				// Credit
				sPrefix		= '- ';
				fDifference	= Math.abs(fDifference);
				sExtraClass	= 'difference-credit';
			}
			
			return 	$T.td({class: 'amount ' + sExtraClass},
						(fDifference === 0 ? '' : sPrefix + '$' + fDifference.toFixed(2))
					);
		}
		return $T.td();
	},
	
	// _getCurrency: Returns the given value as a float, rounded to 2 decimal places
	_getCurrency	: function(mValue)
	{
		var fValue	= parseFloat(mValue);
		fValue		= Math.round(fValue * 100) / 100;
		return fValue;
	},
	
	// _limitString: Limit the given string to the given limit, truncate & show '...' if the limit is exceeded
	_limitString	: function(sValue, iLimit)
	{
		sValue		= ((sValue !== null) && (typeof sValue !== 'undefined') ? sValue : ''); 
		var sResult	= sValue.toString().substring(0, iLimit);
		if (sResult.length != sValue.length)
		{
			sResult += '...';
		}
		return sResult;
	}
});

