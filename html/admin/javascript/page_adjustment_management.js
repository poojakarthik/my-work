
var Page_Adjustment_Management = Class.create(
{
	initialize	: function(oContainerDiv, oLoadingPopup)
	{
		this._hFilters				= {};
		this._bFirstLoadComplete	= false;
		this._oElement				= $T.div({class: 'page-adjustment-management'});
		this._oLoadingPopup			= oLoadingPopup;
		this._oContainerDiv			= oContainerDiv;
		
		this._iSelectMode			= Page_Adjustment_Management.SELECT_MODE_NONE;
		this._iSelectPageNumber		= null;
		this._iSelectCount			= null;
		this._hSelectedRecords		= {};
		this._hDeselectedRecords	= {};
		
		// Load constants then create UI
		Flex.Constant.loadConstantGroup(Page_Adjustment_Management.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
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
	
	// Protected
	
	_buildUI : function()
	{
		// Create Dataset & pagination object
		this.oDataSet		= 	new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, Page_Adjustment_Management.DATA_SET_DEFINITION);
		this.oPagination 	= 	new Pagination(
									this._updateTable.bind(this), 
									Page_Adjustment_Management.MAX_RECORDS_PER_PAGE, 
									this.oDataSet
								);
		
		// Create Dataset filter object
		this._oFilter	=	new Filter(
								this.oDataSet,
								this.oPagination,
								this._showLoading.bind(this, true) 	// On field value change
							);

		// Add all filter fields
		for (var sFieldName in Page_Adjustment_Management.FILTER_FIELDS)
		{
			if (Page_Adjustment_Management.FILTER_FIELDS[sFieldName].iType)
			{
				this._oFilter.addFilter(sFieldName, Page_Adjustment_Management.FILTER_FIELDS[sFieldName]);
			}
		}

		// Create Dataset sort object
		this._oSort	= new Sort(this.oDataSet, this.oPagination, true, this._showLoading.bind(this, true));
		
		// Create the page HTML
		var sButtonPathBase	= '../admin/img/template/resultset_';
		var oSection		= new Section(true);
		this._oSection 		= oSection;
		this._oElement.appendChild(oSection.getElement());
		
		// Title
		oSection.setTitleContent(
			$T.span(
				$T.span('Adjustment Requests'),
				$T.span({class: 'pagination-info'},
					''
				)
			)
		);
		
		// Main content -- table
		oSection.setContent(
			$T.table({class: 'page-adjustment-management-table reflex highlight-rows'},
				$T.thead(
					// Column headings
					$T.tr({class: 'page-adjustment-management-headerrow'},
						$T.th(),
						$T.th('Adjustment Type'),
						$T.th('Amount ($ inc GST)'),
						$T.th('Nature'),
						$T.th('Account Id'),
						$T.th('Name'),
						$T.th('Service'),
						$T.th('Requested By'),
						$T.th('Requested On'),
						$T.th('') // Actions
					)
				),
				$T.tbody({class: 'alternating'},
					this._createNoRecordsRow(true)
				)
			)
		);
		
		// Register sort headers
		// ... account id
		var aHeaderRowTHs = this._oElement.select('.page-adjustment-management-headerrow > th');
		this._registerSortHeader(aHeaderRowTHs[1], 'adjustment_type_description');
		this._registerSortHeader(aHeaderRowTHs[2], 'amount');
		this._registerSortHeader(aHeaderRowTHs[3], 'transaction_nature_name');
		this._registerSortHeader(aHeaderRowTHs[4], 'account_id');
		this._registerSortHeader(aHeaderRowTHs[5], 'account_name');
		this._registerSortHeader(aHeaderRowTHs[6], 'service_fnn');
		this._registerSortHeader(aHeaderRowTHs[7], 'created_employee_name');
		this._registerSortHeader(aHeaderRowTHs[8], 'effective_date');
		
		// Footer -- loading msg & pagination
		oSection.setFooterContent(
			$T.ul({class: 'reset horizontal page-adjustment-management-options'},
				$T.li({class: 'loading'},
					'Loading...'
				),
				$T.li({class: 'page-adjustment-management-selectoptions'},
					$T.div(
						$T.span('Select: '),
						$T.a('All on page').observe('click', this._selectAllOnPage.bind(this)),
						$T.span(' | '),
						$T.a('None').observe('click', this._deselectAll.bind(this, true))
					)
				),
				$T.li({class: 'page-adjustment-management-selectactions'},
					$T.div(
						$T.span('With Selected:'),
						$T.button({class: 'icon-button'},
							$T.img({src: Page_Adjustment_Management.APPROVE_IMAGE_SOURCE}),
							$T.span('Approve')
						).observe('click', this._actionSelected.bind(this, Page_Adjustment_Management.ACTION_APPROVE)),
						$T.button({class: 'icon-button'},
							$T.img({src: Page_Adjustment_Management.REJECT_IMAGE_SOURCE}),
							$T.span('Reject')
						).observe('click', this._actionSelected.bind(this, Page_Adjustment_Management.ACTION_REJECT))
					)
				),
				$T.li(
					$T.button({class: 'page-adjustment-management-paginationbutton'},
						$T.img({src: sButtonPathBase + 'first.png'})
					)
				),
				$T.li(
					$T.button({class: 'page-adjustment-management-paginationbutton'},
						$T.img({src: sButtonPathBase + 'previous.png'})
					)
				),
				$T.li(
					$T.button({class: 'page-adjustment-management-paginationbutton'},
						$T.img({src: sButtonPathBase + 'next.png'})
					)
				),
				$T.li(
					$T.button({class: 'page-adjustment-management-paginationbutton'},
						$T.img({src: sButtonPathBase + 'last.png'})
					)
				)
			)
		);

		// Bind events to the pagination buttons
		var aBottomPageButtons 	= this._oElement.select('.page-adjustment-management-paginationbutton');
		aBottomPageButtons[0].observe('click', this._changePage.bind(this, 'firstPage'));
		aBottomPageButtons[1].observe('click', this._changePage.bind(this, 'previousPage'));
		aBottomPageButtons[2].observe('click', this._changePage.bind(this, 'nextPage'));
		aBottomPageButtons[3].observe('click', this._changePage.bind(this, 'lastPage'));

		// Setup pagination button object
		this.oPaginationButtons = 
		{
			oBottom	: 
			{
				oFirstPage		: aBottomPageButtons[0],
				oPreviousPage	: aBottomPageButtons[1],
				oNextPage		: aBottomPageButtons[2],
				oLastPage		: aBottomPageButtons[3]
			}
		};
		
		// Load the initial dataset
		this._oSort.refreshData(true);
		this._oFilter.refreshData(true);
		this._refreshPage();
		
		this._oContainerDiv.appendChild(this._oElement);
	},

	_showLoading	: function(bShow)
	{
		var oLoading	= this._oElement.select('.loading').first();
		if (!oLoading)
		{
			return;
		}
		else if (bShow)
		{
			oLoading.show();
		}
		else
		{
			oLoading.hide();
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

		this._bFirstLoadComplete = true;
		this._updatePagination();
		this._updateSorting();
		this._updateFilters();

		this._showLoading(false);
		if (this._oLoadingPopup)
		{
			this._oLoadingPopup.hide();
			delete this._oLoadingPopup;
		}
	},

	_createNoRecordsRow	: function(bOnLoad)
	{
		return $T.tr(
			$T.td({class: 'no-rows', colspan: 0},
				(bOnLoad ? 'Loading...' : 'There are no records to display')
			)
		);
	},

	_createTableRow	: function(oData, iPosition, iTotalResults)
	{
		if (oData.id)
		{
			var oCheckbox = null;
			var oActionTD = $T.td({class: 'page-adjustment-management-actions'});
			
			var oCheckbox = $T.input({type: 'checkbox', class: 'page-adjustment-management-tobeactioned'});
			oCheckbox.observe('change', this._actionCheckboxChanged.bind(this));
			oCheckbox.iRecordId = oData.id;
			
			// Check select mode, select if necessary
			var bChecked = false;
			switch (this._iSelectMode)
			{
				case Page_Adjustment_Management.SELECT_MODE_PAGE:
					if (this._iSelectPageNumber == this.oPagination.intCurrentPage)
					{
						// Is on the page chosen to have all selected 
						bChecked = true;
						break;
					}
					else
					{
						// Different page, clear the select mode
						this._iSelectMode 		= Page_Adjustment_Management.SELECT_MODE_NONE;
						this._iSelectPageNumber	= null;
					}
				
				case Page_Adjustment_Management.SELECT_MODE_NONE:
					if (this._hSelectedRecords[oCheckbox.iRecordId])
					{
						bChecked = true;
					}
					break;
			}
			
			if (bChecked && !this._hDeselectedRecords[oCheckbox.iRecordId])
			{
				oCheckbox.checked 							= true;
				this._hSelectedRecords[oCheckbox.iRecordId]	= true;
			}
			
			var oCreatedDate = Date.$parseDate(oData.effective_date, 'Y-m-d');

			//oData.note	= "FAKE NOTE CONTENT\n\nHere is some multiline stuff.  Some really long lines should teach you a lesson!";
			var	oNoteIcon	= $T.img({'class':'page-adjustment-management-note', src:Page_Adjustment_Management.NOTE_IMAGE_SOURCE, alt:'View Note', title:'View Note'});
			if (oData.note) {
				oNoteIcon.observe('click', Reflex_Popup.alert.curry($T.div({'class':'page-adjustment-management-note-details'}, oData.note), {
					sTitle			: 'Note for ' + oData.adjustment_type_code + ' from ' + oCreatedDate.$format('d/m/Y') + ' for ' + oData.account_id,
					iWidth			: 35,
					sButtonLabel	: 'Close',
					sIconSource		: Page_Adjustment_Management.NOTE_IMAGE_SOURCE
				}));
			} else {
				oNoteIcon.addClassName('-no-content');
				oNoteIcon.setAttribute('alt', 'No Note Supplied');
				oNoteIcon.setAttribute('title', 'No Note Supplied');
			}
			oActionTD.appendChild(oNoteIcon);
			
			var oApproveIcon	= $T.img({class: 'pointer', src: Page_Adjustment_Management.APPROVE_IMAGE_SOURCE, alt: 'Approve This Adjustment', title: 'Approve This Adjustment'}).observe('click', this._actionSingle.bind(this, Page_Adjustment_Management.ACTION_APPROVE, oData.id))
			var oRejectIcon 	= $T.img({class: 'pointer', src: Page_Adjustment_Management.REJECT_IMAGE_SOURCE, alt: 'Reject This Adjustment', title: 'Reject This Adjustment'}).observe('click', this._actionSingle.bind(this, Page_Adjustment_Management.ACTION_REJECT, oData.id))
			oActionTD.appendChild(oApproveIcon);
			oActionTD.appendChild(oRejectIcon);
			
			var	oTR	=	$T.tr(
							$T.td(oCheckbox),
							$T.td(oData.adjustment_type_description + ' (' + oData.adjustment_type_code + ')'),
							$T.td({class: 'page-adjustment-management-amount'},
								oData.amount
							),
							$T.td(oData.transaction_nature_name),
							$T.td(
								$T.a({href: 'flex.php/Account/Overview/?Account.Id=' + oData.account_id},
									oData.account_id
								)
							),
							$T.td(oData.account_name),
							$T.td(oData.service_fnn),
							$T.td(oData.created_employee_name),
							$T.td(oCreatedDate ? oCreatedDate.$format('d/m/Y') : ''),
							oActionTD
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
			var oPageInfo		= this._oElement.select('span.pagination-info').first();
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
		for (var sField in Page_Adjustment_Management.SORT_FIELDS)
		{
			if (this._oSort.isRegistered(sField))
			{
				var oSortImg	= this._oElement.select('img.sort-' + sField).first();
				var iDirection	= this._oSort.getSortDirection(sField);
				oSortImg.src	= Page_Adjustment_Management.SORT_IMAGE_SOURCE[iDirection];
				oSortImg.show();
			}
		}
	},

	_updateFilters	: function()
	{
		for (var sField in Page_Adjustment_Management.FILTER_FIELDS)
		{
			if (Page_Adjustment_Management.FILTER_FIELDS[sField].iType)
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
			var oSpan	= this._oElement.select('th.page-adjustment-management-filter-heading > span.filter-' + sField).first();
			if (oSpan)
			{
				var oDeleteImage	= oSpan.up().select('img.page-adjustment-management-filter-delete').first();
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

	_createFilterValueElement : function(sField, sLabel)
	{
		var oDeleteImage				= $T.img({class: 'page-adjustment-management-filter-delete', src: Page_Adjustment_Management.REMOVE_FILTER_IMAGE_SOURCE, alt: 'Remove Filter', title: 'Remove Filter'});
		oDeleteImage.style.visibility	= 'hidden';
		oDeleteImage.observe('click', this._clearFilterValue.bind(this, sField));

		var oFiterImage	= $T.img({class: 'header-filter', src: Page_Adjustment_Management.FILTER_IMAGE_SOURCE, alt: 'Filter by ' + sLabel, title: 'Filter by ' + sLabel});
		this._oFilter.registerFilterIcon(sField, oFiterImage, sLabel, this._oElement, 0, 10);

		return	$T.th({class: 'page-adjustment-management-filter-heading'},
					$T.span({class: 'filter-' + sField},
						'All'
					),
					$T.div(
						oFiterImage,
						oDeleteImage
					)
				);
	},

	_clearFilterValue : function(sField)
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
		
		oSpan.addClassName('page-adjustment-management-header-sort');
		
		this._oSort.registerToggleElement(oSpan, sSortField, Page_Adjustment_Management.SORT_FIELDS[sSortField]);
	},
	
	_formatFilterValueForDisplay	: function(sField, mValue)
	{
		var oDefinition	= Page_Adjustment_Management.FILTER_FIELDS[sField];
		var aControls	= this._oFilter.getControlsForField(sField);
		var sValue		= '';
		switch (sField)
		{
			// No filtering, see another pagination component for examples on how to format filter values
		}

		return sValue;
	},
	
	_getSelectedRecordIds : function()
	{
		var aIds = [];
		for (var iRecordId in this._hSelectedRecords)
		{
			if (this._hSelectedRecords[iRecordId])
			{
				aIds.push(parseInt(iRecordId));
			}
		}
		
		return aIds;
	},
	
	_getDeselectedRecordIds : function()
	{
		var aIds = [];
		for (var iRecordId in this._hDeselectedRecords)
		{
			aIds.push(parseInt(iRecordId));
		}
		return aIds;
	},
	
	_actionSingle : function(iAction, iRecordId)
	{
		this._actionItems(iAction, [iRecordId]);
	},
	
	_actionSelected : function(iAction)
	{
		var aRecordIds = [];
		switch (this._iSelectMode)
		{
			case Page_Adjustment_Management.SELECT_MODE_PAGE:
			case Page_Adjustment_Management.SELECT_MODE_NONE:
				// Use all that have been selected on any page
				aRecordIds = this._getSelectedRecordIds();
				break;
		}
		
		if (aRecordIds.length == 0)
		{
			var sAction = '';
			switch (iAction)
			{
				case Page_Adjustment_Management.ACTION_APPROVE:
					sAction = 'Approve';
					break;
				case Page_Adjustment_Management.ACTION_REJECT:
					sAction = 'Reject';
					break;
			}
			
			Reflex_Popup.alert('There are no Adjustments selected to ' + sAction + '.')
			return;
		}
		
		this._actionItems(iAction, aRecordIds);
	},
	
	_actionItems : function(iAction, aRecordIds, oResponse)
	{
		new Popup_Adjustment_Management_Action_Adjustment(aRecordIds, iAction, this._actionItemsComplete.bind(this));
	},
	
	_refreshPage : function()
	{
		this._showLoading(true);
		this.oPagination.getCurrentPage();
	},
	
	_actionItemsComplete : function()
	{
		this._deselectAll(false);
		this._refreshPage();
	},
	
	_selectAllOnPage : function()
	{
		this._iSelectMode 			= Page_Adjustment_Management.SELECT_MODE_PAGE;
		this._iSelectPageNumber		= this.oPagination.intCurrentPage;
		this._hSelectedRecords 		= {};
		this._hDeselectedRecords	= {};
		this._refreshPage();
	},
	
	_deselectAll : function(bReloadPage, oEvent)
	{
		this._iSelectMode 			= Page_Adjustment_Management.SELECT_MODE_NONE;
		this._hSelectedRecords 		= {};
		this._hDeselectedRecords	= {};
		
		if (bReloadPage)
		{
			this._refreshPage();
		}
	},
	
	_actionCheckboxChanged	: function(oEvent)
	{
		var iRecordId	= oEvent.target.iRecordId;
		var bChecked 	= !!oEvent.target.checked;
		
		if (bChecked)
		{
			// Selected
			this._hDeselectedRecords[iRecordId]	= false;
			this._hSelectedRecords[iRecordId] 	= true;
		}
		else
		{
			// Deselected
			this._hDeselectedRecords[iRecordId]	= true;
			this._hSelectedRecords[iRecordId] 	= false;
		}
	}
});

// Static

Object.extend(Page_Adjustment_Management,
{
	REQUIRED_CONSTANT_GROUPS	: ['adjustment_status'],
	DATA_SET_DEFINITION			: {sObject: 'Adjustment', sMethod: 'getPendingDataset'},
	MAX_RECORDS_PER_PAGE		: 20,
	
	FILTER_IMAGE_SOURCE			: '../admin/img/template/table_row_insert.png',
	REMOVE_FILTER_IMAGE_SOURCE	: '../admin/img/template/delete.png',
	APPROVE_IMAGE_SOURCE		: '../admin/img/template/approve.png',
	REJECT_IMAGE_SOURCE			: '../admin/img/template/decline.png',
	NOTE_IMAGE_SOURCE			: '../admin/img/template/note.png',
	
	ACTION_APPROVE	: 1,
	ACTION_REJECT	: 2,
	
	SELECT_MODE_NONE	: 1,
	SELECT_MODE_PAGE	: 2,
	
	SORT_FIELDS	:	
	{
		adjustment_type_description	: Sort.DIRECTION_ASC,
		amount						: Sort.DIRECTION_OFF,
		transaction_nature_name		: Sort.DIRECTION_OFF,
		account_id					: Sort.DIRECTION_OFF,
		account_name				: Sort.DIRECTION_OFF,
		service_fnn					: Sort.DIRECTION_OFF,
		created_employee_name		: Sort.DIRECTION_OFF,
		effective_date				: Sort.DIRECTION_OFF
	},
	
	_ajaxError : function(oResponse)
	{
		var sMessage = (oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.');
		Reflex_Popup.alert(sMessage, {sTitle: 'Error'});
	},
		
	_getConstantGroupOptions : function(sConstantGroup, fnCallback)
	{
		var aOptions		= [];
		var aConstantGroup 	= Flex.Constant.arrConstantGroups[sConstantGroup];
		for (var iId in aConstantGroup)
		{
			aOptions.push(
				$T.option({value: iId},
					aConstantGroup[iId].Name
				)
			);
		}
		fnCallback(aOptions);
	},
	
	_formatDateTimeFilterValue : function(sDateTime)
	{
		var oDate = Date.$parseDate(sDateTime, 'Y-m-d H:i:s');
		return oDate.$format('j/m/y h:i');
	}
});

Page_Adjustment_Management.SORT_IMAGE_SOURCE						= {};
Page_Adjustment_Management.SORT_IMAGE_SOURCE[Sort.DIRECTION_OFF]	= '../admin/img/template/order_neither.png';
Page_Adjustment_Management.SORT_IMAGE_SOURCE[Sort.DIRECTION_ASC]	= '../admin/img/template/order_asc.png';
Page_Adjustment_Management.SORT_IMAGE_SOURCE[Sort.DIRECTION_DESC]	= '../admin/img/template/order_desc.png';

//Filter Control field definitions (starts at 
Page_Adjustment_Management.FILTER_FIELDS	=
{
	adjustment_status_id :
	{
		iType : Filter.FILTER_TYPE_VALUE
	}
};

