
var Popup_Invoice_Rerate_Summary	= Class.create(Reflex_Popup, 
{
	initialize	: function($super, oNewInvoice, oOriginalInvoice, mDebugLog, bAllowAdjustment)
	{
		$super(85);
		
		this._oNewInvoice		= oNewInvoice;
		this._oOriginalInvoice	= oOriginalInvoice;
		this._mDebugLog			= mDebugLog;
		this._hToggleRows		= {};
		this._bAllowAdjustment	= (typeof bAllowAdjustment == 'undefined') ? true : !!bAllowAdjustment;
		
		this._buildUI();
	},
	
	// Private
	_buildUI	: function()
	{
		var oSection	= new Section(false);
		oSection.setContent(
			$T.ul({class: 'reset horizontal'},
				$T.li(this._buildInvoiceSummary(this._oOriginalInvoice, 'Original Invoice')),
				$T.li(this._buildInvoiceSummary(this._oNewInvoice, 'Rerated Invoice', this._oOriginalInvoice.oSummaryData))
			)
		);
		
		this._fAdjustmentAmount	= this._oNewInvoice.oSummaryData.fInvoiceTotal - this._oOriginalInvoice.oSummaryData.fInvoiceTotal;
		
		var oLogButton			= 	$T.button({class: 'icon-button'},
										'View Log'
									).observe('click', this._showDebugLog.bind(this, this._mDebugLog));
		var oAdjustmentButton	= 	$T.button({class: 'icon-button'},
										'Add Adjustment'
									).observe('click', this._doAddAdjustment.bind(this));
		this._oContent			=	$T.div({class: 'popup-invoice-rerate-summary'},
										oSection.getElement(),
										$T.div({class: 'buttons'},
											oAdjustmentButton,
											oLogButton,
											$T.button({class: 'icon-button'},
												'Cancel'
											).observe('click', this.hide.bind(this))
										)
									);
		
		if (!this._mDebugLog || this._mDebugLog === '')
		{
			// Hide log button, no log returned
			oLogButton.hide();
		}
		
		if (!this._bAllowAdjustment)
		{
			// Hide adjustment button because difference is a debit or there is no difference 
			oAdjustmentButton.hide();
		}
		
		// Hide all service details
		for (var sId in this._hToggleRows)
		{
			if (sId.match(/(service_\d+)|(shared_plans)/))
			{
				this._toggleRows(sId);
			}
		}
		
		this.setTitle('Rerate Invoice Complete');
		this.addCloseButton();
		this.setContent(this._oContent);
		this.display();
		this._hideLoading();
	},
	
	_buildInvoiceSummary	: function(oInvoice, sTitle, oCompareTo)
	{
		var oData	= {aServices: {}};
		oCompareTo	= (oCompareTo ? oCompareTo : {});
		var oTBody	= $T.tbody();
		var oTable	= 	$T.table({class: 'invoice-summary'},
							oTBody
						);
		
		// Invoice title
		var fInvoiceTotal	= parseFloat(oInvoice.Total) + parseFloat(oInvoice.Tax);
		
		oData.fInvoiceTotal	= fInvoiceTotal;
		var oPDFImage		= $T.img({class: 'pdf-link', src: '../admin/img/template/pdf_small.png', title: 'Download Invoice PDF', alt: 'Download Invoice PDF'});
		oPDFImage.observe('click', this._downloadPDF.bind(this, oInvoice));
		
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
		
		// New Charges
		var oNewChargesRow	= 	$T.tr({class: 'toggle-row new-charges'},
									$T.td({class: 'padding-cell'}),
									$T.td({colspan: 3, class: 'underline'},
										$T.img({src: Popup_Invoice_Rerate_Summary.TOGGLE_OPEN}),
										'New Charges'
									)
								);
		oTBody.appendChild(oNewChargesRow);
		
		// Service data summary
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
			
			var oUsageRow			= Popup_Invoice_Rerate_Summary._getServiceSummaryRow('Usage', fUsage, 'highlight subitem', 'highlight');
			var oPlanChargesRow		= Popup_Invoice_Rerate_Summary._getServiceSummaryRow('Plan Charges', fPlanCharges, 'highlight subitem', 'highlight');
			var oPlanCreditsRow		= Popup_Invoice_Rerate_Summary._getServiceSummaryRow('Plan Discounts', fPlanCredits, 'highlight subitem', 'highlight');
			var oServiceChargesRow	= Popup_Invoice_Rerate_Summary._getServiceSummaryRow('Service Charges & Discounts', fServiceChargesAndCredits, 'underline highlight subitem', 'underline highlight');
			var oServiceTotalRow	= Popup_Invoice_Rerate_Summary._getServiceSummaryRow('', fCalcServiceTotal, 'subitem', 'underline subtotal');
			
			if (oCompareTo && oCompareTo.aServices && oCompareTo.aServices[iServiceId])
			{
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
		
		// Account level charges
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
		
		// Invoice GST
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
		
		// Shared Plans
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
		
		// New Charges amount
		oData.fNewChargesTotal	= fNewChargesTotal;
		oNewChargesRow.appendChild(Popup_Invoice_Rerate_Summary._getAmountTD(fNewChargesTotal, 'underline total'));
		oNewChargesRow.appendChild(Popup_Invoice_Rerate_Summary._getNatureTD(fNewChargesTotal));
		oNewChargesRow.appendChild(Popup_Invoice_Rerate_Summary._getDifferenceTD(fNewChargesTotal, oCompareTo.fNewChargesTotal));
		this._registerToggleRow('new_charges', oNewChargesRow, iNewChargesRows);
		
		// Adjustments
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
		
		oInvoice.oSummaryData	= oData;
		
		return 	oTable;
	},
	
	_downloadPDF	: function(oInvoice)
	{
		// Show alert
		Reflex_Popup.alert('Generating the PDF for Invoice ' + oInvoice.Id + '. This may take a few moments to complete');
		
		// Redirect to the invoice pdf generation application handler
		var oCreatedOn	= Date.$parseDate(oInvoice.CreatedOn, 'Y-m-d'); 
		window.location	= 	'/admin/reflex.php/Invoice/PDF/' + oInvoice.Id + 
							'/?Account=' + oInvoice.Account + 
							'&Invoice_Run_Id=' + oInvoice.invoice_run_id + 
							'&Year=' + oCreatedOn.getFullYear() + 
							'&Month=' + (oCreatedOn.getMonth() + 1);
	},
	
	_doAddAdjustment	: function()
	{
		this._addAdjustment(false);
	},
	
	_addAdjustment	: function(bForceIfNoDifference)
	{
		if ((this._fAdjustmentAmount >= Popup_Invoice_Rerate_Summary.MIN_ADJUSTMENT) && !bForceIfNoDifference)
		{
			Reflex_Popup.yesNoCancel(
				'There difference between the original invoice and the rerated invoice totals is not a Credit amount. Do you still want to add an adjustment?', 
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
							}
						};
		Vixen.Popup.ShowAjaxPopup('AddAdjustmentPopupId', 'medium', 'Request Adjustment', 'Adjustment', 'Add', oData);
	},
	
	_showLoading	: function()
	{
		if (!this._oLoading)
		{
			this._oLoading	= new Reflex_Popup.Loading();
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
	
	_registerToggleRow	: function(sId, oToggleRow, iRowsToToggle)
	{
		if (!this._hToggleRows[sId])
		{
			this._hToggleRows[sId]	= {bVisible: true, aRows: [], iRowsToToggle: iRowsToToggle};
		}
		this._hToggleRows[sId].aRows.push(oToggleRow);
		oToggleRow.observe('click', this._toggleRows.bind(this, sId));
	},	
	
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
	
	_showDebugLog	: function(sText)
	{
		var oTextArea	=	$T.textarea({class: 'log-text'},
								sText
							);
		Reflex_Popup.alert(oTextArea, {sTitle: 'Log', iWidth: 61, bOverrideStyles: false});
	}
});

// Static members

Object.extend(Popup_Invoice_Rerate_Summary,
{
	TOGGLE_CLOSED	: '../admin/img/template/tree_closed.png',
	TOGGLE_OPEN		: '../admin/img/template/tree_open.png',
	
	MIN_ADJUSTMENT	: -1,
	
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
			// Credit
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
			// Debit
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
	
	_getDifferenceTD	: function(mValue, mCompareTo)
	{
		if (typeof mCompareTo !== 'undefined')
		{
			var fValue		= Popup_Invoice_Rerate_Summary._getCurrency(mValue);
			var fCompareTo	= Popup_Invoice_Rerate_Summary._getCurrency(mCompareTo);
			var fDifference	= Popup_Invoice_Rerate_Summary._getCurrency(fValue - fCompareTo);
			var sExtraClass	= '';
			if (fDifference > 0)
			{
				// Debit
				sExtraClass	= 'amount-debit';
			}
			else if (fDifference < 0)
			{
				// Credit
				fDifference	= Math.abs(fDifference);
				sExtraClass	= 'amount-credit';
			}
			
			return 	$T.td({class: 'amount ' + sExtraClass},
						(fDifference === 0 ? '' : '$' + fDifference.toFixed(2))
					);
		}
		return $T.td();
	},
	
	_getCurrency	: function(mValue)
	{
		var fValue	= parseFloat(mValue);
		fValue		= Math.round(fValue * 100) / 100;
		return fValue;
	},
	
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
