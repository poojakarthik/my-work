
var Contact_Title	= Class.create
({
	initialize	: function(iId, fnCallback)
	{
		if (iId)
		{
			// Load via JSON
			this.iId	= iId;
			Contact_Title.getAll(this._load.bind(this, fnCallback));
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
Contact_Title._oDataset	= 	new Dataset_Ajax(
											Dataset_Ajax.CACHE_MODE_NO_CACHING, 
											{sObject: 'Contact_Title', sMethod: 'getDataSet'}
										);

/* Static Methods */

Contact_Title.getAll	= function(fnCallback, iRecordCount, aResultSet)
{
	if (iRecordCount === undefined || aResultSet === undefined)
	{
		// Make Request
		this._oDataset.getRecords(Contact_Title.getAll.bind(Contact_Title, fnCallback));
	}
	else
	{
		// Pass aResultSet to Callback
		fnCallback(aResultSet);
	}
};

Contact_Title.getAllAsSelectOptions	= function(fnCallback, oClosures)
{
	if (!oClosures)
	{
		// Make Request
		Contact_Title.getAll(Contact_Title.getAllAsSelectOptions.bind(Contact_Title, fnCallback));
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


