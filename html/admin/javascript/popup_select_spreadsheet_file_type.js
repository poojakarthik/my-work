
var Popup_Select_Spreadsheet_File_Type = Class.create(Reflex_Popup, 
{
	initialize : function($super, sMessage, fnCallback)
	{
		$super(35);
		this._fnCallback = fnCallback;
		this._buildUI(sMessage);
	},
	
	_buildUI : function(sMessage)
	{
		this._oFileTypeControl =	Control_Field.factory(
										'select',
										{
											sLabel		: 'Spreadsheet File Type',
											mMandatory	: true,
											mEditable	: true,
											fnPopulate	: Popup_Select_Spreadsheet_File_Type._getFileTypeOptions
										}
									);
		this._oFileTypeControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		
		var oContentDiv =	$T.div({class: 'popup-select-file-type'},
								$T.div({class: 'popup-select-file-type-message'},
									sMessage ? sMessage : 'Please choose a spreadsheet file type:'
								),
								this._oFileTypeControl.getElement(),
								$T.div({class: 'popup-select-file-type-buttons'},
									$T.button('OK').observe('click', this._ok.bind(this)),
									$T.button('Cancel').observe('click', this.hide.bind(this))
								)
							);
		
		this.addCloseButton();
		this.setTitle('Choose Spreadsheet File Type');
		this.setContent(oContentDiv);
		this.display();
	},
	
	_ok : function()
	{
		try
		{
			this._oFileTypeControl.validate(false);
			this._oFileTypeControl.save(true);
		}
		catch (oEx)
		{
			Reflex_Popup.alert(oEx);
			return;
		}

		this.hide();
		
		if (this._fnCallback)
		{
			this._fnCallback(this._oFileTypeControl.getValue());
		}
	}
});

Object.extend(Popup_Select_Spreadsheet_File_Type, 
{
	_getFileTypeOptions : function(fnCallback, oResponse)
	{
		var aOptions = [];
		aOptions.push(
			$T.option({value: 'CSV'},
				'CSV'	
			)
		);
		aOptions.push(
			$T.option({value: 'Excel2007'},
				'Excel 2007'	
			)
		);
		fnCallback(aOptions);
	},	

	_ajaxError : function(oResponse)
	{
		var sMessage = (oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.');
		Reflex_Popup.alert(sMessage, {sTitle: 'Error'});
	}
});