## Prerequisites

There are the exhaustive liste of prerequisites needed to install the framework :
  
  - PHP >= 7.2 with modules `php7.2-common php7.2-curl php7.2-xml php7.2-zip php7.2-gd php7.2-mysql php7.2-mbstring php-apcu` et `pcntl`
  - MYSQL & MysqlDump
  - root access on Ubuntu >= 16.04
  - Apache >= 2.4 with modules `rewrite deflate headers filter` enabled
  
??? note "Note on Ubuntu"

    Normaly, while the pcntl extension is available on **PHP**, and if the system can run systemctl
    to manage daemons, all should be just fine.

    But as I didn't have the time yet to test it in other environments, I let this requirement in place.

??? note "Note on Apache"

    **WFW Bee-color** use .htaccess files to provide some redirections and public folder access
    (called `webroot`). For the moment, a **Nginx** compatibility is not planned.

## Installation

### Main folder

Let's start downloading sources and removing the .git directory, which will not be used anymore :

``` bash
git clone git@framagit.org:Ariart/bee-color-framework.git
rm -rf bee-color-framework/.git
mv bee-color-framework global
```

Then, we create the folder in which the framework will be, with all your projects :

``` bash
sudo mkdir /srv/wfw
sudo mv ~/global /srv/wfw
```

Now, we give it to apache who become the only owner :

``` bash
sudo chown -R www-data:www-data /srv/wfw
sudo chmod -R 700 /srv/wfw
```

??? note

	If your apache user is not www-data, think about adapting comands.

### Install script

Almost down, we just nee to install daemons :

``` bash
sudo /srv/wfw/global/cli/installer/install.sh -global
```

??? help "Why the `-global` arg ?"

	This arg is there to install the `wfw` command on your system.

	You can omit it, but if you do so, you will have to use its full path to call it :
    ```bash
    sudo /srv/wfw/global/cli/wfw/WFWGlogalLauncher.php ...
    ```
    instead of :
    ```bash
    sudo wfw ...
    ```

### MYSQL

To continue, we have te create a MYSQL user, who have enough permissions to create databases and other users.
For this, you can go through CLI or phpmyadmin, as you like.

??? warning

	Even if many of you already know that, it's always good to specify :

	Given the permissions granted to this user, think about choose a strong password policy, and,
	if possible, denie him connection from the outside world if your mysql instance is local.

    ??? hint

		If you use **phpmyadmin**, you can edit the `/etc/phpmyadmin/conf.inc.php` file. Find the first
		`$i++;` and add the following lines :
        ```
        <?
        //...
        $i++;
        // Forbide connection to all users
        $cfg['Servers'][$i]['AllowDeny']['order'] = 'explicit';
        // Allow apublicuser to connect
        $cfg['Servers'][$i]['AllowDeny']['rules'] = [
            'allow apublicuser from all'
        ];
        ```

### Configuration

Let's say your **MYSQL** user, which you created in the previous step is `wfw-user` and his password
is `mypassword`.

Edit the `/srv/wfw/global/cli/wfw/config/conf.json` file with your favourite editor. If your apache user is not
www-data, change it accordingly to your setup.

``` bash
sudo nano /srv/wfw/global/cli/wfw/config/conf.json
```

``` json hl_lines="2 7 8"
{
	"unix_user" : "www-data",
	"permissions" : 700,
	"mysql" : {
		"path" : "mysql",
		"root" : {
			"login" : "wfw-user",
			"password" : "mypassword"
		}
	},
	"mysqldump_path" : "mysqldump",
	"tmp" : "/tmp"
}
```

For the other options, nothing very hard to understand :

- `permissions` : permissions given to files created by `wfw`
- `mysql/path` : path to mysql, if not global
- `mysqldump_path` : path to mysqldump, if not global
- `tmp` : path to the `wfw` working dir.

??? note

	Even if `/tmp` is a public directory, all sensible files will be created with minimum permissions
	for security concerns.

The framework is now configured and ready to use.

### Creating our first project

Let's execute the following command :

```bash
sudo wfw create ProjectName /srv/wfw
```

!!! info

	For mor details about the available commands in **CLI**, please take a look [there](/cli/wfw).

### Apache configurations

#### Grant access to the project folder

To allow `.htaccess` to correctly redirect requests in the projet folder, you must create an
apache conf file in `/etc/apache2/sites-available` usualy with the name of your website/web application :
(ex : `project-name.com`)

We will start by configuring the root directory configs :
```apache
<Directory /srv/wfw/ProjectName>
	AllowOverride All
	Options FollowSymLinks Indexes
	Require all granted
	Order allow,deny
	Allow from all
</Directory>
```

