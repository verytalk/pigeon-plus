
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for posts
-- ----------------------------
DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `content` longtext COLLATE utf8_bin NOT NULL,
  `author` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `time` bigint(32) NOT NULL,
  `public` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` int(2) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `permission` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registe_ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registe_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latest_ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latest_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;


INSERT INTO `users`(`id`, `username`, `password`, `email`, `permission`, `registe_ip`, `registe_time`, `latest_ip`, `latest_time`, `status`, `token`) VALUES (0, 'admin', '$2y$10$rX04fyo0t4YWgur660Ste.WbnuQ/3zyltgutSRyjgn2.TkW//3uFS', 'admin', 'root', '127.0.0.1', '1609818772', '127.0.0.1', '1611542859', '200', '9bd12fe2ebb8f81034cc73bcf1f71e24');