
/**
 * 
 * This is a Pseudo-ORM class for the FAKE type FollowUp_Status.
 * 
 * A followup_status is a combination of the followup_closure_types with 
 * followup_closure as sub-statuses and 3 'magic' values:
 * 
 *  - ACTIVE
 * 		- CURRENT
 * 		- OVERDUE
 * 
 */

var FollowUp_Status	= Class.create
({
	initialize	: function(iId, fnCallback)
	{
		// Since this class doesn't actually represent a table record, an instance will do nothing.
		this.id	= iId;
		fnCallback(this);
	}
});

/* Class members */

FollowUp_Status._hFollowUpClosureCache	= null;
FollowUp_Status._oDataset				= 	new Dataset_Ajax(
												Dataset_Ajax.CACHE_MODE_NO_CACHING, 
												{sObject: 'FollowUp_Closure', sMethod: 'getDataSet'}
											);

/* Static Methods */

FollowUp_Status.getActiveClosures	= function(fnCallback, iRecordCount, aResultSet)
{
	if (iRecordCount === undefined || aResultSet === undefined)
	{
		// Constant group load callback
		var fnGetData	= function(fnCallback)
		{
			this._oDataset.setFilter({status_id: $CONSTANT.STATUS_ACTIVE});
			this._oDataset.getRecords(
				this.getActiveClosures.bind(this, fnCallback)
			);
		}
		
		// Load constant group status
		Flex.Constant.loadConstantGroup('status', fnGetData.bind(this, fnCallback));
	}
	else
	{
		// Clear filter
		this._oDataset.setFilter(null);
		
		// Cache the result set
		for (i = 0; i < aResultSet.length; i++)
		{
			FollowUp_Status._hFollowUpClosureCache[aResultSet[i].id]	= aResultSet[i];
		}
		
		// Pass Response to Callback
		fnCallback(aResultSet);
	}
};

FollowUp_Status.getAllAsSelectOptions	= function(fnCallback, oClosures)
{
	if (!oClosures)
	{
		// Make Request
		this.getActiveClosures(this.getAllAsSelectOptions.bind(this, fnCallback));
	}
	else
	{
		// Create an Array of OPTION DOM Elements
		var aOptions	= [];
		
		aOptions.push(
			$T.option({value: FollowUp_Status.ACTIVE_VALUE},
				'All ' + FollowUp_Status.ACTIVE_TEXT
			)
		);
		
		aOptions.push(
			$T.option({value: FollowUp_Status.COMPLETED_VALUE},
				'All ' + FollowUp_Status.COMPLETED_TEXT
			)
		);
		
		aOptions.push(
			$T.option({value: FollowUp_Status.DISMISSED_VALUE},
				'All ' + FollowUp_Status.DISMISSED_TEXT
			)
		);
		
		var oActiveOptionGroup	= $T.optgroup({label: FollowUp_Status.ACTIVE_TEXT});
		oActiveOptionGroup.appendChild(
			$T.option({value: FollowUp_Status.CURRENT_VALUE},
				FollowUp_Status.CURRENT_TEXT
			)
		);
		oActiveOptionGroup.appendChild(
			$T.option({value: FollowUp_Status.OVERDUE_VALUE},
				FollowUp_Status.OVERDUE_TEXT
			)
		);
		
		var hOptionGroups											= {}
		hOptionGroups[$CONSTANT.FOLLOWUP_CLOSURE_TYPE_COMPLETED]	= $T.optgroup({label: FollowUp_Status.COMPLETED_TEXT});
		hOptionGroups[$CONSTANT.FOLLOWUP_CLOSURE_TYPE_DISMISSED]	= $T.optgroup({label: FollowUp_Status.DISMISSED_TEXT});
		
		var oClosure	= null;
		for (i in oClosures)
		{
			oClosure	= oClosures[i];
			hOptionGroups[oClosure.followup_closure_type_id].appendChild(
				$T.option({value: oClosure.id},
					oClosure.name
				)
			);
		}
		
		aOptions.push(oActiveOptionGroup);
		aOptions.push(hOptionGroups[$CONSTANT.FOLLOWUP_CLOSURE_TYPE_COMPLETED]);
		aOptions.push(hOptionGroups[$CONSTANT.FOLLOWUP_CLOSURE_TYPE_DISMISSED]);
		
		// Pass to Callback
		fnCallback(aOptions);
	}
};

FollowUp_Status.getStatusText	= function(oFollowUp)
{
	if (oFollowUp.followup_closure_id)
	{
		// Closed, get the closure name
		if (FollowUp_Status._hFollowUpClosureCache)
		{
			return FollowUp_Status._hFollowUpClosureCache[oFollowUp.followup_closure_id];
		}
		else
		{
			// Need to have called FollowUp_Status.getActiveClosures
		}
	}
	else
	{
		// Active, check the date to see if overdue or current
		var iDueDate	= Date.parse(oFollowUp.due_datetime.replace(/-/g, '/'));
		var iNow		= new Date().getTime();
		
		if (iDueDate >= iNow)
		{
			// Current
			return FollowUp_Status.CURRENT_TEXT;
		}
		else
		{
			// Overdue
			return FollowUp_Status.OVERDUE_TEXT;
		}
	}
};

FollowUp_Status.ACTIVE_VALUE	= 'ACTIVE';
FollowUp_Status.ACTIVE_TEXT		= 'Active';
FollowUp_Status.CURRENT_VALUE	= 'CURRENT';
FollowUp_Status.CURRENT_TEXT	= 'Current';
FollowUp_Status.OVERDUE_VALUE	= 'OVERDUE';
FollowUp_Status.OVERDUE_TEXT	= 'Overdue';
FollowUp_Status.COMPLETED_VALUE	= 'COMPLETED';
FollowUp_Status.COMPLETED_TEXT	= 'Completed';
FollowUp_Status.DISMISSED_VALUE	= 'DISMISSED';
FollowUp_Status.DISMISSED_TEXT	= 'Dismissed';



