
var Component_Account_User_Edit = Class.create(Reflex_Component, {
	initialize : function($super) {
		// Additional Configuration
		this.CONFIG	= Object.extend({
			iAccountId		: {},
			iAccountUserId 	: {}
		}, this.CONFIG || {});
		
		this._sValidUsername = null;
		
		// Parent Constructor
		$super.apply(this, $A(arguments).slice(1));
		
		this.NODE.addClassName('component-account-user-edit');
	},
	
	_buildUI : function() {
		this._oForm = new Form({onsubmit: this._formSubmitted.bind(this, null)},
			new Control_Hidden({
				sName : 'id'
			}),
			new Control_Hidden({
				sName : 'account_id'
			}),
			$T.dl({'class': 'reflex-propertylist'},
				$T.dt('Username'),
				$T.dd(
					new Control_Text({
						sName 		: 'username',
						sLabel		: 'Username',
						mMandatory	: true,
						fnValidate	: this._validateUsername.bind(this)
					})
				),
				$T.dt('Given Name'),
				$T.dd(
					new Control_Text({
						sName 		: 'given_name',
						sLabel		: 'Given Name',
						mMandatory	: true
					})
				),
				$T.dt('Family Name'),
				$T.dd(
					new Control_Text({
						sName 		: 'family_name',
						sLabel		: 'Family Name',
						mMandatory	: true
					})
				),
				$T.dt('Email'),
				$T.dd(
					new Control_Text({
						sName 		: 'email',
						sLabel		: 'Email',
						mMandatory	: true,
						fnValidate	: function (oControl) {
							return Reflex_Validation.Exception.email(oControl.getValue());
						}
					})
				),
				$T.dt({'class': 'component-account-user-edit-change-password'},
					'Change Password'
				),
				$T.dd({'class': 'component-account-user-edit-change-password'},
					new Control_Checkbox({
						sName		: 'change_password',
						onchange 	: this._changePassword.bind(this)
					})
				),
				$T.dt({'class': 'component-account-user-edit-password'},
					'Password'
				),
				$T.dd({'class': 'component-account-user-edit-password'},
					new Control_Password({
						sName 		: 'new_password',
						sLabel		: 'Password',
						mMandatory	: this._passwordMandatory.bind(this)
					})
				),
				$T.dt({'class': 'component-account-user-edit-password'},
					'Confirm Password'
				),
				$T.dd({'class': 'component-account-user-edit-password'},
					new Control_Password({
						sName 		: 'confirm_password',
						sLabel		: 'Confirm Password',
						mMandatory	: this._passwordMandatory.bind(this),
						fnValidate	: this._validatePasswordConfirmation.bind(this)
					})
				),
				$T.dt({'class': 'component-account-user-edit-status'},
					'Status'
				),
				$T.dd({'class': 'component-account-user-edit-status'},
					new Control_Select({
						sName 		: 'status_id',
						sLabel		: 'Status',
						mMandatory	: this._statusMandatory.bind(this),
						fnPopulate	: Flex.Constant.getConstantGroupOptions.curry('status')
					})
				)
			),
			$T.div({'class': 'component-account-user-edit-buttons'},
				$T.button({'class': 'icon-button'},
					$T.img({src: '../admin/img/template/tick.png'}),
					$T.span('Save')
				),
				$T.button({type: 'button', 'class': 'icon-button', onclick: this.fire.bind(this, 'cancel')},
					$T.img({src: '../admin/img/template/delete.png'}),
					$T.span('Cancel')
				)
			)
		);
		this.NODE = this._oForm.getNode();
	},
	
	_syncUI : function() {
		this._oForm.control('account_id').setValue(this.get('iAccountId'));
		
		if (this.get('iAccountUserId')) {
			// Editing user
			this._loadAccountUser();
		} else {
			// New user
			this.select('.component-account-user-edit-change-password').each(Element.hide);
			this.select('.component-account-user-edit-status').each(Element.hide);
			this._onReady();
		}
	},
	
	_loadAccountUser : function(oResponse) {
		if (!oResponse) {
			// Request
			var oReq = new Reflex_AJAX_Request('Account_User', 'getForId', this._loadAccountUser.bind(this));
			oReq.send(this.get('iAccountUserId'));
		} else if (oResponse.hasException()) {
			// Error
			var oException = oResponse.getException();
			Reflex_Popup.alert(oException.sMessage || 'There was a critical error accessing the Flex Server', {
				sTitle			: 'Database Error',
				sDebugContent	: oResponse.getDebugLog()
			});
		} else {
			// Success
			var oUser = oResponse.getData();
			
			this.select('.component-account-user-edit-password').each(Element.hide);
			this._oForm.control('username').set('iControlState', Control.STATE_READ_ONLY);
			
			this._oForm.control('id').setValue(oUser.id);
			this._oForm.control('username').setValue(oUser.username);
			this._oForm.control('given_name').setValue(oUser.given_name);
			this._oForm.control('family_name').setValue(oUser.family_name);
			this._oForm.control('email').setValue(oUser.email);
			this._oForm.control('status_id').setValue(oUser.status_id);
			this._onReady();
		}
	},
	
	_formSubmitted : function(oResponse) {
		if (!oResponse) {
			var oReq = new Reflex_AJAX_Request('Account_User', 'save', this._formSubmitted.bind(this));
			oReq.send(this._oForm.getData());
		} else if (oResponse.hasException('Exception_Set')) {
			// Validation error
			var oException		= oResponse.getException();
			var aExceptions		= oException.mData;
			var oErrorElement 	= $T.ul();
			for (var i = 0; i < aExceptions.length; i++) {
				oErrorElement.appendChild($T.li(aExceptions[i].sMessage));
			}
			
			Reflex_Popup.alert(
				$T.div({class: 'validation-error-content'},
					$T.div('There were errors in the form:'),
					oErrorElement
				),
				{sTitle: 'Validation Error', iWidth: 30}
			);
		} else if (oResponse.hasException()) {
			// Error
			var oException = oResponse.getException();
			Reflex_Popup.alert(oException.sMessage || 'There was a critical error accessing the Flex Server', {
				sTitle			: 'Database Error',
				sDebugContent	: oResponse.getDebugLog()
			});
		} else {
			// Success
			this.fire('complete');
		}
	},
	
	_passwordMandatory : function() {
		return !this.get('iAccountUserId') || (this._oForm && this._oForm.control('change_password').get('bChecked'));
	},
	
	_validatePasswordConfirmation : function(oControl) {
		if (oControl.getValue() != this._oForm.control('new_password').getValue()) {
			throw new Error("Password confirmation must match password");
		}
		return true;
	},
	
	_statusMandatory : function() {
		return this.get('iAccountUserId');
	},
	
	_changePassword : function(oEvent) {
		if (oEvent.getTarget().get('bChecked')) {
			this.select('.component-account-user-edit-password').each(Element.show);
		} else {
			this.select('.component-account-user-edit-password').each(Element.hide);
		}
	},
	
	_validateUsername : function(oControl) {
		var sUsername = oControl.getValue();
		if (this._sValidUsername == sUsername) {
			return true;
		} else {
			this._checkUsername(sUsername);
			throw new Error("Username is not unique, please choose another");
		}
	},
	
	_checkUsername : function(sUsername, oResponse) {
		if (!oResponse) {
			var oReq = new Reflex_AJAX_Request('Account_User', 'checkUsername', this._checkUsername.bind(this, sUsername));
			oReq.send(sUsername);
		} else if (oResponse.hasException()) {
			// Error
			var oException = oResponse.getException();
			Reflex_Popup.alert(oException.sMessage || 'There was a critical error accessing the Flex Server', {
				sTitle			: 'Database Error',
				sDebugContent	: oResponse.getDebugLog()
			});
		} else {
			// Success
			if (oResponse.get('bUnique') === true) {
				this._sValidUsername = sUsername;
				this._oForm.control('username').validate();
			} else {
				this._sValidUsername = null;
			}
		}
	}
});

Object.extend(Component_Account_User_Edit, {
	createAsPopup : function() {
		var	oComponent	= Component_Account_User_Edit.constructApply($A(arguments)),
		oPopup			= new Reflex_Popup(40);
		oPopup.setTitle((oComponent.get('iAccountUserId') ? 'Edit' : 'New') + ' Customer Portal User');
		oPopup.addCloseButton();
		oPopup.setContent(oComponent.getNode());	
		return oPopup;
	}
});
