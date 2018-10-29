# Prérequis

Voici la liste des prérequis pour l'installation du framework : 
  
  - PHP >= 7.2 avec les extensions `php7.2-common php7.2-curl php7.2-xml php7.2-zip php7.2-gd php7.2-mysql php7.2-mbstring php-apcu` et `pcntl`
  - MYSQL & MysqlDump
  - Accès root sur Ubuntu >= 16.04
  - Apache >= 2.4 avec les modules `rewrite deflate headers filter`
  
??? note "Remarque sur Ubuntu"
    
    En principe, du moment que l'extention pcntl est disponible sur **PHP** (ce qui exclus les plateformes
    Windows), et que le système permet l'installation de daemons via **systemctl**, tout devrait bien se 
    passer.
    Par ailleurs, il est tout à fait possible de remplacer le script d'installation par celui de votre
    choix, du moment que installez correctement les trois daemons nécessaires au fonctionnement du 
    framework.
    
    Je laisse cependant ce prérequis pour le moment, puisque je n'ai pas encore eu la possibilité de
    le tester sur un autre environnement.
    
??? note "Remarque sur Apache"

    **WFW Bee-color** utilise des .htaccess pour permettre les redirections et les accés aux dossiers
    publics (appelés `webroot`). Pour l'instant, un portage **Nginx** n'est pas encore prévu.

## Installation

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
    
Maintenant, il nous faut créer un utilisateur MYSQL qui a les permissions necessaires à la création
d'autres utilisateur, et de base de données.

??? warning "Attention"

    La plupart d'entre-vous le sait déjà, mais je pense que c'est toujours bon a rappeler : 
    
    Etant donné les droits conférés à cet utilisateur, pensez à lui choisir un mot de passe 
    sécurisé, et si possible à lui interdire les connexion depuis l'extérieur si votre instance 
    de Mysql tourne en local.
        
    ??? hint "Astuce"
        
        Si vous utilisez Phpmyadmin, vous pouvez éditer le fichier `/etc/phpmyadmin/conf.inc.php`,
        déplacez-vous jusqu'au premier `<? $i++; ` et ajoutez les lignes suivantes : 
        ``` 
            <?
            $cfg['Servers'][$i]['AllowDeny']['order'] = 'explicit'; //interdit l'accés à tous les utilisateurs
            $cfg['Servers'][$i]['AllowDeny']['rules'] = [
                'allow apublicuser from all' //l'utilisateur que vous souhaitez autoriser, s'il y en a
            ];
        ```
        