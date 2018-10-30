# WFW

!!! warning "En cours de rédaction"

	Cette page est encore en cours de construction

Une fois le framework [installé](general/start), le programme `wfw` est disponible. Il permet de
gérer l'instance globale du framework, d'ajouter, supprimer, mettre à jour des projets et exécuter
quelques commandes spécifiques à un projet.

## Création d'un projet

L'utilitaire `wfw` permet de gérer simplement les nouveaux projets.
Il se charge pour vous des opérations suivantes, ce qui simplifie grandement le processus :

- Créer le squelette du projet.
- Copier des dossiers `/srv/wfw/global/engine`, `/srv/wfw/global/cli`, `/srv/wfw/global/daemons` vers le dossier du
projet.
- Créer les liens symboliques nécessaires à la gestion du projet par `wfw`.
- Créer la base de données et deux utilisateurs qui peuvent agir dessus.
- Créer le [container kvs](daemons/kvs) et son utilisateur.
- Créer l'[instance msserver](daemons/msserver) et son utilisateur.
- Éditer tous les fichiers de configurations impactés.
- Créer un premier utilisateur actif pour votre application.

Pour créer un projet sur notre framework fraîchement installé, il n'y a donc rien de plus simple, il suffit
 d'avoir recours à la commande `wfw create [Nom du projet] [chemin absolu vers son répertoire d'installation]`

``` bash
sudo wfw create ProjectName /srv/wfw
```

??? help "Pourquoi le chemin est-il obligatoire ?"

    Pour des raisons de flexibilité, le dossier dans lequel vous créez votre projet peut très bien se
    trouver n'importe où sur le système.

    Peu importe votre configuration, pensez bien à utiliser un chemin **absolu** lors de la commande,
    et faites attention à ce que le dossier de destination appartienne à l'utilisateur www-data avec
    le droit d'exécution.

À la fin de l'installation, un fichier ProjectName.cred sera créé dans le dossier temporaire spécifié par les
configurations.
Il contient sur la première ligne le login du premier utilisateur créé pour votre projet,
 ainsi que son mot de passe, généré automatiquement à partir d'un **UUID v4**.

!!! warning "Attention"

    Si une base de données correspondant à la base qui sera créée par `wfw` existe déjà,
    elle sera silencieusement supprimée et remplacée par une base de données propre.

    Idem pour les utilisateurs.

    Ce fonctionnement est normal et particulièrement utile dans le cas d'une création de projets
    qui se serait mal déroulée notamment à cause d'un problème de permissions. Mais si vous
    souhaitez réinstaller un projet ET conserver ses données, pensez à effectuer un [backup]()
    avant, sinon vos données seront perdues.

## Importation et mise à jour d'un projet

Les commandes pour l'import et la mise à jour d'un projet sont les mêmes, puisqu'elles passent
par les mêmes étapes : `wfw import [Nom du projet] [chemin absolu vers les sources]`

### Dans un projet existant

La commande est très simple. Il vous suffit de préciser le nom du projet, et le chemin **absolu**
d'accès aux sources permettant son import ou sa mise à jour :
```bash
sudo wfw import ProjectName ~/projectname
```

Les configurations seront automatiquement éditées par `wfw` pour faire correspondre les mots
de passe et les utilisateurs mysql, kvs et msserver.


### Si le projet n'a pas encore été créé via `wfw create`

Il faut d'abord le créer via la commande :

```bash
sudo wfw create ProjectName /srv/wfw
```

Puis l'importer en fournissant le chemin **absolu** vers les sources à utiliser pour l'import :
```bash
sudo wfw import ProjectName ~/projectname
```

!!! info "Information sur les données"

	 L'import des données **MYSQL**, s'il y en, n'est pas pris en charge par `wfw` et doit être réalisé
	 manuellement.

## Supprimer un projet

La commande pour supprimer un projet est la suivante : `wfw remove [Nom du projet]`

```bash
sudo wfw remove ProjectName
```

!!! warning "Attention"

    L'utilitaire ne supprime pas la base de données afin d'éviter qu'une erreur de manipulation
    ne tourne au désastre en supprimant toutes les données associées à votre projet.

    Il ne supprime pas non plus le dossier du projet à l'emplacement où vous l'avez installé. Il
    ne fait que supprimer les liens symboliques lui permettant de gérer ce projet, et de nettoyer
    les utilisateurs du msserver et du kvs.

    Pour le supprimer totalement, vous devez effectuer les commandes manuelles nécessaires :
    ```bash
    sudo rm -rf /srv/wfw/ProjectName
    ```
    Puis la suppression de la base de données et des utilisateurs qui avaient été générés par
    `wfw create`

    ??? hint "Astuce : suppression accidentelle"

        Si vous supprimez par erreur un projet, pas de panique. Commencez par déplacer son dossier
        dans un autre répertoir :

        ```bash
        sudo mv /srv/wfw/ProjectName ~
        ```

        Si vous avez des données à conserver, utilisez mysqldump :

        ```bash
        mysqldump -u root -p[root_password] [database_name] > ~/dumpfilename.sql
        ```

        Récréez et importer votre projet :
        ```bash
        sudo wfw create ProjectName /srv/wfw
        sudo wfw import ProjectName ~/ProjectName
        ```

        Restaurez vos données :

        ```bash
        mysql -u root -p[root_password] [database_name] < ~/dumpfilename.sql
        ```

## Mettre à jour le framework

Mettre à jour le framework est aussi simple qu'importer un projet. `wfw` intègre une commande `update`
similaire à `import`.

Commencez par télécharger la dernière version du framework, puis utilisez la commande update après
avoir supprimé le dossier `.git`.

```bash
cd ~
git clone git@framagit.org:Ariart/bee-color-wfw.git
rm -rf bee-color-wfw/.git
sudo wfw update -all ~/bee-color-wfw
```

Vous pouvez *remplacer* l'argument `-all` par les différents arguments suivants, en fonction de vos
 besoins :

- `-all` : le framework et toutes ses instances.
- `-self` : uniquement le framework
- `-ProjectName` : le projet ProjectName
- `-self,ProjectName1,ProjectName3` : le framework ainsi que les projets ProjectName1 et ProjectName3.

La cohabitation entre plusieurs projets ayant différentes versions du framework de base est
prévue dans le cas où des changements dans les sources entraînent une incompatibilité avec les versions ultérieures.

Chaque projet ayant sa propre copie des sources pour son propre fonctionnement, cela permet à chaque
 projet de fonctionner indépendamment des autres.

!!! note "Note"

	 En toute logique, tant que les changements n'affectent pas l'interface client du [KVS](/daemons/kvs/)
	 ou celle du [MSServer](/daemons/msserver/), vous ne risquez rien à mettre à jour le framework sans
	 mettre à jour les projets.

 Le fait de le garder à jour vous permettra de créer de nouveaux projets à partir de la nouvelle
 version du framework.