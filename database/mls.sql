--
-- Database: `mls`
--
CREATE DATABASE IF NOT EXISTS `mls` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `mls`;

-- --------------------------------------------------------

--
-- Table structure for table `ignored_listings`
--

CREATE TABLE IF NOT EXISTS `ignored_listings` (
  `listing_id` char(10) NOT NULL,
  `user_id` char(23) NOT NULL,
  `timestamp` int(12) unsigned NOT NULL,
  UNIQUE KEY `listing_id` (`listing_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` char(23) NOT NULL,
  `address` tinyint(1) NOT NULL DEFAULT '1',
  `photos` tinyint(1) NOT NULL DEFAULT '1',
  `search_type` enum('sale','rent','both') NOT NULL DEFAULT 'rent',
  `min_price` int(11) NOT NULL DEFAULT '0',
  `max_price` int(11) NOT NULL DEFAULT '0',
  `min_lot_size` int(11) NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL,
  UNIQUE KEY `unique_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;