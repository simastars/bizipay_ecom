-- Virtual Accounts / Wallet integration tables

CREATE TABLE IF NOT EXISTS `tbl_virtual_account` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cust_id` int NOT NULL,
  `reference` varchar(100) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `account_name` varchar(255) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_id` varchar(50) DEFAULT NULL,
  `reserved_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('reserved','active','kyc_pending','kyc_validated','closed') DEFAULT 'reserved',
  `meta` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference_unique` (`reference`),
  KEY `cust_idx` (`cust_id`),
  UNIQUE KEY `account_number_unique` (`account_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbl_virtual_account_kyc` (
  `id` int NOT NULL AUTO_INCREMENT,
  `virtual_account_id` int NOT NULL,
  `cust_id` int NOT NULL,
  `bvn` varchar(50) DEFAULT NULL,
  `provider_response` json DEFAULT NULL,
  `status` enum('pending','validated','failed') DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `va_idx` (`virtual_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbl_virtual_account_tx` (
  `id` int NOT NULL AUTO_INCREMENT,
  `virtual_account_id` int NOT NULL,
  `cust_id` int NOT NULL,
  `provider_ref` varchar(100) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'NGN',
  `status` enum('pending','verified','credited','ignored') DEFAULT 'pending',
  `provider_payload` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `va_tx_idx` (`virtual_account_id`),
  UNIQUE KEY `provider_ref_unique` (`provider_ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
