
var Page_FollowUp_Recurring_List = Class.create(
{
	/*
	 * iEmployeeId & bEditMode are used to determine which actions can be performed on a follow-up.
	 * 
	 * If bEditMode is true, then all can be edited, reassigned and closed.
	 * 
	 * If bEditMode is false, then only those who belong to iEmployeeId can be closed or edited (not reassigned).
	 */
	initialize	: function(oContainerDiv, iEmployeeId, bEditMode)
	{
		this._iEmployeeId	= iEmployeeId;
		this._bEditMode		= bEditMode;
		this._hFilters		= {};
		
		// Create DataSet & pagination object
		this.oDataSet	= new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, Page_FollowUp_Recurring_List.DATA_SET_DEFINITION);
		this.oDataSet.setSortingFields({due_datetime: 'ASC'});
		
		this.oPagination	= new Pagination(this._updateTable.bind(this), Page_FollowUp_Recurring_List.MAX_RECORDS_PER_PAGE, this.oDataSet);
		
		// Create filter object
		this._oFilter	= new Filter(this.oDataSet, this.oPagination);
		for (var sFieldName in Page_FollowUp_Recurring_List.FILTER_FIELDS)
		{
			this._oFilter.addFilter(sFieldName, Page_FollowUp_Recurring_List.FILTER_FIELDS[sFieldName]);
		}
		
		if (this._iEmployeeId)
		{
			// Set the 'owner' filter
			this._oFilter.setFilterValue(Page_FollowUp_Recurring_List.FILTER_FIELD_OWNER, this._iEmployeeId, this._iEmployeeId);
		}
		
		// Create sort object
		this._oSort	= new Sort(this.oDataSet, this.oPagination, true);
		
		// Create the page HTML
		var sButtonPathBase	= '../admin/img/template/resultset_';
		this._oContentDiv 	= 	$T.div({class: 'followup-list-all'},
									// All
									$T.div({class: 'section'},
										$T.div({class: 'section-header'},
											$T.div({class: 'section-header-title'},
												$T.span('Recurring Follow-Ups'),
												$T.span({class: 'followup-list-all-pagination-info'},
													''
												)
											),
											$T.div({class: 'section-header-options'},
												$T.div({class: 'followup-list-all-pagination'},
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
															'Type', 
															Page_FollowUp_Recurring_List.SORT_FIELD_TYPE,
															Page_FollowUp_Recurring_List.FILTER_FIELD_TYPE
														),
														this._createFieldHeader('Details', false, false, true),
														this._createFieldHeader('Summary', false, false, true),
														/*this._createFieldHeader(
															'Created', 
															Page_FollowUp_Recurring_List.SORT_FIELD_DATE_CREATED
														),*/
														this._createFieldHeader(
															'Owner', 
															(this._iEmployeeId ? null : Page_FollowUp_Recurring_List.SORT_FIELD_OWNER), 
															(this._iEmployeeId ? null : Page_FollowUp_Recurring_List.FILTER_FIELD_OWNER)
														),
														this._createFieldHeader(
															'Start Date', 
															Page_FollowUp_Recurring_List.SORT_FIELD_START_DATE, 
															Page_FollowUp_Recurring_List.FILTER_FIELD_START_DATE
														),
														this._createFieldHeader(
															'End Date', 
															Page_FollowUp_Recurring_List.SORT_FIELD_END_DATE, 
															Page_FollowUp_Recurring_List.FILTER_FIELD_END_DATE
														),
														this._createFieldHeader(
															'Last Actioned', 
															Page_FollowUp_Recurring_List.SORT_FIELD_LAST_ACTIONED,
															Page_FollowUp_Recurring_List.FILTER_FIELD_LAST_ACTIONED
														),
														this._createFieldHeader(
															'Last Modified', 
															Page_FollowUp_Recurring_List.SORT_FIELD_LAST_MODIFIED
														),
														this._createFieldHeader(
															'Category', 
															Page_FollowUp_Recurring_List.SORT_FIELD_CATEGORY, 
															Page_FollowUp_Recurring_List.FILTER_FIELD_CATEGORY
														),
														this._createFieldHeader(
															'Recurrence Period', 
															Page_FollowUp_Recurring_List.SORT_FIELD_RECURRENCE_PERIOD
														),
														this._createFieldHeader('')
													),
													// Filter values
													$T.tr(
														this._createFilterValueElement(Page_FollowUp_Recurring_List.FILTER_FIELD_TYPE),
														$T.th(),
														$T.th(),
														//$T.th(),
														(this._iEmployeeId ? $T.th() : this._createFilterValueElement(Page_FollowUp_Recurring_List.FILTER_FIELD_OWNER)),
														this._createFilterValueElement(Page_FollowUp_Recurring_List.FILTER_FIELD_START_DATE),
														this._createFilterValueElement(Page_FollowUp_Recurring_List.FILTER_FIELD_END_DATE),
														this._createFilterValueElement(Page_FollowUp_Recurring_List.FILTER_FIELD_LAST_ACTIONED),
														$T.th(),
														this._createFilterValueElement(Page_FollowUp_Recurring_List.FILTER_FIELD_CATEGORY),
														$T.th(),
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
		var aTopPageButtons		= this._oContentDiv.select('div.section-header-options button.followup-list-all-pagination-button');
		var aBottomPageButtons 	= this._oContentDiv.select('div.footer-pagination button');
		
		// First
		aTopPageButtons[0].observe('click', this.oPagination.firstPage.bind(this.oPagination));
		aBottomPageButtons[0].observe('click', this.oPagination.firstPage.bind(this.oPagination));
		
		//Previous		
		aTopPageButtons[1].observe('click', this.oPagination.previousPage.bind(this.oPagination));
		aBottomPageButtons[1].observe('click', this.oPagination.previousPage.bind(this.oPagination));
		
		// Next
		aTopPageButtons[2].observe('click', this.oPagination.nextPage.bind(this.oPagination));
		aBottomPageButtons[2].observe('click', this.oPagination.nextPage.bind(this.oPagination));
		
		// Last
		aTopPageButtons[3].observe('click', this.oPagination.lastPage.bind(this.oPagination));
		aBottomPageButtons[3].observe('click', this.oPagination.lastPage.bind(this.oPagination));
		
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
		
		// Add the new records
		var oPageInfo	= this._oContentDiv.select('span.followup-list-all-pagination-info').first();
		
		// Check if any results came back
		if (!oResultSet || oResultSet.intTotalResults == 0 || oResultSet.arrResultSet.length == 0)
		{
			// No records
			oTBody.appendChild(this._createNoRecordsRow());
		}
		else
		{
			// Update Page ? of ?
			var iCurrentPage	= oResultSet.intCurrentPage + 1;
			oPageInfo.innerHTML	= '(Page '+ iCurrentPage +' of ' + oResultSet.intPageCount + ')';
			
			// Add the rows
			var aData	= jQuery.json.arrayAsObject(oResultSet.arrResultSet);
			
			for(var i in aData)
			{
				oTBody.appendChild(this._createTableRow(aData[i]));
			}
		}
		
		this._updatePagination();
		this._updateSorting();
		this._updateFilters();
		
		// Call manual refresh on the followup link
		FollowUpLink.refresh();
		
		// Close the loading popup
		if (this.oLoadingOverlay)
		{
			this.oLoadingOverlay.hide();
		}
	},
	
	_createNoRecordsRow	: function(bOnLoad)
	{
		return $T.tr(
			$T.td({class: 'followup-list-all-norecords', colspan: 0},
				(bOnLoad ? 'Loading...' : 'There are no records to display')
			)
		);
	},
	
	_createTableRow	: function(oFollowUpRecurring)
	{
		if (oFollowUpRecurring.id != null)
		{
			var	oTR	=	$T.tr(
							$T.td(Page_FollowUp_Recurring_List._getTypeElement(oFollowUpRecurring.followup_type_id)),		
							Page_FollowUp_Recurring_List.getFollowUpDescriptionTD(oFollowUpRecurring.followup_type_id, oFollowUpRecurring.details),
							$T.td(oFollowUpRecurring.summary),
							//$T.td(Page_FollowUp_Recurring_List.getDateTimeElement(oFollowUpRecurring.created_datetime)),				
							$T.td(oFollowUpRecurring.assigned_employee_label),
							$T.td(Page_FollowUp_Recurring_List.getDateTimeElement(oFollowUpRecurring.start_datetime)),
							$T.td(Page_FollowUp_Recurring_List.getDateTimeElement(oFollowUpRecurring.end_datetime)),
							$T.td(Page_FollowUp_Recurring_List.getDateTimeElement(oFollowUpRecurring.last_actioned)),
							$T.td(Page_FollowUp_Recurring_List.getDateTimeElement(oFollowUpRecurring.modified_datetime)),
							$T.td(oFollowUpRecurring.followup_category_label),
							$T.td(Page_FollowUp_Recurring_List.getRecurrencePeriod(oFollowUpRecurring)),
							$T.td(this._getFollowUpActions(oFollowUpRecurring))
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
		for (var sField in Page_FollowUp_Recurring_List.SORT_FIELDS)
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
					oSortImg.src	= Page_FollowUp_Recurring_List.SORT_IMAGE_SOURCE[iDirection];
					oSortImg.show();
				}
			}
		}
	},
	
	_updateFilters	: function()
	{
		for (var sField in Page_FollowUp_Recurring_List.FILTER_FIELDS)
		{
			if (this._oFilter.isRegistered(sField))
			{
				var mValue	= this._oFilter.getFilterValue(sField);
				var oSpan	= this._oContentDiv.select('th.followup-list-all-filter > span.followup-list-all-filter-' + sField).first();
				
				if (oSpan)
				{
					if (mValue !== null && (typeof mValue !== 'undefined'))
					{
						// Value, show it
						oSpan.innerHTML						= mValue;
						oSpan.nextSibling.style.visibility	= 'visible';
					}
					else
					{
						// No value, hide delete image
						oSpan.innerHTML						= 'All';
						oSpan.nextSibling.style.visibility	= 'hidden';
					}
				}
			}
		}
	},
	
	_createFilterValueElement	: function(sField)
	{
		var oDeleteImage				= $T.img({src: Page_FollowUp_Recurring_List.REMOVE_FILTER_IMAGE_SOURCE, alt: 'Remove Filter', title: 'Remove Filter'});
		oDeleteImage.style.visibility	= 'hidden';
		oDeleteImage.observe('click', this._clearFilterValue.bind(this, sField));
		
		return	$T.th({class: 'followup-list-all-filter'},
					$T.span({class: 'followup-list-all-filter-' + sField},
						'All'
					),
					oDeleteImage
				);
	},
		
	_clearFilterValue	: function(sField)
	{
		this._oFilter.clearFilterValue(sField);
		this._oFilter.refreshData();
	},
	
	_createFieldHeader	: function(sLabel, sSortField, sFilterField, bMultiLine)
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
			
			this._oSort.registerToggleElement(oSpan, sSortField, Page_FollowUp_Recurring_List.SORT_FIELDS[sSortField]);
			this._oSort.registerToggleElement(oSortImg, sSortField, Page_FollowUp_Recurring_List.SORT_FIELDS[sSortField]);
		}
		
		// Optional filter field
		if (sFilterField)
		{
			var oIcon	= $T.img({src: Page_FollowUp_Recurring_List.FILTER_IMAGE_SOURCE, alt: 'Filter by ' + sLabel, title: 'Filter by ' + sLabel});
			this._oFilter.registerFilterIcon(sFilterField, oIcon, sLabel);
			oTH.appendChild(oIcon);
		}
		
		return oTH;
	},
	
	_getFollowUpActions	: function(oFollowUpRecurring)
	{
		var oUL	= $T.ul({class: 'reset horizontal followup-list-all-actions'});
		
		var oEnd	= $T.img({src: Page_FollowUp_Recurring_List.ACTION_END_IMAGE_SOURCE, alt: 'End the Follow-Up', title: 'End the Follow-Up'});
		oEnd.observe('click', this._end.bind(this, oFollowUpRecurring.id));
		oUL.appendChild($T.li(oEnd));
		
		var oEditDueDate	= $T.img({src: Page_FollowUp_Recurring_List.ACTION_EDIT_DATE_IMAGE_SOURCE, alt: 'Edit End Date', title: 'Edit End Date'});
		oEditDueDate.observe('click', this._editDueDate.bind(this, oFollowUpRecurring));
		oUL.appendChild($T.li(oEditDueDate));
		
		var oReAssign	= $T.img({src: Page_FollowUp_Recurring_List.ACTION_REASSIGN_IMAGE_SOURCE, alt: 'Reassign the Follow-Up', title: 'Reassign the Follow-Up'});
		oReAssign.observe('click', this._reAssignFollowUp.bind(this, oFollowUpRecurring));
		oUL.appendChild($T.li(oReAssign));
		
		var oInvAndPay	= 	$T.a({href: 'flex.php/Account/InvoicesAndPayments/?Account.Id=' + oFollowUpRecurring.details.account_id},
								$T.img({src: Page_FollowUp_Recurring_List.ACTION_INV_PAYMENTS_IMAGE_SOURCE, alt: 'Invoices & Payments', title: 'Invoices & Payments'})
							);
		oUL.appendChild($T.li(oInvAndPay));
		
		if (oFollowUpRecurring.end_datetime)
		{
			var iEndDate	= Date.parse(oFollowUpRecurring.end_datetime.replace(/-/g, '/'));
			if ((iEndDate >= new Date().getTime()) && (this._bEditMode || (oFollowUpRecurring.assigned_employee_id == this._iEmployeeId)))
			{
				// Leave visible
			}
			else
			{
				oEnd.toggle();
				oEditDueDate.toggle();
			}
		}
		else
		{
			// Leave visible
		}
		
		if (this._bEditMode)
		{
			// Leave visible
		}
		else
		{
			oReAssign.toggle();
		}
		
		if (oFollowUpRecurring.details && oFollowUpRecurring.details.account_id)
		{
			// Leave visible
		}
		else
		{
			oInvAndPay.toggle();
		}
				
		return oUL;
	},
	
	_reAssignFollowUp	: function(oFollowUpRecurring)
	{
		var oPopup	= 	new Popup_FollowUp_Reassign(
							null, 
							oFollowUpRecurring.id,
							this.oPagination.getCurrentPage.bind(this.oPagination)
						);
	},
	
	_editDueDate	: function(oFollowUpRecurring)
	{
		var oPopup	= 	new Popup_FollowUp_Recurring_End_Date(
							oFollowUpRecurring,
							this.oPagination.getCurrentPage.bind(this.oPagination)
						);
	},
	
	_end	: function(iFollowUpId)
	{
		Reflex_Popup.yesNoCancel(
			'Are you sure you want to end this Recurring Follow-Up?', 
			{fnOnYes: this._setEndDateNow.bind(this, iFollowUpId)}
		);
	},
	
	_setEndDateNow	: function(iFollowUpId, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Show loading
			this.oLoading	= new Reflex_Popup.Loading('Setting end date...');
			this.oLoading.display();
			
			// Make request
			var fnJSON	= 	jQuery.json.jsonFunction(
								this._setEndDateNow.bind(this, iFollowUpId), 
								this._ajaxError.bind(this, false), 
								'FollowUp_Recurring', 
								'updateEndDate'
							);
			var oNow	= new Date();
			fnJSON(iFollowUpId, oNow.$format('Y-m-d H:i:s'));
		}
		else if (oResponse.Success)
		{
			this.oLoading.hide();
			delete this.oLoading;
			
			// Refresh the page
			this.oPagination.getCurrentPage();
		}
		else
		{
			// Error
			this._ajaxError(false, oResponse);
		}
	},
	
	_ajaxError	: function(bHideOnClose, oResponse)
	{
		if (this.oLoading)
		{
			this.oLoading.hide();
			delete this.oLoading;
		}
		
		var oConfig	= {sTitle: 'Error', fnOnClose: (bHideOnClose ? this.hide.bind(this) : null)};
		
		if (oResponse.Message)
		{
			Reflex_Popup.alert(oResponse.Message, oConfig);
		}
		else if (oResponse.ERROR)
		{
			Reflex_Popup.alert(oResponse.ERROR, oConfig);
		}
	}
});

