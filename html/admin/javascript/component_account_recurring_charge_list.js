

var Component_Account_Recurring_Charge_List = Class.create(
{
	initialize	: function(oContainerDiv, iAccountId, fnUpdatePagination)
	{
		this._oContainerDiv			= oContainerDiv;
		this._iAccountId			= iAccountId;
		this._fnUpdatePagination	= fnUpdatePagination;
			
		this._hFilters	= {};
		this._oOverlay 	= new Reflex_Loading_Overlay();
		this._oElement	= $T.div({class: 'component-account-recurring-charge-list'});
		
		// Load constants then create UI
		Flex.Constant.loadConstantGroup(Component_Account_Recurring_Charge_List.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
	},

	// Public
	
	getElement : function()
	{
		return this._oElement;
	},
	
	getSection : function()
	{
		return this._oSection;
	},

	changePage : function(sFunction)
	{
		this._changePage(sFunction);
	},

	updatePagination : function()
	{
		this._updatePagination();
	},
	
	refresh : function()
	{
		// Load the initial dataset
		this._oSort.refreshData(true);
		this._oFilter.refreshData(true);
		this.oPagination.getCurrentPage();
		this._showLoading(true);
	},
	
	// Protected
	
	_buildUI : function()
	{
		// Create Dataset & pagination object
		this.oDataSet		= 	new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, Component_Account_Recurring_Charge_List.DATA_SET_DEFINITION);
		this.oPagination 	= 	new Pagination(
									this._updateTable.bind(this), 
									Component_Account_Recurring_Charge_List.MAX_RECORDS_PER_PAGE, 
									this.oDataSet
								);
		
		// Create Dataset filter object
		this._oFilter	=	new Filter(
								this.oDataSet,
								this.oPagination
							);
		this._oFilter.addFilter('account_id', {iType: Filter.FILTER_TYPE_VALUE});
		
		// Create Dataset sort object
		this._oSort	= new Sort(this.oDataSet, this.oPagination);
		
		// Create the page HTML
		var sButtonPathBase	= '../admin/img/template/resultset_';
		var oSection		= new Section(true);
		this._oSection 		= oSection;
		this._oElement.appendChild(oSection.getElement());
		
		// Main content -- table
		oSection.setContent(
			$T.table({class: 'component-account-recurring-charge-list-table reflex highlight-rows listing-fw3'},
				$T.thead(
					// Column headings
					$T.tr({class: 'component-account-recurring-charge-list-headerrow'},
						$T.th('Date'),
						$T.th('Description'),
						$T.th('Status'),
						$T.th('') 	// Delete/Cancel
					)
				),
				$T.tbody({class: 'alternating'},
					this._createNoRecordsRow(true)
				)
			)
		);
		
		// Default sorting & filtering
		this._oFilter.setFilterValue('account_id', this._iAccountId);
		this._oSort.registerField('started_on', Sort.DIRECTION_DESC);
		this._oSort.registerField('id', Sort.DIRECTION_DESC);

		if (this._oContainerDiv)
		{
			this._oContainerDiv.appendChild(this._oElement);
		}
	},

	_showLoading	: function(bShow)
	{
		if (bShow)
		{
			this._oOverlay.attachTo(this._oElement.select('table > tbody').first());
		}
		else
		{
			this._oOverlay.detach();
		}
	},

	// _changePage: Executes the given function (name) on the dataset pagination object.
	_changePage	: function(sFunction)
	{
		this._showLoading(true);
		this.oPagination[sFunction]();
	},

	// _updateTable: Page load callback from the dataset pagination object.
	_updateTable	: function(oResultSet)
	{
		var oTBody = this._oElement.select('table > tbody').first();
		
		// Remove all existing rows
		while (oTBody.firstChild)
		{
			// Remove event handlers from the action buttons
			var oEditButton = oTBody.firstChild.select('img').first();

			if (oEditButton)
			{
				oEditButton.stopObserving();
			}

			// Remove the row
			oTBody.firstChild.remove();
		}
		
		// Check if any results came back
		if (!oResultSet || oResultSet.intTotalResults == 0 || oResultSet.arrResultSet.length == 0)
		{
			oTBody.appendChild(this._createNoRecordsRow());
		}
		else
		{
			// Add the rows
			var aData	= jQuery.json.arrayAsObject(oResultSet.arrResultSet);
			var iCount	= 0;
			for (var i in aData)
			{
				iCount++;
				oTBody.appendChild(this._createTableRow(aData[i], parseInt(i), oResultSet.intTotalResults));
			}
		}

		this._updatePagination();
		
		this._showLoading(false);
	},

	_createNoRecordsRow	: function(bOnLoad)
	{
		return $T.tr(
			$T.td({class: 'no-rows', colspan: 9},
				(bOnLoad ? 'Loading...' : 'There are no Charges to display')
			)
		);
	},

	_createTableRow	: function(oData, iPosition, iTotalResults)
	{
		if (oData.id)
		{
			var bWaiting		= 	oData.recurring_charge_status_id == $CONSTANT.RECURRING_CHARGE_STATUS_AWAITING_APPROVAL;
			var bApproved		= 	oData.recurring_charge_status_id == $CONSTANT.RECURRING_CHARGE_STATUS_ACTIVE;
			var oStartedOnDate	= 	(oData.started_on ? Date.$parseDate(oData.started_on, 'Y-m-d') : null);
			var oTR				= 	$T.tr(
										$T.td(oStartedOnDate ? oStartedOnDate.$format('d/m/Y') : 'N/A'),
										$T.td(oData.charge_type_description),
										$T.td(oData.recurring_charge_status_name)
									);
			
			if (oData.delete_enabled)
			{
				if (bWaiting)
				{
					// Build the "Cancel Charge Request" 
					oTR.appendChild(this._createDeleteButton(oData.id, true));
				}
				else if (bApproved)
				{
					// Build the "Delete Charge" button
					oTR.appendChild(this._createDeleteButton(oData.id, false));
				}
				else
				{
					var oTD			= $T.td('');
					oTD.innerHTML	= '&nbsp;';
					oTR.appendChild(oTD);
				}
			}
			
			// NOTE: This vixen table linking has been disabled until called upon, the code commented here is a mixture of charge & recurring
			// charge related code because concepts from both will be needed to re-add the linking/tooltip
			/*
			// Vixen tooltip content
			var oTooltipContent	= {};
			
			if (this._bUserIsGod)
			{
				oTooltipContent[this._sVisibleChargeModel + ' Id :']	= oData.id;
			}
			
			if (oData.CreatedBy)
			{
				oTooltipContent['Requested By :']	= oData.created_by_label;
			}
			
			if (oData.ApprovedBy && bApproved)
			{
				oTooltipContent['Approved By :']	= oData.approved_by_label;
			}
			
			if (oData.Service)
			{
				if (this._bUserIsGod)
				{
					oTooltipContent['Service :']	= oData.Service;
				}
				
				oTooltipContent['Service FNN :']	= oData.serviceFNN;
			}
			
			oTooltipContent['Status :']			= oData.status_label;
			oTooltipContent['Description :']	= oData.Description;
			
			if (oData.Notes)
			{
				oTooltipContent['Notes :']	= oData.Notes;
			}
			
			oTBody.appendChild(Component_Account_Recurring_Charge_List.createVixenTooltipContent(sRowId, oTooltipContent));
			
			// Vixen table stuff
			oRow	= Component_Account_Recurring_Charge_List.createVixenTableRow();
			Component_Account_Recurring_Charge_List.addVixenTableIndex(oRow, 'invoice_run_id', oData.invoice_run_id);
			
			if (oData.LinkType == $CONSTANT.CHARGE_LINK_PAYMENT)
			{
				// This charge relates directly to a payment
				Component_Account_Recurring_Charge_List.addVixenTableIndex(oRow, 'PaymentId', oData.LinkId);
			} 
			else if (oData.LinkType == $CONSTANT.CHARGE_LINK_RECURRING)
			{
				// This charge relates directly to a recurring charge
				Component_Account_Recurring_Charge_List.addVixenTableIndex(oRow, 'RecurringChargeId', oData.LinkId);
			}
			
			Vixen.table[this._sTableId].row.push(oRow);
			
			// Add tooltip
			var sToolTipHtml = "";
			if (bUserIsGod)
			{
				// Display the associated RecurringCharge Id if the user is GOD
				sToolTipHtml .= oData.Id;
			}
			if (oData.service_id)
			{
				if (bUserIsGod)
				{
					// Display the associated service Id if the user is GOD
					sToolTipHtml .= oData.service_id;
				}
				// The Recurring Charge is a Service Recurring Charge.  Display the FNN of the Service
				sToolTipHtml .= oData.service_fnn;
			}
			
			// Add GST to the MinCharge and RecursionCharge
			oData.min_charge		= this._applyGlobalTax(oData.min_charge);
			oData.recursion_charge	= AddGST(oData.recursion_charge);
			
			// TimesToCharge requires the Recursion Charge to not equal 0
			if (oData.recursion_charge != 0)
			{
				// Calculate the required number of recursions
				var fMinCharge 			= parseFloat(new Number(oData.min_charge).toFixed(2));
				var fRecursionCharge 	= parseFloat(new Number(oData.recursion_charge).toFixed(2));
				oData.times_to_charge 	= ceil(abs((fMinCharge / fRecursionCharge) - 0.01));
			}
			else
			{
				// The recursion charge is 0, which should never really happen, but I've found cases where it is this value
				oData.times_to_charge = "Infinity";
			}
			
			if (oData.in_advance)
			{
				oData.charged = "In Advance";
			}
			else
			{
				// Recurring Charge is charged in arrears
				if (oData.last_charged_on == oData.started_on)
				{
					// The last_charged_on does not truely represent the last time the account was charged
					// Set it to NULL
					oData.last_charged_on = NULL;
				}
				oData.charged = "In Arrears";
			}
			sToolTipHtml .= oData.charged;
			if (oData.total_charged > 0.0)
			{
				sToolTipHtml .= oData.last_charged_on;
				sToolTipHtml .= this._applyGlobalTax(oData.total_charged);
			}
			
			sToolTipHtml .= oData.nature;
			
			var sRecurringFreq = oData.recurring_freq ." ". oData.recurring_freq_type;
			
			sToolTipHtml .= sRecurringFreq;
			sToolTipHtml .= oData.times_to_charge;
			sToolTipHtml .= oData.total_recursions;
			sToolTipHtml .= this._applyGlobalTax(oData.cancellation_fee);
			sToolTipHtml .= oData.min_charge;
			sToolTipHtml .= oData.recursion_charge;
			sToolTipHtml .= oData.continuable;
			sToolTipHtml .= oData.unique_charge;
			
			var TimesToCharge = oData.times_to_charge;
			
			// Work out the end date
			if (is_numeric(iTimesToCharge))
			{
				// The end date depends on the Recurring Frequency type, the recurring frequency and the times to charge
				switch (oData.RecurringFreqType)
				{
					case BILLING_FREQ_DAY:
						var iTotalNumOfDays	= iTimesToCharge * oData.recurring_freq;
						var iEndTime		= strtotime("+{iTotalNumOfDays} days", strtotime(oData.started_on));
						break;
						
					case BILLING_FREQ_MONTH:
						var iTotalNumOfMonths	= iTimesToCharge * oData.recurring_freq;
						var iEndTime			= strtotime("+{iTotalNumOfMonths} months", strtotime(oData.started_on));
						break;
						
					case BILLING_FREQ_HALF_MONTH:
						// If there is an even number of half months, then you can just work out how many whole months to add to the CreatedOn date
						// If there is an odd number of half months, then add the even number of months on to the CreatedOn date; find out
						// what 1 month beyond this date would be and then find the middle of these 2 dates expressed in seconds
						var iTotalNumOfHalfMonths	= iTimesToCharge * oData.recurring_freq;
						var iTotalNumOfMonths		= (int)(iTotalNumOfHalfMonths / 2);
						var bExtraHalfMonth			= iTotalNumOfHalfMonths % 2;
						var iEndTime				= strtotime("+{iTotalNumOfMonths} months", strtotime(oData.started_on));
						
						if (bExtraHalfMonth)
						{
							var iOneMonthBeyondEndTime	= strtotime("+1 months", iEndTime);
							var iEndTime				= iEndTime + ((int)((iOneMonthBeyondEndTime - iEndTime) / 2));
						}
						break;
				}
				var sEndTime = date("d/m/Y", iEndTime);
			}
			else
			{
				// TimesToCharge is not a number.  It must equal Infinity
				var sEndTime = "Infinity";
			}
			
			sToolTipHtml 	.= oData.started_on;
			oData.end_date	= sEndTime;
			sToolTipHtml 	.= oData.end_date;
			*/
			
			return oTR;
		}
		else
		{
			// Invalid, return empty row
			return $T.tr();
		}
	},
	
	_createDeleteButton : function(iChargeId, bCancel)
	{
		var sAlt	= (bCancel ? 'Cancel Charge Request' : 'Delete Charge');
		var oButton	= $T.img({src: Component_Account_Recurring_Charge_List.DELETE_IMAGE_SOURCE, alt: sAlt, title: sAlt}); 
		oButton.observe('click', this._deleteCharge.bind(this, iChargeId));
		return	$T.td({class: 'component-account-recurring-charge-list-delete'},
					oButton
				);
	},
	
	_updatePagination : function(iPageCount)
	{
		if (iPageCount == undefined)
		{
			// Get the page count
			this.oPagination.getPageCount(this._updatePagination.bind(this));
		}
		else
		{
			if (this._fnUpdatePagination)
			{
				this._fnUpdatePagination(this.oPagination.intCurrentPage, iPageCount);
			}
		}
	},
	
	_deleteCharge	: function(iChargeId)
	{
		Vixen.Popup.ShowAjaxPopup(
			'DeleteRecurringChargePopupId', 
			'medium', 
			'Recurring Charge', 
			'Account', 
			'DeleteRecord',
			{
				DeleteRecord		: {RecordType: 'RecurringCharge'},
				RecurringCharge 	: {Id: iChargeId}
			}
		)
	}
});

