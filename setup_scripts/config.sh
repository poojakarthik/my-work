#!/bin/sh

# -----------------------------------------------------------------------------#
# viXen Config Setup Script
# -----------------------------------------------------------------------------#
#
# run this script as root to add a default config file

# make dir
mkdir -m 755 /etc/vixen

# remove file if it exists
rm vixen.conf

# write conf file
FileVixenConf="<?php
//----------------------------------------------------------------------------//
// /etc/vixen/vixen.conf
//----------------------------------------------------------------------------//

// Data Access constants

$strDBServer = 'DPS';
//$strDBServer = 'CATWALK';
//$strDBServer = 'MINX';

// Defaults
$strDBUser        = 'vixen';
$strDBPassword    = 'V1x3n';
$strDBDatabase    = 'vixen';
switch ($strDBServer)
{
	case 'DPS':
		$strDBURL		= '10.11.12.13';
		break;

	case 'MINX':
		$strDBURL		= '10.11.12.16';
		break;
	
	case 'CATWALK':
		$strDBURL		= '10.11.12.14';
		$strDBPassword		= 'vixen';
		$strDBDatabase		= 'vixenworking';
		break;

	default:
		throw new Exception('Bad Database Connection Definition');
		die;
}


$GLOBALS['**arrDatabase']['URL']		= $strDBURL;
$GLOBALS['**arrDatabase']['User']		= $strDBUser;
$GLOBALS['**arrDatabase']['Password']	= $strDBPassword;
$GLOBALS['**arrDatabase']['Database']	= $strDBDatabase;
?>
"
echo "$FileVixenConf" > /etc/vixen/vixen.conf

# set permissions
chmod 644 /etc/vixen/vixen.conf

#done
echo "conf file written to /etc/vixen/vixen.conf"
