<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// collection application
//----------------------------------------------------------------------------//
require_once('application_loader.php');

//----------------------------------------------------------------------------//
// CONFIG
//----------------------------------------------------------------------------//
				
		// TESTING DEFINITIONS
		$strTestUsername	= "download";
		$strTestPassword	= "password";
		$strTestDir			= "/home/CDR/";
		$strTestServer		= "10.11.12.13";

		// Unitel Dirs
		$arrUnitelTestDir[]	= "2006/aug/";
		$arrUnitelTestDir[]	= "2006/sep/";
		$arrUnitelTestDir[]	= "2006/oct/";
		$arrUnitelTestDir[]	= "2006/nov/";
		$arrUnitelTestDir[]	= "2006/dec/";
		foreach($arrUnitelTestDir AS $strUnitelTestDir)
		{
 			$arrConfig['Define']["RSLCOM"]	["Dir"][]						= $strTestDir."unitel/cdrbatches/".$strUnitelTestDir;
			$arrConfig['Define']["RSLCOM"]	["Dir"][]						= $strTestDir."unitel/cdrbatchesoffnet/".$strUnitelTestDir;
			$arrConfig['Define']["Commander"]	["Dir"][]					= $strTestDir."unitel/mobilecdrbatches/".$strUnitelTestDir;
		}
		
		// Optus Dirs
		$arrOptusTestDir[] 	= "August 06/Speedi Files/";
		$arrOptusTestDir[] 	= "Sept 06/Speedi Files/";
		$arrOptusTestDir[] 	= "Oct 06/Speedi Files/";
		$arrOptusTestDir[] 	= "Nov 06/Speedi Files/";
		$arrOptusTestDir[] 	= "Dec 06/Speedi Files/";
		foreach($arrOptusTestDir AS $strOptusTestDir)
		{
			$arrConfig['Define']["Optus"]	["Dir"][]						= $strTestDir."optus/".$strOptusTestDir;
		}
		
		// AAPT Dirs
		$arrAAPTTestDir[] 	= "August 06/";
		$arrAAPTTestDir[] 	= "Sept 06/";
		$arrAAPTTestDir[] 	= "Oct 06/";
		$arrAAPTTestDir[] 	= "Nov 06/";
		$arrAAPTTestDir[] 	= "Dec 06/";
		foreach($arrAAPTTestDir AS $strAAPTTestDir)
		{
			$arrConfig['Define']["AAPT"]	["Dir"][]						= $strTestDir."aapt/".$strAAPTTestDir;
		}
		
		// Unitel Landline and S&E Definition

		$arrConfig['Define']["RSLCOM"]	["Name"]							= "RSLCOM";
		$arrConfig['Define']["RSLCOM"]	["Carrier"]							= CARRIER_UNITEL;
 		$arrConfig['Define']["RSLCOM"]	["Type"]							= COLLECTION_TYPE_FTP;
 		$arrConfig['Define']["RSLCOM"]	["Server"]							= $strTestServer;
 		$arrConfig['Define']["RSLCOM"]	["Username"]						= $strTestUsername;
 		$arrConfig['Define']["RSLCOM"]	["PWord"]							= $strTestPassword;
 		$arrConfig['Define']["RSLCOM"]	["FinalDir"]						= DESTINATION_ROOT.'unitel/';
		$arrConfig['Define']["RSLCOM"]	["FileType"][REGEX_RSLCOM]			= CDR_UNITEL_RSLCOM;
		$arrConfig['Define']["RSLCOM"]	["FileType"][REGEX_UNITEL_SE]		= CDR_UNITEL_RSLCOM;

		// Unitel Landline and S&E Definition
		$arrConfig['Define']["Commander"]	["Name"]							= "Commander";
		$arrConfig['Define']["Commander"]	["Carrier"]							= CARRIER_UNITEL;
 		$arrConfig['Define']["Commander"]	["Type"]							= COLLECTION_TYPE_FTP;
 		$arrConfig['Define']["Commander"]	["Server"]							= $strTestServer;
 		$arrConfig['Define']["Commander"]	["Username"]						= $strTestUsername;
 		$arrConfig['Define']["Commander"]	["PWord"]							= $strTestPassword;
 		$arrConfig['Define']["Commander"]	["FinalDir"]						= DESTINATION_ROOT.'unitel/';
		$arrConfig['Define']["Commander"]	["FileType"][REGEX_COMMANDER]		= CDR_UNITEL_COMMANDER;
		

		// Optus Definition
		$arrConfig['Define']["Optus"]	["Name"]						= "Optus";
		$arrConfig['Define']["Optus"]	["Carrier"]						= CARRIER_OPTUS;
 		$arrConfig['Define']["Optus"]	["Type"]						= COLLECTION_TYPE_FTP;
 		$arrConfig['Define']["Optus"]	["Server"]						= $strTestServer;
 		$arrConfig['Define']["Optus"]	["Username"]					= $strTestUsername;
 		$arrConfig['Define']["Optus"]	["PWord"]						= $strTestPassword;
 		$arrConfig['Define']["Optus"]	["FinalDir"]					= DESTINATION_ROOT."optus/";
		$arrConfig['Define']["Optus"]	["FileType"][REGEX_OPTUS]		= CDR_OPTUS_STANDARD;
		
		// AAPT Definition
		$arrConfig['Define']["AAPT"]	['ZipPword']					= "zbj6v04ls";
		$arrConfig['Define']["AAPT"]	["Name"]						= "AAPT";
		$arrConfig['Define']["AAPT"]	["Carrier"]						= CARRIER_AAPT;
 		$arrConfig['Define']["AAPT"]	["Type"]						= COLLECTION_TYPE_FTP;
 		$arrConfig['Define']["AAPT"]	["Server"]						= $strTestServer;
 		$arrConfig['Define']["AAPT"]	["Username"]					= $strTestUsername;
 		$arrConfig['Define']["AAPT"]	["PWord"]						= $strTestPassword;
 		$arrConfig['Define']["AAPT"]	["FinalDir"]					= DESTINATION_ROOT."aapt/";
		$arrConfig['Define']["AAPT"]	["FileType"][REGEX_AAPT]		= CDR_AAPT_STANDARD;

	


//----------------------------------------------------------------------------//
// RUN Collection
//----------------------------------------------------------------------------//


echo "<pre>\n";

// Application entry point - create an instance of the application object
$appCollection = new ApplicationCollection($arrConfig);

// run the thing
$appCollection->Collect();
$appCollection->_rptCollectionReport->Finish("/home/vixen_log/collection_app/".date("Ymd_His").".log");

// finished
echo("\n-- End of Collection --\n");
echo "</pre>\n";
die();

?>
