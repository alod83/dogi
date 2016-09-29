#!/bin/bash

# This script runs a complete linking to dbpedia

# WARNING: This script works without mysql password. Modify to work with password 
user="root"
password=""

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

cpassword="-p $password"
mysqlpassword="-p$password"
if [[  -z  $password  ]]; then
	cpassword=""
	mysqlpassword=""
fi

