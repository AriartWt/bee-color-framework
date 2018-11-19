Nous allons commencer par créer le fichier `~/Aperture/site/config/conf.json` :

```json
{
	server : {

	}
}
```

Puisque `wfw` s'occupe des identifiants et mots de passe mysql, msserver et kvs, inutile de les
préciser ici, ils seront automatiquement ajoutés.

Pour plus de détail sur le fonctionnement exhaustif des configuration, merci de vous référer à la
section [correspondante](/general/first_steps/config).

A présent, nous allons définir les paramètres de contexte de notre site internet (pour plus
d'informations, c'est [par ici](/general/first_steps/context)).

Comme par défaut

```php
<?php

```