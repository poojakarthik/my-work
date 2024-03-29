var Pagination	= Class.create
({
	initialize	: function(fncUpdateCallback, intPageSize, objDataset, bolRefreshPageCountBeforeChangingPage)
	{
		this.intCurrentPage = 0;
		
		this._fncCallback	= fncUpdateCallback;
		this._intPageSize	= intPageSize;
		this._objDataset	= objDataset;
		
		this._bolRefreshPageCountBeforeChangingPage = true;
		if (!Object.isUndefined(bolRefreshPageCountBeforeChangingPage) && (bolRefreshPageCountBeforeChangingPage === false))
		{
			this._bolRefreshPageCountBeforeChangingPage	= false;
		}
	},
	
	setUpdateCallback : function(fnUpdateCallback) 
	{
		this._fncCallback = fnUpdateCallback;
	},
	
	getPageSize : function()
	{
		return this._intPageSize;
	},
	
	// getCurrentPage()
	getCurrentPage	: function()
	{
		this._objDataset.getRecords(this._getCurrentPage.bind(this), this._intPageSize, this.intCurrentPage * this._intPageSize);
	},
	
	_getCurrentPage	: function(intTotalResults, arrResultSet)
	{
		//alert("Page returned, sending to callback...");
		var objResultSet	=	{
									intTotalResults	: intTotalResults,
									arrResultSet	: arrResultSet,
									intCurrentPage	: this.intCurrentPage,
									intPageSize		: this._intPageSize,
									intPageCount	: this._calculatePageCount(intTotalResults)
								};
		this._fncCallback(objResultSet);
	},
	
	// getPageCount()
	getPageCount	: function(fncCallback, bolForceRefresh)
	{
		bolForceRefresh	= (bolForceRefresh == undefined) ? false : bolForceRefresh;
		this._objDataset.getRecordCount(this._getPageCount.bind(this, fncCallback), bolForceRefresh);
	},
	
	_getPageCount	: function(fncCallback, intRecordCount)
	{
		fncCallback(this._calculatePageCount(intRecordCount));
	},
	
	_calculatePageCount	: function(intRecordCount)
	{
		return Math.ceil(intRecordCount / this._intPageSize);
	},
	
	// nextPage()
	nextPage		: function()
	{
		this.jumpToPage(this.intCurrentPage + 1);
	},

	// previousPage()
	previousPage	: function()
	{
		this.jumpToPage(Math.max(this.intCurrentPage - 1, 0));
	},

	// firstPage()
	firstPage		: function()
	{
		this.jumpToPage(Pagination.PAGE_FIRST);
	},

	// lastPage()
	lastPage		: function(bForceRefresh)
	{
		this.jumpToPage(Pagination.PAGE_LAST, bForceRefresh);
	},

	// jumpToPage()
	jumpToPage		: function(intPageNumber, bolForceRefresh)
	{
		bolForceRefresh	= ((bolForceRefresh == undefined) ? false : bolForceRefresh) || this._bolRefreshPageCountBeforeChangingPage;
		this.getPageCount(this._jumpToPage.bind(this, intPageNumber), bolForceRefresh);
	},
	
	_jumpToPage	: function(intPageNumber, intPageCount)
	{
		if (intPageNumber == Pagination.PAGE_LAST || intPageNumber >= intPageCount)
		{
			intPageNumber	= intPageCount-1;
		}
		
		if (intPageNumber < Pagination.PAGE_FIRST)
		{
			intPageNumber	= Pagination.PAGE_FIRST;
		}
		
		this.intCurrentPage	= intPageNumber;
		return this.getCurrentPage();
	},
	
	// setDataset()
	setDataset	: function(objDataset)
	{
		if (objDataset instanceof Dataset)
		{
			this._objDataset	= objDataset;
		}
		else
		{
			throw "objDataset is not a valid Dataset";
		}
	},
	
	// getDataset()
	getDataset	: function()
	{
		return this._objDataset;
	}
});

// Class Constants
Pagination.PAGE_FIRST						= 0;
Pagination.PAGE_LAST						= -1;