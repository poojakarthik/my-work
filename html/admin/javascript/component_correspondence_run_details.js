
var Component_Correspondence_Run_Details	= Class.create(
{
	initialize	: function(oContainer, iId)
	{
		this._oLoadingPopup	= new Reflex_Popup.Loading();
		this._oLoadingPopup.display();
		
		this._oContainer		= oContainer;
		this._iId				= iId;
		this._hDeliveryMethods	= null;
		Correspondence_Delivery_Method.getAll(this._deliveryMethodsLoaded.bind(this));	
	},
	
	_deliveryMethodsLoaded	: function(hMethods)
	{
		this._hDeliveryMethods	= hMethods;
		Flex.Constant.loadConstantGroup('correspondence_run_error', this._errorTypesLoaded.bind(this));
	},
	
	_errorTypesLoaded	: function(hErrorTypes)
	{
		Correspondence_Run.getForId(this._iId, this._detailsLoaded.bind(this));
	},
	
	_detailsLoaded	: function(oRun)
	{
		// Count items
		var iEmail	= 0;
		var iPost	= 0;
		var iTotal	= oRun.correspondence.length;
		for (var i = 0; i < oRun.correspondence.length; i++)
		{
			var oDeliveryMethod	= this._hDeliveryMethods[oRun.correspondence[i].correspondence_delivery_method_id];
			switch (oDeliveryMethod.system_name)
			{
				case 'EMAIL':
					iEmail++;
					break;
				case 'POST':
					iPost++;
					break;
			}
		}
		
		// Status
		var oStatus			= Correspondence_Run_Status.getStatusFromCorrespondenceRun(oRun);
		var oStatusElement	= $T.div($T.div(oStatus.name));
		if (oStatus.id == Correspondence_Run_Status.PROCESSING_FAILED)
		{
			var oError	= Flex.Constant.arrConstantGroups.correspondence_run_error[oRun.correspondence_run_error_id];
			oStatusElement.appendChild(
				$T.div({class: 'subscript'},
					oError.Name
				)
			);
		}
		
		this._oSection	= new Section_Expandable(true, 'component-correspondence-run-details');
		this._oSection.setTitleText('Details');
		this._oSection.setContent(
			$T.table({class: 'reflex input'},
				$T.tbody(
					$T.tr(
						$T.th('Id'),
						$T.td(oRun.id),
						$T.th('Number of Items'),
						$T.td(oRun.correspondence.length)
					),
					$T.tr(
						$T.th('Template'),
						$T.td(
							$T.div(oRun.template.name),
							$T.div({class: 'subscript'},
								oRun.template.template_code
							)
						),
						$T.th('Emailed Items'),
						$T.td(iEmail)
					),
					$T.tr(
						$T.th('Processed'),
						$T.td(Component_Correspondence_Run_Details._formatDateTime(oRun.processed_datetime)),
						$T.th('Posted Items'),
						$T.td(iPost)
					),
					$T.tr(
						$T.th('Scheduled for Dispatch'),
						$T.td(Component_Correspondence_Run_Details._formatDateTime(oRun.scheduled_datetime)),
						$T.th('Source'),
						$T.td(
							$T.div(oRun.source ? oRun.source : ''),
							$T.div({class:'subscript'},
								oRun.import_file_name ? oRun.import_file_name : ''
							)
						)
					),
					$T.tr(
						$T.th('Dispatched'),
						$T.td(Component_Correspondence_Run_Details._formatDateTime(oRun.delivered_datetime, 'Not yet delivered')),
						$T.th('Data File'),
						$T.td(oRun.export_file_name ? oRun.export_file_name : 'N/A')
					),
					$T.tr(
						$T.th('Created'),
						$T.td(Component_Correspondence_Run_Details._formatDateTime(oRun.created)),
						$T.th('Status'),
						$T.td(oStatusElement)
					),
					$T.tr(
						$T.th('Pre-Printed'),
						$T.td(oRun.preprinted ? 'Yes' : 'No'),
						$T.th(''),
						$T.td('')
					)
				)
			)
		);
		this._oSection.setExpanded(true);
		this._oContainer.appendChild(this._oSection.getElement());
		
		if (this._oLoadingPopup)
		{
			this._oLoadingPopup.hide();
			delete this._oLoadingPopup;
		}
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
