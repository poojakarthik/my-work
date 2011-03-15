
var Component_Collections_Account_Management = Class.create(
{
	initialize	: function(oContainerDiv, oLoadingPopup)
	{
		this._hFilters					= {};
		this._oContainerDiv				= oContainerDiv;
		this._bFirstLoadComplete		= false;
		this._hControlOnChangeCallbacks	= {};
		this._oLoadingPopup				= oLoadingPopup;
		
		Flex.Constant.loadConstantGroup(Component_Collections_Account_Management.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
	},
	
	_buildUI : function()
	{
		// Create Dataset & pagination object
		this.oDataSet		= new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, Component_Collections_Account_Management.DATA_SET_DEFINITION);
		this.oPagination	= new Pagination(this._updateTable.bind(this), Component_Collections_Account_Management.MAX_RECORDS_PER_PAGE, this.oDataSet, false);

		// Create Dataset filter object
		this._oFilter =	new Filter(
							this.oDataSet,
							this.oPagination,
							this._showLoading.bind(this, true),	// On field value change,
							false								// Don't force page count refresh on filter change
						);

		// Add all filter fields
		for (var sFieldName in Component_Collections_Account_Management.FILTER_FIELDS)
		{
			if (Component_Collections_Account_Management.FILTER_FIELDS[sFieldName].iType)
			{
				this._oFilter.addFilter(sFieldName, Component_Collections_Account_Management.FILTER_FIELDS[sFieldName]);
			}
		}
		
		// Create Dataset sort object
		this._oSort	= new Sort(this.oDataSet, this.oPagination, true, this._showLoading.bind(this, true));
		
		this._oOverlay = new Reflex_Loading_Overlay();
		
		// Create the page HTML
		var sButtonPathBase	= 	'../admin/img/template/resultset_';
		var oSection		= 	new Section(true);
		this._oContentDiv 	= 	$T.div({class: 'component-collections-account-management'},
									oSection.getElement()
								);
		
		// Title
		oSection.setTitleContent(
			$T.span(
				$T.span('Accounts'),
				$T.span({class: 'pagination-info'},
					''
				)
			)
		);
		
		oSection.addToHeaderOptions($T.button('Export to File').observe('click', this._exportToFile.bind(this, null)));
		
		// Main content -- table
		oSection.setContent(
			$T.table({class: 'reflex highlight-rows'},
				$T.thead(
					// Column headings
					$T.tr({class: 'component-collections-account-management-headerrow'},
						$T.th({rowspan: 2},
							'Account'
						),
						$T.th({rowspan: 2},
							'Customer Group'
						),
						$T.th({rowspan: 2},
							'Scenario'
						),
						$T.th('Balance'),
						$T.th('Aging of Debt (Days Overdue)'),
						$T.th({rowspan: 2},
							'Last Payment'
						),
						$T.th({rowspan: 2},
							'Status'
						),
						$T.th({rowspan: 2},
							'Suspension'
						),
						$T.th('Promise to Pay Instalments'),
						$T.th({rowspan: 2},
							'Current Event'
						)
					),
					// Extra columns
					$T.tr({class: 'component-collections-account-management-extracolumns'},
						$T.th(
							$T.ul({class: 'component-collections-account-management-balance-columns reset horizontal'},
								$T.li('Total'),
								$T.li('Overdue')
							)
						),
						$T.th(
							$T.ul({class: 'component-collections-account-management-overdue-columns reset horizontal'},
								$T.li('1-30'),
								$T.li('30-60'),
								$T.li('60-90'),
								$T.li('90+')
							)
						),
						$T.th(
							$T.ul({class: 'component-collections-account-management-promise-columns reset horizontal'},
								$T.li('Previous'),
								$T.li('Next Due')
							)
						)
					),
					// Filter values
					$T.tr(
						this._createFilterValueElement('account_id', 'Account'),
						this._createFilterValueElement('customer_group_id', 'Customer Group'),
						this._createFilterValueElement('collection_scenario_id', 'Scenario'),
						$T.th(),
						$T.th(),
						$T.th(),
						this._createFilterValueElement('collection_status', 'Status'),
						$T.th(),
						$T.th(),
						$T.th()
					)
				),
				$T.tbody({class: 'alternating'},
					this._createNoRecordsRow(true)
				)
			)
		);
		
		this._oTBody = this._oContentDiv.select('tbody').first();
		
		// Register sort headers
		// ... account id
		var aHeaderRowTHs = this._oContentDiv.select('.component-collections-account-management-headerrow > th');
		this._registerSortHeader(aHeaderRowTHs[0], 'account_id');
		this._registerSortHeader(aHeaderRowTHs[1], 'customer_group_name');
		this._registerSortHeader(aHeaderRowTHs[2], 'scenario_name');
		
		// ... balance & overdue balance
		var aBalanceHeaderTHs = this._oContentDiv.select('.component-collections-account-management-balance-columns > li');
		this._registerSortHeader(aBalanceHeaderTHs[0], 'balance');
		this._registerSortHeader(aBalanceHeaderTHs[1], 'overdue_balance');
		
		// .. aging of debt
		var aAgingHeaderTHs = this._oContentDiv.select('.component-collections-account-management-overdue-columns > li');
		this._registerSortHeader(aAgingHeaderTHs[0], 'overdue_amount_from_1_30');
		this._registerSortHeader(aAgingHeaderTHs[1], 'overdue_amount_from_30_60');
		this._registerSortHeader(aAgingHeaderTHs[2], 'overdue_amount_from_60_90');
		this._registerSortHeader(aAgingHeaderTHs[3], 'overdue_amount_from_90_on');
		
		// ... promise to pay
		var aPromiseToPayTHs = this._oContentDiv.select('.component-collections-account-management-promise-columns > li');
		this._registerSortHeader(aPromiseToPayTHs[0], 'previous_promise_instalment_due_date');
		this._registerSortHeader(aPromiseToPayTHs[1], 'next_promise_instalment_due_date');
		
		// Footer -- loading msg & pagination
		oSection.setFooterContent(
			$T.div(
				$T.div(
					$T.button(
						$T.img({src: sButtonPathBase + 'first.png'})
					),
					$T.button(
						$T.img({src: sButtonPathBase + 'previous.png'})
					),
					$T.button(
						$T.img({src: sButtonPathBase + 'next.png'})
					),
					$T.button(
						$T.img({src: sButtonPathBase + 'last.png'})
					)
				)
			)
		);

		// Bind events to the pagination buttons
		var aBottomPageButtons 	= this._oContentDiv.select('div.section-footer button');
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
		
		// Attach content and get data
		this._oContainerDiv.appendChild(this._oContentDiv);

		// Default collection_status
		this._oFilter.setFilterValue('collection_status', 'IN_COLLECTIONS');
		
		// Load the initial dataset
		this._oSort.refreshData(true);
		this._oFilter.refreshData(true);
		this.oPagination.getCurrentPage();
		
		// Load the event types (for when an event needs to be actioned)
		Component_Collections_Account_Management._cacheAllEventTypes();
		
		if (this._oLoadingPopup)
		{
			this._oLoadingPopup.hide();
			delete this._oLoadingPopup;
		}
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
		var oTBody = this._oContentDiv.select('table > tbody').first();

		// Remove all existing rows
		while (oTBody.firstChild)
		{
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
				oTBody.appendChild(this._createTableRow(aData[i]));
			}
		}

		this._bFirstLoadComplete	= true;
		this._updatePagination();
		this._updateSorting();
		this._updateFilters();

		this._showLoading(false);
	},

	_createNoRecordsRow	: function(bOnLoad)
	{
		return $T.tr(
			$T.td({class: 'component-collections-account-management-no-rows', colspan: 0},
				(bOnLoad ? '' : 'There are no accounts to display')
			)
		);
	},

	_createTableRow	: function(oData)
	{
		if (oData.account_id)
		{
			// Account
			var oAccountElement = $T.td({class: 'component-collections-account-management-account'});
			if (oData.account_id && oData.account_name)
			{
				var sUrl = 'flex.php/Account/Overview/?Account.Id=' + oData.account_id;
				oAccountElement.appendChild(
					$T.div({class: 'popup-followup-detail-subdetail account'},
						$T.div({class: 'account-id'},
							$T.a({href: sUrl},
								oData.account_id + ': '
							)
						),
						$T.a({class: 'account-name', href: sUrl},
							oData.account_name
						)
					)
				);
			}
			
			// Balance
			var oBalanceElement =	$T.td(
										$T.ul({class: 'component-collections-account-management-balance-columns reset horizontal'},
											$T.li(Component_Collections_Account_Management._getCurrencyElement(oData.balance)),
											$T.li(Component_Collections_Account_Management._getCurrencyElement(oData.overdue_balance))
										)
									);
			
			// Overdue
			var oOverdueElement = $T.td();
			if (oData.overdue_balance)
			{
				oOverdueElement = 	$T.td(
										$T.ul({class: 'component-collections-account-management-overdue-columns reset horizontal'},
											$T.li(Component_Collections_Account_Management._getCurrencyElement(oData.overdue_amount_from_1_30)),
											$T.li(Component_Collections_Account_Management._getCurrencyElement(oData.overdue_amount_from_30_60)),
											$T.li(Component_Collections_Account_Management._getCurrencyElement(oData.overdue_amount_from_60_90)),
											$T.li(Component_Collections_Account_Management._getCurrencyElement(oData.overdue_amount_from_90_on))
										)
									);
			}
			
			// Suspension
			var oSuspensionElement = $T.td();
			if (oData.current_suspension_end_datetime)
			{
				oSuspensionElement =	$T.td(
											$T.span('Until '),
											Component_Collections_Account_Management._getDateTimeElement(oData.current_suspension_end_datetime)
										);
			}
			
			// Promise
			var oPromiseElement = 	$T.td(
										$T.ul({class: 'component-collections-account-management-promise-columns reset horizontal'},
											$T.li(Component_Collections_Account_Management._getDateTimeElement(oData.previous_promise_instalment_due_date)),
											$T.li(Component_Collections_Account_Management._getDateTimeElement(oData.next_promise_instalment_due_date))
										)
									);
			
			// Current event
			var oCurrentEventElement = $T.td();
			if (oData.account_collection_event_history_id)
			{
				// There is a current/last event
				oCurrentEventElement = $T.td($T.span(oData.collection_event_name));
				if (oData.account_collection_event_status_id == $CONSTANT.ACCOUNT_COLLECTION_EVENT_STATUS_COMPLETED)
				{
					// Current event is the last completed event
					oCurrentEventElement.appendChild(
						$T.div(
							$T.span('Completed on '), 
							Component_Collections_Account_Management._getDateTimeElement(oData.completed_datetime)
						)
					);
				}
				else if (oData.collection_event_invocation_id == $CONSTANT.COLLECTION_EVENT_INVOCATION_MANUAL)
				{
					// Not completed and is manual, allow completion
					var oCompleteButton = 	$T.button({class: 'icon-button'},
												$T.img({src: '../admin/img/template/approve.png'}),
												$T.span('Complete')
											);
					oCompleteButton.observe('click', this._completeEvent.bind(this, oData));
					oCurrentEventElement.appendChild($T.div(oCompleteButton));
				}
			}
			
			var	oTR	=	$T.tr(
							oAccountElement,
							$T.td(oData.customer_group_name),
							$T.td(oData.scenario_name),
							oBalanceElement,
							oOverdueElement,
							$T.td(
								$T.span(
									Component_Collections_Account_Management._getCurrencyElement(oData.last_payment_amount),
									$T.span(' on ')
								),
								Component_Collections_Account_Management._getDateElement(oData.last_payment_paid_date)
							),
							$T.td(Component_Collections_Account_Management._getStatusElement(oData.collection_status)),
							oSuspensionElement,
							oPromiseElement,
							oCurrentEventElement
						);
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
			// Update Page ? of ?, show 1 for page count if it is 0 because there is technically still a page even though it's empty
			var oPageInfo		= this._oContentDiv.select('span.pagination-info').first();
			oPageInfo.innerHTML	= '(Page '+ (this.oPagination.intCurrentPage + 1) +' of ' + (iPageCount == 0 ? 1 : iPageCount) + ')';

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

	_updateSorting	: function()
	{
		for (var sField in Component_Collections_Account_Management.SORT_FIELDS)
		{
			if (this._oSort.isRegistered(sField))
			{
				var oSortImg	= this._oContentDiv.select('img.sort-' + sField).first();
				var iDirection	= this._oSort.getSortDirection(sField);
				oSortImg.src	= Component_Collections_Account_Management.SORT_IMAGE_SOURCE[iDirection];
				oSortImg.show();
			}
		}
	},

	_updateFilters	: function()
	{
		for (var sField in Component_Collections_Account_Management.FILTER_FIELDS)
		{
			if (Component_Collections_Account_Management.FILTER_FIELDS[sField].iType)
			{
				this._updateFilterDisplayValue(sField);
			}
		}
	},

	_updateFilterDisplayValue	: function(sField)
	{
		if (this._oFilter.isRegistered(sField))
		{
			var mValue	= this._oFilter.getFilterValue(sField);
			var oSpan	= this._oContentDiv.select('th.component-collections-account-management-filter-heading > span.filter-' + sField).first();
			if (oSpan)
			{
				var oDeleteImage	= oSpan.up().select('img.filter-delete').first();
				if (mValue !== null && (typeof mValue !== 'undefined'))
				{
					// Value, show it
					oSpan.innerHTML					= this._formatFilterValueForDisplay(sField, mValue);
					oDeleteImage.style.visibility	= 'visible';
				}
				else
				{
					// No value, hide delete image
					oSpan.innerHTML					= 'All';
					oDeleteImage.style.visibility	= 'hidden';
				}
			}
		}
	},

	_createFilterValueElement	: function(sField, sLabel)
	{
		var oDeleteImage				= $T.img({class: 'filter-delete', src: Component_Collections_Account_Management.REMOVE_FILTER_IMAGE_SOURCE, alt: 'Remove Filter', title: 'Remove Filter'});
		oDeleteImage.style.visibility	= 'hidden';
		oDeleteImage.observe('click', this._clearFilterValue.bind(this, sField));

		var oFiterImage	= $T.img({class: 'header-filter', src: Component_Collections_Account_Management.FILTER_IMAGE_SOURCE, alt: 'Filter by ' + sLabel, title: 'Filter by ' + sLabel});
		this._oFilter.registerFilterIcon(sField, oFiterImage, sLabel, this._oContentDiv, 0, 10);

		return	$T.th({class: 'component-collections-account-management-filter-heading'},
					$T.span({class: 'filter-' + sField},
						'All'
					),
					$T.div(
						oFiterImage,
						oDeleteImage
					)
				);
	},

	_clearFilterValue	: function(sField)
	{
		this._oFilter.clearFilterValue(sField);
		this._oFilter.refreshData();
	},

	_registerSortHeader : function(oElement, sSortField)
	{
		var oSortImg = $T.img({class: 'sort-' + (sSortField ? sSortField : '')});
		oElement.insertBefore(oSortImg, oElement.firstChild);
		
		var oSpan = oElement.select('span').first();
		if (!oSpan)
		{
			oSpan = oElement;
		}
		
		oSpan.addClassName('component-collections-account-management-header-sort');
		
		this._oSort.registerToggleElement(oSpan, sSortField, Component_Collections_Account_Management.SORT_FIELDS[sSortField]);
		this._oSort.registerToggleElement(oSortImg, sSortField, Component_Collections_Account_Management.SORT_FIELDS[sSortField]);
	},
	
	_formatFilterValueForDisplay	: function(sField, mValue)
	{
		var oDefinition	= Component_Collections_Account_Management.FILTER_FIELDS[sField];
		var aControls	= this._oFilter.getControlsForField(sField);
		var sValue		= '';
		switch (sField)
		{
			case 'account_id':
				sValue = mValue;
				break;
			case 'customer_group_id':
			case 'collection_scenario_id':
			case 'collection_status':
				// Return the text (display) value of the control field
				sValue = aControls[0].getElementText();
				break;
		}

		return sValue;
	},
	
	_completeEvent : function(oData)
	{
		Collection_Event_Type.getInstance(
			oData.collection_event_type_implementation_id, 
			[oData.account_collection_event_history_id],
			this._eventInvocationComplete.bind(this)
		);
	},
	
	_eventInvocationComplete : function()
	{
		this._showLoading(true);
		this.oPagination.getCurrentPage();
	},
	
	_exportToFile : function(sFileType, oResponse, oEvent)
	{
		if (!sFileType)
		{
			new Popup_Select_Spreadsheet_File_Type('Please choose the type of file that you want the results to be saved in.', this._exportToFile.bind(this));
			return;
		}
		
		if (!oResponse)
		{
			this._oLoadingPopup = new Reflex_Popup.Loading('Generating File...');
			this._oLoadingPopup.display();
			
			// Request
			var fnResp	= this._exportToFile.bind(this, sFileType);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collections', 'generateAccountLedgerFile');
			fnReq(this._oSort.getSortData(), this._oFilter.getFilterData(), sFileType);
			return;
		}
		
		this._oLoadingPopup.hide();
		delete this._oLoadingPopup;
		
		if (!oResponse.bSuccess)
		{
			// Error
			Component_Barring_Action_Ledger._ajaxError(oResponse);
			return;
		}

		// Redirect to download the file
		window.location = 'reflex.php/Collections/DownloadLedgerFile/' + oResponse.sFilename + '/?Type=' + oResponse.sMIME;
	}
});

// Static

Object.extend(Component_Collections_Account_Management,
{
	REQUIRED_CONSTANT_GROUPS	: ['collection_event_type_implementation', 'account_collection_event_status', 'collection_event_invocation'],
	DATA_SET_DEFINITION			: {sObject: 'Collections', sMethod: 'getAccounts'},
	MAX_RECORDS_PER_PAGE		: 10,
	
	FILTER_IMAGE_SOURCE				: '../admin/img/template/table_row_insert.png',
	REMOVE_FILTER_IMAGE_SOURCE		: '../admin/img/template/delete.png',
	DETAILS_ACCOUNT_IMAGE_SOURCE	: '../admin/img/template/account.png',
	ACTION_VIEW_IMAGE_SOURCE		: '../admin/img/template/magnifier.png',
	
	collection_status :
	{
		'NOT_IN_COLLECTIONS'	: 'Not In Collections',
		'IN_COLLECTIONS'		: 'In Collections',
		'PROMISE_TO_PAY'		: 'Promise To Pay',
		'SUSPENDED'				: 'Suspended'
	},
	
	// Sorting definitions
	SORT_FIELDS	:	
	{
		account_id 								: Sort.DIRECTION_OFF,
		customer_group_name						: Sort.DIRECTION_OFF,
		balance									: Sort.DIRECTION_OFF,
		overdue_balance							: Sort.DIRECTION_DESC,
		overdue_amount_from_1_30				: Sort.DIRECTION_OFF,
		overdue_amount_from_30_60				: Sort.DIRECTION_OFF,
		overdue_amount_from_60_90				: Sort.DIRECTION_OFF,
		overdue_amount_from_90_on				: Sort.DIRECTION_OFF,
		previous_promise_instalment_due_date	: Sort.DIRECTION_OFF,
		next_promise_instalment_due_date		: Sort.DIRECTION_OFF,
		scenario_name							: Sort.DIRECTION_OFF
	},
		
	_ajaxError : function(oResponse)
	{
		var sMessage = (oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.');
		Reflex_Popup.alert(sMessage, {sTitle: 'Error'});
	},
	
	_getDateTimeElement	: function(sMySQLDate)
	{
		if (!sMySQLDate)
		{
			return $T.div('');
		}

		var oDate	= new Date(Date.parse(sMySQLDate.replace(/-/g, '/')));
		var sDate	= oDate.$format('d/m/Y');
		var sTime	= oDate.$format('h:i A');

		return 	$T.div(
					$T.div(sDate),
					$T.div({class: 'datetime-time'},
						sTime
					)
				);
	},

	_getDateElement	: function(sMySQLDate)
	{
		if (!sMySQLDate)
		{
			return $T.div('');
		}

		var oDate	= new Date(Date.parse(sMySQLDate.replace(/-/g, '/')));
		var sDate	= oDate.$format('d/m/Y');

		return 	$T.div(sDate);
	},
	
	_formatDateTimeFilterValue	: function(sDateTime)
	{
		var oDate	= Date.$parseDate(sDateTime, 'Y-m-d H:i:s');
		return oDate.$format('j/m/y');
	},

	_getStatusElement	: function(sStatus)
	{
		return $T.span(Component_Collections_Account_Management.collection_status[sStatus]);
	},
	
	_getCurrencyElement	: function(fAmount)
	{
		var fAbsAmount 		= Math.abs(fAmount);
		var oTypeElement	= null;
		if (fAmount < 0)
		{
			oTypeElement = 	$T.span({class: 'component-collections-account-management-currency-credit'},
								'CR'
							);
		}
		
		return 	$T.span({class: 'component-collections-account-management-currency'},
					$T.span('$' + fAbsAmount.toFixed(2)),
					oTypeElement
				);
	},

	_getAllScenariosAsOptions : function(fnCallback, oResponse)
	{
		if (!oResponse)
		{
			var fnResponse	= Component_Collections_Account_Management._getAllScenariosAsOptions.curry(fnCallback);
			var fnRequest 	= jQuery.json.jsonFunction(fnResponse, fnResponse, 'Collection_Scenario', 'getAll');
			fnRequest(false, true);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Component_Collections_Account_Management._ajaxError(oResponse);
			return;
		}
		
		var aOptions = [];
		if (!Object.isArray(oResponse.aScenarios))
		{
			for (var iId in oResponse.aScenarios)
			{
				aOptions.push(
					$T.option({value: iId},
						oResponse.aScenarios[iId].name
					)
				);
			}
		}
		fnCallback(aOptions);
	},
	
	_getAllCollectionsStatusesAsOptions	: function(fnCallback)
	{
		var aOptions = [];
		for (var sValue in Component_Collections_Account_Management.collection_status)
		{
			aOptions.push(
				$T.option({value: sValue},
					Component_Collections_Account_Management.collection_status[sValue]
				)
			);
		}
		fnCallback(aOptions);
	},
	
	_cacheAllEventTypes : function(fnCallback, oResponse)
	{
		if (!oResponse)
		{
			Collection_Event_Type.getAll(Component_Collections_Account_Management._cacheAllEventTypes.curry(fnCallback));
			return;
		}
		
		Component_Collections_Account_Management._hCollectionEventTypes = oResponse.aResults;
		
		if (fnCallback)
		{
			fnCallback();
		}
	}
});

Component_Collections_Account_Management.SORT_IMAGE_SOURCE						= {};
Component_Collections_Account_Management.SORT_IMAGE_SOURCE[Sort.DIRECTION_OFF]	= '../admin/img/template/order_neither.png';
Component_Collections_Account_Management.SORT_IMAGE_SOURCE[Sort.DIRECTION_ASC]	= '../admin/img/template/order_asc.png';
Component_Collections_Account_Management.SORT_IMAGE_SOURCE[Sort.DIRECTION_DESC]	= '../admin/img/template/order_desc.png';

// Filter Control field definitions
Component_Collections_Account_Management.YEAR_MINIMUM	= new Date().getFullYear();
Component_Collections_Account_Management.YEAR_MAXIMUM	= Component_Collections_Account_Management.YEAR_MINIMUM + 5;

var oNow	= new Date();
Component_Collections_Account_Management.FILTER_FIELDS	=
{
	account_id	:
	{
		iType	: Filter.FILTER_TYPE_VALUE,
		oOption	:
		{
			sType		: 'text',
			mDefault	: null,
			oDefinition	:
			{
				sLabel		: 'Account',
				mEditable	: true,
				mMandatory	: false,
				fnValidate	: null
			}
		}
	},
	collection_scenario_id	:
	{
		iType	: Filter.FILTER_TYPE_VALUE,
		oOption	:
		{
			sType		: 'select',
			mDefault	: null,
			oDefinition	:
			{
				sLabel		: 'Scenario',
				mEditable	: true,
				mMandatory	: false,
				fnValidate	: null,
				fnPopulate	: Component_Collections_Account_Management._getAllScenariosAsOptions
			}
		}
	},
	customer_group_id	:
	{
		iType	: Filter.FILTER_TYPE_VALUE,
		oOption	:
		{
			sType		: 'select',
			mDefault	: null,
			oDefinition	:
			{
				sLabel		: 'Customer Group',
				mEditable	: true,
				mMandatory	: false,
				fnValidate	: null,
				fnPopulate	: Customer_Group.getAllAsSelectOptions
			}
		}
	},
	collection_status	:
	{
		iType	: Filter.FILTER_TYPE_VALUE,
		oOption	:
		{
			sType		: 'select',
			mDefault	: null,
			oDefinition	:
			{
				sLabel		: 'Status',
				mEditable	: true,
				mMandatory	: false,
				fnValidate	: null,
				fnPopulate	: Component_Collections_Account_Management._getAllCollectionsStatusesAsOptions
			}
		}
	},
};

