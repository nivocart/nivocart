-- --------------------------------------------------------

--
-- Database: `nivocart`
--

-- --------------------------------------------------------

SET SQL_MODE = "";

--
-- Table structure for table `nc_address`
--

DROP TABLE IF EXISTS `nc_address`;
CREATE TABLE `nc_address` (
  `address_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `firstname` varchar(32) NOT NULL,
  `lastname` varchar(32) NOT NULL,
  `company` varchar(32) NOT NULL,
  `company_id` varchar(32) NOT NULL,
  `tax_id` varchar(32) NOT NULL,
  `address_1` varchar(128) NOT NULL,
  `address_2` varchar(128) NOT NULL,
  `city` varchar(128) NOT NULL,
  `postcode` varchar(10) NOT NULL,
  `country_id` int(11) NOT NULL DEFAULT '0',
  `zone_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`address_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_administration`
--

DROP TABLE IF EXISTS `nc_administration`;
CREATE TABLE `nc_administration` (
  `administration_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `contrast` varchar(32) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`administration_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_administration`
--

INSERT INTO `nc_administration` (`administration_id`, `name`, `contrast`, `date_added`, `date_modified`) VALUES
(1, 'light', 'light', '2018-06-05 10:06:48', '2018-06-05 14:02:42'),
(2, 'dark', 'dark', '2018-06-05 10:06:48', '2018-06-05 14:02:42');

-- --------------------------------------------------------

--
-- Table structure for table `nc_affiliate`
--

DROP TABLE IF EXISTS `nc_affiliate`;
CREATE TABLE `nc_affiliate` (
  `affiliate_id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(32) NOT NULL,
  `lastname` varchar(32) NOT NULL,
  `email` varchar(96) NOT NULL,
  `telephone` varchar(32) NOT NULL,
  `fax` varchar(32) NOT NULL,
  `password` varchar(40) NOT NULL,
  `salt` varchar(9) NOT NULL,
  `company` varchar(32) NOT NULL,
  `website` varchar(255) NOT NULL,
  `address_1` varchar(128) NOT NULL,
  `address_2` varchar(128) NOT NULL,
  `city` varchar(128) NOT NULL,
  `postcode` varchar(10) NOT NULL,
  `country_id` int(11) NOT NULL,
  `zone_id` int(11) NOT NULL,
  `code` varchar(64) NOT NULL,
  `commission` decimal(4,2) NOT NULL DEFAULT '0.00',
  `tax` varchar(64) NOT NULL,
  `payment` varchar(6) NOT NULL,
  `cheque` varchar(100) NOT NULL,
  `paypal` varchar(64) NOT NULL,
  `bank_name` varchar(64) NOT NULL,
  `bank_branch_number` varchar(64) NOT NULL,
  `bank_swift_code` varchar(64) NOT NULL,
  `bank_account_name` varchar(64) NOT NULL,
  `bank_account_number` varchar(64) NOT NULL,
  `ip` varchar(40) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `approved` tinyint(1) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`affiliate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_affiliate_activity`
--

DROP TABLE IF EXISTS `nc_affiliate_activity`;
CREATE TABLE `nc_affiliate_activity` (
  `affiliate_activity_id` int(11) NOT NULL AUTO_INCREMENT,
  `affiliate_id` int(11) NOT NULL,
  `key` varchar(64) NOT NULL,
  `name` text CHARACTER SET utf8 NOT NULL,
  `ip` varchar(40) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`affiliate_activity_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_affiliate_login`
--

DROP TABLE IF EXISTS `nc_affiliate_login`;
CREATE TABLE `nc_affiliate_login` (
  `affiliate_login_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(96) NOT NULL,
  `ip` varchar(40) NOT NULL,
  `total` int(4) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`affiliate_login_id`),
  KEY `email` (`email`),
  KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_affiliate_product`
--

DROP TABLE IF EXISTS `nc_affiliate_product`;
CREATE TABLE `nc_affiliate_product` (
  `affiliate_product_id` int(11) NOT NULL AUTO_INCREMENT,
  `affiliate_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`affiliate_product_id`,`affiliate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_affiliate_transaction`
--

DROP TABLE IF EXISTS `nc_affiliate_transaction`;
CREATE TABLE `nc_affiliate_transaction` (
  `affiliate_transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `affiliate_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `description` text CHARACTER SET utf8 NOT NULL,
  `amount` decimal(15,4) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`affiliate_transaction_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_api_key`
--

DROP TABLE IF EXISTS `nc_api_key`;
CREATE TABLE `nc_api_key` (
  `api_key_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `code` varchar(128) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`api_key_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_attribute`
--

DROP TABLE IF EXISTS `nc_attribute`;
CREATE TABLE `nc_attribute` (
  `attribute_id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute_group_id` int(11) NOT NULL,
  `sort_order` int(3) NOT NULL,
  PRIMARY KEY (`attribute_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_attribute_description`
--

DROP TABLE IF EXISTS `nc_attribute_description`;
CREATE TABLE `nc_attribute_description` (
  `attribute_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`attribute_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_attribute_group`
--

DROP TABLE IF EXISTS `nc_attribute_group`;
CREATE TABLE `nc_attribute_group` (
  `attribute_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `sort_order` int(3) NOT NULL,
  PRIMARY KEY (`attribute_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_attribute_group_description`
--

DROP TABLE IF EXISTS `nc_attribute_group_description`;
CREATE TABLE `nc_attribute_group_description` (
  `attribute_group_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`attribute_group_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_banner`
--

DROP TABLE IF EXISTS `nc_banner`;
CREATE TABLE `nc_banner` (
  `banner_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`banner_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_banner_image`
--

DROP TABLE IF EXISTS `nc_banner_image`;
CREATE TABLE `nc_banner_image` (
  `banner_image_id` int(11) NOT NULL AUTO_INCREMENT,
  `banner_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `external_link` tinyint(1) NOT NULL,
  `sort_order` int(3) NOT NULL,
  `clicked` int(11) NOT NULL,
  PRIMARY KEY (`banner_image_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_banner_image_description`
--

DROP TABLE IF EXISTS `nc_banner_image_description`;
CREATE TABLE `nc_banner_image_description` (
  `banner_image_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `banner_id` int(11) NOT NULL,
  `title` varchar(64) NOT NULL,
  PRIMARY KEY (`banner_image_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_block_ip`
--

DROP TABLE IF EXISTS `nc_block_ip`;
CREATE TABLE `nc_block_ip` (
  `block_ip_id` int(11) NOT NULL AUTO_INCREMENT,
  `from_ip` varchar(32) NOT NULL,
  `to_ip` varchar(32) NOT NULL,
  PRIMARY KEY (`block_ip_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_category`
--

DROP TABLE IF EXISTS `nc_category`;
CREATE TABLE `nc_category` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `image` varchar(255) DEFAULT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `top` tinyint(1) NOT NULL DEFAULT '0',
  `column` int(3) NOT NULL DEFAULT '0',
  `sort_order` int(3) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`category_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_category_description`
--

DROP TABLE IF EXISTS `nc_category_description`;
CREATE TABLE `nc_category_description` (
  `category_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text CHARACTER SET utf8 NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `meta_keyword` varchar(255) NOT NULL,
  PRIMARY KEY (`category_id`,`language_id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_category_filter`
--

DROP TABLE IF EXISTS `nc_category_filter`;
CREATE TABLE `nc_category_filter` (
  `category_id` int(11) NOT NULL,
  `filter_id` int(11) NOT NULL,
  PRIMARY KEY (`category_id`,`filter_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_category_path`
--

DROP TABLE IF EXISTS `nc_category_path`;
CREATE TABLE `nc_category_path` (
  `category_id` int(11) NOT NULL,
  `path_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY (`category_id`,`path_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_category_to_layout`
--

DROP TABLE IF EXISTS `nc_category_to_layout`;
CREATE TABLE `nc_category_to_layout` (
  `category_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `layout_id` int(11) NOT NULL,
  PRIMARY KEY (`category_id`,`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_category_to_store`
--

DROP TABLE IF EXISTS `nc_category_to_store`;
CREATE TABLE `nc_category_to_store` (
  `category_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  PRIMARY KEY (`category_id`,`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_connection`
--

DROP TABLE IF EXISTS `nc_connection`;
CREATE TABLE `nc_connection` (
  `connection_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `backend` tinyint(1) NOT NULL,
  `frontend` tinyint(1) NOT NULL,
  PRIMARY KEY (`connection_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_connection_route`
--

DROP TABLE IF EXISTS `nc_connection_route`;
CREATE TABLE `nc_connection_route` (
  `connection_route_id` int(11) NOT NULL AUTO_INCREMENT,
  `connection_id` int(11) NOT NULL,
  `icon` varchar(64) NOT NULL,
  `title` varchar(64) NOT NULL,
  `route` varchar(255) NOT NULL,
  PRIMARY KEY (`connection_route_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_country`
--

DROP TABLE IF EXISTS `nc_country`;
CREATE TABLE `nc_country` (
  `country_id` int(11) NOT NULL AUTO_INCREMENT,
  `iso_code_2` varchar(2) NOT NULL,
  `iso_code_3` varchar(3) NOT NULL,
  `address_format` text CHARACTER SET utf8 NOT NULL,
  `postcode_required` tinyint(1) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`country_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_country`
--

INSERT INTO `nc_country` (`country_id`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `status`) VALUES
(1, 'AF', 'AFG', '', 0, 1),
(2, 'AL', 'ALB', '', 0, 1),
(3, 'DZ', 'DZA', '', 0, 1),
(4, 'AS', 'ASM', '', 0, 1),
(5, 'AD', 'AND', '', 0, 1),
(6, 'AO', 'AGO', '', 0, 1),
(7, 'AI', 'AIA', '', 0, 1),
(8, 'AQ', 'ATA', '', 0, 1),
(9, 'AG', 'ATG', '', 0, 1),
(10, 'AR', 'ARG', '', 0, 1),
(11, 'AM', 'ARM', '', 0, 1),
(12, 'AW', 'ABW', '', 0, 1),
(13, 'AU', 'AUS', '', 0, 1),
(14, 'AT', 'AUT', '', 0, 1),
(15, 'AZ', 'AZE', '', 0, 1),
(16, 'BS', 'BHS', '', 0, 1),
(17, 'BH', 'BHR', '', 0, 1),
(18, 'BD', 'BGD', '', 0, 1),
(19, 'BB', 'BRB', '', 0, 1),
(20, 'BY', 'BLR', '', 0, 1),
(21, 'BE', 'BEL', '{firstname} {lastname}\r\n{company}\r\n{address_1}\r\n{address_2}\r\n{postcode} {city}\r\n{country}', 0, 1),
(22, 'BZ', 'BLZ', '', 0, 1),
(23, 'BJ', 'BEN', '', 0, 1),
(24, 'BM', 'BMU', '', 0, 1),
(25, 'BT', 'BTN', '', 0, 1),
(26, 'BO', 'BOL', '', 0, 1),
(27, 'BA', 'BIH', '', 0, 1),
(28, 'BW', 'BWA', '', 0, 1),
(29, 'BV', 'BVT', '', 0, 1),
(30, 'BR', 'BRA', '', 0, 1),
(31, 'IO', 'IOT', '', 0, 1),
(32, 'BN', 'BRN', '', 0, 1),
(33, 'BG', 'BGR', '', 0, 1),
(34, 'BF', 'BFA', '', 0, 1),
(35, 'BI', 'BDI', '', 0, 1),
(36, 'KH', 'KHM', '', 0, 1),
(37, 'CM', 'CMR', '', 0, 1),
(38, 'CA', 'CAN', '', 0, 1),
(39, 'CV', 'CPV', '', 0, 1),
(40, 'KY', 'CYM', '', 0, 1),
(41, 'CF', 'CAF', '', 0, 1),
(42, 'TD', 'TCD', '', 0, 1),
(43, 'CL', 'CHL', '', 0, 1),
(44, 'CN', 'CHN', '', 0, 1),
(45, 'CX', 'CXR', '', 0, 1),
(46, 'CC', 'CCK', '', 0, 1),
(47, 'CO', 'COL', '', 0, 1),
(48, 'KM', 'COM', '', 0, 1),
(49, 'CG', 'COG', '', 0, 1),
(50, 'CK', 'COK', '', 0, 1),
(51, 'CR', 'CRI', '', 0, 1),
(52, 'CI', 'CIV', '', 0, 1),
(53, 'HR', 'HRV', '', 0, 1),
(54, 'CU', 'CUB', '', 0, 1),
(55, 'CY', 'CYP', '', 0, 1),
(56, 'CZ', 'CZE', '', 0, 1),
(57, 'DK', 'DNK', '', 0, 1),
(58, 'DJ', 'DJI', '', 0, 1),
(59, 'DM', 'DMA', '', 0, 1),
(60, 'DO', 'DOM', '', 0, 1),
(61, 'TL', 'TLS', '', 0, 1),
(62, 'EC', 'ECU', '', 0, 1),
(63, 'EG', 'EGY', '', 0, 1),
(64, 'SV', 'SLV', '', 0, 1),
(65, 'GQ', 'GNQ', '', 0, 1),
(66, 'ER', 'ERI', '', 0, 1),
(67, 'EE', 'EST', '', 0, 1),
(68, 'ET', 'ETH', '', 0, 1),
(69, 'FK', 'FLK', '', 0, 1),
(70, 'FO', 'FRO', '', 0, 1),
(71, 'FJ', 'FJI', '', 0, 1),
(72, 'FI', 'FIN', '', 0, 1),
(74, 'FR', 'FRA', '{firstname} {lastname}\r\n{company}\r\n{address_1}\r\n{address_2}\r\n{postcode} {city}\r\n{country}', 1, 1),
(75, 'GF', 'GUF', '', 0, 1),
(76, 'PF', 'PYF', '', 0, 1),
(77, 'TF', 'ATF', '', 0, 1),
(78, 'GA', 'GAB', '', 0, 1),
(79, 'GM', 'GMB', '', 0, 1),
(80, 'GE', 'GEO', '', 0, 1),
(81, 'DE', 'DEU', '{company}\r\n{firstname} {lastname}\r\n{address_1}\r\n{address_2}\r\n{postcode} {city}\r\n{country}', 1, 1),
(82, 'GH', 'GHA', '', 0, 1),
(83, 'GI', 'GIB', '', 0, 1),
(84, 'GR', 'GRC', '', 0, 1),
(85, 'GL', 'GRL', '', 0, 1),
(86, 'GD', 'GRD', '', 0, 1),
(87, 'GP', 'GLP', '', 0, 1),
(88, 'GU', 'GUM', '', 0, 1),
(89, 'GT', 'GTM', '', 0, 1),
(90, 'GN', 'GIN', '', 0, 1),
(91, 'GW', 'GNB', '', 0, 1),
(92, 'GY', 'GUY', '', 0, 1),
(93, 'HT', 'HTI', '', 0, 1),
(94, 'HM', 'HMD', '', 0, 1),
(95, 'HN', 'HND', '', 0, 1),
(96, 'HK', 'HKG', '', 0, 1),
(97, 'HU', 'HUN', '', 0, 1),
(98, 'IS', 'ISL', '', 0, 1),
(99, 'IN', 'IND', '', 0, 1),
(100, 'ID', 'IDN', '', 0, 1),
(101, 'IR', 'IRN', '', 0, 1),
(102, 'IQ', 'IRQ', '', 0, 1),
(103, 'IE', 'IRL', '', 0, 1),
(104, 'IL', 'ISR', '', 0, 1),
(105, 'IT', 'ITA', '', 0, 1),
(106, 'JM', 'JAM', '', 0, 1),
(107, 'JP', 'JPN', '', 0, 1),
(108, 'JO', 'JOR', '', 0, 1),
(109, 'KZ', 'KAZ', '', 0, 1),
(110, 'KE', 'KEN', '', 0, 1),
(111, 'KI', 'KIR', '', 0, 1),
(112, 'KP', 'PRK', '', 0, 1),
(113, 'KR', 'KOR', '', 0, 1),
(114, 'KW', 'KWT', '', 0, 1),
(115, 'KG', 'KGZ', '', 0, 1),
(116, 'LA', 'LAO', '', 0, 1),
(117, 'LV', 'LVA', '', 0, 1),
(118, 'LB', 'LBN', '', 0, 1),
(119, 'LS', 'LSO', '', 0, 1),
(120, 'LR', 'LBR', '', 0, 1),
(121, 'LY', 'LBY', '', 0, 1),
(122, 'LI', 'LIE', '', 0, 1),
(123, 'LT', 'LTU', '', 0, 1),
(124, 'LU', 'LUX', '', 0, 1),
(125, 'MO', 'MAC', '', 0, 1),
(126, 'MK', 'MKD', '', 0, 1),
(127, 'MG', 'MDG', '', 0, 1),
(128, 'MW', 'MWI', '', 0, 1),
(129, 'MY', 'MYS', '', 0, 1),
(130, 'MV', 'MDV', '', 0, 1),
(131, 'ML', 'MLI', '', 0, 1),
(132, 'MT', 'MLT', '', 0, 1),
(133, 'MH', 'MHL', '', 0, 1),
(134, 'MQ', 'MTQ', '', 0, 1),
(135, 'MR', 'MRT', '', 0, 1),
(136, 'MU', 'MUS', '', 0, 1),
(137, 'YT', 'MYT', '', 0, 1),
(138, 'MX', 'MEX', '', 0, 1),
(139, 'FM', 'FSM', '', 0, 1),
(140, 'MD', 'MDA', '', 0, 1),
(141, 'MC', 'MCO', '', 0, 1),
(142, 'MN', 'MNG', '', 0, 1),
(143, 'MS', 'MSR', '', 0, 1),
(144, 'MA', 'MAR', '', 0, 1),
(145, 'MZ', 'MOZ', '', 0, 1),
(146, 'MM', 'MMR', '', 0, 1),
(147, 'NA', 'NAM', '', 0, 1),
(148, 'NR', 'NRU', '', 0, 1),
(149, 'NP', 'NPL', '', 0, 1),
(150, 'NL', 'NLD', '', 0, 1),
(151, 'AN', 'ANT', '', 0, 1),
(152, 'NC', 'NCL', '', 0, 1),
(153, 'NZ', 'NZL', '', 0, 1),
(154, 'NI', 'NIC', '', 0, 1),
(155, 'NE', 'NER', '', 0, 1),
(156, 'NG', 'NGA', '', 0, 1),
(157, 'NU', 'NIU', '', 0, 1),
(158, 'NF', 'NFK', '', 0, 1),
(159, 'MP', 'MNP', '', 0, 1),
(160, 'NO', 'NOR', '', 0, 1),
(161, 'OM', 'OMN', '', 0, 1),
(162, 'PK', 'PAK', '', 0, 1),
(163, 'PW', 'PLW', '', 0, 1),
(164, 'PA', 'PAN', '', 0, 1),
(165, 'PG', 'PNG', '', 0, 1),
(166, 'PY', 'PRY', '', 0, 1),
(167, 'PE', 'PER', '', 0, 1),
(168, 'PH', 'PHL', '', 0, 1),
(169, 'PN', 'PCN', '', 0, 1),
(170, 'PL', 'POL', '', 0, 1),
(171, 'PT', 'PRT', '', 0, 1),
(172, 'PR', 'PRI', '', 0, 1),
(173, 'QA', 'QAT', '', 0, 1),
(174, 'RE', 'REU', '', 0, 1),
(175, 'RO', 'ROM', '', 0, 1),
(176, 'RU', 'RUS', '', 0, 1),
(177, 'RW', 'RWA', '', 0, 1),
(178, 'KN', 'KNA', '', 0, 1),
(179, 'LC', 'LCA', '', 0, 1),
(180, 'VC', 'VCT', '', 0, 1),
(181, 'WS', 'WSM', '', 0, 1),
(182, 'SM', 'SMR', '', 0, 1),
(183, 'ST', 'STP', '', 0, 1),
(184, 'SA', 'SAU', '', 0, 1),
(185, 'SN', 'SEN', '', 0, 1),
(186, 'SC', 'SYC', '', 0, 1),
(187, 'SL', 'SLE', '', 0, 1),
(188, 'SG', 'SGP', '', 0, 1),
(189, 'SK', 'SVK', '{firstname} {lastname}\r\n{company}\r\n{address_1}\r\n{address_2}\r\n{city} {postcode}\r\n{zone}\r\n{country}', 0, 1),
(190, 'SI', 'SVN', '', 0, 1),
(191, 'SB', 'SLB', '', 0, 1),
(192, 'SO', 'SOM', '', 0, 1),
(193, 'ZA', 'ZAF', '', 0, 1),
(194, 'GS', 'SGS', '', 0, 1),
(195, 'ES', 'ESP', '', 0, 1),
(196, 'LK', 'LKA', '', 0, 1),
(197, 'SH', 'SHN', '', 0, 1),
(198, 'PM', 'SPM', '', 0, 1),
(199, 'SD', 'SDN', '', 0, 1),
(200, 'SR', 'SUR', '', 0, 1),
(201, 'SJ', 'SJM', '', 0, 1),
(202, 'SZ', 'SWZ', '', 0, 1),
(203, 'SE', 'SWE', '{company}\r\n{firstname} {lastname}\r\n{address_1}\r\n{address_2}\r\n{postcode} {city}\r\n{country}', 1, 1),
(204, 'CH', 'CHE', '', 0, 1),
(205, 'SY', 'SYR', '', 0, 1),
(206, 'TW', 'TWN', '', 0, 1),
(207, 'TJ', 'TJK', '', 0, 1),
(208, 'TZ', 'TZA', '', 0, 1),
(209, 'TH', 'THA', '', 0, 1),
(210, 'TG', 'TGO', '', 0, 1),
(211, 'TK', 'TKL', '', 0, 1),
(212, 'TO', 'TON', '', 0, 1),
(213, 'TT', 'TTO', '', 0, 1),
(214, 'TN', 'TUN', '', 0, 1),
(215, 'TR', 'TUR', '', 0, 1),
(216, 'TM', 'TKM', '', 0, 1),
(217, 'TC', 'TCA', '', 0, 1),
(218, 'TV', 'TUV', '', 0, 1),
(219, 'UG', 'UGA', '', 0, 1),
(220, 'UA', 'UKR', '', 0, 1),
(221, 'AE', 'ARE', '', 0, 1),
(222, 'GB', 'UKM', '{firstname} {lastname}\r\n{company}\r\n{address_1}\r\n{address_2}\r\n{city}\r\n{zone}\r\n{postcode}\r\n{country}', 1, 1),
(223, 'US', 'USA', '{firstname} {lastname}\r\n{company}\r\n{address_1}\r\n{address_2}\r\n{city}, {zone} {postcode}\r\n{country}', 0, 1),
(224, 'UM', 'UMI', '', 0, 1),
(225, 'UY', 'URY', '', 0, 1),
(226, 'UZ', 'UZB', '', 0, 1),
(227, 'VU', 'VUT', '', 0, 1),
(228, 'VA', 'VAT', '', 0, 1),
(229, 'VE', 'VEN', '', 0, 1),
(230, 'VN', 'VNM', '', 0, 1),
(231, 'VG', 'VGB', '', 0, 1),
(232, 'VI', 'VIR', '', 0, 1),
(233, 'WF', 'WLF', '', 0, 1),
(234, 'EH', 'ESH', '', 0, 1),
(235, 'YE', 'YEM', '', 0, 1),
(237, 'CD', 'COD', '', 0, 1),
(238, 'ZM', 'ZMB', '', 0, 1),
(239, 'ZW', 'ZWE', '', 0, 1),
(242, 'ME', 'MNE', '', 0, 1),
(243, 'RS', 'SRB', '', 0, 1),
(244, 'AX', 'ALA', '', 0, 1),
(245, 'BQ', 'BES', '', 0, 1),
(246, 'CW', 'CUW', '', 0, 1),
(247, 'PS', 'PSE', '', 0, 1),
(248, 'SS', 'SSD', '', 0, 1),
(249, 'BL', 'BLM', '', 0, 1),
(250, 'MF', 'MAF', '', 0, 1),
(251, 'IC', 'ICA', '', 0, 1),
(252, 'AC', 'ASC', '', 0, 1),
(253, 'XK', 'UNK', '', 0, 1),
(254, 'IM', 'IMN', '', 0, 1),
(255, 'TA', 'SHN', '', 0, 1),
(256, 'JE', 'JEY', '', 1, 1),
(257, 'GG', 'GGY', '', 1, 1),
(260, 'SX', 'SXM', '', 0, 1),
(261, 'EA', 'EAF', '', 0, 1),
(262, 'GB', 'ENG', '{firstname} {lastname}\r\n{company}\r\n{address_1}\r\n{address_2}\r\n{city}\r\n{zone}\r\n{postcode}\r\n{country}', 1, 1),
(263, 'GB', 'WLS', '{firstname} {lastname}\r\n{company}\r\n{address_1}\r\n{address_2}\r\n{city}\r\n{zone}\r\n{postcode}\r\n{country}', 1, 1),
(264, 'GB', 'NIR', '{firstname} {lastname}\r\n{company}\r\n{address_1}\r\n{address_2}\r\n{city}\r\n{zone}\r\n{postcode}\r\n{country}', 1, 1),
(265, 'GB', 'SCT', '{firstname} {lastname}\r\n{company}\r\n{address_1}\r\n{address_2}\r\n{city}\r\n{zone}\r\n{postcode}\r\n{country}', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `nc_country_description`
--

DROP TABLE IF EXISTS `nc_country_description`;
CREATE TABLE `nc_country_description` (
  `country_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`country_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_country_description`
--

INSERT INTO `nc_country_description` (`country_id`, `language_id`, `name`) VALUES
(1, 1, 'Afghanistan'),
(2, 1, 'Albania'),
(3, 1, 'Algeria'),
(4, 1, 'American Samoa'),
(5, 1, 'Andorra'),
(6, 1, 'Angola'),
(7, 1, 'Anguilla'),
(8, 1, 'Antarctica'),
(9, 1, 'Antigua and Barbuda'),
(10, 1, 'Argentina'),
(11, 1, 'Armenia'),
(12, 1, 'Aruba'),
(13, 1, 'Australia'),
(14, 1, 'Austria'),
(15, 1, 'Azerbaijan'),
(16, 1, 'Bahamas'),
(17, 1, 'Bahrain'),
(18, 1, 'Bangladesh'),
(19, 1, 'Barbados'),
(20, 1, 'Belarus'),
(21, 1, 'Belgium'),
(22, 1, 'Belize'),
(23, 1, 'Benin'),
(24, 1, 'Bermuda'),
(25, 1, 'Bhutan'),
(26, 1, 'Bolivia'),
(27, 1, 'Bosnia and Herzegovina'),
(28, 1, 'Botswana'),
(29, 1, 'Bouvet Island'),
(30, 1, 'Brazil'),
(31, 1, 'British Indian Ocean Territory'),
(32, 1, 'Brunei Darussalam'),
(33, 1, 'Bulgaria'),
(34, 1, 'Burkina Faso'),
(35, 1, 'Burundi'),
(36, 1, 'Cambodia'),
(37, 1, 'Cameroon'),
(38, 1, 'Canada'),
(39, 1, 'Cape Verde'),
(40, 1, 'Cayman Islands'),
(41, 1, 'Central African Republic'),
(42, 1, 'Chad'),
(43, 1, 'Chile'),
(44, 1, 'China'),
(45, 1, 'Christmas Island'),
(46, 1, 'Cocos (Keeling) Islands'),
(47, 1, 'Colombia'),
(48, 1, 'Comoros'),
(49, 1, 'Congo, Republic of the'),
(50, 1, 'Cook Islands'),
(51, 1, 'Costa Rica'),
(52, 1, 'Cote D&#39;Ivoire'),
(53, 1, 'Croatia'),
(54, 1, 'Cuba'),
(55, 1, 'Cyprus'),
(56, 1, 'Czech Republic'),
(57, 1, 'Denmark'),
(58, 1, 'Djibouti'),
(59, 1, 'Dominica'),
(60, 1, 'Dominican Republic'),
(61, 1, 'East Timor'),
(62, 1, 'Ecuador'),
(63, 1, 'Egypt'),
(64, 1, 'El Salvador'),
(65, 1, 'Equatorial Guinea'),
(66, 1, 'Eritrea'),
(67, 1, 'Estonia'),
(68, 1, 'Ethiopia'),
(69, 1, 'Falkland Islands (Malvinas)'),
(70, 1, 'Faroe Islands'),
(71, 1, 'Fiji'),
(72, 1, 'Finland'),
(74, 1, 'France'),
(75, 1, 'French Guiana'),
(76, 1, 'French Polynesia'),
(77, 1, 'French Southern Territories'),
(78, 1, 'Gabon'),
(79, 1, 'Gambia'),
(80, 1, 'Georgia'),
(81, 1, 'Germany'),
(82, 1, 'Ghana'),
(83, 1, 'Gibraltar'),
(84, 1, 'Greece'),
(85, 1, 'Greenland'),
(86, 1, 'Grenada'),
(87, 1, 'Guadeloupe'),
(88, 1, 'Guam'),
(89, 1, 'Guatemala'),
(90, 1, 'Guinea'),
(91, 1, 'Guinea-Bissau'),
(92, 1, 'Guyana'),
(93, 1, 'Haiti'),
(94, 1, 'Heard and McDonald Islands'),
(95, 1, 'Honduras'),
(96, 1, 'Hong Kong'),
(97, 1, 'Hungary'),
(98, 1, 'Iceland'),
(99, 1, 'India'),
(100, 1, 'Indonesia'),
(101, 1, 'Iran, Islamic Republic of'),
(102, 1, 'Iraq'),
(103, 1, 'Ireland'),
(104, 1, 'Israel'),
(105, 1, 'Italy'),
(106, 1, 'Jamaica'),
(107, 1, 'Japan'),
(108, 1, 'Jordan'),
(109, 1, 'Kazakhstan'),
(110, 1, 'Kenya'),
(111, 1, 'Kiribati'),
(112, 1, 'North Korea'),
(113, 1, 'Korea, Republic of'),
(114, 1, 'Kuwait'),
(115, 1, 'Kyrgyzstan'),
(116, 1, 'Lao People&#39;s Democratic Republic'),
(117, 1, 'Latvia'),
(118, 1, 'Lebanon'),
(119, 1, 'Lesotho'),
(120, 1, 'Liberia'),
(121, 1, 'Libyan Arab Jamahiriya'),
(122, 1, 'Liechtenstein'),
(123, 1, 'Lithuania'),
(124, 1, 'Luxembourg'),
(125, 1, 'Macau'),
(126, 1, 'Macedonia (FYROM)'),
(127, 1, 'Madagascar'),
(128, 1, 'Malawi'),
(129, 1, 'Malaysia'),
(130, 1, 'Maldives'),
(131, 1, 'Mali'),
(132, 1, 'Malta'),
(133, 1, 'Marshall Islands'),
(134, 1, 'Martinique'),
(135, 1, 'Mauritania'),
(136, 1, 'Mauritius'),
(137, 1, 'Mayotte'),
(138, 1, 'Mexico'),
(139, 1, 'Micronesia, Federated States of'),
(140, 1, 'Moldova, Republic of'),
(141, 1, 'Monaco'),
(142, 1, 'Mongolia'),
(143, 1, 'Montserrat'),
(144, 1, 'Morocco'),
(145, 1, 'Mozambique'),
(146, 1, 'Myanmar'),
(147, 1, 'Namibia'),
(148, 1, 'Nauru'),
(149, 1, 'Nepal'),
(150, 1, 'Netherlands'),
(151, 1, 'Netherlands Antilles'),
(152, 1, 'New Caledonia'),
(153, 1, 'New Zealand'),
(154, 1, 'Nicaragua'),
(155, 1, 'Niger'),
(156, 1, 'Nigeria'),
(157, 1, 'Niue'),
(158, 1, 'Norfolk Island'),
(159, 1, 'Northern Mariana Islands'),
(160, 1, 'Norway'),
(161, 1, 'Oman'),
(162, 1, 'Pakistan'),
(163, 1, 'Palau'),
(164, 1, 'Panama'),
(165, 1, 'Papua New Guinea'),
(166, 1, 'Paraguay'),
(167, 1, 'Peru'),
(168, 1, 'Philippines'),
(169, 1, 'Pitcairn'),
(170, 1, 'Poland'),
(171, 1, 'Portugal'),
(172, 1, 'Puerto Rico'),
(173, 1, 'Qatar'),
(174, 1, 'Reunion'),
(175, 1, 'Romania'),
(176, 1, 'Russian Federation'),
(177, 1, 'Rwanda'),
(178, 1, 'Saint Kitts and Nevis'),
(179, 1, 'Saint Lucia'),
(180, 1, 'Saint Vincent and the Grenadines'),
(181, 1, 'Samoa'),
(182, 1, 'San Marino'),
(183, 1, 'Sao Tome and Principe'),
(184, 1, 'Saudi Arabia'),
(185, 1, 'Senegal'),
(186, 1, 'Seychelles'),
(187, 1, 'Sierra Leone'),
(188, 1, 'Singapore'),
(189, 1, 'Slovak Republic'),
(190, 1, 'Slovenia'),
(191, 1, 'Solomon Islands'),
(192, 1, 'Somalia'),
(193, 1, 'South Africa'),
(194, 1, 'South Georgia &amp; South Sandwich Islands'),
(195, 1, 'Spain'),
(196, 1, 'Sri Lanka'),
(197, 1, 'St. Helena'),
(198, 1, 'St. Pierre and Miquelon'),
(199, 1, 'Sudan'),
(200, 1, 'Suriname'),
(201, 1, 'Svalbard and Jan Mayen Islands'),
(202, 1, 'Swaziland'),
(203, 1, 'Sweden'),
(204, 1, 'Switzerland'),
(205, 1, 'Syrian Arab Republic'),
(206, 1, 'Taiwan'),
(207, 1, 'Tajikistan'),
(208, 1, 'Tanzania, United Republic of'),
(209, 1, 'Thailand'),
(210, 1, 'Togo'),
(211, 1, 'Tokelau'),
(212, 1, 'Tonga'),
(213, 1, 'Trinidad and Tobago'),
(214, 1, 'Tunisia'),
(215, 1, 'Turkey'),
(216, 1, 'Turkmenistan'),
(217, 1, 'Turks and Caicos Islands'),
(218, 1, 'Tuvalu'),
(219, 1, 'Uganda'),
(220, 1, 'Ukraine'),
(221, 1, 'United Arab Emirates'),
(222, 1, 'United Kingdom'),
(223, 1, 'United States'),
(224, 1, 'United States Minor Outlying Islands'),
(225, 1, 'Uruguay'),
(226, 1, 'Uzbekistan'),
(227, 1, 'Vanuatu'),
(228, 1, 'Vatican City State (Holy See)'),
(229, 1, 'Venezuela'),
(230, 1, 'Vietnam'),
(231, 1, 'Virgin Islands (British)'),
(232, 1, 'Virgin Islands (U.S.)'),
(233, 1, 'Wallis and Futuna Islands'),
(234, 1, 'Western Sahara'),
(235, 1, 'Yemen'),
(237, 1, 'Congo, Democratic Republic of the'),
(238, 1, 'Zambia'),
(239, 1, 'Zimbabwe'),
(242, 1, 'Montenegro'),
(243, 1, 'Serbia'),
(244, 1, '&Aring;land Islands'),
(245, 1, 'Bonaire, Sint Eustatius and Saba'),
(246, 1, 'Cura&ccedil;ao'),
(247, 1, 'Palestinian Territory, Occupied'),
(248, 1, 'South Sudan'),
(249, 1, 'St. Barth&eacute;lemy'),
(250, 1, 'St. Martin (French part)'),
(251, 1, 'Canary Islands'),
(252, 1, 'Ascension Island (British)'),
(253, 1, 'Kosovo, Republic of'),
(254, 1, 'Isle of Man'),
(255, 1, 'Tristan da Cunha'),
(256, 1, 'Jersey'),
(257, 1, 'Guernsey'),
(260, 1, 'Sint Maarten'),
(261, 1, 'Spanish Territories of North Africa'),
(262, 1, 'England (UK)'),
(263, 1, 'Wales (UK)'),
(264, 1, 'Northern Ireland (UK)'),
(265, 1, 'Scotland (UK)');

-- --------------------------------------------------------

--
-- Table structure for table `nc_coupon`
--

DROP TABLE IF EXISTS `nc_coupon`;
CREATE TABLE `nc_coupon` (
  `coupon_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `code` varchar(10) NOT NULL,
  `type` char(1) NOT NULL,
  `discount` decimal(15,4) NOT NULL,
  `logged` tinyint(1) NOT NULL,
  `shipping` tinyint(1) NOT NULL,
  `total` decimal(15,4) NOT NULL,
  `date_start` date NOT NULL DEFAULT '0000-00-00',
  `date_end` date NOT NULL DEFAULT '0000-00-00',
  `uses_total` int(11) NOT NULL,
  `uses_customer` varchar(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`coupon_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_coupon`
--

INSERT INTO `nc_coupon` (`coupon_id`, `name`, `code`, `type`, `discount`, `logged`, `shipping`, `total`, `date_start`, `date_end`, `uses_total`, `uses_customer`, `status`, `date_added`) VALUES
(1, '-10% Discount', '2222', 'P', '10.0000', 0, 0, '0.0000', '2018-01-01', '2025-01-01', 10, '10', 0, '2017-12-27 13:55:03'),
(2, 'Free Shipping', '3333', 'P', '0.0000', 0, 1, '100.0000', '2018-01-01', '2025-01-01', 10, '10', 0, '2017-12-27 21:13:53'),
(3, '-10.00 Discount', '1111', 'F', '10.0000', 0, 0, '10.0000', '2018-01-01', '2025-01-01', 100000, '10000', 0, '2017-12-27 21:15:18');

-- --------------------------------------------------------

--
-- Table structure for table `nc_coupon_category`
--

DROP TABLE IF EXISTS `nc_coupon_category`;
CREATE TABLE `nc_coupon_category` (
  `coupon_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`coupon_id`,`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_coupon_history`
--

DROP TABLE IF EXISTS `nc_coupon_history`;
CREATE TABLE `nc_coupon_history` (
  `coupon_history_id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `amount` decimal(15,4) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`coupon_history_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_coupon_product`
--

DROP TABLE IF EXISTS `nc_coupon_product`;
CREATE TABLE `nc_coupon_product` (
  `coupon_product_id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  PRIMARY KEY (`coupon_product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_currency`
--

DROP TABLE IF EXISTS `nc_currency`;
CREATE TABLE `nc_currency` (
  `currency_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL,
  `code` varchar(3) NOT NULL,
  `symbol_left` varchar(12) NOT NULL,
  `symbol_right` varchar(12) NOT NULL,
  `decimal_place` char(1) NOT NULL,
  `value` float(15,8) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`currency_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_currency`
--

INSERT INTO `nc_currency` (`currency_id`, `title`, `code`, `symbol_left`, `symbol_right`, `decimal_place`, `value`, `status`, `date_modified`) VALUES
(1, 'British Pound', 'GBP', '£', '', '2', 0.77586073, 0, '2018-08-05 06:50:33'),
(2, 'Dollar US', 'USD', '$', '', '2', 1.00000000, 1, '2018-08-05 06:50:33'),
(3, 'Euro', 'EUR', '', ' €', '2', 0.86465627, 0, '2018-08-05 15:13:10');

-- --------------------------------------------------------

--
-- Table structure for table `nc_customer`
--

DROP TABLE IF EXISTS `nc_customer`;
CREATE TABLE `nc_customer` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL DEFAULT '0',
  `firstname` varchar(32) NOT NULL,
  `lastname` varchar(32) NOT NULL,
  `email` varchar(96) NOT NULL,
  `telephone` varchar(32) NOT NULL,
  `fax` varchar(32) NOT NULL,
  `gender` varchar(32) NOT NULL,
  `date_of_birth` date NOT NULL,
  `password` varchar(40) NOT NULL,
  `salt` varchar(9) NOT NULL,
  `cart` text CHARACTER SET utf8,
  `wishlist` text CHARACTER SET utf8,
  `newsletter` tinyint(1) NOT NULL DEFAULT '0',
  `address_id` int(11) NOT NULL DEFAULT '0',
  `customer_group_id` int(11) NOT NULL,
  `ip` varchar(32) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL,
  `approved` tinyint(1) NOT NULL,
  `token` varchar(255) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_customer_ban_ip`
--

DROP TABLE IF EXISTS `nc_customer_ban_ip`;
CREATE TABLE `nc_customer_ban_ip` (
  `customer_ban_ip_id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(32) NOT NULL,
  PRIMARY KEY (`customer_ban_ip_id`),
  KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_customer_deleted`
--

DROP TABLE IF EXISTS `nc_customer_deleted`;
CREATE TABLE `nc_customer_deleted` (
  `customer_deleted_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL DEFAULT '0',
  `firstname` varchar(32) NOT NULL,
  `lastname` varchar(32) NOT NULL,
  `email` varchar(96) NOT NULL,
  `orders` int(11) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`customer_deleted_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_customer_group`
--

DROP TABLE IF EXISTS `nc_customer_group`;
CREATE TABLE `nc_customer_group` (
  `customer_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `approval` int(1) NOT NULL,
  `company_id_display` int(1) NOT NULL,
  `company_id_required` int(1) NOT NULL,
  `tax_id_display` int(1) NOT NULL,
  `tax_id_required` int(1) NOT NULL,
  `sort_order` int(3) NOT NULL,
  PRIMARY KEY (`customer_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_customer_group`
--

INSERT INTO `nc_customer_group` (`customer_group_id`, `approval`, `company_id_display`, `company_id_required`, `tax_id_display`, `tax_id_required`, `sort_order`) VALUES
(1, 0, 1, 0, 0, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `nc_customer_group_description`
--

DROP TABLE IF EXISTS `nc_customer_group_description`;
CREATE TABLE `nc_customer_group_description` (
  `customer_group_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `description` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`customer_group_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_customer_group_description`
--

INSERT INTO `nc_customer_group_description` (`customer_group_id`, `language_id`, `name`, `description`) VALUES
(1, 1, 'Default', 'Default');

-- --------------------------------------------------------

--
-- Table structure for table `nc_customer_group_image`
--

DROP TABLE IF EXISTS `nc_customer_group_image`;
CREATE TABLE `nc_customer_group_image` (
  `customer_group_image_id` int(11) NOT NULL,
  `customer_group_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  PRIMARY KEY (`customer_group_image_id`,`customer_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_customer_history`
--

DROP TABLE IF EXISTS `nc_customer_history`;
CREATE TABLE `nc_customer_history` (
  `customer_history_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `comment` text CHARACTER SET utf8 NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`customer_history_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_customer_ip`
--

DROP TABLE IF EXISTS `nc_customer_ip`;
CREATE TABLE `nc_customer_ip` (
  `customer_ip_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `ip` varchar(32) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`customer_ip_id`),
  KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_customer_online`
--

DROP TABLE IF EXISTS `nc_customer_online`;
CREATE TABLE `nc_customer_online` (
  `ip` varchar(32) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `url` text NOT NULL,
  `referer` text NOT NULL,
  `user_agent` text NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_customer_reward`
--

DROP TABLE IF EXISTS `nc_customer_reward`;
CREATE TABLE `nc_customer_reward` (
  `customer_reward_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `order_id` int(11) NOT NULL DEFAULT '0',
  `description` text CHARACTER SET utf8 NOT NULL,
  `points` int(8) NOT NULL DEFAULT '0',
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`customer_reward_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_customer_transaction`
--

DROP TABLE IF EXISTS `nc_customer_transaction`;
CREATE TABLE `nc_customer_transaction` (
  `customer_transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `description` text CHARACTER SET utf8 NOT NULL,
  `amount` decimal(15,4) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`customer_transaction_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_customer_wishlist`
--

DROP TABLE IF EXISTS `nc_customer_wishlist`;
CREATE TABLE `nc_customer_wishlist` (
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`customer_id`,`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_download`
--

DROP TABLE IF EXISTS `nc_download`;
CREATE TABLE `nc_download` (
  `download_id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(128) NOT NULL,
  `mask` varchar(128) NOT NULL,
  `remaining` int(11) NOT NULL DEFAULT '0',
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`download_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_download_description`
--

DROP TABLE IF EXISTS `nc_download_description`;
CREATE TABLE `nc_download_description` (
  `download_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`download_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_eucountry`
--

DROP TABLE IF EXISTS `nc_eucountry`;
CREATE TABLE `nc_eucountry` (
  `eucountry_id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(2) DEFAULT NULL,
  `rate` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`eucountry_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_eucountry`
--

INSERT INTO `nc_eucountry` (`eucountry_id`, `code`, `rate`, `status`) VALUES
(1, 'AT', '20.0000', '1'),
(2, 'BE', '21.0000', '1'),
(3, 'BG', '20.0000', '1'),
(4, 'CY', '19.0000', '1'),
(5, 'CZ', '21.0000', '1'),
(6, 'DE', '19.0000', '1'),
(7, 'DK', '25.0000', '1'),
(8, 'EE', '20.0000', '1'),
(9, 'ES', '21.0000', '1'),
(10, 'FI', '24.0000', '1'),
(11, 'FR', '20.0000', '1'),
(12, 'GB', '20.0000', '1'),
(13, 'GR', '24.0000', '1'),
(14, 'HR', '25.0000', '1'),
(15, 'HU', '27.0000', '1'),
(16, 'IE', '23.0000', '1'),
(17, 'IT', '22.0000', '1'),
(18, 'LV', '21.0000', '1'),
(19, 'LT', '21.0000', '1'),
(20, 'LU', '17.0000', '1'),
(21, 'MT', '18.0000', '1'),
(22, 'NL', '21.0000', '1'),
(23, 'PL', '23.0000', '1'),
(24, 'PT', '23.0000', '1'),
(25, 'RO', '19.0000', '1'),
(26, 'SE', '25.0000', '1'),
(27, 'SI', '22.0000', '1'),
(28, 'SK', '20.0000', '1');

-- --------------------------------------------------------

--
-- Table structure for table `nc_eucountry_description`
--

DROP TABLE IF EXISTS `nc_eucountry_description`;
CREATE TABLE `nc_eucountry_description` (
  `eucountry_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `eucountry` varchar(128) NOT NULL,
  `description` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`eucountry_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_eucountry_description`
--

INSERT INTO `nc_eucountry_description` (`eucountry_id`, `language_id`, `eucountry`, `description`) VALUES
(1, 1, 'Austria', 'VAT Rate AT'),
(2, 1, 'Belgium', 'VAT Rate BE'),
(3, 1, 'Bulgaria', 'VAT Rate BG'),
(4, 1, 'Cyprus', 'VAT Rate CY'),
(5, 1, 'Czech Republic', 'VAT Rate CZ'),
(6, 1, 'Germany', 'VAT Rate DE'),
(7, 1, 'Denmark', 'VAT Rate DK'),
(8, 1, 'Estonia', 'VAT Rate EE'),
(9, 1, 'Spain', 'VAT Rate ES'),
(10, 1, 'Finland', 'VAT Rate FI'),
(11, 1, 'France', 'VAT Rate FR'),
(12, 1, 'United Kingdom', 'VAT Rate GB'),
(13, 1, 'Greece', 'VAT Rate GR'),
(14, 1, 'Croatia', 'VAT Rate HR'),
(15, 1, 'Hungary', 'VAT Rate HU'),
(16, 1, 'Ireland', 'VAT Rate IE'),
(17, 1, 'Italy', 'VAT Rate IT'),
(18, 1, 'Latvia', 'VAT Rate LV'),
(19, 1, 'Lithuania', 'VAT Rate LT'),
(20, 1, 'Luxembourg', 'VAT Rate LU'),
(21, 1, 'Malta', 'VAT Rate MT'),
(22, 1, 'Netherlands', 'VAT Rate NL'),
(23, 1, 'Poland', 'VAT Rate PL'),
(24, 1, 'Portugal', 'VAT Rate PT'),
(25, 1, 'Romania', 'VAT Rate RO'),
(26, 1, 'Sweden', 'VAT Rate SE'),
(27, 1, 'Slovenia', 'VAT Rate SI'),
(28, 1, 'Slovakia', 'VAT Rate SK');

-- --------------------------------------------------------

--
-- Table structure for table `nc_eucountry_to_store`
--

DROP TABLE IF EXISTS `nc_eucountry_to_store`;
CREATE TABLE `nc_eucountry_to_store` (
  `eucountry_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`eucountry_id`,`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_eucountry_to_store`
--

INSERT INTO `nc_eucountry_to_store` (`eucountry_id`, `store_id`) VALUES
(1, '0'),
(2, '0'),
(3, '0'),
(4, '0'),
(5, '0'),
(6, '0'),
(7, '0'),
(8, '0'),
(9, '0'),
(10, '0'),
(11, '0'),
(12, '0'),
(13, '0'),
(14, '0'),
(15, '0'),
(16, '0'),
(17, '0'),
(18, '0'),
(19, '0'),
(20, '0'),
(21, '0'),
(22, '0'),
(23, '0'),
(24, '0'),
(25, '0'),
(26, '0'),
(27, '0'),
(28, '0');

-- --------------------------------------------------------

--
-- Table structure for table `nc_extension`
--

DROP TABLE IF EXISTS `nc_extension`;
CREATE TABLE `nc_extension` (
  `extension_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL,
  `code` varchar(32) NOT NULL,
  PRIMARY KEY (`extension_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_extension`
--

INSERT INTO `nc_extension` (`extension_id`, `type`, `code`) VALUES
(1, 'theme', 'default'),
(2, 'total', 'shipping'),
(3, 'total', 'sub_total'),
(4, 'total', 'tax'),
(5, 'total', 'total'),
(6, 'total', 'credit'),
(7, 'total', 'handling'),
(8, 'total', 'low_order_fee'),
(9, 'total', 'coupon'),
(10, 'total', 'reward'),
(11, 'total', 'voucher'),
(12, 'payment', 'cod'),
(13, 'payment', 'free_checkout'),
(14, 'shipping', 'flat_1'),
(15, 'module', 'account'),
(16, 'module', 'affiliate'),
(17, 'module', 'menu_horizontal'),
(18, 'module', 'menu_vertical'),
(19, 'module', 'news'),
(20, 'module', 'latest');

-- --------------------------------------------------------

--
-- Table structure for table `nc_field`
--

DROP TABLE IF EXISTS `nc_field`;
CREATE TABLE `nc_field` (
  `field_id` int(11) NOT NULL AUTO_INCREMENT,
  `sort_order` int(3) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`field_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_field_description`
--

DROP TABLE IF EXISTS `nc_field_description`;
CREATE TABLE `nc_field_description` (
  `field_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `title` varchar(64) NOT NULL,
  `description` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`field_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_filter`
--

DROP TABLE IF EXISTS `nc_filter`;
CREATE TABLE `nc_filter` (
  `filter_id` int(11) NOT NULL AUTO_INCREMENT,
  `filter_group_id` int(11) NOT NULL,
  `sort_order` int(3) NOT NULL,
  PRIMARY KEY (`filter_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_filter_description`
--

DROP TABLE IF EXISTS `nc_filter_description`;
CREATE TABLE `nc_filter_description` (
  `filter_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `filter_group_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`filter_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_filter_group`
--

DROP TABLE IF EXISTS `nc_filter_group`;
CREATE TABLE `nc_filter_group` (
  `filter_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `sort_order` int(3) NOT NULL,
  PRIMARY KEY (`filter_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_filter_group_description`
--

DROP TABLE IF EXISTS `nc_filter_group_description`;
CREATE TABLE `nc_filter_group_description` (
  `filter_group_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`filter_group_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_footer`
--

DROP TABLE IF EXISTS `nc_footer`;
CREATE TABLE `nc_footer` (
  `footer_id` int(11) NOT NULL AUTO_INCREMENT,
  `position` tinyint(1) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`footer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_footer`
--

INSERT INTO `nc_footer` (`footer_id`, `position`, `status`) VALUES
(1, 1, 1),
(2, 2, 1),
(3, 2, 1),
(4, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `nc_footer_description`
--

DROP TABLE IF EXISTS `nc_footer_description`;
CREATE TABLE `nc_footer_description` (
  `footer_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`footer_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_footer_description`
--

INSERT INTO `nc_footer_description` (`footer_id`, `language_id`, `name`) VALUES
(1, 1, 'Browse'),
(2, 1, 'Explore'),
(3, 1, 'News'),
(4, 1, 'Information');

-- --------------------------------------------------------

--
-- Table structure for table `nc_footer_route`
--

DROP TABLE IF EXISTS `nc_footer_route`;
CREATE TABLE `nc_footer_route` (
  `footer_route_id` int(11) NOT NULL AUTO_INCREMENT,
  `footer_id` int(11) NOT NULL,
  `route` varchar(255) NOT NULL,
  `external_link` tinyint(1) NOT NULL,
  `sort_order` int(3) NOT NULL,
  PRIMARY KEY (`footer_route_id`,`footer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_footer_route`
--

INSERT INTO `nc_footer_route` (`footer_route_id`, `footer_id`, `route`, `external_link`, `sort_order`) VALUES
(29, 1, 'product/special ', 0, 4),
(28, 1, 'product/manufacturer ', 0, 3),
(27, 1, 'product/product_wall', 0, 2),
(26, 1, 'product/category_list ', 0, 1),
(12, 4, 'information/contact', 0, 1),
(25, 2, 'information/sitemap', 0, 2),
(24, 2, 'product/search', 0, 1),
(31, 3, 'information/news_list ', 0, 3),
(13, 4, 'information/information&amp;information_id=5', 0, 2),
(14, 4, 'information/information&amp;information_id=3', 0, 3),
(15, 4, 'information/information&amp;information_id=6', 0, 4),
(16, 4, 'information/information&amp;information_id=4', 0, 5),
(30, 1, 'product/review_list ', 0, 5);

-- --------------------------------------------------------

--
-- Table structure for table `nc_footer_route_description`
--

DROP TABLE IF EXISTS `nc_footer_route_description`;
CREATE TABLE `nc_footer_route_description` (
  `footer_route_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `footer_id` int(11) NOT NULL,
  `title` varchar(64) NOT NULL,
  PRIMARY KEY (`footer_route_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_footer_route_description`
--

INSERT INTO `nc_footer_route_description` (`footer_route_id`, `language_id`, `footer_id`, `title`) VALUES
(28, 1, 1, 'All Brands'),
(27, 1, 1, 'All Products'),
(26, 1, 1, 'All Categories'),
(12, 1, 4, 'Contact us'),
(25, 1, 2, 'Site map'),
(24, 1, 2, 'Search'),
(31, 1, 3, 'Newsletter'),
(13, 1, 4, 'Terms &amp; Conditions'),
(14, 1, 4, 'Privacy policy'),
(15, 1, 4, 'Delivery information'),
(16, 1, 4, 'About us'),
(29, 1, 1, 'All Offers'),
(30, 1, 1, 'All Reviews');

-- --------------------------------------------------------

--
-- Table structure for table `nc_footer_to_store`
--

DROP TABLE IF EXISTS `nc_footer_to_store`;
CREATE TABLE `nc_footer_to_store` (
  `footer_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  PRIMARY KEY (`footer_id`,`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_footer_to_store`
--

INSERT INTO `nc_footer_to_store` (`footer_id`, `store_id`) VALUES
(1, 0),
(2, 0),
(3, 0),
(4, 0);

-- --------------------------------------------------------

--
-- Table structure for table `nc_geo_zone`
--

DROP TABLE IF EXISTS `nc_geo_zone`;
CREATE TABLE `nc_geo_zone` (
  `geo_zone_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `description` varchar(255) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`geo_zone_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_geo_zone`
--

INSERT INTO `nc_geo_zone` (`geo_zone_id`, `name`, `description`, `date_added`, `date_modified`) VALUES
(1, 'EU VAT Zone', 'EU VAT Zone', '2018-01-01 00:00:01', '2018-01-10 01:01:01'),
(3, 'UK VAT Zone', 'UK VAT', '2017-01-06 23:26:25', '2018-02-26 22:33:24'),
(4, 'UK Shipping', 'UK Shipping Zones', '2017-06-23 01:14:53', '2018-12-15 15:18:13');

-- --------------------------------------------------------

--
-- Table structure for table `nc_information`
--

DROP TABLE IF EXISTS `nc_information`;
CREATE TABLE `nc_information` (
  `information_id` int(11) NOT NULL AUTO_INCREMENT,
  `bottom` int(1) NOT NULL DEFAULT '0',
  `sort_order` int(3) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`information_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_information`
--

INSERT INTO `nc_information` (`information_id`, `bottom`, `sort_order`, `status`) VALUES
(3, 1, 3, 1),
(4, 1, 1, 1),
(5, 1, 4, 1),
(6, 1, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `nc_information_description`
--

DROP TABLE IF EXISTS `nc_information_description`;
CREATE TABLE `nc_information_description` (
  `information_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `title` varchar(64) NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `meta_keyword` varchar(255) NOT NULL,
  `description` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`information_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_information_description`
--

INSERT INTO `nc_information_description` (`information_id`, `language_id`, `title`, `meta_description`, `meta_keyword`, `description`) VALUES
(4, 1, 'About Us', '', '', '&lt;p&gt;\r\n  About Us&lt;/p&gt;\r\n'),
(5, 1, 'Terms &amp; Conditions', '', '', '&lt;p&gt;\r\n  Terms &amp;amp; Conditions&lt;/p&gt;\r\n'),
(3, 1, 'Privacy Policy', '', '', '&lt;p&gt;\r\n  Privacy Policy&lt;/p&gt;\r\n'),
(6, 1, 'Delivery Information', '', '', '&lt;p&gt;\r\n  Delivery Information&lt;/p&gt;\r\n');

-- --------------------------------------------------------

--
-- Table structure for table `nc_information_to_layout`
--

DROP TABLE IF EXISTS `nc_information_to_layout`;
CREATE TABLE `nc_information_to_layout` (
  `information_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `layout_id` int(11) NOT NULL,
  PRIMARY KEY (`information_id`,`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_information_to_store`
--

DROP TABLE IF EXISTS `nc_information_to_store`;
CREATE TABLE `nc_information_to_store` (
  `information_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  PRIMARY KEY (`information_id`,`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_information_to_store`
--

INSERT INTO `nc_information_to_store` (`information_id`, `store_id`) VALUES
(3, 0),
(4, 0),
(5, 0),
(6, 0);

-- --------------------------------------------------------

--
-- Table structure for table `nc_language`
--

DROP TABLE IF EXISTS `nc_language`;
CREATE TABLE `nc_language` (
  `language_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `code` varchar(5) NOT NULL,
  `locale` varchar(255) NOT NULL,
  `image` varchar(64) NOT NULL,
  `directory` varchar(32) NOT NULL,
  `filename` varchar(64) NOT NULL,
  `sort_order` int(3) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`language_id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_language`
--

INSERT INTO `nc_language` (`language_id`, `name`, `code`, `locale`, `image`, `directory`, `filename`, `sort_order`, `status`) VALUES
(1, 'English', 'en', 'en_US.UTF-8,en-US,en-gb,english', 'gb.png', 'english', 'english', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `nc_layout`
--

DROP TABLE IF EXISTS `nc_layout`;
CREATE TABLE `nc_layout` (
  `layout_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`layout_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_layout`
--

INSERT INTO `nc_layout` (`layout_id`, `name`) VALUES
(1, 'Home'),
(2, 'Product'),
(3, 'Category'),
(4, 'Default'),
(5, 'Manufacturer'),
(6, 'Account'),
(7, 'Checkout'),
(8, 'Contact'),
(9, 'Sitemap'),
(10, 'Affiliate'),
(11, 'Information'),
(12, 'Special'),
(13, 'News');

-- --------------------------------------------------------

--
-- Table structure for table `nc_layout_route`
--

DROP TABLE IF EXISTS `nc_layout_route`;
CREATE TABLE `nc_layout_route` (
  `layout_route_id` int(11) NOT NULL AUTO_INCREMENT,
  `layout_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `route` varchar(255) NOT NULL,
  PRIMARY KEY (`layout_route_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_layout_route`
--

INSERT INTO `nc_layout_route` (`layout_route_id`, `layout_id`, `store_id`, `route`) VALUES
(1, 6, 0, 'account'),
(2, 10, 0, 'affiliate/'),
(3, 3, 0, 'product/category'),
(4, 1, 0, 'common/home'),
(5, 2, 0, 'product/product'),
(6, 11, 0, 'information/information'),
(7, 5, 0, 'product/manufacturer'),
(8, 7, 0, 'checkout/'),
(9, 8, 0, 'information/contact'),
(10, 9, 0, 'information/sitemap'),
(11, 12, 0, 'product/special'),
(12, 13, 0, 'information/news'),
(13, 13, 0, 'information/news_list');

-- --------------------------------------------------------

--
-- Table structure for table `nc_length_class`
--

DROP TABLE IF EXISTS `nc_length_class`;
CREATE TABLE `nc_length_class` (
  `length_class_id` int(11) NOT NULL AUTO_INCREMENT,
  `value` decimal(15,8) NOT NULL,
  PRIMARY KEY (`length_class_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_length_class`
--

INSERT INTO `nc_length_class` (`length_class_id`, `value`) VALUES
(1, '1.00000000'),
(2, '10.00000000'),
(3, '0.39370000');

-- --------------------------------------------------------

--
-- Table structure for table `nc_length_class_description`
--

DROP TABLE IF EXISTS `nc_length_class_description`;
CREATE TABLE `nc_length_class_description` (
  `length_class_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `title` varchar(32) NOT NULL,
  `unit` varchar(4) NOT NULL,
  PRIMARY KEY (`length_class_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_length_class_description`
--

INSERT INTO `nc_length_class_description` (`length_class_id`, `language_id`, `title`, `unit`) VALUES
(1, 1, 'Centimeter', 'cm'),
(2, 1, 'Millimeter', 'mm'),
(3, 1, 'Inch', 'in');

-- --------------------------------------------------------

--
-- Table structure for table `nc_location`
--

DROP TABLE IF EXISTS `nc_location`;
CREATE TABLE `nc_location` (
  `location_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `address` text CHARACTER SET utf8 NOT NULL,
  `telephone` varchar(32) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `latitude` varchar(32) NOT NULL,
  `longitude` varchar(32) NOT NULL,
  `open` text CHARACTER SET utf8 NOT NULL,
  `comment` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`location_id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_manufacturer`
--

DROP TABLE IF EXISTS `nc_manufacturer`;
CREATE TABLE `nc_manufacturer` (
  `manufacturer_id` int(11) NOT NULL AUTO_INCREMENT,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(3) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`manufacturer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_manufacturer_description`
--

DROP TABLE IF EXISTS `nc_manufacturer_description`;
CREATE TABLE `nc_manufacturer_description` (
  `manufacturer_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  `description` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`manufacturer_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_manufacturer_to_store`
--

DROP TABLE IF EXISTS `nc_manufacturer_to_store`;
CREATE TABLE `nc_manufacturer_to_store` (
  `manufacturer_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  PRIMARY KEY (`manufacturer_id`,`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_media`
--

DROP TABLE IF EXISTS `nc_media`;
CREATE TABLE `nc_media` (
  `media_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `media` varchar(255) DEFAULT NULL,
  `credit` varchar(128) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`media_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_menu`
--

DROP TABLE IF EXISTS `nc_menu`;
CREATE TABLE `nc_menu` (
  `menu_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(64) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`menu_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_menu`
--

INSERT INTO `nc_menu` (`menu_id`, `title`, `status`) VALUES
(1, 'Default Menu', 1);

-- --------------------------------------------------------

--
-- Table structure for table `nc_menu_item`
--

DROP TABLE IF EXISTS `nc_menu_item`;
CREATE TABLE `nc_menu_item` (
  `menu_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT '0',
  `menu_item_link` varchar(255) NOT NULL,
  `external_link` tinyint(1) NOT NULL,
  `sort_order` int(3) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`menu_item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_menu_item_description`
--

DROP TABLE IF EXISTS `nc_menu_item_description`;
CREATE TABLE `nc_menu_item_description` (
  `menu_item_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `menu_item_name` varchar(64) NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `meta_keyword` varchar(255) NOT NULL,
  PRIMARY KEY (`menu_item_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_menu_item_path`
--

DROP TABLE IF EXISTS `nc_menu_item_path`;
CREATE TABLE `nc_menu_item_path` (
  `menu_item_id` int(11) NOT NULL,
  `path_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY (`menu_item_id`,`path_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_menu_to_store`
--

DROP TABLE IF EXISTS `nc_menu_to_store`;
CREATE TABLE `nc_menu_to_store` (
  `menu_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  PRIMARY KEY (`menu_id`,`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_menu_to_store`
--

INSERT INTO `nc_menu_to_store` (`menu_id`, `store_id`) VALUES
(1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `nc_news`
--

DROP TABLE IF EXISTS `nc_news`;
CREATE TABLE `nc_news` (
  `news_id` int(11) NOT NULL AUTO_INCREMENT,
  `image` varchar(255) DEFAULT NULL,
  `related_method` varchar(64) NOT NULL,
  `related_option` text NOT NULL,
  `lightbox` varchar(32) NOT NULL,
  `sort_order` int(3) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `date_added` datetime NOT NULL,
  `viewed` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`news_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_news_description`
--

DROP TABLE IF EXISTS `nc_news_description`;
CREATE TABLE `nc_news_description` (
  `news_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `meta_description` VARCHAR(255) NOT NULL,
  `description` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`news_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_news_download`
--

DROP TABLE IF EXISTS `nc_news_download`;
CREATE TABLE `nc_news_download` (
  `news_download_id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(128) NOT NULL,
  `mask` varchar(128) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`news_download_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_news_download_description`
--

DROP TABLE IF EXISTS `nc_news_download_description`;
CREATE TABLE `nc_news_download_description` (
  `news_download_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`news_download_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_news_product_related`
--

DROP TABLE IF EXISTS `nc_news_product_related`;
CREATE TABLE `nc_news_product_related` (
  `news_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  PRIMARY KEY (`news_id`,`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_news_to_download`
--

DROP TABLE IF EXISTS `nc_news_to_download`;
CREATE TABLE `nc_news_to_download` (
  `news_id` int(11) NOT NULL,
  `news_download_id` int(11) NOT NULL,
  PRIMARY KEY (`news_id`,`news_download_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_news_to_store`
--

DROP TABLE IF EXISTS `nc_news_to_store`;
CREATE TABLE `nc_news_to_store` (
  `news_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  PRIMARY KEY (`news_id`,`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_offer_category_category`
--

DROP TABLE IF EXISTS `nc_offer_category_category`;
CREATE TABLE `nc_offer_category_category` (
  `offer_category_category_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `type` char(1) NOT NULL,
  `discount` decimal(15,4) NOT NULL,
  `logged` tinyint(1) NOT NULL,
  `category_one` int(11) NOT NULL,
  `category_two` int(11) NOT NULL,
  `date_start` date NOT NULL DEFAULT '0000-00-00',
  `date_end` date NOT NULL DEFAULT '0000-00-00',
  `status` tinyint(1) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`offer_category_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_offer_category_product`
--

DROP TABLE IF EXISTS `nc_offer_category_product`;
CREATE TABLE `nc_offer_category_product` (
  `offer_category_product_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `type` char(1) NOT NULL,
  `discount` decimal(15,4) NOT NULL,
  `logged` tinyint(1) NOT NULL,
  `category_one` int(11) NOT NULL,
  `product_two` int(11) NOT NULL,
  `date_start` date NOT NULL DEFAULT '0000-00-00',
  `date_end` date NOT NULL DEFAULT '0000-00-00',
  `status` tinyint(1) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`offer_category_product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_offer_product_category`
--

DROP TABLE IF EXISTS `nc_offer_product_category`;
CREATE TABLE `nc_offer_product_category` (
  `offer_product_category_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `type` char(1) NOT NULL,
  `discount` decimal(15,4) NOT NULL,
  `logged` tinyint(1) NOT NULL,
  `product_one` int(11) NOT NULL,
  `category_two` int(11) NOT NULL,
  `date_start` date NOT NULL DEFAULT '0000-00-00',
  `date_end` date NOT NULL DEFAULT '0000-00-00',
  `status` tinyint(1) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`offer_product_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_offer_product_product`
--

DROP TABLE IF EXISTS `nc_offer_product_product`;
CREATE TABLE `nc_offer_product_product` (
  `offer_product_product_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `type` char(1) NOT NULL,
  `discount` decimal(15,4) NOT NULL,
  `logged` tinyint(1) NOT NULL,
  `product_one` int(11) NOT NULL,
  `product_two` int(11) NOT NULL,
  `date_start` date NOT NULL DEFAULT '0000-00-00',
  `date_end` date NOT NULL DEFAULT '0000-00-00',
  `status` tinyint(1) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`offer_product_product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_option`
--

DROP TABLE IF EXISTS `nc_option`;
CREATE TABLE `nc_option` (
  `option_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL,
  `sort_order` int(3) NOT NULL,
  PRIMARY KEY (`option_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_option`
--

INSERT INTO `nc_option` (`option_id`, `type`, `sort_order`) VALUES
(1, 'radio', 2),
(2, 'checkbox', 3),
(4, 'text', 4),
(5, 'select', 1),
(6, 'textarea', 5),
(7, 'file', 6),
(8, 'date', 7),
(9, 'time', 8),
(10, 'datetime', 9),
(11, 'select', 1),
(12, 'date', 1);

-- --------------------------------------------------------

--
-- Table structure for table `nc_option_description`
--

DROP TABLE IF EXISTS `nc_option_description`;
CREATE TABLE `nc_option_description` (
  `option_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`option_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_option_description`
--

INSERT INTO `nc_option_description` (`option_id`, `language_id`, `name`) VALUES
(1, 1, 'Radio'),
(2, 1, 'Checkbox'),
(4, 1, 'Text'),
(6, 1, 'Textarea'),
(8, 1, 'Date'),
(7, 1, 'File'),
(5, 1, 'Select'),
(9, 1, 'Time'),
(10, 1, 'Date &amp; Time'),
(12, 1, 'Delivery Date'),
(11, 1, 'Size');

-- --------------------------------------------------------

--
-- Table structure for table `nc_option_value`
--

DROP TABLE IF EXISTS `nc_option_value`;
CREATE TABLE `nc_option_value` (
  `option_value_id` int(11) NOT NULL AUTO_INCREMENT,
  `option_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `sort_order` int(3) NOT NULL,
  PRIMARY KEY (`option_value_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_option_value`
--

INSERT INTO `nc_option_value` (`option_value_id`, `option_id`, `image`, `sort_order`) VALUES
(43, 1, '', 3),
(32, 1, '', 1),
(45, 2, '', 4),
(44, 2, '', 3),
(42, 5, '', 4),
(41, 5, '', 3),
(39, 5, '', 1),
(40, 5, '', 2),
(31, 1, '', 2),
(23, 2, '', 1),
(24, 2, '', 2),
(46, 11, '', 1),
(47, 11, '', 2),
(48, 11, '', 3);

-- --------------------------------------------------------

--
-- Table structure for table `nc_option_value_description`
--

DROP TABLE IF EXISTS `nc_option_value_description`;
CREATE TABLE `nc_option_value_description` (
  `option_value_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`option_value_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_option_value_description`
--

INSERT INTO `nc_option_value_description` (`option_value_id`, `language_id`, `option_id`, `name`) VALUES
(43, 1, 1, 'Large'),
(32, 1, 1, 'Small'),
(45, 1, 2, 'Checkbox 4'),
(44, 1, 2, 'Checkbox 3'),
(31, 1, 1, 'Medium'),
(42, 1, 5, 'Yellow'),
(41, 1, 5, 'Green'),
(39, 1, 5, 'Red'),
(40, 1, 5, 'Blue'),
(23, 1, 2, 'Checkbox 1'),
(24, 1, 2, 'Checkbox 2'),
(48, 1, 11, 'Large'),
(47, 1, 11, 'Medium'),
(46, 1, 11, 'Small');

-- --------------------------------------------------------

--
-- Table structure for table `nc_order`
--

DROP TABLE IF EXISTS `nc_order`;
CREATE TABLE `nc_order` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_no` int(11) NOT NULL DEFAULT '0',
  `invoice_prefix` varchar(32) NOT NULL,
  `store_id` int(11) NOT NULL DEFAULT '0',
  `store_name` varchar(64) NOT NULL,
  `store_url` varchar(255) NOT NULL,
  `customer_id` int(11) NOT NULL DEFAULT '0',
  `customer_group_id` int(11) NOT NULL DEFAULT '0',
  `firstname` varchar(32) NOT NULL,
  `lastname` varchar(32) NOT NULL,
  `email` varchar(96) NOT NULL,
  `telephone` varchar(32) NOT NULL,
  `fax` varchar(32) NOT NULL,
  `payment_firstname` varchar(32) NOT NULL,
  `payment_lastname` varchar(32) NOT NULL,
  `payment_company` varchar(32) NOT NULL,
  `payment_company_id` varchar(32) NOT NULL,
  `payment_tax_id` varchar(32) NOT NULL,
  `payment_address_1` varchar(128) NOT NULL,
  `payment_address_2` varchar(128) NOT NULL,
  `payment_city` varchar(128) NOT NULL,
  `payment_postcode` varchar(10) NOT NULL,
  `payment_country` varchar(128) NOT NULL,
  `payment_country_id` int(11) NOT NULL,
  `payment_zone` varchar(128) NOT NULL,
  `payment_zone_id` int(11) NOT NULL,
  `payment_address_format` text CHARACTER SET utf8 NOT NULL,
  `payment_method` varchar(128) NOT NULL,
  `payment_code` varchar(128) NOT NULL,
  `shipping_firstname` varchar(32) NOT NULL,
  `shipping_lastname` varchar(32) NOT NULL,
  `shipping_company` varchar(32) NOT NULL,
  `shipping_address_1` varchar(128) NOT NULL,
  `shipping_address_2` varchar(128) NOT NULL,
  `shipping_city` varchar(128) NOT NULL,
  `shipping_postcode` varchar(10) NOT NULL,
  `shipping_country` varchar(128) NOT NULL,
  `shipping_country_id` int(11) NOT NULL,
  `shipping_zone` varchar(128) NOT NULL,
  `shipping_zone_id` int(11) NOT NULL,
  `shipping_address_format` text CHARACTER SET utf8 NOT NULL,
  `shipping_method` varchar(128) NOT NULL,
  `shipping_code` varchar(128) NOT NULL,
  `comment` text CHARACTER SET utf8 NOT NULL,
  `total` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `order_status_id` int(11) NOT NULL DEFAULT '0',
  `affiliate_id` int(11) NOT NULL,
  `commission` decimal(15,4) NOT NULL,
  `language_id` int(11) NOT NULL,
  `currency_id` int(11) NOT NULL,
  `currency_code` varchar(3) NOT NULL,
  `currency_value` decimal(15,8) NOT NULL DEFAULT '1.00000000',
  `ip` varchar(32) NOT NULL,
  `forwarded_ip` varchar(32) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `accept_language` varchar(255) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `abandoned` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_order_download`
--

DROP TABLE IF EXISTS `nc_order_download`;
CREATE TABLE `nc_order_download` (
  `order_download_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `order_product_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `filename` varchar(128) NOT NULL,
  `mask` varchar(128) NOT NULL,
  `remaining` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`order_download_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_order_history`
--

DROP TABLE IF EXISTS `nc_order_history`;
CREATE TABLE `nc_order_history` (
  `order_history_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `order_status_id` int(11) NOT NULL,
  `notify` tinyint(1) NOT NULL DEFAULT '0',
  `comment` text CHARACTER SET utf8 NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`order_history_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_order_option`
--

DROP TABLE IF EXISTS `nc_order_option`;
CREATE TABLE `nc_order_option` (
  `order_option_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `order_product_id` int(11) NOT NULL,
  `product_option_id` int(11) NOT NULL,
  `product_option_value_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `value` text CHARACTER SET utf8 NOT NULL,
  `type` varchar(32) NOT NULL,
  PRIMARY KEY (`order_option_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_order_product`
--

DROP TABLE IF EXISTS `nc_order_product`;
CREATE TABLE `nc_order_product` (
  `order_product_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `model` varchar(64) NOT NULL,
  `quantity` int(4) NOT NULL,
  `price` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `cost` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `total` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `tax` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `reward` int(8) NOT NULL,
  `picked` tinyint(1) NOT NULL,
  `backordered` varchar(255) NOT NULL,
  PRIMARY KEY (`order_product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_order_recurring`
--

DROP TABLE IF EXISTS `nc_order_recurring`;
CREATE TABLE `nc_order_recurring` (
  `order_recurring_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `status` tinyint(4) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_quantity` int(11) NOT NULL,
  `profile_id` int(11) NOT NULL,
  `profile_name` varchar(255) NOT NULL,
  `profile_description` varchar(255) NOT NULL,
  `recurring_frequency` varchar(25) NOT NULL,
  `recurring_cycle` smallint(6) NOT NULL,
  `recurring_duration` smallint(6) NOT NULL,
  `recurring_price` decimal(10,4) NOT NULL,
  `trial` tinyint(1) NOT NULL,
  `trial_frequency` varchar(25) NOT NULL,
  `trial_cycle` smallint(6) NOT NULL,
  `trial_duration` smallint(6) NOT NULL,
  `trial_price` decimal(10,4) NOT NULL,
  `profile_reference` varchar(255) NOT NULL,
  PRIMARY KEY (`order_recurring_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_order_recurring_transaction`
--

DROP TABLE IF EXISTS `nc_order_recurring_transaction`;
CREATE TABLE `nc_order_recurring_transaction` (
  `order_recurring_transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_recurring_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `amount` decimal(10,4) NOT NULL,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`order_recurring_transaction_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_order_status`
--

DROP TABLE IF EXISTS `nc_order_status`;
CREATE TABLE `nc_order_status` (
  `order_status_id` int(11) NOT NULL AUTO_INCREMENT,
  `language_id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`order_status_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_order_status`
--

INSERT INTO `nc_order_status` (`order_status_id`, `language_id`, `name`) VALUES
(2, 1, 'Processing'),
(3, 1, 'Shipped'),
(7, 1, 'Canceled'),
(5, 1, 'Complete'),
(8, 1, 'Denied'),
(9, 1, 'Canceled Reversal'),
(10, 1, 'Failed'),
(11, 1, 'Refunded'),
(12, 1, 'Reversed'),
(13, 1, 'Chargeback'),
(1, 1, 'Pending'),
(16, 1, 'Voided'),
(15, 1, 'Processed'),
(14, 1, 'Expired');

-- --------------------------------------------------------

--
-- Table structure for table `nc_order_total`
--

DROP TABLE IF EXISTS `nc_order_total`;
CREATE TABLE `nc_order_total` (
  `order_total_id` int(10) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `code` varchar(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `text` varchar(255) NOT NULL,
  `value` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `sort_order` int(3) NOT NULL,
  PRIMARY KEY (`order_total_id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_order_voucher`
--

DROP TABLE IF EXISTS `nc_order_voucher`;
CREATE TABLE `nc_order_voucher` (
  `order_voucher_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `voucher_id` int(11) NOT NULL,
  `description` text CHARACTER SET utf8 NOT NULL,
  `code` varchar(10) NOT NULL,
  `from_name` varchar(64) NOT NULL,
  `from_email` varchar(96) NOT NULL,
  `to_name` varchar(64) NOT NULL,
  `to_email` varchar(96) NOT NULL,
  `voucher_theme_id` int(11) NOT NULL,
  `message` text CHARACTER SET utf8 NOT NULL,
  `amount` decimal(15,4) NOT NULL,
  PRIMARY KEY (`order_voucher_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------
--
-- Table structure for table `nc_palette`
--

DROP TABLE IF EXISTS `nc_palette`;
CREATE TABLE `nc_palette` (
  `palette_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`palette_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_palette_color`
--

DROP TABLE IF EXISTS `nc_palette_color`;
CREATE TABLE `nc_palette_color` (
  `palette_color_id` int(11) NOT NULL AUTO_INCREMENT,
  `palette_id` int(11) NOT NULL,
  `color` varchar(6) NOT NULL,
  `skin` varchar(64) NOT NULL,
  PRIMARY KEY (`palette_color_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_palette_color_description`
--

DROP TABLE IF EXISTS `nc_palette_color_description`;
CREATE TABLE `nc_palette_color_description` (
  `palette_color_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `palette_id` int(11) NOT NULL,
  `title` varchar(64) NOT NULL,
  PRIMARY KEY (`palette_color_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_payment_image`
--

DROP TABLE IF EXISTS `nc_payment_image`;
CREATE TABLE `nc_payment_image` (
  `payment_image_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `payment` varchar(64) NOT NULL,
  `image` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`payment_image_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_payment_image`
--

INSERT INTO `nc_payment_image` (`payment_image_id`, `name`, `payment`, `image`, `status`) VALUES
(1, 'Free Checkout', 'free_checkout', 'data/payment/free-140.png', 1),
(2, 'Cash on Delivery', 'cod', 'data/payment/cod-140.png', 1);

-- --------------------------------------------------------

--
-- Table structure for table `nc_product`
--

DROP TABLE IF EXISTS `nc_product`;
CREATE TABLE `nc_product` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `model` varchar(64) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `label` varchar(255) DEFAULT NULL,
  `price` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `cost` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `quote` tinyint(1) NOT NULL DEFAULT '0',
  `age_minimum` int(2) NOT NULL,
  `tax_class_id` int(11) NOT NULL,
  `date_available` date NOT NULL,
  `palette_id` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `quantity` int(4) NOT NULL DEFAULT '0',
  `minimum` int(11) NOT NULL DEFAULT '1',
  `subtract` tinyint(1) NOT NULL DEFAULT '1',
  `stock_status_id` int(11) NOT NULL,
  `shipping` tinyint(1) NOT NULL DEFAULT '1',
  `sku` varchar(64) NOT NULL,
  `upc` varchar(12) NOT NULL,
  `ean` varchar(14) NOT NULL,
  `jan` varchar(13) NOT NULL,
  `isbn` varchar(13) NOT NULL,
  `mpn` varchar(64) NOT NULL,
  `location` varchar(128) NOT NULL,
  `length` decimal(15,8) NOT NULL DEFAULT '0.00000000',
  `width` decimal(15,8) NOT NULL DEFAULT '0.00000000',
  `height` decimal(15,8) NOT NULL DEFAULT '0.00000000',
  `length_class_id` int(11) NOT NULL DEFAULT '0',
  `weight` decimal(15,8) NOT NULL DEFAULT '0.00000000',
  `weight_class_id` int(11) NOT NULL DEFAULT '0',
  `manufacturer_id` int(11) NOT NULL,
  `points` int(8) NOT NULL DEFAULT '0',
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `viewed` int(8) NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_product_attribute`
--

DROP TABLE IF EXISTS `nc_product_attribute`;
CREATE TABLE `nc_product_attribute` (
  `product_id` int(11) NOT NULL,
  `attribute_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `text` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`product_id`,`attribute_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_product_color`
--

DROP TABLE IF EXISTS `nc_product_color`;
CREATE TABLE `nc_product_color` (
  `product_color_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `palette_color_id` int(11) NOT NULL,
  PRIMARY KEY (`product_color_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_product_description`
--

DROP TABLE IF EXISTS `nc_product_description`;
CREATE TABLE `nc_product_description` (
  `product_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text CHARACTER SET utf8 NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `meta_keyword` varchar(255) NOT NULL,
  `tag` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`product_id`,`language_id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_product_discount`
--

DROP TABLE IF EXISTS `nc_product_discount`;
CREATE TABLE `nc_product_discount` (
  `product_discount_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `customer_group_id` int(11) NOT NULL,
  `quantity` int(4) NOT NULL DEFAULT '0',
  `priority` int(5) NOT NULL DEFAULT '1',
  `price` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `date_start` date NOT NULL DEFAULT '0000-00-00',
  `date_end` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`product_discount_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_product_field`
--

DROP TABLE IF EXISTS `nc_product_field`;
CREATE TABLE `nc_product_field` (
  `product_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `text` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`product_id`,`field_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_product_filter`
--

DROP TABLE IF EXISTS `nc_product_filter`;
CREATE TABLE `nc_product_filter` (
  `product_id` int(11) NOT NULL,
  `filter_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`,`filter_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_product_image`
--

DROP TABLE IF EXISTS `nc_product_image`;
CREATE TABLE `nc_product_image` (
  `product_image_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `palette_color_id` int(11) NOT NULL DEFAULT '0',
  `sort_order` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_image_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_product_option`
--

DROP TABLE IF EXISTS `nc_product_option`;
CREATE TABLE `nc_product_option` (
  `product_option_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `option_value` text CHARACTER SET utf8 NOT NULL,
  `required` tinyint(1) NOT NULL,
  PRIMARY KEY (`product_option_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_product_option_value`
--

DROP TABLE IF EXISTS `nc_product_option_value`;
CREATE TABLE `nc_product_option_value` (
  `product_option_value_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_option_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `option_value_id` int(11) NOT NULL,
  `quantity` int(3) NOT NULL,
  `subtract` tinyint(1) NOT NULL,
  `price` decimal(15,4) NOT NULL,
  `price_prefix` varchar(1) NOT NULL,
  `points` int(8) NOT NULL,
  `points_prefix` varchar(1) NOT NULL,
  `weight` decimal(15,8) NOT NULL,
  `weight_prefix` varchar(1) NOT NULL,
  PRIMARY KEY (`product_option_value_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_product_profile`
--

DROP TABLE IF EXISTS `nc_product_profile`;
CREATE TABLE `nc_product_profile` (
  `product_id` int(11) NOT NULL,
  `profile_id` int(11) NOT NULL,
  `customer_group_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`,`profile_id`,`customer_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_product_recurring`
--

DROP TABLE IF EXISTS `nc_product_recurring`;
CREATE TABLE `nc_product_recurring` (
  `product_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`,`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_product_related`
--

DROP TABLE IF EXISTS `nc_product_related`;
CREATE TABLE `nc_product_related` (
  `product_id` int(11) NOT NULL,
  `related_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`,`related_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_product_reward`
--

DROP TABLE IF EXISTS `nc_product_reward`;
CREATE TABLE `nc_product_reward` (
  `product_reward_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `customer_group_id` int(11) NOT NULL DEFAULT '0',
  `points` int(8) NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_reward_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_product_special`
--

DROP TABLE IF EXISTS `nc_product_special`;
CREATE TABLE `nc_product_special` (
  `product_special_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `customer_group_id` int(11) NOT NULL,
  `priority` int(5) NOT NULL DEFAULT '1',
  `price` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `date_start` date NOT NULL DEFAULT '0000-00-00',
  `date_end` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`product_special_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_product_tag`
--

DROP TABLE IF EXISTS `nc_product_tag`;
CREATE TABLE `nc_product_tag` (
  `product_tag_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `tag` varchar(64) NOT NULL,
  PRIMARY KEY (`product_tag_id`),
  KEY `product_id` (`product_id`),
  KEY `language_id` (`language_id`),
  KEY `tag` (`tag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_product_tax_local_rate`
--

DROP TABLE IF EXISTS `nc_product_tax_local_rate`;
CREATE TABLE `nc_product_tax_local_rate` (
  `product_tax_local_rate_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `tax_local_rate_id` int(11) NOT NULL,
  PRIMARY KEY (`product_tax_local_rate_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_product_to_category`
--

DROP TABLE IF EXISTS `nc_product_to_category`;
CREATE TABLE `nc_product_to_category` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`,`category_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_product_to_download`
--

DROP TABLE IF EXISTS `nc_product_to_download`;
CREATE TABLE `nc_product_to_download` (
  `product_id` int(11) NOT NULL,
  `download_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`,`download_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_product_to_layout`
--

DROP TABLE IF EXISTS `nc_product_to_layout`;
CREATE TABLE `nc_product_to_layout` (
  `product_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `layout_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`,`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_product_to_location`
--

DROP TABLE IF EXISTS `nc_product_to_location`;
CREATE TABLE `nc_product_to_location` (
  `product_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_id`,`location_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_product_to_store`
--

DROP TABLE IF EXISTS `nc_product_to_store`;
CREATE TABLE `nc_product_to_store` (
  `product_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_id`,`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_product_youtube`
--

DROP TABLE IF EXISTS `nc_product_youtube`;
CREATE TABLE `nc_product_youtube` (
  `product_id` int(11) NOT NULL,
  `video_code` varchar(255) NOT NULL,
  PRIMARY KEY (`product_id`,`video_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_profile`
--

DROP TABLE IF EXISTS `nc_profile`;
CREATE TABLE `nc_profile` (
  `profile_id` int(11) NOT NULL AUTO_INCREMENT,
  `sort_order` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `price` decimal(10,4) NOT NULL,
  `frequency` enum('day','week','semi_month','month','year') NOT NULL,
  `duration` int(10) UNSIGNED NOT NULL,
  `cycle` int(10) UNSIGNED NOT NULL,
  `trial_status` tinyint(4) NOT NULL,
  `trial_price` decimal(10,4) NOT NULL,
  `trial_frequency` enum('day','week','semi_month','month','year') NOT NULL,
  `trial_duration` int(10) UNSIGNED NOT NULL,
  `trial_cycle` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`profile_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_profile_description`
--

DROP TABLE IF EXISTS `nc_profile_description`;
CREATE TABLE `nc_profile_description` (
  `profile_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`profile_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_return`
--

DROP TABLE IF EXISTS `nc_return`;
CREATE TABLE `nc_return` (
  `return_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `firstname` varchar(32) NOT NULL,
  `lastname` varchar(32) NOT NULL,
  `email` varchar(96) NOT NULL,
  `telephone` varchar(32) NOT NULL,
  `product` varchar(255) NOT NULL,
  `model` varchar(64) NOT NULL,
  `quantity` int(4) NOT NULL,
  `opened` tinyint(1) NOT NULL,
  `return_reason_id` int(11) NOT NULL,
  `return_action_id` int(11) NOT NULL,
  `return_status_id` int(11) NOT NULL,
  `comment` text CHARACTER SET utf8,
  `date_ordered` date NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`return_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_return_action`
--

DROP TABLE IF EXISTS `nc_return_action`;
CREATE TABLE `nc_return_action` (
  `return_action_id` int(11) NOT NULL AUTO_INCREMENT,
  `language_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`return_action_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_return_action`
--

INSERT INTO `nc_return_action` (`return_action_id`, `language_id`, `name`) VALUES
(1, 1, 'Refunded'),
(2, 1, 'Credit Issued'),
(3, 1, 'Replacement Sent');

-- --------------------------------------------------------

--
-- Table structure for table `nc_return_history`
--

DROP TABLE IF EXISTS `nc_return_history`;
CREATE TABLE `nc_return_history` (
  `return_history_id` int(11) NOT NULL AUTO_INCREMENT,
  `return_id` int(11) NOT NULL,
  `return_status_id` int(11) NOT NULL,
  `notify` tinyint(1) NOT NULL,
  `comment` text CHARACTER SET utf8 NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`return_history_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_return_reason`
--

DROP TABLE IF EXISTS `nc_return_reason`;
CREATE TABLE `nc_return_reason` (
  `return_reason_id` int(11) NOT NULL AUTO_INCREMENT,
  `language_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`return_reason_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_return_reason`
--

INSERT INTO `nc_return_reason` (`return_reason_id`, `language_id`, `name`) VALUES
(1, 1, 'Dead On Arrival'),
(2, 1, 'Received Wrong Item'),
(3, 1, 'Order Error'),
(4, 1, 'Faulty, please supply details'),
(5, 1, 'Other, please supply details');

-- --------------------------------------------------------

--
-- Table structure for table `nc_return_status`
--

DROP TABLE IF EXISTS `nc_return_status`;
CREATE TABLE `nc_return_status` (
  `return_status_id` int(11) NOT NULL AUTO_INCREMENT,
  `language_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`return_status_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_return_status`
--

INSERT INTO `nc_return_status` (`return_status_id`, `language_id`, `name`) VALUES
(1, 1, 'Pending'),
(3, 1, 'Complete'),
(2, 1, 'Awaiting Products');

-- --------------------------------------------------------

--
-- Table structure for table `nc_review`
--

DROP TABLE IF EXISTS `nc_review`;
CREATE TABLE `nc_review` (
  `review_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `author` varchar(64) NOT NULL,
  `text` text CHARACTER SET utf8 NOT NULL,
  `rating` int(1) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`review_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_robot_online`
--

DROP TABLE IF EXISTS `nc_robot_online`;
CREATE TABLE `nc_robot_online` (
  `ip` varchar(32) NOT NULL,
  `robot` varchar(64) NOT NULL,
  `user_agent` text NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_setting`
--

DROP TABLE IF EXISTS `nc_setting`;
CREATE TABLE `nc_setting` (
  `setting_id` int(16) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL DEFAULT '0',
  `group` varchar(32) NOT NULL,
  `key` varchar(64) NOT NULL,
  `value` text CHARACTER SET utf8 NOT NULL,
  `serialized` tinyint(1) NOT NULL,
  PRIMARY KEY (`setting_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_setting`
--

INSERT INTO `nc_setting` (`setting_id`, `store_id`, `group`, `key`, `value`, `serialized`) VALUES
(1, 0, 'shipping', 'shipping_sort_order', '3', 0),
(2, 0, 'sub_total', 'sub_total_sort_order', '1', 0),
(3, 0, 'sub_total', 'sub_total_status', '1', 0),
(4, 0, 'tax', 'tax_status', '1', 0),
(5, 0, 'total', 'total_sort_order', '9', 0),
(6, 0, 'total', 'total_status', '1', 0),
(7, 0, 'tax', 'tax_sort_order', '5', 0),
(8, 0, 'free_checkout', 'free_checkout_sort_order', '1', 0),
(9, 0, 'free_checkout', 'free_checkout_status', '1', 0),
(10, 0, 'free_checkout', 'free_checkout_order_status_id', '1', 0),
(11, 0, 'cod', 'cod_sort_order', '2', 0),
(12, 0, 'cod', 'cod_status', '1', 0),
(13, 0, 'cod', 'cod_geo_zone_id', '0', 0),
(14, 0, 'cod', 'cod_order_status_id', '1', 0),
(15, 0, 'cod', 'cod_total_max', '', 0),
(16, 0, 'cod', 'cod_total', '0.01', 0),
(17, 0, 'shipping', 'shipping_status', '1', 0),
(18, 0, 'shipping', 'shipping_estimator', '1', 0),
(19, 0, 'coupon', 'coupon_sort_order', '4', 0),
(20, 0, 'coupon', 'coupon_status', '1', 0),
(21, 0, 'flat_1', 'flat_1_sort_order', '1', 0),
(22, 0, 'flat_1', 'flat_1_status', '1', 0),
(23, 0, 'flat_1', 'flat_1_geo_zone_id', '0', 0),
(24, 0, 'flat_1', 'flat_1_tax_class_id', '0', 0),
(25, 0, 'flat_1', 'flat_1_cost', '5.00', 0),
(26, 0, 'credit', 'credit_sort_order', '7', 0),
(27, 0, 'credit', 'credit_status', '1', 0),
(28, 0, 'reward', 'reward_sort_order', '2', 0),
(29, 0, 'reward', 'reward_status', '1', 0),
(30, 0, 'voucher', 'voucher_sort_order', '8', 0),
(31, 0, 'voucher', 'voucher_status', '1', 0),
(32, 0, 'config', 'config_image_product_width', '120', 0),
(33, 0, 'config', 'config_image_product_height', '120', 0),
(34, 0, 'config', 'config_image_popup_height', '600', 0),
(35, 0, 'config', 'config_image_popup_width', '600', 0),
(36, 0, 'config', 'config_image_thumb_height', '230', 0),
(37, 0, 'config', 'config_image_thumb_width', '230', 0),
(38, 0, 'config', 'config_image_category_height', '100', 0),
(39, 0, 'config', 'config_image_category_width', '100', 0),
(40, 0, 'config', 'config_image_additional_width', '70', 0),
(41, 0, 'config', 'config_image_additional_height', '70', 0),
(42, 0, 'config', 'config_image_brand_width', '80', 0),
(43, 0, 'config', 'config_image_brand_height', '80', 0),
(44, 0, 'config', 'config_image_related_width', '80', 0),
(45, 0, 'config', 'config_image_related_height', '80', 0),
(46, 0, 'config', 'config_image_compare_width', '90', 0),
(47, 0, 'config', 'config_image_compare_height', '90', 0),
(48, 0, 'config', 'config_image_wishlist_width', '47', 0),
(49, 0, 'config', 'config_image_wishlist_height', '47', 0),
(50, 0, 'config', 'config_image_newsthumb_width', '200', 0),
(51, 0, 'config', 'config_image_newsthumb_height', '200', 0),
(52, 0, 'config', 'config_image_newspopup_width', '500', 0),
(53, 0, 'config', 'config_image_newspopup_height', '500', 0),
(54, 0, 'config', 'config_image_cart_width', '47', 0),
(55, 0, 'config', 'config_image_cart_height', '47', 0),
(56, 0, 'config', 'config_icon', 'data/cart.png', 0),
(57, 0, 'config', 'config_logo', 'data/logo.png', 0),
(58, 0, 'config', 'config_label_size_ratio', '40', 0),
(59, 0, 'config', 'config_label_stock', '', 0),
(60, 0, 'config', 'config_label_offer', '', 0),
(61, 0, 'config', 'config_label_special', '', 0),
(62, 0, 'config', 'config_ftp_status', '0', 0),
(63, 0, 'config', 'config_ftp_host', 'localhost', 0),
(64, 0, 'config', 'config_ftp_port', '21', 0),
(65, 0, 'config', 'config_ftp_username', '', 0),
(66, 0, 'config', 'config_ftp_password', '', 0),
(67, 0, 'config', 'config_ftp_root', '', 0),
(68, 0, 'config', 'config_mail_protocol', 'mail', 0),
(69, 0, 'config', 'config_mail_parameter', '', 0),
(70, 0, 'config', 'config_smtp_host', '', 0),
(71, 0, 'config', 'config_smtp_username', '', 0),
(72, 0, 'config', 'config_smtp_password', '', 0),
(73, 0, 'config', 'config_smtp_port', '25', 0),
(74, 0, 'config', 'config_smtp_timeout', '5', 0),
(75, 0, 'config', 'config_alert_mail', '1', 0),
(76, 0, 'config', 'config_account_mail', '0', 0),
(77, 0, 'config', 'config_alert_emails', '', 0),
(78, 0, 'config', 'config_facebook', '', 0),
(79, 0, 'config', 'config_twitter', '', 0),
(80, 0, 'config', 'config_google', '', 0),
(81, 0, 'config', 'config_pinterest', '', 0),
(82, 0, 'config', 'config_instagram', '', 0),
(83, 0, 'config', 'config_skype', '', 0),
(84, 0, 'config', 'config_addthis', '', 0),
(85, 0, 'config', 'config_meta_google', '', 0),
(86, 0, 'config', 'config_meta_bing', '', 0),
(87, 0, 'config', 'config_meta_yandex', '', 0),
(88, 0, 'config', 'config_meta_baidu', '', 0),
(89, 0, 'config', 'config_meta_alexa', '', 0),
(90, 0, 'config', 'config_google_analytics', '', 0),
(91, 0, 'config', 'config_alexa_analytics', '', 0),
(92, 0, 'config', 'config_matomo_analytics', '', 0),
(93, 0, 'config', 'config_maintenance', '0', 0),
(94, 0, 'config', 'config_seo_url', '0', 0),
(95, 0, 'config', 'config_seo_url_cache', '0', 0),
(96, 0, 'config', 'config_encryption', 'SUBSTRING(SHA1(RAND()) FROM 1 FOR 8)', 0),
(97, 0, 'config', 'config_compression', '6', 0),
(98, 0, 'config', 'config_error_display', '1', 0),
(99, 0, 'config', 'config_error_log', '1', 0),
(100, 0, 'config', 'config_error_filename', 'error.txt', 0),
(101, 0, 'config', 'config_mail_filename', 'mail.txt', 0),
(102, 0, 'config', 'config_quote_filename', 'quote.txt', 0),
(103, 0, 'config', 'config_secure', '0', 0),
(104, 0, 'config', 'config_shared', '0', 0),
(105, 0, 'config', 'config_robots', 'abot\r\ndbot\r\nebot\r\nhbot\r\nkbot\r\nlbot\r\nmbot\r\nnbot\r\nobot\r\npbot\r\nrbot\r\nsbot\r\ntbot\r\nvbot\r\nybot\r\nzbot\r\nbot.\r\nbot/\r\n_bot\r\n.bot\r\n/bot\r\n-bot\r\n:bot\r\n(bot\r\ncrawl\r\nslurp\r\nspider\r\nseek\r\naccoona\r\nacoon\r\nadressendeutschland\r\nah-ha.com\r\nahoy\r\naltavista\r\nananzi\r\nanthill\r\nappie\r\narachnophilia\r\narale\r\naraneo\r\naranha\r\narchitext\r\naretha\r\narks\r\nasterias\r\natlocal\r\natn\r\natomz\r\naugurfind\r\nbackrub\r\nbannana_bot\r\nbaypup\r\nbdfetch\r\nbig brother\r\nbiglotron\r\nbjaaland\r\nblackwidow\r\nblaiz\r\nblog\r\nblo.\r\nbloodhound\r\nboitho\r\nbooch\r\nbradley\r\nbutterfly\r\ncalif\r\ncassandra\r\nccubee\r\ncfetch\r\ncharlotte\r\nchurl\r\ncienciaficcion\r\ncmc\r\ncollective\r\ncomagent\r\ncombine\r\ncomputingsite\r\ncsci\r\ncurl\r\ncusco\r\ndaumoa\r\ndeepindex\r\ndelorie\r\ndepspid\r\ndeweb\r\ndie blinde kuh\r\ndigger\r\nditto\r\ndmoz\r\ndocomo\r\ndownload express\r\ndtaagent\r\ndwcp\r\nebiness\r\nebingbong\r\ne-collector\r\nejupiter\r\nemacs-w3 search engine\r\nesther\r\nevliya celebi\r\nezresult\r\nfalcon\r\nfelix ide\r\nferret\r\nfetchrover\r\nfido\r\nfindlinks\r\nfireball\r\nfish search\r\nfouineur\r\nfunnelweb\r\ngazz\r\ngcreep\r\ngenieknows\r\ngetterroboplus\r\ngeturl\r\nglx\r\ngoforit\r\ngolem\r\ngrabber\r\ngrapnel\r\ngralon\r\ngriffon\r\ngromit\r\ngrub\r\ngulliver\r\nhamahakki\r\nharvest\r\nhavindex\r\nhelix\r\nheritrix\r\nhku www octopus\r\nhomerweb\r\nhtdig\r\nhtml index\r\nhtml_analyzer\r\nhtmlgobble\r\nhubater\r\nhyper-decontextualizer\r\nia_archiver\r\nibm_planetwide\r\nichiro\r\niconsurf\r\niltrovatore\r\nimage.kapsi.net\r\nimagelock\r\nincywincy\r\nindexer\r\ninfobee\r\ninformant\r\ningrid\r\ninktomisearch.com\r\ninspector web\r\nintelliagent\r\ninternet shinchakubin\r\nip3000\r\niron33\r\nisraeli-search\r\nivia\r\njack\r\njakarta\r\njavabee\r\njetbot\r\njumpstation\r\nkatipo\r\nkdd-explorer\r\nkilroy\r\nknowledge\r\nkototoi\r\nkretrieve\r\nlabelgrabber\r\nlachesis\r\nlarbin\r\nlegs\r\nlibwww\r\nlinkalarm\r\nlink validator\r\nlinkscan\r\nlockon\r\nlwp\r\nlycos\r\nmagpie\r\nmantraagent\r\nmapoftheinternet\r\nmarvin/\r\nmattie\r\nmediafox\r\nmediapartners\r\nmercator\r\nmerzscope\r\nmicrosoft url control\r\nminirank\r\nmiva\r\nmj12\r\nmnogosearch\r\nmoget\r\nmonster\r\nmoose\r\nmotor\r\nmultitext\r\nmuncher\r\nmuscatferret\r\nmwd.search\r\nmyweb\r\nnajdi\r\nnameprotect\r\nnationaldirectory\r\nnazilla\r\nncsa beta\r\nnec-meshexplorer\r\nnederland.zoek\r\nnetcarta webmap engine\r\nnetmechanic\r\nnetresearchserver\r\nnetscoop\r\nnewscan-online\r\nnhse\r\nnokia6682/\r\nnomad\r\nnoyona\r\nnutch\r\nnzexplorer\r\nobjectssearch\r\noccam\r\nomni\r\nopen text\r\nopenfind\r\nopenintelligencedata\r\norb search\r\nosis-project\r\npack rat\r\npageboy\r\npagebull\r\npage_verifier\r\npanscient\r\nparasite\r\npartnersite\r\npatric\r\npear.\r\npegasus\r\nperegrinator\r\npgp key agent\r\nphantom\r\nphpdig\r\npicosearch\r\npiltdownman\r\npimptrain\r\npinpoint\r\npioneer\r\npiranha\r\nplumtreewebaccessor\r\npogodak\r\npoirot\r\npompos\r\npoppelsdorf\r\npoppi\r\npopular iconoclast\r\npsycheclone\r\npublisher\r\npython\r\nrambler\r\nraven search\r\nroach\r\nroad runner\r\nroadhouse\r\nrobbie\r\nrobofox\r\nrobozilla\r\nrules\r\nsalty\r\nsbider\r\nscooter\r\nscoutjet\r\nscrubby\r\nsearch.\r\nsearchprocess\r\nsemanticdiscovery\r\nsenrigan\r\nsg-scout\r\nshai\'hulud\r\nshark\r\nshopwiki\r\nsidewinder\r\nsift\r\nsilk\r\nsimmany\r\nsite searcher\r\nsite valet\r\nsitetech-rover\r\nskymob.com\r\nsleek\r\nsmartwit\r\nsna-\r\nsnappy\r\nsnooper\r\nsohu\r\nspeedfind\r\nsphere\r\nsphider\r\nspinner\r\nspyder\r\nsteeler/\r\nsuke\r\nsuntek\r\nsupersnooper\r\nsurfnomore\r\nsven\r\nsygol\r\nszukacz\r\ntach black widow\r\ntarantula\r\ntempleton\r\n/teoma\r\nt-h-u-n-d-e-r-s-t-o-n-e\r\ntheophrastus\r\ntitan\r\ntitin\r\ntkwww\r\ntoutatis\r\nt-rex\r\ntutorgig\r\ntwiceler\r\ntwisted\r\nucsd\r\nudmsearch\r\nurl check\r\nupdated\r\nvagabondo\r\nvalkyrie\r\nverticrawl\r\nvictoria\r\nvision-search\r\nvolcano\r\nvoyager/\r\nvoyager-hc\r\nw3c_validator\r\nw3m2\r\nw3mir\r\nwalker\r\nwallpaper\r\nwanderer\r\nwauuu\r\nwavefire\r\nweb core\r\nweb hopper\r\nweb wombat\r\nwebbandit\r\nwebcatcher\r\nwebcopy\r\nwebfoot\r\nweblayers\r\nweblinker\r\nweblog monitor\r\nwebmirror\r\nwebmonkey\r\nwebquest\r\nwebreaper\r\nwebsitepulse\r\nwebsnarf\r\nwebstolperer\r\nwebvac\r\nwebwalk\r\nwebwatch\r\nwebwombat\r\nwebzinger\r\nwhizbang\r\nwhowhere\r\nwild ferret\r\nworldlight\r\nwwwc\r\nwwwster\r\nxenu\r\nxget\r\nxift\r\nxirq\r\nyandex\r\nyanga\r\nyeti\r\nyodao\r\nzao\r\nzippp\r\nzyborg', 0),
(106, 0, 'config', 'config_notification_affiliate', '0', 0),
(107, 0, 'config', 'config_notification_review', '0', 0),
(108, 0, 'config', 'config_notification_low', '1', 0),
(109, 0, 'config', 'config_notification_stock', '1', 0),
(110, 0, 'config', 'config_notification_approval', '0', 0),
(111, 0, 'config', 'config_notification_deleted', '0', 0),
(112, 0, 'config', 'config_notification_online', '0', 0),
(113, 0, 'config', 'config_notification_return', '1', 0),
(114, 0, 'config', 'config_notification_complete', '1', 0),
(115, 0, 'config', 'config_notification_pending', '1', 0),
(116, 0, 'config', 'config_notifications', '1', 0),
(117, 0, 'config', 'config_news_chars', '1200', 0),
(118, 0, 'config', 'config_news_addthis', '1', 0),
(119, 0, 'config', 'config_cookie_age', '365', 0),
(120, 0, 'config', 'config_cookie_privacy', '3', 0),
(121, 0, 'config', 'config_cookie_position', 'top', 0),
(122, 0, 'config', 'config_cookie_theme', 'dark', 0),
(123, 0, 'config', 'config_cookie_consent', '0', 0),
(124, 0, 'config', 'config_captcha_font', 'Recaptcha', 0),
(125, 0, 'config', 'config_price_hide', '0', 0),
(126, 0, 'config', 'config_price_free', '1', 0),
(127, 0, 'config', 'config_share_addthis', '0', 0),
(128, 0, 'config', 'config_lightbox', 'viewbox', 0),
(129, 0, 'config', 'config_buy_now', '0', 0),
(130, 0, 'config', 'config_barcode_type', 'TYPE_CODE_128', 0),
(131, 0, 'config', 'config_admin_barcode', '0', 0),
(132, 0, 'config', 'config_catalog_barcode', '0', 0),
(133, 0, 'config', 'config_user_group_display', 'a:1:{i:0;s:1:\"1\";}', 1),
(134, 0, 'config', 'config_autocomplete_offer', '0', 0),
(135, 0, 'config', 'config_autocomplete_product', '0', 0),
(136, 0, 'config', 'config_autocomplete_category', '0', 0),
(137, 0, 'config', 'config_pagination_lo', '1', 0),
(138, 0, 'config', 'config_pagination_hi', '0', 0),
(139, 0, 'config', 'config_catalog_limit', '12', 0),
(140, 0, 'config', 'config_admin_limit', '20', 0),
(141, 0, 'config', 'config_admin_menu_icons', '1', 0),
(142, 0, 'config', 'config_admin_width_limit', '0', 0),
(143, 0, 'config', 'config_admin_stylesheet', 'light', 0),
(144, 0, 'config', 'config_voucher_max', '1000', 0),
(145, 0, 'config', 'config_voucher_min', '1', 0),
(146, 0, 'config', 'config_coupon_special', '0', 0),
(147, 0, 'config', 'config_reward_display', '0', 0),
(148, 0, 'config', 'config_reward_rate', '100', 0),
(149, 0, 'config', 'config_return_disable', '0', 0),
(150, 0, 'config', 'config_return_status_id', '2', 0),
(151, 0, 'config', 'config_return_id', '0', 0),
(152, 0, 'config', 'config_affiliate_disable', '0', 0),
(153, 0, 'config', 'config_affiliate_activity', '1', 0),
(154, 0, 'config', 'config_affiliate_fax', '0', 0),
(155, 0, 'config', 'config_affiliate_mail', '0', 0),
(156, 0, 'config', 'config_affiliate_id', '5', 0),
(157, 0, 'config', 'config_login_attempts', '5', 0),
(158, 0, 'config', 'config_affiliate_commission', '5', 0),
(159, 0, 'config', 'config_affiliate_auto', '1', 0),
(160, 0, 'config', 'config_affiliate_approval', '0', 0),
(161, 0, 'config', 'config_force_delete', '0', 0),
(162, 0, 'config', 'config_account_id', '3', 0),
(163, 0, 'config', 'config_account_captcha', '0', 0),
(164, 0, 'config', 'config_picklist_status', '1', 0),
(165, 0, 'config', 'config_customer_dob', '0', 0),
(166, 0, 'config', 'config_customer_gender', '0', 0),
(167, 0, 'config', 'config_customer_fax', '0', 0),
(168, 0, 'config', 'config_customer_redirect', '0', 0),
(169, 0, 'config', 'config_customer_price', '0', 0),
(170, 0, 'config', 'config_customer_group_display', 'a:1:{i:0;s:1:\"1\";}', 1),
(171, 0, 'config', 'config_customer_group_id', '1', 0),
(172, 0, 'config', 'config_customer_online', '0', 0),
(173, 0, 'config', 'config_supplier_group_id', '1', 0),
(174, 0, 'config', 'config_stock_status_id', '5', 0),
(175, 0, 'config', 'config_stock_checkout', '0', 0),
(176, 0, 'config', 'config_stock_warning', '0', 0),
(177, 0, 'config', 'config_stock_display', '0', 0),
(178, 0, 'config', 'config_tax_customer', 'shipping', 0),
(179, 0, 'config', 'config_tax_default', 'shipping', 0),
(180, 0, 'config', 'config_vat', '0', 0),
(181, 0, 'config', 'config_tax', '1', 0),
(182, 0, 'config', 'config_review_login', '0', 0),
(183, 0, 'config', 'config_review_status', '1', 0),
(184, 0, 'config', 'config_download', '1', 0),
(185, 0, 'config', 'config_product_count', '1', 0),
(186, 0, 'config', 'config_empty_category', '0', 0),
(187, 0, 'config', 'config_one_page_point', '1', 0),
(188, 0, 'config', 'config_one_page_voucher', '1', 0),
(189, 0, 'config', 'config_one_page_coupon', '1', 0),
(190, 0, 'config', 'config_one_page_newsletter', '0', 0),
(191, 0, 'config', 'config_one_page_phone', '0', 0),
(192, 0, 'config', 'config_one_page_checkout', '0', 0),
(193, 0, 'config', 'config_express_point', '2', 0),
(194, 0, 'config', 'config_express_voucher', '1', 0),
(195, 0, 'config', 'config_express_coupon', '1', 0),
(196, 0, 'config', 'config_express_newsletter', '0', 0),
(197, 0, 'config', 'config_express_comment', '1', 0),
(198, 0, 'config', 'config_express_postcode', '0', 0),
(199, 0, 'config', 'config_express_billing', '1', 0),
(200, 0, 'config', 'config_express_phone', '0', 0),
(201, 0, 'config', 'config_express_password', '1', 0),
(202, 0, 'config', 'config_express_autofill', '1', 0),
(203, 0, 'config', 'config_express_checkout', '1', 0),
(204, 0, 'config', 'config_guest_checkout', '0', 0),
(205, 0, 'config', 'config_abandoned_cart', '7', 0),
(206, 0, 'config', 'config_complete_status_id', '5', 0),
(207, 0, 'config', 'config_order_status_id', '1', 0),
(208, 0, 'config', 'config_order_edit', '30', 0),
(209, 0, 'config', 'config_tax_breakdown', '0', 0),
(210, 0, 'config', 'config_cart_weight', '1', 0),
(211, 0, 'config', 'config_auto_invoice', '1', 0),
(212, 0, 'config', 'config_invoice_prefix', CONCAT('INV-',YEAR(NOW()),'-00'), 0),
(213, 0, 'config', 'config_checkout_id', '5', 0),
(214, 0, 'config', 'config_map_display', '0', 0),
(215, 0, 'config', 'config_map_code', '', 0),
(216, 0, 'config', 'config_store_location', '0', 0),
(217, 0, 'config', 'config_store_longitude', '', 0),
(218, 0, 'config', 'config_store_latitude', '', 0),
(219, 0, 'config', 'config_store_address', '0', 0),
(220, 0, 'config', 'config_time_offset', '0', 0),
(221, 0, 'config', 'config_currency', 'GBP', 0),
(222, 0, 'config', 'config_currency_auto', '1', 0),
(223, 0, 'config', 'config_alpha_vantage', '', 0),
(224, 0, 'config', 'config_date_format', 'short', 0),
(225, 0, 'config', 'config_owner', 'Your Name', 0),
(226, 0, 'config', 'config_address', 'Store Address', 0),
(227, 0, 'config', 'config_email', 'contact@store.com', 0),
(228, 0, 'config', 'config_email_noreply', 'noreply@store.com', 0),
(229, 0, 'config', 'config_telephone', '0123456789', 0),
(230, 0, 'config', 'config_fax', '', 0),
(231, 0, 'config', 'config_company_id', '', 0),
(232, 0, 'config', 'config_company_tax_id', '', 0),
(233, 0, 'config', 'config_bank_name', '', 0),
(234, 0, 'config', 'config_bank_sort_code', '', 0),
(235, 0, 'config', 'config_bank_account', '', 0),
(236, 0, 'config', 'config_title', 'Your Store', 0),
(237, 0, 'config', 'config_meta_description', 'My Store', 0),
(238, 0, 'config', 'config_meta_keyword', '', 0),
(239, 0, 'config', 'config_template', 'default', 0),
(240, 0, 'config', 'config_layout_id', '4', 0),
(241, 0, 'config', 'config_country_id', '222', 0),
(242, 0, 'config', 'config_zone_id', '3553', 0),
(243, 0, 'config', 'config_language', 'en', 0),
(244, 0, 'config', 'config_admin_language', 'en', 0),
(245, 0, 'config', 'config_length_class_id', '1', 0),
(246, 0, 'config', 'config_weight_class_id', '1', 0),
(247, 0, 'config', 'config_name', 'Your Store', 0),
(248, 0, 'config', 'config_robots_online', '1', 0),
(249, 0, 'config', 'config_password', '1', 0),
(250, 0, 'config', 'config_ban_page', 'search', 0),
(251, 0, 'config', 'config_sitemap_links', '0', 0),
(252, 0, 'config', 'config_file_max_size', '2048000', 0),
(253, 0, 'config', 'config_file_extension_allowed', 'txt\r\npng\r\njpe\r\njpeg\r\njpg\r\ngif\r\nbmp\r\nico\r\ntiff\r\ntif\r\nsvg\r\nsvgz\r\nzip\r\nrar\r\nmsi\r\ncab\r\nmp3\r\nmp4\r\navi\r\noga\r\nogv\r\nogg\r\nm4a\r\nm4v\r\nwav\r\nwebm\r\nwma\r\nwmv\r\nqt\r\nmov\r\npdf\r\npsd\r\nai\r\neps\r\nps\r\ndoc\r\nrtf\r\nxls\r\nppt\r\nodt\r\nods', 0),
(254, 0, 'config', 'config_file_mime_allowed', 'text/plain\r\nimage/png\r\nimage/x-png\r\nimage/jpeg\r\nimage/pjpeg\r\nimage/gif\r\nimage/bmp\r\nimage/tiff\r\nimage/svg+xml\r\nmultipart/x-zip\r\napplication/zip\r\n&quot;application/zip&quot;\r\napplication/x-zip\r\n&quot;application/x-zip&quot;\r\napplication/x-zip-compressed\r\n&quot;application/x-zip-compressed&quot;\r\napplication/rar\r\n&quot;application/rar&quot;\r\napplication/x-rar\r\n&quot;application/x-rar&quot;\r\napplication/x-rar-compressed\r\n&quot;application/x-rar-compressed&quot;\r\napplication/octet-stream\r\n&quot;application/octet-stream&quot;\r\naudio/mp3\r\naudio/mpeg\r\naudio/x-mpeg\r\naudio/mpeg3\r\naudio/x-mpeg-3\r\naudio/oga\r\naudio/m4a\r\naudio/wav\r\naudio/x-wav\r\naudio/wave\r\naudio/x-pn-wav\r\naudio/webm\r\naudio/x-ms-wma\r\nvideo/x-ms-asf\r\nvideo/mp4\r\nvideo/mpeg\r\nvideo/x-mpeg\r\nvideo/x-mpeq2a\r\nvideo/ogv\r\nvideo/ogm\r\nvideo/webm\r\nvideo/x-m4v\r\nvideo/msvideo\r\nvideo/x-ms-wmv\r\nvideo/quicktime\r\napplication/ogg\r\napplication/pdf\r\napplication/x-pdf\r\napplication/x-shockwave-flash\r\napplication/msword\r\napplication/rtf\r\napplication/vnd.ms-excel\r\napplication/vnd.ms-powerpoint\r\napplication/vnd.oasis.opendocument.text\r\napplication/vnd.oasis.opendocument.spreadsheet', 0),
(255, 0, 'export_import', 'export_import_settings_use_export_tags', '1', 0),
(256, 0, 'export_import', 'export_import_settings_use_export_cache', '0', 0),
(257, 0, 'export_import', 'export_import_settings_use_import_cache', '0', 0),
(258, 0, 'account', 'account_theme', '0', 0),
(259, 0, 'account', 'account_title1', '', 0),
(260, 0, 'account', 'account_mode', '0', 0),
(261, 0, 'affiliate', 'affiliate_theme', '1', 0),
(262, 0, 'affiliate', 'affiliate_title1', '', 0),
(263, 0, 'affiliate', 'affiliate_mode', '0', 0),
(264, 0, 'latest', 'latest_theme', '0', 0),
(265, 0, 'latest', 'latest_title1', '', 0),
(266, 0, 'latest', 'latest_viewproduct', '0', 0),
(267, 0, 'latest', 'latest_addproduct', '0', 0),
(268, 0, 'menu_horizontal', 'menu_horizontal_theme', 'dark', 0),
(269, 0, 'menu_horizontal', 'menu_horizontal_header_color', 'white', 0),
(270, 0, 'menu_horizontal', 'menu_horizontal_header_shape', 'rounded-0', 0),
(271, 0, 'menu_horizontal', 'menu_horizontal_column_limit', '6', 0),
(272, 0, 'menu_horizontal', 'menu_horizontal_column_number', '2', 0),
(273, 0, 'menu_horizontal', 'menu_horizontal_module', 'a:13:{i:0;a:7:{s:7:\"menu_id\";s:1:\"1\";s:4:\"home\";s:1:\"1\";s:9:\"layout_id\";s:1:\"6\";s:8:\"position\";s:14:\"content_higher\";s:9:\"direction\";s:1:\"1\";s:6:\"status\";s:1:\"1\";s:10:\"sort_order\";s:1:\"1\";}i:1;a:7:{s:7:\"menu_id\";s:1:\"1\";s:4:\"home\";s:1:\"1\";s:9:\"layout_id\";s:2:\"10\";s:8:\"position\";s:14:\"content_higher\";s:9:\"direction\";s:1:\"1\";s:6:\"status\";s:1:\"1\";s:10:\"sort_order\";s:1:\"1\";}i:2;a:7:{s:7:\"menu_id\";s:1:\"1\";s:4:\"home\";s:1:\"1\";s:9:\"layout_id\";s:1:\"3\";s:8:\"position\";s:14:\"content_higher\";s:9:\"direction\";s:1:\"1\";s:6:\"status\";s:1:\"1\";s:10:\"sort_order\";s:1:\"1\";}i:3;a:7:{s:7:\"menu_id\";s:1:\"1\";s:4:\"home\";s:1:\"1\";s:9:\"layout_id\";s:1:\"7\";s:8:\"position\";s:14:\"content_higher\";s:9:\"direction\";s:1:\"1\";s:6:\"status\";s:1:\"1\";s:10:\"sort_order\";s:1:\"1\";}i:4;a:7:{s:7:\"menu_id\";s:1:\"1\";s:4:\"home\";s:1:\"1\";s:9:\"layout_id\";s:1:\"8\";s:8:\"position\";s:14:\"content_higher\";s:9:\"direction\";s:1:\"1\";s:6:\"status\";s:1:\"1\";s:10:\"sort_order\";s:1:\"1\";}i:5;a:7:{s:7:\"menu_id\";s:1:\"1\";s:4:\"home\";s:1:\"1\";s:9:\"layout_id\";s:1:\"4\";s:8:\"position\";s:14:\"content_higher\";s:9:\"direction\";s:1:\"1\";s:6:\"status\";s:1:\"1\";s:10:\"sort_order\";s:1:\"1\";}i:6;a:7:{s:7:\"menu_id\";s:1:\"1\";s:4:\"home\";s:1:\"1\";s:9:\"layout_id\";s:1:\"1\";s:8:\"position\";s:14:\"content_higher\";s:9:\"direction\";s:1:\"1\";s:6:\"status\";s:1:\"1\";s:10:\"sort_order\";s:1:\"1\";}i:7;a:7:{s:7:\"menu_id\";s:1:\"1\";s:4:\"home\";s:1:\"1\";s:9:\"layout_id\";s:2:\"11\";s:8:\"position\";s:14:\"content_higher\";s:9:\"direction\";s:1:\"1\";s:6:\"status\";s:1:\"1\";s:10:\"sort_order\";s:1:\"1\";}i:8;a:7:{s:7:\"menu_id\";s:1:\"1\";s:4:\"home\";s:1:\"1\";s:9:\"layout_id\";s:1:\"5\";s:8:\"position\";s:14:\"content_higher\";s:9:\"direction\";s:1:\"1\";s:6:\"status\";s:1:\"1\";s:10:\"sort_order\";s:1:\"1\";}i:9;a:7:{s:7:\"menu_id\";s:1:\"1\";s:4:\"home\";s:1:\"1\";s:9:\"layout_id\";s:2:\"13\";s:8:\"position\";s:14:\"content_higher\";s:9:\"direction\";s:1:\"1\";s:6:\"status\";s:1:\"1\";s:10:\"sort_order\";s:1:\"1\";}i:10;a:7:{s:7:\"menu_id\";s:1:\"1\";s:4:\"home\";s:1:\"1\";s:9:\"layout_id\";s:1:\"2\";s:8:\"position\";s:14:\"content_higher\";s:9:\"direction\";s:1:\"1\";s:6:\"status\";s:1:\"1\";s:10:\"sort_order\";s:1:\"1\";}i:11;a:7:{s:7:\"menu_id\";s:1:\"1\";s:4:\"home\";s:1:\"1\";s:9:\"layout_id\";s:1:\"9\";s:8:\"position\";s:14:\"content_higher\";s:9:\"direction\";s:1:\"1\";s:6:\"status\";s:1:\"1\";s:10:\"sort_order\";s:1:\"1\";}i:12;a:7:{s:7:\"menu_id\";s:1:\"1\";s:4:\"home\";s:1:\"1\";s:9:\"layout_id\";s:2:\"12\";s:8:\"position\";s:14:\"content_higher\";s:9:\"direction\";s:1:\"1\";s:6:\"status\";s:1:\"1\";s:10:\"sort_order\";s:1:\"1\";}}', 1),
(274, 0, 'menu_vertical', 'menu_vertical_theme', '1', 0),
(275, 0, 'menu_vertical', 'menu_vertical_title1', '', 0),
(276, 0, 'news', 'news_theme', '1', 0),
(277, 0, 'news', 'news_title1', '', 0),
(278, 0, 'news', 'news_headline', '0', 0),
(279, 0, 'default', 'default_widescreen', 'normal', 0),
(280, 0, 'default', 'default_breadcrumbs', '1', 0),
(281, 0, 'default', 'default_back_to_top', '1', 0),
(282, 0, 'default', 'default_right_click', '0', 0),
(283, 0, 'default', 'default_web_design', '', 0),
(284, 0, 'default', 'default_powered_by', '1', 0),
(285, 0, 'default', 'default_footer_theme', 'dark', 0),
(286, 0, 'default', 'default_footer_color', 'white', 0),
(287, 0, 'default', 'default_footer_shape', 'rounded-0', 0),
(288, 0, 'default', 'default_footer_big_column', '0', 0),
(289, 0, 'default', 'default_footer_location', '1', 0),
(290, 0, 'default', 'default_footer_phone', '1', 0),
(291, 0, 'default', 'default_footer_email', '1', 0),
(292, 0, 'default', 'default_footer_facebook', '0', 0),
(293, 0, 'default', 'default_footer_twitter', '0', 0),
(294, 0, 'default', 'default_footer_google', '0', 0),
(295, 0, 'default', 'default_footer_pinterest', '0', 0),
(296, 0, 'default', 'default_footer_instagram', '0', 0),
(297, 0, 'default', 'default_footer_skype', '0', 0),
(298, 0, 'default', 'default_livesearch', '1', 0),
(299, 0, 'default', 'default_livesearch_limit', '10', 0),
(300, 0, 'default', 'default_product_stock_low', '0', 0),
(301, 0, 'default', 'default_product_stock_limit', '1', 0),
(302, 0, 'default', 'default_manufacturer_name', '0', 0),
(303, 0, 'default', 'default_manufacturer_image', '1', 0),
(304, 0, 'default', 'default_stylesheet', '0', 0);

-- --------------------------------------------------------

--
-- Table structure for table `nc_stock_status`
--

DROP TABLE IF EXISTS `nc_stock_status`;
CREATE TABLE `nc_stock_status` (
  `stock_status_id` int(11) NOT NULL AUTO_INCREMENT,
  `language_id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`stock_status_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_stock_status`
--

INSERT INTO `nc_stock_status` (`stock_status_id`, `language_id`, `name`) VALUES
(7, 1, 'In Stock'),
(8, 1, 'Pre-Order'),
(5, 1, 'Out Of Stock'),
(6, 1, '2 - 3 Days');

-- --------------------------------------------------------

--
-- Table structure for table `nc_store`
--

DROP TABLE IF EXISTS `nc_store`;
CREATE TABLE `nc_store` (
  `store_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `url` varchar(255) NOT NULL,
  `ssl` varchar(255) NOT NULL,
  PRIMARY KEY (`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_supplier`
--

DROP TABLE IF EXISTS `nc_supplier`;
CREATE TABLE `nc_supplier` (
  `supplier_id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(32) NOT NULL,
  `company` varchar(32) NOT NULL,
  `account` varchar(32) NOT NULL,
  `description` text CHARACTER SET utf8 NOT NULL,
  `contact` varchar(64) NOT NULL,
  `email` varchar(96) NOT NULL,
  `telephone` varchar(32) NOT NULL,
  `fax` varchar(32) NOT NULL,
  `address_id` int(11) NOT NULL DEFAULT '0',
  `supplier_group_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`supplier_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_supplier_address`
--

DROP TABLE IF EXISTS `nc_supplier_address`;
CREATE TABLE `nc_supplier_address` (
  `address_id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) NOT NULL,
  `company` varchar(32) NOT NULL,
  `address_1` varchar(128) NOT NULL,
  `address_2` varchar(128) NOT NULL,
  `city` varchar(128) NOT NULL,
  `postcode` varchar(10) NOT NULL,
  `country_id` int(11) NOT NULL DEFAULT '0',
  `zone_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`address_id`),
  KEY `supplier_id` (`supplier_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_supplier_group`
--

DROP TABLE IF EXISTS `nc_supplier_group`;
CREATE TABLE `nc_supplier_group` (
  `supplier_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_method` varchar(32) NOT NULL,
  `payment_method` varchar(32) NOT NULL,
  `sort_order` int(3) NOT NULL,
  PRIMARY KEY (`supplier_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_supplier_group`
--

INSERT INTO `nc_supplier_group` (`supplier_group_id`, `order_method`, `payment_method`, `sort_order`) VALUES
(1, 'email', 'creditcard', 1);

-- --------------------------------------------------------

--
-- Table structure for table `nc_supplier_group_description`
--

DROP TABLE IF EXISTS `nc_supplier_group_description`;
CREATE TABLE `nc_supplier_group_description` (
  `supplier_group_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `description` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`supplier_group_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_supplier_group_description`
--

INSERT INTO `nc_supplier_group_description` (`supplier_group_id`, `language_id`, `name`, `description`) VALUES
(1, 1, 'Default', 'Default');

-- --------------------------------------------------------

--
-- Table structure for table `nc_supplier_history`
--

DROP TABLE IF EXISTS `nc_supplier_history`;
CREATE TABLE `nc_supplier_history` (
  `supplier_history_id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) NOT NULL,
  `comment` text CHARACTER SET utf8 NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`supplier_history_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_supplier_product`
--

DROP TABLE IF EXISTS `nc_supplier_product`;
CREATE TABLE `nc_supplier_product` (
  `supplier_product_id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `manufacturer_id` int(11) NOT NULL,
  `model` varchar(64) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `tax_class_id` int(11) NOT NULL,
  `unit` int(11) NOT NULL,
  `color` varchar(64) NOT NULL,
  `size` varchar(64) NOT NULL,
  `quantity` int(11) NOT NULL,
  `length` decimal(15,8) NOT NULL DEFAULT '0.00000000',
  `width` decimal(15,8) NOT NULL DEFAULT '0.00000000',
  `height` decimal(15,8) NOT NULL DEFAULT '0.00000000',
  `length_class_id` int(11) NOT NULL DEFAULT '0',
  `weight` decimal(15,8) NOT NULL DEFAULT '0.00000000',
  `weight_class_id` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`supplier_product_id`,`supplier_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_tax_class`
--

DROP TABLE IF EXISTS `nc_tax_class`;
CREATE TABLE `nc_tax_class` (
  `tax_class_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL,
  `description` varchar(255) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`tax_class_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_tax_class`
--

INSERT INTO `nc_tax_class` (`tax_class_id`, `title`, `description`, `date_added`, `date_modified`) VALUES
(1, 'Taxable Goods', 'Taxed Products', '2017-01-06 23:21:53', '2017-09-23 14:07:50'),
(2, 'Downloadable Products', 'Downloadable', '2017-09-21 22:19:39', '2017-09-22 10:27:36'),
(3, 'EU E-medias', 'EU Tax Law', '2018-01-01 00:00:01', '2018-01-17 10:15:15');

-- --------------------------------------------------------

--
-- Table structure for table `nc_tax_local_rate`
--

DROP TABLE IF EXISTS `nc_tax_local_rate`;
CREATE TABLE `nc_tax_local_rate` (
  `tax_local_rate_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `rate` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`tax_local_rate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_tax_rate`
--

DROP TABLE IF EXISTS `nc_tax_rate`;
CREATE TABLE `nc_tax_rate` (
  `tax_rate_id` int(11) NOT NULL AUTO_INCREMENT,
  `geo_zone_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(32) NOT NULL,
  `rate` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `type` char(1) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`tax_rate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_tax_rate`
--

INSERT INTO `nc_tax_rate` (`tax_rate_id`, `geo_zone_id`, `name`, `rate`, `type`, `date_added`, `date_modified`) VALUES
(1, 3, 'VAT (20%)', '20.0000', 'P', '2017-03-09 21:17:10', '2017-09-22 22:24:29'),
(2, 3, 'Eco Tax (+2.00)', '2.0000', 'F', '2017-09-21 21:49:23', '2017-09-23 00:40:19'),
(3, 1, 'EU Members VAT', '20.0000', 'P', '2018-01-01 00:00:01', '2018-01-17 10:15:15');

-- --------------------------------------------------------

--
-- Table structure for table `nc_tax_rate_to_customer_group`
--

DROP TABLE IF EXISTS `nc_tax_rate_to_customer_group`;
CREATE TABLE `nc_tax_rate_to_customer_group` (
  `tax_rate_id` int(11) NOT NULL,
  `customer_group_id` int(11) NOT NULL,
  PRIMARY KEY (`tax_rate_id`,`customer_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_tax_rate_to_customer_group`
--

INSERT INTO `nc_tax_rate_to_customer_group` (`tax_rate_id`, `customer_group_id`) VALUES
(1, 1),
(2, 1),
(3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `nc_tax_rule`
--

DROP TABLE IF EXISTS `nc_tax_rule`;
CREATE TABLE `nc_tax_rule` (
  `tax_rule_id` int(11) NOT NULL AUTO_INCREMENT,
  `tax_class_id` int(11) NOT NULL,
  `tax_rate_id` int(11) NOT NULL,
  `based` varchar(32) NOT NULL,
  `priority` int(5) NOT NULL DEFAULT '1',
  PRIMARY KEY (`tax_rule_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_tax_rule`
--

INSERT INTO `nc_tax_rule` (`tax_rule_id`, `tax_class_id`, `tax_rate_id`, `based`, `priority`) VALUES
(1, 2, 1, 'payment', 1),
(2, 2, 2, 'store', 0),
(3, 1, 1, 'shipping', 1),
(4, 1, 2, 'shipping', 2),
(5, 3, 3, 'shipping', 1);

-- --------------------------------------------------------

--
-- Table structure for table `nc_upload`
--

DROP TABLE IF EXISTS `nc_upload`;
CREATE TABLE `nc_upload` (
  `upload_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `filename` varchar(128) NOT NULL,
  `code` varchar(32) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`upload_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_url_alias`
--

DROP TABLE IF EXISTS `nc_url_alias`;
CREATE TABLE `nc_url_alias` (
  `url_alias_id` int(11) NOT NULL AUTO_INCREMENT,
  `query` varchar(255) NOT NULL,
  `keyword` varchar(255) NOT NULL,
  PRIMARY KEY (`url_alias_id`),
  KEY `query` (`query`),
  KEY `keyword` (`keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_user`
--

DROP TABLE IF EXISTS `nc_user`;
CREATE TABLE `nc_user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_group_id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `image` varchar(255) NOT NULL,
  `password` varchar(40) NOT NULL,
  `salt` varchar(9) NOT NULL,
  `firstname` varchar(32) NOT NULL,
  `lastname` varchar(32) NOT NULL,
  `email` varchar(96) NOT NULL,
  `code` varchar(40) NOT NULL,
  `ip` varchar(32) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_user_group`
--

DROP TABLE IF EXISTS `nc_user_group`;
CREATE TABLE `nc_user_group` (
  `user_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `permission` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`user_group_id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_user_group`
--

INSERT INTO `nc_user_group` (`user_group_id`, `name`, `permission`) VALUES
(1, 'Top Administrator', 'a:2:{s:6:\"access\";a:245:{i:0;s:12:\"blog/article\";i:1;s:11:\"blog/author\";i:2;s:13:\"blog/category\";i:3;s:12:\"blog/comment\";i:4;s:12:\"blog/install\";i:5;s:11:\"blog/report\";i:6;s:17:\"catalog/attribute\";i:7;s:23:\"catalog/attribute_group\";i:8;s:16:\"catalog/category\";i:9;s:16:\"catalog/download\";i:10;s:13:\"catalog/field\";i:11;s:14:\"catalog/filter\";i:12;s:19:\"catalog/information\";i:13;s:20:\"catalog/manufacturer\";i:14;s:12:\"catalog/news\";i:15;s:21:\"catalog/news_download\";i:16;s:14:\"catalog/option\";i:17;s:15:\"catalog/palette\";i:18;s:15:\"catalog/product\";i:19;s:15:\"catalog/profile\";i:20;s:14:\"catalog/review\";i:21;s:18:\"common/filemanager\";i:22;s:23:\"common/filemanager_full\";i:23;s:11:\"common/home\";i:24;s:21:\"design/administration\";i:25;s:13:\"design/banner\";i:26;s:17:\"design/connection\";i:27;s:13:\"design/footer\";i:28;s:13:\"design/layout\";i:29;s:12:\"design/media\";i:30;s:11:\"design/menu\";i:31;s:17:\"design/menu_items\";i:32;s:14:\"design/payment\";i:33;s:14:\"extension/feed\";i:34;s:15:\"extension/fraud\";i:35;s:22:\"extension/modification\";i:36;s:16:\"extension/module\";i:37;s:17:\"extension/openbay\";i:38;s:17:\"extension/payment\";i:39;s:18:\"extension/shipping\";i:40;s:15:\"extension/theme\";i:41;s:15:\"extension/total\";i:42;s:16:\"feed/google_base\";i:43;s:19:\"feed/google_sitemap\";i:44;s:13:\"feed/rss_feed\";i:45;s:12:\"feed/storeya\";i:46;s:18:\"fraud/fraudlabspro\";i:47;s:13:\"fraud/maxmind\";i:48;s:20:\"localisation/country\";i:49;s:21:\"localisation/currency\";i:50;s:21:\"localisation/geo_zone\";i:51;s:21:\"localisation/language\";i:52;s:25:\"localisation/length_class\";i:53;s:21:\"localisation/location\";i:54;s:25:\"localisation/order_status\";i:55;s:26:\"localisation/return_action\";i:56;s:26:\"localisation/return_reason\";i:57;s:26:\"localisation/return_status\";i:58;s:25:\"localisation/stock_status\";i:59;s:22:\"localisation/tax_class\";i:60;s:27:\"localisation/tax_local_rate\";i:61;s:21:\"localisation/tax_rate\";i:62;s:25:\"localisation/weight_class\";i:63;s:17:\"localisation/zone\";i:64;s:24:\"modification/blog_system\";i:65;s:20:\"modification/eutaxes\";i:66;s:19:\"modification/vqmods\";i:67;s:14:\"module/account\";i:68;s:16:\"module/affiliate\";i:69;s:18:\"module/also_bought\";i:70;s:13:\"module/banner\";i:71;s:17:\"module/bestseller\";i:72;s:11:\"module/blog\";i:73;s:20:\"module/blog_category\";i:74;s:15:\"module/carousel\";i:75;s:15:\"module/category\";i:76;s:15:\"module/featured\";i:77;s:13:\"module/filter\";i:78;s:22:\"module/google_hangouts\";i:79;s:11:\"module/html\";i:80;s:18:\"module/information\";i:81;s:13:\"module/latest\";i:82;s:12:\"module/links\";i:83;s:15:\"module/location\";i:84;s:19:\"module/manufacturer\";i:85;s:18:\"module/mediaplayer\";i:86;s:22:\"module/menu_horizontal\";i:87;s:20:\"module/menu_vertical\";i:88;s:11:\"module/news\";i:89;s:14:\"module/palette\";i:90;s:13:\"module/paypal\";i:91;s:14:\"module/popular\";i:92;s:16:\"module/pp_layout\";i:93;s:12:\"module/skype\";i:94;s:16:\"module/slideshow\";i:95;s:14:\"module/special\";i:96;s:12:\"module/store\";i:97;s:15:\"module/tagcloud\";i:98;s:13:\"module/viewed\";i:99;s:14:\"module/welcome\";i:100;s:24:\"payment/authorizenet_aim\";i:101;s:24:\"payment/authorizenet_sim\";i:102;s:16:\"payment/bank_bni\";i:103;s:21:\"payment/bank_transfer\";i:104;s:16:\"payment/best2pay\";i:105;s:23:\"payment/best2pay_emoney\";i:106;s:22:\"payment/bluepay_hosted\";i:107;s:24:\"payment/bluepay_redirect\";i:108;s:15:\"payment/cardpay\";i:109;s:14:\"payment/cheque\";i:110;s:11:\"payment/cod\";i:111;s:12:\"payment/eway\";i:112;s:17:\"payment/firstdata\";i:113;s:24:\"payment/firstdata_remote\";i:114;s:21:\"payment/free_checkout\";i:115;s:14:\"payment/g2apay\";i:116;s:17:\"payment/globalpay\";i:117;s:24:\"payment/globalpay_remote\";i:118;s:16:\"payment/in_store\";i:119;s:22:\"payment/klarna_account\";i:120;s:22:\"payment/klarna_invoice\";i:121;s:14:\"payment/liqpay\";i:122;s:14:\"payment/nochex\";i:123;s:13:\"payment/okpay\";i:124;s:14:\"payment/payhub\";i:125;s:12:\"payment/paym\";i:126;s:15:\"payment/paymate\";i:127;s:16:\"payment/paypoint\";i:128;s:13:\"payment/payza\";i:129;s:26:\"payment/perpetual_payments\";i:130;s:18:\"payment/pp_express\";i:131;s:25:\"payment/pp_payflow_iframe\";i:132;s:14:\"payment/pp_pro\";i:133;s:21:\"payment/pp_pro_iframe\";i:134;s:17:\"payment/pp_pro_pf\";i:135;s:17:\"payment/pp_pro_uk\";i:136;s:19:\"payment/pp_standard\";i:137;s:15:\"payment/sagepay\";i:138;s:22:\"payment/sagepay_direct\";i:139;s:22:\"payment/sagepay_server\";i:140;s:18:\"payment/sagepay_us\";i:141;s:14:\"payment/skrill\";i:142;s:23:\"payment/stripe_payments\";i:143;s:19:\"payment/twocheckout\";i:144;s:28:\"payment/web_payment_software\";i:145;s:16:\"payment/worldpay\";i:146;s:23:\"payment/worldpay_online\";i:147;s:22:\"report/abandoned_carts\";i:148;s:25:\"report/affiliate_activity\";i:149;s:27:\"report/affiliate_commission\";i:150;s:21:\"report/banner_clicked\";i:151;s:23:\"report/customer_country\";i:152;s:22:\"report/customer_credit\";i:153;s:23:\"report/customer_deleted\";i:154;s:22:\"report/customer_online\";i:155;s:21:\"report/customer_order\";i:156;s:22:\"report/customer_reward\";i:157;s:24:\"report/customer_wishlist\";i:158;s:20:\"report/product_label\";i:159;s:21:\"report/product_markup\";i:160;s:24:\"report/product_purchased\";i:161;s:23:\"report/product_quantity\";i:162;s:21:\"report/product_viewed\";i:163;s:19:\"report/robot_online\";i:164;s:18:\"report/sale_coupon\";i:165;s:17:\"report/sale_order\";i:166;s:18:\"report/sale_profit\";i:167;s:18:\"report/sale_return\";i:168;s:20:\"report/sale_shipping\";i:169;s:15:\"report/sale_tax\";i:170;s:14:\"sale/affiliate\";i:171;s:12:\"sale/contact\";i:172;s:11:\"sale/coupon\";i:173;s:13:\"sale/customer\";i:174;s:20:\"sale/customer_ban_ip\";i:175;s:19:\"sale/customer_group\";i:176;s:10:\"sale/offer\";i:177;s:28:\"sale/offer_category_category\";i:178;s:27:\"sale/offer_category_product\";i:179;s:27:\"sale/offer_product_category\";i:180;s:26:\"sale/offer_product_product\";i:181;s:10:\"sale/order\";i:182;s:14:\"sale/recurring\";i:183;s:11:\"sale/return\";i:184;s:13:\"sale/supplier\";i:185;s:19:\"sale/supplier_group\";i:186;s:21:\"sale/supplier_product\";i:187;s:12:\"sale/voucher\";i:188;s:18:\"sale/voucher_theme\";i:189;s:15:\"setting/setting\";i:190;s:13:\"setting/store\";i:191;s:16:\"shipping/airmail\";i:192;s:16:\"shipping/auspost\";i:193;s:19:\"shipping/canadapost\";i:194;s:17:\"shipping/citylink\";i:195;s:12:\"shipping/dhl\";i:196;s:16:\"shipping/digital\";i:197;s:14:\"shipping/fedex\";i:198;s:15:\"shipping/flat_1\";i:199;s:15:\"shipping/flat_2\";i:200;s:15:\"shipping/flat_3\";i:201;s:13:\"shipping/free\";i:202;s:16:\"shipping/geozone\";i:203;s:13:\"shipping/item\";i:204;s:23:\"shipping/parcelforce_48\";i:205;s:15:\"shipping/pickup\";i:206;s:14:\"shipping/price\";i:207;s:19:\"shipping/royal_mail\";i:208;s:12:\"shipping/tnt\";i:209;s:12:\"shipping/ups\";i:210;s:13:\"shipping/usps\";i:211;s:15:\"shipping/weight\";i:212;s:13:\"theme/default\";i:213;s:20:\"tool/api_key_manager\";i:214;s:11:\"tool/backup\";i:215;s:13:\"tool/block_ip\";i:216;s:16:\"tool/cache_files\";i:217;s:17:\"tool/cache_images\";i:218;s:18:\"tool/configuration\";i:219;s:13:\"tool/database\";i:220;s:14:\"tool/error_log\";i:221;s:18:\"tool/export_import\";i:222;s:22:\"tool/export_import_raw\";i:223;s:13:\"tool/mail_log\";i:224;s:14:\"tool/quote_log\";i:225;s:20:\"tool/seo_url_manager\";i:226;s:12:\"tool/sitemap\";i:227;s:11:\"tool/upload\";i:228;s:12:\"total/coupon\";i:229;s:12:\"total/credit\";i:230;s:19:\"total/gift_wrapping\";i:231;s:14:\"total/handling\";i:232;s:16:\"total/klarna_fee\";i:233;s:19:\"total/low_order_fee\";i:234;s:12:\"total/offers\";i:235;s:16:\"total/paypal_fee\";i:236;s:12:\"total/reward\";i:237;s:14:\"total/shipping\";i:238;s:15:\"total/sub_total\";i:239;s:9:\"total/tax\";i:240;s:11:\"total/total\";i:241;s:13:\"total/voucher\";i:242;s:9:\"user/user\";i:243;s:13:\"user/user_log\";i:244;s:20:\"user/user_permission\";}s:6:\"modify\";a:245:{i:0;s:12:\"blog/article\";i:1;s:11:\"blog/author\";i:2;s:13:\"blog/category\";i:3;s:12:\"blog/comment\";i:4;s:12:\"blog/install\";i:5;s:11:\"blog/report\";i:6;s:17:\"catalog/attribute\";i:7;s:23:\"catalog/attribute_group\";i:8;s:16:\"catalog/category\";i:9;s:16:\"catalog/download\";i:10;s:13:\"catalog/field\";i:11;s:14:\"catalog/filter\";i:12;s:19:\"catalog/information\";i:13;s:20:\"catalog/manufacturer\";i:14;s:12:\"catalog/news\";i:15;s:21:\"catalog/news_download\";i:16;s:14:\"catalog/option\";i:17;s:15:\"catalog/palette\";i:18;s:15:\"catalog/product\";i:19;s:15:\"catalog/profile\";i:20;s:14:\"catalog/review\";i:21;s:18:\"common/filemanager\";i:22;s:23:\"common/filemanager_full\";i:23;s:11:\"common/home\";i:24;s:21:\"design/administration\";i:25;s:13:\"design/banner\";i:26;s:17:\"design/connection\";i:27;s:13:\"design/footer\";i:28;s:13:\"design/layout\";i:29;s:12:\"design/media\";i:30;s:11:\"design/menu\";i:31;s:17:\"design/menu_items\";i:32;s:14:\"design/payment\";i:33;s:14:\"extension/feed\";i:34;s:15:\"extension/fraud\";i:35;s:22:\"extension/modification\";i:36;s:16:\"extension/module\";i:37;s:17:\"extension/openbay\";i:38;s:17:\"extension/payment\";i:39;s:18:\"extension/shipping\";i:40;s:15:\"extension/theme\";i:41;s:15:\"extension/total\";i:42;s:16:\"feed/google_base\";i:43;s:19:\"feed/google_sitemap\";i:44;s:13:\"feed/rss_feed\";i:45;s:12:\"feed/storeya\";i:46;s:18:\"fraud/fraudlabspro\";i:47;s:13:\"fraud/maxmind\";i:48;s:20:\"localisation/country\";i:49;s:21:\"localisation/currency\";i:50;s:21:\"localisation/geo_zone\";i:51;s:21:\"localisation/language\";i:52;s:25:\"localisation/length_class\";i:53;s:21:\"localisation/location\";i:54;s:25:\"localisation/order_status\";i:55;s:26:\"localisation/return_action\";i:56;s:26:\"localisation/return_reason\";i:57;s:26:\"localisation/return_status\";i:58;s:25:\"localisation/stock_status\";i:59;s:22:\"localisation/tax_class\";i:60;s:27:\"localisation/tax_local_rate\";i:61;s:21:\"localisation/tax_rate\";i:62;s:25:\"localisation/weight_class\";i:63;s:17:\"localisation/zone\";i:64;s:24:\"modification/blog_system\";i:65;s:20:\"modification/eutaxes\";i:66;s:19:\"modification/vqmods\";i:67;s:14:\"module/account\";i:68;s:16:\"module/affiliate\";i:69;s:18:\"module/also_bought\";i:70;s:13:\"module/banner\";i:71;s:17:\"module/bestseller\";i:72;s:11:\"module/blog\";i:73;s:20:\"module/blog_category\";i:74;s:15:\"module/carousel\";i:75;s:15:\"module/category\";i:76;s:15:\"module/featured\";i:77;s:13:\"module/filter\";i:78;s:22:\"module/google_hangouts\";i:79;s:11:\"module/html\";i:80;s:18:\"module/information\";i:81;s:13:\"module/latest\";i:82;s:12:\"module/links\";i:83;s:15:\"module/location\";i:84;s:19:\"module/manufacturer\";i:85;s:18:\"module/mediaplayer\";i:86;s:22:\"module/menu_horizontal\";i:87;s:20:\"module/menu_vertical\";i:88;s:11:\"module/news\";i:89;s:14:\"module/palette\";i:90;s:13:\"module/paypal\";i:91;s:14:\"module/popular\";i:92;s:16:\"module/pp_layout\";i:93;s:12:\"module/skype\";i:94;s:16:\"module/slideshow\";i:95;s:14:\"module/special\";i:96;s:12:\"module/store\";i:97;s:15:\"module/tagcloud\";i:98;s:13:\"module/viewed\";i:99;s:14:\"module/welcome\";i:100;s:24:\"payment/authorizenet_aim\";i:101;s:24:\"payment/authorizenet_sim\";i:102;s:16:\"payment/bank_bni\";i:103;s:21:\"payment/bank_transfer\";i:104;s:16:\"payment/best2pay\";i:105;s:23:\"payment/best2pay_emoney\";i:106;s:22:\"payment/bluepay_hosted\";i:107;s:24:\"payment/bluepay_redirect\";i:108;s:15:\"payment/cardpay\";i:109;s:14:\"payment/cheque\";i:110;s:11:\"payment/cod\";i:111;s:12:\"payment/eway\";i:112;s:17:\"payment/firstdata\";i:113;s:24:\"payment/firstdata_remote\";i:114;s:21:\"payment/free_checkout\";i:115;s:14:\"payment/g2apay\";i:116;s:17:\"payment/globalpay\";i:117;s:24:\"payment/globalpay_remote\";i:118;s:16:\"payment/in_store\";i:119;s:22:\"payment/klarna_account\";i:120;s:22:\"payment/klarna_invoice\";i:121;s:14:\"payment/liqpay\";i:122;s:14:\"payment/nochex\";i:123;s:13:\"payment/okpay\";i:124;s:14:\"payment/payhub\";i:125;s:12:\"payment/paym\";i:126;s:15:\"payment/paymate\";i:127;s:16:\"payment/paypoint\";i:128;s:13:\"payment/payza\";i:129;s:26:\"payment/perpetual_payments\";i:130;s:18:\"payment/pp_express\";i:131;s:25:\"payment/pp_payflow_iframe\";i:132;s:14:\"payment/pp_pro\";i:133;s:21:\"payment/pp_pro_iframe\";i:134;s:17:\"payment/pp_pro_pf\";i:135;s:17:\"payment/pp_pro_uk\";i:136;s:19:\"payment/pp_standard\";i:137;s:15:\"payment/sagepay\";i:138;s:22:\"payment/sagepay_direct\";i:139;s:22:\"payment/sagepay_server\";i:140;s:18:\"payment/sagepay_us\";i:141;s:14:\"payment/skrill\";i:142;s:23:\"payment/stripe_payments\";i:143;s:19:\"payment/twocheckout\";i:144;s:28:\"payment/web_payment_software\";i:145;s:16:\"payment/worldpay\";i:146;s:23:\"payment/worldpay_online\";i:147;s:22:\"report/abandoned_carts\";i:148;s:25:\"report/affiliate_activity\";i:149;s:27:\"report/affiliate_commission\";i:150;s:21:\"report/banner_clicked\";i:151;s:23:\"report/customer_country\";i:152;s:22:\"report/customer_credit\";i:153;s:23:\"report/customer_deleted\";i:154;s:22:\"report/customer_online\";i:155;s:21:\"report/customer_order\";i:156;s:22:\"report/customer_reward\";i:157;s:24:\"report/customer_wishlist\";i:158;s:20:\"report/product_label\";i:159;s:21:\"report/product_markup\";i:160;s:24:\"report/product_purchased\";i:161;s:23:\"report/product_quantity\";i:162;s:21:\"report/product_viewed\";i:163;s:19:\"report/robot_online\";i:164;s:18:\"report/sale_coupon\";i:165;s:17:\"report/sale_order\";i:166;s:18:\"report/sale_profit\";i:167;s:18:\"report/sale_return\";i:168;s:20:\"report/sale_shipping\";i:169;s:15:\"report/sale_tax\";i:170;s:14:\"sale/affiliate\";i:171;s:12:\"sale/contact\";i:172;s:11:\"sale/coupon\";i:173;s:13:\"sale/customer\";i:174;s:20:\"sale/customer_ban_ip\";i:175;s:19:\"sale/customer_group\";i:176;s:10:\"sale/offer\";i:177;s:28:\"sale/offer_category_category\";i:178;s:27:\"sale/offer_category_product\";i:179;s:27:\"sale/offer_product_category\";i:180;s:26:\"sale/offer_product_product\";i:181;s:10:\"sale/order\";i:182;s:14:\"sale/recurring\";i:183;s:11:\"sale/return\";i:184;s:13:\"sale/supplier\";i:185;s:19:\"sale/supplier_group\";i:186;s:21:\"sale/supplier_product\";i:187;s:12:\"sale/voucher\";i:188;s:18:\"sale/voucher_theme\";i:189;s:15:\"setting/setting\";i:190;s:13:\"setting/store\";i:191;s:16:\"shipping/airmail\";i:192;s:16:\"shipping/auspost\";i:193;s:19:\"shipping/canadapost\";i:194;s:17:\"shipping/citylink\";i:195;s:12:\"shipping/dhl\";i:196;s:16:\"shipping/digital\";i:197;s:14:\"shipping/fedex\";i:198;s:15:\"shipping/flat_1\";i:199;s:15:\"shipping/flat_2\";i:200;s:15:\"shipping/flat_3\";i:201;s:13:\"shipping/free\";i:202;s:16:\"shipping/geozone\";i:203;s:13:\"shipping/item\";i:204;s:23:\"shipping/parcelforce_48\";i:205;s:15:\"shipping/pickup\";i:206;s:14:\"shipping/price\";i:207;s:19:\"shipping/royal_mail\";i:208;s:12:\"shipping/tnt\";i:209;s:12:\"shipping/ups\";i:210;s:13:\"shipping/usps\";i:211;s:15:\"shipping/weight\";i:212;s:13:\"theme/default\";i:213;s:20:\"tool/api_key_manager\";i:214;s:11:\"tool/backup\";i:215;s:13:\"tool/block_ip\";i:216;s:16:\"tool/cache_files\";i:217;s:17:\"tool/cache_images\";i:218;s:18:\"tool/configuration\";i:219;s:13:\"tool/database\";i:220;s:14:\"tool/error_log\";i:221;s:18:\"tool/export_import\";i:222;s:22:\"tool/export_import_raw\";i:223;s:13:\"tool/mail_log\";i:224;s:14:\"tool/quote_log\";i:225;s:20:\"tool/seo_url_manager\";i:226;s:12:\"tool/sitemap\";i:227;s:11:\"tool/upload\";i:228;s:12:\"total/coupon\";i:229;s:12:\"total/credit\";i:230;s:19:\"total/gift_wrapping\";i:231;s:14:\"total/handling\";i:232;s:16:\"total/klarna_fee\";i:233;s:19:\"total/low_order_fee\";i:234;s:12:\"total/offers\";i:235;s:16:\"total/paypal_fee\";i:236;s:12:\"total/reward\";i:237;s:14:\"total/shipping\";i:238;s:15:\"total/sub_total\";i:239;s:9:\"total/tax\";i:240;s:11:\"total/total\";i:241;s:13:\"total/voucher\";i:242;s:9:\"user/user\";i:243;s:13:\"user/user_log\";i:244;s:20:\"user/user_permission\";}}'),
(2, 'Demonstration', '');

-- --------------------------------------------------------

--
-- Table structure for table `nc_user_log`
--

DROP TABLE IF EXISTS `nc_user_log`;
CREATE TABLE `nc_user_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `action` varchar(50) NOT NULL,
  `allowed` tinyint(1) NOT NULL,
  `url` varchar(200) NOT NULL,
  `ip` varchar(32) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_version`
--

DROP TABLE IF EXISTS `nc_version`;
CREATE TABLE `nc_version` (
  `version_id` int(11) NOT NULL AUTO_INCREMENT,
  `version` varchar(20) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`version_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_voucher`
--

DROP TABLE IF EXISTS `nc_voucher`;
CREATE TABLE `nc_voucher` (
  `voucher_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `from_name` varchar(64) NOT NULL,
  `from_email` varchar(96) NOT NULL,
  `to_name` varchar(64) NOT NULL,
  `to_email` varchar(96) NOT NULL,
  `voucher_theme_id` int(11) NOT NULL,
  `message` text CHARACTER SET utf8 NOT NULL,
  `amount` decimal(15,4) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`voucher_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_voucher_history`
--

DROP TABLE IF EXISTS `nc_voucher_history`;
CREATE TABLE `nc_voucher_history` (
  `voucher_history_id` int(11) NOT NULL AUTO_INCREMENT,
  `voucher_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `amount` decimal(15,4) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`voucher_history_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nc_voucher_theme`
--

DROP TABLE IF EXISTS `nc_voucher_theme`;
CREATE TABLE `nc_voucher_theme` (
  `voucher_theme_id` int(11) NOT NULL AUTO_INCREMENT,
  `image` varchar(255) NOT NULL,
  PRIMARY KEY (`voucher_theme_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_voucher_theme`
--

INSERT INTO `nc_voucher_theme` (`voucher_theme_id`, `image`) VALUES
(7, 'data/demo/vouchers/gift-voucher-birthday.jpg'),
(6, 'data/demo/vouchers/gift-voucher-xmas.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `nc_voucher_theme_description`
--

DROP TABLE IF EXISTS `nc_voucher_theme_description`;
CREATE TABLE `nc_voucher_theme_description` (
  `voucher_theme_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`voucher_theme_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_voucher_theme_description`
--

INSERT INTO `nc_voucher_theme_description` (`voucher_theme_id`, `language_id`, `name`) VALUES
(6, 1, 'Christmas'),
(7, 1, 'Birthday');

-- --------------------------------------------------------

--
-- Table structure for table `nc_weight_class`
--

DROP TABLE IF EXISTS `nc_weight_class`;
CREATE TABLE `nc_weight_class` (
  `weight_class_id` int(11) NOT NULL AUTO_INCREMENT,
  `value` decimal(15,8) NOT NULL DEFAULT '0.00000000',
  PRIMARY KEY (`weight_class_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_weight_class`
--

INSERT INTO `nc_weight_class` (`weight_class_id`, `value`) VALUES
(1, '1.00000000'),
(2, '1000.00000000'),
(5, '2.20460000'),
(6, '35.27400000');

-- --------------------------------------------------------

--
-- Table structure for table `nc_weight_class_description`
--

DROP TABLE IF EXISTS `nc_weight_class_description`;
CREATE TABLE `nc_weight_class_description` (
  `weight_class_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `title` varchar(32) NOT NULL,
  `unit` varchar(4) NOT NULL,
  PRIMARY KEY (`weight_class_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_weight_class_description`
--

INSERT INTO `nc_weight_class_description` (`weight_class_id`, `language_id`, `title`, `unit`) VALUES
(1, 1, 'Kilogram', 'kg'),
(2, 1, 'Gram', 'g'),
(5, 1, 'Pound ', 'lb'),
(6, 1, 'Ounce', 'oz');

-- --------------------------------------------------------

--
-- Table structure for table `nc_zone`
--

DROP TABLE IF EXISTS `nc_zone`;
CREATE TABLE `nc_zone` (
  `zone_id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` int(11) NOT NULL,
  `code` varchar(32) NOT NULL,
  `name` varchar(128) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`zone_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_zone`
--

INSERT INTO `nc_zone` (`zone_id`, `country_id`, `code`, `name`, `status`) VALUES
(1, 1, 'BDS', 'Badakhshan', 1),
(2, 1, 'BDG', 'Badghis', 1),
(3, 1, 'BGL', 'Baghlan', 1),
(4, 1, 'BAL', 'Balkh', 1),
(5, 1, 'BAM', 'Bamian', 1),
(6, 1, 'FRA', 'Farah', 1),
(7, 1, 'FYB', 'Faryab', 1),
(8, 1, 'GHA', 'Ghazni', 1),
(9, 1, 'GHO', 'Ghowr', 1),
(10, 1, 'HEL', 'Helmand', 1),
(11, 1, 'HER', 'Herat', 1),
(12, 1, 'JOW', 'Jowzjan', 1),
(13, 1, 'KAB', 'Kabul', 1),
(14, 1, 'KAN', 'Kandahar', 1),
(15, 1, 'KAP', 'Kapisa', 1),
(16, 1, 'KHO', 'Khost', 1),
(17, 1, 'KNR', 'Konar', 1),
(18, 1, 'KDZ', 'Kondoz', 1),
(19, 1, 'LAG', 'Laghman', 1),
(20, 1, 'LOW', 'Lowgar', 1),
(21, 1, 'NAN', 'Nangrahar', 1),
(22, 1, 'NIM', 'Nimruz', 1),
(23, 1, 'NUR', 'Nurestan', 1),
(24, 1, 'ORU', 'Oruzgan', 1),
(25, 1, 'PIA', 'Paktia', 1),
(26, 1, 'PKA', 'Paktika', 1),
(27, 1, 'PAR', 'Parwan', 1),
(28, 1, 'SAM', 'Samangan', 1),
(29, 1, 'SAR', 'Sar-e Pol', 1),
(30, 1, 'TAK', 'Takhar', 1),
(31, 1, 'WAR', 'Wardak', 1),
(32, 1, 'ZAB', 'Zabol', 1),

(33, 2, 'BR', 'Berat', 1),
(34, 2, 'BU', 'Bulqize', 1),
(35, 2, 'DL', 'Delvine', 1),
(36, 2, 'DV', 'Devoll', 1),
(37, 2, 'DI', 'Diber', 1),
(38, 2, 'DR', 'Durres', 1),
(39, 2, 'EL', 'Elbasan', 1),
(40, 2, 'ER', 'Kolonje', 1),
(41, 2, 'FR', 'Fier', 1),
(42, 2, 'GJ', 'Gjirokast&euml;r', 1),
(43, 2, 'GR', 'Gramsh', 1),
(44, 2, 'HA', 'Has', 1),
(45, 2, 'KA', 'Kavaje', 1),
(46, 2, 'KB', 'Kurbin', 1),
(47, 2, 'KC', 'Kucove', 1),
(48, 2, 'KO', 'Korce', 1),
(49, 2, 'KR', 'Kruj&euml;', 1),
(50, 2, 'KU', 'Kukes', 1),
(51, 2, 'LB', 'Librazhd', 1),
(52, 2, 'LE', 'Lezhe', 1),
(53, 2, 'LU', 'Lushnje', 1),
(54, 2, 'MM', 'Malesi e Madhe', 1),
(55, 2, 'MK', 'Mallakast&euml;r', 1),
(56, 2, 'MT', 'Mat', 1),
(57, 2, 'MR', 'Mirdite', 1),
(58, 2, 'PQ', 'Peqin', 1),
(59, 2, 'PR', 'Permet', 1),
(60, 2, 'PG', 'Pogradec', 1),
(61, 2, 'PU', 'Puke', 1),
(62, 2, 'SH', 'Shkoder', 1),
(63, 2, 'SK', 'Skrapar', 1),
(64, 2, 'SR', 'Sarande', 1),
(65, 2, 'TE', 'Tepelene', 1),
(66, 2, 'TP', 'Tropoje', 1),
(67, 2, 'TR', 'Tiran&euml;', 1),
(68, 2, 'VL', 'Vlor&euml;', 1),

(69, 3, 'ADR', 'Adrar', 1),
(70, 3, 'ADE', 'Ain Defla', 1),
(71, 3, 'ATE', 'Ain Temouchent', 1),
(72, 3, 'ALG', 'Alger', 1),
(73, 3, 'ANN', 'Annaba', 1),
(74, 3, 'BAT', 'Batna', 1),
(75, 3, 'BEC', 'Bechar', 1),
(76, 3, 'BEJ', 'Bejaia', 1),
(77, 3, 'BIS', 'Biskra', 1),
(78, 3, 'BLI', 'Blida', 1),
(79, 3, 'BBA', 'Bordj Bou Arreridj', 1),
(80, 3, 'BOA', 'Bouira', 1),
(81, 3, 'BMD', 'Boumerdes', 1),
(82, 3, 'CHL', 'Chlef', 1),
(83, 3, 'CON', 'Constantine', 1),
(84, 3, 'DJE', 'Djelfa', 1),
(85, 3, 'EBA', 'El Bayadh', 1),
(86, 3, 'EOU', 'El Oued', 1),
(87, 3, 'ETA', 'El Tarf', 1),
(88, 3, 'GHA', 'Ghardaia', 1),
(89, 3, 'GUE', 'Guelma', 1),
(90, 3, 'ILL', 'Illizi', 1),
(91, 3, 'JIJ', 'Jijel', 1),
(92, 3, 'KHE', 'Khenchela', 1),
(93, 3, 'LAG', 'Laghouat', 1),
(94, 3, 'MUA', 'Muaskar', 1),
(95, 3, 'MED', 'Medea', 1),
(96, 3, 'MIL', 'Mila', 1),
(97, 3, 'MOS', 'Mostaganem', 1),
(98, 3, 'MSI', 'M''Sila', 1),
(99, 3, 'NAA', 'Naama', 1),
(100, 3, 'ORA', 'Oran', 1),
(101, 3, 'OUA', 'Ouargla', 1),
(102, 3, 'OEB', 'Oum el-Bouaghi', 1),
(103, 3, 'REL', 'Relizane', 1),
(104, 3, 'SAI', 'Saida', 1),
(105, 3, 'SET', 'Setif', 1),
(106, 3, 'SBA', 'Sidi Bel Abbes', 1),
(107, 3, 'SKI', 'Skikda', 1),
(108, 3, 'SAH', 'Souk Ahras', 1),
(109, 3, 'TAM', 'Tamanghasset', 1),
(110, 3, 'TEB', 'Tebessa', 1),
(111, 3, 'TIA', 'Tiaret', 1),
(112, 3, 'TIN', 'Tindouf', 1),
(113, 3, 'TIP', 'Tipaza', 1),
(114, 3, 'TIS', 'Tissemsilt', 1),
(115, 3, 'TOU', 'Tizi Ouzou', 1),
(116, 3, 'TLE', 'Tlemcen', 1),

(117, 4, 'E', 'Eastern', 1),
(118, 4, 'M', 'Manu''a', 1),
(119, 4, 'R', 'Rose Island', 1),
(120, 4, 'S', 'Swains Island', 1),
(121, 4, 'W', 'Western', 1),

(122, 5, 'ALV', 'Andorra la Vella', 1),
(123, 5, 'CAN', 'Canillo', 1),
(124, 5, 'ENC', 'Encamp', 1),
(125, 5, 'ESE', 'Escaldes-Engordany', 1),
(126, 5, 'LMA', 'La Massana', 1),
(127, 5, 'ORD', 'Ordino', 1),
(128, 5, 'SJL', 'Sant Julia de Loria', 1),

(129, 6, 'BGO', 'Bengo', 1),
(130, 6, 'BGU', 'Benguela', 1),
(131, 6, 'BIE', 'Bie', 1),
(132, 6, 'CAB', 'Cabinda', 1),
(133, 6, 'CCU', 'Cuando-Cubango', 1),
(134, 6, 'CNO', 'Cuanza Norte', 1),
(135, 6, 'CUS', 'Cuanza Sul', 1),
(136, 6, 'CNN', 'Cunene', 1),
(137, 6, 'HUA', 'Huambo', 1),
(138, 6, 'HUI', 'Huila', 1),
(139, 6, 'LUA', 'Luanda', 1),
(140, 6, 'LNO', 'Lunda Norte', 1),
(141, 6, 'LSU', 'Lunda Sul', 1),
(142, 6, 'MAL', 'Malange', 1),
(143, 6, 'MOX', 'Moxico', 1),
(144, 6, 'NAM', 'Namibe', 1),
(145, 6, 'UIG', 'Uige', 1),
(146, 6, 'ZAI', 'Zaire', 1),

(147, 9, 'ASG', 'Saint George', 1),
(148, 9, 'ASJ', 'Saint John', 1),
(149, 9, 'ASM', 'Saint Mary', 1),
(150, 9, 'ASL', 'Saint Paul', 1),
(151, 9, 'ASR', 'Saint Peter', 1),
(152, 9, 'ASH', 'Saint Philip', 1),
(153, 9, 'BAR', 'Barbuda', 1),
(154, 9, 'RED', 'Redonda', 1),

(155, 10, 'AN', 'Antartida e Islas del Atlantico', 1),
(156, 10, 'BA', 'Buenos Aires', 1),
(157, 10, 'CA', 'Catamarca', 1),
(158, 10, 'CH', 'Chaco', 1),
(159, 10, 'CU', 'Chubut', 1),
(160, 10, 'CO', 'Cordoba', 1),
(161, 10, 'CR', 'Corrientes', 1),
(162, 10, 'DF', 'Distrito Federal', 1),
(163, 10, 'ER', 'Entre Rios', 1),
(164, 10, 'FO', 'Formosa', 1),
(165, 10, 'JU', 'Jujuy', 1),
(166, 10, 'LP', 'La Pampa', 1),
(167, 10, 'LR', 'La Rioja', 1),
(168, 10, 'ME', 'Mendoza', 1),
(169, 10, 'MI', 'Misiones', 1),
(170, 10, 'NE', 'Neuquen', 1),
(171, 10, 'RN', 'Rio Negro', 1),
(172, 10, 'SA', 'Salta', 1),
(173, 10, 'SJ', 'San Juan', 1),
(174, 10, 'SL', 'San Luis', 1),
(175, 10, 'SC', 'Santa Cruz', 1),
(176, 10, 'SF', 'Santa Fe', 1),
(177, 10, 'SD', 'Santiago del Estero', 1),
(178, 10, 'TF', 'Tierra del Fuego', 1),
(179, 10, 'TU', 'Tucuman', 1),

(180, 11, 'AGT', 'Aragatsotn', 1),
(181, 11, 'ARR', 'Ararat', 1),
(182, 11, 'ARM', 'Armavir', 1),
(183, 11, 'GEG', 'Geghark''unik''', 1),
(184, 11, 'KOT', 'Kotayk''', 1),
(185, 11, 'LOR', 'Lorri', 1),
(186, 11, 'SHI', 'Shirak', 1),
(187, 11, 'SYU', 'Syunik''', 1),
(188, 11, 'TAV', 'Tavush', 1),
(189, 11, 'VAY', 'Vayots'' Dzor', 1),
(190, 11, 'YER', 'Yerevan', 1),

(191, 13, 'ACT', 'Australian Capital Territory', 1),
(192, 13, 'NSW', 'New South Wales', 1),
(193, 13, 'NT', 'Northern Territory', 1),
(194, 13, 'QLD', 'Queensland', 1),
(195, 13, 'SA', 'South Australia', 1),
(196, 13, 'TAS', 'Tasmania', 1),
(197, 13, 'VIC', 'Victoria', 1),
(198, 13, 'WA', 'Western Australia', 1),

(199, 14, 'BUR', 'Burgenland', 1),
(200, 14, 'KAR', 'K&auml;rnten', 1),
(201, 14, 'NOS', 'Nieder&ouml;sterreich', 1),
(202, 14, 'OOS', 'Ober&ouml;sterreich', 1),
(203, 14, 'SAL', 'Salzburg', 1),
(204, 14, 'STE', 'Steiermark', 1),
(205, 14, 'TIR', 'Tirol', 1),
(206, 14, 'VOR', 'Vorarlberg', 1),
(207, 14, 'WIE', 'Wien', 1),

(208, 15, 'AB', 'Ali Bayramli', 1),
(209, 15, 'ABS', 'Abseron', 1),
(210, 15, 'AGC', 'AgcabAdi', 1),
(211, 15, 'AGM', 'Agdam', 1),
(212, 15, 'AGS', 'Agdas', 1),
(213, 15, 'AGA', 'Agstafa', 1),
(214, 15, 'AGU', 'Agsu', 1),
(215, 15, 'AST', 'Astara', 1),
(216, 15, 'BA', 'Baki', 1),
(217, 15, 'BAB', 'BabAk', 1),
(218, 15, 'BAL', 'BalakAn', 1),
(219, 15, 'BAR', 'BArdA', 1),
(220, 15, 'BEY', 'Beylaqan', 1),
(221, 15, 'BIL', 'Bilasuvar', 1),
(222, 15, 'CAB', 'Cabrayil', 1),
(223, 15, 'CAL', 'Calilabab', 1),
(224, 15, 'CUL', 'Culfa', 1),
(225, 15, 'DAS', 'Daskasan', 1),
(226, 15, 'DAV', 'Davaci', 1),
(227, 15, 'FUZ', 'Fuzuli', 1),
(228, 15, 'GA', 'Ganca', 1),
(229, 15, 'GAD', 'Gadabay', 1),
(230, 15, 'GOR', 'Goranboy', 1),
(231, 15, 'GOY', 'Goycay', 1),
(232, 15, 'HAC', 'Haciqabul', 1),
(233, 15, 'IMI', 'Imisli', 1),
(234, 15, 'ISM', 'Ismayilli', 1),
(235, 15, 'KAL', 'Kalbacar', 1),
(236, 15, 'KUR', 'Kurdamir', 1),
(237, 15, 'LA', 'Lankaran', 1),
(238, 15, 'LAC', 'Lacin', 1),
(239, 15, 'LAN', 'Lankaran', 1),
(240, 15, 'LER', 'Lerik', 1),
(241, 15, 'MAS', 'Masalli', 1),
(242, 15, 'MI', 'Mingacevir', 1),
(243, 15, 'NA', 'Naftalan', 1),
(244, 15, 'NEF', 'Neftcala', 1),
(245, 15, 'OGU', 'Oguz', 1),
(246, 15, 'ORD', 'Ordubad', 1),
(247, 15, 'QAB', 'Qabala', 1),
(248, 15, 'QAX', 'Qax', 1),
(249, 15, 'QAZ', 'Qazax', 1),
(250, 15, 'QOB', 'Qobustan', 1),
(251, 15, 'QBA', 'Quba', 1),
(252, 15, 'QBI', 'Qubadli', 1),
(253, 15, 'QUS', 'Qusar', 1),
(254, 15, 'SA', 'Saki', 1),
(255, 15, 'SAT', 'Saatli', 1),
(256, 15, 'SAB', 'Sabirabad', 1),
(257, 15, 'SAD', 'Sadarak', 1),
(258, 15, 'SAH', 'Sahbuz', 1),
(259, 15, 'SAK', 'Saki', 1),
(260, 15, 'SAL', 'Salyan', 1),
(261, 15, 'SM', 'Sumqayit', 1),
(262, 15, 'SMI', 'Samaxi', 1),
(263, 15, 'SKR', 'Samkir', 1),
(264, 15, 'SMX', 'Samux', 1),
(265, 15, 'SAR', 'Sarur', 1),
(266, 15, 'SIY', 'Siyazan', 1),
(267, 15, 'SS', 'Susa', 1),
(268, 15, 'SUS', 'Susa', 1),
(269, 15, 'TAR', 'Tartar', 1),
(270, 15, 'TOV', 'Tovuz', 1),
(271, 15, 'UCA', 'Ucar', 1),
(272, 15, 'XA', 'Xankandi', 1),
(273, 15, 'XAC', 'Xacmaz', 1),
(274, 15, 'XAN', 'Xanlar', 1),
(275, 15, 'XIZ', 'Xizi', 1),
(276, 15, 'XCI', 'Xocali', 1),
(277, 15, 'XVD', 'Xocavand', 1),
(278, 15, 'YAR', 'Yardimli', 1),
(279, 15, 'YEV', 'Yevlax', 1),
(280, 15, 'ZAN', 'Zangilan', 1),
(281, 15, 'ZAQ', 'Zaqatala', 1),
(282, 15, 'ZAR', 'Zardab', 1),
(283, 15, 'NX', 'Naxcivan', 1),

(284, 16, 'ACK', 'Acklins', 1),
(285, 16, 'BER', 'Berry Islands', 1),
(286, 16, 'BIM', 'Bimini', 1),
(287, 16, 'BLK', 'Black Point', 1),
(288, 16, 'CAT', 'Cat Island', 1),
(289, 16, 'CAB', 'Central Abaco', 1),
(290, 16, 'CAN', 'Central Andros', 1),
(291, 16, 'CEL', 'Central Eleuthera', 1),
(292, 16, 'FRE', 'City of Freeport', 1),
(293, 16, 'CRO', 'Crooked Island', 1),
(294, 16, 'EGB', 'East Grand Bahama', 1),
(295, 16, 'EXU', 'Exuma', 1),
(296, 16, 'GRD', 'Grand Cay', 1),
(297, 16, 'HAR', 'Harbour Island', 1),
(298, 16, 'HOP', 'Hope Town', 1),
(299, 16, 'INA', 'Inagua', 1),
(300, 16, 'LNG', 'Long Island', 1),
(301, 16, 'MAN', 'Mangrove Cay', 1),
(302, 16, 'MAY', 'Mayaguana', 1),
(303, 16, 'MOO', 'Moore''s Island', 1),
(304, 16, 'NAB', 'North Abaco', 1),
(305, 16, 'NAN', 'North Andros', 1),
(306, 16, 'NEL', 'North Eleuthera', 1),
(307, 16, 'RAG', 'Ragged Island', 1),
(308, 16, 'RUM', 'Rum Cay', 1),
(309, 16, 'SAL', 'San Salvador', 1),
(310, 16, 'SAB', 'South Abaco', 1),
(311, 16, 'SAN', 'South Andros', 1),
(312, 16, 'SEL', 'South Eleuthera', 1),
(313, 16, 'SWE', 'Spanish Wells', 1),
(314, 16, 'WGB', 'West Grand Bahama', 1),

(315, 17, 'CAP', 'Capital', 1),
(316, 17, 'CEN', 'Central', 1),
(317, 17, 'MUH', 'Muharraq', 1),
(318, 17, 'NOR', 'Northern', 1),
(319, 17, 'SOU', 'Southern', 1),

(320, 18, 'BAR', 'Barisal', 1),
(321, 18, 'CHI', 'Chittagong', 1),
(322, 18, 'DHA', 'Dhaka', 1),
(323, 18, 'KHU', 'Khulna', 1),
(324, 18, 'RAJ', 'Rajshahi', 1),
(325, 18, 'SYL', 'Sylhet', 1),

(326, 19, 'CC', 'Christ Church', 1),
(327, 19, 'AND', 'Saint Andrew', 1),
(328, 19, 'GEO', 'Saint George', 1),
(329, 19, 'JAM', 'Saint James', 1),
(330, 19, 'JOH', 'Saint John', 1),
(331, 19, 'JOS', 'Saint Joseph', 1),
(332, 19, 'LUC', 'Saint Lucy', 1),
(333, 19, 'MIC', 'Saint Michael', 1),
(334, 19, 'PET', 'Saint Peter', 1),
(335, 19, 'PHI', 'Saint Philip', 1),
(336, 19, 'THO', 'Saint Thomas', 1),

(337, 20, 'BR', 'Brestskaya (Brest)', 1),
(338, 20, 'HO', 'Homyel''skaya (Homyel'')', 1),
(339, 20, 'HM', 'Horad Minsk', 1),
(340, 20, 'HR', 'Hrodzyenskaya (Hrodna)', 1),
(341, 20, 'MA', 'Mahilyowskaya (Mahilyow)', 1),
(342, 20, 'MI', 'Minskaya', 1),
(343, 20, 'VI', 'Vitsyebskaya (Vitsyebsk)', 1),

(344, 21, 'VAN', 'Antwerpen', 1),
(345, 21, 'WBR', 'Brabant Wallon', 1),
(346, 21, 'WHT', 'Hainaut', 1),
(347, 21, 'WLG', 'Li&egrave;ge', 1),
(348, 21, 'VLI', 'Limburg', 1),
(349, 21, 'WLX', 'Luxembourg', 1),
(350, 21, 'WNA', 'Namur', 1),
(351, 21, 'VOV', 'Oost-Vlaanderen', 1),
(352, 21, 'VBR', 'Vlaams Brabant', 1),
(353, 21, 'VWV', 'West-Vlaanderen', 1),

(354, 22, 'BZ', 'Belize', 1),
(355, 22, 'CY', 'Cayo', 1),
(356, 22, 'CR', 'Corozal', 1),
(357, 22, 'OW', 'Orange Walk', 1),
(358, 22, 'SC', 'Stann Creek', 1),
(359, 22, 'TO', 'Toledo', 1),

(360, 23, 'AL', 'Alibori', 1),
(361, 23, 'AK', 'Atakora', 1),
(362, 23, 'AQ', 'Atlantique', 1),
(363, 23, 'BO', 'Borgou', 1),
(364, 23, 'CO', 'Collines', 1),
(365, 23, 'DO', 'Donga', 1),
(366, 23, 'KO', 'Kouffo', 1),
(367, 23, 'LI', 'Littoral', 1),
(368, 23, 'MO', 'Mono', 1),
(369, 23, 'OU', 'Ou&eacute;m&eacute;', 1),
(370, 23, 'PL', 'Plateau', 1),
(371, 23, 'ZO', 'Zou', 1),

(372, 24, 'DS', 'Devonshire', 1),
(373, 24, 'HC', 'Hamilton City', 1),
(374, 24, 'HA', 'Hamilton', 1),
(375, 24, 'PG', 'Paget', 1),
(376, 24, 'PB', 'Pembroke', 1),
(377, 24, 'GC', 'Saint George City', 1),
(378, 24, 'SG', 'Saint George''s', 1),
(379, 24, 'SA', 'Sandys', 1),
(380, 24, 'SM', 'Smith''s', 1),
(381, 24, 'SH', 'Southampton', 1),
(382, 24, 'WA', 'Warwick', 1),

(383, 25, 'BUM', 'Bumthang', 1),
(384, 25, 'CHU', 'Chukha', 1),
(385, 25, 'DAG', 'Dagana', 1),
(386, 25, 'GAS', 'Gasa', 1),
(387, 25, 'HAA', 'Haa', 1),
(388, 25, 'LHU', 'Lhuntse', 1),
(389, 25, 'MON', 'Mongar', 1),
(390, 25, 'PAR', 'Paro', 1),
(391, 25, 'PEM', 'Pemagatshel', 1),
(392, 25, 'PUN', 'Punakha', 1),
(393, 25, 'SJO', 'Samdrup Jongkhar', 1),
(394, 25, 'SAT', 'Samtse', 1),
(395, 25, 'SAR', 'Sarpang', 1),
(396, 25, 'THI', 'Thimphu', 1),
(397, 25, 'TRG', 'Trashigang', 1),
(398, 25, 'TRY', 'Trashiyangste', 1),
(399, 25, 'TRO', 'Trongsa', 1),
(400, 25, 'TSI', 'Tsirang', 1),
(401, 25, 'WPH', 'Wangdue Phodrang', 1),
(402, 25, 'ZHE', 'Zhemgang', 1),

(403, 26, 'BEN', 'Beni', 1),
(404, 26, 'CHU', 'Chuquisaca', 1),
(405, 26, 'COC', 'Cochabamba', 1),
(406, 26, 'LPZ', 'La Paz', 1),
(407, 26, 'ORU', 'Oruro', 1),
(408, 26, 'PAN', 'Pando', 1),
(409, 26, 'POT', 'Potos&iacute;', 1),
(410, 26, 'SCZ', 'Santa Cruz', 1),
(411, 26, 'TAR', 'Tarija', 1),

(412, 27, 'BRO', 'Brcko district', 1),
(413, 27, 'FUS', 'Unsko-Sanski Kanton', 1),
(414, 27, 'FPO', 'Posavski Kanton', 1),
(415, 27, 'FTU', 'Tuzlanski Kanton', 1),
(416, 27, 'FZE', 'Zenicko-Dobojski Kanton', 1),
(417, 27, 'FBP', 'Bosanskopodrinjski Kanton', 1),
(418, 27, 'FSB', 'Srednjebosanski Kanton', 1),
(419, 27, 'FHN', 'Hercegovacko-neretvanski Kanton', 1),
(420, 27, 'FZH', 'Zapadnohercegovacka Zupanija', 1),
(421, 27, 'FSA', 'Kanton Sarajevo', 1),
(422, 27, 'FZA', 'Zapadnobosanska', 1),
(423, 27, 'SBL', 'Banja Luka', 1),
(424, 27, 'SDO', 'Doboj', 1),
(425, 27, 'SBI', 'Bijeljina', 1),
(426, 27, 'SVL', 'Vlasenica', 1),
(427, 27, 'SSR', 'Sarajevo-Romanija or Sokolac', 1),
(428, 27, 'SFO', 'Foca', 1),
(429, 27, 'STR', 'Trebinje', 1),

(430, 28, 'CE', 'Central', 1),
(431, 28, 'GH', 'Ghanzi', 1),
(432, 28, 'KD', 'Kgalagadi', 1),
(433, 28, 'KT', 'Kgatleng', 1),
(434, 28, 'KW', 'Kweneng', 1),
(435, 28, 'NG', 'Ngamiland', 1),
(436, 28, 'NE', 'North East', 1),
(437, 28, 'NW', 'North West', 1),
(438, 28, 'SE', 'South East', 1),
(439, 28, 'SO', 'Southern', 1),

(440, 30, 'AC', 'Acre', 1),
(441, 30, 'AL', 'Alagoas', 1),
(442, 30, 'AP', 'Amap&aacute;', 1),
(443, 30, 'AM', 'Amazonas', 1),
(444, 30, 'BA', 'Bah&iacute;a', 1),
(445, 30, 'CE', 'Cear&aacute;', 1),
(446, 30, 'DF', 'Distrito Federal', 1),
(447, 30, 'ES', 'Esp&iacute;rito Santo', 1),
(448, 30, 'GO', 'Goi&aacute;s', 1),
(449, 30, 'MA', 'Maranh&atilde;o', 1),
(450, 30, 'MT', 'Mato Grosso', 1),
(451, 30, 'MS', 'Mato Grosso do Sul', 1),
(452, 30, 'MG', 'Minas Gera&iacute;s', 1),
(453, 30, 'PA', 'Par&aacute;', 1),
(454, 30, 'PB', 'Para&iacute;ba', 1),
(455, 30, 'PR', 'Paran&aacute;', 1),
(456, 30, 'PE', 'Pernambuco', 1),
(457, 30, 'PI', 'Piau&iacute;', 1),
(458, 30, 'RJ', 'Rio de Janeiro', 1),
(459, 30, 'RN', 'Rio Grande do Norte', 1),
(460, 30, 'RS', 'Rio Grande do Sul', 1),
(461, 30, 'RO', 'Rond&ocirc;nia', 1),
(462, 30, 'RR', 'Roraima', 1),
(463, 30, 'SC', 'Santa Catarina', 1),
(464, 30, 'SP', 'S&atilde;o Paulo', 1),
(465, 30, 'SE', 'Sergipe', 1),
(466, 30, 'TO', 'Tocantins', 1),

(467, 31, 'PB', 'Peros Banhos', 1),
(468, 31, 'SI', 'Salomon Islands', 1),
(469, 31, 'NI', 'Nelsons Island', 1),
(470, 31, 'TB', 'Three Brothers', 1),
(471, 31, 'EA', 'Eagle Islands', 1),
(472, 31, 'DI', 'Danger Island', 1),
(473, 31, 'EG', 'Egmont Islands', 1),
(474, 31, 'DG', 'Diego Garcia', 1),

(475, 32, 'BEL', 'Belait', 1),
(476, 32, 'BRM', 'Brunei and Muara', 1),
(477, 32, 'TEM', 'Temburong', 1),
(478, 32, 'TUT', 'Tutong', 1),

(479, 33, '', 'Blagoevgrad', 1),
(480, 33, '', 'Burgas', 1),
(481, 33, '', 'Dobrich', 1),
(482, 33, '', 'Gabrovo', 1),
(483, 33, '', 'Haskovo', 1),
(484, 33, '', 'Kardjali', 1),
(485, 33, '', 'Kyustendil', 1),
(486, 33, '', 'Lovech', 1),
(487, 33, '', 'Montana', 1),
(488, 33, '', 'Pazardjik', 1),
(489, 33, '', 'Pernik', 1),
(490, 33, '', 'Pleven', 1),
(491, 33, '', 'Plovdiv', 1),
(492, 33, '', 'Razgrad', 1),
(493, 33, '', 'Shumen', 1),
(494, 33, '', 'Silistra', 1),
(495, 33, '', 'Sliven', 1),
(496, 33, '', 'Smolyan', 1),
(497, 33, '', 'Sofia', 1),
(498, 33, '', 'Sofia - town', 1),
(499, 33, '', 'Stara Zagora', 1),
(500, 33, '', 'Targovishte', 1),
(501, 33, '', 'Varna', 1),
(502, 33, '', 'Veliko Tarnovo', 1),
(503, 33, '', 'Vidin', 1),
(504, 33, '', 'Vratza', 1),
(505, 33, '', 'Yambol', 1),

(506, 34, 'BAL', 'Bale', 1),
(507, 34, 'BAM', 'Bam', 1),
(508, 34, 'BAN', 'Banwa', 1),
(509, 34, 'BAZ', 'Bazega', 1),
(510, 34, 'BOR', 'Bougouriba', 1),
(511, 34, 'BLG', 'Boulgou', 1),
(512, 34, 'BOK', 'Boulkiemde', 1),
(513, 34, 'COM', 'Comoe', 1),
(514, 34, 'GAN', 'Ganzourgou', 1),
(515, 34, 'GNA', 'Gnagna', 1),
(516, 34, 'GOU', 'Gourma', 1),
(517, 34, 'HOU', 'Houet', 1),
(518, 34, 'IOA', 'Ioba', 1),
(519, 34, 'KAD', 'Kadiogo', 1),
(520, 34, 'KEN', 'Kenedougou', 1),
(521, 34, 'KOD', 'Komondjari', 1),
(522, 34, 'KOP', 'Kompienga', 1),
(523, 34, 'KOS', 'Kossi', 1),
(524, 34, 'KOL', 'Koulpelogo', 1),
(525, 34, 'KOT', 'Kouritenga', 1),
(526, 34, 'KOW', 'Kourweogo', 1),
(527, 34, 'LER', 'Leraba', 1),
(528, 34, 'LOR', 'Loroum', 1),
(529, 34, 'MOU', 'Mouhoun', 1),
(530, 34, 'NAH', 'Nahouri', 1),
(531, 34, 'NAM', 'Namentenga', 1),
(532, 34, 'NAY', 'Nayala', 1),
(533, 34, 'NOU', 'Noumbiel', 1),
(534, 34, 'OUB', 'Oubritenga', 1),
(535, 34, 'OUD', 'Oudalan', 1),
(536, 34, 'PAS', 'Passore', 1),
(537, 34, 'PON', 'Poni', 1),
(538, 34, 'SAG', 'Sanguie', 1),
(539, 34, 'SAM', 'Sanmatenga', 1),
(540, 34, 'SEN', 'Seno', 1),
(541, 34, 'SIS', 'Sissili', 1),
(542, 34, 'SOM', 'Soum', 1),
(543, 34, 'SOR', 'Sourou', 1),
(544, 34, 'TAP', 'Tapoa', 1),
(545, 34, 'TUY', 'Tuy', 1),
(546, 34, 'YAG', 'Yagha', 1),
(547, 34, 'YAT', 'Yatenga', 1),
(548, 34, 'ZIR', 'Ziro', 1),
(549, 34, 'ZOD', 'Zondoma', 1),
(550, 34, 'ZOW', 'Zoundweogo', 1),

(551, 35, 'BB', 'Bubanza', 1),
(552, 35, 'BJ', 'Bujumbura', 1),
(553, 35, 'BR', 'Bururi', 1),
(554, 35, 'CA', 'Cankuzo', 1),
(555, 35, 'CI', 'Cibitoke', 1),
(556, 35, 'GI', 'Gitega', 1),
(557, 35, 'KR', 'Karuzi', 1),
(558, 35, 'KY', 'Kayanza', 1),
(559, 35, 'KI', 'Kirundo', 1),
(560, 35, 'MA', 'Makamba', 1),
(561, 35, 'MU', 'Muramvya', 1),
(562, 35, 'MY', 'Muyinga', 1),
(563, 35, 'MW', 'Mwaro', 1),
(564, 35, 'NG', 'Ngozi', 1),
(565, 35, 'RT', 'Rutana', 1),
(566, 35, 'RY', 'Ruyigi', 1),

(567, 36, 'PP', 'Phnom Penh', 1),
(568, 36, 'PS', 'Preah Seihanu (Kompong Som or Sihanoukville)', 1),
(569, 36, 'PA', 'Pailin', 1),
(570, 36, 'KB', 'Keb', 1),
(571, 36, 'BM', 'Banteay Meanchey', 1),
(572, 36, 'BA', 'Battambang', 1),
(573, 36, 'KM', 'Kampong Cham', 1),
(574, 36, 'KN', 'Kampong Chhnang', 1),
(575, 36, 'KU', 'Kampong Speu', 1),
(576, 36, 'KO', 'Kampong Som', 1),
(577, 36, 'KT', 'Kampong Thom', 1),
(578, 36, 'KP', 'Kampot', 1),
(579, 36, 'KL', 'Kandal', 1),
(580, 36, 'KK', 'Kaoh Kong', 1),
(581, 36, 'KR', 'Kratie', 1),
(582, 36, 'MK', 'Mondul Kiri', 1),
(583, 36, 'OM', 'Oddar Meancheay', 1),
(584, 36, 'PU', 'Pursat', 1),
(585, 36, 'PR', 'Preah Vihear', 1),
(586, 36, 'PG', 'Prey Veng', 1),
(587, 36, 'RK', 'Ratanak Kiri', 1),
(588, 36, 'SI', 'Siemreap', 1),
(589, 36, 'ST', 'Stung Treng', 1),
(590, 36, 'SR', 'Svay Rieng', 1),
(591, 36, 'TK', 'Takeo', 1),

(592, 37, 'ADA', 'Adamawa (Adamaoua)', 1),
(593, 37, 'CEN', 'Centre', 1),
(594, 37, 'EST', 'East (Est)', 1),
(595, 37, 'EXN', 'Extreme North (Extreme-Nord)', 1),
(596, 37, 'LIT', 'Littoral', 1),
(597, 37, 'NOR', 'North (Nord)', 1),
(598, 37, 'NOT', 'Northwest (Nord-Ouest)', 1),
(599, 37, 'OUE', 'West (Ouest)', 1),
(600, 37, 'SUD', 'South (Sud)', 1),
(601, 37, 'SOU', 'Southwest (Sud-Ouest)', 1),

(602, 38, 'AB', 'Alberta', 1),
(603, 38, 'BC', 'British Columbia', 1),
(604, 38, 'MB', 'Manitoba', 1),
(605, 38, 'NB', 'New Brunswick', 1),
(606, 38, 'NL', 'Newfoundland and Labrador', 1),
(607, 38, 'NT', 'Northwest Territories', 1),
(608, 38, 'NS', 'Nova Scotia', 1),
(609, 38, 'NU', 'Nunavut', 1),
(610, 38, 'ON', 'Ontario', 1),
(611, 38, 'PE', 'Prince Edward Island', 1),
(612, 38, 'QC', 'Qu&eacute;bec', 1),
(613, 38, 'SK', 'Saskatchewan', 1),
(614, 38, 'YT', 'Yukon Territory', 1),

(615, 39, 'BV', 'Boa Vista', 1),
(616, 39, 'BR', 'Brava', 1),
(617, 39, 'CS', 'Calheta de Sao Miguel', 1),
(618, 39, 'MA', 'Maio', 1),
(619, 39, 'MO', 'Mosteiros', 1),
(620, 39, 'PA', 'Paul', 1),
(621, 39, 'PN', 'Porto Novo', 1),
(622, 39, 'PR', 'Praia', 1),
(623, 39, 'RG', 'Ribeira Grande', 1),
(624, 39, 'SL', 'Sal', 1),
(625, 39, 'CA', 'Santa Catarina', 1),
(626, 39, 'CR', 'Santa Cruz', 1),
(627, 39, 'SD', 'Sao Domingos', 1),
(628, 39, 'SF', 'Sao Filipe', 1),
(629, 39, 'SN', 'Sao Nicolau', 1),
(630, 39, 'SV', 'Sao Vicente', 1),
(631, 39, 'TA', 'Tarrafal', 1),

(632, 40, 'CR', 'Creek', 1),
(633, 40, 'EA', 'Eastern', 1),
(634, 40, 'ML', 'Midland', 1),
(635, 40, 'ST', 'South Town', 1),
(636, 40, 'SP', 'Spot Bay', 1),
(637, 40, 'SK', 'Stake Bay', 1),
(638, 40, 'WD', 'West End', 1),
(639, 40, 'WN', 'Western', 1),

(640, 41, 'BBA', 'Bamingui-Bangoran', 1),
(641, 41, 'BKO', 'Basse-Kotto', 1),
(642, 41, 'HKO', 'Haute-Kotto', 1),
(643, 41, 'HMB', 'Haut-Mbomou', 1),
(644, 41, 'KEM', 'Kemo', 1),
(645, 41, 'LOB', 'Lobaye', 1),
(646, 41, 'MKD', 'Mambere-Kade&ocirc;', 1),
(647, 41, 'MBO', 'Mbomou', 1),
(648, 41, 'NMM', 'Nana-Mambere', 1),
(649, 41, 'OMP', 'Ombella-M''Poko', 1),
(650, 41, 'OUK', 'Ouaka', 1),
(651, 41, 'OUH', 'Ouham', 1),
(652, 41, 'OPE', 'Ouham-Pende', 1),
(653, 41, 'VAK', 'Vakaga', 1),
(654, 41, 'NGR', 'Nana-Grebizi', 1),
(655, 41, 'SMB', 'Sangha-Mbaere', 1),
(656, 41, 'BAN', 'Bangui', 1),

(657, 42, 'BA', 'Batha', 1),
(658, 42, 'BI', 'Biltine', 1),
(659, 42, 'BE', 'Borkou-Ennedi-Tibesti', 1),
(660, 42, 'CB', 'Chari-Baguirmi', 1),
(661, 42, 'GU', 'Guera', 1),
(662, 42, 'KA', 'Kanem', 1),
(663, 42, 'LA', 'Lac', 1),
(664, 42, 'LC', 'Logone Occidental', 1),
(665, 42, 'LR', 'Logone Oriental', 1),
(666, 42, 'MK', 'Mayo-Kebbi', 1),
(667, 42, 'MC', 'Moyen-Chari', 1),
(668, 42, 'OU', 'Ouaddai', 1),
(669, 42, 'SA', 'Salamat', 1),
(670, 42, 'TA', 'Tandjile', 1),

(671, 43, 'AI', 'Aisen del General Carlos Ibanez', 1),
(672, 43, 'AN', 'Antofagasta', 1),
(673, 43, 'AR', 'Arauca&ntilde;a', 1),
(674, 43, 'AT', 'Atacama', 1),
(675, 43, 'BI', 'Bio-Bio', 1),
(676, 43, 'CO', 'Coquimbo', 1),
(677, 43, 'LI', 'Libertador General Bernardo O''Hi', 1),
(678, 43, 'LL', 'Los Lagos', 1),
(679, 43, 'MA', 'Magallanes y de la Antartica Chi', 1),
(680, 43, 'ML', 'Maule', 1),
(681, 43, 'RM', 'Region Metropolitana', 1),
(682, 43, 'TA', 'Tarapaca', 1),
(683, 43, 'VS', 'Valpara&iacute;so', 1),

(684, 44, 'AN', 'Anhui', 1),
(685, 44, 'BE', 'Beijing', 1),
(686, 44, 'CH', 'Chongqing', 1),
(687, 44, 'FU', 'Fujian', 1),
(688, 44, 'GA', 'Gansu', 1),
(689, 44, 'GU', 'Guangdong', 1),
(690, 44, 'GX', 'Guangxi', 1),
(691, 44, 'GZ', 'Guizhou', 1),
(692, 44, 'HA', 'Hainan', 1),
(693, 44, 'HB', 'Hebei', 1),
(694, 44, 'HL', 'Heilongjiang', 1),
(695, 44, 'HE', 'Henan', 1),
(696, 44, 'HK', 'Hong Kong', 1),
(697, 44, 'HU', 'Hubei', 1),
(698, 44, 'HN', 'Hunan', 1),
(699, 44, 'IM', 'Inner Mongolia', 1),
(700, 44, 'JI', 'Jiangsu', 1),
(701, 44, 'JX', 'Jiangxi', 1),
(702, 44, 'JL', 'Jilin', 1),
(703, 44, 'LI', 'Liaoning', 1),
(704, 44, 'MA', 'Macau', 1),
(705, 44, 'NI', 'Ningxia', 1),
(706, 44, 'SH', 'Shaanxi', 1),
(707, 44, 'SA', 'Shandong', 1),
(708, 44, 'SG', 'Shanghai', 1),
(709, 44, 'SX', 'Shanxi', 1),
(710, 44, 'SI', 'Sichuan', 1),
(711, 44, 'TI', 'Tianjin', 1),
(712, 44, 'XI', 'Xinjiang', 1),
(713, 44, 'YU', 'Yunnan', 1),
(714, 44, 'ZH', 'Zhejiang', 1),

(715, 46, 'D', 'Direction Island', 1),
(716, 46, 'H', 'Home Island', 1),
(717, 46, 'O', 'Horsburgh Island', 1),
(718, 46, 'S', 'South Island', 1),
(719, 46, 'W', 'West Island', 1),

(720, 47, 'AMZ', 'Amazonas', 1),
(721, 47, 'ANT', 'Antioquia', 1),
(722, 47, 'ARA', 'Arauca', 1),
(723, 47, 'ATL', 'Atlantico', 1),
(724, 47, 'BDC', 'Bogota D.C.', 1),
(725, 47, 'BOL', 'Bolivar', 1),
(726, 47, 'BOY', 'Boyaca', 1),
(727, 47, 'CAL', 'Caldas', 1),
(728, 47, 'CAQ', 'Caqueta', 1),
(729, 47, 'CAS', 'Casanare', 1),
(730, 47, 'CAU', 'Cauca', 1),
(731, 47, 'CES', 'Cesar', 1),
(732, 47, 'CHO', 'Choco', 1),
(733, 47, 'COR', 'Cordoba', 1),
(734, 47, 'CAM', 'Cundinamarca', 1),
(735, 47, 'GNA', 'Guainia', 1),
(736, 47, 'GJR', 'Guajira', 1),
(737, 47, 'GVR', 'Guaviare', 1),
(738, 47, 'HUI', 'Huila', 1),
(739, 47, 'MAG', 'Magdalena', 1),
(740, 47, 'MET', 'Meta', 1),
(741, 47, 'NAR', 'Narino', 1),
(742, 47, 'NDS', 'Norte de Santander', 1),
(743, 47, 'PUT', 'Putumayo', 1),
(744, 47, 'QUI', 'Quindio', 1),
(745, 47, 'RIS', 'Risaralda', 1),
(746, 47, 'SAP', 'San Andres y Providencia', 1),
(747, 47, 'SAN', 'Santander', 1),
(748, 47, 'SUC', 'Sucre', 1),
(749, 47, 'TOL', 'Tolima', 1),
(750, 47, 'VDC', 'Valle del Cauca', 1),
(751, 47, 'VAU', 'Vaupes', 1),
(752, 47, 'VIC', 'Vichada', 1),

(753, 48, 'G', 'Grande Comore', 1),
(754, 48, 'A', 'Anjouan', 1),
(755, 48, 'M', 'Moheli', 1),

(756, 49, 'BO', 'Bouenza', 1),
(757, 49, 'BR', 'Brazzaville', 1),
(758, 49, 'CU', 'Cuvette', 1),
(759, 49, 'CO', 'Cuvette-Ouest', 1),
(760, 49, 'KO', 'Kouilou', 1),
(761, 49, 'LE', 'Lekoumou', 1),
(762, 49, 'LI', 'Likouala', 1),
(763, 49, 'NI', 'Niari', 1),
(764, 49, 'PL', 'Plateaux', 1),
(765, 49, 'PO', 'Pool', 1),
(766, 49, 'SA', 'Sangha', 1),

(767, 50, 'PU', 'Pukapuka', 1),
(768, 50, 'RK', 'Rakahanga', 1),
(769, 50, 'MK', 'Manihiki', 1),
(770, 50, 'PE', 'Penrhyn', 1),
(771, 50, 'NI', 'Nassau Island', 1),
(772, 50, 'SU', 'Surwarrow', 1),
(773, 50, 'PA', 'Palmerston', 1),
(774, 50, 'AI', 'Aitutaki', 1),
(775, 50, 'MA', 'Manuae', 1),
(776, 50, 'TA', 'Takutea', 1),
(777, 50, 'MT', 'Mitiaro', 1),
(778, 50, 'AT', 'Atiu', 1),
(779, 50, 'MU', 'Mauke', 1),
(780, 50, 'RR', 'Rarotonga', 1),
(781, 50, 'MG', 'Mangaia', 1),

(782, 51, 'AL', 'Alajuela', 1),
(783, 51, 'CA', 'Cartago', 1),
(784, 51, 'GU', 'Guanacaste', 1),
(785, 51, 'HE', 'Heredia', 1),
(786, 51, 'LI', 'Limon', 1),
(787, 51, 'PU', 'Puntarenas', 1),
(788, 51, 'SJ', 'San Jose', 1),

(789, 52, 'ABE', 'Abengourou', 1),
(790, 52, 'ABI', 'Abidjan', 1),
(791, 52, 'ABO', 'Aboisso', 1),
(792, 52, 'ADI', 'Adiake', 1),
(793, 52, 'ADZ', 'Adzope', 1),
(794, 52, 'AGB', 'Agboville', 1),
(795, 52, 'AGN', 'Agnibilekrou', 1),
(796, 52, 'ALE', 'Alepe', 1),
(797, 52, 'BOC', 'Bocanda', 1),
(798, 52, 'BAN', 'Bangolo', 1),
(799, 52, 'BEO', 'Beoumi', 1),
(800, 52, 'BIA', 'Biankouma', 1),
(801, 52, 'BDK', 'Bondoukou', 1),
(802, 52, 'BGN', 'Bongouanou', 1),
(803, 52, 'BFL', 'Bouafle', 1),
(804, 52, 'BKE', 'Bouake', 1),
(805, 52, 'BNA', 'Bouna', 1),
(806, 52, 'BDL', 'Boundiali', 1),
(807, 52, 'DKL', 'Dabakala', 1),
(808, 52, 'DBU', 'Dabou', 1),
(809, 52, 'DAL', 'Daloa', 1),
(810, 52, 'DAN', 'Danane', 1),
(811, 52, 'DAO', 'Daoukro', 1),
(812, 52, 'DIM', 'Dimbokro', 1),
(813, 52, 'DIV', 'Divo', 1),
(814, 52, 'DUE', 'Duekoue', 1),
(815, 52, 'FER', 'Ferkessedougou', 1),
(816, 52, 'GAG', 'Gagnoa', 1),
(817, 52, 'GBA', 'Grand-Bassam', 1),
(818, 52, 'GLA', 'Grand-Lahou', 1),
(819, 52, 'GUI', 'Guiglo', 1),
(820, 52, 'ISS', 'Issia', 1),
(821, 52, 'JAC', 'Jacqueville', 1),
(822, 52, 'KAT', 'Katiola', 1),
(823, 52, 'KOR', 'Korhogo', 1),
(824, 52, 'LAK', 'Lakota', 1),
(825, 52, 'MAN', 'Man', 1),
(826, 52, 'MKN', 'Mankono', 1),
(827, 52, 'MBA', 'Mbahiakro', 1),
(828, 52, 'ODI', 'Odienne', 1),
(829, 52, 'OUM', 'Oume', 1),
(830, 52, 'SAK', 'Sakassou', 1),
(831, 52, 'SPE', 'San-Pedro', 1),
(832, 52, 'SAS', 'Sassandra', 1),
(833, 52, 'SEG', 'Seguela', 1),
(834, 52, 'SIN', 'Sinfra', 1),
(835, 52, 'SOU', 'Soubre', 1),
(836, 52, 'TAB', 'Tabou', 1),
(837, 52, 'TAN', 'Tanda', 1),
(838, 52, 'TIE', 'Tiebissou', 1),
(839, 52, 'TIN', 'Tingrela', 1),
(840, 52, 'TIA', 'Tiassale', 1),
(841, 52, 'TBA', 'Touba', 1),
(842, 52, 'TLP', 'Toulepleu', 1),
(843, 52, 'TMD', 'Toumodi', 1),
(844, 52, 'VAV', 'Vavoua', 1),
(845, 52, 'YAM', 'Yamoussoukro', 1),
(846, 52, 'ZUE', 'Zuenoula', 1),

(847, 53, 'BB', 'Bjelovarsko-bilogorska', 1),
(848, 53, 'GZ', 'Grad Zagreb', 1),
(849, 53, 'DN', 'Dubrova&ccaron;ko-neretvanska', 1),
(850, 53, 'IS', 'Istarska', 1),
(851, 53, 'KA', 'Karlova&ccaron;ka', 1),
(852, 53, 'KK', 'Koprivni&ccaron;ko-kri&zcaron;eva&ccaron;ka', 1),
(853, 53, 'KZ', 'Krapinsko-zagorska', 1),
(854, 53, 'LS', 'Li&ccaron;ko-senjska', 1),
(855, 53, 'ME', 'Medimurska', 1),
(856, 53, 'OB', 'Osje&ccaron;ko-baranjska', 1),
(857, 53, 'PS', 'Po&zcaron;e&scaron;ko-slavonska', 1),
(858, 53, 'PG', 'Primorsko-goranska', 1),
(859, 53, 'SK', '&Scaron;ibensko-kninska', 1),
(860, 53, 'SM', 'Sisa&ccaron;ko-moslava&ccaron;ka', 1),
(861, 53, 'BP', 'Brodsko-posavska', 1),
(862, 53, 'SD', 'Splitsko-dalmatinska', 1),
(863, 53, 'VA', 'Vara&zcaron;dinska', 1),
(864, 53, 'VP', 'Viroviti&ccaron;ko-podravska', 1),
(865, 53, 'VS', 'Vukovarsko-srijemska', 1),
(866, 53, 'ZA', 'Zadarska', 1),
(867, 53, 'ZG', 'Zagreba&ccaron;ka', 1),

(868, 54, 'CA', 'Camaguey', 1),
(869, 54, 'CD', 'Ciego de Avila', 1),
(870, 54, 'CI', 'Cienfuegos', 1),
(871, 54, 'CH', 'Ciudad de La Habana', 1),
(872, 54, 'GR', 'Granma', 1),
(873, 54, 'GU', 'Guantanamo', 1),
(874, 54, 'HO', 'Holguin', 1),
(875, 54, 'IJ', 'Isla de la Juventud', 1),
(876, 54, 'LH', 'La Habana', 1),
(877, 54, 'LT', 'Las Tunas', 1),
(878, 54, 'MA', 'Matanzas', 1),
(879, 54, 'PR', 'Pinar del Rio', 1),
(880, 54, 'SS', 'Sancti Spiritus', 1),
(881, 54, 'SC', 'Santiago de Cuba', 1),
(882, 54, 'VC', 'Villa Clara', 1),

(883, 55, 'F', 'Famagusta', 1),
(884, 55, 'K', 'Kyrenia', 1),
(885, 55, 'A', 'Larnaca', 1),
(886, 55, 'I', 'Limassol', 1),
(887, 55, 'N', 'Nicosia', 1),
(888, 55, 'P', 'Paphos', 1),

(889, 56, 'U', '&Uacute;steck&yacute;', 1),
(890, 56, 'C', 'Jiho&ccaron;esk&yacute;', 1),
(891, 56, 'B', 'Jihomoravsk&yacute;', 1),
(892, 56, 'K', 'Karlovarsk&yacute;', 1),
(893, 56, 'H', 'Kr&aacute;lovehradeck&yacute;', 1),
(894, 56, 'L', 'Libereck&yacute;', 1),
(895, 56, 'T', 'Moravskoslezsk&yacute;', 1),
(896, 56, 'M', 'Olomouck&yacute;', 1),
(897, 56, 'E', 'Pardubick&yacute;', 1),
(898, 56, 'P', 'Plze&ncaron;sk&yacute;', 1),
(899, 56, 'A', 'Praha', 1),
(900, 56, 'S', 'St&rcaron;edo&ccaron;esk&yacute;', 1),
(901, 56, 'J', 'Vyso&ccaron;ina', 1),
(902, 56, 'Z', 'Zl&iacute;nsk&yacute;', 1),

(903, 57, 'AR', 'Arhus', 1),
(904, 57, 'BH', 'Bornholm', 1),
(905, 57, 'CO', 'Copenhagen', 1),
(906, 57, 'FO', 'Faroe Islands', 1),
(907, 57, 'FR', 'Frederiksborg', 1),
(908, 57, 'FY', 'Fyn', 1),
(909, 57, 'KO', 'Kobenhavn', 1),
(910, 57, 'NO', 'Nordjylland', 1),
(911, 57, 'RI', 'Ribe', 1),
(912, 57, 'RK', 'Ringkobing', 1),
(913, 57, 'RO', 'Roskilde', 1),
(914, 57, 'SO', 'Sonderjylland', 1),
(915, 57, 'ST', 'Storstrom', 1),
(916, 57, 'VK', 'Vejle', 1),
(917, 57, 'VJ', 'Vestj&aelig;lland', 1),
(918, 57, 'VB', 'Viborg', 1),

(919, 58, 'S', '''Ali Sabih', 1),
(920, 58, 'K', 'Dikhil', 1),
(921, 58, 'J', 'Djibouti', 1),
(922, 58, 'O', 'Obock', 1),
(923, 58, 'T', 'Tadjoura', 1),

(924, 59, 'AND', 'Saint Andrew Parish', 1),
(925, 59, 'DAV', 'Saint David Parish', 1),
(926, 59, 'GEO', 'Saint George Parish', 1),
(927, 59, 'JOH', 'Saint John Parish', 1),
(928, 59, 'JOS', 'Saint Joseph Parish', 1),
(929, 59, 'LUK', 'Saint Luke Parish', 1),
(930, 59, 'MAR', 'Saint Mark Parish', 1),
(931, 59, 'PAT', 'Saint Patrick Parish', 1),
(932, 59, 'PAU', 'Saint Paul Parish', 1),
(933, 59, 'PET', 'Saint Peter Parish', 1),

(934, 60, 'DN', 'Distrito Nacional', 1),
(935, 60, 'AZ', 'Azua', 1),
(936, 60, 'BC', 'Baoruco', 1),
(937, 60, 'BH', 'Barahona', 1),
(938, 60, 'DJ', 'Dajabon', 1),
(939, 60, 'DU', 'Duarte', 1),
(940, 60, 'EL', 'Elias Pina', 1),
(941, 60, 'SY', 'El Se&iacute;bo', 1),
(942, 60, 'ET', 'Espaillat', 1),
(943, 60, 'HM', 'Hato Mayor', 1),
(944, 60, 'IN', 'Independencia', 1),
(945, 60, 'AL', 'La Altagracia', 1),
(946, 60, 'RO', 'La Romana', 1),
(947, 60, 'VE', 'La Vega', 1),
(948, 60, 'MT', 'Maria Trinidad Sanchez', 1),
(949, 60, 'MN', 'Monse&ntilde;or Nouel', 1),
(950, 60, 'MC', 'Monte Cristi', 1),
(951, 60, 'MP', 'Monte Plata', 1),
(952, 60, 'PD', 'Pedernales', 1),
(953, 60, 'PR', 'Peravia (Bani)', 1),
(954, 60, 'PP', 'Puerto Plata', 1),
(955, 60, 'SL', 'Salcedo', 1),
(956, 60, 'SM', 'Samana', 1),
(957, 60, 'SH', 'Sanchez Ramirez', 1),
(958, 60, 'SC', 'San Cristob&aacute;l', 1),
(959, 60, 'JO', 'San Jose de Ocoa', 1),
(960, 60, 'SJ', 'San Juan', 1),
(961, 60, 'PM', 'San Pedro de Macoris', 1),
(962, 60, 'SA', 'Santiago', 1),
(963, 60, 'ST', 'Santiago Rodriguez', 1),
(964, 60, 'SD', 'Santo Domingo', 1),
(965, 60, 'VA', 'Valverde', 1),

(966, 61, 'AL', 'Aileu', 1),
(967, 61, 'AN', 'Ainaro', 1),
(968, 61, 'BA', 'Baucau', 1),
(969, 61, 'BO', 'Bobonaro', 1),
(970, 61, 'CO', 'Cova Lima', 1),
(971, 61, 'DI', 'Dili', 1),
(972, 61, 'ER', 'Ermera', 1),
(973, 61, 'LA', 'Lautem', 1),
(974, 61, 'LI', 'Liquica', 1),
(975, 61, 'MT', 'Manatuto', 1),
(976, 61, 'MF', 'Manufahi', 1),
(977, 61, 'OE', 'Oecussi', 1),
(978, 61, 'VI', 'Viqueque', 1),

(979, 62, 'AZU', 'Azuay', 1),
(980, 62, 'BOL', 'Bolivar', 1),
(981, 62, 'CAN', 'Ca&ntilde;ar', 1),
(982, 62, 'CAR', 'Carchi', 1),
(983, 62, 'CHI', 'Chimborazo', 1),
(984, 62, 'COT', 'Cotopaxi', 1),
(985, 62, 'EOR', 'El Oro', 1),
(986, 62, 'ESM', 'Esmeraldas', 1),
(987, 62, 'GPS', 'Gal&aacute;pagos', 1),
(988, 62, 'GUA', 'Guayas', 1),
(989, 62, 'IMB', 'Imbabura', 1),
(990, 62, 'LOJ', 'Loja', 1),
(991, 62, 'LRO', 'Los Rios', 1),
(992, 62, 'MAN', 'Manab&iacute;', 1),
(993, 62, 'MSA', 'Morona Santiago', 1),
(994, 62, 'NAP', 'Napo', 1),
(995, 62, 'ORE', 'Orellana', 1),
(996, 62, 'PAS', 'Pastaza', 1),
(997, 62, 'PIC', 'Pichincha', 1),
(998, 62, 'SUC', 'Sucumb&iacute;os', 1),
(999, 62, 'TUN', 'Tungurahua', 1),
(1000, 62, 'ZCH', 'Zamora Chinchipe', 1);

INSERT INTO `nc_zone` (`zone_id`, `country_id`, `code`, `name`, `status`) VALUES
(1001, 63, 'DHY', 'Ad Daqahliyah', 1),
(1002, 63, 'BAM', 'Al Bahr al Ahmar', 1),
(1003, 63, 'BHY', 'Al Buhayrah', 1),
(1004, 63, 'FYM', 'Al Fayyum', 1),
(1005, 63, 'GBY', 'Al Gharbiyah', 1),
(1006, 63, 'IDR', 'Al Iskandariyah', 1),
(1007, 63, 'IML', 'Al Isma''iliyah', 1),
(1008, 63, 'JZH', 'Al Jizah', 1),
(1009, 63, 'MFY', 'Al Minufiyah', 1),
(1010, 63, 'MNY', 'Al Minya', 1),
(1011, 63, 'QHR', 'Al Qahirah', 1),
(1012, 63, 'QLY', 'Al Qalyubiyah', 1),
(1013, 63, 'WJD', 'Al Wadi al Jadid', 1),
(1014, 63, 'SHQ', 'Ash Sharqiyah', 1),
(1015, 63, 'SWY', 'As Suways', 1),
(1016, 63, 'ASW', 'Aswan', 1),
(1017, 63, 'ASY', 'Asyut', 1),
(1018, 63, 'BSW', 'Bani Suwayf', 1),
(1019, 63, 'BSD', 'Bur Sa''id', 1),
(1020, 63, 'DMY', 'Dumyat', 1),
(1021, 63, 'JNS', 'Janub Sina''', 1),
(1022, 63, 'KSH', 'Kafr ash Shaykh', 1),
(1023, 63, 'MAT', 'Matruh', 1),
(1024, 63, 'QIN', 'Qina', 1),
(1025, 63, 'SHS', 'Shamal Sina''', 1),
(1026, 63, 'SUH', 'Suhaj', 1),

(1027, 64, 'AH', 'Ahuachapan', 1),
(1028, 64, 'CA', 'Cabanas', 1),
(1029, 64, 'CH', 'Chalatenango', 1),
(1030, 64, 'CU', 'Cuscatlan', 1),
(1031, 64, 'LB', 'La Libertad', 1),
(1032, 64, 'PZ', 'La Paz', 1),
(1033, 64, 'UN', 'La Union', 1),
(1034, 64, 'MO', 'Morazan', 1),
(1035, 64, 'SM', 'San Miguel', 1),
(1036, 64, 'SS', 'San Salvador', 1),
(1037, 64, 'SV', 'San Vicente', 1),
(1038, 64, 'SA', 'Santa Ana', 1),
(1039, 64, 'SO', 'Sonsonate', 1),
(1040, 64, 'US', 'Usulutan', 1),

(1041, 65, 'AN', 'Provincia Annobon', 1),
(1042, 65, 'BN', 'Provincia Bioko Norte', 1),
(1043, 65, 'BS', 'Provincia Bioko Sur', 1),
(1044, 65, 'CS', 'Provincia Centro Sur', 1),
(1045, 65, 'KN', 'Provincia Kie-Ntem', 1),
(1046, 65, 'LI', 'Provincia Litoral', 1),
(1047, 65, 'WN', 'Provincia Wele-Nzas', 1),

(1048, 66, 'MA', 'Central (Maakel)', 1),
(1049, 66, 'KE', 'Anseba (Keren)', 1),
(1050, 66, 'DK', 'Southern Red Sea (Debubawi-Keih-Bahri)', 1),
(1051, 66, 'SK', 'Northern Red Sea (Semenawi-Keih-Bahri)', 1),
(1052, 66, 'DE', 'Southern (Debub)', 1),
(1053, 66, 'BR', 'Gash-Barka (Barentu)', 1),

(1054, 67, 'HA', 'Harju maakond (Tallinn)', 1),
(1055, 67, 'HI', 'Hiiu maakond (Kardla)', 1),
(1056, 67, 'IV', 'Ida-Viru maakond (Johvi)', 1),
(1057, 67, 'JA', 'J&auml;rva maakond (Paide)', 1),
(1058, 67, 'JO', 'J&otilde;geva maakond (Jogeva)', 1),
(1059, 67, 'LV', 'L&auml;&auml;ne-Viru maakond (Rakvere)', 1),
(1060, 67, 'LA', 'L&auml;&auml;ne maakond (Haapsalu)', 1),
(1061, 67, 'PA', 'P&auml;rnu maakond (Parnu)', 1),
(1062, 67, 'PO', 'P&otilde;lva maakond (Polva)', 1),
(1063, 67, 'RA', 'Rapla maakond (Rapla)', 1),
(1064, 67, 'SA', 'Saare maakond (Kuessaare)', 1),
(1065, 67, 'TA', 'Tartu maakond (Tartu)', 1),
(1066, 67, 'VA', 'Valga maakond (Valga)', 1),
(1067, 67, 'VI', 'Viljandi maakond (Viljandi)', 1),
(1068, 67, 'VO', 'V&otilde;ru maakond (Voru)', 1),

(1069, 68, 'AF', 'Afar', 1),
(1070, 68, 'AH', 'Amhara', 1),
(1071, 68, 'BG', 'Benishangul-Gumuz', 1),
(1072, 68, 'GB', 'Gambela', 1),
(1073, 68, 'HR', 'Hariai', 1),
(1074, 68, 'OR', 'Oromia', 1),
(1075, 68, 'SM', 'Somali', 1),
(1076, 68, 'SN', 'Southern Nations - Nationalities and Peoples Region', 1),
(1077, 68, 'TG', 'Tigray', 1),
(1078, 68, 'AA', 'Addis Ababa', 1),
(1079, 68, 'DD', 'Dire Dawa', 1),

(1080, 71, 'C', 'Central Division', 1),
(1081, 71, 'N', 'Northern Division', 1),
(1082, 71, 'E', 'Eastern Division', 1),
(1083, 71, 'W', 'Western Division', 1),
(1084, 71, 'R', 'Rotuma', 1),

(1086, 72, 'ES', 'Etel&auml;-Suomen L&auml;&auml;ni', 1),
(1087, 72, 'IS', 'It&auml;-Suomen L&auml;&auml;ni', 1),
(1088, 72, 'LS', 'L&auml;nsi-Suomen L&auml;&auml;ni', 1),
(1089, 72, 'LA', 'Lapin L&auml;&auml;ni', 1),
(1090, 72, 'OU', 'Oulun L&auml;&auml;ni', 1),

(1114, 74, '01', 'Ain', 1),
(1115, 74, '02', 'Aisne', 1),
(1116, 74, '03', 'Allier', 1),
(1117, 74, '04', 'Alpes de Haute Provence', 1),
(1118, 74, '05', 'Hautes-Alpes', 1),
(1119, 74, '06', 'Alpes Maritimes', 1),
(1120, 74, '07', 'Ard&egrave;che', 1),
(1121, 74, '08', 'Ardennes', 1),
(1122, 74, '09', 'Ari&egrave;ge', 1),
(1123, 74, '10', 'Aube', 1),
(1124, 74, '11', 'Aude', 1),
(1125, 74, '12', 'Aveyron', 1),
(1126, 74, '13', 'Bouches du Rh&ocirc;ne', 1),
(1127, 74, '14', 'Calvados', 1),
(1128, 74, '15', 'Cantal', 1),
(1129, 74, '16', 'Charente', 1),
(1130, 74, '17', 'Charente Maritime', 1),
(1131, 74, '18', 'Cher', 1),
(1132, 74, '19', 'Corr&egrave;ze', 1),
(1133, 74, '2A', 'Corse du Sud', 1),
(1134, 74, '2B', 'Haute Corse', 1),
(1135, 74, '21', 'C&ocirc;te d&#039;or', 1),
(1136, 74, '22', 'C&ocirc;tes d&#039;Armor', 1),
(1137, 74, '23', 'Creuse', 1),
(1138, 74, '24', 'Dordogne', 1),
(1139, 74, '25', 'Doubs', 1),
(1140, 74, '26', 'Dr&ocirc;me', 1),
(1141, 74, '27', 'Eure', 1),
(1142, 74, '28', 'Eure et Loir', 1),
(1143, 74, '29', 'Finist&egrave;re', 1),
(1144, 74, '30', 'Gard', 1),
(1145, 74, '31', 'Haute Garonne', 1),
(1146, 74, '32', 'Gers', 1),
(1147, 74, '33', 'Gironde', 1),
(1148, 74, '34', 'H&eacute;rault', 1),
(1149, 74, '35', 'Ille et Vilaine', 1),
(1150, 74, '36', 'Indre', 1),
(1151, 74, '37', 'Indre et Loire', 1),
(1152, 74, '38', 'Is&eacute;re', 1),
(1153, 74, '39', 'Jura', 1),
(1154, 74, '40', 'Landes', 1),
(1155, 74, '41', 'Loir et Cher', 1),
(1156, 74, '42', 'Loire', 1),
(1157, 74, '43', 'Haute Loire', 1),
(1158, 74, '44', 'Loire Atlantique', 1),
(1159, 74, '45', 'Loiret', 1),
(1160, 74, '46', 'Lot', 1),
(1161, 74, '47', 'Lot et Garonne', 1),
(1162, 74, '48', 'Loz&egrave;re', 1),
(1163, 74, '49', 'Maine et Loire', 1),
(1164, 74, '50', 'Manche', 1),
(1165, 74, '51', 'Marne', 1),
(1166, 74, '52', 'Haute Marne', 1),
(1167, 74, '53', 'Mayenne', 1),
(1168, 74, '54', 'Meurthe et Moselle', 1),
(1169, 74, '55', 'Meuse', 1),
(1170, 74, '56', 'Morbihan', 1),
(1171, 74, '57', 'Moselle', 1),
(1172, 74, '58', 'Ni&egrave;vre', 1),
(1173, 74, '59', 'Nord', 1),
(1174, 74, '60', 'Oise', 1),
(1175, 74, '61', 'Orne', 1),
(1176, 74, '62', 'Pas de Calais', 1),
(1177, 74, '63', 'Puy de D&ocirc;me', 1),
(1178, 74, '64', 'Pyr&eacute;n&eacute;es Atlantiques', 1),
(1179, 74, '65', 'Hautes Pyr&eacute;n&eacute;es', 1),
(1180, 74, '66', 'Pyr&eacute;n&eacute;es Orientales', 1),
(1181, 74, '67', 'Bas Rhin', 1),
(1182, 74, '68', 'Haut Rhin', 1),
(1183, 74, '69', 'Rh&ocirc;ne', 1),
(1184, 74, '70', 'Haute Sa&ocirc;ne', 1),
(1185, 74, '71', 'Sa&ocirc;ne et Loire', 1),
(1186, 74, '72', 'Sarthe', 1),
(1187, 74, '73', 'Savoie', 1),
(1188, 74, '74', 'Haute Savoie', 1),
(1189, 74, '75', 'Paris, &Icirc;le de France', 1),
(1190, 74, '76', 'Seine Maritime', 1),
(1191, 74, '77', 'Seine et Marne', 1),
(1192, 74, '78', 'Yvelines', 1),
(1193, 74, '79', 'Deux S&egrave;vres', 1),
(1194, 74, '80', 'Somme', 1),
(1195, 74, '81', 'Tarn', 1),
(1196, 74, '82', 'Tarn et Garonne', 1),
(1197, 74, '83', 'Var', 1),
(1198, 74, '84', 'Vaucluse', 1),
(1199, 74, '85', 'Vend&eacute;e', 1),
(1200, 74, '86', 'Vienne', 1),
(1201, 74, '87', 'Haute Vienne', 1),
(1202, 74, '88', 'Vosges', 1),
(1203, 74, '89', 'Yonne', 1),
(1204, 74, '90', 'Territoire de Belfort', 1),
(1205, 74, '91', 'Essonne', 1),
(1206, 74, '92', 'Hauts de Seine', 1),
(1207, 74, '93', 'Seine St-Denis', 1),
(1208, 74, '94', 'Val de Marne', 1),
(1209, 74, '95', 'Val d&#039;Oise', 1),

(1210, 76, 'M', 'Archipel des Marquises', 1),
(1211, 76, 'T', 'Archipel des Tuamotu', 1),
(1212, 76, 'I', 'Archipel des Tubuai', 1),
(1213, 76, 'V', 'Iles du Vent', 1),
(1214, 76, 'S', 'Iles Sous-le-Vent', 1),

(1215, 77, 'C', 'Iles Crozet', 1),
(1216, 77, 'K', 'Iles Kerguelen', 1),
(1217, 77, 'A', 'Ile Amsterdam', 1),
(1218, 77, 'P', 'Ile Saint-Paul', 1),
(1219, 77, 'D', 'Terre Ad&eacute;lie', 1),

(1220, 78, 'ES', 'Estuaire', 1),
(1221, 78, 'HO', 'Haut-Ogooue', 1),
(1222, 78, 'MO', 'Moyen-Ogooue', 1),
(1223, 78, 'NG', 'Ngounie', 1),
(1224, 78, 'NY', 'Nyanga', 1),
(1225, 78, 'OI', 'Ogooue-Ivindo', 1),
(1226, 78, 'OL', 'Ogooue-Lolo', 1),
(1227, 78, 'OM', 'Ogooue-Maritime', 1),
(1228, 78, 'WN', 'Woleu-Ntem', 1),

(1229, 79, 'BJ', 'Banjul', 1),
(1230, 79, 'BS', 'Basse', 1),
(1231, 79, 'BR', 'Brikama', 1),
(1232, 79, 'JA', 'Janjangbure', 1),
(1233, 79, 'KA', 'Kanifeng', 1),
(1234, 79, 'KE', 'Kerewan', 1),
(1235, 79, 'KU', 'Kuntaur', 1),
(1236, 79, 'MA', 'Mansakonko', 1),
(1237, 79, 'LR', 'Lower River', 1),
(1238, 79, 'CR', 'Central River', 1),
(1239, 79, 'NB', 'North Bank', 1),
(1240, 79, 'UR', 'Upper River', 1),
(1241, 79, 'WE', 'Western', 1),

(1242, 80, 'AB', 'Abkhazia', 1),
(1243, 80, 'AJ', 'Ajaria', 1),
(1244, 80, 'TB', 'Tbilisi', 1),
(1245, 80, 'GU', 'Guria', 1),
(1246, 80, 'IM', 'Imereti', 1),
(1247, 80, 'KA', 'Kakheti', 1),
(1248, 80, 'KK', 'Kvemo Kartli', 1),
(1249, 80, 'MM', 'Mtskheta-Mtianeti', 1),
(1250, 80, 'RL', 'Racha Lechkhumi and Kvemo Svanet', 1),
(1251, 80, 'SZ', 'Samegrelo-Zemo Svaneti', 1),
(1252, 80, 'SJ', 'Samtskhe-Javakheti', 1),
(1253, 80, 'SK', 'Shida Kartli', 1),

(1254, 81, 'BAW', 'Baden-W&uuml;rttemberg', 1),
(1255, 81, 'BAY', 'Bayern', 1),
(1256, 81, 'BER', 'Berlin', 1),
(1257, 81, 'BRG', 'Brandenburg', 1),
(1258, 81, 'BRE', 'Bremen', 1),
(1259, 81, 'HAM', 'Hamburg', 1),
(1260, 81, 'HES', 'Hessen', 1),
(1261, 81, 'MEC', 'Mecklenburg-Vorpommern', 1),
(1262, 81, 'NDS', 'Niedersachsen', 1),
(1263, 81, 'NRW', 'Nordrhein-Westfalen', 1),
(1264, 81, 'RHE', 'Rheinland-Pfalz', 1),
(1265, 81, 'SAR', 'Saarland', 1),
(1266, 81, 'SAS', 'Sachsen', 1),
(1267, 81, 'SAC', 'Sachsen-Anhalt', 1),
(1268, 81, 'SCN', 'Schleswig-Holstein', 1),
(1269, 81, 'THE', 'Th&uuml;ringen', 1),

(1270, 82, 'AS', 'Ashanti Region', 1),
(1271, 82, 'BA', 'Brong-Ahafo Region', 1),
(1272, 82, 'CE', 'Central Region', 1),
(1273, 82, 'EA', 'Eastern Region', 1),
(1274, 82, 'GA', 'Greater Accra Region', 1),
(1275, 82, 'NO', 'Northern Region', 1),
(1276, 82, 'UE', 'Upper East Region', 1),
(1277, 82, 'UW', 'Upper West Region', 1),
(1278, 82, 'VO', 'Volta Region', 1),
(1279, 82, 'WE', 'Western Region', 1),

(1280, 84, 'AT', 'Attica', 1),
(1281, 84, 'CN', 'Central Greece', 1),
(1282, 84, 'CM', 'Central Macedonia', 1),
(1283, 84, 'CR', 'Crete', 1),
(1284, 84, 'EM', 'East Macedonia and Thrace', 1),
(1285, 84, 'EP', 'Epirus', 1),
(1286, 84, 'II', 'Ionian Islands', 1),
(1287, 84, 'NA', 'North Aegean', 1),
(1288, 84, 'PP', 'Peloponnesos', 1),
(1289, 84, 'SA', 'South Aegean', 1),
(1290, 84, 'TH', 'Thessaly', 1),
(1291, 84, 'WG', 'West Greece', 1),
(1292, 84, 'WM', 'West Macedonia', 1),

(1293, 85, 'A', 'Avannaa (North Greenland)', 1),
(1294, 85, 'T', 'Tunu (East Greenland)', 1),
(1295, 85, 'K', 'Kitaa (West Greenland)', 1),

(1296, 86, 'A', 'Saint Andrew', 1),
(1297, 86, 'D', 'Saint David', 1),
(1298, 86, 'G', 'Saint George', 1),
(1299, 86, 'J', 'Saint John', 1),
(1300, 86, 'M', 'Saint Mark', 1),
(1301, 86, 'P', 'Saint Patrick', 1),
(1302, 86, 'C', 'Carriacou', 1),
(1303, 86, 'Q', 'Petit Martinique', 1),

(1304, 89, 'AV', 'Alta Verapaz', 1),
(1305, 89, 'BV', 'Baja Verapaz', 1),
(1306, 89, 'CM', 'Chimaltenango', 1),
(1307, 89, 'CQ', 'Chiquimula', 1),
(1308, 89, 'PE', 'El Peten', 1),
(1309, 89, 'PR', 'El Progreso', 1),
(1310, 89, 'QC', 'El Quiche', 1),
(1311, 89, 'ES', 'Escuintla', 1),
(1312, 89, 'GU', 'Guatemala', 1),
(1313, 89, 'HU', 'Huehuetenango', 1),
(1314, 89, 'IZ', 'Izabal', 1),
(1315, 89, 'JA', 'Jalapa', 1),
(1316, 89, 'JU', 'Jutiapa', 1),
(1317, 89, 'QZ', 'Quetzaltenango', 1),
(1318, 89, 'RE', 'Retalhuleu', 1),
(1319, 89, 'ST', 'Sacatepequez', 1),
(1320, 89, 'SM', 'San Marcos', 1),
(1321, 89, 'SR', 'Santa Rosa', 1),
(1322, 89, 'SO', 'Solola', 1),
(1323, 89, 'SU', 'Suchitepequez', 1),
(1324, 89, 'TO', 'Totonicapan', 1),
(1325, 89, 'ZA', 'Zacapa', 1),

(1326, 90, 'CNK', 'Conakry', 1),
(1327, 90, 'BYL', 'Beyla', 1),
(1328, 90, 'BFA', 'Boffa', 1),
(1329, 90, 'BOK', 'Boke', 1),
(1330, 90, 'COY', 'Coyah', 1),
(1331, 90, 'DBL', 'Dabola', 1),
(1332, 90, 'DLB', 'Dalaba', 1),
(1333, 90, 'DGR', 'Dinguiraye', 1),
(1334, 90, 'DBR', 'Dubreka', 1),
(1335, 90, 'FRN', 'Faranah', 1),
(1336, 90, 'FRC', 'Forecariah', 1),
(1337, 90, 'FRI', 'Fria', 1),
(1338, 90, 'GAO', 'Gaoual', 1),
(1339, 90, 'GCD', 'Gueckedou', 1),
(1340, 90, 'KNK', 'Kankan', 1),
(1341, 90, 'KRN', 'Kerouane', 1),
(1342, 90, 'KND', 'Kindia', 1),
(1343, 90, 'KSD', 'Kissidougou', 1),
(1344, 90, 'KBA', 'Koubia', 1),
(1345, 90, 'KDA', 'Koundara', 1),
(1346, 90, 'KRA', 'Kouroussa', 1),
(1347, 90, 'LAB', 'Labe', 1),
(1348, 90, 'LLM', 'Lelouma', 1),
(1349, 90, 'LOL', 'Lola', 1),
(1350, 90, 'MCT', 'Macenta', 1),
(1351, 90, 'MAL', 'Mali', 1),
(1352, 90, 'MAM', 'Mamou', 1),
(1353, 90, 'MAN', 'Mandiana', 1),
(1354, 90, 'NZR', 'Nzerekore', 1),
(1355, 90, 'PIT', 'Pita', 1),
(1356, 90, 'SIG', 'Siguiri', 1),
(1357, 90, 'TLM', 'Telimele', 1),
(1358, 90, 'TOG', 'Tougue', 1),
(1359, 90, 'YOM', 'Yomou', 1),

(1360, 91, 'BF', 'Bafata Region', 1),
(1361, 91, 'BB', 'Biombo Region', 1),
(1362, 91, 'BS', 'Bissau Region', 1),
(1363, 91, 'BL', 'Bolama Region', 1),
(1364, 91, 'CA', 'Cacheu Region', 1),
(1365, 91, 'GA', 'Gabu Region', 1),
(1366, 91, 'OI', 'Oio Region', 1),
(1367, 91, 'QU', 'Quinara Region', 1),
(1368, 91, 'TO', 'Tombali Region', 1),

(1369, 92, 'BW', 'Barima-Waini', 1),
(1370, 92, 'CM', 'Cuyuni-Mazaruni', 1),
(1371, 92, 'DM', 'Demerara-Mahaica', 1),
(1372, 92, 'EC', 'East Berbice-Corentyne', 1),
(1373, 92, 'EW', 'Essequibo Islands-West Demerara', 1),
(1374, 92, 'MB', 'Mahaica-Berbice', 1),
(1375, 92, 'PM', 'Pomeroon-Supenaam', 1),
(1376, 92, 'PI', 'Potaro-Siparuni', 1),
(1377, 92, 'UD', 'Upper Demerara-Berbice', 1),
(1378, 92, 'UT', 'Upper Takutu-Upper Essequibo', 1),

(1379, 93, 'AR', 'Artibonite', 1),
(1380, 93, 'CE', 'Centre', 1),
(1381, 93, 'GA', 'Grand''Anse', 1),
(1382, 93, 'ND', 'Nord', 1),
(1383, 93, 'NE', 'Nord-Est', 1),
(1384, 93, 'NO', 'Nord-Ouest', 1),
(1385, 93, 'OU', 'Ouest', 1),
(1386, 93, 'SD', 'Sud', 1),
(1387, 93, 'SE', 'Sud-Est', 1),

(1388, 94, 'F', 'Flat Island', 1),
(1389, 94, 'M', 'McDonald Island', 1),
(1390, 94, 'S', 'Shag Island', 1),
(1391, 94, 'H', 'Heard Island', 1),

(1392, 95, 'AT', 'Atlantida', 1),
(1393, 95, 'CH', 'Choluteca', 1),
(1394, 95, 'CL', 'Col&oacute;n', 1),
(1395, 95, 'CM', 'Comayagua', 1),
(1396, 95, 'CP', 'Cop&aacute;n', 1),
(1397, 95, 'CR', 'Cortes', 1),
(1398, 95, 'PA', 'El Para&iacute;so', 1),
(1399, 95, 'FM', 'Francisco Morazan', 1),
(1400, 95, 'GD', 'Gracias a D&iacute;os', 1),
(1401, 95, 'IN', 'Intibuca', 1),
(1402, 95, 'IB', 'Islas de la Bah&iacute;a (Bay Islands)', 1),
(1403, 95, 'PZ', 'La P&aacute;z', 1),
(1404, 95, 'LE', 'Lempira', 1),
(1405, 95, 'OC', 'Ocotepeque', 1),
(1406, 95, 'OL', 'Olancho', 1),
(1407, 95, 'SB', 'Santa Barbara', 1),
(1408, 95, 'VA', 'Valle', 1),
(1409, 95, 'YO', 'Yoro', 1),

(1410, 96, 'HCW', 'Central and Western Hong Kong Island', 1),
(1411, 96, 'HEA', 'Eastern Hong Kong Island', 1),
(1412, 96, 'HSO', 'Southern Hong Kong Island', 1),
(1413, 96, 'HWC', 'Wan Chai Hong Kong Island', 1),
(1414, 96, 'KKC', 'Kowloon City Kowloon', 1),
(1415, 96, 'KKT', 'Kwun Tong Kowloon', 1),
(1416, 96, 'KSS', 'Sham Shui Po Kowloon', 1),
(1417, 96, 'KWT', 'Wong Tai Sin Kowloon', 1),
(1418, 96, 'KYT', 'Yau Tsim Mong Kowloon', 1),
(1419, 96, 'NIS', 'Islands New Territories', 1),
(1420, 96, 'NKT', 'Kwai Tsing New Territories', 1),
(1421, 96, 'NNO', 'North New Territories', 1),
(1422, 96, 'NSK', 'Sai Kung New Territories', 1),
(1423, 96, 'NST', 'Sha Tin New Territories', 1),
(1424, 96, 'NTP', 'Tai Po New Territories', 1),
(1425, 96, 'NTW', 'Tsuen Wan New Territories', 1),
(1426, 96, 'NTM', 'Tuen Mun New Territories', 1),
(1427, 96, 'NYL', 'Yuen Long New Territories', 1),

(1428, 97, 'BK', 'Bacs-Kiskun', 1),
(1429, 97, 'BA', 'Baranya', 1),
(1430, 97, 'BE', 'Bekes', 1),
(1431, 97, 'BS', 'Bekescsaba', 1),
(1432, 97, 'BZ', 'Borsod-Abauj-Zemplen', 1),
(1433, 97, 'BU', 'Budapest', 1),
(1434, 97, 'CS', 'Csongrad', 1),
(1435, 97, 'DE', 'Debrecen', 1),
(1436, 97, 'DU', 'Dunaujvaros', 1),
(1437, 97, 'EG', 'Eger', 1),
(1438, 97, 'FE', 'Fejer', 1),
(1439, 97, 'GY', 'Gyor', 1),
(1440, 97, 'GM', 'Gyor-Moson-Sopron', 1),
(1441, 97, 'HB', 'Hajdu-Bihar', 1),
(1442, 97, 'HE', 'Heves', 1),
(1443, 97, 'HO', 'Hodmezovasarhely', 1),
(1444, 97, 'JN', 'Jasz-Nagykun-Szolnok', 1),
(1445, 97, 'KA', 'Kaposvar', 1),
(1446, 97, 'KE', 'Kecskemet', 1),
(1447, 97, 'KO', 'Komarom-Esztergom', 1),
(1448, 97, 'MI', 'Miskolc', 1),
(1449, 97, 'NA', 'Nagykanizsa', 1),
(1450, 97, 'NO', 'Nograd', 1),
(1451, 97, 'NY', 'Nyiregyhaza', 1),
(1452, 97, 'PE', 'Pecs', 1),
(1453, 97, 'PS', 'Pest', 1),
(1454, 97, 'SO', 'Somogy', 1),
(1455, 97, 'SP', 'Sopron', 1),
(1456, 97, 'SS', 'Szabolcs-Szatmar-Bereg', 1),
(1457, 97, 'SZ', 'Szeged', 1),
(1458, 97, 'SE', 'Szekesfehervar', 1),
(1459, 97, 'SL', 'Szolnok', 1),
(1460, 97, 'SM', 'Szombathely', 1),
(1461, 97, 'TA', 'Tatabanya', 1),
(1462, 97, 'TO', 'Tolna', 1),
(1463, 97, 'VA', 'Vas', 1),
(1464, 97, 'VE', 'Veszprem', 1),
(1465, 97, 'ZA', 'Zala', 1),
(1466, 97, 'ZZ', 'Zalaegerszeg', 1),

(1467, 98, 'AL', 'Austurland', 1),
(1468, 98, 'HF', 'Hofuoborgarsvaeoi', 1),
(1469, 98, 'NE', 'Norourland eystra', 1),
(1470, 98, 'NV', 'Norourland vestra', 1),
(1471, 98, 'SL', 'Suourland', 1),
(1472, 98, 'SN', 'Suournes', 1),
(1473, 98, 'VF', 'Vestfiroir', 1),
(1474, 98, 'VL', 'Vesturland', 1),

(1475, 99, 'AN', 'Andaman and Nicobar Islands', 1),
(1476, 99, 'AP', 'Andhra Pradesh', 1),
(1477, 99, 'AR', 'Arunachal Pradesh', 1),
(1478, 99, 'AS', 'Assam', 1),
(1479, 99, 'BI', 'Bihar', 1),
(1480, 99, 'CH', 'Chandigarh', 1),
(1481, 99, 'DA', 'Dadra and Nagar Haveli', 1),
(1482, 99, 'DM', 'Daman and Diu', 1),
(1483, 99, 'DE', 'Delhi', 1),
(1484, 99, 'GO', 'Goa', 1),
(1485, 99, 'GU', 'Gujarat', 1),
(1486, 99, 'HA', 'Haryana', 1),
(1487, 99, 'HP', 'Himachal Pradesh', 1),
(1488, 99, 'JA', 'Jammu and Kashmir', 1),
(1489, 99, 'KA', 'Karnataka', 1),
(1490, 99, 'KE', 'Kerala', 1),
(1491, 99, 'LI', 'Lakshadweep Islands', 1),
(1492, 99, 'MP', 'Madhya Pradesh', 1),
(1493, 99, 'MA', 'Maharashtra', 1),
(1494, 99, 'MN', 'Manipur', 1),
(1495, 99, 'ME', 'Meghalaya', 1),
(1496, 99, 'MI', 'Mizoram', 1),
(1497, 99, 'NA', 'Nagaland', 1),
(1498, 99, 'OR', 'Orissa', 1),
(1499, 99, 'PO', 'Pondicherry', 1),
(1500, 99, 'PU', 'Punjab', 1),
(1501, 99, 'RA', 'Rajasthan', 1),
(1502, 99, 'SI', 'Sikkim', 1),
(1503, 99, 'TN', 'Tamil Nadu', 1),
(1504, 99, 'TR', 'Tripura', 1),
(1505, 99, 'UP', 'Uttar Pradesh', 1),
(1506, 99, 'WB', 'West Bengal', 1),

(1507, 100, 'AC', 'Aceh', 1),
(1508, 100, 'BA', 'Bali', 1),
(1509, 100, 'BT', 'Banten', 1),
(1510, 100, 'BE', 'Bengkulu', 1),
(1511, 100, 'BD', 'BoDeTaBek', 1),
(1512, 100, 'GO', 'Gorontalo', 1),
(1513, 100, 'JK', 'Jakarta Raya', 1),
(1514, 100, 'JA', 'Jambi', 1),
(1515, 100, 'JB', 'Jawa Barat', 1),
(1516, 100, 'JT', 'Jawa Tengah', 1),
(1517, 100, 'JI', 'Jawa Timur', 1),
(1518, 100, 'KB', 'Kalimantan Barat', 1),
(1519, 100, 'KS', 'Kalimantan Selatan', 1),
(1520, 100, 'KT', 'Kalimantan Tengah', 1),
(1521, 100, 'KI', 'Kalimantan Timur', 1),
(1522, 100, 'BB', 'Kepulauan Bangka Belitung', 1),
(1523, 100, 'LA', 'Lampung', 1),
(1524, 100, 'MA', 'Maluku', 1),
(1525, 100, 'MU', 'Maluku Utara', 1),
(1526, 100, 'NB', 'Nusa Tenggara Barat', 1),
(1527, 100, 'NT', 'Nusa Tenggara Timur', 1),
(1528, 100, 'PA', 'Papua', 1),
(1529, 100, 'RI', 'Riau', 1),
(1530, 100, 'SN', 'Sulawesi Selatan', 1),
(1531, 100, 'ST', 'Sulawesi Tengah', 1),
(1532, 100, 'SG', 'Sulawesi Tenggara', 1),
(1533, 100, 'SA', 'Sulawesi Utara', 1),
(1534, 100, 'SB', 'Sumatera Barat', 1),
(1535, 100, 'SS', 'Sumatera Selatan', 1),
(1536, 100, 'SU', 'Sumatera Utara', 1),
(1537, 100, 'YO', 'Yogyakarta', 1),

(1538, 101, 'TEH', 'Tehran', 1),
(1539, 101, 'QOM', 'Qom', 1),
(1540, 101, 'MKZ', 'Markazi', 1),
(1541, 101, 'QAZ', 'Qazvin', 1),
(1542, 101, 'GIL', 'Gilan', 1),
(1543, 101, 'ARD', 'Ardabil', 1),
(1544, 101, 'ZAN', 'Zanjan', 1),
(1545, 101, 'EAZ', 'East Azarbaijan', 1),
(1546, 101, 'WEZ', 'West Azarbaijan', 1),
(1547, 101, 'KRD', 'Kurdistan', 1),
(1548, 101, 'HMD', 'Hamadan', 1),
(1549, 101, 'KRM', 'Kermanshah', 1),
(1550, 101, 'ILM', 'Ilam', 1),
(1551, 101, 'LRS', 'Lorestan', 1),
(1552, 101, 'KZT', 'Khuzestan', 1),
(1553, 101, 'CMB', 'Chahar Mahaal and Bakhtiari', 1),
(1554, 101, 'KBA', 'Kohkiluyeh and Buyer Ahmad', 1),
(1555, 101, 'BSH', 'Bushehr', 1),
(1556, 101, 'FAR', 'Fars', 1),
(1557, 101, 'HRM', 'Hormozgan', 1),
(1558, 101, 'SBL', 'Sistan and Baluchistan', 1),
(1559, 101, 'KRB', 'Kerman', 1),
(1560, 101, 'YZD', 'Yazd', 1),
(1561, 101, 'EFH', 'Esfahan', 1),
(1562, 101, 'SMN', 'Semnan', 1),
(1563, 101, 'MZD', 'Mazandaran', 1),
(1564, 101, 'GLS', 'Golestan', 1),
(1565, 101, 'NKH', 'North Khorasan', 1),
(1566, 101, 'RKH', 'Razavi Khorasan', 1),
(1567, 101, 'SKH', 'South Khorasan', 1),

(1568, 102, 'BD', 'Baghdad', 1),
(1569, 102, 'SD', 'Salah ad Din', 1),
(1570, 102, 'DY', 'Diyala', 1),
(1571, 102, 'WS', 'Wasit', 1),
(1572, 102, 'MY', 'Maysan', 1),
(1573, 102, 'BA', 'Al Basrah', 1),
(1574, 102, 'DQ', 'Dhi Qar', 1),
(1575, 102, 'MU', 'Al Muthanna', 1),
(1576, 102, 'QA', 'Al Qadisyah', 1),
(1577, 102, 'BB', 'Babil', 1),
(1578, 102, 'KB', 'Al Karbala', 1),
(1579, 102, 'NJ', 'An Najaf', 1),
(1580, 102, 'AB', 'Al Anbar', 1),
(1581, 102, 'NN', 'Ninawa', 1),
(1582, 102, 'DH', 'Dahuk', 1),
(1583, 102, 'AL', 'Arbil', 1),
(1584, 102, 'TM', 'At Ta''mim', 1),
(1585, 102, 'SL', 'As Sulaymaniyah', 1),

(1586, 103, 'CA', 'Carlow', 1),
(1587, 103, 'CV', 'Cavan', 1),
(1588, 103, 'CL', 'Clare', 1),
(1589, 103, 'CO', 'Cork', 1),
(1590, 103, 'DO', 'Donegal', 1),
(1591, 103, 'DU', 'Dublin', 1),
(1592, 103, 'GA', 'Galway', 1),
(1593, 103, 'KE', 'Kerry', 1),
(1594, 103, 'KI', 'Kildare', 1),
(1595, 103, 'KL', 'Kilkenny', 1),
(1596, 103, 'LA', 'Laois', 1),
(1597, 103, 'LE', 'Leitrim', 1),
(1598, 103, 'LI', 'Limerick', 1),
(1599, 103, 'LO', 'Longford', 1),
(1600, 103, 'LU', 'Louth', 1),
(1601, 103, 'MA', 'Mayo', 1),
(1602, 103, 'ME', 'Meath', 1),
(1603, 103, 'MO', 'Monaghan', 1),
(1604, 103, 'OF', 'Offaly', 1),
(1605, 103, 'RO', 'Roscommon', 1),
(1606, 103, 'SL', 'Sligo', 1),
(1607, 103, 'TI', 'Tipperary', 1),
(1608, 103, 'WA', 'Waterford', 1),
(1609, 103, 'WE', 'Westmeath', 1),
(1610, 103, 'WX', 'Wexford', 1),
(1611, 103, 'WI', 'Wicklow', 1),

(1612, 104, 'BS', 'Be''er Sheva', 1),
(1613, 104, 'BH', 'Bika''at Hayarden', 1),
(1614, 104, 'EA', 'Eilat and Arava', 1),
(1615, 104, 'GA', 'Galil', 1),
(1616, 104, 'HA', 'Haifa', 1),
(1617, 104, 'JM', 'Jehuda Mountains', 1),
(1618, 104, 'JE', 'Jerusalem', 1),
(1619, 104, 'NE', 'Negev', 1),
(1620, 104, 'SE', 'Semaria', 1),
(1621, 104, 'SH', 'Sharon', 1),
(1622, 104, 'TA', 'Tel Aviv (Gosh Dan)', 1),

(3860, 105, 'CL', 'Caltanissetta', 1),
(3842, 105, 'AG', 'Agrigento', 1),
(3843, 105, 'AL', 'Alessandria', 1),
(3844, 105, 'AN', 'Ancona', 1),
(3845, 105, 'AO', 'Aosta', 1),
(3846, 105, 'AR', 'Arezzo', 1),
(3847, 105, 'AP', 'Ascoli Piceno', 1),
(3848, 105, 'AT', 'Asti', 1),
(3849, 105, 'AV', 'Avellino', 1),
(3850, 105, 'BA', 'Bari', 1),
(3851, 105, 'BL', 'Belluno', 1),
(3852, 105, 'BN', 'Benevento', 1),
(3853, 105, 'BG', 'Bergamo', 1),
(3854, 105, 'BI', 'Biella', 1),
(3855, 105, 'BO', 'Bologna', 1),
(3856, 105, 'BZ', 'Bolzano', 1),
(3857, 105, 'BS', 'Brescia', 1),
(3858, 105, 'BR', 'Brindisi', 1),
(3859, 105, 'CA', 'Cagliari', 1),

(1643, 106, 'CLA', 'Clarendon Parish', 1),
(1644, 106, 'HAN', 'Hanover Parish', 1),
(1645, 106, 'KIN', 'Kingston Parish', 1),
(1646, 106, 'MAN', 'Manchester Parish', 1),
(1647, 106, 'POR', 'Portland Parish', 1),
(1648, 106, 'AND', 'Saint Andrew Parish', 1),
(1649, 106, 'ANN', 'Saint Ann Parish', 1),
(1650, 106, 'CAT', 'Saint Catherine Parish', 1),
(1651, 106, 'ELI', 'Saint Elizabeth Parish', 1),
(1652, 106, 'JAM', 'Saint James Parish', 1),
(1653, 106, 'MAR', 'Saint Mary Parish', 1),
(1654, 106, 'THO', 'Saint Thomas Parish', 1),
(1655, 106, 'TRL', 'Trelawny Parish', 1),
(1656, 106, 'WML', 'Westmoreland Parish', 1),

(1657, 107, 'AI', 'Aichi', 1),
(1658, 107, 'AK', 'Akita', 1),
(1659, 107, 'AO', 'Aomori', 1),
(1660, 107, 'CH', 'Chiba', 1),
(1661, 107, 'EH', 'Ehime', 1),
(1662, 107, 'FK', 'Fukui', 1),
(1663, 107, 'FU', 'Fukuoka', 1),
(1664, 107, 'FS', 'Fukushima', 1),
(1665, 107, 'GI', 'Gifu', 1),
(1666, 107, 'GU', 'Gumma', 1),
(1667, 107, 'HI', 'Hiroshima', 1),
(1668, 107, 'HO', 'Hokkaido', 1),
(1669, 107, 'HY', 'Hyogo', 1),
(1670, 107, 'IB', 'Ibaraki', 1),
(1671, 107, 'IS', 'Ishikawa', 1),
(1672, 107, 'IW', 'Iwate', 1),
(1673, 107, 'KA', 'Kagawa', 1),
(1674, 107, 'KG', 'Kagoshima', 1),
(1675, 107, 'KN', 'Kanagawa', 1),
(1676, 107, 'KO', 'Kochi', 1),
(1677, 107, 'KU', 'Kumamoto', 1),
(1678, 107, 'KY', 'Kyoto', 1),
(1679, 107, 'MI', 'Mie', 1),
(1680, 107, 'MY', 'Miyagi', 1),
(1681, 107, 'MZ', 'Miyazaki', 1),
(1682, 107, 'NA', 'Nagano', 1),
(1683, 107, 'NG', 'Nagasaki', 1),
(1684, 107, 'NR', 'Nara', 1),
(1685, 107, 'NI', 'Niigata', 1),
(1686, 107, 'OI', 'Oita', 1),
(1687, 107, 'OK', 'Okayama', 1),
(1688, 107, 'ON', 'Okinawa', 1),
(1689, 107, 'OS', 'Osaka', 1),
(1690, 107, 'SA', 'Saga', 1),
(1691, 107, 'SI', 'Saitama', 1),
(1692, 107, 'SH', 'Shiga', 1),
(1693, 107, 'SM', 'Shimane', 1),
(1694, 107, 'SZ', 'Shizuoka', 1),
(1695, 107, 'TO', 'Tochigi', 1),
(1696, 107, 'TS', 'Tokushima', 1),
(1697, 107, 'TK', 'Tokyo', 1),
(1698, 107, 'TT', 'Tottori', 1),
(1699, 107, 'TY', 'Toyama', 1),
(1700, 107, 'WA', 'Wakayama', 1),
(1701, 107, 'YA', 'Yamagata', 1),
(1702, 107, 'YM', 'Yamaguchi', 1),
(1703, 107, 'YN', 'Yamanashi', 1),

(1704, 108, 'AM', '''Amman', 1),
(1705, 108, 'AJ', 'Ajlun', 1),
(1706, 108, 'AA', 'Al ''Aqabah', 1),
(1707, 108, 'AB', 'Al Balqa''', 1),
(1708, 108, 'AK', 'Al Karak', 1),
(1709, 108, 'AL', 'Al Mafraq', 1),
(1710, 108, 'AT', 'At Tafilah', 1),
(1711, 108, 'AZ', 'Az Zarqa''', 1),
(1712, 108, 'IR', 'Irbid', 1),
(1713, 108, 'JA', 'Jarash', 1),
(1714, 108, 'MA', 'Ma''an', 1),
(1715, 108, 'MD', 'Madaba', 1),

(1716, 109, 'AL', 'Almaty', 1),
(1717, 109, 'AC', 'Almaty City', 1),
(1718, 109, 'AM', 'Aqmola', 1),
(1719, 109, 'AQ', 'Aqtobe', 1),
(1720, 109, 'AS', 'Astana City', 1),
(1721, 109, 'AT', 'Atyrau', 1),
(1722, 109, 'BA', 'Batys Qazaqstan', 1),
(1723, 109, 'BY', 'Bayqongyr City', 1),
(1724, 109, 'MA', 'Mangghystau', 1),
(1725, 109, 'ON', 'Ongtustik Qazaqstan', 1),
(1726, 109, 'PA', 'Pavlodar', 1),
(1727, 109, 'QA', 'Qaraghandy', 1),
(1728, 109, 'QO', 'Qostanay', 1),
(1729, 109, 'QY', 'Qyzylorda', 1),
(1730, 109, 'SH', 'Shyghys Qazaqstan', 1),
(1731, 109, 'SO', 'Soltustik Qazaqstan', 1),
(1732, 109, 'ZH', 'Zhambyl', 1),

(1733, 110, 'CE', 'Central', 1),
(1734, 110, 'CO', 'Coast', 1),
(1735, 110, 'EA', 'Eastern', 1),
(1736, 110, 'NA', 'Nairobi Area', 1),
(1737, 110, 'NE', 'North Eastern', 1),
(1738, 110, 'NY', 'Nyanza', 1),
(1739, 110, 'RV', 'Rift Valley', 1),
(1740, 110, 'WE', 'Western', 1),

(1741, 111, 'AG', 'Abaiang', 1),
(1742, 111, 'AM', 'Abemama', 1),
(1743, 111, 'AK', 'Aranuka', 1),
(1744, 111, 'AO', 'Arorae', 1),
(1745, 111, 'BA', 'Banaba', 1),
(1746, 111, 'BE', 'Beru', 1),
(1747, 111, 'bT', 'Butaritari', 1),
(1748, 111, 'KA', 'Kanton', 1),
(1749, 111, 'KR', 'Kiritimati', 1),
(1750, 111, 'KU', 'Kuria', 1),
(1751, 111, 'MI', 'Maiana', 1),
(1752, 111, 'MN', 'Makin', 1),
(1753, 111, 'ME', 'Marakei', 1),
(1754, 111, 'NI', 'Nikunau', 1),
(1755, 111, 'NO', 'Nonouti', 1),
(1756, 111, 'ON', 'Onotoa', 1),
(1757, 111, 'TT', 'Tabiteuea', 1),
(1758, 111, 'TR', 'Tabuaeran', 1),
(1759, 111, 'TM', 'Tamana', 1),
(1760, 111, 'TW', 'Tarawa', 1),
(1761, 111, 'TE', 'Teraina', 1),

(1762, 112, 'CHA', 'Chagang-do', 1),
(1763, 112, 'HAB', 'Hamgyong-bukto', 1),
(1764, 112, 'HAN', 'Hamgyong-namdo', 1),
(1765, 112, 'HWB', 'Hwanghae-bukto', 1),
(1766, 112, 'HWN', 'Hwanghae-namdo', 1),
(1767, 112, 'KAN', 'Kangwon-do', 1),
(1768, 112, 'PYB', 'P''yongan-bukto', 1),
(1769, 112, 'PYN', 'P''yongan-namdo', 1),
(1770, 112, 'YAN', 'Ryanggang-do (Yanggang-do)', 1),
(1771, 112, 'NAJ', 'Rason Directly Governed City', 1),
(1772, 112, 'PYO', 'P''yongyang Special City', 1),

(1773, 113, 'CO', 'Ch''ungch''ong-bukto', 1),
(1774, 113, 'CH', 'Ch''ungch''ong-namdo', 1),
(1775, 113, 'CD', 'Cheju-do', 1),
(1776, 113, 'CB', 'Cholla-bukto', 1),
(1777, 113, 'CN', 'Cholla-namdo', 1),
(1778, 113, 'IG', 'Inch''on-gwangyoksi', 1),
(1779, 113, 'KA', 'Kangwon-do', 1),
(1780, 113, 'KG', 'Kwangju-gwangyoksi', 1),
(1781, 113, 'KD', 'Kyonggi-do', 1),
(1782, 113, 'KB', 'Kyongsang-bukto', 1),
(1783, 113, 'KN', 'Kyongsang-namdo', 1),
(1784, 113, 'PG', 'Pusan-gwangyoksi', 1),
(1785, 113, 'SO', 'Soul-t''ukpyolsi', 1),
(1786, 113, 'TA', 'Taegu-gwangyoksi', 1),
(1787, 113, 'TG', 'Taejon-gwangyoksi', 1),

(1788, 114, 'AL', 'Al ''Asimah', 1),
(1789, 114, 'AA', 'Al Ahmadi', 1),
(1790, 114, 'AF', 'Al Farwaniyah', 1),
(1791, 114, 'AJ', 'Al Jahra''', 1),
(1792, 114, 'HA', 'Hawalli', 1),

(1793, 115, 'GB', 'Bishkek', 1),
(1794, 115, 'B', 'Batken', 1),
(1795, 115, 'C', 'Chu', 1),
(1796, 115, 'J', 'Jalal-Abad', 1),
(1797, 115, 'N', 'Naryn', 1),
(1798, 115, 'O', 'Osh', 1),
(1799, 115, 'T', 'Talas', 1),
(1800, 115, 'Y', 'Ysyk-Kol', 1),

(1801, 116, 'VT', 'Vientiane', 1),
(1802, 116, 'AT', 'Attapu', 1),
(1803, 116, 'BK', 'Bokeo', 1),
(1804, 116, 'BL', 'Bolikhamxai', 1),
(1805, 116, 'CH', 'Champasak', 1),
(1806, 116, 'HO', 'Houaphan', 1),
(1807, 116, 'KH', 'Khammouan', 1),
(1808, 116, 'LM', 'Louang Namtha', 1),
(1809, 116, 'LP', 'Louangphabang', 1),
(1810, 116, 'OU', 'Oudomxai', 1),
(1811, 116, 'PH', 'Phongsali', 1),
(1812, 116, 'SL', 'Salavan', 1),
(1813, 116, 'SV', 'Savannakhet', 1),
(1814, 116, 'VI', 'Vientiane', 1),
(1815, 116, 'XA', 'Xaignabouli', 1),
(1816, 116, 'XE', 'Xekong', 1),
(1817, 116, 'XI', 'Xiangkhoang', 1),
(1818, 116, 'XN', 'Xaisomboun', 1),

(1852, 119, 'BE', 'Berea', 1),
(1853, 119, 'BB', 'Butha-Buthe', 1),
(1854, 119, 'LE', 'Leribe', 1),
(1855, 119, 'MF', 'Mafeteng', 1),
(1856, 119, 'MS', 'Maseru', 1),
(1857, 119, 'MH', 'Mohale''s Hoek', 1),
(1858, 119, 'MK', 'Mokhotlong', 1),
(1859, 119, 'QN', 'Qacha''s Nek', 1),
(1860, 119, 'QT', 'Quthing', 1),
(1861, 119, 'TT', 'Thaba-Tseka', 1),

(1862, 120, 'BI', 'Bomi', 1),
(1863, 120, 'BG', 'Bong', 1),
(1864, 120, 'GB', 'Grand Bassa', 1),
(1865, 120, 'CM', 'Grand Cape Mount', 1),
(1866, 120, 'GG', 'Grand Gedeh', 1),
(1867, 120, 'GK', 'Grand Kru', 1),
(1868, 120, 'LO', 'Lofa', 1),
(1869, 120, 'MG', 'Margibi', 1),
(1870, 120, 'ML', 'Maryland', 1),
(1871, 120, 'MS', 'Montserrado', 1),
(1872, 120, 'NB', 'Nimba', 1),
(1873, 120, 'RC', 'Rivercess', 1),
(1874, 120, 'SN', 'Sinoe', 1),

(1875, 121, 'AJ', 'Ajdabiya', 1),
(1876, 121, 'AZ', 'Al ''Aziziyah', 1),
(1877, 121, 'FA', 'Al Fatih', 1),
(1878, 121, 'JA', 'Al Jabal al Akhdar', 1),
(1879, 121, 'JU', 'Al Jufrah', 1),
(1880, 121, 'KH', 'Al Khums', 1),
(1881, 121, 'KU', 'Al Kufrah', 1),
(1882, 121, 'NK', 'An Nuqat al Khams', 1),
(1883, 121, 'AS', 'Ash Shati''', 1),
(1884, 121, 'AW', 'Awbari', 1),
(1885, 121, 'ZA', 'Az Zawiyah', 1),
(1886, 121, 'BA', 'Banghazi', 1),
(1887, 121, 'DA', 'Darnah', 1),
(1888, 121, 'GD', 'Ghadamis', 1),
(1889, 121, 'GY', 'Gharyan', 1),
(1890, 121, 'MI', 'Misratah', 1),
(1891, 121, 'MZ', 'Murzuq', 1),
(1892, 121, 'SB', 'Sabha', 1),
(1893, 121, 'SW', 'Sawfajjin', 1),
(1894, 121, 'SU', 'Surt', 1),
(1895, 121, 'TL', 'Tarabulus (Tripoli)', 1),
(1896, 121, 'TH', 'Tarhunah', 1),
(1897, 121, 'TU', 'Tubruq', 1),
(1898, 121, 'YA', 'Yafran', 1),
(1899, 121, 'ZL', 'Zlitan', 1),

(1900, 122, 'V', 'Vaduz', 1),
(1901, 122, 'A', 'Schaan', 1),
(1902, 122, 'B', 'Balzers', 1),
(1903, 122, 'N', 'Triesen', 1),
(1904, 122, 'E', 'Eschen', 1),
(1905, 122, 'M', 'Mauren', 1),
(1906, 122, 'T', 'Triesenberg', 1),
(1907, 122, 'R', 'Ruggell', 1),
(1908, 122, 'G', 'Gamprin', 1),
(1909, 122, 'L', 'Schellenberg', 1),
(1910, 122, 'P', 'Planken', 1),

(1911, 123, 'AL', 'Alytus', 1),
(1912, 123, 'KA', 'Kaunas', 1),
(1913, 123, 'KL', 'Klaipeda', 1),
(1914, 123, 'MA', 'Marijampole', 1),
(1915, 123, 'PA', 'Panevezys', 1),
(1916, 123, 'SI', 'Siauliai', 1),
(1917, 123, 'TA', 'Taurage', 1),
(1918, 123, 'TE', 'Telsiai', 1),
(1919, 123, 'UT', 'Utena', 1),
(1920, 123, 'VI', 'Vilnius', 1),

(1921, 124, 'DD', 'Diekirch', 1),
(1922, 124, 'DC', 'Clervaux', 1),
(1923, 124, 'DR', 'Redange', 1),
(1924, 124, 'DV', 'Vianden', 1),
(1925, 124, 'DW', 'Wiltz', 1),
(1926, 124, 'GG', 'Grevenmacher', 1),
(1927, 124, 'GE', 'Echternach', 1),
(1928, 124, 'GR', 'Remich', 1),
(1929, 124, 'LL', 'Luxembourg', 1),
(1930, 124, 'LC', 'Capellen', 1),
(1931, 124, 'LE', 'Esch-sur-Alzette', 1),
(1932, 124, 'LM', 'Mersch', 1),

(1933, 125, 'OLF', 'Our Lady Fatima Parish', 1),
(1934, 125, 'ANT', 'St. Anthony Parish', 1),
(1935, 125, 'LAZ', 'St. Lazarus Parish', 1),
(1936, 125, 'CAT', 'Cathedral Parish', 1),
(1937, 125, 'LAW', 'St. Lawrence Parish', 1),

(1938, 127, 'AN', 'Antananarivo', 1),
(1939, 127, 'AS', 'Antsiranana', 1),
(1940, 127, 'FN', 'Fianarantsoa', 1),
(1941, 127, 'MJ', 'Mahajanga', 1),
(1942, 127, 'TM', 'Toamasina', 1),
(1943, 127, 'TL', 'Toliara', 1),

(1944, 128, 'BLK', 'Balaka', 1),
(1945, 128, 'BLT', 'Blantyre', 1),
(1946, 128, 'CKW', 'Chikwawa', 1),
(1947, 128, 'CRD', 'Chiradzulu', 1),
(1948, 128, 'CTP', 'Chitipa', 1),
(1949, 128, 'DDZ', 'Dedza', 1),
(1950, 128, 'DWA', 'Dowa', 1),
(1951, 128, 'KRG', 'Karonga', 1),
(1952, 128, 'KSG', 'Kasungu', 1),
(1953, 128, 'LKM', 'Likoma', 1),
(1954, 128, 'LLG', 'Lilongwe', 1),
(1955, 128, 'MCG', 'Machinga', 1),
(1956, 128, 'MGC', 'Mangochi', 1),
(1957, 128, 'MCH', 'Mchinji', 1),
(1958, 128, 'MLJ', 'Mulanje', 1),
(1959, 128, 'MWZ', 'Mwanza', 1),
(1960, 128, 'MZM', 'Mzimba', 1),
(1961, 128, 'NTU', 'Ntcheu', 1),
(1962, 128, 'NKB', 'Nkhata Bay', 1),
(1963, 128, 'NKH', 'Nkhotakota', 1),
(1964, 128, 'NSJ', 'Nsanje', 1),
(1965, 128, 'NTI', 'Ntchisi', 1),
(1966, 128, 'PHL', 'Phalombe', 1),
(1967, 128, 'RMP', 'Rumphi', 1),
(1968, 128, 'SLM', 'Salima', 1),
(1969, 128, 'THY', 'Thyolo', 1),
(1970, 128, 'ZBA', 'Zomba', 1),

(1971, 129, 'JO', 'Johor', 1),
(1972, 129, 'KE', 'Kedah', 1),
(1973, 129, 'KL', 'Kelantan', 1),
(1974, 129, 'LA', 'Labuan', 1),
(1975, 129, 'ME', 'Melaka', 1),
(1976, 129, 'NS', 'Negeri Sembilan', 1),
(1977, 129, 'PA', 'Pahang', 1),
(1978, 129, 'PE', 'Perak', 1),
(1979, 129, 'PR', 'Perlis', 1),
(1980, 129, 'PP', 'Pulau Pinang', 1),
(1981, 129, 'SA', 'Sabah', 1),
(1982, 129, 'SR', 'Sarawak', 1),
(1983, 129, 'SE', 'Selangor', 1),
(1984, 129, 'TE', 'Terengganu', 1),
(1985, 129, 'WP', 'Wilayah Persekutuan', 1),

(1986, 130, 'THU', 'Thiladhunmathi Uthuru', 1),
(1987, 130, 'THD', 'Thiladhunmathi Dhekunu', 1),
(1988, 130, 'MLU', 'Miladhunmadulu Uthuru', 1),
(1989, 130, 'MLD', 'Miladhunmadulu Dhekunu', 1),
(1990, 130, 'MAU', 'Maalhosmadulu Uthuru', 1),
(1991, 130, 'MAD', 'Maalhosmadulu Dhekunu', 1),
(1992, 130, 'FAA', 'Faadhippolhu', 1),
(1993, 130, 'MAA', 'Male Atoll', 1),
(1994, 130, 'AAU', 'Ari Atoll Uthuru', 1),
(1995, 130, 'AAD', 'Ari Atoll Dheknu', 1),
(1996, 130, 'FEA', 'Felidhe Atoll', 1),
(1997, 130, 'MUA', 'Mulaku Atoll', 1),
(1998, 130, 'NAU', 'Nilandhe Atoll Uthuru', 1),
(1999, 130, 'NAD', 'Nilandhe Atoll Dhekunu', 1),
(2000, 130, 'KLH', 'Kolhumadulu', 1),
(2001, 130, 'HDH', 'Hadhdhunmathi', 1),
(2002, 130, 'HAU', 'Huvadhu Atoll Uthuru', 1),
(2003, 130, 'HAD', 'Huvadhu Atoll Dhekunu', 1),
(2004, 130, 'FMU', 'Fua Mulaku', 1),
(2005, 130, 'ADD', 'Addu', 1);

INSERT INTO `nc_zone` (`zone_id`, `country_id`, `code`, `name`, `status`) VALUES
(2006, 131, 'GA', 'Gao', 1),
(2007, 131, 'KY', 'Kayes', 1),
(2008, 131, 'KD', 'Kidal', 1),
(2009, 131, 'KL', 'Koulikoro', 1),
(2010, 131, 'MP', 'Mopti', 1),
(2011, 131, 'SG', 'Segou', 1),
(2012, 131, 'SK', 'Sikasso', 1),
(2013, 131, 'TB', 'Tombouctou', 1),
(2014, 131, 'CD', 'Bamako Capital District', 1),

(2015, 132, 'ATT', 'Attard', 1),
(2016, 132, 'BAL', 'Balzan', 1),
(2017, 132, 'BGU', 'Birgu', 1),
(2018, 132, 'BKK', 'Birkirkara', 1),
(2019, 132, 'BRZ', 'Birzebbuga', 1),
(2020, 132, 'BOR', 'Bormla', 1),
(2021, 132, 'DIN', 'Dingli', 1),
(2022, 132, 'FGU', 'Fgura', 1),
(2023, 132, 'FLO', 'Floriana', 1),
(2024, 132, 'GDJ', 'Gudja', 1),
(2025, 132, 'GZR', 'Gzira', 1),
(2026, 132, 'GRG', 'Gargur', 1),
(2027, 132, 'GXQ', 'Gaxaq', 1),
(2028, 132, 'HMR', 'Hamrun', 1),
(2029, 132, 'IKL', 'Iklin', 1),
(2030, 132, 'ISL', 'Isla', 1),
(2031, 132, 'KLK', 'Kalkara', 1),
(2032, 132, 'KRK', 'Kirkop', 1),
(2033, 132, 'LIJ', 'Lija', 1),
(2034, 132, 'LUQ', 'Luqa', 1),
(2035, 132, 'MRS', 'Marsa', 1),
(2036, 132, 'MKL', 'Marsaskala', 1),
(2037, 132, 'MXL', 'Marsaxlokk', 1),
(2038, 132, 'MDN', 'Mdina', 1),
(2039, 132, 'MEL', 'Melliea', 1),
(2040, 132, 'MGR', 'Mgarr', 1),
(2041, 132, 'MST', 'Mosta', 1),
(2042, 132, 'MQA', 'Mqabba', 1),
(2043, 132, 'MSI', 'Msida', 1),
(2044, 132, 'MTF', 'Mtarfa', 1),
(2045, 132, 'NAX', 'Naxxar', 1),
(2046, 132, 'PAO', 'Paola', 1),
(2047, 132, 'PEM', 'Pembroke', 1),
(2048, 132, 'PIE', 'Pieta', 1),
(2049, 132, 'QOR', 'Qormi', 1),
(2050, 132, 'QRE', 'Qrendi', 1),
(2051, 132, 'RAB', 'Rabat', 1),
(2052, 132, 'SAF', 'Safi', 1),
(2053, 132, 'SGI', 'San Giljan', 1),
(2054, 132, 'SLU', 'Santa Lucija', 1),
(2055, 132, 'SPB', 'San Pawl il-Bahar', 1),
(2056, 132, 'SGW', 'San Gwann', 1),
(2057, 132, 'SVE', 'Santa Venera', 1),
(2058, 132, 'SIG', 'Siggiewi', 1),
(2059, 132, 'SLM', 'Sliema', 1),
(2060, 132, 'SWQ', 'Swieqi', 1),
(2061, 132, 'TXB', 'Ta Xbiex', 1),
(2062, 132, 'TRX', 'Tarxien', 1),
(2063, 132, 'VLT', 'Valletta', 1),
(2064, 132, 'XGJ', 'Xgajra', 1),
(2065, 132, 'ZBR', 'Zabbar', 1),
(2066, 132, 'ZBG', 'Zebbug', 1),
(2067, 132, 'ZJT', 'Zejtun', 1),
(2068, 132, 'ZRQ', 'Zurrieq', 1),
(2069, 132, 'FNT', 'Fontana', 1),
(2070, 132, 'GHJ', 'Ghajnsielem', 1),
(2071, 132, 'GHR', 'Gharb', 1),
(2072, 132, 'GHS', 'Ghasri', 1),
(2073, 132, 'KRC', 'Kercem', 1),
(2074, 132, 'MUN', 'Munxar', 1),
(2075, 132, 'NAD', 'Nadur', 1),
(2076, 132, 'QAL', 'Qala', 1),
(2077, 132, 'VIC', 'Victoria', 1),
(2078, 132, 'SLA', 'San Lawrenz', 1),
(2079, 132, 'SNT', 'Sannat', 1),
(2080, 132, 'ZAG', 'Xagra', 1),
(2081, 132, 'XEW', 'Xewkija', 1),
(2082, 132, 'ZEB', 'Zebbug', 1),

(2083, 133, 'ALG', 'Ailinginae', 1),
(2084, 133, 'ALL', 'Ailinglaplap', 1),
(2085, 133, 'ALK', 'Ailuk', 1),
(2086, 133, 'ARN', 'Arno', 1),
(2087, 133, 'AUR', 'Aur', 1),
(2088, 133, 'BKR', 'Bikar', 1),
(2089, 133, 'BKN', 'Bikini', 1),
(2090, 133, 'BKK', 'Bokak', 1),
(2091, 133, 'EBN', 'Ebon', 1),
(2092, 133, 'ENT', 'Enewetak', 1),
(2093, 133, 'EKB', 'Erikub', 1),
(2094, 133, 'JBT', 'Jabat', 1),
(2095, 133, 'JLT', 'Jaluit', 1),
(2096, 133, 'JEM', 'Jemo', 1),
(2097, 133, 'KIL', 'Kili', 1),
(2098, 133, 'KWJ', 'Kwajalein', 1),
(2099, 133, 'LAE', 'Lae', 1),
(2100, 133, 'LIB', 'Lib', 1),
(2101, 133, 'LKP', 'Likiep', 1),
(2102, 133, 'MJR', 'Majuro', 1),
(2103, 133, 'MLP', 'Maloelap', 1),
(2104, 133, 'MJT', 'Mejit', 1),
(2105, 133, 'MIL', 'Mili', 1),
(2106, 133, 'NMK', 'Namorik', 1),
(2107, 133, 'NAM', 'Namu', 1),
(2108, 133, 'RGL', 'Rongelap', 1),
(2109, 133, 'RGK', 'Rongrik', 1),
(2110, 133, 'TOK', 'Toke', 1),
(2111, 133, 'UJA', 'Ujae', 1),
(2112, 133, 'UJL', 'Ujelang', 1),
(2113, 133, 'UTK', 'Utirik', 1),
(2114, 133, 'WTH', 'Wotho', 1),
(2115, 133, 'WTJ', 'Wotje', 1),

(2116, 135, 'AD', 'Adrar', 1),
(2117, 135, 'AS', 'Assaba', 1),
(2118, 135, 'BR', 'Brakna', 1),
(2119, 135, 'DN', 'Dakhlet Nouadhibou', 1),
(2120, 135, 'GO', 'Gorgol', 1),
(2121, 135, 'GM', 'Guidimaka', 1),
(2122, 135, 'HC', 'Hodh Ech Chargui', 1),
(2123, 135, 'HG', 'Hodh El Gharbi', 1),
(2124, 135, 'IN', 'Inchiri', 1),
(2125, 135, 'TA', 'Tagant', 1),
(2126, 135, 'TZ', 'Tiris Zemmour', 1),
(2127, 135, 'TR', 'Trarza', 1),
(2128, 135, 'NO', 'Nouakchott', 1),

(2129, 136, 'BR', 'Beau Bassin-Rose Hill', 1),
(2130, 136, 'CU', 'Curepipe', 1),
(2131, 136, 'PU', 'Port Louis', 1),
(2132, 136, 'QB', 'Quatre Bornes', 1),
(2133, 136, 'VP', 'Vacoas-Phoenix', 1),
(2134, 136, 'AG', 'Agalega Islands', 1),
(2135, 136, 'CC', 'Cargados Carajos Shoals (Saint Brandon Islands)', 1),
(2136, 136, 'RO', 'Rodrigues', 1),
(2137, 136, 'BL', 'Black River', 1),
(2138, 136, 'FL', 'Flacq', 1),
(2139, 136, 'GP', 'Grand Port', 1),
(2140, 136, 'MO', 'Moka', 1),
(2141, 136, 'PA', 'Pamplemousses', 1),
(2142, 136, 'PW', 'Plaines Wilhems', 1),
(2143, 136, 'PL', 'Port Louis', 1),
(2144, 136, 'RR', 'Rivi&egrave;re du Rempart', 1),
(2145, 136, 'SA', 'Savanne', 1),

(2146, 138, 'BN', 'Baja California Norte', 1),
(2147, 138, 'BS', 'Baja California Sur', 1),
(2148, 138, 'CA', 'Campeche', 1),
(2149, 138, 'CI', 'Chiapas', 1),
(2150, 138, 'CH', 'Chihuahua', 1),
(2151, 138, 'CZ', 'Coahuila de Zaragoza', 1),
(2152, 138, 'CL', 'Colima', 1),
(2153, 138, 'DF', 'Distr&iacute;to Federal', 1),
(2154, 138, 'DU', 'Durango', 1),
(2155, 138, 'GA', 'Guanajuato', 1),
(2156, 138, 'GE', 'Guerrero', 1),
(2157, 138, 'HI', 'Hidalgo', 1),
(2158, 138, 'JA', 'Jalisco', 1),
(2159, 138, 'ME', 'Mex&iacute;co', 1),
(2160, 138, 'MI', 'Michoacan de Ocampo', 1),
(2161, 138, 'MO', 'Morelos', 1),
(2162, 138, 'NA', 'Nayarit', 1),
(2163, 138, 'NL', 'Nuevo L&eacute;on', 1),
(2164, 138, 'OA', 'Oaxaca', 1),
(2165, 138, 'PU', 'Puebla', 1),
(2166, 138, 'QA', 'Queretaro de Arteaga', 1),
(2167, 138, 'QR', 'Quintana Roo', 1),
(2168, 138, 'SA', 'San Luis Potos&iacute;', 1),
(2169, 138, 'SI', 'Sinaloa', 1),
(2170, 138, 'SO', 'Sonora', 1),
(2171, 138, 'TB', 'Tabasco', 1),
(2172, 138, 'TM', 'Tamaulipas', 1),
(2173, 138, 'TL', 'Tlaxcala', 1),
(2174, 138, 'VE', 'Veracruz-Llave', 1),
(2175, 138, 'YU', 'Yucatan', 1),
(2176, 138, 'ZA', 'Zacatecas', 1),

(2177, 139, 'C', 'Chuuk', 1),
(2178, 139, 'K', 'Kosrae', 1),
(2179, 139, 'P', 'Pohnpei', 1),
(2180, 139, 'Y', 'Yap', 1),

(2181, 140, 'GA', 'Gagauzia (Utag)', 1),
(2182, 140, 'CU', 'Chisin&auml;u', 1),
(2183, 140, 'BA', 'B&auml;lti', 1),
(2184, 140, 'CA', 'Cahul', 1),
(2185, 140, 'ED', 'Edinet', 1),
(2186, 140, 'LA', 'L&auml;pusna', 1),
(2187, 140, 'OR', 'Orhei', 1),
(2188, 140, 'SO', 'Soroca', 1),
(2189, 140, 'TI', 'Tighina', 1),
(2190, 140, 'UN', 'Ungheni', 1),
(2191, 140, 'SN', 'St&icirc;nga Nistrului', 1),

(2192, 141, 'FV', 'Fontvieille', 1),
(2193, 141, 'LC', 'La Condamine', 1),
(2194, 141, 'MV', 'Monaco-Ville', 1),
(2195, 141, 'MC', 'Monte-Carlo', 1),

(2196, 142, '1', 'Ulanbaatar', 1),
(2197, 142, '035', 'Orhon', 1),
(2198, 142, '037', 'Darhan uul', 1),
(2199, 142, '039', 'Hentiy', 1),
(2200, 142, '041', 'Hovsgol', 1),
(2201, 142, '043', 'Hovd', 1),
(2202, 142, '046', 'Uvs', 1),
(2203, 142, '047', 'Tov', 1),
(2204, 142, '049', 'Selenge', 1),
(2205, 142, '051', 'Suhbaatar', 1),
(2206, 142, '053', 'Omnogovi', 1),
(2207, 142, '055', 'Ovorhangay', 1),
(2208, 142, '057', 'Dzavhan', 1),
(2209, 142, '059', 'DundgovL', 1),
(2210, 142, '061', 'Dornod', 1),
(2211, 142, '063', 'Dornogov', 1),
(2212, 142, '064', 'Govi-Sumber', 1),
(2213, 142, '065', 'Govi-Altay', 1),
(2214, 142, '067', 'Bulgan', 1),
(2215, 142, '069', 'Bayanhongor', 1),
(2216, 142, '071', 'Bayan-Olgiy', 1),
(2217, 142, '073', 'Arhangay', 1),

(2218, 143, 'A', 'Saint Anthony', 1),
(2219, 143, 'G', 'Saint Georges', 1),
(2220, 143, 'P', 'Saint Peter', 1),

(2221, 144, 'AGD', 'Agadir', 1),
(2222, 144, 'HOC', 'Al Hoceima', 1),
(2223, 144, 'AZI', 'Azilal', 1),
(2224, 144, 'BME', 'Beni Mellal', 1),
(2225, 144, 'BSL', 'Ben Slimane', 1),
(2226, 144, 'BLM', 'Boulemane', 1),
(2227, 144, 'CBL', 'Casablanca', 1),
(2228, 144, 'CHA', 'Chaouen', 1),
(2229, 144, 'EJA', 'El Jadida', 1),
(2230, 144, 'EKS', 'El Kelaa des Sraghna', 1),
(2231, 144, 'ERA', 'Er Rachidia', 1),
(2232, 144, 'ESS', 'Essaouira', 1),
(2233, 144, 'FES', 'Fes', 1),
(2234, 144, 'FIG', 'Figuig', 1),
(2235, 144, 'GLM', 'Guelmim', 1),
(2236, 144, 'IFR', 'Ifrane', 1),
(2237, 144, 'KEN', 'Kenitra', 1),
(2238, 144, 'KHM', 'Khemisset', 1),
(2239, 144, 'KHN', 'Khenifra', 1),
(2240, 144, 'KHO', 'Khouribga', 1),
(2241, 144, 'LYN', 'Laayoune', 1),
(2242, 144, 'LAR', 'Larache', 1),
(2243, 144, 'MRK', 'Marrakech', 1),
(2244, 144, 'MKN', 'Meknes', 1),
(2245, 144, 'NAD', 'Nador', 1),
(2246, 144, 'ORZ', 'Ouarzazate', 1),
(2247, 144, 'OUJ', 'Oujda', 1),
(2248, 144, 'RSA', 'Rabat-Sale', 1),
(2249, 144, 'SAF', 'Safi', 1),
(2250, 144, 'SET', 'Settat', 1),
(2251, 144, 'SKA', 'Sidi Kacem', 1),
(2252, 144, 'TGR', 'Tangier', 1),
(2253, 144, 'TAN', 'Tan-Tan', 1),
(2254, 144, 'TAO', 'Taounate', 1),
(2255, 144, 'TRD', 'Taroudannt', 1),
(2256, 144, 'TAT', 'Tata', 1),
(2257, 144, 'TAZ', 'Taza', 1),
(2258, 144, 'TET', 'Tetouan', 1),
(2259, 144, 'TIZ', 'Tiznit', 1),
(2260, 144, 'ADK', 'Ad Dakhla', 1),
(2261, 144, 'BJD', 'Boujdour', 1),
(2262, 144, 'ESM', 'Es Smara', 1),

(2263, 145, 'CD', 'Cabo Delgado', 1),
(2264, 145, 'GZ', 'Gaza', 1),
(2265, 145, 'IN', 'Inhambane', 1),
(2266, 145, 'MN', 'Manica', 1),
(2267, 145, 'MC', 'Maputo (city)', 1),
(2268, 145, 'MP', 'Maputo', 1),
(2269, 145, 'NA', 'Nampula', 1),
(2270, 145, 'NI', 'Niassa', 1),
(2271, 145, 'SO', 'Sofala', 1),
(2272, 145, 'TE', 'Tete', 1),
(2273, 145, 'ZA', 'Zambezia', 1),

(2274, 146, 'AY', 'Ayeyarwady', 1),
(2275, 146, 'BG', 'Bago', 1),
(2276, 146, 'MG', 'Magway', 1),
(2277, 146, 'MD', 'Mandalay', 1),
(2278, 146, 'SG', 'Sagaing', 1),
(2279, 146, 'TN', 'Tanintharyi', 1),
(2280, 146, 'YG', 'Yangon', 1),
(2281, 146, 'CH', 'Chin State', 1),
(2282, 146, 'KC', 'Kachin State', 1),
(2283, 146, 'KH', 'Kayah State', 1),
(2284, 146, 'KN', 'Kayin State', 1),
(2285, 146, 'MN', 'Mon State', 1),
(2286, 146, 'RK', 'Rakhine State', 1),
(2287, 146, 'SH', 'Shan State', 1),

(2288, 147, 'CA', 'Caprivi', 1),
(2289, 147, 'ER', 'Erongo', 1),
(2290, 147, 'HA', 'Hardap', 1),
(2291, 147, 'KR', 'Karas', 1),
(2292, 147, 'KV', 'Kavango', 1),
(2293, 147, 'KH', 'Khomas', 1),
(2294, 147, 'KU', 'Kunene', 1),
(2295, 147, 'OW', 'Ohangwena', 1),
(2296, 147, 'OK', 'Omaheke', 1),
(2297, 147, 'OT', 'Omusati', 1),
(2298, 147, 'ON', 'Oshana', 1),
(2299, 147, 'OO', 'Oshikoto', 1),
(2300, 147, 'OJ', 'Otjozondjupa', 1),

(2301, 148, 'AO', 'Aiwo', 1),
(2302, 148, 'AA', 'Anabar', 1),
(2303, 148, 'AT', 'Anetan', 1),
(2304, 148, 'AI', 'Anibare', 1),
(2305, 148, 'BA', 'Baiti', 1),
(2306, 148, 'BO', 'Boe', 1),
(2307, 148, 'BU', 'Buada', 1),
(2308, 148, 'DE', 'Denigomodu', 1),
(2309, 148, 'EW', 'Ewa', 1),
(2310, 148, 'IJ', 'Ijuw', 1),
(2311, 148, 'ME', 'Meneng', 1),
(2312, 148, 'NI', 'Nibok', 1),
(2313, 148, 'UA', 'Uaboe', 1),
(2314, 148, 'YA', 'Yaren', 1),

(2315, 149, 'BA', 'Bagmati', 1),
(2316, 149, 'BH', 'Bheri', 1),
(2317, 149, 'DH', 'Dhawalagiri', 1),
(2318, 149, 'GA', 'Gandaki', 1),
(2319, 149, 'JA', 'Janakpur', 1),
(2320, 149, 'KA', 'Karnali', 1),
(2321, 149, 'KO', 'Kosi', 1),
(2322, 149, 'LU', 'Lumbini', 1),
(2323, 149, 'MA', 'Mahakali', 1),
(2324, 149, 'ME', 'Mechi', 1),
(2325, 149, 'NA', 'Narayani', 1),
(2326, 149, 'RA', 'Rapti', 1),
(2327, 149, 'SA', 'Sagarmatha', 1),
(2328, 149, 'SE', 'Seti', 1),

(2329, 150, 'DR', 'Drenthe', 1),
(2330, 150, 'FL', 'Flevoland', 1),
(2331, 150, 'FR', 'Friesland', 1),
(2332, 150, 'GE', 'Gelderland', 1),
(2333, 150, 'GR', 'Groningen', 1),
(2334, 150, 'LI', 'Limburg', 1),
(2335, 150, 'NB', 'Noord-Brabant', 1),
(2336, 150, 'NH', 'Noord-Holland', 1),
(2337, 150, 'OV', 'Overijssel', 1),
(2338, 150, 'UT', 'Utrecht', 1),
(2339, 150, 'ZE', 'Zeeland', 1),
(2340, 150, 'ZH', 'Zuid-Holland', 1),

(2341, 152, 'L', 'Iles Loyaut&eacute;', 1),
(2342, 152, 'N', 'Nord', 1),
(2343, 152, 'S', 'Sud', 1),

(2344, 153, 'AUK', 'Auckland', 1),
(2345, 153, 'BOP', 'Bay of Plenty', 1),
(2346, 153, 'CAN', 'Canterbury', 1),
(2347, 153, 'COR', 'Coromandel', 1),
(2348, 153, 'GIS', 'Gisborne', 1),
(2349, 153, 'FIO', 'Fiordland', 1),
(2350, 153, 'HKB', 'Hawke''s Bay', 1),
(2351, 153, 'MBH', 'Marlborough', 1),
(2352, 153, 'MWT', 'Manawatu-Wanganui', 1),
(2353, 153, 'MCM', 'Mt Cook-Mackenzie', 1),
(2354, 153, 'NSN', 'Nelson', 1),
(2355, 153, 'NTL', 'Northland', 1),
(2356, 153, 'OTA', 'Otago', 1),
(2357, 153, 'STL', 'Southland', 1),
(2358, 153, 'TKI', 'Taranaki', 1),
(2359, 153, 'WGN', 'Wellington', 1),
(2360, 153, 'WKO', 'Waikato', 1),
(2361, 153, 'WAI', 'Wairarapa', 1),
(2362, 153, 'WTC', 'West Coast', 1),

(2363, 154, 'AN', 'Atl&aacute;ntico Norte', 1),
(2364, 154, 'AS', 'Atl&aacute;ntico Sur', 1),
(2365, 154, 'BO', 'Boaco', 1),
(2366, 154, 'CA', 'Carazo', 1),
(2367, 154, 'CI', 'Chinandega', 1),
(2368, 154, 'CO', 'Chontales', 1),
(2369, 154, 'ES', 'Esteli', 1),
(2370, 154, 'GR', 'Granada', 1),
(2371, 154, 'JI', 'Jinotega', 1),
(2372, 154, 'LE', 'Le&oacute;n', 1),
(2373, 154, 'MD', 'Madriz', 1),
(2374, 154, 'MN', 'Managua', 1),
(2375, 154, 'MS', 'Masaya', 1),
(2376, 154, 'MT', 'Matagalpa', 1),
(2377, 154, 'NS', 'Nuevo Segovia', 1),
(2378, 154, 'RS', 'Rio San Juan', 1),
(2379, 154, 'RI', 'Rivas', 1),

(2380, 155, 'AG', 'Agadez', 1),
(2381, 155, 'DF', 'Diffa', 1),
(2382, 155, 'DS', 'Dosso', 1),
(2383, 155, 'MA', 'Maradi', 1),
(2384, 155, 'NM', 'Niamey', 1),
(2385, 155, 'TH', 'Tahoua', 1),
(2386, 155, 'TL', 'Tillaberi', 1),
(2387, 155, 'ZD', 'Zinder', 1),

(2388, 156, 'AB', 'Abia', 1),
(2389, 156, 'CT', 'Abuja Federal Capital Territory', 1),
(2390, 156, 'AD', 'Adamawa', 1),
(2391, 156, 'AK', 'Akwa Ibom', 1),
(2392, 156, 'AN', 'Anambra', 1),
(2393, 156, 'BC', 'Bauchi', 1),
(2394, 156, 'BY', 'Bayelsa', 1),
(2395, 156, 'BN', 'Benue', 1),
(2396, 156, 'BO', 'Borno', 1),
(2397, 156, 'CR', 'Cross River', 1),
(2398, 156, 'DE', 'Delta', 1),
(2399, 156, 'EB', 'Ebonyi', 1),
(2400, 156, 'ED', 'Edo', 1),
(2401, 156, 'EK', 'Ekiti', 1),
(2402, 156, 'EN', 'Enugu', 1),
(2403, 156, 'GO', 'Gombe', 1),
(2404, 156, 'IM', 'Imo', 1),
(2405, 156, 'JI', 'Jigawa', 1),
(2406, 156, 'KD', 'Kaduna', 1),
(2407, 156, 'KN', 'Kano', 1),
(2408, 156, 'KT', 'Katsina', 1),
(2409, 156, 'KE', 'Kebbi', 1),
(2410, 156, 'KO', 'Kogi', 1),
(2411, 156, 'KW', 'Kwara', 1),
(2412, 156, 'LA', 'Lagos', 1),
(2413, 156, 'NA', 'Nassarawa', 1),
(2414, 156, 'NI', 'Niger', 1),
(2415, 156, 'OG', 'Ogun', 1),
(2416, 156, 'ONG', 'Ondo', 1),
(2417, 156, 'OS', 'Osun', 1),
(2418, 156, 'OY', 'Oyo', 1),
(2419, 156, 'PL', 'Plateau', 1),
(2420, 156, 'RI', 'Rivers', 1),
(2421, 156, 'SO', 'Sokoto', 1),
(2422, 156, 'TA', 'Taraba', 1),
(2423, 156, 'YO', 'Yobe', 1),
(2424, 156, 'ZA', 'Zamfara', 1),

(2425, 159, 'N', 'Northern Islands', 1),
(2426, 159, 'R', 'Rota', 1),
(2427, 159, 'S', 'Saipan', 1),
(2428, 159, 'T', 'Tinian', 1),

(2429, 160, 'AK', 'Akershus', 1),
(2430, 160, 'AA', 'Aust-Agder', 1),
(2431, 160, 'BU', 'Buskerud', 1),
(2432, 160, 'FM', 'Finnmark', 1),
(2433, 160, 'HM', 'Hedmark', 1),
(2434, 160, 'HL', 'Hordaland', 1),
(2435, 160, 'MR', 'M&oslash;re og Romdal', 1),
(2436, 160, 'NT', 'Nord-Tr&oslash;ndelag', 1),
(2437, 160, 'NL', 'Nordland', 1),
(2438, 160, 'OF', '&Oslash;stfold', 1),
(2439, 160, 'OP', 'Oppland', 1),
(2440, 160, 'OL', 'Oslo', 1),
(2441, 160, 'RL', 'Rogaland', 1),
(2442, 160, 'ST', 'S&oslash;r-Tr&oslash;ndelag', 1),
(2443, 160, 'SJ', 'Sogn og Fjordane', 1),
(2444, 160, 'SV', 'Svalbard', 1),
(2445, 160, 'TM', 'Telemark', 1),
(2446, 160, 'TR', 'Troms', 1),
(2447, 160, 'VA', 'Vest-Agder', 1),
(2448, 160, 'VF', 'Vestfold', 1),

(2449, 161, 'DA', 'Ad Dakhiliyah', 1),
(2450, 161, 'BA', 'Al Batinah', 1),
(2451, 161, 'WU', 'Al Wusta', 1),
(2452, 161, 'SH', 'Ash Sharqiyah', 1),
(2453, 161, 'ZA', 'Az Zahirah', 1),
(2454, 161, 'MA', 'Masqat', 1),
(2455, 161, 'MU', 'Musandam', 1),
(2456, 161, 'ZU', 'Zufar', 1),

(2457, 162, 'B', 'Balochistan', 1),
(2458, 162, 'T', 'Federally Administered Tribal Areas', 1),
(2459, 162, 'I', 'Islamabad Capital Territory', 1),
(2460, 162, 'N', 'North-West Frontier', 1),
(2461, 162, 'P', 'Punjab', 1),
(2462, 162, 'S', 'Sindh', 1),

(2463, 163, 'AM', 'Aimeliik', 1),
(2464, 163, 'AR', 'Airai', 1),
(2465, 163, 'AN', 'Angaur', 1),
(2466, 163, 'HA', 'Hatohobei', 1),
(2467, 163, 'KA', 'Kayangel', 1),
(2468, 163, 'KO', 'Koror', 1),
(2469, 163, 'ME', 'Melekeok', 1),
(2470, 163, 'NA', 'Ngaraard', 1),
(2471, 163, 'NG', 'Ngarchelong', 1),
(2472, 163, 'ND', 'Ngardmau', 1),
(2473, 163, 'NT', 'Ngatpang', 1),
(2474, 163, 'NC', 'Ngchesar', 1),
(2475, 163, 'NR', 'Ngeremlengui', 1),
(2476, 163, 'NW', 'Ngiwal', 1),
(2477, 163, 'PE', 'Peleliu', 1),
(2478, 163, 'SO', 'Sonsorol', 1),

(2479, 164, 'BT', 'Bocas del Toro', 1),
(2480, 164, 'CH', 'Chiriqui', 1),
(2481, 164, 'CC', 'Cocl&eacute;', 1),
(2482, 164, 'CL', 'Col&oacute;n', 1),
(2483, 164, 'DA', 'Dari&eacute;n', 1),
(2484, 164, 'HE', 'Herrera', 1),
(2485, 164, 'LS', 'Los Santos', 1),
(2486, 164, 'PA', 'Panam&aacute;', 1),
(2487, 164, 'SB', 'San Blas', 1),
(2488, 164, 'VG', 'Veraguas', 1),

(2489, 165, 'BV', 'Bougainville', 1),
(2490, 165, 'CE', 'Central', 1),
(2491, 165, 'CH', 'Chimbu', 1),
(2492, 165, 'EH', 'Eastern Highlands', 1),
(2493, 165, 'EB', 'East New Britain', 1),
(2494, 165, 'ES', 'East Sepik', 1),
(2495, 165, 'EN', 'Enga', 1),
(2496, 165, 'GU', 'Gulf', 1),
(2497, 165, 'MD', 'Madang', 1),
(2498, 165, 'MN', 'Manus', 1),
(2499, 165, 'MB', 'Milne Bay', 1),
(2500, 165, 'MR', 'Morobe', 1),
(2501, 165, 'NC', 'National Capital', 1),
(2502, 165, 'NI', 'New Ireland', 1),
(2503, 165, 'NO', 'Northern', 1),
(2504, 165, 'SA', 'Sandaun', 1),
(2505, 165, 'SH', 'Southern Highlands', 1),
(2506, 165, 'WE', 'Western', 1),
(2507, 165, 'WH', 'Western Highlands', 1),
(2508, 165, 'WB', 'West New Britain', 1),

(2509, 166, 'AG', 'Alto Paraguay', 1),
(2510, 166, 'AN', 'Alto Parana', 1),
(2511, 166, 'AM', 'Amambay', 1),
(2512, 166, 'AS', 'Asunci&oacute;n', 1),
(2513, 166, 'BO', 'Boquer&oacute;n', 1),
(2514, 166, 'CG', 'Caaguaz&uacute;', 1),
(2515, 166, 'CZ', 'Caazapa', 1),
(2516, 166, 'CN', 'Canindeyu', 1),
(2517, 166, 'CE', 'Central', 1),
(2518, 166, 'CC', 'Concepci&oacute;n', 1),
(2519, 166, 'CD', 'Cordillera', 1),
(2520, 166, 'GU', 'Guaira', 1),
(2521, 166, 'IT', 'Itap&uacute;a', 1),
(2522, 166, 'MI', 'Misiones', 1),
(2523, 166, 'NE', 'Neembucu', 1),
(2524, 166, 'PA', 'Paraguari', 1),
(2525, 166, 'PH', 'Presidente Hayes', 1),
(2526, 166, 'SP', 'San Pedro', 1),

(2527, 167, 'AM', 'Amazonas', 1),
(2528, 167, 'AN', 'Ancash', 1),
(2529, 167, 'AP', 'Apurimac', 1),
(2530, 167, 'AR', 'Arequipa', 1),
(2531, 167, 'AY', 'Ayacucho', 1),
(2532, 167, 'CJ', 'Cajamarca', 1),
(2533, 167, 'CL', 'Callao', 1),
(2534, 167, 'CU', 'Cusco', 1),
(2535, 167, 'HV', 'Huancavelica', 1),
(2536, 167, 'HO', 'Hu&aacute;nuco', 1),
(2537, 167, 'IC', 'Ica', 1),
(2538, 167, 'JU', 'Jun&iacute;n', 1),
(2539, 167, 'LD', 'La Libertad', 1),
(2540, 167, 'LY', 'Lambayeque', 1),
(2541, 167, 'LI', 'Lima', 1),
(2542, 167, 'LO', 'Loreto', 1),
(2543, 167, 'MD', 'Madre de Dios', 1),
(2544, 167, 'MO', 'Moquegua', 1),
(2545, 167, 'PA', 'Pasco', 1),
(2546, 167, 'PI', 'Piura', 1),
(2547, 167, 'PU', 'Puno', 1),
(2548, 167, 'SM', 'San Mart&iacute;n', 1),
(2549, 167, 'TA', 'Tacna', 1),
(2550, 167, 'TU', 'Tumbes', 1),
(2551, 167, 'UC', 'Ucayali', 1),

(2552, 168, 'ABR', 'Abra', 1),
(2553, 168, 'ANO', 'Agusan del Norte', 1),
(2554, 168, 'ASU', 'Agusan del Sur', 1),
(2555, 168, 'AKL', 'Aklan', 1),
(2556, 168, 'ALB', 'Albay', 1),
(2557, 168, 'ANT', 'Antique', 1),
(2558, 168, 'APY', 'Apayao', 1),
(2559, 168, 'AUR', 'Aurora', 1),
(2560, 168, 'BAS', 'Basilan', 1),
(2561, 168, 'BTA', 'Bataan', 1),
(2562, 168, 'BTE', 'Batanes', 1),
(2563, 168, 'BTG', 'Batangas', 1),
(2564, 168, 'BLR', 'Biliran', 1),
(2565, 168, 'BEN', 'Benguet', 1),
(2566, 168, 'BOL', 'Bohol', 1),
(2567, 168, 'BUK', 'Bukidnon', 1),
(2568, 168, 'BUL', 'Bulacan', 1),
(2569, 168, 'CAG', 'Cagayan', 1),
(2570, 168, 'CNO', 'Camarines Norte', 1),
(2571, 168, 'CSU', 'Camarines Sur', 1),
(2572, 168, 'CAM', 'Camiguin', 1),
(2573, 168, 'CAP', 'Capiz', 1),
(2574, 168, 'CAT', 'Catanduanes', 1),
(2575, 168, 'CAV', 'Cavite', 1),
(2576, 168, 'CEB', 'Cebu', 1),
(2577, 168, 'CMP', 'Compostela', 1),
(2578, 168, 'DNO', 'Davao del Norte', 1),
(2579, 168, 'DSU', 'Davao del Sur', 1),
(2580, 168, 'DOR', 'Davao Oriental', 1),
(2581, 168, 'ESA', 'Eastern Samar', 1),
(2582, 168, 'GUI', 'Guimaras', 1),
(2583, 168, 'IFU', 'Ifugao', 1),
(2584, 168, 'INO', 'Ilocos Norte', 1),
(2585, 168, 'ISU', 'Ilocos Sur', 1),
(2586, 168, 'ILO', 'Iloilo', 1),
(2587, 168, 'ISA', 'Isabela', 1),
(2588, 168, 'KAL', 'Kalinga', 1),
(2589, 168, 'LAG', 'Laguna', 1),
(2590, 168, 'LNO', 'Lanao del Norte', 1),
(2591, 168, 'LSU', 'Lanao del Sur', 1),
(2592, 168, 'UNI', 'La Union', 1),
(2593, 168, 'LEY', 'Leyte', 1),
(2594, 168, 'MAG', 'Maguindanao', 1),
(2595, 168, 'MRN', 'Marinduque', 1),
(2596, 168, 'MSB', 'Masbate', 1),
(2597, 168, 'MIC', 'Mindoro Occidental', 1),
(2598, 168, 'MIR', 'Mindoro Oriental', 1),
(2599, 168, 'MSC', 'Misamis Occidental', 1),
(2600, 168, 'MOR', 'Misamis Oriental', 1),
(2601, 168, 'MOP', 'Mountain', 1),
(2602, 168, 'NOC', 'Negros Occidental', 1),
(2603, 168, 'NOR', 'Negros Oriental', 1),
(2604, 168, 'NCT', 'North Cotabato', 1),
(2605, 168, 'NSM', 'Northern Samar', 1),
(2606, 168, 'NEC', 'Nueva Ecija', 1),
(2607, 168, 'NVZ', 'Nueva Vizcaya', 1),
(2608, 168, 'PLW', 'Palawan', 1),
(2609, 168, 'PMP', 'Pampanga', 1),
(2610, 168, 'PNG', 'Pangasinan', 1),
(2611, 168, 'QZN', 'Quezon', 1),
(2612, 168, 'QRN', 'Quirino', 1),
(2613, 168, 'RIZ', 'Rizal', 1),
(2614, 168, 'ROM', 'Romblon', 1),
(2615, 168, 'SMR', 'Samar', 1),
(2616, 168, 'SRG', 'Sarangani', 1),
(2617, 168, 'SQJ', 'Siquijor', 1),
(2618, 168, 'SRS', 'Sorsogon', 1),
(2619, 168, 'SCO', 'South Cotabato', 1),
(2620, 168, 'SLE', 'Southern Leyte', 1),
(2621, 168, 'SKU', 'Sultan Kudarat', 1),
(2622, 168, 'SLU', 'Sulu', 1),
(2623, 168, 'SNO', 'Surigao del Norte', 1),
(2624, 168, 'SSU', 'Surigao del Sur', 1),
(2625, 168, 'TAR', 'Tarlac', 1),
(2626, 168, 'TAW', 'Tawi-Tawi', 1),
(2627, 168, 'ZBL', 'Zambales', 1),
(2628, 168, 'ZNO', 'Zamboanga del Norte', 1),
(2629, 168, 'ZSU', 'Zamboanga del Sur', 1),
(2630, 168, 'ZSI', 'Zamboanga Sibugay', 1),

(2631, 170, 'DO', 'Dolnoslaskie', 1),
(2632, 170, 'KP', 'Kujawsko-Pomorskie', 1),
(2633, 170, 'LO', 'Lodzkie', 1),
(2634, 170, 'LL', 'Lubelskie', 1),
(2635, 170, 'LU', 'Lubuskie', 1),
(2636, 170, 'ML', 'Malopolskie', 1),
(2637, 170, 'MZ', 'Mazowieckie', 1),
(2638, 170, 'OP', 'Opolskie', 1),
(2639, 170, 'PP', 'Podkarpackie', 1),
(2640, 170, 'PL', 'Podlaskie', 1),
(2641, 170, 'PM', 'Pomorskie', 1),
(2642, 170, 'SL', 'Slaskie', 1),
(2643, 170, 'SW', 'Swietokrzyskie', 1),
(2644, 170, 'WM', 'Warminsko-Mazurskie', 1),
(2645, 170, 'WP', 'Wielkopolskie', 1),
(2646, 170, 'ZA', 'Zachodniopomorskie', 1),

(2647, 198, 'P', 'Saint Pierre', 1),
(2648, 198, 'M', 'Miquelon', 1),

(2649, 171, 'AC', 'A&ccedil;ores', 1),
(2650, 171, 'AV', 'Aveiro', 1),
(2651, 171, 'BE', 'Beja', 1),
(2652, 171, 'BR', 'Braga', 1),
(2653, 171, 'BA', 'Bragan&ccedil;a', 1),
(2654, 171, 'CB', 'Castelo Branco', 1),
(2655, 171, 'CO', 'Coimbra', 1),
(2656, 171, 'EV', '&Eacute;vora', 1),
(2657, 171, 'FA', 'Faro', 1),
(2658, 171, 'GU', 'Guarda', 1),
(2659, 171, 'LE', 'Leiria', 1),
(2660, 171, 'LI', 'Lisboa', 1),
(2661, 171, 'ME', 'Madeira', 1),
(2662, 171, 'PO', 'Portalegre', 1),
(2663, 171, 'PR', 'Porto', 1),
(2664, 171, 'SA', 'Santar&eacute;m', 1),
(2665, 171, 'SE', 'Set&uacute;bal', 1),
(2666, 171, 'VC', 'Viana do Castelo', 1),
(2667, 171, 'VR', 'Vila Real', 1),
(2668, 171, 'VI', 'Viseu', 1),

(2669, 173, 'DW', 'Ad Dawhah', 1),
(2670, 173, 'GW', 'Al Ghuwayriyah', 1),
(2671, 173, 'JM', 'Al Jumayliyah', 1),
(2672, 173, 'KR', 'Al Khawr', 1),
(2673, 173, 'WK', 'Al Wakrah', 1),
(2674, 173, 'RN', 'Ar Rayyan', 1),
(2675, 173, 'JB', 'Jarayan al Batinah', 1),
(2676, 173, 'MS', 'Madinat ash Shamal', 1),
(2677, 173, 'UD', 'Umm Sa''id', 1),
(2678, 173, 'UL', 'Umm Salal', 1),

(2679, 175, 'AB', 'Alba', 1),
(2680, 175, 'AR', 'Arad', 1),
(2681, 175, 'AG', 'Arges', 1),
(2682, 175, 'BC', 'Bacau', 1),
(2683, 175, 'BH', 'Bihor', 1),
(2684, 175, 'BN', 'Bistrita-Nasaud', 1),
(2685, 175, 'BT', 'Botosani', 1),
(2686, 175, 'BV', 'Brasov', 1),
(2687, 175, 'BR', 'Braila', 1),
(2688, 175, 'B', 'Bucuresti', 1),
(2689, 175, 'BZ', 'Buzau', 1),
(2690, 175, 'CS', 'Caras-Severin', 1),
(2691, 175, 'CL', 'Calarasi', 1),
(2692, 175, 'CJ', 'Cluj', 1),
(2693, 175, 'CT', 'Constanta', 1),
(2694, 175, 'CV', 'Covasna', 1),
(2695, 175, 'DB', 'Dimbovita', 1),
(2696, 175, 'DJ', 'Dolj', 1),
(2697, 175, 'GL', 'Galati', 1),
(2698, 175, 'GR', 'Giurgiu', 1),
(2699, 175, 'GJ', 'Gorj', 1),
(2700, 175, 'HR', 'Harghita', 1),
(2701, 175, 'HD', 'Hunedoara', 1),
(2702, 175, 'IL', 'Ialomita', 1),
(2703, 175, 'IS', 'Iasi', 1),
(2704, 175, 'IF', 'Ilfov', 1),
(2705, 175, 'MM', 'Maramures', 1),
(2706, 175, 'MH', 'Mehedinti', 1),
(2707, 175, 'MS', 'Mures', 1),
(2708, 175, 'NT', 'Neamt', 1),
(2709, 175, 'OT', 'Olt', 1),
(2710, 175, 'PH', 'Prahova', 1),
(2711, 175, 'SM', 'Satu-Mare', 1),
(2712, 175, 'SJ', 'Salaj', 1),
(2713, 175, 'SB', 'Sibiu', 1),
(2714, 175, 'SV', 'Suceava', 1),
(2715, 175, 'TR', 'Teleorman', 1),
(2716, 175, 'TM', 'Timis', 1),
(2717, 175, 'TL', 'Tulcea', 1),
(2718, 175, 'VS', 'Vaslui', 1),
(2719, 175, 'VL', 'Valcea', 1),
(2720, 175, 'VN', 'Vrancea', 1),

(2721, 176, 'AB', 'Abakan', 1),
(2722, 176, 'AG', 'Aginskoye', 1),
(2723, 176, 'AN', 'Anadyr', 1),
(2724, 176, 'AR', 'Arkahangelsk', 1),
(2725, 176, 'AS', 'Astrakhan', 1),
(2726, 176, 'BA', 'Barnaul', 1),
(2727, 176, 'BE', 'Belgorod', 1),
(2728, 176, 'BI', 'Birobidzhan', 1),
(2729, 176, 'BL', 'Blagoveshchensk', 1),
(2730, 176, 'BR', 'Bryansk', 1),
(2731, 176, 'CH', 'Cheboksary', 1),
(2732, 176, 'CL', 'Chelyabinsk', 1),
(2733, 176, 'CR', 'Cherkessk', 1),
(2734, 176, 'CI', 'Chita', 1),
(2735, 176, 'DU', 'Dudinka', 1),
(2736, 176, 'EL', 'Elista', 1),
(2738, 176, 'GA', 'Gorno-Altaysk', 1),
(2739, 176, 'GR', 'Groznyy', 1),
(2740, 176, 'IR', 'Irkutsk', 1),
(2741, 176, 'IV', 'Ivanovo', 1),
(2742, 176, 'IZ', 'Izhevsk', 1),
(2743, 176, 'KA', 'Kalinigrad', 1),
(2744, 176, 'KL', 'Kaluga', 1),
(2745, 176, 'KS', 'Kasnodar', 1),
(2746, 176, 'KZ', 'Kazan', 1),
(2747, 176, 'KE', 'Kemerovo', 1),
(2748, 176, 'KH', 'Khabarovsk', 1),
(2749, 176, 'KM', 'Khanty-Mansiysk', 1),
(2750, 176, 'KO', 'Kostroma', 1),
(2751, 176, 'KR', 'Krasnodar', 1),
(2752, 176, 'KN', 'Krasnoyarsk', 1),
(2753, 176, 'KU', 'Kudymkar', 1),
(2754, 176, 'KG', 'Kurgan', 1),
(2755, 176, 'KK', 'Kursk', 1),
(2756, 176, 'KY', 'Kyzyl', 1),
(2757, 176, 'LI', 'Lipetsk', 1),
(2758, 176, 'MA', 'Magadan', 1),
(2759, 176, 'MK', 'Makhachkala', 1),
(2760, 176, 'MY', 'Maykop', 1),
(2761, 176, 'MO', 'Moscow', 1),
(2762, 176, 'MU', 'Murmansk', 1),
(2763, 176, 'NA', 'Nalchik', 1),
(2764, 176, 'NR', 'Naryan Mar', 1),
(2765, 176, 'NZ', 'Nazran', 1),
(2766, 176, 'NI', 'Nizhniy Novgorod', 1),
(2767, 176, 'NO', 'Novgorod', 1),
(2768, 176, 'NV', 'Novosibirsk', 1),
(2769, 176, 'OM', 'Omsk', 1),
(2770, 176, 'OR', 'Orel', 1),
(2771, 176, 'OE', 'Orenburg', 1),
(2772, 176, 'PA', 'Palana', 1),
(2773, 176, 'PE', 'Penza', 1),
(2774, 176, 'PR', 'Perm', 1),
(2775, 176, 'PK', 'Petropavlovsk-Kamchatskiy', 1),
(2776, 176, 'PT', 'Petrozavodsk', 1),
(2777, 176, 'PS', 'Pskov', 1),
(2778, 176, 'RO', 'Rostov-na-Donu', 1),
(2779, 176, 'RY', 'Ryazan', 1),
(2780, 176, 'SL', 'Salekhard', 1),
(2781, 176, 'SA', 'Samara', 1),
(2782, 176, 'SR', 'Saransk', 1),
(2783, 176, 'SV', 'Saratov', 1),
(2784, 176, 'SM', 'Smolensk', 1),
(2785, 176, 'SP', 'St. Petersburg', 1),
(2786, 176, 'ST', 'Stavropol', 1),
(2787, 176, 'SY', 'Syktyvkar', 1),
(2788, 176, 'TA', 'Tambov', 1),
(2789, 176, 'TO', 'Tomsk', 1),
(2790, 176, 'TU', 'Tula', 1),
(2791, 176, 'TR', 'Tura', 1),
(2792, 176, 'TV', 'Tver', 1),
(2793, 176, 'TY', 'Tyumen', 1),
(2794, 176, 'UF', 'Ufa', 1),
(2795, 176, 'UL', 'Ul''yanovsk', 1),
(2796, 176, 'UU', 'Ulan-Ude', 1),
(2797, 176, 'US', 'Ust''-Ordynskiy', 1),
(2798, 176, 'VL', 'Vladikavkaz', 1),
(2799, 176, 'VA', 'Vladimir', 1),
(2800, 176, 'VV', 'Vladivostok', 1),
(2801, 176, 'VG', 'Volgograd', 1),
(2802, 176, 'VD', 'Vologda', 1),
(2803, 176, 'VO', 'Voronezh', 1),
(2804, 176, 'VY', 'Vyatka', 1),
(2805, 176, 'YA', 'Yakutsk', 1),
(2806, 176, 'YR', 'Yaroslavl', 1),
(2807, 176, 'YE', 'Yekaterinburg', 1),
(2808, 176, 'YO', 'Yoshkar-Ola', 1),

(2809, 177, 'BU', 'Butare', 1),
(2810, 177, 'BY', 'Byumba', 1),
(2811, 177, 'CY', 'Cyangugu', 1),
(2812, 177, 'GK', 'Gikongoro', 1),
(2813, 177, 'GS', 'Gisenyi', 1),
(2814, 177, 'GT', 'Gitarama', 1),
(2815, 177, 'KG', 'Kibungo', 1),
(2816, 177, 'KY', 'Kibuye', 1),
(2817, 177, 'KR', 'Kigali Rurale', 1),
(2818, 177, 'KV', 'Kigali-ville', 1),
(2819, 177, 'RU', 'Ruhengeri', 1),
(2820, 177, 'UM', 'Umutara', 1),

(2821, 178, 'CCN', 'Christ Church Nichola Town', 1),
(2822, 178, 'SAS', 'Saint Anne Sandy Point', 1),
(2823, 178, 'SGB', 'Saint George Basseterre', 1),
(2824, 178, 'SGG', 'Saint George Gingerland', 1),
(2825, 178, 'SJW', 'Saint James Windward', 1),
(2826, 178, 'SJC', 'Saint John Capesterre', 1),
(2827, 178, 'SJF', 'Saint John Figtree', 1),
(2828, 178, 'SMC', 'Saint Mary Cayon', 1),
(2829, 178, 'CAP', 'Saint Paul Capesterre', 1),
(2830, 178, 'CHA', 'Saint Paul Charlestown', 1),
(2831, 178, 'SPB', 'Saint Peter Basseterre', 1),
(2832, 178, 'STL', 'Saint Thomas Lowland', 1),
(2833, 178, 'STM', 'Saint Thomas Middle Island', 1),
(2834, 178, 'TPP', 'Trinity Palmetto Point', 1),

(2835, 179, 'AR', 'Anse-la-Raye', 1),
(2836, 179, 'CA', 'Castries', 1),
(2837, 179, 'CH', 'Choiseul', 1),
(2838, 179, 'DA', 'Dauphin', 1),
(2839, 179, 'DE', 'Dennery', 1),
(2840, 179, 'GI', 'Gros-Islet', 1),
(2841, 179, 'LA', 'Laborie', 1),
(2842, 179, 'MI', 'Micoud', 1),
(2843, 179, 'PR', 'Praslin', 1),
(2844, 179, 'SO', 'Soufri&egrave;re', 1),
(2845, 179, 'VF', 'Vieux-Fort', 1),

(2846, 180, 'C', 'Charlotte', 1),
(2847, 180, 'R', 'Grenadines', 1),
(2848, 180, 'A', 'Saint Andrew', 1),
(2849, 180, 'D', 'Saint David', 1),
(2850, 180, 'G', 'Saint George', 1),
(2851, 180, 'P', 'Saint Patrick', 1),

(2852, 181, 'AN', 'A''ana', 1),
(2853, 181, 'AI', 'Aiga-i-le-Tai', 1),
(2854, 181, 'AT', 'Atua', 1),
(2855, 181, 'FA', 'Fa''asaleleaga', 1),
(2856, 181, 'GE', 'Gaga''emauga', 1),
(2857, 181, 'GF', 'Gagaifomauga', 1),
(2858, 181, 'PA', 'Palauli', 1),
(2859, 181, 'SA', 'Satupa''itea', 1),
(2860, 181, 'TU', 'Tuamasaga', 1),
(2861, 181, 'VF', 'Va''a-o-Fonoti', 1),
(2862, 181, 'VS', 'Vaisigano', 1),

(2863, 182, 'AC', 'Acquaviva', 1),
(2864, 182, 'BM', 'Borgo Maggiore', 1),
(2865, 182, 'CH', 'Chiesanuova', 1),
(2866, 182, 'DO', 'Domagnano', 1),
(2867, 182, 'FA', 'Faetano', 1),
(2868, 182, 'FI', 'Fiorentino', 1),
(2869, 182, 'MO', 'Montegiardino', 1),
(2870, 182, 'SM', 'Citta di San Marino', 1),
(2871, 182, 'SE', 'Serravalle', 1),

(2872, 183, 'S', 'S&atilde;o Tom&eacute;', 1),
(2873, 183, 'P', 'Principe', 1),

(2874, 184, 'BH', 'Al Bahah', 1),
(2875, 184, 'HS', 'Al Hudud ash Shamaliyah', 1),
(2876, 184, 'JF', 'Al Jawf', 1),
(2877, 184, 'MD', 'Al Madinah', 1),
(2878, 184, 'QS', 'Al Qasim', 1),
(2879, 184, 'RD', 'Ar Riyad', 1),
(2880, 184, 'AQ', 'Ash Sharqiyah (Eastern)', 1),
(2881, 184, 'AS', '''Asir', 1),
(2882, 184, 'HL', 'Ha''il', 1),
(2883, 184, 'JZ', 'Jizan', 1),
(2884, 184, 'ML', 'Makkah', 1),
(2885, 184, 'NR', 'Najran', 1),
(2886, 184, 'TB', 'Tabuk', 1),

(2887, 185, 'DA', 'Dakar', 1),
(2888, 185, 'DI', 'Diourbel', 1),
(2889, 185, 'FA', 'Fatick', 1),
(2890, 185, 'KA', 'Kaolack', 1),
(2891, 185, 'KO', 'Kolda', 1),
(2892, 185, 'LO', 'Louga', 1),
(2893, 185, 'MA', 'Matam', 1),
(2894, 185, 'SL', 'Saint-Louis', 1),
(2895, 185, 'TA', 'Tambacounda', 1),
(2896, 185, 'TH', 'Thi&eacute;s', 1),
(2897, 185, 'ZI', 'Ziguinchor', 1),

(2898, 186, 'AP', 'Anse aux Pins', 1),
(2899, 186, 'AB', 'Anse Boileau', 1),
(2900, 186, 'AE', 'Anse Etoile', 1),
(2901, 186, 'AL', 'Anse Louis', 1),
(2902, 186, 'AR', 'Anse Royale', 1),
(2903, 186, 'BL', 'Baie Lazare', 1),
(2904, 186, 'BS', 'Baie Sainte Anne', 1),
(2905, 186, 'BV', 'Beau Vallon', 1),
(2906, 186, 'BA', 'Bel Air', 1),
(2907, 186, 'BO', 'Bel Ombre', 1),
(2908, 186, 'CA', 'Cascade', 1),
(2909, 186, 'GL', 'Glacis', 1),
(2910, 186, 'GM', 'Grand'' Anse (on Mah&eacute;)', 1),
(2911, 186, 'GP', 'Grand'' Anse (on Praslin)', 1),
(2912, 186, 'DG', 'La Digue', 1),
(2913, 186, 'RA', 'La Rivi&egrave;re Anglaise', 1),
(2914, 186, 'MB', 'Mont Buxton', 1),
(2915, 186, 'MF', 'Mont Fleuri', 1),
(2916, 186, 'PL', 'Plaisance', 1),
(2917, 186, 'PR', 'Pointe La Rue', 1),
(2918, 186, 'PG', 'Port Glaud', 1),
(2919, 186, 'SL', 'Saint Louis', 1),
(2920, 186, 'TA', 'Takamaka', 1),

(2921, 187, 'E', 'Eastern', 1),
(2922, 187, 'N', 'Northern', 1),
(2923, 187, 'S', 'Southern', 1),
(2924, 187, 'W', 'Western', 1),

(2925, 189, 'BA', 'Banskobystrick&yacute;', 1),
(2926, 189, 'BR', 'Bratislavsk&yacute;', 1),
(2927, 189, 'KO', 'Ko&scaron;ick&yacute;', 1),
(2928, 189, 'NI', 'Nitriansky', 1),
(2929, 189, 'PR', 'Pre&scaron;ovsk&yacute;', 1),
(2930, 189, 'TC', 'Tren&ccaron;iansky', 1),
(2931, 189, 'TV', 'Trnavsk&yacute;', 1),
(2932, 189, 'ZI', '&Zcaron;ilinsk&yacute;', 1),

(2933, 191, 'CE', 'Central', 1),
(2934, 191, 'CH', 'Choiseul', 1),
(2935, 191, 'GC', 'Guadalcanal', 1),
(2936, 191, 'HO', 'Honiara', 1),
(2937, 191, 'IS', 'Isabel', 1),
(2938, 191, 'MK', 'Makira', 1),
(2939, 191, 'ML', 'Malaita', 1),
(2940, 191, 'RB', 'Rennell and Bellona', 1),
(2941, 191, 'TM', 'Temotu', 1),
(2942, 191, 'WE', 'Western', 1),

(2943, 192, 'AW', 'Awdal', 1),
(2944, 192, 'BK', 'Bakool', 1),
(2945, 192, 'BN', 'Banaadir', 1),
(2946, 192, 'BR', 'Bari', 1),
(2947, 192, 'BY', 'Bay', 1),
(2948, 192, 'GA', 'Galguduud', 1),
(2949, 192, 'GE', 'Gedo', 1),
(2950, 192, 'HI', 'Hiiraan', 1),
(2951, 192, 'JD', 'Jubbada Dhexe', 1),
(2952, 192, 'JH', 'Jubbada Hoose', 1),
(2953, 192, 'MU', 'Mudug', 1),
(2954, 192, 'NU', 'Nugaal', 1),
(2955, 192, 'SA', 'Sanaag', 1),
(2956, 192, 'SD', 'Shabeellaha Dhexe', 1),
(2957, 192, 'SH', 'Shabeellaha Hoose', 1),
(2958, 192, 'SL', 'Sool', 1),
(2959, 192, 'TO', 'Togdheer', 1),
(2960, 192, 'WG', 'Woqooyi Galbeed', 1),

(2961, 193, 'EC', 'Eastern Cape', 1),
(2962, 193, 'FS', 'Free State', 1),
(2963, 193, 'GT', 'Gauteng', 1),
(2964, 193, 'KN', 'KwaZulu-Natal', 1),
(2965, 193, 'LP', 'Limpopo', 1),
(2966, 193, 'MP', 'Mpumalanga', 1),
(2967, 193, 'NW', 'North West', 1),
(2968, 193, 'NC', 'Northern Cape', 1),
(2969, 193, 'WC', 'Western Cape', 1),

(2970, 195, 'CA', 'La Coru&ntilde;a', 1),
(2971, 195, 'AL', '&Aacute;lava', 1),
(2972, 195, 'AB', 'Albacete', 1),
(2973, 195, 'AC', 'Alicante', 1),
(2974, 195, 'AM', 'Almer&iacute;a', 1),
(2975, 195, 'AS', 'Asturias', 1),
(2976, 195, 'AV', '&Aacute;vila', 1),
(2977, 195, 'BJ', 'Badajoz', 1),
(2978, 195, 'IB', 'Baleares', 1),
(2979, 195, 'BA', 'Barcelona', 1),
(2980, 195, 'BU', 'Burgos', 1),
(2981, 195, 'CC', 'C&aacute;ceres', 1),
(2982, 195, 'CZ', 'C&aacute;diz', 1),
(2983, 195, 'CT', 'Cantabria', 1),
(2984, 195, 'CL', 'Castell&oacute;n', 1),
(2986, 195, 'CR', 'Ciudad Real', 1),
(2987, 195, 'CD', 'C&oacute;rdoba', 1),
(2988, 195, 'CU', 'Cuenca', 1),
(2989, 195, 'GI', 'Girona', 1),
(2990, 195, 'GD', 'Granada', 1),
(2991, 195, 'GJ', 'Guadalajara', 1),
(2992, 195, 'GP', 'Guip&uacute;zcoa', 1),
(2993, 195, 'HL', 'Huelva', 1),
(2994, 195, 'HS', 'Huesca', 1),
(2995, 195, 'JN', 'Ja&eacute;n', 1),
(2996, 195, 'RJ', 'La Rioja', 1),
(2998, 195, 'LE', 'Le&oacute;n', 1),
(2999, 195, 'LL', 'L&eacute;rida', 1),
(3000, 195, 'LG', 'Lugo', 1),
(3001, 195, 'MD', 'Madrid', 1),
(3002, 195, 'MA', 'M&aacute;laga', 1),
(3004, 195, 'MU', 'Murc&iacute;a', 1),
(3005, 195, 'NV', 'Navarra', 1),
(3006, 195, 'OU', 'Ourense', 1),
(3007, 195, 'PL', 'Palencia', 1),
(3008, 195, 'PO', 'Pontevedra', 1),
(3009, 195, 'SL', 'Salamanca', 1),
(3011, 195, 'SG', 'Segovia', 1),
(3012, 195, 'SV', 'Sevilla', 1),
(3013, 195, 'SO', 'Soria', 1),
(3014, 195, 'TA', 'Tarragona', 1),
(3015, 195, 'TE', 'Teruel', 1),
(3016, 195, 'TO', 'Toledo', 1),
(3017, 195, 'VC', 'Valencia', 1),
(3018, 195, 'VD', 'Valladolid', 1),
(3019, 195, 'VZ', 'Vizcaya', 1),
(3020, 195, 'ZM', 'Zamora', 1),
(3021, 195, 'ZR', 'Zaragoza', 1);

INSERT INTO `nc_zone` (`zone_id`, `country_id`, `code`, `name`, `status`) VALUES
(3022, 196, 'CE', 'Central', 1),
(3023, 196, 'EA', 'Eastern', 1),
(3024, 196, 'NC', 'North Central', 1),
(3025, 196, 'NO', 'Northern', 1),
(3026, 196, 'NW', 'North Western', 1),
(3027, 196, 'SA', 'Sabaragamuwa', 1),
(3028, 196, 'SO', 'Southern', 1),
(3029, 196, 'UV', 'Uva', 1),
(3030, 196, 'WE', 'Western', 1),

(3031, 197, 'A', 'Ascension', 1),
(3032, 197, 'S', 'Saint Helena', 1),
(3033, 197, 'T', 'Tristan da Cunha', 1),

(3034, 199, 'ANL', 'A''ali an Nil', 1),
(3035, 199, 'BAM', 'Al Bahr al Ahmar', 1),
(3036, 199, 'BRT', 'Al Buhayrat', 1),
(3037, 199, 'JZR', 'Al Jazirah', 1),
(3038, 199, 'KRT', 'Al Khartum', 1),
(3039, 199, 'QDR', 'Al Qadarif', 1),
(3040, 199, 'WDH', 'Al Wahdah', 1),
(3041, 199, 'ANB', 'An Nil al Abyad', 1),
(3042, 199, 'ANZ', 'An Nil al Azraq', 1),
(3043, 199, 'ASH', 'Ash Shamaliyah', 1),
(3044, 199, 'BJA', 'Bahr al Jabal', 1),
(3045, 199, 'GIS', 'Gharb al Istiwa''iyah', 1),
(3046, 199, 'GBG', 'Gharb Bahr al Ghazal', 1),
(3047, 199, 'GDA', 'Gharb Darfur', 1),
(3048, 199, 'GKU', 'Gharb Kurdufan', 1),
(3049, 199, 'JDA', 'Janub Darfur', 1),
(3050, 199, 'JKU', 'Janub Kurdufan', 1),
(3051, 199, 'JQL', 'Junqali', 1),
(3052, 199, 'KSL', 'Kassala', 1),
(3053, 199, 'NNL', 'Nahr an Nil', 1),
(3054, 199, 'SBG', 'Shamal Bahr al Ghazal', 1),
(3055, 199, 'SDA', 'Shamal Darfur', 1),
(3056, 199, 'SKU', 'Shamal Kurdufan', 1),
(3057, 199, 'SIS', 'Sharq al Istiwa''iyah', 1),
(3058, 199, 'SNR', 'Sinnar', 1),
(3059, 199, 'WRB', 'Warab', 1),

(3060, 200, 'BR', 'Brokopondo', 1),
(3061, 200, 'CM', 'Commewijne', 1),
(3062, 200, 'CR', 'Coronie', 1),
(3063, 200, 'MA', 'Marowijne', 1),
(3064, 200, 'NI', 'Nickerie', 1),
(3065, 200, 'PA', 'Para', 1),
(3066, 200, 'PM', 'Paramaribo', 1),
(3067, 200, 'SA', 'Saramacca', 1),
(3068, 200, 'SI', 'Sipaliwini', 1),
(3069, 200, 'WA', 'Wanica', 1),

(3070, 202, 'H', 'Hhohho', 1),
(3071, 202, 'L', 'Lubombo', 1),
(3072, 202, 'M', 'Manzini', 1),
(3073, 202, 'S', 'Shishelweni', 1),

(3074, 203, 'K', 'Blekinge', 1),
(3075, 203, 'W', 'Dalarna', 1),
(3076, 203, 'X', 'G&auml;vleborg', 1),
(3077, 203, 'I', 'Gotland', 1),
(3078, 203, 'N', 'Halland', 1),
(3079, 203, 'Z', 'J&auml;mtland', 1),
(3080, 203, 'F', 'J&ouml;nk&ouml;ping', 1),
(3081, 203, 'H', 'Kalmar', 1),
(3082, 203, 'G', 'Kronoberg', 1),
(3083, 203, 'BD', 'Norrbotten', 1),
(3084, 203, 'T', '&Ouml;rebro', 1),
(3085, 203, 'E', '&Ouml;sterg&ouml;tland', 1),
(3086, 203, 'M', 'Sk&aring;ne', 1),
(3087, 203, 'D', 'S&ouml;dermanland', 1),
(3088, 203, 'AB', 'Stockholm', 1),
(3089, 203, 'C', 'Uppsala', 1),
(3090, 203, 'S', 'V&auml;rmland', 1),
(3091, 203, 'AC', 'V&auml;sterbotten', 1),
(3092, 203, 'Y', 'V&auml;sternorrland', 1),
(3093, 203, 'U', 'V&auml;stmanland', 1),
(3094, 203, 'O', 'V&auml;stra G&ouml;taland', 1),

(3095, 204, 'AG', 'Aargau', 1),
(3096, 204, 'AR', 'Appenzell Ausserrhoden', 1),
(3097, 204, 'AI', 'Appenzell Innerrhoden', 1),
(3098, 204, 'BS', 'Basel-Stadt', 1),
(3099, 204, 'BL', 'Basel-Landschaft', 1),
(3100, 204, 'BE', 'Bern', 1),
(3101, 204, 'FR', 'Fribourg', 1),
(3102, 204, 'GE', 'Gen&egrave;ve', 1),
(3103, 204, 'GL', 'Glarus', 1),
(3104, 204, 'GR', 'Graub&uuml;nden', 1),
(3105, 204, 'JU', 'Jura', 1),
(3106, 204, 'LU', 'Luzern', 1),
(3107, 204, 'NE', 'Neuch&acirc;tel', 1),
(3108, 204, 'NW', 'Nidwald', 1),
(3109, 204, 'OW', 'Obwald', 1),
(3110, 204, 'SG', 'St. Gallen', 1),
(3111, 204, 'SH', 'Schaffhausen', 1),
(3112, 204, 'SZ', 'Schwyz', 1),
(3113, 204, 'SO', 'Solothurn', 1),
(3114, 204, 'TG', 'Thurgau', 1),
(3115, 204, 'TI', 'Ticino', 1),
(3116, 204, 'UR', 'Uri', 1),
(3117, 204, 'VS', 'Valais', 1),
(3118, 204, 'VD', 'Vaud', 1),
(3119, 204, 'ZG', 'Zug', 1),
(3120, 204, 'ZH', 'Z&uuml;rich', 1),

(3121, 205, 'HA', 'Al Hasakah', 1),
(3122, 205, 'LA', 'Al Ladhiqiyah', 1),
(3123, 205, 'QU', 'Al Qunaytirah', 1),
(3124, 205, 'RQ', 'Ar Raqqah', 1),
(3125, 205, 'SU', 'As Suwayda', 1),
(3126, 205, 'DA', 'Dara', 1),
(3127, 205, 'DZ', 'Dayr az Zawr', 1),
(3128, 205, 'DI', 'Dimashq', 1),
(3129, 205, 'HL', 'Halab', 1),
(3130, 205, 'HM', 'Hamah', 1),
(3131, 205, 'HI', 'Hims', 1),
(3132, 205, 'ID', 'Idlib', 1),
(3133, 205, 'RD', 'Rif Dimashq', 1),
(3134, 205, 'TA', 'Tartus', 1),

(3135, 206, 'CH', 'Chang-hua', 1),
(3136, 206, 'CI', 'Chia-i', 1),
(3137, 206, 'HS', 'Hsin-chu', 1),
(3138, 206, 'HL', 'Hua-lien', 1),
(3139, 206, 'IL', 'I-lan', 1),
(3140, 206, 'KH', 'Kao-hsiung county', 1),
(3141, 206, 'KM', 'Kin-men', 1),
(3142, 206, 'LC', 'Lien-chiang', 1),
(3143, 206, 'ML', 'Miao-li', 1),
(3144, 206, 'NT', 'Nan-t''ou', 1),
(3145, 206, 'PH', 'P''eng-hu', 1),
(3146, 206, 'PT', 'P''ing-tung', 1),
(3147, 206, 'TG', 'T''ai-chung', 1),
(3148, 206, 'TA', 'T''ai-nan', 1),
(3149, 206, 'TP', 'T''ai-pei county', 1),
(3150, 206, 'TT', 'T''ai-tung', 1),
(3151, 206, 'TY', 'T''ao-yuan', 1),
(3152, 206, 'YL', 'Yun-lin', 1),
(3153, 206, 'CC', 'Chia-i city', 1),
(3154, 206, 'CL', 'Chi-lung', 1),
(3155, 206, 'HC', 'Hsin-chu', 1),
(3156, 206, 'TH', 'T''ai-chung', 1),
(3157, 206, 'TN', 'T''ai-nan', 1),
(3158, 206, 'KC', 'Kao-hsiung city', 1),
(3159, 206, 'TC', 'T''ai-pei city', 1),

(3160, 207, 'GB', 'Gorno-Badakhstan', 1),
(3161, 207, 'KT', 'Khatlon', 1),
(3162, 207, 'SU', 'Sughd', 1),

(3163, 208, 'AR', 'Arusha', 1),
(3164, 208, 'DS', 'Dar es Salaam', 1),
(3165, 208, 'DO', 'Dodoma', 1),
(3166, 208, 'IR', 'Iringa', 1),
(3167, 208, 'KA', 'Kagera', 1),
(3168, 208, 'KI', 'Kigoma', 1),
(3169, 208, 'KJ', 'Kilimanjaro', 1),
(3170, 208, 'LN', 'Lindi', 1),
(3171, 208, 'MY', 'Manyara', 1),
(3172, 208, 'MR', 'Mara', 1),
(3173, 208, 'MB', 'Mbeya', 1),
(3174, 208, 'MO', 'Morogoro', 1),
(3175, 208, 'MT', 'Mtwara', 1),
(3176, 208, 'MW', 'Mwanza', 1),
(3177, 208, 'PN', 'Pemba North', 1),
(3178, 208, 'PS', 'Pemba South', 1),
(3179, 208, 'PW', 'Pwani', 1),
(3180, 208, 'RK', 'Rukwa', 1),
(3181, 208, 'RV', 'Ruvuma', 1),
(3182, 208, 'SH', 'Shinyanga', 1),
(3183, 208, 'SI', 'Singida', 1),
(3184, 208, 'TB', 'Tabora', 1),
(3185, 208, 'TN', 'Tanga', 1),
(3186, 208, 'ZC', 'Zanzibar Central/South', 1),
(3187, 208, 'ZN', 'Zanzibar North', 1),
(3188, 208, 'ZU', 'Zanzibar Urban/West', 1),

(3189, 209, 'Amnat Charoen', 'Amnat Charoen', 1),
(3190, 209, 'Ang Thong', 'Ang Thong', 1),
(3191, 209, 'Ayutthaya', 'Ayutthaya', 1),
(3192, 209, 'Bangkok', 'Bangkok', 1),
(3193, 209, 'Buriram', 'Buriram', 1),
(3194, 209, 'Chachoengsao', 'Chachoengsao', 1),
(3195, 209, 'Chai Nat', 'Chai Nat', 1),
(3196, 209, 'Chaiyaphum', 'Chaiyaphum', 1),
(3197, 209, 'Chanthaburi', 'Chanthaburi', 1),
(3198, 209, 'Chiang Mai', 'Chiang Mai', 1),
(3199, 209, 'Chiang Rai', 'Chiang Rai', 1),
(3200, 209, 'Chon Buri', 'Chon Buri', 1),
(3201, 209, 'Chumphon', 'Chumphon', 1),
(3202, 209, 'Kalasin', 'Kalasin', 1),
(3203, 209, 'Kamphaeng Phet', 'Kamphaeng Phet', 1),
(3204, 209, 'Kanchanaburi', 'Kanchanaburi', 1),
(3205, 209, 'Khon Kaen', 'Khon Kaen', 1),
(3206, 209, 'Krabi', 'Krabi', 1),
(3207, 209, 'Lampang', 'Lampang', 1),
(3208, 209, 'Lamphun', 'Lamphun', 1),
(3209, 209, 'Loei', 'Loei', 1),
(3210, 209, 'Lop Buri', 'Lop Buri', 1),
(3211, 209, 'Mae Hong Son', 'Mae Hong Son', 1),
(3212, 209, 'Maha Sarakham', 'Maha Sarakham', 1),
(3213, 209, 'Mukdahan', 'Mukdahan', 1),
(3214, 209, 'Nakhon Nayok', 'Nakhon Nayok', 1),
(3215, 209, 'Nakhon Pathom', 'Nakhon Pathom', 1),
(3216, 209, 'Nakhon Phanom', 'Nakhon Phanom', 1),
(3217, 209, 'Nakhon Ratchasima', 'Nakhon Ratchasima', 1),
(3218, 209, 'Nakhon Sawan', 'Nakhon Sawan', 1),
(3219, 209, 'Nakhon Si Thammarat', 'Nakhon Si Thammarat', 1),
(3220, 209, 'Nan', 'Nan', 1),
(3221, 209, 'Narathiwat', 'Narathiwat', 1),
(3222, 209, 'Nong Bua Lamphu', 'Nong Bua Lamphu', 1),
(3223, 209, 'Nong Khai', 'Nong Khai', 1),
(3224, 209, 'Nonthaburi', 'Nonthaburi', 1),
(3225, 209, 'Pathum Thani', 'Pathum Thani', 1),
(3226, 209, 'Pattani', 'Pattani', 1),
(3227, 209, 'Phangnga', 'Phangnga', 1),
(3228, 209, 'Phatthalung', 'Phatthalung', 1),
(3229, 209, 'Phayao', 'Phayao', 1),
(3230, 209, 'Phetchabun', 'Phetchabun', 1),
(3231, 209, 'Phetchaburi', 'Phetchaburi', 1),
(3232, 209, 'Phichit', 'Phichit', 1),
(3233, 209, 'Phitsanulok', 'Phitsanulok', 1),
(3234, 209, 'Phrae', 'Phrae', 1),
(3235, 209, 'Phuket', 'Phuket', 1),
(3236, 209, 'Prachin Buri', 'Prachin Buri', 1),
(3237, 209, 'Prachuap Khiri Khan', 'Prachuap Khiri Khan', 1),
(3238, 209, 'Ranong', 'Ranong', 1),
(3239, 209, 'Ratchaburi', 'Ratchaburi', 1),
(3240, 209, 'Rayong', 'Rayong', 1),
(3241, 209, 'Roi Et', 'Roi Et', 1),
(3242, 209, 'Sa Kaeo', 'Sa Kaeo', 1),
(3243, 209, 'Sakon Nakhon', 'Sakon Nakhon', 1),
(3244, 209, 'Samut Prakan', 'Samut Prakan', 1),
(3245, 209, 'Samut Sakhon', 'Samut Sakhon', 1),
(3246, 209, 'Samut Songkhram', 'Samut Songkhram', 1),
(3247, 209, 'Sara Buri', 'Sara Buri', 1),
(3248, 209, 'Satun', 'Satun', 1),
(3249, 209, 'Sing Buri', 'Sing Buri', 1),
(3250, 209, 'Sisaket', 'Sisaket', 1),
(3251, 209, 'Songkhla', 'Songkhla', 1),
(3252, 209, 'Sukhothai', 'Sukhothai', 1),
(3253, 209, 'Suphan Buri', 'Suphan Buri', 1),
(3254, 209, 'Surat Thani', 'Surat Thani', 1),
(3255, 209, 'Surin', 'Surin', 1),
(3256, 209, 'Tak', 'Tak', 1),
(3257, 209, 'Trang', 'Trang', 1),
(3258, 209, 'Trat', 'Trat', 1),
(3259, 209, 'Ubon Ratchathani', 'Ubon Ratchathani', 1),
(3260, 209, 'Udon Thani', 'Udon Thani', 1),
(3261, 209, 'Uthai Thani', 'Uthai Thani', 1),
(3262, 209, 'Uttaradit', 'Uttaradit', 1),
(3263, 209, 'Yala', 'Yala', 1),
(3264, 209, 'Yasothon', 'Yasothon', 1),

(3265, 210, 'K', 'Kara', 1),
(3266, 210, 'P', 'Plateaux', 1),
(3267, 210, 'S', 'Savanes', 1),
(3268, 210, 'C', 'Centrale', 1),
(3269, 210, 'M', 'Maritime', 1),

(3270, 211, 'A', 'Atafu', 1),
(3271, 211, 'F', 'Fakaofo', 1),
(3272, 211, 'N', 'Nukunonu', 1),

(3273, 212, 'H', 'Ha''apai', 1),
(3274, 212, 'T', 'Tongatapu', 1),
(3275, 212, 'V', 'Vava''u', 1),

(3276, 213, 'CT', 'Couva/Tabaquite/Talparo', 1),
(3277, 213, 'DM', 'Diego Martin', 1),
(3278, 213, 'MR', 'Mayaro/Rio Claro', 1),
(3279, 213, 'PD', 'Penal/Debe', 1),
(3280, 213, 'PT', 'Princes Town', 1),
(3281, 213, 'SG', 'Sangre Grande', 1),
(3282, 213, 'SL', 'San Juan/Laventille', 1),
(3283, 213, 'SI', 'Siparia', 1),
(3284, 213, 'TP', 'Tunapuna/Piarco', 1),
(3285, 213, 'PS', 'Port of Spain', 1),
(3286, 213, 'SF', 'San Fernando', 1),
(3287, 213, 'AR', 'Arima', 1),
(3288, 213, 'PF', 'Point Fortin', 1),
(3289, 213, 'CH', 'Chaguanas', 1),
(3290, 213, 'TO', 'Tobago', 1),

(3291, 214, 'AR', 'Ariana', 1),
(3292, 214, 'BJ', 'Beja', 1),
(3293, 214, 'BA', 'Ben Arous', 1),
(3294, 214, 'BI', 'Bizerte', 1),
(3295, 214, 'GB', 'Gabes', 1),
(3296, 214, 'GF', 'Gafsa', 1),
(3297, 214, 'JE', 'Jendouba', 1),
(3298, 214, 'KR', 'Kairouan', 1),
(3299, 214, 'KS', 'Kasserine', 1),
(3300, 214, 'KB', 'Kebili', 1),
(3301, 214, 'KF', 'Kef', 1),
(3302, 214, 'MH', 'Mahdia', 1),
(3303, 214, 'MN', 'Manouba', 1),
(3304, 214, 'ME', 'Medenine', 1),
(3305, 214, 'MO', 'Monastir', 1),
(3306, 214, 'NA', 'Nabeul', 1),
(3307, 214, 'SF', 'Sfax', 1),
(3308, 214, 'SD', 'Sidi', 1),
(3309, 214, 'SL', 'Siliana', 1),
(3310, 214, 'SO', 'Sousse', 1),
(3311, 214, 'TA', 'Tataouine', 1),
(3312, 214, 'TO', 'Tozeur', 1),
(3313, 214, 'TU', 'Tunis', 1),
(3314, 214, 'ZA', 'Zaghouan', 1),

(3315, 215, 'ADA', 'Adana', 1),
(3316, 215, 'ADI', 'Adiyaman', 1),
(3317, 215, 'AFY', 'Afyonkarahisar', 1),
(3318, 215, 'AGR', 'Agri', 1),
(3319, 215, 'AKS', 'Aksaray', 1),
(3320, 215, 'AMA', 'Amasya', 1),
(3321, 215, 'ANK', 'Ankara', 1),
(3322, 215, 'ANT', 'Antalya', 1),
(3323, 215, 'ARD', 'Ardahan', 1),
(3324, 215, 'ART', 'Artvin', 1),
(3325, 215, 'AYI', 'Aydin', 1),
(3326, 215, 'BAL', 'Balikesir', 1),
(3327, 215, 'BAR', 'Bartin', 1),
(3328, 215, 'BAT', 'Batman', 1),
(3329, 215, 'BAY', 'Bayburt', 1),
(3330, 215, 'BIL', 'Bilecik', 1),
(3331, 215, 'BIN', 'Bing&ouml;l', 1),
(3332, 215, 'BIT', 'Bitlis', 1),
(3333, 215, 'BOL', 'Bolu', 1),
(3334, 215, 'BRD', 'Burdur', 1),
(3335, 215, 'BRS', 'Bursa', 1),
(3336, 215, 'CKL', '&Ccedil;anakkale', 1),
(3337, 215, 'CKR', '&Ccedil;ankiri', 1),
(3338, 215, 'COR', '&Ccedil;orum', 1),
(3339, 215, 'DEN', 'Denizli', 1),
(3340, 215, 'DIY', 'Diyarbakir', 1),
(3341, 215, 'DUZ', 'D&uuml;zce', 1),
(3342, 215, 'EDI', 'Edirne', 1),
(3343, 215, 'ELA', 'Elazig', 1),
(3344, 215, 'EZC', 'Erzincan', 1),
(3345, 215, 'EZR', 'Erzurum', 1),
(3346, 215, 'ESK', 'Eskisehir', 1),
(3347, 215, 'GAZ', 'Gaziantep', 1),
(3348, 215, 'GIR', 'Giresun', 1),
(3349, 215, 'GMS', 'G&uuml;m&uuml;shane', 1),
(3350, 215, 'HKR', 'Hakkari', 1),
(3351, 215, 'HTY', 'Hatay', 1),
(3352, 215, 'IGD', 'Igdir', 1),
(3353, 215, 'ISP', 'Isparta', 1),
(3354, 215, 'IST', 'Istanbul', 1),
(3355, 215, 'IZM', 'Izmir', 1),
(3356, 215, 'KAH', 'Kahramanmaras', 1),
(3357, 215, 'KRB', 'Karab&uuml;k', 1),
(3358, 215, 'KRM', 'Karaman', 1),
(3359, 215, 'KRS', 'Kars', 1),
(3360, 215, 'KAS', 'Kastamonu', 1),
(3361, 215, 'KAY', 'Kayseri', 1),
(3362, 215, 'KLS', 'Kilis', 1),
(3363, 215, 'KRK', 'Kirikkale', 1),
(3364, 215, 'KLR', 'Kirklareli', 1),
(3365, 215, 'KRH', 'Kirsehir', 1),
(3366, 215, 'KOC', 'Kocaeli', 1),
(3367, 215, 'KON', 'Konya', 1),
(3368, 215, 'KUT', 'K&uuml;tahya', 1),
(3369, 215, 'MAL', 'Malatya', 1),
(3370, 215, 'MAN', 'Manisa', 1),
(3371, 215, 'MAR', 'Mardin', 1),
(3372, 215, 'MER', 'Mersin', 1),
(3373, 215, 'MUG', 'Mugla', 1),
(3374, 215, 'MUS', 'Mus', 1),
(3375, 215, 'NEV', 'Nevsehir', 1),
(3376, 215, 'NIG', 'Nigde', 1),
(3377, 215, 'ORD', 'Ordu', 1),
(3378, 215, 'OSM', 'Osmaniye', 1),
(3379, 215, 'RIZ', 'Rize', 1),
(3380, 215, 'SAK', 'Sakarya', 1),
(3381, 215, 'SAM', 'Samsun', 1),
(3382, 215, 'SAN', 'Sanliurfa', 1),
(3383, 215, 'SII', 'Siirt', 1),
(3384, 215, 'SIN', 'Sinop', 1),
(3385, 215, 'SIR', 'Sirnak', 1),
(3386, 215, 'SIV', 'Sivas', 1),
(3387, 215, 'TEL', 'Tekirdag', 1),
(3388, 215, 'TOK', 'Tokat', 1),
(3389, 215, 'TRA', 'Trabzon', 1),
(3390, 215, 'TUN', 'Tunceli', 1),
(3391, 215, 'USK', 'Usak', 1),
(3392, 215, 'VAN', 'Van', 1),
(3393, 215, 'YAL', 'Yalova', 1),
(3394, 215, 'YOZ', 'Yozgat', 1),
(3395, 215, 'ZON', 'Zonguldak', 1),

(3396, 216, 'A', 'Ahal Welayaty', 1),
(3397, 216, 'B', 'Balkan Welayaty', 1),
(3398, 216, 'D', 'Dashhowuz Welayaty', 1),
(3399, 216, 'L', 'Lebap Welayaty', 1),
(3400, 216, 'M', 'Mary Welayaty', 1),

(3401, 217, 'AC', 'Ambergris Cays', 1),
(3402, 217, 'DC', 'Dellis Cay', 1),
(3403, 217, 'FC', 'French Cay', 1),
(3404, 217, 'LW', 'Little Water Cay', 1),
(3405, 217, 'RC', 'Parrot Cay', 1),
(3406, 217, 'PN', 'Pine Cay', 1),
(3407, 217, 'SL', 'Salt Cay', 1),
(3408, 217, 'GT', 'Grand Turk', 1),
(3409, 217, 'SC', 'South Caicos', 1),
(3410, 217, 'EC', 'East Caicos', 1),
(3411, 217, 'MC', 'Middle Caicos', 1),
(3412, 217, 'NC', 'North Caicos', 1),
(3413, 217, 'PR', 'Providenciales', 1),
(3414, 217, 'WC', 'West Caicos', 1),

(3415, 218, 'NMG', 'Nanumanga', 1),
(3416, 218, 'NLK', 'Niulakita', 1),
(3417, 218, 'NTO', 'Niutao', 1),
(3418, 218, 'FUN', 'Funafuti', 1),
(3419, 218, 'NME', 'Nanumea', 1),
(3420, 218, 'NUI', 'Nui', 1),
(3421, 218, 'NFT', 'Nukufetau', 1),
(3422, 218, 'NLL', 'Nukulaelae', 1),
(3423, 218, 'VAI', 'Vaitupu', 1),

(3424, 219, 'KAL', 'Kalangala', 1),
(3425, 219, 'KMP', 'Kampala', 1),
(3426, 219, 'KAY', 'Kayunga', 1),
(3427, 219, 'KIB', 'Kiboga', 1),
(3428, 219, 'LUW', 'Luwero', 1),
(3429, 219, 'MAS', 'Masaka', 1),
(3430, 219, 'MPI', 'Mpigi', 1),
(3431, 219, 'MUB', 'Mubende', 1),
(3432, 219, 'MUK', 'Mukono', 1),
(3433, 219, 'NKS', 'Nakasongola', 1),
(3434, 219, 'RAK', 'Rakai', 1),
(3435, 219, 'SEM', 'Sembabule', 1),
(3436, 219, 'WAK', 'Wakiso', 1),
(3437, 219, 'BUG', 'Bugiri', 1),
(3438, 219, 'BUS', 'Busia', 1),
(3439, 219, 'IGA', 'Iganga', 1),
(3440, 219, 'JIN', 'Jinja', 1),
(3441, 219, 'KAB', 'Kaberamaido', 1),
(3442, 219, 'KML', 'Kamuli', 1),
(3443, 219, 'KPC', 'Kapchorwa', 1),
(3444, 219, 'KTK', 'Katakwi', 1),
(3445, 219, 'KUM', 'Kumi', 1),
(3446, 219, 'MAY', 'Mayuge', 1),
(3447, 219, 'MBA', 'Mbale', 1),
(3448, 219, 'PAL', 'Pallisa', 1),
(3449, 219, 'SIR', 'Sironko', 1),
(3450, 219, 'SOR', 'Soroti', 1),
(3451, 219, 'TOR', 'Tororo', 1),
(3452, 219, 'ADJ', 'Adjumani', 1),
(3453, 219, 'APC', 'Apac', 1),
(3454, 219, 'ARU', 'Arua', 1),
(3455, 219, 'GUL', 'Gulu', 1),
(3456, 219, 'KIT', 'Kitgum', 1),
(3457, 219, 'KOT', 'Kotido', 1),
(3458, 219, 'LIR', 'Lira', 1),
(3459, 219, 'MRT', 'Moroto', 1),
(3460, 219, 'MOY', 'Moyo', 1),
(3461, 219, 'NAK', 'Nakapiripirit', 1),
(3462, 219, 'NEB', 'Nebbi', 1),
(3463, 219, 'PAD', 'Pader', 1),
(3464, 219, 'YUM', 'Yumbe', 1),
(3465, 219, 'BUN', 'Bundibugyo', 1),
(3466, 219, 'BSH', 'Bushenyi', 1),
(3467, 219, 'HOI', 'Hoima', 1),
(3468, 219, 'KBL', 'Kabale', 1),
(3469, 219, 'KAR', 'Kabarole', 1),
(3470, 219, 'KAM', 'Kamwenge', 1),
(3471, 219, 'KAN', 'Kanungu', 1),
(3472, 219, 'KAS', 'Kasese', 1),
(3473, 219, 'KBA', 'Kibaale', 1),
(3474, 219, 'KIS', 'Kisoro', 1),
(3475, 219, 'KYE', 'Kyenjojo', 1),
(3476, 219, 'MSN', 'Masindi', 1),
(3477, 219, 'MBR', 'Mbarara', 1),
(3478, 219, 'NTU', 'Ntungamo', 1),
(3479, 219, 'RUK', 'Rukungiri', 1),

(3480, 220, '71', 'Cherkas''ka Oblast''', 1),
(3481, 220, '74', 'Chernihivs''ka Oblast''', 1),
(3482, 220, '77', 'Chernivets''ka Oblast''', 1),
(3483, 220, '43', 'Crimea', 1),
(3484, 220, '12', 'Dnipropetrovs''ka Oblast''', 1),
(3485, 220, '14', 'Donets''ka Oblast''', 1),
(3486, 220, '26', 'Ivano-Frankivs''ka Oblast''', 1),
(3487, 220, '65', 'Khersons''ka Oblast''', 1),
(3488, 220, '68', 'Khmel''nyts''ka Oblast''', 1),
(3489, 220, '35', 'Kirovohrads''ka Oblast''', 1),
(3490, 220, '30', 'Kyiv', 1),
(3491, 220, '32', 'Kyivs''ka Oblast''', 1),
(3492, 220, '09', 'Luhans''ka Oblast''', 1),
(3493, 220, '46', 'L''vivs''ka Oblast''', 1),
(3494, 220, '48', 'Mykolayivs''ka Oblast''', 1),
(3495, 220, '51', 'Odes''ka Oblast''', 1),
(3496, 220, '53', 'Poltavs''ka Oblast''', 1),
(3497, 220, '56', 'Rivnens''ka Oblast''', 1),
(3498, 220, '40', 'Sevastopol''', 1),
(3499, 220, '59', 'Sums''ka Oblast''', 1),
(3500, 220, '61', 'Ternopil''s''ka Oblast''', 1),
(3501, 220, '05', 'Vinnyts''ka Oblast''', 1),
(3502, 220, '07', 'Volyns''ka Oblast''', 1),
(3503, 220, '21', 'Zakarpats''ka Oblast''', 1),
(3504, 220, '23', 'Zaporiz''ka Oblast''', 1),
(3505, 220, '18', 'Zhytomyrs''ka oblast''', 1),

(3506, 221, 'ADH', 'Abu Dhaby', 1),
(3507, 221, 'AJ', '''Ajman', 1),
(3508, 221, 'FU', 'Al Fujayrah', 1),
(3509, 221, 'SH', 'Ash Shariqah', 1),
(3510, 221, 'DU', 'Dubai', 1),
(3511, 221, 'RK', 'R''as al Khaymah', 1),
(3512, 221, 'UQ', 'Umm al Qaywayn', 1),

(3513, 222, 'ABN', 'Aberdeen', 1),
(3514, 222, 'ABNS', 'Aberdeenshire', 1),
(3515, 222, 'ANG', 'Anglesey', 1),
(3516, 222, 'AGS', 'Angus', 1),
(3517, 222, 'ARY', 'Argyll and Bute', 1),
(3518, 222, 'BEDS', 'Bedfordshire', 1),
(3519, 222, 'BERKS', 'Berkshire', 1),
(3520, 222, 'BLA', 'Blaenau Gwent', 1),
(3521, 222, 'BRI', 'Bridgend', 1),
(3522, 222, 'BSTL', 'Bristol', 1),
(3523, 222, 'BUCKS', 'Buckinghamshire', 1),
(3524, 222, 'CAE', 'Caerphilly', 1),
(3525, 222, 'CAMBS', 'Cambridgeshire', 1),
(3526, 222, 'CDF', 'Cardiff', 1),
(3527, 222, 'CARM', 'Carmarthenshire', 1),
(3528, 222, 'CDGN', 'Ceredigion', 1),
(3529, 222, 'CHES', 'Cheshire', 1),
(3530, 222, 'CLACK', 'Clackmannanshire', 1),
(3531, 222, 'CON', 'Conwy', 1),
(3532, 222, 'CORN', 'Cornwall', 1),
(3533, 222, 'DNBG', 'Denbighshire', 1),
(3534, 222, 'DERBY', 'Derbyshire', 1),
(3535, 222, 'DVN', 'Devon', 1),
(3536, 222, 'DOR', 'Dorset', 1),
(3537, 222, 'DGL', 'Dumfries and Galloway', 1),
(3538, 222, 'DUND', 'Dundee', 1),
(3539, 222, 'DHM', 'Durham', 1),
(3540, 222, 'ARYE', 'East Ayrshire', 1),
(3541, 222, 'DUNBE', 'East Dunbartonshire', 1),
(3542, 222, 'LOTE', 'East Lothian', 1),
(3543, 222, 'RENE', 'East Renfrewshire', 1),
(3544, 222, 'ERYS', 'East Riding of Yorkshire', 1),
(3545, 222, 'SXE', 'East Sussex', 1),
(3546, 222, 'EDIN', 'Edinburgh', 1),
(3547, 222, 'ESX', 'Essex', 1),
(3548, 222, 'FALK', 'Falkirk', 1),
(3549, 222, 'FFE', 'Fife', 1),
(3550, 222, 'FLINT', 'Flintshire', 1),
(3551, 222, 'GLAS', 'Glasgow', 1),
(3552, 222, 'GLOS', 'Gloucestershire', 1),
(3553, 222, 'LDN', 'London', 1),
(3554, 222, 'MCH', 'Greater Manchester', 1),
(3555, 222, 'GDD', 'Gwynedd', 1),
(3556, 222, 'HANTS', 'Hampshire', 1),
(3557, 222, 'HWR', 'Herefordshire', 1),
(3558, 222, 'HERTS', 'Hertfordshire', 1),
(3559, 222, 'HLD', 'Highlands', 1),
(3560, 222, 'IVER', 'Inverclyde', 1),
(3561, 222, 'IOW', 'Isle of Wight', 1),
(3562, 222, 'KNT', 'Kent', 1),
(3563, 222, 'LANCS', 'Lancashire', 1),
(3564, 222, 'LEICS', 'Leicestershire', 1),
(3565, 222, 'LINCS', 'Lincolnshire', 1),
(3566, 222, 'MSY', 'Merseyside', 1),
(3567, 222, 'MERT', 'Merthyr Tydfil', 1),
(3568, 222, 'MLOT', 'Midlothian', 1),
(3569, 222, 'MMOUTH', 'Monmouthshire', 1),
(3570, 222, 'MORAY', 'Moray', 1),
(3571, 222, 'NPRTAL', 'Neath Port Talbot', 1),
(3572, 222, 'NEWPT', 'Newport', 1),
(3573, 222, 'NOR', 'Norfolk', 1),
(3574, 222, 'ARYN', 'North Ayrshire', 1),
(3575, 222, 'LANN', 'North Lanarkshire', 1),
(3576, 222, 'YSN', 'North Yorkshire', 1),
(3577, 222, 'NHM', 'Northamptonshire', 1),
(3578, 222, 'NLD', 'Northumberland', 1),
(3579, 222, 'NOT', 'Nottinghamshire', 1),
(3580, 222, 'ORK', 'Orkney Islands', 1),
(3581, 222, 'OFE', 'Oxfordshire', 1),
(3582, 222, 'PEM', 'Pembrokeshire', 1),
(3583, 222, 'PERTH', 'Perth and Kinross', 1),
(3584, 222, 'PWS', 'Powys', 1),
(3585, 222, 'REN', 'Renfrewshire', 1),
(3586, 222, 'RHON', 'Rhondda Cynon Taff', 1),
(3587, 222, 'RUT', 'Rutland', 1),
(3588, 222, 'BOR', 'Scottish Borders', 1),
(3589, 222, 'SHET', 'Shetland Islands', 1),
(3590, 222, 'SPE', 'Shropshire', 1),
(3591, 222, 'SOM', 'Somerset', 1),
(3592, 222, 'ARYS', 'South Ayrshire', 1),
(3593, 222, 'LANS', 'South Lanarkshire', 1),
(3594, 222, 'YSS', 'South Yorkshire', 1),
(3595, 222, 'SFD', 'Staffordshire', 1),
(3596, 222, 'STIR', 'Stirling', 1),
(3597, 222, 'SFK', 'Suffolk', 1),
(3598, 222, 'SRY', 'Surrey', 1),
(3599, 222, 'SWAN', 'Swansea', 1),
(3600, 222, 'TORF', 'Torfaen', 1),
(3601, 222, 'TWR', 'Tyne and Wear', 1),
(3602, 222, 'VGLAM', 'Vale of Glamorgan', 1),
(3603, 222, 'WARKS', 'Warwickshire', 1),
(3604, 222, 'WDUN', 'West Dunbartonshire', 1),
(3605, 222, 'WLOT', 'West Lothian', 1),
(3606, 222, 'WMD', 'West Midlands', 1),
(3607, 222, 'SXW', 'West Sussex', 1),
(3608, 222, 'YSW', 'West Yorkshire', 1),
(3609, 222, 'WIL', 'Western Isles', 1),
(3610, 222, 'WLT', 'Wiltshire', 1),
(3611, 222, 'WORCS', 'Worcestershire', 1),
(3612, 222, 'WRX', 'Wrexham', 1),

(3613, 223, 'AL', 'Alabama', 1),
(3614, 223, 'AK', 'Alaska', 1),
(3615, 223, 'AS', 'American Samoa', 1),
(3616, 223, 'AZ', 'Arizona', 1),
(3617, 223, 'AR', 'Arkansas', 1),
(3618, 223, 'AF', 'Armed Forces Africa', 1),
(3619, 223, 'AA', 'Armed Forces Americas', 1),
(3620, 223, 'AC', 'Armed Forces Canada', 1),
(3621, 223, 'AE', 'Armed Forces Europe', 1),
(3622, 223, 'AM', 'Armed Forces Middle East', 1),
(3623, 223, 'AP', 'Armed Forces Pacific', 1),
(3624, 223, 'CA', 'California', 1),
(3625, 223, 'CO', 'Colorado', 1),
(3626, 223, 'CT', 'Connecticut', 1),
(3627, 223, 'DE', 'Delaware', 1),
(3628, 223, 'DC', 'District of Columbia', 1),
(3629, 223, 'FM', 'Federated States Of Micronesia', 1),
(3630, 223, 'FL', 'Florida', 1),
(3631, 223, 'GA', 'Georgia', 1),
(3632, 223, 'GU', 'Guam', 1),
(3633, 223, 'HI', 'Hawaii', 1),
(3634, 223, 'ID', 'Idaho', 1),
(3635, 223, 'IL', 'Illinois', 1),
(3636, 223, 'IN', 'Indiana', 1),
(3637, 223, 'IA', 'Iowa', 1),
(3638, 223, 'KS', 'Kansas', 1),
(3639, 223, 'KY', 'Kentucky', 1),
(3640, 223, 'LA', 'Louisiana', 1),
(3641, 223, 'ME', 'Maine', 1),
(3642, 223, 'MH', 'Marshall Islands', 1),
(3643, 223, 'MD', 'Maryland', 1),
(3644, 223, 'MA', 'Massachusetts', 1),
(3645, 223, 'MI', 'Michigan', 1),
(3646, 223, 'MN', 'Minnesota', 1),
(3647, 223, 'MS', 'Mississippi', 1),
(3648, 223, 'MO', 'Missouri', 1),
(3649, 223, 'MT', 'Montana', 1),
(3650, 223, 'NE', 'Nebraska', 1),
(3651, 223, 'NV', 'Nevada', 1),
(3652, 223, 'NH', 'New Hampshire', 1),
(3653, 223, 'NJ', 'New Jersey', 1),
(3654, 223, 'NM', 'New Mexico', 1),
(3655, 223, 'NY', 'New York', 1),
(3656, 223, 'NC', 'North Carolina', 1),
(3657, 223, 'ND', 'North Dakota', 1),
(3658, 223, 'MP', 'Northern Mariana Islands', 1),
(3659, 223, 'OH', 'Ohio', 1),
(3660, 223, 'OK', 'Oklahoma', 1),
(3661, 223, 'OR', 'Oregon', 1),
(3662, 223, 'PW', 'Palau', 1),
(3663, 223, 'PA', 'Pennsylvania', 1),
(3664, 223, 'PR', 'Puerto Rico', 1),
(3665, 223, 'RI', 'Rhode Island', 1),
(3666, 223, 'SC', 'South Carolina', 1),
(3667, 223, 'SD', 'South Dakota', 1),
(3668, 223, 'TN', 'Tennessee', 1),
(3669, 223, 'TX', 'Texas', 1),
(3670, 223, 'UT', 'Utah', 1),
(3671, 223, 'VT', 'Vermont', 1),
(3672, 223, 'VI', 'Virgin Islands', 1),
(3673, 223, 'VA', 'Virginia', 1),
(3674, 223, 'WA', 'Washington', 1),
(3675, 223, 'WV', 'West Virginia', 1),
(3676, 223, 'WI', 'Wisconsin', 1),
(3677, 223, 'WY', 'Wyoming', 1),

(3678, 224, 'BI', 'Baker Island', 1),
(3679, 224, 'HI', 'Howland Island', 1),
(3680, 224, 'JI', 'Jarvis Island', 1),
(3681, 224, 'JA', 'Johnston Atoll', 1),
(3682, 224, 'KR', 'Kingman Reef', 1),
(3683, 224, 'MA', 'Midway Atoll', 1),
(3684, 224, 'NI', 'Navassa Island', 1),
(3685, 224, 'PA', 'Palmyra Atoll', 1),
(3686, 224, 'WI', 'Wake Island', 1),

(3687, 225, 'AR', 'Artigas', 1),
(3688, 225, 'CA', 'Canelones', 1),
(3689, 225, 'CL', 'Cerro Largo', 1),
(3690, 225, 'CO', 'Colonia', 1),
(3691, 225, 'DU', 'Durazno', 1),
(3692, 225, 'FS', 'Flores', 1),
(3693, 225, 'FA', 'Florida', 1),
(3694, 225, 'LA', 'Lavalleja', 1),
(3695, 225, 'MA', 'Maldonado', 1),
(3696, 225, 'MO', 'Montevideo', 1),
(3697, 225, 'PA', 'Paysand&uacute;', 1),
(3698, 225, 'RN', 'R&iacute;o Negro', 1),
(3699, 225, 'RV', 'Rivera', 1),
(3700, 225, 'RO', 'Rocha', 1),
(3701, 225, 'SL', 'Salto', 1),
(3702, 225, 'SJ', 'San Jos&eacute;', 1),
(3703, 225, 'SO', 'Soriano', 1),
(3704, 225, 'TA', 'Tacuaremb&oacute;', 1),
(3705, 225, 'TT', 'Treinta y Tres', 1),

(3706, 226, 'AN', 'Andijon', 1),
(3707, 226, 'BU', 'Bukhoro', 1),
(3708, 226, 'FA', 'Farghona', 1),
(3709, 226, 'JI', 'Jizzakh', 1),
(3710, 226, 'NG', 'Namangan', 1),
(3711, 226, 'NW', 'Nawaoiy', 1),
(3712, 226, 'QA', 'Qashqadaryo', 1),
(3713, 226, 'QR', 'Qaraqalpoghiston Republikasi', 1),
(3714, 226, 'SA', 'Samarqand', 1),
(3715, 226, 'SI', 'Sirdaryo', 1),
(3716, 226, 'SU', 'Surkhondaryo', 1),
(3717, 226, 'TK', 'Tashkent City', 1),
(3718, 226, 'TO', 'Toshkent Region', 1),
(3719, 226, 'XO', 'Khorazm', 1),

(3720, 227, 'MA', 'Malampa', 1),
(3721, 227, 'PE', 'Penama', 1),
(3722, 227, 'SA', 'Sanma', 1),
(3723, 227, 'SH', 'Shefa', 1),
(3724, 227, 'TA', 'Tafea', 1),
(3725, 227, 'TO', 'Torba', 1),

(3726, 229, 'AM', 'Amazonas', 1),
(3727, 229, 'AN', 'Anzo&aacute;tegui', 1),
(3728, 229, 'AP', 'Apure', 1),
(3729, 229, 'AR', 'Aragua', 1),
(3730, 229, 'BA', 'Barinas', 1),
(3731, 229, 'BO', 'Bol&iacute;var', 1),
(3732, 229, 'CA', 'Carabobo', 1),
(3733, 229, 'CO', 'Cojedes', 1),
(3734, 229, 'DA', 'Delta Amacuro', 1),
(3735, 229, 'DF', 'Dependenc&iacute;as Federales', 1),
(3736, 229, 'DI', 'Distrito Federal', 1),
(3737, 229, 'FA', 'Falc&oacute;n', 1),
(3738, 229, 'GU', 'Gu&aacute;rico', 1),
(3739, 229, 'LA', 'Lara', 1),
(3740, 229, 'ME', 'Merida', 1),
(3741, 229, 'MI', 'Miranda', 1),
(3742, 229, 'MO', 'Monagas', 1),
(3743, 229, 'NE', 'Nueva Esparta', 1),
(3744, 229, 'PO', 'Portuguesa', 1),
(3745, 229, 'SU', 'Sucre', 1),
(3746, 229, 'TA', 'Tachira', 1),
(3747, 229, 'TR', 'Trujillo', 1),
(3748, 229, 'VA', 'Vargas', 1),
(3749, 229, 'YA', 'Yaracuy', 1),
(3750, 229, 'ZU', 'Zulia', 1),

(3751, 230, 'AG', 'An Giang', 1),
(3752, 230, 'BG', 'Bac Giang', 1),
(3753, 230, 'BK', 'Bac Kan', 1),
(3754, 230, 'BL', 'Bac Lieu', 1),
(3755, 230, 'BC', 'Bac Ninh', 1),
(3756, 230, 'BR', 'Ba Ria-Vung Tau', 1),
(3757, 230, 'BN', 'Ben Tre', 1),
(3758, 230, 'BH', 'Binh Dinh', 1),
(3759, 230, 'BU', 'Binh Duong', 1),
(3760, 230, 'BP', 'Binh Phuoc', 1),
(3761, 230, 'BT', 'Binh Thuan', 1),
(3762, 230, 'CM', 'Ca Mau', 1),
(3763, 230, 'CT', 'Can Tho', 1),
(3764, 230, 'CB', 'Cao Bang', 1),
(3765, 230, 'DL', 'Dak Lak', 1),
(3766, 230, 'DG', 'Dak Nong', 1),
(3767, 230, 'DN', 'Da Nang', 1),
(3768, 230, 'DB', 'Dien Bien', 1),
(3769, 230, 'DI', 'Dong Nai', 1),
(3770, 230, 'DT', 'Dong Thap', 1),
(3771, 230, 'GL', 'Gia Lai', 1),
(3772, 230, 'HG', 'Ha Giang', 1),
(3773, 230, 'HD', 'Hai Duong', 1),
(3774, 230, 'HP', 'Hai Phong', 1),
(3775, 230, 'HM', 'Ha Nam', 1),
(3776, 230, 'HI', 'Ha Noi', 1),
(3777, 230, 'HT', 'Ha Tay', 1),
(3778, 230, 'HH', 'Ha Tinh', 1),
(3779, 230, 'HB', 'Hoa Binh', 1),
(3780, 230, 'HC', 'Ho Chi Minh City', 1),
(3781, 230, 'HU', 'Hau Giang', 1),
(3782, 230, 'HY', 'Hung Yen', 1),

(3783, 232, 'C', 'Saint Croix', 1),
(3784, 232, 'J', 'Saint John', 1),
(3785, 232, 'T', 'Saint Thomas', 1),

(3786, 233, 'A', 'Alo', 1),
(3787, 233, 'S', 'Sigave', 1),
(3788, 233, 'W', 'Wallis', 1),

(3789, 235, 'AB', 'Abyan', 1),
(3790, 235, 'AD', 'Adan', 1),
(3791, 235, 'AM', 'Amran', 1),
(3792, 235, 'BA', 'Al Bayda', 1),
(3793, 235, 'DA', 'Ad Dali', 1),
(3794, 235, 'DH', 'Dhamar', 1),
(3795, 235, 'HD', 'Hadramawt', 1),
(3796, 235, 'HJ', 'Hajjah', 1),
(3797, 235, 'HU', 'Al Hudaydah', 1),
(3798, 235, 'IB', 'Ibb', 1),
(3799, 235, 'JA', 'Al Jawf', 1),
(3800, 235, 'LA', 'Lahij', 1),
(3801, 235, 'MA', 'Ma''rib', 1),
(3802, 235, 'MR', 'Al Mahrah', 1),
(3803, 235, 'MW', 'Al Mahwit', 1),
(3804, 235, 'SD', 'Sa''dah', 1),
(3805, 235, 'SN', 'San''a', 1),
(3806, 235, 'SH', 'Shabwah', 1),
(3807, 235, 'TA', 'Ta''izz', 1),

(3812, 237, 'BC', 'Bas-Congo', 1),
(3813, 237, 'BN', 'Bandundu', 1),
(3814, 237, 'EQ', 'Equateur', 1),
(3815, 237, 'KA', 'Katanga', 1),
(3816, 237, 'KE', 'Kasai-Oriental', 1),
(3817, 237, 'KN', 'Kinshasa', 1),
(3818, 237, 'KW', 'Kasai-Occidental', 1),
(3819, 237, 'MA', 'Maniema', 1),
(3820, 237, 'NK', 'Nord-Kivu', 1),
(3821, 237, 'OR', 'Orientale', 1),
(3822, 237, 'SK', 'Sud-Kivu', 1),

(3823, 238, 'CE', 'Central', 1),
(3824, 238, 'CB', 'Copperbelt', 1),
(3825, 238, 'EA', 'Eastern', 1),
(3826, 238, 'LP', 'Luapula', 1),
(3827, 238, 'LK', 'Lusaka', 1),
(3828, 238, 'NO', 'Northern', 1),
(3829, 238, 'NW', 'North-Western', 1),
(3830, 238, 'SO', 'Southern', 1),
(3831, 238, 'WE', 'Western', 1),

(3832, 239, 'BU', 'Bulawayo', 1),
(3833, 239, 'HA', 'Harare', 1),
(3834, 239, 'ML', 'Manicaland', 1),
(3835, 239, 'MC', 'Mashonaland Central', 1),
(3836, 239, 'ME', 'Mashonaland East', 1),
(3837, 239, 'MW', 'Mashonaland West', 1),
(3838, 239, 'MV', 'Masvingo', 1),
(3839, 239, 'MN', 'Matabeleland North', 1),
(3840, 239, 'MS', 'Matabeleland South', 1),
(3841, 239, 'MD', 'Midlands', 1);

-- --------------------------------------------------------

--
-- Dumping Correction data for table `nc_zone`
--

INSERT INTO `nc_zone` (`zone_id`, `country_id`, `code`, `name`, `status`) VALUES
(3861, 105, 'CB', 'Campobasso', 1),
(3862, 105, 'CI', 'Carbonia-Iglesias', 1),
(3863, 105, 'CE', 'Caserta', 1),
(3864, 105, 'CT', 'Catania', 1),
(3865, 105, 'CZ', 'Catanzaro', 1),
(3866, 105, 'CH', 'Chieti', 1),
(3867, 105, 'CO', 'Como', 1),
(3868, 105, 'CS', 'Cosenza', 1),
(3869, 105, 'CR', 'Cremona', 1),
(3870, 105, 'KR', 'Crotone', 1),
(3871, 105, 'CN', 'Cuneo', 1),
(3872, 105, 'EN', 'Enna', 1),
(3873, 105, 'FE', 'Ferrara', 1),
(3874, 105, 'FI', 'Firenze', 1),
(3875, 105, 'FG', 'Foggia', 1),
(3876, 105, 'FC', 'Forli-Cesena', 1),
(3877, 105, 'FR', 'Frosinone', 1),
(3878, 105, 'GE', 'Genova', 1),
(3879, 105, 'GO', 'Gorizia', 1),
(3880, 105, 'GR', 'Grosseto', 1),
(3881, 105, 'IM', 'Imperia', 1),
(3882, 105, 'IS', 'Isernia', 1),
(3883, 105, 'AQ', 'L&#39;Aquila', 1),
(3884, 105, 'SP', 'La Spezia', 1),
(3885, 105, 'LT', 'Latina', 1),
(3886, 105, 'LE', 'Lecce', 1),
(3887, 105, 'LC', 'Lecco', 1),
(3888, 105, 'LI', 'Livorno', 1),
(3889, 105, 'LO', 'Lodi', 1),
(3890, 105, 'LU', 'Lucca', 1),
(3891, 105, 'MC', 'Macerata', 1),
(3892, 105, 'MN', 'Mantova', 1),
(3893, 105, 'MS', 'Massa-Carrara', 1),
(3894, 105, 'MT', 'Matera', 1),
(3895, 105, 'VS', 'Medio Campidano', 1),
(3896, 105, 'ME', 'Messina', 1),
(3897, 105, 'MI', 'Milano', 1),
(3898, 105, 'MO', 'Modena', 1),
(3899, 105, 'NA', 'Napoli', 1),
(3900, 105, 'NO', 'Novara', 1),
(3901, 105, 'NU', 'Nuoro', 1),
(3902, 105, 'OG', 'Ogliastra', 1),
(3903, 105, 'OT', 'Olbia-Tempio', 1),
(3904, 105, 'OR', 'Oristano', 1),
(3905, 105, 'PD', 'Padova', 1),
(3906, 105, 'PA', 'Palermo', 1),
(3907, 105, 'PR', 'Parma', 1),
(3908, 105, 'PV', 'Pavia', 1),
(3909, 105, 'PG', 'Perugia', 1),
(3910, 105, 'PU', 'Pesaro e Urbino', 1),
(3911, 105, 'PE', 'Pescara', 1),
(3912, 105, 'PC', 'Piacenza', 1),
(3913, 105, 'PI', 'Pisa', 1),
(3914, 105, 'PT', 'Pistoia', 1),
(3915, 105, 'PN', 'Pordenone', 1),
(3916, 105, 'PZ', 'Potenza', 1),
(3917, 105, 'PO', 'Prato', 1),
(3918, 105, 'RG', 'Ragusa', 1),
(3919, 105, 'RA', 'Ravenna', 1),
(3920, 105, 'RC', 'Reggio Calabria', 1),
(3921, 105, 'RE', 'Reggio Emilia', 1),
(3922, 105, 'RI', 'Rieti', 1),
(3923, 105, 'RN', 'Rimini', 1),
(3924, 105, 'RM', 'Roma', 1),
(3925, 105, 'RO', 'Rovigo', 1),
(3926, 105, 'SA', 'Salerno', 1),
(3927, 105, 'SS', 'Sassari', 1),
(3928, 105, 'SV', 'Savona', 1),
(3929, 105, 'SI', 'Siena', 1),
(3930, 105, 'SR', 'Siracusa', 1),
(3931, 105, 'SO', 'Sondrio', 1),
(3932, 105, 'TA', 'Taranto', 1),
(3933, 105, 'TE', 'Teramo', 1),
(3934, 105, 'TR', 'Terni', 1),
(3935, 105, 'TO', 'Torino', 1),
(3936, 105, 'TP', 'Trapani', 1),
(3937, 105, 'TN', 'Trento', 1),
(3938, 105, 'TV', 'Treviso', 1),
(3939, 105, 'TS', 'Trieste', 1),
(3940, 105, 'UD', 'Udine', 1),
(3941, 105, 'VA', 'Varese', 1),
(3942, 105, 'VE', 'Venezia', 1),
(3943, 105, 'VB', 'Verbano-Cusio-Ossola', 1),
(3944, 105, 'VC', 'Vercelli', 1),
(3945, 105, 'VR', 'Verona', 1),
(3946, 105, 'VV', 'Vibo Valentia', 1),
(3947, 105, 'VI', 'Vicenza', 1),
(3948, 105, 'VT', 'Viterbo', 1),

(3949, 222, 'ANT', 'County Antrim', 1),
(3950, 222, 'ARM', 'County Armagh', 1),
(3951, 222, 'DOW', 'County Down', 1),
(3952, 222, 'FER', 'County Fermanagh', 1),
(3953, 222, 'LDY', 'County Londonderry', 1),
(3954, 222, 'TYR', 'County Tyrone', 1),
(3955, 222, 'CMA', 'Cumbria', 1),

(3956, 190, '1', 'Pomurska', 1),
(3957, 190, '2', 'Podravska', 1),
(3958, 190, '3', 'Koro&scaron;ka', 1),
(3959, 190, '4', 'Savinjska', 1),
(3960, 190, '5', 'Zasavska', 1),
(3961, 190, '6', 'Spodnjeposavska', 1),
(3962, 190, '7', 'Jugovzhodna Slovenija', 1),
(3963, 190, '8', 'Osrednjeslovenska', 1),
(3964, 190, '9', 'Gorenjska', 1),
(3965, 190, '10', 'Notranjsko-kra&scaron;ka', 1),
(3966, 190, '11', 'Gori&scaron;ka', 1),
(3967, 190, '12', 'Obalno-kra&scaron;ka', 1),

(3968, 33, '', 'Ruse', 1),

(3969, 101, 'ALB', 'Alborz', 1),

(3970, 21, 'BRU', 'Brussels-Capital Region', 1),

(3971, 138, 'AG', 'Aguascalientes', 1),

(3972, 222, 'IOM', 'Isle of Man', 1),

(3973, 242, '01', 'Andrijevica', 1),
(3974, 242, '02', 'Bar', 1),
(3975, 242, '03', 'Berane', 1),
(3976, 242, '04', 'Bijelo Polje', 1),
(3977, 242, '05', 'Budva', 1),
(3978, 242, '06', 'Cetinje', 1),
(3979, 242, '07', 'Danilovgrad', 1),
(3980, 242, '08', 'Herceg-Novi', 1),
(3981, 242, '09', 'Kola&scaron;in', 1),
(3982, 242, '10', 'Kotor', 1),
(3983, 242, '11', 'Mojkovac', 1),
(3984, 242, '12', 'Nik&scaron;i&cacute;', 1),
(3985, 242, '13', 'Plav', 1),
(3986, 242, '14', 'Pljevlja', 1),
(3987, 242, '15', 'Plu&zcaron;ine', 1),
(3988, 242, '16', 'Podgorica', 1),
(3989, 242, '17', 'Ro&zcaron;aje', 1),
(3990, 242, '18', '&Scaron;avnik', 1),
(3991, 242, '19', 'Tivat', 1),
(3992, 242, '20', 'Ulcinj', 1),
(3993, 242, '21', '&Zcaron;abljak', 1),

(3994, 243, '00', 'Belgrade', 1),
(3995, 243, '01', 'North Ba&ccaron;ka', 1),
(3996, 243, '02', 'Central Banat', 1),
(3997, 243, '03', 'North Banat', 1),
(3998, 243, '04', 'South Banat', 1),
(3999, 243, '05', 'West Bačka', 1),
(4000, 243, '06', 'South Bačka', 1),
(4001, 243, '07', 'Srem', 1),
(4002, 243, '08', 'Ma&ccaron;va', 1),
(4003, 243, '09', 'Kolubara', 1),
(4004, 243, '10', 'Podunavlje', 1),
(4005, 243, '11', 'Brani&ccaron;evo', 1),
(4006, 243, '12', '&Scaron;umadija', 1),
(4007, 243, '13', 'Pomoravlje', 1),
(4008, 243, '14', 'Bor', 1),
(4009, 243, '15', 'Zaje&ccaron;ar', 1),
(4010, 243, '16', 'Zlatibor', 1),
(4011, 243, '17', 'Moravica', 1),
(4012, 243, '18', 'Ra&scaron;ka', 1),
(4013, 243, '19', 'Rasina', 1),
(4014, 243, '20', 'Ni&scaron;ava', 1),
(4015, 243, '21', 'Toplica', 1),
(4016, 243, '22', 'Pirot', 1),
(4017, 243, '23', 'Jablanica', 1),
(4018, 243, '24', 'P&ccaron;inja', 1),
(4019, 243, '25', 'Kosovo', 1),

(4020, 245, 'BO', 'Bonaire', 1),
(4021, 245, 'SA', 'Saba', 1),
(4022, 245, 'SE', 'Sint Eustatius', 1),

(4023, 248, 'EC', 'Central Equatoria', 1),
(4024, 248, 'EE', 'Eastern Equatoria', 1),
(4025, 248, 'JG', 'Jonglei', 1),
(4026, 248, 'LK', 'Lakes', 1),
(4027, 248, 'BN', 'Northern Bahr el-Ghazal', 1),
(4028, 248, 'UY', 'Unity', 1),
(4029, 248, 'NU', 'Upper Nile', 1),
(4030, 248, 'WR', 'Warrap', 1),
(4031, 248, 'BW', 'Western Bahr el-Ghazal', 1),
(4032, 248, 'EW', 'Western Equatoria', 1),

(4036, 117, '0661405', 'Aina&zcaron;i, Salacgrivas novads', 1),
(4037, 117, '0320201', 'Aizkraukle, Aizkraukles novads', 1),
(4038, 117, '0320200', 'Aizkraukles novads', 1),
(4039, 117, '0640605', 'Aizpute, Aizputes novads', 1),
(4040, 117, '0640600', 'Aizputes novads', 1),
(4041, 117, '0560805', 'Akniste, Aknistes novads', 1),
(4042, 117, '0560800', 'Aknistes novads', 1),
(4043, 117, '0661007', 'Aloja, Alojas novads', 1),
(4044, 117, '0661000', 'Alojas novads', 1),
(4045, 117, '0624200', 'Alsungas novads', 1),
(4046, 117, '0360201', 'Aluksne, Aluksnes novads', 1),
(4047, 117, '0360200', 'Aluksnes novads', 1),
(4048, 117, '0424701', 'Amatas novads', 1),
(4049, 117, '0360805', 'Ape, Apes novads', 1),
(4050, 117, '0360800', 'Apes novads', 1),
(4051, 117, '0460805', 'Auce, Auces novads', 1),
(4052, 117, '0460800', 'Auces novads', 1),
(4053, 117, '0804400', 'Ada&zcaron;u novads', 1),
(4054, 117, '0804900', 'Babītes novads', 1),
(4055, 117, '0800605', 'Baldone, Baldones novads', 1),
(4056, 117, '0800600', 'Baldones novads', 1),
(4057, 117, '0800807', 'Balo&zcaron;i, Kekavas novads', 1),
(4058, 117, '0384400', 'Baltinavas novads', 1),
(4059, 117, '0380201', 'Balvi, Balvu novads', 1),
(4060, 117, '0380200', 'Balvu novads', 1),
(4061, 117, '0400201', 'Bauska, Bauskas novads', 1),
(4062, 117, '0400200', 'Bauskas novads', 1),
(4063, 117, '0964700', 'Beverīnas novads', 1),
(4064, 117, '0840605', 'Broceni, Brocenu novads', 1),
(4065, 117, '0840601', 'Brocenu novads', 1),
(4066, 117, '0967101', 'Burtnieku novads', 1),
(4067, 117, '0805200', 'Carnikavas novads', 1),
(4068, 117, '0700807', 'Cesvaine, Cesvaines novads', 1),
(4069, 117, '0700800', 'Cesvaines novads', 1),
(4070, 117, '0420201', 'Cesis, Cēsu novads', 1),
(4071, 117, '0420200', 'Cesu novads', 1),
(4072, 117, '0684901', 'Ciblas novads', 1),
(4073, 117, '0601009', 'Dagda, Dagdas novads', 1),
(4074, 117, '0601000', 'Dagdas novads', 1),
(4075, 117, '0050000', 'Daugavpils', 1),
(4076, 117, '0440200', 'Daugavpils novads', 1),
(4077, 117, '0460201', 'Dobele, Dobeles novads', 1),
(4078, 117, '0460200', 'Dobeles novads', 1),
(4079, 117, '0885100', 'Dundagas novads', 1),
(4080, 117, '0640807', 'Durbe, Durbes novads', 1),
(4081, 117, '0640801', 'Durbes novads', 1),
(4082, 117, '0905100', 'Engures novads', 1),
(4083, 117, '0705500', 'Erglu novads', 1),
(4084, 117, '0806000', 'Garkalnes novads', 1),
(4085, 117, '0641009', 'Grobina, Grobinas novads', 1),
(4086, 117, '0641000', 'Grobinas novads', 1),
(4087, 117, '0500201', 'Gulbene, Gulbenes novads', 1),
(4088, 117, '0500200', 'Gulbenes novads', 1),
(4089, 117, '0406400', 'Iecavas novads', 1),
(4090, 117, '0740605', 'Ik&scaron;kile, Ik&scaron;kiles novads', 1),
(4091, 117, '0740600', 'Ik&scaron;kiles novads', 1),
(4092, 117, '0440807', 'Ilukste, Ilukstes novads', 1),
(4093, 117, '0440801', 'Ilukstes novads', 1),
(4094, 117, '0801800', 'In&ccaron;ukalna novads', 1),
(4095, 117, '0321007', 'Jaunjelgava, Jaunjelgavas novads', 1),
(4096, 117, '0321000', 'Jaunjelgavas novads', 1),
(4097, 117, '0425700', 'Jaunpiebalgas novads', 1),
(4098, 117, '0905700', 'Jaunpils novads', 1),
(4099, 117, '0090000', 'Jelgava', 1),
(4100, 117, '0540200', 'Jelgavas novads', 1),
(4101, 117, '0110000', 'Jekabpils', 1),
(4102, 117, '0560200', 'Jekabpils novads', 1),
(4103, 117, '0130000', 'Jūrmala', 1),
(4104, 117, '0540211', 'Kalnciems, Jelgavas novads', 1),
(4105, 117, '0901211', 'Kandava, Kandavas novads', 1),
(4106, 117, '0901201', 'Kandavas novads', 1),
(4107, 117, '0681009', 'Karsava, Karsavas novads', 1),
(4108, 117, '0681000', 'Karsavas novads', 1),
(4109, 117, '0960200', 'Kocēnu novads, bij. Valmieras', 1),
(4110, 117, '0326100', 'Kokneses novads', 1),
(4111, 117, '0600201', 'Kraslava, Kraslavas novads', 1),
(4112, 117, '0600202', 'Kraslavas novads', 1),
(4113, 117, '0806900', 'Krimuldas novads', 1),
(4114, 117, '0566900', 'Krustpils novads', 1),
(4115, 117, '0620201', 'Kuldiga, Kuldigas novads', 1),
(4116, 117, '0620200', 'Kuldigas novads', 1),
(4117, 117, '0741001', 'Keguma novads', 1),
(4118, 117, '0741009', 'Kegums, Keguma novads', 1),
(4119, 117, '0800800', 'Kekavas novads', 1),
(4120, 117, '0741413', 'Lielvarde, Lielvardes novads', 1),
(4121, 117, '0741401', 'Lielvardes novads', 1),
(4122, 117, '0170000', 'Liepaja', 1),
(4123, 117, '0660201', 'Limba&zcaron;i, Limba&zcaron;u novads', 1),
(4124, 117, '0660200', 'Limba&zcaron;u novads', 1),
(4125, 117, '0421211', 'Ligatne, Ligatnes novads', 1),
(4126, 117, '0421200', 'Ligatnes novads', 1),
(4127, 117, '0761211', 'Livani, Livanu novads', 1),
(4128, 117, '0761201', 'Livanu novads', 1),
(4129, 117, '0701413', 'Lubana, Lubanas novads', 1),
(4130, 117, '0701400', 'Lubanas novads', 1),
(4131, 117, '0680201', 'Ludza, Ludzas novads', 1),
(4132, 117, '0680200', 'Ludzas novads', 1),
(4133, 117, '0700201', 'Madona, Madonas novads', 1),
(4134, 117, '0700200', 'Madonas novads', 1),
(4135, 117, '0961011', 'Mazsalaca, Mazsalacas novads', 1),
(4136, 117, '0961000', 'Mazsalacas novads', 1),
(4137, 117, '0807400', 'Malpils novads', 1),
(4138, 117, '0807600', 'Marupes novads', 1),
(4139, 117, '0887600', 'Mersraga novads', 1),
(4140, 117, '0967300', 'Nauk&scaron;enu novads', 1),
(4141, 117, '0327100', 'Neretas novads', 1),
(4142, 117, '0647900', 'Nicas novads', 1),
(4143, 117, '0740201', 'Ogre, Ogres novads', 1),
(4144, 117, '0740202', 'Ogres novads', 1),
(4145, 117, '0801009', 'Olaine, Olaines novads', 1),
(4146, 117, '0801000', 'Olaines novads', 1),
(4147, 117, '0546701', 'Ozolnieku novads', 1),
(4148, 117, '0427500', 'Pargaujas novads', 1),
(4149, 117, '0641413', 'Pavilosta, Pavilostas novads', 1),
(4150, 117, '0641401', 'Pavilostas novads', 1),
(4151, 117, '0980213', 'Piltene, Ventspils novads', 1),
(4152, 117, '0321413', 'Plavinas, Plavinu novads', 1),
(4153, 117, '0321400', 'Plavinu novads', 1),
(4154, 117, '0760201', 'Preili, Preilu novads', 1),
(4155, 117, '0760202', 'Preilu novads', 1),
(4156, 117, '0641615', 'Priekule, Priekules novads', 1),
(4157, 117, '0641600', 'Priekules novads', 1),
(4158, 117, '0427300', 'Priekulu novads', 1),
(4159, 117, '0427700', 'Raunas novads', 1),
(4160, 117, '0210000', 'Rezekne', 1),
(4161, 117, '0780200', 'Rezeknes novads', 1),
(4162, 117, '0766300', 'Riebinu novads', 1),
(4163, 117, '0010000', 'Riga', 1),
(4164, 117, '0888300', 'Rojas novads', 1),
(4165, 117, '0808400', 'Ropa&zcaron;u novads', 1),
(4166, 117, '0648500', 'Rucavas novads', 1),
(4167, 117, '0387500', 'Rugaju novads', 1),
(4168, 117, '0407700', 'Rundales novads', 1),
(4169, 117, '0961615', 'Rujiena, Rujienas novads', 1),
(4170, 117, '0961600', 'Rujienas novads', 1),
(4171, 117, '0880213', 'Sabile, Talsu novads', 1),
(4172, 117, '0661415', 'Salacgriva, Salacgrivas novads', 1),
(4173, 117, '0661400', 'Salacgrīvas novads', 1),
(4174, 117, '0568700', 'Salas novads', 1),
(4175, 117, '0801200', 'Salaspils novads', 1),
(4176, 117, '0801211', 'Salaspils, Salaspils novads', 1),
(4177, 117, '0840200', 'Saldus novads', 1),
(4178, 117, '0840201', 'Saldus, Saldus novads', 1),
(4179, 117, '0801413', 'Saulkrasti, Saulkrastu novads', 1),
(4180, 117, '0801400', 'Saulkrastu novads', 1),
(4181, 117, '0941813', 'Seda, Stren&ccaron;u novads', 1),
(4182, 117, '0809200', 'Sejas novads', 1),
(4183, 117, '0801615', 'Sigulda, Siguldas novads', 1),
(4184, 117, '0801601', 'Siguldas novads', 1),
(4185, 117, '0328200', 'Skrīveru novads', 1),
(4186, 117, '0621209', 'Skrunda, Skrundas novads', 1),
(4187, 117, '0621200', 'Skrundas novads', 1),
(4188, 117, '0941615', 'Smiltene, Smiltenes novads', 1),
(4189, 117, '0941600', 'Smiltenes novads', 1),
(4190, 117, '0661017', 'Staicele, Alojas novads', 1),
(4191, 117, '0880215', 'Stende, Talsu novads', 1),
(4192, 117, '0809600', 'Stopinu novads', 1),
(4193, 117, '0941817', 'Stren&ccaron;i, Stren&ccaron;u novads', 1),
(4194, 117, '0941800', 'Stren&ccaron;u novads', 1),
(4195, 117, '0440815', 'Subate, Ilukstes novads', 1),
(4196, 117, '0880201', 'Talsi, Talsu novads', 1),
(4197, 117, '0880200', 'Talsu novads', 1),
(4198, 117, '0468900', 'Tervetes novads', 1),
(4199, 117, '0900200', 'Tukuma novads', 1),
(4200, 117, '0900201', 'Tukums, Tukuma novads', 1),
(4201, 117, '0649300', 'Vainodes novads', 1),
(4202, 117, '0880217', 'Valdemarpils, Talsu novads', 1),
(4203, 117, '0940201', 'Valka, Valkas novads', 1),
(4204, 117, '0940200', 'Valkas novads', 1),
(4205, 117, '0250000', 'Valmiera', 1),
(4206, 117, '0801817', 'Vanga&zcaron;i, In&ccaron;ukalna novads', 1),
(4207, 117, '0701817', 'Varaklani, Varaklanu novads', 1),
(4208, 117, '0701800', 'Varaklanu novads', 1),
(4209, 117, '0769101', 'Varkavas novads', 1),
(4210, 117, '0429300', 'Vecpiebalgas novads', 1),
(4211, 117, '0409500', 'Vecumnieku novads', 1),
(4212, 117, '0270000', 'Ventspils', 1),
(4213, 117, '0980200', 'Ventspils novads', 1),
(4214, 117, '0561815', 'Viesite, Viesites novads', 1),
(4215, 117, '0561800', 'Viesites novads', 1),
(4216, 117, '0381615', 'Vilaka, Vilakas novads', 1),
(4217, 117, '0381600', 'Vilakas novads', 1),
(4218, 117, '0781817', 'Vilani, Vilanu novads', 1),
(4219, 117, '0781800', 'Vilanu novads', 1),
(4220, 117, '0681817', 'Zilupe, Zilupes novads', 1),
(4221, 117, '0681801', 'Zilupes novads', 1),

(4222, 43, 'AP', 'Arica y Parinacota', 1),
(4223, 43, 'LR', 'Los Rios', 1),

(4224, 220, '63', 'Kharkivs''ka Oblast''', 1),

(4225, 118, 'LB-BR', 'Beirut', 1),
(4226, 118, 'LB-BE', 'Bekaa', 1),
(4227, 118, 'LB-ML', 'Mount Lebanon', 1),
(4228, 118, 'LB-NB', 'Nabatieh', 1),
(4229, 118, 'LB-NR', 'North', 1),
(4230, 118, 'LB-ST', 'South', 1),

(4231, 99, 'TS', 'Telangana', 1),
(4232, 44, 'QH', 'Qinghai', 1),

(4246, 100, 'KR', 'Kepulauan Riau', 1),
(4247, 100, 'KU', 'Kalimantan Utara', 1),
(4248, 100, 'PB', 'Papua Barat', 1),
(4249, 100, 'SB', 'Sulawesi Barat', 1),

(4250, 257, 'CE', 'Ceuta', 1),
(4251, 257, 'ML', 'Melilla', 1),

(4252, 251, 'PM', 'Las Palmas', 1),
(4253, 251, 'SC', 'Santa Cruz de Tenerife', 1),

(4254, 244, 'AX-MH', 'Mariehamn', 1),
(4255, 244, 'AX-BR', 'Br&auml;nd&ouml;', 1),
(4256, 244, 'AX-EC', 'Ecker&ouml;', 1),
(4257, 244, 'AX-FI', 'Finstr&ouml;m', 1),
(4258, 244, 'AX-FO', 'F&ouml;gl&ouml;', 1),
(4259, 244, 'AX-GE', 'Geta', 1),
(4260, 244, 'AX-HA', 'Hammarland', 1),
(4261, 244, 'AX-JO', 'Jomala', 1),
(4262, 244, 'AX-KU', 'Kumlinge', 1),
(4263, 244, 'AX-KO', 'K&ouml;kar', 1),
(4264, 244, 'AX-LE', 'Lemland', 1),
(4265, 244, 'AX-LU', 'Lumparland', 1),
(4266, 244, 'AX-SA', 'Saltvik', 1),
(4267, 244, 'AX-SO', 'Sottunga', 1),
(4268, 244, 'AX-SU', 'Sund', 1),
(4269, 244, 'AX-VA', 'V&aring;rd&ouml;', 1),

(4270, 87, 'BT', 'Basse Terre', 1),
(4271, 87, 'GT', 'Grande Terre', 1),
(4272, 87, 'PT', 'Iles de la Petite Terre', 1),
(4273, 87, 'LD', 'La D&eacute;sirade', 1),
(4274, 87, 'LS', 'Les Saintes', 1),
(4275, 87, 'MG', 'Marie Galante', 1),

(4276, 75, '', 'Cayenne', 1),
(4277, 75, '', 'Saint-Laurent-du-Maroni', 1),

(4278, 253, 'DAK', '&ETH;akovica', 1),
(4279, 253, 'GAN', 'Ganjilane', 1),
(4280, 253, 'KOM', 'Kosovska Mitrovica', 1),
(4281, 253, 'PEC', 'Pecki', 1),
(4282, 253, 'PRS', 'Prishtina', 1),
(4283, 253, 'PRZ', 'Prizren', 1),
(4284, 253, 'URO', 'Uro&scaron;evac', 1),

(4285, 69, 'EF', 'East Falkland (Stanley)', 1),
(4286, 69, 'WF', 'West Falkland', 1),
(4287, 69, 'LF', 'Lafonia', 1),

(4288, 70, '', 'T&oacute;rshavn', 1),
(4289, 70, '', 'Bordhoy', 1),
(4290, 70, '', 'Eysturoy', 1),
(4291, 70, '', 'Fugloy', 1),
(4292, 70, '', 'Hestur', 1),
(4293, 70, '', 'Kalsoy', 1),
(4294, 70, '', 'K&oacute;ltur', 1),
(4295, 70, '', 'Mykines', 1),
(4296, 70, '', 'N&oacute;lsoy', 1),
(4297, 70, '', 'Sandoy', 1),
(4298, 70, '', 'Sk&uacute;voy', 1),
(4299, 70, '', 'Streymoy', 1),
(4300, 70, '', 'Sudhuroy', 1),
(4301, 70, '', 'Svinoy', 1),
(4302, 70, '', 'V&aacute;gar', 1),
(4303, 70, '', 'Vidhoy', 1),

(4304, 71, 'K', 'Cikobla', 1),
(4305, 71, 'O', 'Ono-i-lau', 1),
(4306, 71, 'V', 'Vatoa', 1),

(4307, 174, 'SBE', 'Saint-Beno&icirc;t', 1),
(4308, 174, 'SDE', 'Saint-Denis', 1),
(4309, 174, 'SPA', 'Saint-Paul', 1),
(4310, 174, 'SPI', 'Saint-Pierre', 1),

(4311, 262, 'BDF', 'Bedfordshire', 1),
(4312, 262, 'BER', 'Berkshire', 1),
(4313, 262, 'BST', 'Bristol', 1),
(4314, 262, 'BKM', 'Buckinghamshire', 1),
(4315, 262, 'CAM', 'Cambridgeshire', 1),
(4316, 262, 'CHS', 'Cheshire', 1),
(4317, 262, 'CON', 'Cornwall', 1),
(4318, 262, 'CMA', 'Cumbria', 1),
(4319, 262, 'DBY', 'Derbyshire', 1),
(4320, 262, 'DEV', 'Devon', 1),
(4321, 262, 'DOR', 'Dorset', 1),
(4322, 262, 'DUR', 'Durham', 1),
(4323, 262, 'ESX', 'East Sussex', 1),
(4324, 262, 'ESS', 'Essex', 1),
(4325, 262, 'GLS', 'Gloucestershire', 1),
(4326, 262, 'MAN', 'Greater Manchester', 1),
(4327, 262, 'HAM', 'Hampshire', 1),
(4328, 262, 'HEF', 'Herefordshire', 1),
(4329, 262, 'HRT', 'Hertfordshire', 1),
(4330, 262, 'IOS', 'Isle of Wight', 1),
(4331, 262, 'KEN', 'Kent', 1),
(4332, 262, 'LAN', 'Lancashire', 1),
(4333, 262, 'LEC', 'Leicestershire', 1),
(4334, 262, 'LIN', 'Lincolnshire', 1),
(4335, 262, 'LND', 'London', 1),
(4336, 262, 'LIV', 'Merseyside', 1),
(4337, 262, 'MDX', 'Middlesex', 1),
(4338, 262, 'NFK', 'Norfolk', 1),
(4339, 262, 'NTH', 'Northamptonshire', 1),
(4340, 262, 'NBL', 'Northumberland', 1),
(4341, 262, 'NYK', 'North Yorkshire', 1),
(4342, 262, 'NTT', 'Nottinghamshire', 1),
(4343, 262, 'OXF', 'Oxfordshire', 1),
(4344, 262, 'RUT', 'Rutland', 1),
(4345, 262, 'SHR', 'Shropshire', 1),
(4346, 262, 'SOM', 'Somerset', 1),
(4347, 262, 'SYS', 'South Yorkshire', 1),
(4348, 262, 'STS', 'Staffordshire', 1),
(4349, 262, 'SFK', 'Suffolk', 1),
(4350, 262, 'SRY', 'Surrey', 1),
(4351, 262, 'TAW', 'Tyne and Wear', 1),
(4352, 262, 'WAR', 'Warwickshire', 1),
(4353, 262, 'WMD', 'West Midlands', 1),
(4354, 262, 'WSX', 'West Sussex', 1),
(4355, 262, 'WYS', 'West Yorkshire', 1),
(4356, 262, 'WIL', 'Wiltshire', 1),
(4357, 262, 'WOR', 'Worcestershire', 1),
(4358, 262, 'YKS', 'Yorkshire', 1),

(4359, 263, 'IOA', 'Isle of Anglesey', 1),
(4360, 263, 'CDG', 'Ceredigion', 1),
(4361, 263, 'CTS', 'Carmarthenshire', 1),
(4362, 263, 'CYD', 'Clwyd', 1),
(4363, 263, 'DBS', 'Denbighshire', 1),
(4364, 263, 'DYF', 'Dyfed', 1),
(4365, 263, 'FLS', 'Flintshire', 1),
(4366, 263, 'GWT', 'Gwent', 1),
(4367, 263, 'GWD', 'Gwynedd', 1),
(4368, 263, 'MGG', 'Mid Glamorgan', 1),
(4369, 263, 'MNS', 'Monmouthshire', 1),
(4370, 263, 'PBS', 'Pembrokeshire', 1),
(4371, 263, 'POW', 'Powys', 1),
(4372, 263, 'SGG', 'South Glamorgan', 1),
(4373, 263, 'WGG', 'West Glamorgan', 1),
(4374, 263, 'WXH', 'Wrexham', 1),

(4375, 264, 'ANT', 'Antrim', 1),
(4376, 264, 'ARM', 'Armagh', 1),
(4377, 264, 'BFS', 'City of Belfast', 1),
(4378, 264, 'DOW', 'Down', 1),
(4379, 264, 'FGH', 'Fermanagh', 1),
(4380, 264, 'LDD', 'Londonderry', 1),
(4381, 264, 'DER', 'City of Derry', 1),
(4382, 264, 'TYR', 'Tyrone', 1),

(4383, 265, 'COA', 'City of Aberdeen', 1),
(4384, 265, 'ABS', 'Aberdeenshire', 1),
(4385, 265, 'ANG', 'Angus (Forfarshire)', 1),
(4386, 265, 'ARG', 'Argyll', 1),
(4387, 265, 'ARS', 'Ayrshire', 1),
(4388, 265, 'BFS', 'Banffshire', 1),
(4389, 265, 'BWS', 'Berwickshire', 1),
(4390, 265, 'BUT', 'Bute', 1),
(4391, 265, 'CTN', 'Caithness', 1),
(4392, 265, 'CKS', 'Clackmannanshire', 1),
(4393, 265, 'DFS', 'Dumfriesshire', 1),
(4394, 265, 'DBS', 'Dunbartonshire (Dumbarton)', 1),
(4395, 265, 'COD', 'City of Dundee', 1),
(4396, 265, 'ELT', 'East Lothian (Haddingtonshire)', 1),
(4397, 265, 'COE', 'City of Edinburgh', 1),
(4398, 265, 'FIF', 'Fife', 1),
(4399, 265, 'COG', 'City of Glasgow', 1),
(4400, 265, 'IVS', 'Inverness-shire', 1),
(4401, 265, 'KCS', 'Kincardineshire', 1),
(4402, 265, 'KRS', 'Kinross-shire', 1),
(4403, 265, 'KBS', 'Kirkcudbrightshire', 1),
(4404, 265, 'LKS', 'Lanarkshire', 1),
(4405, 265, 'MLT', 'Midlothian (County of Edinburgh)', 1),
(4406, 265, 'MOR', 'Moray (Elginshire)', 1),
(4407, 265, 'NAS', 'Nairnshire', 1),
(4408, 265, 'ORK', 'Orkney', 1),
(4409, 265, 'PBS', 'Peeblesshire', 1),
(4410, 265, 'PRS', 'Perthshire', 1),
(4411, 265, 'RFS', 'Renfrewshire', 1),
(4412, 265, 'RAC', 'Ross and Cromarty', 1),
(4413, 265, 'RBS', 'Roxburghshire', 1),
(4414, 265, 'SKS', 'Selkirkshire', 1),
(4415, 265, 'SHL', 'Shetland (Zetland)', 1),
(4416, 265, 'STS', 'Stirlingshire', 1),
(4417, 265, 'SUL', 'Sutherland', 1),
(4418, 265, 'WLT', 'West Lothian (Linlithgowshire)', 1),
(4419, 265, 'WTS', 'Wigtownshire', 1);

-- --------------------------------------------------------

--
-- Table structure for table `nc_zone_to_geo_zone`
--

DROP TABLE IF EXISTS `nc_zone_to_geo_zone`;
CREATE TABLE `nc_zone_to_geo_zone` (
  `zone_to_geo_zone_id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` int(11) NOT NULL,
  `zone_id` int(11) NOT NULL DEFAULT '0',
  `geo_zone_id` int(11) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`zone_to_geo_zone_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `nc_zone_to_geo_zone`
--

INSERT INTO `nc_zone_to_geo_zone` (`zone_to_geo_zone_id`, `country_id`, `zone_id`, `geo_zone_id`, `date_added`, `date_modified`) VALUES
(1, 81, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(2, 14, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(3, 21, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(4, 33, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(5, 55, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(6, 53, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(7, 57, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(8, 195, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(9, 67, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(10, 72, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(11, 74, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(12, 84, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(13, 97, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(14, 103, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(15, 105, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(16, 117, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(17, 123, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(18, 124, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(19, 132, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(20, 150, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(21, 170, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(22, 171, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(23, 56, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(24, 175, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(25, 222, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(26, 189, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(27, 190, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(28, 203, 0, 1, '2018-01-17 10:45:05', '0000-00-00 00:00:00'),
(65, 222, 0, 4, '2017-12-15 15:18:13', '0000-00-00 00:00:00'),
(66, 222, 0, 3, '2018-04-21 16:36:23', '0000-00-00 00:00:00'),
(67, 254, 0, 3, '2018-04-21 16:36:23', '0000-00-00 00:00:00');

-- --------------------------------------------------------
