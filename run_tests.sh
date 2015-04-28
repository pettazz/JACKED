#!/usr/bin/env sh

mysql -u root -h 127.0.0.1 -e 'DROP DATABASE IF EXISTS jacked_test; CREATE DATABASE jacked_test;'
mysql -u root -h 127.0.0.1 jacked_test < schema/jacked_structure.sql

if [ $# -eq 0 ]; then
    phpunit test/src 
else
    ARGS=("$@")
    phpunit ${ARGS[@]/#/test/src/}
fi

mysql -u root -h 127.0.0.1 -e "DROP DATABASE jacked_test;"