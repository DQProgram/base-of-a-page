SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id_user` int(9) NOT NULL AUTO_INCREMENT,
  `username` varchar(40) COLLATE latin1_general_ci NOT NULL,
  `password` varchar(60) COLLATE latin1_general_ci NOT NULL,
  `email` varchar(30) COLLATE latin1_general_ci NOT NULL,
  `nif` varchar(9) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

INSERT INTO `users` (`id_user`, `username`, `password`, `email`, `nif`) VALUES
(1,	'Maria',	'25d55ad283aa400af464c76d713c07ad',	'maria1@aki.com',	'222222222');
