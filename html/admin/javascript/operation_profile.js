
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

Operation_Profile._oDataset	= new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_FULL_CACHING, {strObject: 'Operation_Profile', strMethod: 'getRecords'});

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
		this._oDataset.getRecords(
			Operation_Profile.getAll.bind(Operation_Profile, fCallback)
		);
	}
	else
	{
		// Pass Response to Callback
		fCallback(aResultSet);
	}
};

Operation_Profile.getAllChildOperations	= function(fnCallback, oResponse)
{
	if (typeof oResponse === 'undefined')
	{
		// Make Request
		var fnGetAll	= 	jQuery.json.jsonFunction(
								Operation_Profile.getAllChildOperations.curry(fnCallback),
								null,
								'Operation_Profile_Operation',
								'getAll'
							);
		fnGetAll();
	}
	else if (oResponse.Success)
	{
		// Got response
		if (fnCallback)
		{
			fnCallback(oResponse);
		}
	}
};

Operation_Profile.getAllIndexed	= function(fCallback, aResultSet)
{
	if (aResultSet === undefined)
	{
		// Make Request
		Operation_Profile.getAll(
			Operation_Profile.getAllIndexed.bind(Operation_Profile, fCallback)
		);
	}
	else
	{
		// Index this Result Set with the Ids
		var oResultSet	= {};
		var hDependants	= {};
		var oProfile	= null;
		
		for (iSequence in aResultSet)
		{
			oProfile				= aResultSet[iSequence];
			oProfile.aPrerequisites	= oProfile.aDependants;
			oResultSet[oProfile.id]	= oProfile;
			
			// Add to the list of dependants for each prerequisite
			for (var i = 0; i < oProfile.aPrerequisites.length; i++)
			{
				var iId	= oProfile.aPrerequisites[i];
				
				if (!hDependants[iId])
				{
					hDependants[iId]	= [];
				}
				
				hDependants[iId].push(oProfile.id);
			}
		}
		
		// Add dependants to each profile
		for (var iProfileId in oResultSet)
		{
			oResultSet[iProfileId].aDependants	= (hDependants[iProfileId] ? hDependants[iProfileId] : []);
		}
		
		// Pass to Callback
		fCallback(oResultSet);
	}
};
