#!/bin/bash

# This script runs a complete linking to dbpedia
# WARNING: This script works without mysql password. Modify to work with password 

source ../utilities/input.sh

echo "DBPEDIA EXTRACTION AND FILTERING PROCEDURE"

# Commented, not necessary anymore, already done by the main script (not linking)
#echo "Duplicating table DBpedia_autori from $old_db to $new_db"
#mysql -u $user $mysqlpassword -e "USE $new_db; CREATE TABLE IF NOT EXISTS DBpedia_autori LIKE $old_db.DBpedia_autori;INSERT DBpedia_autori SELECT * FROM $old_db.DBpedia_autori;"; 
#echo "Done"

# run dbpedia extractor to extract links
echo "Extracting new URLs from DBPedia"
php dbpedia_extract_urls.php -o $old_db -n $new_db -u $user $cpassword
echo "Done"

# run dbpedia filter to filter only to some classes
echo "Filtering extracted URLs"
php dbpedia_filter_classes.php -o $old_db -u $user $cpassword

# Remove all unfiltered links
mysql -u $user $mysqlpassword -e "USE $old_db; DELETE FROM DBpedia_autori WHERE Filtered = 'NO';"; 
echo "Done"