Page_FollowUp_Recurring_List.MAX_RECORDS_PER_PAGE		= 15;
Page_FollowUp_Recurring_List.EDIT_IMAGE_SOURCE			= '../admin/img/template/pencil.png';
Page_FollowUp_Recurring_List.FILTER_IMAGE_SOURCE		= '../admin/img/template/table_row_insert.png';
Page_FollowUp_Recurring_List.REMOVE_FILTER_IMAGE_SOURCE	= '../admin/img/template/delete.png';

Page_FollowUp_Recurring_List.ACTION_END_IMAGE_SOURCE			= '../admin/img/template/decline.png';
Page_FollowUp_Recurring_List.ACTION_EDIT_DATE_IMAGE_SOURCE		= '../admin/img/template/edit_date.png';
Page_FollowUp_Recurring_List.ACTION_REASSIGN_IMAGE_SOURCE		= '../admin/img/template/user_edit.png';
Page_FollowUp_Recurring_List.ACTION_INV_PAYMENTS_IMAGE_SOURCE	= '../admin/img/template/invoices_payments.png';
Page_FollowUp_Recurring_List.ACTION_RECURRING_IMAGE_SOURCE		= '../admin/img/template/followup_recurring.png';

Page_FollowUp_Recurring_List.SORT_IMAGE_SOURCE						= {};
Page_FollowUp_Recurring_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_ASC]	= '../admin/img/template/order_asc.png';
Page_FollowUp_Recurring_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_DESC]	= '../admin/img/template/order_desc.png';

