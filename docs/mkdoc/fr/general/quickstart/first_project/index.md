Cette section vise à vous permettre d'utiliser rapidement **WFW** pour créer un site web, sans
rentrer dans les détails de son fonctionnement.

A la fin de cette section vous serez capable de créer de nouvelles pages, d'utiliser un `layout` et
d'activer le panneau d'administration par défaut.

!!! note "Note"
	Pour pouvoir suivre les différentes explications présentes sur cette page, il vous faut avoir suivi
	la [procédure d'installation](/general/start.md) au préalable.

	De même, pensez à jeter un coup d'oeil à la section [environnement de développement](/general/quickstart/dev),
	particulièrement utile pour profiter d'un IDE tel que [PHPStorm](https://www.jetbrains.com/phpstorm/)
	et vous permettre de travailler avec efficacité.

## Sommaire

Voici le sommaire des différentes étapes :

- [Création du projet](/general/quickstart/first_project/#creation-du-projet)
- [Configuration](/general/quickstart/first_project/config)
- [Création du layout](general/quickstart/first_project/layout)
- [Création d'une page](general/quickstart/first_project/page)
- [Login et restrictions d'accés](general/quickstart/first_project/access)
- [Activer le panneau d'administration](general/quickstart/first_project/admin_panel)

## Création du projet

A partir d'ici, nous considérons que vous avez [installé](/general/start.md) **WFW** et que vous
avez suivis les conseils de la section [environnement de développement](/general/quickstart/dev). Si
ce n'est pas le cas, pensez à adapter les différents exemples.

Trève de bavardage, prenons l'exemple concret de la création d'un site internet pour un projet que
nous appellerons "Aperture" :

```bash
sudo wfw create Aperture /srv/wfw
```

Maintenant que notre projet peut-être géré par **WFW**, nous pouvons créer le dossier du projet dans
notre IDE préféré. Ici, nous allons considérer que c'est le répertoire `~/Aperture` :

```bash
cd ~
mkdir Aperture
```

???help "Pourquoi ne pas travailler dans le dossier du projet ?"
	La réponse à cette question est simple : pour des questions de permissions.
	Etant donné que `wfw` attribue automatiquement les projet à l'utilisateur d'apache (`www-data` par
	défaut), ouvrir, créer et modifier les fichiers dans votre IDE peut devenir pénible.

	C'est pourquoi nous préférerons séparer les dossiers des projets, d'autant que cela vous permettra
	également de découpler les fichiers de votre projets et ceux du framework, voir même de remplacer
	les fichiers du framework si nécessaire. Pour plus d'informations, voir la section expliquant
	l'[arborescence](/general/first_steps/tree).

Par la suite, à chaque fois que nous voudrons constater les changement effectués à notre site internet,
nous utiliserons la commande suivante :
```bash
sudo wfw import Aperture ~/Aperture
```

Créons maintenant le dossier qui contiendra notre site (ou application), nommé sobrement `site`, ainsi
 que son arborescence de base :

```
/site
	/config
		conf.json
		site.context.php
	/package
		/web
			/handlers
				/action
				/response
			/layouts
			/views
			/webroot
```

???note "Note"
	Pour plus d'informations, voir la section expliquant l'[arborescence](/general/first_steps/tree).

Maintenant que nous sommes prêts à travailler, rendez-vous dans la section
[Configuration](/general/quickstart/first_project/config)