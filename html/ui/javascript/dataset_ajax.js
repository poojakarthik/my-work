
/**
	Dataset_Ajax
*/
var Dataset_Ajax = Class.create({
	
	initialize	: function(iCacheMode, oJSONDefinition) {
		this._oJSONDefinition			= oJSONDefinition;
		this._sRecordSetUniquenessHash	= null;
		this._hSort						= null;
		this._hFilter					= null;
		
		// Caching Details
		this.setCacheMode(iCacheMode);
		this._aRecordCache		= null;
		this._iRecordCount		= null;
		this._iCacheTimeout		= Dataset_Ajax.CACHE_TIMEOUT_DEFAULT;
		this._iLastCachedOn		= null;
	},
	
	getRecords	: function(fnCallback, iLimit, iOffset) {
		iLimit	= Math.max(0, (iLimit >= 0) ? iLimit : 0);
		iOffset	= Math.max(0, (iOffset >= 0) ? iOffset : 0);
		
		// Do we need to update the cache?
		var iTime = (new Date()).getTime();				
		switch (this._iCacheMode) {
			case Dataset_Ajax.CACHE_MODE_NO_CACHING:
				// Yes -- AJAX just this range
				break;
			case Dataset_Ajax.CACHE_MODE_FULL_CACHING:
				if (this._aRecordCache == null || 	// Cache is empty or...
					!this._iLastCachedOn || 		// Have not attempted to load any records into the cache or...
					(this._iCacheTimeout && this._iCacheTimeout > (iTime - this._iLastCachedOn))) {	// The whole cache has expired
					// Yes -- AJAX full result set
					iLimit 	= null;
					iOffset	= null;
				} else {
					// No -- already cached
					this._getRecords(fnCallback, iLimit, iOffset);
					return;
				}
				break;
			case Dataset_Ajax.CACHE_MODE_PROGRESSIVE_CACHING:
			case Dataset_Ajax.CACHE_MODE_SUBSET_CACHING:
				// Yes -- Modify the offset and limit so that only values within the allowed subset that aren't
				// already cached, are requested
				var iDataUntilOffset	= null;
				for (var i = iOffset; i < iOffset + iLimit; i++) {
					if (this._aRecordCache && this._aRecordCache[i]) {
						// Already have data for this offset
						iDataUntilOffset	= i;
					} else {
						// We've found an offset where there is no cached data. Stop searching.
						// Any breaks in the data (i.e. if there were records at a higher offset than this) 
						// should not occur and as such are not handled.
						break;
					}
				}
				
				// New offset is from where ever the cached data stops
				var iNewOffset = ((iDataUntilOffset === null) ? iOffset : (iDataUntilOffset + 1));
				
				// New limit is the difference between the new offset and the original set boundary (defined by iOffset + iLimit)
				var iNewLimit = (iOffset + iLimit) - iNewOffset;
				
				// Use modified offset & limit
				iLimit 	= iNewLimit;
				iOffset	= iNewOffset;
				break;
		}
		
		var bCountOnly	= false;
		var fnCallback 	= this._getRecords.bind(this, fnCallback, iLimit, iOffset);
		if (this._oJSONDefinition instanceof Reflex_AJAX_Request) {
			// Reflex_AJAX_Request instance
			var fnResp = this._reflexAJAXResponse.bind(this, fnCallback);
			
			// Stop observing 'succes' and 'error' events, they are re-observed each request
			this._oJSONDefinition.stopObserving('success');
			this._oJSONDefinition.stopObserving('error');
			
			// Renew observation of events
			this._oJSONDefinition.observe('success', fnResp);
			this._oJSONDefinition.observe('error', fnResp);
			
			// Send request
			this._oJSONDefinition.send(bCountOnly, iLimit, iOffset, this._hSort, this._hFilter);
		} else {
			// Object and method, use a jquery json function
			var fnResp = jQuery.json.handleResponse.curry(fnCallback);
			var fnReq = jQuery.json.jsonFunction(
				fnResp, 
				fnResp, 
				this._oJSONDefinition.sObject, 
				this._oJSONDefinition.sMethod
			);
			fnReq(bCountOnly, iLimit, iOffset, this._hSort, this._hFilter);
		}
	},
	
	// _reflexAJAXResponse: Special json request callback when Reflex_AJAX_Request is used to get the dataset. Handles exceptions.
	_reflexAJAXResponse : function(fnCallback, oResponse) {
		if (oResponse.hasException()) {
			Reflex_AJAX_Response.errorPopup(oResponse);
			return;
		}
		
		// Pass the response data onto the intended callback 
		fnCallback(oResponse.getData());
	},
	
	_getRecords	: function(fnCallback, iLimit, iOffset, oResponse) {
		if (oResponse) {
			// Update the cache
			var oRecords = {};
			if (Object.isArray(oResponse.aRecords)) {
				for (var i = 0, j = oResponse.aRecords.length; i < j; i++) {
					oRecords[i]	= oResponse.aRecords[i];
				}
			} else {
				oRecords = oResponse.aRecords;
			}
			
			switch (this._iCacheMode) {
				case Dataset_Ajax.CACHE_MODE_PROGRESSIVE_CACHING:
					// Import into the new cache, data from existing cache
					for (var i in this._aRecordCache) {
						oRecords[i]	= this._aRecordCache[i];
					}
					break;
				case Dataset_Ajax.CACHE_MODE_SUBSET_CACHING:
					// Import into the new cache, data from the old cache that fits within the allowed subset
					for (var i in this._aRecordCache) {
						var iTempOffset	= parseInt(i);
						if (iTempOffset < iOffset) {
							// Below the cache subset, not kept
						} else if (iTempOffset >= (iOffset + iLimit)) {
							// Above the cache subset, not kept
						} else {
							// Within the subset, kept
							oRecords[i]	= this._aRecordCache[i];
						}
					}
					break;
			}
			
			this._aRecordCache	= oRecords;
			this._iRecordCount	= oResponse.iRecordCount;
			this._iLastCachedOn	= (new Date()).getTime();
		}
		
		iLimit = (iLimit > 0) ? iLimit : Object.keys(this._aRecordCache).length;
		
		// Choose our results
		var aResultSet = {};
		for (var i = iOffset, j = (iOffset + iLimit); i <= j; i++) {
			if (this._aRecordCache[i]) {
				aResultSet[i]	= this._aRecordCache[i];
			}
		}
		
		// Empty our cache if it's no longer needed
		if (this._iCacheMode == Dataset_Ajax.CACHE_MODE_NO_CACHING) {
			this.emptyCache();
		}
		
		// "Return" the Results via callback
		fnCallback(this._iRecordCount, aResultSet);
	},
	
	getRecordCount	: function(fnCallback, bForceRefresh) {
		bForceRefresh = (bForceRefresh == undefined) ? false : bForceRefresh;
		if (bForceRefresh || (this._iRecordCount === null)) {
			var bCountOnly	= true;
			var iLimit		= null;
			var iOffset		= null;
			var fnCallback 	= this._getRecordCount.bind(this, fnCallback);
			if (this._oJSONDefinition instanceof Reflex_AJAX_Request) {
				// Reflex_AJAX_Request instance
				var fnResp = this._reflexAJAXResponse.bind(this, fnCallback);
				
				// Stop observing 'succes' and 'error' events, they are re-observed each request
				this._oJSONDefinition.stopObserving('success');
				this._oJSONDefinition.stopObserving('error');
				
				// Renew observation of events
				this._oJSONDefinition.observe('success', fnResp);
				this._oJSONDefinition.observe('error', fnResp);
				
				// Send request
				this._oJSONDefinition.send(bCountOnly, iLimit, iOffset, this._hSort, this._hFilter);
			} else {
				// Object and method, use a jquery json function
				var fnReq = jQuery.json.jsonFunction(
					jQuery.json.handleResponse.curry(fnCallback), 
					null, 
					this._oJSONDefinition.sObject, 
					this._oJSONDefinition.sMethod
				);
				fnReq(bCountOnly, iLimit, iOffset, this._hSort, this._hFilter);
			}
		} else {
			fnCallback(this._iRecordCount);
		}
	},
	
	_getRecordCount	: function(fnCallback, oResponse) {
		this._iRecordCount = oResponse.iRecordCount;
		fnCallback(this._iRecordCount);
	},
	
	emptyCache	: function() {
		this._aRecordCache = null;
	},
	
	setCacheMode	: function(iCacheMode) {
		switch (iCacheMode)
		{
			// LEGAL VALUES
			case Dataset_Ajax.CACHE_MODE_NO_CACHING:
				this.emptyCache();
				break;
				
			case Dataset_Ajax.CACHE_MODE_FULL_CACHING:
				break;
			
			case Dataset_Ajax.CACHE_MODE_PROGRESSIVE_CACHING:
				break;
			
			case Dataset_Ajax.CACHE_MODE_SUBSET_CACHING:
				break;
				
			// Illegal values
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
		this._iCacheTimeout = (iCacheTimeout > 0) ? Math.max(iCacheTimeout, Dataset_Ajax.CACHE_TIMEOUT_MINIMUM) * 1000 : null;
	},
	
	getCacheTimeout : function() {
		return this._iCacheTimeout;
	},
	
	setJSONDefinition : function(oJSONDefinition) {
		this._oJSONDefinition = oJSONDefinition;
	},
	
	setSortingFields : function(hFieldsToSort) {
		this._hSort = hFieldsToSort;
	},
	
	setFilter : function(hFilter) {
		this._hFilter = hFilter;
	}
});

Dataset_Ajax.CACHE_MODE_NO_CACHING			= 0;
Dataset_Ajax.CACHE_MODE_FULL_CACHING		= 1;
Dataset_Ajax.CACHE_MODE_PROGRESSIVE_CACHING	= 2;
Dataset_Ajax.CACHE_MODE_SUBSET_CACHING		= 3;

Dataset_Ajax.CACHE_TIMEOUT_MINIMUM			= 0;	// In Seconds TODO -- Raise this?
Dataset_Ajax.CACHE_TIMEOUT_DEFAULT			= null;	// In Seconds TODO -- Have a default?
