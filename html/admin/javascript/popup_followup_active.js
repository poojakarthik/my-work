

var Popup_FollowUp_Active	= Class.create(Reflex_Popup,
{
	initialize	: function($super)
	{
		$super(73);
		
		this._iWindowResizeCounter	= 0;
		this._buildUI();
		this._iCurrentHeightLimit	= this._getHeightLimit();
		this._refresh();
	},

	dragEnd	: function($super, oEvent)
	{
		$super(oEvent);
		this._windowResizeEvent();
	},
	
	_buildUI	: function()
	{
		// All good
		this._oContent	= 	$T.div({class: 'popup-followup-active'},
								this._buildSectionContent(Popup_FollowUp_Active.SECTION_OVERDUE),
								this._buildSectionContent(Popup_FollowUp_Active.SECTION_TODAY),
								this._buildSectionContent(Popup_FollowUp_Active.SECTION_NEXT_WEEK),
								$T.div({class: 'popup-followup-active-buttons'},
									$T.button({class: 'icon-button'},
										$T.img({src: Popup_FollowUp_Active.FOLLOWUP_IMAGE_SOURCE, alt: 'Manage Follow-Ups', title: 'Manage Follow-Ups'}),
										'Manage Follow-Ups'
									),
									$T.button({class: 'icon-button'},
										$T.img({src: Popup_FollowUp_Active.FOLLOWUP_RECURRING_IMAGE_SOURCE, alt: 'Manage Recurring Follow-Ups', title: 'Manage Recurring Follow-Ups'}),
										'Manage Recurring Follow-Ups'
									)
								)
							);
				
		// Footer button events
		var oFollowUpButton	= this._oContent.select('button.icon-button').first();
		oFollowUpButton.observe('click', this._goToPage.bind(this, 'Manage', null, false));
		var oFollowUpRecurringButton	= this._oContent.select('button.icon-button').last();
		oFollowUpRecurringButton.observe('click', this._goToPage.bind(this, 'ManageRecurring', null, false));

		// Window resize event
		Event.observe(window, 'resize', this._windowResizeEvent.bind(this));
		
		this.setTitle('Active Follow-Ups');
		this.addCloseButton();
		this.setIcon(Popup_FollowUp_Active.FOLLOWUP_IMAGE_SOURCE);
		this.setContent(this._oContent);
		this.display();
	},
	
	_refresh	: function(oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Get active followups
			var fnGetFollowUps	= 	jQuery.json.jsonFunction(
										this._refresh.bind(this), 
										this._ajaxError.bind(this, true),
										'FollowUp', 
										'getDataSetForAuthenticatedEmployee'
									);
			
			// Build filter object for request
			var oEndDate	= new Date();
			oEndDate.shift(8, 'days');
			var sEndDate	= oEndDate.$format('Y-m-d 00:00');
			var oFilter		= 	{
									due_datetime	: {mFrom: null, mTo: sEndDate},
									status			: 'ACTIVE',
									now				: Math.floor(new Date().getTime() / 1000)
								};
			
			fnGetFollowUps(false, null, null, {due_datetime: 'ASC'}, oFilter, Popup_FollowUp_Active.MAX_SUMMARY_CHARACTERS);
			
			// Call manual refresh on the followup link
			FollowUpLink.refresh();
		}
		else if (oResponse.Success)
		{
			// Clear rows
			var aRows	= this._oContent.select('table > tbody > tr');
			for (var i = 0; i < aRows.length; i++)
			{
				aRows[i].remove();
			}
			
			// Add rows
			var iTotalCount	= 0;
			var oNow		= new Date();
			
			var aCounts											= {};
			aCounts[Popup_FollowUp_Active.SECTION_OVERDUE]		= {iTotal: 0, iAdded: 0};
			aCounts[Popup_FollowUp_Active.SECTION_TODAY]		= {iTotal: 0, iAdded: 0};
			aCounts[Popup_FollowUp_Active.SECTION_NEXT_WEEK]	= {iTotal: 0, iAdded: 0};
			
			var oFollowUp		= null;
			var sSection		= null;
			for (var i = 0; i < oResponse.aRecords.length; i++)
			{
				oFollowUp	= oResponse.aRecords[i];
				sSection	= null;
				if (oFollowUp.status == 'Overdue')
				{
					// Overdue
					sSection	= Popup_FollowUp_Active.SECTION_OVERDUE;
				}
				else
				{
					// Check if the date is today or within the next 7 days
					var oDate	= new Date(Date.parse(oFollowUp.due_datetime.replace(/-/g, '/')));
					if ((oDate.getDate() == oNow.getDate()) && (oDate.getMonth() == oNow.getMonth()) && (oDate.getFullYear() == oNow.getFullYear()))
					{
						// Today
						sSection	= Popup_FollowUp_Active.SECTION_TODAY;
					}
					else
					{
						// Next 7 Days
						sSection	= Popup_FollowUp_Active.SECTION_NEXT_WEEK;
					}
				}
				
				aCounts[sSection].iTotal++;
				iTotalCount++;
				
				if (this.container.clientHeight < this._iCurrentHeightLimit)
				{
					// Only add if there is enough room in the section
					if (this._addFollowUpToSection(sSection, oFollowUp))
					{
						aCounts[sSection].iAdded++;
					}
				}
			}
			
			// Put empty table rows in
			for (var sSection in aCounts)
			{
				var sClass	= Popup_FollowUp_Active.getClassFromSection(sSection);
				if (aCounts[sSection].iTotal == 0)
				{
					// Add empty row
					var oTBody	= this._oContent.select('tbody#section-' + sClass).first() 
					oTBody.appendChild(
						$T.tr({class: 'popup-followup-active-row'},
							$T.td({class: 'popup-followup-active-empty'},
								Popup_FollowUp_Active.EMPTY_SECTION
							)
						)
					);
				}
				else if (aCounts[sSection].iAdded == 0)
				{
					// Add 'click to view more' row
					var oTBody	= this._oContent.select('tbody#section-' + Popup_FollowUp_Active.getClassFromSection(sSection)).first()
					oTBody.appendChild(
						$T.tr({class: 'popup-followup-active-row'},
							$T.td({class: 'popup-followup-active-empty-clickformore'},
								$T.span('There are more Follow-Ups that have not been shown. '),
								$T.a({href: this._goToPage('Manage', '', true)},
									'Click here to view them'
								)
							)
						)
					);
				}
				
				// Update the section summary
				var oSummaryDiv			= this._oContent.select('div.section-header-summary-' + sClass).first();
				oSummaryDiv.innerHTML	= 'Showing ' + aCounts[sSection].iAdded + ' of ' + aCounts[sSection].iTotal;
				
				if (aCounts[sSection].iAdded < aCounts[sSection].iTotal)
				{
					// Add a link to view more
					oSummaryDiv.appendChild(
						$T.span(
							' - ',
							$T.a({href: this._goToPage('Manage', '', true)},
								'View all Active Follow-Ups'
							)
						)
					);
				}
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
		}
	},
	
	_buildSectionContent	: function(sSection)
	{
		var sClass	= Popup_FollowUp_Active.getClassFromSection(sSection);
		return $T.div({class: 'section'},
			$T.div({class: 'section-header'},
				$T.div({class: 'section-header-title'},
					$T.span(sSection)
				),
				$T.div({class: 'section-header-options'},
					$T.div({class: 'section-header-summary section-header-summary-' + sClass})
				)
			),
			$T.div({class: 'section-content section-content-fitted'},
				$T.table(
					$T.tbody({id: 'section-' + sClass},
						$T.tr({class: 'popup-followup-active-row'},
							$T.td({class: 'popup-followup-active-empty'},
								Popup_FollowUp_Active.EMPTY_SECTION
							)
						)
					)
				)
			)
		);
	},
		
	_addFollowUpToSection	: function(sSection, oFollowUp)
	{
		// Section specific details
		var mDate				= '';
		var sDateClass			= '';
		var oFollowUpDueDate	= new Date(Date.parse(oFollowUp.due_datetime.replace(/-/g, '/')));
		var iNow				= new Date().getTime();
		
		switch (sSection)
		{
			case Popup_FollowUp_Active.SECTION_OVERDUE:
				// Date - how many days (or weeks if over a week) ago
				var iDiff	= iNow - oFollowUpDueDate.getTime();
				var iWeek	= Math.floor(iDiff / Popup_FollowUp_Active.MILLISECOND_WEEK);
				var iDay	= Math.floor(iDiff / Popup_FollowUp_Active.MILLISECOND_DAY);
				var iHour	= Math.floor(iDiff / Popup_FollowUp_Active.MILLISECOND_HOUR);
				var iMinute	= Math.floor(iDiff / Popup_FollowUp_Active.MILLISECOND_MINUTE);
				var iSecond	= Math.floor(iDiff / Popup_FollowUp_Active.MILLISECOND_SECOND);
				
				if (iWeek == 0)
				{
					if (iDay == 0)
					{
						if (iHour == 0)
						{
							if (iMinute == 0)
							{
								mDate	= iSecond + (iSecond == 1 ? ' sec' : ' secs');
							}
							else
							{
								mDate	= iMinute + (iMinute == 1 ? ' min' : ' mins');
							}
						}
						else
						{
							mDate	= iHour + (iHour == 1 ? ' hour' : ' hours');
						}
					}
					else
					{
						mDate	= iDay + (iDay == 1 ? ' day' : ' days');
					}
				}
				else
				{
					mDate	= iWeek + (iWeek == 1 ? ' week' : ' weeks');
				}
				
				sDateClass	= ' followup-status-overdue';
				break;
			case Popup_FollowUp_Active.SECTION_TODAY:
				// Date - time of day
				mDate	= oFollowUpDueDate.$format('g:i A');
				break;
			case Popup_FollowUp_Active.SECTION_NEXT_WEEK:
				// Date - day of week, day of month and short month
				mDate	= 	$T.div(
								$T.div(oFollowUpDueDate.$format('l')),
								$T.div({class: 'popup-followup-active-date-subdate'},
									oFollowUpDueDate.$format('jS M')
								)
							);
				break;
		}
		
		// Build the row
		var oDateTD		= 	$T.td({class: 'popup-followup-active-date' + sDateClass},
								$T.div(mDate)
							);
		
		var sId				= (oFollowUp.followup_id ? oFollowUp.followup_id : 'r_' + oFollowUp.followup_recurring_id);
		var oEventElement	= oDateTD.select('div').first();
		var sDate			= oFollowUpDueDate.$format('l jS M Y g:i A');
		oEventElement.observe('mouseover', this._showFullDate.bind(this, sDate, sId));
		oDateTD.observe('mouseout', this._hideFullDate.bind(this));
		
		var oDetailTD	= 	$T.td({class: 'popup-followup-active-detail'},
								Popup_FollowUp_Active.getFollowUpDetailsElement(oFollowUp)
							);
		var oSummaryTD	= 	$T.td({class: 'popup-followup-active-summary'},
								$T.div(
									Popup_FollowUp_Active._getTypeElement(oFollowUp.followup_type_id),
									$T.span(oFollowUp.followup_category_label)
								),
								$T.div({class: 'popup-followup-active-summary-text'},
									oFollowUp.summary
								)
							);
		var oActionTD	= 	$T.td({class: 'popup-followup-active-actions'},
								this._getActions(oFollowUp)
							);
		var oTBody		= this._oContent.select('tbody#section-' + Popup_FollowUp_Active.getClassFromSection(sSection)).first();
		var oTR			= 	$T.tr({class: 'popup-followup-active-row'},
								oDateTD,
								oDetailTD,
								oSummaryTD,
								oActionTD					
							);
		oTBody.appendChild(oTR);
		
		// Check that the height hasn't exceeded the limit
		if (this.container.clientHeight > this._iCurrentHeightLimit)
		{
			oTBody.removeChild(oTR);
			return false;
		}
		
		return true;
	},
	
	_showFullDate	: function(sDate, sId, oEvent)
	{
		if (!this._oFullDateElement)
		{
			// Create the full date preview div
			this._oFullDateElement	= $T.div({class: 'popup-followup-active-date-full'});
			this._oFullDateElement.hide();
			document.body.appendChild(this._oFullDateElement);
		}
		
		// Show it if not already visible for the given followup
		if (sId != this._sCurrentFullDateItemId)
		{
			this._oFullDateElement.innerHTML	= sDate;
			this._oFullDateElement.style.left	= (oEvent.clientX + 20) + 'px';
			this._oFullDateElement.style.top	= (oEvent.clientY + window.pageYOffset - 10) + 'px';
			this._sCurrentFullDateItemId		= sId;
			this._oFullDateElement.show();
		}
	},
	
	_hideFullDate	: function(oEvent)
	{
		if (this._sCurrentFullDateItemId != null)
		{
			// Make sure the mouse has left the td containing the date
			var oNode	= oEvent.explicitOriginalTarget;
			var bCancel	= (oNode.hasClassName ? oNode.hasClassName('popup-followup-active-date') : false);
			while(!bCancel && oNode != document.body)
			{
				oNode	= oNode.parentNode;
				if (oNode.hasClassName && oNode.hasClassName('popup-followup-active-date'))
				{
					bCancel	= true;
				}
			}
			
			if (bCancel)
			{
				return;
			}
			
			this._oFullDateElement.hide();
			this._sCurrentFullDateItemId	= null;
		}
	},
	
	_getActions	: function(oFollowUp)
	{
		var oUL	= $T.ul({class: 'reset horizontal popup-followup-active-actions'});
		
		var oClose	= $T.img({src: Popup_FollowUp_Active.ACTION_CLOSE_IMAGE_SOURCE, alt: 'Close the Follow-Up', title: 'Close the Follow-Up'});
		oClose.observe('click', this._closeFollowUp.bind(this, oFollowUp, $CONSTANT.FOLLOWUP_CLOSURE_TYPE_COMPLETED));
		oUL.appendChild($T.li(oClose));
		
		var oDismiss	= $T.img({src: Popup_FollowUp_Active.ACTION_DISMISS_IMAGE_SOURCE, alt: 'Dismiss the Follow-Up', title: 'Dismiss the Follow-Up'});
		oDismiss.observe('click', this._closeFollowUp.bind(this, oFollowUp, $CONSTANT.FOLLOWUP_CLOSURE_TYPE_DISMISSED));
		oUL.appendChild($T.li(oDismiss));
		
		var oEditDueDate	= $T.img({src: Popup_FollowUp_Active.ACTION_EDIT_DATE_IMAGE_SOURCE, alt: 'Edit Due Date', title: 'Edit Due Date'});
		oEditDueDate.observe('click', this._editDueDate.bind(this, oFollowUp));
		oUL.appendChild($T.li(oEditDueDate));
		
		var oInvAndPay	= 	$T.a({href: 'flex.php/Account/InvoicesAndPayments/?Account.Id=' + oFollowUp.details.account_id},
								$T.img({src: Popup_FollowUp_Active.ACTION_INV_PAYMENTS_IMAGE_SOURCE, alt: 'Invoices & Payments', title: 'Invoices & Payments'})
							);
		oUL.appendChild($T.li(oInvAndPay));
		
		if (oFollowUp.details && oFollowUp.details.account_id)
		{
			// Leave visible
		}
		else
		{
			oInvAndPay.toggle();
		}
		
		if (oFollowUp.followup_recurring_id)
		{
			oEditDueDate.toggle();
		}
		
		return oUL;
	},
	
	_closeFollowUp	: function(oFollowUp, iFollowUpClosureTypeId)
	{
		var oPopup	= 	new Popup_FollowUp_Close(
							iFollowUpClosureTypeId,
							oFollowUp.followup_id, 
							oFollowUp.followup_recurring_id,
							oFollowUp.followup_recurring_iteration,
							this._refresh.bind(this)
						);
	},
	
	_editDueDate	: function(oFollowUp)
	{
		var oPopup	= 	new Popup_FollowUp_Due_Date(
							oFollowUp.followup_id, 
							oFollowUp.due_datetime,
							this._refresh.bind(this)
						);
	},
	
	_goToPage	: function(sMethod, sParameters, bReturnURL)
	{
		sParameters		= (sParameters ? '?' + sParameters : '');
		var sURL		= 'reflex.php/FollowUp/' + sMethod + '/' + sParameters;
		
		if (bReturnURL)
		{
			return sURL;
		}
		else
		{
			window.location	= sURL;
		}
	},
	
	_windowResizeTimeout	: function(iExpectedCount)
	{
		if (this._iWindowResizeCounter == iExpectedCount)
		{
			this._refresh();
		}
	},
	
	_windowResizeEvent	: function()
	{
		var iHeightLimit	= this._getHeightLimit();
		if (iHeightLimit != this._iCurrentHeightLimit)
		{
			this._iCurrentHeightLimit	= iHeightLimit;
			this._iWindowResizeCounter++;
			setTimeout(this._windowResizeTimeout.bind(this, this._iWindowResizeCounter), Popup_FollowUp_Active.WINDOW_RESIZE_TIMEOUT);
		}
	},
	
	_getHeightLimit	: function()
	{
		return window.innerHeight - this.container.offsetTop - 50;
	}
});






