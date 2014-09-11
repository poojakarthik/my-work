
var Component_FollowUp_Category_List = Class.create(
{
	initialize	: function(oContainerDiv)
	{
		this._hFilters	= {};
		
		// Create DataSet & pagination object
		this.oDataSet		= 	new Dataset_Ajax(
									Dataset_Ajax.CACHE_MODE_NO_CACHING, 
									Component_FollowUp_Category_List.DATA_SET_DEFINITION
								);
		this.oPagination	= 	new Pagination(
									this._updateTable.bind(this), 
									Component_FollowUp_Category_List.MAX_RECORDS_PER_PAGE, 
									this.oDataSet
								);
		
		// Create filter object
		this._oFilter	= new Filter(this.oDataSet, this.oPagination);
		for (var sFieldName in Component_FollowUp_Category_List.FILTER_FIELDS)
		{
			this._oFilter.addFilter(sFieldName, Component_FollowUp_Category_List.FILTER_FIELDS[sFieldName]);
		}
		
		// Create sort object
		this._oSort	= new Sort(this.oDataSet, this.oPagination, true);
		
		// Create the page HTML
		var sButtonPathBase	= '../admin/img/template/resultset_';
		this._oContentDiv 	= 	$T.div({class: 'followup-configure-list'},
									// All
									$T.div({class: 'section'},
										$T.div({class: 'section-header'},
											$T.div({class: 'section-header-title'},
												$T.span('Categories'),
												$T.span({class: 'followup-configure-list-pagination-info'},
													''
												)
											),
											$T.div({class: 'section-header-options'},
												$T.div({class: 'followup-configure-list-pagination'},
													$T.button({class: 'followup-configure-list-pagination-button'},
														$T.img({src: sButtonPathBase + 'first.png'})
													),
													$T.button({class: 'followup-configure-list-pagination-button'},
														$T.img({src: sButtonPathBase + 'previous.png'})
													),
													$T.button({class: 'followup-configure-list-pagination-button'},
														$T.img({src: sButtonPathBase + 'next.png'})
													),
													$T.button({class: 'followup-configure-list-pagination-button'},
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
															Component_FollowUp_Category_List.SORT_FIELD_NAME
														),
														this._createFieldHeader(
															'Description', 
															Component_FollowUp_Category_List.SORT_FIELD_DESCRIPTION
														),
														this._createFieldHeader(
															'Status', 
															Component_FollowUp_Category_List.SORT_FIELD_STATUS/*,
															Component_FollowUp_Category_List.FILTER_FIELD_STATUS*/
														),
														this._createFieldHeader('')
													)/*,
													// Filter values
													$T.tr(
														$T.th(),
														$T.th(),
														this._createFilterValueElement(Component_FollowUp_Category_List.FILTER_FIELD_STATUS),
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
												$T.img({src: Component_FollowUp_Category_List.ADD_IMAGE_SOURCE, alt: 'Add Category', title: 'Add Category'}),
												$T.span('Add Category')
											)
										)
									)
								);
		
		// Bind events to the pagination buttons
		var aTopPageButtons		= this._oContentDiv.select('div.section-header-options button.followup-configure-list-pagination-button');
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
		var oPageInfo	= this._oContentDiv.select('span.followup-configure-list-pagination-info').first();
		
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
	
	_createTableRow	: function(oCategory)
	{
		if (oCategory.id != null)
		{
			var sStatusClass	= '';
			switch (oCategory.status_id)
			{
				case $CONSTANT.STATUS_ACTIVE:
					sStatusClass	= 'followup-configure-list-status-active';
					break;
				case $CONSTANT.STATUS_INACTIVE:
					sStatusClass	= 'followup-configure-list-status-inactive';
					break;
			}
			
			var	oTR	=	$T.tr(
							$T.td(oCategory.name),		
							$T.td(oCategory.description),
							$T.td({class: sStatusClass},
								Flex.Constant.arrConstantGroups.status[oCategory.status_id].Name
							),
							$T.td({class: 'followup-configure-list-actions'},
								$T.ul({class: 'reset horizontal'}
									// Actions added below
								)
							)
						);
			
			// Attach actions
			var oActionsUL	= oTR.select('td.followup-configure-list-actions > ul.reset').first();
			var oEdit		= $T.img({src: Component_FollowUp_Category_List.EDIT_IMAGE_SOURCE, alt: 'Edit the Category', title: 'Edit the Category'});
			oEdit.observe('click', this._edit.bind(this, oCategory.id));
			oActionsUL.appendChild(
				$T.li(oEdit)
			);
			
			var oDeactivate	= $T.img({src: Component_FollowUp_Category_List.INACTIVE_IMAGE_SOURCE, alt: 'Deactivate the Category', title: 'Deactivate the Category'});
			oDeactivate.observe('click', this._deactivate.bind(this, oCategory.id, oCategory.name, false));
			oActionsUL.appendChild(
				$T.li(oDeactivate)
			);
			
			if (oCategory.status_id != $CONSTANT.STATUS_ACTIVE)
			{
				// Hide the deactivate, not active
				oDeactivate.style.visibility	= 'hidden';
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
		for (var sField in Component_FollowUp_Category_List.SORT_FIELDS)
		{
			if (this._oSort.isRegistered(sField))
			{
				var oSortImg	= this._oContentDiv.select('th.followup-configure-list-header > img.followup-configure-list-sort-' + sField).first();
				var iDirection	= this._oSort.getSortDirection(sField);
				if (iDirection == Sort.DIRECTION_OFF)
				{
					oSortImg.hide();
				}
				else
				{
					oSortImg.src	= Component_FollowUp_Category_List.SORT_IMAGE_SOURCE[iDirection];
					oSortImg.show();
				}
			}
		}
	},
	
	_updateFilters	: function()
	{
		for (var sField in Component_FollowUp_Category_List.FILTER_FIELDS)
		{
			if (this._oFilter.isRegistered(sField))
			{
				var mValue	= this._oFilter.getFilterValue(sField);
				var oSpan	= this._oContentDiv.select('th.followup-configure-list-filter > span.followup-configure-list-filter-' + sField).first();
				
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
		var oDeleteImage				= $T.img({src: Component_FollowUp_Category_List.REMOVE_FILTER_IMAGE_SOURCE, alt: 'Remove Filter', title: 'Remove Filter'});
		oDeleteImage.style.visibility	= 'hidden';
		oDeleteImage.observe('click', this._clearFilterValue.bind(this, sField));
		
		return	$T.th({class: 'followup-configure-list-filter'},
					$T.span({class: 'followup-configure-list-filter-' + sField},
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
		var oSortImg	= $T.img({class: 'followup-configure-list-sort-' + (sSortField ? sSortField : '')});
		var oTH			= 	$T.th({class: 'followup-configure-list-header' + (bMultiLine ? '-multiline' : '')},
								oSortImg,
								$T.span(sLabel)
							);
		oSortImg.hide();
		
		// Optional sort field
		if (sSortField)
		{
			var oSpan	= oTH.select('span').first();
			oSpan.addClassName('followup-configure-list-header-sort');
			
			this._oSort.registerToggleElement(oSpan, sSortField, Component_FollowUp_Category_List.SORT_FIELDS[sSortField]);
			this._oSort.registerToggleElement(oSortImg, sSortField, Component_FollowUp_Category_List.SORT_FIELDS[sSortField]);
		}
		
		// Optional filter field
		if (sFilterField)
		{
			var oIcon	= $T.img({src: Component_FollowUp_Category_List.FILTER_IMAGE_SOURCE, alt: 'Filter by ' + sLabel, title: 'Filter by ' + sLabel});
			this._oFilter.registerFilterIcon(sFilterField, oIcon, sLabel);
			oTH.appendChild(oIcon);
		}
		
		return oTH;
	},
	
	_ajaxError : function(oResponse) {
		if (this.oLoading) {
			this.oLoading.hide();
			delete this.oLoading;
		}
		
		jQuery.json.errorPopup(oResponse);
	},
	
	_edit	: function(iCategoryId)
	{
		var oPopup	= 	new Popup_Record_Edit(
							'FollowUp_Category', 
							'Follow-Up Category', 
							'', 
							iCategoryId, 
							Component_FollowUp_Category_List.EDIT_FIELDS, 
							this.oPagination.getCurrentPage.bind(this.oPagination)
						);
	},
	
	_deactivate	: function(iCategoryId, sCategoryName, bConfirmation)
	{
		if (bConfirmation)
		{
			// Got confirmation, deactive the category
			var fnDeactivate	= 	jQuery.json.jsonFunction(
										this.oPagination.getCurrentPage.bind(this.oPagination),
										this._ajaxError.bind(this),
										'FollowUp_Category',
										'deactivate'
									);
			fnDeactivate(iCategoryId);
		}
		else
		{
			// Show confirmation popup
			Reflex_Popup.yesNoCancel(
				"Are you sure you want to deactivate the category '" + sCategoryName +"'", 
				{fnOnYes: this._deactivate.bind(this, iCategoryId, sCategoryName, true)}
			);
		}
	}
});

Component_FollowUp_Category_List.MAX_RECORDS_PER_PAGE		= 5;
Component_FollowUp_Category_List.FILTER_IMAGE_SOURCE		= '../admin/img/template/table_row_insert.png';
Component_FollowUp_Category_List.REMOVE_FILTER_IMAGE_SOURCE	= '../admin/img/template/delete.png';
Component_FollowUp_Category_List.EDIT_IMAGE_SOURCE			= '../admin/img/template/pencil.png';
Component_FollowUp_Category_List.INACTIVE_IMAGE_SOURCE		= '../admin/img/template/decline.png';
Component_FollowUp_Category_List.ADD_IMAGE_SOURCE			= '../admin/img/template/new.png';

Component_FollowUp_Category_List.SORT_IMAGE_SOURCE						= {};
Component_FollowUp_Category_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_ASC]	= '../admin/img/template/order_asc.png';
Component_FollowUp_Category_List.SORT_IMAGE_SOURCE[Sort.DIRECTION_DESC]	= '../admin/img/template/order_desc.png';

Component_FollowUp_Category_List.FILTER_FIELD_STATUS	= 'status_id';

Component_FollowUp_Category_List.SORT_FIELD_NAME		= 'name';
Component_FollowUp_Category_List.SORT_FIELD_DESCRIPTION	= 'description';
Component_FollowUp_Category_List.SORT_FIELD_STATUS		= 'status_id';

Component_FollowUp_Category_List.DATA_SET_DEFINITION	= {sObject: 'FollowUp_Category', sMethod: 'getDataSet'};

// Control field population function
Component_FollowUp_Category_List.getAllStatusAsSelectOptions	= function(fnCallback)
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

// Filter Control field definitions
Component_FollowUp_Category_List.FILTER_FIELDS	= {};
Component_FollowUp_Category_List.FILTER_FIELDS[Component_FollowUp_Category_List.FILTER_FIELD_STATUS]	= 	{
																												iType	: Filter.FILTER_TYPE_VALUE,
																												oOption	: 	{
																																sType		: 'select',
																																mDefault	: null,
																																oDefinition	:	{
																																					sLabel		: 'Owner',
																																					mEditable	: true,
																																					fnValidate	: null,
																																					fnPopulate	: Component_FollowUp_Category_List.getAllStatusAsSelectOptions
																																				}
																															}
																											};

// Sorting definitions
Component_FollowUp_Category_List.SORT_FIELDS	=	{
														'name'			: Sort.DIRECTION_OFF,
														'description'	: Sort.DIRECTION_OFF,
														'status_id'		: Sort.DIRECTION_OFF
													};

//Control field definitions
Component_FollowUp_Category_List.EDIT_FIELDS				= {};
Component_FollowUp_Category_List.EDIT_FIELDS.name			= 	{
																	sType		: 'text',
																	oDefinition	:	{
																						sLabel		: 'Name',
																						fnValidate	: Reflex_Validation.stringOfLength.curry(null, 128),
																						mMandatory	: true
																					}
																};
Component_FollowUp_Category_List.EDIT_FIELDS.description	= 	{
																	sType		: 'text',
																	oDefinition	:	{
																						sLabel		: 'Description',
																						fnValidate	: Reflex_Validation.stringOfLength.curry(null, 255),
																						mMandatory	: true
																					}
																};
Component_FollowUp_Category_List.EDIT_FIELDS.status_id		=	{
																	sType			: 'select',
																	mDefaultValue	: 1,	// ACTIVE
																	oDefinition		:	{
																							sLabel		: 'Status',
																							fnValidate	: null,
																							fnPopulate	: Component_FollowUp_Category_List.getAllStatusAsSelectOptions,
																							mMandatory	: true
																						}
																};

