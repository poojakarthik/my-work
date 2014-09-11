
var State	= Class.create
({
	initialize	: function(iId, fnCallback)
	{
		if (iId)
		{
			// Load via JSON
			this.iId	= iId;
			State.getAll(this._load.bind(this, fnCallback));
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
State._oDataset	= 	new Dataset_Ajax(
											Dataset_Ajax.CACHE_MODE_NO_CACHING, 
											{sObject: 'State', sMethod: 'getDataSet'}
										);

/* Static Methods */

State.getAll	= function(fnCallback, iRecordCount, aResultSet)
{
	if (iRecordCount === undefined || aResultSet === undefined)
	{
		// Make Request
		this._oDataset.getRecords(State.getAll.bind(State, fnCallback));
	}
	else
	{
		// Pass aResultSet to Callback
		fnCallback(aResultSet);
	}
};

State.getAllAsSelectOptions	= function(fnCallback, oClosures)
{
	if (!oClosures)
	{
		// Make Request
		State.getAll(State.getAllAsSelectOptions.bind(State, fnCallback));
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


