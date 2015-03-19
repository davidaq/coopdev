#!/bin/bash
while true; do
    curl 'http://localhost/coopdev/dev?chron' 2>/dev/null | while read x; do curl "$x" > /dev/null 2>&1; done
    sleep 10
done
