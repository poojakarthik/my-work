
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
	initialize	: function(oDataSetAjax, oPagination, fnFilterUpdateCallback, fnOnUseCallback)
	{
		this._oDataSetAjax				= oDataSetAjax;
		this._oPagination				= oPagination;
		this._hFilters					= {};
		this._hControls					= {};
		this._fnFilterUpdateCallback	= fnFilterUpdateCallback;
		this._fnOnUseCallback			= fnOnUseCallback;
		document.body.observe('click', this._hideFilterOptions.bind(this));
	},
	
	// 
	// Public Methods
	//
	
	/*
	 * oDefinition structure
	 * 	- iType: 	FILTER_TYPE constant
	 *  - oOption: 	Control_Field.factory definition object
	 */
	addFilter	: function(sField, oDefinition)
	{
		this._hFilters[sField]	= {iType: oDefinition.iType};
		
		switch (oDefinition.iType)
		{
			case Filter.FILTER_TYPE_VALUE:
				this._hFilters[sField].mValue			= null;
				
				if (oDefinition.oOption)
				{
					this._hFilters[sField].oOptionsElement	= this._createOption(sField, oDefinition.oOption);
				}
				break;
			case Filter.FILTER_TYPE_SET:
				this._hFilters[sField].aValues			= [];
				
				if (oDefinition.oOption)
				{
					this._hFilters[sField].oOptionsElement	= this._createOption(sField, oDefinition.oOption);
				}
				break;
			case Filter.FILTER_TYPE_RANGE:
				if (oDefinition.oOption)
				{
					
					// Create the options element
					this._hFilters[sField].oOptionsElement	= 	$T.div(
																	$T.div(
																		$T.select({class: 'filter-range-type-select'},
																			$T.option({value: Filter.RANGE_TYPE_FROM},
																				oDefinition.sGreaterThan
																			),
																			$T.option({value: Filter.RANGE_TYPE_TO},
																				oDefinition.sLessThan
																			),
																			$T.option({value: Filter.RANGE_TYPE_BETWEEN},
																				oDefinition.sBetween
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
																					this._createOption(sField, oDefinition.oOption)
																				)
																			),
																			$T.tr(
																				$T.td(
																					$T.span({class: 'filter-overlay-options-content-range-label'},
																						oDefinition.sTo
																					)
																				),
																				$T.td(
																					this._createOption(sField, oDefinition.oOption)
																				)
																			)
																		)
																	)
																);
					
					// Attach events to the type select
					var oTypeSelect	= this._hFilters[sField].oOptionsElement.select('select.filter-range-type-select').first();
					oTypeSelect.observe('change', this._rangeTypeSelected.bind(this, oTypeSelect, sField));
					
					this._hFilters[sField].oRangeDefinition	= 	{
																	sGreaterThan	: oDefinition.sGreaterThan,
																	sLessThan		: oDefinition.sLessThan,
																	sBetween		: oDefinition.sBetween,
																	oTypeSelect		: oTypeSelect
																};
					
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
				this._hFilters[sField].sEndsWith		= null;
				
				if (oDefinition.oOption)
				{
					this._hFilters[sField].oOptionsElement	= this._createOption(sField, oDefinition.oOption);
				}
				break;
			case Filter.FILTER_TYPE_STARTS_WITH:
				this._hFilters[sField].sStartsWith		= null;
				
				if (oDefinition.oOption)
				{
					this._hFilters[sField].oOptionsElement	= this._createOption(sField, oDefinition.oOption);
				}
				break;
		}
	},
	
	clearFilterValue	: function(sField)
	{
		this.setFilterValue(sField);
	},
	
	setFilterValue	: function(sField)
	{
		var oFilter	= this._hFilters[sField];
		var bClear	= arguments.length == 1;
		switch (oFilter.iType)
		{
			case Filter.FILTER_TYPE_VALUE:
				oFilter.mValue	= (bClear ? null : arguments[1]);
				oFilter.sValue	= (bClear ? null : arguments[2]);
				break;
			case Filter.FILTER_TYPE_SET:
				oFilter.aValues		= (bClear ? null : arguments[1]);
				oFilter.aStrValues	= (bClear ? null : arguments[2]);
				break;
			case Filter.FILTER_TYPE_RANGE:
				oFilter.mFrom	= (bClear ? null : arguments[1]);
				oFilter.mTo		= (bClear ? null : arguments[2]);
				oFilter.sFrom	= (bClear ? null : arguments[3]);
				oFilter.sTo		= (bClear ? null : arguments[4]);
				
				// Set the option control field values
				if (!bClear)
				{
					this._hControls[sField][0].setValue(oFilter.mFrom);
					this._hControls[sField][1].setValue(oFilter.mTo);
				}
				
				if (arguments[5])
				{
					// Set the range definition value
					oFilter.oRangeDefinition.oTypeSelect.value	= arguments[5];
					this._rangeTypeSelected(oFilter.oRangeDefinition.oTypeSelect, sField);
				}
				break;
			case Filter.FILTER_TYPE_CONTAINS:
				oFilter.sContains	= (bClear ? null : arguments[1]);
				break;
			case Filter.FILTER_TYPE_ENDS_WITH:
				oFilter.sEndsWith	= (bClear ? null : arguments[1]);
				break;
			case Filter.FILTER_TYPE_STARTS_WITH:
				oFilter.sStartsWith	= (bClear ? null : arguments[1]);
				break;
		}
		
		if (bClear)
		{
			// Clear the control field values
			var aControls	= this._hControls[sField];
			
			for (var i = 0; i < aControls.length; i++)
			{
				if (aControls[i].clearValue)
				{
					aControls[i].clearValue();
				}
				else
				{
					aControls[i].setElementValue(null);
				}
			}
		}
		
		if (this._fnFilterUpdateCallback)
		{
			this._fnFilterUpdateCallback(sField);
		}
	},
	
	getFilterValue	: function(sField, bRaw)
	{
		var oFilter	= this._hFilters[sField];
		var mValue	= null;
		switch (oFilter.iType)
		{
			case Filter.FILTER_TYPE_VALUE:
				mValue	= (bRaw ? oFilter.mValue : oFilter.sValue);
				break;
			case Filter.FILTER_TYPE_SET:
				mValue	= (bRaw ? oFilter.aValues : oFilter.aStrValues.join(', '));
				break;
			case Filter.FILTER_TYPE_RANGE:
				if (oFilter.mFrom != null || oFilter.mTo != null)
				{
					if (bRaw)
					{
						// Return both limits
						mValue	= {mFrom: oFilter.mFrom, mTo: oFilter.mTo};
					}
					else
					{
						// Return range type specific string
						switch (parseInt(oFilter.oRangeDefinition.oTypeSelect.value))
						{
							case Filter.RANGE_TYPE_FROM:
								mValue	= oFilter.oRangeDefinition.sGreaterThan + ' ' + oFilter.sFrom;
								break;
							case Filter.RANGE_TYPE_TO:
								mValue	= oFilter.oRangeDefinition.sLessThan + ' ' + oFilter.sTo;
								break;
							case Filter.RANGE_TYPE_BETWEEN:
								mValue	= oFilter.oRangeDefinition.sBetween + ' ' + oFilter.sFrom + ' and ' + oFilter.sTo;
								break;
						}
					}
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
	
	refreshData	: function(bCancelRefresh)
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
		
		this._oDataSetAjax.setFilter(hFilters);
		
		if (!bCancelRefresh)
		{
			this._getCurrentPage();
		}
	},
	
	_getCurrentPage	: function(iPageCount)
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
			this._oPagination.getPageCount(this._getCurrentPage.bind(this), true);
		}
	},
	
	registerFilterIcon	: function(sField, oIcon, sLabel)
	{
		oIcon.observe('click', this._showFilterOptions.bind(this, sField, sLabel));
	},
	
	//
	// Private methods
	//
	
	_showFilterOptions	: function(sField, sLabel, oEvent)
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
			document.body.appendChild(this._oFilterOptionsElement);
			
			// Attach button events
			var oUseButton		= this._oFilterOptionsElement.select('button.icon-button').first();
			oUseButton.observe('click', this._useCurrentFilterOptions.bind(this));
			var oCancelButton	= this._oFilterOptionsElement.select('button.icon-button').last();
			oCancelButton.observe('click', this._hideFilterOptions.bind(this, null));
		}
		
		var oContent		= this._oFilterOptionsElement.select('div.filter-overlay-options-content').first();
		oContent.innerHTML	= '';
		
		this._oFilterOptionsElement.style.left	= oEvent.clientX + 'px';
		this._oFilterOptionsElement.style.top	= oEvent.clientY + 'px';
		this._oFilterOptionsElement.sField		= sField;
		
		oContent.appendChild(this._hFilters[sField].oOptionsElement);
		
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
		// Create the control field and cache it
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
		var sField		= this._oFilterOptionsElement.sField;
		var aControls	= this._hControls[sField];
		var oFilter		= this._hFilters[sField];
		
		// Check for validation errors in the controls
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
		
		switch (oFilter.iType)
		{
			case Filter.FILTER_TYPE_RANGE:
				var mFrom	= aControls[0].getElementValue();
				var sFrom	= (mFrom && aControls[0].getElementText ? aControls[0].getElementText() : mFrom);
				var mTo		= (aControls[1] ? aControls[1].getElementValue() : null);
				var sTo		= (mTo && aControls[1].getElementText ? aControls[1].getElementText() : mTo);
				this.setFilterValue(sField, mFrom, mTo, sFrom, sTo);
				break;
			case Filter.FILTER_TYPE_VALUE:
			case Filter.FILTER_TYPE_SET:	
				// NOTE: There is no Control_Field that currently supports an array returned from getElementValue() so this will break.
			case Filter.FILTER_TYPE_CONTAINS:
			case Filter.FILTER_TYPE_ENDS_WITH:
			case Filter.FILTER_TYPE_STARTS_WITH:
				var mValue	= aControls[0].getElementValue();
				var sValue	= (aControls[0].getElementText ? aControls[0].getElementText() : mValue);
				this.setFilterValue(sField, mValue, sValue);
				break;
		}
		
		this._hideFilterOptions();
		
		if (this._fnOnUseCallback)
		{
			this._fnOnUseCallback();
		}
		
		this.refreshData();
	},
	
	isRegistered	: function(sField)
	{
		return (this._hFilters[sField] !== null && (typeof this._hFilters[sField] != 'undefined'));
	},
	
	_rangeTypeSelected	: function(oSelect, sField)
	{
		var iRangeType	= parseInt(oSelect.value);
		var oFromSelect	= this._hControls[sField][0];
		var oToSelect	= this._hControls[sField][1];
		if (oFromSelect && oToSelect)
		{
			oFromSelect	= oFromSelect.getElement().parentNode.parentNode;
			oToSelect	= oToSelect.getElement().parentNode.parentNode;
			
			switch (iRangeType)
			{
				case Filter.RANGE_TYPE_FROM:
					oFromSelect.show();
					oToSelect.hide();
					break;
				case Filter.RANGE_TYPE_TO:
					oFromSelect.hide();
					oToSelect.show();
					break;
				case Filter.RANGE_TYPE_BETWEEN:
					oFromSelect.show();
					oToSelect.show();
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

Filter.RANGE_TYPE_FROM		= 1;
Filter.RANGE_TYPE_TO		= 2;
Filter.RANGE_TYPE_BETWEEN	= 3;
