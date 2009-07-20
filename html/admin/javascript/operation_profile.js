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
Operation_Profile._oDataset.setCacheTimeout(300);	// Recache timeout of 5 mins

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
		this._oDataset.getRecords(this.getAll.bind(this, fCallback));
	}
	else
	{
		// Pass Response to Callback
		fCallback(aResultSet);
	}
};

Operation_Profile.prepareForTreeGrid	= function(oOperationProfiles)
{
	oOperationProfiles	= jQuery.json.arrayAsObject(oOperationProfiles);
	
	//Reflex_Debug.asHTMLPopup(oOperationProfiles);
	
	for (iOperationProfileId in oOperationProfiles)
	{
		oOperationProfiles[iOperationProfileId].aInstances	= [];
	}
	
	//Reflex_Debug.asHTMLPopup(oOperationProfiles);
	
	return oOperationProfiles;
};