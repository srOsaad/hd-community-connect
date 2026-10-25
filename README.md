## Project Documentation

This section accompanies the Applied Web Project Part 2 submission for
COS10026 Web Technology Project, Group HD.

### Project overview
Community Connect is a fictional community and social services
organisation (allocated scenario G03). Part 1 built the site as static
HTML5 and CSS3. Part 2 turns it into a dynamic PHP and MySQL site: job
listings come from the database, applicants submit and check their
applications online, and HR managers review and manage applications.

### Team and responsibilities
| Member | Student ID | Part 1 | Part 2 |
| --- | --- | --- | --- |
| Shahryar Rahman Saad (Sam) | 106406031 | Project lead, index.html | Shared includes, staff access control, index.php, manage.php, manager registration, shared error page |
| Gia Nghia Le (Tom) | 106424716 | Communication lead, apply.html | apply.php, process_eoi.php, applicant page |
| Shashwat Chhabra (Shash) | 106514329 | Project documentation, about.html | about.php, staff and applicant login and logout |
| Declan Elliott | 106052926 | QA, jobs.html | jobs.php, job_details.php, jobs_search.php |

### Running the site locally
1. Copy the project folder into XAMPP's `htdocs` folder and start Apache and MySQL.
2. In phpMyAdmin, create a database named `hd_community_connect`.
3. Open that database, go to the Import tab and import `hd_community_connect.sql`.
4. Visit `http://localhost/hd-community-connect/`.

Database settings are in `settings.php` (XAMPP defaults: user `root`, no password).

### Test accounts
| Account | Login | Password |
| --- | --- | --- |
| HR manager | `admin` (Staff Login) | `admin` |
| Sample applications 1 to 6 | Application ID from the `eoi` table, e.g. `23337561325102026` (Check Application) | `Test1234` |

### Pages
| Page | Purpose |
| --- | --- |
| `index.php` | Home page |
| `jobs.php`, `jobs_search.php`, `job_details.php` | Job listings, search and full job details from the `jobs` table |
| `apply.php`, `process_eoi.php`, `success.php` | Job application form, server-side validation and saving, confirmation with the application ID |
| `userlogin.php`, `applicant.php` | Applicants log in with their application ID and password to check status and edit while it is still New |
| `about.php` | Team page from the `group_info`, `members` and `contributions` tables |
| `stafflogin.php`, `manage.php`, `staffregister.php`, `logout.php` | HR manager login, application management and manager registration |

Shared files: `header.inc`, `nav.inc`, `footer.inc`, `db_connect.inc`, `settings.php`, `functions.inc`, `auth.inc`, `jobs.inc` and `error.inc`.

### Database tables
| Table | Holds |
| --- | --- |
| `jobs` | Job listings |
| `eoi` | Applications (expressions of interest) |
| `users` | Applicant logins, one per application |
| `staff` | HR manager logins |
| `group_info`, `members`, `contributions` | About page content |

### Manage page features
- List all applications, or search by application ID, job reference, first name, last name and status
- Sort by EOI number, job reference, first name, last name, state or status, ascending or descending
- Change an application's status (New, Current, Final)
- Delete all applications for a job reference, with a confirmation step
- Application counts by status

### Enhancements
- Manager registration with a unique username and a password rule; passwords are stored hashed
- Staff-only access to the manage pages, with logout after 30 minutes of inactivity
- Login lockout after 5 failed attempts, for staff and applicants
- Applicants get a unique application ID and password to check and edit their own application; their session also ends after 30 minutes of inactivity
- The apply form keeps entered values after a validation error
- One shared error page for database, not-found and form errors
- Prepared statements for every database query that uses input, and CSRF tokens on the manage, manager registration and applicant edit forms

### Acknowledgements and third-party content
- Logo generated with Google Gemini.
- "What Is a Community?" quotation adapted from the Wikipedia article "Community", licensed under CC BY-SA 4.0.
- Colour scheme adapted from the Australian Services Union's Social and Community Services page.
- Typefaces Source Serif 4 and Source Sans 3 by Adobe (Google Fonts, SIL OFL).
