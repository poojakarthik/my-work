<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// dev tools
//----------------------------------------------------------------------------//
/**
 * dev tools
 *
 * Developer Tools
 *
 * This file contains developer tool functions
 *
 * @file		dev_tools.php
 * @language	PHP
 * @package		framework
 * @author		Jared 'flame' Herbohn
 * @version		7.01
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 
//----------------------------------------------------------------------------//
// FUNCTIONS
//----------------------------------------------------------------------------//

	//------------------------------------------------------------------------//
	// GetTODOList
	//------------------------------------------------------------------------//
	/**
	 * GetTODOList()
	 *
	 * Gets a complete list of TODOs from all project files
	 *
	 * Gets a complete list of TODOs from all project files
	 * 
	 * @param	string	strStartDir
	 * @return	array
	 * 
	 * @function
	 */
	 function GetTODOList($strStartDir='')
	 {
	 	// trim start dir
	 	$strStartDir = trim($strStartDir);
		
	 	// add trailing slash to dir name
		if ($strStartDir)
		{
			$strStartDir .= '/';
		}
		
		// get path length
		$intPathLength = strlen($strStartDir);
		
	 	// setup todo input array
		$arrRawTODO = Array();
		
		// setup todo output array
		$arrOutputTODO = Array();
		
		// setup grep command
		$strCommand = "grep -R TODO {$strStartDir}*";
		
		// run grep command
		$strResult = exec($strCommand , $arrRawTODO);
		
		// look at the results
		foreach($arrRawTODO AS $strLine)
		{
			// split result line
			$arrLine = explode(':',$strLine, 2);
			
			$strFilePath 		= substr(trim($arrLine[0]), $intPathLength);
			$strTODO 			= trim($arrLine[1]);
			$strFile			= basename($strFilePath);
			$arrFile			= explode('.', $strFile);
			$strFileType		= '';
			
			//echo "$strLine\n";
			
			if (is_array($arrFile))
			{
				$strFileType	= strtolower(array_pop($arrFile));
			}
			else
			{
				continue;
			}
			
			// skip .svn garbage
			if (strpos($strFilePath, '.svn/') !== FALSE)
			{
				continue;
			}
			
			// only look at 'TODO' lines
			if (strpos($strTODO, 'TODO'.'!') === FALSE)
			{
				continue;
			}
			
			// remove TODO from line
			$arrTODO = explode('TODO'.'!', $strTODO, 2);
			if (!is_array($arrTODO))
			{
				continue;
			}
			
			// do we have exta !
			if (substr($arrTODO[1], 0, 1) == '!')
			{
				$strTODO = ltrim($arrTODO[1],'!');
				$strName = 'TODO';
			}
			else
			{
				$arrTODO = explode('!', $arrTODO[1], 2);
				$strName = trim($arrTODO[0]);
				$strTODO = $arrTODO[1];
			}
			
			// no name = no go
			if (!$strName)
			{
				continue;
			}
			
			// check if we want to look at this file type
			switch($strFileType)
			{
				case 'php':
				case 'js':
				case 'xsl':
					// proccess this line
					$arrOutputTODO[$strFilePath][$strName][] = $strTODO;
					
					break;
					
				default:
					// skip this file
			}
			
		}
		
		return $arrOutputTODO;
	 	
		
	 }

?>
