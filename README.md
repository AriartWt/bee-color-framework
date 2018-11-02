This document is also available in [english](README.en.md).

# Avant-propos

Le framework **wfw** (pour Web FrameWork) est un framework prévu pour la réalisation
de sites web et/ou services et/ou application plus ou moins complexes et est 
déjà équipé d'un système de gestion des langues et a été écrit en **PHP 7.2**. 

Le but est de promouvoir un support de base pour la création de projets articulés autour 
du principe **CQRS** et pour offrir un support de base à l'**Event-sourcing**. Si vous souhaitez
vous documenter sur ces sujets, n'hésitez pas à visionner les conférences de **Greg Young** et
de ses confrères.

Son architecture ne se base pas sur MVC (ou aucun autre de ses dérivés) parce 
que ce pattern est avant tout destiné à des long-running process et qu'il est bien
souvent utilisé à tort et à travers, et souvent mal appliqué (je vous renvoie
sur les articles éclairés et documentés de Tom Butler sur ce design
pattern, et notamment les deux articles très justement appelés
[MVC : Model-View-Confusion](https://r.je/views-are-not-templates.html)).

Un serveur http ayant un fonctionnement totalement différent, j'ai choisi d'implémenter
un pattern personnalisé à base de handlers, prévu pour simplifier au maximum
la création de sites internet sans impacter le nombre de possibilités d'utilisation.

De plus, j'ai choisi volontairement de limiter au strict minimum le recours à
des librairies tierces afin de garder la maîtrise de l'évolution du framework
sans avoir à dépendre d'autres projets, afin de pérenniser mon travail.

La réutilisation du code est une très bonne chose, lorsqu'elle est faite avec
intelligence et discernement. Un projet qui a besoin de plus de 10 autres projets
indépendants devient très difficile à maintenir avec le temps.

## Attribution :

Si l'un des principaux buts de ce framework est de limiter le recours aux dépendances
à d'autres projets, j'en ai tout de même intégré trois au coeur du framework :
   - [PHPMailer](https://github.com/PHPMailer/PHPMailer)
   - [HTMLPurifier](http://htmlpurifier.org/)
   - [Dice](https://github.com/Level-2/Dice)

Pourquoi ? Parce que ces librairies me semblent parfaitement indispensables et
surtout sont bien plus abouties que tout ce que j'aurais pu coder moi même:
  - **HTMLPurifier** est un projet brillant qui demande des connaissances que je ne
possède pas dans le domaine des failles XSS.
  - **PHPMailer**, qu'il est inutile de présenter, est une fantastique librairie pour
  les envois de mails.
  - **Dice** est le seul conteneur d'injection de dépendances qui joue son rôle avec
efficacité sans dévier de son objectif principal. Ses performances rivalisent avec
les DIC les plus aboutis (et les surpassent même, voir les tests de performance
[ici](https://github.com/Level-2/Dice#performance)).

Il est par ailleurs à noter que ces librairies sont intégrées au coeur du framework,
mais peuvent être remplacées si vous le souhaitez par celles de votre choix.
WFW se veut totalement souple, et j'espère que ce pari est réussi.

# Documentation

La documentation complète est disponible sur un [site internet dédié](https://wfwdoc.bee-color.fr).

# Contribution

N'hésitez pas à contribuer !

Je fais actuellement de mon mieux pour essayer de proposer
la documentation en deux langues : anglais et français. Si vous avez des corrections
à apporter, ou des traductions à proposer, je les accepte volontiers.

Enfin, je fais tout mon possible pour produire une documentation aussi claire et 
détaillée que possible, mais je ne suis pas à l'abri d'erreurs, notamment dans
la documentation en anglais. Plus que votre indulgence, votre participation sera
grandement appréciée.