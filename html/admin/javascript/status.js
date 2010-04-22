var Status	= Class.create
({
	initialize	: function(iId, fCallback)
	{
		if (iId)
		{
			// Load via JSON
			this.iId	= iId;
			Status._oDataset.getRecords(this._load.bind(this, fCallback));
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

Status._oDataset	= new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, {strObject: 'Status', strMethod: 'getDataset'});

Status.getForId	= function(iId, fCallback)
{
	return new Status(iId, fCallback);
}

Status.getAll	= function(fCallback, iRecordCount, aResultSet)
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

Status.getAllAsSelectOptions	= function(fCallback, oResponse)
{
	if (!oResponse)
	{
		// Make Request
		this.getAll(this.getAllAsSelectOptions.bind(this, fCallback));
	}
	else
	{
		// Create an Array of SELECT DOM Elements
		var aOptions	= [];
		
		for (i in oResponse)
		{
			var oOption	= 	$T.option({value: oResponse[i].id},
								oResponse[i].name
							);
			aOptions.push(oOption);		
		}
			
		// Pass to Callback
		fCallback(aOptions);
	}
};