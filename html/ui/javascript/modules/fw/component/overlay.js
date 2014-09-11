
var	$D			= require('../dom/factory'),
	Class		= require('fw/class'),
	Layer		= require('./layer');

var	self	= new Class({
	extends	: Layer,

	construct	: function () {
		this.CONFIG = Object.extend({
			//bShowCloseButton : {},
			bModal			: {
				fnGetter : function(bModal) {
					return (bModal === false ? false : true);
				}
			}
		}, this.CONFIG || {});
		this._super.apply(this, arguments);
		this.NODE.addClassName('fw-layer-overlay');
	},

	// Component Lifecycle
	//------------------------------------------------------------------------//
	_buildUI	: function () {
		this.NODE = this.NODE || $D.div();
	},
	
	_syncUI	: function () {
		// Show Close Icon
		/*
		if (this.get('bShowCloseButton')) {
			var oCloseButton = $D.button({'class':'fw-overlay-close'}).observe('click', this.hide.bind(this));
			this.NODE.insertBefore(oCloseButton, this.NODE.firstChild);
		}
		*/
		// Ready
		this._onReady();
	},
	//------------------------------------------------------------------------//
	
	
	// Positioning
	//------------------------------------------------------------------------//
	display : function() {
		this._super();
		this.centre();
	},
	centre : function() {
		self.centreOverlay(this);
	},
	//------------------------------------------------------------------------//
	
});

Object.extend(self, {
	_oContainer	: null,
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
	centreOverlay : function(oOverlay) {
		var oNode 			= oOverlay.getNode();
		var oContainer		= self._getContainer();
		oNode.style.left 	= ((oContainer.clientWidth - oNode.clientWidth) / 2) + 'px';
	}
});

return self;