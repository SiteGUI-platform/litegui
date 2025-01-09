#!/bin/bash
# Author: Nam Nguyen
# Version: 1.0
# Usage: setup.sh domain.com admin@email.com 
# Description: this script helps setup the server to work with domain.com
# 1. Change sitegui_mysql/root password
# 2. Upload public key for sitegui
# 3. Issue SSL cert for domain:
# 4. Change litegui.com to your domain in /home/sitegui/mysite/src/config.php, /etc/nginx/conf/default.conf
# 5. Change Site Manager account's name, email, id

echo -e "\nThanks for trying out LiteGUI. Let's get started by completing the initial server setup\n"
while true; do
   read -p "Enter the domain you want to use with LiteGUI: " DOMAIN
   [[ $DOMAIN =~ ^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$ ]] && break
done   
while true; do
   read -p "Enter your email address to setup the management account: " EMAIL
   [[ $EMAIL =~ ^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$ ]] && break
done

# Check required inputs
#test -z "$1" && echo "Usage: ./setup.sh your-domain.com your@email.com" && exit 1
#[[ ! $2 =~ ^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$ ]] && echo "Usage: ./setup.sh your-domain.com your@email.com" && exit 1
#Issue SSL cert for the domain
/etc/nginx/acme.sh/create.sh $DOMAIN
# Check the status
if [ $? -eq 0 ]; then
   #Change MySQL root and sitegui password
   DB_ROOT=$(tr -dc 'A-Za-z0-9!?%=' < /dev/urandom | head -c 20)
   DB_SG=$(tr -dc 'A-Za-z0-9!?%=' < /dev/urandom | head -c 20)
   PASS=$(tr -dc 'A-Za-z0-9!?%=' < /dev/urandom | head -c 15)
   SALT=$(tr -dc 'A-Za-z0-9!?%=' < /dev/urandom | head -c 20)
   mysql -u root -e "SET PASSWORD FOR sitegui_mysql@'localhost' = PASSWORD('$DB_SG');"
   mysql -u root -e "SET PASSWORD FOR root@'localhost' = PASSWORD('$DB_ROOT');"
   mysql -u root -e "FLUSH PRIVILEGES;"
   sed -i "s/\$config.*//" /home/sitegui/config.local.php
   echo "\$config['db']['password'] = '$DB_SG';" >> /home/sitegui/config.local.php
   echo "\$config['static_salt'] = '$SALT';" >> /home/sitegui/config.local.php
   sed -i "s/password=.*/password=$DB_ROOT/" /root/.my.cnf

   #Update Staff email, password and timestamp
   HASHED=$(php -r "echo password_hash('$PASS', PASSWORD_DEFAULT);")
   TNOW=$(date +%s)
   mysql -u root -e "UPDATE sitegui_mysite.mysite1_user SET email='$EMAIL', password='$HASHED', registered=$TNOW WHERE id=1023;"

   #Replace domain
   CONFIGURED_FILE="/etc/nginx/acme.sh/.main-domain"
   OLD_DOMAIN=$(cat "$CONFIGURED_FILE" 2>/dev/null)
   OLD_DOMAIN=${OLD_DOMAIN:-litegui.com}
   sed -i "s/$OLD_DOMAIN/$DOMAIN/" /home/sitegui/mysite/src/config.php
   sed -i "s/$OLD_DOMAIN/$DOMAIN/" /etc/nginx/conf.d/default.conf
   systemctl restart nginx
   echo $DOMAIN > $CONFIGURED_FILE
   echo "litegui.$DOMAIN" > /etc/hostname
   hostnamectl set-hostname "litegui.$DOMAIN" 
   dbus-uuidgen --ensure=/etc/machine-id
   dbus-uuidgen --ensure
   dpkg-reconfigure openssh-server

   #Print info
   echo -e "You can start building your site now.\n"
   echo "Site Manager: https://admin.$DOMAIN/siteadmin"
   echo "Username: $EMAIL"
   echo "Password: $PASS"
   echo -e "\n"

   # Ask the user for their public SSH key
   AUTH_KEYS_FILE="/root/.ssh/authorized_keys"
   read -p "Please add your public SSH key for 'root' account: " PUBLIC_KEY
   # Check if the key is already in the authorized_keys file
   if grep -Fxq "$PUBLIC_KEY" "$AUTH_KEYS_FILE"; then
      echo "The SSH key is already in the authorized_keys file."
   elif [ -n "$PUBLIC_KEY" ]; then
      # Append the public key to the authorized_keys file
      echo "$PUBLIC_KEY" > "$AUTH_KEYS_FILE"
      echo "$PUBLIC_KEY" > /home/sitegui/.ssh/authorized_keys
      echo "The SSH key has been added to the authorized_keys file."
   fi
   echo "Now please change the root password to remove the default one"
   passwd
   echo "Done. Happy Building!"
else
   echo "Failed to create an SSL certificate for $DOMAIN."
   echo "Make sure to point $DOMAIN, www.$DOMAIN, cdn.$DOMAIN, my.$DOMAIN, mz.$DOMAIN, admin.$DOMAIN, edit.$DOMAIN to this server first."
   echo "You may use litegui.com as the domain if you dont have one or cannot point it to this server." 
   echo "You will be able to run the setup again when you login next time. Goodbye!" 
fi