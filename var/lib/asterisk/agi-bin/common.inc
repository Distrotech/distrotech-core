$pg_handle=pg_connect("host=localhost dbname=asterisk user=asterisk password=zatelepass");

function odbcquery($sqlquery) {
  global $pg_handle;

  $odbcexec=pg_query($pg_handle,$sqlquery);
  if (pg_num_rows($odbcexec) > 0) {
    return pg_fetch_row($odbcexec,0);
  } else {
    return -1;
  }
}