// Static

Object.extend(Component_Account_Recurring_Charge_List,
{
	DATA_SET_DEFINITION			: {sObject: 'Recurring_Charge', sMethod: 'getAccountListDataset'},
	MAX_RECORDS_PER_PAGE		: 15,
	
	FILTER_IMAGE_SOURCE			: '../admin/img/template/table_row_insert.png',
	REMOVE_FILTER_IMAGE_SOURCE	: '../admin/img/template/delete.png',
	DELETE_IMAGE_SOURCE			: '../admin/img/template/delete.png',
	OLD_ADJUSTMENT_IMAGE_SOURCE	: '../admin/img/template/information.png',
	ICON_IMAGE_SOURCE			: '../admin/img/template/payment.png',
	
	REQUIRED_CONSTANT_GROUPS	: ['recurring_charge_status'],
	
	// Sorting definitions
	SORT_FIELDS	:	
	{
		id 				: Sort.DIRECTION_ASC,
		name			: Sort.DIRECTION_OFF,
		description		: Sort.DIRECTION_OFF,
		scenario_name	: Sort.DIRECTION_OFF,
		status_name		: Sort.DIRECTION_OFF
	},
	
	_hRecurringChargeStatus : {},
	
	_ajaxError : function(oResponse)
	{
		var sMessage = (oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.');
		Reflex_Popup.alert(sMessage, {sTitle: 'Error', sDebugContent: oResponse.sDebug});
	}
});

Component_Account_Recurring_Charge_List.SORT_IMAGE_SOURCE							= {};
Component_Account_Recurring_Charge_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_OFF]		= '../admin/img/template/order_neither.png';
Component_Account_Recurring_Charge_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_ASC]		= '../admin/img/template/order_asc.png';
Component_Account_Recurring_Charge_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_DESC]	= '../admin/img/template/order_desc.png';

