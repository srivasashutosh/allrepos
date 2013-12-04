PHPUnit tests
-------------

To install phpunit read the official [documentation](http://phpunit.de/manual/3.7/en/installation.html)

To run all the tests please use:

```bash 
$ phpunit -c path-to-scalr
```

If you want to run particular test you can use construction 
`phpunit -c path-to-scalr path-to-test1 path-to-test2 ...` 
as an example below:

```bash 
$ cd /var/www/scalr
$ phpunit app/src/Scalr/Tests/SoftwareDependencyTest.php
```

By default all functional thests are skipped. If you want to enable them you should inspect your config file
whether `skip_functional_tests` is `false` in `phpunit` section. `test_envid` option must be provided as well.

