var Correspondence_Template	= Class.create
({
	// NO instance functionality
});

// Static
Object.extend(Correspondence_Template, 
{
	getAllWithNonSystemSourcesAsSelectOptions	: function(fnCallback, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Make Request
			var fnGetTemplates	=	jQuery.json.jsonFunction(
										Correspondence_Template.getAllWithNonSystemSourcesAsSelectOptions.curry(fnCallback),
										Correspondence_Template._ajaxError,
										'Correspondence_Template',
										'getAllWithNonSystemSources'
									);
			fnGetTemplates();
		}
		else
		{
			// Build array of option elements
			var aOptions = [];
			if (!oResponse.bSuccess)
			{
				// Failed
				Correspondence_Template._ajaxError(oResponse);
			}
			else
			{
				// Success
				aOptions = Object.keys(oResponse.aCorrespondenceTemplates).filter(function (id) {
					return !isNaN(id);
				}).sort(function (id1, id2) {
					// Sort alphabetically
					if (oResponse.aCorrespondenceTemplates[id1].name < oResponse.aCorrespondenceTemplates[id2].name) {
						return -1;
					} else if (oResponse.aCorrespondenceTemplates[id1].name > oResponse.aCorrespondenceTemplates[id2].name) {
						return 1;
					} else {
						return 0;
					}
				}).map(function (id) {
					return $T.option({value: id},
						oResponse.aCorrespondenceTemplates[id].name
					);
				});
			}
			
			// Pass to Callback
			fnCallback(aOptions);
		}
	},

	getCorrespondenceSourceType	: function(iTemplateId, fnCallback, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Make Request
			var fnGetSourceType	=	jQuery.json.jsonFunction(
										Correspondence_Template.getCorrespondenceSourceType.curry(iTemplateId, fnCallback),
										Correspondence_Template._ajaxError,
										'Correspondence_Template',
										'getCorrespondenceSourceType'
									);
			fnGetSourceType(iTemplateId);
		}
		else
		{
			if (!oResponse.bSuccess)
			{
				// Failed
				Correspondence_Template._ajaxError(oResponse);
				fnCallback(null);
			}
			else
			{
				// Success
				fnCallback(oResponse.oCorrespondenceSourceType);
			}
		}
	},

	getAllAsSelectOptions	: function(fnCallback, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Make Request
			var fnGetAll	=	jQuery.json.jsonFunction(
									Correspondence_Template.getAllAsSelectOptions.curry(fnCallback),
									Correspondence_Template.getAllAsSelectOptions.curry(fnCallback),
									'Correspondence_Template',
									'getAll'
								);
			fnGetAll();
		}
		else
		{
			// Create options
			var aOptions	= [];
			for (var iId in oResponse.aResults)
			{
				aOptions.push(
					$T.option({value: iId},
						oResponse.aResults[iId].name
					)
				);
			}
			fnCallback(aOptions);
		}
	},

	getAdditionalColumns	: function(iId, fnCallback, oResponse)
	{
		if (!oResponse)
		{
			var fnGetAdditionalColumns	=	jQuery.json.jsonFunction(
												Correspondence_Template.getAdditionalColumns.curry(iId, fnCallback),
												Correspondence_Template.getAdditionalColumns.curry(iId, fnCallback),
												'Correspondence_Template',
												'getAdditionalColumns'
											);
			fnGetAdditionalColumns(iId);
		}
		else
		{
			fnCallback(oResponse.aAdditionalColumns);
		}
	},
	
	_ajaxError	: function(oResponse)
	{
		jQuery.json.errorPopup(oResponse);
	}
});
