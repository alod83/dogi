#!/bin/bash

# This script runs a complete linking to dbpedia
# WARNING: This script works without mysql password. Modify to work with password 

source ../utilities/input.sh

echo "DBPEDIA EXTRACTION AND FILTERING PROCEDURE"

# WARNING: set mysql password
echo "Duplicating table tabDBpedia from $old_db to $new_db"
mysql -u $user $mysqlpassword -e "USE $new_db; CREATE TABLE IF NOT EXISTS tabDBpedia LIKE $old_db.tabDBpedia;INSERT tabDBpedia SELECT * FROM $old_db.tabDBpedia;"; 
echo "Done"

# run dbpedia extractor to extract links
echo "Extracting new URLs from DBPedia"
php dbpedia_extract_urls.php -o $old_db -n $new_db -u $user $cpassword
echo "Done"

# run dbpedia filter to filter only to some classes
echo "Filtering extracted URLs"
php dbpedia_filter_classes.php -n $new_db -u $user $cpassword


# Remove all unfiltered links
# WARNING: set mysql password
mysql -u $user $mysqlpassword -e "USE $new_db; DELETE FROM tabDBpedia WHERE Filtered = 'NO';"; 
echo "Done"