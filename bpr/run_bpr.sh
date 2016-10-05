#!/bin/bash

# This script runs a complete linking to bpr

source ../utilities/input.sh

echo "BPR EXTRACTION AND FILTERING PROCEDURE"

# Commented, not necessary anymore, already done by the main script (not linking)
#echo "Duplicating all tables from $old_db to $new_db"
#mysql -u $user $mysqlpassword -e "USE $new_db; 
#CREATE TABLE IF NOT EXISTS BPR_riviste LIKE $old_db.BPR_riviste;
#INSERT BPR_riviste SELECT * FROM $old_db.BPR_riviste;"
#echo "Done"

# run bpr extractor to extract links
echo "Extracting new URLs from BPR"
php bpr_extract_urls.php -o $old_db -n $new_db -u $user $cpassword
echo "Done" 

# TODO visualize if there are exact matches
echo "Number of non exact matches: " $(mysql -u $user $mysqlpassword -e "SELECT COUNT(*) FROM $old_db.BPR_riviste WHERE matchesatto = 0;")