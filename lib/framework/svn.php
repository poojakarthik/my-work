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
 * @author		Andrew White
 * @version		6.10
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

$svnTransaction = new SvnLookup("/home/vixen/subversion/", "2049");
$strAuthor = $svnTransaction->Author();
$strLogMessage = $svnTransaction->LogMessage();

echo $strAuthor;
echo $strLogMessage;

// echo shell_exec("svnlook author /home/vixen/subversion/ -r 2049");


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
 * @prefix	svn
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
	 * __Construct($strPath, $intRevision)
	 *
	 * Store the information passed from the subversion hook
	 *
	 * Store the repository path and revision number of the subversion commit
	 *
	 * @param	string	$strPath		Full path to SVN repository
	 * @param	int		$intRevision	Revision number of commit
	 * @return	void
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	function __Construct ($strPath, $intRevision)
	{
		$this->_strPath = $strPath;
		$this->_intRevisionNum = $intRevision;
	}
	
	
	//------------------------------------------------------------------------//
	// Author
	//------------------------------------------------------------------------//
	/**
	 * Author()
	 *
	 * Find the author of the subversion commit
	 *
	 * Find the author of the subversion commit
	 *
	 * @return	string
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	function Author()
	{
		return shell_exec("svnlook author {$this->_strPath} -r {$this->_intRevisionNum}");
	}
	
	//------------------------------------------------------------------------//
	// LogMessage
	//------------------------------------------------------------------------//
	/**
	 * LogMessage()
	 *
	 * Find the log message of the subversion commit
	 *
	 * Find the log message of the subversion commit
	 *
	 * @return	string
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	function LogMessage()
	{
		return shell_exec("svnlook log {$this->_strPath} -r {$this->_intRevisionNum}");
	
	
	}
	

	
}




?>
