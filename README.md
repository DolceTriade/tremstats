Tremstats Too 2.0.0
===============================================================================
For Tremulous 1.2 games.log

Requirements
-------------------------------------------------------------------------------
You need atleast the following things for the Tremulous server:

 -  Python 2.6
 -  Site-Packages:                (common .deb or .rpm package name)
    -  MySQLdb                      (python-mysqldb or MySQL-python)
    -  Python Imaging Library "PIL" (python-imaging)

And the following things for the webserver (may be the same box):

 - MySQL 4.1 or higher
 - PHP 5.1 or higher
 - php-gd (php module for gd graphics library)

A server running:

 - Tremulous 1.2

*Note:* Windows specific code has been removed from the log parser, this plus
      the lack of any testing whatsoever on Windows almost guarantees
      that you will need to fix tremstats.py if you plan to parse
      logs on a Windows platform.



Privacy
-------------------------------------------------------------------------------
By default everything is enabled for viewing from the web, including player
chat and name aliases.

You may wish to protect some of your player's privacy by disabling the PRIVACY
options in web/core/config.inc.php by setting them to '1'.



Installation
-------------------------------------------------------------------------------
To make it slightly easier, a setup.sh script is included which will walk
you through setting up the initial database config files. (needs bash)

First you have to setup your MySQL database. So create a new database or change
to an existing one. Then execute both of the SQL files _structure.sql_ and
_data.sql_.

Now copy all files from the _parser_ directory to a directory on your Tremulous
server, for example _/usr/local/games/tremstats/_. Then give the mainfile
execute rights by typing `chmod a+x tremstats.py`.

Then copy _config.py.default_ to _config.py_, open _config.py_ in your prefered
texteditor and edit the first few lines and enter your MySQL data and location
of the _games.log_ and all your custom maps PK3s. After this part is done, you
may parse your log which was created so far by typing `./tremstats.py`.

You may want to parse your logfile every 12 hours or something like that.
(as your database gets bigger, calculating stats will take more time, so
 try to avoid parsing more than once or twice a day unless you have
 processing power to spare -Rezyn )
If you use crontab, you can enter the following line into _/etc/crontab_:

    0 */12 * * * root /usr/local/games/tremstats/tremstats.py

For further information about crontab, see Google.

Now, as you have setup all important things, you also want to see some output.
So copy all files from _web_ to a directory reachable from the internet.  Copy
the file _core/config.inc.php.default_ to _core/config.inc.php_, edit
_core/config.inc.php_ and enter your MySQL data here again. The last setting in
this file is the address of your tremulous server. This should be something
like `localhost:30720` or an external address. If you have done this step, you
have done everything. Have fun with Tremstats!



Notices on tremstats.py
-------------------------------------------------------------------------------
Tremstats.py offers you some nice features. Type `tremstats.py --help` for
more information.



Notices on the output
-------------------------------------------------------------------------------
The output system is written very simple. If you want to just change the
colors, you may edit the CSS file. If you want a bigger change to the layout,
you can change the template files in the _template_ directory. They are
written in PHP, so you don't have to learn any complicated template system
syntax. Even if you don't know PHP but HTML, you will understand the really
simple syntax used there.

If you change the colors background behind the graphs, you may also edit the
__graphs.php_ file and set the colors there.



Special Thanks from DASPRiD
-------------------------------------------------------------------------------
I want to thank Ingar, who supported me with a lot of knowledge about linux
and helped me to test the parser several times. He also patched Tremulous for
me.

Then I want to thank WolfWings, who wrote the patch for Tremulous, very good
work.

Also a big thanks to Gilmor, who found the cause for this nasty bug of unlogged
kills and destructions and also to DigiFad, who submitted a patch for windows.

At this point, thanks to Basilisk for the really great icons!

At least I want to thank all users in #tremulous, who helped me with some small
information and gave me lots of tips. Your are the best!



Contact
-------------------------------------------------------------------------------
Contact Petr at the [github project webpage](https://github.com/ppetr/tremstats).
Do not contact Rezyn or DASPRiD about problems with this *Tremstats* fork.

### Tremstats 2.0.0

 - Name:    Petr
 - Website: [https://github.com/ppetr/tremstats](https://github.com/ppetr/tremstats)

### Tremstats Too 1.9.3

 - Name:    Rezyn
 - E-Mail:  johne@verizon.net
 - Website: [http://rezyn.mercenariesguild.net](http://rezyn.mercenariesguild.net)
 - IRC:     find me in #tremulous on freenode.net

### Tremstats 0.6.0

Original tremstats release by DASPRiD

 - Name:    Ben 'DASPRiD' Scholzen
 - Jabber:  dasprid@jabber.org
 - ICQ:     105677955
 - IRC:     #DASPRiDs @ quakenet
 - E-Mail:  mail@dasprids.de
 - Website: [http://www.dasprids.de](http://www.dasprids.de)