Page_FollowUp_Recurring_List.TYPE_NOTE_IMAGE_SOURCE						= '../admin/img/template/followup_note.png';
Page_FollowUp_Recurring_List.TYPE_ACTION_IMAGE_SOURCE					= '../admin/img/template/followup_action.png';
Page_FollowUp_Recurring_List.TYPE_TICKET_CORRESPONDENCE_IMAGE_SOURCE	= '../admin/img/template/tickets.png';

Page_FollowUp_Recurring_List.DETAILS_ACCOUNT_IMAGE_SOURCE			= '../admin/img/template/account.png';
Page_FollowUp_Recurring_List.DETAILS_ACCOUNT_CONTACT_IMAGE_SOURCE	= '../admin/img/template/contact_small.png';
Page_FollowUp_Recurring_List.DETAILS_ACCOUNT_SERVICE_IMAGE_SOURCE	= '../admin/img/template/service.png';
Page_FollowUp_Recurring_List.DETAILS_TICKET_IMAGE_SOURCE			= Page_FollowUp_Recurring_List.TYPE_TICKET_CORRESPONDENCE_IMAGE_SOURCE;

Page_FollowUp_Recurring_List.FILTER_FIELD_OWNER			= 'assigned_employee_id';
Page_FollowUp_Recurring_List.FILTER_FIELD_TYPE			= 'followup_type_id';
Page_FollowUp_Recurring_List.FILTER_FIELD_CATEGORY		= 'followup_category_id';
Page_FollowUp_Recurring_List.FILTER_FIELD_START_DATE	= 'start_datetime';
Page_FollowUp_Recurring_List.FILTER_FIELD_END_DATE		= 'end_datetime';
Page_FollowUp_Recurring_List.FILTER_FIELD_LAST_ACTIONED	= 'last_actioned';

