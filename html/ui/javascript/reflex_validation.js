var Reflex_Validation	=
{
	email	: function(strTest)
	{
		return (strTest.search(/^[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i) != -1) ? true : false;
	},
	
	fnn		: function(strTest)
	{
		// Allows for FNNs in the following forms:
		//	* 0733534816	-- Fixed Line (Land Line)
		//	* 0406818784	-- Mobile MSN
		//	* 134585		-- Inbound 13
		//	* 1300154845	-- Inbound 1300
		//	* 1800648432	-- Inbound 1800
		return (Reflex_Validation.fnnFixedLine(strTest) || Reflex_Validation.fnnMobile(strTest) || Reflex_Validation.fnnInbound(strTest));
		//return (strTest.search(/^(13\d{4}|(0[123456789]\d{2}|1[38]00)\d{6})$/) != -1) ? true : false;
	},
	
	fnnFixedLine	: function(strTest)
	{
		return (strTest.search(/^0[12356789]\d{8}$/) != -1) ? true : false;
	},
	
	fnnMobile		: function(strTest)
	{
		return (strTest.search(/^04\d{8}$/) != -1) ? true : false;
	},
	
	fnnInbound		: function(strTest)
	{
		return (strTest.search(/^(13\d{4}|1[38]00\d{6})$/) != -1) ? true : false;
	},
	
	digits	: function(strTest)
	{
		return (strTest.search(/^\d*$/) != -1) ? true : false;
	}
};