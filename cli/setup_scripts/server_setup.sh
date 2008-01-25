#!/bin/sh

# -----------------------------------------------------------------------------#
# viXen Server Setup Script
# -----------------------------------------------------------------------------#

# -----------------------------------------------------------------------------#
# USAGE
# -----------------------------------------------------------------------------#

# server_setup <hostname> <ip_suffix>

# -----------------------------------------------------------------------------#
# CONFIG
# -----------------------------------------------------------------------------#

# Server Host Name
StrHostName="$1"

# IP Suffix
IntSuffix="$2"

# Test Input
if [ $# != 2 ] || [ $[ 0 + $IntSuffix] -lt 8 ] || [ $[ 0 + $IntSuffix] -gt 24 ] || [ $StrHostName = "" ]
then
	echo "USAGE server_setup <hostname> <ip_suffix>"
	exit
fi

IPeth0Prefix="10.11.12"
IPeth1Prefix="10.10.10"
IPeth2Prefix="192.168.2"

# Internal IP Address
IPeth0="IPeth0Prefix.$IntSuffix"

# Backup IP Address
IPeth1="IPeth1Prefix.$IntSuffix"

# External IP Address
IPeth2="$IPeth2Prefix.$IntSuffix"

# -----------------------------------------------------------------------------#
# APT Config
# -----------------------------------------------------------------------------#

echo "Configuring apt ..."

FileSources="
#

## Software from the Install CD
deb cdrom:[Ubuntu-Server 6.06.1 _Dapper Drake_ - Release amd64 (20060807.1)]/ dapper main restricted

## Standard Software
deb http://au.archive.ubuntu.com/ubuntu/ dapper main restricted
deb-src http://au.archive.ubuntu.com/ubuntu/ dapper main restricted

## Major bug fix updates
deb http://au.archive.ubuntu.com/ubuntu/ dapper-updates main restricted
deb-src http://au.archive.ubuntu.com/ubuntu/ dapper-updates main restricted

## Software from the 'universe' & ' multiverse' repositorys
deb http://au.archive.ubuntu.com/ubuntu/ dapper universe multiverse
deb-src http://au.archive.ubuntu.com/ubuntu/ dapper universe

## Software from the 'backports' repository
deb http://au.archive.ubuntu.com/ubuntu/ dapper-backports main restricted universe multiverse
deb-src http://au.archive.ubuntu.com/ubuntu/ dapper-backports main restricted universe multiverse

## Security Updates
deb http://security.ubuntu.com/ubuntu dapper-security main restricted
deb-src http://security.ubuntu.com/ubuntu dapper-security main restricted
deb http://security.ubuntu.com/ubuntu dapper-security universe
deb-src http://security.ubuntu.com/ubuntu dapper-security universe

		"
echo "$FileSources" > /etc/apt/sources.list

# update package lists
apt-get update

# -----------------------------------------------------------------------------#
# HOST NAME
# -----------------------------------------------------------------------------#

echo "Configuring hostname ..."

echo $StrHostName > /etc/hostname
hostname $StrHostName


# -----------------------------------------------------------------------------#
# HOSTS
# -----------------------------------------------------------------------------#

echo "Configuring hosts ..."

FileHosts="
# localhost
127.0.0.1       localhost
127.0.1.1       $StrHostName

# servers
10.11.12.13     dollarpeepshow peepshow peep thepeepshow dps tps
10.11.12.14     catwalk
10.11.12.15     spank
10.11.12.16     minx

# workstations
10.11.12.212    bash cyrene
10.11.12.214    rich waste ratsarse zemu
10.11.12.213    flame

# The following lines are desirable for IPv6 capable hosts
::1     ip6-localhost ip6-loopback
fe00::0 ip6-localnet
ff00::0 ip6-mcastprefix
ff02::1 ip6-allnodes
ff02::2 ip6-allrouters
ff02::3 ip6-allhosts
		"
echo "$FileHosts" > /etc/hosts

# -----------------------------------------------------------------------------#
# /etc/iftab
# -----------------------------------------------------------------------------#

echo "Configuring network ..."

FileIftab="

# View from back of server
# ----------------------------------------------------------
# | ------                                                 |
# | |eth2|                                                 |
# | ------                                                 |
# |                     ------  ------                     |
# |                     |eth1|  |eth0|                     |
# |                     ------  ------                     |
# |                                                        |
# ----------------------------------------------------------

#eth0 (internal : 1,000mbps)
eth0 businfo 0000:02:09.0

#eth1 (backup   : 1,000mbps)
eth1 businfo 0000:02:09.1

#eth2 (external :   100mbps)
#eth2 businfo 0000:03:08.0

			"
echo "$FileIftab" > /etc/iftab


# -----------------------------------------------------------------------------#
# /etc/network/interfaces
# -----------------------------------------------------------------------------#

FileInterfaces="

# The loopback network interface
auto lo
iface lo inet loopback


# View from back of server
# ----------------------------------------------------------
# | ------                                                 |
# | |eth2|                                                 |
# | ------                                                 |
# |                     ------  ------                     |
# |                     |eth1|  |eth0|                     |
# |                     ------  ------                     |
# |                                                        |
# ----------------------------------------------------------

#eth0 (internal : 1,000mbps)
auto eth0
iface eth0 inet static
address $IPeth0
netmask 255.255.255.0
broadcast 10.11.12.255

#eth1 (backup   : 1,000mbps)
auto eth1
iface eth1 inet static
address $IPeth1
netmask 255.255.255.0
broadcast 10.10.10.255

#eth2 (external :   100mbps)
auto eth2
iface eth2 inet static
address $IPeth2
netmask 255.255.255.0
broadcast 192.168.2.255
gateway 192.168.2.253
			"
echo "$FileInterfaces" > /etc/network/interfaces

# -----------------------------------------------------------------------------#
# FOLDERS
# -----------------------------------------------------------------------------#

echo "Configuring Folders ..."

# viXen folder
mkdir -pm 755 /usr/share/vixen

# -----------------------------------------------------------------------------#
# INSTALL PACKAGES
# -----------------------------------------------------------------------------#

echo "Installing Packages ..."

# ssh server
apt-get install openssh-server

# postfix
apt-get install postfix

# mysql
apt-get install mysql-server mysql-client

# php
apt-get install php5 php5-cli php5-sqlite php5-mysql php5-gd php5-xsl php5-mysqli php5-curl

# xslt
apt-get install libxslt1.1

# svn
apt-get install subversion subversion-tools

# zip
apt-get install zip unzip

#re-start apache just incase
/etc/init.d/apache2 stop
/etc/init.d/apache2 start

# -----------------------------------------------------------------------------#
# PHP.INI : Apache
# -----------------------------------------------------------------------------#

echo "Configuring php ..."

FilePhpIniApache="
[PHP]

;;;;;;;;;;;;;;;;;;;;
; Language Options ;
;;;;;;;;;;;;;;;;;;;;

engine = On
zend.ze1_compatibility_mode = Off
short_open_tag = On
asp_tags = Off
precision    =  12
y2k_compliance = On
output_buffering = Off
zlib.output_compression = Off
implicit_flush = Off
unserialize_callback_func=
serialize_precision = 100
allow_call_time_pass_reference = On
safe_mode = Off
safe_mode_gid = Off
safe_mode_include_dir =
safe_mode_exec_dir =
safe_mode_allowed_env_vars = PHP_
safe_mode_protected_env_vars = LD_LIBRARY_PATH
disable_functions =
disable_classes =
expose_php = On


;;;;;;;;;;;;;;;;;;;
; Resource Limits ;
;;;;;;;;;;;;;;;;;;;

max_execution_time = 300     ; Maximum execution time of each script, in seconds
max_input_time = 300 ; Maximum amount of time each script may spend parsing request data
memory_limit = 64M      ; Maximum amount of memory a script may consume (64MB)


;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
; Error handling and logging ;
;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;

error_reporting  =  E_ALL & ~E_NOTICE
display_errors = Off
display_startup_errors = Off
log_errors = Off
log_errors_max_len = 1024
ignore_repeated_errors = Off
ignore_repeated_source = Off
report_memleaks = On
track_errors = Off
; Log errors to specified file.
;error_log = filename


;;;;;;;;;;;;;;;;;
; Data Handling ;
;;;;;;;;;;;;;;;;;

variables_order = \"EGPCS\"
register_globals = Off
register_long_arrays = On
register_argc_argv = On
auto_globals_jit = On
post_max_size = 8M
magic_quotes_gpc = Off
magic_quotes_runtime = Off
magic_quotes_sybase = Off
auto_prepend_file =
auto_append_file =
default_mimetype = \"text/html\"


;;;;;;;;;;;;;;;;;;;;;;;;;
; Paths and Directories ;
;;;;;;;;;;;;;;;;;;;;;;;;;

doc_root =
user_dir =
enable_dl = On


;;;;;;;;;;;;;;;;
; File Uploads ;
;;;;;;;;;;;;;;;;

file_uploads = On
upload_max_filesize = 2M


;;;;;;;;;;;;;;;;;;
; Fopen wrappers ;
;;;;;;;;;;;;;;;;;;

allow_url_fopen = On
default_socket_timeout = 60


;;;;;;;;;;;;;;;;;;;;;;
; Dynamic Extensions ;
;;;;;;;;;;;;;;;;;;;;;;
extension=sqlite.so
extension=mysqli.so
extension=mysql.so
extension=xsl.so
extension=curl.so

;;;;;;;;;;;;;;;;;;;
; Module Settings ;
;;;;;;;;;;;;;;;;;;;

[Syslog]
define_syslog_variables  = Off

[mail function]
;sendmail_path =

[SQL]
sql.safe_mode = Off

[MySQL]
mysql.allow_persistent = On
mysql.max_persistent = -1
mysql.max_links = -1
mysql.default_port =
mysql.default_socket =
mysql.default_host =
mysql.default_user =
mysql.default_password =
mysql.connect_timeout = 60
mysql.trace_mode = Off

[MySQLi]
mysqli.max_links = -1
mysqli.default_port = 3306
mysqli.default_socket =
mysqli.default_host =
mysqli.default_user =
mysqli.default_pw =
mysqli.reconnect = Off

[bcmath]
bcmath.scale = 0

[Session]
session.save_handler = files
session.use_cookies = 1
session.name = PHPSESSID
session.auto_start = 0
session.cookie_lifetime = 0
session.cookie_path = /
session.cookie_domain =
session.serialize_handler = php
session.gc_divisor     = 100
session.gc_maxlifetime = 1440
session.bug_compat_42 = 1
session.bug_compat_warn = 1
session.referer_check =
session.entropy_length = 0
session.entropy_file =
session.cache_limiter = nocache
session.cache_expire = 180
session.use_trans_sid = 0
session.hash_function = 0
session.hash_bits_per_character = 4

url_rewriter.tags = \"a=href,area=href,frame=src,input=src,form=,fieldset=\"


[Tidy]
tidy.clean_output = Off

[soap]
soap.wsdl_cache_enabled=1
soap.wsdl_cache_dir=\"/tmp\"
soap.wsdl_cache_ttl=86400
			"
echo "$FilePhpIniApache" > /etc/php5/apache2/php.ini


# -----------------------------------------------------------------------------#
# PHP.INI : CLI
# -----------------------------------------------------------------------------#

FilePhpIniCLI="
[PHP]

engine = On
short_open_tag = On
asp_tags = Off
precision    =  12
y2k_compliance = On
output_buffering = Off
zlib.output_compression = Off
implicit_flush = Off
unserialize_callback_func=
serialize_precision = 100
allow_call_time_pass_reference = On
safe_mode = Off
safe_mode_gid = Off
safe_mode_include_dir =
safe_mode_exec_dir =
safe_mode_allowed_env_vars = PHP_
safe_mode_protected_env_vars = LD_LIBRARY_PATH
disable_functions =
disable_classes =
expose_php = On


;;;;;;;;;;;;;;;;;;;
; Resource Limits ;
;;;;;;;;;;;;;;;;;;;

max_execution_time = 300     ; Maximum execution time of each script, in seconds
max_input_time = 300 ; Maximum amount of time each script may spend parsing request data
memory_limit = 1024M      ; Maximum amount of memory a script may consume (8MB)


;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
; Error handling and logging ;
;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;

error_reporting  =  E_ALL & ~E_NOTICE
display_errors = On
display_startup_errors = Off
log_errors = Off
log_errors_max_len = 1024
ignore_repeated_errors = Off
ignore_repeated_source = Off
report_memleaks = On
track_errors = Off
; Log errors to specified file.
;error_log = filename


;;;;;;;;;;;;;;;;;
; Data Handling ;
;;;;;;;;;;;;;;;;;

variables_order = \"EGPCS\"
register_globals = Off
register_long_arrays = On
register_argc_argv = On
auto_globals_jit = On
post_max_size = 8M
magic_quotes_gpc = Off
magic_quotes_runtime = Off
magic_quotes_sybase = Off
auto_prepend_file =
auto_append_file =
default_mimetype = \"text/html\"


;;;;;;;;;;;;;;;;;;;;;;;;;
; Paths and Directories ;
;;;;;;;;;;;;;;;;;;;;;;;;;

doc_root =
user_dir =
enable_dl = On


;;;;;;;;;;;;;;;;
; File Uploads ;
;;;;;;;;;;;;;;;;

file_uploads = On
;upload_tmp_dir =
upload_max_filesize = 2M


;;;;;;;;;;;;;;;;;;
; Fopen wrappers ;
;;;;;;;;;;;;;;;;;;

allow_url_fopen = On
; Define the anonymous ftp password (your email address)
;from=\"john@doe.com\"
; Define the User-Agent string
; user_agent=\"PHP\"
default_socket_timeout = 60


;;;;;;;;;;;;;;;;;;;;;;
; Dynamic Extensions ;
;;;;;;;;;;;;;;;;;;;;;;

extension=sqlite.so
extension=mysqli.so
extension=mysql.so
extension=xsl.so
extension=curl.so

;;;;;;;;;;;;;;;;;;;
; Module Settings ;
;;;;;;;;;;;;;;;;;;;

[Syslog]
define_syslog_variables  = Off

[mail function]
;sendmail_path =

[SQL]
sql.safe_mode = Off

[MySQL]
mysql.allow_persistent = On
mysql.max_persistent = -1
mysql.max_links = -1
mysql.default_port =
mysql.default_socket =
mysql.default_host =
mysql.default_user =
mysql.default_password =
mysql.connect_timeout = 60
mysql.trace_mode = Off

[MySQLi]
mysqli.max_links = -1
mysqli.default_port = 3306
mysqli.default_socket =
mysqli.default_host =
mysqli.default_user =
mysqli.default_pw =
mysqli.reconnect = Off

[bcmath]
bcmath.scale = 0

[Session]
session.save_handler = files
session.use_cookies = 1
session.name = PHPSESSID
session.auto_start = 0
session.cookie_lifetime = 0
session.cookie_path = /
session.cookie_domain =
session.serialize_handler = php
session.gc_divisor     = 100
session.gc_maxlifetime = 1440
session.bug_compat_42 = 1
session.bug_compat_warn = 1
session.referer_check =
session.entropy_length = 0
session.entropy_file =
session.cache_limiter = nocache
session.cache_expire = 180
session.use_trans_sid = 0
session.hash_function = 0
session.hash_bits_per_character = 4

url_rewriter.tags = \"a=href,area=href,frame=src,input=src,form=,fieldset=\"

[Tidy]
tidy.clean_output = Off

[soap]
soap.wsdl_cache_enabled=1
soap.wsdl_cache_dir=\"/tmp\"
soap.wsdl_cache_ttl=86400
			"
echo "$FilePhpIniCLI" > /etc/php5/cli/php.ini


# -----------------------------------------------------------------------------#
# MY.CNF
# -----------------------------------------------------------------------------#

echo "Configuring MySQL ..."

FileMyCnf="

[client]
port            = 3306
socket          = /var/run/mysqld/mysqld.sock

[mysqld_safe]
socket          = /var/run/mysqld/mysqld.sock
nice            = 0

[mysqld]
user            = mysql
pid-file        = /var/run/mysqld/mysqld.pid
socket          = /var/run/mysqld/mysqld.sock
port            = 3306
basedir         = /usr
datadir         = /var/lib/mysql
tmpdir          = /tmp
language        = /usr/share/mysql/english
skip-external-locking
old_passwords   = 1

bind-address            = 127.0.0.1
bind-address            = $IPeth0
bind-address            = $IPeth1

key_buffer              = 16M
max_allowed_packet      = 16M
thread_stack            = 128K

query_cache_limit       = 1048576
query_cache_size        = 16777216
query_cache_type        = 1

#server-id              = 1
log-bin                 = /var/log/mysql/mysql-bin.log
expire-logs-days        = 20
max_binlog_size         = 104857600
#binlog-do-db           = include_database_name
#binlog-ignore-db       = include_database_name

skip-bdb

# 4GB RAM
#tmp_table_size         = 536870912

#innodb_buffer_pool_size			= 2147483648
#innodb_additional_mem_pool_size	= 16777216
#innodb_log_buffer_size			= 67108864
#innodb_log_file_size			= 1073741824

# 2GB RAM
tmp_table_size         			= 268435456

innodb_buffer_pool_size			= 1073741824
innodb_additional_mem_pool_size	= 16777216
innodb_log_buffer_size			= 67108864
innodb_log_file_size			= 536870912

innodb_file_per_table

[mysqldump]
quick
quote-names
max_allowed_packet      = 16M

[mysql]

[isamchk]
key_buffer              = 16M
			"
echo "$FileMyCnf" > /etc/my.cnf


# -----------------------------------------------------------------------------#
# VIXEN FILES
# -----------------------------------------------------------------------------#

echo "Installing Vixen ..."

svn export --non-interactive --force --no-auth-cache --username export --password export http://10.11.12.13/svn_vixen /usr/share/vixen

# -----------------------------------------------------------------------------#
# APACHE CONFIG
# -----------------------------------------------------------------------------#

echo "Configuring Apache ..."

# Intranet
FileVixenIntranet="
<VirtualHost $IPeth2>
        ServerAdmin webmaster@localhost
        ServerName viXen
        DocumentRoot /usr/share/vixen/intranet_app
        <Directory />
                Options FollowSymLinks
                AllowOverride None
        </Directory>
        <Directory /usr/share/vixen/intranet_app/>
                Options Indexes FollowSymLinks MultiViews
                AllowOverride None
                Order allow,deny
                allow from all
        </Directory>

        ErrorLog /var/log/apache2/error.log

        # Possible values include: debug, info, notice, warn, error, crit,
        # alert, emerg.
        LogLevel warn

        CustomLog /var/log/apache2/access.log combined
        ServerSignature On
</VirtualHost>
			"
echo "$FileVixenIntranet" > /etc/apache2/sites-available/vixen-intranet

# Website
FileVixenWebsite="
<VirtualHost $IPeth2>
        ServerAdmin webmaster@localhost
        ServerName viXen
        DocumentRoot /usr/share/vixen/client_app
        <Directory />
                Options FollowSymLinks
                AllowOverride None
        </Directory>
        <Directory /usr/share/vixen/client_app/>
                Options Indexes FollowSymLinks MultiViews
                AllowOverride None
                Order allow,deny
                allow from all
        </Directory>

        ErrorLog /var/log/apache2/error.log

        # Possible values include: debug, info, notice, warn, error, crit,
        # alert, emerg.
        LogLevel warn

        CustomLog /var/log/apache2/access.log combined
        ServerSignature On
</VirtualHost>
			"
echo "$FileVixenWebsite" > /etc/apache2/sites-available/vixen-website

# Dev
FileVixenDev="
<VirtualHost $IPeth0>
        ServerAdmin webmaster@localhost

        DocumentRoot /var/www
        <Directory />
                Options FollowSymLinks
                AllowOverride None
        </Directory>
        <Directory /var/www/>
                Options Indexes FollowSymLinks MultiViews
                AllowOverride None
                Order deny,allow
                Deny from all
                Allow from $IPeth0Prefix
        </Directory>

        ErrorLog /var/log/apache2/error.log
        LogLevel warn
        CustomLog /var/log/apache2/access.log combined
        ServerSignature On
			"
echo "$FileVixenDev" > /etc/apache2/sites-available/vixen-dev

# Default (Dead End)
FileVixenDefault="
NameVirtualHost *
<VirtualHost *>
        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/default
        <Directory />
                Options FollowSymLinks
                AllowOverride None
				Order deny,allow
                Deny from all
        </Directory>
        <Directory /var/www/default/>
                Options FollowSymLinks
                AllowOverride None
                Order deny,allow
                Deny from all
        </Directory>
        ErrorLog /var/log/apache2/error.log
        LogLevel warn
        CustomLog /var/log/apache2/access.log combined
        ServerSignature On
</VirtualHost>
			"
echo "$FileVixenDefault" > /etc/apache2/sites-available/vixen-default

# enable the sites
ln -s /etc/apache2/sites-available/vixen-intranet /etc/apache2/sites-enabled/vixen-intranet 
#ln -s /etc/apache2/sites-available/vixen-website /etc/apache2/sites-enabled/vixen-website 
ln -s /etc/apache2/sites-available/vixen-dev /etc/apache2/sites-enabled/vixen-dev 

# enable default site (Dead End)
mkdir -pm 755 /var/www/default
echo "" > /var/www/index.html
rm /etc/apache2/sites-enabled/000-default
ln -s /etc/apache2/sites-available/000-default /etc/apache2/sites-enabled/vixen-default



# -----------------------------------------------------------------------------#
# VIXEN CONFIG
# -----------------------------------------------------------------------------#

echo "Configuring Vixen ..."

# copy default server index page(s) into place
cp /usr/share/vixen/www/* /var/www/

# run setup script
#cd /usr/share/vixen/setup_scripts
#chmod 755 /usr/share/vixen/setup_scripts/server_setup.php
#/usr/share/vixen/setup_scripts/server_setup.php


# -----------------------------------------------------------------------------#
# DONE
# -----------------------------------------------------------------------------#

echo "Server Setup is Complete"
