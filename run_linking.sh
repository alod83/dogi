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

echo "WELCOME TO THE DOGI linking procedure"
echo "Make sure that mysql and PHP are in your PATH"

user="root"
password=""

cd dbpedia
./run_dbpedia.sh -o $old_db -n $new_db
cd ../viaf
./run_viaf.sh -o $old_db -n $new_db
cd ../bpr
./run_bpr.sh -o $old_db -n $new_db
cd ..
