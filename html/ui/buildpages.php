<?php

// CULL:: Is this used? If not, get rid. Also, can probably get rid of the skel_pages directory if this goes.

function NewPage($strApp, $strMethod)
{
	/*------------------------------------------------------------------
	 *
	 * Build Application Template
	 *
	 *------------------------------------------------------------------*/
	
	$arrFiles = Array();
	$strSkelDir = "skel_pages/";
	
	// $strApp 		= Account
	// $strMethod 	= View
	
	//ui/app_template/account.php:View()
	
	/* Create Application Template $strApp with method $strMethod
	 *
	 * Tags to be replaced:
	 *	<MethodName>	View
	 *  <PageName>		Account_View
	 *  <ClassName>		AppTemplateAccount
	 *  <Class>			account
	 *
	*/
 	
	$arrPlaceHolders = Array("<MethodName>", "<PageName>", "<ClassName>", "<Class>");
	$arrReplaceValues = Array($strMethod, $strApp . "_" . $strMethod, "AppTemplate" . $strApp, strtolower($strApp));
	
	$strDir = "app_template/";
	
	//Get dir handle
	$handle = opendir($strDir);
	// Get file list
	While (false !== ($strFile = readdir($handle)))
	{
		if (substr($strFile, 0, 1) != ".")
		{
			// If file already exists
			if ($strFile == (strtolower($strApp . ".php")))
			{
				// Get contents of file
				$arrFiles['app_template'] = file_get_contents($strDir . $strFile);
				// If method already exists
				if (strpos($arrFiles['app_template'], "function " . $strMethod . "()"))
				{
					echo "Failed: Method $strMethod already exists\n";
					return FALSE;
				}
				else
				{
					$intInsertPoint = strpos($arrFiles['app_template'], "    //----- DO NOT REMOVE -----//");
					if (!$intInsertPoint)
					{
						echo "Failed: File $strFile already exists, but insertion tag was not found\n";
						return FALSE;
					}
					$strSkeleton = file_get_contents($strSkelDir . "apptemplateclass.php");
					// Replace tags in skeleton
					$strFleshedOut = str_replace($arrPlaceHolders, $arrReplaceValues, $strSkeleton);
					// Add method to end of file
					$arrFiles['app_template'] = substr_replace($arrFiles['app_template'], $strFleshedOut, $intInsertPoint, 0);
					//echo $arrFiles['app_template'];
					
				}
				break;
			}
		}
	}
	closedir($handle);
	
	if ($arrFiles['app_template'] == "")
	{
		// Make file from skeleton
		$strSkeleton = file_get_contents($strSkelDir . "apptemplate.php");
		// Replace tags in skeleton
		$strFleshedOut = str_replace($arrPlaceHolders, $arrReplaceValues, $strSkeleton);
		// Add method to end of file
		$arrFiles['app_template'] = $strFleshedOut;
		//echo $arrFiles['app_template'];
	}
	
	//------------------------------------------------------------------//
	//
	// Build Page Template
	//
	//------------------------------------------------------------------//
	
	//ui/page_template/account_view.php
	
	/* Create Page Template $strApp _ $strMethod
	 *
	 * Tags to be replaced:
	 *	<PageName>		Account View
	 *
	*/ 
	
	$arrPlaceHolders = Array("<PageName>");
	$arrReplaceValues = Array($strApp . " " . $strMethod);
	
	$strDir = "page_template/";
	
	//Get dir handle
	$handle = opendir($strDir);
	// Get file list
	While (false !== ($strFile = readdir($handle)))
	{
		if (substr($strFile, 0, 1) != ".")
		{
			// If file already exists
			if ($strFile == (strtolower($strApp . "_" . $strMethod . ".php")))
			{
				// Return false
				echo "Failed: File $strFile already exists\n";
				return FALSE;
			}
		}
	}
	closedir($handle);
	
	if ($arrFiles['page_template'] == "")
	{
		// Make file from skeleton
		$strSkeleton = file_get_contents($strSkelDir . "pagetemplate.php");
		// Replace tags in skeleton
		$strFleshedOut = str_replace($arrPlaceHolders, $arrReplaceValues, $strSkeleton);
		// Add method to end of file
		$arrFiles['page_template'] = $strFleshedOut;
	}
	
	//------------------------------------------------------------------//
	//
	// Save Application Template and Page Template
	//
	//------------------------------------------------------------------//
	
	if (($arrFiles['app_template'] != "") && ($arrFiles['page_template'] != ""))
	{
		// $arrFiles now contains the full source of both files
		// Save the files back to their directories
		
		//create php file an add modified data to it.
		touch("app_template/" . strtolower($strApp . ".php") );
		file_put_contents("app_template/" . strtolower($strApp . ".php"), $arrFiles['app_template']);
		
		touch("page_template/" . strtolower($strApp . "_" . $strMethod . ".php") );
		file_put_contents("page_template/" . strtolower($strApp . "_" . $strMethod . ".php"), $arrFiles['page_template']);
		
		echo "New Page created successfuly\n";
	}
		

}

