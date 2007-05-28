<?php

include_once('application_loader.php');

//DBO()->Object->Property->value = 1;
DBO()->Object->BlowMe = "test";

//Debug(DBO()->Object->Property->value);


//echo DBO()->Object->Property->value;

DBO()->Object->BlowMe->Render();


//Debug(DBO()->Object->Property);
//Debug(DBO()->Object);

DBO()->Account->Id = 1000004777;
DBO()->Account->Load(1000004777);  // you should not have to pass the id to this method
DBO()->Account->ABN->Render();

?>
