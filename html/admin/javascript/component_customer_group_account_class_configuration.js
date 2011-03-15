
var Component_Customer_Group_Account_Class_Configuration = Class.create(
{
	initialize	: function(oContainerDiv, oLoadingPopup)
	{
		this._oElement				= $T.div({class: 'component-customer-group-account-class-configuration'});
		this._oContainerDiv			= oContainerDiv;
		this._oLoadingPopup			= oLoadingPopup;
		this._oOverlay 				= new Reflex_Loading_Overlay();
		this._hAccountClassControls	= {};
		
		// Load constants then create UI
		Flex.Constant.loadConstantGroup(Component_Customer_Group_Account_Class_Configuration.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
	},

	// Public
	
	getElement : function()
	{
		return this._oElement;
	},
	
	reloadCustomerGroups : function()
	{
		this._loadCustomerGroups();
	},
	
	refreshSelectControls : function()
	{
		for (var iId in this._hAccountClassControls)
		{
			this._hAccountClassControls[iId].populate();
		}
	},
	
	// Protected
	
	_buildUI : function()
	{
		var oSection		= new Section(true);
		this._oSection 		= oSection;
		this._oElement.appendChild(oSection.getElement());
		
		oSection.setTitleContent($T.span('Customer Group Default Account Classes'));
		
		// Main content -- table
		oSection.setContent(
			$T.table({class: 'component-customer-group-account-class-configuration-table reflex highlight-rows'},
				$T.thead(
					// Column headings
					$T.tr({class: 'component-customer-group-account-class-configuration-headerrow'},
						$T.th('Customer Group'),
						$T.th('Default Account Class')
					)
				),
				$T.tbody({class: 'alternating'})
			)
		);
		
		oSection.setFooterContent(
			$T.div(
				$T.button({class: 'icon-button'},
					$T.img({src: '../admin/img/template/approve.png'}),
					$T.span('Save Changes')
				).observe('click', this._doSave.bind(this)),
				$T.button({class: 'icon-button'},
					$T.img({src: '../admin/img/template/decline.png'}),
					$T.span('Revert Changes')
				).observe('click', this._doRevert.bind(this))
			)
		);
		
		this._oTBody = this._oElement.select('tbody').first();
		this._oOverlay.attachTo(this._oTBody);
		this._loadCustomerGroups();
		
		if (this._oContainerDiv)
		{
			this._oContainerDiv.appendChild(this._oElement);
		}
	},
	
	_loadCustomerGroups : function(oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp	= this._loadCustomerGroups.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Customer_Group', 'getAll');
			fnReq();
			return;
		}
		
		this._oOverlay.detach();
		
		if (!oResponse.bSuccess)
		{
			// Error
			Component_Customer_Group_Account_Class_Configuration._ajaxError(oResponse);
			return;
		}
		
		this._oTBody.innerHTML = '';
		
		for (var i in oResponse.aResults)
		{
			var oCustomerGroup = oResponse.aResults[i];
			this._oTBody.appendChild(
				$T.tr(
					$T.td(
						oCustomerGroup.internal_name	
					),
					$T.td(
						this._createAccountClassControl(oCustomerGroup).getElement()
					)
				)	
			);
		}
		
		if (this._oLoadingPopup)
		{
			this._oLoadingPopup.hide();
			delete this._oLoadingPopup;
		}
	},
	
	_createAccountClassControl : function(oCustomerGroup)
	{
		if (!this._hAccountClassControls[oCustomerGroup.Id])
		{
			var oControl =	Control_Field.factory(
								'select',
								{
									sLabel		: 'Account Class',
									mEditable	: true,
									mMandatory	: true,
									fnPopulate	: Component_Customer_Group_Account_Class_Configuration._getAccountClassOptions,
									sExtraClass	: 'component-customer-group-account-class-configuration-account-class-select'
								}
							);
			oControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
			this._hAccountClassControls[oCustomerGroup.Id] = oControl;
		}
		
		this._hAccountClassControls[oCustomerGroup.Id].setValue(oCustomerGroup.default_account_class_id);
		return this._hAccountClassControls[oCustomerGroup.Id];
	},
	
	_doSave : function()
	{
		this._save();
	},
	
	_save : function(oResponse)
	{
		if (!oResponse)
		{
			// Get values and do request
			var hValues = {};
			try
			{
				for (var iId in this._hAccountClassControls)
				{
					this._hAccountClassControls[iId].validate(false);
					this._hAccountClassControls[iId].save(true);
					hValues[iId] = this._hAccountClassControls[iId].getValue();
				}
			}
			catch (oException)
			{
				Reflex_Popup.alert('Please make sure all Customer Groups have a default Account Class.');
				return;
			}
			
			this._oLoading = new Reflex_Popup.Loading('Saving Changes...');
			this._oLoading.display();
			
			var fnResp	= this._save.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Customer_Group', 'setDefaultAccountClasses');
			fnReq(hValues);
			return;
		}
		
		this._oLoading.hide();
		delete this._oLoading;
		
		if (!oResponse.bSuccess)
		{
			// Error
			Component_Customer_Group_Account_Class_Configuration._ajaxError(oResponse);
			return;
		}
		
		Reflex_Popup.alert('Changes to Default Account Classes saved.');
	},
	
	_doRevert : function()
	{
		for (var iId in this._hAccountClassControls)
		{
			this._hAccountClassControls[iId].revert();
		}
	}
});

// Static

Object.extend(Component_Customer_Group_Account_Class_Configuration,
{
	REQUIRED_CONSTANT_GROUPS	: ['status'],
	
	_ajaxError : function(oResponse)
	{
		var sMessage = (oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.');
		Reflex_Popup.alert(sMessage, {sTitle: 'Error'});
	},
	
	_getAccountClassOptions : function(fnCallback, oResponse)
	{
		if (!oResponse)
		{
			var fnResponse	= Component_Customer_Group_Account_Class_Configuration._getAccountClassOptions.curry(fnCallback);
			var fnRequest 	= jQuery.json.jsonFunction(fnResponse, fnResponse, 'Account_Class', 'getAll');
			fnRequest(true);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Component_Customer_Group_Account_Class_Configuration._ajaxError(oResponse);
			return;
		}
		
		var aOptions = [];
		if (!Object.isArray(oResponse.aClasses))
		{
			for (var iId in oResponse.aClasses)
			{
				aOptions.push(
					$T.option({value: iId},
						oResponse.aClasses[iId].name
					)
				);
			}
		}
		fnCallback(aOptions);
	}
});
