
var Component_Adjustment_Type_List = Class.create(
{
	initialize	: function(oContainerDiv, oLoadingPopup)
	{
		this._oContainerDiv = oContainerDiv;
		this._oLoadingPopup	= oLoadingPopup;
		
		// Create DataSet & pagination object
		this.oDataset = new Dataset_Ajax(
							Dataset_Ajax.CACHE_MODE_NO_CACHING, 
							{sObject: 'Adjustment_Type', sMethod: 'getDataset'}
						);
		this.oPagination = 	new Pagination(
								this._updateTable.bind(this),
								Component_Adjustment_Type_List.MAX_RECORDS_PER_PAGE, 
								this.oDataset
							);
		this._oFilter =	new Filter(
							this.oDataset,
							this.oPagination,
							this._showLoading.bind(this, true) 	// On field value change
						);
		this._oOverlay = new Reflex_Loading_Overlay();
		this._oElement = $T.div({class: 'page-adjustment-type'});
		
		// Add all filter fields
		for (var sFieldName in Component_Adjustment_Type_List.FILTER_FIELDS)
		{
			if (Component_Adjustment_Type_List.FILTER_FIELDS[sFieldName].iType)
			{
				this._oFilter.addFilter(sFieldName, Component_Adjustment_Type_List.FILTER_FIELDS[sFieldName]);
			}
		}

		// Create Dataset sort object
		this._oSort	= new Sort(this.oDataset, this.oPagination, true, this._showLoading.bind(this, true));
		
		Flex.Constant.loadConstantGroup(Component_Adjustment_Type_List.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
	},
	
	// Public
	
	getElement : function()
	{
		return this._oElement;
	},
	
	// Protected
	
	_buildUI : function()
	{
		// Create the page HTML
		var sButtonPathBase	= '../admin/img/template/resultset_';
		var oSection		= new Section(true);
		this._oSection 		= oSection;
		
		this._oElement.appendChild(oSection.getElement());
		
		// Title
		oSection.setTitleContent(
			$T.span(
				$T.span('Adjustment Types'),
				$T.span({class: 'pagination-info'},
					''
				)
			)
		);
		
		oSection.addToHeaderOptions(
			$T.button({class: 'icon-button'},
				$T.img({src: Component_Adjustment_Type_List.ADD_IMAGE_SOURCE, alt: '', title: 'Add Adjustment Type'}),
				$T.span('Add Adjustment Type')
			).observe('click', this._showAddPopup.bind(this))
		);
		
		// Main content -- table
		oSection.setContent(
			$T.table({class: 'page-adjustment-type-table reflex highlight-rows'},
				$T.thead(
					// Column headings
					$T.tr({class: 'page-adjustment-type-headerrow'},
						$T.th('Code'),
						$T.th('Description'),
						$T.th({colspan: '3'},
							'Amount ($ inc GST)'
						),
						$T.th('Visibility'),
						$T.th('Status'),
						$T.th('')
					)
				),
				$T.tbody({class: 'alternating'},
					this._createNoRecordsRow(true)
				)
			)
		);
		
		// Register sort headers
		// ... account id
		var aHeaderRowTHs = this._oElement.select('.page-adjustment-type-headerrow > th');
		this._registerSortHeader(aHeaderRowTHs[0], 'code');
		this._registerSortHeader(aHeaderRowTHs[1], 'description');
		this._registerSortHeader(aHeaderRowTHs[2], 'amount');
		this._registerSortHeader(aHeaderRowTHs[3], 'adjustment_type_invoice_visibility_name');
		this._registerSortHeader(aHeaderRowTHs[4], 'status_name');
		
		// Footer -- loading msg & pagination
		oSection.setFooterContent(
			$T.ul({class: 'reset horizontal page-adjustment-type-options'},
				$T.li(
					$T.button({class: 'page-adjustment-type-paginationbutton'},
						$T.img({src: sButtonPathBase + 'first.png'})
					)
				),
				$T.li(
					$T.button({class: 'page-adjustment-type-paginationbutton'},
						$T.img({src: sButtonPathBase + 'previous.png'})
					)
				),
				$T.li(
					$T.button({class: 'page-adjustment-type-paginationbutton'},
						$T.img({src: sButtonPathBase + 'next.png'})
					)
				),
				$T.li(
					$T.button({class: 'page-adjustment-type-paginationbutton'},
						$T.img({src: sButtonPathBase + 'last.png'})
					)
				)
			)
		);

		// Bind events to the pagination buttons
		var aBottomPageButtons 	= this._oElement.select('.page-adjustment-type-paginationbutton');
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
		
		this._oSort.sortField('code', Sort.DIRECTION_ASC);
		
		// Load the initial dataset
		this._oSort.refreshData(true);
		this._oFilter.refreshData(true);
		this._refreshPage();
		
		if (this._oContainerDiv)
		{
			this._oContainerDiv.appendChild(this._oElement);
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
	
	// _changePage: Executes the given function (name) on the dataset pagination object.
	_changePage	: function(sFunction)
	{
		this._showLoading(true);
		this.oPagination[sFunction]();
	},

	_refreshPage : function()
	{
		this._showLoading(true);
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
	
	_showAddPopup	: function()
	{
		new Popup_Adjustment_Type(this.oPagination.lastPage.bind(this.oPagination, true));
	},
	
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
	
	_createTableRow	: function(oData, bAlternateRow)
	{
		if (oData.id != null)
		{
			// Add CSS to the nature cell
			var sNatureTDClass = '';
			
			switch (oData.transaction_nature_code)
			{
				case 'DR':
					sNatureTDClass = 'charge-nature-debit';
					break;
				case 'CR':
					sNatureTDClass = 'charge-nature-credit';
					break;
			}
			
			var oActionTD = $T.td({class: 'page-adjustment-type-actions charge-archive'});
			if (oData.status_id == $CONSTANT.STATUS_ACTIVE)
			{
				// Add click event to the 'archive' button
				var oArchiveButton = $T.img({class: 'pointer', src: Component_Adjustment_Type_List.ARCHIVE_IMAGE_SOURCE, alt: 'Archive', title: 'Archive'});
				oArchiveButton.observe('click', this._archive.bind(this, oData.id, false));
				oActionTD.appendChild(oArchiveButton);
			}
			
			// Add a row with the charge types details, alternating class applied
			var	oTR	=	$T.tr(
							$T.td({class: 'page-adjustment-type-code'},
								oData.code
							),
							$T.td({class: 'page-adjustment-type-description'},
								oData.description
							),
							$T.td({class: 'page-adjustment-type-amount charge-amount-number'},
								new Number(oData.amount).toFixed(2)
							),
							$T.td({class: sNatureTDClass},
								oData.transaction_nature_code
							),
							$T.td({class: 'charge-amount-fixation'},
								oData.is_amount_fixed ? '(Fixed)' : ''
							),
							$T.td(oData.adjustment_type_invoice_visibility_name),
							$T.td(oData.status_name),
							oActionTD
						);
			
			return oTR;
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
	
	_archive	: function(iId, bConfirmed)
	{
		// Show yes, no, cancel
		if (!bConfirmed)
		{
			Reflex_Popup.yesNoCancel(	$T.div(
											$T.p('Archiving this Adjustment Type will make it unavailable for use.'),
											$T.p('Are you sure you want to archive it?')
										),
										{
											sTitle		: 'Archive Confirmation', 
											sYesLabel	: 'Yes, Archive', 
											sNoLabel	: 'No, do not Archive', 
											fnOnYes		: this._archive.bind(this, iId, true, false)
										});
			return;
		}
		
		this.oLoadingOverlay	= new Reflex_Popup.Loading('Archiving...');
		this.oLoadingOverlay.display();
		
		// Archive confirmed do the AJAX request
		var fnArchive = jQuery.json.jsonFunction(
							this._archiveComplete.bind(this), 
							this._archiveFailed.bind(this), 
							'Adjustment_Type', 
							'archiveAdjustmentType'
						);
		fnArchive(iId);
	},
	
	_archiveComplete	: function(oResponse)
	{
		if (oResponse.bSuccess)
		{
			// Handler for getPageCount
			var fnPageCountCallback	= function(iPageCount)
			{
				if (this.oPagination.intCurrentPage > (iPageCount - 1))
				{
					this.oPagination.lastPage(true);
				}
				else
				{
					this.oPagination.getCurrentPage();
				}
			}
			
			// Refresh the current page, or move to the last, if this page is empty
			this.oPagination.getPageCount(fnPageCountCallback.bind(this), true);
		}
		else
		{
			// Hide loading & show error popup
			this.oLoadingOverlay.hide();
			delete this.oLoadingOverlay;
			
			Reflex_Popup.alert((oResponse.Message ? oResponse.Message : ''), {sTitle: 'Error'});
		}
	},
	
	_archiveFailed	: function(oResponse) {
		jQuery.json.errorPopup(oResponse);
		
		// Close the loading popup
		if (this.oLoadingOverlay) {
			this.oLoadingOverlay.hide();
			delete this.oLoadingOverlay;
		}
	},

	_updateSorting	: function()
	{
		for (var sField in Component_Adjustment_Type_List.SORT_FIELDS)
		{
			if (this._oSort.isRegistered(sField))
			{
				var oSortImg	= this._oElement.select('img.sort-' + sField).first();
				var iDirection	= this._oSort.getSortDirection(sField);
				oSortImg.src	= Component_Adjustment_Type_List.SORT_IMAGE_SOURCE[iDirection];
				oSortImg.show();
			}
		}
	},

	_updateFilters	: function()
	{
		for (var sField in Component_Adjustment_Type_List.FILTER_FIELDS)
		{
			if (Component_Adjustment_Type_List.FILTER_FIELDS[sField].iType)
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
			var oSpan	= this._oElement.select('th.page-adjustment-type-filter-heading > span.filter-' + sField).first();
			if (oSpan)
			{
				var oDeleteImage	= oSpan.up().select('img.page-adjustment-type-filter-delete').first();
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
		var oDeleteImage				= $T.img({class: 'page-adjustment-type-filter-delete', src: Component_Adjustment_Type_List.REMOVE_FILTER_IMAGE_SOURCE, alt: 'Remove Filter', title: 'Remove Filter'});
		oDeleteImage.style.visibility	= 'hidden';
		oDeleteImage.observe('click', this._clearFilterValue.bind(this, sField));

		var oFiterImage	= $T.img({class: 'header-filter', src: Component_Adjustment_Type_List.FILTER_IMAGE_SOURCE, alt: 'Filter by ' + sLabel, title: 'Filter by ' + sLabel});
		this._oFilter.registerFilterIcon(sField, oFiterImage, sLabel, this._oElement, 0, 10);

		return	$T.th({class: 'page-adjustment-type-filter-heading'},
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
		
		oSpan.addClassName('page-adjustment-type-header-sort');
		
		this._oSort.registerToggleElement(oSpan, sSortField, Component_Adjustment_Type_List.SORT_FIELDS[sSortField]);
	},
	
	_formatFilterValueForDisplay	: function(sField, mValue)
	{
		var oDefinition	= Component_Adjustment_Type_List.FILTER_FIELDS[sField];
		var aControls	= this._oFilter.getControlsForField(sField);
		var sValue		= '';
		switch (sField)
		{
			case 'code':
			case 'description':
				sValue = aControls[0].getElementValue();
				break;
			
			case 'adjustment_type_invoice_visibility_id':
			case 'status_id':
				// Return the text (display) value of the control field
				sValue = aControls[0].getElementText();
				break;
		}

		return sValue;
	}
});

Object.extend(Component_Adjustment_Type_List, 
{
	REQUIRED_CONSTANT_GROUPS	: ['status'],
	MAX_RECORDS_PER_PAGE		: 20,
	ARCHIVE_IMAGE_SOURCE		: '../admin/img/template/delete.png',
	ADD_IMAGE_SOURCE			: '../admin/img/template/new.png',
	
	SORT_FIELDS	:	
	{
		code									: Sort.DIRECTION_OFF,
		description								: Sort.DIRECTION_OFF,
		amount									: Sort.DIRECTION_OFF,
		adjustment_type_invoice_visibility_name	: Sort.DIRECTION_OFF,
		status_name								: Sort.DIRECTION_OFF
	},
});

Component_Adjustment_Type_List.SORT_IMAGE_SOURCE						= {};
Component_Adjustment_Type_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_OFF]	= '../admin/img/template/order_neither.png';
Component_Adjustment_Type_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_ASC]	= '../admin/img/template/order_asc.png';
Component_Adjustment_Type_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_DESC]	= '../admin/img/template/order_desc.png';
