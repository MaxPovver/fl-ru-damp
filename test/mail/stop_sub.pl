#!/usr/bin/perl

$dbname='/tmp/failmail/stop_sub_cur.txt';
$dbname1='/tmp/failmail/stop_sub_all.txt';
$reportname='/tmp/failmail/stop-';
$newmail='/tmp/failmail/55x-emails.log';

($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime(time()-60*60*4);

if ($hour == 23) {
  $rd=sprintf("%4d.%02d.%02d",($year+1900),($mon+1),$mday);
  `mv $dbname1 $reportname$rd.log`;
  `touch $dbname1`;
};

open I, 'grep "550 Message was not accepted -- invalid mailbox" /var/log/maillog |';
open O, ">$dbname";

while (<I>) {
  chomp;
  /<(.*?)>/;
  $email=$1;
  print O "$email\n";
};

close I;
close O;


`sort $dbname | uniq >> $dbname1`;
#`rm -f $dbname`;
#`mv $dbname.new $dbname`;
#`cat $dbname >>$dbname1`;

`sort $dbname1 > $dbname1.srt`;
`cat $dbname1.srt | uniq -u > $newmail`;
`cat $dbname1.srt | uniq > $dbname1.new`;
`rm -f $dbname1`;
`rm -f $dbname1.srt`;
`mv $dbname1.new $dbname1`;

$r=`/usr/bin/php /var/www/_freelance/test/mail/stop_subscr.php`;

print $r;

### temp code to block via postfix

open I, "cat $dbname1 $reportname* |";
open O, ">/etc/postfix/dropmail";
while (<I>) {
  chomp;
  print O "$_\t\t\tdevnull\@localhost\n";
};
    `postmap /etc/postfix/dropmail`;
    `service postfix reload`;
    
exit;

