# WFW

!!! warning "Writing in progress"

	This page is under construction

Once the framework [installed](general/start), `wfw` is enabled. It manages its global instance and
allow you to create, remove, update and exec commands on your projects.

## Create a project

To make it easier to configure your new projects, `wfw` handle several tasks when you use its `create`
command:

- Create the project skeleton.
- Copy `/srv/wfw/global/engine`, `/srv/wfw/global/cli`, `/srv/wfw/global/daemons` to the project root
- Create symlinks required by `wfw` to manage th project.
- Create the project db and two users who can manage it.
- Create the [kvs container](daemons/kvs) and its user.
- Create the [msserver instance](daemons/msserver) and its user.
- Edit all configuration files.
- Create a first user enabled for your app/website.

So, to create a project in our installed framework, let's use the command
`wfw create [Project name] [absolute path to project folder]`

``` bash
sudo wfw create ProjectName /srv/wfw
```

??? help "Why is the path ?"

	For the sake of flexibility, the folder which will contain your project can be anywhere on your
	system.

	No matter your configuration, think to use an **absolute** path with this command, and paid
	attention to the destination folder : it owner *must* be the apache user and must have the
	execution permission.

When the command will ends, a `ProjectName.cred` file will be created under the `wfw` working dir
given in the `cli/wfw/conf/config.json` file.
It contains the first user credentials : user login on the first line, and the password on the second,
automatically generated with an UUID v4.

!!! warning

	If a database that have the same name than those which will be created by `wfw` already exists in
	**mysql**, it will be silently removed and replaced by a clean DB.

	The same process will happen for existing users.

	This process is normal and usefull when a `create` command fails, espacially for permissions
	problems. So, if you want to resintall a project AND keep its data, think about create a [backup](/cli/backup/)
	before, otherwise your data will be **lost**.

## Import and update a project

Commands to import and update a project are the same : `wfw import [Project name] [absolute path to sources]`

### In an existing project

Just use the `import` command with the project name and the **absolute** path to the sources you
want to import to update it :
```bash
sudo wfw import ProjectName ~/projectname
```

The project configs will be automatically edited by `wfw` to match mysql, kvs and msserv's users.

### If the project haven't been created with `wfw create`

You must first create it :

```bash
sudo wfw create ProjectName /srv/wfw
```

And then import your sources with their **absolute** path :
```bash
sudo wfw import ProjectName ~/projectname
```

!!! info "Infos on data"

	The **MYSQL** data import have to be done by hand, since `wfw` is not able to do it.

## Remove a project

The commande to remove is `wfw remove [Project name]` :

```bash
sudo wfw remove ProjectName
```

!!! warning

	`wfw` will not remove **mysql** database to avoid a disater after a manipulation error.

	It will not remove the project folder, for the same reason, only its symbolic links, and will cleanup
	[kvs](/daemons/kvs/) and [msserver](/daemons/msserver/) configurations (users and container/instance).

	To definively remove it, you must do it manually :
    ```bash
    sudo rm -rf /srv/wfw/ProjectName
    ```

    Then, remove by hand the database and users created by `wfw create`.

    ??? hint "Hint : accidental remove"

		If you accidentaly use the `wfw remove` command, start by move the project folder to a safe
		location (your home directory should be fine) :

        ```bash
        sudo mv /srv/wfw/ProjectName ~
        ```

		If you have data to keep, use mysqldump :

        ```bash
        mysqldump -u root -p[root_password] [database_name] > ~/dumpfilename.sql
        ```

        Create a new project with the same name as the one you just removed :
        ```bash
        sudo wfw create ProjectName /srv/wfw
        sudo wfw import ProjectName ~/ProjectName
        ```

        Restaure your data.

        ```bash
        mysql -u root -p[root_password] [database_name] < ~/dumpfilename.sql
        ```

## Updating the framework

A framework update can be done with the `wfw update` command which behave like `import` but for the
global directory.

Start downloading the last framework version, and then use the command `update` (think about removing the
.git folder) :

```bash
cd ~
git clone git@framagit.org:Ariart/bee-color-framework.git
rm -rf bee-color-framework/.git
sudo wfw update -all ~/bee-color-wfw
```

You can *replace* the `-all` argument by the following ones, according to your needs :

- `-all` : framework and all projects.
- `-self` : framework only
- `-ProjectName` : ProjectName project
- `-self,ProjectName1,ProjectName3` : framework, ProjectName1 and ProjectName3.

You can update the framework only, if a version in backward incompatible for a project. Each get it's own
copy of the framework source at it creation, so each is idependant from the others.

!!! note

	While no backward incompatible changes are made to the client interface of the [KVS](/daemons/kvs/)
	or the [MSserver](/daemons/msserver/), there should be no problem to update the framework without
	keeping project updated, unless a security release is deployed.

Keep the framework updated allow you to create new projects with its new version.