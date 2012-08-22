var Class = require('fw/class'),
	$D = require('fw/dom/factory'),
	Component = require('fw/component'),
	Form = require('fw/component/form'),
	Control = require('fw/component/control'),
	Select = require('fw/component/control/select'),
	Text = require('fw/component/control/text'),
	Number = require('fw/component/control/number'),
	Checkbox = require('fw/component/control/checkbox'),
	Hidden = require('fw/component/control/hidden');

var self = Class.create({
	'extends' : Component,

	construct : function() {
		this.CONFIG = Object.extend({
			oTicketingConfig : {}
		}, this.CONFIG || {});
		this._super.apply(this, arguments);
		this.NODE.addClassName('flex-page-ticketing-administration-ticketingconfig');
	},

	_buildUI : function() {
		this._oForm = new Form({onsubmit: this._formSubmitted.bind(this)},
			$D.div({'class': 'reflex-propertylist'},
				$D.dt('Protocol'),
				$D.dd(
					new Select({
						sName : 'protocol',
						sLabel : 'Protocol',
						fnPopulate : function(fnCallback) {
							fnCallback([
								$D.option({value: 'IMAP'},
									'IMAP'
								)/*, // These options not supported currently
								$D.option({value: 'POP3'},
									'Pop3'
								),
								$D.option({value: 'MAILDIR'},
									'Maildir'
								),
								$D.option({value: 'MBOX'},
									'Mbox'
								),
								$D.option({value: 'XML'},
									'XML'
								)*/
							]);
						},
						mMandatory : true
					})
				),
				$D.dt('Host'),
				$D.dd(
					new Text({
						sName : 'host',
						sLabel : 'Host',
						mMandatory : true
					})
				),
				$D.dt('Port'),
				$D.dd(
					new Number({
						sName : 'port',
						sLabel : 'Port',
						iDecimalPlaces : 0
					})
				),
				$D.dt('Username'),
				$D.dd(
					new Text({
						sName : 'username',
						sLabel : 'Username'
					})
				),
				$D.dt('Password'),
				$D.dd(
					new Text({
						sName : 'password',
						sLabel : 'Password'
					})
				),
				$D.dt('Use SSL'),
				$D.dd(
					new Checkbox({
						sName : 'use_ssl',
						sLabel : 'Use SSL',
						mValue : 1
					})
				),
				$D.dt('Archive Folder'),
				$D.dd(
					new Text({
						sName : 'archive_folder_name',
						sLabel : 'Archive Folder'
					})
				),
				new Hidden({
					sName : 'is_active'
				})
			),
			$D.div({'class': 'flex-page-ticketing-administration-ticketingconfig-buttons'},
				$D.button({'class': 'icon-button flex-page-ticketing-administration-ticketingconfig-buttons-save'},
					$D.img({src: '/admin/img/template/tick.png'}),
					$D.span('Save')
				),
				$D.button({'class': 'icon-button flex-page-ticketing-administration-ticketingconfig-buttons-reactivate', type: 'button', onclick: this._reactivate.bind(this)},
					$D.img({src: '/admin/img/template/tick.png'}),
					$D.span('Reactivate')
				),
				$D.button({'class': 'icon-button', type: 'button', onclick: this.fire.bind(this, 'close')},
					$D.img({src: '/admin/img/template/close.png'}),
					$D.span('Close')
				)
			)
		);

		this.NODE = this._oForm.getNode();
	},

	_syncUI : function() {
		if (!this._bReady) {
			var oTicketingConfig = this.get('oTicketingConfig');
			if (oTicketingConfig) {
				// Editing an existing record, load the current properties
				this._oForm.control('protocol').set('mValue', oTicketingConfig.protocol);
				this._oForm.control('host').set('mValue', oTicketingConfig.host);
				this._oForm.control('port').set('mValue', oTicketingConfig.port);
				this._oForm.control('username').set('mValue', oTicketingConfig.username);
				this._oForm.control('password').set('mValue', oTicketingConfig.password);
				this._oForm.control('use_ssl').set('bChecked', oTicketingConfig.use_ssl == 1);
				this._oForm.control('archive_folder_name').set('mValue', oTicketingConfig.archive_folder_name);
				this._oForm.control('is_active').set('mValue', oTicketingConfig.is_active);

				if (!oTicketingConfig.is_active) {
					this._oForm.control('protocol').set('iControlState', Control.STATE_READ_ONLY);
					this._oForm.control('host').set('iControlState', Control.STATE_READ_ONLY);
					this._oForm.control('port').set('iControlState', Control.STATE_READ_ONLY);
					this._oForm.control('username').set('iControlState', Control.STATE_READ_ONLY);
					this._oForm.control('password').set('iControlState', Control.STATE_READ_ONLY);
					this._oForm.control('use_ssl').set('iControlState', Control.STATE_READ_ONLY);
					this._oForm.control('archive_folder_name').set('iControlState', Control.STATE_READ_ONLY);
					this.NODE.addClassName('-inactive');
				}
			}

			this._onReady();
		} else {
			this._onReady();
		}
	},

	_formSubmitted : function(oEvent, oResponse) {
		if (!oResponse) {
			// Make request
			this._oLoading = new Reflex_Popup.Loading('Saving...', true);
			
			if (this.get('oTicketingConfig')) {
				// Update
				var oReq = new Reflex_AJAX_Request('Ticketing_Config', 'updateRecord', this._formSubmitted.bind(this, null));
				oReq.send(this.get('oTicketingConfig').id, oEvent.getTarget().getData());
			} else {
				// Create
				var oReq = new Reflex_AJAX_Request('Ticketing_Config', 'createRecord', this._formSubmitted.bind(this, null));
				oReq.send(oEvent.getTarget().getData());
			}			
		} else {
			// Response, hide loading
			this._oLoading.hide();

			if (oResponse.hasException('Exception_Set')) {
				// Validation error
				var oException = oResponse.getException();
				var aExceptions = oException.mData;
				var oErrorElement = $T.ul();
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
					sTitle : 'Database Error',
					sDebugContent : oResponse.getDebugLog()
				});
			} else {
				this.fire('save');
			}
		}
	},

	_reactivate : function() {
		this._oForm.control('is_active').set('mValue', '1');
		this._oForm.submit();
	},

	statics : {
		createAsPopup : function() {
			var oComponent = self.applyAsConstructor(arguments);
			var oPopup = new Reflex_Popup.factory(oComponent.getNode(), {
				sTitle : (oComponent.get('oTicketingConfig') ? 'Edit Mail Source' : 'New Mail Source'),
				iWidth : 40
			});
			return oPopup;
		}
	}
});

return self;