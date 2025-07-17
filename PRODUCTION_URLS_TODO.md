# Production URL & Hostname Update Checklist

Update the following locations before deploying to production:

---

## 1. Database Host (MySQL)
Replace `'localhost'` with your production database host if needed.

- [ ] **edit_bot.php** (line 9): `$host = 'localhost';`
- [ ] **delete_bot.php** (line 9): `$host = 'localhost';`
- [ ] **login_process.php** (line 3): `$host = 'localhost';`
- [ ] **api/get_bot_data.php** (line 20): `'localhost'` in `new mysqli`
- [ ] **see_queries.php** (line 7): `$host = 'localhost';`
- [ ] **submit_query.php** (line 10): `$host = 'localhost';`
- [ ] **signup_process.php** (line 3): `$host = 'localhost';`
- [ ] **bot.php** (line 6): `'localhost'` in `new mysqli`
- [ ] **dashboard.php** (line 9): `$host = 'localhost';`
- [ ] **create_bot.php** (line 9): `$host = 'localhost';`

---

## 2. Hardcoded Web URLs (Frontend/Backend Integration)
Replace local URLs with your production domain.

- [ ] **dashboard.php** (line 188):
  - `http://127.0.0.1:5001?bot_id=...` → `https://your-production-domain.com?bot_id=...`

- [ ] **chat_ui.html** (all instances):
  - `const apiBase = 'http://localhost/Mira/';` → `const apiBase = 'https://your-production-domain.com/';`
  - `fetch('http://localhost/Mira/submit_query.php', ...)` → `fetch('https://your-production-domain.com/submit_query.php', ...)`
  - `fetch(apiBase + 'api/get_bot_data.php?bot_id=' + bot_id)` → `fetch('https://your-production-domain.com/api/get_bot_data.php?bot_id=' + bot_id)`

---

## 3. API Endpoints
Update any other API endpoints using `localhost` or local paths to production URLs.

- [ ] **chat_ui.html** (see above)

---

## 4. Test All Integrations
After updating, test all integrations to ensure:
- No CORS issues
- All endpoints are reachable
- Database connections work

---

**Tip:**
- Search for `localhost`, `127.0.0.1`, `http://`, and `https://` in your codebase to catch any missed locations.
- Update environment variables or config files if you use them for URLs/hosts. 