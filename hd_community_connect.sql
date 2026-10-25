SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `eoi`;
DROP TABLE IF EXISTS `job_requirements`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `staff`;
DROP TABLE IF EXISTS `contributions`;
DROP TABLE IF EXISTS `members`;
DROP TABLE IF EXISTS `group_info`;

CREATE TABLE `jobs` (
  `job_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `reference` char(6) NOT NULL,
  `description` text NOT NULL,
  `salary` decimal(10,2) NOT NULL,
  `reporting_line` text NOT NULL,
  PRIMARY KEY (`job_id`),
  UNIQUE KEY `reference` (`reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `job_requirements` (
  `requirement_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_id` int(10) UNSIGNED NOT NULL,
  `list_type` enum('responsibility','essential','preferred') NOT NULL,
  `item_text` varchar(255) NOT NULL,
  `sort_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`requirement_id`),
  KEY `job_id` (`job_id`),
  CONSTRAINT `job_requirements_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`job_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `eoi` (
  `EOInumber` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ApplicationID` varchar(17) NOT NULL,
  `JobReference` char(6) NOT NULL,
  `FirstName` varchar(20) NOT NULL,
  `LastName` varchar(20) NOT NULL,
  `DOB` date NOT NULL,
  `Gender` enum('Male','Female','Other') NOT NULL,
  `StreetAddress` varchar(255) NOT NULL,
  `Suburb` varchar(255) NOT NULL,
  `State` enum('VIC','NSW','QLD','NT','WA','SA','TAS','ACT') NOT NULL,
  `Postcode` char(4) NOT NULL,
  `Email` varchar(150) NOT NULL,
  `Phone` varchar(10) NOT NULL,
  `Skills` set('Problem solving','Communication','Teamwork') NOT NULL,
  `OtherSkills` text NOT NULL,
  `Status` enum('New','Current','Final') NOT NULL DEFAULT 'New',
  PRIMARY KEY (`EOInumber`),
  UNIQUE KEY `ApplicationID` (`ApplicationID`),
  UNIQUE KEY `job_email` (`JobReference`,`Email`),
  KEY `fk_eoi_job` (`JobReference`),
  KEY `FirstName` (`FirstName`),
  KEY `LastName` (`LastName`),
  KEY `Status` (`Status`),
  CONSTRAINT `fk_eoi_job` FOREIGN KEY (`JobReference`) REFERENCES `jobs` (`reference`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `users` (
  `eoi_number` int(10) UNSIGNED NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `failed_attempts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `lockout_until` datetime DEFAULT NULL,
  PRIMARY KEY (`eoi_number`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`eoi_number`) REFERENCES `eoi` (`EOInumber`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `staff` (
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `failed_attempts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `lockout_until` datetime DEFAULT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `group_info` (
  `group_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_name` varchar(50) NOT NULL,
  `intro` text NOT NULL,
  `lecture_time` varchar(60) NOT NULL,
  `lecture_staff` varchar(60) NOT NULL,
  `tutorial_time` varchar(60) NOT NULL,
  `tutorial_staff` varchar(60) NOT NULL,
  `photo_path` varchar(255) NOT NULL,
  `photo_alt` varchar(255) NOT NULL,
  `photo_caption` varchar(255) NOT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `members` (
  `member_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` varchar(60) NOT NULL,
  `nickname` varchar(30) DEFAULT NULL,
  `student_id` varchar(12) NOT NULL,
  `quote_text` varchar(255) DEFAULT NULL,
  `quote_by` varchar(60) DEFAULT NULL,
  `fun_fact` varchar(255) DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `contributions` (
  `contribution_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `member_id` int(10) UNSIGNED NOT NULL,
  `project_part` tinyint(3) UNSIGNED NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`contribution_id`),
  KEY `member_id` (`member_id`),
  CONSTRAINT `contributions_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`) ON DELETE CASCADE,
  CONSTRAINT `project_part_1_or_2` CHECK (`project_part` in (1,2))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `jobs` (`job_id`, `title`, `reference`, `description`, `salary`, `reporting_line`) VALUES
(1, 'Community Organiser', '100001', 'Community Organisers plan and coordinate our community events.', 70000, 'Programs Manager'),
(2, 'Fundraiser', '100002', 'Fundraisers organise and run fundraising events and manage the funds raised.', 94000, 'Manager/Treasurer'),
(3, 'Media and PR Officer', '100003', 'Media and PR Officers look after our brand, media relations and social media.', 90000, 'Communications Manager'),
(4, 'Volunteer Coordinator', '100004', 'Volunteer Coordinators recruit, train and support the volunteers who run our community programs.', 75000, 'Programs Manager'),
(5, 'Youth Program Officer', '100005', 'Youth Program Officers run after-school and holiday programs for young people aged 12 to 18.', 72000, 'Programs Manager'),
(6, 'Community Support Worker', '100006', 'Community Support Workers help people access housing, food relief and other support services.', 68000, 'Support Services Coordinator'),
(7, 'Grants and Partnerships Officer', '100007', 'Grants and Partnerships Officers find funding opportunities and manage relationships with partner organisations.', 85000, 'Manager/Treasurer'),
(8, 'Digital Services Officer', '100008', 'Digital Services Officers maintain our website and online services so the community can find and use our programs.', 88000, 'Operations Manager');

INSERT INTO `job_requirements` (`requirement_id`, `job_id`, `list_type`, `item_text`, `sort_order`) VALUES
(1, 1, 'responsibility', 'Plan and coordinate community events', 1),
(2, 1, 'responsibility', 'Build links with local groups and partners', 2),
(3, 1, 'responsibility', 'Speak at and facilitate community meetings', 3),
(4, 1, 'essential', 'Effective communication', 1),
(5, 1, 'essential', 'Strategic planning', 2),
(6, 1, 'essential', 'Knowledge of relevant legislation and policy', 3),
(7, 1, 'preferred', 'Problem-solving', 1),
(8, 1, 'preferred', 'Time management', 2),
(9, 1, 'preferred', 'Team leadership', 3),
(10, 2, 'responsibility', 'Keep accurate financial records', 1),
(11, 2, 'responsibility', 'Plan and run fundraising events', 2),
(12, 2, 'responsibility', 'Communicate with donors and the public', 3),
(13, 2, 'essential', 'Communication skills', 1),
(14, 2, 'essential', 'Budget management', 2),
(15, 2, 'essential', 'Data analysis', 3),
(16, 2, 'preferred', 'Networking', 1),
(17, 2, 'preferred', 'Strategic thinking', 2),
(18, 2, 'preferred', 'Persistence and resilience', 3),
(19, 3, 'responsibility', 'Manage media relations', 1),
(20, 3, 'responsibility', 'Look after the Community Connect brand', 2),
(21, 3, 'responsibility', 'Run our social media channels', 3),
(22, 3, 'essential', 'Effective communication', 1),
(23, 3, 'essential', 'Writing for media and social media', 2),
(24, 3, 'essential', 'Research and analysis', 3),
(25, 3, 'preferred', 'Cultural awareness', 1),
(26, 3, 'preferred', 'Networking', 2),
(27, 3, 'preferred', 'Graphic design', 3),
(28, 4, 'responsibility', 'Recruit and onboard new volunteers', 1),
(29, 4, 'responsibility', 'Plan volunteer rosters for programs and events', 2),
(30, 4, 'responsibility', 'Run volunteer training and check-ins', 3),
(31, 4, 'essential', 'Strong organisational skills', 1),
(32, 4, 'essential', 'Clear written and verbal communication', 2),
(33, 4, 'essential', 'Working with Children Check', 3),
(34, 4, 'preferred', 'Experience using a volunteer management system', 1),
(35, 4, 'preferred', 'First Aid certificate', 2),
(36, 4, 'preferred', 'Second language', 3),
(37, 5, 'responsibility', 'Plan and deliver weekly youth activities', 1),
(38, 5, 'responsibility', 'Build links with local schools and families', 2),
(39, 5, 'responsibility', 'Record attendance and program outcomes', 3),
(40, 5, 'essential', 'Experience working with young people', 1),
(41, 5, 'essential', 'Working with Children Check', 2),
(42, 5, 'essential', 'Patience and good judgement', 3),
(43, 5, 'preferred', 'Certificate IV in Youth Work', 1),
(44, 5, 'preferred', 'Mental Health First Aid', 2),
(45, 5, 'preferred', 'Driver licence', 3),
(46, 6, 'responsibility', 'Meet with clients and assess their needs', 1),
(47, 6, 'responsibility', 'Refer clients to local support services', 2),
(48, 6, 'responsibility', 'Keep accurate case notes', 3),
(49, 6, 'essential', 'Empathy and active listening', 1),
(50, 6, 'essential', 'Knowledge of local support services', 2),
(51, 6, 'essential', 'Police Check', 3),
(52, 6, 'preferred', 'Certificate IV in Community Services', 1),
(53, 6, 'preferred', 'Experience with case management software', 2),
(54, 6, 'preferred', 'Second language', 3),
(55, 7, 'responsibility', 'Research and write grant applications', 1),
(56, 7, 'responsibility', 'Report to funders on how grants are spent', 2),
(57, 7, 'responsibility', 'Maintain relationships with partner organisations', 3),
(58, 7, 'essential', 'Excellent written communication', 1),
(59, 7, 'essential', 'Budget and reporting skills', 2),
(60, 7, 'essential', 'Attention to detail', 3),
(61, 7, 'preferred', 'Experience in the not-for-profit sector', 1),
(62, 7, 'preferred', 'Networking', 2),
(63, 7, 'preferred', 'Project management', 3),
(64, 8, 'responsibility', 'Update website content and online forms', 1),
(65, 8, 'responsibility', 'Support staff with digital tools', 2),
(66, 8, 'responsibility', 'Track website and service usage', 3),
(67, 8, 'essential', 'Basic HTML, CSS and web publishing skills', 1),
(68, 8, 'essential', 'Problem-solving', 2),
(69, 8, 'essential', 'Clear communication with non-technical people', 3),
(70, 8, 'preferred', 'Experience with PHP and MySQL', 1),
(71, 8, 'preferred', 'Accessibility (WCAG) knowledge', 2),
(72, 8, 'preferred', 'Graphic design', 3);

INSERT INTO `eoi` (`EOInumber`, `ApplicationID`, `JobReference`, `FirstName`, `LastName`, `DOB`, `Gender`, `StreetAddress`, `Suburb`, `State`, `Postcode`, `Email`, `Phone`, `Skills`, `OtherSkills`, `Status`) VALUES
(1, '23337561325102026', '100001', 'Mia', 'Nguyen', '1999-04-12', 'Female', '12 High St', 'Hawthorn', 'VIC', '3122', 'mia.nguyen@example.com', '0412345678', 'Communication,Teamwork', 'Event planning &amp; first aid', 'New'),
(2, '27037561325102026', '100001', 'Liam', 'Smith', '1995-11-03', 'Male', '4 Station Rd', 'Box Hill', 'VIC', '3128', 'liam.smith@example.com', '0423456789', 'Problem solving', '', 'Current'),
(3, '30737561325102026', '100002', 'Aisha', 'Rahman', '2001-02-28', 'Female', '88 King St', 'Newtown', 'NSW', '2042', 'aisha.r@example.com', '0434567890', 'Communication', 'Grant writing', 'New'),
(4, '34437561325102026', '100002', 'Noah', 'Williams', '1990-07-19', 'Male', '3 Queen St', 'Brisbane', 'QLD', '4000', 'noah.w@example.com', '0445678901', 'Problem solving,Teamwork', '', 'Final'),
(5, '38137561325102026', '100003', 'Sam', 'Lee', '1998-09-09', 'Other', '21 Rundle Mall', 'Adelaide', 'SA', '5000', 'sam.lee@example.com', '0456789012', 'Communication,Teamwork', 'Canva, social media', 'New'),
(6, '41837561325102026', '100003', 'Olivia', 'Smith', '2003-12-01', 'Female', '7 Hay St', 'Perth', 'WA', '6000', 'olivia.s@example.com', '0467890123', 'Teamwork', '', 'Current');

INSERT INTO `users` (`eoi_number`, `password_hash`, `failed_attempts`, `lockout_until`) VALUES
(1, '$2y$10$VJ1PRb3/y4EpsFWATkNovel41cibLdSWLZ5lXxXdN2DQxhJd2bjcG', 0, NULL),
(2, '$2y$10$VJ1PRb3/y4EpsFWATkNovel41cibLdSWLZ5lXxXdN2DQxhJd2bjcG', 0, NULL),
(3, '$2y$10$VJ1PRb3/y4EpsFWATkNovel41cibLdSWLZ5lXxXdN2DQxhJd2bjcG', 0, NULL),
(4, '$2y$10$VJ1PRb3/y4EpsFWATkNovel41cibLdSWLZ5lXxXdN2DQxhJd2bjcG', 0, NULL),
(5, '$2y$10$VJ1PRb3/y4EpsFWATkNovel41cibLdSWLZ5lXxXdN2DQxhJd2bjcG', 0, NULL),
(6, '$2y$10$VJ1PRb3/y4EpsFWATkNovel41cibLdSWLZ5lXxXdN2DQxhJd2bjcG', 0, NULL);

INSERT INTO `staff` (`username`, `password_hash`, `failed_attempts`, `lockout_until`) VALUES
('admin', '$2y$10$a5DOvGwSWi.i0oKgKjQYYe3l.iTtsjsFAanUlmYkP.iZyw1C.GctS', 0, NULL);

INSERT INTO `group_info` (`group_id`, `group_name`, `intro`, `lecture_time`, `lecture_staff`, `tutorial_time`, `tutorial_staff`, `photo_path`, `photo_alt`, `photo_caption`) VALUES
(1, 'HD', 'Community Connect''s site was designed and built by a four-person student team over Semester 2, 2026. This page introduces who we are, what each of us contributed, and a little about us outside of the project.', 'Monday 4:30pm to 5:30pm', 'Dr Atie Kia', 'Tuesday 12:30pm to 2:30pm', 'Razeen Hashmi', 'images/HD-team-photo.jpg', 'The HD team standing together on campus', 'The HD team: Sam, Tom, Shash and Declan.');

INSERT INTO `members` (`member_id`, `full_name`, `nickname`, `student_id`, `quote_text`, `quote_by`, `fun_fact`, `sort_order`) VALUES
(1, 'Shahryar Rahman Saad', 'Sam', '106406031', 'You cannot shake hands with a clenched fist.', 'Indira Gandhi', 'I am interested in web development and enjoy creating interactive websites.', 1),
(2, 'Gia Nghia Le', 'Tom', '106424716', 'Doubt kills more dreams than failure ever will.', 'Suzy Kassem', 'I am a big fan of anime and manga, and I enjoy watching and reading them in my free time.', 2),
(3, 'Shashwat Chhabra', 'Shash', '106514329', 'Never put off until tomorrow what can be done today.', 'Sensei Wu', 'I love to play Football/Soccer and I am very passionate about it.', 3),
(4, 'Declan Elliott', NULL, '106052926', 'First we mine, then we craft. Let''s MINECRAFT', 'Steve', 'I am really into music and I play the guitar in my spare time.', 4);

INSERT INTO `contributions` (`contribution_id`, `member_id`, `project_part`, `description`) VALUES
(1, 1, 1, 'Project lead. Set up and maintained the GitHub repository and Jira board, built the shared page structure, navigation and base CSS that every page sits inside, and developed index.html.'),
(2, 1, 2, 'Built the shared PHP includes (header, nav, footer, database connection), the staff-only access check, index.php and the HR manage page.'),
(3, 2, 1, 'Communication lead. Kept the team Discord channel active, liaised with the teaching team via the Canvas discussion board, and developed apply.html, including the job application form.'),
(4, 2, 2, 'Built apply.php, process_eoi.php to validate and save applications, and the applicant page for checking and editing an application.'),
(5, 3, 1, 'Project documentation. Developed about.html and prepared the written documentation accompanying the project on GitHub.'),
(6, 3, 2, 'Built about.php from the database and the staff and applicant login and logout pages.'),
(7, 4, 1, 'QA analyst. Developed jobs.html and ran testing across the site throughout development, including HTML/CSS validation, link and form checks, cross-browser testing, and third-party resource attribution.'),
(8, 4, 2, 'Built the jobs listing, full job and job search pages from the jobs table.');

SET FOREIGN_KEY_CHECKS = 1;
