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

--
-- Table structure for table `unique_ids`
--

CREATE TABLE IF NOT EXISTS `unique_ids` (
  `unique_id` char(23) NOT NULL,
  UNIQUE KEY `unique_id` (`unique_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
