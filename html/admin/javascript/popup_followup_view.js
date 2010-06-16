
var Popup_FollowUp_View	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iType, iTypeDetail, oFollowUp)
	{
		$super(40);
		
		this._iType						= iType;
		this._iTypeDetail				= iTypeDetail;
		this._iSelectedRecurringEndDate	= null;
		this._iSelectedRecurrenceType	= null;
		this._oFollowUp					= oFollowUp;
		
		// Date object to use as 'now'
		this._oInstanceCreatedDate		= new Date();
		this._oInstanceCreatedDate.setSeconds(0);
		this._oInstanceCreatedDate.setMilliseconds(0);
		
		// Get all categories then build ui
		FollowUp_Category.getAllIndexed(this._buildUI.bind(this));
	},

	_buildUI	: function(hCategories, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Get details about the ticket/note/action
			var fnGetContextDetails	=	jQuery.json.jsonFunction(
											this._buildUI.bind(this, hCategories), 
											this._ajaxError.bind(this, true), 
											'FollowUp', 
											'getFollowUpContextDetails'
										);
			fnGetContextDetails(this._iType, this._iTypeDetail);
		}
		else if (oResponse.Success)
		{
			// Cache response
			this._oDetails		= oResponse.aDetails;
			
			// Create due/end date information elements
			var oDateElement	= null;
			if (this._oFollowUp.due_datetime)
			{
				// Once off, show due date
				oDateElement	= 	$T.table({class: 'popup-followup-view-followup-details-onceoff'},
										$T.tbody(
											$T.tr(
												$T.td('Due On'),
												$T.td(Popup_FollowUp_View.formatDateTime(this._oFollowUp.due_datetime))
											)
										)
									);
				
				// Popup icon and title
				this.setTitle('View Follow-Up');
				this.setIcon(Popup_FollowUp_View.FOLLOWUP_IMAGE_SOURCE);
			}
			else
			{
				// Recurring show end date and number of occurences (if set)
				var oEndDateElement	= null;
				
				if (this._oFollowUp.end_datetime == '9999-12-31 23:59:59')
				{
					// No end date
					oEndDateElement	= $T.div('No End Date');
				}
				else
				{
					// End date as well as number of occurences
					oEndDateElement	= 	$T.div(
											$T.div(Popup_FollowUp_View.formatDateTime(this._oFollowUp.end_datetime)),
											$T.div(
												'(After ' + 
												Popup_FollowUp_View.calculateOccurrences(
													this._oFollowUp.start_datetime, 
													this._oFollowUp.end_datetime, 
													this._oFollowUp.recurrence_multiplier, 
													this._oFollowUp.followup_recurrence_period_id
												) + ' occurrences)'
											)
										);
				}
				
				oDateElement	=	$T.table({class: 'popup-followup-view-followup-details-recurring'},
										$T.tbody(
											$T.tr(
												$T.td('Start On'),
												$T.td(Popup_FollowUp_View.formatDateTime(this._oFollowUp.start_datetime))
											),
											$T.tr(
												$T.td('How Often?'),
												$T.td({class: 'popup-followup-add-details-recurring-howoften'},
													$T.span(
														'Every ' + 
														this._oFollowUp.recurrence_multiplier +
														' ' + 
														Flex.Constant.arrConstantGroups.followup_recurrence_period[this._oFollowUp.followup_recurrence_period_id].Name + 
														'(s)'
													)
												)
											),
											$T.tr(
												$T.td('Until'),
												$T.td({class: 'popup-followup-add-details-recurring-end'},
													(oEndDateElement ? oEndDateElement : '')
												)
											)
										)
									);
				// Popup icon and title
				this.setTitle('View Recurring Follow-Up');
				this.setIcon(Popup_FollowUp_View.FOLLOWUP_RECURRING_IMAGE_SOURCE);
			}
			
			// Build popup content
			this._oContent	= 	$T.div({class: 'popup-followup-add'},
									$T.div({class: 'section'},
										$T.div({class: 'section-header'},
											$T.div({class: 'section-header-title'},
												Flex.Constant.arrConstantGroups.followup_type[this._iType].Name + ' Details'
											)
										),
										$T.div({class: 'section-content'},
											this._createDetailsElement()
										)
									),
									$T.div({class: 'section'},
										$T.div({class: 'section-header'},
											$T.div({class: 'section-header-title'},
												'Follow-Up Details'
											)
										),
										$T.div({class: 'section-content section-content-fitted'},
											$T.table({class: 'input popup-followup-details input popup-followup-view-followup-details'},
												$T.colgroup(
													$T.col({style: 'width: 23%'}),
													$T.col({style: 'width: 77%'})
												),
												$T.tbody(
													$T.tr(
														$T.th({class: 'label'},
															'Type :'
														),
														$T.td(
															Popup_FollowUp_View._getTypeElement(this._iType)
														)															
													),
													$T.tr(
														$T.th({class: 'label'},
															'Category :'
														),
														$T.td(
															hCategories[this._oFollowUp.followup_category_id].name
														)
													),
													$T.tr(
														$T.th({class: 'label'}),
														$T.td({class: 'popup-followup-details-recurrence-type-select'},
															oDateElement
														)
													)
												)
											)
										)
									),
									$T.div({class: 'section'},
										$T.div({class: 'section-header'},
											$T.div({class: 'section-header-title'},
												'Change History'
											)
										),
										$T.div({class: 'section-content'},
											''
										)
									),
									$T.div({class: 'popup-followup-add-buttons'},
										$T.button({class: 'icon-button'},
											'Close'
										)
									)
								);
			
			// Footer button events
			var oCloseButton	= this._oContent.select('button.icon-button').last();
			oCloseButton.observe('click', this.hide.bind(this));
			
			this.addCloseButton();
			this.setContent(this._oContent);
			this.display();
		}
		else
		{
			// Error
			this._ajaxError(true, oResponse);
		}
	},
	
	_ajaxError	: function(bHideOnClose, oResponse)
	{
		if (this.oLoading)
		{
			this.oLoading.hide();
			delete this.oLoading;
		}
		
		if (oResponse.Success == false)
		{
			var oConfig	= {sTitle: 'Error', fnOnClose: (bHideOnClose ? this.hide.bind(this) : null)};
			
			if (oResponse.Message)
			{
				Reflex_Popup.alert(oResponse.Message, oConfig);
			}
			else if (oResponse.ERROR)
			{
				Reflex_Popup.alert(oResponse.ERROR, oConfig);
			}
			else if (oResponse.aValidationErrors)
			{
				Popup_FollowUp_View._showValidationErrorPopup(oResponse.aValidationErrors);
			}
		}
	},
	
	_createDetailsElement	: function()
	{
		var oDiv	= $T.div({class: 'popup-followup-add-details'});
		if (this._oDetails)
		{
			switch (this._iType)
			{
				case $CONSTANT.FOLLOWUP_TYPE_ACTION:
				case $CONSTANT.FOLLOWUP_TYPE_NOTE:
					// Account, service or contact info
					if (this._oDetails.customer_group)
					{
						oDiv.appendChild(Popup_FollowUp_View.getCustomerGroupLink(this._oDetails.account_id, this._oDetails.customer_group));
					}
					
					if (this._oDetails.account_id && this._oDetails.account_name)
					{
						oDiv.appendChild(Popup_FollowUp_View.getAccountLink(this._oDetails.account_id, this._oDetails.account_name));
					}
					
					if (this._oDetails.service_id && this._oDetails.service_fnn)
					{
						oDiv.appendChild(Popup_FollowUp_View.getServiceLink(this._oDetails.service_id, this._oDetails.service_fnn));
					}
					
					if (this._oDetails.contact_id && this._oDetails.contact_name)
					{
						oDiv.appendChild(Popup_FollowUp_View.getAccountContactLink(this._oDetails.contact_id, this._oDetails.contact_name));
					}
					break;
				case $CONSTANT.FOLLOWUP_TYPE_TICKET_CORRESPONDENCE:
					// Account or ticket contact info
					if (this._oDetails.customer_group)
					{
						oDiv.appendChild(Popup_FollowUp_View.getCustomerGroupLink(this._oDetails.account_id, this._oDetails.customer_group));
					}
					
					if (this._oDetails.account_id && this._oDetails.account_name)
					{
						oDiv.appendChild(Popup_FollowUp_View.getAccountLink(this._oDetails.account_id, this._oDetails.account_name));
					}
					
					if (this._oDetails.account_id && this._oDetails.ticket_id && this._oDetails.ticket_contact_name)
					{
						oDiv.appendChild(Popup_FollowUp_View.getTicketLink(this._oDetails.ticket_id, this._oDetails.account_id, this._oDetails.ticket_contact_name));
					}
					break;
			}
		}
		
		return oDiv;
	}
});

