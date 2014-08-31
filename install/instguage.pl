#!/usr/bin/perl

$last=0;
$|=1;

open(FL,"-");
while(<FL>) {
  if (/\sInstalling\s:\s(.*)\s([0-9]+)\/([0-9]+)/) {
    $perc=int(($2 * 100) / $3);
    if ( $last ne $perc) {
      print $perc . "\n";
      $last = $perc;
    }
  }
}
close(FL);
