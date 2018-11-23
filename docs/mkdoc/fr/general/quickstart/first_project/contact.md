Notre page de contact disposera d'un formulaire avec validation des données et une protection contre
d'éventuels spam-bots en utilisant la technique du pot de miel (honeypot), afin d'éviter à nos visiteurs
d'avoir à remplir un captcha.

!!!note "Note"
	La mise en place de l'anti-spam sur le formulaire s'appuie aussi sur d'autres techniques
	additionnelles que nous détaillerons plus loin.

## Le formulaire

Puisque nous auront besoin de vérifier les entrées des visteurs (rappelez-vous : never trust
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

Nous en profiterons également pour changer l'url dans le fichier `~/Aperture/site/config/site.context.php`
afin que la page soit accessible depuis l'url `http://localhost/Aperture/science/Nous-contacter`.

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

```php tab="site.context.php" hl_lines="20"
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
				"science/Nous-contacter" => "web/contact",
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

Puisque le rôle des `ResponseHandler` est simplement de désigner la vue à charger en fonction de la
requête et que le fait d'enregistrer une demande de contact est une action d'un visiteur sur le site,
nous utiliserons un `ActionHandler` pour traiter les données envoyées en `POST`.

Pour le moment, nous allons simplement envoyer un mail lorsqu'un visiteur soumet un formulaire valide :

- L'adresse mail à laquelle sera envoyé le message sera l'adresse de contact d'Aperture :
`contact@aperture.fr`.
- L'adresse mail de l'éxpéditeur sera une adresse de type noreply : `noreply@aperture.fr`.

Pour cela, nous allons créer l'`ActionHandler` `ContactHandler` qui se chargera de
 valider et traiter les données entrées par l'utilisateur.
 Le handler doit être ajouté au dossier `~/Aperture/site/package/web/handlers/action` :

```php
<?php
// file : ~/Aperture/site/package/web/handlers/action/ContactHandler
namespace wfw\site\package\web\handlers\action;

use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHandler;
use wfw\engine\core\request\IRequest;
use wfw\engine\core\request\IRequestData;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\Redirection;
use wfw\engine\core\response\responses\StaticResponse;
use wfw\engine\core\router\IRouter;
use wfw\engine\core\session\ISession;
use wfw\engine\lib\HTML\helpers\forms\errors\FormValidationPolicyFailure;
use wfw\engine\lib\network\mail\EmailSubject;
use wfw\engine\lib\network\mail\IMailProvider;
use wfw\engine\lib\network\mail\Mail;
use wfw\engine\lib\network\mail\MailBody;
use wfw\engine\lib\network\mail\NamedEmail;
use wfw\engine\package\general\domain\Email;
use wfw\site\package\web\lib\HTML\ContactForm;

final class ContactHandler implements IActionHandler {
	private $_session;
	private $_form;
	private $_errorIcon;
	private $_provider;
	private $_router;

	public function __construct(
		ISession $session,
		IRouter $router,
		IMailProvider $provider
	) {
		$this->_session = $session;
		$this->_router = $router;
		$this->_provider = $provider;
		$this->_errorIcon = $router->webroot('Image/Icons/delete.png');

		//S'il existe déjà un formulaire dans la session, on l'utilise
		//Sinon on le crée
		if(!$session->exists('contact_form')){
			$this->_form = $this->createForm();
			$session->set('contact_form',$this->_form);
		}else $this->_form = $session->get('contact_form');
	}

	private function createForm():ContactForm{
		return new ContactForm($this->_errorIcon,$this->_router);
	}

	public function handle(IAction $action): IResponse {
		//Si le formulaire est posté, on traite les données
		//Sinon on re-génère le formulaire.
		if($action->getRequest()->getMethod() === IRequest::POST) {
			//On récupère les données postées
			$data = $action->getRequest()->getData()->get(
				IRequestData::POST,
				true
			);

			//On récupère la clé de formulaire. Si la clé n'est pas fournie, on
			// lui substitue une chaîne vide.
			$formKey = $action->getRequest()->getData()->get(
				IRequestData::GET,
				true
			)["form_id"]??'';

			try{
				//La classe Form vérifie la validité de la clé
				//et des données.
				//Il applique également sa police de validation
				if($this->_form->validates($data,$formKey))
					$this->sendMail();
			}catch(FormValidationPolicyFailure $e){
				//Si l'une des FormValidationPolicy échoue, elle lève
				//une exception.
				//Il s'agit peut-être d'un spam, ou d'une seconde
				//soumission accidentelle
				return new Redirection("web/contact");
			}
		}else $this->_session->set('contact_form',$this->createForm());
		return new StaticResponse($action);
	}

	private function sendMail():void{
		$message ="Nom & prénom : ".$this->_form->get("name")->getData()."\n"
			."Entreprise : ".$this->_form->get("company")->getData()."\n"
			."Téléphone : ".$this->_form->get("phone")->getData()."\n"
			."Consentement mentions légales et données personnelles : oui\n"
			."Mail : ".$this->_form->get("mail")->getData()."\n\n"
			."Message : \n\t".$this->_form->get("message")->getData();
		try{
			$mailMessage="Bonjour,\nVous recevez ce mail parce qu'une "
			 ."personne souhaite vous contacter par le biais du formulaire"
			 ." de contact de votre site internet."
			 ."\n\nContenu du message et coordonnées :\n\n$message\n\n"
			 ."Important : Ceci est un mail automatique, merci de ne pas"
			 ." y répondre."
			 ."\n\n En vous souhaitant une bonne journée,"
			 ."\nCordialement,\nGLaDOS";
			$this->_provider->send(new Mail(
				new NamedEmail(new Email("noreply@aperture.fr")),
				[ new NamedEmail(new Email("contact@aperture.fr")) ],
				[],
				[],
				[],
				[],
				new EmailSubject(
					"Un visiteur souhaite vous contacter "
					."(via le formulaire général)"
				),
				new MailBody($mailMessage,null,false)
			));
		}catch(\Exception $e){}
		$this->_session->set('contact_form',$this->createForm());
	}
}
```

!!!note "Note"
	Pour plus d'information sur les `ActionHandler`, merci de consulter la section
	[dédiée](/general/first_steps/handlers#ActionHandler).

## Utiliser le module `engine/contact`

Puisque **WFW** embarque un module gérant les prises de contact via les formulaires, voyons
comment le mettre en place pour permettre à un membre d'Aperture de les voir apparaître
dans son panneau d'administration.

!!!info "Information"
	Cette fonctionnalité permet à l'administrateur du site de garder une trace de toutes les demandes
	de contact effectuées via son site internet.

	Ainsi, même si l'envoi du mail échoue pour une raison ou une autre, la prise de contact est
	archivée et consultable.

Pour cela, nous allons modifier légèrement notre classe pour demander au module d'enregistrer la prise
de contact. Il suffit de demander dans le constructeur l'interface `ICommandBus` et de lui
faire éxécuter la commande `CreateContact` :

```php hl_lines="7 21 22 23 30 39 43 100 101 102 103"
<?php
// file : ~/Aperture/site/package/web/handlers/action/ContactHandler
namespace wfw\site\package\web\handlers\action;

use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHandler;
use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\request\IRequest;
use wfw\engine\core\request\IRequestData;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\Redirection;
use wfw\engine\core\response\responses\StaticResponse;
use wfw\engine\core\router\IRouter;
use wfw\engine\core\session\ISession;
use wfw\engine\lib\HTML\helpers\forms\errors\FormValidationPolicyFailure;
use wfw\engine\lib\network\mail\EmailSubject;
use wfw\engine\lib\network\mail\IMailProvider;
use wfw\engine\lib\network\mail\Mail;
use wfw\engine\lib\network\mail\MailBody;
use wfw\engine\lib\network\mail\NamedEmail;
use wfw\engine\package\contact\command\CreateContact;
use wfw\engine\package\contact\domain\ContactInfos;
use wfw\engine\package\contact\domain\ContactLabel;
use wfw\engine\package\general\domain\Email;
use wfw\site\package\web\lib\HTML\ContactForm;

final class ContactHandler implements IActionHandler {
	private $_session;
	private $_form;
	private $_bus;
	private $_errorIcon;
	private $_provider;
	private $_router;

	public function __construct(
		ISession $session,
		IRouter $router,
		IMailProvider $provider,
		ICommandBud $bus
	) {
		$this->_session = $session;
		$this->_router = $router;
		$this->_bus = $bus;
		$this->_provider = $provider;
		$this->_errorIcon = $router->webroot('Image/Icons/delete.png');

		//S'il existe déjà un formulaire dans la session, on l'utilise
		//Sinon on le crée
		if(!$session->exists('contact_form')){
			$this->_form = $this->createForm();
			$session->set('contact_form',$this->_form);
		}else $this->_form = $session->get('contact_form');
	}

	private function createForm():ContactForm{
		return new ContactForm($this->_errorIcon,$this->_router);
	}

	public function handle(IAction $action): IResponse {
		//Si le formulaire est posté, on traite les données
		//Sinon on re-génère le formulaire.
		if($action->getRequest()->getMethod() === IRequest::POST) {
			//On récupère les données postées
			$data = $action->getRequest()->getData()->get(
				IRequestData::POST,
				true
			);

			//On récupère la clé de formulaire. Si la clé n'est pas fournie, on
			// lui substitue une chaîne vide.
			$formKey = $action->getRequest()->getData()->get(
				IRequestData::GET,
				true
			)["form_id"]??'';

			try{
				//La classe Form vérifie la validité de la clé
				//et des données.
				//Il applique également sa police de validation
				if($this->_form->validates($data,$formKey))
					$this->sendMail();
			}catch(FormValidationPolicyFailure $e){
				//Si l'une des FormValidationPolicy échoue, elle lève
				//une exception.
				//Il s'agit peut-être d'un spam, ou d'une seconde
				//soumission accidentelle
				return new Redirection("web/contact");
			}
		}else $this->_session->set('contact_form',$this->createForm());
		return new StaticResponse($action);
	}

	private function sendMail():void{
		$message ="Nom & prénom : ".$this->_form->get("name")->getData()."\n"
			."Entreprise : ".$this->_form->get("company")->getData()."\n"
			."Téléphone : ".$this->_form->get("phone")->getData()."\n"
			."Consentement mentions légales et données personnelles : oui\n"
			."Mail : ".$this->_form->get("mail")->getData()."\n\n"
			."Message : \n\t".$this->_form->get("message")->getData();
		$this->_bus->execute(new CreateContact(
			new ContactLabel("Formulaire général"),
			new ContactInfos($message)
		));
		try{
			$mailMessage="Bonjour,\nVous recevez ce mail parce qu'une "
			 ."personne souhaite vous contacter par le biais du formulaire"
			 ." de contact de votre site internet."
			 ."\n\nContenu du message et coordonnées :\n\n$message\n\n"
			 ."Important : Ceci est un mail automatique, merci de ne pas"
			 ." y répondre."
			 ."\n\n En vous souhaitant une bonne journée,"
			 ."\nCordialement,\nGLaDOS";
			$this->_provider->send(new Mail(
				new NamedEmail(new Email("noreply@aperture.fr")),
				[ new NamedEmail(new Email("contact@aperture.fr")) ],
				[],
				[],
				[],
				[],
				new EmailSubject(
					"Un visiteur souhaite vous contacter "
					."(via le formulaire général)"
				),
				new MailBody($mailMessage,null,false)
			));
		}catch(\Exception $e){}
		$this->_session->set('contact_form',$this->createForm());
	}
}
```

L'administrateur du site Aperture verra maintenant chaque nouvelle prise de contact dans son panneau
d'administration, désactivé par défaut.

## Notifications temporaires échec/succés

Avant d'activer le panneau d'administration pour voir les messages déposés par les
visiteurs, nous allons modifier légèrement nos différents fichiers pour afficher au dessus du
formulaire un message pour indiquer à l'utilisateur que sa demande a bien été prise en compte,
ou pour lui signifer que des erreurs ont été trouvées dans le remplissage du formulaire,
le cas échéant.

Pour cela, nous allons faire appel au `INotifier`. Il nous faut modifier légèrement la vue `Contact`
ainsi que l'`ActionHandler` `ContactHandler` :

```php tab="Contact.php" hl_lines="8 14 20 26 48 49 50"
<?php
// file : ~/Aperture/site/package/web/views/contact/Contact.php

namespace wfw\site\package\web\views\contact;

use wfw\engine\core\view\View;
use wfw\engine\core\router\IRouter;
use wfw\engine\core\notifier\INotifier;
use wfw\engine\lib\HTML\resources\css\ICSSManager;

final class Contact extends View{
	private $_form;
	private $_router;
	private $_notifier;
	private $_cssManager;

	public function __construct(
		IRouter $router,
		ISession $session,
		INotifier $notifier,
		ICSSManager $cssManager
	){
		parent::__construct();
		$this->_cssManager = $cssManager;
		$this->_router = $router;
		$this->_notifier = $notifier;

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

	public function getNotifier():INotifier{
		return $this->_notifier;
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

```php tab="Contact.view.php" hl_lines="7"
<?php
// file : ~/Aperture/site/package/web/views/contact/Contact.view.php
$router = $this->getRouter();
$form = $this->getForm();
?>
<h1>Nous contacter</h1>
<div class="form-infos"><?php echo $this->getNotifier()->print(); ?></div>
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

```php tab="ContactHandler.php" hl_lines="8 9 36 43 48 87 88 89 135 136 137 138"
<?php
// file : ~/Aperture/site/package/web/handlers/action/ContactHandler
namespace wfw\site\package\web\handlers\action;

use wfw\engine\core\action\IAction;
use wfw\engine\core\action\IActionHandler;
use wfw\engine\core\command\ICommandBus;
use wfw\engine\core\notifier\INotifier;
use wfw\engine\core\notifier\Message;
use wfw\engine\core\request\IRequest;
use wfw\engine\core\request\IRequestData;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\Redirection;
use wfw\engine\core\response\responses\StaticResponse;
use wfw\engine\core\router\IRouter;
use wfw\engine\core\session\ISession;
use wfw\engine\lib\HTML\helpers\forms\errors\FormValidationPolicyFailure;
use wfw\engine\lib\network\mail\EmailSubject;
use wfw\engine\lib\network\mail\IMailProvider;
use wfw\engine\lib\network\mail\Mail;
use wfw\engine\lib\network\mail\MailBody;
use wfw\engine\lib\network\mail\NamedEmail;
use wfw\engine\package\contact\command\CreateContact;
use wfw\engine\package\contact\domain\ContactInfos;
use wfw\engine\package\contact\domain\ContactLabel;
use wfw\engine\package\general\domain\Email;
use wfw\site\package\web\lib\HTML\ContactForm;

final class ContactHandler implements IActionHandler {
	private $_session;
	private $_form;
	private $_bus;
	private $_errorIcon;
	private $_provider;
	private $_router;
	private $_notifier;

	public function __construct(
		ISession $session,
		IRouter $router,
		IMailProvider $provider,
		ICommandBud $bus,
		INotifier $notifier
	) {
		$this->_session = $session;
		$this->_router = $router;
		$this->_bus = $bus;
		$this->_notifier = $notifier;
		$this->_provider = $provider;
		$this->_errorIcon = $router->webroot('Image/Icons/delete.png');

		//S'il existe déjà un formulaire dans la session, on l'utilise
		//Sinon on le crée
		if(!$session->exists('contact_form')){
			$this->_form = $this->createForm();
			$session->set('contact_form',$this->_form);
		}else $this->_form = $session->get('contact_form');
	}

	private function createForm():ContactForm{
		return new ContactForm($this->_errorIcon,$this->_router);
	}

	public function handle(IAction $action): IResponse {
		//Si le formulaire est posté, on traite les données
		//Sinon on re-génère le formulaire.
		if($action->getRequest()->getMethod() === IRequest::POST) {
			//On récupère les données postées
			$data = $action->getRequest()->getData()->get(
				IRequestData::POST,
				true
			);

			//On récupère la clé de formulaire. Si la clé n'est pas fournie, on
			// lui substitue une chaîne vide.
			$formKey = $action->getRequest()->getData()->get(
				IRequestData::GET,
				true
			)["form_id"]??'';

			try{
				//La classe Form vérifie la validité de la clé
				//et des données.
				//Il applique également sa police de validation
				if($this->_form->validates($data,$formKey))
					$this->sendMail();
				else $this->_notifier->addMessage(new Message(
					"Certaines de vos informations sont inexactes !",'error'
				));
			}catch(FormValidationPolicyFailure $e){
				//Si l'une des FormValidationPolicy échoue, elle lève
				//une exception.
				//Il s'agit peut-être d'un spam, ou d'une seconde
				//soumission accidentelle
				return new Redirection("web/contact");
			}
		}else $this->_session->set('contact_form',$this->createForm());
		return new StaticResponse($action);
	}

	private function sendMail():void{
		$message ="Nom & prénom : ".$this->_form->get("name")->getData()."\n"
			."Entreprise : ".$this->_form->get("company")->getData()."\n"
			."Téléphone : ".$this->_form->get("phone")->getData()."\n"
			."Consentement mentions légales et données personnelles : oui\n"
			."Mail : ".$this->_form->get("mail")->getData()."\n\n"
			."Message : \n\t".$this->_form->get("message")->getData();
		$this->_bus->execute(new CreateContact(
			new ContactLabel("Formulaire général"),
			new ContactInfos($message)
		));
		try{
			$mailMessage="Bonjour,\nVous recevez ce mail parce qu'une "
			 ."personne souhaite vous contacter par le biais du formulaire"
			 ." de contact de votre site internet."
			 ."\n\nContenu du message et coordonnées :\n\n$message\n\n"
			 ."Important : Ceci est un mail automatique, merci de ne pas"
			 ." y répondre."
			 ."\n\n En vous souhaitant une bonne journée,"
			 ."\nCordialement,\nGLaDOS";
			$this->_provider->send(new Mail(
				new NamedEmail(new Email("noreply@aperture.fr")),
				[ new NamedEmail(new Email("contact@aperture.fr")) ],
				[],
				[],
				[],
				[],
				new EmailSubject(
					"Un visiteur souhaite vous contacter "
					."(via le formulaire général)"
				),
				new MailBody($mailMessage,null,false)
			));
		}catch(\Exception $e){}
		$this->_notifier->addMessage(new Message(
			 "Votre message a bien été transmis à notre équipe, "
			 ."nous vous répondrons dans les meilleurs délais."
		 ));
		$this->_session->set('contact_form',$this->createForm());
	}
}
```

!!!info "Notifications temporaires"
	Ce type de message est totalement facultatif en cas d'erreur, puisque le formulaire affichera
	automatiquement une icone et un message pour chaque champ mal-rempli.

	En revanche, indiquer à l'utilisateur que son formulaire est reçu sans erreur est, selon moi,
	indispensable.

	Dans notre configuration actuelle, nous n'affichons aucun message lorsque l'une des polices de
	validation du formulaire lève une exception afin de ne donner aucun indice à un éventuel spam-bot sur
	la réussite ou non de son action.

	Bien entendu, ces techniques de protection anti-spam ne fonctionnent plus dans le cas d'une
	attaque ciblée. Si tel venait à être le cas, vous pouvez créer une `IFormValidationPolicy`
	permettant de contrer un spammer spécifique, ou utiliser d'autres polices existantes supplémentaires.

Voyons maintenant comment activer le panneau d'administration à [l'étape suivante](admin_panel).