Page_FollowUp_Recurring_List.SORT_FIELD_DATE_CREATED		= 'created_datetime';
Page_FollowUp_Recurring_List.SORT_FIELD_OWNER				= 'assigned_employee_id';
Page_FollowUp_Recurring_List.SORT_FIELD_TYPE				= 'followup_type_id';
Page_FollowUp_Recurring_List.SORT_FIELD_LAST_MODIFIED		= 'modified_datetime';
Page_FollowUp_Recurring_List.SORT_FIELD_CATEGORY			= 'followup_category_id';
Page_FollowUp_Recurring_List.SORT_FIELD_START_DATE			= 'start_datetime';
Page_FollowUp_Recurring_List.SORT_FIELD_END_DATE			= 'end_datetime';
Page_FollowUp_Recurring_List.SORT_FIELD_RECURRENCE_PERIOD	= 'recurrence_period';
Page_FollowUp_Recurring_List.SORT_FIELD_LAST_ACTIONED		= 'last_actioned';

Page_FollowUp_Recurring_List.DATA_SET_DEFINITION		= {sObject: 'FollowUp_Recurring', sMethod: 'getDataSet'};

Page_FollowUp_Recurring_List.NO_END_DATE				= '9999-12-31 23:59:59';

// Helper functions
Page_FollowUp_Recurring_List._getTypeElement	= function(iType)
{
	if (Flex.Constant.arrConstantGroups.followup_type)
	{
		var sAlt	= Flex.Constant.arrConstantGroups.followup_type[iType].Name;
		var sImgSrc	= '';
		
		switch (iType)
		{
			case $CONSTANT.FOLLOWUP_TYPE_NOTE:
				sImgSrc	= Page_FollowUp_Recurring_List.TYPE_NOTE_IMAGE_SOURCE;
				break;
			case $CONSTANT.FOLLOWUP_TYPE_ACTION:
				sImgSrc	= Page_FollowUp_Recurring_List.TYPE_ACTION_IMAGE_SOURCE;
				break;
			case $CONSTANT.FOLLOWUP_TYPE_TICKET_CORRESPONDENCE:
				sImgSrc	= Page_FollowUp_Recurring_List.TYPE_TICKET_CORRESPONDENCE_IMAGE_SOURCE;
				break;
		}
		
		return $T.img({src: sImgSrc, alt: sAlt, title: sAlt});
	}
	
	return 'Error';
};

