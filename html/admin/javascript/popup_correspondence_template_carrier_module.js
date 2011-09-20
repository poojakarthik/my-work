
var Popup_Correspondence_Template_Carrier_Module = Class.create(Reflex_Popup,
{
	initialize : function($super, fnOnComplete)
	{
		$super(30);
		
		this._fnOnComplete	= fnOnComplete;
		this._aControls 	= [];
		
		Flex.Constant.loadConstantGroup(Popup_Correspondence_Template_Carrier_Module.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
	},
	
	_buildUI : function()
	{
		// Controls
		var oModuleControl =	Control_Field.factory(
									'select',
									{
										sLabel		: 'Carrier Module',
										mEditable	: true,
										mMandatory	: true,
										fnPopulate	: Popup_Correspondence_Template_Carrier_Module._getCarrierModuleOptions
									}
								);
		var oCodeControl =	Control_Field.factory(
								'text',
								{
									sLabel		: 'Template Code',
									mEditable	: true,
									mMandatory	: true
								}
							);
		
		oModuleControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		oCodeControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._aControls = [oModuleControl, oCodeControl]; 
		
		// Dom
		var oContentDiv = 	$T.div({class: 'popup-correspondence-template-carrier-module'},
								$T.table({class: 'reflex input'},
									$T.tbody(
										$T.tr(
											$T.th('Carrier Module'),
											$T.td(oModuleControl.getElement())
										),
										$T.tr(
											$T.th('Template Code'),
											$T.td(oCodeControl.getElement())
										)
									)
								),
								$T.div({class: 'popup-correspondence-template-carrier-module-buttons'},
									$T.button({class: 'icon-button'},
										$T.img({src: '../admin/img/template/approve.png'}),
										$T.span('Save')
									).observe('click', this._doSave.bind(this)),
									$T.button({class: 'icon-button'},
										$T.img({src: '../admin/img/template/decline.png'}),
										$T.span('Cancel')
									).observe('click', this.hide.bind(this))
								)
							);
		
		this.setTitle('Correspondence Template Carrier Module');
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
				Popup_Correspondence_Template_Carrier_Module._validationError(aErrors);
				return;
			}
			
			// Build the details object
			var oDetails = 	
			{
				carrier_module_id	: parseInt(this._aControls[0].getValue()),
				template_code		: this._aControls[1].getValue()
			};
			
			this._oLoading = new Reflex_Popup.Loading('Saving...');
			this._oLoading.display();
			
			// Make request (sending the details object)
			var fnResp 	= this._save.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Correspondence_Template', 'createCorrespondenceTemplateCarrierModule');
			fnReq(oDetails);
			return;
		}

		this._oLoading.hide();
		delete this._oLoading;
		
		if (!oResponse.bSuccess)
		{
			Popup_Correspondence_Template_Carrier_Module._ajaxError(oResponse, 'Could not save the Correspondence Template Carrier Module');
			return;
		}
		
		this.hide();
		if (this._fnOnComplete)
		{
			this._fnOnComplete(oResponse.iCorrespondenceTemplateCarrierModuleId);
		}
	}
});

Object.extend(Popup_Correspondence_Template_Carrier_Module, 
{
	REQUIRED_CONSTANT_GROUPS : ['carrier_module_type'],
	
	_ajaxError : function(oResponse, sMessage)
	{
		if (oResponse.aErrors)
		{
			// Validation errors
			Popup_Correspondence_Template_Carrier_Module._validationError(oResponse.aErrors);
		}
		else
		{
			// Exception
			Reflex_Popup.alert(
				(sMessage ? sMessage + '. ' : '') + oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.', 
				{sTitle: 'Error', sDebugContent: oResponse.sDebug}
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
	},
	
	_getCarrierModuleOptions : function(fnCallback, oResponse)
	{
		if (!oResponse)
		{
			var fnResponse	= Popup_Correspondence_Template_Carrier_Module._getCarrierModuleOptions.curry(fnCallback);
			var fnRequest 	= jQuery.json.jsonFunction(fnResponse, fnResponse, 'Carrier_Module', 'getAll');
			fnRequest(true, $CONSTANT.MODULE_TYPE_CORRESPONDENCE_EXPORT);
			//fnRequest(true);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Popup_Correspondence_Template_Carrier_Module._ajaxError(oResponse);
			return;
		}
		
		var aOptions = [];
		//debugger;
		if (!Object.isArray(oResponse.aModules))
		{
			for (var iId in oResponse.aModules)
			{
				var oModule			= oResponse.aModules[iId],
					sDescription	= Popup_Correspondence_Template_Carrier_Module._buildCarrierModuleDescription(oResponse.aModules[iId]);
				aOptions.push(
					$T.option({value: iId},
						sDescription
					)
				);
			}
		}
		fnCallback(aOptions);
	},

	_buildCarrierModuleDescription	: function (oCarrierModule) {
		var	sDescription;

		if (oCarrierModule.description) {
			sDescription	= oCarrierModule.description;
		} else {
			sDescription	= oCarrierModule.carrier_name + ' : ' + oCarrierModule.carrier_module_type_name;
			
			if (oCarrierModule.customer_group_name !== null) {
				sDescription	+= ' (' + oCarrierModule.customer_group_name + ')';
			};
		}
		return sDescription;
	}
});
