
var Component_Carrier_Module_Config = Class.create(
{
	initialize : function()
	{
		this._bRenderMode	= Control_Field.RENDER_MODE_EDIT;
		this._oElement 		= $T.div({class: 'component-carrier-module-config'});
		this._buildUI();
		this.clearConfig();
		
		this._sLoadedModule 			= null;
		this._iLoadedCarrierModuleId	= null;
	},
	
	// Public
	
	getElement : function()
	{
		return this._oElement;
	},
	
	setRenderMode : function(bRenderMode)
	{
		this._bRenderMode = bRenderMode;
	},
	
	loadFromCarrierModule : function(iCarrierModuleId, sModule)
	{
		if (iCarrierModuleId !== this._iLoadedCarrierModuleId)
		{
			this._iLoadedCarrierModuleId 	= iCarrierModuleId;
			this._sLoadedModule				= sModule;
			Flex.Constant.loadConstantGroup(Component_Carrier_Module_Config.REQUIRED_CONSTANT_GROUPS, this._loadFromCarrierModule.bind(this, iCarrierModuleId));
		}
	},
	
	loadFromModuleName : function(sModule, oResponse)
	{
		if (sModule !== this._sLoadedModule)
		{
			this._sLoadedModule = sModule;
			Flex.Constant.loadConstantGroup(Component_Carrier_Module_Config.REQUIRED_CONSTANT_GROUPS, this._loadFromModuleName.bind(this, sModule));
		}
	},
	
	clearConfig : function()
	{
		this._sLoadedModule 			= null;
		this._iLoadedCarrierModuleId 	= null;
		
		this._oHeader.hide();
		this._oContent.hide();
		this._oEmptyLabel.show();
		this._clearTable();
	},
	
	getData : function()
	{
		if (this._bRenderMode)
		{
			var aData = [];
			for (var i = 0; i < this._oTBody.childNodes.length; i++)
			{
				var oTR			= this._oTBody.childNodes[i];
				var oRecord 	= oTR.oRecord;
				oRecord.Value	= oTR.oControl.getElementValue();
				
				// This will be the same as the one already on the record object, if loaded from a carrier module
				oRecord.Name = oTR.sName;
				
				aData.push(oRecord);
			}
			return aData;
		}
		
		return null;
	},
	
	// Protected
	
	_buildUI : function()
	{
		this._oElement.appendChild(	
			$T.table(
				$T.thead(
					$T.tr(
						$T.td('Name'),
						$T.td('Description'),
						$T.td('Value')
					)
				)
			)
		);
		this._oElement.appendChild(	
			$T.div({class: 'component-carrier-module-config-columnlist'},	
				$T.table(
					$T.tbody()
				)
			)
		);
		this._oEmptyLabel = $T.div('No configuration available');
		this._oEmptyLabel.hide();
		this._oElement.appendChild(this._oEmptyLabel);
		
		this._oHeader 	= this._oElement.select('table').first();
		this._oContent	= this._oElement.select('.component-carrier-module-config-columnlist').first();
		this._oTBody 	= this._oElement.select('tbody').first();
	},
	
	_clearTable : function()
	{
		while (this._oTBody.firstChild)
		{
			// Remove references to data and the value control
			this._oTBody.firstChild.oRecord		= null;
			this._oTBody.firstChild.oControl	= null;
			this._oTBody.firstChild.remove();
		}
	},
	
	_loadFromCarrierModule : function(iCarrierModuleId, oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp 	= this._loadFromCarrierModule.bind(this, iCarrierModuleId);
			var fnReq 	= jQuery.json.jsonFunction(fnResp, fnResp, 'Carrier_Module', 'getConfigForId');
			fnReq(iCarrierModuleId);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Component_Carrier_Module_Config._ajaxError(oResponse);
			return;
		}
		
		if (Object.isArray(oResponse.aConfig) && (oResponse.aConfig.length == 0))
		{
			// Try to load from the module name
			this._loadFromModuleName(this._sLoadedModule);
			return;
		}
		
		// Have config, list it
		this._oHeader.show();
		this._oContent.show();
		this._oEmptyLabel.hide();
		
		// Clear existing config items
		this._clearTable();
		
		var hConfigRecords = oResponse.aConfig;
		for (var i in hConfigRecords)
		{
			var oRecord = hConfigRecords[i];
			this._addRow(oRecord.Name, oRecord);
		}
	},
	
	_loadFromModuleName : function(sModule, oResponse)
	{
		if (!oResponse)
		{
			var fnResp	= this._loadFromModuleName.bind(this, sModule);
			var fnReq 	= jQuery.json.jsonFunction(fnResp, fnResp, 'Carrier_Module', 'getConfigForModuleName');
			fnReq(sModule);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Component_Carrier_Module_Config._ajaxError(oResponse);
			return;
		}
		
		if (oResponse.aConfig === null || (Object.isArray(oResponse.aConfig) && (oResponse.aConfig.length == 0)))
		{
			// No config
			this._oHeader.hide();
			this._oContent.hide();
			this._oEmptyLabel.show();
		}
		else
		{
			// Have config, list it
			this._oHeader.show();
			this._oContent.show();
			this._oEmptyLabel.hide();
			
			// Clear existing config items
			this._clearTable();
			
			var hConfigRecords = oResponse.aConfig;
			for (var sName in hConfigRecords)
			{
				this._addRow(sName, hConfigRecords[sName]);
			}
		}
	},
	
	_viewNonEditableSerialisedValue : function(sValue)
	{
		new Popup_Carrier_Module_Config_JSON_Editor(sValue, null, false);
	},
	
	_viewSerialisedValue : function(oControl)
	{
		new Popup_Carrier_Module_Config_JSON_Editor(
			oControl.getElementValue(), 
			this._cacheSerialisedValue.bind(this, oControl), 
			this._bRenderMode
		);
	},
	
	_cacheSerialisedValue : function(oControl, sSerialisedValue)
	{
		oControl.setValue(sSerialisedValue);
	},
	
	_addRow : function(sName, oRecord)
	{
		var oValueElement	= $T.div();
		var oControl 		= null;
		if (this._bRenderMode)
		{
			// Show editable control field
			oRecord.Type = (!!oRecord.Type ? oRecord.Type : $CONSTANT.DATA_TYPE_STRING);
			switch (oRecord.Type)
			{
				case $CONSTANT.DATA_TYPE_STRING:
					oControl =	Control_Field.factory(
									'textarea',
									{
										sLabel		: sName,
										mEditable	: true,
										mMandatory	: false,
										sExtraClass	: 'component-carrier-module-config-stringfield',
										rows		: 4
									}
								);
					oValueElement = oControl.getElement();
					break;
				case $CONSTANT.DATA_TYPE_INTEGER:
				case $CONSTANT.DATA_TYPE_FLOAT:
					oControl =	Control_Field.factory(
									'number',
									{
										sLabel			: sName,
										mEditable		: true,
										mMandatory		: false,
										iDecimalPlaces	: (oRecord.Type == $CONSTANT.DATA_TYPE_FLOAT ? 2 : 0)
									}
								);
					oValueElement = oControl.getElement();
					break;
				case $CONSTANT.DATA_TYPE_BOOLEAN:
					oControl =	Control_Field.factory(
									'checkbox',
									{
										sLabel		: sName,
										mEditable	: true,
										mMandatory	: false
									}
								);
					oValueElement = oControl.getElement();
					break;
				case $CONSTANT.DATA_TYPE_SERIALISED:
				case $CONSTANT.DATA_TYPE_ARRAY:
					oControl =	Control_Field.factory(
									'hidden',
									{
										sLabel : sName
									}
								);
					oValueElement.appendChild(
						$T.button('View Details').observe('click', this._viewSerialisedValue.bind(this, oControl))
					);
					break;
			}
			
			if (oControl !== null)
			{
				oControl.setRenderMode(this._bRenderMode);
				if ((oRecord.Value !== null) && !Object.isUndefined(oRecord.Value))
				{
					oControl.setValue(oRecord.Value);
				}
			}
		}
		else
		{
			// Not in edit mode
			if (oRecord.Type == $CONSTANT.DATA_TYPE_SERIALISED || oRecord.Type == $CONSTANT.DATA_TYPE_ARRAY)
			{
				// For serialise and array types, show the view details button which will show the config object but will
				// be uneditable
				oValueElement.appendChild(
					$T.button('View Details').observe('click', this._viewNonEditableSerialisedValue.bind(this, oRecord.Value))
				);
			}
			else
			{
				// Just show value
				oValueElement.innerHTML = oRecord.Value.escapeHTML();
			}
		}
		
		var oTR = 	$T.tr(
						$T.td(sName),
						$T.td(oRecord.Description),
						$T.td(oValueElement)
					);
		oTR.sName		= sName;
		oTR.oRecord 	= oRecord;
		oTR.oControl	= oControl;
		this._oTBody.appendChild(oTR);
	}
});

Object.extend(Component_Carrier_Module_Config, 
{
	REQUIRED_CONSTANT_GROUPS : ['data_type'],
	
	_ajaxError : function(oResponse, sMessage) {
		// Exception
		jQuery.json.errorPopup(oResponse, sMessage);
	}
});