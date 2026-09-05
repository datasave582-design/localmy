/* FCM delivery worker. Application data is NOT stored in Firebase. */
importScripts('https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.8.0/firebase-messaging-compat.js');

firebase.initializeApp({
  apiKey: 'AIzaSyBBSsIRGaOswYUxMY6G6udxLM8V_-Tdytk',
  authDomain: 'local-9a38c.firebaseapp.com',
  projectId: 'local-9a38c',
  storageBucket: 'local-9a38c.firebasestorage.app',
  messagingSenderId: '251764724922',
  appId: '1:251764724922:web:7e9823c4e20249b79f1e83'
});

try {
  const messaging = firebase.messaging();
  messaging.onBackgroundMessage(payload => {
    const n = payload.notification || {};
    self.registration.showNotification(n.title || 'MyLocal', {
      body: n.body || 'नई सूचना',
      icon: '/icon-192.png',
      data: payload.data || {}
    });
  });
} catch(e) {}
