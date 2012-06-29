
/*
 * filter
 * 
 * Controls filtering of given dataset and pagination objects.
 * 
 * You can specify which field alias' to filter on and what type of value is expected (see the 
 * TYPE class constants for more info).
 *  
 */

var	Class 	= require('../class'),
	Dataset = require('../dataset');
	
var self = new Class({
	implements : [require('../observable')],
	
	construct : function() {
		this._hFilters = {};
	},
	
	// Public
	
	addFilter : function(sField, iType) {
		this._hFilters[sField] = {iType: iType};
		switch (iType) {
			case self.TYPE_VALUE:
				this._hFilters[sField].mValue = null;
				break;
			case self.TYPE_SET:
				this._hFilters[sField].aValues = null;
				break;
			case self.TYPE_RANGE:
				this._hFilters[sField].mFrom 	= null;
				this._hFilters[sField].mTo		= null;
				break;
			case self.TYPE_CONTAINS:
				this._hFilters[sField].sContains = null;
				break;
			case self.TYPE_ENDS_WITH:
				this._hFilters[sField].sEndsWith = null;
				break;
			case self.TYPE_STARTS_WITH:
				this._hFilters[sField].sStartsWith = null;
				break;
		}
	},
	
	removeFilter : function(sField) {
		if (this._hFilters[sField]) {
			delete this._hFilters[sField];
		}
	},
	
	setFilterValue : function(sField) {
		var oFilter	= this._hFilters[sField];
		var bClear	= arguments.length == 1;
		switch (oFilter.iType) {
			case self.TYPE_VALUE:
				oFilter.mValue = (bClear ? null : arguments[1]);
				break;
			case self.TYPE_SET:
				oFilter.aValues	= (bClear ? null : arguments[1]);
				break;
			case self.TYPE_RANGE:
				oFilter.mFrom	= (bClear ? null : arguments[1]);
				oFilter.mTo		= (bClear ? null : arguments[2]);
				break;
			case self.TYPE_CONTAINS:
				oFilter.sContains = (bClear ? null : arguments[1]);
				break;
			case self.TYPE_ENDS_WITH:
				oFilter.sEndsWith = (bClear ? null : arguments[1]);
				break;
			case self.TYPE_STARTS_WITH:
				oFilter.sStartsWith	= (bClear ? null : arguments[1]);
				break;
		}
		this.fire('update');
	},
	
	clearFilterValue : function(sField) {
		this.setFilterValue(sField);
	},
	
	getFilterValue : function(sField) {
		var oFilter	= this._hFilters[sField];
		var mValue	= null;
		switch (oFilter.iType) {
			case self.TYPE_VALUE:
				mValue	= oFilter.mValue;
				break;
			case self.TYPE_SET:
				mValue	= oFilter.aValues;
				break;
			case self.TYPE_RANGE:
				if (oFilter.mFrom != null || oFilter.mTo != null) {
					// Return both limits
					mValue = {mFrom: oFilter.mFrom, mTo: oFilter.mTo};
				} else {
					mValue = null;
				}
				break;
			case self.TYPE_CONTAINS:
				mValue = oFilter.sContains;
				break;
			case self.TYPE_ENDS_WITH:
				mValue = oFilter.sEndsWith;
				break;
			case self.TYPE_STARTS_WITH:
				mValue = oFilter.sStartsWith;
				break;
		}
		return mValue;
	},
	
	getFilterState : function(sField) {
		return this._hFilters[sField];
	},
	
	getFilterType : function(sField) {
		return this._hFilters[sField] ? this._hFilters[sField].iType : null;
	},
	
	refreshData : function() {
		var hFilters	= {};
		var oFilter		= null;
		for (var sField in this._hFilters) {
			oFilter	= this._hFilters[sField];
			switch (oFilter.iType) {
				case self.TYPE_VALUE:
					if (oFilter.mValue !== null) {
						hFilters[sField]	= {};
						hFilters[sField]	= oFilter.mValue;
					}
					break;
				case self.TYPE_SET:
					if (oFilter.aValues !== null) {
						hFilters[sField]			= {};
						hFilters[sField].aValues	= [];
						for (var i = 0; i < oFilter.aValues.length; i++) {
							hFilters[sField].aValues.push(oFilter.aValues[i]);
						}
					}
					break;
				case self.TYPE_RANGE:
					if (oFilter.mFrom !== null || oFilter.mTo != null) {
						hFilters[sField]		= {};
						hFilters[sField].mFrom	= oFilter.mFrom;
						hFilters[sField].mTo	= oFilter.mTo;
					}
					break;
				case self.TYPE_CONTAINS:
					if (oFilter.mValue !== null) {
						hFilters[sField]			= {};
						hFilters[sField].sContains	= oFilter.sContains;
					}
					break;
				case self.TYPE_ENDS_WITH:
					if (oFilter.mValue !== null) {
						hFilters[sField]			= {};
						hFilters[sField].sEndsWith	= oFilter.sEndsWith;
					}
					break;
				case self.TYPE_STARTS_WITH:
					if (oFilter.mValue !== null) {
						hFilters[sField]				= {};
						hFilters[sField].sStartsWith	= oFilter.sStartsWith;
					}
					break;
			}
		}
		this.fire('refresh', hFilters);
	},
	
	isRegistered : function(sField) {
		return (this._hFilters[sField] !== null && (typeof this._hFilters[sField] != 'undefined'));
	},
	
	getFilterType : function(sField) {
		return (this._hFilters[sField] ? this._hFilters[sField].iType : null);
	}
});

Object.extend(self, {
	TYPE_VALUE			: 1,	// e.g. x = 1
	TYPE_SET			: 2,	// e.g. x IN (1,2,3)
	TYPE_RANGE			: 3,	// e.g. x BETWEEN 1 AND 3
	TYPE_CONTAINS		: 4,	// e.g. x LIKE '%1%'
	TYPE_ENDS_WITH		: 5,	// e.g. x LIKE '%1'
	TYPE_STARTS_WITH	: 6,	// e.g. x LIKE '1%'

	RANGE_TYPE_FROM		: 1,
	RANGE_TYPE_TO		: 2,
	RANGE_TYPE_BETWEEN	: 3
});

return self;