Page_FollowUp_Recurring_List._getAllTypesAsOptions	= function(fCallback)
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

Page_FollowUp_Recurring_List._validateDueDate	= function(sValue)
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

Page_FollowUp_Recurring_List.getFollowUpDescriptionTD	= function(iType, oDetails)
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
					oTD.appendChild(Page_FollowUp_Recurring_List.getCustomerGroupLink(oDetails.account_id, oDetails.customer_group));
				}
				
				if (oDetails.account_id && oDetails.account_name)
				{
					oTD.appendChild(Page_FollowUp_Recurring_List.getAccountLink(oDetails.account_id, oDetails.account_name));
				}
				
				if (oDetails.service_id && oDetails.service_fnn)
				{
					oTD.appendChild(Page_FollowUp_Recurring_List.getServiceLink(oDetails.service_id, oDetails.service_fnn));
				}
				
				if (oDetails.contact_id && oDetails.contact_name)
				{
					oTD.appendChild(Page_FollowUp_Recurring_List.getAccountContactLink(oDetails.contact_id, oDetails.contact_name));
				}
				break;
			case $CONSTANT.FOLLOWUP_TYPE_TICKET_CORRESPONDENCE:
				// Account or ticket contact info
				if (oDetails.customer_group)
				{
					oTD.appendChild(Page_FollowUp_Recurring_List.getCustomerGroupLink(oDetails.account_id, oDetails.customer_group));
				}
				
				if (oDetails.account_id && oDetails.account_name)
				{
					oTD.appendChild(Page_FollowUp_Recurring_List.getAccountLink(oDetails.account_id, oDetails.account_name));
				}
				
				if (oDetails.account_id && oDetails.ticket_id && oDetails.ticket_contact_name)
				{
					oTD.appendChild(Page_FollowUp_Recurring_List.getTicketLink(oDetails.ticket_id, oDetails.account_id, oDetails.ticket_contact_name));
				}
				break;
		}
	}
	
	return oTD;
};

