
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
		
		this._oSection	= new Section_Expandable(true, 'component-correspondence-run-details');
		this._oSection.setTitleText('Details');
		this._oSection.setContent(
			$T.table({class: 'reflex input'},
				$T.tbody(
					$T.tr(
						$T.th('Id'),
						$T.td({class: 'id'},
							'-'
							//oRun.id
						),
						$T.th('Status'),
						$T.td({class: 'status'},
							'-'
							//oStatusElement
						)
					),
					$T.tr(
						$T.th('Template'),
						$T.td({class: 'template'},
							'-'
							/*$T.div(oRun.template.name),
							$T.div({class: 'subscript'},
								oRun.template.template_code
							)*/
						),
						$T.th('Source'),
						$T.td({class: 'source'},
							/*$T.div(oRun.source ? oRun.source : ''),
							$T.div({class:'subscript'},
								oRun.import_file_name ? oRun.import_file_name : ''
							)*/
							'-'
						)
					),
					$T.tr(
						$T.th('Created By'),
						$T.td({class: 'created-by'},
							'-'
							//oRun.created_employee_name
						),
						$T.th('Number of Items'),
						$T.td({class: 'number-of-items'},
							'-'
							//oRun.correspondence.length
						)
					),
					$T.tr(
						$T.th('Created'),
						$T.td({class: 'created'},
							//Component_Correspondence_Run_Details._formatDateTime(oRun.created)
							'-'
						),
						$T.th('Emailed Items'),
						$T.td({class: 'emailed-items'},
							//iEmail
							'-'
						)
					),
					$T.tr(
						$T.th('Processed'),
						$T.td({class: 'processed'},
							'-'
							//Component_Correspondence_Run_Details._formatDateTime(oRun.processed_datetime, 'Not Yet Processed')
						),						
						$T.th('Posted Items'),
						$T.td({class: 'posted-items'},
							'-'
							//iPost
						)
					),
					$T.tr(
						$T.th('Scheduled for Dispatch'),
						$T.td({class: 'scheduled-for-dispatch'},
							'-'
							//Component_Correspondence_Run_Details._formatDateTime(oRun.scheduled_datetime)
						),
						$T.th('Data File'),
						$T.td({class: 'data-file'},
							'-'
							//oRun.export_file_name ? oRun.export_file_name : 'N/A'
						)
					),
					$T.tr(
						$T.th('Dispatched'),
						$T.td({class: 'dispatched'},
							'-'
							//Component_Correspondence_Run_Details._formatDateTime(oRun.delivered_datetime, 'Awaiting Dispatch')
						),
						$T.th('Pre-Printed'),
						$T.td({class: 'pre-printed'},
							'-'
							//oRun.preprinted ? 'Yes' : 'No'
						)
					)
				)
			)
		);
		this._oSection.setExpanded(true);
		this._oContainer.appendChild(this._oSection.getElement());
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
		
		var aTDs		= this._oContainer.select('td');
		var oTD			= null;
		for (var i = 0; i < aTDs.length; i++)
		{
			oTD				= aTDs[i];
			oTD.innerHTML	= '';
			switch (oTD.className)
			{
				case 'id':
					oTD.appendChild($T.span(oRun.id));
					break;
				case 'status':
					oTD.appendChild(oStatusElement)
					break;
				case 'source':
					oTD.appendChild($T.div(oRun.source ? oRun.source : ''));
					oTD.appendChild(
						$T.div({class:'subscript'},
							oRun.import_file_name ? oRun.import_file_name : ''
						)
					);
					break;
				case 'template':
					oTD.appendChild($T.div(oRun.template.name));
					oTD.appendChild(
						$T.div({class: 'subscript'},
							oRun.template.template_code
						)
					);
					break;
				case 'created-by':
					oTD.appendChild($T.span(oRun.created_employee_name));
					break;
				case 'number-of-items':
					oTD.appendChild($T.span(oRun.correspondence.length));
					break;
				case 'emailed-items':
					oTD.appendChild($T.span(iEmail));
					break;
				case 'posted-items':
					oTD.appendChild($T.span(iPost));
					break;
				case 'scheduled-for-dispatch':
					oTD.appendChild($T.span(Component_Correspondence_Run_Details._formatDateTime(oRun.scheduled_datetime)));
					break;
				case 'data-file':
					oTD.appendChild($T.span(oRun.export_file_name ? oRun.export_file_name : 'N/A'));
					break;
				case 'created':
					oTD.appendChild($T.span(Component_Correspondence_Run_Details._formatDateTime(oRun.created)));
					break;
				case 'dispatched':
					oTD.appendChild($T.span(Component_Correspondence_Run_Details._formatDateTime(oRun.delivered_datetime, 'Awaiting Dispatch')));
					break;
				case 'pre-printed':
					oTD.appendChild($T.span(oRun.preprinted ? 'Yes' : 'No'));
					break;
				case 'processed':
					oTD.appendChild($T.span(Component_Correspondence_Run_Details._formatDateTime(oRun.processed_datetime, 'Not Yet Processed')));
					break;
			}
		}
		
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
