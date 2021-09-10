-- Adminer 4.8.1 MySQL 5.5.5-10.0.38-MariaDB-0ubuntu0.16.04.1 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP VIEW IF EXISTS `crm_active_users_count`;
CREATE TABLE `crm_active_users_count` (`active_users` bigint(22));


DROP VIEW IF EXISTS `crm_answers_today`;
CREATE TABLE `crm_answers_today` (`user_id` int(11), `questions_answered` bigint(21));


DROP VIEW IF EXISTS `crm_average_answers_today`;
CREATE TABLE `crm_average_answers_today` (`users` bigint(21), `average_answers` decimal(24,4));


SET NAMES utf8mb4;

DROP TABLE IF EXISTS `quiz_answer`;
CREATE TABLE `quiz_answer` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `quiz_question_id` int(10) unsigned NOT NULL,
  `answer` text NOT NULL,
  `correct` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `quiz_question_id` (`quiz_question_id`),
  CONSTRAINT `quiz_answer_ibfk_2` FOREIGN KEY (`quiz_question_id`) REFERENCES `quiz_question` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `quiz_answer` (`id`, `quiz_question_id`, `answer`, `correct`) VALUES
(1,	1,	'Dublin',	1),
(2,	1,	'Edinburgh',	0),
(3,	2,	'3',	0),
(4,	2,	'4',	1),
(5,	2,	'5',	0);

DROP TABLE IF EXISTS `quiz_question`;
CREATE TABLE `quiz_question` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `quiz_question` (`id`, `question`) VALUES
(1,	'What is the capital of Ireland?'),
(2,	'2 + 2 = ?');

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `credits` int(10) unsigned NOT NULL DEFAULT '0',
  `points` int(10) unsigned NOT NULL DEFAULT '0',
  `created` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `user` (`id`, `credits`, `points`, `created`) VALUES
(1,	40,	150,	'2021-09-05'),
(2,	10,	30,	'2021-09-05');

DROP TABLE IF EXISTS `user_answer`;
CREATE TABLE `user_answer` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `quiz_question_id` int(10) unsigned NOT NULL,
  `quiz_answer_id` bigint(20) unsigned NOT NULL,
  `date_answered` date NOT NULL,
  `points` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_question_date` (`user_id`,`quiz_question_id`,`date_answered`),
  KEY `quiz_question_id` (`quiz_question_id`),
  KEY `quiz_answer_id` (`quiz_answer_id`),
  CONSTRAINT `user_answer_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `user_answer_ibfk_2` FOREIGN KEY (`quiz_question_id`) REFERENCES `quiz_question` (`id`),
  CONSTRAINT `user_answer_ibfk_3` FOREIGN KEY (`quiz_answer_id`) REFERENCES `quiz_answer` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `user_answer` (`id`, `user_id`, `quiz_question_id`, `quiz_answer_id`, `date_answered`, `points`) VALUES
(1,	1,	1,	1,	'2021-09-05',	10),
(5,	2,	1,	1,	'2021-09-05',	10),
(14,	1,	2,	4,	'2021-09-09',	10),
(18,	1,	1,	1,	'2021-09-09',	10),
(20,	2,	1,	1,	'2021-09-09',	10),
(21,	2,	2,	3,	'2021-09-09',	0);

DROP TABLE IF EXISTS `user_subscription`;
CREATE TABLE `user_subscription` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `renew_attempts` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_subscription_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `user_subscription` (`id`, `user_id`, `start_date`, `end_date`, `renew_attempts`) VALUES
(1,	1,	'2021-09-01',	'2021-09-16',	0),
(2,	2,	'2021-09-09',	'2021-09-16',	0);

DROP TABLE IF EXISTS `crm_active_users_count`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `crm_active_users_count` AS select (count(`user_subscription`.`id`) + (select count(`user`.`id`) from `user` where (`user`.`created` = curdate()))) AS `active_users` from `user_subscription` where (`user_subscription`.`end_date` >= curdate());

DROP TABLE IF EXISTS `crm_answers_today`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `crm_answers_today` AS select `user_answer`.`user_id` AS `user_id`,count(`user_answer`.`quiz_question_id`) AS `questions_answered` from `user_answer` where (`user_answer`.`date_answered` = curdate()) group by `user_answer`.`user_id`;

DROP TABLE IF EXISTS `crm_average_answers_today`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `crm_average_answers_today` AS select count(`crm_answers_today`.`user_id`) AS `users`,avg(`crm_answers_today`.`questions_answered`) AS `average_answers` from `crm_answers_today`;

-- 2021-09-10 09:04:25
