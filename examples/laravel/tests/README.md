# HTTP response checks

`test.sh` uses host curl against the published localhost port and pipes response bodies into `assert-response.php` inside the server container. Assertions throw on failure without depending on PHP assertion configuration. Checks cover discovery, all service operations, extension preservation, persistence, UUID identity, nullable/mobile fields and ordinary 422 errors. The test script owns teardown.

by-dolly
