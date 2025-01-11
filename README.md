# SiteGUI CMS aka LiteGUI
LiteGUI is the nick name of the SiteGUI CMS - a simplified version of the SiteGUI platform that can be used as the starting point to develop the backend for your web/mobile apps. LiteGUI can be used as a complete CMS system or a headless API backend.

# Features
LiteGUI includes the following features:
1. User and Staff Portal
2. Staff & Role RBAC Management			
3. User Management
4. User Group Management
5. Headless/API backend
6. Pre-made and Custom Apps/Templates (available on SiteGUI Appstore)
7. CMS
   - Multilingual       
   - Visual Editor
   - Widget Editor			
   - Menu Editor			
   - File Manager			
   - Template & Layout Editor			
   - Custom Layout			
   - Template Inheritance			
   - Page Cloning			
   - Page Versioning
   - Product Editor			
   - Multiple Product Variants			
   - Multiple Product Attributes	

# License
LiteGUI is distributed under the Elastic License 2.0 (https://www.elastic.co/licensing/elastic-license) that allows using the software for almost any purpose (e.g: build a web/mobile application for your customer using LiteGUI and charge an one-off fee for the development service) except providing/hosting the software as a cloud/SaaS product (for which customers are charged on a recurring basis).

LiteGUI free/default themes/templates are distributed under the MIT License.

# Installation
LiteGUI requires PHP 8 (with Curl, GD, Mbstring, MySQL, Redis and XML extensions), MySQL/MariaDB, Nginx web server and optionally Redis to work properly. 

LiteGUI provides pre-configured virtual machine images that can be deployed on your own server or public cloud providers. This is the preferred installation method as LiteGUI has been configured to work out of the box. We also provide a setup.sh script to set your domain up with LiteGUI when you login as root via virtual console (the root password is *StartLiteGUI*) or SSH using a public key.

The following images are available. Please note that all files are compressed and are about 750MB in size, the listed size is the maximum capacity for the virtual hard drive. You may use the 25G drive for testing and the 50G drive for production, this capacity can be increased if you need more storage space.

[LiteGUI 50GB KVM/QEMU image](https://cdn.sitegui.com/public/uploads/site/2/LiteGUI/litegui-vm-disk-50G.qcow2.gz)

[LiteGUI 25GB KVM/QEMU image](https://cdn.sitegui.com/public/uploads/site/2/LiteGUI/litegui-vm-disk-25G.qcow2.gz)

[LiteGUI 25GB VMWare/VirtualBox image](https://cdn.sitegui.com/public/uploads/site/2/LiteGUI/litegui-vm-disk-25G.vmdk.zip)

The best way to spin up these images and have your LiteGUI environment ready in minutes is to sign up for a LiteGUI VM through https://sitegui.com/store/litegui-hosting, the minimal VM starts from $10/month. You can also download the 25G QEMU image and upload to cloud providers like DigitalOcean to create a LiteGUI VM from the image. DigitalOcean offers free $200 credit (valid for 60 days) for you to try out, here is the signup link https://m.do.co/c/5f276041432e.

For those who wants to install LiteGUI manually on an existing server, you may download the source here and place it outside the webroot (to prevent direct access) and then configure Nginx to serve your website using mysite/src/index.php, the user portal using 
mysite/src/siteuser.php, the staff portal using mysite/src/siteadmin.php and the static files using the folder mysite/resources/public (see the sample Nginx configuration below). You also need to create a database to load the schema and data provided in the file "sitegui_mysite.sql". After that you can add the database connection credentials and domain information to mysite/src/config.php or config.local.php to complete the installation.

```
server {
   listen   80;
   listen   443 ssl;
   http2 on;
   server_name litegui.com *.litegui.com editing.domain.com;
   include  ssl_params;
   ssl_certificate     ssl/litegui.com.crt;
   ssl_certificate_key ssl/litegui.com.key;
   # SiteIndex
   location / {
      include        fastcgi_params;
      fastcgi_pass   unix:/var/run/php/php8.3-fpm.sock;
      fastcgi_param  SCRIPT_FILENAME   /home/sitegui/mysite/src/index.php;
      fastcgi_param  SERVER_NAME $host; 
   }
   # SiteAdmin
   location ~ ^/siteadmin/oauth/ {
      rewrite ^/siteadmin/oauth/(.+) /siteadmin?oauth=$1 permanent;
   }
   location ~ ^/siteadmin(/|\.json|$) {
      include        fastcgi_params;
      fastcgi_pass   unix:/var/run/php/php8.3-fpm.sock;
      fastcgi_param  SCRIPT_FILENAME   /home/sitegui/mysite/src/siteadmin.php;
      fastcgi_param  SERVER_NAME $host; 
   }
   # SiteUser
   location ~ ^/account/oauth/ {
      rewrite ^/account/oauth/(.+) /account?oauth=$1 permanent;
   }
   location ~ ^/account(/|\.json|$) {
      include        fastcgi_params;
      fastcgi_pass   unix:/var/run/php/php8.3-fpm.sock;
      fastcgi_param  SCRIPT_FILENAME   /home/sitegui/mysite/src/siteuser.php;
      fastcgi_param  SERVER_NAME $host; 
   }
   location ~ [^/]\.php(/|$) {
      deny all; #no need to serve any PHP files other than the above
   }
}

#Serving images/assets files
server {
   listen   80;
   listen   443 ssl;
   http2 on;
   server_name cdn.litegui.com;
   #root     /home/sitegui/public_html;
   include  ssl_params;
   ssl_certificate     ssl/litegui.com.crt;
   ssl_certificate_key ssl/litegui.com.key;
   # Disable access to .php and .tpl files completely
   location ~ \.(php|tpl)$ {
      return 403;
   }
   location /public/ {
      root /home/sitegui/mysite/resources; # /public/ will be added automatically
      location ~ \.(json)$ {
        add_header Access-Control-Allow-Origin *; #allow cors for on demand translation
      }
   }
   # SiteIndex
   location / {
      try_files $uri $uri/ =404;
   }
}
```