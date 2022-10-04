#!/bin/bash

parallel --will-cite -j 2 php -f gap_direct_insert_archiveteam.php {} ::: {"10$1","11$1"} &
wait
echo "All Complete"
