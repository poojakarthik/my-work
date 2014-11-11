#!/bin/bash
# gitRepositoryURL="https://github.com/SBTelecom/flex.git";
gitRepositoryURL="git://192.168.33.1/YBS/flex";

deploymentPath="$1";
deploymentBasename=`basename "$deploymentPath"`

log() { echo "$@" >&2; }
wouldrun() { echo "run:" "$@" >&2; }

if [ "${#deploymentPath}" -eq 0 ]; then
	log "You must supply a deployment path"
	exit 1;
fi

log "Deploying Flex backend to: '$deploymentPath'…"

# Dependencies
installGit () {
	log 'Installing `git` from ppa:git-core/ppa';
	sudo add-apt-repository --yes ppa:git-core/ppa 2> /dev/null &&
		sudo apt-get --quiet --quiet --yes update &&
		sudo apt-get --quiet --quiet --yes install git;
}

# log 'Installing deployment dependencies…';
# sudo apt-get --quiet --quiet update &&
# sudo apt-get --quiet --quiet install software-properties-common wget ca-certificates &&
# installGit;

# if [ ! $? -eq 0 ]; then
# 	exit 1;
# fi

# Code deployment
log -n 'Deploying code';
if [ -d "$deploymentPath" ]; then
	# Pull
	log ' to existing clone in' "'$deploymentPath'…";
	pushd "$deploymentPath" > /dev/null &&
	sudo git pull origin master;
	if [ ! $? -eq 0 ]; then
		exit 1;
	fi
	popd > /dev/null;
else
	# Clone
	log ' to new clone to' "'$deploymentPath'… (from $gitRepositoryURL)";

	read -p "Would you like to create a new deployment at '$deploymentPath'? [y/N]: " shouldClone;
	if [ ! "$shouldClone" = 'y' ]; then
		exit 1;
	fi

	sudo mkdir -p $(dirname "$deploymentPath") &&
	wouldrun git clone --origin=origin --branch=master --single-branch "$gitRepositoryURL" "$deploymentPath" &&
	sudo git clone --origin=origin --branch=master --single-branch "$gitRepositoryURL" "$deploymentPath";
	if [ ! $? -eq 0 ]; then
		exit 1;
	fi
fi

# Runtime Dependencies
log 'Installing runtime dependencies…';
sudo bash "$deploymentPath/util/deploy/dependencies/php" &&
sudo bash "$deploymentPath/util/deploy/dependencies/mysql-client" &&
sudo bash "$deploymentPath/util/deploy/dependencies/postgresql-client";

if [ ! $? -eq 0 ]; then
	exit 1;
fi

# Runtime directories
log 'Creating runtime directories…';
sudo mkdir -p --mode=0777 \
	"$deploymentPath/files" \
	"$deploymentPath/logs" \
	"$deploymentPath/log" \
	"$deploymentPath/lib/data/model/type" \
	"$deploymentPath/lib/classes/do/sales/base";

# Configuration
log 'Checking Flex Configuration…';
if [ ! -f "$deploymentPath/customer.cfg.php" ]; then
	sudo sh -c "echo '<?php' > $deploymentPath/customer.cfg.php;"
fi

if [ ! -f "$deploymentPath/flex.cfg.php" ]; then
	read -p 'No Flex Configuration found, supply an scp path to pull from [flex.template.cfg.php]: ' cfgSCPPath
	if [ ! "$cfgSCPPath" ]; then
		cfgSCPPath="$deploymentPath/util/flex.template.cfg.php"
	fi

	sudo scp "$cfgSCPPath" "$deploymentPath/flex.cfg.php" &&
	sudo editor "$deploymentPath/flex.cfg.php";
fi

# Database
mysqlOptionFile="`php flexcfgphp-to-myqloptionfile.php $deploymentPath/flex.cfg.php host port user password database`";
echo 'SHOW TABLES;' | mysql --defaults-extra-file="$mysqlOptionFile";
if [ $? -gt 0 ]; then
	log "Can't connect to database using details from flex.cfg.php"
	read -p "Create a new database (you will need MySQL administrator permissions)? [y/N]: " shouldCreateDB;
	if [ ! "$shouldCreateDB" = 'y' ]; then
		log "You can resume deployment by running this script again"
		exit 1;
	fi

	read -p "Admin User: " dbAdminUser;
	read -p -s "Admin Password: " dbAdminPassword;

	mysqlAdminOptionFile="`php flexcfgphp-to-myqloptionfile.php $deploymentPath/flex.cfg.php host port`";
	mysqlAdminOptionFile="$mysqlAdminOptionFile\nuser=$dbAdminUser\npassword=$dbAdminPassword\n"

	mysqlAdminDatabase="`php flexcfgphp-to-myqloptionfile.php $deploymentPath/flex.cfg.php host port | grep 'database='`";
	wouldrun -- mysqladmin --defaults-extra-file=<(php flexcfgphp-to-myqloptionfile.php) -- create "$mysqlAdminDatabase"
fi

# Rollout
pushd "$deploymentPath/cli" >> /dev/null;
rolloutVersionsPending="$(php rollout.php -p -v | wc -l)";
if [ "$rolloutVersionsPending" -gt 0 ]; then
	read -p "There are $rolloutVersionsPending Rollout versions pending. Release? [Y/n]: " shouldRollout;
	if [ ! "$shouldCreateDB" = 'n' ]; then
		log "WARNING: Not performing Rollout can leave the database and codebase out of sync!"
	else
		wouldrun -- sudo php rollout.php -v;
		if [ $? -gt 0 ]; then
			exit 1;
		fi
	fi
fi

if [ `ls -1 "$deploymentPath/lib/data/model/type" | wc --lines` -eq 0 ]; then
	log "Building Flex data model…";
	wouldrun -- sudo php rollout.php -m -v;
	if [ $? -gt 0 ]; then
		exit 1;
	fi
fi

if [ `ls -1 "$deploymentPath/classes/do/sales/base" | wc --lines` -eq 0 ]; then
	log "Building Flex/Sales Portal data model…";
	wouldrun -- sudo php build_do_model.php -v;
	if [ $? -gt 0 ]; then
		exit 1;
	fi
fi
popd >> /dev/null;

# Crontab
if [ ! -f "$deploymentPath/crontab" ]; then
	read -p 'No Flex crontab found, supply an scp path to pull from [flex.template.crontab]: ' crontabSCPPath
	if [ ! "$crontabSCPPath" ]; then
		crontabSCPPath="$deploymentPath/util/flex.template.cfg.php"
	fi

	sudo scp "$crontabSCPPath" "$deploymentPath/crontab" &&
	sudo editor "$deploymentPath/crontab";
fi

if [ ! -L "/etc/cron.d/$deploymentBasename" ]; then
	log "Creating crontab symlink…";
	sudo ln -s "$deploymentPath/crontab" "/etc/cron.d/$deploymentBasename";
	sudo service cron reload;
fi

log "Deployment Complete!";
exit 0;