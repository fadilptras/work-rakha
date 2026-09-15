-- Query SQL untuk membuat tabel sales_outlet_closings

CREATE TABLE IF NOT EXISTS `sales_outlet_closings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `year` int(11) NOT NULL,
  `month` varchar(255) NOT NULL,
  `ps` varchar(255) DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `closing_rate` decimal(5,2) DEFAULT NULL,
  `closing_count` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_sales_outlet_closing` (`year`, `month`, `ps`, `customer_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
