
var Popup_Collections_Warning = Class.create(Reflex_Popup, 
{
	initialize : function($super, fnOnComplete)
	{
		$super(30);
		
		this._fnOnComplete 	= fnOnComplete;
		this._aControls		= [];
		
		this._buildUI();
	},
	
	_buildUI : function()
	{
		// Create control fields
		var oNameControl = 	Control_Field.factory(
								'text', 
								{
									sLabel		: 'Name',
									fnValidate	: Reflex_Validation.stringOfLength.curry(null, 256),
									mMandatory	: true,
									mEditable	: true
								}
							);
		oNameControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._oNameControl = oNameControl;
		this._aControls.push(oNameControl);

		var oMessageControl = 	Control_Field.factory(
									'textarea', 
									{
										sLabel		: 'Message',
										fnValidate	: Reflex_Validation.stringOfLength.curry(null, 1024),
										mMandatory	: true,
										mEditable	: true,
										rows		: 10
									}
								);
		oMessageControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._oMessageControl = oMessageControl;
		this._aControls.push(oMessageControl);
		
		// Create ui content
		var oContentDiv = $T.div({class: 'popup-collections-severity'},
								$T.table({class: 'reflex input'},
									$T.tbody(
										$T.tr(
											$T.th('Name'),
											$T.td(this._oNameControl.getElement())
										),
										$T.tr(
											$T.th('Message'),
											$T.td(this._oMessageControl.getElement())
										)
									)
								),
								$T.div({class: 'popup-collections-severity-buttons'},
									$T.button('Save').observe('click', this._doSave.bind(this)),
									$T.button('Cancel').observe('click', this.hide.bind(this))
								)
							);
		
		this.setTitle('Create Collections Warning');
		this.setContent(oContentDiv);
		this.addCloseButton();
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
				}
				catch (oException)
				{
					aErrors.push(oException);
				}
			}
			
			if (aErrors.length)
			{
				// There were validation errors, show all in a popup
				Popup_Collections_Severity._validationError(aErrors);
				return;
			}
			
			// Build the details object
			var oDetails = 	
			{
				name 	: this._oNameControl.getElementValue(),
				message	: this._oMessageControl.getElementValue()
			};
			
			// Make request (sending the details object)
			var fnResp 	= this._save.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Warning', 'createWarning');
			fnReq(oDetails);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Popup_Collections_Severity._ajaxError(oResponse, 'Could not save the Warning');
			return;
		}
		
		Reflex_Popup.alert('Warning saved successfully');
		
		this.hide();
		if (this._fnOnComplete)
		{
			this._fnOnComplete(oResponse.iWarningId);
		}
	}
});

Object.extend(Popup_Collections_Warning, 
{
	_ajaxError : function(oResponse, sMessage)
	{
		if (oResponse.aErrors)
		{
			// Validation errors
			Popup_Collections_Warning._validationError(oResponse.aErrors);
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
			$T.div({class: 'alert-validation-error'},
				$T.div('There were errors in the form:'),
				oErrorElement
			),
			{sTitle: 'Validation Error'}
		);
	},
});