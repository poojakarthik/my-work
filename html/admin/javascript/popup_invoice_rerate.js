
var Popup_Invoice_Rerate	= Class.create(Reflex_Popup, 
{
	initialize	: function($super, iInvoiceId, iRatePlanOverrideId)
	{
		$super(65);
		
		this._iInvoiceId			= iInvoiceId;
		this._iRatePlanOverrideId	= iRatePlanOverrideId;
		this._oRatePlanOverride		= null;
		this._oInvoice				= null;
		this._hServices				= null;
		
		this._showLoading();
		//this._checkForUnarchivedCDRs();
		this._buildUI();
	},
	
	// Private
	
	_ratePlanOverrideLoaded	: function(oRatePlan)
	{
		this._oRatePlanOverride	= oRatePlan;
		this._buildUI();
	},
	
	_buildUI	: function(bHasUnarchivedCDRs, oInvoice, oAccount, hServices, oRatePlan)
	{
		// Data preparation
		if (typeof bHasUnarchivedCDRs == 'undefined')
		{
			// Step 1: Check if the invoice has unarchived CDR records
			Flex.Invoice.hasUnarchivedCDRs(this._iInvoiceId, this._buildUI.bind(this));
			return;
		}
		else if (typeof oInvoice == 'undefined')
		{
			// Step 2: Get the invoice data
			if (bHasUnarchivedCDRs === true)
			{
				// Yes, get invoice details
				Flex.Invoice.getForId(this._iInvoiceId, this._buildUI.bind(this, bHasUnarchivedCDRs));
			}
			else
			{
				if (bHasUnarchivedCDRs === false)
				{
					// No
					Reflex_Popup.alert('This CDR Usage data for this invoice has been archived, it cannot be rerated.');
				}
				else
				{
					// Error
					Reflex_Popup.alert('There was an error accessing the database, please contact YBS for assistance.', {sTitle: 'Error'});
				}				
				this._hideLoading();
			}
			return;
		}
		else if (typeof oAccount == 'undefined')
		{
			// Step 3: Get the account data
			Flex.Account.getForId(oInvoice.Account, this._buildUI.bind(this, bHasUnarchivedCDRs, oInvoice));
			return;
		}
		else if (typeof hServices == 'undefined')
		{
			// Step 4: Get the service information for the account/invoice
			Flex.Invoice.getServicesForInvoice(this._iInvoiceId, this._buildUI.bind(this, bHasUnarchivedCDRs, oInvoice, oAccount));
			return;
		}
		else if (typeof oRatePlan == 'undefined')
		{
			// Step 5: Get the rate plan information (if override id provided)
			if (this._iRatePlanOverrideId)
			{
				Flex.Plan.getForId(this._iRatePlanOverrideId, this._buildUI.bind(this, bHasUnarchivedCDRs, oInvoice, oAccount, hServices));
				return;
			}
			else
			{
				// Continue on to buildUI
			}
		}
		
		// Step 6: Build UI
		this._oInvoice			= oInvoice;
		this._oAccount			= oAccount;
		this._hServices			= hServices;
		this._oRatePlanOverride	= oRatePlan;
		
		var oDetailsSection	= new Section(false, 'invoice-details');
		oDetailsSection.setTitleText('Invoice Details');
		oDetailsSection.setContent(
			$T.table(
				$T.tbody(
					$T.tr(
						$T.th(
							$T.span('Account'),
							$T.span(':')
						),
						$T.td(
							$T.a({href: 'flex.php/Account/Overview/?Account.Id=' + this._oAccount.Id},
								this._oAccount.Id
							)
						),
						$T.th(
							$T.span('Invoice #'),
							$T.span(':')
						),
						$T.td(this._oInvoice.Id)
					),
					$T.tr(
						$T.th(
							$T.span('Business Name'),
							$T.span(':')
						),
						$T.td(this._oAccount.BusinessName),
						$T.th(
							$T.span('Created On'),
							$T.span(':')
						),
						$T.td(Date.$parseDate(this._oInvoice.CreatedOn, 'Y-m-d').$format('d/m/Y'))
					),
					$T.tr(
						$T.th(
							$T.span('Customer Group'),
							$T.span(':')
						),
						$T.td(this._oAccount.customer_group.internal_name),
						$T.th(
							$T.span('Billing Period'),
							$T.span(':')
						),
						$T.td(
							Date.$parseDate(this._oInvoice.billing_period_start_datetime, 'Y-m-d H:i:s').$format('d/m/Y') + 
							' to ' + 
							Date.$parseDate(this._oInvoice.billing_period_end_datetime, 'Y-m-d H:i:s').$format('d/m/Y')
						)
					),
					$T.tr(
						$T.th(),
						$T.td(),
						$T.th(
							$T.span('Charge Total'),
							$T.span(':')
						),
						$T.td('$' + this._oInvoice.charge_total)
					),
					$T.tr(
						$T.th(),
						$T.td(),
						$T.th(
							$T.span('Charge Tax'),
							$T.span(':')
						),
						$T.td('$' + this._oInvoice.charge_tax)
					)
				)
			)
		);
		
		var oServiceListContent	= this._buildServiceList();
		
		var oServiceSection	= new Section(false, 'service-list');
		oServiceSection.setTitleText('Rate Plan Changes');
		oServiceSection.setContent(
			oServiceListContent
		);
		
		this._oContent	=	$T.div({class: 'popup-invoice-rerate'},
								oDetailsSection.getElement(),
								oServiceSection.getElement(),
								$T.div({class: 'buttons'},
									$T.button({class: 'icon-button'},
										'Rerate'
									).observe('click', this._doRerate.bind(this)),
									$T.button({class: 'icon-button'},
										'Cancel'
									).observe('click', this.hide.bind(this))
								)
							);
		
		this._hideLoading();
		
		this.setTitle('Rerate Invoice: ' + this._iInvoiceId);
		this.addCloseButton();
		this.setContent(this._oContent);
		this.display();
	},
	
	_buildServiceList	: function()
	{
		var oTBody	= $T.tbody();
		for (var iServiceId in this._hServices)
		{
			var oService	= this._hServices[iServiceId];
			
			var oModifiedElement				= $T.img({src: '../admin/img/template/required_input.png'});
			oModifiedElement.style.visibility	= 'hidden';
			oService.oModifiedElement			= oModifiedElement;
			
			var oTR	= 	$T.tr(
							$T.td(oModifiedElement),
							$T.td(oService.FNN),
							$T.td('(' + oService.rate_plan.Name + ')')
						);
	
			// No rate plan override OR the service type matches the rate plan override: show a plan select
			var bShowPlanSelect	= !this._oRatePlanOverride || (oService.ServiceType == this._oRatePlanOverride.ServiceType);
			if (bShowPlanSelect)
			{
				var oPlanSelect	=	Control_Field.factory(
										'select',
										{
											sLabel		: 'Plan',
											mEditable	: true,
											mMandatory	: true,
											mVisible	: true,
											fnPopulate	: this._getAvailablePlansForService.bind(this, iServiceId, oService.rate_plan.Id),
											bDisableValidationStyling	: true
										}
									); 
				oPlanSelect.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				oPlanSelect.addOnChangeCallback(this._planChanged.bind(this, iServiceId));
				oService.oPlanSelect	= oPlanSelect;
				oTR.appendChild($T.td(oPlanSelect.getElement()));
			}
			oTBody.appendChild(oTR);
		}
		
		return 	$T.div(
					$T.table(
						oTBody
					)
				);
	},
	
	_doRerate	: function()
	{
		this._rerate(false);
	},
	
	_rerate	: function(bForceIfNoChange)
	{
		var hRatePlanChanges	= {};
		var iChangeCount		= 0;
		for (var iServiceId in this._hServices)
		{
			var oService	= this._hServices[iServiceId];
			if (oService.oPlanSelect)
			{
				// Service has been included in the interface, only fails when rate plan override provided
				var iSelectedPlan	= parseInt(oService.oPlanSelect.getElementValue());
				if (!isNaN(iSelectedPlan) && (oService.rate_plan.Id != iSelectedPlan))
				{
					hRatePlanChanges[iServiceId]	= iSelectedPlan;
					iChangeCount++;
				}
			}
		}
		
		// Check for no plan changes (prompt if not forced to ignore)
		if (iChangeCount == 0 && !bForceIfNoChange)
		{
			Reflex_Popup.yesNoCancel('You have not changed any plans. Do you still want to Rerate the invoice?', {fnOnYes: this._rerate.bind(this, true)});
			return;
		}
		
		this._showLoading('Rerating Invoice...');
		Flex.Invoice.rerateInvoice(this._iInvoiceId, hRatePlanChanges, this._rerateComplete.bind(this));
	},
	
	_rerateComplete	: function(oNewInvoice, oOldInvoice, mDebugLog)
	{
		if (oNewInvoice && oOldInvoice)
		{
			this._hideLoading();
			this.hide();
			new Popup_Invoice_Rerate_Summary(oNewInvoice, oOldInvoice, mDebugLog, !this._oRatePlanOverride);
		}
	},
	
	_ajaxError	: function(oResponse, sDescription)
	{
		if (this._oLoading)
		{
			this._oLoading.hide();
			delete this._oLoading;
		}
		
		var oConfig	= {sTitle: 'Error'};
		if (oResponse.sMessage)
		{
			// Exception/Error message
			Reflex_Popup.alert(oResponse.sMessage, oConfig);
		}
		else if (oResponse.ERROR)
		{
			// System error, not thrown by handler code
			Reflex_Popup.alert(oResponse.ERROR, oConfig);
		}		
	},
	
	_showLoading	: function(sMessage)
	{
		if (!this._oLoading)
		{
			this._oLoading	= new Reflex_Popup.Loading(sMessage);
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
	
	_getAvailablePlansForService	: function(iServiceId, iCurrentRatePlan, fnCallback, hRatePlans)
	{
		var oService	= this._hServices[iServiceId];
		if (this._oRatePlanOverride)
		{
			// Return options
			var aOptions	= [];
			
			// Add the 'No change' option (with the current plan id as value)
			aOptions.push(
				$T.option({value: iCurrentRatePlan},
					'-- No Change --'
				)
			);
			
			// Rate plan override specified, only return it, if the services ServiceType matches
			if (oService.ServiceType == this._oRatePlanOverride.ServiceType)
			{
				aOptions.push(
					$T.option({value: this._oRatePlanOverride.Id},
		       	 		this._oRatePlanOverride.Name
		       	 	)
			    );
			}
			
			fnCallback(aOptions);
		}
		else
		{
			// No rate plan override, make request to retrieve list of available plans
			if (typeof hRatePlans == 'undefined')
			{
				// Get rate plans
				Flex.Plan.getForAccount(
					oService.Account, 
					false, 
					this._getAvailablePlansForService.bind(this, iServiceId, iCurrentRatePlan, fnCallback)
				);
			}
			else
			{
				// Return options
				var aOptions	= [];
				
				// Add the 'No change' option (with the current plan id as value)
				aOptions.push(
					$T.option({value: iCurrentRatePlan},
						'-- No Change --'
					)
				);
				
				// Add the rest of the eligible plans
				for (var iId in hRatePlans[oService.ServiceType])
				{
					aOptions.push(
						$T.option({value: iId},
							hRatePlans[oService.ServiceType][iId].Name
						)
					);
				}
				fnCallback(aOptions);
			}
		}
	},
	
	_planChanged	: function(iServiceId)
	{
		var oService		= this._hServices[iServiceId];
		var iSelectedPlan	= parseInt(oService.oPlanSelect.getElementValue());
		if (!isNaN(iSelectedPlan) && (oService.rate_plan.Id != iSelectedPlan))
		{
			oService.oModifiedElement.style.visibility	= 'visible';
		}
		else
		{
			oService.oModifiedElement.style.visibility	= 'hidden';
		}
	}
});

// Static members

Object.extend(Popup_Invoice_Rerate,
{
	
});

