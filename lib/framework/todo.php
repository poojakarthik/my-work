<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

// config
$strDomain	= "yellowbilling.com.au";
$strSubject	= "TODO List : ".Date('Y-m-d');
$strHeader	= "The following TODOs are currently assigned to you;\n";
$strFooter	= "\n\nThis is an automated message, do not reply\n\n";
$strAdmin	= "flame";

// include developer tools
require_once('dev_tools.php');

// get a list of all todos
$arrTODO = GetTODOList("/home/flame/vixen");

// setup email array
$arrEmail = Array();

// build email text
foreach ($arrTODO as $strFile => $arrUser)
{
	foreach ($arrUser as $strUser=>$arrUserTODO)
	{
		$arrEmail[$strUser] .= "File : $strFile\n";
		foreach ($arrUserTODO as $strTODO)
		{
			if ($strTODO)
			{
				$arrEmail[$strUser] .= "        $strTODO\n";
			}
			else
			{
				$arrEmail[$strUser] .= "        TODO (see file for details)\n";
			}
		}
	}
}

// send emails
foreach($arrEmail AS $strUser=>$strMessage)
{
	$strUser = strtolower($strUser);
	switch ($strUser)
	{
		case 'todo':
		case 'jared':
			$strUser = 'flame';
		case 'bash':
		case 'rich':
		case 'flame':
			// send email
			mail ("$strUser@$strDomain", $strSubject, "$strUser, \n $strHeader \n $strMessage \n $strFooter");
			//echo "$strUser, \n $strHeader \n $strMessage \n $strFooter";
			
			// send admin a copy of every email
			if ($strUser != $strAdmin)
			{
				mail ("$strAdmin@$strDomain", "$strUser : $strSubject", "$strUser, \n $strHeader \n $strMessage \n $strFooter");
			}
			break;
	}
}

?>
