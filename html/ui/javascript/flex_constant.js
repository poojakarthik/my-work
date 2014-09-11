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
		if (typeof mixConstantGroup != 'string')
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
		
		if (arrLoadConstantGroups.length)
		{
			// Load
			var fncJsonFunc		= jQuery.json.jsonFunction(this._loadConstantGroupResponse.bind(this, fncCallback), this._loadConstantGroupResponse.bind(this, fncCallback), 'ConstantGroup', 'getConstantGroups');
			fncJsonFunc(arrLoadConstantGroups, true);
		}
		else if (fncCallback)
		{
			// Nothing to load - call the Callback function
			fncCallback();
		}
	},
	
	getConstantGroupOptions : function(sConstantGroup, fnCallback)
	{
		var aConstantGroup = Flex.Constant.arrConstantGroups[sConstantGroup];
		if (!aConstantGroup)
		{
			Flex.Constant.loadConstantGroup(sConstantGroup, Flex.Constant.getConstantGroupOptions.curry(sConstantGroup, fnCallback));
			return;
		}
		
		var aOptions = [];
		for (var iId in aConstantGroup)
		{
			aOptions.push(
				$T.option({value: iId},
					aConstantGroup[iId].Name ? aConstantGroup[iId].Name : aConstantGroup[iId].Description
				)
			);
		}
		fnCallback(aOptions);
	},
	
	_loadConstantGroupResponse	: function (fnCallback, oResponse) {
		if (oResponse) {
			// AJAX Success
			if (oResponse.Success) {
				// Load Successful
				for (var i in oResponse.arrConstantGroups) {
					// Add to Constant Group List
					this.arrConstantGroups[i] = oResponse.arrConstantGroups[i];
					
					// Add to Constant List
					for (var t in this.arrConstantGroups[i]) {
						this.arrConstants[this.arrConstantGroups[i][t].Constant] = parseInt(t);
					}
				}
				
				if (fnCallback) {
					fnCallback();
				}
				return true;
			}
		}

		// Error
		jQuery.json.errorPopup(oResponse);
	}
});

if (Flex.Constant == undefined)
{
	Flex.Constant	= new Flex_Constant();
	$CONSTANT		= Flex.Constant.arrConstants;
	$CONSTANT_GROUP	= Flex.Constant.arrConstantGroups;
}