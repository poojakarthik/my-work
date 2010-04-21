
var Operation_Profile	= Class.create
({
	initialize	: function(iId, fCallback)
	{
		if (iId)
		{
			// Load via JSON
			this.iId	= iId;
			Operation_Profile.getAllIndexed(this._load.bind(this, fCallback));
		}
		else
		{
			// New Object
			this.iId			= null;
			this.oProperties	= {};
		}
	},
	
	_load	: function(fCallback, aResultSet)
	{
		// Set properties
		this.oProperties	= aResultSet[this.iId];
		
		// Callback
		if (fCallback)
		{
			fCallback(this);
		}
	},
	
	save	: function(aOperationProfileIds, aOperationIds, fnCallback)
	{
		var fnSave	= jQuery.json.jsonFunction(this._saveComplete.bind(this, fnCallback), this._saveComplete.bind(this), 'Operation_Profile', 'save');
		fnSave(this.iId, aOperationProfileIds, aOperationIds);
	},
	
	_saveComplete	: function(fnCallback, oResponse)
	{
		debugger;
		if (oResponse.Success)
		{
			// All good
			if (fnCallback)
			{
				fnCallback();
			}
		}
		else
		{
			// AJAX Error
			if (oReponse.Message)
			{
				Reflex_Popup.alert(oResponse.Message);
			}
			else
			{
				Reflex_Popup.alert('There was an error saving the profile.');
			}
		}
	},
	
	getControls	: function()
	{
		this._refreshControls();
		return this.oPropertyControls;
	},
	
	_refreshControls	: function()
	{
		if (!this.oPropertyControls)
		{
			// Create a control for each property
			this.oPropertyControls	= {};
			var oProperty			= null;
			
			for (sProperty in Operation_Profile.oProperties)
			{
				oProperty							= Operation_Profile.oProperties[sProperty];
				this.oPropertyControls[sProperty]	= Control_Field.factory(oProperty.sType, oProperty.oDefinition);
			}
		}
		
		for (sProperty in this.oPropertyControls)
		{
			if (Object.keys(this.oProperties).length)
			{
				this.oPropertyControls[sProperty].setValue(this.oProperties[sProperty]);
			}
			else
			{
				// FIXME: Default values instead?
				this.oPropertyControls[sProperty].setValue('');
			}
		}
	}
});

Operation_Profile._oDataset	= new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, {strObject: 'Operation_Profile', strMethod: 'getRecords'});

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
/*
Operation_Profile.oProperties	= {};

Operation_Profile.oProperties.LastName			= {};
Operation_Profile.oProperties.LastName.sType	= 'text';

Operation_Profile.oProperties.LastName.oDefinition				= {};
Operation_Profile.oProperties.LastName.oDefinition.sLabel		= 'Last Name';
Operation_Profile.oProperties.LastName.oDefinition.mEditable	= true;
Operation_Profile.oProperties.LastName.oDefinition.mMandatory	= true;
Operation_Profile.oProperties.LastName.oDefinition.mAutoTrim	= true;
Operation_Profile.oProperties.LastName.oDefinition.iMaxLength	= 256;


Operation_Profile.oProperties.LastName			= {};
Operation_Profile.oProperties.LastName.sType	= 'text';

Operation_Profile.oProperties.LastName.oDefinition				= {};
Operation_Profile.oProperties.LastName.oDefinition.sLabel		= 'Last Name';
Operation_Profile.oProperties.LastName.oDefinition.mEditable	= true;
Operation_Profile.oProperties.LastName.oDefinition.mMandatory	= true;
Operation_Profile.oProperties.LastName.oDefinition.mAutoTrim	= true;
Operation_Profile.oProperties.LastName.oDefinition.iMaxLength	= 1024;
*/

