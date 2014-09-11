
var Correspondence_Run_Status	= Class.create();

Object.extend(Correspondence_Run_Status,
{
	SUBMITTED			: 'SUBMITTED',
	PROCESSED			: 'PROCESSED',
	PROCESSING_FAILED	: 'PROCESSING_FAILED',
	DISPATCHED			: 'DISPATCHED',
	PARTLY_DISPATCHED	: 'PARTLY_DISPATCHED',
	
	
	_hStatuses	: {}
});

Correspondence_Run_Status._hStatuses[Correspondence_Run_Status.SUBMITTED]	= 
{
	id		: Correspondence_Run_Status.SUBMITTED, 
	name	: 'Submitted'
};
Correspondence_Run_Status._hStatuses[Correspondence_Run_Status.PROCESSED]	= 
{
	id		: Correspondence_Run_Status.PROCESSED, 
	name	: 'Processed'
};
Correspondence_Run_Status._hStatuses[Correspondence_Run_Status.PROCESSING_FAILED]	= 
{
	id		: Correspondence_Run_Status.PROCESSING_FAILED, 
	name	: 'Processing Failed'
};
Correspondence_Run_Status._hStatuses[Correspondence_Run_Status.DISPATCHED]	= 
{
	id		: Correspondence_Run_Status.DISPATCHED, 
	name	: 'Dispatched'
};

Correspondence_Run_Status._hStatuses[Correspondence_Run_Status.PARTLY_DISPATCHED]	= 
{
	id		: Correspondence_Run_Status.PARTLY_DISPATCHED, 
	name	: 'Partially Dispatched'
};

Object.extend(Correspondence_Run_Status,
{
	getAll	: function(fnCallback)
	{
		fnCallback(Correspondence_Run_Status._hStatuses);
	},
	
	getAllAsSelectOptions	: function(fnCallback)
	{
		var aOptions	= [];
		for (var iId in Correspondence_Run_Status._hStatuses)
		{
			aOptions.push(
				$T.option({value: iId},
					Correspondence_Run_Status._hStatuses[iId].name
				)
			);
		}
		fnCallback(aOptions);
	},
	
	getStatusFromCorrespondenceRun	: function(oRun)
	{
		var iStatusId	= null;
		if (oRun.delivered_datetime !== null)
		{
			iStatusId	= Correspondence_Run_Status.DISPATCHED;
		}
		else if (oRun.correspondence_run_error_id !== null)
		{
			iStatusId	= Correspondence_Run_Status.PROCESSING_FAILED;
		}
		else if (oRun.status == "Partially Dispatched")
		{
			iStatusId	= Correspondence_Run_Status.PARTLY_DISPATCHED;
		}
		else if (oRun.processed_datetime !== null)
		{
			iStatusId	= Correspondence_Run_Status.PROCESSED;
		}
		else
		{
			iStatusId	= Correspondence_Run_Status.SUBMITTED;
		}
		return Correspondence_Run_Status._hStatuses[iStatusId];
	}
});