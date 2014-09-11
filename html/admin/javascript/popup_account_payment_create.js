
var Popup_Account_Payment_Create = Class.create(Reflex_Popup,
{
	initialize : function($super, iAccountId)
	{
		$super(45);
	
		this._oComponent =	new Component_Account_Payment_Create(
								iAccountId, 
								Component_Account_Payment_Create.SAVE_MODE_SAVE,
								this._saveComplete.bind(this)
							);
		
		this.setTitle('New Payment');
		this.addCloseButton();
		this.setContent(
			$T.div({class: 'popup-account-payment-create'},
				this._oComponent.getElement(),
				$T.div({class: 'popup-account-payment-create-buttons'},
					$T.button({class: 'icon-button'},
						$T.img({src: '../admin/img/template/approve.png'}),
						$T.span('Save')
					).observe('click', this._oComponent.save.bind(this._oComponent)),
					$T.button({class: 'icon-button'},
						$T.span('Cancel')
					).observe('click', this.hide.bind(this))
				)
			)
		);
		this.display();
	},
	
	_saveComplete : function(iPaymentId)
	{
		// Update various components
		if (typeof Component_Account_Payment_List != 'undefined')
		{
			Component_Account_Payment_List.refreshInstances();
		}
		
		if (typeof Component_Account_Collections != 'undefined')
		{
			Component_Account_Collections.refreshInstances();
		}
		
		if (typeof Component_Account_Invoice_List != 'undefined')
		{
			Component_Account_Invoice_List.refreshInstances();
		}
		
		if (typeof Vixen.AccountDetails != 'undefined')
		{
			Vixen.AccountDetails.CancelEdit();
		}
		
		this.hide();
	}
});