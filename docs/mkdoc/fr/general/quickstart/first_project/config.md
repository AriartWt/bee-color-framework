## Fichier de configuration

Nous allons commencer par créer le fichier `~/Aperture/site/config/conf.json` :

```json
{
	"server" : {
		"packages": [
			"site/web"
		]
	}
}
```

Pour le moment nous n'avons qu'à indiquer le package que nous utilisons, à savoir `web` disponible
dans le dossier `site`.

!!! note "Note"
	Puisque `wfw` s'occupe des identifiants et mots de passe mysql, msserver et kvs, inutile de les
	préciser ici, ils seront automatiquement ajoutés.

	Pour plus de détail sur le fonctionnement exhaustif des configurations, merci de vous référer à la
	section [correspondante](/general/first_steps/config).

## Contexte

A présent, nous allons définir les paramètres de contexte de notre site internet (pour plus
d'informations, c'est [par ici](/general/first_steps/context)).

### Création du layout

Puisque nous devrons renseigner la classe du layout à utiliser, autant la créer tout de suite,
ainsi que le fichier `view` correspondant :

```php tab="ApertureLayout.php"
<?php
//file ~/Aperture/site/package/web/layouts/ApertureLayout.php
namespace wfw\site\package\web\layouts;

use wfw\engine\core\view\Layout;

class ApertureLayout extends Layout {
	public function __construct(){
		parent::__construct(null);
	}
}
```

```php tab="ApertureLayout.view.php"
<?php
//file ~/Aperture/site/package/web/layouts/ApertureLayout.view.php
?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="initial-scale=1">
	</head>
	<body>
		<?php echo $this->getView()->render(); ?>
	</body>
</html>
```

Ce layout contient le code HTML de base de notre site internet. Toutes les vues que nous créerons
par la suite seront rendues par défaut à l'intérieur de ce layout, à l'endroit où l'appel
`<?php echo $this->getView()->render(); ?>` est effectué.

### site.context.php

Maintenant que notre layout est prêt, nous pouvons créer le fichier
`~/Aperture/site/config/site.context.php` qui permet de définir la classe du
[contexte](/general/first_steps/context) à charger pour notre site internet (index `<? "class"`),
ainsi que les arguments avec lesquels il doit être construit (index `<? "args"`) :

```php
<?php
use wfw\engine\core\app\context\DefaultContext;
use wfw\site\package\web\layouts\ApertureLayout;

return function(array $args = []){
	return [
		//contexte à utiliser.
		"class" => DefaultContext::class,
		"args" => [
			//classe de notre layout
			ApertureLayout::class,
			null,
			null,
			[
				//Renomme le package web en science. De cette manière,
				//toute url commençant par web, sera transformée en
				//une url commençant par science.
				//Ex : web/home sera transformée en science/home
				"science/*" => "web/*",
				//Défini web/home comme la page d'accueil
				"/" => "web/home"
			],
			[
				"fr" => [
					//fichier de langue à charger pour notre projet
					ENGINE."/config/lang/fr.lang.json"
				]
			],
			[],
			[],
			//permet de passer les valeurs de $_SERVER, $_FILE,
			//$_GET et $_POST à notre contexte
			$args["globals"] ?? [],
			null,
			//Définit une url de base (localhost/Aperture dans notre cas)
			//Si vous avez opté pour une configuration locale utilisant un
			//domaine personnalisé, ou si vous migrez votre site vers
			//un serveur de production, pensez à préciser une chaine vide à
			//la place.
			'/Aperture'
		]
	];
};
```

!!!note "Note"
	Pour plus d'informations concernant le fichier `site.context.php`, merci de vous référer à
	la section [correspondante](/general/first_steps/config).

### Inclusion Css

Puisqu'il est plutôt inenvisageable de créer un site internet sans styliser un peu son apparence,
voyons comment charger une feuille de style `css`.

Pour cela, nous allons avoir besoin des classes `CSSManager` et `Router` (pour la résolution des urls).

Puisque **WFW** utilise un [contener d'injection de dépendances](/general/dic), il suffit de les appeler
grâce au nom de leurs interfaces respectives via le constructeur de notre layout.

```php tab="ApertureLayout.php" hl_lines="6 7 10 11 14 15 18 19 22 25 26 27 28 29 30"
<?php
//file ~/Aperture/site/package/web/layouts/ApertureLayout.php
namespace wfw\site\package\web\layouts;

use wfw\engine\core\view\Layout;
use wfw\engine\core\router\IRouter;
use wfw\engine\lib\HTML\resources\css\ICSSManager;

class ApertureLayout extends Layout {
	private $_cssManager;
	private $_router;

	public function __construct(
		ICSSManager $cssManager,
		IRouter $router
	){
		parent::__construct(null);
		$this->_cssManager = $cssManager;
		$this->_router = $router;

		//permet d'inclure le fichier css global sur toutes les pages
		$cssManager->register($router->webroot('Css/web/Style.css'));
	}

	public function getCssImportCallable():callable{
		$cssManager = $this->_cssManager;
		return function(string $key, string $buffer) use ($cssManager):string{
			return str_replace($key,$cssManager->write(),$buffer);
		};
	}
}
```

```php tab="ApertureLayout.view.php" hl_lines="9"
<?php
//file ~/Aperture/site/package/web/layouts/ApertureLayout.view.php
?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="initial-scale=1">
		<?php echo $this->registerPostAction($this->getCSSImportCallable()); ?>
	</head>
	<body>
		<?php echo $this->getView()->render(); ?>
	</body>
</html>
```

```css tab="Style.css"
/* file : ~/Aperture/site/package/web/webroot/Css/Style.css */
html{
	margin:0;
	padding:0;
}
```

!!! help "A quoi sert la méthode `getCssImportCallable()` ?"
	Pour plus d'information sur l'inclusion de fichiers Css/JavaScript, merci de lire la section
	relative aux [gestionnaires de ressources css/JavaScript](/general/first_steps/ressources_managers).

	En ce qui concerne la méthode `registerPostAction()`, vous trouverez les informations
	correspondantes dans la section consacrée aux [vues](/general/first_steps/views).

La configuration de notre site internet étant terminée, nous poouvons passer à la [création des
premières pages](/general/quickstart/first_project/page).