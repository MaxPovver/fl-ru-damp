#!/bin/bash 

echo '' > /tmp/failmail/55x.log;
cat /var/log/maillog | grep "said: 55[0-3] " > /tmp/failmail/55x.log;
echo '' > /tmp/failmail/55x-emails.log; 
cat /tmp/failmail/55x.log | egrep -o "to=<.*>," | egrep -o "\b[a-zA-Z0-9.-]+@[a-zA-Z0-9.-]+\.[a-zA-Z0-9.-]+\b" > /tmp/failmail/55x-emails.log;

php /var/www/_freelance/test/mail/stop_subscr.php