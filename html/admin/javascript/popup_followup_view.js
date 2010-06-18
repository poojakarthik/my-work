
var Popup_FollowUp_View	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iType, iTypeDetail, oFollowUp)
	{
		$super(50);
		
		this._iType			= iType;
		this._iTypeDetail	= iTypeDetail;
		this._oFollowUp		= oFollowUp;
		this._bIsRecurring	= (!this._oFollowUp.due_datetime && this._oFollowUp.start_datetime);
		
		// Show loading
		this.oLoading	= 	new Reflex_Popup.Loading(
								'Loading ' + 
								(this._bIsRecurring ? 'Recurring ' : ' ') + 
								'Follow-Up Details...'
							);
		this.oLoading.display();
		
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
			if (!this._bIsRecurring)
			{
				// Once off, show due date
				oDateElement	= 	$T.table({class: 'popup-followup-view-followup-details-onceoff'},
										$T.tbody(
											$T.tr(
												$T.td('Due On'),
												$T.td(Popup_FollowUp_View.formatDateTime(this._oFollowUp.due_datetime, false, true))
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
													$T.td({class: 'popup-followup-add-details-recurring-duenext'})
												)
											)
										);
				// Popup icon and title
				this.setTitle('View Recurring Follow-Up');
				this.setIcon(Popup_FollowUp_View.FOLLOWUP_RECURRING_IMAGE_SOURCE);
				this._getNextDueDate();
			}
			
			// Build popup content
			this._oContent	= 	$T.div({class: 'popup-followup-add'},
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
													$T.tr({class: 'popup-followup-view-followup-details-type-detail'},
														$T.th({class: 'label'},
															Flex.Constant.arrConstantGroups.followup_type[this._iType].Name + ' Details :'
														),
														$T.td(this._createDetailsElement())
													)
												)
											)
										)
									),
									oDateElement,									
									$T.div({class: 'section popup-followup-view-history'},
										$T.div({class: 'section-header'},
											$T.div({class: 'section-header-title'},
												'Change History'
											)
										),
										$T.div({class: 'section-content'},
											$T.ul({class: 'reset'})
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
			this._getHistoryData();
		}
		else
		{
			// Error
			this._ajaxError(true, oResponse);
		}
	},
	
	_getNextDueDate	: function(oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Make request to get the next recurring due date
			var fnNextDueDate	= 	jQuery.json.jsonFunction(
									this._getNextDueDate.bind(this), 
									this._ajaxError.bind(this),
									'FollowUp_Recurring',
									'getNextDueDate'
								);
			fnNextDueDate(this._oFollowUp.id);
		}
		else if (oResponse.Success)
		{
			// All good, show then next due date
			var oNextDueDateTD	= this._oContent.select('td.popup-followup-add-details-recurring-duenext').first();
			oNextDueDateTD.appendChild(Popup_FollowUp_View.formatDateTime(oResponse.sDueDateTime, false, true));
		}
		else
		{
			// Error
			this._ajaxError(true, oResponse);
		}
	},
	
	_getHistoryData	: function(oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Make request to get history
			var sJSONHandlerBaseName	= (this._bIsRecurring ? 'FollowUp_Recurring' : 'FollowUp');
			var sJSONHandlerMethod		= (this._bIsRecurring ? 'getForRecurringFollowUp' : 'getForFollowUp');
			var fnHistory				= 	jQuery.json.jsonFunction(
												this._getHistoryData.bind(this), 
												this._ajaxError.bind(this),
												sJSONHandlerBaseName + '_History',
												sJSONHandlerMethod
											);
			fnHistory(this._oFollowUp.id);
		}
		else if (oResponse.Success)
		{
			// Hide loading
			this.oLoading.hide();
			delete this.oLoading;
			
			// Generate the list data, changes between each record
			var oUL				= this._oContent.select('div.popup-followup-view-history ul.reset').first();
			var oPrevious		= null;
			var oCurrent		= null;
			var aReasons		= null;
			var oChanges		= null;
			var aRecords		= [];
			var hListData		= {};
			var oChangeElement	= null;
			for (var iId in oResponse.aResults)
			{
				oCurrent	= oResponse.aResults[iId];
				aReasons	= [];
				oChanges	= $T.div();
				if (oPrevious)
				{
					// Look at changes between current and previous
					for (var sFieldName in oCurrent)
					{
						if (!Popup_FollowUp_View.CHANGE_FIELDS_TO_IGNORE[sFieldName])
						{
							if (oCurrent[sFieldName] != oPrevious[sFieldName])
							{
								oChangeElement	= Popup_FollowUp_View.getChangeDescriptionElement(sFieldName, oPrevious, oCurrent);
								if (oChangeElement)
								{
									oChanges.appendChild(oChangeElement);
								}
							}
						}
					}
					
					// Build reason name list
					if (oCurrent.aModifyReasons)
					{
						for (var j in oCurrent.aModifyReasons)
						{
							if (oCurrent.aModifyReasons[j].id)
							{
								aReasons.push(oCurrent.aModifyReasons[j].name);
							}
						}
					}
					
					// ... plus reassign reason name
					if (oCurrent.oReassignReason)
					{
						aReasons.push(oCurrent.oReassignReason.name);
					}
				}
				else
				{
					// Must be first (creation) record
					oChanges.appendChild($T.span('Created'));
				}
				
				// Cache the list data for after sorting
				hListData[iId]	= 	{
										sEmployee	: oCurrent.modified_employee_name,
										sDateTime	: oCurrent.modified_datetime,
										aReasons	: aReasons, 
										oChanges	: oChanges
									};
				oPrevious		= oCurrent;
				
				// Cache the record for sorting
				aRecords.push(oCurrent);
			}
			
			// Sort the array, by modified_datetime descending
			var oSorter	= 	new Reflex_Sorter(
								[
								 	{
										sField		: 'modified_datetime', 
										bReverse	: true, 
										fnCompare	: Reflex_Sorter.stringGreaterThan
									}
								]
							);
			oSorter.sort(aRecords);
			
			// Generate li's
			var oListData	= null;
			for (var i = 0; i < aRecords.length; i++)
			{
				oListData	= hListData[aRecords[i].id];
				
				oUL.appendChild(
					$T.li({class: 'popup-followup-view-history-item'},
						$T.ul({class: 'reset horizontal'},
							$T.li(
								$T.div(Popup_FollowUp_View.formatDateTime(oListData.sDateTime, true)),
								$T.div({class: 'popup-followup-view-history-item-modified'},
									oListData.sEmployee
								)
							),
							$T.li(
								$T.div(
									oListData.oChanges,
									$T.div({class: 'popup-followup-view-history-item-reasons'},
										(oListData.aReasons.length ? oListData.aReasons.join() : '')
									)
								)
							)
						)
					)
				);
			}
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

Popup_FollowUp_View.CHANGE_FIELDS_TO_IGNORE	= 	{
													'id'						: true,
													'aModifyReasons'			: true,
													'modified_datetime'			: true, 
													'modified_employee_id'		: true,
													'oReassignReason'			: true,
													'followup_id'				: true,
													'assigned_employee_name'	: true
												};

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
	return 	$T.div({class: 'popup-followup-view-details-subdetail'},
				$T.span(sName)
			);
};

