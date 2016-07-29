#!/bin/sh

# This script runs a complete linking to bpr
# WARNING: This script works without mysql password. Modify to work with password 

source ../utilities/input.sh

echo "BPR EXTRACTION AND FILTERING PROCEDURE"

# WARNING: set mysql password
echo "Duplicating all tables from $old_db to $new_db"
mysql -u $user $mysqlpassword -e "USE $new_db; 
CREATE TABLE IF NOT EXISTS tabBPR LIKE $old_db.tabBPR;
INSERT tabBPR SELECT * FROM $old_db.tabBPR;"
echo "Done"

# run bpr extractor to extract links
echo "Extracting new URLs from BPR"
php bpr_extract_urls.php -o $old_db -n $new_db -u $user $cpassword
echo "Done" 
