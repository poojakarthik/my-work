
var Page_FollowUp_List = Class.create(
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
		this._oContentDiv 	= 	$T.div({class: 'page-followup-list'},
									$T.div({class: 'page-followup-list-active-container'}
										// Active only - placeholder
									),
									$T.div({class: 'page-followup-list-recurring-container'}
										// Recurring only - placeholder
									),
									$T.div({class: 'page-followup-list-all-container'}
										// All - placeholder
									)
								);
		
		this._oFollowUpListActive		= 	new Component_FollowUp_List_Active(
												this._oContentDiv.select('div.page-followup-list-active-container').first(), 
												this._iEmployeeId, 
												this._bEditMode
											);
		this._oFollowUpListRecurring	= 	new Component_FollowUp_List_Recurring(
												this._oContentDiv.select('div.page-followup-list-recurring-container').first(), 
												this._iEmployeeId, 
												this._bEditMode
											);
		this._oFollowUpListAll			= 	new Component_FollowUp_List_All(
												this._oContentDiv.select('div.page-followup-list-all-container').first(), 
												this._iEmployeeId, 
												this._bEditMode
											);		
		oContainerDiv.appendChild(this._oContentDiv);
	}
});

		/*this._hFilters		= {};
		
		// Create DataSet & pagination object
		this.oDataSet	= new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, Page_FollowUp_List.DATA_SET_DEFINITION);
		this.oDataSet.setSortingFields({due_datetime: 'ASC'});
		
		this.oPagination	= new Pagination(this._updateTable.bind(this), Page_FollowUp_List.MAX_RECORDS_PER_PAGE, this.oDataSet);
		
		// Create filter object
		this._oFilter	= new Filter(this.oDataSet, this.oPagination);
		for (var sFieldName in Page_FollowUp_List.FILTER_FIELDS)
		{
			this._oFilter.addFilter(sFieldName, Page_FollowUp_List.FILTER_FIELDS[sFieldName]);
		}
		
		if (this._iEmployeeId)
		{
			// Set the 'owner' filter
			this._oFilter.setFilterValue(Page_FollowUp_List.FILTER_FIELD_OWNER, this._iEmployeeId, this._iEmployeeId);
		}
		
		// Create sort object
		this._oSort	= new Sort(this.oDataSet, this.oPagination, true);
		
		// Create the page HTML
		var sButtonPathBase	= '../admin/img/template/resultset_';
		this._oContentDiv 	= 	$T.div({class: 'followup-list'},
									// Active only
									$T.div({class: 'section followup-list-active'},
										$T.div({class: 'section-header'},
											$T.div({class: 'section-header-title'},
												$T.span('Active Follow-Ups')
											)
										),
										$T.div({class: 'section-content'},
											'active'
										)
									),
									// Recurring only
									$T.div({class: 'section followup-list-recurring'},
										$T.div({class: 'section-header'},
											$T.div({class: 'section-header-title'},
												$T.span('Recurring Follow-Ups')
											)
										),
										$T.div({class: 'section-content'},
											'recurring'
										)
									),
									// All
									$T.div({class: 'section followup-list-all'},
										$T.div({class: 'section-header'},
											$T.div({class: 'section-header-title'},
												$T.span('All Follow-Ups')
											),
											$T.div({class: 'section-header-options'},
												$T.div({class: 'followup-list-pagination'},
													$T.button({class: 'followup-list-pagination-button'},
														$T.img({src: sButtonPathBase + 'first.png'})
													),
													$T.button({class: 'followup-list-pagination-button'},
														$T.img({src: sButtonPathBase + 'previous.png'})
													),
													$T.button({class: 'followup-list-pagination-button'},
														$T.img({src: sButtonPathBase + 'next.png'})
													),
													$T.button({class: 'followup-list-pagination-button'},
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
															Page_FollowUp_List.SORT_FIELD_TYPE,
															Page_FollowUp_List.FILTER_FIELD_TYPE
														),
														this._createFieldHeader('Details', false, false, true),
														this._createFieldHeader('Summary', false, false, true),
														this._createFieldHeader(
															'Created', 
															Page_FollowUp_List.SORT_FIELD_DATE_CREATED
														),
														this._createFieldHeader(
															'Owner', 
															(this._iEmployeeId ? null : Page_FollowUp_List.SORT_FIELD_OWNER), 
															(this._iEmployeeId ? null : Page_FollowUp_List.FILTER_FIELD_OWNER)
														),
														this._createFieldHeader(
															'Due Date', 
															Page_FollowUp_List.SORT_FIELD_FOLLOWUP_DATE, 
															Page_FollowUp_List.FILTER_FIELD_FOLLOWUP_DATE
														),
														this._createFieldHeader(
															'Last Modified', 
															Page_FollowUp_List.SORT_FIELD_LAST_MODIFIED
														),
														this._createFieldHeader(
															'Category', 
															Page_FollowUp_List.SORT_FIELD_CATEGORY, 
															Page_FollowUp_List.FILTER_FIELD_CATEGORY
														),
														this._createFieldHeader(
															'Status', 
															Page_FollowUp_List.SORT_FIELD_STATUS, 
															Page_FollowUp_List.FILTER_FIELD_STATUS
														),
														this._createFieldHeader('')
													),
													// Filter values
													$T.tr(
														this._createFilterValueElement(Page_FollowUp_List.FILTER_FIELD_TYPE),
														$T.th(),
														$T.th(),
														$T.th(),
														(this._iEmployeeId ? $T.th() : this._createFilterValueElement(Page_FollowUp_List.FILTER_FIELD_OWNER)),
														this._createFilterValueElement(Page_FollowUp_List.FILTER_FIELD_FOLLOWUP_DATE),
														$T.th(),
														this._createFilterValueElement(Page_FollowUp_List.FILTER_FIELD_CATEGORY),
														this._createFilterValueElement(Page_FollowUp_List.FILTER_FIELD_STATUS),
														$T.th()
													)
												),
												$T.tbody({class: 'alternating'}
													// ...
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
		//var aTopPageButtons		= this._oContentDiv.select('table > caption div.caption_options button.followup-list-pagination-button');
		var aTopPageButtons		= this._oContentDiv.select('div.section-header-options button.followup-list-pagination-button');
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
		//var oCaptionTitle = this._oContentDiv.select('table > caption > div.caption_bar > div.caption_title').first();
		
		// Check if any results came back
		if (!oResultSet || oResultSet.intTotalResults == 0 || oResultSet.arrResultSet.length == 0)
		{
			// No records
			//oCaptionTitle.innerHTML = 'No Records';
			
			oTBody.appendChild( 
								$T.tr(
									$T.td({colspan: this._oContentDiv.select('table > thead > tr').first().childNodes.length},
										'There are no records to display'
									)
								)
							);
		}
		else
		{
			// Update Page ? of ?
			var iCurrentPage		= oResultSet.intCurrentPage + 1;
			//oCaptionTitle.innerHTML	= 'Page '+ iCurrentPage +' of ' + oResultSet.intPageCount;
			
			// Add the rows
			var aData	= jQuery.json.arrayAsObject(oResultSet.arrResultSet);
			
			for(var i in aData)
			{
				oTBody.appendChild(this._createTableRow(aData[i]));
			}
			
			this._updatePagination();
		}
		
		this._updateSorting();
		this._updateFilters();
		
		// Close the loading popup
		if (this.oLoadingOverlay)
		{
			this.oLoadingOverlay.hide();
			delete this.oLoadingOverlay;
		}
	},
	
	_createTableRow	: function(oFollowUp)
	{
		if ((oFollowUp.followup_id != null) || (oFollowUp.followup_recurring_id != null))
		{
			var	oTR	=	$T.tr(
							$T.td(Page_FollowUp_List._getTypeString(oFollowUp.followup_type_id)),		
							Page_FollowUp_List.getFollowUpDescriptionTD(oFollowUp.followup_type_id, oFollowUp.details),
							$T.td(oFollowUp.summary),
							$T.td(Page_FollowUp_List.formatDate(oFollowUp.created_datetime)),				
							$T.td(oFollowUp.assigned_employee_label),
							$T.td(Page_FollowUp_List.formatDate(oFollowUp.due_datetime)),
							$T.td(Page_FollowUp_List.formatDate(oFollowUp.modified_datetime)),
							$T.td(oFollowUp.followup_category_label),
							$T.td(oFollowUp.status),
							$T.td(this._getFollowUpActions(oFollowUp))
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
		for (var sField in Page_FollowUp_List.SORT_FIELDS)
		{
			if (this._oSort.isRegistered(sField))
			{
				var oSortImg	= this._oContentDiv.select('th.followup-list-header > img.followup-list-sort-' + sField).first();
				var iDirection	= this._oSort.getSortDirection(sField);
				if (iDirection == Sort.DIRECTION_OFF)
				{
					oSortImg.hide();
				}
				else
				{
					oSortImg.src	= Page_FollowUp_List.SORT_IMAGE_SOURCE[iDirection];
					oSortImg.show();
				}
			}
		}
	},
	
	_updateFilters	: function()
	{
		for (var sField in Page_FollowUp_List.FILTER_FIELDS)
		{
			if (this._oFilter.isRegistered(sField))
			{
				var mValue	= this._oFilter.getFilterValue(sField);
				var oSpan	= this._oContentDiv.select('th.followup-list-filter > span.followup-list-filter-' + sField).first();
				
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
		var oDeleteImage				= $T.img({src: Page_FollowUp_List.REMOVE_FILTER_IMAGE_SOURCE, alt: 'Remove Filter', title: 'Remove Filter'});
		oDeleteImage.style.visibility	= 'hidden';
		oDeleteImage.observe('click', this._clearFilterValue.bind(this, sField));
		
		return	$T.th({class: 'followup-list-filter'},
					$T.span({class: 'followup-list-filter-' + sField},
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
		var oSortImg	= $T.img({class: 'followup-list-sort-' + (sSortField ? sSortField : '')});
		var oTH			= 	$T.th({class: 'followup-list-header' + (bMultiLine ? '-multiline' : '')},
								oSortImg,
								$T.span(sLabel)
							);
		oSortImg.hide();
		
		// Optional sort field
		if (sSortField)
		{
			var oSpan	= oTH.select('span').first();
			oSpan.addClassName('followup-list-header-sort');
			
			this._oSort.registerToggleElement(oSpan, sSortField, Page_FollowUp_List.SORT_FIELDS[sSortField]);
			this._oSort.registerToggleElement(oSortImg, sSortField, Page_FollowUp_List.SORT_FIELDS[sSortField]);
		}
		
		// Optional filter field
		if (sFilterField)
		{
			var oIcon	= $T.img({src: Page_FollowUp_List.FILTER_IMAGE_SOURCE, alt: 'Filter by ' + sLabel, title: 'Filter by ' + sLabel});
			this._oFilter.registerFilterIcon(sFilterField, oIcon, sLabel);
			oTH.appendChild(oIcon);
		}
		
		return oTH;
	},
	
	_getFollowUpActions	: function(oFollowUp)
	{
		var oUL	= $T.ul({class: 'reset horizontal followup-list-actions'});
		
		var oClose	= $T.img({src: Page_FollowUp_List.ACTION_CLOSE_IMAGE_SOURCE, alt: 'Close the Follow-Up', title: 'Close the Follow-Up'});
		oClose.observe('click', this._closeFollowUp.bind(this, oFollowUp, $CONSTANT.FOLLOWUP_CLOSURE_TYPE_COMPLETED));
		oUL.appendChild($T.li(oClose));
		
		var oDismiss	= $T.img({src: Page_FollowUp_List.ACTION_DISMISS_IMAGE_SOURCE, alt: 'Dismiss the Follow-Up', title: 'Dismiss the Follow-Up'});
		oDismiss.observe('click', this._closeFollowUp.bind(this, oFollowUp, $CONSTANT.FOLLOWUP_CLOSURE_TYPE_DISMISSED));
		oUL.appendChild($T.li(oDismiss));
		
		var oEditDueDate	= $T.img({src: Page_FollowUp_List.ACTION_EDIT_DATE_IMAGE_SOURCE, alt: 'Edit Due Date', title: 'Edit Due Date'});
		oEditDueDate.observe('click', this._editDueDate.bind(this, oFollowUp));
		oUL.appendChild($T.li(oEditDueDate));
		
		var oReAssign	= $T.img({src: Page_FollowUp_List.ACTION_REASSIGN_IMAGE_SOURCE, alt: 'Reassign the Follow-Up', title: 'Reassign the Follow-Up'});
		oReAssign.observe('click', this._reAssignFollowUp.bind(this, oFollowUp));
		oUL.appendChild($T.li(oReAssign));
		
		var oInvAndPay	= 	$T.a({href: 'flex.php/Account/InvoicesAndPayments/?Account.Id=' + oFollowUp.details.account_id},
								$T.img({src: Page_FollowUp_List.ACTION_INV_PAYMENTS_IMAGE_SOURCE, alt: 'Invoices & Payments', title: 'Invoices & Payments'})
							);
		oUL.appendChild($T.li(oInvAndPay));
		
		if (oFollowUp.followup_id && !oFollowUp.followup_closure_id && (this._bEditMode || (oFollowUp.assigned_employee_id == this._iEmployeeId)))
		{
			//oEditDueDate.show();
		}
		else
		{
			oEditDueDate.toggle();
		}
		
		if (!oFollowUp.followup_closure_id && (this._bEditMode || (oFollowUp.assigned_employee_id == this._iEmployeeId)))
		{
			//oClose.show();
			//oDismiss.show();
		}
		else
		{
			oClose.toggle();
			oDismiss.toggle();
		}
		
		if (this._bEditMode && !oFollowUp.followup_closure_id)
		{
			//oReAssign.show();
		}
		else
		{
			oReAssign.toggle();
		}
		
		if (oFollowUp.details && oFollowUp.details.account_id)
		{
			//oInvAndPay.show();
		}
		else
		{
			oInvAndPay.toggle();
		}
		
		/*
		// Edit due date (only for active, once off followups)
		if (oFollowUp.followup_id && !oFollowUp.followup_closure_id && (this._bEditMode || (oFollowUp.assigned_employee_id == this._iEmployeeId)))
		{
			var oEditDueDate	= $T.img({src: Page_FollowUp_List.ACTION_EDIT_DATE_IMAGE_SOURCE, alt: 'Edit Due Date', title: 'Edit Due Date'});
			oEditDueDate.observe('click', this._editDueDate.bind(this, oFollowUp));
			oUL.appendChild($T.li(oEditDueDate));
		}
		
		// Close (can't close one that's already closed)
		if (!oFollowUp.followup_closure_id && (this._bEditMode || (oFollowUp.assigned_employee_id == this._iEmployeeId)))
		{
			var oClose	= $T.img({src: Page_FollowUp_List.ACTION_CLOSE_IMAGE_SOURCE, alt: 'Close the Follow-Up', title: 'Close the Follow-Up'});
			oClose.observe('click', this._closeFollowUp.bind(this, oFollowUp, $CONSTANT.FOLLOWUP_CLOSURE_TYPE_COMPLETED));
			oUL.appendChild($T.li(oClose));
			
			var oDismiss	= $T.img({src: Page_FollowUp_List.ACTION_DISMISS_IMAGE_SOURCE, alt: 'Dismiss the Follow-Up', title: 'Dismiss the Follow-Up'});
			oDismiss.observe('click', this._closeFollowUp.bind(this, oFollowUp, $CONSTANT.FOLLOWUP_CLOSURE_TYPE_DISMISSED));
			oUL.appendChild($T.li(oDismiss));
		}
		
		// Re-assign
		if (this._bEditMode && !oFollowUp.followup_closure_id)
		{
			var oReAssign	= $T.img({src: Page_FollowUp_List.ACTION_REASSIGN_IMAGE_SOURCE, alt: 'Reassign the Follow-Up', title: 'Reassign the Follow-Up'});
			oReAssign.observe('click', this._reAssignFollowUp.bind(this, oFollowUp));
			oUL.appendChild($T.li(oReAssign));
		}
		
		// Invoices & Payments Link
		if (oFollowUp.details && oFollowUp.details.account_id)
		{
			var oInvAndPay	= 	$T.a({href: 'flex.php/Account/InvoicesAndPayments/?Account.Id=' + oFollowUp.details.account_id},
									$T.img({src: Page_FollowUp_List.ACTION_INV_PAYMENTS_IMAGE_SOURCE, alt: 'Invoices & Payments', title: 'Invoices & Payments'})
								);
			oUL.appendChild($T.li(oInvAndPay));
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
	}
});

Page_FollowUp_List.MAX_RECORDS_PER_PAGE			= 15;
Page_FollowUp_List.EDIT_IMAGE_SOURCE			= '../admin/img/template/pencil.png';
Page_FollowUp_List.FILTER_IMAGE_SOURCE			= '../admin/img/template/table_row_insert.png';
Page_FollowUp_List.REMOVE_FILTER_IMAGE_SOURCE	= '../admin/img/template/delete.png';

Page_FollowUp_List.ACTION_CLOSE_IMAGE_SOURCE			= '../admin/img/template/approve.png';
Page_FollowUp_List.ACTION_DISMISS_IMAGE_SOURCE			= '../admin/img/template/decline.png';
Page_FollowUp_List.ACTION_EDIT_DATE_IMAGE_SOURCE		= '../admin/img/template/edit_date.png';
Page_FollowUp_List.ACTION_REASSIGN_IMAGE_SOURCE			= '../admin/img/template/user_edit.png';
Page_FollowUp_List.ACTION_INV_PAYMENTS_IMAGE_SOURCE		= '../admin/img/template/invoices_payments.png';

Page_FollowUp_List.SORT_IMAGE_SOURCE						= {};
Page_FollowUp_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_ASC]	= '../admin/img/template/order_asc.png';
Page_FollowUp_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_DESC]	= '../admin/img/template/order_desc.png';

Page_FollowUp_List.FILTER_FIELD_OWNER			= 'assigned_employee_id';
Page_FollowUp_List.FILTER_FIELD_FOLLOWUP_DATE	= 'due_datetime';
Page_FollowUp_List.FILTER_FIELD_TYPE			= 'followup_type_id';
Page_FollowUp_List.FILTER_FIELD_CATEGORY		= 'followup_category_id';
Page_FollowUp_List.FILTER_FIELD_STATUS			= 'status';

Page_FollowUp_List.SORT_FIELD_DATE_CREATED	= 'created_datetime';
Page_FollowUp_List.SORT_FIELD_OWNER			= 'assigned_employee_id';
Page_FollowUp_List.SORT_FIELD_FOLLOWUP_DATE	= 'due_datetime';
Page_FollowUp_List.SORT_FIELD_TYPE			= 'followup_type_id';
Page_FollowUp_List.SORT_FIELD_LAST_MODIFIED	= 'modified_datetime';
Page_FollowUp_List.SORT_FIELD_CATEGORY		= 'followup_category_id';
Page_FollowUp_List.SORT_FIELD_STATUS		= 'status';

Page_FollowUp_List.DATA_SET_DEFINITION		= {sObject: 'FollowUp', sMethod: 'getDataSet'};

// Helper functions
Page_FollowUp_List._getTypeString	= function(iType)
{
	if (Flex.Constant.arrConstantGroups.followup_type)
	{
		return Flex.Constant.arrConstantGroups.followup_type[iType].Name;
	}
	
	return 'Error';
};

Page_FollowUp_List._getAllTypesAsOptions	= function(fCallback)
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

Page_FollowUp_List._validateDueDate	= function(sValue)
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

Page_FollowUp_List.getFollowUpDescriptionTD	= function(iType, oDetails)
{
	var oTD	= $T.td();
	
	if (oDetails)
	{
		switch (iType)
		{
			case $CONSTANT.FOLLOWUP_TYPE_ACTION:
			case $CONSTANT.FOLLOWUP_TYPE_NOTE:
				// Account, service or contact info
				if (oDetails.account_id && oDetails.account_name)
				{
					oTD.appendChild(Page_FollowUp_List.getAccountLink(oDetails.account_id, oDetails.account_name));
				}
				
				if (oDetails.service_id && oDetails.service_fnn)
				{
					oTD.appendChild(Page_FollowUp_List.getServiceLink(oDetails.service_id, oDetails.service_fnn));
				}
				
				if (oDetails.contact_id && oDetails.contact_name)
				{
					oTD.appendChild(Page_FollowUp_List.getAccountContactLink(oDetails.contact_id, oDetails.contact_name));
				}
				break;
			case $CONSTANT.FOLLOWUP_TYPE_TICKET_CORRESPONDENCE:
				// Account or ticket contact info
				if (oDetails.account_id && oDetails.account_name)
				{
					oTD.appendChild(Page_FollowUp_List.getAccountLink(oDetails.account_id, oDetails.account_name));
				}
				
				if (oDetails.account_id && oDetails.ticket_id && oDetails.ticket_contact_name)
				{
					oTD.appendChild(Page_FollowUp_List.getTicketLink(oDetails.ticket_id, oDetails.account_id, oDetails.ticket_contact_name));
				}
				break;
		}
	}
	
	return oTD;
};

Page_FollowUp_List.getAccountLink	= function(iId, sName)
{
	return 	$T.div(
				$T.a({href: 'flex.php/Account/Overview/?Account.Id=' + iId},
					iId + ': ' + sName
				)
			);
};

Page_FollowUp_List.getAccountContactLink	= function(iId, sName)
{
	return 	$T.div(
				$T.a({href: 'reflex.php/Contact/View/' + iId + '/'},
					sName
				)
			);
};

Page_FollowUp_List.getServiceLink	= function(iId, sFNN)
{
	return 	$T.div(
				$T.a({href: 'flex.php/Service/View/?Service.Id=' + iId},
					sFNN
				)
			);
};

Page_FollowUp_List.getTicketLink	= function(iTicketId, iAccountId, sContact)
{
	return 	$T.div(
				$T.a({href: 'reflex.php/Ticketing/Ticket/' + iTicketId + '/View/?Account=' + iAccountId},
					'Ticket ' + iTicketId + ' (' + sContact + ')'
				)
			);
};

Page_FollowUp_List.formatDate	= function(sMySQLDate)
{
	var oDate	= new Date(Date.parse(sMySQLDate.replace(/-/g, '/')));
	return Reflex_Date_Format.format('d/m/Y h:i A', oDate);
}

// Filter Control field definitions
var oNow							= new Date();
Page_FollowUp_List.YEAR_MINIMUM		= oNow.getFullYear();
Page_FollowUp_List.YEAR_MAXIMUM		= Page_FollowUp_List.YEAR_MINIMUM + 10;

Page_FollowUp_List.FILTER_FIELDS	= {};
Page_FollowUp_List.FILTER_FIELDS[Page_FollowUp_List.FILTER_FIELD_OWNER]		= 	{
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
Page_FollowUp_List.FILTER_FIELDS[Page_FollowUp_List.FILTER_FIELD_FOLLOWUP_DATE]	= 	{
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
																																	sLabel		: 'Date',
																																	mEditable	: true,
																																	fnValidate	: Page_FollowUp_List._validateDueDate,
																																	iMinYear	: Page_FollowUp_List.YEAR_MINIMUM,
																																	iMaxYear	: Page_FollowUp_List.YEAR_MAXIMUM,
																																	iFormat		: Control_Field_Combo_Date.FORMAT_D_M_Y
																																}
																											}
																					};
Page_FollowUp_List.FILTER_FIELDS[Page_FollowUp_List.FILTER_FIELD_TYPE]	= 	{
																				iType	: Filter.FILTER_TYPE_VALUE,
																				oOption	:	{
																								sType		: 'select',
																								mDefault	: null,
																								oDefinition	:	{
																													sLabel		: 'Status',
																													mEditable	: true,
																													fnValidate	: null,
																													fnPopulate	: Page_FollowUp_List._getAllTypesAsOptions
																												}
																							}
																				
																			};
Page_FollowUp_List.FILTER_FIELDS[Page_FollowUp_List.FILTER_FIELD_STATUS]	= 	{
																					iType	: Filter.FILTER_TYPE_VALUE,
																					oOption	: 	{
																									sType		: 'select',
																									mDefault	: null,
																									oDefinition	:	{
																														sLabel		: 'Type',
																														mEditable	: true,
																														fnValidate	: null,
																														fnPopulate	: FollowUp_Status.getAllAsSelectOptions.bind(FollowUp_Status)
																													}
																								}
																					
																				};
Page_FollowUp_List.FILTER_FIELDS[Page_FollowUp_List.FILTER_FIELD_CATEGORY]	= 	{
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
Page_FollowUp_List.SORT_FIELDS	=	{
										created_datetime		: Sort.DIRECTION_OFF,
										assigned_employee_id	: Sort.DIRECTION_OFF,
										due_datetime			: Sort.DIRECTION_ASC,
										followup_type_id		: Sort.DIRECTION_OFF,
										modified_datetime		: Sort.DIRECTION_OFF,
										followup_category_id	: Sort.DIRECTION_OFF,
										status					: Sort.DIRECTION_OFF
									};
*/