
var Motorpass_Promotion_Code	= Class.create
({
	initialize	: function(iId, fnCallback)
	{
		if (iId)
		{
			// Load via JSON
			this.iId	= iId;
			Motorpass_Promotion_Code.getAll(this._load.bind(this, fnCallback));
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
Motorpass_Promotion_Code._oDataset	= 	new Dataset_Ajax(
											Dataset_Ajax.CACHE_MODE_NO_CACHING, 
											{sObject: 'Motorpass_Promotion_Code', sMethod: 'getDataSet'}
										);

/* Static Methods */

Motorpass_Promotion_Code.getAll	= function(fnCallback, iRecordCount, aResultSet)
{
	if (iRecordCount === undefined || aResultSet === undefined)
	{
		// Make Request
		this._oDataset.getRecords(Motorpass_Promotion_Code.getAll.bind(Motorpass_Promotion_Code, fnCallback));
	}
	else
	{
		// Pass aResultSet to Callback
		fnCallback(aResultSet);
	}
};

Motorpass_Promotion_Code.getAllAsSelectOptions	= function(fnCallback, oClosures)
{
	if (!oClosures)
	{
		// Make Request
		Motorpass_Promotion_Code.getAll(Motorpass_Promotion_Code.getAllAsSelectOptions.bind(Motorpass_Promotion_Code, fnCallback));
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


