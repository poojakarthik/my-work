
var Popup_FollowUp_Close	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iFollowUpClosureTypeId, iFollowUpId, iFollowUpRecurringId, iFollowUpRecurringIteration, fnOnFinish)
	{
		$super(30);
		
		this._iFollowUpClosureTypeId		= iFollowUpClosureTypeId;
		this._iFollowUpId					= iFollowUpId;
		this._iFollowUpRecurringId			= iFollowUpRecurringId;
		this._iFollowUpRecurringIteration	= iFollowUpRecurringIteration;
		this._fnOnFinish					= fnOnFinish;
		
		switch (this._iFollowUpClosureTypeId)
		{
			case $CONSTANT.FOLLOWUP_CLOSURE_TYPE_COMPLETED:
				this._sPurpose		= 'Close';
				this._sPurposeIng	= 'Clos';
				break;
			case $CONSTANT.FOLLOWUP_CLOSURE_TYPE_DISMISSED:
				this._sPurpose		= 'Dismiss';
				this._sPurposeIng	= 'Dismiss';
				break;
			default:
				Reflex_Popup.alert('ERROR: This should not occur, incorrect follow-up closure type provided.');
				return;
		}
		
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
		var oSelect			= new Control_Field_Select('Reason');
		oSelect.setPopulateFunction(FollowUp_Closure.getForClosureTypeAsSelectOptions.curry(this._iFollowUpClosureTypeId));
		oSelect.setVisible(true);
		oSelect.setEditable(true);
		oSelect.setMandatory(true);
		oSelect.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._oSelect	= oSelect;
		
		// Build content
		this._oContent	= 	$T.div({class: 'popup-followup-close'},
								$T.div({class: 'popup-followup-close-reason'},
									$T.div('Please choose a reason for ' + this._sPurposeIng + 'ing this ' + this._sType + ':'),
									$T.div({class: 'popup-followup-close-reason-list'},
										oSelect.getElement()
									)
								),
								$T.div({class: 'popup-followup-close-buttons'},
									$T.button({class: 'icon-button'},
										$T.span(this._sPurpose + ' ' + this._sType)
									),
									$T.button({class: 'icon-button'},
										$T.span('Cancel')
									)
								)
							);
		
		// Button events
		var oCloseButton	= this._oContent.select('div.popup-followup-close-buttons > button.icon-button').first();
		oCloseButton.observe('click', this._doClose.bind(this));
		
		var oCancelButton	= this._oContent.select('div.popup-followup-close-buttons > button.icon-button').last();
		oCancelButton.observe('click', this.hide.bind(this));
					
		// Popup setup
		this.setTitle(this._sPurpose + ' ' + this._sType);
		this.setIcon(Popup_FollowUp_Close.ICON_IMAGE_SOURCE);
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
			// Make the close request
			if (this._oSelect.validate())
			{
				// Show loading
				this.oLoading	= new Reflex_Popup.Loading(this._sPurposeIng + 'ing...');
				this.oLoading.display();
				
				var iFollowUpClosureId	= this._oSelect.getElementValue(); 
				
				if (this._iFollowUpId)
				{
					var fnJSON	= 	jQuery.json.jsonFunction(
										this._doClose.bind(this, null), 
										this._ajaxError.bind(this), 
										'FollowUp', 
										'closeFollowUp'
									);
					fnJSON(this._iFollowUpId, iFollowUpClosureId);
				}
				else if (this._iFollowUpRecurringId)
				{
					var fnJSON	= 	jQuery.json.jsonFunction(
										this._doClose.bind(this, null), 
										this._ajaxError.bind(this), 
										'FollowUp', 
										'closeRecurringFollowUpIteration'
									);
					fnJSON(this._iFollowUpRecurringId, iFollowUpClosureId, this._iFollowUpRecurringIteration);
				}
			}
			else
			{
				Reflex_Popup.alert('Please choose a reason before ' + this._sPurposeIng + 'ing the ' + this._sType);
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
Popup_FollowUp_Close.ICON_IMAGE_SOURCE 		= '../admin/img/template/delete.png';
