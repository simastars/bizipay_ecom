-- Migration: add referral fields to tbl_customer
ALTER TABLE tbl_customer
ADD COLUMN cust_referral_code VARCHAR(50) NULL UNIQUE AFTER cust_timestamp,
ADD COLUMN cust_referred_by INT NULL AFTER cust_referral_code;

-- Optional: add a settings field for referral amount (if you manage settings manually)
-- ALTER TABLE tbl_settings ADD COLUMN referral_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00;
