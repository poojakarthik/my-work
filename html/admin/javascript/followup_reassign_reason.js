
var FollowUp_Reassign_Reason	= Class.create
({
	initialize	: function(iId, fnCallback)
	{
		if (iId)
		{
			// Load via JSON
			this.iId	= iId;
			FollowUp_Reassign_Reason.getAll(this._load.bind(this, fnCallback));
		}
		else
		{
			// New Object
			this.iId			= null;
			this.oProperties	= {};
		}
	},
	
	_load	: function(fnCallback, aResultSet)
	{
		// Set properties
		for (i in aResultSet)
		{
			if (aResultSet[i].id == this.Id)
			{
				this.oProperties	= aResultSet[i];
				break;
			}
		}
		
		// Callback
		if (fnCallback)
		{
			fnCallback(this);
		}
	}
});

/* Class members */
FollowUp_Reassign_Reason._oDataset	= 	new Dataset_Ajax(
											Dataset_Ajax.CACHE_MODE_NO_CACHING, 
											{sObject: 'FollowUp_Reassign_Reason', sMethod: 'getDataSet'}
										);

/* Static Methods */

FollowUp_Reassign_Reason.getAll	= function(fnCallback, iRecordCount, aResultSet)
{
	if (iRecordCount === undefined || aResultSet === undefined)
	{
		// Make Request
		this._oDataset.getRecords(FollowUp_Reassign_Reason.getAll.bind(FollowUp_Reassign_Reason, fnCallback));
	}
	else
	{
		// Pass aResultSet to Callback
		fnCallback(aResultSet);
	}
};

FollowUp_Reassign_Reason.getAllAsSelectOptions	= function(fnCallback, oResults)
{
	if (!oResults)
	{
		// Make Request
		FollowUp_Reassign_Reason.getAll(FollowUp_Reassign_Reason.getAllAsSelectOptions.bind(FollowUp_Reassign_Reason, fnCallback));
	}
	else
	{
		// Create an Array of OPTION DOM Elements
		var aOptions	= [];
		for (i in oResults)
		{
			aOptions.push(
				$T.option({value: oResults[i].id},
					oResults[i].name
				)
			);
		}
		
		// Pass to Callback
		fnCallback(aOptions);
	}
};
