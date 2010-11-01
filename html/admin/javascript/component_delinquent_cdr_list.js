
var Component_Delinquent_CDR_List = Class.create(
{
	/*
	 * iEmployeeId & bEditMode are used to determine which actions can be performed on a follow-up.
	 * 
	 * If bEditMode is true, then all can be edited, reassigned and closed.
	 * 
	 * If bEditMode is false, then only those who belong to iEmployeeId can be closed or edited (not reassigned).
	 */
	initialize	: function(oContainerDiv, iEmployeeId, bEditMode, bActive, sStartDate, sEndDate)
	{
		this._oLoadingPopup	= new Reflex_Popup.Loading();
		this._iEmployeeId	= iEmployeeId;
		this._bEditMode		= bEditMode;
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
		
		
		// //First, the added header options
				// this._oShowWriteOffs = Control_Field.factory('checkbox', {
																			// sLabel		: 'Show Written Off CDRs',
																			// mMandatory	: true,
																			// mEditable	: true,
																			// mVisible	: true,
																			// bDisableValidationStyling	: true
																		// });
				
				// this._oShowWriteOffs.addOnChangeCallback(this._showWriteOffsChanged.bind(this));				
				

			
			
			
			

		
		
		
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
												// $T.span({class: 'header-label'}, 'Show Written Off CDRs'),
												// $T.span({class: 'header-label'}, this._oShowWriteOffs.getElement()),
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
		//var aTopPageButtons		= this._oContentDiv.select('div.section-header-options button.followup-list-all-pagination-button');
		var aBottomPageButtons 	= this._oContentDiv.select('div.footer-pagination button');
		
		// First
		//aTopPageButtons[0].observe('click', this._changePage.bind(this, 'firstPage'));
		aBottomPageButtons[0].observe('click', this._changePage.bind(this, 'firstPage'));
		
		//Previous		
		//aTopPageButtons[1].observe('click', this._changePage.bind(this, 'previousPage'));
		aBottomPageButtons[1].observe('click', this._changePage.bind(this, 'previousPage'));
		
		// Next
		//aTopPageButtons[2].observe('click', this._changePage.bind(this, 'nextPage'));
		aBottomPageButtons[2].observe('click', this._changePage.bind(this, 'nextPage'));
		
		// Last
		//aTopPageButtons[3].observe('click', this._changePage.bind(this, 'lastPage'));
		aBottomPageButtons[3].observe('click', this._changePage.bind(this, 'lastPage'));
		
		// Setup pagination button object
		this.oPaginationButtons = {
			// oTop	: {
				// oFirstPage		: aTopPageButtons[0],
				// oPreviousPage	: aTopPageButtons[1],
				// oNextPage		: aTopPageButtons[2],
				// oLastPage		: aTopPageButtons[3]
			// },
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
		this._refresh();
		//this._oSort.refreshData(true);
		//this._oFilter.refreshData(true);
		//this.oPagination.getCurrentPage();
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
		// var oLoading	= this._oContentDiv.select('span.pagination-loading').first();
		// if (bShow)
		// {
			// oLoading.show();
		// }
		// else
		// {
			// oLoading.hide();
		// }
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
		this._oFilter.setFilterValue(Component_Delinquent_CDR_List.FILTER_FIELD_EARLIEST_CDR, this._earliestStartDatePicker.getElementValue(), this._latestStartDatePicker.getElementValue() );
		this._oSort.refreshData(true);
		this._oFilter.refreshData(true);
		this.oPagination.getCurrentPage();
	
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
	
		debugger;
		new Popup_CDR(strStartDate, strEndDate, strFNN	,intCarrier, intServiceType, iStatus);
	
	
	
	},
	
	_writeOff : function (sFNN, bConfirm, oResponse)
	{
		
		var oFNN = this._oData[sFNN][107];
		if (!oResponse)
		{
			if (!bConfirm)
			{
						Reflex_Popup.yesNoCancel(
													"This will set the status for all CDRs with FNN " + sFNN + " to Write Off. Is that what you want to do?",
													{
														sNoLabel		: 'No', 
														sYesLabel		: 'Yes',														
														bOverrideStyle	: true,
														iWidth			: 45,
														sTitle			: 'CDR Writeoff',
														fnOnYes			: this._writeOff.bind(this,sFNN, true, false)														
													}
												);
			
			}
			else
			{
				
				this._oLoadingPopup.display();
				var fnRequest     = jQuery.json.jsonFunction(this._writeOff.bind(this, sFNN, true), null, 'CDR', 'bulkWriteOffForFNN');
				fnRequest(oFNN.EarliestStartDatetime, oFNN.LatestStartDatetime, oFNN.FNN, oFNN.Carrier, oFNN.ServiceType);
			}
		}
		else
		{
			this._oLoadingPopup.hide();
			Reflex_Popup.alert('All CDRs for FNN ' + sFNN + ' have been written off succesfully');
		}
	
	},
	
	_setService: function (oStatusCell, oResponse, strStartDate, strEndDate, strFNN	,intCarrier, intServiceType, iServiceId)
	{
		if (!oResponse)
		{
			
			
			// AssignCDRsToServices($sFNN, $iCarrier, $iServiceType, $sCDRs)
			
			var fnRequest     = jQuery.json.jsonFunction(this._setService.bind(this, oStatusCell), null, 'CDR', 'BulkAssignCDRsToServices');
			//fnRequest(this.oDataSet._hSort, this.oDataSet._hFilter);
			fnRequest(strFNN, intCarrier, intServiceType, strStartDate, strEndDate, iServiceId);
		
		}
		else
		{
			
			oStatusCell.innerHTML = 'Assigned to Account: ' + oResponse.aData[iCDRId].account_id + ', Service Id: ' + oResponse.aData[iCDRId].service_id;
			
		
		
		}
		
	
	
	},
	
	_showServicesPopup: function(oStatusCell, strStartDate, strEndDate, strFNN	,intCarrier, intServiceType)
	{	
		
		
		new Popup_CDR_Service_List(this._setService.bind(this, oStatusCell, false, strStartDate, strEndDate, strFNN	,intCarrier, intServiceType), null, strStartDate, strEndDate, strFNN	,intCarrier, intServiceType);

	},
	
	
	_createTableRow	: function(oCDR)
	{
		
		var writeOff = "";
		var assign = "";
		var viewDetails = viewDetails = $T.img({class:"followup-list-all-action-icon", src: "../admin/img/template/magnifier.png", alt: 'Show Details', title: 'Show Details'}).observe('click', this._showCDRPopup.bind(this, oCDR.EarliestStartDatetime, oCDR.LatestStartDatetime, oCDR.FNN, oCDR.Carrier, oCDR.ServiceType, oCDR.Status));
		var statusCell = $T.td(oCDR.StatusDescr);
		
		if (oCDR.Status == 107)
		{
			writeOff = $T.img({class:"followup-list-all-action-icon", src: "../admin/img/template/delete.png", alt: 'Write Off', title: 'Write Off'}).observe('click', this._writeOff.bind(this, oCDR.FNN, false, false));
			assign = $T.img({class:"followup-list-all-action-icon", src: "../admin/img/template/telephone_add.png", alt: 'Assign to Service', title: 'Assign to Service'}).observe('click', this._showServicesPopup.bind(this, statusCell, oCDR.EarliestStartDatetime, oCDR.LatestStartDatetime, oCDR.FNN, oCDR.Carrier, oCDR.ServiceType, oCDR.Status));
		}
			
		var	oTR	=	$T.tr(
						$T.td(oCDR.FNN),
						$T.td(oCDR.carrier_label),
						$T.td(oCDR.TotalCost),
						$T.td(oCDR.Count),							
						$T.td(oCDR.EarliestStartDatetime),
						$T.td(oCDR.LatestStartDatetime),
						statusCell,
						$T.td({class : "followup-list-all-action-icons"},writeOff, assign, viewDetails)
					);
			

			
		return oTR;
		
	},
	
	_updatePagination : function(iPageCount)
	{
		// Update the 'disabled' state of each pagination button
		//this.oPaginationButtons.oTop.oFirstPage.disabled 		= true;
		this.oPaginationButtons.oBottom.oFirstPage.disabled 	= true;
		//this.oPaginationButtons.oTop.oPreviousPage.disabled		= true;
		this.oPaginationButtons.oBottom.oPreviousPage.disabled	= true;
		//this.oPaginationButtons.oTop.oNextPage.disabled 		= true;
		this.oPaginationButtons.oBottom.oNextPage.disabled 		= true;
		//this.oPaginationButtons.oTop.oLastPage.disabled 		= true;
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
				//this.oPaginationButtons.oTop.oFirstPage.disabled 		= false;
				this.oPaginationButtons.oBottom.oFirstPage.disabled		= false;
				//this.oPaginationButtons.oTop.oPreviousPage.disabled 	= false;
				this.oPaginationButtons.oBottom.oPreviousPage.disabled 	= false;
			}
			if (this.oPagination.intCurrentPage < (iPageCount - 1) && iPageCount)
			{
				// Enable the next and last buttons
				//this.oPaginationButtons.oTop.oNextPage.disabled 	= false;
				this.oPaginationButtons.oBottom.oNextPage.disabled 	= false;
				//this.oPaginationButtons.oTop.oLastPage.disabled 	= false;
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
		var oDefinition	= Component_Delinquent_CDR_List.FILTER_FIELDS[sField];
		var aControls	= this._oFilter.getControlsForField(sField);
		var sValue		= '';
		switch (sField)
		{
			case Component_Delinquent_CDR_List.FILTER_FIELD_FOLLOWUP_DATE:
				var oState		= this._oFilter.getFilterState(sField);
				var bGotFrom	= mValue.mFrom != null;
				var bGotTo		= mValue.mTo != null;
				var sFrom		= (bGotFrom ? Component_Delinquent_CDR_List.formatDateTimeFilterValue(mValue.mFrom) : null);
				var sTo			= (bGotTo ? Component_Delinquent_CDR_List.formatDateTimeFilterValue(mValue.mTo) : null);
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
			case Component_Delinquent_CDR_List.FILTER_FIELD_OWNER:
			case Component_Delinquent_CDR_List.FILTER_FIELD_TYPE:
			case Component_Delinquent_CDR_List.FILTER_FIELD_CATEGORY:
			case Component_Delinquent_CDR_List.FILTER_FIELD_STATUS:
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

Component_Delinquent_CDR_List.MAX_RECORDS_PER_PAGE		= 10;
Component_Delinquent_CDR_List.EDIT_IMAGE_SOURCE			= '../admin/img/template/pencil.png';
Component_Delinquent_CDR_List.FILTER_IMAGE_SOURCE			= '../admin/img/template/table_row_insert.png';
Component_Delinquent_CDR_List.REMOVE_FILTER_IMAGE_SOURCE	= '../admin/img/template/delete.png';

Component_Delinquent_CDR_List.ACTION_CLOSE_IMAGE_SOURCE			= '../admin/img/template/approve.png';
Component_Delinquent_CDR_List.ACTION_DISMISS_IMAGE_SOURCE			= '../admin/img/template/decline.png';
Component_Delinquent_CDR_List.ACTION_EDIT_DATE_IMAGE_SOURCE		= '../admin/img/template/edit_date.png';
Component_Delinquent_CDR_List.ACTION_REASSIGN_IMAGE_SOURCE		= '../admin/img/template/user_edit.png';
Component_Delinquent_CDR_List.ACTION_INV_PAYMENTS_IMAGE_SOURCE	= '../admin/img/template/invoices_payments.png';
Component_Delinquent_CDR_List.ACTION_RECURRING_IMAGE_SOURCE		= '../admin/img/template/followup_recurring.png';
Component_Delinquent_CDR_List.ACTION_VIEW_IMAGE_SOURCE			= '../admin/img/template/magnifier.png';

Component_Delinquent_CDR_List.SORT_IMAGE_SOURCE						= {};
Component_Delinquent_CDR_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_ASC]	= '../admin/img/template/order_asc.png';
Component_Delinquent_CDR_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_DESC]	= '../admin/img/template/order_desc.png';

Component_Delinquent_CDR_List.TYPE_NOTE_IMAGE_SOURCE					= '../admin/img/template/followup_note.png';
Component_Delinquent_CDR_List.TYPE_ACTION_IMAGE_SOURCE				= '../admin/img/template/followup_action.png';
Component_Delinquent_CDR_List.TYPE_TICKET_CORRESPONDENCE_IMAGE_SOURCE	= '../admin/img/template/tickets.png';

Component_Delinquent_CDR_List.DETAILS_ACCOUNT_IMAGE_SOURCE			= '../admin/img/template/account.png';
Component_Delinquent_CDR_List.DETAILS_ACCOUNT_CONTACT_IMAGE_SOURCE	= '../admin/img/template/contact_small.png';
Component_Delinquent_CDR_List.DETAILS_ACCOUNT_SERVICE_IMAGE_SOURCE	= '../admin/img/template/service.png';
Component_Delinquent_CDR_List.DETAILS_TICKET_IMAGE_SOURCE				= Component_Delinquent_CDR_List.TYPE_TICKET_CORRESPONDENCE_IMAGE_SOURCE;

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
//Component_Delinquent_CDR_List.SORT_FIELD_FOLLOWUP_DATE	= 'StartDatetime';
Component_Delinquent_CDR_List.SORT_FIELD_FNN				= 'FNN';
Component_Delinquent_CDR_List.SORT_FIELD_COUNT				= 'Count';
Component_Delinquent_CDR_List.SORT_FIELD_CARRIER			= 'carrier_label';
Component_Delinquent_CDR_List.SORT_FIELD_COST				= 'TotalCost';
Component_Delinquent_CDR_List.SORT_FIELD_STATUS				= 'StatusDescr';

Component_Delinquent_CDR_List.RANGE_FILTER_DATE_REGEX			= /^(\d{4}-\d{2}-\d{2})(\s\d{2}:\d{2}:\d{2})?$/;
Component_Delinquent_CDR_List.RANGE_FILTER_FROM_MINUTES		= '00:00:00';
Component_Delinquent_CDR_List.RANGE_FILTER_TO_MINUTES			= '23:59:59';

Component_Delinquent_CDR_List.DATA_SET_DEFINITION			= {sObject: 'CDR', sMethod: 'getDelinquentDataSet'};

// Helper functions
Component_Delinquent_CDR_List._getTypeElement	= function(iType)
{
	if (Flex.Constant.arrConstantGroups.followup_type)
	{
		var sAlt	= Flex.Constant.arrConstantGroups.followup_type[iType].Name;
		var sImgSrc	= '';
		
		switch (iType)
		{
			case $CONSTANT.FOLLOWUP_TYPE_NOTE:
				sImgSrc	= Component_Delinquent_CDR_List.TYPE_NOTE_IMAGE_SOURCE;
				break;
			case $CONSTANT.FOLLOWUP_TYPE_ACTION:
				sImgSrc	= Component_Delinquent_CDR_List.TYPE_ACTION_IMAGE_SOURCE;
				break;
			case $CONSTANT.FOLLOWUP_TYPE_TICKET_CORRESPONDENCE:
				sImgSrc	= Component_Delinquent_CDR_List.TYPE_TICKET_CORRESPONDENCE_IMAGE_SOURCE;
				break;
		}
		
		return $T.img({src: sImgSrc, alt: sAlt, title: sAlt});
	}
	
	return 'Error';
};

Component_Delinquent_CDR_List._getAllTypesAsOptions	= function(fCallback)
{
	var aOptions	= [];
	for (var iType in Flex.Constant.arrConstantGroups.followup_type)
	{
		aOptions.push(
			$T.option({value: iType},
				Flex.Constant.arrConstantGroups.followup_type[iType].Name
			)
		);
	}
	
	if (fCallback)
	{
		fCallback(aOptions);
	}
};

Component_Delinquent_CDR_List._validateDueDate	= function(sValue)
{
	if (isNaN(Date.parse(sValue.replace(/-/g, '/'))))
	{
		return false;
	}
	else
	{
		return true;
	}
};

Component_Delinquent_CDR_List.getFollowUpDescriptionTD	= function(iType, oDetails)
{
	var oTD	= $T.td();
	
	if (oDetails)
	{
		switch (iType)
		{
			case $CONSTANT.FOLLOWUP_TYPE_ACTION:
			case $CONSTANT.FOLLOWUP_TYPE_NOTE:
				// Account, service or contact info
				if (oDetails.customer_group)
				{
					oTD.appendChild(Component_Delinquent_CDR_List.getCustomerGroupLink(oDetails.account_id, oDetails.customer_group));
				}
				
				if (oDetails.account_id && oDetails.account_name)
				{
					oTD.appendChild(Component_Delinquent_CDR_List.getAccountLink(oDetails.account_id, oDetails.account_name));
				}
				
				if (oDetails.service_id && oDetails.service_fnn)
				{
					oTD.appendChild(Component_Delinquent_CDR_List.getServiceLink(oDetails.service_id, oDetails.service_fnn));
				}
				
				if (oDetails.contact_id && oDetails.contact_name)
				{
					oTD.appendChild(Component_Delinquent_CDR_List.getAccountContactLink(oDetails.contact_id, oDetails.contact_name));
				}
				break;
			case $CONSTANT.FOLLOWUP_TYPE_TICKET_CORRESPONDENCE:
				// Account or ticket contact info
				if (oDetails.customer_group)
				{
					oTD.appendChild(Component_Delinquent_CDR_List.getCustomerGroupLink(oDetails.account_id, oDetails.customer_group));
				}
				
				if (oDetails.account_id && oDetails.account_name)
				{
					oTD.appendChild(Component_Delinquent_CDR_List.getAccountLink(oDetails.account_id, oDetails.account_name));
				}
				
				if (oDetails.account_id && oDetails.ticket_id && oDetails.ticket_contact_name)
				{
					oTD.appendChild(Component_Delinquent_CDR_List.getTicketLink(oDetails.ticket_id, oDetails.account_id, oDetails.ticket_contact_name));
				}
				break;
		}
	}
	
	return oTD;
};

Component_Delinquent_CDR_List.getCustomerGroupLink	= function(iAccountId, sName)
{
	return 	$T.div({class: 'popup-followup-detail-subdetail customer-group'},
				$T.span(sName)
			);
};

Component_Delinquent_CDR_List.getAccountLink	= function(iId, sName)
{
	var sUrl	= 'flex.php/Account/Overview/?Account.Id=' + iId;
	return 	$T.div({class: 'popup-followup-detail-subdetail account'},
				$T.div({class: 'account-id'},
					$T.img({src: Component_Delinquent_CDR_List.DETAILS_ACCOUNT_IMAGE_SOURCE}),
					$T.a({href: sUrl},
						iId + ': '
					)
				),
				$T.a({class: 'account-name', href: sUrl},
					sName
				)
			);
};

Component_Delinquent_CDR_List.getAccountContactLink	= function(iId, sName)
{
	return 	$T.div({class: 'popup-followup-detail-subdetail'},
				$T.img({src: Component_Delinquent_CDR_List.DETAILS_ACCOUNT_CONTACT_IMAGE_SOURCE}),
				$T.a({href: 'reflex.php/Contact/View/' + iId + '/'},
					sName
				)
			);
};

Component_Delinquent_CDR_List.getServiceLink	= function(iId, sFNN)
{
	return 	$T.div({class: 'popup-followup-detail-subdetail'},
				$T.img({src: Component_Delinquent_CDR_List.DETAILS_ACCOUNT_SERVICE_IMAGE_SOURCE}),
				$T.a({href: 'flex.php/Service/View/?Service.Id=' + iId},
					'FNN: ' + sFNN
				)
			);
};

Component_Delinquent_CDR_List.getTicketLink	= function(iTicketId, iAccountId, sContact)
{
	return 	$T.div({class: 'popup-followup-detail-subdetail'},
				$T.img({src: Component_Delinquent_CDR_List.DETAILS_TICKET_IMAGE_SOURCE}),
				$T.a({href: 'reflex.php/Ticketing/Ticket/' + iTicketId + '/View/?Account=' + iAccountId},
					'Ticket ' + iTicketId + ' (' + sContact + ')'
				)
			);
};

Component_Delinquent_CDR_List.getDateTimeElement	= function(sMySQLDate)
{
	var oDate	= new Date(Date.parse(sMySQLDate.replace(/-/g, '/')));
	var sDate	= oDate.$format('d/m/Y');
	var sTime	= oDate.$format('h:i A');
	
	return 	$T.div(
				$T.div(sDate),
				$T.div({class: 'followup-list-all-datetime-time'},
					sTime
				)
			);
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


};

Component_Delinquent_CDR_List.formatDateTimeFilterValue	= function(sDateTime)
{
	var oDate	= Date.$parseDate(sDateTime, 'Y-m-d H:i:s');
	return oDate.$format('j/m/y');
};


	Component_Delinquent_CDR_List.isValidFNN		= function(strFNN)
	{
		
		return true;
	},






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


// Filter Control field definitions
var oNow										= new Date();
Component_Delinquent_CDR_List.YEAR_MINIMUM		= 2010;
Component_Delinquent_CDR_List.YEAR_MAXIMUM		= Component_Delinquent_CDR_List.YEAR_MINIMUM + 5;

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

// Component_Delinquent_CDR_List.FILTER_FIELDS[Component_Delinquent_CDR_List.FILTER_FIELD_STATUS]	= 	{
																									// iType	: Filter.FILTER_TYPE_VALUE,
																									// oOption	: 	{
																													// sType		: 'select',
																													// mDefault	: null,
																													// oDefinition	:	{
																																		// sLabel		: 'Status',
																																		// mEditable	: true,
																																		// mMandatory	: false,
																																		// fnValidate	: null,
																																		// fnPopulate	: FollowUp_Status.getAllAsSelectOptions.bind(FollowUp_Status)
																																	// }
																												// }
																									
																								// };
// Component_Delinquent_CDR_List.FILTER_FIELDS[Component_Delinquent_CDR_List.FILTER_FIELD_CATEGORY]	= 	{
																										// iType	: Filter.FILTER_TYPE_VALUE,
																										// oOption	:	{
																														// sType		: 'select',
																														// mDefault	: null,
																														// oDefinition	:	{
																																			// sLabel		: 'Category',
																																			// mEditable	: true,
																																			// mMandatory	: false,
																																			// fnValidate	: null,
																																			// fnPopulate	: FollowUp_Category.getActiveAsSelectOptions.bind(FollowUp_Category)
																																		// }
																													// }
																									// };


// Sorting definitions
Component_Delinquent_CDR_List.SORT_FIELDS	=	{
												created_datetime		: Sort.DIRECTION_OFF,
												assigned_employee_id	: Sort.DIRECTION_OFF,
												due_datetime			: Sort.DIRECTION_ASC,
												followup_type_id		: Sort.DIRECTION_OFF,
												modified_datetime		: Sort.DIRECTION_OFF,
												followup_category_id	: Sort.DIRECTION_OFF,
												status					: Sort.DIRECTION_OFF
											};
