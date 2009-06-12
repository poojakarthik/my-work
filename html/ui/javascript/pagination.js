var Pagination	= Class.create
({
	initialize	: function(fncUpdateCallback, intPageSize, objDataset)
	{
		this._intCurrentPage	= 0;
		this._intPageSize		= intPageSize;
		
		this._objDataset		= objDataset;
		
		this._fncCallback		= fncUpdateCallback;
	},
	
	// getCurrentPage()
	getCurrentPage	: function()
	{
		this._objDataset.getRecords(this._getCurrentPage.bind(this), this._intPageSize, this._intCurrentPage * this._intPageSize);
	},
	
	_getCurrentPage	: function(intTotalResults, arrResultSet)
	{
		var objResultSet	=	{
									intTotalResults	: intTotalResults,
									arrResultSet	: arrResultSet,
									intCurrentPage	: this._intCurrentPage,
									intPageSize		: this._intPageSize,
									intPageCount	: this._calculatePageCount(intTotalResults)
								}
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
		this.jumpToPage(this._intCurrentPage++);
	},

	// previousPage()
	previousPage	: function()
	{
		this.jumpToPage(Math.min(this._intCurrentPage--, 0));
	},

	// firstPage()
	firstPage		: function()
	{
		this.jumpToPage(Pagination.PAGE_FIRST);
	},

	// lastPage()
	lastPage		: function()
	{
		this.jumpToPage(Pagination.PAGE_LAST);
	},

	// jumpToPage()
	jumpToPage		: function(intPageNumber, bolForceRefresh)
	{
		bolForceRefresh	= (bolForceRefresh == undefined) ? false : bolForceRefresh;
		this.getPageCount(this._jumpToPage.bind(this, intPageNumber), bolForceRefresh);
	},
	
	_jumpToPage	: function(intPageNumber, intPageCount)
	{
		if (intPageNumber == Pagination.PAGE_LAST || intPageNumber > intPageCount)
		{
			intPageNumber	= intPageCount;
		}
		
		this._intCurrentPage	= intPageNumber;
		return this.getCurrentPage();
	}
	
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