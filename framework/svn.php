<?php

//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// svn.php
//----------------------------------------------------------------------------//
/**
 * svn.php
 *
 * Script run by Subversion post-commit hook for bugtracking
 *
 * Script run by Subversion post-commit hook to close bugs on the FlySpray system
 * and post comments detailing their closing 
 *
 * @file		svn.php
 * @language	PHP
 * @package		<package_name>
 * @author		<Author Name>
 * @version		6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */





echo shell_exec("svnlook author /home/vixen/subversion/ -r 2049");


die();
//----------------------------------------------------------------------------//
// SvnLookup
//----------------------------------------------------------------------------//
/**
 * SvnLookup
 *
 * Find the subversion commit transaction information
 *
 * Use the repository path and transaction number passed to it to find the author
 * and log message information of the subversion commit
 *
 * @prefix	<prefix>
 *
 * @package	<package_name>
 * @parent	<full.parent.path>
 * @class	<ClassName||InstanceName>
 * @extends	<ClassName>
 */


class SvnLookup
	
{	
	//------------------------------------------------------------------------//
	// __Construct
	//------------------------------------------------------------------------//
	/**
	 * __Construct($strPath, $strRevision)
	 *
	 * Store the information passed from the subversion hook
	 *
	 * Store the repository path and revision number of the subversion commit
	 *
	 * @param	<type>	<$name>	[optional] <description>
	 * @return	<type>
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	
	
	function __Construct ($strPath, $strRevision)
	{
	$this->_strPath = $strPath;
	$this->_strRevisionNum = $strRevision;
	}
	
	
	//------------------------------------------------------------------------//
	// Authors
	//------------------------------------------------------------------------//
	/**
	 * Authors()
	 *
	 * Find the author of the subversion commit
	 *
	 * Find the author of the subversion commit
	 *
	 * @param	<type>	<$name>	[optional] <description>
	 * @return	<type>
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	
	function Authors()
	{
		return shell_exec("svnlook author {$this->_strPath} -r {$this->_strRevisionNum}");
	}
	
	//------------------------------------------------------------------------//
	// LogMessage
	//------------------------------------------------------------------------//
	/**
	 * Authors()
	 *
	 * Find the log message of the subversion commit
	 *
	 * Find the log message of the subversion commit
	 *
	 * @param	<type>	<$name>	[optional] <description>
	 * @return	<type>
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	
	function LogMessage()
	{
		return shell_exec("svnlook log {$this->_strPath} -r {$this->_strRevisionNum}");
	
	
	}
	

	
}

$svnTransaction = new SvnLookup;
$strAuthor = $svnTransaction->Author();
$strLogMessage = $svnTransaction->LogMessage();



?>