Popup_FollowUp_View.getAccountLink	= function(iId, sName)
{
	return 	$T.div({class: 'popup-followup-view-details-subdetail'},
				$T.img({src: Popup_FollowUp_View.DETAILS_ACCOUNT_IMAGE_SOURCE}),
				$T.span(sName + ' (' + iId + ')')
			);
};

Popup_FollowUp_View.getAccountContactLink	= function(iId, sName)
{
	return 	$T.div({class: 'popup-followup-view-details-subdetail'},
				$T.img({src: Popup_FollowUp_View.DETAILS_ACCOUNT_CONTACT_IMAGE_SOURCE}),
				$T.span(sName)
			);
};

Popup_FollowUp_View.getServiceLink	= function(iId, sFNN)
{
	return 	$T.div({class: 'popup-followup-view-details-subdetail'},
				$T.img({src: Popup_FollowUp_View.DETAILS_ACCOUNT_SERVICE_IMAGE_SOURCE}),
				$T.span('FNN : ' + sFNN)
			);
};

Popup_FollowUp_View.getTicketLink	= function(iTicketId, iAccountId, sContact)
{
	return 	$T.div({class: 'popup-followup-view-details-subdetail'},
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

Popup_FollowUp_View.getChangeDescriptionElement	= function(sFieldName, oPrevious, oCurrent)
{
	//var aWordChunks	= [];
	var oElement	= null;
	var mFrom		= null;
	var mTo			= null;
	switch (sFieldName)
	{
		case 'due_datetime':
			oElement	= 	$T.div(
								$T.span({class: 'popup-followup-view-history-item-bold'},
									'Due Date'
								),
								' changed from ',
								$T.span({class: 'popup-followup-view-history-item-from'},
									Popup_FollowUp_View.formatDateTime(oPrevious.due_datetime, true)
								),
								' to ',
								$T.span({class: 'popup-followup-view-history-item-to'},
									Popup_FollowUp_View.formatDateTime(oCurrent.due_datetime, true)
								)
							);
			break;
		case 'end_datetime':
			oElement	= 	$T.div(
								$T.span({class: 'popup-followup-view-history-item-bold'},
									'End Date'
								),
								' changed from ',
								$T.span({class: 'popup-followup-view-history-item-from'},
									(oPrevious.end_datetime == Popup_FollowUp_View.NO_END_DATE ? 'No End Date' : Popup_FollowUp_View.formatDateTime(oPrevious.end_datetime, true))
								),
								' to ',
								$T.span({class: 'popup-followup-view-history-item-to'},
									(oCurrent.end_datetime == Popup_FollowUp_View.NO_END_DATE ? 'No End Date' : Popup_FollowUp_View.formatDateTime(oCurrent.end_datetime, true))
								)		
							);
			break;
		case 'assigned_employee_id':
			oElement	= 	$T.div(
								$T.span({class: 'popup-followup-view-history-item-bold'},
									'Reassigned'
								),
								' from ',
								$T.span({class: 'popup-followup-view-history-item-from'},
									oPrevious.assigned_employee_name
								),
								' to ',
								$T.span({class: 'popup-followup-view-history-item-to'},		
									oCurrent.assigned_employee_name
								)
							);
			break;
	}
	
	return oElement;
};
