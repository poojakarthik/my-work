
var Component_Correspondence_Ledger_For_Account = Class.create(
{
	initialize	: function(oContainerDiv, iAccountId)
	{
		this._hFilters					= {};
		this._oContainerDiv				= oContainerDiv;
		this._bFirstLoadComplete		= false;
		this._hControlOnChangeCallbacks	= {};
		
		// Create DataSet & pagination object
		this.oDataSet	= new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, Component_Correspondence_Ledger_For_Account.DATA_SET_DEFINITION);
		
		this.oPagination	= new Pagination(this._updateTable.bind(this), Component_Correspondence_Ledger_For_Account.MAX_RECORDS_PER_PAGE, this.oDataSet);
		
		// Create filter object
		this._oFilter	=	new Filter(
								this.oDataSet, 
								this.oPagination, 
								this._filterFieldUpdated.bind(this) // On field value change
							);
		
		// Add all filter fields
		for (var sFieldName in Component_Correspondence_Ledger_For_Account.FILTER_FIELDS)
		{
			if (Component_Correspondence_Ledger_For_Account.FILTER_FIELDS[sFieldName].iType)
			{
				this._oFilter.addFilter(sFieldName, Component_Correspondence_Ledger_For_Account.FILTER_FIELDS[sFieldName]);
			}
		}
		
		this._oFilter.setFilterValue('account_id', iAccountId);
		
		// Create sort object
		this._oSort	= new Sort(this.oDataSet, this.oPagination, true);
		
		// Create the page HTML
		var sButtonPathBase	= '../admin/img/template/resultset_';
		this._oContentDiv 	= 	$T.div({class: 'correspondence-ledger-for-run'},
									// All
									$T.div({class: 'section'},
										$T.div({class: 'section-header'},
											$T.div({class: 'section-header-title'},
												$T.span('Correspondence Items'),
												$T.span({class: 'pagination-info'},
													''
												)
											),
											$T.div({class: 'section-header-options'}
												
											)
										),
										$T.div({class: 'section-content section-content-fitted'},
											$T.table({class: 'reflex highlight-rows'},
												$T.thead(
													// Column headings
													$T.tr(
														this._createFieldHeader('Id', 'id', false),
														this._createFieldHeader('Correspondence Run', 'correspondence_run_id', false),
														this._createFieldHeader('Customer Group', 'customer_group_name', false),
														this._createFieldHeader('Method', 'correspondence_delivery_method_name', false),
														//this._createFieldHeader('Account Name', 'account_name', false),
														this._createFieldHeader('Addressee'),
														this._createFieldHeader('Address'),
														//this._createFieldHeader('Email', 'email', false),
														//this._createFieldHeader('Mobile', 'mobile', false),
														//this._createFieldHeader('Landline', 'landline', false),
														this._createFieldHeader('')	// Actions
													),
													// Filter values
													$T.tr(
														$T.th(),
														$T.th(),
														this._createFilterValueElement('customer_group_id', 'Customer Group'),
														this._createFilterValueElement('correspondence_delivery_method_id', 'Delivery Method'),
														//$T.th(),
														$T.th(),
														$T.th(),
														//$T.th(),
														//$T.th(),
														//$T.th(),
														$T.th()
													)
												),
												$T.tbody({class: 'alternating'},
													this._createNoRecordsRow(true)
												)
											)
										),
										$T.div({class: 'section-footer'}, 
											$T.span({class: 'loading'},
												'Loading...'
											),
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
									)
								);
		
		// Bind events to the pagination buttons
		var aBottomPageButtons 	= this._oContentDiv.select('div.section-footer button');
		
		// First
		aBottomPageButtons[0].observe('click', this._changePage.bind(this, 'firstPage'));
		
		//Previous		
		aBottomPageButtons[1].observe('click', this._changePage.bind(this, 'previousPage'));
		
		// Next
		aBottomPageButtons[2].observe('click', this._changePage.bind(this, 'nextPage'));
		
		// Last
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
		oContainerDiv.appendChild(this._oContentDiv);
		
		// Send the initial sorting parameters to dataset ajax 
		this._oSort.refreshData(true);
		this._oFilter.refreshData(true);
		this.oPagination.getCurrentPage();
	},
	
	_showLoading	: function(bShow)
	{
		var oLoading	= this._oContentDiv.select('span.loading').first();
		if (bShow)
		{
			oLoading.show();
		}
		else
		{
			oLoading.hide();
		}
	},
	
	_changePage	: function(sFunction)
	{
		this._showLoading(true);
		this.oPagination[sFunction]();
	},
	
	_updateTable	: function(oResultSet)
	{
		var oTBody = this._oContentDiv.select('table > tbody').first();
		
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
			$T.td({class: 'no-rows', colspan: 0},
				(bOnLoad ? 'Loading...' : 'There are no records to display')
			)
		);
	},
	
	_createTableRow	: function(oItem)
	{
		if (oItem.id !== null)
		{
			// Delivery Method
			var oDeliveryMethodTD	=	$T.td(
											$T.div(oItem.correspondence_delivery_method_name)
										);
			switch (oItem.correspondence_delivery_method_id)
			{
				case $CONSTANT.CORRESPONDENCE_DELIVERY_METHOD_POST:
					// Add nothing, address is always shown
					break;
				case $CONSTANT.CORRESPONDENCE_DELIVERY_METHOD_EMAIL:
					// Add email
					var sEmail	= ((oItem.email && oItem.email) !== '' ? oItem.email : 'Not Supplied');
					oDeliveryMethodTD.appendChild(
						$T.div(sEmail)
					);
					break;
				case $CONSTANT.CORRESPONDENCE_DELIVERY_METHOD_SMS:
					// Add both landline and mobile numbers
					if (oItem.landline)
					{
						oDeliveryMethodTD.appendChild(
							$T.div('L: ' + oItem.landline)
						);
					}
					
					if (oItem.mobile)
					{
						oDeliveryMethodTD.appendChild(
							$T.div('M: ' + oItem.mobile)
						);
					}
					break;
			}
			
			var	oTR	=	$T.tr(
							$T.td(oItem.id),
							$T.td(oItem.correspondence_run_id),
							$T.td(oItem.customer_group_name),
							oDeliveryMethodTD,
							$T.td(
								(oItem.title ? oItem.title : '') + 
								' ' + (oItem.first_name ? oItem.first_name : '') + 
								' ' + (oItem.last_name ? oItem.last_name : '')
							),
							$T.td(
								$T.div(oItem.address_line_1), 
								$T.div(oItem.address_line_2 ? oItem.address_line_2 : ''), 
								$T.div(oItem.suburb), 
								$T.div(oItem.postcode + ' ' + oItem.state)
							),
							//$T.td(oItem.email),
							//$T.td(oItem.mobile),
							//$T.td(oItem.landline),
							$T.td(this._getItemActions(oItem))
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
		for (var sField in Component_Correspondence_Ledger_For_Account.SORT_FIELDS)
		{
			if (this._oSort.isRegistered(sField))
			{
				var oSortImg	= this._oContentDiv.select('th.header > img.sort-' + sField).first();
				var iDirection	= this._oSort.getSortDirection(sField);
				oSortImg.src	= Component_Correspondence_Ledger_For_Account.SORT_IMAGE_SOURCE[iDirection];
				oSortImg.show();
			}
		}
	},
	
	_updateFilters	: function()
	{
		for (var sField in Component_Correspondence_Ledger_For_Account.FILTER_FIELDS)
		{
			if (Component_Correspondence_Ledger_For_Account.FILTER_FIELDS[sField].iType)
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
			var oSpan	= this._oContentDiv.select('th.filter-heading > span.filter-' + sField).first();
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
		var oDeleteImage				= $T.img({class: 'filter-delete', src: Component_Correspondence_Ledger_For_Account.REMOVE_FILTER_IMAGE_SOURCE, alt: 'Remove Filter', title: 'Remove Filter'});
		oDeleteImage.style.visibility	= 'hidden';
		oDeleteImage.observe('click', this._clearFilterValue.bind(this, sField));
		
		var oFiterImage	= $T.img({class: 'header-filter', src: Component_Correspondence_Ledger_For_Account.FILTER_IMAGE_SOURCE, alt: 'Filter by ' + sLabel, title: 'Filter by ' + sLabel});
		this._oFilter.registerFilterIcon(sField, oFiterImage, sLabel, this._oContainerDiv, 0, 22);
		
		return	$T.th({class: 'filter-heading'},
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
	
	_createFieldHeader	: function(sLabel, sSortField, bMultiLine)
	{
		var oSortImg	= $T.img({class: 'sort-' + (sSortField ? sSortField : '')});
		var oTH			= 	$T.th({class: 'header' + (bMultiLine ? '-multiline' : '')},
								oSortImg,
								$T.span(sLabel)
							);
		oSortImg.hide();
		
		// Optional sort field
		if (sSortField)
		{
			var oSpan	= oTH.select('span').first();
			oSpan.addClassName('header-sort');
			
			this._oSort.registerToggleElement(oSpan, sSortField, Component_Correspondence_Ledger_For_Account.SORT_FIELDS[sSortField]);
			this._oSort.registerToggleElement(oSortImg, sSortField, Component_Correspondence_Ledger_For_Account.SORT_FIELDS[sSortField]);
		}
				
		return oTH;
	},
	
	_getItemActions	: function(oItem)
	{
		var oUL		= $T.ul({class: 'reset horizontal actions'});
		var oView	= $T.img({class: 'pointer', src: Component_Correspondence_Ledger_For_Account.ACTION_VIEW_IMAGE_SOURCE, alt: 'View Additional Columns', title: 'View Additional Columns'});
		oView.observe('click', this._viewDetailsPopup.bind(this, oItem));
		oUL.appendChild($T.li(oView));
		return oUL;
	},
	
	_viewDetailsPopup	: function(oItem)
	{
		new Popup_Correspondence_Data(oItem.id);
	},
	
	_filterFieldUpdated	: function(sField)
	{
		// Make sure the from date has 00:00 (start of day) for minutes and the to date has 23:59 (end of day)
		// so that both days are included in the search
		if (sField.match(/due_datetime/))
		{
			var oValue	= this._oFilter.getFilterValue(sField);
			if (oValue)
			{
				if (oValue.mFrom)
				{
					oValue.mFrom	= 	oValue.mFrom.replace(
											Component_Correspondence_Ledger_For_Account.RANGE_FILTER_DATE_REGEX, 
											'$1 ' + Component_Correspondence_Ledger_For_Account.RANGE_FILTER_FROM_MINUTES
										);
				}
				
				if (oValue.mTo)
				{
					oValue.mTo	= 	oValue.mTo.replace(
										Component_Correspondence_Ledger_For_Account.RANGE_FILTER_DATE_REGEX, 
										'$1 ' + Component_Correspondence_Ledger_For_Account.RANGE_FILTER_TO_MINUTES
									);
				}
				
				this._oFilter.setFilterValue(sField, oValue.mFrom, oValue.mTo, null, true);
			}
		}
	},
	
	_formatFilterValueForDisplay	: function(sField, mValue)
	{
		var oDefinition	= Component_Correspondence_Ledger_For_Account.FILTER_FIELDS[sField];
		var aControls	= this._oFilter.getControlsForField(sField);
		var sValue		= '';
		switch (sField)
		{
			case 'customer_group_id':
			case 'correspondence_delivery_method_id':
				// Control Field Select
				var oControl	= aControls[0];
				if (oControl.bPopulated)
				{
					sValue	= oControl.getElementText();
					
					// Remove the onchange callback, if it was used to update this filter value
					if (typeof this._hControlOnChangeCallbacks[sField] != 'undefined')
					{
						oControl.removeOnChangeCallback(this._hControlOnChangeCallbacks[sField]);
						delete this._hControlOnChangeCallbacks[sField];
					}
				}
				else
				{
					// Set change handler
					var iCallbackIndex	=	oControl.addOnChangeCallback(
												this._updateFilterDisplayValue.bind(this, sField)
											);
					this._hControlOnChangeCallbacks[sField]	= iCallbackIndex;
					sValue	= 'loading...';
				}
				break;
		}
		
		return sValue;
	},
});

// Static

Object.extend(Component_Correspondence_Ledger_For_Account, 
{
	MAX_RECORDS_PER_PAGE		: 5,
	EDIT_IMAGE_SOURCE			: '../admin/img/template/pencil.png',
	FILTER_IMAGE_SOURCE			: '../admin/img/template/table_row_insert.png',
	REMOVE_FILTER_IMAGE_SOURCE	: '../admin/img/template/delete.png',

	ACTION_VIEW_IMAGE_SOURCE	: '../admin/img/template/magnifier.png',

	RANGE_FILTER_DATE_REGEX		: /^(\d{4}-\d{2}-\d{2})(\s\d{2}:\d{2}:\d{2})?$/,
	RANGE_FILTER_FROM_MINUTES	: '00:00:00',
	RANGE_FILTER_TO_MINUTES		: '23:59:59',

	DATA_SET_DEFINITION			: {sObject: 'Correspondence', sMethod: 'getDataSet'},

	// Helper functions
	_validateDueDate	: function(sValue)
	{
		if (isNaN(Date.parse(sValue.replace(/-/g, '/'))))
		{
			return false;
		}
		else
		{
			return true;
		}
	},

	getDateTimeElement	: function(sMySQLDate)
	{
		if (sMySQLDate === null)
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

	formatDateTimeFilterValue	: function(sDateTime)
	{
		var oDate	= Date.$parseDate(sDateTime, 'Y-m-d H:i:s');
		return oDate.$format('j/m/y');
	},


	getPrePrintedOptions	: function(fnCallback)
	{
		fnCallback(
			[
	      	 	$T.option({value: 0}, 
	      	 		'No'
	      	 	),
	      	 	$T.option({value: 1}, 
	      	 		'Yes'
	      	 	)
	      	]
	    );
	},
	
	//Sorting definitions
	SORT_FIELDS	:	{
						id									: Sort.DIRECTION_ASC,
						correspondence_run_id				: Sort.DIRECTION_OFF,
						customer_group_name					: Sort.DIRECTION_OFF,
						correspondence_delivery_method_name	: Sort.DIRECTION_OFF,
						account_name						: Sort.DIRECTION_OFF,
						email								: Sort.DIRECTION_OFF,
						mobile								: Sort.DIRECTION_OFF,
						landline							: Sort.DIRECTION_OFF
					},
});

Component_Correspondence_Ledger_For_Account.SORT_IMAGE_SOURCE						= {};
Component_Correspondence_Ledger_For_Account.SORT_IMAGE_SOURCE[Sort.DIRECTION_OFF]	= '../admin/img/template/order_neither.png';
Component_Correspondence_Ledger_For_Account.SORT_IMAGE_SOURCE[Sort.DIRECTION_ASC]	= '../admin/img/template/order_asc.png';
Component_Correspondence_Ledger_For_Account.SORT_IMAGE_SOURCE[Sort.DIRECTION_DESC]	= '../admin/img/template/order_desc.png';

// Filter Control field definitions
Component_Correspondence_Ledger_For_Account.YEAR_MINIMUM	= 2010;
Component_Correspondence_Ledger_For_Account.YEAR_MAXIMUM	= Component_Correspondence_Ledger_For_Account.YEAR_MINIMUM + 5;

var oNow	= new Date();
Component_Correspondence_Ledger_For_Account.FILTER_FIELDS	= 
{
	account_id	: 
	{
		iType	: Filter.FILTER_TYPE_VALUE
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
	correspondence_delivery_method_id	: 
	{
		iType	: Filter.FILTER_TYPE_VALUE,
		oOption	: 	
		{
			sType		: 'select',
			mDefault	: null,
			oDefinition	:	
			{
				sLabel		: 'Delivery Method',
				mEditable	: true,
				mMandatory	: false,
				fnValidate	: null,
				fnPopulate	: Correspondence_Delivery_Method.getAllAsSelectOptions
			}
		}
	}
};
