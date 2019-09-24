# Introduction

!!! info

	Cette documentation est également disponible en [français](https://wfwdoc.bee-color.fr/fr).


## Foreword

The **wfw** framework (for Web FrameWork) have been designed to create websites, webservices and
more or less complicated web applications. It natively supports multilang and have been written in **PHP 7.2**.

The purpose is to provide a support to [Event-sourced](event_sourcing) projects and a design which encourages
use of [CQRS](cqrs). If you want to know more about those principles, please takes a look to **Greg Young**'s
conferences on youtube.

The wfw design don't use a MVC pattern (and no one of it's derivatives) because it should
be used to design long-running process. Often missused, or used where it shouldn't, it leads
to structural problem in most web softwares. For more informations about this complex subject,
I let you take a look to a bunch of articles written by Tom Butler about MVC, including this one
beautifully entititled [MVC : Model-View-Confusion](https://r.je/views-are-not-templates.html).

Because of the way HTTP works, I choose to implements a handlers based architecture created to
be easy-to-use to create websites. I tried to make it flexible to handle most use-cases.

Furthermore, I decided to avoid use of third party libraries to keep control on the framework's
evolutions without depending on other projetcs and sustain it.
Code reuse is a beautifull concept, when applyed carefully. In my opinion, a project which depends
upon ten or more other projects become a maintenance nightmare after few years.

## Attribution

The wfw's principal concern is to avoid third party libraries, but I still integrated three of them
in the framework's core :

   - [PHPMailer](https://github.com/PHPMailer/PHPMailer)
   - [HTMLPurifier](http://htmlpurifier.org/)
   - [Dice](https://github.com/Level-2/Dice)

Why ? Because those libraries seems essentials to me, and beacause it's far beyond what I can
ever do myself :

  - **HTMLPurifier**, a very good protection against XSS.
  - **PHPMailer**, which I shouldn't have to introduce, to easily send mails.
  - **Dice**, the only one dependency injection container which is efficient and not over-complicated.
   (To be as convinced as I am, please take the time to read [this](https://github.com/Level-2/Dice#performance)
   about performances and it's well written documentation)

Finally, even if this libraries have been integrated in the framework's core, they can be
replaced by watever you want. I tried to design this framework as flexible as I can, so I
hope that it's a success.

## Another one PHP framework?

How many PHP frameworks have been created this year ? How many will be created the next one ?

A really huge amount. As usual. So, why do I written this one ?

I decided to write this one because I wanted to work with a framework which promote **CQRS** and **DDD**,
and implements a reliable way to use **Event Sourcing**. This combination of concept is under-represented,
in my opinion, in the PHP world, wich rely on (for most of them) **ORMs**.

I didn't want to use or create another one ORM in **WFW** because they encourage to use *dumb objects*
as domain objects in most of web apps. This often leads to misconceptions and technical depts which can
be disastrous, and maintenance nightmares.

!!!warning
	I know taht we can write good code with **ORMs** (even if the impedence mistmatch problem can be
	a really good reason to not use them). But, in my opinion, **ORMs** makes bad practices easier.

You can argues that using Event Sourcing to create websites is a pretty good example of over-engeneering.

And I will disagree for three reasons. The first, because it depends on the project. and I don't want to
restrict the use cases of **WFW** to only create simple websites. The second is that many of the
base web features are time and version based. Two importants characteristics that are natively embeded
in **Event Sourcing**. And the last one, but not the least, is its ability to not rely on strict
and inflexible SQL schemas.

**Event Sourcing** is not new and have many advantages which are detailed in a [dedicated section](/general/evenet_sourcing),
with many of its drawbacks, to be fair enough. There are no silver bullets, only some techs which can
be adapted to solve a problem, or not.

## Installation

For details about how it works and how to install, please [follow this link](/general/start)

## Contribution

Feel free to contribute !

I do my best to write this documentation in two langs : English and French. Since I'm a french
guy, I try to traduce it as I can, so please do not hesitate to correct me when I'm wrong, it
would be appreciated.

Finally, I try to produce a well written doc, as clear and detailed as possible, so, once again,
if something is missing or unclear, don't hesistate to contact me.
