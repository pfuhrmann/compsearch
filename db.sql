CREATE TABLE `companies` (
  `id` varchar(50),
  `name` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `hq` varchar(255) DEFAULT NULL,
  `industry` varchar(255) DEFAULT NULL,
  `tags` mediumtext,
  `launch_date` int(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
);
