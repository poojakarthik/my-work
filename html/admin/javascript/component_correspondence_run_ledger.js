
var Component_Correspondence_Run_Ledger = Class.create(
{
	initialize	: function(oContainerDiv, oLoadingPopup)
	{
		this._hFilters					= {};
		this._oContainerDiv				= oContainerDiv;
		this._bFirstLoadComplete		= false;
		this._hControlOnChangeCallbacks	= {};
		this._oLoadingPopup				= oLoadingPopup;

		// Create Dataset & pagination object
		this.oDataSet	= new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, Component_Correspondence_Run_Ledger.DATA_SET_DEFINITION);

		this.oPagination	= new Pagination(this._updateTable.bind(this), Component_Correspondence_Run_Ledger.MAX_RECORDS_PER_PAGE, this.oDataSet);

		// Create Dataset filter object
		this._oFilter	=	new Filter(
								this.oDataSet,
								this.oPagination,
								this._showLoading.bind(this, true) 	// On field value change
							);

		// Add all filter fields
		for (var sFieldName in Component_Correspondence_Run_Ledger.FILTER_FIELDS)
		{
			if (Component_Correspondence_Run_Ledger.FILTER_FIELDS[sFieldName].iType)
			{
				this._oFilter.addFilter(sFieldName, Component_Correspondence_Run_Ledger.FILTER_FIELDS[sFieldName]);
			}
		}

		// Create Dataset sort object
		this._oSort	= new Sort(this.oDataSet, this.oPagination, true, this._showLoading.bind(this, true));

		// Create the page HTML
		var sButtonPathBase	= '../admin/img/template/resultset_';
		var oSection		= new Section(true);
		this._oContentDiv 	= 	$T.div({class: 'correspondence-run-ledger'},
									oSection.getElement()
								);
		
		// Title
		oSection.setTitleContent(
			$T.span(
				$T.span('Correspondence Runs'),
				$T.span({class: 'pagination-info'},
					''
				)
			)
		);
		
		// Main content -- table
		oSection.setContent(
			$T.table({class: 'reflex highlight-rows'},
				$T.thead(
					// Column headings
					$T.tr(
						this._createFieldHeader('Processed', 'processed_datetime'),
						this._createFieldHeader('Source'),
						this._createFieldHeader('Template', 'correspondence_template_name'),
						this._createFieldHeader('Created By', 'created_employee_name'),
						this._createFieldHeader('Items', 'count_correspondence'),
						this._createFieldHeader('Data File(s)'),
						this._createFieldHeader('Dispatched', 'delivered_datetime'),
						this._createFieldHeader('Status'),
						this._createFieldHeader('')	// Actions
					),
					// Filter values
					$T.tr(
						this._createFilterValueElement('processed_datetime', 'Processed'),
						$T.th(),
						this._createFilterValueElement('correspondence_template_id', 'Template'),
						this._createFilterValueElement('created_employee_id', 'Created By'),
						$T.th(),
						$T.th(),
						this._createFilterValueElement('delivered_datetime', 'Dispatched'),
						this._createFilterValueElement('status', 'Status'),
						$T.th()
					)
				),
				$T.tbody({class: 'alternating'},
					this._createNoRecordsRow(true)
				)
			)
		);
		
		// Footer -- loading msg & pagination
		oSection.setFooterContent(
			$T.div(
				$T.span({class: 'loading'},
					'Loading...'
				),
				$T.div(
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
		);

		// Bind events to the pagination buttons
		var aBottomPageButtons 	= this._oContentDiv.select('div.section-footer button');
		aBottomPageButtons[0].observe('click', this._changePage.bind(this, 'firstPage'));
		aBottomPageButtons[1].observe('click', this._changePage.bind(this, 'previousPage'));
		aBottomPageButtons[2].observe('click', this._changePage.bind(this, 'nextPage'));
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

		// Load the initial dataset
		this._oSort.refreshData(true);
		this._oFilter.refreshData(true);
		this.oPagination.getCurrentPage();
	},

	_showLoading	: function(bShow)
	{
		var oLoading	= this._oContentDiv.select('span.loading').first();
		if (!oLoading)
		{
			return;
		}
		else if (bShow)
		{
			oLoading.show();
		}
		else
		{
			oLoading.hide();
		}
	},

	// _changePage: Executes the given function (name) on the dataset pagination object.
	_changePage	: function(sFunction)
	{
		this._showLoading(true);
		this.oPagination[sFunction]();
	},

	// _updateTable: Page load callback from the dataset pagination object.
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

		this._showLoading(false);
		if (this._oLoadingPopup)
		{
			this._oLoadingPopup.hide();
			delete this._oLoadingPopup;
		}
	},

	_createNoRecordsRow	: function(bOnLoad)
	{
		return $T.tr(
			$T.td({class: 'no-rows', colspan: 9},
				(bOnLoad ? 'Loading...' : 'There are no records to display')
			)
		);
	},

	_createTableRow	: function(oRun)
	{
		if (oRun.id !== null)
		{
			var oTDSource	= $T.td($T.span(oRun.source ? oRun.source : ''));
			if (oRun.import_file_name)
			{
				oTDSource.appendChild(
					$T.div({class: 'subscript'},
						oRun.import_file_name
					)
				)
			}

			// Build delivery (file export) summary
			var oDataFileTD	= Component_Correspondence_Run_Ledger._buildDataFileSummary(oRun);

			// Build items by delivery method cell
			var oEmailSummary	= oRun.delivery_method_breakdown.EMAIL;
			var oPostSummary	= oRun.delivery_method_breakdown.POST;
			
			var oItemsTD	=	$T.td({class: 'correspondence-items'},
									Component_Correspondence_Run_Ledger._buildDeliveryMethodSummary(oEmailSummary, 'Email', 'correspondence_email'),
									Component_Correspondence_Run_Ledger._buildDeliveryMethodSummary(oPostSummary, 'Post', 'lorry'),
									$T.div({class: 'correspondence-item-total'},
										$T.img({src: '../admin/img/template/sum.png', alt: 'Total', title: 'Total'}),
										$T.span(oRun.delivery_method_breakdown.TOTAL.count)
									)
								);
			
			var	oTR	=	$T.tr(
							$T.td(Component_Correspondence_Run_Ledger.getDateTimeElement(oRun.processed_datetime)),
							oTDSource,
							$T.td(
								$T.div(
									$T.div(oRun.correspondence_template_name),
									$T.div({class: 'subscript'},
										oRun.correspondence_template_code
									)
								)
							),
							$T.td(oRun.created_employee_name),
							oItemsTD,
							oDataFileTD,
							$T.td(Component_Correspondence_Run_Ledger.getDateTimeElement(oRun.delivered_datetime)),
							$T.td(Correspondence_Run_Status.getStatusFromCorrespondenceRun(oRun).name),
							$T.td(this._getRunActions(oRun))
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
			var oPageInfo		= this._oContentDiv.select('span.pagination-info').first();
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
		for (var sField in Component_Correspondence_Run_Ledger.SORT_FIELDS)
		{
			if (this._oSort.isRegistered(sField))
			{
				var oSortImg	= this._oContentDiv.select('th.header > img.sort-' + sField).first();
				var iDirection	= this._oSort.getSortDirection(sField);
				oSortImg.src	= Component_Correspondence_Run_Ledger.SORT_IMAGE_SOURCE[iDirection];
				oSortImg.show();
			}
		}
	},

	_updateFilters	: function()
	{
		for (var sField in Component_Correspondence_Run_Ledger.FILTER_FIELDS)
		{
			if (Component_Correspondence_Run_Ledger.FILTER_FIELDS[sField].iType)
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
			var oSpan	= this._oContentDiv.select('th.filter-heading > span.filter-' + sField).first();
			if (oSpan)
			{
				var oDeleteImage	= oSpan.up().select('img.filter-delete').first();
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
		var oDeleteImage				= $T.img({class: 'filter-delete', src: Component_Correspondence_Run_Ledger.REMOVE_FILTER_IMAGE_SOURCE, alt: 'Remove Filter', title: 'Remove Filter'});
		oDeleteImage.style.visibility	= 'hidden';
		oDeleteImage.observe('click', this._clearFilterValue.bind(this, sField));

		var oFiterImage	= $T.img({class: 'header-filter', src: Component_Correspondence_Run_Ledger.FILTER_IMAGE_SOURCE, alt: 'Filter by ' + sLabel, title: 'Filter by ' + sLabel});
		this._oFilter.registerFilterIcon(sField, oFiterImage, sLabel, null, 0, 10, 'correspondence-run-ledger-filterpopup');

		return	$T.th({class: 'filter-heading'},
					$T.span({class: 'filter-' + sField},
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
		var oSortImg	= $T.img({class: 'sort-' + (sSortField ? sSortField : '')});
		var oTH			= 	$T.th({class: 'header' + (bMultiLine ? '-multiline' : '')},
								oSortImg,
								$T.span(sLabel)
							);
		oSortImg.hide();

		// Optional sort field
		if (sSortField)
		{
			var oSpan	= oTH.select('span').first();
			oSpan.addClassName('header-sort');

			this._oSort.registerToggleElement(oSpan, sSortField, Component_Correspondence_Run_Ledger.SORT_FIELDS[sSortField]);
			this._oSort.registerToggleElement(oSortImg, sSortField, Component_Correspondence_Run_Ledger.SORT_FIELDS[sSortField]);
		}

		return oTH;
	},

	_getRunActions	: function(oRun)
	{
		var oUL		= $T.ul({class: 'reset horizontal actions'});
		var oView	= $T.img({class: 'pointer', src: Component_Correspondence_Run_Ledger.ACTION_VIEW_IMAGE_SOURCE, alt: 'View More Details', title: 'View More Details'});
		oView.observe('click', this._viewDetailsPopup.bind(this, oRun));
		oUL.appendChild($T.li(oView));
		return oUL;
	},

	_viewDetailsPopup	: function(oRun)
	{
		new Popup_Correspondence_Run(oRun.id);
	},

	_formatFilterValueForDisplay	: function(sField, mValue)
	{
		var oDefinition	= Component_Correspondence_Run_Ledger.FILTER_FIELDS[sField];
		var aControls	= this._oFilter.getControlsForField(sField);
		var sValue		= '';
		switch (sField)
		{
			case 'processed_datetime':
			case 'delivered_datetime':
				var oState		= this._oFilter.getFilterState(sField);
				var bGotFrom	= mValue.mFrom != null;
				var bGotTo		= mValue.mTo != null;
				var sFrom		= (bGotFrom ? Component_Correspondence_Run_Ledger.formatDateTimeFilterValue(mValue.mFrom)	: null);
				var sTo			= (bGotTo 	? Component_Correspondence_Run_Ledger.formatDateTimeFilterValue(mValue.mTo) 	: null);
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
			case 'correspondence_template_id':
			case 'created_employee_id':
			case 'status':
				// Control Field Select
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

// Static

Object.extend(Component_Correspondence_Run_Ledger,
{
	MAX_RECORDS_PER_PAGE		: 5,
	EDIT_IMAGE_SOURCE			: '../admin/img/template/pencil.png',
	FILTER_IMAGE_SOURCE			: '../admin/img/template/table_row_insert.png',
	REMOVE_FILTER_IMAGE_SOURCE	: '../admin/img/template/delete.png',
	ACTION_VIEW_IMAGE_SOURCE	: '../admin/img/template/magnifier.png',
	RANGE_FILTER_DATE_REGEX		: /^(\d{4}-\d{2}-\d{2})(\s\d{2}:\d{2}:\d{2})?$/,
	RANGE_FILTER_FROM_MINUTES	: '00:00:00',
	RANGE_FILTER_TO_MINUTES		: '23:59:59',
	DATA_SET_DEFINITION			: {sObject: 'Correspondence_Run', sMethod: 'getDataSet'},

	// Helper functions
	_validateDueDate	: function(sValue)
	{
		if (isNaN(Date.parse(sValue.replace(/-/g, '/'))))
		{
			return false;
		}
		else
		{
			return true;
		}
	},

	getDateTimeElement	: function(sMySQLDate)
	{
		if (sMySQLDate === null)
		{
			return $T.div('');
		}

		var oDate	= new Date(Date.parse(sMySQLDate.replace(/-/g, '/')));
		var sDate	= oDate.$format('d/m/Y');
		var sTime	= oDate.$format('h:i A');

		return 	$T.div(
					$T.div(sDate),
					$T.div({class: 'datetime-time'},
						sTime
					)
				);
	},

	formatDateTimeFilterValue	: function(sDateTime)
	{
		var oDate	= Date.$parseDate(sDateTime, 'Y-m-d H:i:s');
		return oDate.$format('j/m/y');
	},

	getPrePrintedOptions	: function(fnCallback)
	{
		fnCallback(
			[
	      	 	$T.option({value: 0},
	      	 		'No'
	      	 	),
	      	 	$T.option({value: 1},
	      	 		'Yes'
	      	 	)
	      	]
	    );
	},

	_buildDeliveryMethodSummary	: function(oSummary, sLabel)
	{
		var sIcon	= Correspondence_Delivery_Method.getIconForSystemName(sLabel.toUpperCase());
		return 	$T.div(
					$T.img({src: sIcon, alt: sLabel, title: sLabel}),
					$T.span(oSummary.count)
				);
	},
	
	_buildDataFileSummary	: function(oRun)
	{
		var oTD	= $T.td({class: 'data-file-name'});
		if (oRun.file_export_breakdown)
		{
			var oFileInfo	= null;
			for (var i = 0; i < oRun.file_export_breakdown.length; i++)
			{
				oFileInfo					= oRun.file_export_breakdown[i];
				var oDeliveryMethodsSpan	= $T.span({class: 'component-correspondence-run-ledger-dispatchsummary-file-carrier-method'});
				var sMethod					= null;
				for (var j in oFileInfo.delivery_methods)
				{
					if (isNaN(j))
					{
						break;
					}
					
					sMethod	= oFileInfo.delivery_methods[j];
					oDeliveryMethodsSpan.appendChild(
						$T.img({src: Correspondence_Delivery_Method.getIconForSystemName(sMethod), alt: sMethod, title: sMethod})
					);
				}
				
				oTD.appendChild(
					$T.div(
						$T.div({class: 'component-correspondence-run-ledger-carrier'},
							$T.span(oFileInfo.carrier),
							oDeliveryMethodsSpan
						),
						$T.div({class: 'subscript component-correspondence-run-ledger-carrier-summary'},
							$T.div(
								(oFileInfo.file !== null ? oFileInfo.file : 'No File Dispatched')
							)
						)
					)
				);
			}
		}
		return oTD;
	},
	
	//Sorting definitions
	SORT_FIELDS	:	
	{
		processed_datetime				: Sort.DIRECTION_DESC,
		correspondence_template_name	: Sort.DIRECTION_OFF,
		created_employee_name			: Sort.DIRECTION_OFF,
		count_correspondence			: Sort.DIRECTION_OFF,
		count_email						: Sort.DIRECTION_OFF,
		count_post						: Sort.DIRECTION_OFF,
		delivered_datetime				: Sort.DIRECTION_OFF
	},
});

Component_Correspondence_Run_Ledger.SORT_IMAGE_SOURCE						= {};
Component_Correspondence_Run_Ledger.SORT_IMAGE_SOURCE[Sort.DIRECTION_OFF]	= '../admin/img/template/order_neither.png';
Component_Correspondence_Run_Ledger.SORT_IMAGE_SOURCE[Sort.DIRECTION_ASC]	= '../admin/img/template/order_asc.png';
Component_Correspondence_Run_Ledger.SORT_IMAGE_SOURCE[Sort.DIRECTION_DESC]	= '../admin/img/template/order_desc.png';

// Filter Control field definitions
Component_Correspondence_Run_Ledger.YEAR_MINIMUM	= 2010;
Component_Correspondence_Run_Ledger.YEAR_MAXIMUM	= Component_Correspondence_Run_Ledger.YEAR_MINIMUM + 5;

var oNow	= new Date();
Component_Correspondence_Run_Ledger.FILTER_FIELDS	=
{
	correspondence_template_id	:
	{
		iType	: Filter.FILTER_TYPE_VALUE,
		oOption	:
		{
			sType		: 'select',
			mDefault	: null,
			oDefinition	:
			{
				sLabel		: 'Template',
				mEditable	: true,
				mMandatory	: false,
				fnValidate	: null,
				fnPopulate	: Correspondence_Template.getAllAsSelectOptions
			}
		}
	},
	processed_datetime	:
	{
		iType			: Filter.FILTER_TYPE_RANGE,
		bFrom			: true,
		sFrom			: 'Start Date',
		bTo				: true,
		sTo				: 'End Date',
		sFromOption		: 'On Or After',
		sToOption		: 'On Or Before',
		sBetweenOption	: 'Between',
		oOption			:
		{
			sType		: 	'date-picker',
			mDefault	:	null,
			oDefinition	:
			{
				sLabel		: 'Date',
				mEditable	: true,
				bTimePicker	: true,
				mMandatory	: false,
				fnValidate	: Component_Correspondence_Run_Ledger._validateDueDate,
				sDateFormat	: 'Y-m-d H:i:s',
				iYearStart	: Component_Correspondence_Run_Ledger.YEAR_MINIMUM,
				iYearEnd	: Component_Correspondence_Run_Ledger.YEAR_MAXIMUM
			}
		}
	},
	delivered_datetime	:
	{
		iType			: Filter.FILTER_TYPE_RANGE,
		bFrom			: true,
		sFrom			: 'Start Date',
		bTo				: true,
		sTo				: 'End Date',
		sFromOption		: 'On Or After',
		sToOption		: 'On Or Before',
		sBetweenOption	: 'Between',
		oOption			:
		{
			sType		: 	'date-picker',
			mDefault	:	null,
			oDefinition	:
			{
				sLabel		: 'Date',
				mEditable	: true,
				bTimePicker	: true,
				mMandatory	: false,
				fnValidate	: Component_Correspondence_Run_Ledger._validateDueDate,
				sDateFormat	: 'Y-m-d H:i:s',
				iYearStart	: Component_Correspondence_Run_Ledger.YEAR_MINIMUM,
				iYearEnd	: Component_Correspondence_Run_Ledger.YEAR_MAXIMUM
			}
		}
	},
	created_employee_id	:
	{
		iType	: Filter.FILTER_TYPE_VALUE,
		oOption	:
		{
			sType		: 'select',
			mDefault	: null,
			oDefinition	:
			{
				sLabel		: 'Created By',
				mEditable	: true,
				mMandatory	: false,
				fnValidate	: null,
				fnPopulate	: Employee.getAllAsSelectOptions.bind(Employee)
			}
		}
	},
	status	:
	{
		iType	: Filter.FILTER_TYPE_VALUE,
		oOption	:
		{
			sType		: 'select',
			mDefault	: null,
			oDefinition	:
			{
				sLabel		: 'Status',
				mEditable	: true,
				mMandatory	: false,
				fnValidate	: null,
				fnPopulate	: Correspondence_Run_Status.getAllAsSelectOptions
			}
		}
	}
};

