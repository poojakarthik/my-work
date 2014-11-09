var	$D			= require('fw/dom/factory'),
	Class		= require('fw/class'),
	Component	= require('fw/component'),
	Popup		= require('fw/component/popup'),
	Alert		= require('fw/component/popup/alert');

var	self = new Class({
	extends : Popup,
	
	construct : function() {
		this.CONFIG = Object.extend({}, this.CONFIG || {});
		this._super.apply(this, arguments);		
		this.NODE.addClassName('admin-popup-confirm');
		this.display();
	},
	
	_buildUI : function() {
		this._super();
		this._oTitle.update('Confirmation');
		
		this.appendChild(
			$D.div(
				this._oContentDiv = $D.div(),
				$D.div({'class': 'admin-popup-confirm-buttons'},
					$D.button({type: 'button', name: 'yes', onclick: this._confirmed.bind(this, 'yes')}, 'Yes'),
					$D.button({type: 'button', name: 'no', onclick: this._confirmed.bind(this, 'no')}, 'No')
				)
			)
		);
		
		this.ATTACHMENTS['default'] = this._oContentDiv;
	},
	
	_syncUI : function() {
		var	sIconURI = this.get('sIconURI');
		if (sIconURI) {
			this._oIcon.setAttribute('src', sIconURI);
		}
		this._onReady();
	},
	
	_confirmed : function(sEvent) {
		this.fire(sEvent);
		this.hide();
	}
});

return self;
