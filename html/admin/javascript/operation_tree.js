
var Operation_Tree	= Class.create
({
	initialize	: function(sRenderHeirarchy, aSelected, fnDataSource, fnOnLoad, fnOnCheck)
	{
		this._bEditable	= false;
		this.fnOnLoad	= fnOnLoad;
		this.fnOnCheck	= fnOnCheck;
		
		// Create Reflex.Control.Tree
		this.oControl	= new Reflex.Control.Tree();
		
		// Set the columns, single operation tree has description column added
		switch (sRenderHeirarchy)
		{
			case Operation_Tree.RENDER_OPERATION_PROFILE:
				this.oControl.setColumns(
					{
						'label'			: {sTitle: 'Operation'}
					}
				);
				break;
			
			case Operation_Tree.RENDER_OPERATION:
				this.oControl.setColumns(
					{
						'label'			: {sTitle: 'Operation'},
						'description'	: {sTitle: 'Description'}
					}
				);
				break;
		}
		
		this.oControl.oHeader.hide();
		
		// Create loading element
		this.oLoading	= 	$T.div({class: 'loading'},
								$T.div({class: 'operation-tree-loading'},
									$T.img({src: '../admin/img/template/loading.gif', alt: '', title: 'Loading'}),
									$T.span('Retrieving list of Operations...')
								)
							);
		
		this.setSelected(aSelected, true);
		this.setRenderHeirarchy(sRenderHeirarchy);
		
		// Load Data
		if (fnDataSource)
		{
			this.oControl.getElement().appendChild(this.oLoading);
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
		
		// Rebuild the Tree
		this._buildTree();
		
		// Load callback
		if (this.fnOnLoad)
		{
			this.fnOnLoad();
		}
		
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
			case Operation_Tree.RENDER_OPERATION_PROFILE:
			case Operation_Tree.RENDER_OPERATION:
				this._sRenderHeirarchy	= sRenderHeirarchy;
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
	
	isLoaded	: function()
	{
		return this._bLoaded;
	},
	
	_buildTree	: function()
	{
		// Rebuild the tree
		for (iOperationId in this.oOperations)
		{
			// Render top-level Nodes
			switch (this._sRenderHeirarchy)
			{
				case Operation_Tree.RENDER_OPERATION_PROFILE:
					// Only Operations with no dependants
					this.oControl.getRootNode().addChild(this._convertOperationToTreeNode(iOperationId));
					break;
					
				case Operation_Tree.RENDER_OPERATION:
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
	
	_convertOperationToTreeNode	: function(iOperationId, oParentNodeOverride)
	{
		var oNode		= 	new Reflex.Control.Tree.Node.Checkable(
								null,
								iOperationId,
								this._bEditable,
								this.onSelectHandler.bind(this)
							);
		var oNodeData	= {label: this.oOperations[iOperationId].name};
		
		this._oOperationDetails[iOperationId].aNodeInstances.push(oNode);
		
		switch (this._sRenderHeirarchy)
		{
			case Operation_Tree.RENDER_OPERATION_PROFILE:
				// Render all prerequisites, inline, not heirarchical
				var oParentNode	= oNode;
				
				if (typeof oParentNodeOverride !== 'undefined')
				{
					// Disable the selection of this 2nd level node, preselect however, so it shows when not editable
					oNode.setCheckedState(true, true);
					oNode.setEnabled(false);
					oParentNode	= oParentNodeOverride;
				}
				
				if (this.oOperations[iOperationId].aPrerequisites && this.oOperations[iOperationId].aPrerequisites.length)
				{
					for (var i = 0; i < this.oOperations[iOperationId].aPrerequisites.length; i++)
					{
						var iId	= this.oOperations[iOperationId].aPrerequisites[i];
						
						if (this.oOperations[iId])
						{
							oParentNode.addChild(this._convertOperationToTreeNode(this.oOperations[iOperationId].aPrerequisites[i], oParentNode));
						}
					}
				}
				
				oNode.setIcon(Operation_Tree.TREE_NODE_PROFILE_IMAGE);
				break;
				
			case Operation_Tree.RENDER_OPERATION:
				// Render all dependants
				if (this.oOperations[iOperationId].aDependants && this.oOperations[iOperationId].aDependants.length)
				{
					for (var i = 0; i < this.oOperations[iOperationId].aDependants.length; i++)
					{
						var iId	= this.oOperations[iOperationId].aDependants[i];
						
						if (this.oOperations[iId])
						{
							oNode.addChild(this._convertOperationToTreeNode(this.oOperations[iOperationId].aDependants[i]));
						}
					}
				}
				
				// Add description to the node data
				oNodeData.description	= 	$T.span(
												$T.span({class: 'operation-tree-profile-description'},
													this.oOperations[iOperationId].description
												)
											);
				
				// Hide it to start with
				oNodeData.description.select('span.operation-tree-profile-description').first().hide();
				
				// Add mouseover event so that it can be shown when the node is hovered
				oNode.oElement.observe('mouseover', this._operationHover.bind(this, oNode, true));
				oNode.oElement.observe('mouseout', this._operationHover.bind(this, oNode, false));
				oNode.setIcon(Operation_Tree.TREE_NODE_OPERATION_IMAGE);
				break;
		}
		
		oNode.setData(oNodeData);
		
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
	
	setOperationSelected	: function(iOperationId, bSelected, bDisableNodes, bEnableNodes)
	{
		if (!this.oOperations[iOperationId] || !this._oOperationDetails[iOperationId])
		{
			throw "Operation with Id #" +  iOperationId + " does not exist!";
		}
		
		this._oOperationDetails[iOperationId].bSelected	= bSelected;
		
		// Update all Node instances
		var oNode	= null;
		
		for (var i = 0; i < this._oOperationDetails[iOperationId].aNodeInstances.length; i++)
		{
			oNode	= this._oOperationDetails[iOperationId].aNodeInstances[i]; 
			
			// If enabling, do so before setCheckedState otherwise it won't do it
			if(bEnableNodes)
			{
				oNode.setEnabled(true);
			}
			
			oNode.setCheckedState(bSelected, true);
			
			// If disabling, do so after setCheckedState otherwise it won't do it
			if (bDisableNodes)
			{
				oNode.setEnabled(false);
			}
		}
		
		// Update all Prerequisites
		if (this._oOperationDetails[iOperationId].bSelected === true)
		{
			for (var i = 0; i < this.oOperations[iOperationId].aPrerequisites.length; i++)
			{
				this.setOperationSelected(this.oOperations[iOperationId].aPrerequisites[i], true, bDisableNodes, bEnableNodes);
			}
		}
		
		// Update all Dependants
		if (this._oOperationDetails[iOperationId].bSelected === false)
		{
			for (var i = 0; i < this.oOperations[iOperationId].aDependants.length; i++)
			{
				this.setOperationSelected(this.oOperations[iOperationId].aDependants[i], false, bDisableNodes, bEnableNodes);
			}
		}
	},
	
	onSelectHandler	: function(oNode)
	{
		this.setOperationSelected(oNode.getValue(), oNode.isChecked(), false);
		
		if (this.fnOnCheck)
		{
			this.fnOnCheck();
		}
	},
	
	setSelected	: function(aSelected, bSelectOnly, bDisableSelected)
	{
		if (!this._bLoaded)
		{
			return;
		}
		
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
	
	getSelected	: function(bEnabledNodesOnly)
	{
		var aSelected	= [];
		for (iOperationId in this._oOperationDetails)
		{
			if (this._oOperationDetails[iOperationId].bSelected)
			{
				// Check if the first node is enabled, they either all will or all won't be
				var bEnabled = this._oOperationDetails[iOperationId].aNodeInstances[0].isEnabled();
				
				if ((bEnabledNodesOnly && bEnabled) || !bEnabledNodesOnly)
				{
					aSelected.push(iOperationId);
				}
			}
		}
		
		return aSelected;
	},
	
	render	: function()
	{
		this.oControl.paint();
	},
	
	deSelectAll	: function(bEnableNodes)
	{
		for (iOperationId in this.oOperations)
		{
			this.setOperationSelected(iOperationId, false, false, bEnableNodes);
		}
	},
	
	selectAll	: function(bEnableNodes)
	{
		for (iOperationId in this.oOperations)
		{
			this.setOperationSelected(iOperationId, true);
		}
	},
	
	_operationHover	: function(oNode, bShow, event)
	{
		var oSpan	= oNode.oElement.select('span.operation-tree-profile-description').first(); 
		
		if (bShow)
		{
			oSpan.show();
		}
		else
		{
			oSpan.hide();
		}
		
		event.stop();
	}
});

Operation_Tree.RENDER_OPERATION_PROFILE		= 'operation_profile';
Operation_Tree.RENDER_OPERATION				= 'operation';

Operation_Tree.TREE_NODE_PROFILE_IMAGE		= '../admin/img/template/contacts.png';
Operation_Tree.TREE_NODE_OPERATION_IMAGE	= '../admin/img/template/operation.png';

