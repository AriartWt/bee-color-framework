#!/bin/bash
if [[ $(/usr/bin/id -u) -ne 0 ]]; then
	echo "Root permissions are required to run this script !"
	exit
fi

INSTALLPATH="$( cd "$(dirname "$0")" ; pwd -P )"
ROOTPATH="$( cd "$(dirname "$INSTALLPATH")" ; pwd -P )"
ROOTPATH="$( cd "$(dirname "$ROOTPATH")" ; pwd -P )"

find "$INSTALLPATH/../" -name "*Launcher.php" | xargs chmod +x

if [ ! -d "/usr/lib/systemd/system" ]
then
	mkdir "/usr/lib/systemd/system"
fi

declare -a services=("sctl" "kvs" "msserver")
for i in "${services[@]}"
do
    #Reset the file if exists
    if [ -f "$INSTALLPATH/config/$i.systemctl" ]
    then
        truncate -s 0 "$INSTALLPATH/config/$i.systemctl"
    fi
    #Create the file with the good path to scripts
    cat "$INSTALLPATH/config/$i.systemctl.template" | sed -e "s+@ROOT+$ROOTPATH+g" >> "$INSTALLPATH/config/$i.systemctl"
	#Add to systemctl and setup daemons
	SERVICE_NAME="wfw-$i"
	mv "$INSTALLPATH/config/$i.systemctl" "/usr/lib/systemd/system/$SERVICE_NAME.service"
	systemctl enable "$SERVICE_NAME".service
	systemctl start "$SERVICE_NAME".service
done

if [ "$1" = "-global" ]
then
	ln -s "$INSTALLPATH/../wfw/WFWGlobalLauncher.php" "/usr/bin/wfw"
	echo "Created symlink from $INSTALLPATH/../wfw/WFWGlobalLauncher.php to /usr/bin/wfw"
fi