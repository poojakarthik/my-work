<?php

include_once('application_loader.php');

DBO()->Object->Property->Value = 1;




echo DBO()->Object->Property->Value;

DBO()->Object->Property->Render();


Debug(DBO()->Object->Property);

?>
