var Reflex_Validation	=
{
	stringNotEmpty	: function(strString, bolTrim)
	{
		strValue	= (bolTrim) ? strValue.replace(/(^.+|$.+)/, '') : strValue;
		return (strValue.length > 0);
	},
	
	email	: function(strEmail)
	{
		return (strEmail.search(/[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/i) != -1) ? true : false;
	},
	
	fnn		: function(strFNN)
	{
		// Allows for FNNs in the following forms:
		//	* 0733534816
		//	* 134585
		//	* 1300154845
		//	* 1800648432
		return (strFNN.search(/(13\d{4}|(0[12356789]\d{2}|1[38])\d{6})/) != -1) ? true : false;
	},
	
	digits	: function(strDigits)
	{
		return (strFNN.search(/^\d*$/) != -1) ? true : false;
	}
};