var Popup_Collections_Event_List_Details = Class.create(Reflex_Popup, 
{
	initialize : function($super, iEventId, iImplementationId)
	{
		$super(40);
		this._iEventId 			= iEventId;
		this._iImplementationId	= iImplementationId;
		Flex.Constant.loadConstantGroup(Popup_Collections_Event_List_Details.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
	},
	
	_buildUI : function(oResponse)
	{
		if (!oResponse)
		{
			var fnResp 	= this._buildUI.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Event', 'getEventDetails');
			fnReq(this._iEventId);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Popup_Collections_Event_List_Details._ajaxError(oResponse);
			return;
		}
		
		this._oEventDetails = oResponse.oDetails;
		
		var oContentDiv =	$T.div({class: 'popup-collections-event-list-event-details'},
								$T.table({class: 'reflex input'},
									$T.tbody()
								)
							);
		this._oTBody = oContentDiv.select('tbody').first();
		
		this.setIcon('../admin/img/template/magnifier.png');
		this.setTitle('Event Details');
		this.addCloseButton();
		this.setContent(oContentDiv);
		
		// Show the implementation detail options
		var aRows = [];
		switch (this._oEventDetails.collection_event_type_implementation_id)
		{
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_CORRESPONDENCE:
				// correspondence_template_id
				aRows.push(
					$T.tr(
						$T.th('Correspondence Template'),
						$T.td(this._oEventDetails.correspondence_template.name)
					)
				);
				
				// document_template_type_id
				aRows.push(
					$T.tr(
						$T.th('Document Template Type'),
						$T.td(this._oEventDetails.document_template_type ? this._oEventDetails.document_template_type.name : 'None')
					)
				);
				break;
				
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_REPORT:
				// report_sql
				aRows.push(
					$T.tr(
						$T.th('Report SQL'),
						$T.td(
							$T.textarea(this._oEventDetails.report_sql)
						)
					)
				);
				
				// email_notification_id
				aRows.push(
					$T.tr(
						$T.th('Email Notification'),
						$T.td(this._oEventDetails.email_notification.name)
					)
				);
				
				// collection_event_report_output_id
				aRows.push(
					$T.tr(
						$T.th('Report Output'),
						$T.td(this._oEventDetails.collection_event_report_output.Name)
					)
				);
				
				break;
			
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_ACTION:
				// action_type_id
				aRows.push(
					$T.tr(
						$T.th('Action Type'),
						$T.td(this._oEventDetails.action_type.name)
					)
				);
				break;
				
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_SEVERITY:
				// collection_severity_id
				aRows.push(
					$T.tr(
						$T.th('Severity'),
						$T.td(this._oEventDetails.collection_severity.name)
					)
				);
				break;
				
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_OCA:
				// legal_fee_charge_type_id
				aRows.push(
					$T.tr(
						$T.th('Late Fee (Charge Type)'),
						$T.td(this._oEventDetails.legal_fee_charge_type.ChargeType + ' (' + this._oEventDetails.legal_fee_charge_type.Description + ')')
					)
				);
				break;
				
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_CHARGE:
				// charge_type_id
				aRows.push(
					$T.tr(
						$T.th('Charge Type'),
						$T.td(this._oEventDetails.charge_type.ChargeType + ' (' + this._oEventDetails.charge_type.Description + ')')
					)
				);
				// allow_recharge
				aRows.push(
					$T.tr(
						$T.th('Allow Recharge'),
						$T.td(this._oEventDetails.allow_recharge ? 'Yes' : 'No')
					)
				);
				
				if (this._oEventDetails.percentage_outstanding_debt)
				{
					// percentage_outstanding_debt
					aRows.push(
						$T.tr(
							$T.th('Percentage of Outstanding Debt'),
							$T.td(this._oEventDetails.percentage_outstanding_debt)
						)
					);
					// minimum_amount
					aRows.push(
						$T.tr(
							$T.th('Minimum Amount'),
							$T.td(this._oEventDetails.minimum_amount)
						)
					);
					// maximum_amount
					aRows.push(
						$T.tr(
							$T.th('Maximum Amount'),
							$T.td(this._oEventDetails.maximum_amount)
						)
					);
				}
				else
				{
					// flat_fee
					aRows.push(
						$T.tr(
							$T.th('Flat Fee'),
							$T.td(this._oEventDetails.minimum_amount)
						)
					);
				}
				break;
			
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_EXIT_COLLECTIONS:
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_BARRING:
			case $CONSTANT.COLLECTION_EVENT_TYPE_IMPLEMENTATION_TDC:
				// No extra details are necessary for these implementations
				break;
		}
		
		for (var i = 0; i < aRows.length; i++)
		{
			this._oTBody.appendChild(aRows[i]);
		}
		
		this.display();
	}
});

Object.extend(Popup_Collections_Event_List_Details,
{
	REQUIRED_CONSTANT_GROUPS : ['collection_event_invocation', 
	                        	'collection_event_type_implementation'],
	
	_ajaxError : function(oResponse)
	{
		var sMessage = (oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.');
		Reflex_Popup.alert(sMessage, {sTitle: 'Error'});
	},
});