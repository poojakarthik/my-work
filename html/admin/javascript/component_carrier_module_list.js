



var Component_Carrier_Module_List = Class.create(
{
	initialize	: function(oLoadingPopup)
	{
		this._hFilters				= {};
		this._bFirstLoadComplete	= false;
		this._oElement				= $T.div({class: 'component-carrier-module-list'});
		this._oLoadingPopup			= oLoadingPopup;
		this._oOverlay 				= new Reflex_Loading_Overlay();
		
		// Load constants then create UI
		Flex.Constant.loadConstantGroup(Component_Carrier_Module_List.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
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
		this.oDataSet		= 	new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, Component_Carrier_Module_List.DATA_SET_DEFINITION);
		this.oPagination 	= 	new Pagination(
									this._updateTable.bind(this), 
									Component_Carrier_Module_List.MAX_RECORDS_PER_PAGE, 
									this.oDataSet
								);
		
		// Create Dataset filter object
		this._oFilter	=	new Filter(
								this.oDataSet,
								this.oPagination,
								this._showLoading.bind(this, true) 	// On field value change
							);

		// Add all filter fields
		for (var sFieldName in Component_Carrier_Module_List.FILTER_FIELDS)
		{
			if (Component_Carrier_Module_List.FILTER_FIELDS[sFieldName].iType)
			{
				this._oFilter.addFilter(sFieldName, Component_Carrier_Module_List.FILTER_FIELDS[sFieldName]);
			}
		}

		// Create Dataset sort object
		this._oSort	= new Sort(this.oDataSet, this.oPagination, true, this._showLoading.bind(this, true));
		
		// Create second dataset for getting selected instance ids (including ones not on the current page)
		this._oDataSetSelection = new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, Component_Carrier_Module_List.DATA_SET_DEFINITION);
		
		// Create the page HTML
		var sButtonPathBase	= '../admin/img/template/resultset_';
		var oSection		= new Section(true);
		this._oSection 		= oSection;
		this._oElement.appendChild(oSection.getElement());
		
		// Title
		oSection.setTitleContent(
			$T.span(
				$T.span('Carrier Modules'),
				$T.span({class: 'pagination-info'},
					''
				)
			)
		);
		
		// Header options
		oSection.addToHeaderOptions(
			$T.button({class: 'icon-button'},
				$T.img({src: '../admin/img/template/new.png'}),
				$T.span('Add Carrier Module')	
			).observe('click', this._addModule.bind(this))
		);
		
		// Main content -- table
		oSection.setContent(
			$T.table({class: 'component-carrier-module-list-table reflex highlight-rows'},
				$T.thead(
					// Column headings
					$T.tr({class: 'component-carrier-module-list-headerrow'},
						$T.th({class: 'component-carrier-module-list-id-header'},
							'#'
						),
						$T.th({class: 'component-carrier-module-list-carrier-header'},
							'Carrier'
						),
						$T.th({class: 'component-carrier-module-list-customergroup-header'},
							'Customer Group'
						),
						$T.th({class: 'component-carrier-module-list-type-header'},
							'Type'
						),
						$T.th({class: 'component-carrier-module-list-filetype-header'},
							'File Type'
						),
						$T.th('Module'),
						$T.th({class: 'component-carrier-module-list-description-header'},
							'Description'
						),
						$T.th({class: 'component-carrier-module-list-frequency-header'},
							'Frequency'
						),
						$T.th({class: 'component-carrier-module-list-lastsent-header'},
							'Last Sent'
						),
						$T.th({class: 'component-carrier-module-list-earliestdelivery-header'},
							'Earliest Delivery'
						),
						$T.th({class: 'component-carrier-module-list-status-header'},
							'Active'
						),
						$T.th({class: 'component-carrier-module-list-actions-header'},
							''
						) // Actions
					),
					// Filter values
					$T.tr(
						$T.th(),
						this._createFilterValueElement('carrier_id', 'Carrier'),
						this._createFilterValueElement('customer_group_id', 'Customer Group'),
						this._createFilterValueElement('carrier_module_type_id', 'Type'),
						this._createFilterValueElement('file_type_id', 'File Type'),
						$T.th(),
						this._createFilterValueElement('description', 'Description'),
						$T.th(),
						$T.th(),
						$T.th(),
						this._createFilterValueElement('is_active', 'Active'),
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
		var aHeaderRowTHs = this._oElement.select('.component-carrier-module-list-headerrow > th');
		this._registerSortHeader(aHeaderRowTHs[0], 'id');
		this._registerSortHeader(aHeaderRowTHs[1], 'carrier_name');
		this._registerSortHeader(aHeaderRowTHs[2], 'customer_group_name');
		this._registerSortHeader(aHeaderRowTHs[3], 'carrier_module_type_name');
		this._registerSortHeader(aHeaderRowTHs[4], 'file_type_name');
		this._registerSortHeader(aHeaderRowTHs[5], 'module');
		this._registerSortHeader(aHeaderRowTHs[6], 'description');
		this._registerSortHeader(aHeaderRowTHs[7], 'frequency_value');
		this._registerSortHeader(aHeaderRowTHs[8], 'last_sent_datetime');
		this._registerSortHeader(aHeaderRowTHs[9], 'earliest_delivery');
		this._registerSortHeader(aHeaderRowTHs[10], 'is_active_label');
				
		// Footer -- loading msg & pagination
		oSection.setFooterContent(
			$T.ul({class: 'reset horizontal component-carrier-module-list-options'},
				$T.li(
					$T.button({class: 'component-carrier-module-list-paginationbutton'},
						$T.img({src: sButtonPathBase + 'first.png'})
					)
				),
				$T.li(
					$T.button({class: 'component-carrier-module-list-paginationbutton'},
						$T.img({src: sButtonPathBase + 'previous.png'})
					)
				),
				$T.li(
					$T.button({class: 'component-carrier-module-list-paginationbutton'},
						$T.img({src: sButtonPathBase + 'next.png'})
					)
				),
				$T.li(
					$T.button({class: 'component-carrier-module-list-paginationbutton'},
						$T.img({src: sButtonPathBase + 'last.png'})
					)
				)
			)
		);

		// Bind events to the pagination buttons
		var aBottomPageButtons 	= this._oElement.select('.component-carrier-module-list-paginationbutton');
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
		if (bShow)
		{
			this._oOverlay.attachTo(this._oElement.select('table > tbody').first());
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
			// Create the row element
			var oActionTD 		= $T.td();
			var sStatusIconText = null;
			var sStatusIconSrc 	= null;
			var fnStatusOnClick	= null;
			if (oData.is_active)
			{
				sStatusIconText = 'Disable';
				sStatusIconSrc 	= '../admin/img/template/decline.png';
				fnStatusOnClick	= this._setModuleActive.bind(this, oData.id, false, null);
			}
			else
			{
				sStatusIconText = 'Enable';
				sStatusIconSrc 	= '../admin/img/template/approve.png';
				fnStatusOnClick	= this._setModuleActive.bind(this, oData.id, true, null);
			}
			
			// Status icon
			var oStatusIcon = $T.img({class: 'pointer', src: sStatusIconSrc, alt: sStatusIconText, title: sStatusIconText});
			oStatusIcon.observe('click', fnStatusOnClick);
			oActionTD.appendChild(oStatusIcon);
			
			// View icon
			var oViewIcon = $T.img({class: 'pointer', src: '../admin/img/template/magnifier.png', alt: 'View Module', title: 'View Module'});
			oViewIcon.observe('click', this._viewModule.bind(this, oData.id));
			oActionTD.appendChild(oViewIcon);
			
			// Edit icon
			var oEditIcon = $T.img({class: 'pointer', src: '../admin/img/template/pencil.png', alt: 'Edit Module', title: 'Edit Module'});
			oEditIcon.observe('click', this._editModule.bind(this, oData.id));
			oActionTD.appendChild(oEditIcon);
			
			// Clone icon
			var oCloneIcon = $T.img({class: 'pointer', src: '../admin/img/template/new.png', alt: 'Clone Module', title: 'Clone Module'});
			oCloneIcon.observe('click', this._cloneModule.bind(this, oData.id));
			oActionTD.appendChild(oCloneIcon);
			
			var oLastSentDate	= Date.$parseDate(oData.last_sent_datetime, 'Y-m-d H:i:s');
			var oLastSentTD		= $T.td()
			if (oLastSentDate)
			{
				oLastSentTD.appendChild($T.div(oLastSentDate.$format('d/m/y')));
				oLastSentTD.appendChild(
					$T.div({class: 'datetime-time'},
						oLastSentDate.$format('g:i A')
					)
				);
			}
			
			var oEarliestDeliveryDate = Date.$parseDate('1970-01-01 00:00:00', 'Y-m-d H:i:s');
			oEarliestDeliveryDate.setSeconds(oData.earliest_delivery);
			
			var	oTR	=	$T.tr(
							$T.td(oData.id),
							$T.td(oData.carrier_name),
							$T.td(oData.customer_group_name),
							$T.td(oData.carrier_module_type_name),
							$T.td(oData.file_type_name),
							$T.td({class: 'component-carrier-module-list-module-cell'},
								oData.module
							),
							$T.td({class: 'component-carrier-module-list-description-cell'},
								oData.description
							),
							$T.td(oData.frequency + ' ' + oData.frequency_type_name + (oData.frequency == 1 ? '' : 's')),
							oLastSentTD,
							$T.td(oEarliestDeliveryDate.$format('g:i A')),
							$T.td(oData.is_active_label),
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
		for (var sField in Component_Carrier_Module_List.SORT_FIELDS)
		{
			if (this._oSort.isRegistered(sField))
			{
				var oSortImg	= this._oElement.select('img.sort-' + sField).first();
				var iDirection	= this._oSort.getSortDirection(sField);
				oSortImg.src	= Component_Carrier_Module_List.SORT_IMAGE_SOURCE[iDirection];
				oSortImg.show();
			}
		}
	},

	_updateFilters	: function()
	{
		for (var sField in Component_Carrier_Module_List.FILTER_FIELDS)
		{
			if (Component_Carrier_Module_List.FILTER_FIELDS[sField].iType)
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
			var oSpan	= this._oElement.select('th.component-carrier-module-list-filter-heading > span.filter-' + sField).first();
			if (oSpan)
			{
				var oDeleteImage	= oSpan.up().select('img.component-carrier-module-list-filter-delete').first();
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
		var oDeleteImage				= $T.img({class: 'component-carrier-module-list-filter-delete', src: Component_Carrier_Module_List.REMOVE_FILTER_IMAGE_SOURCE, alt: 'Remove Filter', title: 'Remove Filter'});
		oDeleteImage.style.visibility	= 'hidden';
		oDeleteImage.observe('click', this._clearFilterValue.bind(this, sField));

		var oFiterImage	= $T.img({class: 'header-filter', src: Component_Carrier_Module_List.FILTER_IMAGE_SOURCE, alt: 'Filter by ' + sLabel, title: 'Filter by ' + sLabel});
		this._oFilter.registerFilterIcon(sField, oFiterImage, sLabel, this._oElement, 0, 10);

		return	$T.th({class: 'component-carrier-module-list-filter-heading'},
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
		
		oSpan.addClassName('component-carrier-module-list-header-sort');
		
		this._oSort.registerToggleElement(oSpan, sSortField, Component_Carrier_Module_List.SORT_FIELDS[sSortField]);
		this._oSort.registerToggleElement(oSortImg, sSortField, Component_Carrier_Module_List.SORT_FIELDS[sSortField]);
	},
	
	_formatFilterValueForDisplay	: function(sField, mValue)
	{
		var oDefinition	= Component_Carrier_Module_List.FILTER_FIELDS[sField];
		var aControls	= this._oFilter.getControlsForField(sField);
		var sValue		= '';
		switch (sField)
		{
			case 'carrier_id':
			case 'customer_group_id':
			case 'carrier_module_type_id':
			case 'file_type_id':
				sValue = aControls[0].getElementText();
				break;
				
			case 'is_active':
				sValue = (aControls[0].getElementValue() ? 'Yes' : 'No');
				break;
			
			case 'description':
				sValue = "Like '" + aControls[0].getElementValue() + "'";
				break;
		}

		return sValue;
	},
	
	_setModuleActive : function(iModuleId, bActive, oResponse)
	{
		if (!oResponse)
		{
			// Make request
			this._oLoading = new Reflex_Popup.Loading('Changing status...');
			this._oLoading.display();
			
			var fnResp 	= this._setModuleActive.bind(this, iModuleId, bActive);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Carrier_Module', 'setModuleActive');
			fnReq(iModuleId, bActive);
			return;
		}
		
		this._oLoading.hide();
		delete this._oLoading;
		
		if (!oResponse.bSuccess)
		{
			// Handle error
			Component_Carrier_Module_List._ajaxError(oResponse);
			return;
		}
		
		// Refresh table
		this.oPagination.getCurrentPage();
	},
	
	_addModule : function()
	{
		new Popup_Carrier_Module(null, null, this.oPagination.getCurrentPage.bind(this.oPagination));
	},
	
	_viewModule : function(iCarrierModuleId)
	{
		new Popup_Carrier_Module(Popup_Carrier_Module.MODE_VIEW, iCarrierModuleId, this.oPagination.getCurrentPage.bind(this.oPagination));
	},
	
	_editModule : function(iCarrierModuleId)
	{
		new Popup_Carrier_Module(Popup_Carrier_Module.MODE_EDIT, iCarrierModuleId, this.oPagination.getCurrentPage.bind(this.oPagination));
	},
	
	_cloneModule : function(iCarrierModuleId)
	{
		new Popup_Carrier_Module(Popup_Carrier_Module.MODE_CLONE, iCarrierModuleId, this.oPagination.getCurrentPage.bind(this.oPagination));
	}
});

// Static

Object.extend(Component_Carrier_Module_List,
{
	DATA_SET_DEFINITION			: new Reflex_AJAX_Request('Carrier_Module', 'getDataset'),
	MAX_RECORDS_PER_PAGE		: 10,
	FILTER_IMAGE_SOURCE			: '../admin/img/template/table_row_insert.png',
	REMOVE_FILTER_IMAGE_SOURCE	: '../admin/img/template/delete.png',
	REQUIRED_CONSTANT_GROUPS	: ['status'],
	
	// Sorting definitions
	SORT_FIELDS	:	
	{
		id 							: Sort.DIRECTION_ASC,
		carrier_name 				: Sort.DIRECTION_OFF,
		customer_group_name 		: Sort.DIRECTION_OFF,
		carrier_module_type_name 	: Sort.DIRECTION_OFF,
		file_type_name 				: Sort.DIRECTION_OFF,
		module 						: Sort.DIRECTION_OFF,
		description					: Sort.DIRECTION_OFF,
		frequency_value 			: Sort.DIRECTION_OFF,
		last_sent_datetime 			: Sort.DIRECTION_OFF,
		earliest_delivery 			: Sort.DIRECTION_OFF,
		is_active_label 			: Sort.DIRECTION_OFF
	},
	
	_ajaxError : function(oResponse)
	{
		var sMessage = (oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.');
		Reflex_Popup.alert(sMessage, {sTitle: 'Error', sDebugContent: oResponse.sDebug});
	},
	
	_getCarrierOptions : function(fnCallback, oResponse)
	{
		if (!oResponse)
		{
			var fnResponse	= Component_Carrier_Module_List._getCarrierOptions.curry(fnCallback);
			var fnRequest 	= jQuery.json.jsonFunction(fnResponse, fnResponse, 'Carrier', 'getCarriers');
			fnRequest();
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Component_Carrier_Module_List._ajaxError(oResponse);
			return;
		}
		
		var aOptions = [];
		for (var i = 0; i < oResponse.aRecords.length; i++)
		{
			aOptions.push(
				$T.option({value: oResponse.aRecords[i].Id},
					oResponse.aRecords[i].Name
				)
			);
		}
		fnCallback(aOptions);
	}
});

Component_Carrier_Module_List.SORT_IMAGE_SOURCE							= {};
Component_Carrier_Module_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_OFF]		= '../admin/img/template/order_neither.png';
Component_Carrier_Module_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_ASC]		= '../admin/img/template/order_asc.png';
Component_Carrier_Module_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_DESC]	= '../admin/img/template/order_desc.png';
 
Component_Carrier_Module_List.FILTER_FIELDS	=
{
	carrier_id	:
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
				fnPopulate	: Component_Carrier_Module_List._getCarrierOptions
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
				fnPopulate	: Customer_Group.getAllAsSelectOptions
			}
		}
	},
	carrier_module_type_id	:
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
				fnPopulate	: Flex.Constant.getConstantGroupOptions.curry('carrier_module_type')
			}
		}
	},
	file_type_id	:
	{
		iType	: Filter.FILTER_TYPE_VALUE,
		oOption	:
		{
			sType		: 'select',
			mDefault	: null,
			oDefinition	:
			{
				sLabel		: 'File Type',
				mEditable	: true,
				mMandatory	: false,
				fnPopulate	: Flex.Constant.getConstantGroupOptions.curry('resource_type')
			}
		}
	},
	is_active	:
	{
		iType	: Filter.FILTER_TYPE_VALUE,
		oOption	:
		{
			sType		: 'checkbox',
			mDefault	: null,
			oDefinition	:
			{
				sLabel		: 'Is Active',
				mEditable	: true,
				mMandatory	: false
			}
		}
	},
	description	:
	{
		iType	: Filter.FILTER_TYPE_CONTAINS,
		oOption	:
		{
			sType		: 'text',
			mDefault	: null,
			oDefinition	:
			{
				sLabel		: 'Description',
				mEditable	: true,
				mMandatory	: false
			}
		}
	}
};
