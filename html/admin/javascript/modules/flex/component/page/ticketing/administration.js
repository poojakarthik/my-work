
var $D = require('fw/dom/factory'),
	Class = require('fw/class'),
	TicketingConfig = require('./administration/ticketingconfig');

var self = Class.create({
	'extends' : require('fw/component'),

	construct : function() {
		this.CONFIG = Object.extend({}, this.CONFIG || {});
		this._super.apply(this, arguments);
		this.NODE.addClassName('flex-page-ticketing-administration');
	},

	_buildUI : function() {
		this.NODE = $D.div(
			$D.table({'class': 'reflex highlight-rows flex-page-ticketing-administration-mailsources'},
				$D.caption(
					$D.div({'class': "caption_bar"},
						$D.div({'class': "caption_title"},
							'Mail Sources'
						),
						$D.div({'class': "caption_options"},
							$D.button({'class': 'icon-button', onclick: this._newTicketingConfig.bind(this)},
								$D.img({src: '/admin/img/template/new.png'}),
								$D.span('New Mail Source')
							)
						)
					)
				),
				$D.thead({'class': 'header'},
					$D.tr(
						$D.th('Protocol'),
						$D.th('Host'),
						$D.th('Port'),
						$D.th('Username'),
						$D.th('Password'),
						$D.th('Use SSL'),
						$D.th('Archive Folder'),
						$D.th('Status'),
						$D.th()
					)
				),
				$D.tfoot({'class': 'footer'},
					$D.tr(
						$D.th({colspan: 10})
					)
				),
				$D.tbody({class: 'alternating'})
			),
			$D.table({'class': 'reflex highlight-rows flex-page-ticketing-administration-customergroupconfig'},
				$D.caption(
					$D.div({id: "caption_bar", 'class': "caption_bar"},
						$D.div({id: "caption_title", 'class': "caption_title"},
							'Customer Group Configuration'
						),
						$D.div({id: "caption_options", 'class': "caption_options"})
					)
				),
				$D.thead({'class': 'header'},
					$D.tr(
						$D.th('Name'),
						$D.th(' ')
					)
				),
				$D.tfoot({'class': 'footer'},
					$D.tr(
						$D.th({colspan: 2})
					)
				),
				$D.tbody({class: 'alternating'})
			)
		);
	},

	_syncUI : function() {
		if (!this._bReady) {
			this._loadCustomerGroups(
				this._loadMailSources.bind(this, 
					this._onReady.bind(this)));
		} else {
			this._onReady();
		}
	},

	_loadCustomerGroups : function(fnCallback, oResponse) {
		if (!oResponse) {
			// Request
			var fnResp = this._loadCustomerGroups.bind(this, fnCallback);
			var fnReq = jQuery.json.jsonFunction(fnResp, fnResp, 'Customer_Group', 'getAll');
			fnReq();
			return;
		} else {
			if (!oResponse.bSuccess) {
				// Error
				jQuery.json.errorPopup(oResponse);
			} else {
				if (typeof oResponse.aResults.length == 'undefined') {
					for (var i in oResponse.aResults) {
						var oRecord = oResponse.aResults[i];
						this.select('.flex-page-ticketing-administration-customergroupconfig tbody')[0].appendChild($D.tr(
							$D.td(oRecord.internal_name),
							$D.td({'class': 'flex-page-ticketing-administration-actions'},
								$D.a({href: '/admin/reflex.php/Ticketing/GroupAdmin/' + oRecord.Id + '/View'},
									'View'
								)
							)
						));
					}
				}

				if (fnCallback) {
					fnCallback();
				}
			}
		}
	},

	_loadMailSources : function(fnCallback, oResponse) {
		if (!oResponse) {
			// Request
			var oReq = new Reflex_AJAX_Request('Ticketing_Config', 'getAll', this._loadMailSources.bind(this, fnCallback));
			oReq.send();
		} else {
			if (oResponse.hasException()) {
				// Error
				Reflex_AJAX_Response.errorPopup(oResponse);
			} else {
				// Success
				var oRecords = oResponse.get('oRecords');
				var oTBody = this.select('.flex-page-ticketing-administration-mailsources tbody')[0];
				oTBody.innerHTML = '';
				if (typeof oRecords.length == 'undefined') {
					for (var i in oRecords) {
						var oRecord = oRecords[i];
						oTBody.appendChild($D.tr({'class': (oRecord.is_active ? '-active' : '-inactive')},
							$D.td(oRecord.protocol),
							$D.td(oRecord.host),
							$D.td(oRecord.port),
							$D.td(oRecord.username ? oRecord.username : ''),
							$D.td(oRecord.password ? oRecord.password : ''),
							$D.td(oRecord.use_ssl ? 'Yes' : 'No'),
							$D.td(oRecord.archive_folder_name ? oRecord.archive_folder_name : ''),
							$D.td(oRecord.is_active ? 'Active' : 'Inactive'),
							$D.td({'class': 'flex-page-ticketing-administration-actions'},
								$D.img({src: '/admin/img/template/remove.png', title: 'Deactivate this Mail Source', onclick: this._deactivateTicketingConfig.bind(this, oRecord, null)}),
								$D.img({src: '/admin/img/template/edit.png', title: 'Edit this Mail Source', onclick: this._editTicketingConfig.bind(this, oRecord)})
							)
						));
					}
				}

				if (fnCallback) {
					fnCallback();
				}
			}
		}
	},

	_editTicketingConfig : function(oTicketingConfig) {
		var oPopup = TicketingConfig.createAsPopup({
			oTicketingConfig : oTicketingConfig,
			onready : function() {
				oPopup.display();
			},
			onsave : function() {
				this._loadMailSources();
				oPopup.hide();
			}.bind(this),
			onclose : function() {
				oPopup.hide();
			}
		});
	},

	_newTicketingConfig : function() {
		var oPopup = TicketingConfig.createAsPopup({
			onready : function() {
				oPopup.display();
			},
			onsave : function() {
				this._loadMailSources();
				oPopup.hide();
			}.bind(this),
			onclose : function() {
				oPopup.hide();
			}
		});
	},

	_deactivateTicketingConfig : function(oTicketingConfig, oResponse) {
		// Update
		if (!oResponse) {
			// Mark as inactive
			oTicketingConfig.is_active = 0;

			// Make request
			this._oLoading = new Reflex_Popup.Loading('Deactivating Mail Source...', true);
			var oReq = new Reflex_AJAX_Request('Ticketing_Config', 'updateRecord', this._deactivateTicketingConfig.bind(this, oTicketingConfig));
			oReq.send(oTicketingConfig.id, oTicketingConfig);	
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
				// Success
				this._loadMailSources();
			}
		}
	},

	statics : {
		
	}
});

return self;