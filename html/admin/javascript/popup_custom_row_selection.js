
var Popup_Custom_Row_Selection = Class.create(Reflex_Popup, 
{
	initialize : function($super, sItemType, fnOk)
	{
		$super();
		
		this._sItemType	= sItemType;
		this._fnOk 		= fnOk;
		
		this._buildUI();
	},
	
	_buildUI : function()
	{
		var oContent =	$T.div({class: 'popup-collections-event-management-select-events'},
							$T.div(
								$T.span('Select the '),
								$T.select({class: 'popup-collections-event-management-select-events-from'},
									$T.option({value: Popup_Custom_Row_Selection.SELECT_FIRST},
										'First'
									),
									$T.option({value: Popup_Custom_Row_Selection.SELECT_LAST},
										'Last'
									)
								),
								$T.input({class: 'popup-collections-event-management-select-events-number'}),
								$T.span(' ' + this._sItemType + '(s).')
							),
							$T.div({class: 'popup-collections-event-management-select-events-buttons'},
								$T.button('OK').observe('click', this._complete.bind(this)),
								$T.button('Cancel').observe('click', this.hide.bind(this))
							)
						);
		
		this.setContent(oContent);
		this.setTitle('Choose the ' + this._sItemType + '(s) to select');
		this.addCloseButton();
		this.display();
	},
	
	_complete : function()
	{
		var iSelect = parseInt(this.container.select('.popup-collections-event-management-select-events-from').first().value);
		var iNumber = parseInt(this.container.select('.popup-collections-event-management-select-events-number').first().value);
		
		switch (iSelect)
		{
			case Popup_Custom_Row_Selection.SELECT_FIRST:
				// Leave alone
				break;
				
			case Popup_Custom_Row_Selection.SELECT_LAST:
				iNumber	= -1 * iNumber;
				break;
		}
		
		if (this._fnOk)
		{
			this._fnOk(iNumber);
		}
		
		this.hide();
	}
});

Object.extend(Popup_Custom_Row_Selection,
{
	SELECT_FIRST	: 1,
	SELECT_LAST		: 2,
});