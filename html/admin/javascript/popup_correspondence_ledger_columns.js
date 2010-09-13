
var Popup_Correspondence_Ledger_Columns	= Class.create(Reflex_Popup, 
{
	initialize	: function($super, hColumns, fnCallback)
	{
		$super(0);
		this.container.style.width = 'auto';
		
		this._fnCallback	= fnCallback;
		this._hCheckboxes	= {};
		
		var oUL			= $T.ul({class: 'columns reset horizontal'});
		var oContent	= 	$T.div({class: 'popup-correspondence-ledger-columns'},
								oUL,
								$T.div({class: 'buttons'},
									$T.button({class: 'icon-button'},
										'OK'
									).observe('click', this._useSelected.bind(this))
								)
							);
		var i			= 0;
		var oCurrentUL	= null;
		for (var sColumn in hColumns)
		{
			if ((i == Popup_Correspondence_Ledger_Columns.MAX_LINES) || !oCurrentUL)
			{
				// New 'column'
				oCurrentUL	= $T.ul({class: 'reset'});
				oUL.appendChild(
					$T.li(oCurrentUL)
				);
			}
			
			var oCheckbox		= $T.input({type: 'checkbox'});
			oCheckbox.checked	= !!hColumns[sColumn];
			
			var sDisplayName	= sColumn;
			if (Popup_Correspondence_Ledger_Columns.COLUMN_DISPLAY_NAMES[sColumn])
			{
				sDisplayName	= Popup_Correspondence_Ledger_Columns.COLUMN_DISPLAY_NAMES[sColumn];
			}
			
			oCurrentUL.appendChild(
				$T.li(
					oCheckbox,
					$T.span({class: 'pointer'},
						sDisplayName
					).observe('click', this._toggleCheckbox.bind(this, oCheckbox))
				)
			);
			
			this._hCheckboxes[sColumn]	= oCheckbox;
			i++;
		}
		
		this.setContent(oContent);
		this.setTitle('Choose Columns to Show');
		this.addCloseButton();
		this.display();
	},
	
	_toggleCheckbox	: function(oCheckbox)
	{
		oCheckbox.checked	= !oCheckbox.checked;
	},
	
	_useSelected	: function()
	{
		var hColumns	= {};
		for (var sColumn in this._hCheckboxes)
		{
			hColumns[sColumn]	= (this._hCheckboxes[sColumn].checked ? true : false);
		}
		
		if (this._fnCallback)
		{
			this._fnCallback(hColumns);
		}
		this.hide();
	}
});

// Static

Object.extend(Popup_Correspondence_Ledger_Columns, 
{
	MAX_LINES	: 4,
	
	COLUMN_DISPLAY_NAMES	:
	{
		id									: 'Id',
		account_id							: 'Account Id',
		customer_group_name					: 'Customer Group',
		correspondence_delivery_method_name	: 'Correspondence Delivery Method',
		addressee							: 'Addressee',
		address								: 'Address'
	}
});