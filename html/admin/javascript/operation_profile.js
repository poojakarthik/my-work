var Operation_Profile	= Class.create
({
	initialize	: function(iId, fCallback)
	{
		if (iId)
		{
			// Load via JSON
			this.iId	= iId;
			Operation_Profile._oDataset.getRecords(this._load.bind(this, fCallback));
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

Operation_Profile._oDataset	= new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_FULL_CACHING, {strObject: 'Operation_Profile', strMethod: 'getDataset'});

/* Static Methods */

Operation_Profile.getForId	= function(iId, fCallback)
{
	return new Operation_Profile(iId, fCallback);
}

Operation_Profile.getAll	= function(fCallback, iRecordCount, aResultSet)
{
	if (iRecordCount === undefined || aResultSet === undefined)
	{
		// Make Request
		this._oDataset.getRecords(Operation_Profile.getAll.bind(Operation_Profile, fCallback));
	}
	else
	{
		// Pass Response to Callback
		fCallback(aResultSet);
	}
};

Operation_Profile.getAllIndexed	= function(fCallback, aResultSet)
{
	if (aResultSet === undefined)
	{
		// Make Request
		Operation_Profile.getAll(Operation_Profile.getAllIndexed.bind(Operation_Profile, fCallback));
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
		//Reflex_Debug.asHTMLPopup(oResultSet);
		//Reflex_Debug.asHTMLPopup(aResultSet);
		fCallback(oResultSet);
	}
};