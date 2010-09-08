
var Correspondence	= Class.create({});

Object.extend(Correspondence, 
{
	getForId	: function(iId, fnCallback, oResponse)
	{
		if (!oResponse)
		{
			var fnGetAll	=	jQuery.json.jsonFunction(
									Correspondence.getForId.curry(iId, fnCallback),
									Correspondence.getForId.curry(iId, fnCallback),
									'Correspondence',
									'getForId'
								);
			fnGetAll(iId);
		}
		else
		{
			fnCallback(oResponse.aData, $A(oResponse.aAdditionalColumns));
		}
	}
});