Page_FollowUp_Recurring_List.getCustomerGroupLink	= function(iAccountId, sName)
{
	return 	$T.div(sName);
};

Page_FollowUp_Recurring_List.getAccountLink	= function(iId, sName)
{
	var sUrl	= 'flex.php/Account/Overview/?Account.Id=' + iId;
	return 	$T.div({class: 'popup-followup-active-detail-subdetail'},
				$T.img({src: Page_FollowUp_Recurring_List.DETAILS_ACCOUNT_IMAGE_SOURCE}),
				$T.a({href: sUrl},
					sName + ' (' + iId + ')'
				)
			);
};

Page_FollowUp_Recurring_List.getAccountContactLink	= function(iId, sName)
{
	return 	$T.div({class: 'popup-followup-active-detail-subdetail'},
				$T.img({src: Page_FollowUp_Recurring_List.DETAILS_ACCOUNT_CONTACT_IMAGE_SOURCE}),
				$T.a({href: 'reflex.php/Contact/View/' + iId + '/'},
					sName
				)
			);
};

Page_FollowUp_Recurring_List.getServiceLink	= function(iId, sFNN)
{
	return 	$T.div({class: 'popup-followup-active-detail-subdetail'},
				$T.img({src: Page_FollowUp_Recurring_List.DETAILS_ACCOUNT_SERVICE_IMAGE_SOURCE}),
				$T.a({href: 'flex.php/Service/View/?Service.Id=' + iId},
					'FNN : ' + sFNN
				)
			);
};

