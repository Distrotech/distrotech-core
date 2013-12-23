#!/usr/bin/perl

use Digest::MD5 qw(md5);

##
## ./md5.pl <FQDN> "<IPADDR>+<SNBITS>"
##


$hname=@ARGV[0];
$ip=@ARGV[1];
$version="3.5";

$todec=encode();
print "Serial $todec\n";

sub decode {
  local($keyin)=@_;
  $keyin=join("",split(/-/,$keyin));

  $rnum=substr($keyin,0,2);
  $rnum=hex($rnum);
  $rnum=$rnum*256;

  $knum=substr($keyin,2,2);
  $knum=hex($knum);

  $scode=substr($keyin,4,2);
  $scode=hex($scode);

  $idnum=$knum+$rnum;

  if ($scode >= 128) {
    $keyname="$hname $ip x";
    $scode=$scode-128;
  } else {
    $keyname="$ip $hname x";
  }

  if ($scode >= 64) {
    $idrem=$idnum % 9;
    $keyname.=" $idrem";
    $scode=$scode-64;
  }

  $keyname=lc($keyname);

  if ($scode >= 32) {
    $scode=$scode-32;
    $keyname=join("^",split(/i/,$keyname));
  }

  if ($scode >= 16) {
    $scode=$scode-16;
    $keyname=join("%",split(/\./,$keyname));
  }

  if ($scode >= 8) {
    $scode=$scode-8;
    $keyname=join("\$",split(/a/,$keyname));
  }

  if ($scode >= 4) {
    $scode=$scode-4;
    $keyname=join("#",split(/o/,$keyname));
  }

  if ($scode >= 2) {
    $scode=$scode-2;
    $keyname=join("@",split(/e/,$keyname));
  }

  if ($scode >= 1) {
    $keyname=join("!",split(/u/,$keyname));
  }


  $key=substr($keyin,6);
  $key=undigest($key);

  $keyorig=md5("$idnum$version$keyname");

}

sub encode {
  $idnum=int(rand(65535));
 
  $scode=int(rand(255));

  $knum=$idnum % 256;
  $idn2=($idnum-$knum)/256;
  $rnum=$idn2 % 256;


  $rchr=chr($rnum);
  $kchr=chr($knum);
  $ocode=chr($scode);

  if ($scode >= 128) {
    $keytag="$hname $ip x";
    $scode=$scode-128;
  } else {
    $keytag="$ip $hname x";
  }

  if ($scode >= 64) {
    $idrem=$idnum % 9;
    $keytag.=" $idrem";
    $scode=$scode-64;
  }

  $keytag=lc($keytag);

  if ($scode >= 32) {
    $scode=$scode-32;
    $keytag=join("^",split(/i/,$keytag));
  }

  if ($scode >= 16) {
    $scode=$scode-16;
    $keytag=join("%",split(/\./,$keytag));
  }

  if ($scode >= 8) {
    $scode=$scode-8;
    $keytag=join("\$",split(/a/,$keytag));
  }

  if ($scode >= 4) {
    $scode=$scode-4;
    $keytag=join("#",split(/o/,$keytag));
  }

  if ($scode >= 2) {
    $scode=$scode-2;
    $keytag=join("@",split(/e/,$keytag));
  }

  if ($scode >= 1) {
    $keytag=join("!",split(/u/,$keytag));
  }

  $iddig=md5("$idnum$version$keytag");

  $kout=mkdigest($rchr);
  $kout.=mkdigest($kchr);
  $kout.=mkdigest($ocode);
  $kout.=mkdigest($iddig);
  $kout=sprintf("%s-%s-%s-%s-%s-%s-%s",substr($kout,0,6),substr($kout,6,6),substr($kout,12,6),substr($kout,18,6),
                 substr($kout,24,6),substr($kout,30,6),substr($kout,36));
  return $kout;
}

sub mkdigest {
  $dig="";
  local($instr)=@_;
  @todig=split(//,$instr);
  foreach $ndig (@todig) {
    $dord=ord($ndig);
    $odig=sprintf("%X",$dord);
    if (length($odig) < 2) {
      $odig="0$odig";
    }
    $dig.=$odig;
  }
  return $dig;
}


sub undigest {
  $md5out="";
  local($keyin)=@_;
  for(pos=0;$pos<32;$pos=$pos+2) {
    $char=substr($keyin,$pos,2);
    $char=hex($char);
    $md5out.=chr($char);
  }
  return $md5out
}
