#!/bin/sh

# This script runs a complete linking to bpr
# WARNING: This script works without mysql password. Modify to work with password 

usage() { echo "USAGE: -o old_db -n new_db": ; exit 1; }

while getopts "o:n:" opt; do
	case $opt in
 		o)
      		old_db=$OPTARG
      		;;
    		n)
      		new_db=$OPTARG
      		;;
    		*)
      		usage
      		;;
  esac
done


if [ -z "${old_db}" ] || [ -z "${new_db}" ]; then
    usage
fi

user="root"
password=""
echo "WELCOME TO THE BPR EXTRACTION AND FILTERING PROCEDURE"
echo "Make sure that mysql and PHP are in your PATH"

# WARNING: set mysql password
echo "Duplicating all tables from $old_db to $new_db"
mysql -u $user -p$password -e "USE $new_db; 
CREATE TABLE IF NOT EXISTS tabBPR LIKE $old_db.tabBPR;
INSERT tabBPR SELECT * FROM $old_db.tabBPR;"
echo "Done"

# run bpr extractor to extract links
echo "Extracting new URLs from BPR"
php bpr_extract_urls.php -o $old_db -n $new_db -u $user -p $password
echo "Done" 
