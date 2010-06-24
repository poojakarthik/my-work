
var Popup_FollowUp_View	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iId, bIsRecurring, bFullDetailsVisible)
	{
		$super(50);
		
		this._iId					= iId;
		this._bIsRecurring			= bIsRecurring;
		this._bFullDetailsVisible	= ((bFullDetailsVisible || !bIsRecurring) ? true : false);
		
		// Show loading
		this.oLoading	= 	new Reflex_Popup.Loading(
								'Loading Follow-Up Details...'
							);
		this.oLoading.display();
		
		// Date object to use as 'now'
		this._oInstanceCreatedDate	= new Date();
		this._oInstanceCreatedDate.setSeconds(0);
		this._oInstanceCreatedDate.setMilliseconds(0);
		
		this._loadData();
	},
	
	_loadData	: function()
	{
		var fnMainDataGathered	= function(hCategories)
		{
			// Cache categories
			this._hCategories	= hCategories;
			if (this._bIsRecurring)
			{
				// Recurring - Get next due date, then occurence data
				this._getNextDueDate(
					this._getOccurenceData.bind(
						this,
						this._buildUI.bind(this)
					)
				)
			}
			else
			{
				// Once Off - All done, build UI
				this._buildUI();
			}
		};
		
		// Chain together ajax request functions
		this._getFollowUpDetails(								// 1. Get the followup details
			this._getContextContent.bind(						// 2. Get the content of the followups context (i.e. note)
				this,
				ActionsAndNotes.loadActionAndNoteTypes.bind(	// 3. Load the list of valid action and note types 
					ActionsAndNotes,
					FollowUp_Category.getAllIndexed.bind(		// 4. Load the list of followup categories
						FollowUp_Category, 
						fnMainDataGathered.bind(this)			// Finally... BUILD THE INTERFACE!
					)
				)
			)
		);				
				
	},
	
	_getFollowUpDetails	: function(fnCallback, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Get details about the ticket/note/action
			var fnGetContextDetails	=	jQuery.json.jsonFunction(
											this._getFollowUpDetails.bind(this, fnCallback), 
											this._ajaxError.bind(this, true), 
											'FollowUp' + (this._bIsRecurring ? '_Recurring' : ''), 
											'getFollowUpDetails'
										);
			fnGetContextDetails(this._iId);
		}
		else if (oResponse.Success)
		{
			// Cache response
			this._oFollowUp	= oResponse.oFollowUp;
			this._iType		= this._oFollowUp.followup_type_id;
			this._sTypeName	= Flex.Constant.arrConstantGroups.followup_type[this._iType].Name;
			this._oDetails	= oResponse.aDetails;
			this._bIsClosed	= (this._oFollowUp.followup_closure_id != null);
			
			if (fnCallback)
			{
				fnCallback();
			}
		}
		else
		{
			// Error
			this._ajaxError(true, oResponse);
		}
	},

	_getContextContent	: function(fnCallback, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Get details about the ticket/note/action
			var sHandler	= '';
			var sMethod		= '';
			var iId			= null;
			switch (this._iType)
			{
				case $CONSTANT.FOLLOWUP_TYPE_ACTION:
					sHandler	= 'ActionsAndNotes';
					sMethod		= 'getActionDetails';
					iId			= this._oDetails.action_id;
					break;
				case $CONSTANT.FOLLOWUP_TYPE_NOTE:
					sHandler	= 'ActionsAndNotes';
					sMethod		= 'getNoteDetails';
					iId			= this._oDetails.note_id;
					break;
				case $CONSTANT.FOLLOWUP_TYPE_TICKET_CORRESPONDENCE:
					sHandler	= 'Ticketing_Correspondence';
					sMethod		= 'getForId';
					iId			= this._oDetails.ticketing_correspondence_id;
					break;
			}
			
			var fnContextDetails	=	jQuery.json.jsonFunction(
											this._getContextContent.bind(this, fnCallback), 
											this._ajaxError.bind(this, true), 
											sHandler, 
											sMethod
										);
			fnContextDetails(iId);
		}
		else if (oResponse.Success)
		{
			// Cache response
			this._oContextContent	= (oResponse.oDetails ? oResponse.oDetails : oResponse.oCorrespondence);
			
			if (fnCallback)
			{
				fnCallback();
			}
		}
		else
		{
			// Error
			this._ajaxError(true, oResponse);
		}
	},
	
	_getNextDueDate	: function(fnCallback, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Make request to get the next recurring due date
			var fnNextDueDate	= 	jQuery.json.jsonFunction(
									this._getNextDueDate.bind(this, fnCallback), 
									this._ajaxError.bind(this),
									'FollowUp_Recurring',
									'getNextDueDate'
								);
			fnNextDueDate(this._oFollowUp.id);
		}
		else if (oResponse.Success)
		{
			// All good, cache the next due date
			this._sNextDueDate	= Popup_FollowUp_View.formatDateTime(oResponse.sDueDateTime, false, true);
			
			if (fnCallback)
			{
				fnCallback();
			}
		}
		else
		{
			// Error
			this._ajaxError(true, oResponse);
		}
	},
	
	_getOccurenceData	: function(fnCallback, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Make request to get the all occurrences for the recurring follow-up 
			var fnOccurrenceData	= 	jQuery.json.jsonFunction(
											this._getOccurenceData.bind(this, fnCallback), 
											this._ajaxError.bind(this),
											'FollowUp_Recurring',
											'getOccurrences'
										);
			fnOccurrenceData(this._oFollowUp.id, Math.floor(new Date().getTime() / 1000));
		}
		else if (oResponse.Success)
		{
			// All good, cache the next due date
			this._aPastOccurrences	= oResponse.aOccurrences;
			
			if (fnCallback)
			{
				fnCallback();
			}
		}
		else
		{
			// Error
			this._ajaxError(true, oResponse);
		}
	},
	
	_buildUI	: function()
	{
		// Create due/end date information elements
		var oDateElement	= null;
		if (!this._bIsRecurring)
		{
			// Once off, show due date
			oDateElement	= 	$T.table({class: 'popup-followup-view-followup-details-onceoff'},
									$T.tbody(
										$T.tr(
											$T.td('Due On'),
											$T.td(Popup_FollowUp_View.formatDateTime(this._oFollowUp.due_datetime, false, !this._bIsClosed))
										)
									)
								);
			
			if (this._bIsClosed)
			{
				// Add closed datetime & closure reason
				var oTBody	= oDateElement.select('tbody').first();
				oTBody.appendChild(
					$T.tr(
						$T.td('Closed On'),
						$T.td(
							Popup_FollowUp_View.formatDateTime(this._oFollowUp.closed_datetime, false)
						)
					)
				);
				oTBody.appendChild(
					$T.tr(
						$T.td('Reason'),
						$T.td(this._oFollowUp.followup_closure.name)
					)
				);
			}
			
			// Popup icon and title
			this.setTitle('View Follow-Up');
			this.setIcon(Popup_FollowUp_View.FOLLOWUP_IMAGE_SOURCE);
		}
		else
		{
			// Recurring show end date and number of occurences (if set)
			var oEndDateElement	= null;
			
			if (this._oFollowUp.end_datetime == Popup_FollowUp_View.NO_END_DATE)
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
			
			var bStartsInFuture	= Date.parse(this._oFollowUp.start_datetime.replace(/-/g, '/')) > new Date().getTime();
			oDateElement		=	$T.table({class: 'popup-followup-view-followup-details-recurring'},
										$T.tbody(
											$T.tr(
												$T.td(bStartsInFuture ? 'Starts On' : 'Started'),
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
											),
											$T.tr(
												$T.td('Due Next'),
												$T.td({class: 'popup-followup-add-details-recurring-duenext'},
													this._sNextDueDate	
												)
											)
										)
									);
			// Popup icon and title
			this.setTitle('View Recurring Follow-Up');
			this.setIcon(Popup_FollowUp_View.FOLLOWUP_RECURRING_IMAGE_SOURCE);
		}
		
		// Past Occurrences section
		var oPastOccurrencesSection	= null;
		if (this._bIsRecurring)
		{
			oPastOccurrencesSection	= this._createPastOccurrencesSection();
		}
		
		// Full contents section
		var oContentsSection	= this._createContentsSection();
		
		// Build main content
		this._oContent	= 	$T.div({class: 'popup-followup-add popup-followup-view'},
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
														Popup_FollowUp_View._getTypeElement(this._iType, this._sTypeName)
													)															
												),
												$T.tr(
													$T.th({class: 'label'},
														'Category :'
													),
													$T.td(
														this._hCategories[this._oFollowUp.followup_category_id].name
													)
												),
												$T.tr({class: 'popup-followup-view-followup-details-type-detail'},
													$T.th({class: 'label'},
														this._sTypeName + ' Details :'
													),
													$T.td(this._createDetailsElement())
												)
											)
										)
									)
								),
								oDateElement,
								oContentsSection.getElement(),
								(oPastOccurrencesSection ? oPastOccurrencesSection.getElement() : ''),
								$T.div({class: 'popup-followup-add-buttons'},
									$T.button({class: 'icon-button'},
										'View Change History'
									),
									$T.button({class: 'icon-button'},
										'Close'
									)
								)
							);
		
		// Footer button events
		var oCloseButton	= this._oContent.select('button.icon-button').last();
		oCloseButton.observe('click', this.hide.bind(this));
		
		var oHistoryButton	= this._oContent.select('button.icon-button').first();
		oHistoryButton.observe('click', this._showChangeHistoryPopup.bind(this));
		
		// Hide loading
		this.oLoading.hide();
		delete this.oLoading;
		
		this.addCloseButton();
		this.setContent(this._oContent);
		this.display();
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
		var oDiv	= $T.div({class: 'popup-followup-view-details'});
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
	},
	
	_createContentsSection	: function()
	{
		var oContentsElement	= null;
		var sExtraSectionClass	= '';
		switch (this._iType)
		{
			case $CONSTANT.FOLLOWUP_TYPE_ACTION:
			case $CONSTANT.FOLLOWUP_TYPE_NOTE:
				var oList	= new ActionsAndNotes.List.Embedded();
				sExtraSectionClass	= ' action-list embedded';
				oContentsElement	= oList.renderItem(this._oContextContent, false);
				break;
			case $CONSTANT.FOLLOWUP_TYPE_TICKET_CORRESPONDENCE:
				var oCreationDate			= Date.$parseDate(this._oContextContent.creation_datetime, 'Y-m-d H:i:s');
				var oDetailsElement			= $T.div({class: 'ticket-correspondence-content'});
				oDetailsElement.innerHTML	= this._oContextContent.details.replace(/\n/g, '<br/>');
				oContentsElement			=	$T.div({class: 'ticket-correspondence'},
													$T.div(
														$T.span('Time '),
														oCreationDate.$format('H:i:s D d-m-Y')
													),
													$T.div(
														$T.span('Event '),
														this._oContextContent.source_name + 
														' - ' + 
														this._oContextContent.delivery_status_name
													),
													$T.div(
														$T.span('Summary '),
														this._oContextContent.summary
													),
													oDetailsElement
												);
				break;
		}
		
		var oContentsSection	= new Section_Expandable(true, 'popup-followup-view-full-details' + sExtraSectionClass);
		oContentsSection.setTitleText(this._sTypeName + ' Contents');
		oContentsSection.setContent(oContentsElement);
		oContentsSection.setExpanded(this._bFullDetailsVisible);
		
		return oContentsSection;
	},
	
	_createPastOccurrencesSection	: function()
	{
		var oContent		= $T.div();
		var oOccurrence		= null;
		var iDueDatetime	= null;
		var aData			= jQuery.json.arrayAsObject(this._aPastOccurrences);
		for (var i in aData)
		{
			oOccurrence		= aData[i];
			iDueDatetime	= Date.$parseDate(oOccurrence.sDueDatetime, 'Y-m-d H:i:s');
			
			oContent.appendChild(
				$T.div(
					Popup_FollowUp_View.formatDateTime(oOccurrence.sDueDatetime, true, false),
					' - ',
					(
						oOccurrence.oFollowUpClosure 
							? 'Closed (' + oOccurrence.oFollowUpClosure.name + ')' 
							:	(
									(iDueDatetime >= new Date().getTime()) 
										? 'Current' 
										: 'Overdue'
								)
						)
				)
			);
		}
		
		var oSection	= new Section_Expandable(false, 'popup-followup-view-past-occurrences');
		oSection.setTitleText('Occurences');
		oSection.setContent(oContent);
		oSection.setExpanded(true);
		return oSection;
	},
	
	_showChangeHistoryPopup	: function()
	{
		var fnOnScriptLoad	= function()
		{
			var oPopup	= new Popup_FollowUp_History(this._iId, this._bIsRecurring);
		}
		
		JsAutoLoader.loadScript('javascript/popup_followup_history.js', fnOnScriptLoad.bind(this));
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

Popup_FollowUp_View.DATE_FORMAT			= 'l jS M Y g:i A';
Popup_FollowUp_View.DATE_FORMAT_SHORT	= 'd/m/y g:i A';

Popup_FollowUp_View.NO_END_DATE			= '9999-12-31 23:59:59';

Popup_FollowUp_View.formatDateTime	= function(sDateTime, bShortVersion, bShowIfOverdue)
{
	var oDate		= new Date(Date.parse(sDateTime.replace(/-/g, '/')));
	var bOverdue	= (bShowIfOverdue ? oDate.getTime() < new Date().getTime() : false);
	if (bShortVersion)
	{
		return	$T.span({class: (bOverdue ? 'popup-followup-view-date-overdue' : '')},
					oDate.$format(Popup_FollowUp_View.DATE_FORMAT_SHORT)
				);
	}
	else
	{
		return	$T.span({class: (bOverdue ? 'popup-followup-view-date-overdue' : '')},
					oDate.$format(Popup_FollowUp_View.DATE_FORMAT)
				);
	}
};

Popup_FollowUp_View.getCustomerGroupLink	= function(iAccountId, sName)
{
	return 	$T.div({class: 'popup-followup-detail-subdetail'},
				$T.span(sName)
			);
};

Popup_FollowUp_View.getAccountLink	= function(iId, sName)
{
	var sUrl	= 'flex.php/Account/Overview/?Account.Id=' + iId;
	return 	$T.div({class: 'popup-followup-detail-subdetail account'},
				$T.div({class: 'account-id'},
					$T.img({src: Popup_FollowUp_View.DETAILS_ACCOUNT_IMAGE_SOURCE}),
					$T.a({href: sUrl},
						iId + ': '
					)
				),
				$T.a({class: 'account-name', href: sUrl},
					sName
				)
			);
};

Popup_FollowUp_View.getAccountContactLink	= function(iId, sName)
{
	return 	$T.div({class: 'popup-followup-detail-subdetail'},
				$T.img({src: Popup_FollowUp_View.DETAILS_ACCOUNT_CONTACT_IMAGE_SOURCE}),
				$T.span(sName)
			);
};

Popup_FollowUp_View.getServiceLink	= function(iId, sFNN)
{
	return 	$T.div({class: 'popup-followup-detail-subdetail'},
				$T.img({src: Popup_FollowUp_View.DETAILS_ACCOUNT_SERVICE_IMAGE_SOURCE}),
				$T.span('FNN : ' + sFNN)
			);
};

Popup_FollowUp_View.getTicketLink	= function(iTicketId, iAccountId, sContact)
{
	return 	$T.div({class: 'popup-followup-detail-subdetail'},
				$T.img({src: Popup_FollowUp_View.DETAILS_TICKET_IMAGE_SOURCE}),
				$T.span('Ticket ' + iTicketId + ' (' + sContact + ')')
			);
};

Popup_FollowUp_View._getTypeElement	= function(iType, sTypeName)
{
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
				$T.img({src: sImgSrc, alt: sTypeName, title: sTypeName}),
				$T.span(
					sTypeName
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

