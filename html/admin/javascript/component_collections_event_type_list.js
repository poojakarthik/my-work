
var Component_Collections_Event_Type_List = Class.create(
{
	initialize	: function(iPageSize, oLoadingPopup)
	{
		this._hFilters				= {};
		this._bFirstLoadComplete	= false;
		this._iPageSize				= iPageSize;
		this._oElement				= $T.div({class: 'component-collections-event-type-list'});
		this._oLoadingPopup			= oLoadingPopup;
		
		// Load constants then create UI
		Flex.Constant.loadConstantGroup(Component_Collections_Event_Type_List.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
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
		this.oDataSet		= 	new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, Component_Collections_Event_Type_List.DATA_SET_DEFINITION);
		this.oPagination 	= 	new Pagination(
									this._updateTable.bind(this), 
									(this._iPageSize ? this._iPageSize : Component_Collections_Event_Type_List.MAX_RECORDS_PER_PAGE), 
									this.oDataSet
								);
		
		// Create Dataset filter object
		this._oFilter	=	new Filter(
								this.oDataSet,
								this.oPagination,
								this._showLoading.bind(this, true) 	// On field value change
							);

		// Add all filter fields
		for (var sFieldName in Component_Collections_Event_Type_List.FILTER_FIELDS)
		{
			if (Component_Collections_Event_Type_List.FILTER_FIELDS[sFieldName].iType)
			{
				this._oFilter.addFilter(sFieldName, Component_Collections_Event_Type_List.FILTER_FIELDS[sFieldName]);
			}
		}

		// Create Dataset sort object
		this._oSort	= new Sort(this.oDataSet, this.oPagination, true, this._showLoading.bind(this, true));
		
		// Create second dataset for getting selected instance ids (including ones not on the current page)
		this._oDataSetSelection = new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, Component_Collections_Event_Type_List.DATA_SET_DEFINITION);
		
		// Create the page HTML
		var sButtonPathBase	= '../admin/img/template/resultset_';
		var oSection		= new Section(true);
		this._oSection 		= oSection;
		this._oElement.appendChild(oSection.getElement());
		
		// Title
		oSection.setTitleContent(
			$T.span(
				$T.span('Collections Event Types'),
				$T.span({class: 'pagination-info'},
					''
				)
			)
		);
		
		// Header options
		oSection.addToHeaderOptions(
			$T.button({class: 'icon-button'},
				$T.img({src: '../admin/img/template/new.png'}),
				$T.span('Add Event Type')	
			).observe('click', this._addEventType.bind(this))
		);
		
		// Main content -- table
		oSection.setContent(
			$T.table({class: 'component-collections-event-type-list-table reflex highlight-rows'},
				$T.thead(
					// Column headings
					$T.tr({class: 'component-collections-event-type-list-headerrow'},
						$T.th('#'),
						$T.th('Name'),
						$T.th('Description'),
						$T.th('System Name'),
						$T.th('Implementation'),
						$T.th('Is Manual'),
						$T.th('Status'),
						$T.th('') // Actions
					),
					// Filter values
					$T.tr(
						$T.th(),
						$T.th(),
						$T.th(),
						$T.th(),
						this._createFilterValueElement('collection_event_type_implementation_id', 'Implementation'),
						this._createFilterValueElement('collection_event_invocation_id', 'Is Manual'),
						this._createFilterValueElement('status_id', 'Status'),
						$T.th()
					)
				),
				$T.tbody({class: 'alternating'},
					this._createNoRecordsRow(true)
				)
			)
		);
		
		// Register sort headers
		// ... account id
		var aHeaderRowTHs = this._oElement.select('.component-collections-event-type-list-headerrow > th');
		this._registerSortHeader(aHeaderRowTHs[0], 'id');
		this._registerSortHeader(aHeaderRowTHs[1], 'name');
		this._registerSortHeader(aHeaderRowTHs[2], 'description');
		this._registerSortHeader(aHeaderRowTHs[3], 'system_name');
		this._registerSortHeader(aHeaderRowTHs[4], 'collection_event_type_implementation_name');
		this._registerSortHeader(aHeaderRowTHs[5], 'collection_event_invocation_name');
		this._registerSortHeader(aHeaderRowTHs[6], 'status_name');
		
		// Footer -- loading msg & pagination
		oSection.setFooterContent(
			$T.ul({class: 'reset horizontal component-collections-event-type-list-options'},
				$T.li({class: 'loading'},
					'Loading...'
				),
				$T.li(
					$T.button({class: 'component-collections-event-type-list-paginationbutton'},
						$T.img({src: sButtonPathBase + 'first.png'})
					)
				),
				$T.li(
					$T.button({class: 'component-collections-event-type-list-paginationbutton'},
						$T.img({src: sButtonPathBase + 'previous.png'})
					)
				),
				$T.li(
					$T.button({class: 'component-collections-event-type-list-paginationbutton'},
						$T.img({src: sButtonPathBase + 'next.png'})
					)
				),
				$T.li(
					$T.button({class: 'component-collections-event-type-list-paginationbutton'},
						$T.img({src: sButtonPathBase + 'last.png'})
					)
				)
			)
		);

		// Bind events to the pagination buttons
		var aBottomPageButtons 	= this._oElement.select('.component-collections-event-type-list-paginationbutton');
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

		// Load the initial dataset
		this._oSort.refreshData(true);
		this._oFilter.refreshData(true);
		this.oPagination.getCurrentPage();
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
			$T.td({class: 'no-rows', colspan: 9},
				(bOnLoad ? 'Loading...' : 'There are no records to display')
			)
		);
	},

	_createTableRow	: function(oData, iPosition, iTotalResults)
	{
		if (oData.id)
		{
			// Create the row element
			
			var sActionIconText = null;
			var sActionIconSrc 	= null;
			var fnActionOnClick	= null;
			if (oData.status_id == $CONSTANT.STATUS_ACTIVE)
			{
				sActionIconText = 'Disable';
				sActionIconSrc 	= '../admin/img/template/decline.png';
				fnActionOnClick	= this._disableEventType.bind(this, oData.id, false);
			}
			else
			{
				sActionIconText = 'Enable';
				sActionIconSrc 	= '../admin/img/template/approve.png';
				fnActionOnClick	= this._enableEventType.bind(this, oData.id);
			}
			
			var	oTR	=	$T.tr(
							$T.td(oData.id),
							$T.td(oData.name),
							$T.td(oData.description),
							$T.td(oData.system_name),
							$T.td(oData.collection_event_type_implementation_name),
							$T.td(oData.collection_event_invocation_name === null ? 'Optional' : oData.collection_event_invocation_name),
							$T.td(oData.status_name),
							$T.td(
								$T.img({class: 'pointer', src: sActionIconSrc, alt: sActionIconText, title: sActionIconText}).observe('click', fnActionOnClick)
							)
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
		for (var sField in Component_Collections_Event_Type_List.SORT_FIELDS)
		{
			if (this._oSort.isRegistered(sField))
			{
				var oSortImg	= this._oElement.select('img.sort-' + sField).first();
				var iDirection	= this._oSort.getSortDirection(sField);
				oSortImg.src	= Component_Collections_Event_Type_List.SORT_IMAGE_SOURCE[iDirection];
				oSortImg.show();
			}
		}
	},

	_updateFilters	: function()
	{
		for (var sField in Component_Collections_Event_Type_List.FILTER_FIELDS)
		{
			if (Component_Collections_Event_Type_List.FILTER_FIELDS[sField].iType)
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
			var oSpan	= this._oElement.select('th.component-collections-event-type-list-filter-heading > span.filter-' + sField).first();
			if (oSpan)
			{
				var oDeleteImage	= oSpan.up().select('img.component-collections-event-type-list-filter-delete').first();
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
		var oDeleteImage				= $T.img({class: 'component-collections-event-type-list-filter-delete', src: Component_Collections_Event_Type_List.REMOVE_FILTER_IMAGE_SOURCE, alt: 'Remove Filter', title: 'Remove Filter'});
		oDeleteImage.style.visibility	= 'hidden';
		oDeleteImage.observe('click', this._clearFilterValue.bind(this, sField));

		var oFiterImage	= $T.img({class: 'header-filter', src: Component_Collections_Event_Type_List.FILTER_IMAGE_SOURCE, alt: 'Filter by ' + sLabel, title: 'Filter by ' + sLabel});
		this._oFilter.registerFilterIcon(sField, oFiterImage, sLabel, this._oElement, 0, 10);

		return	$T.th({class: 'component-collections-event-type-list-filter-heading'},
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
		
		oSpan.addClassName('component-collections-event-type-list-header-sort');
		
		this._oSort.registerToggleElement(oSpan, sSortField, Component_Collections_Event_Type_List.SORT_FIELDS[sSortField]);
		this._oSort.registerToggleElement(oSortImg, sSortField, Component_Collections_Event_Type_List.SORT_FIELDS[sSortField]);
	},
	
	_formatFilterValueForDisplay	: function(sField, mValue)
	{
		var oDefinition	= Component_Collections_Event_Type_List.FILTER_FIELDS[sField];
		var aControls	= this._oFilter.getControlsForField(sField);
		var sValue		= '';
		switch (sField)
		{
			case 'collection_event_type_implementation_id':
			case 'collection_event_invocation_id':
			case 'status_id':
				// Return the text (display) value of the control field
				sValue = aControls[0].getElementText();
				break;
		}

		return sValue;
	},
	
	_disableEventType : function(iEventTypeId, bConfirmed)
	{
		if (!bConfirmed)
		{
			Reflex_Popup.yesNoCancel('Are you sure you want to disable this Event Type?', {fnOnYes: this._disableEventType.bind(this, iEventTypeId, true)});
			return;
		}
		this._setEventTypeStatus(iEventTypeId, $CONSTANT.STATUS_INACTIVE);
	},
	
	_enableEventType : function(iEventTypeId)
	{
		this._setEventTypeStatus(iEventTypeId, $CONSTANT.STATUS_ACTIVE);
	},
	
	_setEventTypeStatus : function(iEventTypeId, iStatusId, oResponse)
	{
		if (!oResponse)
		{
			// Make request
			var fnResp 	= this._setEventTypeStatus.bind(this, iEventTypeId, iStatusId);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Event_Type', 'setStatus');
			fnReq(iEventTypeId, iStatusId);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Handle error
			jQuery.json.errorPopup(oResponse);
			return;
		}
		
		// Refresh table
		this.oPagination.getCurrentPage();
	},
	
	_addEventType : function()
	{
		new Popup_Collections_Event_Type(this.oPagination.getCurrentPage.bind(this.oPagination));
	}
});

// Static

Object.extend(Component_Collections_Event_Type_List,
{
	DATA_SET_DEFINITION			: {sObject: 'Collection_Event_Type', sMethod: 'getDataset'},
	MAX_RECORDS_PER_PAGE		: 5,
	FILTER_IMAGE_SOURCE			: '../admin/img/template/table_row_insert.png',
	REMOVE_FILTER_IMAGE_SOURCE	: '../admin/img/template/delete.png',
	REQUIRED_CONSTANT_GROUPS	: ['collection_event_invocation', 
	                        	   'collection_event_type_implementation',
	                        	   'status'],
	
	// Sorting definitions
	SORT_FIELDS	:	
	{
		id 											: Sort.DIRECTION_ASC,
		name										: Sort.DIRECTION_OFF,
		description									: Sort.DIRECTION_OFF,
		system_name									: Sort.DIRECTION_OFF,
		collection_event_type_implementation_name	: Sort.DIRECTION_OFF,
		collection_event_invocation_name			: Sort.DIRECTION_OFF,
		status_name									: Sort.DIRECTION_OFF
	},
	
	_getInvocationOptions : function(fnCallback)
	{
		var aOptions = [];
		
		// 'Optional' (null) item
		aOptions.push(
			$T.option({value: 0},
				'Optional'
			)
		);
		
		var aConstantGroup 	= Flex.Constant.arrConstantGroups.collection_event_invocation;
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
	}
});

Component_Collections_Event_Type_List.SORT_IMAGE_SOURCE						= {};
Component_Collections_Event_Type_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_OFF]	= '../admin/img/template/order_neither.png';
Component_Collections_Event_Type_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_ASC]	= '../admin/img/template/order_asc.png';
Component_Collections_Event_Type_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_DESC]	= '../admin/img/template/order_desc.png';

//Filter Control field definitions (starts at 
Component_Collections_Event_Type_List.FILTER_FIELDS	=
{
	collection_event_type_implementation_id	:
	{
		iType	: Filter.FILTER_TYPE_VALUE,
		oOption	:
		{
			sType		: 'select',
			mDefault	: null,
			oDefinition	:
			{
				sLabel		: 'Implementation',
				mEditable	: true,
				mMandatory	: false,
				fnPopulate	: Component_Collections_Event_Type_List._getConstantGroupOptions.curry('collection_event_type_implementation')
			}
		}
	},
	collection_event_invocation_id	:
	{
		iType	: Filter.FILTER_TYPE_VALUE,
		oOption	:
		{
			sType		: 'select',
			mDefault	: null,
			oDefinition	:
			{
				sLabel		: 'Is Manual',
				mEditable	: true,
				mMandatory	: false,
				fnPopulate	: Component_Collections_Event_Type_List._getInvocationOptions
			}
		}
	},
	status_id	:
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
				fnPopulate	: Component_Collections_Event_Type_List._getConstantGroupOptions.curry('status')
			}
		}
	}
};

