
-- --------------------------------------------------------

--
-- Table structure for table `ignored_listings`
--

CREATE TABLE IF NOT EXISTS `ignored_listings` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` char(23) NOT NULL,
  `listing_id` varchar(20) NOT NULL,
  `date` int(12) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` char(23) NOT NULL,
  `address` tinyint(1) NOT NULL DEFAULT '1',
  `photos` tinyint(1) NOT NULL DEFAULT '1',
  `search_type` enum('sale','rent') NOT NULL DEFAULT 'rent',
  `min_price` decimal(7,2) NOT NULL DEFAULT '0.00',
  `max_price` decimal(7,2) NOT NULL DEFAULT '2000.00',
  `timestamp` int(10) unsigned NOT NULL,
  UNIQUE KEY `unique_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
