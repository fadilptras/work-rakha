importScripts('https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.10.0/firebase-messaging.js');

// Konfigurasi Firebase Projek Kamu
const firebaseConfig = {
    apiKey: "AIzaSyAipKu1aZwvZaOFR_FbCtkD6jtPYI2e4XE",
    authDomain: "rakha-workflow.firebaseapp.com",
    projectId: "rakha-workflow",
    storageBucket: "rakha-workflow.firebasestorage.app",
    messagingSenderId: "1024207088181",
    appId: "1:1024207088181:web:cc835edf846ac65cf59f7c",
    measurementId: "G-3T6QNML81B"
};

// Inisialisasi di Background
firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

// Handler Pesan saat Browser di Background (Tab Tertutup/Minimize)
messaging.onBackgroundMessage((payload) => {
    console.log('[SW] Notif Background:', payload);
    
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/asset/images/logo-192x192.png', // Pastikan ikon ini ada & ukurannya pas
        badge: '/asset/images/logo-96x96.png'   // Ikon kecil di status bar (Android)
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});

// Event Klik Notifikasi
self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    // Buka URL jika ada di data click_action
    if (event.notification.data && event.notification.data.click_action) {
        event.waitUntil(
            clients.openWindow(event.notification.data.click_action)
        );
    }
});