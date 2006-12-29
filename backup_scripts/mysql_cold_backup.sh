#!/bin/sh

# Backup a MySQL InnoDB Database
#
# built from instructions found at : http://mysql.org/doc/refman/5.0/en/innodb-backup.html

# -----------------------------------------------------------------------------#
# CONFIG
# -----------------------------------------------------------------------------#

# BACKUP_DIR
# 	Full path to backup directory (do NOT include trailing slash)
BACKUP_DIR='/home/backup'

# MYSQL_DIR
# 	Full path to MySQL directory (do NOT include trailing slash)
MYSQL_DIR='/var/lib/mysql'

# INNODB_DIR
# 	Full path to InnoDB directory (do NOT include trailing slash)
INNODB_DIR='/var/lib/mysql'

# DATABASE_NAME
# 	Full path to InnoDB directory (do NOT include trailing slash)
DATABASE_NAME='vixen'

# -----------------------------------------------------------------------------#
# SCRIPT
# -----------------------------------------------------------------------------#

# Set Backup Name
BACKUP_NAME=`date +%Y-%m-%d_%H\:%M\:%S`

# Make Directory for this backup
#echo $BACKUP_DIR/$BACKUP_NAME/$DATABASE_NAME/
mkdir -pm 700 $BACKUP_DIR/$BACKUP_NAME/$DATABASE_NAME/

# Shut down your MySQL server and make sure that it shuts down without errors.
/etc/init.d/mysql stop

# Copy all your data files (ibdata files and .ibd files) into a safe place.
cp -ip $INNODB_DIR/*.idb $BACKUP_DIR/$BACKUP_NAME/
cp -ip $INNODB_DIR/ibdata* $BACKUP_DIR/$BACKUP_NAME/

# Copy all your ib_logfile files to a safe place.
cp -ip $INNODB_DIR/ib_logfile* $BACKUP_DIR/$BACKUP_NAME/

# Copy your my.cnf configuration file or files to a safe place.
#TODO!!!!

# Copy all the .frm files for your InnoDB tables to a safe place.
cp -Rip $MYSQL_DIR/mysql/$DATABASE_NAME/*.frm $BACKUP_DIR/$BACKUP_NAME/$DATABASE_NAME/

# Copy the MySQL database
cp -Rip $MYSQL_DIR/mysql $BACKUP_DIR/$BACKUP_NAME/

# Restart the MySQL server
/etc/init.d/mysql start

# Send a message
#TODO!!!!
