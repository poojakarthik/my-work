/**
	Dataset_Ajax
*/
var Dataset_Ajax	= Class.create
({
	initialize	: function(intCacheMode, objJSONDefinition)
	{
		this._objJSONDefinition	= objJSONDefinition;
		
		this._strRecordSetUniquenessHash	= null;
		
		// Caching Details
		this._intCacheMode			= intCacheMode;
		this._arrRecordCache		= null;
		this._intRecordCount		= null;
	},
	
	getRecords	: function(fncCallback, intLimit, intOffset)
	{
		intLimit	= Math.max(0, intLimit);
		intOffset	= Math.max(0, intOffset);
		
		// Do we need to update the cache?
		if (this._intCacheMode == Dataset_Ajax.CACHE_MODE_NO_CACHING)
		{
			// Yes -- AJAX just this range
			var fncJsonFunc	= jQuery.json.jsonFunction(jQuery.json.handleResponse.curry(this._setCache.bind(this)), null, this._objJSONDefinition.strObject, this._objJSONDefinition.strMethod);
			fncJsonFunc(false, intLimit, intOffset);
		}
		else if (this._arrRecordCache == null)
		{
			// Yes -- AJAX full result set
			var fncJsonFunc	= jQuery.json.jsonFunction(jQuery.json.handleResponse.curry(this._setCache.bind(this)), null, this._objJSONDefinition.strObject, this._objJSONDefinition.strMethod);
			fncJsonFunc();
		}
		else
		{
			// No -- already cached
			this._getRecords(fncCallback, intLimit, intOffset);
		}
	},
	
	_getRecords	: function(fncCallback, intLimit, intOffset, objResponse)
	{
		alert("Setting Cache...");
		this._setCache(objResponse);
		
		// Choose our results
		var arrResultSet	= {};
		for (var i = intOffset, j = (intOffset + intLimit); i < j; i++)
		{
			if (this._arrRecordCache[i])
			{
				arrResultSet[i]	= this._arrRecordCache[i];
			}
		}
		
		// Empty our cache if it's no longer needed
		if (this._intCacheMode == Dataset_Ajax.CACHE_MODE_NO_CACHING)
		{
			this.emptyCache();
		}

		alert("Dataset returned, sending to Pagination callback...");
		// "Return" the Results via callback
		fncCallback(this._intRecordCount, arrResultSet);
	},
	
	getRecordCount	: function(fncCallback, bolForceRefresh)
	{
		bolForceRefresh	= (bolForceRefresh == undefined) ? false : bolForceRefresh;
		
		if (bolForceRefresh || !this._intRecordCount)
		{
			var fncJsonFunc	= jQuery.json.jsonFunction(jQuery.json.handleResponse.curry(this._getRecordCount.bind(this)), null, this._objJSONDefinition.strObject, this._objJSONDefinition.strMethod);
			fncJsonFunc(true);
		}
		else
		{
			fncCallback(this._intRecordCount);
		}
	},
	
	_getRecordCount	: function(fncCallback, objResponse)
	{
		this._intRecordCount	= objResponse.intRecordCount;
		fncCallback(this._intRecordCount);
	},
	
	_setCache	: function(objResponse)
	{
		this._arrRecordCache	= (this.getCacheMode() == Dataset_Ajax.CACHE_MODE_NO_CACHING) ? null : objResponse.arrRecords;
		this._intRecordCount	= objResponse.intRecordCount;
	},
	
	emptyCache	: function()
	{
		this._arrCache	= null;
	},
	
	setCacheMode	: function(intCacheMode)
	{
		switch (intCacheMode)
		{
			// LEGAL VALUES
			case Dataset_Ajax.CACHE_MODE_NO_CACHING:
				this.emptyCache();
				break;
				
			case Dataset_Ajax.CACHE_MODE_FULL_CACHING:
				break;
			
			// ILLEGAL VALUES
			case Dataset_Ajax.CACHE_MODE_PROGRESSIVE_CACHING:
				throw "Dataset Progressive Caching is not supported at this time.";
				break;
			
			default:
				throw "'" + intCacheMode + "' is not a valid Dataset Cache Mode";
				break;
		}
		this._intCacheMode	= intCacheMode;
	},
	
	getCacheMode	: function()
	{
		return this._intCacheMode;
	}
});

Dataset_Ajax.CACHE_MODE_NO_CACHING			= 0;
Dataset_Ajax.CACHE_MODE_FULL_CACHING			= 1;
Dataset_Ajax.CACHE_MODE_PROGRESSIVE_CACHING	= 2;