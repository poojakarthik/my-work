
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
			oCustomerGroup	: {
				id				: 1,
				internal_name	: "Telcoblue",
				external_name	: "Telcoblue"
			},
			oAccounts	: {
				'1000154811'	: {id: 1000154811, account_group_id: 1000154811, account_name: 'Telco Blue', primary_contact_id: 24},
				'1000154803'	: {id: 1000154803, account_group_id: 1000154811, account_name: 'Telco Blue Pty Ltd', primary_contact_id: 152},
				'1000180081'	: {id: 1000180081, account_group_id: 1000154811, account_name: 'Ryan Forrester', primary_contact_id: 948},
				'1000160069'	: {id: 1000160069, account_group_id: 1000154811, account_name: 'Scott Hales', primary_contact_id: 518}
			},
			oContacts	: {
				'50'	: {id: 50, first_name: 'Rich', last_name: 'Davis', account_id: 1000154811, account_group_id: 1000154811, is_shared_contact: true},
				'152'	: {id: 152, first_name: 'Mark', last_name: 'Shield', account_id: 1000154811, account_group_id: 1000154811, is_shared_contact: false},
				'24'	: {id: 24, first_name: 'Julie', last_name: 'Snelson', account_id: 1000154811, account_group_id: 1000154811, is_shared_contact: true},
				'518'	: {id: 518, first_name: 'Scott', last_name: 'Hales', account_id: 1000160069, account_group_id: 1000154811, is_shared_contact: true},
				'948'	: {id: 948, first_name: 'Ryan', last_name: 'Forrester', account_id: 1000180081, account_group_id: 1000154811, is_shared_contact: true}
			}
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

		oNewAccountButton.on('click', this._linkAccount.bind(this));

		oComponentSection.getAttachmentNode('header-actions').appendChild(oContactsButton);
		oComponentSection.getAttachmentNode('header-actions').appendChild(oNewAccountButton);
	},

	_syncUI	: function () {
		//debugger;
		this.NODE.select('.component-section').first().oReflexComponent.set('sTitle', "Accounts Linked to " + this.get('iAccountId'));

		if (!this._oData) {
			// Need to load additional data first
			//----------------------------------------------------------------//
			this._load();
		} else {
			// Remove existing data contents
			//----------------------------------------------------------------//
			this.NODE.select('.component-account-links-list-item').each(Element.remove);

			// Add new elements
			//----------------------------------------------------------------//
			// Items
			for (var i in this._oData.oAccounts) {
				this._buildItemUI(this._oData.oAccounts[i]);
			}

			// Component is ready
			//----------------------------------------------------------------//
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

	_linkAccount	: function () {
		var	iAccountId		= this.get('iAccountId'),
			oDatasetAJAX	= new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, {sObject: 'Account', sMethod: 'searchCustomerGroup'}),	// FIXME
			oAccountSearch	= Control_Field.factory('text-ajax', {
				mEditable				: true,
				bRenderMode				: Control_Field.RENDER_MODE_EDIT,
				sLabel					: 'Account',
				oDatasetAjax			: oDatasetAJAX,
				sDisplayValueProperty	: 'Id',
				oColumnProperties		: {
					Id				: {sClass: 'component-account-links-popup-link-account-list-id'},
					BusinessName	: {sClass: 'component-account-links-popup-link-account-list-name'}
				},
				iResultLimit			: 10,
				sResultPaneClass		: 'component-account-links-popup-link-account-list',
				fnValidate				: (function (mValue) {
					if (this._oData.oAccounts.hasOwnProperty(mValue)) {
						throw "Account " + mValue + " is already linked to " + iAccountId;
					}
					return true;
				}).bind(this)
			});
		oAccountSearch.getFilter().addFilter('customer_group', {iType: Filter.FILTER_TYPE_VALUE});
		oAccountSearch.getFilter().setFilterValue('customer_group', this._oData.oCustomerGroup.id);
		
		var	oPopupContent	= $T.div({'class':'component-account-links-popup-link'},
				$T.p("Search for a " + this._oData.oCustomerGroup.internal_name + " Account to link with " + iAccountId),
				oAccountSearch.getElement()
			),
			oPopupOK		= $T.button({onclick:Reflex_Popup.alert.curry('LOL, jk!')},
				$T.img({src:'../admin/img/template/link_add.png', alt:'', title:'Link to ' + iAccountId, 'class':'icon'}),
				$T.span("Link to " + iAccountId)
			),
			oPopupCancel	= $T.button(
				$T.img({src:'../admin/img/template/delete.png', alt:'', title:'Cancel', 'class':'icon'}),
				$T.span('Cancel')
			),
			oPopup			= Reflex_Popup.factory(oPopupContent, {
				iWidth			: 30,
				aFooterButtons	: [oPopupOK, oPopupCancel],
				bClosable		: true,
				bAutoDisplay	: true,
				sTitle			: "Link an Account to " + iAccountId,
				sIcon			: "../admin/img/template/link_add.png"
			});
		oPopupCancel.on('click', oPopup.hide.bind(oPopup));
	},

	_unlinkAccount	: function (iUnlinkAccountId) {
		var	oRetainedContactList	= $T.ul(),
			oLostContactList		= $T.ul(),
			oContent				= $T.div({'class':'component-account-links-popup-unlink'},
				$T.p('Are you sure you want to break ' + iUnlinkAccountId + ' out of this Account Group?'),
				$T.p('The following Contacts will be retained for this Account:'),
				oRetainedContactList
			),
			aContacts				= this._getContactsForAccount(iUnlinkAccountId),
			oLI;
		for (var i=0, l=aContacts.length; i < l; i++) {
			oLI	= $T.li(
				$T.a({href:'../admin/reflex.php/Contact/View/' + aContacts[i].id},
					aContacts[i].first_name + ' ' + aContacts[i].last_name
				)
			);
			
			if (this._oData.oAccounts[iUnlinkAccountId].primary_contact_id === aContacts[i].id) {
				// Primary Contact
				oLI.insertBefore($T.img({src:'../admin/img/template/primary_contact.png', alt:'Primary Contact', title:'Primary Contact'}), oLI.firstChild);
			}
			
			// Add to the appropriate Lists
			if (this._oData.oAccounts[iUnlinkAccountId].primary_contact_id === aContacts[i].id || aContacts[i].account_id === iUnlinkAccountId) {
				// Primary Contacts and Account Contacts are retained
				oRetainedContactList.insertBefore(
					oLI,
					this._oData.oAccounts[iUnlinkAccountId].primary_contact_id === aContacts[i].id ? oRetainedContactList.firstChild : null
				);
			} else {
				oLostContactList.appendChild(oLI);
			}
		}

		if (oLostContactList.childElements().length) {
			oContent.appendChild($T.p('The following Contacts will no longer be able to access this Account:'));
			oContent.appendChild(oLostContactList);
		}
		
		Reflex_Popup.yesNoCancel(oContent, {
			sTitle			: 'Unlink Account ' + iUnlinkAccountId,
			sIconSource		: '../admin/img/template/link_break.png',
			iWidth			: 30,
			bShowCancel		: false,
			sYesLabel		: 'Accept',
			sYesIconSource	: '../admin/img/template/tick.png',
			sNoLabel		: 'Cancel',
			sNoIconSource	: '../admin/img/template/delete.png',
			fnOnYes			: (function () {
				Reflex_Popup.alert("LOL, jk!");
			}).bind(this)
		});
	},

	_getContactsForAccount	: function (iAccountId) {
		if (!this._oData) {
			throw "Data must be loaded before calling _getContactsForAccount()";
		}

		var aContacts	= [];
		for (var iContactId in this._oData.oContacts) {
			if (this._oData.oContacts[iContactId].account_id === iAccountId) {
				// Contact is tied to the Account
				aContacts.push(this._oData.oContacts[iContactId]);
			} else if (this._oData.oContacts[iContactId].is_shared_contact) {
				// Contact is shared for this Account Group
				aContacts.push(this._oData.oContacts[iContactId]);
			} else if (this._oData.oAccounts[iAccountId].primary_contact_id == iContactId) {
				// Contact is the Primary Contact for this Account
				aContacts.unshift(this._oData.oContacts[iContactId]);
			}
		}

		return aContacts;
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
