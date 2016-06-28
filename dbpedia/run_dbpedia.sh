#!/bin/sh

# This script runs a complete linking to dbpedia
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

echo "WELCOME TO THE DBPEDIA EXTRACTION AND FILTERING PROCEDURE"
echo "Make sure that mysql and PHP are in your PATH"

echo "Duplicating table tabDBpedia from $old_db to $new_db"
mysql -u root -e "USE $new_db; CREATE TABLE IF NOT EXISTS tabDBpedia LIKE $old_db.tabDBpedia;INSERT tabDBpedia SELECT * FROM $old_db.tabDBpedia;"; 
echo "Done"

# run dbpedia extractor to extract links
echo "Extracting new URLs from DBPedia"
php dbpedia_extract_urls.php -o $old_db -n $new_db
echo "Done"

# run dbpedia filter to filter only to some classes
echo "Filtering extracted URLs"
php dbpedia_filter_classes.php -n $new_db
echo "Done"