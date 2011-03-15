
var Popup_Account_Class_Replace_Default_For_Customer_Groups = Class.create(Reflex_Popup,
{
	initialize : function($super, iExcludeAccountClassId, aCustomerGroupIds, fnOnComplete)
	{
		$super(40);
		
		this._iExcludeAccountClassId 	= iExcludeAccountClassId;
		this._aCustomerGroupIds			= aCustomerGroupIds;
		this._fnOnComplete				= fnOnComplete;
		this._aControls 				= [];
		
		Flex.Constant.loadConstantGroup(Popup_Account_Class_Replace_Default_For_Customer_Groups.REQUIRED_CONSTANT_GROUPS, this._buildUI.bind(this));
	},
	
	_buildUI : function(oAccountClass, hCustomerGroups)
	{
		if (!oAccountClass)
		{
			Popup_Account_Class_Replace_Default_For_Customer_Groups._getAccountClassForId(this._iExcludeAccountClassId, this._buildUI.bind(this));
			return;
		}
		
		if (!hCustomerGroups)
		{
			Customer_Group.getAll(this._buildUI.bind(this, oAccountClass));
			return;
		}
		
		// Customer group list
		var oCustomerGroupUL = $T.ul();
		for (var i in hCustomerGroups)
		{
			if (this._aCustomerGroupIds.indexOf(i) != -1)
			{
				oCustomerGroupUL.appendChild($T.li(hCustomerGroups[i].internal_name));
			}
		}
		
		// Account class control
		var oControl =	Control_Field.factory(
							'select',
							{
								sLabel		: 'Account Class',
								mEditable	: true,
								mMandatory	: true,
								fnPopulate	: Popup_Account_Class_Replace_Default_For_Customer_Groups._getAccountClassOptions.curry(this._iExcludeAccountClassId)
							}
						);
		oControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		this._oAccountClassControl = oControl;
		
		// Put it all together
		var oContentDiv = 	$T.div({class: 'popup-account-class-replace-default-for-customer-groups'},
								$T.div(
									$T.div(
										$T.span('The Account Class '),
										$T.span({class: 'popup-account-class-replace-default-for-customer-groups-account-class-name'},
											oAccountClass.name
										),
										$T.span(' is currently used as the default Account Class for the following Customer Groups:')
									),
									oCustomerGroupUL,
									$T.div('Please choose a replacement before making it Inactive:'),
									this._oAccountClassControl.getElement()
								),
								$T.div({class: 'popup-account-class-replace-default-for-customer-groups-buttons'},
									$T.button({class: 'icon-button'},
										$T.img({src: '../admin/img/template/approve.png'}),
										$T.span('Save')
									).observe('click', this._doSave.bind(this)),
									$T.button({class: 'icon-button'},
										$T.span('Cancel')
									).observe('click', this.hide.bind(this))
								)
							);
		
		// Configure popup
		this.setTitle('Replace Customer Group Default Account Class');
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
			try
			{
				this._oAccountClassControl.validate(false);
				this._oAccountClassControl.save(true);
			}
			catch (oException)
			{
				Reflex_Popup.alert(oException, {sTitle: 'Error'});
				return;
			}
			
			this._oLoading = new Reflex_Popup.Loading('Saving...');
			this._oLoading.display();
			
			// Build [customer group => account class id] hash
			var iAccountClassId	= parseInt(this._oAccountClassControl.getValue());
			var hValues 		= {};
			for (var i = 0; i < this._aCustomerGroupIds.length; i++)
			{
				hValues[this._aCustomerGroupIds[i]] = iAccountClassId;
			}
			
			// Make request
			var fnResp 	= this._save.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Customer_Group', 'setDefaultAccountClasses');
			fnReq(hValues);
			return;
		}

		this._oLoading.hide();
		delete this._oLoading;
		
		if (!oResponse.bSuccess)
		{
			Popup_Account_Class_Replace_Default_For_Customer_Groups._ajaxError(oResponse, 'Could not update the default Account Classes');
			return;
		}
		
		this.hide();
		if (this._fnOnComplete)
		{
			this._fnOnComplete();
		}
	}
});

Object.extend(Popup_Account_Class_Replace_Default_For_Customer_Groups, 
{
	REQUIRED_CONSTANT_GROUPS : [],
	
	_ajaxError : function(oResponse, sMessage)
	{
		// Exception
		Reflex_Popup.alert(
			(sMessage ? sMessage + '. ' : '') + oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.', 
			{sTitle: 'Error'}
		);
	},
	
	_getAccountClassForId : function(iId, fnCallback, oResponse)
	{
		if (!oResponse)
		{
			var fnResponse	= Popup_Account_Class_Replace_Default_For_Customer_Groups._getAccountClassForId.curry(iId, fnCallback);
			var fnRequest 	= jQuery.json.jsonFunction(fnResponse, fnResponse, 'Account_Class', 'getForId');
			fnRequest(iId);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Popup_Account_Class_Replace_Default_For_Customer_Groups._ajaxError(oResponse);
			return;
		}
		
		fnCallback(oResponse.oAccountClass);
	},
	
	_getAccountClassOptions : function(iExcludeAccountClassId, fnCallback, oResponse)
	{
		if (!oResponse)
		{
			var fnResponse	= Popup_Account_Class_Replace_Default_For_Customer_Groups._getAccountClassOptions.curry(iExcludeAccountClassId, fnCallback);
			var fnRequest 	= jQuery.json.jsonFunction(fnResponse, fnResponse, 'Account_Class', 'getAll');
			fnRequest(true);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			Popup_Account_Class_Replace_Default_For_Customer_Groups._ajaxError(oResponse);
			return;
		}
		
		var aOptions = [];
		if (!Object.isArray(oResponse.aClasses))
		{
			for (var iId in oResponse.aClasses)
			{
				if (iId != iExcludeAccountClassId)
				{
					aOptions.push(
						$T.option({value: iId},
							oResponse.aClasses[iId].name
						)
					);
				}
			}
		}
		fnCallback(aOptions);
	}
});