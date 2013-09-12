#!/bin/bash
if [[ ! -f ./maintenance ]]; then
	echo "Enabling Maintentance Mode"
	touch ./maintenance
else
	echo "Disabling Maintenance Mode"
	rm ./maintenance
fi

exit 0