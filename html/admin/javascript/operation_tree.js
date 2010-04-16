
var Operation_Tree	= Class.create
({
	initialize	: function(sRenderHeirarchy, aSelected, fnDataSource, fnOnLoad)
	{
		this._bEditable	= false;
		this.fnOnLoad	= fnOnLoad;
		
		// Create Reflex.Control.Tree
		this.oControl	= new Reflex.Control.Tree();
		this.oControl.setColumns(
			{
				'label'	: {sTitle: 'Operation'}
			}
		);
		
		// Create loading element
		this.oLoading	= 	$T.div({class: 'loading'},
								$T.div(
									$T.span(
										$T.img({src: '../admin/img/template/loading.gif', alt: '', title: 'Loading'}),
										'Retrieving list of Operations...'
									)
								)
							);
		this.oControl.getElement().appendChild(this.oLoading);
		
		this.setSelected(aSelected, true);
		this.setRenderHeirarchy(sRenderHeirarchy);
		
		// Load Data
		if (fnDataSource)
		{
			fnDataSource(this._load.bind(this));
		}
	},
	
	_load	: function(oResultSet)
	{
		this.oOperations		= oResultSet;
		this._oOperationDetails	= {};
		
		for (iOperationId in this.oOperations)
		{
			this._oOperationDetails[iOperationId]					= {};
			this._oOperationDetails[iOperationId].bSelected			= false;
			this._oOperationDetails[iOperationId].aNodeInstances	= [];
		}
		
		this._bLoaded	= true;
		
		// Load callback
		if (this.fnOnLoad)
		{
			this.fnOnLoad();
		}
		
		// Rebuild the Tree
		this._buildTree();
		
		// Hide Loading screen
		this.oControl.getElement().style.overflowY	= 'scroll';
		this.oLoading.style.display					= 'none';
	},
	
	getTreeGrid	: function()
	{
		return this.oControl;
	},
	
	getElement	: function()
	{
		return this.oControl.getElement();
	},
	
	setRenderHeirarchy	: function(sRenderHeirarchy)
	{
		switch (sRenderHeirarchy)
		{
			// Valid
			case Operation_Tree.RENDER_HEIRARCHY_INCLUDES:
			case Operation_Tree.RENDER_HEIRARCHY_GROUPED:
			case Operation_Tree.RENDER_HEIRARCHY_NONE:
				this._sRenderHeirarchy	= sRenderHeirarchy;
				break;
			
			// Invalid
			case undefined:
			case null:
				this._sRenderHeirarchy	= Operation_Tree.RENDER_HEIRARCHY_NONE;
				break;
				
			default:
				throw "'"+sRenderHeirarchy+"' is not a valid Render Heirarchy";
				break;
		}
		
		if (this._bLoaded)
		{
			// Rebuild the Tree
			this._buildTree();
		}
	},
	
	_buildTree	: function()
	{
		// Rebuild the tree
		for (iOperationId in this.oOperations)
		{
			// Render top-level Nodes
			switch (this._sRenderHeirarchy)
			{
				case Operation_Tree.RENDER_HEIRARCHY_INCLUDES:
					// Only Operations with no dependants
					if (!this.oOperations[iOperationId].aDependants || !this.oOperations[iOperationId].aDependants.length)
					{
						this.oControl.getRootNode().addChild(this._convertOperationToTreeNode(iOperationId));
					}
					break;
					
				case Operation_Tree.RENDER_HEIRARCHY_GROUPED:
					// Only Operations with no prerequisites
					if (!this.oOperations[iOperationId].aPrerequisites || !this.oOperations[iOperationId].aPrerequisites.length)
					{
						this.oControl.getRootNode().addChild(this._convertOperationToTreeNode(iOperationId));
					}
					break;
				
				default:
					// All Operations
					this.oControl.getRootNode().addChild(this._convertOperationToTreeNode(iOperationId));
					break;
			}
		}
		
		// Render the Tree
		this.oControl.paint();
	},
	
	_convertOperationToTreeNode	: function(iOperationId)
	{
		if (!this.oOperations[iOperationId])
		{
			throw "Operation with Id #" + iOperationId + " does not exist!";
		}
		
		var oNode	= 	new Reflex.Control.Tree.Node.Checkable(
							{label: this.oOperations[iOperationId].name},
							iOperationId,
							this._bEditable,
							this.onSelectHandler.bind(this)
						);
		this._oOperationDetails[iOperationId].aNodeInstances.push(oNode);
		
		switch (this._sRenderHeirarchy)
		{
			case Operation_Tree.RENDER_HEIRARCHY_INCLUDES:
				// Render all prerequisites
				if (this.oOperations[iOperationId].aPrerequisites && this.oOperations[iOperationId].aPrerequisites.length)
				{
					for (var i = 0; i < this.oOperations[iOperationId].aPrerequisites.length; i++)
					{
						oNode.addChild(this._convertOperationToTreeNode(this.oOperations[iOperationId].aPrerequisites[i]));
					}
				}
				
				break;
				
			case Operation_Tree.RENDER_HEIRARCHY_GROUPED:
				// Render all dependants
				if (this.oOperations[iOperationId].aDependants && this.oOperations[iOperationId].aDependants.length)
				{
					for (var i = 0; i < this.oOperations[iOperationId].aDependants.length; i++)
					{
						oNode.addChild(this._convertOperationToTreeNode(this.oOperations[iOperationId].aDependants[i]));
					}
				}
				
				break;
		}
		
		// Set the icon
		oNode.setIcon(Operation_Tree.TREE_NODE_ICON_IMAGE);
		
		return oNode;
	},
	
	getRenderHeirarchy	: function()
	{
		return this._sRenderHeirarchy;
	},
	
	setEditable	: function(bEditable)
	{
		// Set editable on all root node children (they will pass to their children)
		var aChildren	= this.oControl.getRootNode().aChildren;
		
		for (var i = 0; i < aChildren.length; i++)
		{
			aChildren[i].setEditable(bEditable);
		}
		
		this._bEditable	= bEditable;
	},
	
	isEditable	: function()
	{
		return this._bEditable;
	},
	
	setOperationSelected	: function(iOperationId, bSelected, bDisableSelected)
	{
		if (!this.oOperations[iOperationId] || !this._oOperationDetails[iOperationId])
		{
			throw "Operation with Id #"+iOperationId+" does not exist!";
		}
		
		this._oOperationDetails[iOperationId].bSelected	= bSelected;
		
		// Update all Node instances
		var oNode	= null;
		
		for (var i = 0; i < this._oOperationDetails[iOperationId].aNodeInstances.length; i++)
		{
			oNode	= this._oOperationDetails[iOperationId].aNodeInstances[i]; 
			oNode.setCheckedState(bSelected, true);
			
			if (bDisableSelected)
			{
				oNode.setEnabled(false);
			}
		}
		
		// Update all Prerequisites
		if (this._oOperationDetails[iOperationId].bSelected === true)
		{
			for (var i = 0; i < this.oOperations[iOperationId].aPrerequisites.length; i++)
			{
				this.setOperationSelected(this.oOperations[iOperationId].aPrerequisites[i], true, bDisableSelected);
			}
		}
		
		// Update all Dependants
		if (this._oOperationDetails[iOperationId].bSelected === false)
		{
			for (var i = 0; i < this.oOperations[iOperationId].aDependants.length; i++)
			{
				this.setOperationSelected(this.oOperations[iOperationId].aDependants[i], false, bDisableSelected);
			}
		}
	},
	
	onSelectHandler	: function(oNode)
	{
		this.setOperationSelected(oNode.getValue(), oNode.isChecked(), false);
	},
	
	setSelected	: function(aSelected, bSelectOnly, bDisableSelected)
	{
		if (aSelected)
		{
			if (bSelectOnly)
			{
				// Only select the given operations
				for (var i = 0; i < aSelected.length; i++)
				{
					this.setOperationSelected(aSelected[i], true, bDisableSelected);
				}
			}
			else
			{
				// Go through all operations and if it's not in the selected array deselect it, otherwise select.
				for (iOperationId in this.oOperations)
				{
					this.setOperationSelected(iOperationId, aSelected.indexOf(parseInt(iOperationId)) > -1, bDisableSelected);
				}
			}
		}
	},
	
	getSelected	: function()
	{
		var aSelected	= [];
		for (iOperationId in this._oOperationDetails)
		{
			if (this._oOperationDetails[iOperationId].bSelected)
			{
				aSelected.push(iOperationId);
			}
		}
		
		return aSelected;
	},
	
	render	: function()
	{
		this.oControl.paint();
	}
});

Operation_Tree.RENDER_HEIRARCHY_NONE		= 'none';
Operation_Tree.RENDER_HEIRARCHY_INCLUDES	= 'includes';
Operation_Tree.RENDER_HEIRARCHY_GROUPED		= 'grouped';

Operation_Tree.TREE_NODE_ICON_IMAGE			= '../admin/img/template/operation.png';

