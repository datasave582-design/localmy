APNALOCAL — FINAL ADMIN PACKAGE

1) Put index.html, admin.html and sw.js in the same web hosting folder.
2) Firebase project: local-9a38c
3) Enable Authentication > Google.
4) Sign in once using your admin Google account at admin.html.
5) Copy the Firebase UID shown in the non-admin message / Firebase Auth user list.
6) Replace PASTE_YOUR_ADMIN_FIREBASE_UID_HERE in BOTH:
   - admin.html (ADMIN_UIDS array)
   - database.rules.json (all occurrences)
7) Firebase Console > Realtime Database > Rules: paste database.rules.json contents and Publish.
8) Open /admin.html. Admin can:
   - add/delete/approve/reject products/posts
   - manage orders and statuses
   - block/unblock user profiles
   - read/complete help tickets
   - create/stop advertisements
   - change advertisement title/text/image/link/start/end
9) Advertisement is synced at settings/advertising and is shown by the main app.
10) Admin products are permanent and use direct Order. Normal user products are Chat-only.

IMPORTANT SECURITY:
Do not use a public write rule in production. The placeholder UID MUST be replaced before publishing rules.
The Firebase web config itself is not a password; Database Rules and Authentication protect the data.
