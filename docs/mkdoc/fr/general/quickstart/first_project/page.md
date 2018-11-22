Maintenant que tout est prêt, nous pouvons commencer à créer notre site internet.

La première chose à faire, c'est de créer la page d'accueil, sur laquelle les utilisateurs arriveront
par défaut s'ils tentent d'accéder à la racine du site.

## La vue

Il nous faut d'abord créer la vue `Home` dans le dossier `~/Aperture/site/package/web/views/home` :

```php tab="Home.php"
<?php
// file : ~/Aperture/site/package/web/views/home/Home.php

namespace wfw\site\package\web\views\home;

use wfw\engine\core\view\View;

final class Home extends View{
	public function __construct(){
		parent::__construct();
	}
}
```

```php tab="Home.view.php"
<?php
// file : ~/Aperture/site/package/web/views/home/Home.view.php
?>
<h1>GLaDOS vous souhaite la bienvenue chez Aperture-science !</h1>
<p>Veillez à ne pas sortir cet appareil de la zone de tests.</p>
```

!!!note "Note"
	Pour plus d'informations sur les vues, merci de vous référer à la section
	[dédiée](/general/first_steps/views).

## Le ResponseHandler

Pour le moment, notre vue n'est pas encore accessible. Pour permettre aux visiteurs de la trouver,
il nous faut définir un [ResponseHandler](/general/first_steps/handlers#ResponseHandler) dans le
dossier `site/package/web/handlers/response`.

Un `ResponseHandler` par défaut peut-être étendu pour éviter d'avoir à écrire trop de code
rébarbatif lorsque l'on souhaite juste servir une vue relativement simple :

```php
<?php
// file : ~/Aperture/site/package/web/handlers/response/HomeHandler.php

namespace wfw\site\package\web\handlers\response;

use wfw\engine\core\response\DefaultResponseHandler;
use wfw\site\package\web\views\home\Home;

final class HomeHandler extends DefaultResponseHandler{
	/**
	 * @param IViewFactory $factory Permet de créer une vue
	 */
	public function __construct(IViewFactory $factory) {
		parent::__construct($factory, Home::class);
	}
}
```

!!!note "Note"
	Pour plus d'informations sur les `ResponseHandler` merci de vous référer à la section
	[dédiée](/general/first_steps/handlers).

Notre page d'accueil est dorénavant accessible via l'url `http://localhost/Aperture` mais aussi
`http://localhost/Aperture/science/home` (comme nous l'avons défini dans le [contexte](config#sitecontextphp)
à l'étape précédente).

## Paramètrage de l'url

Nous aimerions maintenant que notre page `Home` soit accessible via l'url
`http://localhost/Aperture/science/Accueil` pour des raisons de référencement. Pour cela, il nous
faut éditer le fichier de contexte `~/Aperture/site/config/site.context.php` et y ajouter la ligne
suivante :

```php hl_lines="19"
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
				"science/Accueil" => "web/home",
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

!!!warning "Attention"
	L'ordre des directives du `Router` a son importance ! Les règles les plus précises doivet être
	écrites en premier.


## Transmettre des données au layout depuis la vue

Puisque nous parlions justement de référencement à l'étape précédente, une bonne idée serait de
pouvoir modifier la balise `<title></title>` de notre page, ainsi que la balise meta-description
(`<meta name="description" content="" />`) en fonction des vues.

Pour cela, nous pouvons redéfinir la méthode `View::infos()` dans notre vue `Home`, et modifier
le layout `ApertureLayout` pour lui permettre de récupérer les données :

```php tab="Home.php" hl_lines="13 14 15 16 17 18 19 20"
<?php
// file : ~/Aperture/site/package/web/views/home/Home.php

namespace wfw\site\package\web\views\home;

use wfw\engine\core\view\View;

final class Home extends View{
	public function __construct(){
		parent::__construct();
	}

	public function infos():array{
		return [
			"title" => "Page d'accueil",
			"description" => "Bonjour et bienvenue au centre"
			                ." d'enrichissement assisté par ordinateur "
			                ."d'Aperture Science."
		];
	}
}
```

```php tab="ApertureLayout.view.php" hl_lines="3 4 5 12 13"
<?php
//file ~/Aperture/site/package/web/layouts/ApertureLayout.view.php
$infos = $this->getView()->infos();
$title = $infos["title"] ?? "Expert en portails";
$description = $infos["description"] ?? "Laboratoirs de recherche scientifique.";
?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="initial-scale=1">
		<title><?php echo $title." - Aperture Science"; ?></title>
		<meta name="description" content="<?php echo $description; ?>" />
		<?php echo $this->registerPostAction($this->getCSSImportCallable()); ?>
	</head>
	<body>
		<?php echo $this->getView()->render(); ?>
	</body>
</html>
```

!!!hint "Astuce"
	Puisque toutes les vues ne définiront peut-être pas les index `<? "title"` et `<? "description"`,
	nous utilisons l'opérateur `??` de **PHP 7** permettant de tester l'existence d'un index dans un
	tableau et d'utiliser sa valeur en cas de succès.

	Sinon, nous lui assignons une chaîne par défaut.

	Pour plus d'informations, voir
	[la documentation de PHP](http://php.net/manual/fr/migration70.new-features.php#migration70.new-features.null-coalesce-op).

## CSS

Nous avons deux options pour l'inclusion des règles **CSS** sur notre page :

1. Tout écrire dans le fichier global inclus par `ApertureLayout` (`~/Aperture/site/package/web/webroot/Css/Style.css`)
2. Inclure un fichier Css spécifique à chaque page.

Ici, nous opterons pour la seconde solution, qui a l'avantage d'être plus claire et qui ne constituera
pas un problème de performances si vos pages sont bien servies en [HTTP2](https://httpd.apache.org/docs/2.4/fr/howto/http2.html).

Pour ce faire, nous allons créer le fichier `~/Aperture/site/package/web/webroot/Css/home.css` et modifier
notre vue pour l'inclure à l'aide du `CSSManager` en redéfinissant la méthode `View::render` :

```php tab="Home.php" hl_lines="7 8 11 12 15 16 19 20 23 24 25 26 27 28"
<?php
// file : ~/Aperture/site/package/web/views/home/Home.php

namespace wfw\site\package\web\views\home;

use wfw\engine\core\view\View;
use wfw\engine\core\router\IRouter;
use wfw\engine\lib\HTML\resources\css\ICSSManager;

final class Home extends View{
	private $_router;
	private $_cssManager;

	public function __construct(
		IRouter $router,
		ICSSManager $cssManager
	){
		parent::__construct();
		$this->_cssManager = $cssManager;
		$this->_router = $router;
	}

	public function render():string{
		$this->_cssManager->register(
			$this->_router->webroot("Css/web/home.css")
		);
		return parent::render();
	}

	public function infos():array{
		return [
			"title" => "Page d'accueil",
			"description" => "Bonjour et bienvenue au centre"
			                ." d'enrichissement assisté par ordinateur "
			                ."d'Aperture Science."
		];
	}
}
```

```css tab="home.css"
/* file : ~/Aperture/site/package/web/webroot/Css/home.css */
body{
	background-color:black;
	color:white;
}
h1{
	color:red;
}
```

!!!help "Pourquoi redéfinir `View::render` ?"
	Nous aurions pu utiliser le `CSSManager` dans le constructeur de la vue, mais nous n'aurions
	pas été certains de l'ordre dans lequel les fichiers **CSS** auraient été chargés.

	Puisqu'en l'occurence le fichier `home.css` pourrait très bien redéfinir des règles écrites dans
	`Style.css`, nous nous assurons que l'ordre de chargement des fichiers soit celui que l'on attend.

	Pour plus d'informations sur l'inclusion de fichiers CSS, merci de vous référer à la section
	[dédiée](/general/first_steps/ressources_managers).


Maintenant que nous savons créer des pages de contenu, voyons comment créer une
[page de contact](/general/quickstart/first_project/contact) fonctionnelle.