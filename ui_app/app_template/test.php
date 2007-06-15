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
		
		// This array is used by the DBO()->Invoices to store what payment relates to what Invoice
		// It is necessary to build it over time as many payments can relate to the one invoice
		$arrPayments = Array;
		
		// try and stick this in a functionality module  
		// It should really be done in two parts
		// One to find all the invoices related to each payment
		// and one to find all the payments related to each invoice
		// because you make these tables separately
		// Maybe it should be made so that you specify what table needs this one to link to it
		//		For example: 	I'm building a Payments table and want to highlight Invoices that relate to the highlighted payment
		//				or:		I'm building a Payments table and want to highlight the Account
		foreach (DBL()->Payment as $dboPayment)
		{
			$arrInvoices = Array;
			
			// find each InvoicePayment record that relates to the current Payment record
			foreach (DBL()->InvoicePayment as $dboInvoicePayment)
			{
				if ($dboInvoicePayment->Payment->Value == $dboPayment->Id->Value)
				{
					// the current InvoicePayment record relates to the current Payment record
					
					// find each Invoice that relates to the current InvoicePayment record
					foreach (DBL()->Invoice as $dboInvoice)
					{
						if ($dboInvoice->InvoiceRun->Value == $dboInvoicePayment->InvoiceRun->Value)
						{
							// the current Invoice record relates to the current InvoicePayment record
							// this means that the current Invoice record relates to the current Payment Record
							// so store this information in both of them so that it can be used as an index when VixenTables are being built
							// also rememeber that a payment can be linked to multiple invoices
							// and an invoice can be linked to multiple payments
							
							$arrInvoices[] = $dboInvoice->Id->Value;
							$arrPayments[$dboInvoice->Id->Value][] = $dboPayment->Id->Value;
						}
					}
				}
			}
			$dboPayment->Invoices = $arrInvoices;
		}
		// for each invoice, set the payments that relate to it
		foreach ($DBL()->Invoice as $dboInvoice)
		{
			$dboInvoice->Payments = $arrPayments[$dboInvoice->Id->Value];
		}
		
		// Create a payment table and an invoice table
		// Create the payment table
		Table()->Payment->SetHeader("PaymentId", "Account", "Paid On", "Amount");
		foreach (DBL()->Payment as $dboPayment)
		{
			Table()->Payment->AddRow($dboPayment->Id->Value, $dboPayment->Account->Value, $dboPayment->PaidOn->Value, $dboPayment->Amount->Value);
			foreach ($dboPayment->Invoices->Value as $intInvoice)
			{
				Table()->Payment->AddIndex("Invoice", $intInvoice);
			}
		}
		// Link the invoice table to the payment table so that highlighting a record in the payment table, will highlight related records in the invoice table
		Table()->Payment->LinkTable("Invoice", "Invoice");
		
		// Create the invoice table
		Table()->Invoice->SetHeader("InvoiceId", "Account", "Due On", "Total", "Balance");
		foreach (DBL()->Invoice as $dboInvoice)
		{
			
		}
		
		
		
		
		
		//build functionality module for this
		// linking a payment to an invoice through the InvoicePayment table (Invoice <==> InvoiceRun,Account <==> Payment)
		

		Table()->PaymentTable->SetHeader("Col1 Title", "Col2 Title", "Col3 Title");
		Table()->PaymentTable->SetWidth("20%", "30%", "50%");
		Table()->PaymentTable->SetAlignment("Left", FALSE, "Right");
		
		// row 1 definition
		Table()->PaymentTable->AddRow("Col1.Value1", "Col2.Value1", "Col3.Value1");
		Table()->PaymentTable->SetDetail("[INSERT HTML CODE HERE]");
		Table()->PaymentTable->SetToolTip("[INSERT HTML CODE HERE FOR THE TOOL TIP FOR ROW1]");
		Table()->PaymentTable->AddIndex("Invoice", $intInvoiceNumber);
		
		// row 2 definition
		Table()->PaymentTable->AddRow("Col1.Value2", "Col2.Value2", "Col3.Value2");
		Table()->PaymentTable->SetDetail("[INSERT HTML CODE HERE ALSO]");
		Table()->PaymentTable->SetToolTip("[INSERT HTML CODE HERE FOR THE TOOL TIP FOR ROW2]");
		
		
		
		Table()->PaymentTable->RowHighlighting = TRUE;
		
		Debug(Table()->PaymentTable->Info());
		
		echo "<br>die!";
		die;
		
		// Jared's method
		echo "Hello";

		DBO()->Account->Load();
		
		DBL()->Payment->Account = DBO()->Account->Id->Value;
		DBL()->Payment->Load();
		
		DBL()->Invoice->Account = DBO()->Account->Id->Value;
		DBL()->Invoice->Load();
		
		
		// try and stick this in a functionality module
		foreach (DBL()->Payment as $dboPayment)
		{
			DBL()->InvoicePayment->Account = $dboPayment->Account->Value;
			DBL()->InvoicePayment->Payment = $dboPayment->Id->Value;
			DBL()->InvoicePayment->Load();
			
			foreach (DBL()->InvoicePayment as $dboInvoicePayment)
			{
				foreach (DBL()->Invoice as $dboInvoice)
				{
					if ($dboInvoice->InvoiceRun->Value == $dboInvoicePayment->InvoiceRun->Value)
					{
						//if it matches then that is the (invoice) Id that you use
						
					}
				}
			}
		}
		
		//build functionality module for this
		// linking a payment to an invoice through the InvoicePayment table (Invoice <==> InvoiceRun,Account <==> Payment)
		

		Table()->PaymentTable->SetHeader("Col1 Title", "Col2 Title", "Col3 Title");
		Table()->PaymentTable->SetWidth("20%", "30%", "50%");
		Table()->PaymentTable->SetAlignment("Left", FALSE, "Right");
		
		// row 1 definition
		Table()->PaymentTable->AddRow("Col1.Value1", "Col2.Value1", "Col3.Value1");
		Table()->PaymentTable->SetDetail("[INSERT HTML CODE HERE]");
		Table()->PaymentTable->SetToolTip("[INSERT HTML CODE HERE FOR THE TOOL TIP FOR ROW1]");
		Table()->PaymentTable->AddIndex("Invoice", $intInvoiceNumber);
		
		// row 2 definition
		Table()->PaymentTable->AddRow("Col1.Value2", "Col2.Value2", "Col3.Value2");
		Table()->PaymentTable->SetDetail("[INSERT HTML CODE HERE ALSO]");
		Table()->PaymentTable->SetToolTip("[INSERT HTML CODE HERE FOR THE TOOL TIP FOR ROW2]");
		
		
		
		Table()->PaymentTable->RowHighlighting = TRUE;
		
		Debug(Table()->PaymentTable->Info());
		
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
