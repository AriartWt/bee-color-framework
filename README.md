Le framework wfw (pour Web FramWork) est un framework prévu pour la réalisation
de sites web et/ou services et/ou application plus ou moins complexes et est 
déjà équipé d'un système de gestion des langues.
Son architecture ne se base pas sur MVC (ou aucun autre de ses dérivés) parce 
que ce pattern est avant tout destiné à des long-running process.

Le web ayant un fonctionnement totalement différent, j'ai choisi d'implémenter 
un pattern personnalisé à base de handlers, prévu pour simplifier au maximum
la création de sites internet sans impacter le nombre de possibilités d'utilisation.

De plus, j'ai choisi volontairement de limiter au stricte minimum le recours à 
des librairies tierces afin de garder la maîtrise de l'évolution du framework 
sans avoir à dépendre d'autres projets, afin de péréniser mon travail.

La réutilisation du code est une très bonne chose, lorsqu'elle est faite avec 
intelligence et discernement. Un projet qui a besoin de plus de 10 autres projets
indépendants devient très difficile à maintenir avec le temps.

/!\ Attribution :

Le principal but de ce framework est également de limiter le recours aux dépendances 
à d'autres projets. Pour ce faire, j'en ai intégré trois au coeur du framework :
   - PHPMailer : https://github.com/PHPMailer/PHPMailer
   - HTMLPurifier : http://htmlpurifier.org/
   - Dice : https://github.com/Level-2/Dice
   
Pourquoi ? Parce que ces librairies me semblent parfaitement indispensables et
surtout sont bien plus abouties que tout ce que j'aurais pu coder moi même.
HTMLPurifier est un projet brillant qui demande des connaissances que je ne 
possèdes pas dans le domaine des failles XSS.
PHPMailer est particulièrement bien conçu pour l'envoi de mail,
et Dice est le seul conteneur d'injection de dépendances qui joue son rôle avec
efficacité sans dévier de son objectif principal avec des fonctionnalités inutiles, 
voir contre-intuitives.
