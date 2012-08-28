
var Popup_FollowUp_History	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iId, bIsRecurring)
	{
		$super(50);
		
		this._iId					= iId;
		this._bIsRecurring			= bIsRecurring;
		
		// Show loading
		this.oLoading	= 	new Reflex_Popup.Loading(
								'Loading Change History...'
							);
		this.oLoading.display();
		
		this._buildUI();
	},
	
	_buildUI	: function(oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Make request to get history
			var sJSONHandlerBaseName	= (this._bIsRecurring ? 'FollowUp_Recurring' 		: 'FollowUp');
			var sJSONHandlerMethod		= (this._bIsRecurring ? 'getForRecurringFollowUp' 	: 'getForFollowUp');
			var fnHistory				= 	jQuery.json.jsonFunction(
												this._buildUI.bind(this), 
												this._ajaxError.bind(this, false),
												sJSONHandlerBaseName + '_History',
												sJSONHandlerMethod
											);
			if (this._bIsRecurring)
			{
				// Send now in seconds so that the server uses client time to get the past occurences
				// also send 'true' for closed occurences only
				fnHistory(this._iId, Math.floor(new Date().getTime() / 1000));
			}
			else
			{
				fnHistory(this._iId);
			}
		}
		else if (oResponse.Success)
		{
			// Generate the list data, changes between each record
			var oPrevious		= null;
			var oCurrent		= null;
			var aReasons		= null;
			var oChanges		= null;
			var aRecords		= [];
			var hListData		= {};
			var oChangeElement	= null;
			
			// Process the history details
			for (var iId in oResponse.aHistoryDetails)
			{
				oCurrent		= oResponse.aHistoryDetails[iId];
				aReasons		= [];
				oChanges		= $T.div();
				
				// Check if this is a closure change
				if (oCurrent.oFollowUpClosure)
				{
					// The followup has been closed, show this as a change
					var sClosedText	= null;
					if (!oPrevious)
					{
						// This is the first history record but it's also a closure record (a recurring follow-up occurrence)
						sClosedText	= 'Created & Closed';
					}
					else
					{
						sClosedText	= 'Closed';
					}
					
					oChanges.appendChild($T.span(sClosedText));
					aReasons.push(oCurrent.oFollowUpClosure.name);
				}
				else
				{
					if (oPrevious)
					{
						// Look at changes between current and previous
						for (var sFieldName in oCurrent)
						{
							if (!Popup_FollowUp_History.CHANGE_FIELDS_TO_IGNORE[sFieldName])
							{
								if (oCurrent[sFieldName] != oPrevious[sFieldName])
								{
									oChangeElement	= Popup_FollowUp_History.getChangeDescriptionElement(sFieldName, oPrevious, oCurrent);
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
			
			if (this._bIsRecurring)
			{
				// Process the occurrence details
				var oOccurrenceDetails	= jQuery.json.arrayAsObject(oResponse.aOccurenceDetails);
				var oOccurrence			= null;
				for (var i in oOccurrenceDetails)
				{
					// Create a unique temporary id for the occurrence so that it can be sorted with the history data
					iId				= 'ocurr_' + i;
					oOccurrence		= oOccurrenceDetails[i];
					oOccurrence.id	= iId;
					hListData[iId]	= 	{
											sEmployee	: oOccurrence.sAssignedEmployee,
											sDateTime	: oOccurrence.sClosedDatetime,
											aReasons	: [oOccurrence.oFollowUpClosure.name], 
											oChanges	: 	$T.div({class: 'popup-followup-view-history-actioned'},
																$T.span('Actioned'),
																$T.span(' - Item was due on '),
																Popup_FollowUp_History.formatDateTime(
																	oOccurrence.sDueDatetime, false, false
																)
															)
										};
					
					// Add a 'modified_datetime' field so that it can be sorted with the history data
					oOccurrence.modified_datetime	= oOccurrence.sClosedDatetime;
					
					// Cache the record for sorting
					aRecords.push(oOccurrence);
				}
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
			
			// Create the interface
			var oHistorySection	= new Section(false, 'popup-followup-view-history');
			oHistorySection.setTitleText('Change History');
			oHistorySection.setContent($T.ul({class: 'reset'}));
			
			// Build main content
			this._oContent	= 	$T.div({class: 'popup-followup-add popup-followup-view'},
									oHistorySection.getElement(),
									$T.div({class: 'popup-followup-add-buttons'},
										$T.button({class: 'icon-button'},
											'Close'
										)
									)
								);
			
			// Footer button events
			var oCloseButton	= this._oContent.select('button.icon-button').last();
			oCloseButton.observe('click', this.hide.bind(this));
			
			// Generate li's
			var oUL			= this._oContent.select('div.popup-followup-view-history div.section-content ul.reset').first();
			var oListData	= null;
			for (var i = 0; i < aRecords.length; i++)
			{
				oListData	= hListData[aRecords[i].id];
				
				oUL.appendChild(
					$T.li({class: 'popup-followup-view-history-item'},
						$T.ul({class: 'reset horizontal'},
							$T.li(
								$T.div(Popup_FollowUp_History.formatDateTime(oListData.sDateTime, true)),
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
			
			// Hide loading & show popup
			this.oLoading.hide();
			delete this.oLoading;
			
			if (this._bIsRecurring)
			{
				// Popup icon and title
				this.setTitle('Recurring Follow-Up Change History');
				this.setIcon(Popup_FollowUp_History.FOLLOWUP_RECURRING_IMAGE_SOURCE);
			}
			else
			{
				// Popup icon and title
				this.setTitle('Follow-Up Change History');
				this.setIcon(Popup_FollowUp_History.FOLLOWUP_IMAGE_SOURCE);
			}
			
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
	
	_ajaxError : function(bHideOnClose, oResponse) {
		if (this.oLoading) {
			this.oLoading.hide();
			delete this.oLoading;
		}
		
		if (oResponse.Success == false) {
			if (oResponse.aValidationErrors) {
				Popup_FollowUp_History._showValidationErrorPopup(oResponse.aValidationErrors);
			} else {
				jQuery.json.errorPopup(oResponse, null, (bHideOnClose ? this.hide.bind(this) : null));
			}
		}
	}
});

Popup_FollowUp_History.FOLLOWUP_IMAGE_SOURCE						= '../admin/img/template/followup.png';
Popup_FollowUp_History.FOLLOWUP_RECURRING_IMAGE_SOURCE				= '../admin/img/template/followup_recurring.png';

Popup_FollowUp_History.CHANGE_FIELDS_TO_IGNORE	= 	{
														'id'						: true,
														'aModifyReasons'			: true,
														'modified_datetime'			: true, 
														'modified_employee_id'		: true,
														'oReassignReason'			: true,
														'followup_id'				: true,
														'assigned_employee_name'	: true
													};

Popup_FollowUp_History.DATE_FORMAT			= 'l jS M, Y, g:i A';
Popup_FollowUp_History.DATE_FORMAT_SHORT	= 'd/m/y g:i A';
Popup_FollowUp_History.NO_END_DATE			= '9999-12-31 23:59:59';

Popup_FollowUp_History.formatDateTime	= function(sDateTime, bShortVersion)
{
	var oDate	= new Date(Date.parse(sDateTime.replace(/-/g, '/')));
	return	$T.span(
				oDate.$format(
					bShortVersion ? Popup_FollowUp_History.DATE_FORMAT_SHORT : Popup_FollowUp_History.DATE_FORMAT
				)
			);
};

Popup_FollowUp_History.getChangeDescriptionElement	= function(sFieldName, oPrevious, oCurrent)
{
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
									Popup_FollowUp_History.formatDateTime(oPrevious.due_datetime, true)
								),
								' to ',
								$T.span({class: 'popup-followup-view-history-item-to'},
									Popup_FollowUp_History.formatDateTime(oCurrent.due_datetime, true)
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
									(oPrevious.end_datetime == Popup_FollowUp_History.NO_END_DATE ? 'No End Date' : Popup_FollowUp_History.formatDateTime(oPrevious.end_datetime, true))
								),
								' to ',
								$T.span({class: 'popup-followup-view-history-item-to'},
									(oCurrent.end_datetime == Popup_FollowUp_History.NO_END_DATE ? 'No End Date' : Popup_FollowUp_History.formatDateTime(oCurrent.end_datetime, true))
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

