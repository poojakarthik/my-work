var Correspondence_Template	= Class.create
({
	// NO instance functionality
});

// Static

Correspondence_Template.getAllWithNonSystemSourcesAsSelectOptions	= function(fnCallback, oResponse)
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
		var aOptions	= [];
		if (!oResponse.bSuccess)
		{
			// Failed
			Correspondence_Template._ajaxError(oResponse);
		}
		else
		{
			// Success
			var oTemplate	= null;
			for (i in oResponse.aCorrespondenceTemplates)
			{
				if (isNaN(i))
				{
					continue;
				}
				
				oTemplate	= oResponse.aCorrespondenceTemplates[i];
				aOptions.push(
					$T.option({value: oTemplate.id},
							oTemplate.name
					)
				);		
			}
		}
		
		// Pass to Callback
		fnCallback(aOptions);
	}
};

Correspondence_Template.getCorrespondenceSourceType	= function(iTemplateId, fnCallback, oResponse)
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
};

Correspondence_Template._ajaxError	= function(oResponse)
{
	var oConfig	= {sTitle: 'Error'};
	if (oResponse.sMessage)
	{
		Reflex_Popup.alert(oResponse.sMessage, oConfig);
	}
	else if (oResponse.ERROR)
	{
		Reflex_Popup.alert(oResponse.ERROR, oConfig);
	}
}