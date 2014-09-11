var Dataset	= Class.create
({
	initialize	: function(fncRetrieveCallback)
	{
		this._fncRetrieveCallback			= null;
		this._strResultSetUniquenessHash	= null;
		
		// Caching Details
		this._intCacheMode			= 0;
		this._arrResultCache		= null;
		this._intResultCount		= null;
	},
	
	getRecordCount	: function(bolForceRefresh)
	{
		bolForceRefresh	= (bolForceRefresh == undefined) ? false : bolForceRefresh;
		
		if (bolForceRefresh)
		{
			switch (this.getCacheMode())
			{
				case Dataset.CACHE_MODE_NO_CACHING:
					break;
			}
		}
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
			case Dataset.CACHE_MODE_NO_CACHING:
				this.emptyCache();
				break;
				
			case Dataset.CACHE_MODE_FULL_CACHING:
				break;
			
			// ILLEGAL VALUES
			case Dataset.CACHE_MODE_PROGRESSIVE_CACHING:
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

Dataset.CACHE_MODE_NO_CACHING			= 0;
Dataset.CACHE_MODE_FULL_CACHING			= 1;
Dataset.CACHE_MODE_PROGRESSIVE_CACHING	= 2;