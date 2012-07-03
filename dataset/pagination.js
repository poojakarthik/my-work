
var	Class 	= require('../class'),
	Dataset = require('../dataset');
	
var self = new Class({
	implements : [require('../observable')],
	
	construct : function(iPageSize, oDataset) {
		this.iCurrentPage	= 0;
		this._iPageSize		= iPageSize;
		this._oDataset		= oDataset;
		oDataset.observe('load', this._datasetLoaded.bind(this));
	},
	
	// Public
	
	getCurrentPage : function() {
		this._oDataset.getRecords(this._iPageSize, this.iCurrentPage * this._iPageSize);
	},
	
	setDataset : function(oDataset) {
		if (oDataset instanceof Dataset) {
			this._oDataset = oDataset;
		} else {
			throw "oDataset is not a valid Dataset.";
		}
	},
	
	getDataset : function() {
		return this._oDataset;
	},

	nextPage : function() {
		this.jumpToPage(this.iCurrentPage + 1);
	},

	previousPage : function() {
		this.jumpToPage(Math.max(this.iCurrentPage - 1, 0));
	},

	firstPage : function() {
		this.jumpToPage(self.PAGE_FIRST);
	},

	lastPage : function(bForceRefresh) {
		this.jumpToPage(self.PAGE_LAST, bForceRefresh);
	},

	jumpToPage : function(iPageNumber, bForceRefresh) {
		bForceRefresh = (bForceRefresh == undefined) ? false : bForceRefresh;
		this.getPageCount(this._jumpToPage.bind(this, iPageNumber), bForceRefresh);
	},
	
	getPageCount : function(fnCallback, bForceRefresh) {
		bForceRefresh = (bForceRefresh == undefined) ? false : bForceRefresh;
		this._oDataset.getRecordCount(this._getPageCount.bind(this, fnCallback), bForceRefresh);
	},
	
	// Private
	
	_getPageCount : function(fnCallback, iRecordCount) {
		fnCallback(this._calculatePageCount(iRecordCount));
	},
	
	_calculatePageCount	: function(iRecordCount) {
		return Math.ceil(iRecordCount / this._iPageSize);
	},
	
	_jumpToPage	: function(iPageNumber, iPageCount) {
		if (iPageNumber == self.PAGE_LAST || iPageNumber >= iPageCount) {
			iPageNumber	= iPageCount - 1;
		}
		
		if (iPageNumber < self.PAGE_FIRST) {
			iPageNumber	= self.PAGE_FIRST;
		}
		
		this.iCurrentPage = iPageNumber;
		return this.getCurrentPage();
	},

	_datasetLoaded : function(oEvent) {
		var oResult		= oEvent.getData();
		var oResultSet	= {
			iTotalResults	: oResult.iRecordCount,
			oResultSet		: oResult.oResultSet,
			iCurrentPage	: this.iCurrentPage,
			iPageSize		: this._iPageSize,
			iPageCount		: this._calculatePageCount(oResult.iRecordCount)
		};
		
		this.fire('update', oResultSet);
	}
});

Object.extend(self, {
	PAGE_FIRST	: 0,
	PAGE_LAST	: -1
});

return self;
