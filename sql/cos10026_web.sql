-- Edbridge (W3bb) - COS10026 Project Part 2
-- Full database for the site: jobs, eoi, about and users tables.
--
-- How to import:
--   1. In phpMyAdmin, create (or select) the database named in settings.php: cos10026_web
--   2. Import this file. It drops and recreates every table, so it can be run again safely.
--
-- Built from the per-table files in this folder:
--   jobs.sql (Nguyen Chi Hien), apply.sql (Do Dinh Phu), about.sql and users.sql (Tran Trung Tin).
-- Merged by Nguyen Quoc An.

SET NAMES utf8mb4;
SET time_zone = "+00:00";

DROP TABLE IF EXISTS eoi;
DROP TABLE IF EXISTS jobs;
DROP TABLE IF EXISTS about;
DROP TABLE IF EXISTS users;

-- --------------------------------------------------------
-- jobs: job listings shown on index.php, jobs.php and search.php
-- --------------------------------------------------------

CREATE TABLE `jobs` (
  `job_reference` varchar(10) NOT NULL,
  `title` varchar(100) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `tagline` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `salary_min` decimal(10,2) DEFAULT NULL,
  `salary_max` decimal(10,2) DEFAULT NULL,
  `experience_level` varchar(50) DEFAULT NULL,
  `reports_to` varchar(100) DEFAULT NULL,
  `employment_type` varchar(50) DEFAULT NULL,
  `work_arrangement` varchar(50) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `responsibilities` text DEFAULT NULL,
  `essential_requirements` text DEFAULT NULL,
  `preferable_requirements` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `jobs` (`job_reference`, `title`, `category`, `tagline`, `description`, `salary_min`, `salary_max`, `experience_level`, `reports_to`, `employment_type`, `work_arrangement`, `location`, `responsibilities`, `essential_requirements`, `preferable_requirements`) VALUES
('ED101', 'Learning Platform Web Developer', 'Web Development', 'Help develop and maintain the web interfaces used by students and educators across our online learning platform.', 'You will work with designers and other developers to deliver usable, accessible and responsive learning experiences.', 105000.00, 125000.00, 'At least two years of relevant experience', 'Web Development Team Lead', 'Full-time', 'On-site', 'The tech room, 67 Kangaroo Building, Vegemite Street, Melbourne', 'Develop and maintain web pages using HTML5 and CSS3.\r\nCreate responsive interfaces for desktop, tablet and mobile.\r\nCollaborate with designers to implement accessible page layouts.\r\nTest links, navigation and page structure across browsers.\r\nDocument changes and help resolve interface issues.', 'Understanding of HTML5 and CSS3 fundamentals.\r\nAbility to structure pages using semantic HTML elements.\r\nBasic knowledge of responsive design and web accessibility.\r\nGood communication and teamwork skills.', 'Experience using Git and GitHub for version control.\r\nFamiliarity with accessibility guidelines such as WCAG.\r\nExperience testing websites with keyboard navigation.'),
('ED202', 'Accessibility and UX Designer', 'UI/UX Design', 'Help make online education easier to use for learners with different abilities, needs and levels of digital experience.', 'You will improve the clarity, navigation and inclusiveness of our learning interfaces.', 80000.00, 90000.00, 'Junior', 'Product Design Manager', 'Full-time', 'Hybrid', 'The tech room, 67 Kangaroo Building, Vegemite Street, Melbourne', 'Design clear page layouts and user journeys for online learning.\r\nCreate wireframes and prototypes for new features.\r\nReview interfaces for readability, contrast and keyboard access.\r\nGather feedback and identify usability improvements.\r\nWork with developers to turn design concepts into web pages.', 'Understanding of user-centred design principles.\r\nAbility to explain design decisions clearly.\r\nAwareness of inclusive design and accessibility principles.\r\nWillingness to collaborate and respond to feedback.', 'Experience with wireframing or prototyping tools.\r\nFamiliarity with WCAG accessibility guidelines.\r\nExperience conducting usability testing.'),
('ED307', 'Cybersecurity Analyst', 'Cybersecurity', 'Help protect our organisation against cyber threats and keep our systems, networks and data secure.', 'We are looking for a motivated Cybersecurity Analyst to monitor security systems, investigate potential threats and help strengthen our organisation\'s digital security. You will work with the IT team to identify vulnerabilities, respond to security incidents and maintain a safe technology environment.', 85000.00, 105000.00, 'At least 2 years of relevant experience', 'IT Security Manager', 'Full-time', 'Hybrid', 'The tech room, 67 Kangaroo Building, Vegemite Street, Melbourne', 'Monitor networks and systems for suspicious activities.\r\nInvestigate security alerts and potential cyberattacks.\r\nIdentify vulnerabilities and recommend security improvements.\r\nAssist with incident response and recovery procedures.\r\nPrepare security reports and maintain documentation.', 'Bachelor degree in Cybersecurity, Information Technology or a related field.\r\nUnderstanding of network security, firewalls and access controls.\r\nKnowledge of common cyber threats such as phishing and malware.\r\nStrong analytical and problem-solving skills.\r\nGood communication and teamwork skills.', 'Experience with security tools such as Wireshark, Splunk or SIEM platforms.\r\nKnowledge of penetration testing and vulnerability assessment.\r\nRelevant certifications such as CompTIA Security+ or CEH.\r\nBasic scripting knowledge in Python or PowerShell.');

ALTER TABLE `jobs`
  ADD PRIMARY KEY (`job_reference`);

-- --------------------------------------------------------
-- eoi: applications saved by process_eoi.php, managed in manage.php
-- --------------------------------------------------------

CREATE TABLE eoi (
  EOInumber INT UNSIGNED NOT NULL AUTO_INCREMENT,
  job_reference VARCHAR(5) NOT NULL,
  first_name VARCHAR(20) NOT NULL,
  last_name VARCHAR(20) NOT NULL,
  date_of_birth DATE NOT NULL,
  gender VARCHAR(20) NOT NULL,
  street_address VARCHAR(40) NOT NULL,
  suburb VARCHAR(40) NOT NULL,
  state CHAR(3) NOT NULL,
  postcode CHAR(4) NOT NULL,
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(12) NOT NULL,
  skill1 VARCHAR(50) NULL,
  skill2 VARCHAR(50) NULL,
  skill3 VARCHAR(50) NULL,
  skill4 VARCHAR(50) NULL,
  skill5 VARCHAR(50) NULL,
  other_skills TEXT NULL,
  status ENUM('New', 'Current', 'Final') NOT NULL DEFAULT 'New',
  PRIMARY KEY (EOInumber)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample applications so manage.php has data to list, filter, update and delete.
INSERT INTO eoi
  (job_reference, first_name, last_name, date_of_birth, gender, street_address, suburb, state, postcode, email, phone, skill1, skill2, skill3, skill4, skill5, other_skills, status)
VALUES
  ('ED101', 'Linh', 'Pham', '2001-04-12', 'Female', '12 Collins Street', 'Melbourne', 'VIC', '3000', 'linh.pham@example.com', '0412345678', 'HTML', 'CSS', 'JavaScript', NULL, NULL, 'Built a school club website.', 'New'),
  ('ED101', 'James', 'Nguyen', '1998-09-30', 'Male', '45 George Street', 'Sydney', 'NSW', '2000', 'james.nguyen@example.com', '0498765432', 'HTML', 'CSS', 'PHP', 'MySQL', NULL, NULL, 'Current'),
  ('ED202', 'Sarah', 'Smith', '1999-01-05', 'Female', '8 Queen Street', 'Brisbane', 'QLD', '4000', 'sarah.smith@example.com', '0411222333', 'HTML', 'CSS', NULL, NULL, NULL, 'Ran usability tests for a student app.', 'New'),
  ('ED202', 'Minh', 'Tran', '2000-07-21', 'Prefer not to say', '3 King William Street', 'Adelaide', 'SA', '5000', 'minh.tran@example.com', '0433444555', 'CSS', 'JavaScript', NULL, NULL, NULL, NULL, 'Final');

-- --------------------------------------------------------
-- about: team members and contributions, shown on the About page
-- --------------------------------------------------------

CREATE TABLE about (
  member_id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  display_order      TINYINT UNSIGNED NOT NULL,
  full_name          VARCHAR(60)  NOT NULL,
  student_id         CHAR(9)      NOT NULL,
  part1_contribution TEXT         NOT NULL,
  part2_contribution TEXT         NOT NULL,
  quote              VARCHAR(255) NOT NULL,
  dream_job          VARCHAR(60)  NOT NULL,
  dream_job_icon     VARCHAR(8)   NOT NULL DEFAULT '',
  coding_snack       VARCHAR(40)  NOT NULL,
  hometown           VARCHAR(40)  NOT NULL,
  PRIMARY KEY (member_id),
  UNIQUE KEY uq_about_student_id (student_id),
  UNIQUE KEY uq_about_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO about
  (display_order, full_name, student_id, part1_contribution, part2_contribution,
   quote, dream_job, dream_job_icon, coding_snack, hometown)
VALUES
  (1, 'Tran Trung Tin', '106200136',
   'Set up the Jira board and confirmed tutor access. Built the About page and the shared footer.',
   'Planned: about and users tables, about page loaded from the database, login and logout with account lockout, Jira tasks for Part 2, README and GenAI credits.',
   'i feel like there is a sleep in me that needs to be slept but eachtime i sleep i don''t sleep that sleep.',
   'Security engineer/analyst', '🔐', 'Banh Mi', 'Hanoi'),

  (2, 'Nguyen Quoc An', '105550652',
   'Built the Index page, deployed the site with GitHub Pages and prepared the final ZIP.',
   'Planned: shared PHP includes, settings.php and helper functions, folder restructure, manage page with all HR queries, final SQL file and submission.',
   'Life is like coding: Every bug is a lesson, every fix is a step forward.',
   'AI Engineer', '💻', 'Bugs', 'Hanoi'),

  (3, 'Nguyen Chi Hien', '106393625',
   'Built the Jobs page and checked form validation, keyboard navigation and layouts.',
   'Planned: jobs table, jobs page and home page loaded from the database, job search, styling review of the new pages.',
   '90% of gamblers quit right before they''re about to hit big!',
   'Gambler', '🎰', 'Chips', 'Hanoi'),

  (4, 'Do Dinh Phu', '105559808',
   'Built the Apply page and its form validation, then validated all four HTML pages and checked links.',
   'Planned: eoi table, apply form posting to process_eoi.php, server-side validation and sanitising, final link and validator check.',
   'You squash a bug and 100 more come out~',
   'Game programmer/tester', '🎮', 'Potato Chips', 'Nam Dinh');

-- --------------------------------------------------------
-- users: manager accounts for login.php / manage.php (login: admin / admin)
-- Passwords are bcrypt hashes from password_hash(); lockout uses failed_attempts and locked_until.
-- --------------------------------------------------------

CREATE TABLE users (
  user_id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username        VARCHAR(50)  NOT NULL,
  password_hash   VARCHAR(255) NOT NULL,
  failed_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  locked_until    DATETIME     NULL DEFAULT NULL,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id),
  UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- admin / admin
INSERT INTO users (username, password_hash)
VALUES ('admin', '$2y$12$gZ5Dw4CCiOkUgLf60kN58eDwwz4mvbGO.OafXR8YDOydpmQ16H9fK');
