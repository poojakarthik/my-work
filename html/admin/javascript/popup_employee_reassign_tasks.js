
var Popup_Employee_Reassign_Tasks	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iEmployeeId, fnCallback, bCloseIfNone, sMessage)
	{
		$super(35);
		
		this._iEmployeeId	= iEmployeeId;
		this._fnCallback	= fnCallback;
		this._bCloseIfNone	= bCloseIfNone;
		this._sMessage		= (sMessage ? sMessage : Popup_Employee_Reassign_Tasks.DEFAULT_MESSAGE);
		
		if (!bCloseIfNone)
		{
			this._oLoading	= new Reflex_Popup.Loading('');
			this._oLoading.display();
		}
		
		// Chain of ajax requests, ending in interface creation
		this._getFollowUpCount(
			this._getTicketCount.bind(
				this,
				this._buildUI.bind(this)
			)
		);
	},
	
	_getFollowUpCount	: function(fnCallback, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Get the list of fups for the employee
			var fnGetFollowUps	=	jQuery.json.jsonFunction(
										this._getFollowUpCount.bind(this, fnCallback),
										this._ajaxError.bind(this),
										'Employee',
										'getCountActiveFollowUps'
									);
			fnGetFollowUps(this._iEmployeeId, true);
		}
		else if (oResponse.Success)
		{
			// Handle response
			this._iFollowUpCount	= oResponse.iCount;
			if (fnCallback)
			{
				fnCallback();
			}
		}
		else
		{
			// Error
			this._ajaxError(oResponse);
		}
	},
	
	_getTicketCount	: function(fnCallback, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Get the list of tickets for the employee
			var fnGetTickets	=	jQuery.json.jsonFunction(
										this._getTicketCount.bind(this, fnCallback),
										this._ajaxError.bind(this),
										'Employee',
										'getCountActiveTickets'
									);
			fnGetTickets(this._iEmployeeId, true);
		}
		else if (oResponse.Success)
		{
			// Handle response
			this._iTicketCount	= oResponse.iCount;
			if (fnCallback)
			{
				fnCallback();
			}
		}
		else
		{
			// Error
			this._ajaxError(oResponse);
		}
	},
	
	_buildUI	: function()
	{
		if (this._oLoading)
		{
			this._oLoading.hide();
			delete this._oLoading;
		}
		
		// Check for none, and 'if none' callback
		if ((this._iTicketCount == 0) && (this._iFollowUpCount == 0) && this._bCloseIfNone)
		{
			// Cancel build and call back
			if (this._fnCallback)
			{
				this._fnCallback();
			}
			return;
		}
		
		this._oTicketingUserControl		= Popup_Employee_Reassign_Tasks._getTicketingUserSelectControl();
		this._oFollowUpEmployeeControl	= Popup_Employee_Reassign_Tasks._getEmployeeSelectControl();
		this._oFollowUpReasonControl	= Popup_Employee_Reassign_Tasks._getReassignReasonControl();
		
		this._oContent	=	$T.div({class: 'popup-employee-reassign-tasks'},
								$T.div({class: 'popup-employee-reassign-tasks-message'},
									this._sMessage
								),
								$T.div({class: 'popup-employee-reassign-tasks-task-summary'},
									$T.span({class: 'popup-employee-reassign-tasks-task-label'},
										$T.span('Tickets '),
										$T.span('(' + this._iTicketCount + ')')
									),
									$T.div({class: 'popup-employee-reassign-tasks-tickets-selects'},
										$T.div(
											$T.span({class: 'popup-employee-reassign-tasks-select-label'},
												'Assign to'
											),
											this._oTicketingUserControl.getElement()
										)
									),
									$T.div({class: 'popup-employee-reassign-tasks-tickets-none no-data'},
										$T.span('No reassignment needed...')
									)
								),
								$T.div({class: 'popup-employee-reassign-tasks-task-summary'},
									$T.span({class: 'popup-employee-reassign-tasks-task-label'},
										$T.span('Follow-Ups '),
										$T.span('(' + this._iFollowUpCount + ')')
									),
									$T.div({class: 'popup-employee-reassign-tasks-followups-selects'},
										$T.div(
											$T.span({class: 'popup-employee-reassign-tasks-select-label'},
												'Assign to'
											),
											this._oFollowUpEmployeeControl.getElement()
										),
										$T.div(
											$T.span({class: 'popup-employee-reassign-tasks-select-label'},
												'Reason'
											),
											this._oFollowUpReasonControl.getElement()
										)
									),
									$T.div({class: 'popup-employee-reassign-tasks-followups-none no-data'},
										$T.span('No reassignment needed...')
									)
								),
								$T.div({class: 'reflex-popup-centred'},
									$T.button({class: 'icon-button'},
										'Apply'
									),
									$T.button({class: 'icon-button'},
										'Cancel'
									)
								)
							);
		
		// Button events
		var aButtons	= this._oContent.select('button.icon-button');
		aButtons[0].observe('click', this._apply.bindAsEventListener(this));
		aButtons[1].observe('click', this.hide.bind(this));
		
		var sBaseSelect	= 'div.popup-employee-reassign-tasks-';
		if (!this._iFollowUpCount)
		{
			// Show 'there are no fups' instead of controls
			this._oContent.select(sBaseSelect + 'followups-selects').first().hide();
			this._oContent.select(sBaseSelect + 'followups-none').first().show();
			this._oFollowUpEmployeeControl.setMandatory(false);
			this._oFollowUpReasonControl.setMandatory(false);
		}
		else
		{
			// Show the employee select & reason select
			this._oContent.select(sBaseSelect + 'followups-selects').first().show();
			this._oContent.select(sBaseSelect + 'followups-none').first().hide();
			this._oFollowUpEmployeeControl.setMandatory(true);
			this._oFollowUpReasonControl.setMandatory(true);
		}
		
		if (!this._iTicketCount)
		{
			// Show 'there are no tickets' instead of controls
			this._oContent.select(sBaseSelect + 'tickets-selects').first().hide();
			this._oContent.select(sBaseSelect + 'tickets-none').first().show();
			this._oTicketingUserControl.setMandatory(false);
		}
		else
		{
			// Show the employee select & reason select
			this._oContent.select(sBaseSelect + 'tickets-selects').first().show();
			this._oContent.select(sBaseSelect + 'tickets-none').first().hide();
			this._oTicketingUserControl.setMandatory(true);
		}
		
		// Hide the 'Apply' button when there are no tasks to reassign
		if (!this._iFollowUpCount && !this._iTicketCount)
		{
			aButtons[0].hide();
		}
		
		this.setTitle('Reassign Tickets & Follow-Ups');
		this.setContent(this._oContent);
		this.addCloseButton();
		this.display();
	},
	
	_apply	: function(oEvent, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			var fnReassign	=	jQuery.json.jsonFunction(
									this._apply.bind(this, null),
									this._ajaxError.bind(this),
									'Employee',
									'reassignAllTicketsAndFollowUps'
								);
			
			// Validate input
			var aErrors			= [];
			var iTicketingUser	= 	Popup_Employee_Reassign_Tasks._validateControl(
										this._oTicketingUserControl, 
										aErrors
									);
			var iFupEmployee	= 	Popup_Employee_Reassign_Tasks._validateControl(
										this._oFollowUpEmployeeControl, 
										aErrors
									);
			var iFupReason		= 	Popup_Employee_Reassign_Tasks._validateControl(
										this._oFollowUpReasonControl, 
										aErrors
									);
			
			if (aErrors.length)
			{
				Popup_Employee_Reassign_Tasks._showValidationErrors(aErrors);
				return;
			}
			
			// Show loading
			this._oLoading	= new Reflex_Popup.Loading('Assigning tasks');
			this._oLoading.display();
			
			// Make request
			fnReassign(
				this._iEmployeeId, 
				this._oTicketingUserControl.getElementValue(),
				this._oFollowUpEmployeeControl.getElementValue(),
				this._oFollowUpReasonControl.getElementValue()
			)
		}
		else if (oResponse.Success)
		{
			// Handle response
			if (this._oLoading)
			{
				this._oLoading.hide();
				delete this._oLoading;
			}
			
			this.hide();
			
			// Callback to complete
			if (this._fnCallback)
			{
				this._fnCallback();
			}
			else
			{
				Reflex_Popup.alert('All Tickets & Follow-Ups Successfully Reassigned');
			}
		}
		else
		{
			// Error
			this._ajaxError(oResponse);
		}
	},
	
	_ajaxError	: function(oResponse) {
		if (this._oLoading) {
			this._oLoading.hide();
			delete this._oLoading;
		}
		
		jQuery.json.errorPopup(oResponse);
	}
});

