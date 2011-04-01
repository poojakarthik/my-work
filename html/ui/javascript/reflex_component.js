
Reflex_Component	= Class.create({

	initialize	: function () {
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
			}
		}, this.CONFIG || {});

		this.ATTACHMENTS	= Object.extend({
			'default'	: this.getNode().bind(this)
		}, this.ATTACHMENTS || {});
		
		// Parameters
		var	oArgumentData	= Reflex_Template.parseArguments($A(arguments).slice(1));

		// Init the DOM
		this._buildUI();
		this.NODE.addClassName(Reflex_Component.COMPONENT_DEFAULT_CLASS);

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

	// _syncUI(): Updates the UI with the Config
	_syncUI	: function () {
		// Default implementation does nothing
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

	// setConfig(): Public API to set the Config of this Component
	setConfig	: function (config) {
		for (var sAttribute in config) {
			if (config.hasOwnProperty(sAttribute)) {
				this.set(config[sAttribute]);
			}
		}
	},

	// set(): Sets an individual Config record
	set	: function (sAttribute, mValue) {
		if (sAttribute in this.CONFIG) {
			this.CONFIG[sAttribute].mValue	= (typeof this.CONFIG[sAttribute].fnSetter === 'function') ? this.CONFIG[sAttribute].fnSetter(mValue) : mValue;
		}
	},

	// set(): Gets an individual Config record
	get	: function (sAttribute) {
		if (sAttribute in this.CONFIG) {
			return (typeof this.CONFIG[sAttribute].fnGetter === 'function') ? this.CONFIG[sAttribute].fnGetter(mValue) : mValue;
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
	}
});

Reflex_Component.COMPONENT_DEFAULT_ELEMENT	= 'div';
Reflex_Component.COMPONENT_DEFAULT_CLASS	= 'reflex-component';

Reflex_Component._parseChildReflexComponent	= function (mNodeContainer) {
	return mNodeContainer instanceof Reflex_Component ? mNodeContainer.getNode() : null;
};

// Register the Reflex_Template Child Node Parser
if (Reflex_Template && Reflex_Template.aChildNodeParsers && !Reflex_Template.aChildNodeParsers.indexOf()) {
	Reflex_Template.aChildNodeParsers.unshift(Reflex_Component._parseChildReflexComponent);
}
