var Operation	= Class.create
({
	initialize	: function(iId, fCallback)
	{
		if (iId)
		{
			// Load via JSON
			this.iId	= iId;
			Operation._oDataset.getRecords(this._load.bind(this, fCallback));
		}
		else
		{
			// New Object -- this should never happen
			this.oProperties	= {};
		}
	},
	
	_load	: function(fCallback, iRecordCount, aResultSet)
	{
		// Set properties
		this.oProperties	= aResultSet[this.iId];
		
		// Callback
		if (fCallback)
		{
			fCallback(this);
		}
	}
});

Operation._oDataset	= new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_FULL_CACHING, {strObject: 'Operation', strMethod: 'getDataset'});

/* Static Methods */

Operation.getForId	= function(iId, fCallback)
{
	return new Operation(iId, fCallback);
}

Operation.getAll	= function(fCallback, iRecordCount, aResultSet)
{
	if (iRecordCount === undefined || aResultSet === undefined)
	{
		// Make Request
		this._oDataset.getRecords(Operation.getAll.bind(Operation, fCallback));
	}
	else
	{
		// Pass Response to Callback
		fCallback(aResultSet);
	}
};

Operation.getAllIndexed	= function(fCallback, aResultSet)
{
	if (aResultSet === undefined)
	{
		// Make Request
		Operation.getAll(Operation.getAllIndexed.bind(Operation, fCallback));
	}
	else
	{
		// Index this Result Set with the Ids
		var oResultSet	= {};
		for (iSequence in aResultSet)
		{
			oResultSet[aResultSet[iSequence].id]	= aResultSet[iSequence];
		}
		
		// Pass to Callback
		fCallback(oResultSet);
	}
};