
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
		var aValidationErrors	= [];
		var oControl			= null;
		var sError				= null;
		var oDetails			= {};
		
		for (var sName in this.oPropertyControls)
		{
			oControl	= this.oPropertyControls[sName];
			sError		= Control_Field.getError(oControl);
			
			if (sError)
			{
				aValidationErrors.push(sError);
			}
			else
			{
				oDetails[sName]	= this.oPropertyControls[sName].getValue(true);
			}
		}
		
		// Return with errors if there were any, otherwise continue
		if (aValidationErrors.length)
		{
			this.showValidationErrors(aValidationErrors);
			return;
		}
		
		// Show loading
		this.oLoading	= new Reflex_Popup.Loading('Saving...');
		this.oLoading.display();
		
		var fnSave	= jQuery.json.jsonFunction(this._saveComplete.bind(this, fnCallback), this._saveComplete.bind(this), 'Operation_Profile', 'save');
		fnSave(
			this.iId, 
			oDetails['name'], 
			oDetails['description'], 
			parseInt(oDetails['status_id']), 
			aOperationProfileIds, 
			aOperationIds 
		);
	},
	
	_saveComplete : function(fnCallback, oResponse) {
		// Got response, hide loading
		this.oLoading.hide();
		delete this.oLoading;
		
		if (oResponse.Success) {
			// All good
			if (fnCallback) {
				fnCallback(oResponse);
			}
		} else {
			// AJAX Error
			jQuery.json.errorPopup(oResponse);
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
	},
	
	showValidationErrors	: function(aValidationErrors)
	{
		// Create a UL to list the errors and then show a reflex alert
		var oAlertDom	=	$T.div({class: 'operation-profile-validation-errors'},
								$T.div('There were errors in the profile information: '),
								$T.ul(
									// Added here...
								)
							);
		var oUL	= oAlertDom.select('ul').first();
		
		for (var i = 0; i < aValidationErrors.length; i++)
		{
			oUL.appendChild($T.li(aValidationErrors[i]));
		}
		
		Reflex_Popup.alert(oAlertDom, {iWidth: 30});
	}
});

Operation_Profile._oDataset	= 	new Dataset_Ajax(
									Dataset_Ajax.CACHE_MODE_NO_CACHING, 
									{sObject: 'Operation_Profile', sMethod: 'getActive'}
								);

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

Operation_Profile.getDependants	= function(oResultSet, iProfileId)
{
	var oProfile	= oResultSet[iProfileId];
	var aDependants	= [iProfileId.toString()];
	
	for (var i = 0; i < oProfile.aDependants.length; i++)
	{
		var iId		= oProfile.aDependants[i];
		aDependants	= aDependants.concat(Operation_Profile.getDependants(oResultSet, iId));
	}
	
	return aDependants;
};

Operation_Profile.getAllDependantsIndexed	= function(iLowestProfileId, fnCallback, oResultSet)
{
	if (typeof oResultSet === 'undefined')
	{
		// Make Request
		Operation_Profile.getAllIndexed(
			Operation_Profile.getAllDependantsIndexed.bind(Operation_Profile, iLowestProfileId, fnCallback)
		);
	}
	else
	{
		// Get all parent operations
		var aParents	= Operation_Profile.getDependants(oResultSet, iLowestProfileId);
		var oProfile	= null;
		var oParents	= {};
		
		for (var i = 0; i < aParents.length; i++)
		{
			iProfileId	= aParents[i];
			oProfile	= oResultSet[iProfileId];
			
			if (iProfileId != iLowestProfileId)
			{
				// Empty the prerequisites, their all treated as top level
				oProfile.aPrerequisites	= [];
				oParents[iProfileId]	= oProfile;
			}
		}
		
		fnCallback(oParents);
	}
}

Operation_Profile.getAllPrerequisitesAndNonRelatedIndexed	= function(iHighestProfileId, fnCallback, oResultSet)
{
	if (typeof oResultSet === 'undefined')
	{
		// Make Request
		Operation_Profile.getAllIndexed(
			Operation_Profile.getAllPrerequisitesAndNonRelatedIndexed.bind(Operation_Profile, iHighestProfileId, fnCallback)
		);
	}
	else
	{
		// Get all parent operations
		var aParents	= Operation_Profile.getDependants(oResultSet, iHighestProfileId);
		
		// Check all other profiles prerequisites, if they are parents of iHighestProfileId or 
		// iHighestProfileId itself, remove them
		var oProfile	= null;
		
		for (var iProfileId in oResultSet)
		{
			sProfileId	= iProfileId.toString();
			oProfile	= oResultSet[iProfileId];
			
			// Remove the profile if it is a dependant of iHighestProfileId
			if ((iProfileId == iHighestProfileId) || (aParents.indexOf(sProfileId) > -1))
			{
				delete oResultSet[iProfileId];
			}
			else
			{
				// Remove any prerequisites that are dependants of iHighestProfileId
				for (var i = 0; i < oProfile.aPrerequisites.length; i++)
				{
					var sPrerequisite	= oProfile.aPrerequisites[i].toString();
					
					if (aParents.indexOf(sPrerequisite) > 0)
					{
						oProfile.aPrerequisites.splice(i, 1);
					}
				}
				
				// Remove any dependants that are dependants of iHighestProfileId
				for (var i = 0; i < oProfile.aDependants.length; i++)
				{
					var sDependant	= oProfile.aDependants[i].toString();
					
					if (aParents.indexOf(sDependant) > -1)
					{
						oProfile.aDependants.splice(i, 1);
					}
				}
			}
		}
		
		fnCallback(oResultSet);
	}
}

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
		
		for (var iSequence in aResultSet)
		{
			oProfile				= aResultSet[iSequence];
			oProfile.aPrerequisites	= oProfile.aDependants;
			
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
			
			oResultSet[oProfile.id]	= oProfile;
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

// Controls definition
Operation_Profile.oProperties	= {};

// Name
Operation_Profile.oProperties.name			= {};
Operation_Profile.oProperties.name.sType	= 'text';

Operation_Profile.oProperties.name.oDefinition				= {};
Operation_Profile.oProperties.name.oDefinition.sLabel		= 'Name';
Operation_Profile.oProperties.name.oDefinition.mEditable	= true;
Operation_Profile.oProperties.name.oDefinition.mMandatory	= true;
Operation_Profile.oProperties.name.oDefinition.mAutoTrim	= false;
Operation_Profile.oProperties.name.oDefinition.iMaxLength	= 256;

// Description
Operation_Profile.oProperties.description		= {};
Operation_Profile.oProperties.description.sType	= 'text';

Operation_Profile.oProperties.description.oDefinition				= {};
Operation_Profile.oProperties.description.oDefinition.sLabel		= 'Description';
Operation_Profile.oProperties.description.oDefinition.mEditable		= true;
Operation_Profile.oProperties.description.oDefinition.mMandatory	= true;
Operation_Profile.oProperties.description.oDefinition.mAutoTrim		= false;
Operation_Profile.oProperties.description.oDefinition.iMaxLength	= 1024;

// Status
Operation_Profile.oProperties.status_id			= {};
Operation_Profile.oProperties.status_id.sType	= 'select';

Operation_Profile.oProperties.status_id.oDefinition				= {};
Operation_Profile.oProperties.status_id.oDefinition.sLabel		= 'Status';
Operation_Profile.oProperties.status_id.oDefinition.mEditable	= true;
Operation_Profile.oProperties.status_id.oDefinition.mMandatory	= true;
Operation_Profile.oProperties.status_id.oDefinition.fnPopulate	= Status.getAllAsSelectOptions.bind(Status);

