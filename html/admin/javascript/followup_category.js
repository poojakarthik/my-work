
var FollowUp_Category	= Class.create
({
	initialize	: function(iId, fnCallback)
	{
		if (iId)
		{
			// Load via JSON
			this.iId	= iId;
			FollowUp_Category.getAll(this._load.bind(this, fnCallback));
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
		
		var fnSave	= jQuery.json.jsonFunction(this._saveComplete.bind(this, fnCallback), this._saveComplete.bind(this), 'FollowUp_Category', 'save');
		fnSave(
			this.iId, 
			oDetails['name'], 
			oDetails['description'], 
			parseInt(oDetails['status_id']), 
			aOperationProfileIds, 
			aOperationIds 
		);
	},
	
	_saveComplete	: function(fnCallback, oResponse)
	{
		// Got response, hide loading
		this.oLoading.hide();
		delete this.oLoading;
		
		if (oResponse.Success)
		{
			// All good
			if (fnCallback)
			{
				fnCallback(oResponse);
			}
		}
		else
		{
			// AJAX Error
			if (oResponse.Message)
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
			
			for (sProperty in FollowUp_Category.oProperties)
			{
				oProperty							= FollowUp_Category.oProperties[sProperty];
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
		var oAlertDom	=	$T.div({class: 'followup-category-validation-errors'},
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

FollowUp_Category._oDataset	= 	new Dataset_Ajax(
									Dataset_Ajax.CACHE_MODE_NO_CACHING, 
									{sObject: 'FollowUp_Category', sMethod: 'getDataSet'}
								);

/* Static Methods */

FollowUp_Category.getForId	= function(iId, fnCallback)
{
	return new FollowUp_Category(iId, fnCallback);
}

FollowUp_Category.getAll	= function(fnCallback, iRecordCount, aResultSet)
{
	if (iRecordCount === undefined || aResultSet === undefined)
	{
		// Make Request
		this._oDataset.getRecords(
			FollowUp_Category.getAll.bind(FollowUp_Category, fnCallback)
		);
	}
	else
	{
		// Pass Response to Callback
		fnCallback(aResultSet);
	}
};

FollowUp_Category.getAllIndexed	= function(fnCallback, aResultSet)
{
	if (aResultSet === undefined)
	{
		// Make Request
		FollowUp_Category.getAll(FollowUp_Category.getAllIndexed.bind(FollowUp_Category, fnCallback));
	}
	else
	{
		// Pass Response to Callback
		var hResultSet	= {};
		for (var i in aResultSet)
		{
			hResultSet[aResultSet[i].id]	= aResultSet[i];
		}
		
		fnCallback(hResultSet);
	}
};

FollowUp_Category.getAllAsSelectOptions	= function(fnCallback, oResponse)
{
	if (!oResponse)
	{
		// Make Request
		this.getAll(this.getAllAsSelectOptions.bind(this, fnCallback));
	}
	else
	{
		// Create an Array of OPTION DOM Elements
		var aOptions	= [];
		for (i in oResponse)
		{
			aOptions.push(
				$T.option({value: oResponse[i].id},
					oResponse[i].name
				)
			);		
		}
		
		// Pass to Callback
		fnCallback(aOptions);
	}
};

FollowUp_Category.getActiveAsSelectOptions	= function(fnCallback, iRecordCount, aResultSet)
{
	if (!aResultSet)
	{
		var fnConstantsLoaded	= function(fnCallback)
		{
			// Set filter to return only active categories
			this._oDataset.setFilter({status_id: $CONSTANT.STATUS_ACTIVE});
			
			// Make request
			this._oDataset.getRecords(
				FollowUp_Category.getActiveAsSelectOptions.bind(FollowUp_Category, fnCallback)
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

// Controls definition
FollowUp_Category.oProperties	= {};

// Name
FollowUp_Category.oProperties.name			= {};
FollowUp_Category.oProperties.name.sType	= 'text';

FollowUp_Category.oProperties.name.oDefinition				= {};
FollowUp_Category.oProperties.name.oDefinition.sLabel		= 'Name';
FollowUp_Category.oProperties.name.oDefinition.mEditable	= true;
FollowUp_Category.oProperties.name.oDefinition.mMandatory	= true;
FollowUp_Category.oProperties.name.oDefinition.mAutoTrim	= false;
FollowUp_Category.oProperties.name.oDefinition.iMaxLength	= 128;

// Description
FollowUp_Category.oProperties.description		= {};
FollowUp_Category.oProperties.description.sType	= 'text';

FollowUp_Category.oProperties.description.oDefinition				= {};
FollowUp_Category.oProperties.description.oDefinition.sLabel		= 'Description';
FollowUp_Category.oProperties.description.oDefinition.mEditable		= true;
FollowUp_Category.oProperties.description.oDefinition.mMandatory	= true;
FollowUp_Category.oProperties.description.oDefinition.mAutoTrim		= false;
FollowUp_Category.oProperties.description.oDefinition.iMaxLength	= 256;
