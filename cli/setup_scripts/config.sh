#!/bin/sh

# -----------------------------------------------------------------------------#
# viXen Config Setup Script
# -----------------------------------------------------------------------------#
#
# run this script as root to add a default config file

# make dir
mkdir -m 755 /etc/vixen/

# remove file if it exists
rm /etc/vixen/vixen.conf

# write conf file
cp vixen.conf /etc/vixen/vixen.conf

# set permissions
chmod 644 /etc/vixen/vixen.conf

#done
echo "conf file written to /etc/vixen/vixen.conf"
