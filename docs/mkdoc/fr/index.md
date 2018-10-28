# Présentation

Le framework **wfw** est écrit en **PHP 7.2** et limite ses dépendances à trois librairies intégrées :
  - PHPMailer
  - Dice
  - HTMLPurifier

Inutile d'éspérer l'installer avec *composer*, ce n'est pas prévu parce qu'il n'est pas destiné à être
utilisé comme dépendance vers d'autres projets, étant donné qu'il a été construit pour se suffir à lui 
même. 

    !!! hint "Astuce"
        Vous pouvez cependant y importer les librairies de votre choix, comme expliqué dans la rubrique [importer une librairie]();

## Encore un framework ?

La question se pose, quand on sait le nombre de framework existants et créés chaque années. La décision 
de créer celui-ci se fonde sur un simple constat : je voulais avoir à disposition 
un framework léger conçu pour encourager le **DDD**, pour supporter le principe **CQRS** et me permettre
d'utiliser l'**Event Sourcing** qui, à mon sens, est largement sous-représenté dans le monde de **PHP**, 
la plupart des framework tradtionnels intégrant le plus souvent un **ORM**.

Je met effectivement un point d'honneur à ne pas intégrer d'**ORM** dans **wfw** pour la bonne raison 
qu'ils encouragent à utiliser des *dumb objects* pour gérer l'aspect métier des applications web.
La principale critique qui pourrait être faite, c'est qu'en l'occurence utiliser l'**Event Sourcing** 
pour la création de sites internet relève de l'**over engeneering**.

Ce n'est pas mon avis. Beaucoup de fonctionnalités des sites web se basent sur les dates de créations, les
versions de documents et objets envoyés par les utilisateurs (blog, commentaires, gestion d'utilisateurs )
qui sont gérées nativement par l'**Event Sourcing**. De plus l'avantage non négligeable est la mise à jour
des sites internet. Plus besoin de maintenir un schéma de base de données et de gérer ses différentes versions
au fil du temps. 

Les avantages de l'**Event Sourcing** sont considérables, et sot développés dans une section [dédiée]().

## 
