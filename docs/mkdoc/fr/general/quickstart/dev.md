Avant de commencer, je tiens à préciser que je ne suis absolument pas sponsorisé par qui que ce soit
et que la présente section est avant tout là pour vous expliquer ma manière de travailler ainsi que
les outils que j'utilise.

Je vais donc dès à présent parler de la nécessité d'utiliser un IDE, aussi j'invite même les plus
réfractaires d'entre-vous à lire cette petite introduction avec ouverture d'esprit (oui, c'est à
vous que je m'adresse, utilisateurs de `SublimText`, `Notepad` et autre `Atom`...).

Pour les convaincus, vous pouvez immédiatement passer à la section [suivante](#installation-du-framework).

## IDE > all

Maintenant que la guerre est déclarée, voici mon opinion au sujet des IDE :

Leur utilisation est vivement recommandée que ce soit dans le cadre d'un projet simple ou complexe,
avec **WFW** ou n'importe quel autre framework, tant ils encouragent les bonnes pratiques et simplifient
la vie des développeurs en prenant garde pour eux aux erreurs les plus communes et les plus chronophages.

Que ceux qui n'ont jamais perdu une quinzaine de minutes à chercher un foutu point-virgule ou une satannée
faute de frappe du style : `Enviromnent` au lieu de `Environment` lèvent le doigt (croyez moi, je suis
le premier à le lever).

Ayant pour ma part longuement utilisé `SublimeText 3` avec différents modules, je dois avouer que
la migration vers **PHPStorm** m'a été réellement salutaire autant sur un plan qualitatif que productif.
Je sais que pour nous autres, développeurs, les habitudes sont souvent le plus grand des freins face
au changement, mais prenez le temps d'y penser et d'essayer un véritable IDE.

Pour ma part, je ne regrette absolument pas l'argent dépensé dans l'achat d'une license. Rien que pour
faire du *refactoring* à petite ou grande echelle, un éditeur avancé tel que SublimText ne fait
absolument pas le poids.

## Installation du framework

Suivez la procédure normale d'[installation du framework](/general/start), en omettant la partie
configuration d'apache SSL et HTTPS.

Personellement, je vous recommande juste de rendre accessible à apache le dossier `srv/wfw` comme
root sur le port 80 en éditant le fichier `/etc/apache2/sites-available/000-defaut.conf` pour
ajouter la ligne suivante :
```apache hl_lines="3"
<VirutalHost *:80>
	#...
	DocumentRoot /srv/wfw/
	#...
</VirtualHost>
```

De cette manière, pour accéder à un projet en cours, il vous suffira de faire votre test via l'url
`localhost/ProjectName`

## Configuration de l'IDE

L'exemple ici sera (sans surprise) [PHPStorm](https://www.jetbrains.com/phpstorm/), je vous invite à
faire le lien avec votre propre IDE si vous en utilisez un autre.

Pour ma part, je travail exclusivement sous `ubuntu`, pour tout un tas de raisons que je n'exposerais
pas ici. Vous pouvez développer sous `windows` et faire vos tests dans une machine virtuelle sans trop
de problème en créant un dossier partagé entre `windows` et `ubuntu` (sous **VMWare**, c'est relativement
simple).

Ainsi lancer la commande `sudo wfw import ProjectName /mnt/hgfs/shared/ProjectName` depuis votre
machine virtuelle lorsque vous voulez tester vos modifications devrait sans aucun vous satisfaire.

### Framework

#### Importer le code source

Pour bénéficier de l'auto-complétion de votre IDE, commencez par télécharger les sources du framework :
```bash
cd ~
git clone https://framagit.org/Ariart/bee-color-framework.git
```

Puis importez le projet dans votre IDE : `File->New Project From Existing Files`
et conservez le choix par défaut:

>	Web server is installed localy, source files are located inside its document root

Puis choisissez le dossier du framework `~/bee-color-framework`.
Enfin créez un serveur local s'il n'en existe pas, choisissez en un existant sinon.

!!!note "Note"
	Ce choix n'aura aucune incidence sur la suite puisqu'il n'y a aucun intérêt à tenter d'accéder au
	framework seul depuis votre navigateur.

A présent, laissez vide le champ suivant (`web path for project root '...'`) et cliquez enfin sur `Finish`.

Dans la boîte de dialogue qui s'ouvre, choisissez `Open in current window` et cochez la case
`Add to currently opened projetcs`

#### Reconaissance du namespace

Par défaut, **PHPStorm** ne fait pas le lien entre le dossier du framework et le namespace de base
du framework `wfw`.

Pour le lui préciser, rendez-vous dans les paramètres de **PHPStorm** `File->Settings`,
puis dans `Directories`.

Choisissez le dossier `bee-color-framework` et cliquez sur l'icone `Source`.

Tout à droite, cliquez sur l'icone d'édition juste en dessous de `Source folder` et entrez
`wfw` dans la boite de dialog qui s'ouvre puis validez.

Maintenant que vous bénéficiez de l'auto-complétion pour tout vos projets, voyons la configuration
d'un nouveau projet.

### Nouveau projet

#### Importation du projet

Disons que vous shouaitez créer un nouveau projet nommé `Aperture`, vous créez donc un nouveau
répertoire dans votre home :
```bash
cd ~
mkdir Aperture
```

Puis importez le projet dans votre IDE : `File->New Project From Existing Files` et conservez le choix
par défaut :
>	Web server is installed localy, source files are located inside its document root

Puis choisissez le dossier du projet `~/Aperture`.

Enfin créez un serveur local s'il n'en existe pas, choisissez en un existant sinon.
!!!hint "Astuce"
	Là, il y a deux manières de procéder :

	- Soit vous créez un serveur `localhost` pour tout vos projets pour y accéder via une url de type
	`https://localhost/Aperture`, auquel cas vous devrez indiquer le nom de votre projet dans le champ
	`web path for project root '...'` à l'étape suivante.
	- Soit vous manipulez les fichiers `/etc/hosts` et créez un domaine par projet, auquel cas vous
	créez également un serveur par projet dans **PHPStorm** et vous devrez laisser le champ
	`web path for project root '...'` vide à l'étape suivante.

	Personnellement j'utilise la première solution que je trouve plus simple.

Cliquez sur `Finish`.

Dans la boîte de dialogue qui s'ouvre, choisissez `Open in current window` et cochez la case
`Add to currently opened projetcs`

#### Configuration du namespace

Comme pour la configuration du dossier du framework, il faut indiquer à **PHPStorm** le namespace de
votre projet.

Rendez-vous dans les paramètres de **PHPStorm** `File->Settings`, puis dans `Directories`.

Choisissez le dossier `Aperture` et cliquez sur l'icone `Source`.

Tout à droite, cliquez sur l'icone d'édition juste en dessous de `Source folder` et entrez
`wfw` dans la boite de dialog qui s'ouvre puis validez.

Maintenant, tout les fichiers que vous créerez seront bien sous le namespace `wfw\...`.

## Autocomplétion et documentation des librairies JavaScript

Puisque **WFW** fourni un micro-framework javascript, vous pouvez également bénéficier de l'autocomplétion
en suivant la procédure suivante :

Ouvrez les paramètres de **PHPStorm** `File->Settings` puis rendez-vous dans la section
`Languages & Framework` puis `JavaScript`.

Dans le panneau de droite, cliquez sur le bouton `Add`.

Dans la boîte de dialogue qui s'ouvre, entrez `wfw` comme nom, choisissez la visibilité `global`
puis cliquez sur le bouton <span style="color:green;font-size:130%;">+</span> et choisissez `Attach directories`.

Choisissez le dossier `~/bee-color-framework/docs/wfw_js` et validez.

Cliquez sur `Ok` puis `Apply`.

Votre environnement de développement est maintenant prêt à l'emploi.