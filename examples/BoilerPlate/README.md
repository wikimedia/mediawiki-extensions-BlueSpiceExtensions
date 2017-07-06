#BoilerPlate
This is a simple boilerplate that features the most basic structures of a
**BlueSpice MediaWiki** extension.

##Files
* BoilerPlate.setup.php - This is the **setup** file. It just registers static
information with the MediaWiki application and the BlueSpiceFoundation
* BoilerPlate.class.php - This file holds the main PHP class of the extension.
It get's initialized on startup time by BlueSpiceFoundation

###BoilerPlate.setup.php
... further documentation here
###BoilerPlate.class.php
Important methods to implement:
	public function __construct()
sets up all the information about an extension. This usually happens very early
during runtime execution of MediaWiki and the context (e.g. user, rights , database
access etc.) are not fully available. Do not put any logic in here. Instead, use
	initExt();
for initializing the extension and defining its core functionality. Examples are
registering hooks, defining settings, registering permissions etc.