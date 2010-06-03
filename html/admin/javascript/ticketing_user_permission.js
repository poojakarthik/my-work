var Ticketing_User_Permission	= Class.create
({
	initialize	: function(iId, fCallback)
	{
		if (iId)
		{
			// Load via JSON
			this.iId	= iId;
			Ticketing_User_Permission._oDataset.getRecords(this._load.bind(this, fCallback));
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

Ticketing_User_Permission._oDataset	=	new Dataset_Ajax(
											Dataset_Ajax.CACHE_MODE_NO_CACHING, 
											{sObject: 'Ticketing_User_Permission', sMethod: 'getDataset'}
										);

Ticketing_User_Permission.getForId	= function(iId, fCallback)
{
	return new Ticketing_User_Permission(iId, fCallback);
}

Ticketing_User_Permission.getAll	= function(fCallback, iRecordCount, aResultSet)
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

Ticketing_User_Permission.getAllAsSelectOptions	= function(fCallback, oResponse)
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