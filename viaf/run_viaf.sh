#!/bin/bash

# This script runs a complete linking to viaf
# WARNING: This script works without mysql password. Modify to work with password 

source ../utilities/input.sh

echo "VIAF EXTRACTION AND FILTERING PROCEDURE"
# Commented, not necessary anymore, already done by the main script (not linking)
#echo "Duplicating all tables from $old_db to $new_db"
#mysql -u $user $mysqlpassword -e "USE $new_db; 
#CREATE TABLE IF NOT EXISTS VIAF_autori LIKE $old_db.VIAF_autori;
#INSERT VIAF_autori SELECT * FROM $old_db.VIAF_autori; 
#CREATE TABLE IF NOT EXISTS tabSameAsVIAF LIKE $old_db.tabSameAsVIAF;
#INSERT tabSameAsVIAF SELECT * FROM $old_db.tabSameAsVIAF; 
#CREATE TABLE IF NOT EXISTS tabVariantiVIAF LIKE $old_db.tabVariantiVIAF;
#INSERT tabVariantiVIAF SELECT * FROM $old_db.tabVariantiVIAF; 
#CREATE TABLE IF NOT EXISTS tabOpereVIAF LIKE $old_db.tabOpereVIAF;
#INSERT tabOpereVIAF SELECT * FROM $old_db.tabOpereVIAF; 
#CREATE TABLE IF NOT EXISTS tabParole LIKE $old_db.tabParole;
#INSERT tabParole SELECT * FROM $old_db.tabParole;
#CREATE TABLE IF NOT EXISTS tabParoleVIAF LIKE $old_db.tabParoleVIAF;
#INSERT tabParoleVIAF SELECT * FROM $old_db.tabParoleVIAF;
#CREATE TABLE IF NOT EXISTS legParoleVIAF LIKE $old_db.legParoleVIAF;
#INSERT legParoleVIAF SELECT * FROM $old_db.legParoleVIAF;"; 
#echo "Done"

# run viaf extractor to extract links
echo "Extracting new URLs from VIAF"
php viaf_extract_urls.php -o $old_db -n $new_db -u $user $cpassword;
echo "Extracting Properties from VIAF"
php viaf_extract_properties.php -n $new_db -u $user $cpassword;
echo "Extracting Works from VIAF"
php viaf_extract_works.php -n $new_db -u $user $cpassword;
echo "Done"

# run dbpedia filter to filter only to some classes
echo "Filtering extracted URLs"
# TODO: remove from table people who do not match name and surname
mysql -u $user $mysqlpassword -e "USE $new_db;
DELETE FROM VIAF_autori WHERE Filtered = 'TOBECHECKED' AND NOT EXISTS (SELECT IDViaf FROM tabVariantiVIAF, tabResponsabilita WHERE VIAF_autori.IDViaf = tabVariantiVIAF.IDViaf AND tabResponsabilita.IDResponsabilita = VIAF_autori.IDResponsabilita AND (tabVariantiVIAF.NomeAlternativo LIKE CONCAT('%',SecondoElemento,'%', PrimoElemento,'%') OR tabVariantiVIAF.NomeAlternativo LIKE CONCAT('%',SecondoElemento,'%') OR tabVariantiVIAF.NomeAlternativo LIKE CONCAT('%',PrimoElemento,'%')));
DELETE FROM tabVariantiVIAF WHERE IDViaf NOT IN (SELECT IDViaf FROM VIAF_autori);
DELETE FROM tabSameAsVIAF WHERE IDViaf NOT IN (SELECT IDViaf FROM VIAF_autori);
DELETE FROM tabOpereVIAF WHERE IDViaf NOT IN (SELECT IDViaf FROM VIAF_autori)"

# extract words from dogi titles
php dogi_extract_tokens.php -o $old_db -n $new_db -u $user $cpassword;
php viaf_extract_tokens.php -n $new_db -u $user $cpassword;

# Remove all unfiltered links
mysql -u $user $mysqlpassword -e "USE $new_db;
DELETE FROM VIAF_autori WHERE Filtered = 'TOBECHECKED' AND NOT EXISTS (SELECT DISTINCT IDViaf FROM legParoleVIAF,tabParoleVIAF WHERE legParoleVIAF.idToken = tabParoleVIAF.idToken AND legParoleVIAF.IDViaf = VIAF_autori.IDViaf AND tabParoleVIAF.valore IN (SELECT valore FROM tabParole WHERE quantita > 1000)); 
DELETE FROM tabVariantiVIAF WHERE IDViaf NOT IN (SELECT IDViaf FROM VIAF_autori);
DELETE FROM tabSameAsVIAF WHERE IDViaf NOT IN (SELECT IDViaf FROM VIAF_autori);
DELETE FROM tabOpereVIAF WHERE IDViaf NOT IN (SELECT IDViaf FROM VIAF_autori);
DELETE FROM legParoleVIAF WHERE IDViaf NOT IN (SELECT IDViaf FROM VIAF_autori);
DELETE FROM tabParoleVIAF WHERE IDToken NOT IN (SELECT IDToken FROM legParoleVIAF);
UPDATE VIAF_autori SET Filtered = 'YES';"
echo "Done"