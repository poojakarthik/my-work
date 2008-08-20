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
		
		// My method  (MAKE SURE YOU HAVE THIS USING THE CATWALK DATABASE WHEN TESTING)
		echo "Hello";

		DBO()->Account->Load();
		
		DBL()->Payment->Account = DBO()->Account->Id->Value;
		DBL()->Payment->Load();
		
		DBL()->Invoice->Account = DBO()->Account->Id->Value;
		DBL()->Invoice->Load();
		
		DBL()->InvoicePayment->Account = DBO()->Account->Id->Value;
		DBL()->InvoicePayment->Load();
		
		// try and stick this in a functionality module  
		// It should really be done in two parts
		// One to find all the invoices related to each payment
		// and one to find all the payments related to each invoice
		// because you make these tables separately
		// Maybe it should be made so that you specify what table needs this one to link to it
		//		For example: 	I'm building a Payments table and want to highlight Invoices that relate to the highlighted payment
		//				or:		I'm building a Payments table and want to highlight the Account
		
///EVERYTHING ABOVE THIS LINE GOES IN THE APP TEMPLATE AND EVERYTHING BELOW GOES IN THE HTML TEMPLATE		
		// Create a payment table and an invoice table
		// Create the payment table
		Table()->Payment->SetHeader("PaymentId", "Account", "Paid On", "Amount");
		foreach (DBL()->Payment as $dboPayment)
		{
			// references to Value should eventually be changed to AsValue or AsOutput
			Table()->Payment->AddRow($dboPayment->Id->Value, 
									$dboPayment->Account->Value, 
									$dboPayment->PaidOn->Value, 
									$dboPayment->Amount->Value);
			// find each InvoicePayment record that relates to the current Payment record
			foreach (DBL()->InvoicePayment as $dboInvoicePayment)
			{
				if ($dboInvoicePayment->Payment->Value == $dboPayment->Id->Value)
				{
					// The current InvoicePayment record relates to the payment so add it as an index
					Table()->Payment->AddIndex("InvoiceRun", $dboInvoicePayment->InvoiceRun->Value);
				}
			}
		}
		
		// Link the invoice table to the payment table so that highlighting a record in the payment table, will highlight related records in the invoice table
		Table()->Payment->LinkTable("Invoice", "InvoiceRun");
		
		// Create the invoice table
		Table()->Invoice->SetHeader("Created On", "InvoiceId", "Account", "Due On", "Total", "Balance");
		foreach (DBL()->Invoice as $dboInvoice)
		{
				Table()->Invoice->AddRow($dboInvoice->CreatedOn->Value, 
										$dboInvoice->Id->Value, 
										$dboInvoice->Account->Value,
										$dboInvoice->DueOn->Value,
										$dboInvoice->Total->Value,
										$dboInvoice->Balance->Value);
				Table()->Invoice->AddIndex("InvoiceRun", $dboInvoice->InvoiceRun->Value);
		}
		
		// Link the payment table to the invoice table so that highlighting a record in the invoice table, will highlight related records in the invoice table
		Table()->Payment->LinkTable("Payment", "InvoiceRun");
		
		//Debug(Table()->Payment->Info());
		//Debug(Table()->Invoice->Info());
		//Debug(Table()->Payment->ShowInfo("\t"));
		//Table()->Invoice->ShowInfo();
		//Debug(Table()->Info());
		Table()->ShowInfo();
		
		echo "<br>die!";
		die;
		
		
		DBO()->Account->Load();
		
		DBO()->Account->Balance = -500;
		
		DBO()->Account->Balance->RenderOutput(0);  // the conditions will force this to be rendered using context 1
		
		DBO()->Account->BillingType = -1;
		echo "<br>";
		DBO()->Account->BillingType->RenderOutput();
		DBO()->Account->BillingType = 2;
		echo "<br>";
		DBO()->Account->BillingType->RenderOutput();
		DBO()->Account->BillingType = 3;
		echo "<br>";
		DBO()->Account->BillingType->RenderOutput();
		DBO()->Account->BillingType = 10;
		echo "<br>";
		DBO()->Account->BillingType->RenderOutput();
		DBO()->Account->BillingType = 0;
		echo "<br>";
		DBO()->Account->BillingType->RenderOutput();
		echo "<br>";
		DBO()->Account->Balance = 500;
		DBO()->Account->Balance->RenderOutput(0);
		

		echo "<br>die!";
		die;
		
/*		
		BreadCrumb()->ViewAccount(1000006574);
		BreadCrumb()->ViewService(1, '0787321549');

		//Debug(BreadCrumb()->Info());
		
		//BreadCrumb()->ShowInfo();
		
		DBO()->Account->Id = 1000004777;
		DBO()->Account->Load();
		//Debug(DBO()->Account->Info());
		//DBO()->Account->ShowInfo();
		//DBO()->Account->Info();
		DBO()->ShowInfo();
		
		echo "<br>die!";
		die;
		
/*		
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
*/
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
?>