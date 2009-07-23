var Operation_Tree	= Class.create
({
	initialize	: function(aSelected, sRenderHeirarchy)
	{
		// Create Tree Grid
		this._oTreeGrid				= {};
		this._oTreeGrid.oControl	= new Control_Tree_Grid();
		
		this._oTreeGrid.oColumns									= {};
		this._oTreeGrid.oColumns[Control_Tree_Grid.COLUMN_CHECK]	= {};
		this._oTreeGrid.oColumns[Control_Tree_Grid.COLUMN_LABEL]	= {};
		this._oTreeGrid.oControl.setColumns(this._oTreeGrid.oColumns);
		
		this._oTreeGrid.oLoading						= {};
		this._oTreeGrid.oLoading.domElement				= document.createElement('div');
		this._oTreeGrid.oLoading.domElement.innerHTML	= "<img src='../admin/img/template/loading.gif' alt='' title='Loading' /> <span>Retrieving list of Operations...</span>";
		this._oTreeGrid.oLoading.domElement.addClassName('loading');
		
		this._oTreeGrid.oControl.getElement().appendChild(this._oTreeGrid.oLoading.domElement);
		
		// Set Selected Operations
		this._aSelected	= [];
		this.setSelected(aSelected);
		
		this.setRenderHeirarchy(sRenderHeirarchy);
		
		// Load Operations
		this.oOperations	= Operation.getAll(this._load.bind(this));
	},
	
	_load	: function(oResultSet)
	{
		this._oOperationDetails	= {};
		
		for (iOperationId in oResultSet)
		{
			this._oOperationDetails[iOperationId]					= {};
			this._oOperationDetails[iOperationId].bSelected			= false;
			this._oOperationDetails[iOperationId].aNodeInstances	= [];
		}
		this._bLoaded	= true;
		
		// Rebuild the Tree
		this._buildTree();
	},
	
	getTreeGrid	: function()
	{
		return this._oTreeGrid.oControl;
	},
	
	getElement	: function()
	{
		return this._oTreeGrid.oControl.getElement();
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
		// Remove the existing tree nodes
		this._oTreeGrid.oControl.purgeChildren();
		
		// Rebuild the tree
		for (iOperationId in this.oOperations)
		{
			// Render top-level Nodes
			switch (this._sRenderHeirarchy)
			{
				case Operation_Tree.RENDER_HEIRARCHY_INCLUDES:
					// Only Operations with no dependants
					if (!this.oOperations[iOperationId].oProperties.aDependants || !this.oOperations[iOperationId].oProperties.aDependants.length)
					{
						this._convertOperationToTreeNode(iOperationId);
					}
					break;
					
				case Operation_Tree.RENDER_HEIRARCHY_GROUPED:
					// Only Operations with no prerequisites
					if (!this.oOperations[iOperationId].oProperties.aPrerequisites || !this.oOperations[iOperationId].oProperties.aPrerequisites.length)
					{
						this._convertOperationToTreeNode(iOperationId);
					}
					break;
				
				default:
					// All Operations
					this._convertOperationToTreeNode(iOperationId);
					break;
			}
		}
	},
	
	_convertOperationToTreeNode	: function(iOperationId)
	{
		if (!this.oOperations[iOperationId])
		{
			throw "Operation with Id #"+iOperationId+" does not exist!";
		}
		
		var oNodeContent	=	{}
		oNodeContent[Control_Tree_Grid.COLUMN_LABEL]	= this.oOperations[iOperationId].oProperties.name;
		oNodeContent[Control_Tree_Grid.COLUMN_VALUE]	= iOperationId;
		oNodeContent[Control_Tree_Grid.COLUMN_CHECK]	=	{
																mValue		: iOperationId,
																bChecked	: (this._aSelected.indexOf(iOperationId) > -1)
															};
		var oNode			= Control_Tree_Grid_Node_Data(oNodeContent, Operation_Tree.TREE_GRID_DATATYPE_OPERATION.sName);
		
		this._oOperationDetails[iOperationId].aNodeInstances.push(oNode);
		
		switch (this._sRenderHeirarchy)
		{
			case Operation_Tree.RENDER_HEIRARCHY_INCLUDES:
				// Render all prerequisites
				if (this.oOperations[iOperationId].oProperties.aPrerequisites && this.oOperations[iOperationId].oProperties.aPrerequisites.length)
				{
					for (var i = 0; i < this.oOperations[iOperationId].oProperties.aPrerequisites.length; i++)
					{
						oNode.appendChild(this._convertOperationToTreeNode(this.oOperations[iOperationId].oProperties.aPrerequisites[i]));
					}
				}
				break;
				
			case Operation_Tree.RENDER_HEIRARCHY_GROUPED:
				// Render all dependants
				if (this.oOperations[iOperationId].oProperties.aDependants && this.oOperations[iOperationId].oProperties.aDependants.length)
				{
					for (var i = 0; i < this.oOperations[iOperationId].oProperties.aDependants.length; i++)
					{
						oNode.appendChild(this._convertOperationToTreeNode(this.oOperations[iOperationId].oProperties.aDependants[i]));
					}
				}
				break;
		}
	},
	
	getRenderHeirarchy	: function()
	{
		return this._sRenderHeirarchy;
	},
	
	setEditable	: function(bEditable)
	{
		this._oTreeGrid.oControl.setEditable(bEditable);
	},
	
	isEditable	: function()
	{
		return this._oTreeGrid.oControl.isEditable();
	},
	
	setOperationSelected	: function(iOperationId, bSelected)
	{
		if (!this.oOperations[iOperationId] || !this._oOperationDetails[iOperationId])
		{
			throw "Operation with Id #"+iOperationId+" does not exist!";
		}
		
		this._oOperationDetails[iOperationId].bSelected	= bSelected;
		
		// Update all Node instances
		for (var i = 0; i < this._oOperationDetails[iOperationId].length; i++)
		{
			this._oOperationDetails[iOperationId][i].setSelected(bSelected, true);
		}
		
		// Update all Prerequisites
		if (this._oOperationDetails[iOperationId].bSelected === true)
		{
			for (var i = 0; i < this.oOperations[iOperationId].aPrerequisites.length; i++)
			{
				this.setOperationSelected(this.oOperations[iOperationId].aPrerequisites[i], true);
			}
		}
		
		// Update all Dependants
		if (this._oOperationDetails[iOperationId].bSelected === false)
		{
			for (var i = 0; i < this.oOperations[iOperationId].aDependants.length; i++)
			{
				this.setOperationSelected(this.oOperations[iOperationId].aDependants[i], false);
			}
		}
	},
	
	onSelectHandler	: function(oNode)
	{
		this.setOperationSelected(oNode.getValue(), oNode.isSelected());
	},
	
	setSelected	: function(aSelected)
	{
		if (aSelected)
		{
			for (var i = 0; i < aSelected.length; i++)
			{
				this.onSelectHandler(aSelected[i], true);
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
		this._oTreeGrid.oControl.render();
	}
});

Operation_Tree.RENDER_HEIRARCHY_NONE		= 'none';
Operation_Tree.RENDER_HEIRARCHY_INCLUDES	= 'includes';
Operation_Tree.RENDER_HEIRARCHY_GROUPED		= 'grouped';


Operation_Tree.TREE_GRID_DATATYPE_OPERATION					= {};
Operation_Tree.TREE_GRID_DATATYPE_OPERATION.sName			= 'operation';
Operation_Tree.TREE_GRID_DATATYPE_OPERATION.sDescription	= 'Operation';
Operation_Tree.TREE_GRID_DATATYPE_OPERATION.sIconSource		= '../admin/img/template/operation.png';