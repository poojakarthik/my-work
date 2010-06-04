
var Component_FollowUp_List_Active = Class.create(
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
		//this.oDataSet	= new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, Component_FollowUp_List_Active.DATA_SET_DEFINITION);
		//this.oDataSet.setSortingFields({due_datetime: 'ASC'});
		
		//this.oPagination	= new Pagination(this._updateTable.bind(this), Component_FollowUp_List_Active.MAX_RECORDS_PER_PAGE, this.oDataSet);
		
		// Create filter object
		/*this._oFilter	= new Filter(this.oDataSet, this.oPagination);
		for (var sFieldName in Component_FollowUp_List_Active.FILTER_FIELDS)
		{
			this._oFilter.addFilter(sFieldName, Component_FollowUp_List_Active.FILTER_FIELDS[sFieldName]);
		}
		
		if (this._iEmployeeId)
		{
			// Set the 'owner' filter
			this._oFilter.setFilterValue(Component_FollowUp_List_Active.FILTER_FIELD_OWNER, this._iEmployeeId, this._iEmployeeId);
		}
		
		// Create sort object
		this._oSort	= new Sort(this.oDataSet, this.oPagination, true);
		
		// Create the page HTML
		*/
		this._oContentDiv 	= 	$T.div({class: 'followup-list-active'},
									// Active only
									$T.div({class: 'section'},
										$T.div({class: 'section-header'},
											$T.div({class: 'section-header-title'},
												$T.span('Active Follow-Ups')
											)
										),
										$T.div({class: 'section-content'},
											'active'
										)
									)
								);
		
		// Attach content and get data
		oContainerDiv.appendChild(this._oContentDiv);
		
		// Send the initial sorting parameters to dataset ajax 
		/*this._oSort.refreshData(true);
		this._oFilter.refreshData(true);
		this.oPagination.getCurrentPage();*/
	}
});
	/*
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
							$T.td(Component_FollowUp_List_Active._getTypeString(oFollowUp.followup_type_id)),		
							Component_FollowUp_List_Active.getFollowUpDescriptionTD(oFollowUp.followup_type_id, oFollowUp.details),
							$T.td(oFollowUp.summary),
							$T.td(Component_FollowUp_List_Active.formatDate(oFollowUp.created_datetime)),				
							$T.td(oFollowUp.assigned_employee_label),
							$T.td(Component_FollowUp_List_Active.formatDate(oFollowUp.due_datetime)),
							$T.td(Component_FollowUp_List_Active.formatDate(oFollowUp.modified_datetime)),
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
		for (var sField in Component_FollowUp_List_Active.SORT_FIELDS)
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
					oSortImg.src	= Component_FollowUp_List_Active.SORT_IMAGE_SOURCE[iDirection];
					oSortImg.show();
				}
			}
		}
	},
	
	_updateFilters	: function()
	{
		for (var sField in Component_FollowUp_List_Active.FILTER_FIELDS)
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
		var oDeleteImage				= $T.img({src: Component_FollowUp_List_Active.REMOVE_FILTER_IMAGE_SOURCE, alt: 'Remove Filter', title: 'Remove Filter'});
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
			
			this._oSort.registerToggleElement(oSpan, sSortField, Component_FollowUp_List_Active.SORT_FIELDS[sSortField]);
			this._oSort.registerToggleElement(oSortImg, sSortField, Component_FollowUp_List_Active.SORT_FIELDS[sSortField]);
		}
		
		// Optional filter field
		if (sFilterField)
		{
			var oIcon	= $T.img({src: Component_FollowUp_List_Active.FILTER_IMAGE_SOURCE, alt: 'Filter by ' + sLabel, title: 'Filter by ' + sLabel});
			this._oFilter.registerFilterIcon(sFilterField, oIcon, sLabel);
			oTH.appendChild(oIcon);
		}
		
		return oTH;
	},
	
	_getFollowUpActions	: function(oFollowUp)
	{
		var oUL	= $T.ul({class: 'reset horizontal followup-list-actions'});
		
		var oClose	= $T.img({src: Component_FollowUp_List_Active.ACTION_CLOSE_IMAGE_SOURCE, alt: 'Close the Follow-Up', title: 'Close the Follow-Up'});
		oClose.observe('click', this._closeFollowUp.bind(this, oFollowUp, $CONSTANT.FOLLOWUP_CLOSURE_TYPE_COMPLETED));
		oUL.appendChild($T.li(oClose));
		
		var oDismiss	= $T.img({src: Component_FollowUp_List_Active.ACTION_DISMISS_IMAGE_SOURCE, alt: 'Dismiss the Follow-Up', title: 'Dismiss the Follow-Up'});
		oDismiss.observe('click', this._closeFollowUp.bind(this, oFollowUp, $CONSTANT.FOLLOWUP_CLOSURE_TYPE_DISMISSED));
		oUL.appendChild($T.li(oDismiss));
		
		var oEditDueDate	= $T.img({src: Component_FollowUp_List_Active.ACTION_EDIT_DATE_IMAGE_SOURCE, alt: 'Edit Due Date', title: 'Edit Due Date'});
		oEditDueDate.observe('click', this._editDueDate.bind(this, oFollowUp));
		oUL.appendChild($T.li(oEditDueDate));
		
		var oReAssign	= $T.img({src: Component_FollowUp_List_Active.ACTION_REASSIGN_IMAGE_SOURCE, alt: 'Reassign the Follow-Up', title: 'Reassign the Follow-Up'});
		oReAssign.observe('click', this._reAssignFollowUp.bind(this, oFollowUp));
		oUL.appendChild($T.li(oReAssign));
		
		var oInvAndPay	= 	$T.a({href: 'flex.php/Account/InvoicesAndPayments/?Account.Id=' + oFollowUp.details.account_id},
								$T.img({src: Component_FollowUp_List_Active.ACTION_INV_PAYMENTS_IMAGE_SOURCE, alt: 'Invoices & Payments', title: 'Invoices & Payments'})
							);
		oUL.appendChild($T.li(oInvAndPay));
		
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

Component_FollowUp_List_Active.MAX_RECORDS_PER_PAGE		= 15;
Component_FollowUp_List_Active.EDIT_IMAGE_SOURCE			= '../admin/img/template/pencil.png';
Component_FollowUp_List_Active.FILTER_IMAGE_SOURCE			= '../admin/img/template/table_row_insert.png';
Component_FollowUp_List_Active.REMOVE_FILTER_IMAGE_SOURCE	= '../admin/img/template/delete.png';

Component_FollowUp_List_Active.ACTION_CLOSE_IMAGE_SOURCE			= '../admin/img/template/approve.png';
Component_FollowUp_List_Active.ACTION_DISMISS_IMAGE_SOURCE			= '../admin/img/template/decline.png';
Component_FollowUp_List_Active.ACTION_EDIT_DATE_IMAGE_SOURCE		= '../admin/img/template/edit_date.png';
Component_FollowUp_List_Active.ACTION_REASSIGN_IMAGE_SOURCE		= '../admin/img/template/user_edit.png';
Component_FollowUp_List_Active.ACTION_INV_PAYMENTS_IMAGE_SOURCE	= '../admin/img/template/invoices_payments.png';

Component_FollowUp_List_Active.SORT_IMAGE_SOURCE						= {};
Component_FollowUp_List_Active.SORT_IMAGE_SOURCE[Sort.DIRECTION_ASC]	= '../admin/img/template/order_asc.png';
Component_FollowUp_List_Active.SORT_IMAGE_SOURCE[Sort.DIRECTION_DESC]	= '../admin/img/template/order_desc.png';

Component_FollowUp_List_Active.FILTER_FIELD_OWNER			= 'assigned_employee_id';
Component_FollowUp_List_Active.FILTER_FIELD_FOLLOWUP_DATE	= 'due_datetime';
Component_FollowUp_List_Active.FILTER_FIELD_TYPE			= 'followup_type_id';
Component_FollowUp_List_Active.FILTER_FIELD_CATEGORY		= 'followup_category_id';
Component_FollowUp_List_Active.FILTER_FIELD_STATUS			= 'status';

Component_FollowUp_List_Active.SORT_FIELD_DATE_CREATED		= 'created_datetime';
Component_FollowUp_List_Active.SORT_FIELD_OWNER			= 'assigned_employee_id';
Component_FollowUp_List_Active.SORT_FIELD_FOLLOWUP_DATE	= 'due_datetime';
Component_FollowUp_List_Active.SORT_FIELD_TYPE				= 'followup_type_id';
Component_FollowUp_List_Active.SORT_FIELD_LAST_MODIFIED	= 'modified_datetime';
Component_FollowUp_List_Active.SORT_FIELD_CATEGORY			= 'followup_category_id';
Component_FollowUp_List_Active.SORT_FIELD_STATUS			= 'status';

Component_FollowUp_List_Active.DATA_SET_DEFINITION			= {sObject: 'FollowUp', sMethod: 'getDataSet'};

// Helper functions
Component_FollowUp_List_Active._getTypeString	= function(iType)
{
	if (Flex.Constant.arrConstantGroups.followup_type)
	{
		return Flex.Constant.arrConstantGroups.followup_type[iType].Name;
	}
	
	return 'Error';
};

Component_FollowUp_List_Active._getAllTypesAsOptions	= function(fCallback)
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

Component_FollowUp_List_Active._validateDueDate	= function(sValue)
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

Component_FollowUp_List_Active.getFollowUpDescriptionTD	= function(iType, oDetails)
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
					oTD.appendChild(Component_FollowUp_List_Active.getAccountLink(oDetails.account_id, oDetails.account_name));
				}
				
				if (oDetails.service_id && oDetails.service_fnn)
				{
					oTD.appendChild(Component_FollowUp_List_Active.getServiceLink(oDetails.service_id, oDetails.service_fnn));
				}
				
				if (oDetails.contact_id && oDetails.contact_name)
				{
					oTD.appendChild(Component_FollowUp_List_Active.getAccountContactLink(oDetails.contact_id, oDetails.contact_name));
				}
				break;
			case $CONSTANT.FOLLOWUP_TYPE_TICKET_CORRESPONDENCE:
				// Account or ticket contact info
				if (oDetails.account_id && oDetails.account_name)
				{
					oTD.appendChild(Component_FollowUp_List_Active.getAccountLink(oDetails.account_id, oDetails.account_name));
				}
				
				if (oDetails.account_id && oDetails.ticket_id && oDetails.ticket_contact_name)
				{
					oTD.appendChild(Component_FollowUp_List_Active.getTicketLink(oDetails.ticket_id, oDetails.account_id, oDetails.ticket_contact_name));
				}
				break;
		}
	}
	
	return oTD;
};

Component_FollowUp_List_Active.getAccountLink	= function(iId, sName)
{
	return 	$T.div(
				$T.a({href: 'flex.php/Account/Overview/?Account.Id=' + iId},
					iId + ': ' + sName
				)
			);
};

Component_FollowUp_List_Active.getAccountContactLink	= function(iId, sName)
{
	return 	$T.div(
				$T.a({href: 'reflex.php/Contact/View/' + iId + '/'},
					sName
				)
			);
};

Component_FollowUp_List_Active.getServiceLink	= function(iId, sFNN)
{
	return 	$T.div(
				$T.a({href: 'flex.php/Service/View/?Service.Id=' + iId},
					sFNN
				)
			);
};

Component_FollowUp_List_Active.getTicketLink	= function(iTicketId, iAccountId, sContact)
{
	return 	$T.div(
				$T.a({href: 'reflex.php/Ticketing/Ticket/' + iTicketId + '/View/?Account=' + iAccountId},
					'Ticket ' + iTicketId + ' (' + sContact + ')'
				)
			);
};

Component_FollowUp_List_Active.formatDate	= function(sMySQLDate)
{
	var oDate	= new Date(Date.parse(sMySQLDate.replace(/-/g, '/')));
	return Reflex_Date_Format.format('d/m/Y h:i A', oDate);
}

// Filter Control field definitions
var oNow							= new Date();
Component_FollowUp_List_Active.YEAR_MINIMUM		= oNow.getFullYear();
Component_FollowUp_List_Active.YEAR_MAXIMUM		= Component_FollowUp_List_Active.YEAR_MINIMUM + 10;

Component_FollowUp_List_Active.FILTER_FIELDS	= {};
Component_FollowUp_List_Active.FILTER_FIELDS[Component_FollowUp_List_Active.FILTER_FIELD_OWNER]		= 	{
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
Component_FollowUp_List_Active.FILTER_FIELDS[Component_FollowUp_List_Active.FILTER_FIELD_FOLLOWUP_DATE]	= 	{
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
																																						fnValidate	: Component_FollowUp_List_Active._validateDueDate,
																																						iMinYear	: Component_FollowUp_List_Active.YEAR_MINIMUM,
																																						iMaxYear	: Component_FollowUp_List_Active.YEAR_MAXIMUM,
																																						iFormat		: Control_Field_Combo_Date.FORMAT_D_M_Y
																																					}
																																}
																										};
Component_FollowUp_List_Active.FILTER_FIELDS[Component_FollowUp_List_Active.FILTER_FIELD_TYPE]	= 	{
																									iType	: Filter.FILTER_TYPE_VALUE,
																									oOption	:	{
																													sType		: 'select',
																													mDefault	: null,
																													oDefinition	:	{
																																		sLabel		: 'Status',
																																		mEditable	: true,
																																		fnValidate	: null,
																																		fnPopulate	: Component_FollowUp_List_Active._getAllTypesAsOptions
																																	}
																												}
																									
																								};
Component_FollowUp_List_Active.FILTER_FIELDS[Component_FollowUp_List_Active.FILTER_FIELD_STATUS]	= 	{
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
Component_FollowUp_List_Active.FILTER_FIELDS[Component_FollowUp_List_Active.FILTER_FIELD_CATEGORY]	= 	{
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
Component_FollowUp_List_Active.SORT_FIELDS	=	{
										created_datetime		: Sort.DIRECTION_OFF,
										assigned_employee_id	: Sort.DIRECTION_OFF,
										due_datetime			: Sort.DIRECTION_ASC,
										followup_type_id		: Sort.DIRECTION_OFF,
										modified_datetime		: Sort.DIRECTION_OFF,
										followup_category_id	: Sort.DIRECTION_OFF,
										status					: Sort.DIRECTION_OFF
									};
*/