--
-- Database: `mls`
--

CREATE DATABASE IF NOT EXISTS `mls` DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ignored_listings`
--

CREATE TABLE IF NOT EXISTS `ignored_listings` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` char(23) NOT NULL,
  `listing_id` int(20) unsigned NOT NULL,
  `date` int(12) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` char(23) NOT NULL,
  `date` int(20) unsigned NOT NULL,
  UNIQUE KEY `unique_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `user_options`
--

CREATE TABLE IF NOT EXISTS `user_options` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(20) unsigned NOT NULL,
  `address` bit(1) NOT NULL,
  `photos` bit(1) NOT NULL,
  `type` enum('sale','rent') NOT NULL,
  `minPrice` decimal(7,2) NOT NULL,
  `maxPrice` decimal(7,2) NOT NULL,
  `date` int(20) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;
