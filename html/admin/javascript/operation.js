var Operation	= Class.create
({
	initialize	: function()
	{
		
	}
});

/* Static Methods */
Operation.prepareForTreeGrid	= function(oOperations)
{
	oDependencyTree	= {};
	
	oOperations	= jQuery.json.arrayAsObject(oOperations);
	
	//Reflex_Debug.asHTMLPopup(oOperations);
	
	for (iOperationId in oOperations)
	{
		oOperations[iOperationId].aDependants	= [];
	}
	
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
			
			// Add as a dependant
			oOperations[oOperations[iOperationId].aPrerequisites[i]].aDependants.push(iOperationId);
		}
		
		oOperations[iOperationId].aInstances	= [];
	}
	
	//Reflex_Debug.asHTMLPopup(oOperations);
	
	return oOperations;
};