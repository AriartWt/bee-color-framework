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

Now that your framework is installed and your first project created, let's see how to use `wfw` to
 start the developpement in the [first steps section](/general/first_steps/overview/).