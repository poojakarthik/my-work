
var Component_Correspondence_Run_Details	= Class.create(
{
	initialize	: function(oContainer, iId)
	{
		this._oContainer	= oContainer;
		Correspondence_Run.getForId(iId, this._detailsLoaded.bind(this));
	},
	
	_detailsLoaded	: function(oRun)
	{
		this._oSection	= new Section_Expandable(true, 'component-correspondence-run-details');
		this._oSection.setTitleText('Details');
		this._oSection.setContent(
			$T.table({class: 'reflex input'},
				$T.tbody(
					$T.tr(
						$T.th('Id'),
						$T.td(oRun.id)
					),
					$T.tr(
						$T.th('Template'),
						$T.td(oRun.template.name + ' (' + oRun.template.template_code + ')')
					),
					$T.tr(
						$T.th('Processed'),
						$T.td(Component_Correspondence_Run_Details._formatDateTime(oRun.processed_datetime))
					),
					$T.tr(
						$T.th('Scheduled for Dispatch'),
						$T.td(Component_Correspondence_Run_Details._formatDateTime(oRun.scheduled_datetime))
					),
					$T.tr(
						$T.th('Dispatched'),
						$T.td(Component_Correspondence_Run_Details._formatDateTime(oRun.delivered_datetime, 'Not yet delivered'))
					),
					$T.tr(
						$T.th('Created'),
						$T.td(Component_Correspondence_Run_Details._formatDateTime(oRun.created))
					),
					$T.tr(
						$T.th('Pre-Printed'),
						$T.td(oRun.preprinted ? 'Yes' : 'No')
					)
				)
			)
		);
		this._oSection.setExpanded(true);
		this._oContainer.appendChild(this._oSection.getElement());
	}
});

// Static

Object.extend(Component_Correspondence_Run_Details, 
{
	_formatDateTime	: function(sValue, sNotSet)
	{
		if (sValue)
		{
			return Date.$parseDate(sValue, 'Y-m-d H:i:s').$format('d/m/Y g:i A');
		}
		else
		{
			return (sNotSet ? sNotSet : '');
		}
	}
});