Popup_FollowUp_Active.MILLISECOND_SECOND		= 1000;
Popup_FollowUp_Active.MILLISECOND_MINUTE		= Popup_FollowUp_Active.MILLISECOND_SECOND * 60;
Popup_FollowUp_Active.MILLISECOND_HOUR			= Popup_FollowUp_Active.MILLISECOND_MINUTE * 60;
Popup_FollowUp_Active.MILLISECOND_DAY			= Popup_FollowUp_Active.MILLISECOND_HOUR * 24;
Popup_FollowUp_Active.MILLISECOND_WEEK			= Popup_FollowUp_Active.MILLISECOND_DAY * 7;

Popup_FollowUp_Active.SECTION_OVERDUE			= 'Overdue';
Popup_FollowUp_Active.SECTION_TODAY				= 'Today';
Popup_FollowUp_Active.SECTION_NEXT_WEEK			= 'Next 7 Days';

Popup_FollowUp_Active.EMPTY_SECTION				= 'There are no Follow-Ups for this period';

Popup_FollowUp_Active.FOLLOWUP_IMAGE_SOURCE				= '../admin/img/template/followup.png';
Popup_FollowUp_Active.FOLLOWUP_RECURRING_IMAGE_SOURCE	= '../admin/img/template/followup_recurring.png';

