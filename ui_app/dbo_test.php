<?php

include_once('application_loader.php');

//DBO()->Object->Property->value = 1;
DBO()->Object->Property = "test";

Debug(DBO()->Object->Property->value);


//echo DBO()->Object->Property->value;

//DBO()->Object->Property->Render();


//Debug(DBO()->Object->Property);
//Debug(DBO()->Object);
?>
