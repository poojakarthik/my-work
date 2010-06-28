
var Popup_FollowUp_Reassign	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iFollowUpId, iFollowUpRecurringId, fnOnFinish)
	{
		$super(35);
		
		this._iFollowUpId					= iFollowUpId;
		this._iFollowUpRecurringId			= iFollowUpRecurringId;
		this._fnOnFinish					= fnOnFinish;
		
		if (this._iFollowUpId)
		{
			this._sType	= 'Follow-Up';
		}
		else if (this._iFollowUpRecurringId)
		{
			this._sType	= 'Recurring Follow-Up';	
		}
		else
		{
			Reflex_Popup.alert('ERROR: This should not occur, follow-up has neither id or recurring id.');
			return;
		}
		
		this._buildUI();
	},
	
	_buildUI	: function(oResponse)
	{
		// Create the list of closures
		var oSelect			= new Control_Field_Select('Employee');
		oSelect.setPopulateFunction(Employee.getAllAsSelectOptions.bind(Employee));
		oSelect.setVisible(true);
		oSelect.setEditable(true);
		oSelect.setMandatory(true);
		oSelect.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._oSelect	= oSelect;
		
		var oReasonSelect	= new Control_Field_Select('Reason');
		oReasonSelect.setPopulateFunction(FollowUp_Reassign_Reason.getAllAsSelectOptions.bind(FollowUp_Reassign_Reason));
		oReasonSelect.setVisible(true);
		oReasonSelect.setEditable(true);
		oReasonSelect.setMandatory(true);
		oReasonSelect.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._oReasonSelect	= oReasonSelect;
		
		// Build content
		this._oContent	= 	$T.div({class: 'popup-followup-reassign'},
								$T.div({class: 'popup-followup-reassign-reason'},
									$T.div('Please choose an employee to assign the ' + this._sType + ' to:'),
									$T.div({class: 'popup-followup-reassign-employee-list'},
										oSelect.getElement()
									),
									$T.div('Please specify a reason why you are reassigning the ' + this._sType + ':'),
									$T.div({class: 'popup-followup-reassign-reason-list'},
										oReasonSelect.getElement()
									)
								),
								$T.div({class: 'popup-followup-reassign-buttons'},
									$T.button({class: 'icon-button'},
										$T.span('Reassign ' + this._sType)
									),
									$T.button({class: 'icon-button'},
										$T.span('Cancel')
									)
								)
							);
		
		// Button events
		var oCloseButton	= this._oContent.select('div.popup-followup-reassign-buttons > button.icon-button').first();
		oCloseButton.observe('click', this._doClose.bind(this));
		
		var oCancelButton	= this._oContent.select('div.popup-followup-reassign-buttons > button.icon-button').last();
		oCancelButton.observe('click', this.hide.bind(this));
					
		// Popup setup
		this.setTitle('Reassign ' + this._sType);
		this.setIcon(Popup_FollowUp_Reassign.ICON_IMAGE_SOURCE);
		this.setContent(this._oContent);
		this.display();
	},
	
	_ajaxError	: function(oResponse)
	{
		if (this.oLoading)
		{
			this.oLoading.hide();
			delete this.oLoading;
		}
		
		var oConfig	= {sTitle: 'Error'};
		
		if (oResponse.Message)
		{
			Reflex_Popup.alert(oResponse.Message, oConfig);
		}
		else if (oResponse.ERROR)
		{
			Reflex_Popup.alert(oResponse.ERROR, oConfig);
		}
	},
	
	_doClose	: function(oEvent, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Make the reassign request
			if (this._oSelect.validate() && this._oReasonSelect.validate())
			{
				// Show loading
				this.oLoading	= new Reflex_Popup.Loading('Assigning...');
				this.oLoading.display();
				
				var iEmployeeId			= this._oSelect.getElementValue();
				var iReassignReasonId	= this._oReasonSelect.getElementValue();
				
				if (this._iFollowUpId)
				{
					var fnJSON	= 	jQuery.json.jsonFunction(
										this._doClose.bind(this, null), 
										this._ajaxError.bind(this), 
										'FollowUp', 
										'reassignFollowUp'
									);
					fnJSON(this._iFollowUpId, iEmployeeId, iReassignReasonId);
				}
				else if (this._iFollowUpRecurringId)
				{
					var fnJSON	= 	jQuery.json.jsonFunction(
										this._doClose.bind(this, null), 
										this._ajaxError.bind(this), 
										'FollowUp', 
										'reassignRecurringFollowUp'
									);
					fnJSON(this._iFollowUpRecurringId, iEmployeeId, iReassignReasonId);
				}
			}
			else
			{
				Reflex_Popup.alert('Please choose an employee to reassign the ' + this._sType + ' to and a reason for doing so.');
			}
		}
		else if (oResponse.Success)
		{
			// Success, handle response
			if (this.oLoading)
			{
				this.oLoading.hide();
				delete this.oLoading;
			}
			
			if (this._fnOnFinish)
			{
				this._fnOnFinish();
			}
			
			this.hide();
		}
		else
		{
			// Error
			this._ajaxError(oResponse);
		}
	}
});

// Image paths
Popup_FollowUp_Reassign.ICON_IMAGE_SOURCE 		= '../admin/img/template/user_edit.png';
