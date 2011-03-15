
var Component_Barring_Action_Ledger = Class.create(
{
	initialize	: function(oContainerDiv, oLoadingPopup)
	{
		this._hFilters				= {};
		this._bFirstLoadComplete	= false;
		this._oElement				= $T.div({class: 'component-barring-action-ledger'});
		this._oLoadingPopup			= oLoadingPopup;
		this._oContainerDiv			= oContainerDiv;
		
		this._iSelectMode			= Component_Barring_Action_Ledger.SELECT_MODE_NONE;
		this._iSelectPageNumber		= null;
		this._iSelectCount			= null;
		this._hSelectedRecords	= {};
		this._hDeselectedRecords	= {};
		
		// Load constants then create UI
		Flex.Constant.loadConstantGroup(Component_Barring_Action_Ledger.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
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
		this.oDataSet		= 	new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, Component_Barring_Action_Ledger.DATA_SET_DEFINITION);
		this.oPagination 	= 	new Pagination(
									this._updateTable.bind(this), 
									Component_Barring_Action_Ledger.MAX_RECORDS_PER_PAGE, 
									this.oDataSet
								);
		
		// Create Dataset filter object
		this._oFilter	=	new Filter(
								this.oDataSet,
								this.oPagination,
								this._showLoading.bind(this, true) 	// On field value change
							);

		// Add all filter fields
		for (var sFieldName in Component_Barring_Action_Ledger.FILTER_FIELDS)
		{
			if (Component_Barring_Action_Ledger.FILTER_FIELDS[sFieldName].iType)
			{
				this._oFilter.addFilter(sFieldName, Component_Barring_Action_Ledger.FILTER_FIELDS[sFieldName]);
			}
		}

		// Create Dataset sort object
		this._oSort	= new Sort(this.oDataSet, this.oPagination, true, this._showLoading.bind(this, true));
		
		// Create second dataset for getting selected instance ids (including ones not on the current page)
		this._oDataSetSelection = new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, Component_Barring_Action_Ledger.DATA_SET_DEFINITION);
		
		// Create the page HTML
		var sButtonPathBase	= '../admin/img/template/resultset_';
		var oSection		= new Section(true);
		this._oSection 		= oSection;
		this._oElement.appendChild(oSection.getElement());
		
		// Title
		oSection.setTitleContent(
			$T.span(
				$T.span('Barring Actioning'),
				$T.span({class: 'pagination-info'},
					''
				)
			)
		);
		
		oSection.addToHeaderOptions($T.button('Export to File').observe('click', this._exportToFile.bind(this, null, null)));
		
		// Main content -- table
		oSection.setContent(
			$T.table({class: 'component-barring-action-ledger-table reflex highlight-rows'},
				$T.thead(
					// Column headings
					$T.tr({class: 'component-barring-action-ledger-headerrow'},
						$T.th(),
						$T.th('Account'),
						$T.th('Service'),
						$T.th('Service Type'),
						$T.th('Carrier'),
						$T.th('Target Barring Level'),
						$T.th('Authorised On'),
						$T.th('Authorised By'),
						$T.th('Created On'),
						$T.th('Created By'),
						$T.th('') // Actions
					),
					// Filter values
					$T.tr(
						$T.th(),
						this._createFilterValueElement('account_id', 'Account'),
						this._createFilterValueElement('service_fnn', 'Service'),
						this._createFilterValueElement('service_type_id', 'Service Type'),
						this._createFilterValueElement('carrier_id', 'Carrier'),
						this._createFilterValueElement('barring_level_id', 'Target Barring Level'),
						this._createFilterValueElement('authorised_datetime', 'Authorised On'),
						this._createFilterValueElement('authorised_employee_id', 'Authorised By'),
						this._createFilterValueElement('created_datetime', 'Created On'),
						this._createFilterValueElement('created_employee_id', 'Created By'),
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
		var aHeaderRowTHs = this._oElement.select('.component-barring-action-ledger-headerrow > th');
		this._registerSortHeader(aHeaderRowTHs[1], 'account_id');
		this._registerSortHeader(aHeaderRowTHs[2], 'service_fnn');
		this._registerSortHeader(aHeaderRowTHs[3], 'service_type_name');
		this._registerSortHeader(aHeaderRowTHs[4], 'carrier_name');
		this._registerSortHeader(aHeaderRowTHs[5], 'barring_level_name');
		this._registerSortHeader(aHeaderRowTHs[6], 'authorised_datetime');
		this._registerSortHeader(aHeaderRowTHs[7], 'authorised_employee_name');
		this._registerSortHeader(aHeaderRowTHs[8], 'created_datetime');
		this._registerSortHeader(aHeaderRowTHs[9], 'created_employee_name');
		
		// Footer -- loading msg & pagination
		oSection.setFooterContent(
			$T.ul({class: 'reset horizontal component-barring-action-ledger-options'},
				$T.li({class: 'loading'},
					'Loading...'
				),
				$T.li({class: 'component-barring-action-ledger-selectoptions'},
					$T.div(
						$T.span('Select: '),
						$T.a('All').observe('click', this._selectAll.bind(this)),
						$T.span(' | '),
						$T.a('All on page').observe('click', this._selectAllOnPage.bind(this)),
						$T.span(' | '),
						$T.a('Custom').observe('click', this._selectFirstXRows.bind(this, null)),
						$T.span(' | '),
						$T.a('None').observe('click', this._deselectAll.bind(this, true))
					)
				),
				$T.li({class: 'component-barring-action-ledger-selectactions'},
					$T.div(
						$T.span('With Selected:'),
						$T.button({class: 'icon-button'},
							$T.img({src: Component_Barring_Action_Ledger.ACTION_IMAGE_SOURCE}),
							$T.span('Action')
						).observe('click', this._actionSelected.bind(this, null, null))
					)
				),
				$T.li(
					$T.button({class: 'component-barring-action-ledger-paginationbutton'},
						$T.img({src: sButtonPathBase + 'first.png'})
					)
				),
				$T.li(
					$T.button({class: 'component-barring-action-ledger-paginationbutton'},
						$T.img({src: sButtonPathBase + 'previous.png'})
					)
				),
				$T.li(
					$T.button({class: 'component-barring-action-ledger-paginationbutton'},
						$T.img({src: sButtonPathBase + 'next.png'})
					)
				),
				$T.li(
					$T.button({class: 'component-barring-action-ledger-paginationbutton'},
						$T.img({src: sButtonPathBase + 'last.png'})
					)
				)
			)
		);

		// Bind events to the pagination buttons
		var aBottomPageButtons 	= this._oElement.select('.component-barring-action-ledger-paginationbutton');
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
		if (oData.service_barring_level_id)
		{
			// Selection checkbox
			var oCheckbox = $T.input({type: 'checkbox', class: 'component-barring-action-ledger-tobeactioned'});
			oCheckbox.observe('change', this._actionCheckboxChanged.bind(this));
			oCheckbox.iRecordId = oData.service_barring_level_id;
			
			// Check select mode, select if necessary
			var bChecked = false;
			switch (this._iSelectMode)
			{
				case Component_Barring_Action_Ledger.SELECT_MODE_ALL:
					bChecked = true;
					break;
					
				case Component_Barring_Action_Ledger.SELECT_MODE_PAGE:
					if (this._iSelectPageNumber == this.oPagination.intCurrentPage)
					{
						// Is on the page chosen to have all selected 
						bChecked = true;
						break;
					}
					else
					{
						// Different page, clear the select mode
						this._iSelectMode 		= Component_Barring_Action_Ledger.SELECT_MODE_NONE;
						this._iSelectPageNumber	= null;
					}
				
				case Component_Barring_Action_Ledger.SELECT_MODE_NONE:
					if (this._hSelectedRecords[oCheckbox.iRecordId])
					{
						bChecked = true;
					}
					break;
					
				case Component_Barring_Action_Ledger.SELECT_MODE_FIRST_X:
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
			
			if (bChecked && !this._hDeselectedRecords[oCheckbox.iRecordId])
			{
				oCheckbox.checked 										= true;
				this._hSelectedRecords[oCheckbox.iRecordId]	= true;
			}
			
			var sUrl 			= 'flex.php/Account/Overview/?Account.Id=' + oData.account_id;
			var oAccountElement	= 	$T.div({class: 'component-barring-authorisation-ledger-account'},
										$T.div({class: 'component-barring-authorisation-ledger-subdetail'},
											$T.div({class: 'account-id'},
												$T.a({href: sUrl},
													oData.account_id + ': '
												),
												$T.a({class: 'account-name', href: sUrl},
													oData.account_name
												)
											)
										),
										$T.div({class: 'component-barring-authorisation-ledger-subdetail'},
											$T.span(oData.customer_group_name)
										)
									);
			
			var	oTR	=	$T.tr(
							$T.td(oCheckbox),
							$T.td(oAccountElement),
							$T.td(oData.service_fnn),
							$T.td(oData.service_type_name),
							$T.td(oData.carrier_name),
							$T.td(oData.barring_level_name),
							$T.td(Component_Barring_Action_Ledger._getDateTimeElement(oData.authorised_datetime)),
							$T.td(oData.authorised_employee_name),
							$T.td(Component_Barring_Action_Ledger._getDateTimeElement(oData.created_datetime)),
							$T.td(oData.created_employee_name),
							$T.td(
								$T.img({class: 'pointer', src: Component_Barring_Action_Ledger.ACTION_IMAGE_SOURCE, alt: 'Action This Item', title: 'Action This Item'}).observe('click', this._actionSingle.bind(this, oData.service_barring_level_id))	
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
	
	_createServiceRow : function(oData)
	{
		
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
		for (var sField in Component_Barring_Action_Ledger.SORT_FIELDS)
		{
			if (this._oSort.isRegistered(sField))
			{
				var oSortImg	= this._oElement.select('img.sort-' + sField).first();
				var iDirection	= this._oSort.getSortDirection(sField);
				oSortImg.src	= Component_Barring_Action_Ledger.SORT_IMAGE_SOURCE[iDirection];
				oSortImg.show();
			}
		}
	},

	_updateFilters	: function()
	{
		for (var sField in Component_Barring_Action_Ledger.FILTER_FIELDS)
		{
			if (Component_Barring_Action_Ledger.FILTER_FIELDS[sField].iType)
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
			var oSpan	= this._oElement.select('th.component-barring-action-ledger-filter-heading > span.filter-' + sField).first();
			if (oSpan)
			{
				var oDeleteImage	= oSpan.up().select('img.component-barring-action-ledger-filter-delete').first();
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
		var oDeleteImage				= $T.img({class: 'component-barring-action-ledger-filter-delete', src: Component_Barring_Action_Ledger.REMOVE_FILTER_IMAGE_SOURCE, alt: 'Remove Filter', title: 'Remove Filter'});
		oDeleteImage.style.visibility	= 'hidden';
		oDeleteImage.observe('click', this._clearFilterValue.bind(this, sField));

		var oFiterImage	= $T.img({class: 'header-filter', src: Component_Barring_Action_Ledger.FILTER_IMAGE_SOURCE, alt: 'Filter by ' + sLabel, title: 'Filter by ' + sLabel});
		this._oFilter.registerFilterIcon(sField, oFiterImage, sLabel, this._oElement, 0, 10);

		return	$T.th({class: 'component-barring-action-ledger-filter-heading'},
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
		
		oSpan.addClassName('component-barring-action-ledger-header-sort');
		
		this._oSort.registerToggleElement(oSpan, sSortField, Component_Barring_Action_Ledger.SORT_FIELDS[sSortField]);
	},
	
	_formatFilterValueForDisplay	: function(sField, mValue)
	{
		var oDefinition	= Component_Barring_Action_Ledger.FILTER_FIELDS[sField];
		var aControls	= this._oFilter.getControlsForField(sField);
		var sValue		= '';
		switch (sField)
		{
			case 'account_id':
			case 'service_fnn':
				sValue = aControls[0].getElementValue();
				break;
			
			case 'authorised_employee_id':
			case 'created_employee_id':
			case 'barring_level_id':
			case 'service_type_id':
			case 'carrier_id':
				// Return the text (display) value of the control field
				sValue = aControls[0].getElementText();
				break;
			
			case 'authorised_datetime':
			case 'created_datetime':
				var oState		= this._oFilter.getFilterState(sField);
				var bGotFrom	= mValue.mFrom != null;
				var bGotTo		= mValue.mTo != null;
				var sFrom		= (bGotFrom ? Component_Barring_Action_Ledger._formatDateTimeFilterValue(mValue.mFrom) : null);
				var sTo			= (bGotTo ? Component_Barring_Action_Ledger._formatDateTimeFilterValue(mValue.mTo) : null);
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
	
	_getSelectionData : function(fnCallback, iLimit)
	{
		this._oDataSetSelection.setSortingFields(this._oSort.getSortData());
		this._oDataSetSelection.setFilter(this._oFilter.getFilterData());
		this._oDataSetSelection.getRecords(fnCallback, iLimit);
	},
	
	_actionSingle : function(iRecordId)
	{
		this._actionItems([iRecordId]);
	},
	
	_actionSelected : function(iRecordCount, hRecords)
	{
		var aRecordIds = [];
		switch (this._iSelectMode)
		{
			case Component_Barring_Action_Ledger.SELECT_MODE_ALL:
				// Use all minus the ones that have been deselected (on any page)
				if (iRecordCount === null)
				{
					// Get all of the instance ids for the current filter set
					this._getSelectionData(this._actionSelected.bind(this));
					return;
				}
				
				// Add all that haven't been deselected
				for (var i in hRecords)
				{
					var iRecordId = hRecords[i].service_barring_level_id;
					if (!this._hDeselectedRecords[iRecordId])
					{
						aRecordIds.push(iRecordId);
					}
				}	
				break;
			
			case Component_Barring_Action_Ledger.SELECT_MODE_PAGE:
			case Component_Barring_Action_Ledger.SELECT_MODE_NONE:
				// Use all that have been selected on any page
				aRecordIds = this._getSelectedRecordIds();
				break;
				
			case Component_Barring_Action_Ledger.SELECT_MODE_FIRST_X:
				// Use the first/last x minus ones that have been deselected, as well as any others that have been selected
				if (iRecordCount === null)
				{
					// Get all of the instance ids for the current filter set
					this._getSelectionData(this._actionSelected.bind(this));
					return;
				}
				
				if (this._iSelectCount < 0)
				{
					// The last ? events have been selected
					var iMinPosition = (iRecordCount + this._iSelectCount) - 1;
					for (var i = iMinPosition; i < iRecordCount; i++)
					{
						if (hRecords[i])
						{
							var iRecordId = hRecords[i].service_barring_level_id;
							if (!this._hDeselectedRecords[iRecordId])
							{
								aRecordIds.push(iRecordId);
							}
						}
					}
				}
				else 
				{
					// The first ? events have been selected
					for (var i = 0; i < this._iSelectCount; i++)
					{
						if (hRecords[i])
						{
							var iRecordId = hRecords[i].service_barring_level_id;
							if (!this._hDeselectedRecords[iRecordId])
							{
								aRecordIds.push(iRecordId);
							}
						}
					}
				}
				
				// Include all other selected ones, on other pages
				for (var iRecordId in this._hSelectedRecords)
				{
					iRecordId = parseInt(iRecordId);
					if (aRecordIds.indexOf(iRecordId) == -1)
					{
						aRecordIds.push(iRecordId);
					}
				}
				break;
		}
		
		if (aRecordIds.length == 0)
		{
			Reflex_Popup.alert('There are no items selected to action.')
			return;
		}
		
		this._actionItems(aRecordIds);
	},
	
	_actionItems : function(aRecordIds, oResponse)
	{
		if (!oResponse)
		{
			this._oLoadingPopup = new Reflex_Popup.Loading('Actioning Items...');
			this._oLoadingPopup.display();
			
			var fnResp	= this._actionItems.bind(this, aRecordIds);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Barring', 'actionServiceBarringLevels');
			fnReq(aRecordIds);
			return;
		}
		
		this._oLoadingPopup.hide();
		delete this._oLoadingPopup;
		
		if (!oResponse.bSuccess)
		{
			Component_Barring_Action_Ledger._ajaxError(oResponse);
			return;
		}
		
		this._actionItemComplete();
	},
	
	_actionItemComplete : function()
	{
		this._deselectAll(false);
		this.oPagination.getCurrentPage();
	},

	_selectAll : function()
	{
		this._iSelectMode 			= Component_Barring_Action_Ledger.SELECT_MODE_ALL;
		this._hSelectedRecords 		= {};
		this._hDeselectedRecords	= {};
		this.oPagination.getCurrentPage();
	},
	
	_selectAllOnPage : function()
	{
		this._iSelectMode 			= Component_Barring_Action_Ledger.SELECT_MODE_PAGE;
		this._iSelectPageNumber		= this.oPagination.intCurrentPage;
		this._hSelectedRecords 		= {};
		this._hDeselectedRecords	= {};
		this.oPagination.getCurrentPage();
	},
	
	_selectFirstXRows : function(iNumberOfRows, oEvent)
	{
		if (iNumberOfRows === null)
		{
			new Popup_Custom_Row_Selection('Barring Level Change', this._selectFirstXRows.bind(this));
			return;
		}
		
		this._hSelectedRecords 	= {};
		this._hDeselectedRecords	= {};
		this._iSelectMode 			= Component_Barring_Action_Ledger.SELECT_MODE_FIRST_X;
		this._iSelectCount			= iNumberOfRows;
		this.oPagination.getCurrentPage();
	},
	
	_deselectAll : function(bReloadPage, oEvent)
	{
		this._iSelectMode 			= Component_Barring_Action_Ledger.SELECT_MODE_NONE;
		this._hSelectedRecords 		= {};
		this._hDeselectedRecords	= {};
		
		if (bReloadPage)
		{
			this.oPagination.getCurrentPage();
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
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Barring', 'generateActionLedgerFile');
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
		window.location = 'reflex.php/Barring/DownloadActionLedgerFile/' + oResponse.sFilename + '/?Type=' + oResponse.sMIME;
	}
});

// Static

Object.extend(Component_Barring_Action_Ledger,
{
	DATA_SET_DEFINITION			: {sObject: 'Barring', sMethod: 'getActionLedgerDataset'},
	MAX_RECORDS_PER_PAGE		: 10,
	FILTER_IMAGE_SOURCE			: '../admin/img/template/table_row_insert.png',
	REMOVE_FILTER_IMAGE_SOURCE	: '../admin/img/template/delete.png',
	ACTION_IMAGE_SOURCE			: '../admin/img/template/approve.png',
	REQUIRED_CONSTANT_GROUPS	: ['barring_level', 'service_type'],
	YEAR_MINIMUM				: 2011,
	YEAR_MAXIMUM				: new Date().getFullYear(),
	
	SELECT_MODE_NONE	: 1,
	SELECT_MODE_ALL		: 2,
	SELECT_MODE_PAGE	: 3,
	SELECT_MODE_FIRST_X	: 4,
	
	// Sorting definitions
	SORT_FIELDS	:	
	{
		account_id					: Sort.DIRECTION_OFF,
		authorised_datetime			: Sort.DIRECTION_OFF,
		authorised_employee_name	: Sort.DIRECTION_OFF,
		barring_level_name			: Sort.DIRECTION_OFF,
		service_fnn					: Sort.DIRECTION_OFF,
		created_datetime			: Sort.DIRECTION_OFF,
		created_employee_name		: Sort.DIRECTION_OFF,
		service_type_name			: Sort.DIRECTION_OFF,
		carrier_name				: Sort.DIRECTION_OFF,
	},
	
	_ajaxError : function(oResponse)
	{
		var sMessage = (oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.');
		Reflex_Popup.alert(sMessage, {sTitle: 'Error'});
	},
	
	_getCarrierOptions : function(fnCallback, oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp	= Component_Barring_Action_Ledger._getCarrierOptions.curry(fnCallback);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Carrier', 'getRatePlanCarriers');
			fnReq();
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Component_Barring_Action_Ledger._ajaxError(oResponse);
			return;
		}
		
		// Success, callback with options
		var aOptions = [];
		for (var iId in oResponse.aRecords)
		{
			aOptions.push(
				$T.option({value: iId},
					oResponse.aRecords[iId].Name
				)
			)
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
	
	_getDateTimeElement : function(sMySQLDate)
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
	
	_formatDateTimeFilterValue : function(sDateTime)
	{
		var oDate = Date.$parseDate(sDateTime, 'Y-m-d H:i:s');
		return oDate.$format('j/m/y h:i');
	}
});

Component_Barring_Action_Ledger.SORT_IMAGE_SOURCE						= {};
Component_Barring_Action_Ledger.SORT_IMAGE_SOURCE[Sort.DIRECTION_OFF]	= '../admin/img/template/order_neither.png';
Component_Barring_Action_Ledger.SORT_IMAGE_SOURCE[Sort.DIRECTION_ASC]	= '../admin/img/template/order_asc.png';
Component_Barring_Action_Ledger.SORT_IMAGE_SOURCE[Sort.DIRECTION_DESC]	= '../admin/img/template/order_desc.png';

//Filter Control field definitions (starts at 
Component_Barring_Action_Ledger.FILTER_FIELDS	=
{
	account_id :
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
				mMandatory	: false
			}
		}
	},
	service_fnn :
	{
		iType	: Filter.FILTER_TYPE_VALUE,
		oOption	:
		{
			sType		: 'text',
			mDefault	: null,
			oDefinition	:
			{
				sLabel		: 'Service',
				mEditable	: true,
				mMandatory	: false
			}
		}
	},
	authorised_employee_id :
	{
		iType	: Filter.FILTER_TYPE_VALUE,
		oOption	:
		{
			sType		: 'select',
			mDefault	: null,
			oDefinition	:
			{
				sLabel		: 'Authorised By',
				mEditable	: true,
				mMandatory	: false,
				fnPopulate	: Employee.getAllAsSelectOptions.bind(Employee)
			}
		}
	},
	created_employee_id :
	{
		iType	: Filter.FILTER_TYPE_VALUE,
		oOption	:
		{
			sType		: 'select',
			mDefault	: null,
			oDefinition	:
			{
				sLabel		: 'Actioned By',
				mEditable	: true,
				mMandatory	: false,
				fnPopulate	: Employee.getAllAsSelectOptions.bind(Employee)
			}
		}
	},
	barring_level_id :
	{
		iType	: Filter.FILTER_TYPE_VALUE,
		oOption	:
		{
			sType		: 'select',
			mDefault	: null,
			oDefinition	:
			{
				sLabel		: 'Target Barring Level',
				mEditable	: true,
				mMandatory	: false,
				fnPopulate	: Component_Barring_Action_Ledger._getConstantGroupOptions.curry('barring_level')
			}
		}
	},
	service_type_id :
	{
		iType	: Filter.FILTER_TYPE_VALUE,
		oOption	:
		{
			sType		: 'select',
			mDefault	: null,
			oDefinition	:
			{
				sLabel		: 'Service Type',
				mEditable	: true,
				mMandatory	: false,
				fnPopulate	: Component_Barring_Action_Ledger._getConstantGroupOptions.curry('service_type')
			}
		}
	},
	carrier_id :
	{
		iType	: Filter.FILTER_TYPE_VALUE,
		oOption	:
		{
			sType		: 'select',
			mDefault	: null,
			oDefinition	:
			{
				sLabel		: 'Carrier',
				mEditable	: true,
				mMandatory	: false,
				fnPopulate	: Component_Barring_Action_Ledger._getCarrierOptions
			}
		}
	},
	authorised_datetime :
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
			sType		: 'date-picker',
			mDefault	: null,
			oDefinition	:	
			{
				sLabel		: 'Date',
				mEditable	: true,
				mMandatory	: false,
				sDateFormat	: 'Y-m-d H:i:s',
				bTimePicker	: true,
				iYearStart	: Component_Barring_Action_Ledger.YEAR_MINIMUM,
				iYearEnd	: Component_Barring_Action_Ledger.YEAR_MAXIMUM
			}
		}
	},
	created_datetime :
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
			sType		: 'date-picker',
			mDefault	: null,
			oDefinition	:	
			{
				sLabel		: 'Date',
				mEditable	: true,
				mMandatory	: false,
				sDateFormat	: 'Y-m-d H:i:s',
				bTimePicker	: true,
				iYearStart	: Component_Barring_Action_Ledger.YEAR_MINIMUM,
				iYearEnd	: Component_Barring_Action_Ledger.YEAR_MAXIMUM
			}
		}
	}
};

