OpenVPN-PHP-Gui
=======================

MIT License - See LICENSE.txt

OpenVPN-PHP-Gui is a php script to manage your openvpn installation.  
This is geared for beginners that don't feel comfortable on the command line.


IMPORTANT:
This uses phpseclib to run terminal commands, and thus requires root password for openvpn setup and management

Features:

* Complete initial configuration of OpenVPN - buggy, but works
* View and Manage Certificates - partially working 
* Edit your openvpn.conf file
* Generate and send/download certificate bundles - partially working


If running a Debian based-system (apt-get based), the script will run apt-get install openvpn for you.

Recommended packages to install for Debian Systems:

* lsb-release: For getting name and version of release
* sudo:  For the installer php script

See http://www.youtube.com/watch?v=dNWub40l_GQ to see a video of OpenVPN-PHP-Gui in action (may be an older version). 


INSTALL INSTRUCTIONS

Download zip file, unzip folder into your web root: (i.e. /var/www/openvpn-php-gui)

Open install.php in a text editor, change user variabes (if needed). They are the default variables for a debian server.

Navigate to "http://localhost/openvpn-php-gui/install.php" or whatever IP address your server has, if remote.

Follow instructions.

END
