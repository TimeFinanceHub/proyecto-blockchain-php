CREATE TABLE `blockchain` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `block_index` int(11) NOT NULL,
  `timestamp` bigint(20) NOT NULL,
  `data` text NOT NULL,
  `previous_hash` varchar(255) NOT NULL,
  `hash` varchar(255) NOT NULL,
  `nonce` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;