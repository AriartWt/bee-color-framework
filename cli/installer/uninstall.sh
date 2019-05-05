#!/bin/bash
if [[ $(/usr/bin/id -u) -ne 0 ]]; then
	echo "Root permissions are required to run this script !"
	exit
fi

echo "wfw will be uninstalled..."

LOGPATH="/var/log/wfw"
CONFPATH="/etc/wfw"
A2CONF="/etc/apache2/conf-available/wfw-global.conf"

declare -a services=("msserver" "kvs" "sctl")
for i in "${services[@]}"
do
	SERVICE_NAME="wfw-$i"
	echo "Stopping $SERVICE_NAME.service..."
	systemctl stop "$SERVICE_NAME".service
	echo "Disabling $SERVICE_NAME.service..."
	systemctl disable "$SERVICE_NAME".service
	FILE="/usr/lib/systemd/system/$SERVICE_NAME.service"
	if [ -f "$FILE" ]
	then
		echo "Removing $FILE..."
		rm "$FILE"
	fi
	echo ""
done

FILE="/usr/bin/wfw"
if [ -f "$FILE" ]
then
	echo "Removing symlink $FILE..."
	rm "$FILE"
fi

if [ -f "$A2CONF" ]
then
	echo ""
	echo "Disabling apache wfw-global conf..."
	a2disconf wfw-global
	echo "Removing $A2CONF"
	rm "$A2CONF"
	echo "Reloading apache2..."
	systemctl reload apache2
	echo ""
fi

if [ -f "$LOGPATH" ]
then
	echo "Removing $LOGPATH..."
	rm -rf "$LOGPATH"
fi

if [ -f "$CONFPATH" ]
then
	echo "Removing $CONFPATH..."
	rm -rf "$CONFPATH"
fi

echo "Done."