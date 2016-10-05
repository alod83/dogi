#!/bin/bash

# This script runs a complete linking to dbpedia
# WARNING: This script works without mysql password. Modify to work with password 

source utilities/input.sh

echo "WELCOME TO THE DOGI linking procedure"
echo "Make sure that mysql and PHP are in your PATH"

cd dbpedia
./run_dbpedia.sh -o $old_db -n $new_db -u $user -p $password
cd ../viaf
./run_viaf.sh -o $old_db -n $new_db -u $user -p $password
cd ../bpr
./run_bpr.sh -o $old_db -n $new_db -u $user -p $password
cd ..
