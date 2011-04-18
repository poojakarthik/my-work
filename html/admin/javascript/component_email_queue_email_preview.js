
var Component_Email_Queue_Email_Preview = Class.create(Reflex_Component, {
	
	initialize : function($super) {
		// Additional Configuration
		this.CONFIG	= Object.extend({
			'iEmailId' : {}
		}, this.CONFIG || {});
		
		// Parent Constructor
		$super.apply(this, $A(arguments).slice(1));
		
		this.NODE.addClassName('component-email-queue-email-preview');
	},
	
	_buildUI : function() {
		this._oSection = new Component_Section(
			{
				sTitle	: 'Email Preview',
				sIcon	: '../admin/img/template/email.png'
			}
		);
		this._oSection.getAttachmentNode().appendChild(
			$T.div(
				$T.table({class: 'reflex input'},
					$T.tbody(
						$T.tr(
							$T.th('From'),
							$T.td({class: 'component-email-queue-email-preview-sender'})
						),
						$T.tr(
							$T.th('To'),
							$T.td({class: 'component-email-queue-email-preview-recipients'})
						),
						$T.tr(
							$T.th('Subject'),
							$T.td({class: 'component-email-queue-email-preview-subject'})
						),
						$T.tr(
							$T.th('Attachments'),
							$T.td(
								$T.ul({class: 'reset horizontal component-email-queue-email-preview-attachments'})
							)	
						)
					)	
				),
				$T.iframe({class: 'component-email-queue-email-preview-body'})
			)
		);
		this._oSection.getAttachmentNode('header-actions').appendChild(
			$T.button({class: 'icon-button', onclick: this._showSampleEmailPopup.bind(this)},
				$T.img({src: '../admin/img/template/email.png', alt: '', title: ''}),
				$T.span('Send a Sample Email')
			)
		);
		
		this.NODE = $T.div(this._oSection.getNode());
		
		this.ATTACHMENTS.sender 		= this.NODE.select('.component-email-queue-email-preview-sender').first();
		this.ATTACHMENTS.recipients		= this.NODE.select('.component-email-queue-email-preview-recipients').first();
		this.ATTACHMENTS.subject 		= this.NODE.select('.component-email-queue-email-preview-subject').first();
		this.ATTACHMENTS.attachments	= this.NODE.select('.component-email-queue-email-preview-attachments').first();
		this.ATTACHMENTS.body			= this.NODE.select('.component-email-queue-email-preview-body').first();
	},
	
	_syncUI : function() {
		if (!this._oData) {
			this._load();
		} else {
			// Update the ui to show the email data
			this.getAttachmentNode('sender').innerHTML 		= this._oData.sender;
			this.getAttachmentNode('recipients').innerHTML 	= this._oData.recipients;
			this.getAttachmentNode('subject').innerHTML 	= this._oData.subject;
			
			// Attachments
			var oAttachmentsNode = this.getAttachmentNode('attachments');
			if (!Object.isUndefined(this._oData.attachments.length) && (this._oData.attachments.length == 0)) {
				// No attachments, hide the row
				oAttachmentsNode.up().up().hide();
			} else {
				// There is attachments, show them
				for (var iId in this._oData.attachments) {
					if (isNaN(iId)) {
						break;
					}
					
					var oFile = this._oData.attachments[iId];
					oAttachmentsNode.appendChild(
						$T.li(
							Component_Email_Queue_Email_Preview._getAttachmentIcon(oFile.mime_type).observe('click', this._downloadAttachment.bind(this, oFile.id)),
							$T.span(oFile.filename)
						)
					);
				}
			}
			
			var sBody = '';
			if (this._oData.html && this._oData.html != '') {
				sBody = this._oData.html;
			} else {
				sBody = this._oData.text;
			}
			
			var oBodyNode = this.getAttachmentNode('body');
			oBodyNode.observe('load', function(oBodyNode, sBody) {
				var oIFrame = (oBodyNode.contentWindow) ? oBodyNode.contentWindow : (oBodyNode.contentDocument.document) ? oBodyNode.contentDocument.document : oBodyNode.contentDocument;
				oIFrame.document.open();
				oIFrame.document.write(sBody);
				oIFrame.document.close();
			}.curry(oBodyNode, sBody));
			
			// All done, ready to be shown
			this._onReady();
		}
	},
	
	_load : function(oResponse) {
		if (!oResponse) {
			// Request
			var oReq = new Reflex_AJAX_Request('Email', 'getDetailsForId', this._load.bind(this));
			oReq.send(this.get('iEmailId'));
		} else if (oResponse.hasException()) {
			// Error
			var oException = oResponse.getException();
			Reflex_Popup.alert(oException.sMessage || 'There was a critical error accessing the Flex Server', {
				sTitle			: 'Database Error',
				sDebugContent	: oResponse.getDebugLog()
			});
		} else {
			// Success
			this._oData = oResponse.get('oEmail');
			this._syncUI();
		}
	},
	
	_downloadAttachment : function(iAttachmentId) {
		window.location = 'reflex.php/Email/DownloadAttachment/' + iAttachmentId;
	},
	
	_showSampleEmailPopup : function(oResponse) {
		var oPopup = new Popup_Email_Queue_Email_Preview_Send(
			this.get('iEmailId'),
			function() {
				oPopup.display();
			}
		);
	}
});

Object.extend(Component_Email_Queue_Email_Preview, {
	createAsPopup : function() {
		var	oComponent	= Component_Email_Queue_Email_Preview.constructApply($A(arguments)),
		oPopup			= new Reflex_Popup(60);
		
		oPopup.setTitle('Email Preview');
		oPopup.addCloseButton();
		oPopup.setContent(oComponent.getNode());
		
		return oPopup;
	},
	
	_getAttachmentIcon : function(sMimeType) {
		var sIcon 	= '../admin/img/template/mime/' + sMimeType + '.png';
		var oIcon	= $T.img({class: 'pointer', src: sIcon, alt: 'Download', title: 'Download'});
		oIcon.observe('error', Component_Email_Queue_Email_Preview._getDefaultAttachmentIcon.curry(oIcon));
		return oIcon;
	},
	
	_getDefaultAttachmentIcon : function(oIcon) {
		oIcon.src = '../admin/img/template/mime/default.png';
	}
});