<?php

include_once('application_loader.php');

// **************************DBO() Tests******************************
/*
DBO()->Table1->BlowMe = "test";
DBO()->Table2->BlowMe = "This is stored in Table2";

DBO()->Account->Id = 1000004777;
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

//Debug(DBL());
//DBL()->Account->Id = 1000004777;





?>
