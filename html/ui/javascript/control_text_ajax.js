
var Control_Text_AJAX = Class.create(Control, {
	initialize : function($super) {
		this.CONFIG = Object.extend({
			oDatasetAjax			: {}, 
			sDisplayValueProperty	: {}, 
			fnCreateResult			: {},
			oColumnProperties		: {}, 
			iResultLimit			: {
				fnGetter : function(mValue) {
					return (mValue ? mValue : 0);
				}
			}, 
			sResultPaneClass : {
				fnGetter : function(mValue) {
					return (mValue ? mValue : '');
				}
			}, 
			oFilter : {
				fnSetter : function() {
					throw "Cannot set oFilter, getter only.";
				},
				fnGetter : function() {
					return this._oFilter;
				}.bind(this)
			}
		}, this.CONFIG || {});
		$super.apply(this, $A(arguments).slice(1));
		this.NODE.addClassName('control-text-ajax');
	},
	
	_buildUI : function() {
		this.NODE = $T.div(
			this._oHidden = $T.input({type: 'hidden'}),
			this._oInput = $T.input({type: 'text'}),
			this._oView = $T.span()
		);
		
		this._oResultTable = $T.div({class: 'control-field-text-ajax-overlay'});
		
		var fnChange = this._valueChange.bind(this);
		this._oInput.observe('change', fnChange);
		this._oInput.observe('click', fnChange);
		this._oInput.observe('keyup', fnChange);
		this._oInput.observe('keydown', this._keyDown.bind(this));
		document.body.observe('click', this._checkForClickAway.bind(this));
	},
	
	_syncUI : function($super) {
		$super();
		this._oInput.name = this.get('sName');
		
		if (!this.get('oDatasetAjax')) {
			throw "No Dataset_AJAX object supplied";
		}
		
		// Create filter object to use with dataset ajax
		this._oFilter = new Filter(this.get('oDatasetAjax'));
		
		// Add filter field for the search term 
		this._oFilter.addFilter(Control_Text_AJAX.FILTER_FIELD_SEARCH_TERM, {iType: Filter.FILTER_TYPE_VALUE});
		
		this._sLastSearchTerm		= null;
		this._oHighlightedResult	= null;
		this._hDisplayValues		= {};
		this._hValues				= {};
		
		this.validate();
	},
	
	_setEnabled : function() {
		this._oInput.show();
		this._oInput.enable();
		this._oView.hide();
	},
	
	_setDisabled : function() {
		this._oInput.show();
		this._oInput.disable();
		this._oView.hide();
	},
	
	_setReadOnly : function() {
		this._oInput.hide();
		this._oView.show();
	},
	
	_setValue : function(mValue) {
		this._oHidden.value	= (mValue ? mValue : '');
		var mDisplayValue	= (this._hDisplayValues ? this._hDisplayValues[mValue] : null);
		if (mDisplayValue !== null && (typeof mDisplayValue != 'undefined')) {
			this._oInput.value 		= mDisplayValue;
			this._oView.innerHTML 	= mDisplayValue.toString().escapeHTML();
		} else {
			this._oInput.value = '';
			this._oView.innerHTML = '';
		}
	},
	
	_getValue : function() {
		var mValue = this._oHidden.value;
		if (this._hValues && this._hValues[mValue]) {
			return this._hValues[mValue];
		} else {
			return null;
		}
	},
	
	_clearValue : function() {
		this._setValue('');
	},
	
	_valueChange : function(oEvent) {
		this._search();
		this.validate();
		this.fire('change');
	},
	
	_search	: function() {
		var sSearchTerm	= this._oInput.value;
		if (sSearchTerm !== this._sLastSearchTerm) {
			// Search term has changed, the value is no longer valid
			this._oHidden.value = '';
			
			if (sSearchTerm !== '') {
				// There is a non-empty search term do a search
				this._sLastSearchTerm = sSearchTerm;
				this._oFilter.setFilterValue(Control_Text_AJAX.FILTER_FIELD_SEARCH_TERM, sSearchTerm);
				this._oFilter.refreshData();
				this.get('oDatasetAjax').getRecords(this._datasetLoaded.bind(this), this.get('iResultLimit'));	
			}
		}
	},
	
	_datasetLoaded	: function(iResultCount, hResults) {
		if ((iResultCount == 0) || this._mSelectedResultValue) {
			// 0 results, hide the results table
			this._hideResults();
			return;
		}
		
		// Clear display value cache & highlighted result ref
		this._hDisplayValues		= {};
		this._oHighlightedResult	= null;
		
		// Build table of results
		var oTBody	= $T.tbody();
		var oTable	= $T.table({class: 'control-field-text-ajax-result-table'},
			oTBody
		);
		for (var i in hResults) {
			// Add all of the configured properties as columns
			var oResult	= hResults[i];
			var oTR		= null;
			if (this.get('fnCreateResult')) {
				// Custom result creation function
				oTR = this.get('fnCreateResult')(oResult);
				oTR.addClassName('control-field-text-ajax-result-row');
			} else {
				// Use configured column properties
				oTR						= $T.tr({class: 'control-field-text-ajax-result-row'});
				var oColumnProperties 	= this.get('oColumnProperties');
				for (var sProperty in oColumnProperties) {
					var oColumn	= oColumnProperties[sProperty];
					var mValue	= (oResult[sProperty] ? oResult[sProperty] : '');
					var oTD 	= $T.td(mValue);					
					if (oColumn.sClass) {
						oTD.addClassName(oColumn.sClass);
					}
					oTR.appendChild(oTD);
				}
			}
			
			oTR.observe('mouseover', this._rowMouseOver.bind(this, oTR));
			oTR.iIndex = i;
			
			this._hValues[i]		= oResult;
			this._hDisplayValues[i]	= oResult[this.get('sDisplayValueProperty')];
			oTR.observe('click', this._resultClicked.bind(this, i));
			oTBody.appendChild(oTR);
		}
		
		// Show the table (in an overlay)
		this._oResultTable.innerHTML	= '';
		this._oResultTable.style.width	= (this._oInput.clientWidth + 2) + 'px';
		this._oResultTable.appendChild(oTable);
		this._showResults();
	},
	
	_resultClicked : function(i, oEvent) {
		this._sLastSearchTerm = this._hDisplayValues[i];
		this.setValue(i);
		this._hideResults();
		this.fire('select', oEvent);
	},
	
	_hideResults : function() {
		if (this._oResultTable.parentNode) {
			this._oResultTable.remove();
		}
		this._oHighlightedResult = null;
	},
	
	_showResults : function() {
		this.NODE.appendChild(this._oResultTable);
	},
	
	_keyDown : function(oEvent) {
		var bResultsVisible	= !!this._oResultTable.parentNode;
		if (bResultsVisible) {
			switch (oEvent.keyCode) {
				case 40:	// DOWN ARROW
					if (!this._oHighlightedResult) {
						this._rowMouseOver(this._oResultTable.select('tr').first());
					} else {
						this._rowMouseOver(this._oHighlightedResult.nextSibling ? this._oHighlightedResult.nextSibling : this._oHighlightedResult);
					}
					break;
				case 38:	// UP ARROW
					if (this._oHighlightedResult) {
						this._rowMouseOver(this._oHighlightedResult.previousSibling ? this._oHighlightedResult.previousSibling : this._oHighlightedResult);
					}
					break;
				case 13:	// ENTER
					if (this._oHighlightedResult) {
						this._resultClicked(this._oHighlightedResult.iIndex);
					}
					break;
				default:
					//alert('down: ' + oEvent.keyCode);
			}
		}
	},
	
	_checkForClickAway : function(oEvent) {
		// Check that the target of the click event does not belong within the results overlay
		if (oEvent.explicitOriginalTarget != this._oResultTable) {
			var oParent	= oEvent.explicitOriginalTarget.parentNode;
			while (oParent && (oParent != document.body)) {
				if (oParent == this._oResultTable) {
					return;
				}
				oParent	= oParent.parentNode;
			}
			this._hideResults();
		}
	},
	
	_rowMouseOver : function(oRow) {
		if (this._oHighlightedResult && (this._oHighlightedResult != oRow)) {
			this._oHighlightedResult.removeClassName('highlighted-row');
		}
		this._oHighlightedResult = oRow;
		this._oHighlightedResult.addClassName('highlighted-row')
	}
});

Object.extend(Control_Text_AJAX, {
	FILTER_FIELD_SEARCH_TERM : 'search_term',
});
