
var Correspondence_Run	= Class.create({});

Object.extend(Correspondence_Run, 
{
	getForId	: function(iId, fnCallback, oResponse)
	{
		if (!oResponse)
		{
			var fnGetForId	=	jQuery.json.jsonFunction(
									Correspondence_Run.getForId.curry(iId, fnCallback),
									Correspondence_Run.getForId.curry(iId, fnCallback),
									'Correspondence_Run',
									'getForId'
								);
			fnGetForId(iId);
		}
		else
		{
			fnCallback(oResponse.oCorrespondenceRun);
		}
	}
});
