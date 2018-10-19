#!/bin/bash
if [[ $(/usr/bin/id -u) -ne 0 ]]; then
	echo "Root permissions are required to run this script !"
	exit
fi

declare -a services=("msserver" "kvs" "sctl")
for i in "${services[@]}"
do
	SERVICE_NAME="wfw-$i"
	systemctl stop "$SERVICE_NAME".service
	systemctl disable "$SERVICE_NAME".service
	FILE="/usr/lib/systemd/system/$SERVICE_NAME.service"
	if [ -f "$FILE" ]
	then
		rm "$FILE"
	fi
done

FILE="/usr/bin/wfw"
if [ -f "$FILE" ]
then
	rm "$FILE"
fi