Page_FollowUp_Recurring_List.getTicketLink	= function(iTicketId, iAccountId, sContact)
{
	return 	$T.div({class: 'popup-followup-active-detail-subdetail'},
				$T.img({src: Page_FollowUp_Recurring_List.DETAILS_TICKET_IMAGE_SOURCE}),
				$T.a({href: 'reflex.php/Ticketing/Ticket/' + iTicketId + '/View/?Account=' + iAccountId},
					'Ticket ' + iTicketId + ' (' + sContact + ')'
				)
			);
};

Page_FollowUp_Recurring_List.getDateTimeElement	= function(sMySQLDate)
{
	if (sMySQLDate && (sMySQLDate != Page_FollowUp_Recurring_List.NO_END_DATE))
	{
		var oDate	= new Date(Date.parse(sMySQLDate.replace(/-/g, '/')));
		var sDate	= oDate.$format('d/m/Y');
		var sTime	= oDate.$format('h:i A');
	}
	else
	{
		var sTime	= '';
	}
	
	return 	$T.div(
				$T.div(sDate),
				$T.div({class: 'followup-list-all-datetime-time'},
					sTime
				)
			);
};

Page_FollowUp_Recurring_List.getRecurrencePeriod	= function(oFollowUpRecurring)
{
	var sSuffix	= (oFollowUpRecurring.recurrence_multiplier == 1 ? '' : 's');
	return 	oFollowUpRecurring.recurrence_multiplier + 
			' ' + 
			Flex.Constant.arrConstantGroups.followup_recurrence_period[oFollowUpRecurring.followup_recurrence_period_id].Name + 
			sSuffix;
};

// Filter Control field definitions
var oNow							= new Date();
Page_FollowUp_Recurring_List.YEAR_MINIMUM		= oNow.getFullYear();
Page_FollowUp_Recurring_List.YEAR_MAXIMUM		= Page_FollowUp_Recurring_List.YEAR_MINIMUM + 10;

Page_FollowUp_Recurring_List.FILTER_FIELDS	= {};
Page_FollowUp_Recurring_List.FILTER_FIELDS[Page_FollowUp_Recurring_List.FILTER_FIELD_OWNER]		= 	{
																										iType	: Filter.FILTER_TYPE_VALUE,
																										oOption	: 	{
																														sType		: 'select',
																														mDefault	: null,
																														oDefinition	:	{
																																			sLabel		: 'Owner',
																																			mEditable	: true,
																																			fnValidate	: null,
																																			fnPopulate	: Employee.getAllAsSelectOptions.bind(Employee)
																																		}
																													}
																									};
Page_FollowUp_Recurring_List.FILTER_FIELDS[Page_FollowUp_Recurring_List.FILTER_FIELD_START_DATE]	= 	{
																											iType			: Filter.FILTER_TYPE_RANGE,
																											bFrom			: true,
																											sFrom			: 'Start Date',
																											bTo				: true,
																											sTo				: 'End Date',
																											sGreaterThan	: 'On Or After',
																											sLessThan		: 'On Or Before',
																											sBetween		: 'Between',
																											oOption			: 	{
																																	sType		: 'combo_date',
																																	mDefault	: null,
																																	oDefinition	:	{
																																						sLabel		: 'Start Date',
																																						mEditable	: true,
																																						fnValidate	: Page_FollowUp_Recurring_List._validateDueDate,
																																						iMinYear	: Page_FollowUp_Recurring_List.YEAR_MINIMUM,
																																						iMaxYear	: Page_FollowUp_Recurring_List.YEAR_MAXIMUM,
																																						iFormat		: Control_Field_Combo_Date.FORMAT_D_M_Y
																																					}
																																}
																										};
