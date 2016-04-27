#~/bin/sh

exec ./bin/phiremock &
PHIREMOCK_PID=$!
./vendor/bin/codecept run -c tests
kill -9 $PHIREMOCK_PID

