#!/usr/bin/perl

$ndisk{'a'}=a;
$ndisk{'b'}=a;
$ndisk{'c'}=b;
$ndisk{'d'}=c;
$ndisk{'e'}=d;
$ndisk{'f'}=e;
$ndisk{'g'}=f;
$ndisk{'h'}=g;
$ndisk{'i'}=h;
$ndisk{'j'}=i;
$ndisk{'k'}=j;
$ndisk{'l'}=k;

$dmaj{'a'}=0;
$dmaj{'b'}=16;
$dmaj{'c'}=32;
$dmaj{'d'}=48;
$dmaj{'e'}=64;
$dmaj{'f'}=80;
$dmaj{'g'}=96;
$dmaj{'h'}=112;
$dmaj{'i'}=128;
$dmaj{'j'}=144;
$dmaj{'k'}=160;

open(FT,"/etc/fstab");
open(FTN,">/etc/fstab.new");
while(<FT>) {
  @data=split(/ /,$_);
  @ddat=split("/",@data[0]);
  if ((@ddat > 1) && (@ddat[2] ne "mapper") && (@ddat[1] eq "dev")) {
    $pcnt=chop(@ddat[2]);
    $disk=chop(@ddat[2]);
    $disk=$ndisk{$disk};
    $newdev=@ddat[0] . "/" . @ddat[1] . "/" . @ddat[2] . $disk . $pcnt;
    print FTN $newdev;
    print FTN substr($_,length(@data[0]));
    if ( ! -e $newdev) {
      print "mknod -m 600 " . $newdev . " b 8 " . ($pcnt+$dmaj{$disk}) . "\n";
    }
  } else {
    print FTN $_;
  }  
}
close(FT);
close(FTN);
