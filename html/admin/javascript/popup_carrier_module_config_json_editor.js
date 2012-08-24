
var Popup_Carrier_Module_Config_JSON_Editor = Class.create(Reflex_Popup, 
{
	initialize : function($super, sSerialisedValue, fnOnOK, bEditable)
	{
		$super();
		
		this._sSerialisedValue	= sSerialisedValue;
		this._fnOnOK			= fnOnOK;
		this._bEditable			= !!bEditable;
		
		this._buildUI();
	},
	
	_buildUI : function()
	{
		this._oTextArea	=	Control_Field.factory(
								'textarea', 
								{
									mEditable					: true, 
									sExtraClass					: 'popup-carrier-module-config-json-editor-text',
									rows						: 20,
									cols						: 100,
									bDisableValidationStyling	: true
								}
							);
		this._oTextArea.cancelFocusShiftOnTab();
		this._oTextArea.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		
		var oContentDiv =	$T.div({class: 'popup-carrier-module-config-json-editor'},
								this._oTextArea.getElement(),
								$T.div({class: 'popup-carrier-module-config-json-editor-buttons'},
									this._bEditable ? $T.button({class: 'icon-button'},
										$T.img({src: '../admin/img/template/approve.png'}),
										$T.span('Save Changes')
									).observe('click', this._save.bind(this)) : null,
									$T.button({class: 'icon-button'},
										$T.img({src: '../admin/img/template/decline.png'}),
										$T.span(this._bEditable ? 'Cancel' : 'Close')
									).observe('click', this.hide.bind(this))
								)
							);
		
		this.setTitle((this._bEditable ? 'Edit' : 'View') + ' Carrier Module Config Object');
		this.addCloseButton();
		this.setContent(oContentDiv);
		this.display();
		
		this._getJSONValue();
	},
	
	_getJSONValue : function(oResponse)
	{
		if (!oResponse)
		{
			this._oLoading = new Reflex_Popup.Loading('Converting value...');
			this._oLoading.display();
			
			var fnResp 	= this._getJSONValue.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Carrier_Module', 'getJSONFromSerialisedConfigValue');
			fnReq(this._sSerialisedValue);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			jQuery.json.errorPopup(oResponse);
			return;
		}
		
		// Set text area value and hide loading popup
		this._oTextArea.setValue(this._getTidyJSON(Object.toJSON(oResponse.mValue)));
		this._oLoading.hide();
		delete this._oLoading;
	},
	
	_getSerialisedValue : function(oResult, oResponse)
	{
		if (!oResponse)
		{
			this._oLoading = new Reflex_Popup.Loading('Converting value...');
			this._oLoading.display();
		
			var fnResp 	= this._getSerialisedValue.bind(this, oResult);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Carrier_Module', 'getSerialisedConfigJSONValue');
			fnReq(oResult);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			jQuery.json.errorPopup(oResponse);
			return;
		}
		
		// Set text area value and hide loading popup
		this._oLoading.hide();
		delete this._oLoading;
		
		// Callback and hide
		this.hide();
		if (this._fnOnOK)
		{
			this._fnOnOK(oResponse.sSerialisedValue);
		}
	},
	
	_save : function()
	{
		var sText	= this._oTextArea.getElementValue();
		var oResult = sText.evalJSON();
		if (oResult === false)
		{
			Reflex_Popup.alert("There are errors in your configuration text.");
			return;
		}
		
		this._getSerialisedValue(oResult);
	},
	
	_getTidyJSON : function(sText)
	{
		var bInString 		= false;
		var bEscaped		= false;
		var iTabCount		= 0;
		var sFinalString	= '';
		
		for (var i = 0; i < sText.length; i++)
		{
			var sChar = sText[i];
			
			if (sChar === "\\" && !bEscaped)
			{
				bEscaped = true;
				sFinalString += sChar;
				continue;
			}
			else if (sChar === '"' && !bEscaped)
			{
				bInString = !bInString;
				sFinalString += sChar;
				continue;
			}
			else if (!bInString)
			{
				switch (sChar)
				{
					case '{':
					case '[':
						iTabCount++;
						sFinalString += sChar + "\n" + this._getTabString(iTabCount);
						break;
					case '}':
					case ']':
						iTabCount--;
						sFinalString += "\n" + this._getTabString(iTabCount) + sChar;
						break;
					case ',':
						sFinalString += sChar + "\n" + this._getTabString(iTabCount);
						break;
					case ':':
						sFinalString += ' ' + sChar + ' ';
						break;
					default:
						sFinalString += sChar;
				}
			}
			else if (bInString)
			{
				sFinalString += sChar;
			}
			
			if (bEscaped)
			{
				bEscaped = false;
			}
		}
		return sFinalString;
	},
	
	_getTabString : function(iCount)
	{
		var sStr = '';
		for (var i = 0; i < iCount; i++)
		{
			sStr += "\t";
		}
		return sStr;
	}
});