Popup_FollowUp_Active.ACTION_CLOSE_IMAGE_SOURCE			= '../admin/img/template/approve.png';
Popup_FollowUp_Active.ACTION_DISMISS_IMAGE_SOURCE		= '../admin/img/template/decline.png';
Popup_FollowUp_Active.ACTION_EDIT_DATE_IMAGE_SOURCE		= '../admin/img/template/edit_date.png';
Popup_FollowUp_Active.ACTION_INV_PAYMENTS_IMAGE_SOURCE	= '../admin/img/template/invoices_payments.png';
Popup_FollowUp_Active.ACTION_RECURRING_IMAGE_SOURCE		= '../admin/img/template/followup_recurring.png';

Popup_FollowUp_Active.TYPE_NOTE_IMAGE_SOURCE					= '../admin/img/template/followup_note.png';
Popup_FollowUp_Active.TYPE_ACTION_IMAGE_SOURCE					= '../admin/img/template/followup_action.png';
Popup_FollowUp_Active.TYPE_TICKET_CORRESPONDENCE_IMAGE_SOURCE	= '../admin/img/template/tickets.png';

Popup_FollowUp_Active.DETAILS_ACCOUNT_IMAGE_SOURCE			= '../admin/img/template/account.png';
Popup_FollowUp_Active.DETAILS_ACCOUNT_CONTACT_IMAGE_SOURCE	= '../admin/img/template/contact_small.png';
Popup_FollowUp_Active.DETAILS_ACCOUNT_SERVICE_IMAGE_SOURCE	= '../admin/img/template/service.png';
Popup_FollowUp_Active.DETAILS_TICKET_IMAGE_SOURCE			= Popup_FollowUp_Active.TYPE_TICKET_CORRESPONDENCE_IMAGE_SOURCE;
//Popup_FollowUp_Active.DETAILS_TICKET_CONTACT_IMAGE_SOURCE	= '../admin/img/template/account.png';

