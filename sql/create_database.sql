CREATE DATABASE endless_bowler;

USE endless_bowler;

CREATE TABLE users (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       name VARCHAR(50) NOT NULL UNIQUE,
                       elo INT DEFAULT 1000
);

CREATE TABLE questions (
                           id INT AUTO_INCREMENT PRIMARY KEY,
                           question TEXT NOT NULL,
                           correct_answer VARCHAR(255) NOT NULL
);

CREATE TABLE answers (
                         id INT AUTO_INCREMENT PRIMARY KEY,
                         user_id INT NOT NULL,
                         question_id INT NOT NULL,
                         is_correct BOOLEAN NOT NULL,
                         elo_change INT NOT NULL,
                         timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                         FOREIGN KEY (user_id) REFERENCES users(id),
                         FOREIGN KEY (question_id) REFERENCES questions(id)
);