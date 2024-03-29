var Permission_Tree_Operation	= Class.create(/* extends */ Permission_Tree,
({
	initialize	: function($super, sRenderHeirarchy, aSelected)
	{
		// Parent Constructor
		$super(sRenderHeirarchy);
		
		// Create Tree Grid
		this._oTreeGrid				= {};
		this._oTreeGrid.oControl	= new Control_Tree_Grid();
		
		this._oTreeGrid.oColumns									= {};
		this._oTreeGrid.oColumns[Control_Tree_Grid.COLUMN_CHECK]	= {};
		this._oTreeGrid.oColumns[Control_Tree_Grid.COLUMN_LABEL]	= {};
		this._oTreeGrid.oControl.setColumns(this._oTreeGrid.oColumns);
		
		this._oTreeGrid.oControl.addDataType(Permission_Tree.TREE_GRID_DATATYPE_OPERATION.sName, Permission_Tree.TREE_GRID_DATATYPE_OPERATION.sDescription, Permission_Tree.TREE_GRID_DATATYPE_OPERATION.sIconSource, this.onSelectHandler.bind(this));
		
		this._oTreeGrid.oControl.getElement().style.overflowY	= 'hidden';
		
		this._oTreeGrid.oLoading						= {};
		this._oTreeGrid.oLoading.domElement				= document.createElement('div');
		this._oTreeGrid.oLoading.domElement.innerHTML	= "<div><span><img src='../admin/img/template/loading.gif' alt='' title='Loading' /> Retrieving list of Operations...</span></div>";
		this._oTreeGrid.oLoading.domElement.addClassName('loading');
		
		this._oTreeGrid.oControl.getElement().appendChild(this._oTreeGrid.oLoading.domElement);
		
		// Set Selected Operations
		this._aSelected	= [];
		this.setSelected(aSelected);
		
		this.setRenderHeirarchy(sRenderHeirarchy);
		
		// Load Operations
		Operation.getAllIndexed(this._load.bind(this));
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
		
		// Hide Loading screen
		this._oTreeGrid.oControl.getElement().style.overflowY	= 'scroll';
		this._oTreeGrid.oLoading.domElement.style.display		= 'none';
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
				case Permission_Tree.RENDER_HEIRARCHY_INCLUDES:
					// Only Operations with no dependants
					if (!this.oOperations[iOperationId].aDependants || !this.oOperations[iOperationId].aDependants.length)
					{
						this._oTreeGrid.oControl.appendChild(this._convertOperationToTreeNode(iOperationId));
					}
					break;
					
				case Permission_Tree.RENDER_HEIRARCHY_GROUPED:
					// Only Operations with no prerequisites
					if (!this.oOperations[iOperationId].aPrerequisites || !this.oOperations[iOperationId].aPrerequisites.length)
					{
						this._oTreeGrid.oControl.appendChild(this._convertOperationToTreeNode(iOperationId));
					}
					break;
				
				default:
					// All Operations
					this._oTreeGrid.oControl.appendChild(this._convertOperationToTreeNode(iOperationId));
					break;
			}
		}
		
		// Render the Tree
		this._oTreeGrid.oControl.render();
	},
	
	_convertOperationToTreeNode	: function(iOperationId)
	{
		if (!this.oOperations[iOperationId])
		{
			throw "Operation with Id #"+iOperationId+" does not exist!";
		}
		
		var oNodeContent	=	{}
		oNodeContent[Control_Tree_Grid.COLUMN_LABEL]	= this.oOperations[iOperationId].name;
		oNodeContent[Control_Tree_Grid.COLUMN_VALUE]	= iOperationId;
		oNodeContent[Control_Tree_Grid.COLUMN_CHECK]	=	{
																mValue		: iOperationId,
																bChecked	: (this._aSelected.indexOf(iOperationId) > -1)
															};
		var oNode			= new Control_Tree_Grid_Node_Data(oNodeContent, Permission_Tree.TREE_GRID_DATATYPE_OPERATION.sName);
		
		this._oOperationDetails[iOperationId].aNodeInstances.push(oNode);
		
		switch (this._sRenderHeirarchy)
		{
			case Permission_Tree.RENDER_HEIRARCHY_INCLUDES:
				// Render all prerequisites
				if (this.oOperations[iOperationId].aPrerequisites && this.oOperations[iOperationId].aPrerequisites.length)
				{
					for (var i = 0; i < this.oOperations[iOperationId].aPrerequisites.length; i++)
					{
						oNode.appendChild(this._convertOperationToTreeNode(this.oOperations[iOperationId].aPrerequisites[i]));
					}
				}
				break;
				
			case Permission_Tree.RENDER_HEIRARCHY_GROUPED:
				// Render all dependants
				if (this.oOperations[iOperationId].aDependants && this.oOperations[iOperationId].aDependants.length)
				{
					for (var i = 0; i < this.oOperations[iOperationId].aDependants.length; i++)
					{
						oNode.appendChild(this._convertOperationToTreeNode(this.oOperations[iOperationId].aDependants[i]));
					}
				}
				break;
		}
		
		return oNode;
	},
	
	setOperationSelected	: function(iOperationId, bSelected)
	{
		if (!this.oOperations[iOperationId] || !this._oOperationDetails[iOperationId])
		{
			throw "Operation with Id #"+iOperationId+" does not exist!";
		}
		
		this._oOperationDetails[iOperationId].bSelected	= bSelected;
		
		// Update all Node instances
		for (var i = 0; i < this._oOperationDetails[iOperationId].aNodeInstances.length; i++)
		{
			this._oOperationDetails[iOperationId].aNodeInstances[i].setSelected(bSelected, true);
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
		//Reflex_Debug.asHTMLPopup(aSelected);
		
		if (aSelected)
		{
			for (iOperationId in this.oOperations)
			{
				this.setOperationSelected(iOperationId, aSelected.indexOf(parseInt(iOperationId)) > -1);
			}
		}
		else
		{
			//alert("Setting "+aSelected+" selected Operations");
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
	}
});