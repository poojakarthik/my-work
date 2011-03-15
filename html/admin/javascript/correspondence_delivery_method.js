
var Correspondence_Delivery_Method	= Class.create({});

Object.extend(Correspondence_Delivery_Method, 
{
	hIcons	:
	{
		'EMAIL'	: 'correspondence_email.png',
		'POST'	: 'lorry.png'
	},
	
	getAll	: function(fnCallback, oResponse)
	{
		if (!oResponse)
		{
			var fnGetAll	=	jQuery.json.jsonFunction(
									Correspondence_Delivery_Method.getAll.curry(fnCallback),
									Correspondence_Delivery_Method.getAll.curry(fnCallback),
									'Correspondence_Delivery_Method',
									'getAll'
								);
			fnGetAll();
		}
		else
		{
			fnCallback(oResponse.aResults);
		}
	},
	
	getAllAsSelectOptions	: function(fnCallback, hData)
	{
		if (!hData)
		{
			Correspondence_Delivery_Method.getAll(Correspondence_Delivery_Method.getAllAsSelectOptions.curry(fnCallback));
		}
		else
		{
			var aOptions	= [];
			for (var i in hData)
			{
				aOptions.push(
					$T.option({value: i}, 
						hData[i].name
					)
				);
			}
			fnCallback(aOptions);
		}
	},
	
	getIconForSystemName	: function(sName)
	{
		if (!Correspondence_Delivery_Method.hIcons[sName])
		{
			return null;
		}
		return '../admin/img/template/' + Correspondence_Delivery_Method.hIcons[sName];
	}
});
