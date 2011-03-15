
var Popup_Adjustment_Review_Outcome = Class.create(Reflex_Popup,
{
	initialize : function($super, fnOnComplete)
	{
		$super(30);
		
		this._fnOnComplete	= fnOnComplete;
		this._aControls 	= [];
		
		Flex.Constant.loadConstantGroup(Popup_Adjustment_Review_Outcome.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
	},
	
	_buildUI : function()
	{
		var oNameControl = 	Control_Field.factory(
								'text',
								{
									sLabel		: 'Name',
									mEditable	: true,
									mMandatory	: true,
									fnValidate	: Reflex_Validation.Exception.stringOfLength.curry(0, 256)
								}
							);
		var oDescriptionControl = 	Control_Field.factory(
										'text',
										{
											sLabel		: 'Description',
											mEditable	: true,
											mMandatory	: true,
											fnValidate	: Reflex_Validation.Exception.stringOfLength.curry(0, 256)
										}
									);
				
		oNameControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		oDescriptionControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);	
		
		this._aControls = [oNameControl, oDescriptionControl];
		
		this._oNameControl 			= oNameControl;
		this._oDescriptionControl 	= oDescriptionControl;
		
		var oContentDiv = 	$T.div({class: 'popup-adjustment-review-outcome'},
								$T.table({class: 'reflex input'},
									$T.tbody(
										$T.tr(
											$T.th('Name'),
											$T.td(oNameControl.getElement())
										),
										$T.tr(
											$T.th('Description'),
											$T.td(oDescriptionControl.getElement())
										)
									)
								),
								$T.div({class: 'popup-adjustment-review-outcome-buttons'},
									$T.button({class: 'icon-button'},
										$T.img({src: '../admin/img/template/approve.png'}),
										$T.span('Save')
									).observe('click', this._doSave.bind(this)),
									$T.button({class: 'icon-button'},
										$T.span('Cancel')
									).observe('click', this.hide.bind(this))
								)
							);
		
		this.setTitle('New Declined Adjustment Review Outcome');
		this.addCloseButton();
		this.setContent(oContentDiv);
		this.display();
	},
	
	_doSave : function()
	{
		this._save();
	},
	
	_save : function(oResponse)
	{
		if (!oResponse)
		{
			// Validate base controls
			var aErrors = [];
			for (var i = 0; i < this._aControls.length; i++)
			{
				try
				{
					this._aControls[i].validate(false);
					this._aControls[i].save(true);
				}
				catch (oException)
				{
					aErrors.push(oException);
				}
			}
			
			if (aErrors.length)
			{
				// There were validation errors, show all in a popup
				Popup_Adjustment_Review_Outcome._validationError(aErrors);
				return;
			}
			
			// Build the details object
			var oDetails = 	
			{
				name		: this._oNameControl.getValue(),
				description	: this._oDescriptionControl.getValue()
			};
			
			this._oLoading = new Reflex_Popup.Loading('Saving...');
			this._oLoading.display();
			
			// Make request (sending the details object)
			var fnResp 	= this._save.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Adjustment_Review_Outcome', 'createDeclined');
			fnReq(oDetails);
			return;
		}

		this._oLoading.hide();
		delete this._oLoading;
		
		if (!oResponse.bSuccess)
		{
			Popup_Adjustment_Review_Outcome._ajaxError(oResponse, 'Could not save the Adjustment Review Outcome');
			return;
		}
		
		this.hide();
		if (this._fnOnComplete)
		{
			this._fnOnComplete(oResponse.iOutcomeId);
		}
	}
});

Object.extend(Popup_Adjustment_Review_Outcome, 
{
	REQUIRED_CONSTANT_GROUPS : [],
	
	_ajaxError : function(oResponse, sMessage)
	{
		if (oResponse.aErrors)
		{
			// Validation errors
			Popup_Adjustment_Review_Outcome._validationError(oResponse.aErrors);
		}
		else
		{
			// Exception
			Reflex_Popup.alert(
				(sMessage ? sMessage + '. ' : '') + oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.', 
				{sTitle: 'Error'}
			);
		}
	},
	
	_validationError : function(aErrors)
	{
		var oErrorElement = $T.ul();
		for (var i = 0; i < aErrors.length; i++)
		{
			oErrorElement.appendChild($T.li(aErrors[i]));
		}
		
		Reflex_Popup.alert(
			$T.div(
				$T.div('There were errors in the form:'),
				oErrorElement
			),
			{sTitle: 'Validation Error'}
		);
	}
});
	