Page_FollowUp_Recurring_List.FILTER_FIELDS[Page_FollowUp_Recurring_List.FILTER_FIELD_END_DATE]	= 	{
																										iType			: Filter.FILTER_TYPE_RANGE,
																										bFrom			: true,
																										sFrom			: 'Start Date',
																										bTo				: true,
																										sTo				: 'End Date',
																										sGreaterThan	: 'On Or After',
																										sLessThan		: 'On Or Before',
																										sBetween		: 'Between',
																										oOption			: 	{
																																sType		: 'combo_date',
																																mDefault	: null,
																																oDefinition	:	{
																																					sLabel		: 'End Date',
																																					mEditable	: true,
																																					fnValidate	: Page_FollowUp_Recurring_List._validateDueDate,
																																					iMinYear	: Page_FollowUp_Recurring_List.YEAR_MINIMUM,
																																					iMaxYear	: Page_FollowUp_Recurring_List.YEAR_MAXIMUM,
																																					iFormat		: Control_Field_Combo_Date.FORMAT_D_M_Y
																																				}
																															}
																									};
Page_FollowUp_Recurring_List.FILTER_FIELDS[Page_FollowUp_Recurring_List.FILTER_FIELD_LAST_ACTIONED]	= 	{
																											iType			: Filter.FILTER_TYPE_RANGE,
																											bFrom			: true,
																											sFrom			: 'Start Date',
																											bTo				: true,
																											sTo				: 'End Date',
																											sGreaterThan	: 'On Or After',
																											sLessThan		: 'On Or Before',
																											sBetween		: 'Between',
																											oOption			: 	{
																																	sType		: 'combo_date',
																																	mDefault	: null,
																																	oDefinition	:	{
																																						sLabel		: 'Last Actioned',
																																						mEditable	: true,
																																						fnValidate	: Page_FollowUp_Recurring_List._validateDueDate,
																																						iMinYear	: Page_FollowUp_Recurring_List.YEAR_MINIMUM,
																																						iMaxYear	: Page_FollowUp_Recurring_List.YEAR_MAXIMUM,
																																						iFormat		: Control_Field_Combo_Date.FORMAT_D_M_Y
																																					}
																																}
																										};
Page_FollowUp_Recurring_List.FILTER_FIELDS[Page_FollowUp_Recurring_List.FILTER_FIELD_TYPE]	= 	{
																									iType	: Filter.FILTER_TYPE_VALUE,
																									oOption	:	{
																													sType		: 'select',
																													mDefault	: null,
																													oDefinition	:	{
																																		sLabel		: 'Status',
																																		mEditable	: true,
																																		fnValidate	: null,
																																		fnPopulate	: Page_FollowUp_Recurring_List._getAllTypesAsOptions
																																	}
																												}
																									
																								};
Page_FollowUp_Recurring_List.FILTER_FIELDS[Page_FollowUp_Recurring_List.FILTER_FIELD_CATEGORY]	= 	{
																										iType	: Filter.FILTER_TYPE_VALUE,
																										oOption	:	{
																														sType		: 'select',
																														mDefault	: null,
																														oDefinition	:	{
																																			sLabel		: 'Category',
																																			mEditable	: true,
																																			fnValidate	: null,
																																			fnPopulate	: FollowUp_Category.getAllAsSelectOptions.bind(FollowUp_Category)
																																		}
																													}
																									};


// Sorting definitions
Page_FollowUp_Recurring_List.SORT_FIELDS	=	{
													created_datetime		: Sort.DIRECTION_OFF,
													assigned_employee_id	: Sort.DIRECTION_OFF,
													followup_type_id		: Sort.DIRECTION_OFF,
													modified_datetime		: Sort.DIRECTION_OFF,
													followup_category_id	: Sort.DIRECTION_OFF,
													start_datetime			: Sort.DIRECTION_OFF,
													end_datetime			: Sort.DIRECTION_OFF,
													recurrence_period		: Sort.DIRECTION_OFF,
													last_actioned			: Sort.DIRECTION_OFF
												};