#!/bin/bash

rm -rf ../app/cache/dev

for type in user semestered_training training session trainee trainer participation inscription email
do
   php5 -d memory_limit=2G ../app/console fos:elastica:populate --no-debug --index=sygefor3 --type=$type
done
