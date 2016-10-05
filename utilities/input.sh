#!/bin/bash

# This script runs a complete linking to dbpedia

usage() { echo "USAGE: -o old_db -n new_db -u user -p password" ; exit 1; }

while getopts "o:n:u:p:" opt; do
	case $opt in
 		o)
      		old_db=$OPTARG
      		;;
    		n)
      		new_db=$OPTARG
      		;;
      	u)
      		user=$OPTARG
      		;;
      	p)
      		password=$OPTARG
      		;;
    		*)
      		usage
      		;;
  esac
done

if [ -z "${old_db}" ] || [ -z "${new_db}" ] || [ -z "${user}" ] || [ -z "${password}" ]; then
    usage
fi

cpassword="-p $password"
mysqlpassword="-p$password"
