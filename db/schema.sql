-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `name` VARCHAR(255) DEFAULT NULL, -- Optional: if you want to store user names
  `has_voted_token` VARCHAR(255) DEFAULT NULL, -- Could be used for simple one-time login/access
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notes:
-- - `email` is unique and the primary identifier for voters.
-- - `has_voted_token` could be a pre-generated token for each user to access the voting page for a specific election, or a general access token.
-- - For a "known list of email addresses", you would pre-populate this table.

-- --------------------------------------------------------
-- Table structure for table `admins`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `is_active` BOOLEAN DEFAULT TRUE, -- To easily enable/disable admins
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `elections`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `elections` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `start_date` DATETIME NOT NULL,
  `end_date` DATETIME NOT NULL,
  `is_active` BOOLEAN DEFAULT TRUE, -- To easily enable/disable elections
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notes:
-- - `start_date` and `end_date` define the voting period.
-- - `is_active` can be used by admins to control visibility.

-- --------------------------------------------------------
-- Table structure for table `candidates`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `candidates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `election_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL, -- Optional: details about the candidate
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`election_id`) REFERENCES `elections`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notes:
-- - `election_id` links candidates to a specific election.
-- - `ON DELETE CASCADE` means if an election is deleted, its candidates are also deleted.

-- --------------------------------------------------------
-- Table structure for table `votes`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `votes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `election_id` INT NOT NULL,
  `candidate_id` INT NOT NULL,
  `voted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_vote` (`user_id`, `election_id`), -- Ensures a user can vote only once per election
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`election_id`) REFERENCES `elections`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`candidate_id`) REFERENCES `candidates`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notes:
-- - The `unique_vote` constraint is crucial for "one vote per user per election".
-- - `ON DELETE CASCADE` ensures data integrity.
