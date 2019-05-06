#!/bin/bash
if [[ $(/usr/bin/id -u) -ne 0 ]]; then
	echo "Root permissions are required to run this script !"
	exit
fi

echo "wfw will be installed..."

INSTALLPATH="$( cd "$(dirname "$0")" ; pwd -P )"
ROOTPATH="$( cd "$(dirname "$INSTALLPATH")" ; pwd -P )"
ROOTPATH="$( cd "$(dirname "$ROOTPATH")" ; pwd -P )"

A2CONF="$INSTALLPATH/config/wfw-global.conf"
A2CONF_PATH="/etc/apache2/conf-available"
LOGPATH="/var/log/wfw"
LOGPATH_OWNER="www-data:www-data"
LOGROTATE_PATH="/etc/logrotate.d"
LOGROTATE_CONF="$INSTALLPATH/config/wfw"
CONFPATH="/etc/wfw"

CONFSDIR=()
while IFS=  read -r -d $'\0'; do
    CONFSDIR+=("$REPLY")
done < <(find "$ROOTPATH" -type d -name config -print0)

echo "Installer path : $INSTALLPATH"
echo "Giving execution permission to all *Launcher.php..."
find "$INSTALLPATH/../" -name "*Launcher.php" | xargs chmod +x

if [ ! -d "/usr/lib/systemd/system" ]
then
	mkdir -p "/usr/lib/systemd/system"
	echo "/usr/lib/systemd/system created."
fi

echo ""

declare -a services=("sctl" "kvs" "msserver")
for i in "${services[@]}"
do
    #Reset the file if exists
    DAEMONCONF="$INSTALLPATH/config/$i.systemctl"
    SERVICE_NAME="wfw-$i"
	SERVICE_PATH="/usr/lib/systemd/system/$SERVICE_NAME.service"

	echo "Installing daemon $SERVICE_NAME..."
    if [ -f "$DAEMONCONF" ]
    then
    	echo "Removing old $DAEMONCONF..."
        truncate -s 0 "$DAEMONCONF"
    fi
    #Create the file with the good path to scripts
    echo "Creating $DAEMONCONF..."
    cat "$INSTALLPATH/config/$i.systemctl.template" | sed -e "s+@ROOT+$ROOTPATH+g" >> "$INSTALLPATH/config/$i.systemctl"
	#Add to systemctl and setup daemons
	echo "Moving $DAEMONCONF to $SERVICE_PATH"
	mv "$DAEMONCONF" "$SERVICE_PATH"

	systemctl enable "$SERVICE_NAME.service"
	echo "Service $services.service enabled"
	systemctl start "$SERVICE_NAME.service"
	systemctl is-active --quiet "$SERVICE_NAME.service" && echo "Daemon $SERVICE_NAME started" || "WARNING : Daemon $SERVICE_NAME failed to start"
	echo ""
done

ln -s "$INSTALLPATH/../wfw/WFWGlobalLauncher.php" "/usr/bin/wfw"
echo "Created symlink from $INSTALLPATH/../wfw/WFWGlobalLauncher.php to /usr/bin/wfw"

echo ""
echo "Creating $A2CONF...";
cat "$INSTALLPATH/config/wfw-global.conf.template" | sed -e "s+@ROOT+$ROOTPATH+g" >> "$A2CONF"
echo "Moving $A2CONF to $A2CONF_PATH..."
mv "$A2CONF" "$A2CONF_PATH"
echo "Enabling wfw-global.conf..."
a2enconf wfw-global
echo "Reloading apache2..."
systemctl reload apache2

echo ""
echo "Creating $CONFPATH..."
mkdir -p "$CONFPATH"
for i in "${CONFSDIR[@]}"
do
	LINKNAME="${i/#$ROOTPATH/}"
	LINKNAME="${LINKNAME::-7}"
	mkdir -p "$CONFPATH$LINKNAME"
	rm -rf "$CONFPATH$LINKNAME"
	echo "Creating symlink from $i to $CONFPATH$LINKNAME..."
	ln -s "$i" "$CONFPATH$LINKNAME"
done

echo ""
echo "Creating $LOGPATH..."
mkdir -p "$LOGPATH"
echo "Setting $LOGPATH_OWNER as owner..."
chown -R "$LOGPATH_OWNER" "$LOGPATH"

if [ -d "$LOGROTATE_PATH" ]
then
	echo ""
	echo "Creating $LOGROTATE_CONF..."
	cat "$INSTALLPATH/config/logrotate.template" | sed -e "s+@LOGDIR+$LOGPATH+g" >> "$LOGROTATE_CONF"
	echo "Moving $LOGROTATE_CONF to $LOGROTATE_PATH..."
	mv "$LOGROTATE_CONF" "$LOGROTATE_PATH"
fi

echo ""
echo "Done."