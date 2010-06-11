
var Component_FollowUp_Closure_List = Class.create(
{
	initialize	: function(oContainerDiv)
	{
		this._hFilters	= {};
		
		// Create DataSet & pagination object
		this.oDataSet		= 	new Dataset_Ajax(
									Dataset_Ajax.CACHE_MODE_NO_CACHING, 
									Component_FollowUp_Closure_List.DATA_SET_DEFINITION
								);
		this.oPagination	= 	new Pagination(
									this._updateTable.bind(this), 
									Component_FollowUp_Closure_List.MAX_RECORDS_PER_PAGE, 
									this.oDataSet
								);
		
		// Create filter object
		this._oFilter	= new Filter(this.oDataSet, this.oPagination);
		for (var sFieldName in Component_FollowUp_Closure_List.FILTER_FIELDS)
		{
			this._oFilter.addFilter(sFieldName, Component_FollowUp_Closure_List.FILTER_FIELDS[sFieldName]);
		}
		
		// Create sort object
		this._oSort	= new Sort(this.oDataSet, this.oPagination, true);
		
		// Create the page HTML
		var sButtonPathBase	= '../admin/img/template/resultset_';
		this._oContentDiv 	= 	$T.div({class: 'followup-closure-list'},
									// All
									$T.div({class: 'section'},
										$T.div({class: 'section-header'},
											$T.div({class: 'section-header-title'},
												$T.span('Closure Reasons'),
												$T.span({class: 'followup-closure-list-pagination-info'},
													''
												)
											),
											$T.div({class: 'section-header-options'},
												$T.div({class: 'followup-closure-list-pagination'},
													$T.button({class: 'followup-closure-list-pagination-button'},
														$T.img({src: sButtonPathBase + 'first.png'})
													),
													$T.button({class: 'followup-closure-list-pagination-button'},
														$T.img({src: sButtonPathBase + 'previous.png'})
													),
													$T.button({class: 'followup-closure-list-pagination-button'},
														$T.img({src: sButtonPathBase + 'next.png'})
													),
													$T.button({class: 'followup-closure-list-pagination-button'},
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
															'Name', 
															Component_FollowUp_Closure_List.SORT_FIELD_NAME
														),
														this._createFieldHeader(
															'Description', 
															Component_FollowUp_Closure_List.SORT_FIELD_DESCRIPTION
														),
														this._createFieldHeader(
															'Type',
															Component_FollowUp_Closure_List.SORT_FIELD_TYPE
															//Component_FollowUp_Closure_List.FILTER_FIELD_TYPE
														),
														this._createFieldHeader(
															'Status', 
															Component_FollowUp_Closure_List.SORT_FIELD_STATUS
															//Component_FollowUp_Closure_List.FILTER_FIELD_STATUS
														),
														this._createFieldHeader('')
													)
													// Filter values
													/*$T.tr(
														$T.th(),
														$T.th(),
														this._createFilterValueElement(Component_FollowUp_Closure_List.FILTER_FIELD_TYPE),
														this._createFilterValueElement(Component_FollowUp_Closure_List.FILTER_FIELD_STATUS),
														$T.th()
													)*/
												),
												$T.tbody({class: 'alternating'}
													// ...
												)
											)
										),
										$T.div({class: 'section-footer'},
											$T.button({class: 'icon-button'},
												$T.img({src: Component_FollowUp_Closure_List.ADD_IMAGE_SOURCE, alt: 'Add Closure Reason', title: 'Add Closure Reason'}),
												$T.span('Add Closure Reason')
											)
										)
									)
								);
		
		// Bind events to the pagination buttons
		var aTopPageButtons		= this._oContentDiv.select('div.section-header-options button.followup-closure-list-pagination-button');
		aTopPageButtons[0].observe('click', this.oPagination.firstPage.bind(this.oPagination));
		aTopPageButtons[1].observe('click', this.oPagination.previousPage.bind(this.oPagination));
		aTopPageButtons[2].observe('click', this.oPagination.nextPage.bind(this.oPagination));
		aTopPageButtons[3].observe('click', this.oPagination.lastPage.bind(this.oPagination));
		
		// Setup pagination button object
		this.oPaginationButtons = {
			oTop	: {
				oFirstPage		: aTopPageButtons[0],
				oPreviousPage	: aTopPageButtons[1],
				oNextPage		: aTopPageButtons[2],
				oLastPage		: aTopPageButtons[3]
			}
		};
		
		// Attach content and get data
		oContainerDiv.appendChild(this._oContentDiv);
		
		// Add button
		this._oContentDiv.select('div.section-footer > button.icon-button').first().observe('click', this._edit.bind(this, null));
		
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
			var aActionButtons = oTBody.firstChild.select('img');
			for (var i = 0; i < aActionButtons.length; i++)
			{
				aActionButtons[i].stopObserving();
			}
			
			// Remove the row
			oTBody.firstChild.remove();
		}
		
		// Add the new records
		var oPageInfo	= this._oContentDiv.select('span.followup-closure-list-pagination-info').first();
		
		// Check if any results came back
		if (!oResultSet || oResultSet.intTotalResults == 0 || oResultSet.arrResultSet.length == 0)
		{
			// No records
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
			var iCurrentPage	= oResultSet.intCurrentPage + 1;
			oPageInfo.innerHTML	= '(Page '+ iCurrentPage +' of ' + oResultSet.intPageCount + ')';
			
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
		
		// Call manual refresh on the followup link
		FollowUpLink.refresh();
		
		// Close the loading popup
		if (this.oLoadingOverlay)
		{
			this.oLoadingOverlay.hide();
		}
	},
	
	_createTableRow	: function(oClosure)
	{
		if (oClosure.id != null)
		{
			var sStatusClass	= '';
			switch (oClosure.status_id)
			{
				case $CONSTANT.STATUS_ACTIVE:
					sStatusClass	= 'followup-closure-list-status-active';
					break;
				case $CONSTANT.STATUS_INACTIVE:
					sStatusClass	= 'followup-closure-list-status-inactive';
					break;
			}
			
			var	oTR	=	$T.tr(
							$T.td(oClosure.name),		
							$T.td(oClosure.description),
							$T.td(Flex.Constant.arrConstantGroups.followup_closure_type[oClosure.followup_closure_type_id].Name),
							$T.td({class: sStatusClass},
								Flex.Constant.arrConstantGroups.status[oClosure.status_id].Name
							),
							$T.td({class: 'followup-closure-list-actions'}
								// Action icons added below
							)
						);
			
			// Attach actions
			var oActionsTD	= oTR.select('td.followup-closure-list-actions').first();
			var oEdit		= $T.img({src: Component_FollowUp_Closure_List.EDIT_IMAGE_SOURCE, alt: 'Edit the Closure Reason', title: 'Edit the Closure Reason'});
			oEdit.observe('click', this._edit.bind(this, oClosure.id));
			oActionsTD.appendChild(oEdit);
			
			if (oClosure.status_id == $CONSTANT.STATUS_ACTIVE)
			{
				var oDeactivate	= $T.img({src: Component_FollowUp_Closure_List.INACTIVE_IMAGE_SOURCE, alt: 'Deactivate the Closure Reason', title: 'Deactivate the Closure Reason'});
				oDeactivate.observe('click', this._deactivate.bind(this, oClosure.id, oClosure.name, false));
				oActionsTD.appendChild(oDeactivate);
			}
			
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
		this.oPaginationButtons.oTop.oPreviousPage.disabled		= true;
		this.oPaginationButtons.oTop.oNextPage.disabled 		= true;
		this.oPaginationButtons.oTop.oLastPage.disabled 		= true;
		
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
				this.oPaginationButtons.oTop.oPreviousPage.disabled 	= false;
			}
			if (this.oPagination.intCurrentPage < (iPageCount - 1) && iPageCount)
			{
				// Enable the next and last buttons
				this.oPaginationButtons.oTop.oNextPage.disabled 	= false;
				this.oPaginationButtons.oTop.oLastPage.disabled 	= false;
			}
		}
	},
	
	_updateSorting	: function()
	{
		for (var sField in Component_FollowUp_Closure_List.SORT_FIELDS)
		{
			if (this._oSort.isRegistered(sField))
			{
				var oSortImg	= this._oContentDiv.select('th.followup-closure-list-header > img.followup-closure-list-sort-' + sField).first();
				var iDirection	= this._oSort.getSortDirection(sField);
				if (iDirection == Sort.DIRECTION_OFF)
				{
					oSortImg.hide();
				}
				else
				{
					oSortImg.src	= Component_FollowUp_Closure_List.SORT_IMAGE_SOURCE[iDirection];
					oSortImg.show();
				}
			}
		}
	},
	
	_updateFilters	: function()
	{
		for (var sField in Component_FollowUp_Closure_List.FILTER_FIELDS)
		{
			if (this._oFilter.isRegistered(sField))
			{
				var mValue	= this._oFilter.getFilterValue(sField);
				var oSpan	= this._oContentDiv.select('th.followup-closure-list-filter > span.followup-closure-list-filter-' + sField).first();
				
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
		var oDeleteImage				= $T.img({src: Component_FollowUp_Closure_List.REMOVE_FILTER_IMAGE_SOURCE, alt: 'Remove Filter', title: 'Remove Filter'});
		oDeleteImage.style.visibility	= 'hidden';
		oDeleteImage.observe('click', this._clearFilterValue.bind(this, sField));
		
		return	$T.th({class: 'followup-closure-list-filter'},
					$T.span({class: 'followup-closure-list-filter-' + sField},
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
		var oSortImg	= $T.img({class: 'followup-closure-list-sort-' + (sSortField ? sSortField : '')});
		var oTH			= 	$T.th({class: 'followup-closure-list-header' + (bMultiLine ? '-multiline' : '')},
								oSortImg,
								$T.span(sLabel)
							);
		oSortImg.hide();
		
		// Optional sort field
		if (sSortField)
		{
			var oSpan	= oTH.select('span').first();
			oSpan.addClassName('followup-closure-list-header-sort');
			
			this._oSort.registerToggleElement(oSpan, sSortField, Component_FollowUp_Closure_List.SORT_FIELDS[sSortField]);
			this._oSort.registerToggleElement(oSortImg, sSortField, Component_FollowUp_Closure_List.SORT_FIELDS[sSortField]);
		}
		
		// Optional filter field
		if (sFilterField)
		{
			var oIcon	= $T.img({src: Component_FollowUp_Closure_List.FILTER_IMAGE_SOURCE, alt: 'Filter by ' + sLabel, title: 'Filter by ' + sLabel});
			this._oFilter.registerFilterIcon(sFilterField, oIcon, sLabel);
			oTH.appendChild(oIcon);
		}
		
		return oTH;
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
	},
	
	_edit	: function(iClosureId)
	{
		var oPopup	= new Popup_FollowUp_Closure_Edit(iClosureId, this.oPagination.getCurrentPage.bind(this.oPagination));
	},
	
	_deactivate	: function(iClosureId, sClosureName, bConfirmation)
	{
		if (bConfirmation)
		{
			// Got confirmation, deactive the closure
			var fnDeactivate	= 	jQuery.json.jsonFunction(
										this.oPagination.getCurrentPage.bind(this.oPagination),
										this._ajaxError.bind(this),
										'FollowUp_Closure',
										'deactivate'
									);
			fnDeactivate(iClosureId);
		}
		else
		{
			// Show confirmation popup
			Reflex_Popup.yesNoCancel(
				"Are you sure you want to deactivate the closure reason '" + sClosureName +"'", 
				{fnOnYes: this._deactivate.bind(this, iClosureId, sClosureName, true)}
			);
		}
	}
});

Component_FollowUp_Closure_List.MAX_RECORDS_PER_PAGE		= 10;
Component_FollowUp_Closure_List.FILTER_IMAGE_SOURCE			= '../admin/img/template/table_row_insert.png';
Component_FollowUp_Closure_List.REMOVE_FILTER_IMAGE_SOURCE	= '../admin/img/template/delete.png';
Component_FollowUp_Closure_List.EDIT_IMAGE_SOURCE			= '../admin/img/template/pencil.png';
Component_FollowUp_Closure_List.INACTIVE_IMAGE_SOURCE		= '../admin/img/template/decline.png';
Component_FollowUp_Closure_List.ADD_IMAGE_SOURCE			= '../admin/img/template/new.png';

Component_FollowUp_Closure_List.SORT_IMAGE_SOURCE						= {};
Component_FollowUp_Closure_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_ASC]	= '../admin/img/template/order_asc.png';
Component_FollowUp_Closure_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_DESC]	= '../admin/img/template/order_desc.png';

