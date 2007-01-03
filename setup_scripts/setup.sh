#!/bin/sh

# Backup a MySQL InnoDB Database
#

# Make Dirs
mkdir -m 700 /home/vixen_download
mkdir -m 700 /home/vixen_download/unzip

mkdir -m 700 /home/vixen_import
mkdir -m 700 /home/vixen_import/aapt
mkdir -m 700 /home/vixen_import/iseek
mkdir -m 700 /home/vixen_import/optus
mkdir -m 700 /home/vixen_import/unitel

mkdir -m 700 /home/vixen_upload
mkdir -m 700 /home/vixen_upload/unitel
mkdir -m 700 /home/vixen_upload/unitel/dailyorderfiles
mkdir -m 700 /home/vixen_upload/unitel/preselectionfiles
mkdir -m 700 /home/vixen_upload/aapt
mkdir -m 700 /home/vixen_upload/optus
mkdir -m 700 /home/vixen_upload/iseek

mkdir -m 700 /home/vixen_bill_output
mkdir -m 700 /home/vixen_bill_output/sample

# chown dirs
chown -R www-data.www-data /home/vixen_download
chown -R www-data.www-data /home/vixen_import
chown -R www-data.www-data /home/vixen_upload
chown -R www-data.www-data /home/vixen_bill_output
