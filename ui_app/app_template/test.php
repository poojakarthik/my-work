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
		
		ContextMenu()->Menu_1->Menu_5->Menu_3->View_Account(1000004777, "Arguement 2");
		ContextMenu()->Menu_1->Menu_5->Menu_3->View_Account->ShowInfo();
		//Debug(ContextMenu()->Menu_1->Menu_5->Menu_3->View_Account->Info());
		ContextMenu()->Menu_1->Menu_2->Menu_121->Do_Something();
		ContextMenu()->Menu_1->Menu_2->Menu_121->Do_Something->ShowInfo();
		ContextMenu()->Menu_1->Menu_5->Menu_3->View_Account->ShowInfo();
		die;
		//ContextMenu()->Menu_1->Menu_12->Menu_121->Do_Something->ShowInfo();
		ContextMenu()->Menu_1->Menu_5->Menu_3->Delete_Account();
		ContextMenu()->Menu_1->Menu_5->Menu_3->Add_Account();
		
		
		ContextMenu()->ShowInfo();
		
		Debug(ContextMenu()->Menu_1->Menu_2->Menu_3->View_Account->Info());
		
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
		
		
/*		BreadCrumbs()->AddCrumb("Acc:<id>", "view_account.php?Account.Id=<id>", Array('Id'=>100004777));
		BreadCrumbs()->AddCrumb("Service:<service>-<account>", "view_service.php?Service.Id=<service>&Account=<account>", 
								Array('Service'=>0732504200, 'Account'=>100004777));
		BreadCrumbs()->AddCrumb("Provisioning", "provisioning.php");
		
		//BreadCrumbs()->ShowInfo();
		echo "BreadCrumbs()->Render()...<br>";
		BreadCrumbs()->Render();
		
		echo "<br><br>BreadCrumbs()->Info()...<br>";
		Debug(BreadCrumbs()->Info());
		
		echo "<br><br>BreadCrumbs()->ShowInfo()...<br>";
		BreadCrumbs()->ShowInfo();
*/		
		die;
	}
}
