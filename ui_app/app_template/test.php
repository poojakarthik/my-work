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
		//DBO()->Table1->BlowMe = "test";
		//DBO()->Table2->BlowMe = "This is stored in Table2";
		if (DBO()->Account->Id->Valid())
		{
			DBO()->Account->Load();
			DBL()->Service->Account = DBO()->Account->Id->Value;
			DBL()->Service->Load();
		}
		DBO()->ShowInfo();
		DBL()->ShowInfo();
		
		
		Die();
	}
}
