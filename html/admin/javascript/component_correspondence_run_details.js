
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
		
		// Create a section to hold the UI
		this._oSection	= new Section_Expandable(true, 'component-correspondence-run-details');
		this._oSection.setTitleText('Details');
		
		// Main content -- A table (template) to hold the details
		this._oSection.setContent(
			$T.div({class: 'component-correspondence-run-details-tables'},
				$T.table({class: 'reflex input'},
					$T.tbody(
						$T.tr(
							$T.th('Id'),
							$T.td({class: 'id'},
								'-'
							)
						),
						$T.tr(
							$T.th('Template'),
							$T.td({class: 'template'},
								'-'
							)
						),
						$T.tr(
							$T.th('Created By'),
							$T.td({class: 'created-by'},
								'-'
							)
						),
						$T.tr(
							$T.th('Created'),
							$T.td({class: 'created'},
								'-'
							)
						),
						$T.tr(
							$T.th('Processed'),
							$T.td({class: 'processed'},
								'-'
							)
						),
						$T.tr(
							$T.th('Scheduled for Dispatch'),
							$T.td({class: 'scheduled-for-dispatch'},
								'-'
							)
						),
						$T.tr(
							$T.th('Status'),
							$T.td({class: 'status'},
								'-'
							)
						),
						$T.tr(
							$T.th('Pre-Printed'),
							$T.td({class: 'pre-printed'},
								'-'
							)
						)
					)
				),
				$T.table({class: 'reflex input'},
					$T.tbody(
						$T.tr(
							$T.th('Source'),
							$T.td({class: 'source'},
								'-'
							)
						),
						$T.tr(
							$T.th('Items'),
							$T.td({class: 'items'},
								'-'
							)
						),
						$T.tr(
							$T.th('Data File(s)'),
							$T.td({class: 'data-file'},
								'-'
							)
						)
					)
				)
			)
		);
		
		this._oSection.setExpanded(true);
		this._oContainer.appendChild(this._oSection.getElement());
	},
	
	// _deliveryMethodsLoaded: Callback for correspondence delivery methods request. Makes request for correspondence run error types.
	_deliveryMethodsLoaded	: function(hMethods)
	{
		this._hDeliveryMethods	= hMethods;
		Flex.Constant.loadConstantGroup('correspondence_run_error', this._errorTypesLoaded.bind(this));
	},
	
	// _errorTypesLoaded: Callback for correspondence run error types request. Makes request for correspondence run details. 
	_errorTypesLoaded	: function(hErrorTypes)
	{
		Correspondence_Run.getForId(this._iId, this._detailsLoaded.bind(this));
	},
	
	// _detailsLoaded: Callback for correspondence run details request. Fills out the table with the details. 
	_detailsLoaded	: function(oRun)
	{
		// Count email & post items
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
		
		// Create status representation
		var oStatus			= Correspondence_Run_Status.getStatusFromCorrespondenceRun(oRun);
		var oStatusElement	= $T.div($T.div(oStatus.name));
		switch (oStatus.id)
		{
			case Correspondence_Run_Status.PROCESSING_FAILED:
				var oError	= Flex.Constant.arrConstantGroups.correspondence_run_error[oRun.correspondence_run_error_id];
				oStatusElement.appendChild(
					$T.div({class: 'subscript'},
						oError.Name
					)
				);
				break;
			case Correspondence_Run_Status.DISPATCHED:
				oStatusElement.appendChild(
					$T.div({class: 'subscript'},
						Component_Correspondence_Run_Details._formatDateTime(oRun.delivered_datetime)
					)
				);
				break;
		}
		
		// Fill each cell (using the classname to determine it's purpose)
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
				case 'items':
					oTD.appendChild(
						$T.ul({class: 'reset horizontal component-correspondence-run-details-items'},
							$T.li(
								$T.img({class: 'component-correspondence-run-details-item-icon', src: Correspondence_Delivery_Method.getIconForSystemName('EMAIL'), alt: 'Email', title: 'Email'}),
								$T.span({class: 'component-correspondence-run-details-item-count'}, 
									iEmail
								)
							),
							$T.li(
								$T.img({class: 'component-correspondence-run-details-item-icon', src: Correspondence_Delivery_Method.getIconForSystemName('POST'), alt: 'Post', title: 'Post'}),
								$T.span({class: 'component-correspondence-run-details-item-count'}, 
									iPost
								)
							),
							$T.li(
								$T.img({class: 'component-correspondence-run-details-item-icon', src: '../admin/img/template/sum.png', alt: 'Total', title: 'Total'}),
								$T.span({class: 'component-correspondence-run-details-item-count component-correspondence-run-details-item-count-total'}, 
									oRun.correspondence.length
								)
							)
						)
					);
					break;
				case 'scheduled-for-dispatch':
					oTD.appendChild($T.span(Component_Correspondence_Run_Details._formatDateTime(oRun.scheduled_datetime)));
					break;
				case 'data-file':
					oTD.appendChild(Component_Correspondence_Run_Details._getDispatchSummary(oRun));
					break;
				case 'created':
					oTD.appendChild($T.span(Component_Correspondence_Run_Details._formatDateTime(oRun.created)));
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
			var oDate = Date.$parseDate(sValue, 'Y-m-d H:i:s');
			return (oDate ? oDate.$format('jS M, Y g:ia') : '');
		}
		else
		{
			return (sNotSet ? sNotSet : '');
		}
	},
	
	// _getDispatchSummary: Generates a DOM representation of 
	_getDispatchSummary	: function(oRun)
	{
		if (!oRun.dispatch_data || (oRun.dispatch_data.length == 0))
		{
			return $T.span('N/A');
		}
	
		var oDiv	= $T.div();
		for (var i in oRun.dispatch_data)
		{
			if (isNaN(i))
			{
				break;
			}
			
			var oFileInfo			= oRun.dispatch_data[i];
			var oDeliveryMethods	= $T.span({class: 'component-correspondence-run-details-dispatchsummary-file-carrier-method'});
			for (var sMethod in oFileInfo.delivery_methods)
			{
				if (isNaN(oFileInfo.delivery_methods[sMethod]))
				{
					break;
				}
				
				oDeliveryMethods.appendChild(
					$T.img({src: Correspondence_Delivery_Method.getIconForSystemName(sMethod), alt: sMethod, title: sMethod})
				);
			}
			
			var oFileDiv	= 	$T.div({class: 'component-correspondence-run-details-dispatchsummary-file'},
									$T.div({class: 'component-correspondence-run-details-dispatchsummary-file-carrier'},
										$T.span({class: 'component-correspondence-run-details-dispatchsummary-file-carrier-name'},
											oFileInfo.carrier
										),
										oDeliveryMethods
									),
									$T.div({class: 'component-correspondence-run-details-dispatchsummary-file-file'},
										oFileInfo.file
									)
								);
			var oStatusDiv	= 	$T.div({class: 'component-correspondence-run-details-dispatchsummary-file-status'},
									$T.span({class: 'component-correspondence-run-details-dispatchsummary-file-status-desc'},
										oFileInfo.status
									)
								);
			if (oFileInfo.batch !== null)
			{
				// Has been dispatched
				oStatusDiv.appendChild(
					$T.span({class: 'component-correspondence-run-details-dispatchsummary-file-status-dispatchdate'},
						'(' + Date.$parseDate(oFileInfo.dispatch_date, 'Y-m-d H:i:s').$format('j M Y g:ia') + ')'
					)
				);
			}
			oFileDiv.appendChild(oStatusDiv);
			oDiv.appendChild(oFileDiv);
		}
		
		return oDiv;
	}
});