Popup_Employee_Reassign_Tasks.DEFAULT_MESSAGE	= 'Please specify Employees to assign to and a reason for the Follow-Up reassignment: ';

Popup_Employee_Reassign_Tasks._showValidationErrors	= function(aErrors)
{
	// Create a UL to list the errors and then show a reflex alert
	var oAlertDom	=	$T.div({class: 'validation-errors'},
							$T.div('There were errors in the required information: '),
							$T.ul(
								// Added here...
							)
						);
	var oUL	= oAlertDom.select('ul').first();
	for (var i = 0; i < aErrors.length; i++)
	{
		oUL.appendChild($T.li(aErrors[i]));
	}
	
	Reflex_Popup.alert(oAlertDom, {iWidth: 30});
};

Popup_Employee_Reassign_Tasks._validateControl	= function(oControl, aErrors)
{
	try
	{
		oControl.validate(false);
		return oControl.getElementValue();
	}
	catch (ex)
	{
		aErrors.push(ex);
		return false;
	}
};

Popup_Employee_Reassign_Tasks._getReassignReasonControl	= function()
{
	var oControl	= new Control_Field_Select('Reason');
	oControl.setPopulateFunction(
		FollowUp_Reassign_Reason.getActiveAsSelectOptions.bind(FollowUp_Reassign_Reason)
	);
	oControl.setVisible(true);
	oControl.setEditable(true);
	oControl.setMandatory(true);
	oControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
	return oControl;
};

Popup_Employee_Reassign_Tasks._getTicketingUserSelectControl	= function()
{
	var oControl	= new Control_Field_Select('Ticketing User');
	oControl.setPopulateFunction(Ticketing_User.getAllAsSelectOptions.bind(Ticketing_User));
	oControl.setVisible(true);
	oControl.setEditable(true);
	oControl.setMandatory(true);
	oControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
	return oControl;
};

Popup_Employee_Reassign_Tasks._getEmployeeSelectControl	= function()
{
	var oControl	= new Control_Field_Select('Employee');
	oControl.setPopulateFunction(Employee.getAllAsSelectOptions.bind(Employee));
	oControl.setVisible(true);
	oControl.setEditable(true);
	oControl.setMandatory(true);
	oControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
	return oControl;
};



