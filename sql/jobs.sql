-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 26, 2026 at 09:39 AM
-- Server version: 10.11.16-MariaDB
-- PHP Version: 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cos10026_web`
--

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

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

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`job_reference`, `title`, `category`, `tagline`, `description`, `salary_min`, `salary_max`, `experience_level`, `reports_to`, `employment_type`, `work_arrangement`, `location`, `responsibilities`, `essential_requirements`, `preferable_requirements`) VALUES
('ED101', 'Learning Platform Web Developer', 'Web Development', 'Help develop and maintain the web interfaces used by students and educators across our online learning platform.', 'You will work with designers and other developers to deliver usable, accessible and responsive learning experiences.', 105000.00, 125000.00, 'At least two years of relevant experience', 'Web Development Team Lead', 'Full-time', 'On-site', 'The tech room, 67 Kangaroo Building, Vegemite Street, Melbourne', 'Develop and maintain web pages using HTML5 and CSS3.\r\nCreate responsive interfaces for desktop, tablet and mobile.\r\nCollaborate with designers to implement accessible page layouts.\r\nTest links, navigation and page structure across browsers.\r\nDocument changes and help resolve interface issues.', 'Understanding of HTML5 and CSS3 fundamentals.\r\nAbility to structure pages using semantic HTML elements.\r\nBasic knowledge of responsive design and web accessibility.\r\nGood communication and teamwork skills.', 'Experience using Git and GitHub for version control.\r\nFamiliarity with accessibility guidelines such as WCAG.\r\nExperience testing websites with keyboard navigation.'),
('ED202', 'Accessibility and UX Designer', 'UI/UX Design', 'Help make online education easier to use for learners with different abilities, needs and levels of digital experience.', 'You will improve the clarity, navigation and inclusiveness of our learning interfaces.', 80000.00, 90000.00, 'Junior', 'Product Design Manager', 'Full-time', 'Hybrid', 'The tech room, 67 Kangaroo Building, Vegemite Street, Melbourne', 'Design clear page layouts and user journeys for online learning.\r\nCreate wireframes and prototypes for new features.\r\nReview interfaces for readability, contrast and keyboard access.\r\nGather feedback and identify usability improvements.\r\nWork with developers to turn design concepts into web pages.', 'Understanding of user-centred design principles.\r\nAbility to explain design decisions clearly.\r\nAwareness of inclusive design and accessibility principles.\r\nWillingness to collaborate and respond to feedback.', 'Experience with wireframing or prototyping tools.\r\nFamiliarity with WCAG accessibility guidelines.\r\nExperience conducting usability testing.'),
('EDCS107', 'Cybersecurity Analyst', 'Cybersecurity', 'Help protect our organisation against cyber threats and keep our systems, networks and data secure.', 'We are looking for a motivated Cybersecurity Analyst to monitor security systems, investigate potential threats and help strengthen our organisation\'s digital security. You will work with the IT team to identify vulnerabilities, respond to security incidents and maintain a safe technology environment.', 85000.00, 105000.00, 'At least 2 years of relevant experience', 'IT Security Manager', 'Full-time', 'Hybrid', 'The tech room, 67 Kangaroo Building, Vegemite Street, Melbourne', 'Monitor networks and systems for suspicious activities.\r\nInvestigate security alerts and potential cyberattacks.\r\nIdentify vulnerabilities and recommend security improvements.\r\nAssist with incident response and recovery procedures.\r\nPrepare security reports and maintain documentation.', 'Bachelor degree in Cybersecurity, Information Technology or a related field.\r\nUnderstanding of network security, firewalls and access controls.\r\nKnowledge of common cyber threats such as phishing and malware.\r\nStrong analytical and problem-solving skills.\r\nGood communication and teamwork skills.', 'Experience with security tools such as Wireshark, Splunk or SIEM platforms.\r\nKnowledge of penetration testing and vulnerability assessment.\r\nRelevant certifications such as CompTIA Security+ or CEH.\r\nBasic scripting knowledge in Python or PowerShell.');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`job_reference`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
