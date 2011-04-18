
var Component_Dataset_AJAX_Table = Class.create(Reflex_Component, {
	
	initialize : function($super) {
		// Additional Configuration
		this.CONFIG	= Object.extend({
			'sTitle'					: {},
			'sIcon'						: {},
			'oPagination'				: {},
			'oFilter'					: {},
			'oSort'						: {},
			'hFields'					: {},
			'aRequiredConstantGroups'	: []
		}, this.CONFIG || {});
		
		// Parent Constructor
		$super.apply(this, $A(arguments).slice(1));
		
		this.NODE.addClassName('component-dataset-ajax-table');
	},
	
	// Public
	
	refresh : function() {
		this._refreshData();
	},
	
	// Protected
	
	_buildUI : function() {
		this._oLoadingOverlay	= new Reflex_Loading_Overlay();
		this._oSection 			= new Component_Section();
		this.NODE				= $T.div(this._oSection.getNode());
		
		// Main table
		this._oSection.getAttachmentNode().appendChild(
			$T.table({class: 'reflex highlight-rows'},
				$T.thead(
					// Column headings
					$T.tr({class: 'component-dataset-ajax-table-headerrow'}),
					// Filter values
					$T.tr({class: 'component-dataset-ajax-table-filterrow'})
				),
				$T.tbody({class: 'component-dataset-ajax-table-data alternating'},
					this._createNoRecordsRow(true)
				)
			)
		);
		
		// Footer pagination
		this._oSection.getAttachmentNode('footer-actions').appendChild(
			$T.ul({class: 'reset horizontal component-dataset-ajax-table-options'},
				$T.li(
					$T.button({class: 'component-dataset-ajax-table-paginationbutton'},
						$T.img({src: Component_Dataset_AJAX_Table.PAGINATION_BUTTON_PATH + 'first.png'})
					)
				),
				$T.li(
					$T.button({class: 'component-dataset-ajax-table-paginationbutton'},
						$T.img({src: Component_Dataset_AJAX_Table.PAGINATION_BUTTON_PATH + 'previous.png'})
					)
				),
				$T.li(
					$T.button({class: 'component-dataset-ajax-table-paginationbutton'},
						$T.img({src: Component_Dataset_AJAX_Table.PAGINATION_BUTTON_PATH + 'next.png'})
					)
				),
				$T.li(
					$T.button({class: 'component-dataset-ajax-table-paginationbutton'},
						$T.img({src: Component_Dataset_AJAX_Table.PAGINATION_BUTTON_PATH + 'last.png'})
					)
				)
			)
		);

		// Bind events to the pagination buttons
		var aBottomPageButtons 	= this.NODE.select('.component-dataset-ajax-table-paginationbutton');
		aBottomPageButtons[0].observe('click', this._changePage.bind(this, 'firstPage'));
		aBottomPageButtons[1].observe('click', this._changePage.bind(this, 'previousPage'));
		aBottomPageButtons[2].observe('click', this._changePage.bind(this, 'nextPage'));
		aBottomPageButtons[3].observe('click', this._changePage.bind(this, 'lastPage'));

		// Setup pagination button object
		this._oPaginationButtons = {
			oFirstPage		: aBottomPageButtons[0],
			oPreviousPage	: aBottomPageButtons[1],
			oNextPage		: aBottomPageButtons[2],
			oLastPage		: aBottomPageButtons[3]
		};
		
		this.ATTACHMENTS['header-actions'] = this._oSection.getAttachmentNode('header-actions');
	},
	
	_syncUI : function(bConstantsLoaded) {
		if (!bConstantsLoaded) {
			Flex.Constant.loadConstantGroup(this.get('aRequiredConstantGroups'), this._syncUI.bind(this, true));
			return;
		}
		
		// Section header
		this.NODE.select('.component-section-header-text').first().appendChild(
			$T.span(
				$T.span(this.get('sTitle')),
				$T.span({class: 'component-dataset-ajax-table-pagination-info'})
			)
		);
		this.NODE.select('.component-section-header-icon').first().src = this.get('sIcon');
		
		this.get('oPagination').setUpdateCallback(this._pageLoaded.bind(this));
		this.get('oFilter').setFilterUpdateCallback(this._showLoading.bind(this, true));
		this.get('oSort').setFieldUpdateCallback(this._showLoading.bind(this, true));
		
		// Field/column setup
		var oHeadingTR 	= this.NODE.select('.component-dataset-ajax-table-headerrow').first();
		var oFilterTR 	= this.NODE.select('.component-dataset-ajax-table-filterrow').first();
		var hFields		= this.get('hFields');
		var iFilterable	= 0;
		for (var sFieldName in hFields) {
			// Contains:
			//  - sDisplayName
			//  - oFilterConfig
			//  - mSortField
			//  - sSortDirection
			//  - fnGetValue
			var oFieldDefinition = hFields[sFieldName];
			
			// Create header
			var oHeaderTH = $T.th({class: 'component-dataset-ajax-table-' + sFieldName + '-header'},
				(oFieldDefinition.sDisplayName || oFieldDefinition.sDisplayName == '') ? oFieldDefinition.sDisplayName : sFieldName
			);
			oHeadingTR.appendChild(oHeaderTH);
			
			if (oFieldDefinition.oFilterConfig) {
				// The field is filterable
				var oFilter	= this.get('oFilter');
				
				// Add to the filterer
				this._fillOutFilterConfigWithDefaults(oFieldDefinition.oFilterConfig);
				oFilter.addFilter(sFieldName, oFieldDefinition.oFilterConfig);
				
				// Add a filter header
				var oDeleteImage = $T.img({class: 'component-dataset-ajax-table-filter-delete', src: '../admin/img/template/delete.png', alt: 'Remove Filter', title: 'Remove Filter'});
				oDeleteImage.hide();
				oDeleteImage.observe('click', this._clearFilterValue.bind(this, sFieldName));
				
				var oFiterImage	= $T.img({class: 'header-filter', src: '../admin/img/template/table_row_insert.png', alt: 'Filter by ' + oFieldDefinition.sDisplayName, title: 'Filter by ' + oFieldDefinition.sDisplayName});
				oFilter.registerFilterIcon(sFieldName, oFiterImage, oFieldDefinition.sDisplayName, this.NODE, 0, 10);

				oFilterTR.appendChild(
					$T.th({class: 'component-dataset-ajax-table-filter-heading'},
						$T.span({class: 'filter-' + sFieldName},
							'All'
						),
						$T.div(
							oFiterImage,
							oDeleteImage
						)
					)
				);
				
				iFilterable++;
			} else {
				// Not filterable, empty th in filter header
				oFilterTR.appendChild($T.th());
			}
			
			if (oFieldDefinition.mSortField) {
				// The field is sortable
				var oSort = this.get('oSort');
				
				// Create a sort toggle element
				var sSortField	= (oFieldDefinition.mSortField === true ? sFieldName : oFieldDefinition.mSortField);
				var oSortImg 	= $T.img({class: 'sort-' + (sSortField ? sSortField : '')});
				oHeaderTH.insertBefore(oSortImg, oHeaderTH.firstChild);
				
				// Add extra class to sort text
				var oSpan = oHeaderTH.select('span').first();
				if (!oSpan) {
					oSpan = oHeaderTH;
				}
				oSpan.addClassName('component-dataset-ajax-table-header-sort');
				
				// Register the field & the toggle element(s) with the sorter
				oSort.registerToggleElement(oSpan, sSortField, oFieldDefinition.sDefaultSortDirection);
				
				if (oFieldDefinition.sSortDirection) {
					oSort.sortField(sSortField, oFieldDefinition.sSortDirection, true);
				}
			}
		}
		
		if (iFilterable == 0) {
			oFilterTR.remove();
		}
		
		this._bFirstLoadComplete = false;
		this._refreshData();
	},
	
	_fillOutFilterConfigWithDefaults : function(oFieldDefinition) {
		var oFilterConfig	 	= oFieldDefinition.oFilterConfig;
		var oControlDefinition 	= oFieldDefinition.oOption.oDefinition;
		
		if (!oControlDefinition) {
			oFieldDefinition.oOption.oDefinition = {};
		}
		
		if (!oControlDefinition.sLabel) {
			oControlDefinition.sLabel = oFieldDefinition.sDisplayName;
		}
		
		if (Object.isUndefined(oControlDefinition.mEditable)) {
			oControlDefinition.mEditable = true;
		}
		
		if (Object.isUndefined(oControlDefinition.mMandatory)) {
			oControlDefinition.mMandatory = false;
		}
	},
	
	_refreshData : function() {
		this.get('oSort').refreshData(true);
		this.get('oFilter').refreshData(true);
		this.get('oPagination').getCurrentPage();
	},
	
	_pageLoaded : function(oResultSet) {
		var oTBody = this.NODE.select('.component-dataset-ajax-table-data').first();
		
		// Empty the table
		oTBody.select('tr').each(Element.remove);
		
		// Check if any results came back
		if (!oResultSet || oResultSet.intTotalResults == 0 || oResultSet.arrResultSet.length == 0) {
			// No results
			oTBody.appendChild(this._createNoRecordsRow());
		} else {
			// We've got results, add the rows
			var aData 	= jQuery.json.arrayAsObject(oResultSet.arrResultSet);
			var hFields	= this.get('hFields');
			for (var i in aData) {
				var oTR 		= $T.tr();
				var oData		= aData[i];
				var iPosition	= parseInt(i);
				for (var sFieldName in hFields) {
					var oFieldDefinition = hFields[sFieldName];
					if (oFieldDefinition.fnCreateCell) {
						// A cell creation function has been defined, use it 
						oTR.appendChild(oFieldDefinition.fnCreateCell(oData, iPosition));
					} else {
						// No cell creation function defined, show the raw field value
						var mRawValue = oData[sFieldName];
						oTR.appendChild($T.td(mRawValue ? mRawValue : null));
					}
				}
				
				// Add the row to the tbody
				oTBody.appendChild(oTR);
			}
		}

		this._updatePagination();
		this._updateSorting();
		this._updateFilters();
		this._showLoading(false);
		
		if (!this._bFirstLoadComplete) {
			this._bFirstLoadComplete = true;
			this._onReady();
		}
	},
	
	_showLoading : function(bShow) {
		if (!this._bFirstLoadComplete) {
			return;
		} else if (bShow) {
			this._oLoadingOverlay.attachTo(this.NODE.select('table > tbody').first());
		} else {
			this._oLoadingOverlay.detach();
		}
	},

	_createNoRecordsRow	: function(bOnLoad) {
		return $T.tr(
			$T.td({class: 'no-rows', colspan: 0},
				(bOnLoad ? 'Loading...' : 'There is no data to display')
			)
		);
	},
	
	_clearFilterValue : function(sField) {
		this.get('oFilter').clearFilterValue(sField);
		this.get('oFilter').refreshData();
	},
	
	_updatePagination : function(iPageCount) {
		// Update the 'disabled' state of each pagination button
		this._oPaginationButtons.oFirstPage.disabled 	= true;
		this._oPaginationButtons.oPreviousPage.disabled	= true;
		this._oPaginationButtons.oNextPage.disabled 	= true;
		this._oPaginationButtons.oLastPage.disabled 	= true;

		var oPagination = this.get('oPagination');
		if (iPageCount == undefined) {
			// Get the page count
			oPagination.getPageCount(this._updatePagination.bind(this));
		} else {
			// Update Page ? of ?, show 1 for page count if it is 0 because there is technically still a page even though it's empty
			var oPageInfo		= this.NODE.select('span.component-dataset-ajax-table-pagination-info').first();
			oPageInfo.innerHTML	= '(Page '+ (oPagination.intCurrentPage + 1) +' of ' + (iPageCount == 0 ? 1 : iPageCount) + ')';

			if (oPagination.intCurrentPage != Pagination.PAGE_FIRST) {
				// Enable the first and previous buttons
				this._oPaginationButtons.oFirstPage.disabled		= false;
				this._oPaginationButtons.oPreviousPage.disabled 	= false;
			}
			
			if (oPagination.intCurrentPage < (iPageCount - 1) && iPageCount) {
				// Enable the next and last buttons
				this._oPaginationButtons.oNextPage.disabled 	= false;
				this._oPaginationButtons.oLastPage.disabled 	= false;
			}
		}
	},

	_updateSorting	: function() {
		var hFields = this.get('hFields');
		var oSort	= this.get('oSort');
		for (var sFieldName in hFields) {
			var oFieldDefinition	= hFields[sFieldName];
			var sSortField 			= (oFieldDefinition.mSortField === true ? sFieldName : oFieldDefinition.mSortField);
			if (oSort.isRegistered(sSortField)) {
				var oSortImg	= this.NODE.select('img.sort-' + sSortField).first();
				var sDirection	= oSort.getSortDirection(sSortField);
				oSortImg.src	= Component_Dataset_AJAX_Table.SORT_IMAGE_SOURCE[sDirection];
				oSortImg.show();
			}
		}
	},

	_updateFilters	: function() {
		var hFields = this.get('hFields');
		var oSort	= this.get('oSort');
		for (var sFieldName in hFields) {
			var oFieldDefinition = hFields[sFieldName];
			if (oFieldDefinition.oFilterConfig && oFieldDefinition.oFilterConfig.iType) {
				this._updateFilterDisplayValue(sFieldName);
			}
		}
	},

	_updateFilterDisplayValue	: function(sFieldName) {
		var oFilter = this.get('oFilter');
		if (oFilter.isRegistered(sFieldName)) {
			var mValue	= oFilter.getFilterValue(sFieldName);
			var oSpan	= this.NODE.select('th.component-dataset-ajax-table-filter-heading > span.filter-' + sFieldName).first();
			if (oSpan) {
				var oDeleteImage = oSpan.up().select('img.component-dataset-ajax-table-filter-delete').first();
				if (mValue !== null && (typeof mValue !== 'undefined')) {
					// Have a value, show it
					var oFieldDefinition = this.get('hFields')[sFieldName];
					if (oFieldDefinition.oFilterConfig.fnGetDisplayText) {
						// A customer filter display value function is defined for the field, invoke it passing through the option controls
						oSpan.innerHTML = oFieldDefinition.oFilterConfig.fnGetDisplayText.apply(null, oFilter.getControlsForField(sFieldName));
					}
					oDeleteImage.show();
				} else {
					// No value, hide delete image
					oSpan.innerHTML = 'All';
					oDeleteImage.hide();
				}
			}
		}
	},
	
	_changePage	: function(sFunction) {
		this._showLoading(true);
		this.get('oPagination')[sFunction]();
	}
});

