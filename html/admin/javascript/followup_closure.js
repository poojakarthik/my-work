
var FollowUp_Closure	= Class.create
({
	initialize	: function(iId, fnCallback)
	{
		if (iId)
		{
			// Load via JSON
			this.iId	= iId;
			FollowUp_Closure.getAll(this._load.bind(this, fnCallback));
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
FollowUp_Closure._oDataset	= 	new Dataset_Ajax(
									Dataset_Ajax.CACHE_MODE_NO_CACHING, 
									{sObject: 'FollowUp_Closure', sMethod: 'getDataSet'}
								);

/* Static Methods */

FollowUp_Closure.getAll	= function(fnCallback, iRecordCount, aResultSet)
{
	if (iRecordCount === undefined || aResultSet === undefined)
	{
		// Make Request
		this._oDataset.setFilter(null);
		this._oDataset.getRecords(FollowUp_Closure.getAll.bind(FollowUp_Closure, fnCallback));
	}
	else
	{
		// Pass aResultSet to Callback
		fnCallback(aResultSet);
	}
};

FollowUp_Closure.getForClosureType	= function(iClosureType, fnCallback, iRecordCount, aResultSet)
{
	if (iRecordCount === undefined || aResultSet === undefined)
	{
		// Make Request
		this._oDataset.setFilter({followup_closure_type_id: iClosureType});
		this._oDataset.getRecords(FollowUp_Closure.getForClosureType.bind(FollowUp_Closure, iClosureType, fnCallback));
	}
	else
	{
		// Pass aResultSet to Callback
		fnCallback(aResultSet);
	}
};

FollowUp_Closure.getAllAsSelectOptions	= function(fnCallback, oClosures)
{
	if (!oClosures)
	{
		// Make Request
		FollowUp_Closure.getAll(FollowUp_Closure.getAllAsSelectOptions.bind(FollowUp_Closure, fnCallback));
	}
	else
	{
		// Create an Array of OPTION DOM Elements
		var aOptions	= [];
		for (i in oClosures)
		{
			aOptions.push(
				$T.option({value: oClosures[i].id},
						oClosures[i].name
				)
			);
		}
		
		// Pass to Callback
		fnCallback(aOptions);
	}
};

FollowUp_Closure.getForClosureTypeAsSelectOptions	= function(iClosureType, fnCallback, oClosures)
{
	if (!oClosures)
	{
		// Make Request
		FollowUp_Closure.getForClosureType(
			iClosureType,
			FollowUp_Closure.getForClosureTypeAsSelectOptions.bind(
				FollowUp_Closure, 
				iClosureType, 
				fnCallback
			)
		);
	}
	else
	{
		// Create an Array of OPTION DOM Elements
		var aOptions	= [];
		for (i in oClosures)
		{
			aOptions.push(
				$T.option({value: oClosures[i].id},
						oClosures[i].name
				)
			);
		}
		
		// Pass to Callback
		fnCallback(aOptions);
	}
};

FollowUp_Closure.getActiveForClosureType	= function(iClosureType, fnCallback, iRecordCount, aResultSet)
{
	if (iRecordCount === undefined || aResultSet === undefined)
	{
		var fnConstantsLoaded	= function(fnCallback)
		{
			// Set filter
			this._oDataset.setFilter(
				{
					followup_closure_type_id	: iClosureType,
					status_id					: $CONSTANT.STATUS_ACTIVE
				}
			);
			
			// Make Request
			this._oDataset.getRecords(
				FollowUp_Closure.getForClosureType.bind(
					FollowUp_Closure, 
					iClosureType, 
					fnCallback
				)
			);
		};
		
		// Load the 'status' constant group before proceeding
		Flex.Constant.loadConstantGroup('status', fnConstantsLoaded.bind(this, fnCallback));
	}
	else
	{
		// Pass aResultSet to Callback
		fnCallback(aResultSet);
	}
};

FollowUp_Closure.getActiveForClosureTypeAsSelectOptions	= function(iClosureType, fnCallback, oClosures)
{
	if (!oClosures)
	{
		// Make Request
		FollowUp_Closure.getActiveForClosureType(
			iClosureType,
			FollowUp_Closure.getActiveForClosureTypeAsSelectOptions.bind(
				FollowUp_Closure, 
				iClosureType, 
				fnCallback
			)
		);
	}
	else
	{
		// Create an Array of OPTION DOM Elements
		var aOptions	= [];
		for (i in oClosures)
		{
			aOptions.push(
				$T.option({value: oClosures[i].id},
						oClosures[i].name
				)
			);
		}
		
		// Pass to Callback
		fnCallback(aOptions);
	}
};