#### HTTP, HTTPS et HTTP2

Now that SSL certificates are easy and free to acquire and use (see [let's encrypt](https://letsencrypt.org/)),
we will configure our server to automatically redirect all default unsecure trafic to the secured SSL port :

```apache
<VirtualHost *:80>
	DocumentRoot "/srv/wfw/ProjectName"
	ServerName project-name.com
	# Only if you want to redirect all your subdomains too
	ServerAlias *.project-name.com

	Include /etc/apache2/common/letsencrypt.conf
	RewriteEngine On
	RewriteCond %{HTTPS} off
	RewriteCond %{REQUEST_URI} !^/\.well\-known/acme\-challenge
	RewriteRule (.*) https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]
</VirtualHost>
```
This configuration have to advantages : the first is to secure all your trafic and the second is to
allow your server to serve all your pages with the
[HTTP2](https://httpd.apache.org/docs/current/fr/howto/http2.html) (h2) protocol.

??? note "HTTP2 vs HTTP1 et **WFW**"
	Serving pages through HTTP2 with **WFW** and it's default config is highly recommended since the
	framework encourage you to split your css/javascript files withtout using a module bundler like
	Webpack. You can serve your pages using HTTP1, but the ressources loading will be longer.

	Of course, this default behaviour can be modified to work with module bundlers, see the
	[ressources inclusion section](/general/first_steps/ressources_managers) for more details.

??? help "How to enable HTTP2 with apache and PHP 7.x ?"
	Enabling HTTP2 is easier that it seems but requires some important steps, sometimes hard to find
	on internet.

	If you installed apache2 with `apt-get`, you should have a `httpd` binary compiled with the
	`mod_http2` module. If not, please refer to the
	[apache documentation](https://httpd.apache.org/docs/current/en/howto/http2.html)

	To enable HTTP2 and make it works with PHP 7.x and later, please follow
	[this tuto](https://gist.github.com/GAS85/990b46a3a9c2a16c0ece4e48ebce7300). Think about adapt
	your PHP version accordingly (minimum 7.2 in our case).

??? help "What's in the `/etc/apache2/common/letsencrypt.conf` file ?"
	This config file allow you to automaticaly renew your Let's Encrypt certifacates :
	```apache
	Alias /.well-known/acme-challenge/ /srv/www/letsencrypt/.well-known/acme-challenge/
	<Directory "/srv/www/letsencrypt/.well-known/acme-challenge/">
		Options None
		AllowOverride None
		ForceType text/plain
		RedirectMatch 404 "^(?!/\.well-known/acme-challenge/[\w_-]{43}$)"
	</Directory>
	```
	The Let's Encrypt configuration is far beyond the scope of this documentation, but you can find
	all necessary informations and steps to follow in this easy to understand and well constructed
	tutorial serie :
	[lets-encrypt-from-start-to-finish](https://blog.wizardsoftheweb.pro/lets-encrypt-from-start-to-finish-overview/)

Let's see now the 443 (SSL) port config :
```apache
<VirtualHost *:443>
	DocumentRoot "/srv/wfw/ProjectName"
	ServerName project-name.com

	# Include scoped config
	RewriteEngine On
	RewriteCond %{HTTP_HOST} ^www.project-name.com(.*)
	RewriteRule (.*) https://project-name.com%{REQUEST_URI} [R=301,L]

	SSLEngine on
	SSLCertificateFile /etc/letsencrypt/live/project-name.com/fullchain.pem
	SSLCertificateKeyFile /etc/letsencrypt/live/project-name.com/privkey.pem

	# HTTP2 configs
	Protocols h2 h2c http/1.1
	H2Push on
	H2PushPriority * after
	H2PushPriority text/css before
	H2PushPriority image/jpg after 32
	H2PushPriority image/jpeg after 32
	H2PushPriority image/png after 32
	H2PushPriority application/javascript interleaved
 </VirtualHost>
```
We make the decision to redirect all requets coming for the subdomain `www.project-name.com` to the
root. This step can be omitted if you create your certificate to cover this subdomain using a wildcard
for example.

If you use a different config or another SSL certificates provider, think about edit this config
accordingly.

Know, you only have to enable your conf and restart apache :
```bash
sudo a2ensite project-name.com
sudo systemctl restart apache2
```

## And now ?

Now that your framework is installed, your apache server ready and your first project created,
let's see how to use `wfw` to start the developpement in the
[Quick start section](/general/quickstart/first_project/).

If you want to know more about the framework functionnalities, please read the
[First steps section](/general/first_steps/overview).