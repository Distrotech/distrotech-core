#!/bin/bash

rm /var/spool/apache/htdocs/userguides/images/Sentry.gif
ln -s /var/spool/apache/htdocs/images/Sentry.gif /var/spool/apache/htdocs/userguides/images/Sentry.gif

rm /var/spool/apache/htdocs/userguides/images/banner.gif
ln -s /var/spool/apache/htdocs/images/banner.gif /var/spool/apache/htdocs/userguides/images/banner.gif

rm /var/spool/apache/htdocs/userguides/netsentry.css
ln -s /var/spool/apache/htdocs/netsentry.css /var/spool/apache/htdocs/userguides/netsentry.css


lynx -source "http://firewall/gdimg/img.php?text=Up+(User+Guides)" > /var/spool/apache/htdocs/userguides/instman/images/home.png
lynx -source "http://firewall/gdimg/img_a.php?text=Up+(User+Guides)" > /var/spool/apache/htdocs/userguides/instman/images/home_a.png

lynx -source http://firewall/gdimg/img.php?text=Network+Config > /var/spool/apache/htdocs/userguides/instman/images/networkb.png
lynx -source http://firewall/gdimg/img_a.php?text=Network+Config > /var/spool/apache/htdocs/userguides/instman/images/networkb_a.png

lynx -source http://firewall/gdimg/img.php?text=User+Profiles > /var/spool/apache/htdocs/userguides/instman/images/uprofile.png
lynx -source http://firewall/gdimg/img_a.php?text=User+Profiles > /var/spool/apache/htdocs/userguides/instman/images/uprofile_a.png

lynx -source http://firewall/gdimg/img.php?text=Proxy+Settings > /var/spool/apache/htdocs/userguides/instman/images/proxy.png
lynx -source http://firewall/gdimg/img_a.php?text=Proxy+Settings > /var/spool/apache/htdocs/userguides/instman/images/proxy_a.png

lynx -source http://firewall/gdimg/img.php?text=Email+Config > /var/spool/apache/htdocs/userguides/instman/images/outlook.png
lynx -source http://firewall/gdimg/img_a.php?text=Email+Config > /var/spool/apache/htdocs/userguides/instman/images/outlook_a.png

lynx -source http://firewall/gdimg/img.php?text=LDAP+Config > /var/spool/apache/htdocs/userguides/instman/images/ldap.png
lynx -source http://firewall/gdimg/img_a.php?text=LDAP+Config > /var/spool/apache/htdocs/userguides/instman/images/ldap_a.png


lynx -source http://firewall/gdimg/img.php?text=Windows+9x/Me > /var/spool/apache/htdocs/userguides/images/win.png
lynx -source http://firewall/gdimg/img_a.php?text=Windows+9x/Me > /var/spool/apache/htdocs/userguides/images/win_a.png

lynx -source http://firewall/gdimg/img.php?text=Windows+XP > /var/spool/apache/htdocs/userguides/images/winxp.png
lynx -source http://firewall/gdimg/img_a.php?text=Windows+XP > /var/spool/apache/htdocs/userguides/images/winxp_a.png

