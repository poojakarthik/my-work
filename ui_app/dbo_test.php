<?php

include_once('application_loader.php');

//DBO()->Object->Property->value = 1;
DBO()->Table1->BlowMe = "test";
DBO()->Table2->BlowMe = "This is stored in Table2";

//DBO()->Table1->BlowMe->Render();
//DBO()->Table2->BlowMe->Render();


//Debug(DBO()->Object->Property->value);


//echo DBO()->Table1->BlowMe->Value;

//echo "<br>";

//Debug(DBO()->Object->Property);
//Debug(DBO()->Object);

DBO()->Account->Id = 1000004777;
DBO()->Account->Load();
DBO()->Contact->Id = 1000004777;
//$test = DBO()->Account->Load(1000004777);  // you should not have to pass the id to this method
/*DBO()->Account->Load();
echo "<br>";

echo "<br>";
DBO()->Account->ABN->Render();
echo "<br>";
DBO()->Account->BusinessName->Render();
echo "<br><br><br>";
*/
DBO()->Account->BusinessName->RenderOutput();



//print_r(DBO()->Account);

//DBO()->Account->Id = 0;
//DBO()->Account->BusinessName = "CompuGlobalHyperMegaNet";
//DBO()->Account->Save();


?>
