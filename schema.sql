-- Scholarly Pulse
-- Sanitized database schema for portfolio/demo use.
-- Contains no production user data, passwords, posts, or personal records.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `schools` (
  `SchoolID` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(266) NOT NULL,
  `SchoolType` varchar(100) NOT NULL,
  `Description` varchar(500) NOT NULL,
  `WebsiteURL` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`SchoolID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `courses` (
  `CourseID` int NOT NULL AUTO_INCREMENT,
  `CourseName` varchar(266) NOT NULL,
  `SchoolID` int NOT NULL,
  PRIMARY KEY (`CourseID`),
  KEY `fk_courses_school` (`SchoolID`),
  CONSTRAINT `fk_courses_school`
    FOREIGN KEY (`SchoolID`)
    REFERENCES `schools` (`SchoolID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `majors` (
  `MajorID` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(266) NOT NULL,
  `SchoolID` int NOT NULL,
  PRIMARY KEY (`MajorID`),
  KEY `SchoolID` (`SchoolID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `registration_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `unique_user_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `forum` (
  `forum_id` int NOT NULL AUTO_INCREMENT,
  `forum_name` varchar(255) NOT NULL,
  `forum_description` text,
  `course_id` int NOT NULL,
  `major_id` int DEFAULT NULL,
  `created_by_user_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`forum_id`),
  UNIQUE KEY `unique_course_forum` (`course_id`),
  KEY `major_id` (`major_id`),
  KEY `created_by_user_id` (`created_by_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `blog_posts` (
  `blogpost_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `forum_id` int NOT NULL,
  `blogpost_title` varchar(255) NOT NULL,
  `blogpost_body` text NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `blogpost_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`blogpost_id`),
  KEY `user_id` (`user_id`),
  KEY `forum_id` (`forum_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `post_comments` (
  `comment_id` int NOT NULL AUTO_INCREMENT,
  `blogpost_id` int NOT NULL,
  `user_id` int NOT NULL,
  `comment_text` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`comment_id`),
  KEY `blogpost_id` (`blogpost_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `post_comments_ibfk_1`
    FOREIGN KEY (`blogpost_id`)
    REFERENCES `blog_posts` (`blogpost_id`)
    ON DELETE CASCADE,
  CONSTRAINT `post_comments_ibfk_2`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`user_id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `post_reactions` (
  `reaction_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `blogpost_id` int NOT NULL,
  `reaction_type` enum('like','dislike') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`reaction_id`),
  UNIQUE KEY `unique_user_post_reaction` (`user_id`, `blogpost_id`),
  KEY `blogpost_id` (`blogpost_id`),
  CONSTRAINT `post_reactions_ibfk_1`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`user_id`)
    ON DELETE CASCADE,
  CONSTRAINT `post_reactions_ibfk_2`
    FOREIGN KEY (`blogpost_id`)
    REFERENCES `blog_posts` (`blogpost_id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `saved_posts` (
  `saved_post_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `blogpost_id` int NOT NULL,
  `saved_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`saved_post_id`),
  UNIQUE KEY `unique_saved_post` (`user_id`, `blogpost_id`),
  KEY `blogpost_id` (`blogpost_id`),
  CONSTRAINT `saved_posts_ibfk_1`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`user_id`)
    ON DELETE CASCADE,
  CONSTRAINT `saved_posts_ibfk_2`
    FOREIGN KEY (`blogpost_id`)
    REFERENCES `blog_posts` (`blogpost_id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `user_courses` (
  `user_course_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `course_id` int NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_course_id`),
  UNIQUE KEY `unique_user_course` (`user_id`, `course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
