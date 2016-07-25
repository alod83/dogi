#!/bin/sh

# This script runs a complete linking to viaf
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
echo "WELCOME TO THE VIAF EXTRACTION AND FILTERING PROCEDURE"
echo "Make sure that mysql and PHP are in your PATH"

# WARNING: set mysql password
echo "Duplicating table tabVIAF from $old_db to $new_db"
mysql -u $user -p$password -e "USE $new_db; CREATE TABLE IF NOT EXISTS tabVIAF LIKE $old_db.tabVIAF;INSERT tabVIAF SELECT * FROM $old_db.tabVIAF;"; 
echo "Done"

echo "Duplicating table tabSameAsVIAF from $old_db to $new_db"
mysql -u $user -p$password -e "USE $new_db; CREATE TABLE IF NOT EXISTS tabSameAsVIAF LIKE $old_db.tabSameAsVIAF;INSERT tabSameAsVIAF SELECT * FROM $old_db.tabSameAsVIAF;"; 
echo "Done"

echo "Duplicating table tabOpereVIAF from $old_db to $new_db"
mysql -u $user -p$password -e "USE $new_db; CREATE TABLE IF NOT EXISTS tabOpereVIAF LIKE $old_db.tabOpereVIAF;INSERT tabOpereVIAF SELECT * FROM $old_db.tabOpereVIAF;"; 
echo "Done"

echo "Duplicating table tabParole from $old_db to $new_db"
mysql -u $user -p$password -e "USE $new_db; CREATE TABLE IF NOT EXISTS tabParole LIKE $old_db.tabParole;INSERT tabParole SELECT * FROM $old_db.tabParole;"; 
echo "Done"

# run viaf extractor to extract links
echo "Extracting new URLs from VIAF"
php viaf_extract_urls.php -o $old_db -n $new_db -u $user -p $password;
echo "Extracting Properties from VIAF"
php viaf_extract_properties.php -n $new_db -u $user -p $password;
echo "Extracting Works from VIAF"
php viaf_extract_works.php -n $new_db -u $user -p $password;
echo "Done"

# run dbpedia filter to filter only to some classes
echo "Filtering extracted URLs"
php dogi_extract_tokens.php -o $old_db -n $new_db -u $user -p $password;


# Remove all unfiltered links
# WARNING: set mysql password
#mysql -u root -e "USE $new_db; DELETE FROM tabDBpedia WHERE Filtered = 'NO';"; 
echo "Done"