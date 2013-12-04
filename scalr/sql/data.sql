INSERT INTO `role_categories` (`id`, `env_id`, `name`) VALUES
(1, 0, 'Base'),
(2, 0, 'Databases'),
(3, 0, 'Application Servers'),
(4, 0, 'Load Balancers'),
(5, 0, 'Message Queues'),
(6, 0, 'Caches'),
(7, 0, 'Cloudfoundry'),
(8, 0, 'Mixed');


INSERT INTO `account_users` (`id`, `account_id`, `status`, `email`, `fullname`, `password`, `dtcreated`, `dtlastlogin`, `type`, `comments`) VALUES
(1, 0, 'Active', 'admin', 'Scalr Admin', '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', '2011-09-09 10:49:07', '2011-12-06 02:28:16', 'ScalrAdmin', NULL);
--
-- Dumping data for table `config`
--


INSERT INTO `default_records` (`id`, `clientid`, `type`, `ttl`, `priority`, `value`, `name`) VALUES
(1, 0, 'CNAME', 14400, 0, '%hostname%', 'www');

--
-- Dumping data for table `ipaccess`
--

INSERT INTO `ipaccess` (`id`, `ipaddress`, `comment`) VALUES
(1, '*.*.*.*', 'Disable IP whitelist');

--
-- Dumping data for table `scaling_metrics`
--

INSERT INTO `scaling_metrics` (`id`, `client_id`, `env_id`, `name`, `file_path`, `retrieve_method`, `calc_function`, `algorithm`, `alias`) VALUES
(1, 0, 0, 'LoadAverages', NULL, NULL, 'avg', 'Sensor', 'la'),
(2, 0, 0, 'FreeRam', NULL, NULL, 'avg', 'Sensor', 'ram'),
(3, 0, 0, 'URLResponseTime', NULL, NULL, NULL, 'Sensor', 'http'),
(4, 0, 0, 'SQSQueueSize', NULL, NULL, NULL, 'Sensor', 'sqs'),
(5, 0, 0, 'DateAndTime', NULL, NULL, NULL, 'DateTime', 'time'),
(6, 0, 0, 'BandWidth', NULL, NULL, NULL, 'Sensor', 'bw');

