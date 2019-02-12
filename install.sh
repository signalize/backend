#!/usr/bin/env bash
SECUREKEY=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1)
IPADDR=$(ip a s|sed -ne '/127.0.0.1/!{s/^[ \t]*inet[ \t]*\([0-9.]\+\)\/.*$/\1/p}')

welcome(){
    whiptail --msgbox --backtitle "Signalize Installer" \
    --title "Signalize Installer" \
    "** This will install the Signalize Service on your device!**\n
    Signalize is a SERVER with WebSocket functionality.
    To make this work, you need to open some ports in your router/firewall." \
    12 78
}

setHost(){
    HOST=$(whiptail --inputbox "Type the domain you wan't to use for the signalize server" 8 78 "${IPADDR}" --title "Setup the Hostname" 3>&1 1>&2 2>&3)
    exitstatus=$?
    if [ $exitstatus = 0 ]; then
        setPort
    else
        exit;
    fi
}

setPort(){
    PORT=$(whiptail --inputbox "Witch port you wan't to use for the signalize server?" 8 78 9000 --title "Setup the Webserver Port" 3>&1 1>&2 2>&3)
    exitstatus=$?
    if [ $exitstatus = 0 ]; then
        setSocket
    else
        setHost
    fi
}
setSocket(){
    SOCK=$(whiptail --inputbox "Witch port you wan't to use for the signalize socket? \n** You don't need to open this port in your router/firewall **" 8 78 9050 --title "Setup the Socket Port" 3>&1 1>&2 2>&3)
    exitstatus=$?
    if [ $exitstatus = 0 ]; then
        setModules
    else
        setPort
    fi
}

setModules(){
    MODULES=""
    for file in $PWD/Modules
    do
        MODULES+="\"${file}\" \"${file}\" ON"
    done



    whiptail --title "Check list example" --checklist \
    "Choose user's permissions" 20 78 4 \
    $MODULES
#
#    while read choice
#    do
#        case $choice in
#            John) echo "You chose John"
#            ;;
#            Glen) echo "You chose Glen"
#            ;;
#            Adam) echo "You chose Adam"
#            ;;
#            *)
#            ;;
#        esac
#    done < results
}


setModules

#welcome
#setHost


#sudo apt update
#sudo apt-get install php-fpm nginx
#
#
#
#
#echo "Type the domain you wan't to use for the signalize server, followed by [ENTER]: (default: $ipAddr)"
#read domain;
#
#echo "Type the port you wan't to use for the signalize server, followed by [ENTER]: (default: 9000)"
#read port;
#
#echo "Type the port you wan't to use for the signalize socket, followed by [ENTER]: (default: 9050)"
#read socket;
#
#
#if [ "$domain" == "" ] ; then domain="$ipAddr" ; fi
#if [ "$port" == "" ] ; then port="9000" ; fi
#if [ "$socket" == "" ] ; then socket="9050" ; fi
#
#echo "server {
#        listen $port;
#        listen [::]:$port;
#
#        root $PWD/public;
#        index index.html index.htm index.nginx-debian.html;
#
#        server_name $domain;
#
#        location / {
#                try_files \$uri \$uri/ =404;
#        }
#
#        location /sock/ {
#            proxy_pass http://127.0.0.1:$socket;
#            proxy_http_version 1.1;
#            proxy_set_header Upgrade \$http_upgrade;
#            proxy_set_header Connection "Upgrade";
#        }
#}" > nginx.conf
#
#echo '{
#  "server": {
#    "host": "'$domain'",
#    "port": "'$port'"
#  },
#  "socket": {
#    "port": "'$socket'",
#    "security": "'$SECUREKEY'"
#  },
#  "modules": {}
#}' > config.json
#
#
#sudo rm /etc/nginx/sites-enabled/nginx.conf
#sudo ln -s $PWD/nginx.conf /etc/nginx/sites-enabled/
#sudo systemctl reload nginx
