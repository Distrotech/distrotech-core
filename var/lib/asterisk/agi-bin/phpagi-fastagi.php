#!/usr/bin/php -q
<?php
/*
#    Copyright (C) 2002  <Gregory Hinton Nietsky>
#    Copyright (C) 2005  <ZA Telecomunications>
#
#    This program is free software; you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation; either version 2 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program; if not, write to the Free Software
#    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

  require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'phpagi.php');

  if(!isset($fastagi->config['fastagi']['basedir']))
    $fastagi->config['fastagi']['basedir'] = dirname(__FILE__);

  // perform some security checks

  $script = $fastagi->config['fastagi']['basedir'] . DIRECTORY_SEPARATOR . $fastagi->request['agi_network_script'];

  // in the same directory (or subdirectory)
  $mydir = dirname($fastagi->config['fastagi']['basedir']) . DIRECTORY_SEPARATOR;
  $dir = dirname($script) . DIRECTORY_SEPARATOR;
  if(substr($dir, 0, strlen($mydir)) != $mydir)
  {
    $fastagi->conlog("$script is not allowed to execute.");
    exit;
  }

  // make sure it exists
  if(!file_exists($script))
  {
    $fastagi->conlog("$script does not exist.");
    exit;
  }

  // drop privileges
  if(isset($fastagi->config['fastagi']['setuid']) && $fastagi->config['fastagi']['setuid'])
  {
    $owner = fileowner($script);
    $group = filegroup($script);
    if(!posix_setgid($group) || !posix_setegid($group) || !posix_setuid($owner) || !posix_seteuid($owner))
    {
      $fastagi->conlog("failed to lower privileges.");
      exit;      
    }
  }

  // make sure script is still readable
  if(!is_readable($script))
  {
    $fastagi->conlog("$script is not readable.");
    exit;
  }

  require_once($script);
?>
