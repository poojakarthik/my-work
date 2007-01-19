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

mkdir -m 770 /home/vixen_bill_output
mkdir -m 770 /home/vixen_bill_output/sample
mkdir -m 770 /home/vixen_bill_output/sample/pdf

mkdir -m 700 /home/vixen_invoices

mkdir -m 700 /home/vixen_log
mkdir -m 700 /home/vixen_log/billing_app
mkdir -m 700 /home/vixen_log/charges_app
mkdir -m 700 /home/vixen_log/collection_app
mkdir -m 700 /home/vixen_log/master
mkdir -m 700 /home/vixen_log/mistress
mkdir -m 700 /home/vixen_log/normalisation_app
mkdir -m 700 /home/vixen_log/payment_app
mkdir -m 700 /home/vixen_log/provisioning_app
mkdir -m 700 /home/vixen_log/rating_app

# chown dirs
chown -R www-data.www-data /home/vixen_download
chown -R www-data.www-data /home/vixen_import
chown -R www-data.www-data /home/vixen_upload
chown -R www-data.mysql /home/vixen_bill_output
chown -R www-data.www-data /home/vixen_invoices
chown -R www-data.www-data /home/vixen_log
