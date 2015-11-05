--
-- Table structure for table `playsms_featureAutorespond`
--

DROP TABLE IF EXISTS `playsms_featureAutorespond`;
CREATE TABLE `playsms_featureAutorespond` (
  `id` int(11) NOT NULL,
  `created` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_update` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `uid` int(11) NOT NULL DEFAULT '0',
  `service_name` varchar(255) NOT NULL DEFAULT '',
  `regex` varchar(140) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `sms_receiver` varchar(20) NOT NULL DEFAULT '',
  `smsc` varchar(100) NOT NULL DEFAULT '',
  `flag_deleted` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `playsms_featureAutorespond`
--
ALTER TABLE `playsms_featureAutorespond`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `playsms_featureAutorespond`
--
ALTER TABLE `playsms_featureAutorespond`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