Component_FollowUp_Closure_List.FILTER_FIELD_STATUS		= 'status_id';
Component_FollowUp_Closure_List.FILTER_FIELD_TYPE		= 'followup_closure_type_id';

Component_FollowUp_Closure_List.SORT_FIELD_NAME			= 'name';
Component_FollowUp_Closure_List.SORT_FIELD_DESCRIPTION	= 'description';
Component_FollowUp_Closure_List.SORT_FIELD_TYPE			= 'followup_closure_type_id';
Component_FollowUp_Closure_List.SORT_FIELD_STATUS		= 'status_id';

Component_FollowUp_Closure_List.DATA_SET_DEFINITION	= {sObject: 'FollowUp_Closure', sMethod: 'getDataSet'};

Component_FollowUp_Closure_List.getAllStatusAsSelectOptions	= function(fnCallback)
{
	var aOptions	= [];
	for (var iId in Flex.Constant.arrConstantGroups.status)
	{
		aOptions.push(
			$T.option({value: iId},
				Flex.Constant.arrConstantGroups.status[iId].Name
			)
		);
	}
	
	fnCallback(aOptions);
};

Component_FollowUp_Closure_List.getAllClosureTypesAsSelectOptions	= function(fnCallback)
{
	var aOptions	= [];
	for (var iId in Flex.Constant.arrConstantGroups.followup_closure_type)
	{
		aOptions.push(
			$T.option({value: iId},
				Flex.Constant.arrConstantGroups.followup_closure_type[iId].Name
			)
		);
	}
	
	fnCallback(aOptions);
};

