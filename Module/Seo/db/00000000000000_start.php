<?php

class Start extends \ComposerPack\Migration\AbstractMysqlMigrationUnit {

    public function up()
    {
        $this->db->execute("
        CREATE TABLE `seo` (
          `meta` longtext COLLATE utf8mb4_general_ci DEFAULT NULL,
          `code` int(11) NOT NULL DEFAULT 200,
          `seo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
          `model` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
          `url` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
          `title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
          `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          `modified_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                
        ALTER TABLE `seo`
          ADD PRIMARY KEY (`url`) USING BTREE,
          ADD UNIQUE KEY `seo` (`seo`),
          ADD KEY `title` (`title`),
          ADD KEY `description` (`description`),
          ADD KEY `model` (`model`);
          
        ALTER TABLE `seo`
          ADD CONSTRAINT `seo_ibfk_1` FOREIGN KEY (`url`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
          ADD CONSTRAINT `seo_ibfk_2` FOREIGN KEY (`title`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
          ADD CONSTRAINT `seo_ibfk_3` FOREIGN KEY (`description`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
          ADD CONSTRAINT `seo_ibfk_4` FOREIGN KEY (`seo`) REFERENCES `seo` (`url`) ON DELETE SET NULL ON UPDATE SET NULL;
          
        ");
    }

    public function down()
    {
        $this->db->execute("DROP TABLE IF EXISTS `seo`");
    }

}