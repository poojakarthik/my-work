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
	
	for (iOperationId in oOperations)
	{
		if (!oOperations[iOperationId].aPrerequisites || !oOperations[iOperationId].aPrerequisites.length)
		{
			// Add each top-level Operation (i.e. no prerequsities) to the Tree
			oDependencyTree[iOperationId]	= oOperations[iOperationId];
		}
		else
		{
			// Convert Child-Prerequisites to Parent-Dependants
			for (var i = 0; i < oOperations[iOperationId].aPrerequisites.length; i++)
			{
				if (oOperations[oOperations[iOperationId].aPrerequisites[i]].oDependants === undefined)
				{
					oOperations[oOperations[iOperationId].aPrerequisites[i]].oDependants	= {};
				}
				
				// Add as a dependant
				oOperations[oOperations[iOperationId].aPrerequisites[i]].oDependants[iOperationId]	= oOperations[iOperationId];
			}
		}
	}
	
	Reflex_Debug.asHTMLPopup(oOperations);
	Reflex_Debug.asHTMLPopup(oDependencyTree);
	
	return oDependencyTree;
};

Operation.buildDependencyTreeNode	= function()
{
	
};