## Prérequis

Voici la liste des prérequis pour l'installation du framework : 
  
  - PHP >= 7.2 avec les extensions `php7.2-common php7.2-curl php7.2-xml php7.2-zip php7.2-gd php7.2-mysql php7.2-mbstring php-apcu` et `pcntl`
  - MYSQL & MysqlDump
  - Accès root sur Ubuntu >= 16.04
  - Apache >= 2.4 avec les modules `rewrite deflate headers filter` activés
  
??? note "Remarque sur Ubuntu"
    
    En principe, du moment que l'extention pcntl est disponible sur **PHP** (ce qui exclus les plateformes
    Windows), et que le système permet l'installation  et la gestion de daemons via **systemctl**, 
    tout devrait bien se passer.
    
    Je laisse cependant ce prérequis pour le moment parce que je n'ai pas encore eu le temps de
    le tester sur d'autres environnements.
    
??? note "Remarque sur Apache"

    **WFW Bee-color** utilise des .htaccess pour permettre les redirections et les accés aux dossiers
    publics (appelés `webroot`). Pour l'instant, un portage **Nginx** n'est pas encore prévu.

## Installation

### Dossier principal

Pour commencer, nous allons récupérer les sources, et supprimer le dossier .git, inutile pour la 
suite :

``` bash  
git clone git@framagit.org:Ariart/bee-color-wfw.git
rm -rf bee-color-wfw/.git
mv bee-color-wfw global
```

Ensuite, nous créons le dossier qui va recueillir le framework et vos projets :

``` bash  
sudo mkdir /srv/wfw
sudo mv ~/global /srv/wfw
```
    
Maintenant, on change les permissions pour qu'apache en devienne l'unique propriétaire :

``` bash
sudo chown -R www-data:www-data /srv/wfw
sudo chmod -R 700 /srv/wfw
```
    
??? note
    
    Si l'utilisateur associé à apache n'est pas `www-data`, pensez à adapter les commandes.

### Script d'installation
    
Nous avons presque terminé, il suffit maintenant d'installer les daemons :

``` bash
sudo /srv/wfw/global/cli/installer/install.sh -global
```

??? help "Pourquoi l'argument `-global` ?"

    Cet argument est présent pour installer automatiquement l'utilitaire `wfw` en global sur votre
    système.
    
    Vous pouvez l'omettre, mais il vous faudra alors utiliser son chemin complet lorsque vous 
    voudrez l'utiliser. Vous aurez donc à saisir dans votre invite de commande ceci :
    ```bash
    sudo /srv/wfw/global/cli/wfw/WFWGlogalLauncher.php ...
    ```
    au lieu de :
    ```bash
    sudo wfw ...
    ```

### MYSQL

Pour la suite, il nous faut créer un utilisateur MYSQL qui a les permissions necessaires à la création
de bases de données et d'utilisateurs. Pour cela vous pouvez passer par mysql en ligne de commande,
ou par **phpmyadmin**.

??? warning "Attention"

    La plupart d'entre-vous le sait déjà, mais je pense que c'est toujours bon à rappeler : 
    
    Etant donné les droits conférés à cet utilisateur, pensez à lui choisir un mot de passe 
    sécurisé, et si possible à lui interdire les connexions depuis l'extérieur si votre instance
    de Mysql tourne en local.
        
    ??? hint "Astuce"
        
        Si vous utilisez **phpmyadmin**, vous pouvez éditer le fichier `/etc/phpmyadmin/conf.inc.php`,
        déplacez-vous jusqu'au premier `$i++;` et ajoutez les lignes suivantes : 
        ```
        <?
        //...
        $i++;
        // Interdit la connexion à phpmyadmin à tous les utilisateurs
        $cfg['Servers'][$i]['AllowDeny']['order'] = 'explicit';
        // L'utilisateur apublicuser est autorisé à se connecter.
        $cfg['Servers'][$i]['AllowDeny']['rules'] = [
            'allow apublicuser from all'
        ];
        ```

### Configuration

Disons que l'utilisateur **MYSQL** créé à l'étape précédente est `wfw-user` et son mot de passe `mypassword`.

Editez le fichier `/srv/wfw/global/cli/wfw/config/conf.json`. Si votre utilisateur unix pour
apache n'est pas www-data, pensez à l'éditer aussi.

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

Pour ce qui est des autres options, rien de très compliqué : 

- `permissions` : permissions attribuées à tous les fichiers créés par l'utilitaire wfw
- `mysql/path` : chemin d'accés au programme mysql, s'il n'est pas installé en commande globale
- `mysqldump_path` : chemin d'accés au programme mysqldump, s'il n'est pas installé en commande globale
- `tmp` : chemin d'accés au dossier dans lequel `wfw` travaillera.
  
??? note "Note"
    
      Même si le dossier `/tmp` est public, tous les fichiers sensibles y seront créés
      avec des droits minimums par mesure de sécurité.

Le framework est à présent configuré et prêt à l'emplois.

### Création d'un premier projet

Nous pouvons maintenant créer notre premier projet via la commande suivante :

```bash
sudo wfw create ProjectName /srv/wfw
```

!!! info "Informations"

	Pour plus de détail sur les différentes commandes disponibles en **CLI**, veuillez suivre [ce lien](/cli/wfw/).

Maintenant que votre framework est installé et que votre premier projet est créé, voyons un peu
comment utiliser `wfw` pour commencer le développement dans la section [Premiers pas](/general/first_steps/tree/)