function NewHtmlObject($strClass, $strObject)
{

	//------------------------------------------------------------------//
	//
	// Build HTML Template
	//
	//------------------------------------------------------------------//
	
	//ui/html_template/account/details.php

	// $strClass	= Account
	// $strObject	= Details

	/* Create Html Template $strClass / $strObject .php
	 *
	 * Tags to be replaced:
	 *	<ClassName>			HtmlTemplateAccountDetails
	 *  <ClassDescription>	Account Details
	 *  <ClassTitle>		Account
	 *
	*/ 
	
	$arrPlaceHolders = Array("<ClassName>", "<ClassDescription>", "<ClassTitle>");
	$arrReplaceValues = Array("HtmlTemplate" . $strClass . $strObject, $strClass . " " . $strObject, $strClass);
	
	$strSkelDir = "skel_pages/";
	$strSkeleton = "";
	$strFleshedOut = "";
	
	$strClass = strtolower($strClass);
	$strObject = strtolower($strObject);
	
	$strDir = "html_template/";
	
	//Get dir handle
	$handle = opendir($strDir);
	// Get file list
	While (false !== ($strFile = readdir($handle)))
	{
		if (substr($strFile, 0, 1) != ".")
		{
			if ((is_dir($strDir . $strFile)) && ($strFile == $strClass))
			{
				// Traverse subdirectory
				$subHandle = opendir($strDir . $strFile);
				While (false !== ($strSubFile = readdir($subHandle)))
				{
					if (substr($strSubFile, 0, 1) != ".")
					{
						// If file already exists
						if ($strSubFile == $strObject . ".php")
						{
							// Return false
							echo "Failed: File $strSubFile already exists\n";
							return;
						}
					}
				}
				
				// File does not exist
				// Get skeleton file
				$strSkeleton = file_get_contents($strSkelDir . "htmltemplate.php");
				// Replace tags in skeleton
				$strFleshedOut = str_replace($arrPlaceHolders, $arrReplaceValues, $strSkeleton);
				
				break 1;
			}
		}
	}
	
	if ($strFleshedOut == "")
	{
		// Make file from skeleton
		$strSkeleton = file_get_contents($strSkelDir . "htmltemplate.php");
		// Replace tags in skeleton
		$strFleshedOut = str_replace($arrPlaceHolders, $arrReplaceValues, $strSkeleton);
	}
	
	/*------------------------------------------------------------------
	 *
	 * Save HTML Template
	 *
	 *------------------------------------------------------------------*/
	
	// Save the file back to the directory
	// Make directory
	if (!(is_dir($strDir . $strClass)))
	{
		mkdir($strDir . $strClass);
	}
	//create php file an add modified data to it.
	touch($strDir . $strClass . "/" . $strObject . ".php");
	file_put_contents($strDir . $strClass . "/" . $strObject . ".php", $strFleshedOut);
		
	echo "New Html Object created successfuly\n";

}
?>
