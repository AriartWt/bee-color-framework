## Prérequis

Voici la liste des prérequis pour l'installation du framework : 
  
  - PHP >= 7.2 avec les extensions `php7.2-common php7.2-curl php7.2-xml php7.2-zip php7.2-gd php7.2-mysql php7.2-mbstring php-apcu` et `pcntl`
  - MYSQL & MysqlDump
  - Accès root sur Ubuntu >= 16.04
  - Apache >= 2.4 avec les modules `rewrite deflate headers filter` activés
  
??? note "Remarque sur Ubuntu"
    
    En principe, du moment que l'extension pcntl est disponible sur **PHP** (ce qui exclut les plateformes
    Windows), et que le système permet l'installation  et la gestion de daemons via **systemctl**,
    tout devrait bien se passer.

    Je laisse cependant ce prérequis pour le moment parce que je n'ai pas encore eu le temps de
    le tester sur d'autres environnements.

??? note "Remarque sur Apache"

    **WFW Bee-color** utilise des .htaccess pour permettre les redirections et les accès aux dossiers
    publics (appelés `webroot`). Pour l'instant, un portage **Nginx** n'est pas encore prévu.

## Installation

### Dossier principal

Pour commencer, nous allons récupérer les sources, et supprimer le dossier .git, inutile pour la
suite :

``` bash
git clone git@framagit.org:Ariart/bee-color-framework.git
rm -rf bee-color-framework/.git
mv bee-color-framework global
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

Pour la suite, il nous faut créer un utilisateur MYSQL qui a les permissions nécessaires à la création
de bases de données et d'utilisateurs. Pour cela vous pouvez passer par mysql en ligne de commande,
ou par **phpmyadmin**.

??? warning "Attention"

    La plupart d'entre vous le savent déjà, mais je pense que c'est toujours bon à rappeler :

    Étant donné les droits conférés à cet utilisateur, pensez à lui choisir un mot de passe
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

Éditez le fichier `/srv/wfw/global/cli/wfw/config/conf.json`. Si votre utilisateur unix pour
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

- `permissions` : permissions attribuées à tous les fichiers créés par `wfw`
- `mysql/path` : chemin d'accès au programme mysql, s'il n'est pas installé en commande globale
- `mysqldump_path` : chemin d'accès au programme mysqldump, s'il n'est pas installé en commande globale
- `tmp` : chemin d'accès au dossier dans lequel `wfw` travaillera.

??? note "Note"

      Même si le dossier `/tmp` est public, tous les fichiers sensibles y seront créés
      avec des droits minimums par mesure de sécurité.

Le framework est à présent configuré et prêt à l'emploi.

### Création d'un premier projet

Nous pouvons maintenant créer notre premier projet via la commande suivante :

```bash
sudo wfw create ProjectName /srv/wfw
```

!!! info "Informations"

	Pour plus de détail sur les différentes commandes disponibles en **CLI**, veuillez suivre [ce lien](/cli/wfw/).

### Configuration d'apache

#### Accès au dossier du projet

Pour permettre aux `.htaccess` de correctement rediriger les requêtes arrivant dans le dossier du
projet, créez un fichier de configurations dans `/etc/apache2/sites-available` portant le nom de
votre site (ex : `project-name.com`).

Nous commençons donc par configurer le dossier du projet :
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

Puisque les certificats SSL sont maintenant faciles et gratuits à obtenir (voir [let's encrypt](https://letsencrypt.org/)),
nous allons le configurer par défaut pour rediriger toutes les requêtes non securisées vers le port securisé :

```apache
<VirtualHost *:80>
	DocumentRoot "/srv/wfw/ProjectName"
	ServerName project-name.com
	# Seulement s'il faut rediriger les sous-domaines aussi
	ServerAlias *.project-name.com

	Include /etc/apache2/common/letsencrypt.conf
	RewriteEngine On
	RewriteCond %{HTTPS} off
	RewriteCond %{REQUEST_URI} !^/\.well\-known/acme\-challenge
	RewriteRule (.*) https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]
</VirtualHost>
```
Cette configuration a deux avantages : la première c'est de sécuriser tout le trafique de votre site
ou de votre application, et la seconde c'est qu'elle vous permettra de servir vos pages en
[HTTP2](https://httpd.apache.org/docs/current/fr/howto/http2.html).

??? note "HTTP2 vs HTTP1 et **WFW**"
	Servir les pages en HTTP2 en utilisant **WFW** avec sa configuration de base est vivement recommandé
	 puisque le framework vous encourage à la séparer vos fichiers css/javascript sans passer par des module
	 bundler de type Webpack. Vous pouvez servir vos pages en HTTP1, seulement le téléchargement des
	 ressources sera plus long.

	 Bien entendu, ce comportement par défaut peut-être modifié pour vous permettre d'utiliser un
	 module bundler, voir la section traitant de l'inclusion de [fichiers css et javascript](/general/first_steps/ressources_managers)

??? help "Comment activer HTTP2 dans apache ?"
	L'activation d'HTTP2 n'est pas très difficile mais requiert quand même quelques étapes importantes,
	pas toujours évidentes à trouver sur le net.

	Si vous avez installé apache2 via le gestionnaire de paquets `apt-get`, vous devriez disposer d'un
	binaire `httpd` compilé avec le module `mod_http2`, si ce n'est pas le cas, reportez vous à la
	[documentation d'apache](https://httpd.apache.org/docs/current/fr/howto/http2.html).

	Pour activer HTTP2 et le faire fonctionner avec PHP >= 7.2, veuillez suivre
	[ce tutoriel](https://gist.github.com/GAS85/990b46a3a9c2a16c0ece4e48ebce7300)

??? help "Que contient le fichier  `/etc/apache2/common/letsencrypt.conf` ?"
	Ce fichier de configuration permet un renouvellement automatisé de vos certificats SSL :
	```apache
	Alias /.well-known/acme-challenge/ /srv/www/letsencrypt/.well-known/acme-challenge/
	<Directory "/srv/www/letsencrypt/.well-known/acme-challenge/">
		Options None
		AllowOverride None
		ForceType text/plain
		RedirectMatch 404 "^(?!/\.well-known/acme-challenge/[\w_-]{43}$)"
	</Directory>
	```
	La configuration exacte de let's encrypt ne sera pas traitée plus en détail ici, mais vous pourrez
	trouver toutes les informations nécessaires en suivant cette très bonne série de tutoriels :
	[lets-encrypt-from-start-to-finish](https://blog.wizardsoftheweb.pro/lets-encrypt-from-start-to-finish-overview/)

Voyons à présent la configuration du port 443 :
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

	#Concerne le protocol HTTP2
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
Nous faisons le choix de redigirer toutes les requêtes du sous-domaine `www.project-name.com` vers
la racine. Cette étape peut être omise, si vous précisez ce sous-domaine lors de la création du
certificat avec let's encrypt, ou que vous utilisez un wildcard.

Si vous utilisez une configuration différente, ou d'autres fournisseurs de certificats, pensez à
modifier les configurations en conséquence.

Il ne vous reste plus qu'à activer le site et à redémarrer apache :
```bash
sudo a2ensite project-name.com
sudo systemctl restart apache2
```

## Et maintenant ?

Maintenant que votre framework est installé, que votre serveur est configuré et que votre premier
projet est créé, voyons un peu comment utiliser `wfw` pour commencer le développement dans la
section [Premiers pas](/general/first_steps/overview/).