--
-- Drop existing table
--
DROP TABLE IF EXISTS `civicrm_chapter_entity`;

--
-- Table structure for table `civicrm_chapter_entity`
--

CREATE TABLE `civicrm_chapter_entity` (
  `id` int(10) UNSIGNED NOT NULL,
  `entity_id` int(10) NOT NULL,
  `entity_table` varchar(64) NOT NULL,
  `chapter_code` int(10) DEFAULT NULL,
  `fund_code` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `civicrm_chapter_entity`
--
ALTER TABLE `civicrm_chapter_entity`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `entity_index` (`entity_id`,`entity_table`),
  ADD KEY `entity_id` (`entity_id`),
  ADD KEY `chapter_code_index` (`chapter_code`),
  ADD KEY `fund_code_index` (`fund_code`);
