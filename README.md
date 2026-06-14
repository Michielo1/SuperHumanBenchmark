# SuperHumanBenchmark
This is a repository for the web application made by group E1 for the course Webtechnologie INF at the University of Amsterdam.

This web application contains multiple test where you can test various skills.
This website is similar to https://humanbenchmark.com/ but the tests here are more difficult.

**NOTE: This project was made in effectively about 2 weeks. There may be issues with this project, the code may not be up to standards everywhere so we could deliver the project on the given deadline.**

## More information:

University: University of Amsterdam <br>
Study: BSc Informatica <br>
Course: Webtechnologie <br>
Course code: 5061WEIN5Y <br>
Course coordinator: dr. M. Avgeris <br>
More course information at https://datanose.nl/#course[138341]

### Group: E1
Group members:

Anthony Wan,      16270118 <br>
Michiel Kamphuis, 15659763 <br>
David Nouwen,     16406877 <br>
Sean Li,          16068912 <br>
Sven Menting,     16306058

# API Documentation

This section documents the available HTTP API endpoints, their methods, parameters, and authentication requirements. All endpoints are served under the `/api` base path (exposed via `public_html/api` when deployed) *or* the set api endpoint in bootstrap.php.

---

## Public endpoints (no authentication required)
- **GET** `/api/tests.php` — Returns the list of available tests (id, name, description, type).
- **GET** `/api/stats.php` — Aggregated statistics per test. Optional query: `?test=<test_name>`.
- **GET** `/api/leaderboard.php?benchmark=<name>&limit=<n>` — Top scores for a benchmark. `benchmark` is required; `limit` defaults to 15 (max 50).

---

## Examples & response formats

A few concise examples showing typical requests and responses.

### Leaderboard (public)
Request:
```bash
curl "https://example.test/api/leaderboard.php?benchmark=reaction_test&limit=3"
```
Success response:
```json
{
  "success": true,
  "benchmark": {
    "id": 2,
    "test_name": "reaction_test",
    "test_description": "Reaction time test",
    "test_type": "minimize"
  },
  "leaderboard": [
    { "account_id": 12, "player_name": "Alice", "high_score": 215.32, "attempts": 8, "updated_at": "2025-10-03 12:34:56" },
    { "account_id": 7,  "player_name": "Bob",   "high_score": 223.11, "attempts": 4, "updated_at": "2025-09-20 11:01:00" }
  ]
}
```

### Stats (public)
Request:
```bash
curl "https://example.test/api/stats.php"
```
Success response:
```json
{
  "success": true,
  "tests": [
    { "id": 1, "test_name": "typing_test", "test_type": "maximize", "attempts_count": 1234, "avg_score": 72.4, "unique_players": 321 }
  ]
}
```

### Tests list (public)
Request:
```bash
curl "https://example.test/api/tests.php"
```
Success response (note keys returned by server):
```json
{
  "success": true,
  "tests": [
    { "id": 1, "naam": "typing_test", "beschrijving": "Type as many words as you can", "type": "maximize" }
  ]
}
```

---

# Project setup guide
Before an instance of this project can be spun up, there are a few steps to perform:

1. Set up the database<br>
Set up the database by copying `includes/config-example.php` to `includes/config.php` and correctly change the information within `includes/config.php`. Then run `php includes/setup.php` in the project root.
2. Set up an API symlink<br>
Create a symbolic link from `public_html/api` to `api` directory:
   ```bash
   ln -s /path/to/your/project/api /path/to/your/project/public_html/api
   # Example: ln -s /home/michielk/api /home/michielk/public_html/api
   ```
   Make sure the symlink is owned by your user (not root) for proper Apache access with `SymLinksIfOwnerMatch`.

   **Note:** The API base URL is centrally configured in `includes/bootstrap.php` via the `API_BASE_URL` constant. Client-side scripts read the base from `window.API_BASE_URL`, which is injected into pages by `public_html/components/cookie-consent-include.php`. To change the API host in production, set `API_BASE_URL` in `includes/config.php` to a full URL (for example `https://api.example.com/`) or override it via your deployment environment.

## Cache directory permissions

The application may use a file-backed cache at `cache/` (created under the project root) when APCu is not available. If your environment does not allow PHP to write into the project folder, the file-backed cache will not work. Create the directory and grant appropriate ownership and permissions for the webserver user.

Example commands (Linux, common setups):

```bash
# from project root
mkdir -p cache
# set owner to web server user (Debian/Ubuntu Apache)
sudo chown -R www-data:www-data cache
# or for Nginx on some systems
sudo chown -R nginx:nginx cache
# make directory writable
chmod 755 cache
```
