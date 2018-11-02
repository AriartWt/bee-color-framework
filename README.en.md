Ce document est également disponible en [français](README.md)

# Foreword

The **wfw** framework (for Web FrameWork) have been designed to create websites, webservices and 
more or less complicated web applications. It natively supports multilang and have been written in **PHP 7.2**.

The purpose is to provide a support to **Event-sourced** projects and a design which encourages
use of **CQRS**. If you want to know more about those principles, please takes a look to **Greg Young**'s 
conferences on youtube.

The wfw design don't use a MVC pattern (and no one of it's derivatives) because it should
be used to design long-running process. Often missused, or used where it shouldn't, it leads 
to structural problem in most web softwares. For more informations about this complexe subject, 
I let you take a look to a bunch of articles written by Tom Butler about MVC, including this one
beautifully entititled [MVC : Model-View-Confusion](https://r.je/views-are-not-templates.html).

Because of the way HTTP works, I choose to implements a handlers based architecture created to
be easy-to-use to create websites. I tried to make it flexible to handle most use-cases.

Furthermore, I decided to avoid use of third party libraries to keep control on the framework's
evolutions without depending on other projetcs and sustain it.
Code reuse is a beautifull concept, when applyed carefully. In my opinion, a project which depends 
upon ten or more other projects become a maintenance nightmare after few years.

# Attribution

On of the wfw's principal concern is to avoid third party libraries, But I still integrated three of them
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

# Documentation

The full documenation can be found on it's [dedicated website](https://wfwdoc.bee-color.fr).

# Contribution

Feel free to contribute !

I do my best to write this documentation in two langs : English and French. Since I'm a french
guy, I try to traduce it as I can, so please do not hesitate to correct me when I'm wrong, it
would be appreciated.

Finally, I try to produce a well written doc, as clear and detailed as possible, so, once again,
if something is missing or unclear, don't hesistate to contact me.