Popup_FollowUp_Active.WINDOW_RESIZE_TIMEOUT		= 500;
Popup_FollowUp_Active.MAX_SUMMARY_CHARACTERS	= 130;

Popup_FollowUp_Active._getTypeElement	= function(iType)
{
	if (Flex.Constant.arrConstantGroups.followup_type)
	{
		var sAlt	= Flex.Constant.arrConstantGroups.followup_type[iType].Name;
		var sImgSrc	= '';
		
		switch (iType)
		{
			case $CONSTANT.FOLLOWUP_TYPE_NOTE:
				sImgSrc	= Popup_FollowUp_Active.TYPE_NOTE_IMAGE_SOURCE;
				break;
			case $CONSTANT.FOLLOWUP_TYPE_ACTION:
				sImgSrc	= Popup_FollowUp_Active.TYPE_ACTION_IMAGE_SOURCE;
				break;
			case $CONSTANT.FOLLOWUP_TYPE_TICKET_CORRESPONDENCE:
				sImgSrc	= Popup_FollowUp_Active.TYPE_TICKET_CORRESPONDENCE_IMAGE_SOURCE;
				break;
		}
		
		return $T.img({src: sImgSrc, alt: sAlt, title: sAlt});
	}
	
	return 'Error';
};

Popup_FollowUp_Active.getFollowUpDetailsElement	= function(oFollowUp)
{
	var oDiv		= $T.div();
	var oDetails	= oFollowUp.details;
	
	if (oDetails)
	{
		switch (oFollowUp.followup_type_id)
		{
			case $CONSTANT.FOLLOWUP_TYPE_ACTION:
			case $CONSTANT.FOLLOWUP_TYPE_NOTE:
				// Account, service or contact info
				if (oDetails.customer_group)
				{
					oDiv.appendChild(Popup_FollowUp_Active.getCustomerGroupLink(oDetails.account_id, oDetails.customer_group));
				}
				
				if (oDetails.account_id && oDetails.account_name)
				{
					oDiv.appendChild(Popup_FollowUp_Active.getAccountLink(oDetails.account_id, oDetails.account_name));
				}
				
				if (oDetails.service_id && oDetails.service_fnn)
				{
					oDiv.appendChild(Popup_FollowUp_Active.getServiceLink(oDetails.service_id, oDetails.service_fnn));
				}
				
				if (oDetails.contact_id && oDetails.contact_name)
				{
					oDiv.appendChild(Popup_FollowUp_Active.getAccountContactLink(oDetails.contact_id, oDetails.contact_name));
				}
				break;
			case $CONSTANT.FOLLOWUP_TYPE_TICKET_CORRESPONDENCE:
				// Account or ticket contact info
				if (oDetails.customer_group)
				{
					oDiv.appendChild(Popup_FollowUp_Active.getCustomerGroupLink(oDetails.account_id, oDetails.customer_group));
				}
				
				if (oDetails.account_id && oDetails.account_name)
				{
					oDiv.appendChild(Popup_FollowUp_Active.getAccountLink(oDetails.account_id, oDetails.account_name));
				}
				
				if (oDetails.account_id && oDetails.ticket_id && oDetails.ticket_contact_name)
				{
					oDiv.appendChild(Popup_FollowUp_Active.getTicketLink(oDetails.ticket_id, oDetails.account_id, oDetails.ticket_contact_name));
				}
				break;
		}
	}
	
	return oDiv;
};

