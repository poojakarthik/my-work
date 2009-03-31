// Class: Constant
// Handles Flex Constants (and Constant Groups)
var Flex_Constant	= Class.create
({
	initialize	: function()
	{
		this.arrConstants		= {};
		this.arrConstantGroups	= {};
	},
	
	// Function: load()
	// Loads a Constant Group (or array of Constant Groups) from Flex using AJAX
	loadConstantGroup	: function(mixConstantGroup, fncCallback)
	{
		var arrConstantGroups	= new Array();
		if (typeof mixConstantGroup == 'array')
		{
			arrConstantGroups	= mixConstantGroup;
		}
		else
		{
			arrConstantGroups.push(mixConstantGroup);
		}
		
		// Filter out any that are already loaded
		var arrLoadConstantGroups	= [];
		for (var i = 0; i < arrConstantGroups.length; i++)
		{
			if (this.arrConstantGroups[arrConstantGroups[i]] == undefined)
			{
				// Add
				arrLoadConstantGroups.push(arrConstantGroups[i]);
			}
		}
		
		// Load
		var fncJsonFunc		= jQuery.json.jsonFunction(this._loadConstantGroupResponse.bind(this, fncCallback), this._loadConstantGroupResponse.bind(this, fncCallback), 'ConstantGroup', 'getConstantGroups');
		fncJsonFunc(arrLoadConstantGroups[0], true);
	},
	
	_loadConstantGroupResponse	: function (fncCallback, objResponse)
	{
		if (objResponse)
		{
			// AJAX Success
			if (objResponse.Success)
			{
				// Load Successful
				for (var i in objResponse.arrConstantGroups)
				{
					// Add to Constant Group List
					this.arrConstantGroups[i]	= objResponse.arrConstantGroups[i];
					
					// Add to Constant List
					for (var t in this.arrConstantGroups[i])
					{
						this.arrConstants[this.arrConstantGroups[i][t].Constant]	= parseInt(t);
					}
				}
				
				return true;
			}
			else if (objResponse.Message)
			{
				$Alert(objResponse.Message);
				return false;
			}
			else
			{
				$Alert(objResponse);
				return false;
			}
		}
		else
		{
			// AJAX Failure
			$Alert("There was an error while trying to contact the Server.");
		}
	}
});

if (Flex.Constant == undefined)
{
	Flex.Constant	= new Flex_Constant();
	$CONSTANT		= Flex.Constant.arrConstants;
}