Popup_FollowUp_View.FOLLOWUP_IMAGE_SOURCE						= '../admin/img/template/followup.png';
Popup_FollowUp_View.FOLLOWUP_RECURRING_IMAGE_SOURCE				= '../admin/img/template/followup_recurring.png';

Popup_FollowUp_View.TYPE_NOTE_IMAGE_SOURCE						= '../admin/img/template/followup_note.png';
Popup_FollowUp_View.TYPE_ACTION_IMAGE_SOURCE					= '../admin/img/template/followup_action.png';
Popup_FollowUp_View.TYPE_TICKET_CORRESPONDENCE_IMAGE_SOURCE		= '../admin/img/template/tickets.png';

Popup_FollowUp_View.DETAILS_ACCOUNT_IMAGE_SOURCE				= '../admin/img/template/account.png';
Popup_FollowUp_View.DETAILS_ACCOUNT_CONTACT_IMAGE_SOURCE		= '../admin/img/template/contact_small.png';
Popup_FollowUp_View.DETAILS_ACCOUNT_SERVICE_IMAGE_SOURCE		= '../admin/img/template/service.png';
Popup_FollowUp_View.DETAILS_TICKET_IMAGE_SOURCE					= Popup_FollowUp_View.TYPE_TICKET_CORRESPONDENCE_IMAGE_SOURCE;

