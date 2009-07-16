var Operation	= Class.create
({
	initialize	: function()
	{
		
	}
});

/* Static Methods */
Operation.buildDependencyTree	= function(oOperations)
{
	oDependencyTree	= {};
	
	oOperations	= jQuery.json.arrayAsObject(oOperations);
	
	
	//Reflex_Debug.asHTMLPopup(oOperations);
	
	// Convert Child-Prerequisites to Parent-Dependants
	for (iOperationId in oOperations)
	{
		// Convert Child-Prerequisites to Parent-Dependants
		for (var i = 0; i < oOperations[iOperationId].aPrerequisites.length; i++)
		{
			if (!oOperations[oOperations[iOperationId].aPrerequisites[i]])
			{
				throw "Invalid prerequisite reference: "+oOperations[iOperationId].aPrerequisites[i];
			}
			if (oOperations[oOperations[iOperationId].aPrerequisites[i]].aDependants === undefined)
			{
				oOperations[oOperations[iOperationId].aPrerequisites[i]].aDependants	= [];
			}
			
			// Add as a dependant
			oOperations[oOperations[iOperationId].aPrerequisites[i]].aDependants.push(iOperationId);
		}
		
		oOperations[iOperationId].aInstances	= [];
	}
	
	// Create the Tree
	for (iOperationId in oOperations)
	{
		if (!oOperations[iOperationId].aPrerequisites || !oOperations[iOperationId].aPrerequisites.length)
		{
			// Add each top-level Operation (i.e. no prerequsities) to the Tree
			oDependencyTree[iOperationId]	= Operation.buildDependencyTreeNode(oOperations, iOperationId);
		}
	}
	
	Reflex_Debug.asHTMLPopup(oDependencyTree);
	Reflex_Debug.asHTMLPopup(oOperations);
	
	return oDependencyTree;
};

Operation.buildDependencyTreeNode	= function(oOperations, iPrerequisiteOperationId)
{
	var oDependencyTree	= {};
	
	for (var i = 0; i < oOperations[iPrerequisiteOperationId].aDependants.length; i++)
	{
		// Add the dependants to the tree
		oDependencyTree[oOperations[iPrerequisiteOperationId].aDependants[i]]	= Operation.buildDependencyTreeNode(oOperations, oOperations[iPrerequisiteOperationId].aDependants[i]);
	}
	
	return oDependencyTree;
};