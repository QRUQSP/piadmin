#!/bin/sh

mv /ciniki/sites/qruqsp.local/logs/access.log /ciniki/sites/qruqsp.local/logs/access.`date --date=-1month +%Y-%m`.log
mv /ciniki/sites/qruqsp.local/logs/error.log /ciniki/sites/qruqsp.local/logs/error.`date --date=-1month +%Y-%m`.log
sudo service apache2 reload
gzip /ciniki/sites/qruqsp.local/logs/access.`date --date=-1month +%Y-%m`.log
gzip /ciniki/sites/qruqsp.local/logs/error.`date --date=-1month +%Y-%m`.log
