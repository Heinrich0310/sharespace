# ShareSpace — Project Study Notes

A plain-English explainer of every part of the website, written so you can answer questions from lecturers, examiners, or investors.

**Project**: ShareSpace — a peer-to-peer rental marketplace for South African communities.
**Built by**: Heinrich Potgieter
**Live URL**: https://sharespace.infinityfreeapp.com
**Local URL**: http://localhost:8888/sharespace/

---

## 1. THE BIG PICTURE — Why This Project?

### Q: "What problem does ShareSpace solve?"
**A:** Most South African households own expensive items — power drills, party tents, sound systems, generators — that sit unused 95% of the time. At the same time, neighbours often need those exact items for a one-off project and can't justify buying them. Hire shops are expensive and far away, especially in township areas. ShareSpace is a digital marketplace that connects the two: owners earn cash from idle items, renters get cheap local access.

### Q: "Why a website and not an app?"
**A:** A website works on every phone and every laptop without an install. For an MVP (Minimum Viable Product) in a low-data, low-storage market, the web is the fastest, cheapest way to prove the idea. Native apps are on the roadmap for Phase 3 once user demand is validated.

### Q: "Why focus on townships?"
**A:** Township economies already share resources informally (lending tools, hiring chairs for funerals). Formalising it into a trusted platform creates side income for owners and lowers cost-of-living for renters. It also targets a market that big hire-shop chains don't serve.

---

## 2. TECHNOLOGY CHOICES — What Did I Use and Why?

| Layer | Choice | Why I Chose It |
|---|---|---|
| **Frontend** | HTML5 + CSS3 + vanilla JavaScript | No frameworks → less code to learn, faster page loads, easier marking by lecturers, no build step needed. |
| **Backend** | PHP 8.3 | Server-side, free, widely supported on cheap hosting (InfinityFree, cPanel), strong database support, perfect for a CRUD-heavy marketplace. |
| **Database** | MySQL 8 (InnoDB) | Industry standard for web apps. InnoDB engine gives me foreign keys, transactions, and ACID guarantees — crucial when one user delete must cascade to rentals, reviews, payments etc. |
| **DB Driver** | PDO with prepared statements | Modern, object-oriented, parameterised queries prevent SQL injection. |
| **Local Dev** | MAMP (PHP 8.3.30, Apache :8888, MySQL :8889) | One-click stack on macOS, lets me develop offline. |
| **Production** | InfinityFree free hosting | Zero-cost for a student project, supports PHP/MySQL out of the box, has cPanel + phpMyAdmin. |
| **Deployment** | FTP via `lftp` | Simple, scriptable, no Git server needed on a free host. |
| **Typography** | Google Fonts (Syne + DM Sans) | Free, no download needed, gives a polished, modern look. |
| **Photos** | Unsplash CDN | Free high-quality photos, served from a global CDN — no bandwidth cost on my server. |

### Q: "Why no framework like Laravel or React?"
**A:** Frameworks add complexity. For a single-developer student MVP I wanted full control of the HTML, CSS and SQL — and to *understand* every piece. If the project succeeds and grows, the codebase is small enough to migrate to Laravel later without huge rework.

### Q: "Why MySQL and not MongoDB / Firebase?"
**A:** The data is highly relational — every rental belongs to a listing, every listing belongs to a user, every review belongs to a rental. SQL with foreign keys enforces those relationships and prevents orphan rows. A document database would have made `JOIN` queries clumsy and lost data integrity guarantees.

---

## 3. DATABASE DESIGN — The 8 Tables

```
users ──┬─< listings ──< rentals ──< reviews
        │                  │
        │                  └──< payments
        │
        ├─< messages
        └─< wishlists >── listings
                 ▲
       categories ─┘
```

| Table | Purpose | Key Columns |
|---|---|---|
| `users` | Anyone who signs up (renter, owner, admin) | `user_id`, `email`, `password_hash`, `role`, `location` |
| `categories` | The 4 fixed categories (Tools, Furniture, Electronics, Gardening) | `category_id`, `category_name` |
| `listings` | Every item up for rent | `listing_id`, `user_id` (owner), `category_id`, `title`, `price_per_day`, `image_path`, `availability_status` |
| `rentals` | Each booking made by a renter | `rental_id`, `listing_id`, `renter_id`, `start_date`, `num_days`, `total_price`, `status` |
| `reviews` | Star ratings + comments after a rental | `review_id`, `rental_id`, `reviewer_id`, `rating`, `comment` |
| `messages` | Direct messages between users (also used for admin notifications) | `message_id`, `sender_id`, `receiver_id`, `listing_id`, `message` |
| `wishlists` | Saved/favourited listings per user | `wishlist_id`, `user_id`, `listing_id` |
| `payments` | Payment record per rental (manual now, real gateway later) | `payment_id`, `rental_id`, `amount`, `method`, `status` |

