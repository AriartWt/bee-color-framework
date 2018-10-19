## **Introduction**

### **Qu'est-ce que Bee-color WFW ?**

**Bee-color WFW** (pour Web FrameWork) est un framework open-sources écrit en [PHP](http://php.net/manual/fr/intro-whatis.php) **7.2** fournissant un cadre de création de sites et applications web qui se veut simple d'utilisation, léger et efficace. 

Il fourni tous les outils de base pour vous permettre de développer vos applications web en vous appuyant sur l'[Event Sourcing](http://blog.xebia.fr/2017/01/16/event-sourcing-comprendre-les-bases-dun-systeme-evenementiel/), le principe [CQRS](https://blog.octo.com/cqrs-larchitecture-aux-deux-visages-partie-1/) (**Command Query Responsability Segregation**) et le [DDD](https://blog.xebia.fr/2009/01/28/ddd-la-conception-qui-lie-le-fonctionnel-et-le-code/) (**Domain Driven Design**) pour un code maintenable et évolutif au travers d'une [architecture hexagonale](http://blog.xebia.fr/2016/03/16/perennisez-votre-metier-avec-larchitecture-hexagonale/).

### **Encore un framework PHP ?** 

S'il est vrai qu'un nombre conséquent d'autres frameworks existent déjà ([Symphony](https://symfony.com/), [Laravel](https://laravel.com/), [CodeIgniter](https://codeigniter.com/), etc.). Il est aussi vrai que peu d'entre eux offrent un support de base pour la pratique du **CQRS** et du **DDD**, et un nombre encore plus réduit à l'**Event Sourcing**.

!!! note

	Bien entendu il est tout à fait possible d'utiliser **WFW** avec un **ORM** et sans utiliser ces principes. Il est conçu pour être flexible, cependant il peut-être alors préférable de choisir un outil plus complet.


## **Principes de base**

### **Maintenabilité**

Tout est fait pour éviter le plus possible les dépendances avec d'autres librairies ou applications afin de réduire les problèmes de maintenance. Ainsi, il est aisé de garder une parfaite maîtrise du framework et de ses évolutions sans dépendre d'outils externes, au moins pour le code de base. 

!!! note 

    Vous êtes cependant tout à fait libre d'en importer via `composer` ou un autre gestionnaire de dépendances pour chacun des sites et chacune des application que vous développez, cette restriction n'est appliquée qu'au coeur du framework.

**WFW** met également à votre disposition plusieurs **daemons** entièrement écrits en **PHP** et fonctionnant sous **unix** (car leur implémentation actuelle dépend de l'extension **PHP** [PCNTL](http://php.net/manual/fr/book.pcntl.php) et s'appuie, pour sa partie **IPC** (Inter-process communication) sur des socket de type **AF_UNIX**) : 

- **[KVS]()** : Un simple serveur de stockage clé/valeur (similaire à [APCu](http://php.net/manual/fr/book.apcu.php)). Vous pouvez créer plusieurs `container` cloisonnés avec des accès utilisateur et une gestion de droits basique (`read`,`write`,`admin`) et un mode de stockage par défaut parmi : 
    - `IN_MEMORY_ONLY` : stockage en mémoire uniquement, préconisé comme système de cache.
    - `ON_DISK_ONLY` : stockage sur le disque uniquement pour des données persistées à accès occasionnel.
    - `IN_MEMORY_PERSISTED_ON_DISK` : stockage de données à persister et accédées régulièrement.
        
        !!! note
            Ces modes sont des modes par défaut. Il est possible de stocker n'importe quelle clé de manière indépendante dans un même container.

- **[MSServer]()** : Un DBMS léger orienté objet qui utilise vos paramètres pour gérer et mettre à jour vos modèles. Il est construit pour pouvoir accueillir plusieurs **composants** (`Components`). Deux sont intégrés de base : 
    - **[Writer]()** pour la gestion des modèles (réception d'événements, lecture de modèles, création, modification et suppression d'indexes), s'appuyant sur **KVS**.
    - **[Snapshoter]()** pour la gestion des sauvegardes des modèles et des snapshots pour les **[aggrégats]()** de l'**[EventStore]()**.
- **[STracker]()** : un daemon encore en cours de développement permettra à terme de vérifier que les services sus-cités fonctionnent correctement, d'obtenir des informations sur l'état du serveur, la version du framework et de l'application, d'ordonner la mise à jour du framework ou de l'application, d'obtenir les rapports d'erreurs des différents services (**PHP**, **Apache**, **KVS**, **MSServer**...), etc.

### **Event Sourcing vs ORM** 

#### **Un petit mot sur les ORM**

Les **ORM** (**Object-Relationnal Mapping**) sont de très bons outils pour assurer la persitance de vos objets sans avoir à écrire la moindre ligne de  **SQL**, évitant par la même occasion les erreurs que l'écriture de ces requêtes peut engendrer ou encore la perte de temps de leur rédaction qui s'avère bien souvent fastidieuse et redondante.

Les **ORM** sont, dans la plupart des cas, des outils formidables permettant de gagner un temps de développement considérable, il n'en reste pas moins qu'il existe un problème d'incompatibilité entre le paradigme de la **POO** (**Programmation Orientée Objet**) et celui des **RDBMS** (**Relationnal DataBase Management System**) qui mène souvent les **ORM** sur des chemins chaotiques, produisant alors des requêtes peu ou pas optimisées qui peuvent sérieusement poser des problèmes de perfomances. 

Pour plus d'informations, voici quelques articles traitants du terme "*impedance mismatch*", un problème soulevé depuis les débuts des **ORM** :

* [Sur wikipedia](https://en.wikipedia.org/wiki/Object-relational_impedance_mismatch)
* [Sur agiledata](http://www.agiledata.org/essays/impedanceMismatch.html)
* On notera d'ailleurs de vifs débats à ce sujet [dans les réseaux StackExchange](https://softwareengineering.stackexchange.com/questions/146065/is-there-really-object-relational-impedance-mismatch/146449)

Les **ORM** encouragent également les développeurs à externaliser la logique métier de leur application. En effet, ils s'appuient sur des objets représentant leurs données qui sont plus proches des **DTO** (**Data Transfert Object**) et sont donc dénués de tout comportement. Une fois la logique métier externalisée et disséminée à travers l'application, la simplicité des **ORM** se paie par des coûts de maintenance élevés et une compléxité accrue.

C'est également sans compter la rigidité des bases de données **SQL** quant à la mise à jour, puisque la structure de chaque base de données est fixée par un *schéma*. Enfin, n'oublions pas que le but d'un **ORM** est de fournir un maping entre les objets et la structure de la base de données pour donner l'illusion de manipuler les tables elles-même au travers de la **POO**. Ainsi une parfaite concordance doit toujours être maintenue entre le *schéma* de la base de données et les *objets* qui permettent de la manipuler. Toute modification de l'une de ces deux composantes doit être suivie d'une modification sur l'autre.

Les *objets* et le *schéma* sont alors les deux facettes d'une même pièce, qu'il peut-être laborieux de maintenir et peut constituer l'un des freins dans l'évolution de l'application par l'introduction d'une [dette technique](https://en.wikipedia.org/wiki/Technical_debt).

!!! note

    Il est bien entendu possible de faire évoluer ce schéma à l'aide de scripts de migration, mais il faut alors manipuler les données et le schéma avec précaution sans quoi les résultats pourraient être désastreux suivant les changements opérés.

#### **Et l'Event Sourcing ?** 

L'**Event Sourcing** n'est pas nouveau et nous l'utilisons naturellement au quotidien dans plusieurs domaines, dont le plus évident est le calcul de votre *solde bancaire*. 

En effet, lorsque vous recevez vos relevés de comptes, la banque ne se contente pas de vous envoyer le solde de votre compte. Elle vous envoie la liste des différentes opérations de débit et de crédit survenus au cours d'une période donnée avec un solde de départ, et un solde d'arrivée. La différence entre un schéma **SQL** et un système qui utilise l'**Event Sourcing** est alors clairement visible : 

Sans Event Sourcing : 
	
>	Votre solde est de 20€.

Avec Event Sourcing : 

>	Votre solde était de 920€ au 14/02/2018
>
>	|   Date   |  Débits | Crédits |
>	| -------- | ------- | ------- |
>	| 15/02/18 | 500€    |         |
>	| 16/02/18 |         | 200€    |
>	| 19/02/18 | 600€    |         |
>
>	Votre solde est de 20€ au 19/02/2018

!!! info

    À travers cet exemple caricatural, nous omettons qu'il est possible d'imiter l'**Event Sourcing** avec un schéma **SQL** sur une table déjà existente, ou lorsque le schéma le prévoit. L'illustration ci-dessus sert surtout à montrer qu'avec l'**Event Sourcing**, l'état courant de l'application est *calculé* grâce à la suite des événements de débit et de crédit de manière tout à fait naturelle, sans que le système ne soit **construit** pour le supporter: 
    
    Pour clarifier : contrairement à l'**Event Sourcing**, une cellule d'une table **SQL** ne garde pas naturellement la trace de son état : l'état précédent est *écrasé* lorsqu'une nouvelle valeur y est inscrite.

Ainsi, l'**Event Sourcing** ouvre un certain nombre de portes parmi lesquelles : 

* Le **versionning** : Il est possible de reconstruire n'importe quel état de l'application jusqu'à une certaine date.
* **Traçabilité et déboggage** : Tous les événements qui modifient l'état de l'application étant conservés, il est aisé de déduire les suites de changements qui ont provoqués son état actuel.
* Les **statistiques** : L'enregistrement des événements permet d'obtenir des méta-données sur l'utilisation des fonctionnalités de l'application, aussi bien pour les **développeurs** (interface d'administration, fonctionnalités sous-exploitées ou sur-exploitées, enchainement d'actions...) que pour les **clients** (statistiques exhaustives sur l'utilisation de leurs produits et services par leurs utilisateurs, amélioration de leurs interractions avec leur espace de travail en fonction de leur utilisation, etc.)
* Un découplage de la **source des données** et **leur représentation** :

!!! warning "Don't query your events"
    
    En effet, avec l'**Event Sourcing**, nous ajoutons les événements un à un dans une base de données ou un fichier. Ces événements sont **immuables** (et doivent le rester) et ne doivent en aucun cas être supprimés. On peut ensuite construire des *modèles* de représentation de ces données en utilisant les événements, car il est totalement exclu de tenter d'obtenir l'état courant de l'application avec une requête **SQL** sur les événements eux-même.
    
    Ces *modèles* peuvent-être purement statistiques, ou offrir une vue plus complexe de l'application, en représenter une seule partie, ou même servir à des *modèles* décisionnels. L'important n'étant plus la *structure* des données, mais seulement les données *déduites* des événements. 
    
    Ces *modèles* peuvent-être de nature totalement différents et cohexister au sein du même système : un système de fichier, un ou plusieurs schémas de base de données relationnelles, une ou plusieurs bases **NOSQL** , une ou plusieurs bases de données orientées objet, des fichiers xml, etc.
    
    On peut donc avoir facilement une relation d'un **EventStore** à *N* modèles.

Nous n'irons pas plus loin dans la description de l'**Event-sourcing** ici, vous pourrez néanmoins trouver plus d'informations dans les [excellentes conférences](https://www.youtube.com/watch?v=JHGkaShoyNs) de Greg Young sur le sujet, et les nombreux articles que l'on peut trouver sur le net.

#### **Utilisation**

**WFW** a pour but de rendre la persistence des données de votre application aussi simple que lors de l'utilisation d'un **ORM**, à ceci près que le problème d'*impédance* ne pose plus de contraintes au *design*, et que vos objets (nommés `AggregateRoot` d'après le **DDD**) peuvent désormais contenir les comportements de votre application et encapsuler leurs données.

Ainsi, là où l'utilisation d'un **ORM** pouvait ressembler à `<? $orm->save($obj);` ou parfois même `<? $obj->save()`, l'**EventStore** fonctionne de manière analogue : `<? $eventStore->save($aggregateRoot);`.

#### **Coût de l'Event Sourcing**

L'**Event Sourcing** vient avec un certain coût qu'il faut tout de même souligner : 

- Le stockage des données, pour une même application par rapport à une persistence dans un **RDBMS** est plus gourmand.
    
    Ce problème peut-être en partie réglé par l'utilisation d'un algorithme de compression des chaînes sérialisées, car elles contiennent de nombreuses informations redondantes. Il n'en reste pas moins qu'un **aggrégat** qui est le fruit d'une centaine d'événements sera toujours plus lourd qu'un objet similaire stocké dans un **RDBMS** par exemple. Ce coût est cependant minimisé par le fait que le prix de l'espace de stockage diminue de façon régulière avec les avancées technologiques dans le domaine.
- Il faut recalculer l'état d'un **aggrégat** en lui réappliquant ses événements à chaque fois que l'on souhaite le manipuler.

    !!! hint "Astuce"
    
        Il est possible de pallier à ce problème grâce à la création de snapshots à intervalles réguliers
        
        Le snapshot d'un **aggrégat** est une capture d'écran de son état après application de ses événements. On peut ainsi éviter de le reconstruire en réappliquant tous ses événements depuis sa création en reprenant simplement les événements survenus depuis son dernier snapshot.
        
- Une difficulté de mise à jour des événements après publication de l'application. En effet, il peut être difficile de modifier les événements lorsque ceux-ci sont stockés par sérialisation, car modifier les chaînes sérialisées peut s'avérer périlleux. 
    
    Ainsi, si une modification du comportement et des événements doit être oppérée sur un **aggrégat**, il faut toujours tenir compte des anciennes versions des différents événements. Ce problème peut également être réglé grâce à un snapshot de l'**aggrégat** avant mise à jour de l'application. [D'autres pistes](https://www.infoq.com/news/2017/07/versioning-event-sourcing) peuvent être néanmoins explorées.

#### **CQRS, un principe naturel**

En ce qui concerne le principe **CQRS**, c'est une conséquence directe de l'utilisation de l'**Event Sourcing** : les *commandes* produisent des événements qui servent de base aux modèles, ainsi la partie *commande* et la partie *lecture* (query) étant structurellement séparées, la confusion est moins évidente. 

On définira par commodité que la partie *commande* est responsable des écritures, et/ou produit des effets de bords, tandis que la partie *lecture*, quant à elle, ne produit aucun effet de bord sur l'état de l'application, et ne fait que l'interroger pour obtenir des informations.

Séparer les deux est un bon moyen d'organiser votre code pour le rendre plus facile à maintenir.

!!! note 
    
    Il est aussi bon de rappeler que **WFW** n'est pas un framework **CQRS**. C'est un framework qui s'appuie sur ce principe. Ainsi, il appartient au développeur de comprendre ce concept et de l'appliquer lors de la phase de développement, afin de *favoriser* une distinction entre la partie *commande* et la partie *lecture*. 
    
!!! warning "Attention"

    **CQRS** n'est pas non plus un dogme absolu, il peut arriver que la partie *commande* ait besoin de faire appel à la partie *lecture* (ou inversement), c'est une simple limite conceptuelle attribuée aux bonnes pratiques qu'il ne faut pas hésiter à transgresser lorsque la situation l'impose. 
    
    Toute bonne pratique, utilisée à outrance et sans discernement, peut devenir un anti-pattern. 
    
### **Domain Driven Design et Ubiquitous language**

En quelques mots, le **Domain Driven Design** est une pratique de conception qui place le métier de votre *SI* au coeur de votre application et vous permet d'exprimer les besoins et contraintes métier de manière intelligible pour votre expert fonctionnel (un rôle souvent tenu par votre client).

En effet, votre expert fonctionnel ne connaît probablement pas les langages de programmations, leurs paradigmes, les frameworks ou les librairies. Alors il existe un problème d'incompatibilité de vocabulaire entre l'expression du besoin de l'expert fonctionnel et celui du développeur. Le **DDD** est une des solutions permettant de réduire, voir de faire disparaître ces incompatibilités en proposant une conception au plus près de cette expression : l'ubuiquitous language. 

Ainsi le **DDD** est avant tous l'introduction du vocabulaire fonctionnel dans le code, sa documentation et ses explications. Abandonnez les traditionnels opérations **CRUD** (Create, Read, Update, Delete) pour des termes ayant un sens plus compréhensible pour les non-développeurs.

??? example "Exemple"

    Prenons l'exemple très bref d'un code peu expressif, assez répandu, en prenant comme exemple celui de la gestion d'un utilisateur : 
    ```php
    <?
    //Cas N°1 : changement de l'identifiant de connexion :
    if($user->getPassword() === $password){
        $user->setLogin($login);
        return "Login changé !";
    }else{
        return "Mauvais mot de passe !";
    }
    
    //Cas 2, pour une réinitialisation de mot de passe : 
    $confirmKey  = 'ioshaoiho';
    $mailer->send($confirmKey,$user->getEmail();
    $user->setState(User::RETRIEVE_PASSWORD);
    //...
    if($user->getState() === User::RETRIEVE_PASSWORD && $confirmKey === $givenConfirmKey){
        $user->setPassord($newPassword);
        return "mot de passe réinitialisé";
    }else{
        return "code de confirmation invalide";
    }
    ```
    
    Contre une version plus orientée **DDD** : 
    
    ```php
    <?
    //Cas N°1 : changement de l'identifiant de connexion :
    try{
        $user->modifyLogin($login,$password);
        return "Login changé !";
    }catch(WrongPassordException $e){
        return "Mauvais mot de passe !";
    }
    
    ////Cas 2, pour une réinitialisation de mot de passe : 
    $code = $user->askForAPasswordReset();
    $mailer->send($code,$user->getEmail());
    //...
    try{
        $user->resetPassword($code,$newPassword);
        return "Mot de passe réinitialisé";
    }catch(InvalidConfirmationCodeException $e){
        return "Code de confirmation invalide";
    }
    
    ```
    Dans ces exemples, il est facile de constater que, sans **DDD**, il est parfaitement possible de créer un utilisateur dans un état incohérent, et ce de plusieurs manières : 
    
    - Vérifier les mots de passe par `==` au lieu de `===` par exemple pourrait engendrer une faille de sécurité (idem pour la comparaison du code de confirmation).
    - Oublier la ligne `<? $user->setState( User::RETRIEVE_PASSWORD );` dans le second cas, empêchera définitivement l'utilisateur de retrouver son mot de passe.
    - Le code de confirmation pourrait être altéré accidentellement plus tard dans le code avant sa persistence, rendant là encore impossible la procédure de récupération du mot de passe.
    
    De plus, pour la comparaison du mot de passe, la règle `<? $user->getPassword() === $password` doit  être réécrite partout où la vérification de la validité d'un mot de passe doit avoir lieu (connexion, changement de mail, etc.). Or, si cette règle doit changer pour une raison ou une autre, il faut éditer cette ligne partout où elle apparaît, laissant la porte ouverte à de nombreux bugs suite à une mise à jour. 
    
    Bien entendu, dans le présent exemple, la correction serait relativement triviale, mais le serait beaucoup moins dans un contexte différent avec des règles un peu plus complexes.
    
    En revanche, grâce au **DDD** il est, par *design*, impossible de laisser l'objet `<? User` dans un état incohérent car c'est l'objet lui même qui gère l'intégrité de son état suivant ses règles de fonctionnement, garantissant ainsi que le moindre changement d'une de ces règles est localisé et restreint à l'objet `<? User`.
     Le problème de vérification du mot de passe est alors simplement réglé par une méthode `<? $user->checkPassword($password);` pour reprendre notre précédent exemple. 
     
     De même, le code produit suivant les principes du **DDD** est beaucoup plus proche du vocabulaire de votre expert fonctionnel : `<? $user->setPassword( $password );` est bien moins explicite que `<? $user->resetPassword( $code, $password )` : la définition même de la méthode `<? public function resetPassword(ConfirmationCode $code, Password $password);` laisse peu de doute sur son fonctionnement. 
     
     Notez par ailleurs l'utilisation de "value object" dans la définition de la méthode `<? public function resetPassword(ConfirmationCode $code, Password $password);` : `<? ConfirmationCode` et `<? Password`. C'est une garantie de plus pour la cohérence de l'objet. En plus de rendre sa construction obligatoirement valide, un objet comme `<? ConfirmationCode` vous permet de définir les règles de validité d'un code de confirmation et vous offre la possibilité de les changer sans impacter le fonctionnement de la classe `<? User`. Idem pour l'objet `<? Password`.
        
    
Enfin, le **DDD** n'est pas obligatoirement couplé à l'**Event Sourcing**, il est cependant plus aisé de maintenir l'encapsulation des objets sans accesseurs en enregistrant les *événements* et en reconsitutant l'objet grâce à eux, qu'en exposant tous les attributs de l'objet par des getters et setters (ou par reflexion) pour les besoins d'un **ORM**.

Les ouvrages et les conférences sur le **Domain Driven Design** sont légions, aussi nous vous conseillons de commencer par consulter [cet article](https://blog.xebia.fr/2009/01/28/ddd-la-conception-qui-lie-le-fonctionnel-et-le-code/) pour plus de détails.

## **Architecture**

**WFW** utilise une combinaison entre **MVC** et **architecture hexagonale**.

### **Partie MVC**

- On conserve les appellations *Model*, *View* et *Controller*, et on conserve une partie de leurs rôles : 
    - **View** : Contient le code nécessaire à la mise en place des vues, avec **HTML**, **PHP**, etc.
    - **Model** : Contient la couche d'accès aux données, séparée en deux composants majeurs : 
        - **Repository** : Objet utilisant l'**Event Store** pour retrouver et persister les **aggrégats**
        - **Model** : Modélisation des événements, manipulant des **ModelObject** et renvoyant, sur requête, des **DTO**. Les modèles ne sont pas utilisés tels quels dans le code et n'ont pas à être instancié. Ils sont appelés par le biais d'un MSWriterClient pour assurer la cohérence des données et gérer les accès concurrents. 
            
            ??? example "Exemple"
                On n'écrira pas : 
                ```php
                <?
                $userModel = new UserModel(...);
                $users = $userModel->search("query...");
                ```
                Mais plutôt : 
                ```php
                <?
                $msClient = new MSWriterClient(...);
                $users = $msClient->query(UserModel::class,"query...");
                ```
                 
    - **Controller** : Les controlleurs servent toujours d'interface avec les vues mais n'ont plus la responsabilité d'appeler les modèles pour l'*écriture*, ils restent en revanche responsables de la partie *lecture* (query). Ils servent de filtre de données et contiennent un bus de commande (`<? CommandBus`) permettant d'envoyer des commandes au coeur de l'application : le *domain*. Ce sera ensuite aux différents handlers (`<? CommandHandler`) de traiter la commande, et contacter l'**EventStore** pour la persistance des données.
    
### **Bus de commandes, commandes et handlers**

Pour chaque commande, vous pouvez définir un certain nombre de handlers, que le `<? CommandBus` invoquera lorsqu'une commande écoutée par le handler sera envoyée. 

Les **CommandHandlers** sont les objets qui reçoivent et traitent les commandes, ils utilisent des **ApplicationServices** ainsi que les **DomainServices** et ont accès aux **Repositories**, afin qu'ils puissent être injectés directement dans les **ApplicationServices** ou les **DomainServices** suivant les besoins.
 
 Enfin, les **Respositories** ont accés à l'**EventStore**, qui envoie d'abords les événements au **MSServer** afin qu'ils soient interceptés par le composant **Writer** (ainsi que pour tout autre composant qui écoutera la commande `<? ApplyEvents`) qui se chargera de mettre à jour les [modèles qu'il doit gérer](), puis se sert du **DomainEventDispatcher** pour appeler les différents **DomainEventHandlers** qui écoutent les événements enregistrés.

Il est tout à fait possible d'ajouter d'autres écouteurs à l'**EventDispatcher** pour réagir aux différents événements et, par exemple, gérer un envoi de mail, de sms, une modification du système de fichier etc.

La logique métier est donc totalement déléguée aux **aggrégats**, eux-même manipulés par des **DomainServices** si une action du *domain* fait intervenir plusieurs **aggrégats** par exemple.

Ainsi, le chemin de base de l'information à travers le système est le suivant : 

    { Framework<HTTP> } 
    |
    -> {Controller} = Response
    |    |
    |    -> {CommandBus}
    |    |    |
    |    |    => {CommandHandler<Command>( ApplicationServices(DomainServices) )} = DomainEvents
    |    |        |
    |    |        -> {EventStore(MSServer(Writer),DomainEventDispatcher)}
    |    |            |
    |    |            => {DomainEventHandler<DomainEvent>}
    |    |
    |    -> {View}
    |
    -> { Renderer(Response) } = {Output}[HTML,JSON,XML,PDF,...] 
    |
    -> {HTTP}<Output>
                               
     Légende : 
        |                : Déroulement chronologique du programmme
        ->/=>            : Appel au composant ...
        ->               : Relation 1 à 1 
        =>               : Relation 1 à N
        {}               : Composant
        {foo(bar,...)}   : Le composant foo utilise le service bar, et le service...
        {foo}[bar,...]   : Exemples de composant foo : bar, ...
        {foo<bar>}       : Le composant foo reçoit une requête/commande bar
        {foo}=bar        : Le composant construit ou retourne bar

Tout l'intérêt de passer par un **CommandBus** est de permettre, par exemple, d'utiliser un *décorateur* en lieu et place du **CommandBus** de base afin de pouvoir gérer des droits d'accès, des effets de bords, etc. 

!!! hint "Astuce"
    
    Par définition, le **CommandBus** ne retourne pas de résultat. Si vous souhaitez "récupérer" le résultat d'une commande (par exemple, la création d'un utilisateur, il vous faut écouter l'événement **UserCreated**.
    
    Le bus de commandes par défaut étant synchrone, vous pouvez définir le **Controller** lui même comme un **DomainEventHandler**.

Les droits d'accès peuvent également être gérés avant le **CommandBus** ou après, dans le **CommandHandler** ou , au plus tard, dans l'**ApplicationService**. De manière générale, toute action effectuée par le biais d'un **DomainService** doit être autorisée, c'est à dire que les décisions de sécurité et de restriction d'accès doivent avoir été prises avant l'appel au **DomainService**. 

!!! note "Un petit mot de l'auteur"

    Il y a au moins autant de manières de comprendre et de concevoir des applications en respectant les principes du **DDD** qu'il existe de développeurs, ainsi le présent framework et par extension, le présent article, n'ont pas vocation à être un exemple parfait ou l'unique manière de procéder. 
    
    De même, le **DDD** est si vaste et complexe qu'il en existe différentes conceptions, entrant plus ou moins dans les détails en poussant la reflexion parfois très loin, en sachant que l'application de ces principes dépend à la fois du système, du langage de programmation mais également du projet en lui même ainsi que des développeurs qui travaillent dessus.
    
    Je vous conseille par ailleur de faire un tour sur https://softwareengineering.stackexchange.com , où vous pourrez trouver des discussions passionnantes qui donnent matière à réfléchir et à penser autour du **DDD**, mais pas seulement.
    
    Enfin, la solution implémentée ici est le reflet de *ma* compréhension du **DDD**, certainement perfectible, mais efficace pour résoudre les problèmes que je rencontre, le but étant de ne pas rendre le système trop complexe par rapport au travail qui doit être effectué. En revanche, il est conçu avec suffisament de souplesse pour qu'il vous soit possible de le modifier à votre convenance et ainsi l'**adapter à vos propres besoins**.