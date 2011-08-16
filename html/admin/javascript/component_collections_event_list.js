
var Component_Collections_Event_List = Class.create(
{
	initialize	: function(iPageSize, oLoadingPopup)
	{
		this._hFilters				= {};
		this._bFirstLoadComplete	= false;
		this._iPageSize				= iPageSize;
		this._oElement				= $T.div({class: 'component-collections-event-list'});
		this._oLoadingPopup			= oLoadingPopup;
		
		// Load constants then create UI
		Flex.Constant.loadConstantGroup(Component_Collections_Event_List.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
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
		this.oDataSet		= 	new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, Component_Collections_Event_List.DATA_SET_DEFINITION);
		this.oPagination 	= 	new Pagination(
									this._updateTable.bind(this), 
									(this._iPageSize ? this._iPageSize : Component_Collections_Event_List.MAX_RECORDS_PER_PAGE), 
									this.oDataSet
								);

		// Create Dataset filter object
		this._oFilter	=	new Filter(
								this.oDataSet,
								this.oPagination,
								this._showLoading.bind(this, true) 	// On field value change
							);

		// Add all filter fields
		for (var sFieldName in Component_Collections_Event_List.FILTER_FIELDS)
		{
			if (Component_Collections_Event_List.FILTER_FIELDS[sFieldName].iType)
			{
				this._oFilter.addFilter(sFieldName, Component_Collections_Event_List.FILTER_FIELDS[sFieldName]);
			}
		}

		// Create Dataset sort object
		this._oSort	= new Sort(this.oDataSet, this.oPagination, true, this._showLoading.bind(this, true));
		
		// Create second dataset for getting selected instance ids (including ones not on the current page)
		this._oDataSetSelection = new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, Component_Collections_Event_List.DATA_SET_DEFINITION);
		
		// Create the page HTML
		var sButtonPathBase	= '../admin/img/template/resultset_';
		var oSection		= new Section(true);
		this._oSection 		= oSection;
		this._oElement.appendChild(oSection.getElement());
		
		// Title
		oSection.setTitleContent(
			$T.span(
				$T.span('Collections Events'),
				$T.span({class: 'pagination-info'},
					''
				)
			)
		);
		
		// Header options
		oSection.addToHeaderOptions(
			$T.button({class: 'icon-button'},
				$T.img({src: '../admin/img/template/new.png'}),
				$T.span('Add Event')	
			).observe('click', this._addEvent.bind(this))
		);
		
		// Main content -- table
		oSection.setContent(
			$T.table({class: 'component-collections-event-list-table reflex highlight-rows'},
				$T.thead(
					// Column headings
					$T.tr({class: 'component-collections-event-list-headerrow'},
						$T.th('#'),
						$T.th('Name'),
						$T.th('Description'),
						$T.th('Type'),
						$T.th('Is Manual'),
						$T.th('Status'),
						$T.th('') // Actions
					),
					// Filter values
					$T.tr(
						$T.th(),
						$T.th(),
						$T.th(),
						this._createFilterValueElement('collection_event_type_id', 'Type'),
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
		var aHeaderRowTHs = this._oElement.select('.component-collections-event-list-headerrow > th');
		this._registerSortHeader(aHeaderRowTHs[0], 'id');
		this._registerSortHeader(aHeaderRowTHs[1], 'name');
		this._registerSortHeader(aHeaderRowTHs[2], 'description');
		this._registerSortHeader(aHeaderRowTHs[3], 'collection_event_type_name');
		this._registerSortHeader(aHeaderRowTHs[4], 'collection_event_invocation_name');
		this._registerSortHeader(aHeaderRowTHs[5], 'status_name');
		
		// Footer -- loading msg & pagination
		oSection.setFooterContent(
			$T.ul({class: 'reset horizontal component-collections-event-list-options'},
				$T.li({class: 'loading'},
					'Loading...'
				),
				$T.li(
					$T.button({class: 'component-collections-event-list-paginationbutton'},
						$T.img({src: sButtonPathBase + 'first.png'})
					)
				),
				$T.li(
					$T.button({class: 'component-collections-event-list-paginationbutton'},
						$T.img({src: sButtonPathBase + 'previous.png'})
					)
				),
				$T.li(
					$T.button({class: 'component-collections-event-list-paginationbutton'},
						$T.img({src: sButtonPathBase + 'next.png'})
					)
				),
				$T.li(
					$T.button({class: 'component-collections-event-list-paginationbutton'},
						$T.img({src: sButtonPathBase + 'last.png'})
					)
				)
			)
		);

		// Bind events to the pagination buttons
		var aBottomPageButtons 	= this._oElement.select('.component-collections-event-list-paginationbutton');
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
		if ((oData.id !== null) && !Object.isUndefined(oData.id))
		{
			// Create the row element
			var sActionIconText = null;
			var sActionIconSrc 	= null;
			var fnActionOnClick	= null;
			if (oData.status_id == $CONSTANT.STATUS_ACTIVE)
			{
				sActionIconText = 'Disable';
				sActionIconSrc 	= '../admin/img/template/decline.png';
				fnActionOnClick	= this._disableEvent.bind(this, oData.id, false);
			}
			else
			{
				sActionIconText = 'Enable';
				sActionIconSrc 	= '../admin/img/template/approve.png';
				fnActionOnClick	= this._enableEvent.bind(this, oData.id);
			}
			
			var oDetailIcon = null;
			if (oData.has_details)
			{
				oDetailIcon = $T.img({class: 'pointer', src: Component_Collections_Event_List.VIEW_DETAILS_IMAGE_SOURCE, alt: 'View Event Details', title: 'View Event Details'}).observe('click', this._viewEventDetails.bind(this, oData.id))
			}
			
			var	oTR	=	$T.tr(
							$T.td(oData.id),
							$T.td(oData.name),
							$T.td(oData.description),
							$T.td(oData.collection_event_type_name),
							$T.td(oData.collection_event_invocation_name === null ? 'Optional' : oData.collection_event_invocation_name),
							$T.td(oData.status_name),
							$T.td(
								$T.img({class: 'pointer', src: sActionIconSrc, alt: sActionIconText, title: sActionIconText}).observe('click', fnActionOnClick),
								oDetailIcon
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
		for (var sField in Component_Collections_Event_List.SORT_FIELDS)
		{
			if (this._oSort.isRegistered(sField))
			{
				var oSortImg	= this._oElement.select('img.sort-' + sField).first();
				var iDirection	= this._oSort.getSortDirection(sField);
				oSortImg.src	= Component_Collections_Event_List.SORT_IMAGE_SOURCE[iDirection];
				oSortImg.show();
			}
		}
	},

	_updateFilters	: function()
	{
		for (var sField in Component_Collections_Event_List.FILTER_FIELDS)
		{
			if (Component_Collections_Event_List.FILTER_FIELDS[sField].iType)
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
			var oSpan	= this._oElement.select('th.component-collections-event-list-filter-heading > span.filter-' + sField).first();
			if (oSpan)
			{
				var oDeleteImage	= oSpan.up().select('img.component-collections-event-list-filter-delete').first();
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
		var oDeleteImage				= $T.img({class: 'component-collections-event-list-filter-delete', src: Component_Collections_Event_List.REMOVE_FILTER_IMAGE_SOURCE, alt: 'Remove Filter', title: 'Remove Filter'});
		oDeleteImage.style.visibility	= 'hidden';
		oDeleteImage.observe('click', this._clearFilterValue.bind(this, sField));

		var oFiterImage	= $T.img({class: 'header-filter', src: Component_Collections_Event_List.FILTER_IMAGE_SOURCE, alt: 'Filter by ' + sLabel, title: 'Filter by ' + sLabel});
		this._oFilter.registerFilterIcon(sField, oFiterImage, sLabel, this._oElement, 0, 10);

		return	$T.th({class: 'component-collections-event-list-filter-heading'},
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
		
		oSpan.addClassName('component-collections-event-list-header-sort');
		
		this._oSort.registerToggleElement(oSpan, sSortField, Component_Collections_Event_List.SORT_FIELDS[sSortField]);
		this._oSort.registerToggleElement(oSortImg, sSortField, Component_Collections_Event_List.SORT_FIELDS[sSortField]);
	},
	
	_formatFilterValueForDisplay	: function(sField, mValue)
	{
		var oDefinition	= Component_Collections_Event_List.FILTER_FIELDS[sField];
		var aControls	= this._oFilter.getControlsForField(sField);
		var sValue		= '';
		switch (sField)
		{
			case 'collection_event_type_id':
			case 'collection_event_invocation_id':
			case 'status_id':
				// Return the text (display) value of the control field
				sValue = aControls[0].getElementText();
				break;
		}

		return sValue;
	},
	
	_disableEvent : function(iEventId, bConfirmed)
	{
		if (!bConfirmed)
		{
			Reflex_Popup.yesNoCancel('Are you sure you want to disable this Event?', {fnOnYes: this._disableEvent.bind(this, iEventId, true)});
			return;
		}
		this._setEventStatus(iEventId, $CONSTANT.STATUS_INACTIVE);
	},
	
	_enableEvent : function(iEventId)
	{
		this._setEventStatus(iEventId, $CONSTANT.STATUS_ACTIVE);
	},
	
	_setEventStatus : function(iEventId, iStatusId, oResponse)
	{
		if (!oResponse)
		{
			// Make request
			var fnResp 	= this._setEventStatus.bind(this, iEventId, iStatusId);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Event', 'setStatus');
			fnReq(iEventId, iStatusId);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Handle error
			Component_Collections_Event_List._ajaxError(oResponse);
			return;
		}
		
		// Refresh table
		this.oPagination.getCurrentPage();
	},
	
	_addEvent : function()
	{
		new Popup_Collections_Event(this.oPagination.getCurrentPage.bind(this.oPagination));
	},
	
	_viewEventDetails : function(iEventId)
	{
		new Popup_Collections_Event_List_Details(iEventId);
	}
});

// Static

Object.extend(Component_Collections_Event_List,
{
	DATA_SET_DEFINITION			: {sObject: 'Collection_Event', sMethod: 'getDataset'},
	MAX_RECORDS_PER_PAGE		: 5,
	FILTER_IMAGE_SOURCE			: '../admin/img/template/table_row_insert.png',
	REMOVE_FILTER_IMAGE_SOURCE	: '../admin/img/template/delete.png',
	VIEW_DETAILS_IMAGE_SOURCE	: '../admin/img/template/magnifier.png',
	REQUIRED_CONSTANT_GROUPS	: ['collection_event_invocation', 
	                        	   //'collection_event_type_implementation',
	                        	   'status'],
	
	// Sorting definitions
	SORT_FIELDS	:	
	{
		id 									: Sort.DIRECTION_ASC,
		name								: Sort.DIRECTION_OFF,
		description							: Sort.DIRECTION_OFF,
		collection_event_type_name			: Sort.DIRECTION_OFF,
		collection_event_invocation_name	: Sort.DIRECTION_OFF,
		status_name							: Sort.DIRECTION_OFF
	},
	
	_ajaxError : function(oResponse)
	{
		var sMessage = (oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.');
		Reflex_Popup.alert(sMessage, {sTitle: 'Error'});
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
	},
	
	_getTypeOptions : function(fnCallback, oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp 	= Component_Collections_Event_List._getTypeOptions.curry(fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Event_Type', 'getAll');
			fnReq();
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Component_Collections_Event_List._ajaxError(oResponse);
			return;
		}
		
		// Create options & callback
		var aOptions	= [];
		var aData		= oResponse.aEventTypes;
		for (var i in aData)
		{
			aOptions.push(
				$T.option({value: i},
					aData[i].name	
				)
			);
		}
		
		fnCallback(aOptions);
	}
});

Component_Collections_Event_List.SORT_IMAGE_SOURCE						= {};
Component_Collections_Event_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_OFF]	= '../admin/img/template/order_neither.png';
Component_Collections_Event_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_ASC]	= '../admin/img/template/order_asc.png';
Component_Collections_Event_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_DESC]	= '../admin/img/template/order_desc.png';

//Filter Control field definitions (starts at 
Component_Collections_Event_List.FILTER_FIELDS	=
{
	collection_event_type_id	:
	{
		iType	: Filter.FILTER_TYPE_VALUE,
		oOption	:
		{
			sType		: 'select',
			mDefault	: null,
			oDefinition	:
			{
				sLabel		: 'Type',
				mEditable	: true,
				mMandatory	: false,
				fnPopulate	: Component_Collections_Event_List._getTypeOptions
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
				fnPopulate	: Component_Collections_Event_List._getInvocationOptions
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
				fnPopulate	: Component_Collections_Event_List._getConstantGroupOptions.curry('status')
			}
		}
	}
};

