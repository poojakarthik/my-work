
var FollowUp_Recurring_Modify_Reason	= Class.create
({
	initialize	: function(iId, fnCallback)
	{
		if (iId)
		{
			// Load via JSON
			this.iId	= iId;
			FollowUp_Recurring_Modify_Reason.getAll(this._load.bind(this, fnCallback));
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
FollowUp_Recurring_Modify_Reason._oDataset	= 	new Dataset_Ajax(
													Dataset_Ajax.CACHE_MODE_NO_CACHING, 
													{sObject: 'FollowUp_Recurring_Modify_Reason', sMethod: 'getDataSet'}
												);

/* Static Methods */

FollowUp_Recurring_Modify_Reason.getAll	= function(fnCallback, iRecordCount, aResultSet)
{
	if (iRecordCount === undefined || aResultSet === undefined)
	{
		// Make Request
		this._oDataset.getRecords(FollowUp_Recurring_Modify_Reason.getAll.bind(FollowUp_Recurring_Modify_Reason, fnCallback));
	}
	else
	{
		// Pass aResultSet to Callback
		fnCallback(aResultSet);
	}
};

FollowUp_Recurring_Modify_Reason.getAllAsSelectOptions	= function(fnCallback, oClosures)
{
	if (!oClosures)
	{
		// Make Request
		FollowUp_Recurring_Modify_Reason.getAll(
			FollowUp_Recurring_Modify_Reason.getAllAsSelectOptions.bind(FollowUp_Recurring_Modify_Reason,fnCallback)
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

FollowUp_Recurring_Modify_Reason.getActiveAsSelectOptions	= function(fnCallback, iRecordCount, aResultSet)
{
	if (!aResultSet)
	{
		var fnConstantsLoaded	= function(fnCallback)
		{
			// Set filter to return only active categories
			this._oDataset.setFilter({status_id: $CONSTANT.STATUS_ACTIVE});
			
			// Make request
			this._oDataset.getRecords(
				FollowUp_Recurring_Modify_Reason.getActiveAsSelectOptions.bind(FollowUp_Recurring_Modify_Reason, fnCallback)
			);
		};
		
		// Load the 'status' constant group before proceeding
		Flex.Constant.loadConstantGroup('status', fnConstantsLoaded.bind(this, fnCallback));
	}
	else
	{
		// Remove the filter
		this._oDataset.setFilter(null);
		
		// Create an Array of OPTION DOM Elements
		var aOptions	= [];
		for (i in aResultSet)
		{
			aOptions.push(
				$T.option({value: aResultSet[i].id},
					aResultSet[i].name
				)
			);
		}
		
		// Pass to Callback
		fnCallback(aOptions);
	}
};



