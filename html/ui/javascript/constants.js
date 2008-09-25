//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// constants.js
//----------------------------------------------------------------------------//
/**
 * constants
 *
 * javascript framework for dealing with constants that are available in flex
 *
 * javascript framework for dealing with constants that are available in flex
 * 
 *
 * @file		constants.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel "MagnumSwordFortress" Dawkins
 * @version		8.06
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
//----------------------------------------------------------------------------//
// VixenConstantsClass
//----------------------------------------------------------------------------//
/**
 * VixenConstantsClass
 *
 * Encapsulates all event handling required of the "Account Details" HtmlTemplate
 *
 * Encapsulates all event handling required of the "Account Details" HtmlTemplate
 * Note that this only works with ConstantGroups where the constants are integers or strings
 *
 * @package	ui_app
 * @class	VixenConstantsClass
 * 
 */
function VixenConstantsClass()
{
	// Stores all constant groups
	this.Group	= {};
	
	// Adds the constant group to the cache of constant groups
	/*
	 *	objConstantGroup must be of the form
	 *	object[intConstValue].Constant		= ConstantName
	 *						.Description	= ConstantDescription
	 */
	this.SetConstantGroup = function(strConstantGroup, objConstantGroup)
	{
		var mixValue;
		this.Group[strConstantGroup] = {};
		for (var i=0; i < objConstantGroup.length; i++)
		{
			// Try casting the value to an integer
			mixValue = parseInt(i);
			if (isNaN(mixValue))
			{
				// The value must be a string
				mixValue = i;
			}
			
			// Add the constant to the this object
			this[objConstantGroup[i].Constant] = mixValue;
			
			// Add the constant's details to the ConstantGroup
			this.Group[strConstantGroup][mixValue] = {	
														Constant	: objConstantGroup[i].Constant,
														Description	: objConstantGroup[i].Description
													};
		}
	}
	
	/*
	 *	Returns the ConstantGroup in the form:
	 *	object[intConstValue].Constant		= ConstantName
	 *						.Description	= ConstantDescription
	 *	if the ConstantGroup can not be found, it will return false
	 */
	this.GetConstantGroup = function(strConstantGroup)
	{
		if (this.Group[strConstantGroup] == undefined)
		{
			return false;
		}
		return this.Group[strConstantGroup];
	}
	
	// This is redundant as you can just append the constant's name to Vixen.Constants or $Const like $Const.ACCOUNT_STATUS_ACTIVE
	this.Get = function(strConstant)
	{
		if (this[strConstant] == undefined)
		{
			throw("ERROR: constant: '"+ strConstant +"' is undefined");
			return;
		}
		
		return this[strConstant];
	}
	
	// mixConstant can be 
	// returns false if the constant cannot be found in the constantGroup
	this.GetDescription = function(mixConstantValue, strConstantGroup)
	{
		if (this.Group[strConstantGroup] == undefined || this.Group[strConstantGroup][mixConstantValue] == undefined)
		{
			// The constant could not be found
			return false;
		}
		
		return this.Group[strConstantGroup][mixConstantValue].Description;
	}
	
	// Returns true if the constant value is in the ConstantGroup, else false
	// It is a precondition that the ConstantGroup is loaded
	this.ConstantGroupHasConstant = function(mixConstantValue, strConstantGroup)
	{
		return (this.Group[strConstantGroup][mixConstantValue] != undefined);
	}
	
	this.LoadConstantGroupFromServer = function(strConstantGroup)
	{
		//TODO! retrieves the constant group from the server
		// remember that you cannot pause a javascript function
		// That is to say, if you call this function, the block of calling code wont know when the constant group has been loaded, unless
		// It polls the constant group, or registers some sort of listener, which would be ugly
		
		// If the calling code needed to use this, then it could call it and then call itself in a timeout and keep doing that until the ConstantGroup
		// is loaded, or the constant group is set to FALSE indicating that it could not be found
	}
}

// instanciate the object
if (Vixen.Constants == undefined)
{
	Vixen.Constants = new VixenConstantsClass;
	$Const = Vixen.Constants;
}
