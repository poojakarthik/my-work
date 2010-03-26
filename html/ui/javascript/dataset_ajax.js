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
		this.setCacheMode(intCacheMode);
		this._arrRecordCache		= null;
		this._intRecordCount		= null;
		this._intCacheTimeout		= Dataset_Ajax.CACHE_TIMEOUT_DEFAULT;
		this._intLastCachedOn		= null;
	},
	
	getRecords	: function(fncCallback, intLimit, intOffset)
	{
		intLimit	= Math.max(0, (intLimit >= 0) ? intLimit : 0);
		intOffset	= Math.max(0, (intOffset >= 0) ? intOffset : 0);
		
		// Do we need to update the cache?
		var intTime		= (new Date()).getTime();
		var fncJsonFunc	= jQuery.json.jsonFunction(jQuery.json.handleResponse.curry(this._getRecords.bind(this, fncCallback, intLimit, intOffset)), null, this._objJSONDefinition.strObject, this._objJSONDefinition.strMethod);
		if (this._intCacheMode == Dataset_Ajax.CACHE_MODE_NO_CACHING)
		{
			// Yes -- AJAX just this range
			fncJsonFunc(false, intLimit, intOffset);
		}
		else if (this._arrRecordCache == null || !this._intLastCachedOn || (this._intCacheTimeout && this._intCacheTimeout > (intTime - this._intLastCachedOn)))
		{
			// Yes -- AJAX full result set
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
		if (objResponse)
		{
			//alert("Setting Cache...");
			this._setCache(objResponse);
		}
		
		intLimit	= (intLimit > 0) ? intLimit : Object.keys(this._arrRecordCache).length;
		//alert("Record Cache Length: " + this._arrRecordCache.length);
		//alert("Record Cache Keys Length: " + Object.keys(this._arrRecordCache).length);
		//alert("Offset: " + intOffset);
		//alert("Limit: " + intLimit);
		
		// Choose our results
		var arrResultSet	= {};
		for (var i = intOffset, j = (intOffset + intLimit); i <= j; i++)
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
		
		//alert("Dataset with " + Object.keys(arrResultSet).length + " records returned, sending to callback...");
		// "Return" the Results via callback
		fncCallback(this._intRecordCount, arrResultSet);
	},
	
	getRecordCount	: function(fncCallback, bolForceRefresh)
	{
		bolForceRefresh	= (bolForceRefresh == undefined) ? false : bolForceRefresh;
		
		if (bolForceRefresh || !this._intRecordCount)
		{
			var fncJsonFunc	= jQuery.json.jsonFunction(jQuery.json.handleResponse.curry(this._getRecordCount.bind(this, fncCallback)), null, this._objJSONDefinition.strObject, this._objJSONDefinition.strMethod);
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
		var oRecords	= {};
		if (Object.isArray(objResponse.arrRecords))
		{
			// Convert to an Object
			for (var i = 0, j = objResponse.arrRecords.length; i < j; i++)
			{
				oRecords[i]	= objResponse.arrRecords[i];
			}
		}
		else
		{
			oRecords	= objResponse.arrRecords;
		}
		
		//this._arrRecordCache	= (this.getCacheMode() == Dataset_Ajax.CACHE_MODE_NO_CACHING) ? null : objResponse.arrRecords;
		this._arrRecordCache	= oRecords;
		this._intRecordCount	= objResponse.intRecordCount;
		
		this._intLastCachedOn	= (new Date()).getTime();
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
				//alert("I am UNCACHED");
				this.emptyCache();
				break;
				
			case Dataset_Ajax.CACHE_MODE_FULL_CACHING:
				//alert("I am CACHED");
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
	},
	
	setCacheTimeout	: function(intCacheTimeout)
	{
		this._intCacheTimeout	= (intCacheTimeout > 0) ? Math.max(intCacheTimeout, Dataset_Ajax.CACHE_TIMEOUT_MINIMUM) * 1000 : null;
	},
	
	getCacheTimeout	: function()
	{
		return this._intCacheTimeout;
	}
});

Dataset_Ajax.CACHE_MODE_NO_CACHING			= 0;
Dataset_Ajax.CACHE_MODE_FULL_CACHING		= 1;
Dataset_Ajax.CACHE_MODE_PROGRESSIVE_CACHING	= 2;

Dataset_Ajax.CACHE_TIMEOUT_MINIMUM			= 0;	// In Seconds TODO -- Raise this?
Dataset_Ajax.CACHE_TIMEOUT_DEFAULT			= null;	// In Seconds TODO -- Have a default?
