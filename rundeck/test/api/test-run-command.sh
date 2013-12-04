#!/bin/bash

# TEST: /api/run/command action

DIR=$(cd `dirname $0` && pwd)
source $DIR/include.sh

proj="test"

execargs="echo this is a test of /api/run/command"

# now submit req
runurl="${APIURL}/run/command"

echo "TEST: /api/run/command should fail with no project param"
sh $SRC_DIR/api-expect-error.sh "${runurl}" "project=" 'parameter "project" is required' && echo "OK" || exit 2


echo "TEST: /api/run/command should fail with no exec param"
params="project=${proj}"
sh $SRC_DIR/api-expect-error.sh "${runurl}" "${params}" 'parameter "exec" is required' && echo "OK" || exit 2

echo "TEST: /api/run/command should succeed and return execution id"
# make api request
$CURL -H "$AUTHHEADER" --data-urlencode "exec=${execargs}" ${runurl}?${params} > $DIR/curl.out
if [ 0 != $? ] ; then
    errorMsg "FAIL: failed query request"
    exit 2
fi

sh $SRC_DIR/api-test-success.sh $DIR/curl.out || exit 2
execid=$($XMLSTARLET sel -T -t -o "Execution started with ID: " -v "/result/execution/@id" -n $DIR/curl.out)
if [ "" == "${execid}" ] ; then
    errorMsg "FAIL: expected execution id in result: ${execid}"
    exit 2
fi

echo "OK"

rm $DIR/curl.out
