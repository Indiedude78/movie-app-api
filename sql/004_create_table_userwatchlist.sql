CREATE TABLE IF NOT EXISTS `User_watchlist` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `movie_id` INT NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `watched` INT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `Users`(`id`),
    FOREIGN KEY (`movie_id`) REFERENCES `Movies`(`id`)
)