Popup_FollowUp_Active.getCustomerGroupLink	= function(iAccountId, sName)
{
	return 	$T.div(sName);
};

Popup_FollowUp_Active.getAccountLink	= function(iId, sName)
{
	var sUrl	= 'flex.php/Account/Overview/?Account.Id=' + iId;
	return 	$T.div({class: 'popup-followup-active-detail-subdetail'},
				$T.img({src: Popup_FollowUp_Active.DETAILS_ACCOUNT_IMAGE_SOURCE}),
				$T.a({href: sUrl},
					sName + ' (' + iId + ')'
				)
			);
};

Popup_FollowUp_Active.getAccountContactLink	= function(iId, sName)
{
	return 	$T.div({class: 'popup-followup-active-detail-subdetail'},
				$T.img({src: Popup_FollowUp_Active.DETAILS_ACCOUNT_CONTACT_IMAGE_SOURCE}),
				$T.a({href: 'reflex.php/Contact/View/' + iId + '/'},
					sName
				)
			);
};

Popup_FollowUp_Active.getServiceLink	= function(iId, sFNN)
{
	return 	$T.div({class: 'popup-followup-active-detail-subdetail'},
				$T.img({src: Popup_FollowUp_Active.DETAILS_ACCOUNT_SERVICE_IMAGE_SOURCE}),
				$T.a({href: 'flex.php/Service/View/?Service.Id=' + iId},
					'FNN : ' + sFNN
				)
			);
};

Popup_FollowUp_Active.getTicketLink	= function(iTicketId, iAccountId, sContact)
{
	return 	$T.div({class: 'popup-followup-active-detail-subdetail'},
				$T.img({src: Popup_FollowUp_Active.DETAILS_TICKET_IMAGE_SOURCE}),
				$T.a({href: 'reflex.php/Ticketing/Ticket/' + iTicketId + '/View/?Account=' + iAccountId},
					'Ticket ' + iTicketId + ' (' + sContact + ')'
				)
			);
};

Popup_FollowUp_Active.getClassFromSection	= function(sSection)
{
	return sSection.replace(/\s/g, '_').toLowerCase();
};

