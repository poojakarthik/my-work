//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rate_group_add.js
//----------------------------------------------------------------------------//
/**
 * rate_group_add
 *
 * javascript required of the "Add Rate Group" popup webpage
 *
 * javascript required of the "Add Rate Group" popup webpage
 * 
 *
 * @file		rate_add.js
 * @language	PHP
 * @package		ui_app
 * @author		Ross Mullen
 * @version		7.08
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenRateAddClass
//----------------------------------------------------------------------------//
/**
 * VixenRateAddClass
 *
 * Encapsulates all event handling required of the "Add Rate" popup webpage
 *
 * Encapsulates all event handling required of the "Add Rate" popup webpage
 * 
 *
 * @package	ui_app
 * @class	VixenRateAddClass
 * 
 */
function VixenRateAddClass()
{
	const RATE_CAP_NO_CAP = 100;
	const RATE_CAP_CAP_UNITS = 101;
	const RATE_CAP_CAP_COST = 102;
	const RATE_CAP_NO_CAP_LIMITS = 103;
	const RATE_CAP_CAP_LIMIT = 104;
	const RATE_CAP_CAP_USAGE = 105;
	//------------------------------------------------------------------------//
	// InitialiseForm
	//------------------------------------------------------------------------//
	/**
	 * InitialiseForm
	 *
	 * Sets the member variable storing data relating to all RecordTypes
	 *
	 * Sets the member variable storing data relating to all RecordTypes
	 *
	 * @param	array		arrRecordTypes		array storing all RecordType data
	 *											structure:
	 *											arrRecordTypes[].Id
	 *															.ServiceType
	 *															.Name
	 * @return	void
	 * @method
	 */
	//this.InitialiseForm = function(arrRecordTypes)
	//{
	//	this._arrRecordTypes = arrRecordTypes;
	//}
	
	this.RateCapOnChange = function(intRateCap)
	{
		intRateCap = parseInt(intRateCap);
		switch (intRateCap)
		{
			case RATE_CAP_NO_CAP:
				// hide any details not required for a no cap
				document.getElementById('CapDetailDiv').style.display='none';
				document.getElementById('ExcessDetailDiv').style.display='none';
				break;
			case RATE_CAP_CAP_UNITS:
				// show any details required for a cap
				document.getElementById('CapDetailDiv').style.display='inline';
				break;
			case RATE_CAP_CAP_COST:
				// show the cap details required for a cap
				document.getElementById('CapDetailDiv').style.display='inline';
				break;
			case RATE_CAP_NO_CAP_LIMITS:
				// hide any details not required for a no cap
				document.getElementById('ExcessDetailDiv').style.display='none';
				break;	
			case RATE_CAP_CAP_LIMIT:
				// hide any details not required for a no cap
				document.getElementById('ExcessDetailDiv').style.display='none';
				break;							
			case RATE_CAP_CAP_USAGE:
				// show the excess details required for a cap
				document.getElementById('ExcessDetailDiv').style.display='inline';
				break;
		}
	}
}

// instantiate the object
Vixen.RateAdd = new VixenRateAddClass;
