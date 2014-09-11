
var Customer_Group	= Class.create({});

Object.extend(Customer_Group, 
{
	getAll	: function(fnCallback, oResponse)
	{
		if (!oResponse)
		{
			var fnGetAll	=	jQuery.json.jsonFunction(
									Customer_Group.getAll.curry(fnCallback),
									Customer_Group.getAll.curry(fnCallback),
									'Customer_Group',
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
			Customer_Group.getAll(Customer_Group.getAllAsSelectOptions.curry(fnCallback));
		}
		else
		{
			var aOptions	= [];
			for (var i in hCustomerGroups)
			{
				aOptions.push(
					$T.option({value: i}, 
						hCustomerGroups[i].internal_name
					)
				);
			}
			fnCallback(aOptions);
		}
	}
});
