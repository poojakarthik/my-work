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
		this._oDataset.getRecords(this.getAll.bind(this, fCallback));
	}
	else
	{
		// Pass Response to Callback
		fCallback(aResultSet);
	}
};

Operation.prepareForTreeGrid	= function(oOperations)
{
	oDependencyTree	= {};
	
	oOperations	= jQuery.json.arrayAsObject(oOperations);
	
	//Reflex_Debug.asHTMLPopup(oOperations);
	
	for (iOperationId in oOperations)
	{
		oOperations[iOperationId].aDependants	= [];
	}
	
	// Convert Child-Prerequisites to Parent-Dependants
	for (iOperationId in oOperations)
	{
		// Convert Child-Prerequisites to Parent-Dependants
		for (var i = 0; i < oOperations[iOperationId].aPrerequisites.length; i++)
		{
			if (!oOperations[oOperations[iOperationId].aPrerequisites[i]])
			{
				throw "Invalid prerequisite reference: "+oOperations[iOperationId].aPrerequisites[i];
			}
			
			// Add as a dependant
			oOperations[oOperations[iOperationId].aPrerequisites[i]].aDependants.push(iOperationId);
		}
		
		oOperations[iOperationId].aInstances	= [];
	}
	
	//Reflex_Debug.asHTMLPopup(oOperations);
	
	return oOperations;
};