### Q: "Why store the password as a hash and not the actual password?"
**A:** If the database is ever leaked, plain passwords would be a disaster — most people reuse the same one everywhere. I use PHP's `password_hash()` (which uses bcrypt) so even I can never read a user's password. `password_verify()` then checks a login attempt against the hash without ever reversing it.

### Q: "Why a separate `rentals` table instead of just dates on `listings`?"
**A:** One listing can be rented hundreds of times. Each rental needs its own status (`pending → active → completed → cancelled`), renter, date, total. Putting that on the listing row would be impossible — it's a classic one-to-many relationship.

### Q: "What is a foreign key and why does it matter?"
**A:** A foreign key is a column that points to a primary key in another table. For example, `rentals.listing_id` points to `listings.listing_id`. The database refuses to insert a rental for a listing that doesn't exist, and won't let me delete a listing that still has active rentals (unless I delete the rentals first). This prevents broken data.

---

## 4. CORE FEATURES — How Each One Works

### 4.1 User Registration (`register.php`)
1. User fills in the form (name, email, phone, location, password).
2. PHP validates: required fields present, passwords match, at least 6 characters.
3. Checks email isn't already taken (`SELECT user_id FROM users WHERE email = ?`).
4. Hashes the password with `password_hash($password, PASSWORD_DEFAULT)` (bcrypt).
5. Inserts the user with a prepared statement.
6. Immediately logs them in by setting `$_SESSION` variables and redirects to homepage.

**Q: "Why bcrypt and not MD5 or SHA1?"**
A: MD5 and SHA1 are *fast* — which is bad for passwords because attackers can guess billions of hashes per second. bcrypt is deliberately slow (and has a built-in salt), making brute force impractical.

### 4.2 Login (`login.php`)
1. Form submits email + password.
2. `SELECT * FROM users WHERE email = ?` (prepared, no SQL injection).
3. `password_verify($entered, $user['password_hash'])` compares safely.
4. On success: sets `$_SESSION['user_id']`, `user_name`, `role` and redirects.
5. Admins go to `/admin/index.php`, normal users go to `index.php`.

### 4.3 Sessions
PHP sessions are how the server "remembers" you're logged in. After login, a small cookie holds your session ID; the server stores everything else. Every protected page begins with:
```php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
```

### 4.4 Listing an Item (`list_item.php`)
1. User uploads a photo + fills in title, price, category, description, delivery option.
2. PHP validates the file: must be `image/jpeg`, `image/png`, `image/webp`, or `image/gif`, under 5 MB.
3. File is renamed with `uniqid('listing_')` to prevent collisions and saved to `/uploads/listings/`.
4. Database insert: `INSERT INTO listings ...` with all values bound via prepared statement.

**Q: "Why rename the uploaded file?"**
A: If two users upload `photo.jpg`, the second one would overwrite the first. `uniqid()` guarantees a unique filename. It also stops users from uploading scripts with malicious names.

### 4.5 Browsing (`index.php`)
- Homepage runs a single `SELECT` that joins `listings`, `users`, and `categories`.
- Filters by category and search term using `WHERE` and `LIKE`.
- For each item, if the uploaded photo exists on disk it shows; otherwise it falls back to a Unsplash photo mapped from the listing title (e.g. "Petrol Lawn Mower" → matching Unsplash image).
- `LEFT JOIN users` (not `INNER JOIN`) so listings don't disappear if the owner is deleted.

### 4.6 Listing Detail (`listing.php`)
- Shows the full item details, owner info, price, reviews, and a "Request to Rent" form.
- Renter picks a start date and number of days; PHP calculates total price and inserts into `rentals` with status `pending`.
- A wishlist heart icon toggles save/unsave via `wishlist_toggle.php`.

### 4.7 Messaging (`messages.php`)
- Lists all conversations the logged-in user is part of.
- Sending a message is a single `INSERT INTO messages (sender_id, receiver_id, listing_id, message)`.
- Same table is reused for admin notifications when a listing is removed.

### 4.8 Reviews
- After a rental status is `completed`, the renter can submit a 1–5 star rating + comment.
- One `INSERT INTO reviews` per rental.

### 4.9 Wishlist (`wishlist_toggle.php`)
- A tiny endpoint: checks if a row exists in `wishlists` for the user+listing pair. If yes, delete; if no, insert. Then redirects back.

---

## 5. ADMIN PANEL — `/admin/`

