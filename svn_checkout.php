<?php

ob_start();
$strPath	= rtrim(trim($argv[1]), '/');
echo "\nExporting Flex to '$strPath'...\t\t\t";
ob_flush();

if(!file_exists($strPath))
{
	echo "[ FAILED ]\n\t- Directory does not exist\n\n";
	die;
}

// Remove existing Flex files
shell_exec("rm -Rf $strPath/lib");
shell_exec("rm -Rf $strPath/cli");
shell_exec("rm -Rf $strPath/html");
shell_exec("rm -Rf $strPath/flex.require.php");

// get latest version
shell_exec("svn export --non-interactive --force --no-auth-cache --username export --password export http://192.168.2.13/svn_vixen $strPath");

// set file permissions
shell_exec("chmod -R 0777 $strPath/*");

echo "[   OK   ]\n\n";
die;
?>