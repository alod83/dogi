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
echo "Duplicating all tables from $old_db to $new_db"
mysql -u $user -p$password -e "USE $new_db; 
CREATE TABLE IF NOT EXISTS tabVIAF LIKE $old_db.tabVIAF;
INSERT tabVIAF SELECT * FROM $old_db.tabVIAF; 
CREATE TABLE IF NOT EXISTS tabSameAsVIAF LIKE $old_db.tabSameAsVIAF;
INSERT tabSameAsVIAF SELECT * FROM $old_db.tabSameAsVIAF; 
CREATE TABLE IF NOT EXISTS tabVariantiVIAF LIKE $old_db.tabSameAsVIAF;
INSERT tabVariantiVIAF SELECT * FROM $old_db.tabVariantiVIAF; 
CREATE TABLE IF NOT EXISTS tabOpereVIAF LIKE $old_db.tabOpereVIAF;
INSERT tabOpereVIAF SELECT * FROM $old_db.tabOpereVIAF; 
CREATE TABLE IF NOT EXISTS tabParole LIKE $old_db.tabParole;
INSERT tabParole SELECT * FROM $old_db.tabParole;
CREATE TABLE IF NOT EXISTS tabParoleVIAF LIKE $old_db.tabParoleVIAF;INSERT tabParoleVIAF; SELECT * FROM $old_db.tabParoleVIAF;"; 
CREATE TABLE IF NOT EXISTS legParoleVIAF LIKE $old_db.legParoleVIAF;
INSERT legParoleVIAF; SELECT * FROM $old_db.legParoleVIAF;"; 
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
# TODO: remove from table people who do not match name and surname
mysql -u root -e "USE $new_db;
DELETE FROM tabVIAF WHERE Filtered = 'TOBECHECKED' AND 
tabVIAF.IDViaf NOT IN (SELECT DISTINCT tabVIAF.IDViaf FROM tabVariantiVIAF, tabResponsabilita,tabVIAF
WHERE tabVariantiVIAF.IDViaf = tabVIAF.IDViaf AND tabResponsabilita.IDResponsabilita = tabVIAF.IDResponsabilita
AND tabVariantiVIAF.NomeAlternativo LIKE CONCAT(PrimoElemento,SecondoElemento));"

# extract words from dogi titles
php dogi_extract_tokens.php -o $old_db -n $new_db -u $user -p $password;
php viaf_extract_tokens.php -o $old_db -n $new_db -u $user -p $password;

# Remove all unfiltered links
mysql -u root -e "USE $new_db;
DELETE FROM tabVIAF WHERE Filtered = 'TOBECHECKED' AND IDViaf NOT IN (SELECT DISTINCT idViaf FROM `legParoleVIAF`,tabParoleVIAF WHERE legParoleVIAF.idToken = tabParoleVIAF.idToken AND tabParoleVIAF.valore IN (SELECT valore FROM tabParole WHERE quantita > 1000)); 
DELETE FROM tabVariantiVIAF WHERE IDViaf NOT IN (SELECT IDViaf FROM tabVIAF);
DELETE FROM tabSameAsVIAF WHERE IDViaf NOT IN (SELECT IDViaf FROM tabVIAF);
DELETE FROM tabOpereVIAF WHERE IDViaf NOT IN (SELECT IDViaf FROM tabVIAF);
UPDATE tabVIAF SET Filtered = 'YES';"
echo "Done"