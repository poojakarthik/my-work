
var	Component_Account_Charge_List	= Class.create(
{
	initialize	: function(oContainerDiv, iAccountId)
	{
		this._oContainerDiv			= oContainerDiv;
		this._iAccountId			= iAccountId;
		this._sTableId				= 'ChargeTable';
		this._iVisibleChargeModel	= null;
		this._sVisibleChargeModel	= '';
		this._bInitialLoadComplete	= false;
		this._hCharges				= [];
		this._bCanDelete			= false;
		this._bUserIsGod			= false;
		this._buildUI();
	},
	
	_buildUI	: function(oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Create the interface, empty table
			this._oAddRequestButton	= 	$T.button({class: 'icon-button'},
											$T.span('Request '),
											$T.span('')
										);
			this._oAddRequestButton.observe('click', this._addRequest.bind(this));
			
			// Got Charge data, create list
			this._oContent	= 	$T.div({class: 'component-account-charge-list'},
									$T.div({class: 'component-account-charge-list-title'},
										$T.img({src: Component_Account_Charge_List.ICON_IMAGE_SOURCE, alt: 'Charges', title: 'Charges'}),
										$T.span('Charges')
									),
									$T.div({class: 'component-account-charge-list-tabs'}),
									$T.table({id: this._sTableId, class: 'Listing', cellspacing: '0', cellpadding: '3', border: '0'},
										$T.tbody(
											$T.tr({class: 'First'},
												$T.th('Date'),
												$T.th('Code'),
												$T.th({class: 'amount'},
													'Amount'
												),
												$T.th('')
											),
											$T.tr(
												$T.td({class: 'component-account-charge-list-loading', colspan: '4'},
													'Loading the Charge list...'
												)
											)
										)
									),
									$T.div({class: 'ButtonContainer'},
										$T.div({class: 'Right'},
											this._oAddRequestButton
										)
									)
								);
			
			// Add &nbsp; to nature column to fix display problem
			this._oContent.select('tr.First > th').last().innerHTML = '&nbsp;';
			this._oContainerDiv.appendChild(this._oContent);
			this._oTBody	= this._oContent.select('table.Listing > tbody').first();
			
			// Add the table as a vixen table
			Component_Account_Charge_List.addVixenTable(
				this._sTableId, 
				true, 
				{
					InvoiceTable		: ['invoice_run_id'],
					RecurringChargeTable: ['RecurringChargeId']
				}
			);
			
			// Create tab group
			var oTabContainer		= this._oContent.select('div.component-account-charge-list-tabs').first(); 
			this.oControlTabGroup	= new Control_Tab_Group(oTabContainer, false, this._tabChange.bind(this));
			this.oControlTabGroup.addTab(
				Component_Account_Charge_List.TAB_CHARGES, 
				new Control_Tab(
					Component_Account_Charge_List.TAB_CHARGES, 
					null, 
					Component_Account_Charge_List.CHARGES_IMAGE_SOURCE
				)
			);
			
			this.oControlTabGroup.addTab(
				Component_Account_Charge_List.TAB_ADJUSTMENTS, 
				new Control_Tab(
					Component_Account_Charge_List.TAB_ADJUSTMENTS, 
					null, 
					Component_Account_Charge_List.ADJUSTMENTS_IMAGE_SOURCE
				)
			);
			
			// Get Charge data
			var fnGetCharges	= 	jQuery.json.jsonFunction(
										this._buildUI.bind(this), 
										this._buildUI.bind(this), 
										'Charge', 
										'getForAccount'
									);
			fnGetCharges(this._iAccountId);
		}
		else
		{
			this._bInitialLoadComplete	= true;
			
			if (oResponse.Success)
			{
				// Cache the charges by their charge model id
				this._hCharges										= {};
				this._hCharges[$CONSTANT.CHARGE_MODEL_CHARGE]		= [];
				this._hCharges[$CONSTANT.CHARGE_MODEL_ADJUSTMENT]	= [];
				
				var oCharge	= null;
				for (var i = 0; i < oResponse.aCharges.length; i++)
				{
					oCharge	= oResponse.aCharges[i];
					this._hCharges[oCharge.charge_model_id].push(oCharge);
				}
				
				// Process other response content
				this._bCanDelete	= oResponse.bCanDelete;
				this._bUserIsGod	= oResponse.bUserIsGod;
				
				// Add the delete column if the user has permission
				if (this._bCanDelete)
				{
					var oTHDelete		= $T.th('');
					oTHDelete.innerHTML = '&nbsp;';
					this._oContent.select('tr.First').first().appendChild(oTHDelete);
				}
				
				// Show the charges first
				this.oControlTabGroup.switchToTab(0);
			}
			else
			{
				// Ajax error
				this._ajaxError(oResponse);
				this._showNoDataRow();
			}
		}
	},
	
	_clearRows	: function()
	{
		// Clear all existing rows (except the first)
		var aExistingTRs	= this._oTBody.select('tr');
		
		for (var i = 1; i < aExistingTRs.length; i++)
		{
			aExistingTRs[i].stopObserving();
			aExistingTRs[i].remove();
		}
	},
	
	_addRows	: function()
	{
		// Timing
		//var iStart	= new Date().getTime();
		
		this._clearRows();
		
		// Add new rows, for the current charge model
		var oTBody			= this._oTBody;
		var oCharge			= null;
		var sRowId			= null;
		var iRowIdIndex		= 0;
		var bWaiting		= null;
		var bApproved		= null;
		var aCharges		= this._hCharges[this._iVisibleChargeModel];
		var sChargeModelId	= this._sVisibleChargeModel + ' Id :';
		var oRow			= null;
		var iNumCharges		= aCharges.length;
		var bModelIsCharge	= this._iVisibleChargeModel == $CONSTANT.CHARGE_MODEL_CHARGE;
		
		var iRowsStart	= new Date().getTime();
		var iTotal		= 0;
		
		for (var i = 0; i < iNumCharges; i++)
		{
			// Timing
			//var iInnerStart	= new Date().getTime();
			
			oCharge		= aCharges[i];
			bWaiting	= oCharge.Status == $CONSTANT.CHARGE_WAITING;
			bApproved	= oCharge.Status == $CONSTANT.CHARGE_APPROVED;
			sRowId		= this._sTableId + '_' + iRowIdIndex;
			oTR			= 	$T.tr({id: sRowId, class: ((iRowIdIndex % 2) ? 'Odd' : 'Even')},
								$T.td(oCharge.charge_on_label),
								$T.td(
									oCharge.ChargeType, 
									(bWaiting ? $T.div('(Awaiting Approval)') : '')
								),
								$T.td({class: 'amount Currency'},
									oCharge.amount_inc_gst
								),
								$T.td(oCharge.Nature == 'CR' ? oCharge.Nature : '')
							);
			
			if (this._bCanDelete)
			{
				if (bWaiting)
				{
					// Build the "Cancel Charge Request" 
					this._addDeleteButtonToCharge(oCharge.Id, oTR, true);
				}
				else if (bModelIsCharge && (bApproved || (oCharge.Status == $CONSTANT.CHARGE_TEMP_INVOICE)))
				{
					// Build the "Delete Charge" button
					this._addDeleteButtonToCharge(oCharge.Id, oTR, false);
				}
				else
				{
					var oTD			= $T.td('');
					oTD.innerHTML	= '&nbsp;';
					oTR.appendChild(oTD);
				}
			}
			
			oTBody.appendChild(oTR);
			
			// Vixen tooltip content
			var oTooltipContent	= {};
			
			if (this._bUserIsGod)
			{
				oTooltipContent[this._sVisibleChargeModel + ' Id :']	= oCharge.Id;
			}
			
			if (oCharge.CreatedBy)
			{
				oTooltipContent['Requested By :']	= oCharge.created_by_label;
			}
			
			if (oCharge.ApprovedBy && bApproved)
			{
				oTooltipContent['Approved By :']	= oCharge.approved_by_label;
			}
			
			if (oCharge.Service)
			{
				if (this._bUserIsGod)
				{
					oTooltipContent['Service :']	= oCharge.Service;
				}
				
				oTooltipContent['Service FNN :']	= oCharge.serviceFNN;
			}
			
			oTooltipContent['Status :']			= oCharge.status_label;
			oTooltipContent['Description :']	= oCharge.Description;
			
			if (oCharge.Notes)
			{
				oTooltipContent['Notes :']	= oCharge.Notes;
			}
			
			oTBody.appendChild(Component_Account_Charge_List.createVixenTooltipContent(sRowId, oTooltipContent));
			
			// Vixen table stuff
			oRow	= Component_Account_Charge_List.createVixenTableRow();
			Component_Account_Charge_List.addVixenTableIndex(oRow, 'invoice_run_id', oCharge.invoice_run_id);
			
			if (oCharge.LinkType == $CONSTANT.CHARGE_LINK_PAYMENT)
			{
				// This charge relates directly to a payment
				Component_Account_Charge_List.addVixenTableIndex(oRow, 'PaymentId', oCharge.LinkId);
			} 
			else if (oCharge.LinkType == $CONSTANT.CHARGE_LINK_RECURRING)
			{
				// This charge relates directly to a recurring charge
				Component_Account_Charge_List.addVixenTableIndex(oRow, 'RecurringChargeId', oCharge.LinkId);
			}
			
			Vixen.table[this._sTableId].row.push(oRow);
			iRowIdIndex++;
			
			// Timing
			//iTotal	+= (new Date().getTime() - iInnerStart);
		}
		
		// Vixen table stuff
		Vixen.table[this._sTableId].totalRows	= iRowIdIndex;
		
		if (Vixen.table[this._sTableId].totalRows == 0)
		{
			this._showNoDataRow();
		}
		
		// Timing
		//var iNow	= new Date().getTime();
		//alert('Num Rows: ' + iRowIdIndex + ', Total: ' + (iNow - iStart) + ', Rows Total: ' + (iNow - iRowsStart) + ', Average Single Row: ' + (iTotal / iRowIdIndex).toFixed(2));
	},
	
	_addDeleteButtonToCharge	: function(iChargeId, oTR, bCancel)
	{
		var sAlt	= (bCancel ? 'Cancel ' + this._sVisibleChargeModel + ' Request' : 'Delete ' + this._sVisibleChargeModel);
		var oButton	= $T.img({src: Component_Account_Charge_List.DELETE_IMAGE_SOURCE, alt: sAlt, title: sAlt}); 
		oButton.observe('click', this._deleteCharge.bind(this, iChargeId, bCancel));
		oTR.appendChild(
			$T.td({class: 'delete'},
				oButton
			)
		);
	},
	
	_deleteCharge	: function(iChargeId, bCancel)
	{
		var sModel	= this._sVisibleChargeModel;
		Vixen.Popup.ShowAjaxPopup(
			"DeleteChargePopupId", 
			"medium", 
			sModel, 
			"Account", 
			"DeleteRecord", 
			{
				"DeleteRecord"	: {"RecordType": sModel},
				"Charge"		: {"Id": iChargeId}
			}
		);
	},
	
	_addRequest	: function()
	{
		var sModel	= this._sVisibleChargeModel;
		Vixen.Popup.ShowAjaxPopup(
			"Add" + sModel + "PopupId",
			"medium", 
			"Request " + sModel, 
			sModel,
			"Add",
			{
				"Account": {"Id": this._iAccountId}
			}
		);
	},
	
	_ajaxError	: function(oResponse)
	{
		if (oResponse.Message)
		{
			Reflex_Popup.alert(oResponse.Message);
		}
		else if (oResponse.ERROR)
		{
			Reflex_Popup.alert(oResponse.ERROR);
		}
		else if (oResponse.errorMessage)
		{
			Reflex_Popup.alert(oResponse.errorMessage);
		}
	},
	
	_tabChange	: function(oControlTab)
	{
		// Determine charge model to show
		var iChargeModel	= null;
		
		switch (oControlTab.strName)
		{
			case Component_Account_Charge_List.TAB_CHARGES:
				iChargeModel	= $CONSTANT.CHARGE_MODEL_CHARGE;
				break;
			case Component_Account_Charge_List.TAB_ADJUSTMENTS:
				iChargeModel	= $CONSTANT.CHARGE_MODEL_ADJUSTMENT;
				break;
		}
		
		this._sVisibleChargeModel	= Flex.Constant.arrConstantGroups.charge_model[iChargeModel].Name;
		
		// Update the add request button text
		this._oAddRequestButton.select('span').last().innerHTML	= this._sVisibleChargeModel;
		
		if (this._bInitialLoadComplete && (iChargeModel != this._iVisibleChargeModel))
		{
			// Record new charge model
			this._iVisibleChargeModel	= iChargeModel;
			
			// Update the add request button text
			this._oAddRequestButton.select('span').last().innerHTML	= this._sVisibleChargeModel;
			
			// Clear vixen table 'row' & 'totalRows'
			Vixen.table[this._sTableId].totalRows	= 0;
			Vixen.table[this._sTableId].row			= [];
			
			// Re-add rows given new charge model
			this._addRows();
			
			// Vixen table stuff
			Vixen.Tooltip.Attach(this._sTableId);
			Vixen.Highlight.Attach(this._sTableId);
		}
	},
	
	_showNoDataRow	: function()
	{
		// Clear existing rows
		this._clearRows();
		
		// Show 'no data' row
		this._oTBody.appendChild(
			$T.tr(
				$T.td({class: 'component-account-charge-list-nodata', colspan: '4'},
					'There are no ' + this._sVisibleChargeModel + 's to display'
				)
			)
		);
	}
});

