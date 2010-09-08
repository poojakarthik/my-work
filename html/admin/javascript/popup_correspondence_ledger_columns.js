
var Popup_Correspondence_Ledger_Columns	= Class.create(Reflex_Popup, 
{
	initialize	: function($super, hColumns, fnCallback)
	{
		$super(60);
		
		this._fnCallback	= fnCallback;
		this._hCheckboxes	= {};
		
		var oUL			= $T.ul({class: 'reset horizontal'});
		var oContent	= 	$T.div(
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
			
			oCurrentUL.appendChild(
				$T.li(
					oCheckbox,
					$T.span(sColumn)
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
	MAX_LINES	: 4
});