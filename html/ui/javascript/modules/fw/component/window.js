
var	$D			= require('../dom/factory'),
	Class		= require('fw/class'),
	Layer		= require('./layer');

var	self	= new Class({
	extends	: Layer,

	construct	: function () {
		this.CONFIG = Object.extend({
			sTitle 			: {},
			sIconURI		: {},
			sCloseIconURI	: {},
			bCloseButton	: {},
			bModal			: {
				fnGetter : function(bModal) {
					return (bModal === false ? false : true);
				}
			}
		}, this.CONFIG || {});
		this._super.apply(this, arguments);
		this._oDragStartPosition = null;
		this.NODE.addClassName('fw-layer-window');
	},

	// Component Lifecycle
	//------------------------------------------------------------------------//
	_buildUI	: function () {
		this.NODE = this.NODE || $D.section();
		this.NODE.update();
		this.NODE.appendChild($D.$fragment(
			$D.header({'class' : 'fw-popup-header'},
				$D.img({'class' : 'fw-popup-header-icon'}),
				$D.h2({'class' : 'fw-popup-header-title'}),
				//$D.img({'class' : 'fw-popup-header-close', src: self.CLOSE_ICON, title:'Close'}).observe('click', this.hide.bind(this)),
				$D.button({'class' : 'fw-popup-header-close'}).observe('click', this.hide.bind(this))
			),
			$D.div({'class' : 'fw-popup-content'})
		));
		// Cache important Elements
		this._oIcon			= this.select('.fw-popup-header-icon')[0];
		this._oTitle		= this.select('.fw-popup-header-title')[0];
		this._oCloseIcon	= this.select('.fw-popup-header-close')[0];
		// Override default attachment node
		this.ATTACHMENTS['default'] = this.NODE.select('.fw-popup-content').first();
		// Event handlers for moving (dragging) the popup by it's header
		var oHeader = this.NODE.select('.fw-popup-header').first();
		oHeader.observe('mousedown', this._startHeaderDrag.bind(this));
		document.body.observe('mousemove', this._headerDrag.bind(this));
		Event.observe(window, 'mouseup', this._stopHeaderDrag.bind(this));
	},

	_syncUI	: function () {
		var	sTitle			= this.get('sTitle'),
			sIconURI		= this.get('sIconURI'),
			sCloseIconURI	= this.get('sCloseIconURI'),
			bCloseButton	= this.get('bCloseButton');
		
		// UPDATE: Style the close icon using css instead. 
		/*
		if (sCloseIconURI) {
			this._oCloseIcon.setAttribute('src', sCloseIconURI);
		}
		*/
		this._oTitle.update(sTitle ? sTitle.escapeHTML() : '');
		if (sIconURI) {
			this._oIcon.setAttribute('src', sIconURI);
		} else {
			this._oIcon.hide();
		}
		if (bCloseButton) {
			this.NODE.addClassName('-closable');
		} else {
			this.NODE.removeClassName('-closable');
		}
		this._onReady();
	},
	//------------------------------------------------------------------------//
	display : function() {
		this._super();
		this.centre();
	},
	centre : function() {
		self.centrePopup(this);
	},
	
	_startHeaderDrag : function(oEvent) {
		this._oDragStartPosition = {
			iX		: parseInt(this.NODE.style.left, 10),
			iY		: parseInt(this.NODE.style.top, 10),
			iMouseX : oEvent.clientX,
			iMouseY : oEvent.clientY
		};
	},
	
	_headerDrag : function(oEvent) {
		if (this._oDragStartPosition) {
			var iLeft	= this._oDragStartPosition.iX + (oEvent.clientX - this._oDragStartPosition.iMouseX);
			var iTop	= this._oDragStartPosition.iY + (oEvent.clientY - this._oDragStartPosition.iMouseY);
			
			if (iLeft < 0) {
				iLeft = 0;
			}
			
			if (iTop < 0) {
				iTop = 0;
			}
			
			var oPopupContainer = self._getContainer();
			var iMaxLeft		= oPopupContainer.clientWidth - this.NODE.clientWidth;
			if (iLeft > iMaxLeft) {
				iLeft = iMaxLeft;
			}
			
			var iMaxTop = oPopupContainer.clientHeight - this.NODE.clientHeight;
			if (iTop > iMaxTop) {
				iTop = iMaxTop;
			}
			
			this.NODE.style.left 	= iLeft + 'px';
			this.NODE.style.top 	= iTop + 'px';
		}
	},
	
	_stopHeaderDrag : function() {
		if (this._oDragStartPosition) {
			this._oDragStartPosition = null;
		}
	}
});

Object.extend(self, {
	CLOSE_ICON : 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAOCAYAAAAfSC3RAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH1gwVFy4AuOqj5wAAAfdJREFUKM+Nks9LVHEUxT/3+76jDqODMSkKYYRBQhA4LapVQkEErnRZIbRpGe1a5M6Ff4AQROA/4MaVtFKsoBaBi1kYahmVmDLqjG988358v7fFq6hWns05XM65i3Ov7M0+XOk43LxpXIiIATEAoArqUO/AZeBTyFKcKR6mZy8vyMHjqpbG7kKhC4ICmF9B5yBrQxajSQviFhq3VKOmb9c+ZjaI9tDGd2x5ECmW8+2NHTRLwCWIeujugy6Dpm1J2g2C5KDDgkezBOm/iLl2D3yGrjyHnRq4FBkYQW4/ARPgl+dgb9OjisF7SCPYeguf34PthEtj0FHK9cgtsJ3o2iL6+gV+d91qEhqLOjSN0GaCf/MSKfYi567AjQfgMuT8VfTLB/ziNLq/iYLgweI9RMfQqqOf3kFygjxaQC5c5zf01Szsb/E3rLaPcd/W0PgEAfAZp4HBJRC3AIW+YeTO0/wEG6voxipkcT7rG/4viAACpQpmfBoZqqK1Jfz8FH5+Cq0tIUNVzPg0lCr89hsCC7aAVCeR0QlIQnR5DurbUN/OdRIioxNIdRJsAQKLNO6XNKhYpNgL3RVwKeyu5wz5Nw2M5BzW0egIV8+wsT1DqdyNxsfQ/JGbe/r/baJ18EdKeZC4EWKPsp4Z/zV8VjTxqdqMvKGZ9cz8BHh68g4v9eXTAAAAAElFTkSuQmCC',	
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
	centrePopup : function(oPopup) {
		var oNode 			= oPopup.getNode();
		var oContainer		= self._getContainer();
		oNode.style.left 	= ((oContainer.clientWidth - oNode.clientWidth) / 2) + 'px';
		oNode.style.top	 	= ((oContainer.clientHeight - oNode.clientHeight) / 2) + 'px';
	}
});

return self;