Component_Account_Charge_List.DELETE_IMAGE_SOURCE		= '../admin/img/template/delete.png';
Component_Account_Charge_List.ICON_IMAGE_SOURCE			= '../admin/img/template/payment.png';
Component_Account_Charge_List.CHARGES_IMAGE_SOURCE		= Component_Account_Charge_List.ICON_IMAGE_SOURCE;
Component_Account_Charge_List.ADJUSTMENTS_IMAGE_SOURCE	= '../admin/img/template/charge_in_advance.png';

Component_Account_Charge_List.TAB_CHARGES		= 'Charges';
Component_Account_Charge_List.TAB_ADJUSTMENTS	= 'Adjustments';

// Vixen table helper functions, maybe move these somewhere else so they can be used again
Component_Account_Charge_List.createVixenElement	= function(sLabel, sValue)
{
	var oNBSP		= $T.span();
	oNBSP.innerHTML	= '&nbsp;';
	
	return 	$T.div({class: 'DefaultElement'},
				$T.div({class: 'DefaultOutput Default'},
					sValue
				),
				$T.div({class: 'DefaultLabel'},
					oNBSP,	
					$T.span(sLabel)
				)
			);
};

Component_Account_Charge_List.addVixenTableIndex	= function(oRow, sName, mValue)
{
	if (!oRow.index[sName])
	{
		oRow.index[sName]	= [];
	}
	
	oRow.index[sName].push(mValue);
};

Component_Account_Charge_List.addVixenTable	= function(sTableId, bCollapseAll, oLinkedTables)
{
	Vixen.table[sTableId] 				= {};
	Vixen.table[sTableId].collapseAll	= bCollapseAll;
	Vixen.table[sTableId].linked		= ((oLinkedTables && oLinkedTables != {}) ? true : false);
	Vixen.table[sTableId].link			= oLinkedTables;
};

Component_Account_Charge_List.createVixenTooltipContent	= function(sRowId, oContent)
{
	var oTooltipTR	= $T.tr({id: sRowId + 'DIV-TOOLTIP', style: "display: none;"});
	
	for (var sLabel in oContent)
	{
		oTooltipTR.appendChild(
			Component_Account_Charge_List.createVixenElement(sLabel, oContent[sLabel])
		);
	}
	
	return oTooltipTR;
};

Component_Account_Charge_List.createVixenTableRow = function()
{
	return	{
		up			: true,
		selected	: false,
		index		: {}
	};
};
