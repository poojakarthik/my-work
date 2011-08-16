
var Component_Collections_Event_Management = Class.create(
{
	initialize	: function(oContainerDiv, oLoadingPopup)
	{
		this._hFilters					= {};
		this._oContainerDiv				= oContainerDiv;
		this._bFirstLoadComplete		= false;
		this._hControlOnChangeCallbacks	= {};
		this._oLoadingPopup				= oLoadingPopup;
		this._iSelectMode				= Component_Collections_Event_Management.SELECT_MODE_NONE;
		this._iSelectPageNumber			= null;
		this._iSelectCount				= null;
		this._hSelectedInstances		= {};
		this._hDeselectedInstances		= {};
		
		Flex.Constant.loadConstantGroup(Component_Collections_Event_Management.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
	},

	_buildUI : function()
	{
		if (!Component_Collections_Event_Management._hEventTypes)
		{
			Component_Collections_Event_Management._cacheAllEventTypes(this._buildUI.bind(this));
			return;
		}
		
		// Create Dataset & pagination object
		this.oDataSet 		= new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, Component_Collections_Event_Management.DATA_SET_DEFINITION);
		this.oPagination	= new Pagination(this._updateTable.bind(this), Component_Collections_Event_Management.MAX_RECORDS_PER_PAGE, this.oDataSet);

		// Create Dataset filter object
		this._oFilter	=	new Filter(
								this.oDataSet,
								this.oPagination,
								this._showLoading.bind(this, true) 	// On field value change
							);

		// Add all filter fields
		for (var sFieldName in Component_Collections_Event_Management.FILTER_FIELDS)
		{
			if (Component_Collections_Event_Management.FILTER_FIELDS[sFieldName].iType)
			{
				this._oFilter.addFilter(sFieldName, Component_Collections_Event_Management.FILTER_FIELDS[sFieldName]);
			}
		}

		// Create Dataset sort object
		this._oSort	= new Sort(this.oDataSet, this.oPagination, true, this._showLoading.bind(this, true));
		
		// Create second dataset for getting selected instance ids (including ones not on the current page)
		this._oDataSetSelection = new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, Component_Collections_Event_Management.DATA_SET_DEFINITION);
		
		this._oOverlay = new Reflex_Loading_Overlay();
		
		// Create the page HTML
		var sButtonPathBase	= 	'../admin/img/template/resultset_';
		var oSection		= 	new Section(true);
		this._oContentDiv 	= 	$T.div({class: 'component-collections-event-management'},
									oSection.getElement()
								);
		
		// Title
		oSection.setTitleContent(
			$T.span(
				$T.span('Events'),
				$T.span({class: 'pagination-info'},
					''
				)
			)
		);
		
		// Header options
		this._oBreadcrumb =	new Reflex_Breadcrumb_Select(
								'Events', 
								[{fnPopulate: this._getAllEventTypeOptions.bind(this), sName: 'Event Types'}, 
								 {fnPopulate: this._getEventOptions.bind(this), sName: 'Events'}],
								this._breadcrumbChange.bind(this)
							);
		oSection.addToHeaderOptions(this._oBreadcrumb.getElement());
		oSection.addToHeaderOptions($T.button('Export to File').observe('click', this._exportToFile.bind(this, null)));
		
		// Main content -- table
		oSection.setContent(
			$T.table({class: 'component-collections-event-management-table reflex highlight-rows'},
				$T.thead(
					// Column headings
					$T.tr({class: 'component-collections-event-management-headerrow'},
						$T.th(''),
						$T.th('Account'),
						$T.th('Scenario'),
						$T.th('Event Type'),
						$T.th('Event'),
						$T.th('Scheduled On'),
						$T.th('Status'),
						$T.th()
					),
					// Filter values
					$T.tr(
						$T.th(),
						this._createFilterValueElement('account_id', 'Account'),
						this._createFilterValueElement('collection_scenario_id', 'Scenario'),
						$T.th(),
						$T.th(),
						this._createFilterValueElement('scheduled_datetime', 'Scheduled On'),
						this._createFilterValueElement('account_collection_event_status_id', 'Status'),
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
		var aHeaderRowTHs = this._oContentDiv.select('.component-collections-event-management-headerrow > th');
		this._registerSortHeader(aHeaderRowTHs[1], 'account_id');
		this._registerSortHeader(aHeaderRowTHs[2], 'collection_scenario_name');
		this._registerSortHeader(aHeaderRowTHs[3], 'collection_event_type_name');
		this._registerSortHeader(aHeaderRowTHs[4], 'collection_event_name');
		this._registerSortHeader(aHeaderRowTHs[5], 'scheduled_datetime');
		this._registerSortHeader(aHeaderRowTHs[6], 'account_collection_event_status_name');
		
		// Footer -- loading msg & pagination
		oSection.setFooterContent(
			$T.ul({class: 'reset horizontal component-collections-event-management-options'},
				$T.li({class: 'component-collections-event-management-selectoptions'},
					$T.div(
						$T.span('Select: '),
						$T.a('All').observe('click', this._selectAll.bind(this)),
						$T.span(' | '),
						$T.a('All on page').observe('click', this._selectAllOnPage.bind(this)),
						$T.span(' | '),
						$T.a('Custom').observe('click', this._selectFirstXRows.bind(this, null)),
						$T.span(' | '),
						$T.a('Deselect All').observe('click', this._deselectAll.bind(this, true))
					)
				),
				$T.li({class: 'component-collections-event-management-selectactions'},
					$T.div(
						$T.span('With Selected:'),
						$T.button({class: 'icon-button'},
							$T.img({src: Component_Collections_Event_Management.COMPLETE_IMAGE_SOURCE}),
							$T.span('Complete')
						).observe('click', this._completeSelected.bind(this, null, null))
					)
				),
				$T.li(
					$T.button({class: 'component-collections-event-management-paginationbutton'},
						$T.img({src: sButtonPathBase + 'first.png'})
					)
				),
				$T.li(
					$T.button({class: 'component-collections-event-management-paginationbutton'},
						$T.img({src: sButtonPathBase + 'previous.png'})
					)
				),
				$T.li(
					$T.button({class: 'component-collections-event-management-paginationbutton'},
						$T.img({src: sButtonPathBase + 'next.png'})
					)
				),
				$T.li(
					$T.button({class: 'component-collections-event-management-paginationbutton'},
						$T.img({src: sButtonPathBase + 'last.png'})
					)
				)
			)
		);

		// Bind events to the pagination buttons
		var aBottomPageButtons 	= this._oContentDiv.select('.component-collections-event-management-paginationbutton');
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

		// Load the initial dataset
		this._breadcrumbChange();
		this._oSort.refreshData(true);
		this._oFilter.refreshData(true);
		this.oPagination.getCurrentPage();
	},

	_showLoading	: function(bShow)
	{
		if (bShow)
		{
			this._oOverlay.attachTo(this._oContentDiv.select('table > tbody').first());
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
				(bOnLoad ? 'Loading...' : 'There are no events to display')
			)
		);
	},

	_createTableRow	: function(oData, iPosition, iTotalResults)
	{
		if (oData.account_id)
		{
			// Account
			var oAccountElement = $T.td({class: 'component-collections-event-management-account'});
			if (oData.customer_group)
			{
				oAccountElement.appendChild(
					$T.div({class: 'popup-followup-detail-subdetail customer-group'},
						$T.span(oData.customer_group_internal_name)
					)
				);
			}
			
			if (oData.account_id && oData.account_name)
			{
				var sUrl = 'flex.php/Account/Overview/?Account.Id=' + oData.account_id;
				oAccountElement.appendChild(
					$T.div({class: 'popup-followup-detail-subdetail account'},
						$T.div({class: 'account-id'},
							$T.img({src: Component_Collections_Event_Management.DETAILS_ACCOUNT_IMAGE_SOURCE}),
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
			
			// Status
			var oStatusElement = $T.td(oData.account_collection_event_status_name);
			if (oData.account_collection_event_status_id == $CONSTANT.ACCOUNT_COLLECTION_EVENT_STATUS_COMPLETED)
			{
				oStatusElement.appendChild(
					$T.div({class: 'component-collections-event-management-status-completed'},
						Component_Collections_Event_Management._getDateTimeElement(oData.completed_datetime)
					)
				);
			}
			
			// Action
			var oCheckbox 		= null;
			var iCurrentEventId	= this._oFilter.getFilterValue('collection_event_id');
			if (this._canInstanceBeCompleted(oData) && (iCurrentEventId !== null))
			{
				// Checkbox for completion
				oCheckbox = $T.input({type: 'checkbox', class: 'component-collections-event-management-tobeactioned'});
				oCheckbox.observe('change', this._actionCheckboxChanged.bind(this));
				oCheckbox.iEventInstanceId = oData.account_collection_event_history_id;
				
				// Check select mode, select if necessary
				var bChecked = false;
				switch (this._iSelectMode)
				{
					case Component_Collections_Event_Management.SELECT_MODE_ALL:
						bChecked = true;
						break;
						
					case Component_Collections_Event_Management.SELECT_MODE_PAGE:
						if (this._iSelectPageNumber == this.oPagination.intCurrentPage)
						{
							// Is on the page chosen to have all selected 
							bChecked = true;
							break;
						}
						else
						{
							// Different page, clear the select mode
							this._iSelectMode 		= Component_Collections_Event_Management.SELECT_MODE_NONE;
							this._iSelectPageNumber	= null;
						}
					
					case Component_Collections_Event_Management.SELECT_MODE_NONE:
						if (this._hSelectedInstances[oCheckbox.iEventInstanceId])
						{
							bChecked = true;
						}
						break;
						
					case Component_Collections_Event_Management.SELECT_MODE_FIRST_X:
						if (this._iSelectCount > 0)
						{
							bChecked = (iPosition < this._iSelectCount);
						}
						else
						{
							bChecked = (iPosition >= (iTotalResults + this._iSelectCount));
						}
						break;
				}
				
				if (bChecked && !this._hDeselectedInstances[oCheckbox.iEventInstanceId])
				{
					oCheckbox.checked 										= true;
					this._hSelectedInstances[oCheckbox.iEventInstanceId]	= true;
				}
			}
			
			// Icon for individual completion (always visible)
			var oCompleteIcon = null;
			if (this._canInstanceBeCompleted(oData))
			{
				var oCompleteIcon = $T.img({class: 'pointer', src: Component_Collections_Event_Management.COMPLETE_IMAGE_SOURCE, alt: 'Complete', title: 'Complete'});
				oCompleteIcon.observe('click', this._completeSingle.bind(this, oData.account_collection_event_history_id, oData.collection_event_type_id));
			}
			
			// Create the row element
			var	oTR	=	$T.tr(
							$T.td(oCheckbox),
							oAccountElement,
							$T.td(oData.collection_scenario_name),
							$T.td(oData.collection_event_type_name),
							$T.td(oData.collection_event_name),
							$T.td(Component_Collections_Event_Management._getDateTimeElement(oData.scheduled_datetime)),
							oStatusElement,
							$T.td(oCompleteIcon)
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
		for (var sField in Component_Collections_Event_Management.SORT_FIELDS)
		{
			if (this._oSort.isRegistered(sField))
			{
				var oSortImg	= this._oContentDiv.select('img.sort-' + sField).first();
				var iDirection	= this._oSort.getSortDirection(sField);
				oSortImg.src	= Component_Collections_Event_Management.SORT_IMAGE_SOURCE[iDirection];
				oSortImg.show();
			}
		}
	},

	_updateFilters	: function()
	{
		for (var sField in Component_Collections_Event_Management.FILTER_FIELDS)
		{
			if (Component_Collections_Event_Management.FILTER_FIELDS[sField].iType)
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
			var oSpan	= this._oContentDiv.select('th.component-collections-event-management-filter-heading > span.filter-' + sField).first();
			if (oSpan)
			{
				var oDeleteImage	= oSpan.up().select('img.component-collections-event-management-filter-delete').first();
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
		var oDeleteImage				= $T.img({class: 'component-collections-event-management-filter-delete', src: Component_Collections_Event_Management.REMOVE_FILTER_IMAGE_SOURCE, alt: 'Remove Filter', title: 'Remove Filter'});
		oDeleteImage.style.visibility	= 'hidden';
		oDeleteImage.observe('click', this._clearFilterValue.bind(this, sField));

		var oFiterImage	= $T.img({class: 'header-filter', src: Component_Collections_Event_Management.FILTER_IMAGE_SOURCE, alt: 'Filter by ' + sLabel, title: 'Filter by ' + sLabel});
		this._oFilter.registerFilterIcon(sField, oFiterImage, sLabel, this._oContentDiv, 0, 10);

		return	$T.th({class: 'component-collections-event-management-filter-heading'},
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
		
		oSpan.addClassName('component-collections-event-management-header-sort');
		
		this._oSort.registerToggleElement(oSpan, sSortField, Component_Collections_Event_Management.SORT_FIELDS[sSortField]);
		this._oSort.registerToggleElement(oSortImg, sSortField, Component_Collections_Event_Management.SORT_FIELDS[sSortField]);
	},
	
	_formatFilterValueForDisplay	: function(sField, mValue)
	{
		var oDefinition	= Component_Collections_Event_Management.FILTER_FIELDS[sField];
		var aControls	= this._oFilter.getControlsForField(sField);
		var sValue		= '';
		switch (sField)
		{
			case 'account_id':
				sValue = mValue;
				break;
				
			case 'collection_scenario_id':
			case 'account_collection_event_status_id':
				// Return the text (display) value of the control field
				sValue = aControls[0].getElementText();
				break;
				
			case 'scheduled_datetime':
				var oState		= this._oFilter.getFilterState(sField);
				var bGotFrom	= mValue.mFrom != null;
				var bGotTo		= mValue.mTo != null;
				var sFrom		= (bGotFrom ? Component_Collections_Event_Management._formatDateTimeFilterValue(mValue.mFrom)	: null);
				var sTo			= (bGotTo 	? Component_Collections_Event_Management._formatDateTimeFilterValue(mValue.mTo) 	: null);
				switch (parseInt(oState.oRangeTypeSelect.value))
				{
					case Filter.RANGE_TYPE_FROM:
						sValue	= [oDefinition.sFromOption, sFrom].join(' ');
						break;
					case Filter.RANGE_TYPE_TO:
						sValue	= [oDefinition.sToOption, sTo].join(' ');
						break;
					case Filter.RANGE_TYPE_BETWEEN:
						sValue	= [oDefinition.sBetweenOption, sFrom, 'and', sTo].join(' ');
						break;
				}
				break;
		}

		return sValue;
	},
	
	_getSelectedInstanceIds : function()
	{
		var aIds = [];
		for (var iInstanceId in this._hSelectedInstances)
		{
			if (this._hSelectedInstances[iInstanceId])
			{
				aIds.push(parseInt(iInstanceId));
			}
		}
		return aIds;
	},
	
	_getDeselectedInstanceIds : function()
	{
		var aIds = [];
		for (var iInstanceId in this._hDeselectedInstances)
		{
			aIds.push(parseInt(iInstanceId));
		}
		return aIds;
	},
	
	_getSelectionData : function(fnCallback, iLimit)
	{
		this._oDataSetSelection.setSortingFields(this._oSort.getSortData());
		this._oDataSetSelection.setFilter(this._oFilter.getFilterData());
		this._oDataSetSelection.getRecords(fnCallback, iLimit);
	},
	
	_canInstanceBeCompleted : function(oInstance)
	{
		var bIsScheduled 	= (oInstance.account_collection_event_status_id == $CONSTANT.ACCOUNT_COLLECTION_EVENT_STATUS_SCHEDULED);
		var bIsManual		= (oInstance.collection_event_invocation_id == $CONSTANT.COLLECTION_EVENT_INVOCATION_MANUAL);
		return bIsScheduled && bIsManual;
		return 
	},
	
	_completeSingle : function(iEventInstanceId, iEventTypeId, oEvent)
	{
		this._invokeEvent([iEventInstanceId], iEventTypeId);
	},
	
	_completeSelected : function(iRecordCount, hRecords)
	{
		var aInstanceIdsToComplete = [];
		switch (this._iSelectMode)
		{
			case Component_Collections_Event_Management.SELECT_MODE_ALL:
				// Use all minus the ones that have been deselected (on any page)
				if (iRecordCount === null)
				{
					// Get all of the instance ids for the current filter set
					this._getSelectionData(this._completeSelected.bind(this));
					return;
				}
				
				// Add all that haven't been deselected
				for (var i in hRecords)
				{
					if (this._canInstanceBeCompleted(hRecords[i]))
					{
						var iInstanceId = hRecords[i].account_collection_event_history_id;
						if (!this._hDeselectedInstances[iInstanceId])
						{
							aInstanceIdsToComplete.push(iInstanceId);
						}
					}
				}	
				break;
			
			case Component_Collections_Event_Management.SELECT_MODE_PAGE:
			case Component_Collections_Event_Management.SELECT_MODE_NONE:
				// Use all that have been selected on any page
				aInstanceIdsToComplete = this._getSelectedInstanceIds();
				break;
				
			case Component_Collections_Event_Management.SELECT_MODE_FIRST_X:
				// Use the first/last x minus ones that have been deselected, as well as any others that have been selected
				if (iRecordCount === null)
				{
					// Get all of the instance ids for the current filter set
					this._getSelectionData(this._completeSelected.bind(this));
					return;
				}
				
				if (this._iSelectCount < 0)
				{
					// The last ? events have been selected
					var iMinPosition = (iRecordCount + this._iSelectCount) - 1;
					for (var i = iMinPosition; i < iRecordCount; i++)
					{
						if (hRecords[i] && this._canInstanceBeCompleted(hRecords[i]))
						{
							var iInstanceId = hRecords[i].account_collection_event_history_id;
							if (!this._hDeselectedInstances[iInstanceId])
							{
								aInstanceIdsToComplete.push(iInstanceId);
							}
						}
					}
				}
				else 
				{
					// The first ? events have been selected
					for (var i = 0; i < this._iSelectCount; i++)
					{
						if (hRecords[i] && this._canInstanceBeCompleted(hRecords[i]))
						{
							var iInstanceId = hRecords[i].account_collection_event_history_id;
							if (!this._hDeselectedInstances[iInstanceId])
							{
								aInstanceIdsToComplete.push(iInstanceId);
							}
						}
					}
				}
				
				// Include all other selected ones, on other pages
				for (var iInstanceId in this._hSelectedInstances)
				{
					iInstanceId = parseInt(iInstanceId);
					if (aInstanceIdsToComplete.indexOf(iInstanceId) == -1)
					{
						aInstanceIdsToComplete.push(iInstanceId);
					}
				}
				break;
		}
		
		if (aInstanceIdsToComplete.length == 0)
		{
			Reflex_Popup.alert('There are no events selected to complete.')
			return;
		}
		
		this._invokeEvent(aInstanceIdsToComplete, this._oFilter.getFilterValue('collection_event_type_id'));
	},
	
	_invokeEvent : function(aInstanceIdsToComplete, iEventTypeId)
	{
		var oEventType = Component_Collections_Event_Management._hEventTypes[iEventTypeId];
		if (!oEventType)
		{
			// TODO: Show an error alert
			return;
		}
		
		Collection_Event_Type.getInstance(
			oEventType.collection_event_type_implementation_id, 
			aInstanceIdsToComplete,
			this._eventInvocationComplete.bind(this)
		);
	},
	
	_eventInvocationComplete : function()
	{
		this._deselectAll(false);
		this.oPagination.getCurrentPage();
	},
	
	_getAllEventTypeOptions : function(oBreadcrumb, fnCallback)
	{
		var aOptions 	= [];
		var oType		= null;
		for (var i in Component_Collections_Event_Management._hEventTypes)
		{
			oType = Component_Collections_Event_Management._hEventTypes[i];
			aOptions.push({mValue: oType.id, sText: Component_Collections_Event_Management._hEventTypes[i].name});
		}
		
		if (fnCallback)
		{
			fnCallback(aOptions);
		}
	},
	
	_getEventOptions : function(oBreadcrumb, fnCallback, oResponse)
	{
		if (!oResponse)
		{
			var iEventType 	= oBreadcrumb.getValueAtLevel(0);
			var fnResponse	= this._getEventOptions.bind(this, oBreadcrumb, fnCallback);
			var fnRequest 	= jQuery.json.jsonFunction(fnResponse, fnResponse, 'Collection_Event', 'getEventsForType');
			fnRequest(iEventType);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Component_Collections_Event_Management._ajaxError(oResponse);
			return;
		}
		
		var aOptions = [];
		if (!Object.isArray(oResponse.aEvents))
		{
			for (var iId in oResponse.aEvents)
			{
				aOptions.push({mValue: iId, sText: oResponse.aEvents[iId].name});
			}
		}
		
		if (fnCallback)
		{
			fnCallback(aOptions);
		}
	},
	
	_breadcrumbChange : function()
	{
		var iEventTypeId	= parseInt(this._oBreadcrumb.getValueAtLevel(0));
		var iEventId		= parseInt(this._oBreadcrumb.getValueAtLevel(1));
		iEventTypeId		= (isNaN(iEventTypeId) ? null : iEventTypeId);
		iEventId			= (isNaN(iEventId) ? null : iEventId);
		
		this._oFilter.setFilterValue('collection_event_type_id', iEventTypeId);
		this._oFilter.setFilterValue('collection_event_id', iEventId);
		
		this._oFilter.refreshData(true);
		this._deselectAll(false);
		this.oPagination.getCurrentPage();
	},
	
	_selectAll : function()
	{
		if (this._oFilter.getFilterValue('collection_event_id') !== null)
		{
			this._iSelectMode 			= Component_Collections_Event_Management.SELECT_MODE_ALL;
			this._hSelectedInstances 	= {};
			this._hDeselectedInstances	= {};
			this.oPagination.getCurrentPage();
		}
	},
	
	_selectAllOnPage : function()
	{
		if (this._oFilter.getFilterValue('collection_event_id') !== null)
		{
			this._iSelectMode 			= Component_Collections_Event_Management.SELECT_MODE_PAGE;
			this._iSelectPageNumber		= this.oPagination.intCurrentPage;
			this._hSelectedInstances 	= {};
			this._hDeselectedInstances	= {};
			this.oPagination.getCurrentPage();
		}
	},
	
	_selectFirstXRows : function(iNumberOfRows, oEvent)
	{
		if (this._oFilter.getFilterValue('collection_event_id') !== null)
		{
			if (iNumberOfRows === null)
			{
				new Popup_Custom_Row_Selection('Event', this._selectFirstXRows.bind(this));
				return;
			}
			
			this._hSelectedInstances 	= {};
			this._hDeselectedInstances	= {};
			this._iSelectMode 			= Component_Collections_Event_Management.SELECT_MODE_FIRST_X;
			this._iSelectCount			= iNumberOfRows;
			this.oPagination.getCurrentPage();
		}
	},
	
	_deselectAll : function(bReloadPage, oEvent)
	{
		this._iSelectMode 			= Component_Collections_Event_Management.SELECT_MODE_NONE;
		this._hSelectedInstances 	= {};
		this._hDeselectedInstances	= {};
		
		if (bReloadPage)
		{
			this.oPagination.getCurrentPage();
		}
	},
	
	_actionCheckboxChanged	: function(oEvent)
	{
		var iInstanceId	= oEvent.target.iEventInstanceId;
		var bChecked 	= !!oEvent.target.checked;
		
		if (bChecked)
		{
			// Selected
			this._hDeselectedInstances[iInstanceId]	= false;
			this._hSelectedInstances[iInstanceId] 	= true;
		}
		else
		{
			// Deselected
			this._hDeselectedInstances[iInstanceId]	= true;
			this._hSelectedInstances[iInstanceId] 	= false;
		}
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
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Event_Instance', 'generateLedgerFile');
			fnReq(this._oSort.getSortData(), this._oFilter.getFilterData(), sFileType);
			return;
		}
		
		this._oLoadingPopup.hide();
		delete this._oLoadingPopup;
		
		if (!oResponse.bSuccess)
		{
			// Error
			Component_Collections_Event_Management._ajaxError(oResponse);
			return;
		}

		// Redirect to download the file
		window.location = 'reflex.php/Collections/DownloadLedgerFile/' + oResponse.sFilename + '/?Type=' + oResponse.sMIME;
	}
});





// Static

Object.extend(Component_Collections_Event_Management,
{
	DATA_SET_DEFINITION			: {sObject: 'Collection_Event_Instance', sMethod: 'getDataset'},
	MAX_RECORDS_PER_PAGE		: 10,
	REQUIRED_CONSTANT_GROUPS	: ['account_collection_event_status', 
	                        	   'collection_event_type_implementation', 
	                        	   'collection_event_invocation'],
	                        	   
	FILTER_IMAGE_SOURCE				: '../admin/img/template/table_row_insert.png',
	REMOVE_FILTER_IMAGE_SOURCE		: '../admin/img/template/delete.png',
	DETAILS_ACCOUNT_IMAGE_SOURCE	: '../admin/img/template/account.png',
	COMPLETE_IMAGE_SOURCE			: '../admin/img/template/approve.png',
	
	SELECT_MODE_NONE	: 1,
	SELECT_MODE_ALL		: 2,
	SELECT_MODE_PAGE	: 3,
	SELECT_MODE_FIRST_X	: 4,
	
	// Sorting definitions
	SORT_FIELDS	:	
	{
		account_id 								: Sort.DIRECTION_ASC,
		collection_scenario_name				: Sort.DIRECTION_OFF,
		collection_event_type_name				: Sort.DIRECTION_OFF,
		collection_event_name					: Sort.DIRECTION_OFF,
		scheduled_datetime						: Sort.DIRECTION_OFF,
		account_collection_event_status_name	: Sort.DIRECTION_OFF
	},
	
	_hEventTypes : null,
	
	_ajaxError : function(oResponse)
	{
		var sMessage = (oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.');
		Reflex_Popup.alert(sMessage, {sTitle: 'Error'});
	},
	
	_getDateTimeElement	: function(sMySQLDate)
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

	_formatDateTimeFilterValue	: function(sDateTime)
	{
		var oDate	= Date.$parseDate(sDateTime, 'Y-m-d H:i:s');
		return oDate.$format('j/m/y');
	},

	_getAllScenariosAsOptions : function(fnCallback, oResponse)
	{
		if (!oResponse)
		{
			var fnResponse	= Component_Collections_Event_Management._getAllScenariosAsOptions.curry(fnCallback);
			var fnRequest 	= jQuery.json.jsonFunction(fnResponse, fnResponse, 'Collection_Scenario', 'getAll');
			fnRequest(false, true);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Component_Collections_Event_Management._ajaxError(oResponse);
			return;
		}
		
		var aOptions = [];
		for (var iId in oResponse.aScenarios)
		{
			aOptions.push(
				$T.option({value: iId},
					oResponse.aScenarios[iId].name
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
	
	_getAllEventTypesAsOptions : function(fnCallback)
	{
		Component_Collections_Event_Management._getConstantGroupOptions('collection_event_type', fnCallback);
	},
	
	_getAllEventStatusesAsOptions	: function(fnCallback)
	{
		Component_Collections_Event_Management._getConstantGroupOptions('account_collection_event_status', fnCallback);
	},
	
	_cacheAllEventTypes : function(fnCallback, aResults)
	{
		if (!aResults)
		{
			Collection_Event_Type.getAll(Component_Collections_Event_Management._cacheAllEventTypes.curry(fnCallback));
			return;
		}
		
		Component_Collections_Event_Management._hEventTypes = aResults;
		
		if (fnCallback)
		{
			fnCallback(aResults);
		}
	}
});

Component_Collections_Event_Management.SORT_IMAGE_SOURCE						= {};
Component_Collections_Event_Management.SORT_IMAGE_SOURCE[Sort.DIRECTION_OFF]	= '../admin/img/template/order_neither.png';
Component_Collections_Event_Management.SORT_IMAGE_SOURCE[Sort.DIRECTION_ASC]	= '../admin/img/template/order_asc.png';
Component_Collections_Event_Management.SORT_IMAGE_SOURCE[Sort.DIRECTION_DESC]	= '../admin/img/template/order_desc.png';

//Filter Control field definitions (starts at 
Component_Collections_Event_Management.FILTER_FIELDS	=
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
				fnPopulate	: Component_Collections_Event_Management._getAllScenariosAsOptions
			}
		}
	},
	collection_event_id :
	{
		iType : Filter.FILTER_TYPE_VALUE,
	},
	collection_event_type_id	:
	{
		iType	: Filter.FILTER_TYPE_VALUE,
	},
	account_collection_event_status_id :
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
				fnPopulate	: Component_Collections_Event_Management._getAllEventStatusesAsOptions
			}
		}
	},
	scheduled_datetime	:
	{
		iType			: Filter.FILTER_TYPE_RANGE,
		bFrom			: true,
		sFrom			: 'Start Date',
		bTo				: true,
		sTo				: 'End Date',
		sFromOption		: 'On Or After',
		sToOption		: 'On Or Before',
		sBetweenOption	: 'Between',
		oOption			:
		{
			sType		: 	'date-picker',
			mDefault	:	null,
			oDefinition	:
			{
				sLabel		: 'Scheduled On',
				mEditable	: true,
				bTimePicker	: true,
				mMandatory	: false,
				sDateFormat	: 'Y-m-d H:i:s',
				iYearStart	: 2011,	// Start of the new collections system
				iYearEnd	: new Date().getFullYear() // ...to now
			}
		}
	}
};

