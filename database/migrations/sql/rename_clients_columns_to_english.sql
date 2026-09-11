-- Rename kolom tabel clients ke bahasa Inggris + soft-delete flag (idempotent)
-- Setara dengan migration 2026_09_10_000001 & 2026_09_10_000002

ALTER TABLE clients RENAME COLUMN pic TO ps;
ALTER TABLE clients RENAME COLUMN nama_user TO contact_name;
ALTER TABLE clients RENAME COLUMN no_telpon TO contact_phone;
ALTER TABLE clients RENAME COLUMN alamat_user TO contact_address;
ALTER TABLE clients RENAME COLUMN tanggal_lahir TO contact_birth_date;
ALTER TABLE clients RENAME COLUMN jabatan TO contact_position;
ALTER TABLE clients RENAME COLUMN hobby_client TO contact_hobby;
ALTER TABLE clients RENAME COLUMN nama_apoteker TO pharmacist_name;
ALTER TABLE clients RENAME COLUMN nomor_sipa TO pharmacist_license_no;
ALTER TABLE clients RENAME COLUMN no_telpon_apoteker TO pharmacist_phone;
ALTER TABLE clients RENAME COLUMN komisi TO commission_rate;
ALTER TABLE clients RENAME COLUMN nama_perusahaan TO customer_name;
ALTER TABLE clients RENAME COLUMN tanggal_berdiri TO company_founded_date;
ALTER TABLE clients RENAME COLUMN alamat_perusahaan TO company_address;
ALTER TABLE clients RENAME COLUMN bank TO bank_name;
ALTER TABLE clients RENAME COLUMN no_rekening TO bank_account_number;
ALTER TABLE clients RENAME COLUMN nama_di_rekening TO bank_account_name;
ALTER TABLE clients RENAME COLUMN saldo_awal TO opening_balance;

ALTER TABLE clients RENAME COLUMN contact_name TO client_name;

ALTER TABLE clients ADD COLUMN is_deleted TINYINT(1) NOT NULL DEFAULT 0 AFTER commission_rate;