Avant de commencer, je tiens à préciser que je ne suis absolument pas sponsorisé par qui que ce soit
et que la présente section est avant tout là pour vous expliquer ma manière de travailler ainsi que
les outils que j'utilise.

Je vais donc dès à présent parler de la nécessité d'utiliser un IDE, aussi j'invite même les plus
réfractaires d'entre-vous à lire cette petite introduction avec ouverture d'esprit (oui, je vous vois,
utilisateurs de SublimText, Notepad et autre Atom...).

Pour les convaincus, vous pouvez immédiatement passer à la section [suivante](#configuration-de-lide)

## IDE > all

Maintenant que la guerre est déclarée, voici mon opinion au sujet des IDE :

Leur utilisation est vivement recommandée que ce soit dans le cadre d'un projet simple ou complexe,
avec **WFW** ou n'importe quel autre framework, tant ils encouragent les bonnes pratiques et simplifient
la vie des développeurs en prenant garde pour eux aux erreurs les plus communes et les plus chronophages.

Que ceux qui n'ont jamais perdu une quinzaine de minutes à chercher un foutu point-virgule ou une satannée
faute de frappe du style : `Enviromnent` au lieu de `Environment` lèvent le doigt (croyez moi, je suis
le premier à le lever).

Ayant pour ma part longuement utilisé SublimeText 3 avec différents modules, je dois avouer que
la migration vers PHPStorm m'a été réellement salutaire autant sur un plan qualitatif que productif.
Je sais que pour nous autres, développeurs, les habitudes sont souvent le plus grand freins face
au changement, mais prenez le temps d'y penser et d'essayer un véritable IDE.

Pour ma part, je ne regrette absolument pas l'argent dépensé dans l'achat d'une license. Rien que pour
faire du *refactoring* à petite ou grande echelle, un éditeur avancé tel que SublimText ne fait
absolument pas le poids.

## Configuration de l'IDE

L'exemple ici sera (sans surprise) [PHPStorm](https://www.jetbrains.com/phpstorm/), je vous invite à
faire le lien avec votre propre IDE si vous en utilisez un autre.

Pour ma part, je travail exclusivement sous `ubuntu`, pour tout un tas de raisons que je n'exposerais
pas ici. Vous pouvez développer sous windows et faire vos tests dans une machine virtuelle sans trop
de problème en créant un dossier partagé entre windows et ubuntu (sous VMWare, c'est relativement
simple).

Ainsi en lançant la commande `sudo wfw import ProjectName /mnt/hgfs/shared/ProjectName` depuis votre
machine virtuelle lorsque vous voulez tester vos modifications devrait sans aucun vous satisfaire.

