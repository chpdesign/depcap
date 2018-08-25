<?php

class Start extends \ComposerPack\Migration\AbstractMysqlMigrationUnit {

    public function up()
    {
        $this->db->query("
        CREATE TABLE `content` (
          `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `short` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `long` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `poster` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          `modified_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ALTER TABLE `content`
          ADD PRIMARY KEY (`id`),
          ADD KEY `title` (`title`),
          ADD KEY `text` (`short`),
          ADD KEY `text` (`long`);
        ALTER TABLE `content`
          ADD CONSTRAINT `content_ibfk_1` FOREIGN KEY (`title`) REFERENCES `lang` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
          ADD CONSTRAINT `content_ibfk_2` FOREIGN KEY (`short`) REFERENCES `lang` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
          ADD CONSTRAINT `content_ibfk_3` FOREIGN KEY (`long`) REFERENCES `lang` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;
        ");
    }

    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS `content`");
    }

}