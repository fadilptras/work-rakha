const express = require('express');
const cors = require('cors');
const { makeWASocket, useMultiFileAuthState, DisconnectReason, Browsers } = require('@whiskeysockets/baileys');
const pino = require('pino');
const qrcode = require('qrcode-terminal');

const app = express();
app.use(cors());
app.use(express.json());

let sock = null;
let isReady = false;

async function connectToWhatsApp() {
    const { state, saveCreds } = await useMultiFileAuthState('auth_info_baileys');

    sock = makeWASocket({
        auth: state,
        logger: pino({ level: 'silent' }),
        browser: Browsers.ubuntu('Chrome')
    });

    sock.ev.on('creds.update', saveCreds);

    sock.ev.on('connection.update', (update) => {
        const { connection, lastDisconnect, qr } = update;

        if (qr) {
            console.log('\nScan QR Code ini dengan WhatsApp Anda:');
            qrcode.generate(qr, { small: true });

            console.log('\n======================================================');
            console.log('⚠️ JIKA QR CODE DI ATAS TERPOTONG/SUSAH DI-SCAN ⚠️');
            console.log('1. Copy Teks Panjang di bawah ini:');
            console.log('\n' + qr + '\n');
            console.log('2. Buka web: https://www.the-qrcode-generator.com/');
            console.log('3. Pilih menu "Text", lalu Paste teks tadi ke kotaknya.');
            console.log('4. Sebuah QR Code rapi akan muncul di web tersebut, scan dari sana!');
            console.log('======================================================\n');
        }

        if (connection === 'close') {
            const statusCode = lastDisconnect.error?.output?.statusCode;
            const shouldReconnect = statusCode !== DisconnectReason.loggedOut;
            console.log('Koneksi terputus! Error Code:', statusCode, '| Reconnecting:', shouldReconnect);
            isReady = false;

            if (shouldReconnect) {
                setTimeout(connectToWhatsApp, 5000); // Reconnect setelah 5 detik
            } else {
                console.log('Anda telah Log Out. Silakan hapus folder auth_info_baileys dan mulai ulang untuk scan QR baru.');
            }
        } else if (connection === 'open') {
            console.log('✅ WhatsApp Web Client (Baileys) is READY!');
            isReady = true;
        }
    });
}

// Inisialisasi awal
connectToWhatsApp();

// Endpoint Cek Status
app.get('/status', (req, res) => {
    if (isReady && sock) {
        res.json({
            status: 'connected',
            message: 'WhatsApp Client (Baileys) is connected and ready!'
        });
    } else {
        res.status(503).json({
            status: 'disconnected',
            message: 'WhatsApp Client is not connected.'
        });
    }
});

// Endpoint Mengirim Pesan (Mendukung localhost dan public via cPanel)
app.post(['/send', '/wa-api/send'], async (req, res) => {
    const { target, message } = req.body;

    if (!target || !message) {
        return res.status(400).json({ error: 'Target dan message diperlukan.' });
    }

    if (!isReady || !sock) {
        return res.status(503).json({ error: 'WhatsApp Client belum siap atau terputus.' });
    }

    // Format target (Baileys menggunakan @s.whatsapp.net untuk personal dan @g.us untuk grup)
    let formattedTarget = target;

    // Jika format dari Laravel masih @c.us, ubah ke @s.whatsapp.net
    if (formattedTarget.endsWith('@c.us')) {
        formattedTarget = formattedTarget.replace('@c.us', '@s.whatsapp.net');
    }

    // Jika tidak ada ekstensi (murni angka), tambahkan @s.whatsapp.net
    if (!formattedTarget.includes('@')) {
        formattedTarget = `${formattedTarget}@s.whatsapp.net`;
    }

    try {
        await sock.sendMessage(formattedTarget, { text: message });
        res.json({
            status: 'success',
            message: 'Pesan berhasil dikirim via Baileys!',
            target: formattedTarget
        });
    } catch (error) {
        console.error('Error saat mengirim pesan:', error);
        res.status(500).json({ error: 'Gagal mengirim pesan', details: error.toString() });
    }
});



// Jalankan Express Server
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`🚀 WA Server (Baileys) berjalan di port ${PORT}`);
    console.log('Menunggu inisialisasi Baileys...');
});
