
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
		this._iEmployeeId	= iEmployeeId;
		this._bEditMode		= bEditMode;
		this._hFilters		= {};
		this._oReflexAnchor	= Reflex_Anchor.getInstance();
		
		this._bFirstLoadComplete		= false;
		this._hControlOnChangeCallbacks	= {};
		
		// Create DataSet & pagination object
		this.oDataSet	= new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, Component_Delinquent_CDR_List.DATA_SET_DEFINITION);
		this.oDataSet.setSortingFields({due_datetime: 'ASC'});
		
		this.oPagination	= new Pagination(this._updateTable.bind(this), Component_Delinquent_CDR_List.MAX_RECORDS_PER_PAGE, this.oDataSet);
		
		// Create filter object
		this._oFilter	=	new Filter(
								this.oDataSet, 
								this.oPagination, 
								this._filterFieldUpdated.bind(this) 	// On field value change
							);
		
		// Add and set a 'now' filter value (used for properly determining overdue-ness based on the clients time)
		this._oFilter.addFilter('now', {iType: Filter.FILTER_TYPE_VALUE});
		
		// Add all filter fields
		for (var sFieldName in Component_Delinquent_CDR_List.FILTER_FIELDS)
		{
			this._oFilter.addFilter(sFieldName, Component_Delinquent_CDR_List.FILTER_FIELDS[sFieldName]);
		}
		
	
		this._oFilter.setFilterValue(Component_Delinquent_CDR_List.FILTER_FIELD_EARLIEST_CDR, sStartDate, sEndDate );
		
		
		// Create sort object
		this._oSort	= new Sort(this.oDataSet, this.oPagination, true);
		
		// Create the page HTML
		var sButtonPathBase	= '../admin/img/template/resultset_';
		this._oContentDiv 	= 	$T.div({class: 'followup-list-all'},
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
												$T.div({class: 'followup-list-all-pagination'},
													$T.span({class: 'pagination-loading'},
														'Loading...'
													),
													$T.button({class: 'followup-list-all-pagination-button'},
														$T.img({src: sButtonPathBase + 'first.png'})
													),
													$T.button({class: 'followup-list-all-pagination-button'},
														$T.img({src: sButtonPathBase + 'previous.png'})
													),
													$T.button({class: 'followup-list-all-pagination-button'},
														$T.img({src: sButtonPathBase + 'next.png'})
													),
													$T.button({class: 'followup-list-all-pagination-button'},
														$T.img({src: sButtonPathBase + 'last.png'})
													)
												)
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
														)
													),
													// Filter values
													

													
													
													
													
													
													$T.tr(
														this._createFilterValueElement(Component_Delinquent_CDR_List.FILTER_FIELD_FNN, 'FNN'),
														this._createFilterValueElement(Component_Delinquent_CDR_List.FILTER_FIELD_CARRIER, 'Carrier'),
														this._createFilterValueElement(Component_Delinquent_CDR_List.FILTER_FIELD_COST, 'Cost'),
														this._createFilterValueElement(Component_Delinquent_CDR_List.FILTER_FIELD_TYPE, 'Count'),
														this._createFilterValueElement(Component_Delinquent_CDR_List.FILTER_FIELD_EARLIEST_CDR, 'Earliest'),
														this._createFilterValueElement(Component_Delinquent_CDR_List.FILTER_FIELD_LATEST_CDR, 'Latest')
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
		var aTopPageButtons		= this._oContentDiv.select('div.section-header-options button.followup-list-all-pagination-button');
		var aBottomPageButtons 	= this._oContentDiv.select('div.footer-pagination button');
		
		// First
		aTopPageButtons[0].observe('click', this._changePage.bind(this, 'firstPage'));
		aBottomPageButtons[0].observe('click', this._changePage.bind(this, 'firstPage'));
		
		//Previous		
		aTopPageButtons[1].observe('click', this._changePage.bind(this, 'previousPage'));
		aBottomPageButtons[1].observe('click', this._changePage.bind(this, 'previousPage'));
		
		// Next
		aTopPageButtons[2].observe('click', this._changePage.bind(this, 'nextPage'));
		aBottomPageButtons[2].observe('click', this._changePage.bind(this, 'nextPage'));
		
		// Last
		aTopPageButtons[3].observe('click', this._changePage.bind(this, 'lastPage'));
		aBottomPageButtons[3].observe('click', this._changePage.bind(this, 'lastPage'));
		
		// Setup pagination button object
		this.oPaginationButtons = {
			oTop	: {
				oFirstPage		: aTopPageButtons[0],
				oPreviousPage	: aTopPageButtons[1],
				oNextPage		: aTopPageButtons[2],
				oLastPage		: aTopPageButtons[3]
			},
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
		this._oSort.refreshData(true);
		this._oFilter.refreshData(true);
		this.oPagination.getCurrentPage();
	},
	
	_showLoading	: function(bShow)
	{
		var oLoading	= this._oContentDiv.select('span.pagination-loading').first();
		if (bShow)
		{
			oLoading.show();
		}
		else
		{
			oLoading.hide();
		}
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
			
			for (var i in aData)
			{
				iCount++;
				oTBody.appendChild(this._createTableRow(aData[i]));
			}
		}
		
		this._bFirstLoadComplete	= true;
		this._updatePagination();
		this._updateSorting();
		this._updateFilters();
		
		// Call manual refresh on the followup link
		FollowUpLink.refresh();
	
		this._showLoading(false);
	},
	
	_createNoRecordsRow	: function(bOnLoad)
	{
		return $T.tr(
			$T.td({class: 'followup-list-all-norecords', colspan: 0},
				(bOnLoad ? 'Loading...' : 'There are no records to display')
			)
		);
	},
	
	_createTableRow	: function(oCDR)
	{
		

			
			var	oTR	=	$T.tr(
							$T.td(oCDR.FNN),
							$T.td(oCDR.carrier_label),
							$T.td(oCDR.TotalCost),
							$T.td(oCDR.Count),							
							$T.td(oCDR.EarliestStartDatetime),
							$T.td(oCDR.LatestStartDatetime)							
						);
			

			
			return oTR;
		
	},
	
	_updatePagination : function(iPageCount)
	{
		// Update the 'disabled' state of each pagination button
		this.oPaginationButtons.oTop.oFirstPage.disabled 		= true;
		this.oPaginationButtons.oBottom.oFirstPage.disabled 	= true;
		this.oPaginationButtons.oTop.oPreviousPage.disabled		= true;
		this.oPaginationButtons.oBottom.oPreviousPage.disabled	= true;
		this.oPaginationButtons.oTop.oNextPage.disabled 		= true;
		this.oPaginationButtons.oBottom.oNextPage.disabled 		= true;
		this.oPaginationButtons.oTop.oLastPage.disabled 		= true;
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
				this.oPaginationButtons.oTop.oFirstPage.disabled 		= false;
				this.oPaginationButtons.oBottom.oFirstPage.disabled		= false;
				this.oPaginationButtons.oTop.oPreviousPage.disabled 	= false;
				this.oPaginationButtons.oBottom.oPreviousPage.disabled 	= false;
			}
			if (this.oPagination.intCurrentPage < (iPageCount - 1) && iPageCount)
			{
				// Enable the next and last buttons
				this.oPaginationButtons.oTop.oNextPage.disabled 	= false;
				this.oPaginationButtons.oBottom.oNextPage.disabled 	= false;
				this.oPaginationButtons.oTop.oLastPage.disabled 	= false;
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
	
	_getFollowUpActions	: function(oFollowUp)
	{
		var oUL	= $T.ul({class: 'reset horizontal followup-list-all-actions'});
		
		var oClose	= $T.img({src: Component_Delinquent_CDR_List.ACTION_CLOSE_IMAGE_SOURCE, alt: 'Close the Follow-Up', title: 'Close the Follow-Up'});
		oClose.observe('click', this._closeFollowUp.bind(this, oFollowUp, $CONSTANT.FOLLOWUP_CLOSURE_TYPE_COMPLETED));
		oUL.appendChild($T.li(oClose));
		
		var oDismiss	= $T.img({src: Component_Delinquent_CDR_List.ACTION_DISMISS_IMAGE_SOURCE, alt: 'Dismiss the Follow-Up', title: 'Dismiss the Follow-Up'});
		oDismiss.observe('click', this._closeFollowUp.bind(this, oFollowUp, $CONSTANT.FOLLOWUP_CLOSURE_TYPE_DISMISSED));
		oUL.appendChild($T.li(oDismiss));
		
		var oEditDueDate	= $T.img({src: Component_Delinquent_CDR_List.ACTION_EDIT_DATE_IMAGE_SOURCE, alt: 'Edit Due Date', title: 'Edit Due Date'});
		oEditDueDate.observe('click', this._editDueDate.bind(this, oFollowUp));
		oUL.appendChild($T.li(oEditDueDate));
		
		var oReAssign	= $T.img({src: Component_Delinquent_CDR_List.ACTION_REASSIGN_IMAGE_SOURCE, alt: 'Reassign the Follow-Up', title: 'Reassign the Follow-Up'});
		oReAssign.observe('click', this._reAssignFollowUp.bind(this, oFollowUp));
		oUL.appendChild($T.li(oReAssign));
		
		var oInvAndPay	= 	$T.a({href: 'flex.php/Account/InvoicesAndPayments/?Account.Id=' + oFollowUp.details.account_id},
								$T.img({src: Component_Delinquent_CDR_List.ACTION_INV_PAYMENTS_IMAGE_SOURCE, alt: 'Invoices & Payments', title: 'Invoices & Payments'})
							);
		oUL.appendChild($T.li(oInvAndPay));
		
		var oView	= $T.img({src: Component_Delinquent_CDR_List.ACTION_VIEW_IMAGE_SOURCE, alt: 'View More Details', title: 'View More Details'});
		oView.observe('click', this._viewDetailsPopup.bind(this, oFollowUp));
		oUL.appendChild($T.li(oView));
		
		var oRecurring	= 	$T.a({href: 'reflex.php/FollowUp/ManageRecurring/' + (this._iEmployeeId ? this._iEmployeeId : '')},
								$T.img({src: Component_Delinquent_CDR_List.ACTION_RECURRING_IMAGE_SOURCE, alt: 'Part of a Recurring Follow-Up', title: 'Part of a Recurring Follow-Up'})
							);
		oUL.appendChild($T.li(oRecurring));
		
		if (oFollowUp.followup_id && !oFollowUp.followup_closure_id && (this._bEditMode || (oFollowUp.assigned_employee_id == this._iEmployeeId)))
		{
			// Leave visible
		}
		else
		{
			oEditDueDate.toggle();
		}
		
		if (!oFollowUp.followup_closure_id && (this._bEditMode || (oFollowUp.assigned_employee_id == this._iEmployeeId)))
		{
			// Leave visible
		}
		else
		{
			oClose.toggle();
			oDismiss.toggle();
		}
		
		if (this._bEditMode && !oFollowUp.followup_closure_id)
		{
			// Leave visible
		}
		else
		{
			oReAssign.toggle();
		}
		
		if (oFollowUp.details && oFollowUp.details.account_id)
		{
			// Leave visible
		}
		else
		{
			oInvAndPay.toggle();
		}
		
		if (oFollowUp.followup_recurring_id || !oFollowUp.followup_id)
		{
			// Leave visible
		}
		else
		{
			oRecurring.toggle();
		}
		
		return oUL;
	},
	
	_closeFollowUp	: function(oFollowUp, iFollowUpClosureTypeId)
	{
		var oPopup	= 	new Popup_FollowUp_Close(
							iFollowUpClosureTypeId,
							oFollowUp.followup_id, 
							oFollowUp.followup_recurring_id,
							oFollowUp.followup_recurring_iteration,
							this.oPagination.getCurrentPage.bind(this.oPagination)
						);
	},
	
	_reAssignFollowUp	: function(oFollowUp)
	{
		var oPopup	= 	new Popup_FollowUp_Reassign(
							oFollowUp.followup_id, 
							oFollowUp.followup_recurring_id,
							this.oPagination.getCurrentPage.bind(this.oPagination)
						);
	},
	
	_editDueDate	: function(oFollowUp)
	{
		var oPopup	= 	new Popup_FollowUp_Due_Date(
							oFollowUp.followup_id, 
							oFollowUp.due_datetime,
							this.oPagination.getCurrentPage.bind(this.oPagination)
						);
	},
	
	_viewDetailsPopup	: function(oFollowUp)
	{
		if (oFollowUp.followup_id)
		{
			var oPopup	= new Popup_FollowUp_View(oFollowUp.followup_id, false, true);
		}
		else
		{
			// Show the details for the recurring 'iteration' NOT the recurring fup as a whole
			var oPopup	=	new Popup_FollowUp_View(
								oFollowUp.followup_recurring_id, 
								true, 
								true, 
								oFollowUp.followup_recurring_iteration
							);
		}
	},
	
	_filterFieldUpdated	: function(sField)
	{
		// Make sure the from date has 00:00 (start of day) for minutes and the to date has 23:59 (end of day)
		// so that both days are included in the search
		if (sField.match(/due_datetime/))
		{
			var oValue	= this._oFilter.getFilterValue(sField);
			if (oValue)
			{
				if (oValue.mFrom)
				{
					oValue.mFrom	= 	oValue.mFrom.replace(
											Component_Delinquent_CDR_List.RANGE_FILTER_DATE_REGEX, 
											'$1 ' + Component_Delinquent_CDR_List.RANGE_FILTER_FROM_MINUTES
										);
				}
				
				if (oValue.mTo)
				{
					oValue.mTo	= 	oValue.mTo.replace(
										Component_Delinquent_CDR_List.RANGE_FILTER_DATE_REGEX, 
										'$1 ' + Component_Delinquent_CDR_List.RANGE_FILTER_TO_MINUTES
									);
				}
				
				this._oFilter.setFilterValue(sField, oValue.mFrom, oValue.mTo, null, true);
			}
		}
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
Component_Delinquent_CDR_List.FILTER_FIELD_CARRIER = 'carrier_label';

Component_Delinquent_CDR_List.SORT_FIELD_EARLIEST_CDR		= 'EarliestStartDatetime';
Component_Delinquent_CDR_List.SORT_FIELD_LATEST_CDR			= 'LatestStartDatetime';
//Component_Delinquent_CDR_List.SORT_FIELD_FOLLOWUP_DATE	= 'StartDatetime';
Component_Delinquent_CDR_List.SORT_FIELD_FNN				= 'FNN';
Component_Delinquent_CDR_List.SORT_FIELD_COUNT				= 'Count';
Component_Delinquent_CDR_List.SORT_FIELD_CARRIER			= 'carrier_label';
Component_Delinquent_CDR_List.SORT_FIELD_COST				= 'TotalCost';

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

Component_Delinquent_CDR_List.formatDateTimeFilterValue	= function(sDateTime)
{
	var oDate	= Date.$parseDate(sDateTime, 'Y-m-d H:i:s');
	return oDate.$format('j/m/y');
};

// Filter Control field definitions
var oNow										= new Date();
Component_Delinquent_CDR_List.YEAR_MINIMUM		= 2010;
Component_Delinquent_CDR_List.YEAR_MAXIMUM		= Component_Delinquent_CDR_List.YEAR_MINIMUM + 5;

Component_Delinquent_CDR_List.FILTER_FIELDS														= {};
// Component_Delinquent_CDR_List.FILTER_FIELDS[Component_Delinquent_CDR_List.FILTER_FIELD_OWNER]		= 	{
																										// iType	: Filter.FILTER_TYPE_VALUE,
																										// oOption	: 	{
																														// sType		: 'select',
																														// mDefault	: null,
																														// oDefinition	:	{
																																			// sLabel		: 'Owner',
																																			// mEditable	: true,
																																			// mMandatory	: false,
																																			// fnValidate	: null,
																																			// fnPopulate	: Employee.getAllAsSelectOptions.bind(Employee)
																																		// }
																													// }
																									// };
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
// Component_Delinquent_CDR_List.FILTER_FIELDS[Component_Delinquent_CDR_List.FILTER_FIELD_TYPE]	= 	{
																									// iType	: Filter.FILTER_TYPE_VALUE,
																									// oOption	:	{
																													// sType		: 'select',
																													// mDefault	: null,
																													// oDefinition	:	{
																																		// sLabel		: 'Status',
																																		// mEditable	: true,
																																		// mMandatory	: false,
																																		// fnValidate	: null,
																																		// fnPopulate	: Component_Delinquent_CDR_List._getAllTypesAsOptions
																																	// }
																												// }
																									
																								// };
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
