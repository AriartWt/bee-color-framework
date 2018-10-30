# Présentation

!!! info

	This documentation is also available in [english](https://wfwdoc.bee-color.fr/en).


## Avant-propos

Le framework **wfw** (pour Web FrameWork) est un framework prévu pour la réalisation
de sites web et/ou services et/ou application plus ou moins complexes et est 
déjà équipé d'un système de gestion des langues et a été écrit en **PHP 7.2**. 

Le but est de promouvoir un support de base pour la création de projets articulés autour 
du principe [CQRS](cqrs) et pour offrir un support de base à l'[Event-sourcing](event_sourcing). Si vous souhaitez
vous documenter sur ces sujets, n'hésitez pas à visionner les conférences de **Greg Young** et
de ses confrères.

Son architecture ne se base pas sur MVC (ou aucun autre de ses dérivés) parce 
que ce pattern est avant tout destiné à des long-running process et qu'il est bien
souvent utilisé à tort et à travers, et souvent mal appliqué (je vous renvoie
sur les articles éclairés et documentés de Tom Butler sur ce design
pattern, et notamment les deux articles très justements appelés 
[MVC : Model-View-Confusion](https://r.je/views-are-not-templates.html)).

Un serveur http ayant un fonctionnement totalement différent, j'ai choisi d'implémenter 
un pattern personnalisé à base de handlers, prévu pour simplifier au maximum
la création de sites internet sans impacter le nombre de possibilités d'utilisation.

De plus, j'ai choisi volontairement de limiter au stricte minimum le recours à 
des librairies tierces afin de garder la maîtrise de l'évolution du framework 
sans avoir à dépendre d'autres projets, afin de péréniser mon travail.

La réutilisation du code est une très bonne chose, lorsqu'elle est faite avec 
intelligence et discernement. Un projet qui a besoin de plus de 10 autres projets
indépendants devient très difficile à maintenir avec le temps.

## Attribution

Si le principal but de ce framework est de limiter le recours aux dépendances 
à d'autres projets, j'en ai tout de même intégré trois au coeur du framework :

   - [PHPMailer](https://github.com/PHPMailer/PHPMailer) 
   - [HTMLPurifier](http://htmlpurifier.org/)
   - [Dice](https://github.com/Level-2/Dice)
   
Pourquoi ? Parce que ces librairies me semblent parfaitement indispensables et
surtout sont bien plus abouties que tout ce que j'aurais pu coder moi même:

  - **HTMLPurifier** est un projet brillant qui demande des connaissances que je ne 
possèdes pas dans le domaine des failles XSS.
  - **PHPMailer**, qu'il est inutile de présenter, est une fantastique librairie pour
  les envois de mails.
  - **Dice** est le seul conteneur d'injection de dépendances qui joue son rôle avec
efficacité sans dévier de son objectif principal. Ses performances rivalisent avec
les DIC les plus aboutis (et les surpassent même, voir les tests de proformances
[ici](https://github.com/Level-2/Dice#performance)).

Il est par ailleurs à noter que ces librairies sont intégrées au coeur du framework, 
mais peuvent être remplacées si vous le souhaitez par celles de votre choix.
WFW se veut totalement souple, et j'espère que ce paris est réussis.

## Encore un framework PHP ?

La question se pose, quand on sait le nombre de framework existants et créés chaque années. La décision 
de développer celui-ci se fonde sur un simple constat : je voulais avoir à disposition 
un framework léger conçu pour encourager le **DDD**, pour mettre en place facilement le principe **CQRS**
 et me permettre d'utiliser l'**Event Sourcing** qui, à mon sens, est largement sous-représenté dans 
 le monde de **PHP**, la plupart des framework tradtionnels intégrant le plus souvent un **ORM**.

Je met effectivement un point d'honneur à ne pas intégrer d'**ORM** dans **WFW** pour la bonne raison 
qu'ils encouragent à utiliser des *dumb objects* pour gérer l'aspect métier des applications web. Ce
qui conduit souvent à des conceptions bancales difficiles à maintenir.
!!!warning "Attention"
    Je suis conscient qu'il est possible de produire du code de qualité en utilisant des **ORM** quoique
    je trouve que le problème d'*impedence mismatch* soit tout de même handicapant.
    Je dis simplement que le système **encourage** les mauvaises pratiques plus qu'il ne le devrait.

La principale critique qui pourrait être faite, c'est qu'en l'occurence utiliser l'**Event Sourcing** 
pour la création de sites internet relève de l'**over engeneering**.

Ce n'est pas mon avis. Beaucoup de fonctionnalités des sites web se basent sur les dates de créations, les
versions de documents et objets envoyés par les utilisateurs (blog, commentaires, gestion d'utilisateurs,
 éditions de pages...) qui sont gérées nativement par l'**Event Sourcing**. De plus l'avantage non 
 négligeable est la mise à jour des sites internet. Plus besoin de maintenir un schéma de base de 
 données et de gérer ses différentes versions au fil du temps. 

Les avantages de l'**Event Sourcing** sont considérables, et sont développés dans une section [dédiée]().
Section dans laquelle vous trouverez également ses limites et ses inconvénients, dans un soucis 
d'honnêteté intellectuelle.

## Installation

Pour plus de détails sur son fonctionnement et son installation, c'est [par ici](start)

## Contribution

N'hésitez pas à contribuer ! 

Je fais actuellement de mon mieux pour essayer de proposer
la documentation en deux langues : anglais et français. Si vous avez des corrections
à apporter, ou des traductions à proposer je les accepte volontier.

Enfin, je fais tout mon possible pour produire une documentation aussi claire et 
détaillée que possible, mais je ne suis pas à l'abri d'erreurs, notamment dans
la documentation en anglais. Plus que votre indulgence, votre participation sera
grandement appréciée.