A separate set of pages protected by `$_SESSION['role'] === 'admin'`. Anyone else gets redirected to login.

| Page | Function |
|---|---|
| `admin/index.php` | Dashboard with metric cards: total users, total listings, total rentals, pending rentals. Recent rentals table. |
| `admin/users.php` | Lists all users. Can delete (cascades through every related table inside a transaction). Cannot delete admins. |
| `admin/listings.php` | Lists all listings. Can toggle availability or remove a listing with a reason (which becomes an inbox message to the owner). |
| `admin/rentals.php` | View and manage all rentals — change status, cancel, etc. |

### Q: "What happens if I delete a user who has 5 listings, 12 rentals, and 3 reviews?"
**A:** I use a **PDO transaction** with manual cascade deletion in the correct order:
```
1. payments (linked via rentals.listing_id where listing.user_id = X)
2. payments (linked via rentals where renter_id = X)
3. reviews on the user's rentals
4. reviews left by the user
5. rentals on the user's listings
6. rentals where the user is renter
7. wishlists pointing at the user's listings
8. listings owned by the user
9. the user row itself
```
All of it is wrapped in `beginTransaction() ... commit()` — if any step fails, `rollBack()` undoes everything and nothing changes. This prevents the dreaded "deleted user but orphan rentals everywhere" bug that crashes other pages.

### Q: "Why a transaction?"
**A:** Without one, if step 5 succeeds but step 6 fails, you're left with half-deleted data and a broken site. A transaction is **atomic**: it's either all-or-nothing. This is one of the most important ACID guarantees in databases.

---

## 6. SECURITY — How the Site is Protected

| Risk | How I Defend |
|---|---|
| **SQL Injection** | Every query uses PDO prepared statements with `?` placeholders. User input is never concatenated into SQL. |
| **XSS (Cross-Site Scripting)** | Every value printed into HTML uses `htmlspecialchars()` so script tags become harmless text. |
| **Password Theft** | Passwords are bcrypt-hashed; the raw password never touches the database. |
| **Session Hijacking** | Sessions are managed by PHP's built-in mechanism over HTTPS on the live site. |
| **File Upload Abuse** | Only specific MIME types allowed (jpeg/png/webp/gif), 5 MB max, files renamed to prevent overwrites or executable extensions. |
| **Unauthorized Admin Access** | Every admin page checks `$_SESSION['role'] === 'admin'` and redirects if false. |
| **Orphan Data / Broken Cascades** | Foreign keys + transactional deletes. |
| **Mass Assignment** | I only `INSERT` and `UPDATE` specific named columns — never blindly accept all `$_POST` values. |

### Q: "What is SQL injection and show me where you prevent it?"
**A:** SQL injection is when an attacker enters something like `' OR 1=1 --` in a form, hoping the server pastes it raw into a query and gives them admin access. Example **bad** code: `"SELECT * FROM users WHERE email = '$email'"`. Example **good** code (mine): `$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?"); $stmt->execute([$email]);` — the database driver treats the input as data, never code.

### Q: "What is XSS?"
**A:** Cross-site scripting — when someone enters `<script>alert(1)</script>` as their name, and the site renders it raw, suddenly every visitor's browser runs that script. I use `htmlspecialchars()` everywhere I print user data, which converts `<` and `>` into `&lt;` and `&gt;`.

---

## 7. KEY DESIGN DECISIONS

### Q: "Why one big `index.php` instead of splitting it into smaller files?"
**A:** For a student MVP, keeping the homepage in one file is easier to mark, easier to grep, and there's no routing or build complexity. In a production app I'd refactor each section into includes or use a router.

### Q: "Why store image paths as strings in the DB instead of the binary image?"
**A:** Storing binary images in MySQL bloats the database and slows queries. Best practice is to keep files on disk (or cloud storage) and store only the path. Backups stay small and `SELECT`s are fast.

### Q: "Why a single `messages` table for both user-to-user chat and admin notifications?"
**A:** They're the same shape: sender, receiver, optional listing, text. Reusing the table means one inbox UI displays both — the admin "Your listing was removed" notice arrives in exactly the same place as a normal user message. Less code, less complexity.

### Q: "Why the colour scheme (orange / cream / dark brown)?"
**A:** It's warm, friendly, and earthy — fitting for a community/sharing platform. It also avoids the cold corporate blue of LinkedIn/Facebook and feels more local. Three colours plus white = enough visual contrast without looking busy.

### Q: "Why custom CSS instead of Bootstrap or Tailwind?"
**A:** Custom CSS forced me to understand layout, flexbox, and grid from scratch — better for learning. The site is also lighter (no 200 KB framework). The trade-off is more code I have to write myself.