Popup_FollowUp_View.DATE_FORMAT									= 'l jS M Y g:i A';

Popup_FollowUp_View.formatDateTime	= function(sDateTime)
{
	return Reflex_Date_Format.format(Popup_FollowUp_View.DATE_FORMAT, new Date(Date.parse(sDateTime.replace(/-/g, '/'))));
};

Popup_FollowUp_View.getCustomerGroupLink	= function(iAccountId, sName)
{
	return 	$T.div({class: 'popup-followup-add-details-subdetail'},
				$T.span(sName)
			);
};

Popup_FollowUp_View.getAccountLink	= function(iId, sName)
{
	return 	$T.div({class: 'popup-followup-add-details-subdetail'},
				$T.img({src: Popup_FollowUp_View.DETAILS_ACCOUNT_IMAGE_SOURCE}),
				$T.span(sName + ' (' + iId + ')')
			);
};

Popup_FollowUp_View.getAccountContactLink	= function(iId, sName)
{
	return 	$T.div({class: 'popup-followup-add-details-subdetail'},
				$T.img({src: Popup_FollowUp_View.DETAILS_ACCOUNT_CONTACT_IMAGE_SOURCE}),
				$T.span(sName)
			);
};

Popup_FollowUp_View.getServiceLink	= function(iId, sFNN)
{
	return 	$T.div({class: 'popup-followup-add-details-subdetail'},
				$T.img({src: Popup_FollowUp_View.DETAILS_ACCOUNT_SERVICE_IMAGE_SOURCE}),
				$T.span('FNN : ' + sFNN)
			);
};

Popup_FollowUp_View.getTicketLink	= function(iTicketId, iAccountId, sContact)
{
	return 	$T.div({class: 'popup-followup-add-details-subdetail'},
				$T.img({src: Popup_FollowUp_View.DETAILS_TICKET_IMAGE_SOURCE}),
				$T.span('Ticket ' + iTicketId + ' (' + sContact + ')')
			);
};

Popup_FollowUp_View._getTypeElement	= function(iType)
{
	var sText	= Flex.Constant.arrConstantGroups.followup_type[iType].Name;
	var sImgSrc	= null;
	switch (iType)
	{
		case $CONSTANT.FOLLOWUP_TYPE_ACTION:
			sImgSrc	= Popup_FollowUp_View.TYPE_ACTION_IMAGE_SOURCE;
			break;
		case $CONSTANT.FOLLOWUP_TYPE_NOTE:
			sImgSrc	= Popup_FollowUp_View.TYPE_NOTE_IMAGE_SOURCE;
			break;
		case $CONSTANT.FOLLOWUP_TYPE_TICKET_CORRESPONDENCE:
			sImgSrc	= Popup_FollowUp_View.TYPE_TICKET_CORRESPONDENCE_IMAGE_SOURCE;
			break;
	}
	
	return 	$T.div({class: 'popup-followup-details-type'},
				$T.img({src: sImgSrc, alt: sText, title: sText}),
				$T.span(
					sText
				)
			);	
};

Popup_FollowUp_View.calculateOccurrences	= function(sStartDate, sEndDate, iRecurrenceMultiplier, iRecurrencePeriod)
{
	// Perform a date shift for the correct period until the end date is reached and record the iterations
	var oStartDate	= new Date(Date.parse(sStartDate.replace(/-/g, '/')));
	var oEndDate	= new Date(Date.parse(sEndDate.replace(/-/g, '/')));
	var iIteration	= 0;
	
	while (oStartDate.getTime() <= oEndDate.getTime())
	{
		Popup_FollowUp_View.shiftDate(oStartDate, iRecurrenceMultiplier, iRecurrencePeriod);
		iIteration++;
	}
	
	return iIteration;
};


Popup_FollowUp_View.shiftDate	= function(oDate, iRecurrenceMultiplier, iRecurrencePeriod)
{
	switch (iRecurrencePeriod)
	{
		case $CONSTANT.FOLLOWUP_RECURRENCE_PERIOD_WEEK:
			oDate.shift(iRecurrenceMultiplier * 7, 'days');
			break;
		case $CONSTANT.FOLLOWUP_RECURRENCE_PERIOD_MONTH:
			oDate.shift(iRecurrenceMultiplier, 'months');
			break;
	}
};
