#!/bin/bash

argument1=$1
argument2=$((argument1 + 20)) 
parallel --will-cite -j 2 php -f gap_direct_insert_archiveteam.php {} ::: {"$argument1","$argument2"} &
wait
echo "All Complete"
