#!/bin/bash

#test result of /project/name metadata result for nonexistent project

DIR=$(cd `dirname $0` && pwd)
source $DIR/include.sh

# now submit req
proj="DNEProject"

runurl="${APIURL}/project/${proj}"

echo "TEST: get mising project ${proj}..."

sh $SRC_DIR/api-expect-error.sh "${runurl}" "${params}" "project does not exist: DNEProject" || exit 2
echo "OK"


rm $DIR/curl.out

