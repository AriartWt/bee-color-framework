#Un essais de documentation
##Pour une documentation potable

Il faut déjà commencer par comprende Markdown.

*   Visiblement un espace avant est inutile
*   Un peut d'italic : _Wow_
*   Et même de **gras** pour vous dire !!

ALlons y y pour écrire un super long texte pas très commode à lire et pas très intéressant? Bourré de fautes d'orthographes parce que son 
auteur est débile.

Et si on en fait un autre on est content.

> une courte citation
> d'un expert très malin
 
Qui ne passe tout en insérent [Une url](http://google.com) jamais de lignes.

>
>Jamais.
>Mais il tente quand même des listes : 
> 1. Premier
> 2. Deuxieme
> 5. Ile ne sais plus compter.
> 3. Mais markdown est plus fort que lui.

#Des choses sérieuses.

On va commencer par une petite photo mignone : ![Petit chaton](https://media.koreus.com/201701/chat-tete-bicolore.jpg)
Qui ne s'affiche pas du tout.

`Some piece of code ? `

```
<?php
    class Test implements IMardkwon{
        private $_lol;
        public function __construct(string $lol){
            $this->_lol = $lol;
        }
        public function rigoleUnPeu():string{
            return "$lol!";
        }
    }
```

Une petite séparation ? 

----------------------

??? note
	Attention, le code qui va suivre est complétement con !
	Je ne sais pas si ça marche. Pas dans `<? KVSModes::ON_DISK_ONLY` déjà :/
	Mais bon j'ai plein de choses à écrire dont...
	
	```
	<?php
	    class Test implements IMardkwon{
		private $_lol;
		public function __construct(string $lol){
		    $this->_lol = $lol;
		}
		public function rigoleUnPeu():string{
		    return "$lol!";
		}
	    }
	```

	Du code. Comme toujours.
		

<span style="color:red">Un peu de couleur ! Ou pas.</span>