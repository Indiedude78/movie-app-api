CREATE TABLE IF NOT EXISTS `Movies` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `imdb_id` VARCHAR(255) NOT NULL UNIQUE,
    `title` VARCHAR(255) NOT NULL,
    `release_year` VARCHAR(4),
    `mtype` VARCHAR(1) NOT NULL,
    `poster` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`id`)
)