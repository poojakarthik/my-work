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
FileVixenConf="<?php\n//----------------------------------------------------------------------------//\n// /etc/vixen/vixen.conf\n//----------------------------------------------------------------------------//\n\n// Data Access constants\n\n\$strDBServer = 'DPS';\n//\$strDBServer = 'CATWALK';\n//\$strDBServer = 'MINX';\n\n// Defaults\n\$strDBUser        = 'vixen';\n\$strDBPassword    = 'V1x3n';\n\$strDBDatabase    = 'vixen';\nswitch ($strDBServer)\n{\n	case 'DPS':\n		\$strDBURL		= '10.11.12.13';\n		break;\n\n	case 'MINX':\n		\$strDBURL		= '10.11.12.16';\n		break;\n	\n	case 'CATWALK':\n		\$strDBURL		= '10.11.12.14';\n		\$strDBPassword		= 'vixen';\n		\$strDBDatabase		= 'vixenworking';\n		break;\n\n	default:\n		throw new Exception('Bad Database Connection Definition');\n		die;\n}\n\n\n\$GLOBALS['**arrDatabase']['URL']		= \$strDBURL;\n\$GLOBALS['**arrDatabase']['User']		= \$strDBUser;\n\$GLOBALS['**arrDatabase']['Password']	= \$strDBPassword;\n\$GLOBALS['**arrDatabase']['Database']	= \$strDBDatabase;\n?>\n"

echo "$FileVixenConf" > /etc/vixen/vixen.conf

# set permissions
chmod 644 /etc/vixen/vixen.conf

#done
echo "conf file written to /etc/vixen/vixen.conf"
