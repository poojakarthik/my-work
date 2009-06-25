var User_Role	= Class.create
({
	initialize	: function(iId, fCallback)
	{
		if (iId)
		{
			// Load via JSON
			this.iId	= iId;
			User_Role._oDataset.getRecords(this._load.bind(this, fCallback));
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

User_Role._oDataset	= new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_FULL_CACHING, {strObject: 'User_Role', strMethod: 'getDataset'});

User_Role.getForId	= function(iId, fCallback)
{
	return new User_Role(iId, fCallback);
}

User_Role.getAll	= function(fCallback, iRecordCount, aResultSet)
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

User_Role.getAllAsSelectOptions	= function(fCallback, oResponse)
{
	if (!oResponse)
	{
		// Make Request
		alert("Making a Request for all 'User_Role's");
		this.getAll(this.getAllAsSelectOptions.bind(this, fCallback));
	}
	else
	{
		// Create an Array of SELECT DOM Elements
		alert("Creating Options");
		var aOptions	= [];
		for (i in oResponse)
		{
			var domOption	= document.createElement('option');
			domOption.value	= oResponse[i].id;
			domOption.text	= oResponse[i].name;
			aOptions.push(domOption);
		}
		
		// Pass to Callback
		fCallback(aOptions);
	}
};