# MyLocal — Final Hostinger + Firebase Auth Build

## Architecture
- Firebase: Authentication only (Email/Password, Google if enabled, password reset, auth session).
- Hostinger: PHP API + MySQL for app data.
- Hostinger `/uploads/`: all listing/shop/service/product images.
- MySQL never stores image Base64; it stores normal app JSON/path data.

## Install
1. Create a MySQL database on Hostinger.
2. Edit `api/config.php` and enter DB name/user/password.
3. Upload the complete folder to `public_html`.
4. Open `https://YOUR-DOMAIN/api/setup.php` once.
5. Delete `api/setup.php` immediately after success.
6. In Firebase Authentication, enable the providers you use.
7. Keep the Firebase config in `index.html` / `admin.html`; Firebase web config is not a database credential.
8. First admin: after the first user logs in, change that user's `role` from `user` to `admin` in MySQL. Future admin controls are server-checked.
9. Test `https://YOUR-DOMAIN/api/health.php`.

## Important
- Use HTTPS.
- Do not put MySQL credentials in HTML/JS.
- Do not use Firebase Realtime Database or Firebase Storage for app data/images.
- `upload.php` accepts JPG/PNG/WebP up to 8MB and requires a valid Firebase ID token.
- Seller listing uploads go to Hostinger. Admin-only shop/service/product/event/ad uploads are server-protected.
- `host-db.js` provides a Firebase-Realtime-Database-compatible adapter so the existing UI can keep its working `db.ref(...).set/update/remove/once/on/push/transaction` calls while data is stored in Hostinger/MySQL.
