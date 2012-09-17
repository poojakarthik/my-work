

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
		this._oTooltip	= new Component_List_Tooltip(30);
		
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
	
	_buildUI : function(oTaxType)
	{
		if (typeof oTaxType == 'undefined')
		{
			Component_Account_Recurring_Charge_List._getGlobalTaxType(this._buildUI.bind(this));
			return;
		}
		
		this._oTaxType = oTaxType;
		
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
		this._oTooltip.clearRegisteredRows();
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
			
			// Tooltip content
			var hTooltipContent = {};
			
			if (oData.extra_detail_enabled)
			{
				// Display the associated RecurringCharge Id if the user is GOD
				hTooltipContent['Recurring Charge Id'] = oData.id;
			}
			if (oData.service_id)
			{
				if (oData.extra_detail_enabled)
				{
					// Display the associated service Id if the user is GOD
					hTooltipContent['Service'] = oData.service_id;
				}
				// The Recurring Charge is a Service Recurring Charge.  Display the FNN of the Service
				hTooltipContent['Service FNN'] = oData.service_fnn;
			}
			
			// Add GST to the MinCharge and RecursionCharge
			oData.min_charge		= this._applyGlobalTax(oData.min_charge);
			oData.recursion_charge	= this._applyGlobalTax(oData.recursion_charge);
			
			// iTimesToCharge requires the Recursion Charge to not equal 0
			if (oData.recursion_charge != 0)
			{
				// Calculate the required number of recursions
				var fMinCharge 			= parseFloat(new Number(oData.min_charge).toFixed(2));
				var fRecursionCharge 	= parseFloat(new Number(oData.recursion_charge).toFixed(2));
				oData.times_to_charge 	= Math.ceil(Math.abs((fMinCharge / fRecursionCharge) - 0.01));
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
					oData.last_charged_on = null;
				}
				oData.charged = "In Arrears";
			}
			
			hTooltipContent['Charged'] = oData.charged;
			
			if (oData.total_charge > 0.0)
			{
				hTooltipContent['Last Charged On'] 				= Date.$parseDate(oData.last_charged_on, 'Y-m-d').$format('d/m/Y');
				hTooltipContent['Already Charged (inc GST)']	= '$' + new Number(this._applyGlobalTax(oData.total_charge)).toFixed(2);
			}
			
			hTooltipContent['Nature'] = oData.nature;
			
			var sRecurringFreq 		= oData.recurring_freq + ' ' + oData.recurring_freq_type;
			var sRecurringFreqType 	= '';
			switch (oData.recurring_freq_type)
			{
				case Component_Account_Recurring_Charge_List.BILLING_FREQ_DAY:	// Day
					sRecurringFreqType = "day";
					break;
					
				case Component_Account_Recurring_Charge_List.BILLING_FREQ_MONTH:	// Month
					sRecurringFreqType = "month";
					break;
					
				case Component_Account_Recurring_Charge_List.BILLING_FREQ_HALF_MONTH:	// Half-Month
					sRecurringFreqType = "half-month";
					break;
			}
			
			var sFreqTypePluraliserSuffix = '';
			if (oData.recurring_freq != 1)
			{
				sFreqTypePluraliserSuffix = 's';
			}
			
			hTooltipContent['Recurring Frequency'] = oData.recurring_freq + ' ' + sRecurringFreqType + sFreqTypePluraliserSuffix;
			
			hTooltipContent['Times To Charge'] 				= oData.times_to_charge;
			hTooltipContent['Times Charged'] 				= oData.total_recursions;
			hTooltipContent['Cancellation Fee (inc GST)'] 	= '$' + new Number(this._applyGlobalTax(oData.cancellation_fee)).toFixed(2);
			hTooltipContent['Minimum Charge (inc GST)'] 	= '$' + new Number(oData.min_charge).toFixed(2);
			hTooltipContent['Recurring Charge (inc GST)'] 	= '$' + new Number(oData.recursion_charge).toFixed(2);
			hTooltipContent['Continuable'] 					= (oData.continuable ? 'Yes' : 'No');
			hTooltipContent['Unique Charge'] 				= (oData.unique_charge ? 'Yes' : 'No');
			
			var iTimesToCharge = oData.times_to_charge;
			
			// Work out the end date
			var oStartDate = Date.$parseDate(oData.started_on, 'Y-m-d'),
				oEndDate = new Date(oStartDate);
			if (!isNaN(iTimesToCharge))
			{
				// The end date depends on the Recurring Frequency type, the recurring frequency and the times to charge
				switch (oData.recurring_freq_type)
				{
					case Component_Account_Recurring_Charge_List.BILLING_FREQ_DAY:
						var iTotalNumOfDays	= iTimesToCharge * oData.recurring_freq;
						var iEndTime		= oEndDate.shift(iTotalNumOfDays, 'days').getTime();
						break;
						
					case Component_Account_Recurring_Charge_List.BILLING_FREQ_MONTH:
						var iTotalNumOfMonths	= iTimesToCharge * oData.recurring_freq;
						var iEndTime			= oEndDate.shift(iTotalNumOfMonths, 'months').getTime();
						break;
						
					case Component_Account_Recurring_Charge_List.BILLING_FREQ_HALF_MONTH:
						// If there is an even number of half months, then you can just work out how many whole months to add to the CreatedOn date
						// If there is an odd number of half months, then add the even number of months on to the CreatedOn date; find out
						// what 1 month beyond this date would be and then find the middle of these 2 dates expressed in seconds
						var iTotalNumOfHalfMonths	= iTimesToCharge * oData.recurring_freq;
						var iTotalNumOfMonths		= (int)(iTotalNumOfHalfMonths / 2);
						var bExtraHalfMonth			= iTotalNumOfHalfMonths % 2;
						var iEndTime				= oEndDate.shift(iTotalNumOfMonths, 'months').getTime();
						
						if (bExtraHalfMonth)
						{
							var iOneMonthBeyondEndTime	= new Date(iEndTime).shift(1, 'months').getTime();
							var iEndTime				= iEndTime + (parseInt((iOneMonthBeyondEndTime - iEndTime) / 2));
						}
						break;
				}
				
				var sEndTime = new Date(iEndTime).$format('d/m/Y');
			}
			else
			{
				// iTimesToCharge is not a number.  It must equal Infinity
				var sEndTime = "Infinity";
			}
			
			hTooltipContent['Started On'] 	= oStartDate.$format('d/m/Y');
			hTooltipContent['End Date']		= sEndTime;
			
			this._oTooltip.registerRow(oTR, hTooltipContent);
			
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
	},
	
	_applyGlobalTax : function(mValue)
	{
		var fValue = parseFloat(mValue);
		return fValue + ((this._oTaxType ? parseFloat(this._oTaxType.rate_percentage) : 1) * fValue);
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
	
	BILLING_FREQ_DAY 		: 1,
	BILLING_FREQ_MONTH 		: 2,
	BILLING_FREQ_HALF_MONTH : 3,
	
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
	
	_getGlobalTaxType : function(fnCallback, oResponse) {
		if (!oResponse) {
			// Make request
			var fnResp 	= Component_Account_Recurring_Charge_List._getGlobalTaxType.curry(fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Tax_Type', 'getGlobalTaxType');
			fnReq();
			return;
		}
		
		if (!oResponse.bSuccess) {
			// Error
			jQuery.json.errorPopup(oResponse);
			return;
		}

		if (!oResponse.oTaxType) {
			Reflex_Popup.alert("No Global Tax Type is defined");
		}
		
		if (fnCallback) {
			fnCallback(oResponse.oTaxType);
		}
	}
});

Component_Account_Recurring_Charge_List.SORT_IMAGE_SOURCE							= {};
Component_Account_Recurring_Charge_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_OFF]		= '../admin/img/template/order_neither.png';
Component_Account_Recurring_Charge_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_ASC]		= '../admin/img/template/order_asc.png';
Component_Account_Recurring_Charge_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_DESC]	= '../admin/img/template/order_neither.png';