---

## 8. DEPLOYMENT — How It Got Online

1. Wrote and tested everything in MAMP on localhost.
2. Created a free InfinityFree account (subdomain `sharespace.infinityfreeapp.com`).
3. Used `includes/db.php` to detect which environment is running:
   ```php
   if ($_SERVER['HTTP_HOST'] === 'localhost:8888') { /* MAMP creds */ }
   else { /* InfinityFree creds */ }
   ```
4. Uploaded files via `lftp` (a scriptable FTP client).
5. Exported the local database with `mysqldump`, imported through InfinityFree's phpMyAdmin.

### Q: "Why detect the host instead of using environment variables?"
**A:** InfinityFree free tier doesn't let you set `.env` files or shell environment variables. Host detection is the simplest portable trick that works on both my laptop and the live server with zero config.

---

## 9. WHAT I LEARNED (talking points for lecturers)

- **Relational design** — modelling the rental flow taught me primary/foreign keys, normalisation, and one-to-many vs many-to-many.
- **PDO and prepared statements** — moved from procedural `mysqli` to OO PDO with parameter binding.
- **Transactions** — discovered why ACID matters when a cascade delete failed midway and broke my site.
- **Session-based authentication** — implemented login, logout, role-based access from scratch.
- **File uploads** — learned about MIME validation, unique filenames, directory permissions, and the difference between `$_FILES['tmp_name']` and the final destination.
- **Responsive CSS** — built a mobile-first layout with flexbox and CSS grid.
- **Deployment in the real world** — discovered how production environments differ (file paths, PHP versions, write permissions, anti-bot challenges).
- **Debugging cascade FK constraints** — the biggest lesson: always wrap multi-step deletes in a transaction.

---

## 10. KNOWN LIMITATIONS / FUTURE WORK

I am up-front about what's *not* yet built — investors and lecturers respect honesty more than over-claiming.

| Limit | Plan |
|---|---|
| Payments are manual (cash / EFT recorded) | Integrate Yoco or PayFast in Phase 2 |
| No email / SMS notifications | Hook in Mailgun + Clickatell SMS |
| No mobile app | PWA wrapper first, then native React Native |
| No image moderation | Manual admin review for now; later: AWS Rekognition |
| No reviews on the owner (only on the rental) | Add two-way review system |
| Single image per listing | Multi-image gallery |
| No real-time chat (page refresh required) | Add WebSocket / polling in Phase 3 |

---

## 11. ANSWERS TO LIKELY HOSTILE QUESTIONS

### "What stops a renter from damaging the item and disappearing?"
- Future trust system: ID verification (`is_verified` column already exists), star ratings on both sides, and an optional damage-waiver fee per booking.

### "How will you compete with Gumtree or Facebook Marketplace?"
- Those are for buying/selling, not renting. The booking calendar, owner-renter chat, and rental status workflow are purpose-built for short-term hires.

### "How will you make money?"
- Service fee per completed rental (small % charged at checkout).
- Featured/promoted listings.
- Optional damage protection add-on.
- B2B bulk contracts with schools, churches, community organisations.

### "What's your customer acquisition cost?"
- Township word-of-mouth is the cheapest channel. Partnering with local stokvels and community WhatsApp groups costs almost nothing per signup.

### "Why should I invest now and not in 12 months?"
- The full platform is already built and live — investment goes directly to growth (marketing, mobile app, payment integration) instead of building. Cheaper to validate now than after a competitor enters.

---

## 12. QUICK FILE MAP (if asked "show me where X happens")

| Feature | File |
|---|---|
| Homepage / browse | `index.php` |
| Login | `login.php` |
| Register | `register.php` |
| Logout | `logout.php` |
| Item detail + booking | `listing.php` |
| Create listing | `list_item.php` |
| Edit listing | `edit_listing.php` |
| Delete listing (user) | `delete_listing.php` |
| User dashboard | `dashboard.php` |
| Messages | `messages.php` |
| Save to wishlist | `wishlist_toggle.php` |
| Database connection | `includes/db.php` |
| Admin dashboard | `admin/index.php` |
| Admin users | `admin/users.php` |
| Admin listings | `admin/listings.php` |
| Admin rentals | `admin/rentals.php` |
| Database schema + seed data | `sharespace_import.sql` / `full_fresh.sql` |

---

## 13. ONE-LINE PITCH

> ShareSpace is a peer-to-peer rental marketplace that turns idle household items into community income — currently live, built from scratch with PHP, MySQL, and pure HTML/CSS/JS, with full user authentication, admin moderation, and a transactional cascade-safe database.

---
*End of notes — good luck with the presentation!*