Component_Dataset_AJAX_Table.SORT_IMAGE_SOURCE 						= {};
Component_Dataset_AJAX_Table.SORT_IMAGE_SOURCE[Sort.DIRECTION_OFF]	= '../admin/img/template/order_neither.png';
Component_Dataset_AJAX_Table.SORT_IMAGE_SOURCE[Sort.DIRECTION_ASC]	= '../admin/img/template/order_asc.png';
Component_Dataset_AJAX_Table.SORT_IMAGE_SOURCE[Sort.DIRECTION_DESC]	= '../admin/img/template/order_desc.png';

Object.extend(Component_Dataset_AJAX_Table, {
	PAGINATION_BUTTON_PATH : '../admin/img/template/resultset_',
	
	createAsPopup : function () {
		var	oComponent	= Component_Dataset_AJAX_Table.constructApply($A(arguments)),
			oPopup		= new Reflex_Popup(128);
	
		oPopup.setTitle(oComponent.get('sTitle'));
		oPopup.addCloseButton();
		oPopup.setContent(oComponent.getNode());
	
		return oPopup;
	},
	
	// DEV ONLY BELOW
	test : function() {
		var oDataset 	= new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, new Reflex_AJAX_Request("Carrier_Module", "getDataset"));
		var oPagination = new Pagination(null, 10, oDataset);
		var oFilter		= new Filter(oDataset, oPagination);
		var oSort 		= new Sort(oDataset, oPagination, true);
		var oLoading	= new Reflex_Popup.Loading();
		oLoading.display();
		
		var oPopup = Component_Dataset_AJAX_Table.createAsPopup(
			{
				sTitle		: 'Test Carrier Modules',
				sIcon		: '../admin/img/template/pencil.png',
				oPagination	: oPagination,
				oFilter		: oFilter,
				oSort		: oSort,
				hFields		: {
				
					id : {
						sDisplayName	: '#',
						mSortField		: true,
						sSortDirection	: Sort.DIRECTION_ASC
					},
					
					carrier_id : {
						sDisplayName	: 'Carrier',
						mSortField 		: 'carrier_name',
						oFilterConfig	: {
							iType	: Filter.FILTER_TYPE_VALUE,
							oOption	: {
								sType		: 'select',
								oDefinition	: {
									fnPopulate	: function(fnCallback) {
										var fnResponse	= function(fnCallback, oResponse) {
											if (!oResponse.bSuccess) {
												Component_Carrier_Module_List._ajaxError(oResponse);
											} else {
												var aOptions = [];
												for (var i = 0; i < oResponse.aRecords.length; i++) {
													aOptions.push(
														$T.option({value: oResponse.aRecords[i].Id},
															oResponse.aRecords[i].Name
														)
													);
												}
												fnCallback(aOptions);
											}
										}.curry(fnCallback);
										
										var fnRequest = jQuery.json.jsonFunction(fnResponse, fnResponse, 'Carrier', 'getCarriers');
										fnRequest();
										return; 
									}
								}
							},
							fnGetDisplayText : function(oSelect) {
								return oSelect.getElementText();
							}
						},
						fnCreateCell : function(oData) {
							return $T.td(oData.carrier_name);
						}
					},
					
					customer_group_id : {
						sDisplayName	: 'Customer Group',
						mSortField 		: 'customer_group_name',
						oFilterConfig	: {
							iType	: Filter.FILTER_TYPE_VALUE,
							oOption	: {
								sType		: 'select',
								oDefinition	: {
									fnPopulate	: Customer_Group.getAllAsSelectOptions
								}
							},
							fnGetDisplayText : function(oSelect) {
								return oSelect.getElementText();
							}
						},
						fnCreateCell : function(oData) {
							return $T.td(oData.customer_group_name);
						}
					},
					
					carrier_module_type_id : {
						sDisplayName	: 'Type',
						mSortField		: 'carrier_module_type_name',
						oFilterConfig	: {
							iType	: Filter.FILTER_TYPE_VALUE,
							oOption	: {
								sType		: 'select',
								oDefinition	: {
									fnPopulate	: Flex.Constant.getConstantGroupOptions.curry('carrier_module_type')
								}
							},
							fnGetDisplayText : function(oSelect) {
								return oSelect.getElementText();
							}
						},
						fnCreateCell : function(oData) {
							return $T.td(oData.carrier_module_type_name);
						}
					},
					
					file_type_id : {
						sDisplayName	: 'File Type',
						mSortField		: 'file_type_name',
						oFilterConfig	: {
							iType	: Filter.FILTER_TYPE_VALUE,
							oOption	: {
								sType		: 'select',
								oDefinition	: {
									fnPopulate	: Flex.Constant.getConstantGroupOptions.curry('resource_type')
								}
							},
							fnGetDisplayText : function(oSelect) {
								return oSelect.getElementText();
							}
						},
						fnCreateCell : function(oData) {
							return $T.td(oData.file_type_name);
						}
					},
					
					module : {
						sDisplayName	: 'Module',
						mSortField		: true,
						fnCreateCell	: function(oData) {
							return $T.td({class: 'component-carrier-module-list-module-cell'},
								oData.module
							);
						}
					},
					
					description : {
						sDisplayName	: 'Description',
						mSortField		: true,
						oFilterConfig	: {
							iType	: Filter.FILTER_TYPE_CONTAINS,
							oOption	: {sType: 'text'},
							fnGetDisplayText : function(oText) {
								return "Like '" + oText.getElementValue() + "'";
							}
						},
						fnCreateCell : function(oData) {
							return $T.td({class: 'component-carrier-module-list-description-cell'},
								oData.description
							)
						}
					},
					
					frequency_value : {
						sDisplayName	: 'Frequency',
						mSortField		: 'frequency_value',
						fnCreateCell	: function(oData) {
							return $T.td(oData.frequency + ' ' + oData.frequency_type_name + (oData.frequency == 1 ? '' : 's'));
						}
					},
					
					last_sent_datetime : {
						sDisplayName	: 'Last Sent',
						mSortField		: true,
						fnCreateCell	: function(oData) {
							var oLastSentDate	= Date.$parseDate(oData.last_sent_datetime, 'Y-m-d H:i:s');
							var oLastSentTD		= $T.td()
							if (oLastSentDate) {
								oLastSentTD.appendChild($T.div(oLastSentDate.$format('d/m/y')));
								oLastSentTD.appendChild(
									$T.div({class: 'datetime-time'},
										oLastSentDate.$format('g:i A')
									)
								);
							}
							
							return oLastSentTD;
						}
					},
					
					earliest_delivery : {
						sDisplayName	: 'Earliest Delivery',
						mSortField		: true,
						fnCreateCell	: function(oData) {
							var oEarliestDeliveryDate = Date.$parseDate('1970-01-01 00:00:00', 'Y-m-d H:i:s');
							oEarliestDeliveryDate.setSeconds(oData.earliest_delivery);
							return $T.td(oEarliestDeliveryDate.$format('g:i A'));
						}
					},
					
					is_active : {
						sDisplayName	: 'Active',
						mSortField		: 'is_active_label',
						oFilterConfig	: {
							iType	: Filter.FILTER_TYPE_VALUE,
							oOption	: {sType: 'checkbox'},
							fnGetDisplayText : function(oCheckbox) {
								return (oCheckbox.getElementValue() ? 'Yes' : 'No');
							}
						},
						fnCreateCell : function(oData) {
							return $T.td(oData.is_active_label);
						}
					},
					
					actions : {
						sDisplayName	: '',
						fnCreateCell	: function(oData, iPosition) {
							// Create the row element
							var oActionTD 		= $T.td();
							var sStatusIconText = null;
							var sStatusIconSrc 	= null;
							var fnStatusOnClick	= null;
							if (oData.is_active) {
								sStatusIconText = 'Disable';
								sStatusIconSrc 	= '../admin/img/template/decline.png';
								//fnStatusOnClick	= this._setModuleActive.bind(this, oData.id, false, null);
							} else {
								sStatusIconText = 'Enable';
								sStatusIconSrc 	= '../admin/img/template/approve.png';
								//fnStatusOnClick	= this._setModuleActive.bind(this, oData.id, true, null);
							}
							
							// Status icon
							var oStatusIcon = $T.img({class: 'pointer', src: sStatusIconSrc, alt: sStatusIconText, title: sStatusIconText});
							oStatusIcon.observe('click', fnStatusOnClick);
							oActionTD.appendChild(oStatusIcon);
							
							// View icon
							var oViewIcon = $T.img({class: 'pointer', src: '../admin/img/template/magnifier.png', alt: 'View Module', title: 'View Module'});
							//oViewIcon.observe('click', this._viewModule.bind(this, oData.id));
							oActionTD.appendChild(oViewIcon);
							
							// Edit icon
							var oEditIcon = $T.img({class: 'pointer', src: '../admin/img/template/pencil.png', alt: 'Edit Module', title: 'Edit Module'});
							//oEditIcon.observe('click', this._editModule.bind(this, oData.id));
							oActionTD.appendChild(oEditIcon);
							
							// Clone icon
							var oCloneIcon = $T.img({class: 'pointer', src: '../admin/img/template/new.png', alt: 'Clone Module', title: 'Clone Module'});
							//oCloneIcon.observe('click', this._cloneModule.bind(this, oData.id));
							oActionTD.appendChild(oCloneIcon);
							
							return oActionTD;
						}
					}
				},
				aRequiredConstantGroups	: ['status'],
				fnOnReady				: function() {
					oLoading.hide();
					oPopup.display();
				}
			}
		);
	}
});

