
var Correspondence_Delivery_Method	= Class.create({});

Object.extend(Correspondence_Delivery_Method, 
{
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
	
	getAllAsSelectOptions	: function(fnCallback, hCustomerGroups)
	{
		if (!hCustomerGroups)
		{
			Correspondence_Delivery_Method.getAll(Correspondence_Delivery_Method.getAllAsSelectOptions.curry(fnCallback));
		}
		else
		{
			var aOptions	= [];
			for (var i in hCustomerGroups)
			{
				aOptions.push(
					$T.option({value: i}, 
						hCustomerGroups[i].name
					)
				);
			}
			fnCallback(aOptions);
		}
	}
});
