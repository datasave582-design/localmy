ApnaLocal SuperApp - Admin Login

1. Firebase Console -> Authentication -> Sign-in method -> Email/Password enable करें.
2. Authentication -> Users में Admin email/password user बनाएं.
3. Realtime Database -> admins में इस exact UID को true रखें: 2UAvwgH34nYRoqqg4enno571N9k2 : true
4. admin.html खोलें. कोई automatic login नहीं होगा. Email ID और Firebase Password manually डालना होगा.
5. Database Rules के लिए database.rules.json publish करें.
6. Admin Dashboard में users, posts, orders, advertisements, tickets आदि controls हैं.
