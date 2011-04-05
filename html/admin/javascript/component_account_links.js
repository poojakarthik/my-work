
Component_Account_Links	= Class.create(/* extends */Reflex_Component, {

	initialize	: function ($super) {
		//debugger;
		// Additional Configuration
		this.CONFIG	= Object.extend({
			'iAccountId'	: {}
		}, this.CONFIG || {});

		// Parent Constructor
		$super.apply(this, $A(arguments).slice(1));

		this.NODE.addClassName('component-account-links');
	},

	_load	: function (oResponse) {
		/* DEBUG */
		this._oData	= {
			aAccounts	: [
				{id: 1000154811, account_name: 'Telco Blue'},
				{id: 1000154803, account_name: 'Telco Blue Pty Ltd'},
				{id: 1000180081, account_name: 'Ryan Forrester'},
				{id: 1000160069, account_name: 'Scott Hales'}
			]
		};
		this._syncUI();
		/* /DEBUG */

		if (!oResponse || oResponse.element) {
			// No Response (or Response is an Event): Request Data
			jQuery.json.jsonFunction(this._load.bind(this))
		} else if (!oResponse.bSuccess) {
			// Error
			this._oLoadingPopup.hide();
			Reflex_Popup.alert(oResponse.sMessage || 'There was a critical error accessing the Flex Server', {
				sTitle			: 'Database Error',
				sDebugContent	: oResponse.sDebug
			});
		} else {
			// Success
			this._oData	= oResponse.oData;
			this._syncUI();
		}
	},

	_buildUI	: function () {
		//debugger;
		this.NODE	= $T.div(
			new Component_Section({sIcon:'../admin/img/template/link.png',sTitle:'Linked Accounts'},
				$T.ul({'class':'component-account-links-list'})
			)
		);

		var	oNewAccountButton	= $T.button({type:'button'},
				$T.img({src:'../admin/img/template/link_add.png', 'class':'icon', alt:'', title:'Link another Account'}),
				$T.span('Link another Account')
			),
			oContactsButton	= $T.button({type:'button'},
				$T.img({src:'../admin/img/template/contacts.png', 'class':'icon', alt:'', title:'Manage Contacts'}),
				$T.span('Manage Contacts')
			),
			oComponentSection	= this.NODE.select('.component-section').first().oReflexComponent;
		oComponentSection.getAttachmentNode('header-actions').appendChild(oContactsButton);
		oComponentSection.getAttachmentNode('header-actions').appendChild(oNewAccountButton);
	},

	_syncUI	: function () {
		//debugger;
		this.NODE.select('.component-section').first().oReflexComponent.set('sTitle', "Accounts Linked to " + this.get('iAccountId'));

		if (!this._oData) {
			// Need to load additional data first
			this._load();
		} else {
			// Items
			for (var i=0, j=this._oData.aAccounts.length; i < j; i++) {
				this._buildItemUI(this._oData.aAccounts[i]);
			}

			// Component is ready
			this._onReady();
		}
	},

	_buildItemUI	: function (oAccount) {
		var	oLI	= $T.li({'class':'component-account-links-list-item'},
			$T.div(
				$T.a({href:'../admin/flex.php/Account/Overview/?Account.Id='+oAccount.id},
					oAccount.id,
					': ',
					oAccount.account_name
				)
			),
			$T.img({src:'../admin/img/template/link_break.png','class':'icon component-account-links-list-item-unlink',alt:'Unlink',title:'Unlink'})
		);
		oLI.select('.component-account-links-list-item-unlink').first().observe('click', this._unlinkAccount.bind(this, oAccount.id));
		this.NODE.select('.component-account-links-list').first().appendChild(oLI);
	},

	_newAccountLink	: function (iNewAccountId) {
		// TODO: Create a popup to link the Accounts
		var	iAccountId	= this.get('iAccountId');
	},

	_unlinkAccount	: function (iUnlinkAccountId) {
		// TODO: Create a popup to link the Accounts
		var	iAccountId	= this.get('iAccountId');
	}
});

Component_Account_Links.createAsPopup	= function () {
	var	oComponentAccountLinks	= Component_Account_Links.constructApply($A(arguments)),
		oPopup					= new Reflex_Popup(45),
		oFooterCloseButton		= $T.button(
			$T.img({src:'../admin/img/template/tick.png','class':'icon',alt:''}),
			$T.span('OK')
		);
	oFooterCloseButton.observe('click', oPopup.hide.bind(oPopup));

	oPopup.setTitle('Manage Linked Accounts');
	oPopup.setIcon('../admin/img/template/link.png');
	oPopup.addCloseButton();
	oPopup.setFooterButtons([
		oFooterCloseButton
	], true);

	oPopup.setContent(oComponentAccountLinks.getNode());

	return oPopup;
};
