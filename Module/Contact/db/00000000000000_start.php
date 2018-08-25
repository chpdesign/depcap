<?php

class Start extends \ComposerPack\Migration\AbstractMysqlMigrationUnit {

    public function up()
    {
        $this->db->query("
        CREATE TABLE `contact` (
          `id` int(11) NOT NULL,
          `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `phone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `message` text COLLATE utf8_unicode_ci NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          `modified_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ALTER TABLE `contact`
          ADD PRIMARY KEY (`id`);
        ALTER TABLE `contact`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
        ");
    }

    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS `contact`");
    }

}