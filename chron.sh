#!/bin/bash
while true; do
    curl 'http://dev.ccmelive.com/dev?chron' 2>/dev/null | while read x; do echo $x; curl "$x" > /dev/null 2>&1; done
    sleep 2
done