// Filter Control field definitions
Component_FollowUp_Closure_List.FILTER_FIELDS	= {};
Component_FollowUp_Closure_List.FILTER_FIELDS[Component_FollowUp_Closure_List.FILTER_FIELD_STATUS]	= 	{
																											iType	: Filter.FILTER_TYPE_VALUE,
																											oOption	: 	{
																															sType		: 'select',
																															mDefault	: null,
																															oDefinition	:	{
																																				sLabel		: 'Owner',
																																				mEditable	: true,
																																				fnValidate	: null,
																																				fnPopulate	: Component_FollowUp_Closure_List.getAllStatusAsSelectOptions
																																			}
																														}
																										};
Component_FollowUp_Closure_List.FILTER_FIELDS[Component_FollowUp_Closure_List.FILTER_FIELD_TYPE]	= 	{
																											iType	: Filter.FILTER_TYPE_VALUE,
																											oOption	: 	{
																															sType		: 'select',
																															mDefault	: null,
																															oDefinition	:	{
																																				sLabel		: 'Owner',
																																				mEditable	: true,
																																				fnValidate	: null,
																																				fnPopulate	: Component_FollowUp_Closure_List.getAllClosureTypesAsSelectOptions
																																			}
																														}
																										};
// Sorting definitions
Component_FollowUp_Closure_List.SORT_FIELDS	=	{
													'name'						: Sort.DIRECTION_OFF,
													'description'				: Sort.DIRECTION_OFF,
													'followup_closure_type_id'	: Sort.DIRECTION_OFF,
													'status_id'					: Sort.DIRECTION_OFF
												};

