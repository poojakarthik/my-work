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

		/*
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
		*/
		
		ContextMenu()->Menu_1->Menu_11->Menu_111->View_Account(1000004777);
		ContextMenu()->Menu_1->Menu_12->Menu_121->Do_Something();
		ContextMenu()->Menu_1->Menu_12->Menu_122->Do_Something_Else();
		ContextMenu()->Menu_1->Menu_12->Menu_121->Do_Something();
		ContextMenu()->Menu_1->Menu_12->Menu_121->Do_Something();
		
		ContextMenu()->Menu_2->Menu_2->Menu_3->Do_Something_Else();
		

		//ContextMenu()->Render();		// this works
		//ContextMenu()->ShowInfo();	// this works
		
		//Debug(ContextMenu()->Menu_1->Info());
		Debug(ContextMenu()->Info());
		
		Die();
	}
}
