SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `eoi`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `staff`;
DROP TABLE IF EXISTS `contributions`;
DROP TABLE IF EXISTS `members`;
DROP TABLE IF EXISTS `group_info`;

CREATE TABLE `jobs` (
  `job_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `reference` varchar(6) NOT NULL,
  `description` text NOT NULL,
  `salary` float NOT NULL,
  `reporting_line` text NOT NULL,
  `responsibilities` text NOT NULL,
  `essential` text NOT NULL,
  `preferred` text DEFAULT NULL,
  PRIMARY KEY (`job_id`),
  UNIQUE KEY `reference` (`reference`)
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
  `Email` varchar(1000) NOT NULL,
  `Phone` varchar(10) NOT NULL,
  `Skills` set('Problem solving','Communication','Teamwork') NOT NULL,
  `OtherSkills` text DEFAULT NULL,
  `Status` enum('New','Current','Final') NOT NULL DEFAULT 'New',
  PRIMARY KEY (`EOInumber`),
  UNIQUE KEY `ApplicationID` (`ApplicationID`),
  KEY `fk_eoi_job` (`JobReference`),
  CONSTRAINT `fk_eoi_job` FOREIGN KEY (`JobReference`) REFERENCES `jobs` (`reference`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `users` (
  `eoi_number` int(10) UNSIGNED NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `failed_attempts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `lockout_until` datetime DEFAULT NULL,
  `lockout_minutes` int(10) UNSIGNED NOT NULL DEFAULT 15,
  PRIMARY KEY (`eoi_number`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`eoi_number`) REFERENCES `eoi` (`EOInumber`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `staff` (
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `failed_attempts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `lockout_until` datetime DEFAULT NULL,
  `lockout_minutes` int(10) UNSIGNED NOT NULL DEFAULT 15,
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
  CONSTRAINT `contributions_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `jobs` (`job_id`, `title`, `reference`, `description`, `salary`, `reporting_line`, `responsibilities`, `essential`, `preferred`) VALUES
(1, 'Community Organiser', '100001', 'Community Organisers plan and coordinate our community events.', 70000, 'Programs Manager', 'Plan and coordinate community events\nBuild links with local groups and partners\nSpeak at and facilitate community meetings', 'Effective communication\nStrategic planning\nKnowledge of relevant legislation and policy', 'Problem-solving\nTime management\nTeam leadership'),
(2, 'Fundraiser', '100002', 'Fundraisers organise and run fundraising events and manage the funds raised.', 94000, 'Manager/Treasurer', 'Keep accurate financial records\nPlan and run fundraising events\nCommunicate with donors and the public', 'Communication skills\nBudget management\nData analysis', 'Networking\nStrategic thinking\nPersistence and resilience'),
(3, 'Media and PR Officer', '100003', 'Media and PR Officers look after our brand, media relations and social media.', 90000, 'Communications Manager', 'Manage media relations\nLook after the Community Connect brand\nRun our social media channels', 'Effective communication\nWriting for media and social media\nResearch and analysis', 'Cultural awareness\nNetworking\nGraphic design'),
(4, 'Volunteer Coordinator', '100004', 'Volunteer Coordinators recruit, train and support the volunteers who run our community programs.', 75000, 'Programs Manager', 'Recruit and onboard new volunteers\nPlan volunteer rosters for programs and events\nRun volunteer training and check-ins', 'Strong organisational skills\nClear written and verbal communication\nWorking with Children Check', 'Experience using a volunteer management system\nFirst Aid certificate\nSecond language'),
(5, 'Youth Program Officer', '100005', 'Youth Program Officers run after-school and holiday programs for young people aged 12 to 18.', 72000, 'Programs Manager', 'Plan and deliver weekly youth activities\nBuild links with local schools and families\nRecord attendance and program outcomes', 'Experience working with young people\nWorking with Children Check\nPatience and good judgement', 'Certificate IV in Youth Work\nMental Health First Aid\nDriver licence'),
(6, 'Community Support Worker', '100006', 'Community Support Workers help people access housing, food relief and other support services.', 68000, 'Support Services Coordinator', 'Meet with clients and assess their needs\nRefer clients to local support services\nKeep accurate case notes', 'Empathy and active listening\nKnowledge of local support services\nPolice Check', 'Certificate IV in Community Services\nExperience with case management software\nSecond language'),
(7, 'Grants and Partnerships Officer', '100007', 'Grants and Partnerships Officers find funding opportunities and manage relationships with partner organisations.', 85000, 'Manager/Treasurer', 'Research and write grant applications\nReport to funders on how grants are spent\nMaintain relationships with partner organisations', 'Excellent written communication\nBudget and reporting skills\nAttention to detail', 'Experience in the not-for-profit sector\nNetworking\nProject management'),
(8, 'Digital Services Officer', '100008', 'Digital Services Officers maintain our website and online services so the community can find and use our programs.', 88000, 'Operations Manager', 'Update website content and online forms\nSupport staff with digital tools\nTrack website and service usage', 'Basic HTML, CSS and web publishing skills\nProblem-solving\nClear communication with non-technical people', 'Experience with PHP and MySQL\nAccessibility (WCAG) knowledge\nGraphic design');

INSERT INTO `eoi` (`EOInumber`, `ApplicationID`, `JobReference`, `FirstName`, `LastName`, `DOB`, `Gender`, `StreetAddress`, `Suburb`, `State`, `Postcode`, `Email`, `Phone`, `Skills`, `OtherSkills`, `Status`) VALUES
(1, '23337561325102026', '100001', 'Mia', 'Nguyen', '1999-04-12', 'Female', '12 High St', 'Hawthorn', 'VIC', '3122', 'mia.nguyen@example.com', '0412345678', 'Communication,Teamwork', 'Event planning &amp; first aid', 'New'),
(2, '27037561325102026', '100001', 'Liam', 'Smith', '1995-11-03', 'Male', '4 Station Rd', 'Box Hill', 'VIC', '3128', 'liam.smith@example.com', '0423456789', 'Problem solving', '', 'Current'),
(3, '30737561325102026', '100002', 'Aisha', 'Rahman', '2001-02-28', 'Female', '88 King St', 'Newtown', 'NSW', '2042', 'aisha.r@example.com', '0434567890', 'Communication', 'Grant writing', 'New'),
(4, '34437561325102026', '100002', 'Noah', 'Williams', '1990-07-19', 'Male', '3 Queen St', 'Brisbane', 'QLD', '4000', 'noah.w@example.com', '0445678901', 'Problem solving,Teamwork', '', 'Final'),
(5, '38137561325102026', '100003', 'Sam', 'Lee', '1998-09-09', 'Other', '21 Rundle Mall', 'Adelaide', 'SA', '5000', 'sam.lee@example.com', '0456789012', 'Communication,Teamwork', 'Canva, social media', 'New'),
(6, '41837561325102026', '100003', 'Olivia', 'Smith', '2003-12-01', 'Female', '7 Hay St', 'Perth', 'WA', '6000', 'olivia.s@example.com', '0467890123', 'Teamwork', '', 'Current');

INSERT INTO `users` (`eoi_number`, `password_hash`, `failed_attempts`, `lockout_until`, `lockout_minutes`) VALUES
(1, '$2y$10$VJ1PRb3/y4EpsFWATkNovel41cibLdSWLZ5lXxXdN2DQxhJd2bjcG', 0, NULL, 15),
(2, '$2y$10$VJ1PRb3/y4EpsFWATkNovel41cibLdSWLZ5lXxXdN2DQxhJd2bjcG', 0, NULL, 15),
(3, '$2y$10$VJ1PRb3/y4EpsFWATkNovel41cibLdSWLZ5lXxXdN2DQxhJd2bjcG', 0, NULL, 15),
(4, '$2y$10$VJ1PRb3/y4EpsFWATkNovel41cibLdSWLZ5lXxXdN2DQxhJd2bjcG', 0, NULL, 15),
(5, '$2y$10$VJ1PRb3/y4EpsFWATkNovel41cibLdSWLZ5lXxXdN2DQxhJd2bjcG', 0, NULL, 15),
(6, '$2y$10$VJ1PRb3/y4EpsFWATkNovel41cibLdSWLZ5lXxXdN2DQxhJd2bjcG', 0, NULL, 15);

INSERT INTO `staff` (`username`, `password_hash`, `failed_attempts`, `lockout_until`, `lockout_minutes`) VALUES
('admin', '$2y$10$ioi25gIcURU7giCkK5jJeeOQ.HVXloCh1AiHXtz4MV3r/djh0BFfG', 0, NULL, 15);

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
