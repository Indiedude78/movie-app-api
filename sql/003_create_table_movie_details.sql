CREATE TABLE IF NOT EXISTS Movie_details (
    `id` INT NOT NULL AUTO_INCREMENT,
    `movie_id` INT NOT NULL,
    `imdb_id` VARCHAR(255) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (movie_id) REFERENCES Movies(id),
    FOREIGN KEY (imdb_id) REFERENCES Movies(imdb_id)
);