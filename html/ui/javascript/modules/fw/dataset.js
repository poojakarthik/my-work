
var	Class = require('./class');
	
var self = new Class({
	implements : [require('./observable')],
	
	construct : function(iCacheMode, oSort, oFilter) {
		this._hSort		= null;
		this._hFilter	= null;
		
		this.useSort(oSort);
		this.useFilter(oFilter);
		
		// Caching Details
		this.setCacheMode(iCacheMode);
		this._hRecordCache		= null;
		this._iRecordCount		= null;
		this._iCacheTimeout		= self.CACHE_TIMEOUT_DEFAULT;
		this._iLastCachedOn		= null;
	},

	// Public
	
	useSort : function(oSort) {
		if (oSort) {
			oSort.observe('refresh', this.setSort.bind(this));
		}
	},
	
	useFilter : function(oFilter) {
		if (oFilter) {
			oFilter.observe('refresh', this.setFilter.bind(this));
		}
	},
	
	getRecords : function(iLimit, iOffset) {
		iLimit	= Math.max(0, (iLimit >= 0) ? iLimit : 0);
		iOffset	= Math.max(0, (iOffset >= 0) ? iOffset : 0);
		
		// Do we need to update the cache?
		var iTime = (new Date()).getTime();
		if (this._iCacheMode == self.CACHE_MODE_NO_CACHING) {
			// Cache is disabled, just get this range
			this._getRecordSet(this._recordsLoaded.bind(this, iLimit, iOffset), false, iLimit, iOffset);
		} else if (this._hRecordCache == null || !this._iLastCachedOn || (this._iCacheTimeout && this._iCacheTimeout > (iTime - this._iLastCachedOn))) {
			// Cache enabled but reload of full result set is necessary
			this._getRecordSet(this._recordsLoaded.bind(this, null, null), false, null, null);
		} else {
			// Cache enabled and reload not necessary, return cache subset
			this._recordsLoaded(iLimit, iOffset);
		}
	},	

	getRecordCount : function(fnCallback, bForceRefresh) {
		bForceRefresh = (bForceRefresh == undefined) ? false : bForceRefresh;
		if (bForceRefresh || (this._iRecordCount === null)) {
			this._getRecordSet(this._recordsCounted.bind(this, fnCallback), true, null, null);
		} else {
			fnCallback(this._iRecordCount);
		}
	},

	emptyCache : function() {
		this._hRecordCache	= null;
	},

	setCacheMode : function(iCacheMode) {
		switch (iCacheMode) {
			// LEGAL VALUES
			case self.CACHE_MODE_NO_CACHING:
				this.emptyCache();
				break;
				
			case self.CACHE_MODE_FULL_CACHING:
				break;
			
			// ILLEGAL VALUES
			case self.CACHE_MODE_PROGRESSIVE_CACHING:
				throw "Dataset Progressive Caching is not supported at this time.";
				break;
			
			default:
				throw "'" + iCacheMode + "' is not a valid Dataset Cache Mode";
				break;
		}
		this._iCacheMode = iCacheMode;
	},

	getCacheMode : function() {
		return this._iCacheMode;
	},

	setCacheTimeout : function(iCacheTimeout) {
		this._iCacheTimeout	= (iCacheTimeout > 0) ? Math.max(iCacheTimeout, self.CACHE_TIMEOUT_MINIMUM) * 1000 : null;
	},

	getCacheTimeout : function() {
		return this._iCacheTimeout;
	},

	setSort : function(oEvent) {
		this._hSort	= oEvent.getData();
	},

	setFilter : function(oEvent) {
		this._hFilter = oEvent.getData();
	},
	
	// Private
	
	_getRecordSet : function(fnCallback, bCountOnly, iLimit, iOffset) {
		throw "_getRecordSet must be re-implemented. _recordsLoaded to be invoked with the resulting record count and record set.";
	},
	
	_recordsLoaded : function(iLimit, iOffset, iRecordCount, hRecords) {
		// Only set the Cache if this is a cache-setting run
		if (typeof hRecords !== 'undefined') {
			this._setCache(hRecords, iRecordCount);
		}
		
		iLimit	= (iLimit > 0) ? iLimit : Object.keys(this._hRecordCache).length;
		iOffset	= Math.max(0, (iOffset >= 0) ? iOffset : 0);
		
		// Choose our results
		var oResultSet 	= {};
		var iNumResults	= 0;
		
		for (var i = iOffset, j = (iOffset + iLimit); i <= j; i++) {
			if (this._hRecordCache[i]) {
				oResultSet[i] = this._hRecordCache[i];
				iNumResults++;
			}
		}
		
		// Empty our cache if it's no longer needed
		if (this._iCacheMode == self.CACHE_MODE_NO_CACHING) {
			this.emptyCache();
		}
		
		try {
			this._loadComplete(oResultSet, iLimit, iOffset, iNumResults);
		} catch (oEx) {
			alert(oEx.message);
		}
	},

	_loadComplete : function(oResultSet, iLimit, iOffset, iNumResults) {
		// Fire an event with the results and count
		this.fire('load', {oResultSet: oResultSet, iRecordCount: this._iRecordCount, iNumResults: iNumResults});
	},
	
	_recordsCounted	: function(fnCallback, iRecordCount, hRecords) {
		this._iRecordCount = iRecordCount;
		fnCallback(iRecordCount);
	},
	
	_setCache : function(mRecords, iRecordCount) {
		var hRecords = {};
		if (mRecords) {
			if (Object.isArray(mRecords)) {
				// Convert to an Object
				for (var i = 0, j = mRecords.length; i < j; i++) {
					hRecords[i]	= mRecords[i];
				}
			} else {
				hRecords = mRecords;
			}
		}
		this._hRecordCache	= hRecords;
		this._iRecordCount	= iRecordCount;
		this._iLastCachedOn	= (new Date()).getTime();
	}
});

Object.extend(self, {
	// Cache mode
	CACHE_MODE_NO_CACHING			: 0,
	CACHE_MODE_FULL_CACHING			: 1,
	CACHE_MODE_PROGRESSIVE_CACHING	: 2,

	// Cache timeout
	CACHE_TIMEOUT_MINIMUM	: 0,
	CACHE_TIMEOUT_DEFAULT	: null
});

return self;
