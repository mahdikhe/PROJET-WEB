-- Create the candidate_ratings table
CREATE TABLE IF NOT EXISTS `candidate_ratings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entretien_id` int(11) NOT NULL,
  `rater_id` int(11) NOT NULL,
  `technical_skills` int(11) DEFAULT NULL COMMENT 'Rating 1-5',
  `communication` int(11) DEFAULT NULL COMMENT 'Rating 1-5',
  `experience` int(11) DEFAULT NULL COMMENT 'Rating 1-5',
  `cultural_fit` int(11) DEFAULT NULL COMMENT 'Rating 1-5',
  `overall_rating` int(11) DEFAULT NULL COMMENT 'Rating 1-5',
  `strengths` text DEFAULT NULL,
  `weaknesses` text DEFAULT NULL,
  `general_feedback` text DEFAULT NULL,
  `interview_notes` text DEFAULT NULL,
  `recommendation` enum('Hire','Consider','Reject') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `entretien_id` (`entretien_id`),
  KEY `rater_id` (`rater_id`),
  CONSTRAINT `candidate_ratings_ibfk_1` FOREIGN KEY (`entretien_id`) REFERENCES `entretiens` (`id`) ON DELETE CASCADE
);

-- Add a new column to the entretiens table to store the average rating
ALTER TABLE `entretiens` 
ADD COLUMN `avg_rating` DECIMAL(3,2) DEFAULT NULL AFTER `status`;