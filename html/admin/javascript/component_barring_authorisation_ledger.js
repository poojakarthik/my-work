
var Component_Barring_Authorisation_Ledger = Class.create(
{
	initialize	: function(oContainerDiv, oLoadingPopup)
	{
		this._hFilters				= {};
		this._bFirstLoadComplete	= false;
		this._oElement				= $T.div({class: 'component-barring-authorisation-ledger'});
		this._oLoadingPopup			= oLoadingPopup;
		this._oContainerDiv			= oContainerDiv;
		
		// Load constants then create UI
		Flex.Constant.loadConstantGroup(Component_Barring_Authorisation_Ledger.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
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
		this.oDataSet		= 	new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, Component_Barring_Authorisation_Ledger.DATA_SET_DEFINITION);
		this.oPagination 	= 	new Pagination(
									this._updateTable.bind(this), 
									Component_Barring_Authorisation_Ledger.MAX_RECORDS_PER_PAGE, 
									this.oDataSet
								);
		
		// Create Dataset filter object
		this._oFilter	=	new Filter(
								this.oDataSet,
								this.oPagination,
								this._showLoading.bind(this, true) 	// On field value change
							);

		// Add all filter fields
		for (var sFieldName in Component_Barring_Authorisation_Ledger.FILTER_FIELDS)
		{
			if (Component_Barring_Authorisation_Ledger.FILTER_FIELDS[sFieldName].iType)
			{
				this._oFilter.addFilter(sFieldName, Component_Barring_Authorisation_Ledger.FILTER_FIELDS[sFieldName]);
			}
		}

		// Create Dataset sort object
		this._oSort	= new Sort(this.oDataSet, this.oPagination, true, this._showLoading.bind(this, true));
		
		// Create second dataset for getting selected instance ids (including ones not on the current page)
		this._oDataSetSelection = new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, Component_Barring_Authorisation_Ledger.DATA_SET_DEFINITION);
		
		// Create the page HTML
		var sButtonPathBase	= '../admin/img/template/resultset_';
		var oSection		= new Section(true);
		this._oSection 		= oSection;
		this._oElement.appendChild(oSection.getElement());
		
		// Title
		oSection.setTitleContent(
			$T.span(
				$T.span('Barring Authorisation'),
				$T.span({class: 'pagination-info'},
					''
				)
			)
		);
		
		// Main content -- table
		oSection.setContent(
			$T.table({class: 'component-barring-authorisation-ledger-table reflex highlight-rows'},
				$T.thead(
					// Column headings
					$T.tr({class: 'component-barring-authorisation-ledger-headerrow'},
						$T.th('Account'),
						$T.th('Scenario'),
						$T.th('Services'),
						$T.th('From (Barring Level)'),
						$T.th('To (Barring Level)'),
						$T.th('Created On'),
						$T.th('Created By'),
						$T.th('') // Actions
					),
					// Filter values
					$T.tr(
						this._createFilterValueElement('account_id', 'Account'),
						this._createFilterValueElement('collection_scenario_id', 'Scenario'),
						$T.th(),
						this._createFilterValueElement('current_barring_level_id', 'From (Barring Level)'),
						this._createFilterValueElement('barring_level_id', 'To (Barring Level)'),
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
		var aHeaderRowTHs = this._oElement.select('.component-barring-authorisation-ledger-headerrow > th');
		this._registerSortHeader(aHeaderRowTHs[0], 'account_id');
		this._registerSortHeader(aHeaderRowTHs[1], 'collection_scenario_name');
		this._registerSortHeader(aHeaderRowTHs[3], 'current_barring_level_name');
		this._registerSortHeader(aHeaderRowTHs[4], 'barring_level_name');
		this._registerSortHeader(aHeaderRowTHs[5], 'created_datetime');
		this._registerSortHeader(aHeaderRowTHs[6], 'created_employee_name');
		
		// Footer -- loading msg & pagination
		oSection.setFooterContent(
			$T.ul({class: 'reset horizontal component-barring-authorisation-ledger-options'},
				$T.li({class: 'loading'},
					'Loading...'
				),
				$T.li(
					$T.button({class: 'component-barring-authorisation-ledger-paginationbutton'},
						$T.img({src: sButtonPathBase + 'first.png'})
					)
				),
				$T.li(
					$T.button({class: 'component-barring-authorisation-ledger-paginationbutton'},
						$T.img({src: sButtonPathBase + 'previous.png'})
					)
				),
				$T.li(
					$T.button({class: 'component-barring-authorisation-ledger-paginationbutton'},
						$T.img({src: sButtonPathBase + 'next.png'})
					)
				),
				$T.li(
					$T.button({class: 'component-barring-authorisation-ledger-paginationbutton'},
						$T.img({src: sButtonPathBase + 'last.png'})
					)
				)
			)
		);

		// Bind events to the pagination buttons
		var aBottomPageButtons 	= this._oElement.select('.component-barring-authorisation-ledger-paginationbutton');
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
			$T.td({class: 'no-rows', colspan: 9},
				(bOnLoad ? 'Loading...' : 'There are no records to display')
			)
		);
	},

	_createTableRow	: function(oData, iPosition, iTotalResults)
	{
		if (oData.account_id)
		{	
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
							$T.td(oAccountElement),
							$T.td(oData.collection_scenario_name),
							$T.td(oData.service_barring_level_id_count),
							$T.td(oData.current_barring_level_name),
							$T.td(oData.barring_level_name),
							$T.td(Component_Barring_Authorisation_Ledger._getDateTimeElement(oData.created_datetime)),
							$T.td(oData.created_employee_name),
							$T.td(
								$T.img({class: 'pointer', src: Component_Barring_Authorisation_Ledger.AUTHORISE_IMAGE_SOURCE, alt: 'Authorise This Account', title: 'Authorise This Account'}).observe('click', this._authoriseAccount.bind(this, oData))	
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
		for (var sField in Component_Barring_Authorisation_Ledger.SORT_FIELDS)
		{
			if (this._oSort.isRegistered(sField))
			{
				var oSortImg	= this._oElement.select('img.sort-' + sField).first();
				var iDirection	= this._oSort.getSortDirection(sField);
				oSortImg.src	= Component_Barring_Authorisation_Ledger.SORT_IMAGE_SOURCE[iDirection];
				oSortImg.show();
			}
		}
	},

	_updateFilters	: function()
	{
		for (var sField in Component_Barring_Authorisation_Ledger.FILTER_FIELDS)
		{
			if (Component_Barring_Authorisation_Ledger.FILTER_FIELDS[sField].iType)
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
			var oSpan	= this._oElement.select('th.component-barring-authorisation-ledger-filter-heading > span.filter-' + sField).first();
			if (oSpan)
			{
				var oDeleteImage	= oSpan.up().select('img.component-barring-authorisation-ledger-filter-delete').first();
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
		var oDeleteImage				= $T.img({class: 'component-barring-authorisation-ledger-filter-delete', src: Component_Barring_Authorisation_Ledger.REMOVE_FILTER_IMAGE_SOURCE, alt: 'Remove Filter', title: 'Remove Filter'});
		oDeleteImage.style.visibility	= 'hidden';
		oDeleteImage.observe('click', this._clearFilterValue.bind(this, sField));

		var oFiterImage	= $T.img({class: 'header-filter', src: Component_Barring_Authorisation_Ledger.FILTER_IMAGE_SOURCE, alt: 'Filter by ' + sLabel, title: 'Filter by ' + sLabel});
		this._oFilter.registerFilterIcon(sField, oFiterImage, sLabel, this._oElement, 0, 10);

		return	$T.th({class: 'component-barring-authorisation-ledger-filter-heading'},
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
		
		oSpan.addClassName('component-barring-authorisation-ledger-header-sort');
		
		this._oSort.registerToggleElement(oSpan, sSortField, Component_Barring_Authorisation_Ledger.SORT_FIELDS[sSortField]);
		this._oSort.registerToggleElement(oSortImg, sSortField, Component_Barring_Authorisation_Ledger.SORT_FIELDS[sSortField]);
	},
	
	_formatFilterValueForDisplay	: function(sField, mValue)
	{
		var oDefinition	= Component_Barring_Authorisation_Ledger.FILTER_FIELDS[sField];
		var aControls	= this._oFilter.getControlsForField(sField);
		var sValue		= '';
		switch (sField)
		{
			case 'account_id':
				sValue = aControls[0].getElementValue();
				break;
			
			case 'barring_level_id':
			case 'current_barring_level_id':
			case 'created_employee_id':
			case 'collection_scenario_id':
				// Return the text (display) value of the control field
				sValue = aControls[0].getElementText();
				break;
			
			case 'created_datetime':
				var oState		= this._oFilter.getFilterState(sField);
				var bGotFrom	= mValue.mFrom != null;
				var bGotTo		= mValue.mTo != null;
				var sFrom		= (bGotFrom ? Component_Barring_Authorisation_Ledger._formatDateTimeFilterValue(mValue.mFrom) : null);
				var sTo			= (bGotTo ? Component_Barring_Authorisation_Ledger._formatDateTimeFilterValue(mValue.mTo) : null);
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
	
	_authoriseAccount : function(oData)
	{
		new Popup_Barring_Authorisation_Ledger_Authorise_Account(
			oData.account_id, 
			oData.account_barring_level_id, 
			oData.service_barring_level_ids,
			oData.barring_level_id,
			this._authoriseAccountComplete.bind(this)
		);
	},
	
	_authoriseAccountComplete : function()
	{
		this._showLoading(true);
		this.oPagination.getCurrentPage();
	}
});

// Static

Object.extend(Component_Barring_Authorisation_Ledger,
{
	DATA_SET_DEFINITION			: {sObject: 'Barring', sMethod: 'getAuthorisationLedgerDataset'},
	MAX_RECORDS_PER_PAGE		: 10,
	FILTER_IMAGE_SOURCE			: '../admin/img/template/table_row_insert.png',
	REMOVE_FILTER_IMAGE_SOURCE	: '../admin/img/template/delete.png',
	AUTHORISE_IMAGE_SOURCE		: '../admin/img/template/approve.png',
	REQUIRED_CONSTANT_GROUPS	: ['barring_level'],
	YEAR_MINIMUM				: 2011,
	YEAR_MAXIMUM				: new Date().getFullYear(),
	
	// Sorting definitions
	SORT_FIELDS	:	
	{
		account_id					: Sort.DIRECTION_ASC,
		collection_scenario_name	: Sort.DIRECTION_OFF,
		current_barring_level_name	: Sort.DIRECTION_OFF,
		barring_level_name			: Sort.DIRECTION_OFF,
		created_datetime			: Sort.DIRECTION_OFF,
		created_employee_name		: Sort.DIRECTION_OFF
	},
		
	_getAllScenariosAsOptions : function(fnCallback, oResponse)
	{
		if (!oResponse)
		{
			var fnResponse	= Component_Barring_Authorisation_Ledger._getAllScenariosAsOptions.curry(fnCallback);
			var fnRequest 	= jQuery.json.jsonFunction(fnResponse, fnResponse, 'Collection_Scenario', 'getAll');
			fnRequest(false, true);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			jQuery.json.errorPopup(oResponse);
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

Component_Barring_Authorisation_Ledger.SORT_IMAGE_SOURCE						= {};
Component_Barring_Authorisation_Ledger.SORT_IMAGE_SOURCE[Sort.DIRECTION_OFF]	= '../admin/img/template/order_neither.png';
Component_Barring_Authorisation_Ledger.SORT_IMAGE_SOURCE[Sort.DIRECTION_ASC]	= '../admin/img/template/order_asc.png';
Component_Barring_Authorisation_Ledger.SORT_IMAGE_SOURCE[Sort.DIRECTION_DESC]	= '../admin/img/template/order_desc.png';

Component_Barring_Authorisation_Ledger._barringLevelStatusNames 																	= {};
Component_Barring_Authorisation_Ledger._barringLevelStatusNames[Component_Barring_Authorisation_Ledger.BARRING_LEVEL_STATUS_PENDING]		= 'Pending';
Component_Barring_Authorisation_Ledger._barringLevelStatusNames[Component_Barring_Authorisation_Ledger.BARRING_LEVEL_STATUS_AUTHORISED]	= 'Authorised';

//Filter Control field definitions (starts at 
Component_Barring_Authorisation_Ledger.FILTER_FIELDS	=
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
	barring_level_id :
	{
		iType	: Filter.FILTER_TYPE_VALUE,
		oOption	:
		{
			sType		: 'select',
			mDefault	: null,
			oDefinition	:
			{
				sLabel		: 'To (Barring Level)',
				mEditable	: true,
				mMandatory	: false,
				fnPopulate	: Component_Barring_Authorisation_Ledger._getConstantGroupOptions.curry('barring_level')
			}
		}
	},
	current_barring_level_id :
	{
		iType	: Filter.FILTER_TYPE_VALUE,
		oOption	:
		{
			sType		: 'select',
			mDefault	: null,
			oDefinition	:
			{
				sLabel		: 'From (Barring Level)',
				mEditable	: true,
				mMandatory	: false,
				fnPopulate	: Component_Barring_Authorisation_Ledger._getConstantGroupOptions.curry('barring_level')
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
				iYearStart	: Component_Barring_Authorisation_Ledger.YEAR_MINIMUM,
				iYearEnd	: Component_Barring_Authorisation_Ledger.YEAR_MAXIMUM
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
				sLabel		: 'Created By',
				mEditable	: true,
				mMandatory	: false,
				fnPopulate	: Employee.getAllAsSelectOptions.bind(Employee)
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
				fnPopulate	: Component_Barring_Authorisation_Ledger._getAllScenariosAsOptions
			}
		}
	}
};

