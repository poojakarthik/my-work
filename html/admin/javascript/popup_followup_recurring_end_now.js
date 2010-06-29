
var Popup_FollowUp_Recurring_End_Now	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iFollowUpId, fnOnFinish)
	{
		$super(30);
		
		this._fnOnFinish	= fnOnFinish;
		this._iFollowUpId	= iFollowUpId;
		
		this._buildUI();
	},
	
	_buildUI	: function()
	{
		var oReasonSelect	= new Control_Field_Select('Reason');
		oReasonSelect.setPopulateFunction(FollowUp_Recurring_Modify_Reason.getActiveAsSelectOptions.bind(FollowUp_Recurring_Modify_Reason));
		oReasonSelect.setVisible(true);
		oReasonSelect.setEditable(true);
		oReasonSelect.setMandatory(true);
		oReasonSelect.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._oReasonSelect	= oReasonSelect;
		
		// Build content
		this._oContent	= 	$T.div({class: 'popup-followup-recurring-dates'},
								$T.div({class: 'popup-followup-recurring-dates-reason'},
									$T.div('Please specify a reason why you are ending the Recurring Follow-Up:'),
									$T.div(this._oReasonSelect.getElement())
								),
								$T.div({class: 'popup-followup-recurring-dates-buttons'},
									$T.button({class: 'icon-button'},
										$T.span('Save')
									),
									$T.button({class: 'icon-button'},
										$T.span('Cancel')
									)
								)
							);
		
		// Button events
		var oSaveButton	= this._oContent.select('div.popup-followup-recurring-dates-buttons > button.icon-button').first();
		oSaveButton.observe('click', this._save.bind(this));
		
		var oCancelButton	= this._oContent.select('div.popup-followup-recurring-dates-buttons > button.icon-button').last();
		oCancelButton.observe('click', this.hide.bind(this));
					
		// Popup setup
		this.setTitle('End Recurring Follow-Up');
		this.setIcon(Popup_FollowUp_Recurring_End_Now.ICON_IMAGE_SOURCE);
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
		
		var oConfig	= {sTitle: 'Error', fnOnClose: (bHideOnClose ? this.hide.bind(this) : null)};
		
		if (oResponse.Message)
		{
			Reflex_Popup.alert(oResponse.Message, oConfig);
		}
		else if (oResponse.ERROR)
		{
			Reflex_Popup.alert(oResponse.ERROR, oConfig);
		}
	},
	
	_save	: function(oEvent, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Validate the reason
			try
			{
				this._oReasonSelect.validate(false);
			}
			catch (ex)
			{
				// Validation error, show in popup
				Reflex_Popup.alert(ex);
				return;
			}
			
			// Show loading
			this.oLoading	= new Reflex_Popup.Loading('Ending the Follow-Up...');
			this.oLoading.display();
			
			// Make request
			var fnJSON	= 	jQuery.json.jsonFunction(
								this._save.bind(this, null), 
								this._ajaxError.bind(this, false), 
								'FollowUp_Recurring', 
								'endNow'
							);
			fnJSON(this._iFollowUpId, this._oReasonSelect.getElementValue());
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
			this._ajaxError(false, oResponse);
		}
	}
});

Popup_FollowUp_Recurring_End_Now.DATE_FORMAT	= 'Y-m-d H:i:s';

Popup_FollowUp_Recurring_End_Now.getNowDateTime	= function()
{
	var oNow	= new Date();
	return oNow.$format(Popup_FollowUp_Recurring_End_Now.DATE_FORMAT);
};

