#!/bin/sh

# -----------------------------------------------------------------------------#
# viXen Config Setup Script
# -----------------------------------------------------------------------------#
#
# run this script as root to add a default config file

# make dir
mkdir -m 755 /etc/vixen

# write conf file
FileVixenConf="<?php
//----------------------------------------------------------------------------//
// /etc/vixen/vixen.conf
//----------------------------------------------------------------------------//

// Data Access constants
define('DATABASE_URL', '10.11.12.13');
define('DATABASE_NAME', "vixen");
define('DATABASE_USER', "vixen");
define('DATABASE_PWORD', "V1x3n");

?>"
echo "$FileVixenConf" > /etc/vixen/vixen.conf

# set permissions
chmod 644 /etc/vixen/vixen.conf

#done
echo "conf file written to /etc/vixen/vixen.conf"
