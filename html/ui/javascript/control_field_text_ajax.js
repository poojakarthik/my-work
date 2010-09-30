var Control_Field_Text_AJAX	= Class.create(/* extends */ Control_Field, 
{
	initialize	: function($super, sLabel, sLabelSeparator, oDatasetAjax, sValueProperty, sDisplayValueProperty, oColumnProperties, iResultLimit, sResultPaneClass)
	{
		// Parent
		$super(sLabel, sLabelSeparator);
		
		// Configuration
		this._oDatasetAjax			= oDatasetAjax;
		this._sValueProperty		= sValueProperty;
		this._sDisplayValueProperty	= (sDisplayValueProperty ? sDisplayValueProperty : sValueProperty);
		this._oColumnProperties		= oColumnProperties;
		this._iResultLimit			= (iResultLimit ? iResultLimit : 0);
		this._sResultPaneClass		= (sResultPaneClass ? sResultPaneClass : '');
		
		// Create filter object to use with dataset ajax
		this._oFilter	= new Filter(oDatasetAjax);
		
		// Add filter field for the search term 
		this._oFilter.addFilter(Control_Field_Text_AJAX.FILTER_FIELD_SEARCH_TERM, {iType: Filter.FILTER_TYPE_VALUE})
		
		// Create the DOM Elements
		this.oControlOutput.oEdit	= $T.input({type: 'text'})
		this.oControlOutput.oElement.appendChild(this.oControlOutput.oEdit);
		
		this.oControlOutput.oView	= $T.span();
		this.oControlOutput.oElement.appendChild(this.oControlOutput.oView);
		
		this._oTableContainer	= $T.div({class: 'control-field-text-ajax-overlay'});
		
		// Other properties
		this._aOnChangeCallbacks	= [];
		this._aOnSelectCallbacks	= [];
		this._sLastSearchTerm		= null;
		this._oHighlightedResult	= null;
		this._hDisplayValues		= {};
		
		// Events
		var fnValueChange	= this._valueChange.bind(this);
		this.oControlOutput.oEdit.observe('click', fnValueChange);
		this.oControlOutput.oEdit.observe('change', fnValueChange);
		this.oControlOutput.oEdit.observe('keyup', fnValueChange);
		this.oControlOutput.oEdit.observe('keydown', this._keyDown.bind(this));
		document.body.observe('click', this._checkForClickAway.bind(this));
		
		this.validate();
	},

	// Public
	
	getElementValue	: function()
	{
		return this.oControlOutput.oEdit.value;
	},
	
	setElementValue	: function(mValue)
	{
		this.oControlOutput.oEdit.value	= mValue;
	},
	
	updateElementValue	: function()
	{
		var mValue			= this.getValue();
		var mDisplayValue	= this._hDisplayValues[mValue];
		this.setElementValue(mDisplayValue);
		this.oControlOutput.oView.innerHTML	= mDisplayValue;
	},
	
	addOnChangeCallback	: function(fnCallback)
	{
		this._aOnChangeCallbacks.push(fnCallback);
	},
	
	addOnSelectCallback	: function(fnCallback)
	{
		this._aOnSelectCallbacks.push(fnCallback);
	},
	
	disableInput	: function()
	{
		if (this.bRenderMode == Control_Field.RENDER_MODE_EDIT)
		{
			this.oControlOutput.oEdit.disabled	= true;
		}
	},
	
	enableInput	: function()
	{
		if (this.bRenderMode == Control_Field.RENDER_MODE_EDIT)
		{
			this.oControlOutput.oEdit.removeAttribute('disabled');
		}
	},
	
	getFilter	: function()
	{
		return this._oFilter;
	},
	
	// Private
	
	_valueChange	: function()
	{
		this.validate();
		
		for (var i = 0; i < this._aOnChangeCallbacks.length; i++)
		{
			this._aOnChangeCallbacks[i]();
		}
		
		this._search();
	},
	
	_search	: function()
	{
		var sSearchTerm	= this.getElementValue();
		if ((sSearchTerm !== '') && (sSearchTerm !== this._sLastSearchTerm))
		{
			this._sLastSearchTerm	= sSearchTerm;
			this._oFilter.setFilterValue(Control_Field_Text_AJAX.FILTER_FIELD_SEARCH_TERM, sSearchTerm);
			this._oFilter.refreshData();
			this._oDatasetAjax.getRecords(this._datasetLoaded.bind(this), this._iResultLimit);
		}
	},
	
	_datasetLoaded	: function(iResultCount, hResults)
	{
		if ((iResultCount == 0) || this._mSelectedResultValue)
		{
			// 0 results, hide the results table
			this._hideResults();
			return;
		}
		
		// Clear display value cache
		this._hDisplayValues	= {};
		
		// Build table of results
		var oTBody	= 	$T.tbody();
		var oTable	= 	$T.table({class: 'control-field-text-ajax-result-table'},
							oTBody
						);
		for (var i in hResults)
		{
			// Add all of the configured properties as columns
			var oResult	= hResults[i];
			var oTR		= $T.tr({class: 'control-field-text-ajax-result-row'});
			oTR.observe('mouseover', this._rowMouseOver.bind(this, oTR));
			for (var sProperty in this._oColumnProperties)
			{
				var oColumn	= this._oColumnProperties[sProperty];
				var mValue	= (oResult[sProperty] ? oResult[sProperty] : '');
				var oTD		= 	$T.td({class: 'control-field-text-ajax-result-column'},
									mValue
								);
				if (oColumn.sClass)
				{
					oTD.addClassName(oColumn.sClass);
				}
				oTR.appendChild(oTD);
			}
			
			oTR.mValue							= oResult[this._sValueProperty];
			this._hDisplayValues[oTR.mValue]	= oResult[this._sDisplayValueProperty];
			oTR.observe('click', this._resultClicked.bind(this, oTR));
			oTBody.appendChild(oTR);
		}
		
		// Show the table (in an overlay)
		this._oTableContainer.innerHTML		= '';
		this._oTableContainer.style.width	= (this.oControlOutput.oEdit.clientWidth + 2) + 'px';
		this._oTableContainer.appendChild(oTable);
		this._showResults();
	},
	
	_resultClicked	: function(oTR, oEvent)
	{
		this._sLastSearchTerm	= this._hDisplayValues[oTR.mValue];
		
		this.setValue(oTR.mValue);
		
		this._hideResults();
		
		// Invoke on select callbacks
		for (var i = 0; i < this._aOnSelectCallbacks.length; i++)
		{
			this._aOnSelectCallbacks[i]();
		}
	},
	
	_hideResults	: function()
	{
		if (this._oTableContainer.parentNode)
		{
			this._oTableContainer.remove();
		}
		this._oHighlightedResult	= null;
	},
	
	_showResults	: function()
	{
		this.oControlOutput.oElement.appendChild(this._oTableContainer);
	},
	
	_checkForClickAway	: function(oEvent)
	{
		// Check that the target of the click event does not belong within the results overlay
		if (oEvent.explicitOriginalTarget != this._oTableContainer)
		{
			var oParent	= oEvent.explicitOriginalTarget.parentNode;
			while (oParent && (oParent != document.body))
			{
				if (oParent == this._oTableContainer)
				{
					return;
				}
				oParent	= oParent.parentNode;
			}
			this._hideResults();
		}
	},
	
	_rowMouseOver	: function(oRow)
	{
		if (this._oHighlightedResult && (this._oHighlightedResult != oRow))
		{
			this._oHighlightedResult.removeClassName('highlighted-row');
		}
		this._oHighlightedResult	= oRow;
		this._oHighlightedResult.addClassName('highlighted-row')
	},
	
	_keyDown	: function(oEvent)
	{
		var bResultsVisible	= !!this._oTableContainer.parentNode;
		if (bResultsVisible)
		{
			switch (oEvent.keyCode)
			{
				case 40:	// DOWN ARROW
					if (!this._oHighlightedResult)
					{
						this._rowMouseOver(this._oTableContainer.select('tr').first());
					}
					else
					{
						this._rowMouseOver(this._oHighlightedResult.nextSibling ? this._oHighlightedResult.nextSibling : this._oHighlightedResult);
					}
					break;
				case 38:	// UP ARROW
					if (this._oHighlightedResult)
					{
						this._rowMouseOver(this._oHighlightedResult.previousSibling ? this._oHighlightedResult.previousSibling : this._oHighlightedResult);
					}
					break;
				case 13:	// ENTER
					this._resultClicked(this._oHighlightedResult);
					break;
				default:
					//alert('down: ' + oEvent.keyCode);
			}
		}
	}
});

Object.extend(Control_Field_Text_AJAX, 
{
	FILTER_FIELD_SEARCH_TERM	: 'search_term',
});
