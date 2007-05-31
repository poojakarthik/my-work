<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

class AppTemplateTest extends ApplicationTemplate
{


	function Test()
	{
		if (DBO()->Account->Id->Valid())
		{
			DBO()->Account->Load();
			DBL()->Service->Account = DBO()->Account->Id->Value;
			DBL()->Note->AccountGroup = DBO()->Account->Id->Value;
			DBL()->Service->Load();
			DBL()->Note->Load();
		}
		Debug(DBO()->ShowInfo("\t"));
		Debug(DBL()->ShowInfo("\t\t"));
		
		
		Die();
	}
}
