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
		echo "Hello";
		
		BreadCrumb()->ViewAccount(1000006574);
		BreadCrumb()->ViewService(1, '0787321549');

		Debug(BreadCrumb()->Info());
		
		BreadCrumb()->ShowInfo();
		
		DBO()->Account->Id = 1000004777;
		DBO()->Account->Load();
		//Debug(DBO()->Account->Info());
		//DBO()->Account->ShowInfo();
		//DBO()->Account->Info();
		
		echo "<br>die!";
		die;
		*/
		
		echo "Hello";
		
			DBO()->KnowledgeBase->Load();
			//DBL()->KnowledgeBaseLink->ArticleLeft = DBO()->KnowledgeBase->Id->Value;
			
			DBL()->KnowledgeBaseLink->Where->Set("ArticleLeft = <id> OR ArticleRight = <id>", Array('id'=>DBO()->KnowledgeBase->Id->Value));
			//DBL()->KnowledgeBaseLink->Where->Set("ArticleLeft = 1");
			DBL()->KnowledgeBaseLink->Load();
			
			//DBL()->Note->AccountGroup = DBO()->Account->Id->Value;
			//DBL()->Service->Load();
			//DBL()->Note->Load();
// I need to be able to search both the ArticleLeft and ArticleRight columns of the KnowledgeBaseLink table
// so I have to make sure that the where clause reads "WHERE ArticleLeft = <id> or ArticleRight = <id>"
// I might have to explicitly define the WHERE clause
		
		Debug(DBL()->KnowledgeBaseLink->Info());
		DBL()->KnowledgeBaseLink->ShowInfo();
		
		//echo "<br><br>" . DBO()->KnowledgeBase->Id->Value;
		
		
		echo "die!";
		die;
		// check if the menu is built in the order that it is defined
		
		ContextMenu()->Menu_1->Menu_5->Menu_3->View_Account(1000004777, "Arguement 2");
		ContextMenu()->Do_Something();
		ContextMenu()->Menu_1->Should_Be_Second_In_The_List("well, is it?");
		ContextMenu()->This_Should_Be_The_Third_Item_In_The_Lowest_Level_Menu("Arg1", "Arg2");
		ContextMenu()->Menu_1->Menu_5->Menu_3->Ta_Da();
		ContextMenu()->Menu_1->Menu_1->Generic_Func("well, is it?");
		ContextMenu()->ShowInfo();
		//echo "<br>";
		$strOutput = ContextMenu()->This_Should_Be_The_Third_Item_In_The_Lowest_Level_Menu->ShowInfo("\t");
		echo $strOutput;
		
		die;
		
		//Debug(ContextMenu()->Menu_1->Menu_5->Menu_3->View_Account->Info());
		ContextMenu()->Menu_1->Menu_2->Menu_121->Do_Something();
		ContextMenu()->Menu_1->Menu_2->Menu_121->Do_Something->ShowInfo();
		$strOutput = ContextMenu()->Menu_1->Menu_5->Menu_3->View_Account->ShowInfo("\t\t");
		$strOutput .= ContextMenu()->Menu_1->Menu_5->Menu_3->View_Account->ShowInfo("\t");
		Debug($strOutput);
		//Debug(ContextMenu()->Menu_1->Menu_5->Menu_3->View_Account->ShowInfo("\t\t"));
		ContextMenu()->Menu_1->Menu_5->Menu_3->View_Account->Info();
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
