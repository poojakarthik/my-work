
var Popup_Carrier_Module = Class.create(Reflex_Popup,
{
	initialize : function($super, iMode, iCarrierModuleId, fnOnComplete)
	{
		$super(70);
		
		this._iMode				= (!!iMode ? iMode : Popup_Carrier_Module.MODE_CREATE);
		this._iCarrierModuleId	= (!!iCarrierModuleId ? iCarrierModuleId : null);
		this._fnOnComplete		= fnOnComplete;
		this._aControls 		= [];
		this._hValidModuleNames	= {};
		
		Flex.Constant.loadConstantGroup(Popup_Carrier_Module.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
	},
	
	_buildUI : function()
	{
		// Controls
		var oCarrierControl =	Control_Field.factory(
									'select',
									{
										sLabel		: 'Carrier',
										mEditable	: true,
										mMandatory	: true,
										fnPopulate	: Popup_Carrier_Module._getCarrierOptions
									}
								);
		this._aControls.push(oCarrierControl);
		this._oCarrierControl = oCarrierControl;
		
		var oCustomerGroupControl =	Control_Field.factory(
										'select',
										{
											sLabel		: 'Customer Group',
											mEditable	: true,
											mMandatory	: false,
											fnPopulate	: Customer_Group.getAllAsSelectOptions
										}
									);
		this._aControls.push(oCustomerGroupControl);
		this._oCustomerGroupControl = oCustomerGroupControl;
		
		var oTypeControl =	Control_Field.factory(
								'select',
								{
									sLabel		: 'Type',
									mEditable	: false,
									mMandatory	: true,
									fnPopulate	: Flex.Constant.getConstantGroupOptions.curry('carrier_module_type')
								}
							);
		this._aControls.push(oTypeControl);
		this._oTypeControl = oTypeControl;
		
		var oFileTypeControl =	Control_Field.factory(
									'select',
									{
										sLabel		: 'File Type',
										mEditable	: false,
										mMandatory	: true,
										fnPopulate	: Flex.Constant.getConstantGroupOptions.curry('resource_type')
									}
								);
		this._aControls.push(oFileTypeControl);
		this._oFileTypeControl = oFileTypeControl;
		
		var oModuleControl =	Control_Field.factory(
									'text',
									{
										sLabel		: 'Module',
										mEditable	: true,
										mMandatory	: true,
										fnValidate	: this._validateModule.bind(this)
									}
								);
		this._aControls.push(oModuleControl);
		this._oModuleControl = oModuleControl;
		
		var oDescriptionControl =	Control_Field.factory(
										'text',
										{
											sLabel		: 'Description',
											mEditable	: true,
											mMandatory	: false,
											fnValidate	: Reflex_Validation.Exception.stringOfLength.curry(0, 512)
										}
									);
		this._aControls.push(oDescriptionControl);
		this._oDescriptionControl = oDescriptionControl;
		
		var oFrequencyTypeControl =	Control_Field.factory(
										'select',
										{
											sLabel		: 'Frequency Type',
											mEditable	: true,
											mMandatory	: true,
											fnPopulate	: Flex.Constant.getConstantGroupOptions.curry('FrequencyType'),
											sExtraClass	: 'popup-carrier-module-frequency-type'
										}
									);
		this._aControls.push(oFrequencyTypeControl);
		this._oFrequencyTypeControl = oFrequencyTypeControl;
		
		var oFrequencyControl =	Control_Field.factory(
									'number',
									{
										sLabel		: 'Frequency',
										mEditable	: true,
										mMandatory	: true,
										fnPopulate	: null,
										sExtraClass	: 'popup-carrier-module-frequency'
									}
								);
		this._aControls.push(oFrequencyControl);
		this._oFrequencyControl = oFrequencyControl;
		
		var oEarliestDeliveryControl =	Control_Field.factory(
											'combo-time',
											{
												sLabel				: 'Earliest Delivery',
												mEditable			: true,
												mMandatory			: true,
												mSeparatorElement	: ':',
												fnValidate			: Popup_Carrier_Module._validateTime
											}
										);
		this._aControls.push(oEarliestDeliveryControl);
		this._oEarliestDeliveryControl = oEarliestDeliveryControl;
		
		var oActiveControl =	Control_Field.factory(
									'checkbox',
									{
										sLabel		: 'Active',
										mEditable	: true,
										mMandatory	: false
									}
								);
		this._oActiveControl = oActiveControl;
		this._aControls.push(oActiveControl);
		
		var oConfigControl		= new Component_Carrier_Module_Config(this._iMode, this._iCarrierModuleId);
		this._oConfigControl	= oConfigControl;
		
		// Render mode of controls
		switch (this._iMode)
		{
			case Popup_Carrier_Module.MODE_CREATE:
				// Create
				oCarrierControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				oCustomerGroupControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				oTypeControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				oFileTypeControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				oModuleControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				oEarliestDeliveryControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				
				oDescriptionControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				oFrequencyTypeControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				oFrequencyControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				oConfigControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				oActiveControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				break;
				
			case Popup_Carrier_Module.MODE_EDIT:
				// Edit
				oCarrierControl.setRenderMode(Control_Field.RENDER_MODE_VIEW);
				oCustomerGroupControl.setRenderMode(Control_Field.RENDER_MODE_VIEW);
				oTypeControl.setRenderMode(Control_Field.RENDER_MODE_VIEW);
				oFileTypeControl.setRenderMode(Control_Field.RENDER_MODE_VIEW);
				oModuleControl.setRenderMode(Control_Field.RENDER_MODE_VIEW);
				oEarliestDeliveryControl.setRenderMode(Control_Field.RENDER_MODE_VIEW);
				
				oDescriptionControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				oFrequencyTypeControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				oFrequencyControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				oConfigControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				oActiveControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				break;
				
			case Popup_Carrier_Module.MODE_CLONE:
				// Clone
				oCarrierControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				oCustomerGroupControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				oTypeControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				oFileTypeControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				oModuleControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				oEarliestDeliveryControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				
				oDescriptionControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				oFrequencyTypeControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				oFrequencyControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				oConfigControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				oActiveControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				break;
				
			case Popup_Carrier_Module.MODE_VIEW:
				// View
				oCarrierControl.setRenderMode(Control_Field.RENDER_MODE_VIEW);
				oCustomerGroupControl.setRenderMode(Control_Field.RENDER_MODE_VIEW);
				oTypeControl.setRenderMode(Control_Field.RENDER_MODE_VIEW);
				oFileTypeControl.setRenderMode(Control_Field.RENDER_MODE_VIEW);
				oModuleControl.setRenderMode(Control_Field.RENDER_MODE_VIEW);
				oEarliestDeliveryControl.setRenderMode(Control_Field.RENDER_MODE_VIEW);
				
				oDescriptionControl.setRenderMode(Control_Field.RENDER_MODE_VIEW);
				oFrequencyTypeControl.setRenderMode(Control_Field.RENDER_MODE_VIEW);
				oFrequencyControl.setRenderMode(Control_Field.RENDER_MODE_VIEW);
				oConfigControl.setRenderMode(Control_Field.RENDER_MODE_VIEW);
				oActiveControl.setRenderMode(Control_Field.RENDER_MODE_VIEW);
		}
		
		// Dom
		var oContentDiv = 	$T.div({class: 'popup-carrier-module'},
								$T.table({class: 'reflex input'},
									$T.tbody(
										$T.tr(
											$T.th('Carrier'),
											$T.td(oCarrierControl.getElement())
										),
										$T.tr(
											$T.th('Customer Group'),
											$T.td(oCustomerGroupControl.getElement())
										),
										$T.tr(
											$T.th('Module'),
											$T.td(oModuleControl.getElement())
										),
										$T.tr(
											$T.th('Type'),
											$T.td(oTypeControl.getElement())
										),
										$T.tr(
											$T.th('File Type'),
											$T.td(oFileTypeControl.getElement())
										),
										$T.tr(
											$T.th('Description'),
											$T.td(oDescriptionControl.getElement())
										),
										$T.tr(
											$T.th('Frequency'),
											$T.td(
												oFrequencyControl.getElement(),
												oFrequencyTypeControl.getElement()
											)
										),
										$T.tr(
											$T.th('Earliest Delivery'),
											$T.td(oEarliestDeliveryControl.getElement())
										),
										$T.tr({class: 'popup-carrier-module-active'},
											$T.th('Active'),
											$T.td(oActiveControl.getElement())
										),
										$T.tr(
											$T.th('Configuration'),
											$T.td(oConfigControl.getElement())
										)
									)
								),
								$T.div({class: 'popup-carrier-module-buttons'},
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
		
		// Hide active row if creating or cloning
		if ((this._iMode == Popup_Carrier_Module.MODE_CREATE) || (this._iMode == Popup_Carrier_Module.MODE_CLONE))
		{
			oContentDiv.select('.popup-carrier-module-active').first().hide();
		}
		
		// Hide save button if in view mode
		if (this._iMode == Popup_Carrier_Module.MODE_VIEW)
		{
			oContentDiv.select('.popup-carrier-module-buttons > .icon-button').first().hide();
			oContentDiv.select('.popup-carrier-module-buttons > .icon-button > span').last().innerHTML  = 'Close';
		}
		
		// Show the popup
		var sTitlePrefix = '';
		switch (this._iMode)
		{
			case Popup_Carrier_Module.MODE_VIEW:
				sTitlePrefix = 'View';
				break;
			case Popup_Carrier_Module.MODE_CREATE:
			case Popup_Carrier_Module.MODE_CLONE:
				sTitlePrefix = 'Create';
				break;
			case Popup_Carrier_Module.MODE_EDIT:
				sTitlePrefix = 'Edit';
				break;
		}
		
		this.setTitle(sTitlePrefix + ' Carrier Module');
		this.addCloseButton();
		this.setContent(oContentDiv);
		
		if (this._iCarrierModuleId)
		{
			this._oLoading = new Reflex_Popup.Loading();
			this._oLoading.display();
			this._loadCarrierModule();
		}
		else
		{
			this.display();
		}
	},
	
	_loadCarrierModule : function(oResponse)
	{
		if (!oResponse)
		{
			// Make request
			var fnResponse	= this._loadCarrierModule.bind(this);
			var fnRequest 	= jQuery.json.jsonFunction(fnResponse, fnResponse, 'Carrier_Module', 'getEditingDetailsForId');
			fnRequest(this._iCarrierModuleId);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Popup_Carrier_Module._ajaxError(oResponse);
			return;
		}
		
		var oModule = oResponse.oCarrierModule;
		
		// Refresh config control
		this._oConfigControl.loadFromCarrierModule(this._iCarrierModuleId, oModule.Module);
		
		// Update controls
		this._oCarrierControl.setValue(oModule.Carrier);
		this._oCustomerGroupControl.setValue(oModule.customer_group);
		this._oTypeControl.setValue(oModule.Type);
		this._oFileTypeControl.setValue(oModule.FileType);
		this._oModuleControl.setValue(oModule.Module);
		this._oDescriptionControl.setValue(oModule.description);
		this._oFrequencyTypeControl.setValue(oModule.FrequencyType);
		this._oFrequencyControl.setValue(oModule.Frequency);
		this._oActiveControl.setValue(!!oModule.Active);
		
		var oEarliestDeliveryDate = Date.$parseDate('1970-01-01 00:00:00', 'Y-m-d H:i:s');
		oEarliestDeliveryDate.setSeconds(oModule.EarliestDelivery);
		this._oEarliestDeliveryControl.setValue(oEarliestDeliveryDate.$format('H:i A'));
		
		this._oLoading.hide();
		delete this._oLoading;
		
		this.display();
	},
	
	_doSave : function()
	{
		if (this._iMode == Popup_Carrier_Module.MODE_VIEW)
		{
			throw 'Cannot save Carrier Module in View mode.';
		}
		
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
				Popup_Carrier_Module._validationError(aErrors);
				return;
			}
			
			// Build the details object
			var oDetails = 	
			{
				id						: (this._iMode == Popup_Carrier_Module.MODE_EDIT ? this._iCarrierModuleId : null),
				carrier_id				: parseInt(this._oCarrierControl.getValue()),
				customer_group_id		: parseInt(this._oCustomerGroupControl.getValue()),
				carrier_module_type_id	: parseInt(this._oTypeControl.getValue()),
				resource_type_id		: parseInt(this._oFileTypeControl.getValue()),
				module					: this._oModuleControl.getValue(),
				description				: this._oDescriptionControl.getValue(),
				frequency_type			: parseInt(this._oFrequencyTypeControl.getValue()),
				frequency				: parseInt(this._oFrequencyControl.getValue()),
				earliest_delivery		: Date.$parseDate(this._oEarliestDeliveryControl.getValue(), 'H:i A').$format('H:i:s'),
				config					: this._oConfigControl.getData()
			};
			
			if (this._iMode == Popup_Carrier_Module.MODE_EDIT)
			{
				oDetails.active = this._oActiveControl.getValue();
			}
			
			this._oLoading = new Reflex_Popup.Loading('Saving...');
			this._oLoading.display();
			
			// Make request (sending the details object)
			var fnResp 	= this._save.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Carrier_Module', 'saveModule');
			fnReq(oDetails);
			return;
		}

		this._oLoading.hide();
		delete this._oLoading;
		
		if (!oResponse.bSuccess)
		{
			Popup_Carrier_Module._ajaxError(oResponse, 'Could not save the Carrier Module');
			return;
		}
		
		this.hide();
		if (this._fnOnComplete)
		{
			this._fnOnComplete(oResponse.iCarrierModuleId);
		}
	},
	
	_validateModule : function(mValue, oResponse)
	{
		if (!oResponse && !Object.isUndefined(this._hValidModuleNames[mValue]))
		{
			// The validity of this value has been determine previously, do not do it again, use cached info.
			if (!this._hValidModuleNames[mValue].bValid)
			{
				// Already marked as non-valid, clear config and type values
				this._oTypeControl.clearValue();
				this._oFileTypeControl.clearValue();
				this._oConfigControl.clearConfig();
				throw this._hValidModuleNames[mValue].sReason;
			}
			else
			{
				// Already marked as valid, set the type & file type values and update the config using the module name
				this._oTypeControl.setValue(this._hValidModuleNames[mValue].iCarrierModuleTypeId);
				this._oFileTypeControl.setValue(this._hValidModuleNames[mValue].iResourceTypeId);
				this._oConfigControl.loadFromModuleName(mValue);
				return true;
			}
		}
		
		// We don't know whether it's valid or not 
		if (!oResponse)
		{
			// ...make request to find out
			var fnResp 	= this._validateModule.bind(this, mValue);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Carrier_Module', 'validateModuleName');
			fnReq(mValue);
			return false;
		}
		
		// Cache response and revalidate
		this._hValidModuleNames[mValue] = {bValid: oResponse.bSuccess};
		if (oResponse.bSuccess)
		{
			this._hValidModuleNames[mValue].iCarrierModuleTypeId	= oResponse.iCarrierModuleTypeId;
			this._hValidModuleNames[mValue].iResourceTypeId 		= oResponse.iResourceTypeId;
		}
		else
		{
			this._hValidModuleNames[mValue].sReason = oResponse.sMessage;
		}
		
		if (this._oModuleControl.getElementValue() == mValue)
		{
			// Try and re-validate the control now that we've cached it's validity
			this._oModuleControl.validate();
		}
	}
});

Object.extend(Popup_Carrier_Module, 
{
	REQUIRED_CONSTANT_GROUPS : [],
	
	MODE_CREATE : 1,
	MODE_EDIT	: 2,
	MODE_CLONE	: 3,
	MODE_VIEW	: 4,
	
	_ajaxError : function(oResponse, sMessage)
	{
		if (oResponse.aErrors)
		{
			// Validation errors
			Popup_Carrier_Module._validationError(oResponse.aErrors);
		}
		else
		{
			// Exception
			jQuery.json.errorPopup(oResponse, sMessage);
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
	
	_getCarrierOptions : function(fnCallback, oResponse)
	{
		if (!oResponse)
		{
			var fnResponse	= Popup_Carrier_Module._getCarrierOptions.curry(fnCallback);
			var fnRequest 	= jQuery.json.jsonFunction(fnResponse, fnResponse, 'Carrier', 'getCarriers');
			fnRequest();
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Popup_Carrier_Module._ajaxError(oResponse);
			return;
		}
		
		var aOptions = [];
		for (var i = 0; i < oResponse.aRecords.length; i++)
		{
			aOptions.push(
				$T.option({value: oResponse.aRecords[i].Id},
					oResponse.aRecords[i].Name
				)
			);
		}
		fnCallback(aOptions);
	},
	
	_validateTime : function(mValue)
	{
		if (Date.$parseDate(mValue.toString(), 'g:i A'))
		{
			return true;
		}
		throw 'Invalid time value';
	}
});
