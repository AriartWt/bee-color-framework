Notre page de contact disposera d'un formulaire avec validation des données et une protection contre
d'éventuels spam en utilisant la technique du pot de miel (honeypot), afin d'éviter à nos utilisateurs
d'avoir à remplir un captcha.

!!!note "Note"
	La mise en place de l'anti-spam sur le formulaire s'appuie aussi sur d'autres techniques
	additionnelles que nous détaillerons plus loin.

## Le formulaire

Puisque nous auront besoin de vérifier les entrées de l'utilisateur (rappelez-vous : never trust
user inputs) dans ce formulaire, nous allons commencer par créer ses règles de validation en créant
une classe `ContactRule`, ensuite nous créerons la classe `ContactForm` qui contiendra les
champs de notre formulaire.

Notre formulaire visera a être contacté par des particuliers ou des entreprises vis à vis des services
proposés par Aperture, nous choisissons donc de recueillir les informations suivantes :

- Nom et prénom (requis)
- Nom de l'entreprise (facultatif)
- Adresse e-mail (requis)
- Numéro de téléphone (requis)
- Message (requis)
- Consentement pour l'utilisation des données personnelles (requis quelque soient les informations
à caractère personnel recueillies depuis la mise en place du réglement
[RGPD en europe](https://www.cnil.fr/fr/rgpd-par-ou-commencer))
- Un champ pot de miel, dans notre cas `mobile`, qui provoquera le rejet automatique du formulaire
s'il est rempli.

```php tab="ContactRule.php"
<?php
namespace wfw\site\package\web\security\data;

use wfw\engine\core\security\data\AndRule;
use wfw\engine\core\security\data\IRule;
use wfw\engine\core\security\data\IRuleReport;
use wfw\engine\core\security\data\OrRule;
use wfw\engine\core\security\data\rules\IsBool;
use wfw\engine\core\security\data\rules\IsEmail;
use wfw\engine\core\security\data\rules\IsEmpty;
use wfw\engine\core\security\data\rules\IsPhoneNumber;
use wfw\engine\core\security\data\rules\IsString;
use wfw\engine\core\security\data\rules\MatchRegexp;
use wfw\engine\core\security\data\rules\MaxStringLength;
use wfw\engine\core\security\data\rules\NotEmpty;
use wfw\engine\core\security\data\rules\RequiredFields;

final class ContactRule implements IRule {
	/** @var AndRule $_mainRule */
	private $_mainRule;

	public function __construct() {
		$this->_mainRule = new AndRule(
			"Certaines informations sont invalides !",
			new RequiredFields("Cette information est requise !",
				"name","company","phone","mail","message"
			),
			new IsPhoneNumber(
				"Ceci n'est pas un numéro de téléphone valide !",
				'phone'
			),
			new IsEmail(
				"Ceci n'est pas une adresse mail valide !",
				"mail"
			),
			(new IsBool(
				"Nous avons besoin de votre consentement explicite"
				." pour l'envoi de ce formulaire !",
				"consent")
			)->requireValue(true),
			new NotEmpty(
				"Ce champ ne peut pas être vide !",
				"phone","mail","message"
			),
			new MatchRegexp(
				"/^[a-zA-Z '-]{2,255}$/",
				"Ce champ n'est pas correct",
				'name'
			),
			new MaxStringLength(
				"Votre message ne peut excéder les 20 000 caractères",
				20000,
				"message"
			),
			new OrRule("Si ce champ est précisé, il doit être valide !",
				new MaxStringLength(
					'La longueur de ce champ ne peut excéder 255 caractères',
					255,
					"company"
				),
				new IsEmpty('','company')
			)
		);
	}

	/**
	 * @param array $data Données auxquelles appliquer la règle.
	 * @return IRuleReport
	 */
	public function applyTo(array $data): IRuleReport {
		return $this->_mainRule->applyTo($data);
	}
}
```

```php tab="ContactForm.php"
<?php
namespace wfw\site\package\web\lib\HTML;

use wfw\engine\core\router\IRouter;
use wfw\engine\lib\HTML\helpers\forms\Form;
use wfw\engine\lib\HTML\helpers\forms\HTMLLabel;
use wfw\engine\lib\HTML\helpers\forms\inputs\Checkbox;
use wfw\engine\lib\HTML\helpers\forms\inputs\Text;
use wfw\engine\lib\HTML\helpers\forms\inputs\Textarea;
use wfw\engine\lib\HTML\helpers\forms\validation\Honeypot;
use wfw\engine\lib\HTML\helpers\forms\validation\MinTimeValidity;
use wfw\engine\lib\HTML\helpers\forms\validation\MultiValidationPolicy;
use wfw\site\package\web\security\data\ContactRule;

final class ContactForm extends Form {
	/**
	 * @param string  $errorIcon Icone d'erreur à afficher à côté
	 *                           des champs mal remplis
	 * @param IRouter $router Router
	 * @throws \InvalidArgumentException
	 */
	public function __construct(string $errorIcon, IRouter $router) {
		parent::__construct(
			new ContactRule(),
			$errorIcon,
			new MultiValidationPolicy(
				//on indique que le champ pot de miel est le champ 'mobile'
				new Honeypot("mobile"),
				new MinTimeValidity()
			)
		);
		$this->addInputs(...[
			new Text("name",null,[ "placeholder" => "Nom & prénom*" ]),
			new Text("company",null,[ "placeholder" => "Entreprise" ]),
			new Text("phone",null,[
				"placeholder" => "Téléphone*",
				"type"=>"tel"
			]),
			new Text("mail",null,[
				"placeholder"=>"Mail*",
				"type"=>"mail"
			]),
			new Textarea("message",null,[ "placeholder" => "Message*" ]),
			new Checkbox(
				"consent",
				new HTMLLabel(
					"En soumettant ce formulaire, j'accepte que mes "
					."informations soient enregistrées et utilisées "
					."conformément à vos <a target=\"_blank\" href=\""
					.$router->url("web/legal#confidentialite")
					."\">mentions légales</a>.",
					"rgpd-consent"
				),
				["id"=>"rgpd-consent"]
			),
			new Text("mobile",null,[
				"placeholder" => "Portable",
				"class" => "mobile-phone",
				"type" => "tel"
			])
		]);
	}
}
```

!!!note "Note"
	Vous trouverez plus d'informations sur les formulaires et les règles de validation dans la section
	[dédiée](/general/first_steps/forms).

## La page

Pour créer la vue et la rendre accessible, nous avons besoin, comme
[précédement](/general/quickstart/first_project/page) de trois composants :

- La vue `Contact`
- Le [ResponseHandler](/general/first_steps/handlers#ResponseHandler) `ContactHandler`
- Le fichier Css

```php tab="Contact.php"
<?php
// file : ~/Aperture/site/package/web/views/contact/Contact.php

namespace wfw\site\package\web\views\contact;

use wfw\engine\core\view\View;
use wfw\engine\core\router\IRouter;
use wfw\engine\lib\HTML\resources\css\ICSSManager;

final class Contact extends View{
	private $_form;
	private $_router;
	private $_cssManager;

	public function __construct(
		IRouter $router,
		ISession $session,
		ICSSManager $cssManager
	){
		parent::__construct();
		$this->_cssManager = $cssManager;
		$this->_router = $router;

		//notre formulaire sera stocké dans la session
		//par l'ActionHandler
		$this->_form = $session->get('contact_form');
	}

	public function render():string{
		$this->_cssManager->register(
			$this->_router->webroot("Css/web/contact.css")
		);
		return parent::render();
	}

	public function getRouter():IRouter{
		return $this->_router;
	}

	public function getForm():ContactForm{
		return $this->_form;
	}

	public function infos():array{
		return [
			"title" => "Nous contacter",
			"description" => "N'hésitez pas à nous contacter pour"
			                ." participer à nos recherches dans "
			                ."les salles de test d'Aperture Science."
		];
	}
}
```

```php tab="Contact.view.php"
<?php
// file : ~/Aperture/site/package/web/views/contact/Contact.view.php
$router = $this->getRouter();
$form = $this->getForm();
?>
<h1>Nous contacter</h1>
<form method="post" accept-charset="utf-8"
      action="<?php echo $router->url("web/contact?form_id=".$form->getKey()); ?>">
	<?php echo $form->get("name"); ?>
	<?php echo $form->get("company"); ?>
	<?php echo $form->get("phone"); ?>
	<?php echo $form->get("mobile"); ?>
	<?php echo $form->get("message"); ?>
	<?php echo $form->get("consent"); ?>
	<input type="submit" value="Envoyer">
</form>
```

```php tab="ContactHandler.php"
<?php
// file : ~/Aperture/site/package/web/handlers/response/ContactHandler.php
namespace wfw\site\package\web\handlers\response;

use wfw\engine\core\response\DefaultResponseHandler;
use wfw\site\package\web\views\contact\Contact;

final class HomeHandler extends DefaultResponseHandler{
	/**
	 * @param IViewFactory $factory Permet de créer une vue
	 */
	public function __construct(IViewFactory $factory) {
		parent::__construct($factory, Contact::class);
	}
}
```

```css tab="contact.css"
/* file :  ~/Aperture/site/package/web/webroot/Css/contact.css */
h1{
	color:yellow;
}
```

!!!info "Quelques précisions"
	Nous utilisons deux protections en plus du pot de miel :

	1. MinTimeValidity qui s'assure que le formulaire n'a pas été soumis trop rapidement
	2. Nous ajoutons à l'url l'identifiant du formulaire, pour forcer un utilisateur potentiel
	à afficher notre page. Si l'identifiant est faux, le formulaire sera rejeté. Cette opération
	sera effectuée dans l'ActionHandler à la section suivante et évite qu'un spam-bot envoie des
	requête `POST` sur `web/contact` sans avoir au préalable chargé la page.

## Le ActionHandler



## Utiliser le module `engine/contact`