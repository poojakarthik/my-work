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
		
		/*ContextMenu()->Menu_1->Menu_2->Menu_3->View_Account(1000004777);
		ContextMenu()->Menu_1->Menu_2->Menu_3->View_Account->ShowInfo();
		ContextMenu()->Menu_1->Menu_12->Menu_121->Do_Something();
		ContextMenu()->Menu_1->Menu_12->Menu_121->Do_Something->ShowInfo();
		*/
		//ContextMenu()->Menu_1->Menu_2->Menu_3->Do_Something(123);
/*		
		ContextMenu()->Menu_1->Menu_11->Menu_111->View_Account(1000004777);
		ContextMenu()->Menu_1->Menu_12->Menu_121->Do_Something();
		
		//!!!!!!!!!!!!!!!!!!!!!!!!!!!The following line crashes.  Apparently you can't have a menu item in the lowest menu level
		ContextMenu()->Do_Something_Bitch(122121212);  
		
		ContextMenu()->Menu_1->Menu_12->Menu_122->Do_Something_Else();
			
		ContextMenu()->Menu_2->Menu_2->Menu_3->Do_Something_Else();
		ContextMenu()->ShowInfo();
*/		
		//ContextMenu()->Render();		// this works
		//ContextMenu()->ShowInfo();	// this works
		
		//Debug(ContextMenu()->Menu_1->Info());
		//Debug(ContextMenu()->Info());
		
		
		BreadCrumbs()->AddCrumb("Acc:<id>", "view_account.php?Account.Id=<id>", Array('Id'=>100004777));
		BreadCrumbs()->AddCrumb("Service:<service>", "view_service.php?Service.Id=<service>", Array('Service'=>0732504200));
		BreadCrumbs()->AddCrumb("Provisioning", "provisioning.php");
		
		/*
		How I would like to define the bread crumbs is:
			BreadCrumbs()->Account(1000004777);
			BreadCrumbs()->Account->Service(0732504200);
			
		but there will only be a list of these, it is not as complex as the context menu
		*/
		
		//BreadCrumbs()->ShowInfo();
		BreadCrumbs()->Render();
		
		Die();
	}
}
