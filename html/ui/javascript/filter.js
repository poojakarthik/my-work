
/*
 * Filter
 * 
 * Controls filtering of given Dataset_Ajax and Pagination objects.
 * 
 * You can specify which field alias' to filter on and what type of value is expected (see the 
 * FILTER_TYPE class constants for more info).
 * 
*/
var Filter	= Class.create
({
	initialize	: function(oDataSetAjax, oPagination, fnFilterUpdateCallback, bForcePageCountRefreshOnFilterChange)
	{
		this._oDataSetAjax				= oDataSetAjax;
		this._oPagination				= oPagination;
		this._fnFilterUpdateCallback	= fnFilterUpdateCallback;
		this._hFilters					= {};
		this._hControls					= {};
		
		this._bForcePageCountRefreshOnFilterChange = true;
		if (!Object.isUndefined(bForcePageCountRefreshOnFilterChange) && (bForcePageCountRefreshOnFilterChange === false))
		{
			this._bForcePageCountRefreshOnFilterChange = false;
		}
	},
	
	// 
	// Public Methods
	//
	
	setFilterUpdateCallback : function(fnFilterUpdateCallback) 
	{
		this._fnFilterUpdateCallback = fnFilterUpdateCallback;
	},
	
	/*
	 * oDefinition structure
	 * 	- iType: 	FILTER_TYPE constant
	 *  - oOption: 	Control_Field.factory definition object
	 *  	- iType
	 *  	- oDefinition
	 *  		- sLabel
	 *  		- fnValidate
	 *  		- etc...
	 */
	addFilter	: function(sField, oDefinition)
	{
		this._hFilters[sField]	= {iType: oDefinition.iType};
		
		switch (oDefinition.iType)
		{
			case Filter.FILTER_TYPE_VALUE:
				this._hFilters[sField].mValue	= null;
				
				if (oDefinition.oOption)
				{
					this._hFilters[sField].oOptionsElement	= this._createOption(sField, oDefinition.oOption);
				}
				break;
			case Filter.FILTER_TYPE_SET:
				this._hFilters[sField].aValues	= [];
				
				if (oDefinition.oOption)
				{
					this._hFilters[sField].oOptionsElement	= this._createOption(sField, oDefinition.oOption);
				}
				break;
			case Filter.FILTER_TYPE_RANGE:
				if (oDefinition.oOption)
				{
					// Create the controls for the options (the labels are overwritten here to help with validation messages)
					oDefinition.oOption.oDefinition.sLabel	= oDefinition.sFrom;
					var oFromControlElement	= this._createOption(sField, oDefinition.oOption);
					
					oDefinition.oOption.oDefinition.sLabel	= oDefinition.sTo;
					var oToControlElement	= this._createOption(sField, oDefinition.oOption);
					
					// Create the options element
					this._hFilters[sField].oOptionsElement	= 	$T.div(
																	$T.div(
																		$T.select({class: 'filter-range-type-select'},
																			$T.option({value: Filter.RANGE_TYPE_FROM},
																				oDefinition.sFromOption
																			),
																			$T.option({value: Filter.RANGE_TYPE_TO},
																				oDefinition.sToOption
																			),
																			$T.option({value: Filter.RANGE_TYPE_BETWEEN},
																				oDefinition.sBetweenOption
																			)
																		)
																	),
																	$T.table({class: 'filter-overlay-options-content-range'},
																		$T.tbody(
																			$T.tr(
																				$T.td(
																					$T.span({class: 'filter-overlay-options-content-range-label'},
																						oDefinition.sFrom
																					)
																				),
																				$T.td(
																					oFromControlElement
																				)
																			),
																			$T.tr(
																				$T.td(
																					$T.span({class: 'filter-overlay-options-content-range-label'},
																						oDefinition.sTo
																					)
																				),
																				$T.td(
																					oToControlElement
																				)
																			)
																		)
																	)
																);
					
					// Attach events to the type select
					var oTypeSelect	= this._hFilters[sField].oOptionsElement.select('select.filter-range-type-select').first();
					oTypeSelect.observe('change', this._rangeTypeSelected.bind(this, oTypeSelect, sField));
					
					// Cache reference to the type select
					this._hFilters[sField].oRangeTypeSelect	= oTypeSelect;
					
					// Set default type
					this._rangeTypeSelected(oTypeSelect, sField);
				}
				break;
			case Filter.FILTER_TYPE_CONTAINS:
				this._hFilters[sField].sContains	= null;
				
				if (oDefinition.oOption)
				{
					this._hFilters[sField].oOptionsElement	= this._createOption(sField, oDefinition.oOption);
				}
				break;
			case Filter.FILTER_TYPE_ENDS_WITH:
				this._hFilters[sField].sEndsWith	= null;
				
				if (oDefinition.oOption)
				{
					this._hFilters[sField].oOptionsElement	= this._createOption(sField, oDefinition.oOption);
				}
				break;
			case Filter.FILTER_TYPE_STARTS_WITH:
				this._hFilters[sField].sStartsWith	= null;
				
				if (oDefinition.oOption)
				{
					this._hFilters[sField].oOptionsElement	= this._createOption(sField, oDefinition.oOption);
				}
				break;
		}
	},
	
	setFilterValue	: function(sField)
	{
		var oFilter				= this._hFilters[sField];
		var bClear				= arguments.length == 1;
		var bSetRawValueOnly	= false;
		switch (oFilter.iType)
		{
			case Filter.FILTER_TYPE_VALUE:
				// Get parameters
				var mValue			= (bClear ? null : arguments[1]);
				bSetRawValueOnly	= arguments[2];
				
				// Set value and update option control
				oFilter.mValue		= mValue;
				if (!bClear && !bSetRawValueOnly && this._hControls[sField])
				{
					this._hControls[sField][0].setValue(oFilter.mValue);
				}
				break;
			case Filter.FILTER_TYPE_SET:
				// Get parameters
				var aValues			= (bClear ? null : arguments[1]);
				bSetRawValueOnly	= arguments[2];
				
				// Set value and update option control
				oFilter.aValues		= aValues;
				if (!bClear && !bSetRawValueOnly && this._hControls[sField])
				{
					this._hControls[sField][0].setValue(oFilter.aValues);
				}
				break;
			case Filter.FILTER_TYPE_RANGE:
				// Get parameters
				var mFrom			= (bClear ? null : arguments[1]);
				var mTo				= (bClear ? null : arguments[2]);
				var mRangeTypeValue	= arguments[3];
				bSetRawValueOnly	= arguments[4];
				
				// Set value and update option controls
				oFilter.mFrom	= mFrom;
				oFilter.mTo		= mTo;
				if (!bClear && !bSetRawValueOnly && this._hControls[sField])
				{
					this._hControls[sField][0].setValue(oFilter.mFrom);
					this._hControls[sField][1].setValue(oFilter.mTo);
				}
				
				// Set the range definition value
				if (mRangeTypeValue && !bSetRawValueOnly && this._hControls[sField])
				{
					oFilter.oRangeTypeSelect.value	= mRangeTypeValue;
					this._rangeTypeSelected(oFilter.oRangeTypeSelect, sField);
				}
				break;
			case Filter.FILTER_TYPE_CONTAINS:
				// Get parameters
				var sContains		= (bClear ? null : arguments[1]);
				bSetRawValueOnly	= arguments[2];
				
				// Set value and update option control
				oFilter.sContains	= sContains;				
				if (!bClear && !bSetRawValueOnly && this._hControls[sField])
				{
					this._hControls[sField][0].setValue(oFilter.sContains);
				}
				break;
			case Filter.FILTER_TYPE_ENDS_WITH:
				// Get parameters
				var sEndsWith		= (bClear ? null : arguments[1]);
				bSetRawValueOnly	= arguments[2];
				
				// Set value and update control
				oFilter.sEndsWith	= sEndsWith;
				if (!bClear && !bSetRawValueOnly && this._hControls[sField])
				{
					this._hControls[sField][0].setValue(oFilter.sEndsWith);
				}
				break;
			case Filter.FILTER_TYPE_STARTS_WITH:
				// Get parameters
				var sStartsWith		= (bClear ? null : arguments[1]);
				bSetRawValueOnly	= arguments[2];
				
				// Set value and update control
				oFilter.sStartsWith	= sStartsWith;
				if (!bClear && !bSetRawValueOnly && this._hControls[sField])
				{
					this._hControls[sField][0].setValue(oFilter.sStartsWith);
				}
				break;
		}
		
		if (bClear && !bSetRawValueOnly)
		{
			// Clear the control field values
			var aControls	= this._hControls[sField];
			for (var i = 0; i < aControls.length; i++)
			{
				if (aControls[i].clearValue)
				{
					// Turn mandatory off then clear value and turn back on
					var bMandatory	= aControls[i].isMandatory();
					
					if (bMandatory)
					{
						aControls[i].setMandatory(false);
					}
					
					aControls[i].clearValue();
					
					if (bMandatory)
					{
						aControls[i].setMandatory(bMandatory);
					}
				}
				else
				{
					aControls[i].setElementValue(null);
				}
			}
		}
		
		if (this._fnFilterUpdateCallback && !bSetRawValueOnly)
		{
			this._fnFilterUpdateCallback(sField);
		}
	},
	
	clearFilterValue	: function(sField)
	{
		this.setFilterValue(sField);
	},
	
	getFilterValue	: function(sField)
	{
		var oFilter	= this._hFilters[sField];
		var mValue	= null;
		switch (oFilter.iType)
		{
			case Filter.FILTER_TYPE_VALUE:
				mValue	= oFilter.mValue;
				break;
			case Filter.FILTER_TYPE_SET:
				mValue	= oFilter.aValues;
				break;
			case Filter.FILTER_TYPE_RANGE:
				if (oFilter.mFrom != null || oFilter.mTo != null)
				{
					// Return both limits
					mValue	= {mFrom: oFilter.mFrom, mTo: oFilter.mTo};
				}
				else
				{
					mValue	= null;
				}
				break;
			case Filter.FILTER_TYPE_CONTAINS:
				mValue	= oFilter.sContains;
				break;
			case Filter.FILTER_TYPE_ENDS_WITH:
				mValue	= oFilter.sEndsWith;
				break;
			case Filter.FILTER_TYPE_STARTS_WITH:
				mValue	= oFilter.sStartsWith;
				break;
		}
		
		return mValue;
	},
	
	getFilterState	: function(sField)
	{
		return this._hFilters[sField];
	},
	
	getFilters: function()
	{
		return this.refreshData(true);
	},
	
	getFilterData : function()
	{
		var hFilters	= {};
		var oFilter		= null;
		for (var sField in this._hFilters)
		{
			oFilter	= this._hFilters[sField];
			
			switch (oFilter.iType)
			{
				case Filter.FILTER_TYPE_VALUE:
					if (oFilter.mValue !== null)
					{
						hFilters[sField]	= {};
						hFilters[sField]	= oFilter.mValue;
					}
					break;
				case Filter.FILTER_TYPE_SET:
					if (oFilter.aValues.length !== null)
					{
						hFilters[sField]				= {};
						hFilters[sField].aValues	= [];
						for (var i = 0; i < oFilter.aValues.length; i++)
						{
							hFilters[sField].aValues.push(oFilter.aValues[i]);
						}
					}
					break;
				case Filter.FILTER_TYPE_RANGE:
					if (oFilter.mFrom !== null || oFilter.mTo != null)
					{
						hFilters[sField]		= {};
						hFilters[sField].mFrom	= oFilter.mFrom;
						hFilters[sField].mTo	= oFilter.mTo;
					}
					break;
				case Filter.FILTER_TYPE_CONTAINS:
					if (oFilter.mValue !== null)
					{
						hFilters[sField]			= {};
						hFilters[sField].sContains	= oFilter.sContains;
					}
					break;
				case Filter.FILTER_TYPE_ENDS_WITH:
					if (oFilter.mValue !== null)
					{
						hFilters[sField]			= {};
						hFilters[sField].sEndsWith	= oFilter.sEndsWith;
					}
					break;
				case Filter.FILTER_TYPE_STARTS_WITH:
					if (oFilter.mValue !== null)
					{
						hFilters[sField]				= {};
						hFilters[sField].sStartsWith	= oFilter.sStartsWith;
					}
					break;
			}
		}
		
		return hFilters;
	},
	
	refreshData	: function(bCancelRefresh)
	{
		var hFilters = this.getFilterData();		
		this._oDataSetAjax.setFilter(hFilters);
		
		if (!bCancelRefresh && this._oPagination)
		{
			this._refreshCurrentPageOfData();
		}
		else
		{
			return hFilters;
		}
	},
	
	registerFilterIcon	: function(sField, oIcon, sLabel, oParentElement, iDisplayOffsetX, iDisplayOffsetY)
	{
		oIcon.observe('click', this._showFilterOptions.bind(this, sField, sLabel, oParentElement, iDisplayOffsetX, iDisplayOffsetY));
	},
	
	getControlsForField	: function(sField)
	{
		return this._hControls[sField];
	},
	
	isRegistered	: function(sField)
	{
		return (this._hFilters[sField] !== null && (typeof this._hFilters[sField] != 'undefined'));
	},
	
	//
	// Private methods
	//
	
	_refreshCurrentPageOfData	: function(iPageCount)
	{
		if (typeof iPageCount != 'undefined')
		{
			// Check current pages validity
			if (iPageCount < (this._oPagination.intCurrentPage + 1))
			{
				// Filter has changed the number of pages, go to the last
				this._oPagination.lastPage();
			}
			else
			{
				// Still valid, refresh
				this._oPagination.getCurrentPage();
			}
		}
		else
		{
			// Get the page count to see if the current page is still within the page limits
			this._oPagination.getPageCount(this._refreshCurrentPageOfData.bind(this), this._bForcePageCountRefreshOnFilterChange);
		}
	},
	
	_showFilterOptions	: function(sField, sLabel, oParentElement, iDisplayOffsetX, iDisplayOffsetY, oEvent)
	{
		if (!this._oFilterOptionsElement)
		{
			this._oFilterOptionsElement	= 	$T.div({class: 'filter-overlay-options'},
												$T.div({class: 'filter-overlay-options-content'}),
												$T.div({class: 'filter-overlay-options-buttons'},
													$T.button({class: 'icon-button'},
														$T.span('Use')
													),
													$T.button({class: 'icon-button'},
														$T.span('Cancel')
													)
												)
											);
			
			// Attach button events
			var oUseButton		= this._oFilterOptionsElement.select('button.icon-button').first();
			oUseButton.observe('click', this._useCurrentFilterOptions.bind(this));
			var oCancelButton	= this._oFilterOptionsElement.select('button.icon-button').last();
			oCancelButton.observe('click', this._hideFilterOptions.bind(this, null));
		}
		
		iDisplayOffsetY	= (iDisplayOffsetY ? iDisplayOffsetY : 0);
		iDisplayOffsetX	= (iDisplayOffsetX ? iDisplayOffsetX : 0);
		
		var oContent		= this._oFilterOptionsElement.select('div.filter-overlay-options-content').first();
		oContent.innerHTML	= '';
		
		if (oParentElement)
		{
			// Attach and position relative to the given element
			// Get the position
			var iValueT		= 0;
			var iValueL		= 0;
			var oElement	= oParentElement;
			do 
			{
				iValueT 	+= oElement.offsetTop || 0;
				iValueL 	+= oElement.offsetLeft || 0;
				oElement 	= oElement.offsetParent;
			} 
			while (oElement);
			
			// Get the scroll offset
			var oElement	= oParentElement;
			var iScrollT	= 0;
			var iScrollL	= 0;
			do 
			{
				iScrollL	+= oElement.scrollLeft || 0;
				iScrollT	+= oElement.scrollTop || 0;
				oElement 	= oElement.parentNode;
			}
			while (oElement);
			
			this._oFilterOptionsElement.style.left	= (oEvent.clientX - iValueL + iDisplayOffsetX + iScrollL) + 'px';
			this._oFilterOptionsElement.style.top	= (oEvent.clientY - iValueT + iDisplayOffsetY + iScrollT) + 'px';
			oParentElement.appendChild(this._oFilterOptionsElement);
		}
		else
		{
			// Attach and position relative to the document
			var oScrollOffsets 						= document.viewport.getScrollOffsets();
			this._oFilterOptionsElement.style.left	= (oEvent.clientX + iDisplayOffsetX + oScrollOffsets.left) + 'px';
			this._oFilterOptionsElement.style.top	= (oEvent.clientY + iDisplayOffsetY + oScrollOffsets.top) + 'px';
			document.body.appendChild(this._oFilterOptionsElement);
		}
		
		this._oFilterOptionsElement.sField	= sField;
		oContent.appendChild(this._hFilters[sField].oOptionsElement);
		
		// Make sure the element hasn't overflown the parent
		var iElementWidth			= this._oFilterOptionsElement.getWidth();
		var iElementLeftmostPixel 	= parseInt(this._oFilterOptionsElement.style.left, 10) + iElementWidth;
		var iParentWidth			= oParentElement.getWidth();
		if (iElementLeftmostPixel > iParentWidth) {
			this._oFilterOptionsElement.style.left = (iParentWidth - iElementWidth) + 'px';
		}
		
		this._oFilterOptionsElement.show();
		
		oEvent.stop();
	},
	
	_hideFilterOptions	: function(oEvent)
	{
		if (!this._oFilterOptionsElement)
		{
			// Cancel if the options element overlay hasn't been created
			return;
		}
		
		if (oEvent)
		{
			// From click event, check that the target of the event does not belong within the options overlay
			if (oEvent.explicitOriginalTarget != this._oFilterOptionsElement)
			{
				var oParent	= oEvent.explicitOriginalTarget.parentNode;
				while (oParent && (oParent != document.body))
				{
					if (oParent == this._oFilterOptionsElement)
					{
						return;
					}
					
					oParent	= oParent.parentNode;
				}
				
				this._oFilterOptionsElement.hide();
			}
		}
		else
		{
			// No event, called explicitly
			this._oFilterOptionsElement.hide();
		}
	},
	
	_createOption	: function(sField, oOptionDefinition)
	{
		// Create the control field and cache it, all are created mandatory, validation happens when 'use' is clicked
		var oControl	= Control_Field.factory(oOptionDefinition.sType, oOptionDefinition.oDefinition);
		oControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		
		if (!this._hControls[sField])
		{
			this._hControls[sField]	= [];
		}
		
		this._hControls[sField].push(oControl);
		return oControl.getElement();
	},
	
	_useCurrentFilterOptions	: function()
	{
		// Check for validation errors in the controls
		var sField		= this._oFilterOptionsElement.sField;
		var aControls	= this._hControls[sField];
		var aErrors		= [];
		for (var i = 0; i < aControls.length; i++)
		{
			try
			{
				aControls[i].validate(false);
			}
			catch (ex)
			{
				aErrors.push(ex);
			}
		}
		
		if (aErrors.length)
		{
			// Create a UL to list the errors and then show a reflex alert
			var oAlertDom	=	$T.div({class: 'filter-validation-errors'},
									$T.div('There were errors in the filter input: '),
									$T.ul(
										// Added here...
									)
								);
			var oUL	= oAlertDom.select('ul').first();
			
			for (var i = 0; i < aErrors.length; i++)
			{
				oUL.appendChild($T.li(aErrors[i]));
			}
			
			Reflex_Popup.alert(oAlertDom, {iWidth: 30});
			return;
		}
		
		var oFilter	= this._hFilters[sField];
		switch (oFilter.iType)
		{
			case Filter.FILTER_TYPE_RANGE:
				// Only use each range limit if the control that supplies the value is mandatory (determined by this._rangeTypeSelected)
				var mFrom	= (aControls[0].isMandatory() ? aControls[0].getElementValue() : null);
				var mTo		= (aControls[1].isMandatory() ? aControls[1].getElementValue() : null);
				this.setFilterValue(sField, mFrom, mTo);
				break;
			case Filter.FILTER_TYPE_VALUE:
			case Filter.FILTER_TYPE_SET:	
				// NOTE: There is no Control_Field that currently supports an array returned from getElementValue() so this will break.
			case Filter.FILTER_TYPE_CONTAINS:
			case Filter.FILTER_TYPE_ENDS_WITH:
			case Filter.FILTER_TYPE_STARTS_WITH:
				var mValue	= aControls[0].getElementValue();
				this.setFilterValue(sField, mValue);
				break;
		}
		
		this._hideFilterOptions();
		this.refreshData();
	},
	
	_rangeTypeSelected	: function(oSelect, sField)
	{
		var iRangeType	= parseInt(oSelect.value);
		var oFromSelect	= this._hControls[sField][0];
		var oToSelect	= this._hControls[sField][1];
		if (oFromSelect && oToSelect)
		{
			var oFromSelectParent	= oFromSelect.getElement().parentNode.parentNode;
			var oToSelectParent		= oToSelect.getElement().parentNode.parentNode;
			switch (iRangeType)
			{
				case Filter.RANGE_TYPE_FROM:
					oFromSelectParent.show();
					oFromSelect.setMandatory(true);
					oToSelectParent.hide();
					oToSelect.setMandatory(false);
					break;
				case Filter.RANGE_TYPE_TO:
					oFromSelectParent.hide();
					oFromSelect.setMandatory(false);
					oToSelectParent.show();
					oToSelect.setMandatory(true);
					break;
				case Filter.RANGE_TYPE_BETWEEN:
					oFromSelectParent.show();
					oFromSelect.setMandatory(true);
					oToSelectParent.show();
					oToSelect.setMandatory(true);
					break;
			}
		}
	}
});

Filter.FILTER_TYPE_VALUE		= 1;	// e.g. x = 1
Filter.FILTER_TYPE_SET			= 2;	// e.g. x IN (1,2,3)
Filter.FILTER_TYPE_RANGE		= 3;	// e.g. x BETWEEN 1 AND 3
Filter.FILTER_TYPE_CONTAINS		= 4;	// e.g. x LIKE '%1%'
Filter.FILTER_TYPE_ENDS_WITH	= 5;	// e.g. x LIKE '%1'
Filter.FILTER_TYPE_STARTS_WITH	= 6;	// e.g. x LIKE '1%'

Filter.RANGE_TYPE_FROM			= 1;
Filter.RANGE_TYPE_TO			= 2;
Filter.RANGE_TYPE_BETWEEN		= 3;
