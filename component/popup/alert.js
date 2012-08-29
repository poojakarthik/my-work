
var	$D			= require('../../dom/factory'),
	Class		= require('../../class'),
	Component	= require('../../component'),
	Popup		= require('../popup');

var	self = new Class({
	extends : Popup,
	
	construct : function() {
		this.CONFIG = Object.extend({
			sOKLabel : {}
		}, this.CONFIG || {});
	
		this._super.apply(this, arguments);
		
		this.NODE.addClassName('fw-popup-alert');
		this.display();
	},
	
	_buildUI : function() {
		this._super();
		this.NODE.appendChild(
			$D.div({'class': 'fw-popup-alert-buttons'},
				$D.button($D.span('OK')).observe('click', this._ok.bind(this))
			)
		);
	},
	
	_syncUI : function() {
		var	sTitle			= this.get('sTitle'),
			sIconURI		= this.get('sIconURI'),
			bCloseButton	= this.get('bCloseButton'),
			sOKLabel		= this.get('sOKLabel');

		// Title with the option to be configured
		this._oTitle.update(sTitle ? sTitle.escapeHTML() : 'Alert');
		// Icon with the option to be configured
		if (sIconURI) {
			this._oIcon.setAttribute('src', sIconURI);
		} else {
			//this._oIcon.hide();
			this._oIcon.setAttribute('src', 'data:image/png;base64,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');
		}
		if (bCloseButton) {
			this.NODE.addClassName('-closable');
		} else {
			this.NODE.removeClassName('-closable');
		}
		
		if (sOKLabel) {
			this.NODE.select('.fw-popup-alert-buttons button > span').first().update(sOKLabel);
		}
		
		this._onReady();
	},
	
	// Override
	display : function() {
		this._super();
		this.NODE.select('.fw-popup-alert-buttons > button').first().focus();
	},

	_ok : function() {
		this.fire('ok');
		this.hide();
	}
});

return self;