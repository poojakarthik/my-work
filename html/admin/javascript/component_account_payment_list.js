
var Component_Account_Payment_List = Class.create(
{
	initialize	: function(iAccountId, oContainerDiv, iPageSize)
	{
		this._register();
		
		this._iAccountId	= iAccountId;
		this._oContainerDiv	= oContainerDiv;
		
		var oNow = new Date();
		oNow.shift(-1, 'years');
		this._iOneYearAgo = oNow.getTime();
		
		this._oTooltip = new Component_List_Tooltip(20);
		
		// Load constants then create UI
		Flex.Constant.loadConstantGroup(Component_Account_Payment_List.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
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
	
	refresh : function()
	{
		// Load the initial dataset
		this._oSort.refreshData(true);
		this._oFilter.refreshData(true);
		this.oPagination.getCurrentPage();
		this._showLoading(true);
	},
	
	deregister : function()
	{
		var iIndex = Component_Account_Payment_List._aInstances.indexOf(this);
		Component_Account_Payment_List._aInstances.splice(iIndex, 1);
	},
	
	// Protected
	
	_register : function()
	{
		Component_Account_Payment_List._aInstances.push(this);
	},
	
	_buildUI : function()
	{
		// Create Dataset & pagination object
		this.oDataSet		= 	new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, Component_Account_Payment_List.DATA_SET_DEFINITION);
		this.oPagination 	= 	new Pagination(
									this._updateTable.bind(this), 
									(this._iPageSize ? this._iPageSize : Component_Account_Payment_List.MAX_RECORDS_PER_PAGE), 
									this.oDataSet
								);
		
		this._oOverlay = new Reflex_Loading_Overlay();
		
		// Create Dataset filter object
		this._oFilter = new Filter(this.oDataSet, this.oPagination);
		this._oFilter.addFilter('account_id', {iType: Filter.FILTER_TYPE_VALUE});
		
		// Create Dataset sort object
		this._oSort	= new Sort(this.oDataSet, this.oPagination, false, this._showLoading.bind(this, true));
		
		// Create the page HTML
		var sButtonPathBase	= '../admin/img/template/resultset_';
		
		this._oElement = $T.div({class: 'component-account-payment-list'},
							$T.div({class: 'component-account-payment-list-title'},
								$T.img({src: Component_Account_Payment_List.ICON_IMAGE_SOURCE, alt: 'Payments', title: 'Payments'}),
								$T.span('Payments')
							),
							$T.table({class: 'component-account-payment-list-table reflex highlight-rows listing-fw3'},
								$T.thead(
									// Column headings
									$T.tr({class: 'component-account-payment-list-headerrow'},
										$T.th('Date'),
										$T.th('Type'),
										$T.th({class: 'component-account-payment-list-amount'},
											'Amount ($)'
										),
										$T.th($T.span()) // Reversed
									)
								),
								this._oTBody = $T.tbody({class: 'alternating'},
									this._createNoRecordsRow(true)
								)
							),
							$T.div({class: 'component-account-payment-list-pagination'},
								$T.ul({class: 'reset horizontal'},
									$T.li(
										$T.button({class: 'component-account-payment-list-paginationbutton'},
											$T.img({src: sButtonPathBase + 'first.png'})
										)
									),
									$T.li(
										$T.button({class: 'component-account-payment-list-paginationbutton'},
											$T.img({src: sButtonPathBase + 'previous.png'})
										)
									),
									$T.li(
										$T.button({class: 'component-account-payment-list-paginationbutton'},
											$T.img({src: sButtonPathBase + 'next.png'})
										)
									),
									$T.li(
										$T.button({class: 'component-account-payment-list-paginationbutton'},
											$T.img({src: sButtonPathBase + 'last.png'})
										)
									)
								)
							)
						);
		
		// Bind events to the pagination buttons
		var aBottomPageButtons 	= this._oElement.select('.component-account-payment-list-paginationbutton');
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
		
		// Default filter
		this._oFilter.setFilterValue('account_id', this._iAccountId);
		
		// Default sort
		this._oSort.registerField('paid_date', Sort.DIRECTION_DESC);
		this._oSort.registerField('created_datetime', Sort.DIRECTION_DESC);
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
		return 	$T.tr(
					$T.td({class: 'no-rows', colspan: 9},
						(bOnLoad ? '' : 'There are no payments to display')
					)
				);
	},

	_createTableRow	: function(oData, iPosition, iTotalResults)
	{
		if (oData.payment_id)
		{
			var bIsLessThanAYearOld = Date.$parseDate(oData.paid_date, 'Y-m-d').getTime() > this._iOneYearAgo;			
			var oActionIcon 		= $T.img();
			if (oData.is_reversed)
			{
				// Has been reversed
				oActionIcon.src	= Component_Account_Payment_List.REVERSED_IMAGE_SOURCE;
				oActionIcon.alt	= 'Reversed';
			}
			else if (bIsLessThanAYearOld)
			{
				// Can be reversed
				oActionIcon.src = Component_Account_Payment_List.REVERSE_IMAGE_SOURCE;
				oActionIcon.alt	= 'Reverse this Payment';
				oActionIcon.observe('click', this._reversePayment.bind(this, oData.payment_id));
				oActionIcon.addClassName('pointer');
			}
			else
			{
				// Can be reversed but is over a year old
				oActionIcon.src = Component_Account_Payment_List.OLD_IMAGE_SOURCE;
				oActionIcon.alt	= 'Cannot Reverse';
				oActionIcon.observe('click', this._cannotReverse.bind(this));
				oActionIcon.addClassName('pointer');
			}

			oActionIcon.title = oActionIcon.alt;
			
			var	oTR	=	$T.tr(
							$T.td(Date.$parseDate(oData.paid_date, 'Y-m-d').$format('d-m-Y')),
							$T.td(oData.payment_type_name),
							$T.td({class: 'component-account-payment-list-amount Currency'},
								new Number(oData.amount).toFixed(2)
							),
							$T.td(oActionIcon)
						);
			
			// Tooltip content
			var hTooltipContent = {};
			
			if (oData.extra_detail_enabled)
			{
				hTooltipContent['Payment Id'] = oData.payment_id;
			}
			
			// Payment Type
			hTooltipContent['Payment Type'] = oData.payment_type_name;
			
			// If there is a file import date associated with the payment, then include this too 
			if (oData.imported_datetime !== null)
			{
				hTooltipContent['Imported On'] = Date.$parseDate(oData.imported_datetime, 'Y-m-d H:i:s').$format('d/m/Y');
			}
			
			// EnteredBy (created_employee_name)
			if (oData.created_employee_name !== null)
			{
				hTooltipContent['Entered By'] = oData.created_employee_name;
			}
						
			// Amount applied
			hTooltipContent['Amount Applied ($)'] = new Number(oData.is_reversed ? 0 : (oData.amount - oData.balance)).toFixed(2);
			
			// Balance
			hTooltipContent['Balance ($)'] = new Number(oData.balance).toFixed(2);
			
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
	
	_reversePayment : function(iPaymentId)
	{
		new Popup_Account_Payment_Reverse(iPaymentId, this._reverseComplete.bind(this));
	},
	
	_cannotReverse : function()
	{
		Reflex_Popup.alert(
			'This payment cannot be reversed because it was paid over 12 months ago. Please contact YBS if you wish to reverse it.',
			{iWidth : 35}
		);
	},
	
	_reverseComplete : function()
	{
		this.oPagination.getCurrentPage();
		
		// Refresh account details, if defined
		if (Vixen.AccountDetails)
		{
			Vixen.AccountDetails.CancelEdit();
		}
		
		// Refresh account collections summary, if defined
		if (Component_Account_Collections)
		{
			Component_Account_Collections.refreshInstances();
		}
		
		// Refresh account invoice list, if defined
		if (Component_Account_Invoice_List)
		{
			Component_Account_Invoice_List.refreshInstances();
		}
	}
});

// Static

Object.extend(Component_Account_Payment_List,
{
	DATA_SET_DEFINITION			: {sObject: 'Payment', sMethod: 'getDataset'},
	MAX_RECORDS_PER_PAGE		: 10,
	REQUIRED_CONSTANT_GROUPS	: [],
	ICON_IMAGE_SOURCE			: '../admin/img/template/payment.png',
	REVERSED_IMAGE_SOURCE		: '../admin/img/template/reversed.png',
	REVERSE_IMAGE_SOURCE		: '../admin/img/template/delete.png',
	OLD_IMAGE_SOURCE			: '../admin/img/template/etech_payment_notice.png',
	
	_aInstances					: [],
	
	_ajaxError : function(oResponse)
	{
		var sMessage = (oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.');
		Reflex_Popup.alert(sMessage, {sTitle: 'Error'});
	},
	
	refreshInstances : function()
	{
		for (var i = 0; i < Component_Account_Payment_List._aInstances.length; i++)
		{
			if (Component_Account_Payment_List._aInstances[i] instanceof Component_Account_Payment_List)
			{
				Component_Account_Payment_List._aInstances[i].refresh();
			}
		}
	}
});
