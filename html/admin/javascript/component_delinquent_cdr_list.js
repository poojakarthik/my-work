
var Component_Delinquent_CDR_List = Class.create(
{

	initialize	: function(oContainerDiv)
	{
		this._oLoadingPopup	= new Reflex_Popup.Loading();
		
		this._hFilters		= {};
		this._oReflexAnchor	= Reflex_Anchor.getInstance();
		
		this._bFirstLoadComplete		= false;
		this._hControlOnChangeCallbacks	= {};
		
		// Create DataSet & pagination object
		this.oDataSet	= new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, Component_Delinquent_CDR_List.DATA_SET_DEFINITION);
		this.oDataSet.setSortingFields({FNN: 'DESC'});
		
		this.oPagination	= new Pagination(this._updateTable.bind(this), Component_Delinquent_CDR_List.MAX_RECORDS_PER_PAGE, this.oDataSet);
		
		// Create filter object
		this._oFilter	=	new Filter(
								this.oDataSet, 
								this.oPagination, 
								null 	// On field value change
							);
		
		// Add and set a 'now' filter value (used for properly determining overdue-ness based on the clients time)
		this._oFilter.addFilter('now', {iType: Filter.FILTER_TYPE_VALUE});
		
		// Add all filter fields
		for (var sFieldName in Component_Delinquent_CDR_List.FILTER_FIELDS)
		{
			
			this._oFilter.addFilter(sFieldName, Component_Delinquent_CDR_List.FILTER_FIELDS[sFieldName]);
		}
		
		this._oFilter.setFilterValue(Component_Delinquent_CDR_List.FILTER_FIELD_SHOW_WRITEOFFS, 107);
		
		
		
		// Create sort object
		this._oSort	= new Sort(this.oDataSet, this.oPagination, true);
		
		
		this._earliestStartDatePicker =  Control_Field.factory('date-picker', Component_Delinquent_CDR_List.DATEPICKERCONFIG);
		this._earliestStartDatePicker.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._latestStartDatePicker = Control_Field.factory('date-picker', Component_Delinquent_CDR_List.DATEPICKERCONFIG);
		this._latestStartDatePicker.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		
		var cancelEarliestDate = $T.img({class:"followup-list-all-filter-delete", src: "../admin/img/template/delete.png", alt: 'Remove Date', title: 'Remove Date'}).observe('click', this._clearDatePickerValue.bind(this, this._earliestStartDatePicker));
		var cancelLatestDate = $T.img({class:"followup-list-all-filter-delete", src: "../admin/img/template/delete.png", alt: 'Remove Date', title: 'Remove Date'}).observe('click', this._clearDatePickerValue.bind(this, this._latestStartDatePicker));

		var searchButton = $T.button({class: 'icon-button'},
																$T.img({src: "../admin/img/template/table_refresh.png", alt: '', title: 'Refresh List'}),
																$T.span('Refresh List')
																	).observe('click', this._refresh.bind(this));
		
		
	
		// Create the page HTML
		var sButtonPathBase	= '../admin/img/template/resultset_';
		this._oContentDiv 	= 	$T.div({class: 'delinquent-fnn-list'},
									// All
									$T.div({class: 'section'},
										$T.div({class: 'section-header'},
											$T.div({class: 'section-header-title'},
												$T.span('Delinquent CDRs'),
												$T.span({class: 'followup-list-all-pagination-info'},
													''
												)
											),
											$T.div({class: 'section-header-options'},
												
												$T.span({class: 'header-label'},'Earliest CDR'),
												this._earliestStartDatePicker.getElement(),
												cancelEarliestDate,
												$T.span({class: 'header-label'},'Latest CDR'),
												this._latestStartDatePicker.getElement(),
												cancelLatestDate,
												searchButton
											)
										),
									
										$T.div({class: 'section-content section-content-fitted'},
											$T.table({class: 'reflex highlight-rows'},
												$T.thead(
													// Column headings
													$T.tr(
														this._createFieldHeader(
															'FNN', 
															Component_Delinquent_CDR_List.SORT_FIELD_FNN
														),
														this._createFieldHeader('Carrier', Component_Delinquent_CDR_List.SORT_FIELD_CARRIER),
														this._createFieldHeader('Cost', Component_Delinquent_CDR_List.SORT_FIELD_COST),
														this._createFieldHeader(
															'Count', 
															Component_Delinquent_CDR_List.SORT_FIELD_COUNT
														),
														this._createFieldHeader(
															'Earliest', 
															Component_Delinquent_CDR_List.SORT_FIELD_EARLIEST_CDR
														),
														this._createFieldHeader(
															'Latest', 
															Component_Delinquent_CDR_List.SORT_FIELD_LATEST_CDR	
														),
														this._createFieldHeader(
															'Status', 
															Component_Delinquent_CDR_List.SORT_FIELD_STATUS	
														),
														this._createFieldHeader(
															''
														)
													),
													// Filter values
													$T.tr(
														//$T.th(),
														this._createFilterValueElement(Component_Delinquent_CDR_List.FILTER_FIELD_FNN, 'FNN'),
														this._createFilterValueElement(Component_Delinquent_CDR_List.FILTER_FIELD_CARRIER, 'Carrier'),
														$T.th(),
														$T.th(),
														$T.th(),
														$T.th(),
														this._createFilterValueElement(Component_Delinquent_CDR_List.FILTER_FIELD_SHOW_WRITEOFFS, 'Include Written Off CDRs'),
														$T.th()
													)
												),
												$T.tbody({class: 'alternating'},
													this._createNoRecordsRow(true)
												)
											),
											$T.div({class: 'footer-pagination'},
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
		
		var aBottomPageButtons 	= this._oContentDiv.select('div.footer-pagination button');
		
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
	
	
		this._refresh();

	},
	
	_showWriteOffsChanged : function()
	{
		
		this._refresh();		
	},
	
	
	_clearDatePickerValue: function(oDatePicker)
	{
		oDatePicker.clearValue();	
	},
	
	_showLoading	: function(bShow)
	{

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
			this._oData = {};
			
			for (var i in aData)
			{
				if (typeof this._oData[aData[i].FNN] == 'undefined')
				{
					this._oData[aData[i].FNN] = {};
				}
				this._oData[aData[i].FNN][aData[i].Status] = aData[i];
				iCount++;
				oTBody.appendChild(this._createTableRow(aData[i]));
			}
		}
		
		this._bFirstLoadComplete	= true;
		this._updatePagination();
		this._updateSorting();
		this._updateFilters();
		
		// Call manual refresh on the followup link
		//FollowUpLink.refresh();
	
		this._showLoading(false);
	},
	
	_refresh: function()
	{
		
		this._oLoadingPopup.display();
		this._oFilter.setFilterValue(Component_Delinquent_CDR_List.FILTER_FIELD_EARLIEST_CDR, this._earliestStartDatePicker.getElementValue(), this._latestStartDatePicker.getElementValue() );
		this._oSort.refreshData(true);
		this._oFilter.refreshData(true);
		this.oPagination.getCurrentPage();
		this._updatePagination();
		this._updateSorting();
		this._updateFilters();
		this._oLoadingPopup.hide();
	
	},
	
	_createNoRecordsRow	: function(bOnLoad)
	{
		return $T.tr(
			$T.td({class: 'followup-list-all-norecords', colspan: 0},
				(bOnLoad ? 'Loading...' : 'There are no records to display')
			)
		);
	},
	

	
	_showCDRPopup : function(strStartDate, strEndDate, strFNN	,intCarrier, intServiceType, iStatus)
	{
		new Popup_CDR(strStartDate, strEndDate, strFNN	,intCarrier, intServiceType, iStatus, this._refresh.bind(this));	
	},
	
	_writeOff : function (oStatusCell, actionCell, sFNN, bConfirm, oResponse)
	{		
		var oFNN = this._oData[sFNN][107];
		if (!oResponse)
		{
			if (!bConfirm)
			{
						Reflex_Popup.yesNoCancel(
													"This will set the status for all CDRs with FNN " + sFNN + " to 'Delinquent Usage - Written Off'. Is that what you want to do?",
													{
														sNoLabel		: 'No', 
														sYesLabel		: 'Yes',														
														bOverrideStyle	: true,
														iWidth			: 45,
														sTitle			: 'CDR Writeoff',
														fnOnYes			: this._writeOff.bind(this,oStatusCell,actionCell, sFNN, true, false)														
													}
												);
			
			}
			else
			{
				
				this._oLoadingPopup.display();
				var fnRequest     = jQuery.json.jsonFunction(this._writeOff.bind(this, oStatusCell, actionCell,sFNN, true), null, 'CDR', 'bulkWriteOffForFNN');
				fnRequest(oFNN.EarliestStartDatetime, oFNN.LatestStartDatetime, oFNN.FNN, oFNN.Carrier, oFNN.ServiceType);
			}
		}
		else if (!oResponse.Success)
		{
			this._oLoadingPopup.hide();
			Component_Delinquent_CDR_List.serverErrorMessage(oResponse.sMessage, 'CDR Write Off Error');
		}
		
		else
		{
			var keys = Object.keys(oResponse.aData);
			oStatusCell.innerHTML =  oResponse.sStatus;
			actionCell.innerHTML = "";
			this._oLoadingPopup.hide();
			Reflex_Popup.alert('All CDRs have been written off succesfully.');
		}
	
	},
	
	_setService: function (oStatusCell, oActionCell, oResponse, strStartDate, strEndDate, strFNN	,intCarrier, intServiceType, iServiceId)
	{
		if (!oResponse)
		{			
			this._oLoadingPopup.display();
			var fnRequest     = jQuery.json.jsonFunction(this._setService.bind(this, oStatusCell, oActionCell), null, 'CDR', 'BulkAssignCDRsToServices');
			fnRequest(strFNN, intCarrier, intServiceType, strStartDate, strEndDate, iServiceId);		
		}
		else if (!oResponse.Success)
		{
			this._oLoadingPopup.hide();
			Component_Delinquent_CDR_List.serverErrorMessage(oResponse.sMessage, 'CDR Assignment Error');
		}
		else
		{			
			
			Reflex_Popup.alert('All CDRs have been assigned succesfully.');
	
			var keys = Object.keys(oResponse.aServiceInfo);
			oStatusCell.innerHTML = 'Assigned to Account ' + oResponse.aServiceInfo[0].account_id + ', FNN: ' + oResponse.aServiceInfo[0].fnn;
			oActionCell.innerHTML = "";
			this._oLoadingPopup.hide();			
		}	
	
	},
	
	_showServicesPopup: function(oStatusCell, oActionCell, strStartDate, strEndDate, strFNN	,intCarrier, intServiceType)
	{
		new Popup_CDR_Service_List(this._setService.bind(this, oStatusCell, oActionCell, false, strStartDate, strEndDate, strFNN	,intCarrier, intServiceType), strFNN ,intServiceType);
	},
	
	_setFNNFilter: function(FNN)
	{	
		this._oFilter.setFilterValue(Component_Delinquent_CDR_List.FILTER_FIELD_FNN, FNN);
		this._refresh();	
	},
	
	
	_createTableRow	: function(oCDR)
	{		
		var writeOff = "";
		var assign = "";
		var statusCell = $T.td({class: 'status'}, oCDR.StatusDescr);
		var actionCell = $T.td({class : "followup-list-all-action-icons"});
		
		if (oCDR.Status == 107)
		{
			actionCell.appendChild($T.img({class:"followup-list-all-action-icon", src: "../admin/img/template/delete.png", alt: 'Write Off', title: 'Write Off'}).observe('click', this._writeOff.bind(this, statusCell, actionCell,oCDR.FNN, false, false)));
			actionCell.appendChild($T.img({class:"followup-list-all-action-icon", src: "../admin/img/template/telephone_add.png", alt: 'Assign to Service', title: 'Assign to Service'}).observe('click', this._showServicesPopup.bind(this, statusCell, actionCell, oCDR.EarliestStartDatetime, oCDR.LatestStartDatetime, oCDR.FNN, oCDR.Carrier, oCDR.ServiceType, oCDR.Status)));
		}
		actionCell.appendChild($T.img({class:"followup-list-all-action-icon", src: "../admin/img/template/magnifier.png", alt: 'Show Details', title: 'Show Details'}).observe('click', this._showCDRPopup.bind(this, oCDR.EarliestStartDatetime, oCDR.LatestStartDatetime, oCDR.FNN, oCDR.Carrier, oCDR.ServiceType, oCDR.Status)));	
		
		var	oTR	=	$T.tr(
						$T.td({class: 'fnn', style: 'cursor: pointer'}, oCDR.FNN , $T.img({class: 'followup-list-all-header-filter', src: Component_Delinquent_CDR_List.FILTER_IMAGE_SOURCE, alt: 'Filter', title: 'Filter on this FNN'})).observe('click', this._setFNNFilter.bind(this, oCDR.FNN)),
						$T.td({class: 'carrier'},oCDR.carrier_label),
						$T.td(parseFloat(oCDR.TotalCost).toFixed(2)),
						$T.td(oCDR.Count),							
						$T.td(Component_Delinquent_CDR_List.getDateTimeElement(oCDR.EarliestStartDatetime)),
						$T.td(Component_Delinquent_CDR_List.getDateTimeElement(oCDR.LatestStartDatetime)),
						statusCell,
						actionCell						
					);			
		return oTR;		
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
			var oPageInfo		= this._oContentDiv.select('span.followup-list-all-pagination-info').first();
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
	
		for (var sField in Component_Delinquent_CDR_List.SORT_FIELDS)
		{
			if (this._oSort.isRegistered(sField))
			{
				var oSortImg	= this._oContentDiv.select('th.followup-list-all-header > img.followup-list-all-sort-' + sField).first();
				var iDirection	= this._oSort.getSortDirection(sField);
				if (iDirection == Sort.DIRECTION_OFF)
				{
					oSortImg.hide();
				}
				else
				{
					oSortImg.src	= Component_Delinquent_CDR_List.SORT_IMAGE_SOURCE[iDirection];
					oSortImg.show();
				}
			}
		}
	},
	
	_updateFilters	: function()
	{
		
		for (var sField in Component_Delinquent_CDR_List.FILTER_FIELDS)
		{
			this._updateFilterDisplayValue(sField);
		}
	},
	
	_updateFilterDisplayValue	: function(sField)
	{	
		if (this._oFilter.isRegistered(sField))
		{
			var mValue	= this._oFilter.getFilterValue(sField);
			var oSpan	= this._oContentDiv.select('th.followup-list-all-filter > span.followup-list-all-filter-' + sField).first();
			if (oSpan)
			{
				var oDeleteImage	= oSpan.up().select('img.followup-list-all-filter-delete').first();
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
		var oDeleteImage				= $T.img({class: 'followup-list-all-filter-delete', src: Component_Delinquent_CDR_List.REMOVE_FILTER_IMAGE_SOURCE, alt: 'Remove Filter', title: 'Remove Filter'});
		oDeleteImage.style.visibility	= 'hidden';
		oDeleteImage.observe('click', this._clearFilterValue.bind(this, sField));
		
		var oFiterImage	= $T.img({class: 'followup-list-all-header-filter', src: Component_Delinquent_CDR_List.FILTER_IMAGE_SOURCE, alt: 'Filter by ' + sLabel, title: 'Filter by ' + sLabel});
		this._oFilter.registerFilterIcon(sField, oFiterImage, sLabel);
		
		return	$T.th({class: 'followup-list-all-filter'},
					$T.span({class: 'followup-list-all-filter-' + sField},
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
		var oSortImg	= $T.img({class: 'followup-list-all-sort-' + (sSortField ? sSortField : '')});
		var oTH			= 	$T.th({class: 'followup-list-all-header' + (bMultiLine ? '-multiline' : '')},
								oSortImg,
								$T.span(sLabel)
							);
		oSortImg.hide();
		
		// Optional sort field
		if (sSortField)
		{
			var oSpan	= oTH.select('span').first();
			oSpan.addClassName('followup-list-all-header-sort');
			
			this._oSort.registerToggleElement(oSpan, sSortField, Component_Delinquent_CDR_List.SORT_FIELDS[sSortField]);
			this._oSort.registerToggleElement(oSortImg, sSortField, Component_Delinquent_CDR_List.SORT_FIELDS[sSortField]);
		}
				
		return oTH;
	},
	
	_formatFilterValueForDisplay	: function(sField, mValue)
	{
		
		if (sField == Component_Delinquent_CDR_List.FILTER_FIELD_FNN)
		{
			return mValue;
		}
		else
		{
		
		var oDefinition	= Component_Delinquent_CDR_List.FILTER_FIELDS[sField];
		var aControls	= this._oFilter.getControlsForField(sField);
		var sValue		= '';
		
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
		
		return sValue;
		}
	},
});

Component_Delinquent_CDR_List.MAX_RECORDS_PER_PAGE		= 15;

Component_Delinquent_CDR_List.SORT_IMAGE_SOURCE						= {};
Component_Delinquent_CDR_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_ASC]	= '../admin/img/template/order_asc.png';
Component_Delinquent_CDR_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_DESC]	= '../admin/img/template/order_desc.png';
Component_Delinquent_CDR_List.EDIT_IMAGE_SOURCE			= '../admin/img/template/pencil.png';
Component_Delinquent_CDR_List.FILTER_IMAGE_SOURCE			= '../admin/img/template/table_row_insert.png';
Component_Delinquent_CDR_List.REMOVE_FILTER_IMAGE_SOURCE	= '../admin/img/template/delete.png';


Component_Delinquent_CDR_List.FILTER_FIELD_OWNER			= 'assigned_employee_id';
Component_Delinquent_CDR_List.FILTER_FIELD_EARLIEST_CDR	= 'StartDatetime';
Component_Delinquent_CDR_List.FILTER_FIELD_LATEST_CDR = 'EndDatetime';
Component_Delinquent_CDR_List.FILTER_FIELD_FNN			= 'FNN';
Component_Delinquent_CDR_List.FILTER_FIELD_COST	= 'TotalCost';
Component_Delinquent_CDR_List.FILTER_FIELD_COUNT			= 'Count';
Component_Delinquent_CDR_List.FILTER_FIELD_CARRIER = 'Carrier';
Component_Delinquent_CDR_List.FILTER_FIELD_SHOW_WRITEOFFS = 'Status';


Component_Delinquent_CDR_List.SORT_FIELD_EARLIEST_CDR		= 'EarliestStartDatetime';
Component_Delinquent_CDR_List.SORT_FIELD_LATEST_CDR			= 'LatestStartDatetime';

Component_Delinquent_CDR_List.SORT_FIELD_FNN				= 'FNN';
Component_Delinquent_CDR_List.SORT_FIELD_COUNT				= 'Count';
Component_Delinquent_CDR_List.SORT_FIELD_CARRIER			= 'carrier_label';
Component_Delinquent_CDR_List.SORT_FIELD_COST				= 'TotalCost';
Component_Delinquent_CDR_List.SORT_FIELD_STATUS				= 'StatusDescr';

Component_Delinquent_CDR_List.RANGE_FILTER_DATE_REGEX			= /^(\d{4}-\d{2}-\d{2})(\s\d{2}:\d{2}:\d{2})?$/;
Component_Delinquent_CDR_List.RANGE_FILTER_FROM_MINUTES		= '00:00:00';
Component_Delinquent_CDR_List.RANGE_FILTER_TO_MINUTES			= '23:59:59';

Component_Delinquent_CDR_List.DATA_SET_DEFINITION			= {sObject: 'CDR', sMethod: 'getDelinquentDataSet'};



Component_Delinquent_CDR_List.getDateTimeElement	= function(sMySQLDate)
{
	var oDate	= new Date(Date.parse(sMySQLDate.replace(/-/g, '/')));
	var sDate	= oDate.$format('d/m/Y');
	
	return sDate;
	
};



Component_Delinquent_CDR_List.getCarrierList = function(fCallback, oResponse)
{
	
	if (!oResponse)
	{
		// Make Request for all active employees sorted by first name then last name 
		var fn	=	jQuery.json.jsonFunction(
								Component_Delinquent_CDR_List.getCarrierList.bind(Component_Delinquent_CDR_List.getCarrierList,fCallback ), 
								null, 
								'CDR', 
								'getCarrierList'
							);
		fn();
	}
	else
	{
		
		if (!oResponse.Success)
		{
			Component_Delinquent_CDR_List.serverErrorMessage(oResponse.sMessage, 'CDR Carrier List Retrieval Error');
		}
		else
		{	
			// Create an Array of OPTION DOM Elements
			var oResults	= jQuery.json.arrayAsObject(oResponse.aCarriers);
			var aOptions	= [];
			for (i in oResults)
			{
				aOptions.push(
					$T.option({value: oResults[i].Carrier},
							oResults[i].carrier_label
					)
				);		
			}
			
			// Pass to Callback
			fCallback(aOptions);
		}
	
	}
};

Component_Delinquent_CDR_List.getStatusList = function (fCallback, oResponse)
{
	
	if (!oResponse)
	{
		// Make Request for all active employees sorted by first name then last name 
		var fn	=	jQuery.json.jsonFunction(
								Component_Delinquent_CDR_List.getStatusList.bind(Component_Delinquent_CDR_List.getStatusList,fCallback ), 
								null, 
								'CDR', 
								'getStatusList'
							);
		fn();
	}
	else
	{
		
		if (!oResponse.Success)
		{
			Component_Delinquent_CDR_List.serverErrorMessage(oResponse.sMessage, 'CDR Status List Retrieval Error');
		}
		else
		{		
			// Create an Array of OPTION DOM Elements
			var oResults	= jQuery.json.arrayAsObject(oResponse.aData);
			var aOptions	= [];
			for (i in oResults)
			{
				aOptions.push(
					$T.option({value: i},
							oResults[i]
					)
				);		
			}
			
			// Pass to Callback
			fCallback(aOptions);
		}
	}


};




Component_Delinquent_CDR_List.isValidFNN		= function(strFNN)
{
	
	return true;
},


Component_Delinquent_CDR_List.serverErrorMessage = function (sMessage,sTitle)
{
	
	var detailsDiv = document.createElement('div');
	detailsDiv.innerHTML = sMessage;
	detailsDiv.style.display = 'none';
	detailsDiv.className = 'error-details';
	var div = document.createElement('div');
	div.observe('click', Component_Delinquent_CDR_List._toggleErrorDetails.bind(this,detailsDiv));
	div.innerHTML = "Error Details";
	div.className = 'details-link';
	var containerDiv = document.createElement('div');
	containerDiv.innerHTML = "There was a server processing error. Please contact YBS for assistance.";
	containerDiv.appendChild(div);
	containerDiv.appendChild(detailsDiv);
	containerDiv.className = "email-template-error";	
	Reflex_Popup.alert(containerDiv, {sTitle: sTitle});
};

Component_Delinquent_CDR_List._toggleErrorDetails = function (oDiv)
{
	oDiv.style.display == 'none'?oDiv.style.display = '':oDiv.style.display = 'none';

};

Component_Delinquent_CDR_List.DATEPICKERCONFIG = 			{
																sLabel		: 'Change On', 
																sDateFormat	: 'Y-m-d', 
																bTimePicker	: false,
																iYearStart	: 2010,
																iYearEnd	: new Date().getFullYear() + 1,
																mMandatory	: false,
																mEditable	: true,
																mVisible	: true,
																bDisableValidationStyling	: false,
																fnValidate	: null
															}



Component_Delinquent_CDR_List.FILTER_FIELDS														= {};
Component_Delinquent_CDR_List.FILTER_FIELDS[Component_Delinquent_CDR_List.FILTER_FIELD_CARRIER]		= 	{
																										 iType	: Filter.FILTER_TYPE_VALUE,
																										 oOption	: 	{
																														 sType		: 'select',
																														 mDefault	: null,
																														 oDefinition	:	{
																																			 sLabel		: 'Carrier',
																																			 mEditable	: true,
																																			 mMandatory	: false,
																																			 fnValidate	: null,
																																			 fnPopulate	: Component_Delinquent_CDR_List.getCarrierList.bind(Component_Delinquent_CDR_List)
																																		 }
																													 }
																									 };
																									 
																									 
																									 
Component_Delinquent_CDR_List.FILTER_FIELDS[Component_Delinquent_CDR_List.FILTER_FIELD_SHOW_WRITEOFFS] = 	{
																							 iType	: Filter.FILTER_TYPE_VALUE,
																							 oOption	: 	{
																											 sType		: 'select',
																											 mDefault	: null,
																											 oDefinition	:	{
																																 sLabel		: 'Include Written Off CDRs',
																																mEditable	: true,
																																 mMandatory	: false,
																																 fnPopulate	: Component_Delinquent_CDR_List.getStatusList.bind(Component_Delinquent_CDR_List)
																																 
																															 }
																										 }
																						};																										 
																									 
																								 
																									 
Component_Delinquent_CDR_List.FILTER_FIELDS[Component_Delinquent_CDR_List.FILTER_FIELD_FNN]		= 	{
																							 iType	: Filter.FILTER_TYPE_VALUE,
																							 oOption	: 	{
																											 sType		: 'text',
																											 mDefault	: null,
																											 oDefinition	:	{
																																 sLabel		: 'FNN',
																																mEditable	: true,
																																 mMandatory	: false,																																 
																																 fnValidate		:Component_Delinquent_CDR_List.isValidFNN.bind(Component_Delinquent_CDR_List)																														 
																															 }
																										 }
																						};																										 
																									 
																									 
Component_Delinquent_CDR_List.FILTER_FIELDS[Component_Delinquent_CDR_List.FILTER_FIELD_EARLIEST_CDR]	= 	{
																											iType			: Filter.FILTER_TYPE_RANGE,
																											bFrom			: true,
																											sFrom			: 'Start Date',
																											bTo				: true,
																											sTo				: 'End Date',
																											sFromOption		: 'On Or After',
																											sToOption		: 'On Or Before',
																											sBetweenOption	: 'Between',
																											oOption			: 	{
																																	sType		: 'date-picker',
																																	mDefault	: null,
																																	oDefinition	:	{
																																						sLabel		: 'Date',
																																						mEditable	: true,
																																						mMandatory	: false,
																																						fnValidate	: Component_Delinquent_CDR_List._validateDueDate,
																																						sDateFormat	: 'Y-m-d',
																																						iYearStart	: Component_Delinquent_CDR_List.YEAR_MINIMUM,
																																						iYearEnd	: Component_Delinquent_CDR_List.YEAR_MAXIMUM
																																					}
																																}
																										};

// Sorting definitions
Component_Delinquent_CDR_List.SORT_FIELDS	=	{
													FNN						: Sort.DIRECTION_OFF,
													carrier_label			: Sort.DIRECTION_OFF,
													TotalCost				: Sort.DIRECTION_OFF,
													Count					: Sort.DIRECTION_OFF,
													StatusDescr				: Sort.DIRECTION_OFF,
													EarliestStartDatetime	: Sort.DIRECTION_DESC,
													LatestStartDatetime		: Sort.DIRECTION_OFF
												};
											

