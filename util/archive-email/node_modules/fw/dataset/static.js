
var	Class 	= require('../class'),
	Dataset = require('../dataset'),
	Sorter 	= require('../sorter'),
	Sort 	= require('./sort');
	
var self = new Class({
	extends : Dataset,
	
	construct : function(iCacheMode, fnGetRecords, oSort, oFilter) {
		this._super(iCacheMode, oSort, oFilter);
		this._fnGetRecords	= fnGetRecords;
		this._oSorter		= new Sorter();
	},
	
	_getRecordSet : function(fnCallback, bCountOnly, iLimit, iOffset) {
		if (this._fnGetRecords) {
			this._fnGetRecords(fnCallback, bCountOnly, iLimit, iOffset, this._hSort, this._hFilter);
		} else {
			fnCallback();
		}
	},
	
	_loadComplete : function(hResultSet, iLimit, iOffset) {
		this._sort(hResultSet, iLimit, iOffset, this._iRecordCount);
		this._filter(hResultSet, iLimit, iOffset, this._iRecordCount);
		this._super(hResultSet, iLimit, iOffset);
	},
	
	_sort : function(hRecords, iLimit, iOffset, iRecordCount) {
		// Build array of Sorter compatible field definitions
		var aFieldDefinitions	= [];
		for (var sField in this._hSort) {
			var sDirection	= this._hSort[sField];
			if (sDirection !== Sort.DIRECTION_OFF) {
				aFieldDefinitions.push({
					sField		: sField,
					fnCompare	: Sorter.stringGreaterThan,
					bReverse	: (sDirection === Sort.DIRECTION_DESC)
				});
			}
		}
		
		// Turn hash object into array
		var aRecords = [];
		for (var i in hRecords) {
			if (isNaN(i)) {
				continue;
			}
			
			aRecords.push(hRecords[i]);
		}
		
		this._oSorter.setFieldDefinitions(aFieldDefinitions);
		this._oSorter.sort(aRecords);
		
		// Turn array back into hash object
		iLimit			= (iLimit ? iLimit : iRecordCount);
		iOffset			= (iOffset ? iOffset : 0);
		var iEndOffset	= iOffset + iLimit;
		for (var i = 0; i < aRecords.length; i++) {
			hRecords[iOffset] = aRecords[i];
			iOffset++;
		}
	},
	
	_filter	: function(hRecords, iLimit, iOffset, iRecordCount) {
		iLimit			= (iLimit ? iLimit : iRecordCount);
		iOffset			= (iOffset ? iOffset : 0);
		var iEndOffset	= iOffset + iLimit;
		var oRecord		= null;
		var bRemove		= null;
		for (var i = iOffset; i < iEndOffset; i++) {
			oRecord	= hRecords[i];
			if (!oRecord) {
				continue;
			}
			
			bRemove	= false;
			for (var sField in this._hFilter) {
				var mFilter	= this._hFilter[sField];
				var mValue	= oRecord[sField];
				if (mFilter.aValues) {
					// Set
					var aValues	= (mFilter.aValues === null ? [] : mFilter.aValues);
					if ((aValues.length > 0) && (aValues.indexOf(mValue) == -1)) {
						bRemove	= true;
					}
				} else if (mFilter.mFrom || mFilter.mTo) {
					// Range
					if ((mFilter.mFrom !== null) && (mValue < mFilter.mFrom)) {
						bRemove	= true;
					}
					
					if ((mFilter.mTo !== null) && (mValue > mFilter.mTo)) {
						bRemove	= true;
					}
				} else if (!Object.isUndefined(mFilter.sContains)) {
					// Contains
					if (mFilter.sContains !== null) {
						var sContains	= mFilter.sContains.toString();
						var oRegExp		= new RegExp(sContains, 'i');
						if (!oRegExp.test(mValue.toString())) {
							bRemove	= true;
						}
					}
				} else if (!Object.isUndefined(mFilter.sEndsWith)) {
					// Ends with
					var sEndsWith	= (mFilter.sEndsWith === null ? '' : mFilter.sEndsWith.toString());
					var oRegExp		= new RegExp(sEndsWith + '$', 'i');
					if (!oRegExp.test(mValue.toString())) {
						bRemove	= true;
					}
				} else if (!Object.isUndefined(mFilter.sStartsWith)) {
					// Starts with
					var sStartsWith	= (mFilter.sStartsWith === null ? '' : mFilter.sStartsWith.toString());
					var oRegExp		= new RegExp('^' + sStartsWith, 'i');
					if (!oRegExp.test(mValue.toString())) {
						bRemove	= true;
					}
				} else {
					// Value
					if (mValue != mFilter) {
						bRemove	= true;
					}
				}
			}
			
			if (bRemove) {
				delete hRecords[i];
			}
		}
	}
});

return self;
