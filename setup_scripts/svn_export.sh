#!/bin/sh

# -----------------------------------------------------------------------------#
# viXen Export Script
# -----------------------------------------------------------------------------#
# fetches the latest version of viXen via SVN


# -----------------------------------------------------------------------------#
# USAGE
# -----------------------------------------------------------------------------#

# svn_export

# -----------------------------------------------------------------------------#
# SCRIPT
# -----------------------------------------------------------------------------#

# remove existing vixen files
rm -Rf /usr/share/vixen/*

# get latest version
svn export --non-interactive --force --no-auth-cache --username export --password export http://10.11.12.13/svn_vixen /usr/share/vixen
