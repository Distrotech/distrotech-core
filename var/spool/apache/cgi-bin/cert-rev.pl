#!/usr/bin/perl

use CGI;

CGI::ReadParse(*form_data);

print "Content-Type: application/x-netscape-revocation\n\n";

$num=$form_data{'certid'};

$cert=`openssl ca -status $num 2>&1 |grep -E "$num=Valid \\(V\\)"`;

if ( $cert ne "") {
  print "0";
} elsif ($num > 0) {
  print "1";
} else {
  print "0";
}
          
