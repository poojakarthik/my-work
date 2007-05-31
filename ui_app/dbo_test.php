<?php


include_once('application_loader.php');

$Application->Load('Test.Test');

// **************************DBO() Tests******************************
/*
DBO()->Table1->BlowMe = "test";
DBO()->Table2->BlowMe = "This is stored in Table2";

DBO()->ShowInfo();
Die();

//DBO()->Account->Id = 1000004777;
DBO()->Account->Load();
//DBO()->Contact->Id = 1000004777;

DBO()->Account->BusinessName->RenderOutput();
//DBO()->Account->BillingType->RenderOutput(TRUE, 1);

//Adding a new record to the Account table
//DBO()->Account->Id = 0;
//DBO()->Account->BusinessName = "CompuGlobalHyperMegaNet";
//DBO()->Account->Save();

//DBO()->Account->rewind();
//print_r(DBO()->Account->current());
//DBO()->Account->next();
//print_r(DBO()->Account->key());

//DBO()->Account->{DBO()->Account->key()}->Render();
*/
// **************************DBL() Tests******************************

DBO()->Account->Id = 1000004777;
DBO()->Account->Load();
DBO()->Account->Info();


DBL()->Account->Id = 1000004777;
// defining "DBL()->Account->Id = 1000004777" doesn't affect the where clause of the DBList
// If you were to now run "DBL()->Account->Load()" it will retrieve every record from the Account table
// and create a DBObject for it
DBL()->Account->Where->SetString("Id < 1000004780");
Debug(DBL()->Account->Where->GetString());
DBL()->Account->Load();
//die;
//Debug(DBL()->Account->Info());
//Debug(DBL());
DBL()->ShowInfo();

?>
