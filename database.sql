CREATE DATABASE IF NOT EXISTS mindspace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mindspace;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL, 
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','admin') NOT NULL DEFAULT 'user',
    streak INT UNSIGNED NOT NULL DEFAULT 0,
    total_xp INT UNSIGNED NOT NULL DEFAULT 0,
    last_active DATE NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS quests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(120) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(40) NOT NULL,
    difficulty VARCHAR(20) NOT NULL,
    xp INT UNSIGNED NOT NULL DEFAULT 10,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_quests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    quest_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_quest (user_id, quest_id),
    CONSTRAINT fk_user_quests_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_quests_quest FOREIGN KEY (quest_id) REFERENCES quests(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS expressions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    content TEXT NOT NULL,
    prompt VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_expressions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS resources (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(160) NOT NULL,
    description VARCHAR(255) NOT NULL,
    content TEXT NULL,
    category VARCHAR(60) NOT NULL,
    emoji VARCHAR(16) NOT NULL DEFAULT '📘',
    read_time VARCHAR(40) NOT NULL DEFAULT '3 min read',
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO quests (title, description, category, difficulty, xp, active)
SELECT * FROM (
    SELECT '3-Minute Breathing', 'Pause and take 10 slow breaths.', 'Mindfulness', 'Easy', 15, 1
    UNION ALL SELECT 'Hydration Check', 'Drink one full glass of water.', 'Self-care', 'Easy', 10, 1
    UNION ALL SELECT 'Send a Kind Message', 'Check in on someone you trust.', 'Social', 'Medium', 20, 1
    UNION ALL SELECT 'Gratitude Note', 'Write one thing you are grateful for today.', 'Reflection', 'Easy', 15, 1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM quests LIMIT 1);

INSERT INTO resources (title, description, content, category, emoji, read_time, active)
SELECT * FROM (
    SELECT 'Grounding in 5 Steps', 'A quick method for moments of anxiety.', 'Name 5 things you can see, 4 you can feel, 3 you can hear, 2 you can smell, and 1 you can taste.', 'Anxiety', '🧘', '2 min read', 1
    UNION ALL SELECT 'Sleep Reset', 'Simple evening habits to improve sleep quality.', 'Reduce screen time 30 minutes before bed and keep a consistent sleep schedule.', 'Sleep', '😴', '3 min read', 1
    UNION ALL SELECT 'When to Ask for Help', 'Signs that it is time to reach out to a professional.', 'If low mood persists for 2+ weeks, seek support from a counselor or clinician.', 'Support', '🆘', '4 min read', 1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM resources LIMIT 1);
