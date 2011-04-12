
var Component_Account_Merge_Contacts_List = Class.create(Reflex_Component, {
	
	initialize : function($super) {
		// Additional Configuration
		this.CONFIG	= Object.extend({
			'iAccountId'	: {},
			'fnOnComplete'	: {}
		}, this.CONFIG || {});
	
		// Parent Constructor
		$super.apply(this, $A(arguments).slice(1));
		
		this._hCheckboxes = null;
		
		this.NODE.addClassName('component-account-merge-contacts-list');
	},
	
	// Public
	
	getCompletionCallback : function() {
		return this.get('fnOnComplete');
	},
	
	doMerge : function() {
		this._doMerge();
	},
	
	hideTitle : function() {
		this.NODE.select('.component-section .component-section-header h3').first().hide();
	},
	
	// Protected
	
	_load	: function (oResponse) {
		if (!oResponse || oResponse.element) {
			// No Response (or Response is an Event): Request Data
			var fnResp 	= this._load.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Contact', 'getLinkedContactsForAccount');
			fnReq(this.get('iAccountId'));
		} else if (!oResponse.bSuccess) {
			// Error
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
		this.NODE	= $T.div(
			new Component_Section({sIcon:'../admin/img/template/contacts.png', sTitle: 'Contacts'},
				$T.ul({class: 'reset component-account-merge-contacts-list-contactlist'})
			)
		);
		
		// Setup section
		this.NODE.select('.component-section').first().oReflexComponent.set('sTitle', "Contact Details");		
	},

	_syncUI	: function () {
		this.NODE.select('.component-section').first().oReflexComponent.set('sTitle', "Contacts Linked to Account #" + this.get('iAccountId'));

		if (!this._oData) {
			// Need to load additional data first
			this._load();
		} else {
			// Remove existing contacts
			this.NODE.select('.component-account-merge-contacts-list-contactlist-item').each(Element.remove);
			
			// Add contact Items
			this._hCheckboxes = {};
			for (var i in this._oData) {
				var oContact			= this._oData[i];
				var oCheckboxControl	= Control_Field.factory('checkbox', {mEditable: true});
				oCheckboxControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
				var	oLI	= $T.li({class: 'component-account-merge-contacts-list-contactlist-item'},
					$T.ul({class: 'reset horizontal pointer'},
						$T.li({class: 'component-account-merge-contacts-list-contactlist-item-checkbox'},
							oCheckboxControl.getElement()
						),
						$T.li(
							oContact.FirstName + ' ' + oContact.LastName
						),
						$T.li(' (Account #' + oContact.Account + ')')
					).observe('click', this._toggleCheckbox.bind(this, oCheckboxControl))
				);
				this._hCheckboxes[i] = oCheckboxControl;
				this.NODE.select('.component-account-merge-contacts-list-contactlist').first().appendChild(oLI);
			}
						
			// Component is ready
			//----------------------------------------------------------------//
			this._onReady();
		}
	},
	
	_reSyncUI : function() {
		this._oData = null;
		this._syncUI();
	},
	
	_doMerge : function()
	{
		var hContacts 	= {};
		var iCount		= 0;
		for (var iId in this._hCheckboxes) {
			if (this._hCheckboxes[iId].getElementValue()) {
				hContacts[iId] = this._oData[iId];
				iCount++;
			}
		}
		
		if (iCount < 2) {
			Reflex_Popup.alert('Please choose two or more Contacts to merge together', {iWidth: 32});
			return;
		}
		
		// Show the merge popup
		var oPopup = Component_Account_Merge_Contacts.createAsPopup(
			{
				hContacts		: hContacts,
				fnOnComplete	: function() {
					oPopup.hide();
					this._reSyncUI();
				}.bind(this),
				fnOnReady		: function() {
					oPopup.display();
				}
			}
		);
	},
	
	_toggleCheckbox : function(oControl) {
		if (oControl.getElementValue()) {
			oControl.setValue(false);
		} else {
			oControl.setValue(true);
		}
	}
});

Object.extend(Component_Account_Merge_Contacts_List, {
	
	// Public
	
	createAsPopup : function () {
		var	oComponent		= Component_Account_Merge_Contacts_List.constructApply($A(arguments)),
			oPopup			= new Reflex_Popup(),
			oFooterButton	= $T.button(
				$T.img({src:'../admin/img/template/tick.png', class: 'icon', alt:''}),
				$T.span('Merge Contacts')
			).observe('click', oComponent.doMerge.bind(oComponent));
		
		oComponent.hideTitle();
		
		oPopup.setTitle('Manage Contacts');
		oPopup.setIcon('../admin/img/template/contacts.png');
		oPopup.addCloseButton(Component_Account_Merge_Contacts_List._hidePopupAndCallback.curry(oPopup, oComponent.getCompletionCallback()));
		oPopup.setFooterButtons([oFooterButton], true);
		oPopup.setContent(oComponent.getNode());

		return oPopup;
	},
	
	_hidePopupAndCallback : function(oPopup, fnCallback)
	{
		oPopup.hide();
		if (fnCallback)
		{
			fnCallback();
		}
	}
});
