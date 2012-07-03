
var	$D			= require('../dom/factory'),
	Class		= require('fw/class'),
	Component	= require('../component');

var	self	= new Class({
	extends	: Component,

	construct	: function () {
		this.CONFIG = Object.extend({
			bModal			: {
				fnGetter : function(bModal) {
					return (bModal === false ? false : true);
				}
			}
		}, this.CONFIG || {});
		this._super.apply(this, arguments);
		//this.NODE.addClassName('fw-layer');
	},

	// Component Lifecycle
	//------------------------------------------------------------------------//
	_buildUI	: function () {
		this.NODE = this.NODE || $D.section();
		// Override default attachment node
		this.ATTACHMENTS['default'] = this.NODE.select('.fw-popup-content').first();
	},

	_syncUI	: function () {
		this._onReady();
	},
	//------------------------------------------------------------------------//
	
	display : function() {
		if (this.NODE.parentNode) {
			return;
		}
		self.displayPopup(this);
	},
	
	hide : function() {
		self.hidePopup(this);
		this.fire('hide');
	},
});

Object.extend(self, {
	_aPopups 	: [],
	_oContainer	: null,
	_oUnderlay	: null,
	
	_getContainer : function() {
		if (!self._oContainer) {
			var oElement = document.body.select('.fw-layer').first();
			if (!oElement) {
				oElement = $D.div({'class': 'fw-layer'});
			}
			self._oContainer = oElement;
		}
		return self._oContainer;
	},
	
	_getUnderlay : function() {
		if (!self._oUnderlay) {
			var oElement = document.body.select('.fw-layer-underlay').first();
			if (!oElement) {
				oElement = $D.div({'class': 'fw-layer-underlay'});
			}
			self._oUnderlay = oElement;
		}
		return self._oUnderlay;		
	},
	
	displayPopup : function(oPopup) {
		var oContainer = self._getContainer();
		if (self._aPopups.length == 0) {
			document.body.appendChild(oContainer);
		}
		
		if (oPopup.get('bModal')) {
			oContainer.appendChild(self._getUnderlay());
		}
		
		oContainer.appendChild(oPopup.getNode());
		self._aPopups.push(oPopup);
	},
	
	hidePopup : function(oPopup) {
		oPopup.getNode().remove();
		self._aPopups.splice(self._aPopups.indexOf(oPopup), 1);
		
		var oContainer	= self._getContainer();
		var oUnderlay 	= self._getUnderlay();
		
		if (oPopup.get('bModal')) {
			oUnderlay.remove();
		}
		
		if (self._aPopups.length == 0) {
			oContainer.remove();
		} else if (oPopup.get('bModal')) {
			var oTopMostPopup = self._aPopups[self._aPopups.length - 1];
			oContainer.insertBefore(oUnderlay, oTopMostPopup.getNode());
		}
	}
});

return self;