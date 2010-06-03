
var Popup_FollowUp_Due_Date	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iFollowUpId, initialDateTime, fnOnFinish)
	{
		$super(20);
		
		this._iFollowUpId		= iFollowUpId;
		this._initialDateTime	= initialDateTime;
		this._fnOnFinish		= fnOnFinish;
		this._buildUI();
	},
	
	_buildUI	: function()
	{
		// Build date select
		this._oSelect	= 	new Control_Field.factory(
								'combo_date',
								{
									sLabel		: 'Due Date',
									mEditable	: true,
									mMandatory	: true,
									fnValidate	: Popup_FollowUp_Due_Date._validateDueDate,
									iMinYear	: Popup_FollowUp_Due_Date.YEAR_MINIMUM,
									iMaxYear	: Popup_FollowUp_Due_Date.YEAR_MAXIMUM,
									iFormat		: Control_Field_Combo_Date.FORMAT_D_M_Y
								}
							);
		this._oSelect.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._oSelect.setValue(this._initialDateTime);
		
		// Build content
		this._oContent	= 	$T.div({class: 'popup-followup-due-date'},
								$T.div({class: 'popup-followup-due-date-select'},
									this._oSelect.getElement()
								),
								$T.div({class: 'popup-followup-due-date-buttons'},
									$T.button({class: 'icon-button'},
										$T.span('Save')
									),
									$T.button({class: 'icon-button'},
										$T.span('Cancel')
									)
								)
							);
		
		// Button events
		var oSaveButton	= this._oContent.select('div.popup-followup-due-date-buttons > button.icon-button').first();
		oSaveButton.observe('click', this._doUpdate.bind(this));
		
		var oCancelButton	= this._oContent.select('div.popup-followup-due-date-buttons > button.icon-button').last();
		oCancelButton.observe('click', this.hide.bind(this));
					
		// Popup setup
		this.setTitle('Change Follow-Up Due Date');
		this.setIcon(Popup_FollowUp_Due_Date.ICON_IMAGE_SOURCE);
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
	
	_doUpdate	: function(oEvent, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Show loading
			this.oLoading	= new Reflex_Popup.Loading('Updating due date...');
			this.oLoading.display();
			
			var fnJSON	= 	jQuery.json.jsonFunction(
								this._doUpdate.bind(this, null), 
								this._ajaxError.bind(this, false), 
								'FollowUp', 
								'updateDueDate'
							);
			fnJSON(this._iFollowUpId, this._oSelect.getElementValue());
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
Popup_FollowUp_Due_Date.ICON_IMAGE_SOURCE 	= '../admin/img/template/calendar.png';
Popup_FollowUp_Due_Date.YEAR_MINIMUM		= new Date().getFullYear();
Popup_FollowUp_Due_Date.YEAR_MAXIMUM		= Popup_FollowUp_Due_Date.YEAR_MINIMUM + 10;

Popup_FollowUp_Due_Date._validateDueDate	= function(sValue)
{
	var iMilliseconds	 = Date.parse(sValue.replace(/-/g, '/'));
	if (isNaN(iMilliseconds))
	{
		return false;
	}
	else if (iMilliseconds > new Date().getTime())
	{
		return true;
	}
	else
	{
		return false;
	}
};

