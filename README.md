# Edbridge by W3bb

COS10026 Applied Web Project, Part 2: Server-Side & Dynamic Web Programming. Edbridge is a fictional education careers website created by four students in Hanoi Class 2, Friday morning.

Part 2 turns the static Part 1 site into a dynamic PHP and MySQL web application: shared page parts are PHP includes, job listings and team details load from the database, applications are validated on the server and stored as Expressions of Interest (EOIs), and a login-protected manager page lets HR query and update them.

> **Status:** Part 2 is in progress. Items marked *(planned)* are not built yet; see the [Jira board](https://soulknightprequel15.atlassian.net/jira/software/projects/CGW/boards/3) for current progress.

## Project links

- [GitHub repository](https://github.com/nguyenquocan0606/cos10026_web)
- [Jira board](https://soulknightprequel15.atlassian.net/jira/software/projects/CGW/boards/3)
- [Part 1 live site](https://nguyenquocan0606.github.io/cos10026_web/) (static HTML version; GitHub Pages cannot run PHP)

## Running the site locally

1. Install XAMPP (or another local server with PHP and MySQL) and start Apache and MySQL.
2. Copy the `project2/` folder into the server's web root (for XAMPP: `htdocs/`).
3. In phpMyAdmin (`http://localhost/phpmyadmin`), create the database named in `settings.php` and import the project's `.sql` file.
4. Open `http://localhost/project2/index.php`.

`settings.php` connects as `root` with **no password**, as the brief requires.

**Manager login:** username `admin`, password `admin`. The password is stored only as a bcrypt hash made with PHP's `password_hash()`.

## Team

| Member | Student ID | Part 1 | Part 2 |
| --- | --- | --- | --- |
| Tran Trung Tin | 106200136 | Jira setup, About page and shared footer | `about` and `users` tables, About page from the database, login and logout with account lockout, Jira, README |
| Nguyen Quoc An | 105550652 | Home page, GitHub Pages deployment and final ZIP | Shared PHP includes, `settings.php` and helper functions, folder restructure, manager page, final SQL file and submission |
| Nguyen Chi Hien | 106393625 | Jobs page and navigation/layout review | `jobs` table, Jobs and Home pages from the database, job search, styling review of new pages |
| Do Dinh Phu | 105559808 | Apply page and form validation | `eoi` table, apply form and `process_eoi.php` with server-side validation, final link and validator check |

## Files

### Pages

- `index.php`: company introduction, hiring process and open jobs from the database. *(planned)*
- `jobs.php`: job descriptions loaded from the `jobs` table. *(planned)*
- `search.php`: job search results from the header search bar. *(planned)*
- `apply.php`: application form posting to `process_eoi.php`; browser validation is disabled. *(planned)*
- `process_eoi.php`: validates and sanitises the application on the server, creates the `eoi` table if missing, saves the EOI and shows its EOInumber. Cannot be opened directly. *(planned)*
- `about.php`: team details and Part 1 and Part 2 contributions loaded from the `about` table. *(planned)*
- `login.php` / `logout.php`: manager sign-in and sign-out, with a temporary lock after 3 failed attempts. *(planned)*
- `manage.php`: login-protected HR page to list EOIs (all, by job reference, by first and/or last name), change an EOI's status, delete all EOIs for a job reference, and sort results. *(planned)*

### Shared code

- `header.inc`, `nav.inc`, `footer.inc`: page parts included by every page. *(planned)*
- `settings.php`: database host, user, password (empty) and database name. *(planned)*
- `functions.php`: database connection, input sanitising, safe output and login check helpers. *(planned)*

### Database

| Table | Purpose |
| --- | --- |
| `eoi` | Submitted applications: one column per apply-form field, plus `status` (New, Current, Final; default New). *(planned)* |
| `jobs` | Job listings shown on the Jobs, Home and search pages. *(planned)* |
| `about` | Team members, fun facts and Part 1 and Part 2 contributions. Source: [`sql/about.sql`](sql/about.sql) |
| `users` | Manager accounts: unique username, bcrypt password hash and lockout columns. Source: [`sql/users.sql`](sql/users.sql) |

The per-table SQL files in `sql/` will be merged into a single `.sql` file for submission.

### Assets

- `styles/style.css`: shared and page-specific styling.
- `styles/images/`: images used by the CSS (background patterns).
- `images/`: logo, cover image and group photo.
- `media/video.mp4`: brand introduction video.

## Security

- All form validation happens on the server; input is trimmed, has slashes stripped and is HTML-escaped before use.
- Database queries use prepared statements.
- Passwords are stored as bcrypt hashes and checked with `password_verify()`.
- `manage.php` requires a logged-in session, and accounts lock temporarily after 3 failed logins.
- The minimum requirements use no JavaScript.

## Notes

The email address `careers@edbridge.example` is a fictional demonstration contact. The application form is for assessment testing, not real recruitment.

## Credits

OpenAI Codex assisted with drafting and editing webpage content and CSS logo cropping in Part 1. Claude (Anthropic) assisted with planning Part 2, drafting the `about` and `users` SQL files and this README. The logo and brand intro video were generated using Gemini Argon.
