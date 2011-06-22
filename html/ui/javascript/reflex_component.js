
Reflex_Component	= Class.create({

	initialize	: function () {
		//debugger;
		// General init
		this.NODE			= null;
		
		this.CONFIG			= Object.extend({
			sExtraClass	: {
				fnSetter	: (function (mValue) {
					var	sExistingClass	= this.get('sExtraClass');
					if (sExistingClass) {
						this.getNode().removeClassName(sExistingClass);
					}
					if (mValue) {
						mValue	= String(mValue);
						this.getNode().addClassName(mValue);
					}
					return mValue ? mValue : null;
				}).bind(this)
			},
			fnOnReady	: {
				fnSetter	: function(mValue){if (Object.isFunction(mValue)) return mValue;}
			}
		}, this.CONFIG || {});

		this.ATTACHMENTS	= Object.extend({
			'default'	: this.getNode.bind(this)
		}, this.ATTACHMENTS || {});
		
		// Parameters
		var	oArgumentData	= Reflex_Template.parseArguments($A(arguments));

		// Init the DOM
		this._buildUI();
		this.NODE.addClassName(Reflex_Component.COMPONENT_DEFAULT_CLASS);
		this.NODE.oReflexComponent	= this;

		// Attach Children
		for (var i=0, j=oArgumentData.aChildren.length; i < j; i++) {
			this.appendChild(oArgumentData.aChildren[i]);
		}

		// Set Config
		this.setConfig(oArgumentData.oConfig);

		// Sync the UI
		this._syncUI();
	},

	// Component Lifecycle
	//------------------------------------------------------------------------//
	// _buildUI(): Handles the creation of DOM nodes
	_buildUI	: function () {
		this.NODE	= $T(Reflex_Component.COMPONENT_DEFAULT_ELEMENT, {'class':Reflex_Component.COMPONENT_DEFAULT_CLASS});
	},

	// _syncUI(): Updates the UI with the Config.  Should call _onReady() when the UI is ready for use
	_syncUI	: function () {
		// Default implementation does nothing other than set the component to "ready"
		this._onReady();
	},

	// "Final" Methods (no need to override that I can think of)
	//------------------------------------------------------------------------//
	// getNode(): Returns the root node/element for this component
	getNode	: function () {
		return this.NODE;
	},

	// getAttachmentNode(): Gets the (or one of multiple) DOM nodes which we can attach children to.  Can return null.
	getAttachmentNode	: function (sName) {
		sName	= sName || 'default';
		if (this.ATTACHMENTS.hasOwnProperty(sName)) {
			return (typeof this.ATTACHMENTS[sName] === 'function') ? this.ATTACHMENTS[sName]() : this.ATTACHMENTS[sName];
		}
		return null;
	},

	_onReady	: function () {
		var	fnOnReady	= this.get('fnOnReady');
		if (fnOnReady) {
			setTimeout(fnOnReady, 0);
		}
	},

	// setConfig(): Public API to set the Config of this Component
	setConfig	: function (oConfig) {
		for (var sAttribute in oConfig) {
			if (oConfig.hasOwnProperty(sAttribute)) {
				this.set(sAttribute, oConfig[sAttribute]);
			}
		}
	},

	// set(): Sets an individual Config record
	set	: function (sAttribute, mValue) {
		if (sAttribute in this.CONFIG) {
			mValue	= (typeof this.CONFIG[sAttribute].fnSetter === 'function') ? this.CONFIG[sAttribute].fnSetter(mValue) : mValue;
			if (typeof mValue !== 'undefined') {
				this.CONFIG[sAttribute].mValue	= mValue;
			}
		}
	},

	// set(): Gets an individual Config record
	get	: function (sAttribute) {
		if (sAttribute in this.CONFIG) {
			return (typeof this.CONFIG[sAttribute].fnGetter === 'function') ? this.CONFIG[sAttribute].fnGetter(this.CONFIG[sAttribute].mValue) : this.CONFIG[sAttribute].mValue;
		}
		return null;
	},

	appendChild	: function (mChild) {
		var	oAttachmentNode	= this.getAttachmentNode();
		if (!oAttachmentNode) {
			throw "This Component does not have a default attachment node"
		}
		return oAttachmentNode.appendChild(Reflex_Template.extractNode(mChild));
	},

	insertBefore	: function (mChild, mBeforeChild) {
		var	oAttachmentNode	= this.getAttachmentNode();
		if (!oAttachmentNode) {
			throw "This Component does not have a default attachment node"
		}
		return oAttachmentNode.insertBefore(Reflex_Template.extractNode(mChild), Reflex_Template.extractNode(mBeforeChild));
	},

	replaceChild	: function (mChild, mOldChild) {
		var	oAttachmentNode	= this.getAttachmentNode();
		if (!oAttachmentNode) {
			throw "This Component does not have a default attachment node"
		}
		return oAttachmentNode.replaceChild(Reflex_Template.extractNode(mChild), Reflex_Template.extractNode(mOldChild));
	},

	removeChild	: function (mChild) {
		var	oAttachmentNode	= this.getAttachmentNode();
		if (!oAttachmentNode) {
			throw "This Component does not have a default attachment node"
		}
		return oAttachmentNode.removeChild(Reflex_Template.extractNode(mChild));
	},

	select	: function () {
		var	oNode	= this.getAttachmentNode();
		return oNode.select.apply(oNode, $A(arguments));
	},

	_select	: function () {
		return this.NODE.select.apply(this.NODE, $A(arguments));
	},

	$$	: function () {
		return this.select.apply(this, $A(arguments));
	},

	_$$	: function () {
		return this._select.apply(this, $A(arguments));
	}
});

Reflex_Component.COMPONENT_DEFAULT_ELEMENT	= 'div';
Reflex_Component.COMPONENT_DEFAULT_CLASS	= 'reflex-component';

Reflex_Component._parseChildReflexComponent	= function (mNodeContainer) {
	return mNodeContainer instanceof Reflex_Component ? mNodeContainer.getNode() : null;
};

// Register the Reflex_Template Child Node Parser
if (Reflex_Template && Reflex_Template.aChildNodeParsers && Reflex_Template.aChildNodeParsers.indexOf(Reflex_Template.aChildNodeParsers) === -1) {
	Reflex_Template.aChildNodeParsers.unshift(Reflex_Component._parseChildReflexComponent);
}
