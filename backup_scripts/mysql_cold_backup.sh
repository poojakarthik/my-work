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

# LOG_DIR
# 	Full path to MySQL LOG directory (do NOT include trailing slash)
LOG_DIR='/var/log/mysql'

# OLD_LOG_DIR
# 	Full path to MySQL OLD LOG directory (do NOT include trailing slash)
OLD_LOG_DIR='/var/log/mysql_old'

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

# Make backup dir for logs
mkdir -pm 700 $OLD_LOG_DIR/$BACKUP_NAME/

# Make Directory for this backup
mkdir -pm 700 $BACKUP_DIR/$BACKUP_NAME/$DATABASE_NAME/log/

# Shut down your MySQL server and make sure that it shuts down without errors.
/etc/init.d/mysql stop

# Copy all your data files (ibdata files and .ibd files) into a safe place.
cp -ip $INNODB_DIR/ibdata* $BACKUP_DIR/$BACKUP_NAME/

# Copy all your ib_logfile files to a safe place.
cp -ip $INNODB_DIR/ib_logfile* $BACKUP_DIR/$BACKUP_NAME/

# Copy your my.cnf configuration file or files to a safe place.
cp -ip /etc/mysql/my.cnf $BACKUP_DIR/$BACKUP_NAME/

# Copy all the .frm & .idb files for your InnoDB tables to a safe place.
cp -Rip $MYSQL_DIR/$DATABASE_NAME/* $BACKUP_DIR/$BACKUP_NAME/$DATABASE_NAME/

# Copy the MySQL database
cp -Rip $MYSQL_DIR/mysql $BACKUP_DIR/$BACKUP_NAME/

# Restart the MySQL server
/etc/init.d/mysql start

# Send a message
#TODO!!!!
