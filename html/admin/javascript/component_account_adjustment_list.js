
var Component_Account_Adjustment_List = Class.create(
{
	initialize	: function(iAccountId, oContainerDiv, iPageSize)
	{
		this._register();
		
		this._iAccountId	= iAccountId;
		this._oContainerDiv	= oContainerDiv;
		this._oTooltip 		= new Component_List_Tooltip(25);
		
		// Load constants then create UI
		Flex.Constant.loadConstantGroup(Component_Account_Adjustment_List.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
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
	
	deregister : function()
	{
		var iIndex = Component_Account_Adjustment_List._aInstances.indexOf(this);
		Component_Account_Adjustment_List._aInstances.splice(iIndex, 1);
	},
	
	refresh : function()
	{
		this._showLoading(true);
		this._oSort.refreshData(true);
		this._oFilter.refreshData(true);
		this.oPagination.getCurrentPage();
	},
	
	// Protected
	
	_register : function()
	{
		Component_Account_Adjustment_List._aInstances.push(this);
	},
	
	_buildUI : function()
	{
		// Create Dataset & pagination object
		this.oDataSet		= 	new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, Component_Account_Adjustment_List.DATA_SET_DEFINITION);
		this.oPagination 	= 	new Pagination(
									this._updateTable.bind(this), 
									(this._iPageSize ? this._iPageSize : Component_Account_Adjustment_List.MAX_RECORDS_PER_PAGE), 
									this.oDataSet
								);
		
		this._oOverlay = new Reflex_Loading_Overlay();
		
		// Create Dataset filter object
		this._oFilter = new Filter(this.oDataSet, this.oPagination);
		this._oFilter.addFilter('account_id', {iType: Filter.FILTER_TYPE_VALUE});
		
		// Create Dataset sort object
		this._oSort	= new Sort(this.oDataSet, this.oPagination, true, this._showLoading.bind(this, true));
		
		// Create the page HTML
		var sButtonPathBase	= '../admin/img/template/resultset_';
		
		this._oElement = $T.div({class: 'component-account-adjustment-list'},
							$T.div({class: 'component-account-adjustment-list-title'},
								$T.img({src: Component_Account_Adjustment_List.ICON_IMAGE_SOURCE, alt: 'Adjustments', title: 'Adjustments'}),
								$T.span('Adjustments')
							),
							$T.table({class: 'component-account-adjustment-list-table reflex highlight-rows listing-fw3'},
								$T.thead(
									// Column headings
									$T.tr({class: 'component-account-adjustment-list-headerrow'},
										$T.th('Date'),
										$T.th('Code'),
										$T.th({class: 'component-account-adjustment-list-amount'},
											'Amount ($)'
										),
										$T.th($T.span()), // Nature
										$T.th($T.span()) // Reversed
									)
								),
								this._oTBody = $T.tbody({class: 'alternating'},
									this._createNoRecordsRow(true)
								)
							),
							$T.div({class: 'component-account-adjustment-list-pagination'},
								$T.ul({class: 'reset horizontal'},
									$T.li(
										$T.button({class: 'component-account-adjustment-list-paginationbutton'},
											$T.img({src: sButtonPathBase + 'first.png'})
										)
									),
									$T.li(
										$T.button({class: 'component-account-adjustment-list-paginationbutton'},
											$T.img({src: sButtonPathBase + 'previous.png'})
										)
									),
									$T.li(
										$T.button({class: 'component-account-adjustment-list-paginationbutton'},
											$T.img({src: sButtonPathBase + 'next.png'})
										)
									),
									$T.li(
										$T.button({class: 'component-account-adjustment-list-paginationbutton'},
											$T.img({src: sButtonPathBase + 'last.png'})
										)
									)
								)
							)
						);
		
		// Bind events to the pagination buttons
		var aBottomPageButtons	= this._oElement.select('.component-account-adjustment-list-paginationbutton');
		aBottomPageButtons[0].observe('click', this._changePage.bind(this, 'firstPage'));
		aBottomPageButtons[1].observe('click', this._changePage.bind(this, 'previousPage'));
		aBottomPageButtons[2].observe('click', this._changePage.bind(this, 'nextPage'));
		aBottomPageButtons[3].observe('click', this._changePage.bind(this, 'lastPage'));

		// Setup pagination button object
		this.oPaginationButtons = {
			oBottom	: {
				oFirstPage		: aBottomPageButtons[0],
				oPreviousPage	: aBottomPageButtons[1],
				oNextPage		: aBottomPageButtons[2],
				oLastPage		: aBottomPageButtons[3]
			}
		};

		if (this._oContainerDiv)
		{
			this._oContainerDiv.appendChild(this._oElement);
		}
		
		// Default sort & filter
		this._oFilter.setFilterValue('account_id', this._iAccountId);
		this._oSort.registerField('effective_date', Sort.DIRECTION_DESC);
		
		this.refresh();
	},

	_showLoading	: function(bShow)
	{
		if (bShow)
		{
			this._oOverlay.attachTo(this._oTBody);
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
		if (!oResultSet || oResultSet.intTotalResults === 0 || oResultSet.arrResultSet.length === 0)
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
				oTBody.appendChild(this._createTableRow(aData[i], parseInt(i, 10), oResultSet.intTotalResults));
			}
		}

		this._updatePagination();
		this._showLoading(false);
	},

	_createNoRecordsRow	: function(bOnLoad)
	{
		return	$T.tr(
					$T.td({class: 'no-rows', colspan: 9},
						(bOnLoad ? '' : 'There are no adjustments to display')
					)
				);
	},

	_createTableRow	: function(oData, iPosition, iTotalResults)
	{
		if (oData.adjustment_id)
		{
			var oActionIcon = $T.img();
			if (oData.is_reversed)
			{
				// Has been reversed
				oActionIcon.src	= Component_Account_Adjustment_List.REVERSED_IMAGE_SOURCE;
				oActionIcon.alt	= 'Reversed';
			}
			else
			{
				// Can be reversed
				oActionIcon.src = Component_Account_Adjustment_List.REVERSE_IMAGE_SOURCE;
				oActionIcon.alt	= 'Reverse this Adjustment';
				oActionIcon.observe('click', this._reverseAdjustment.bind(this, oData.adjustment_id));
				oActionIcon.addClassName('pointer');
			}

			oActionIcon.title	= oActionIcon.alt;
			var oEffectiveDate	= Date.$parseDate(oData.effective_date, 'Y-m-d');
			var	oTR	=	$T.tr(
							$T.td(oEffectiveDate ? oEffectiveDate.$format('d-m-Y') : 'N/A'),
							$T.td(oData.adjustment_type_code),
							$T.td({class: 'component-account-adjustment-list-amount Currency'},
								Number(oData.amount).toFixed(2)
							),
							$T.td(oData.transaction_nature_code),
							$T.td(oActionIcon)
						);
	
			// Tooltip content
			var hTooltipContent = {};
			
			if (oData.extra_detail_enabled)
			{
				hTooltipContent['Adjustment Id'] = oData.adjustment_id;
			}
			
			hTooltipContent['Requested By'] = oData.created_employee_name;
			hTooltipContent['Approved By'] = oData.reviewed_employee_name;
			
			if (oData.service_id)
			{
				if (oData.extra_detail_enabled)
				{
					hTooltipContent['Service'] = oData.service_id;
				}
				
				hTooltipContent['Service FNN'] = oData.service_fnn;
			}

			// Note
			//oData.note	= "FAKE NOTE CONTENT\n\nHere is some multiline stuff.  Some really long lines should teach you a lesson!";
			if (oData.note) {
				hTooltipContent.Note = $T.div({'class':'component-account-adjustment-list-tooltip-note'}, oData.note);
			}
			
			hTooltipContent['Status'] = oData.adjustment_status_description;
			
			this._oTooltip.registerRow(oTR, hTooltipContent);
			
			return oTR;
		}
		else
		{
			// Invalid, return empty row
			return $T.tr();
		}
	},

	_updatePagination : function(iPageCount)
	{
		// Update the 'disabled' state of each pagination button
		this.oPaginationButtons.oBottom.oFirstPage.disabled 	= true;
		this.oPaginationButtons.oBottom.oPreviousPage.disabled	= true;
		this.oPaginationButtons.oBottom.oNextPage.disabled 		= true;
		this.oPaginationButtons.oBottom.oLastPage.disabled 		= true;

		if (iPageCount == undefined)
		{
			// Get the page count
			this.oPagination.getPageCount(this._updatePagination.bind(this));
		}
		else
		{
			if (this.oPagination.intCurrentPage != Pagination.PAGE_FIRST)
			{
				// Enable the first and previous buttons
				this.oPaginationButtons.oBottom.oFirstPage.disabled		= false;
				this.oPaginationButtons.oBottom.oPreviousPage.disabled 	= false;
			}
			if (this.oPagination.intCurrentPage < (iPageCount - 1) && iPageCount)
			{
				// Enable the next and last buttons
				this.oPaginationButtons.oBottom.oNextPage.disabled 	= false;
				this.oPaginationButtons.oBottom.oLastPage.disabled 	= false;
			}
		}
	},
	
	_reverseAdjustment : function(iAdjustmentId)
	{
		new Popup_Account_Adjustment_Reverse(iAdjustmentId, this._reverseComplete.bind(this));
	},
	
	_reverseComplete : function()
	{
		this._showLoading(true);
		this.oPagination.getCurrentPage();
		
		// Refresh account details, if defined
		if (Vixen.AccountDetails)
		{
			Vixen.AccountDetails.CancelEdit();
		}

		// Refresh account collections summary, if defined
		if (typeof Component_Account_Collections != 'undefined')
		{
			Component_Account_Collections.refreshInstances();
		}
		
		// Refresh account invoice list, if defined
		if (typeof Component_Account_Invoice_List != 'undefined')
		{
			Component_Account_Invoice_List.refreshInstances();
		}
	}
});

// Static

Object.extend(Component_Account_Adjustment_List,
{
	DATA_SET_DEFINITION			: {sObject: 'Adjustment', sMethod: 'getApprovedDataset'},
	MAX_RECORDS_PER_PAGE		: 10,
	REQUIRED_CONSTANT_GROUPS	: [],
	ICON_IMAGE_SOURCE			: '../admin/img/template/payment.png',
	REVERSED_IMAGE_SOURCE		: '../admin/img/template/reversed.png',
	REVERSE_IMAGE_SOURCE		: '../admin/img/template/delete.png',
	
	_aInstances					: [],
	
	_ajaxError : function(oResponse)
	{
		var sMessage = (oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.');
		Reflex_Popup.alert(sMessage, {sTitle: 'Error'});
	},
	
	refreshInstances : function()
	{
		for (var i = 0; i < Component_Account_Adjustment_List._aInstances.length; i++)
		{
			if (Component_Account_Adjustment_List._aInstances[i] instanceof Component_Account_Adjustment_List)
			{
				Component_Account_Adjustment_List._aInstances[i].refresh();
			}
		}
	}
});
