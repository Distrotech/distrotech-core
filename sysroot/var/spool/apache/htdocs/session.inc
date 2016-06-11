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

include_once "/var/spool/apache/htdocs/cdr/auth.inc";

function sesopen($save_path,$session_name) {
  return(true);
}

function sesclose() {
  return(true);
}

function sesread ($id) {
  global $db;
  $getses=pg_query($db,"SELECT session_data FROM sessions WHERE session_id='" . $id . "'");
  if (pg_num_rows($getses) > 0) {
    $ses_info=pg_fetch_array($getses,0);
    session_decode($ses_info[0]);
    return($ses_info[0]);
  } else {
    return("");
  }
}

function seswrite ($id) {
  global $db;
  $sess_data=session_encode();
  $act_ses=pg_query($db,"SELECT session_logintime FROM sessions WHERE session_id = '" . $id . "'");
  if (pg_num_rows($act_ses) <= 0) {
    $setses=pg_query($db,"INSERT INTO sessions (reseller_id,session_id,session_logintime,session_dla,session_data) VALUES 
                                           (0,'" . $id . "'," . time() . "," . time() . ",'" . $sess_data . "')");
  } else {
    $setses=pg_query($db,"UPDATE sessions set session_dla=" . time() . ",session_data='" . $sess_data . "' WHERE session_id = '" . $id . "'");
  }
  if ($setses === false) {
    return(false);
  } else {
    return(true);
  }
}

function sesdestroy ($id) {
  global $db;
  if (pg_query($db,"DELETE FROM sessions WHERE session_id = '" . $id . "'") === false) {
    return(false);
  } else {
    return(false);
  }
}

function sesgc ($maxlifetime) {
  global $db;
  pg_query($db,"DELETE FROM sessions WHERE session_dla + " . $maxlifetime . " < " . time());
  return true;
}

session_set_save_handler ("sesopen", "sesclose", "sesread", "seswrite", "sesdestroy", "sesgc");
?>
