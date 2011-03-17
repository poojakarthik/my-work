
var Component_Account_Charge_List = Class.create(
{
	initialize	: function(oContainerDiv, iAccountId, fnUpdatePagination)
	{
		this._oContainerDiv			= oContainerDiv;
		this._iAccountId			= iAccountId;
		this._fnUpdatePagination	= fnUpdatePagination;
		
		this._hFilters	= {};
		this._oOverlay 	= new Reflex_Loading_Overlay();
		this._oElement	= $T.div({class: 'component-account-charge-list'});
		this._oTooltip	= new Component_List_Tooltip(30);
		
		// Load constants then create UI
		Flex.Constant.loadConstantGroup(Component_Account_Charge_List.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
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
		this.oDataSet		= 	new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, Component_Account_Charge_List.DATA_SET_DEFINITION);
		this.oPagination 	= 	new Pagination(
									this._updateTable.bind(this), 
									Component_Account_Charge_List.MAX_RECORDS_PER_PAGE, 
									this.oDataSet
								);
		
		// Create Dataset filter object
		this._oFilter	=	new Filter(
								this.oDataSet,
								this.oPagination
							);
		this._oFilter.addFilter('account_id', {iType: Filter.FILTER_TYPE_VALUE});
		this._oFilter.addFilter('charge_status', {iType: Filter.FILTER_TYPE_SET});
		
		// Create Dataset sort object
		this._oSort	= new Sort(this.oDataSet, this.oPagination);
		
		// Create the page HTML
		var sButtonPathBase	= '../admin/img/template/resultset_';
		var oSection		= new Section(true);
		this._oSection 		= oSection;
		this._oElement.appendChild(oSection.getElement());
		
		// Main content -- table
		oSection.setContent(
			$T.table({class: 'component-account-charge-list-table reflex highlight-rows listing-fw3'},
				$T.thead(
					// Column headings
					$T.tr({class: 'component-account-charge-list-headerrow'},
						$T.th('Date'),
						$T.th('Code'),
						$T.th({class: 'component-account-charge-list-amount'},
							'Amount ($)'
						),
						$T.th(''),	// Nature
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
		this._oFilter.setFilterValue('charge_status', [$CONSTANT.CHARGE_WAITING, $CONSTANT.CHARGE_APPROVED, $CONSTANT.CHARGE_TEMP_INVOICE, $CONSTANT.CHARGE_INVOICED]);
		this._oSort.registerField('charged_on', Sort.DIRECTION_DESC);
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
		var oChild = oTBody.firstChild;
		while (oChild)
		{
			if (oTBody.firstChild.nodeName.match(/tr/i))
			{
				// Remove event handlers from the action buttons
				var oEditButton = oTBody.firstChild.select('img').first();
	
				if (oEditButton)
				{
					oEditButton.stopObserving();
				}
	
				// Remove the row
				oTBody.firstChild.remove();
				oChild = oTBody.firstChild;
			}
			else
			{
				oChild = oChild.nextSibling;
			}
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
			var bWaiting		= 	oData.charge_status == $CONSTANT.CHARGE_WAITING;
			var bApproved		= 	oData.charge_status == $CONSTANT.CHARGE_APPROVED;
			var oChargedOnDate	= 	(oData.charged_on ? Date.$parseDate(oData.charged_on, 'Y-m-d') : null);
			var oTR				= 	$T.tr(
										$T.td(oChargedOnDate ? oChargedOnDate.$format('d/m/Y') : 'N/A'),
										$T.td(
											oData.charge_type_code, 
											(bWaiting ? $T.div('(Awaiting Approval)') : '')
										),
										$T.td({class: 'component-account-charge-list-amount Currency'},
											new Number(oData.amount_inc_gst).toFixed(2)
										),
										$T.td(oData.nature == 'CR' ? oData.nature : '')
									);
			
			if (oData.delete_enabled)
			{
				if (bWaiting)
				{
					// Build the "Cancel Charge Request" 
					oTR.appendChild(this._createDeleteButton(oData.id, true));
				}
				else if ((oData.charge_model_id == $CONSTANT.CHARGE_MODEL_CHARGE) && (bApproved || (oData.charge_status == $CONSTANT.CHARGE_TEMP_INVOICE)))
				{
					// Build the "Delete Charge" button
					oTR.appendChild(this._createDeleteButton(oData.id, false));
				}
				else if (oData.charge_model_id == $CONSTANT.CHARGE_MODEL_ADJUSTMENT)
				{
					// Old adjustments
					oTR.appendChild(
						$T.td(
							$T.img({class: 'component-account-charge-list-legacy-adjustment', src: Component_Account_Charge_List.OLD_ADJUSTMENT_IMAGE_SOURCE, alt: 'Legacy Adjustment', title: 'Legacy Adjustment'})
						)
					);
				}
				else
				{
					var oTD			= $T.td('');
					oTD.innerHTML	= '&nbsp;';
					oTR.appendChild(oTD);
				}
			}
			
			// Tooltip content
			var hTooltipContent	= {};
			
			if (oData.extra_detail_enabled)
			{
				hTooltipContent['Charge Id'] = oData.id;
			}
			
			if (oData.created_by)
			{
				hTooltipContent['Requested By'] = oData.created_by_name;
			}
			
			if (oData.approved_by && bApproved)
			{
				hTooltipContent['Approved By'] = oData.approved_by_name;
			}
			
			if (oData.service_id)
			{
				if (oData.extra_detail_enabled)
				{
					hTooltipContent['Service'] = oData.service_id;
				}
				
				hTooltipContent['Service FNN'] = oData.service_fnn;
			}
			
			hTooltipContent['Status']		= Flex.Constant.arrConstantGroups.ChargeStatus[oData.charge_status].Description;
			hTooltipContent['Description']	= oData.description;
			
			if (oData.notes)
			{
				hTooltipContent['Notes'] = oData.notes;
			}
			
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
		var oButton	= $T.img({src: Component_Account_Charge_List.DELETE_IMAGE_SOURCE, alt: sAlt, title: sAlt}); 
		oButton.observe('click', this._deleteCharge.bind(this, iChargeId));
		return	$T.td({class: 'component-account-charge-list-delete'},
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
			'DeleteChargePopupId', 
			'medium', 
			'Charge', 
			'Account', 
			'DeleteRecord', 
			{
				'DeleteRecord'	: {'RecordType': 'Charge'},
				'Charge'		: {'Id': iChargeId}
			}
		);
	},
	
	_requestCharge : function()
	{
		Vixen.Popup.ShowAjaxPopup(
			'AddChargePopupId',
			'medium', 
			'Request Charge', 
			'Charge',
			'Add',
			{
				'Account': {'Id': this._iAccountId}
			}
		);
	}
});

// Static

Object.extend(Component_Account_Charge_List,
{
	DATA_SET_DEFINITION			: {sObject: 'Charge', sMethod: 'getAccountListDataset'},
	MAX_RECORDS_PER_PAGE		: 15,
	
	FILTER_IMAGE_SOURCE			: '../admin/img/template/table_row_insert.png',
	REMOVE_FILTER_IMAGE_SOURCE	: '../admin/img/template/delete.png',
	DELETE_IMAGE_SOURCE			: '../admin/img/template/delete.png',
	OLD_ADJUSTMENT_IMAGE_SOURCE	: '../admin/img/template/information.png',
	ICON_IMAGE_SOURCE			: '../admin/img/template/payment.png',
	
	REQUIRED_CONSTANT_GROUPS	: ['ChargeStatus', 'charge_model'],
	
	// Sorting definitions
	SORT_FIELDS	:	
	{
		id 				: Sort.DIRECTION_ASC,
		name			: Sort.DIRECTION_OFF,
		description		: Sort.DIRECTION_OFF,
		scenario_name	: Sort.DIRECTION_OFF,
		status_name		: Sort.DIRECTION_OFF
	},
	
	_ajaxError : function(oResponse)
	{
		var sMessage = (oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.');
		Reflex_Popup.alert(sMessage, {sTitle: 'Error'});
	}
});

Component_Account_Charge_List.SORT_IMAGE_SOURCE							= {};
Component_Account_Charge_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_OFF]		= '../admin/img/template/order_neither.png';
Component_Account_Charge_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_ASC]		= '../admin/img/template/order_asc.png';
Component_Account_Charge_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_DESC]	= '../admin/img/template/order_desc.png';
