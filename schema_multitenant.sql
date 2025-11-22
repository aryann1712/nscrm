-- Multi-tenant CRM schema (MySQL 5.7 compatible)
-- Import into a fresh database. Adjust the database name as needed.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
/*!40101 SET NAMES utf8mb4 */;

-- CREATE DATABASE IF NOT EXISTS crm_mt DEFAULT CHARACTER SET utf8mb4;
-- USE crm_mt;

-- Users (tenants and sub-users)
CREATE TABLE IF NOT EXISTS users (
  id INT NOT NULL AUTO_INCREMENT,
  owner_id INT NULL,
  is_owner TINYINT(1) NOT NULL DEFAULT 0,
  created_by_user_id INT NULL,
  name VARCHAR(255) NOT NULL,
  company_name VARCHAR(150) NULL,
  email VARCHAR(255) NOT NULL,
  email_verified TINYINT(1) NOT NULL DEFAULT 0,
  verification_pin VARCHAR(6) NULL,
  pin_expires_at TIMESTAMP NULL,
  phone VARCHAR(20) NULL,
  password VARCHAR(255) NULL,
  company_id INT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'admin',
  PRIMARY KEY (id),
  UNIQUE KEY uniq_users_owner_email (owner_id, email),
  UNIQUE KEY uniq_users_owner_phone (owner_id, phone),
  KEY idx_users_owner_id (owner_id),
  KEY idx_users_created_by (created_by_user_id),
  KEY idx_users_is_owner (is_owner)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- RBAC
CREATE TABLE IF NOT EXISTS permissions (
  id INT NOT NULL AUTO_INCREMENT,
  code VARCHAR(191) NOT NULL,
  description VARCHAR(255) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS roles (
  id INT NOT NULL AUTO_INCREMENT,
  owner_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  is_system TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_owner_role_name (owner_id, name),
  KEY idx_roles_owner_id (owner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS role_permissions (
  id INT NOT NULL AUTO_INCREMENT,
  owner_id INT NOT NULL,
  role_id INT NOT NULL,
  permission_id INT NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_role_perm (owner_id, role_id, permission_id),
  KEY idx_role_permissions_role (role_id),
  KEY idx_role_permissions_perm (permission_id),
  KEY idx_role_permissions_owner (owner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_roles (
  id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  role_id INT NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_user_role (user_id, role_id),
  KEY idx_user_roles_user (user_id),
  KEY idx_user_roles_role (role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS owner_feature_flags (
  id INT NOT NULL AUTO_INCREMENT,
  owner_id INT NOT NULL,
  feature_key VARCHAR(120) NOT NULL,
  is_enabled TINYINT(1) NOT NULL DEFAULT 1,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_owner_feature (owner_id, feature_key),
  KEY idx_off_owner (owner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Master/lookups (tenant scoped)
CREATE TABLE IF NOT EXISTS cities (
  id INT NOT NULL AUTO_INCREMENT,
  owner_id INT NOT NULL,
  created_by_user_id INT NULL,
  name VARCHAR(150) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_cities_owner_name (owner_id, name),
  KEY idx_cities_owner_id (owner_id),
  KEY idx_cities_created_by (created_by_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tags (
  id INT NOT NULL AUTO_INCREMENT,
  owner_id INT NOT NULL,
  created_by_user_id INT NULL,
  name VARCHAR(150) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_tags_owner_name (owner_id, name),
  KEY idx_tags_owner_id (owner_id),
  KEY idx_tags_created_by (created_by_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS terms_master (
  id INT NOT NULL AUTO_INCREMENT,
  owner_id INT NOT NULL,
  created_by_user_id INT NULL,
  text VARCHAR(500) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  display_order INT NOT NULL DEFAULT 1000,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY is_active (is_active),
  KEY display_order (display_order),
  KEY idx_terms_owner_id (owner_id),
  KEY idx_terms_created_by (created_by_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inventory related (tenant scoped)
CREATE TABLE IF NOT EXISTS inventory_categories (
  id INT NOT NULL AUTO_INCREMENT,
  owner_id INT NOT NULL,
  created_by_user_id INT NULL,
  name VARCHAR(190) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_invcat_owner_name (owner_id, name),
  KEY idx_invcat_owner_id (owner_id),
  KEY idx_invcat_created_by (created_by_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS inventory_sub_categories (
  id INT NOT NULL AUTO_INCREMENT,
  owner_id INT NOT NULL,
  created_by_user_id INT NULL,
  category_id INT NOT NULL,
  name VARCHAR(190) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_cat_sub (category_id, name),
  KEY category_id (category_id),
  KEY idx_invsubcat_owner_id (owner_id),
  KEY idx_invsubcat_created_by (created_by_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS inventory_units (
  id INT NOT NULL AUTO_INCREMENT,
  owner_id INT NOT NULL,
  created_by_user_id INT NULL,
  code VARCHAR(50) NOT NULL,
  label VARCHAR(100) NULL,
  precision_format VARCHAR(20) NULL,
  active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_invunits_owner_code (owner_id, code),
  KEY idx_inv_units_owner_id (owner_id),
  KEY idx_inv_units_created_by (created_by_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS inventory_hsn (
  id INT NOT NULL AUTO_INCREMENT,
  owner_id INT NOT NULL,
  created_by_user_id INT NULL,
  code VARCHAR(64) NOT NULL,
  rate DECIMAL(5,2) NULL,
  note VARCHAR(255) NULL,
  active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY code (code),
  KEY idx_inv_hsn_owner_id (owner_id),
  KEY idx_inv_hsn_created_by (created_by_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS inventory (
  id INT NOT NULL AUTO_INCREMENT,
  owner_id INT NOT NULL,
  created_by_user_id INT NULL,
  name VARCHAR(500) NOT NULL,
  code VARCHAR(100) NOT NULL,
  importance ENUM('Low','Normal','High','Critical') DEFAULT 'Normal',
  category VARCHAR(200) NOT NULL,
  sub_category VARCHAR(200) NULL,
  quantity DECIMAL(10,2) DEFAULT '0.00',
  rate DECIMAL(10,2) DEFAULT '0.00',
  value DECIMAL(12,2) DEFAULT '0.00',
  tags TEXT,
  active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  batch VARCHAR(100) DEFAULT 'No',
  unit VARCHAR(50) DEFAULT 'no.s',
  store VARCHAR(200) NULL,
  item_type ENUM('products','materials','spares','assemblies') DEFAULT 'products',
  internal_manufacturing TINYINT(1) DEFAULT 1,
  purchase TINYINT(1) DEFAULT 0,
  std_cost DECIMAL(12,2) DEFAULT '0.00',
  purch_cost DECIMAL(12,2) DEFAULT '0.00',
  std_sale_price DECIMAL(12,2) DEFAULT '0.00',
  hsn_sac VARCHAR(100) NULL,
  gst DECIMAL(5,2) DEFAULT '0.00',
  description TEXT,
  internal_notes TEXT,
  min_stock DECIMAL(10,2) DEFAULT '0.00',
  lead_time INT DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_inventory_owner_id (owner_id),
  KEY idx_inventory_created_by (created_by_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- CRM entities
CREATE TABLE IF NOT EXISTS customers (
  id INT NOT NULL AUTO_INCREMENT,
  owner_id INT NOT NULL,
  created_by_user_id INT NULL,
  assigned_to_user_id INT NULL,
  company VARCHAR(200) NOT NULL,
  contact_name VARCHAR(150) NULL,
  contact_phone VARCHAR(30) NULL,
  contact_email VARCHAR(150) NULL,
  relation VARCHAR(50) NULL,
  website VARCHAR(200) NULL,
  industry_segment VARCHAR(150) NULL,
  country VARCHAR(100) NULL,
  state VARCHAR(100) NULL,
  type ENUM('customer','supplier','neighbour','friend') DEFAULT 'customer',
  executive VARCHAR(100) NULL,
  city VARCHAR(120) NULL,
  last_talk DATE NULL,
  next_action DATE NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY company (company),
  KEY contact_name (contact_name),
  KEY type (type),
  KEY city (city),
  KEY idx_customers_owner (owner_id),
  KEY idx_customers_created_by (created_by_user_id),
  KEY idx_customers_assigned_to (assigned_to_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS customers_addresses (
  id INT NOT NULL AUTO_INCREMENT,
  owner_id INT NOT NULL,
  created_by_user_id INT NULL,
  customer_id INT NOT NULL,
  title VARCHAR(120) NULL,
  line1 VARCHAR(255) NULL,
  line2 VARCHAR(255) NULL,
  city VARCHAR(120) NULL,
  country VARCHAR(100) NULL,
  state VARCHAR(100) NULL,
  pincode VARCHAR(20) NULL,
  gstin VARCHAR(20) NULL,
  extra_key VARCHAR(60) NULL,
  extra_value VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY customer_id (customer_id),
  KEY city (city),
  KEY state (state),
  KEY gstin (gstin),
  KEY idx_custaddr_owner_id (owner_id),
  KEY idx_custaddr_created_by (created_by_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS leads (
  id INT NOT NULL AUTO_INCREMENT,
  owner_id INT NOT NULL,
  created_by_user_id INT NULL,
  assigned_to_user_id INT NULL,
  business_name VARCHAR(255) NOT NULL,
  contact_person VARCHAR(255) NOT NULL,
  contact_email VARCHAR(255) NULL,
  contact_phone VARCHAR(50) NULL,
  source VARCHAR(100) NULL,
  stage VARCHAR(50) NULL,
  assigned_to VARCHAR(100) NULL,
  requirements TEXT,
  notes TEXT,
  potential_value DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  last_contact DATE NULL,
  next_followup DATE NULL,
  is_starred TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  salutation VARCHAR(10) NULL,
  first_name VARCHAR(100) NULL,
  last_name VARCHAR(100) NULL,
  designation VARCHAR(100) NULL,
  website VARCHAR(255) NULL,
  address_line1 VARCHAR(255) NULL,
  address_line2 VARCHAR(255) NULL,
  country VARCHAR(100) NULL,
  city VARCHAR(100) NULL,
  state VARCHAR(100) NULL,
  gstin VARCHAR(50) NULL,
  code VARCHAR(50) NULL,
  since DATE NULL,
  category VARCHAR(100) NULL,
  product VARCHAR(255) NULL,
  tags VARCHAR(255) NULL,
  company_id INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY stage (stage),
  KEY assigned_to (assigned_to),
  KEY source (source),
  KEY is_starred (is_starred),
  KEY created_at (created_at),
  KEY idx_leads_owner_id (owner_id),
  KEY idx_leads_created_by (created_by_user_id),
  KEY idx_leads_assigned_to (assigned_to_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS lead_products (
  id INT NOT NULL AUTO_INCREMENT,
  owner_id INT NOT NULL,
  created_by_user_id INT NULL,
  name VARCHAR(150) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_lead_products_owner_name (owner_id, name),
  KEY idx_lead_products_owner_id (owner_id),
  KEY idx_lead_products_created_by (created_by_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS lead_sources (
  id INT NOT NULL AUTO_INCREMENT,
  owner_id INT NOT NULL,
  created_by_user_id INT NULL,
  name VARCHAR(100) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_lead_sources_owner_name (owner_id, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sales docs
CREATE TABLE IF NOT EXISTS bank_accounts (
  id INT NOT NULL AUTO_INCREMENT,
  owner_id INT NOT NULL,
  created_by_user_id INT NULL,
  bank_name VARCHAR(100) NOT NULL,
  account_no VARCHAR(50) NOT NULL,
  branch VARCHAR(100) NULL,
  ifsc VARCHAR(20) NULL,
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY bank_name (bank_name),
  KEY idx_bank_accounts_owner_id (owner_id),
  KEY idx_bank_accounts_created_by (created_by_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS quotations (
  id INT NOT NULL AUTO_INCREMENT,
  owner_id INT NOT NULL,
  created_by_user_id INT NULL,
  assigned_to_user_id INT NULL,
  quote_no INT NOT NULL,
  customer VARCHAR(255) NOT NULL,
  amount DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  valid_till DATE NULL,
  issued_on DATE NULL,
  issued_by VARCHAR(100) NULL,
  type ENUM('Quotation','Proforma','Invoice','Retail') NOT NULL DEFAULT 'Quotation',
  executive VARCHAR(100) NULL,
  response VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  items_json JSON NULL,
  terms_json JSON NULL,
  notes TEXT,
  extra_charge DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  overall_discount DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  bank_account_id INT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'Open',
  attachment_path VARCHAR(255) NULL,
  reference VARCHAR(255) NULL,
  contact_person VARCHAR(100) NULL,
  party_address TEXT,
  received_amount DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  shipping_address TEXT,
  overall_gst_pct DECIMAL(6,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (id),
  UNIQUE KEY uniq_quotations_owner_quote (owner_id, quote_no),
  KEY quote_no (quote_no),
  KEY issued_on (issued_on),
  KEY type (type),
  KEY idx_quotations_owner_id (owner_id),
  KEY idx_quotations_created_by (created_by_user_id),
  KEY idx_quotations_assigned_to (assigned_to_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS invoices (
  id INT NOT NULL AUTO_INCREMENT,
  owner_id INT NOT NULL,
  created_by_user_id INT NULL,
  assigned_to_user_id INT NULL,
  invoice_no INT NOT NULL,
  customer VARCHAR(200) NOT NULL,
  reference VARCHAR(150) NULL,
  contact_person VARCHAR(150) NULL,
  party_address TEXT,
  shipping_address TEXT,
  issued_on DATE NOT NULL,
  valid_till DATE NULL,
  issued_by VARCHAR(150) NULL,
  type VARCHAR(30) DEFAULT 'Invoice',
  executive VARCHAR(120) NULL,
  status ENUM('Pending','Paid','Partial','Cancelled','Overdue') DEFAULT 'Pending',
  received_amount DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  amount DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  taxable_total DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  items_json MEDIUMTEXT,
  terms_json MEDIUMTEXT,
  notes TEXT,
  extra_charge DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  overall_discount DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  bank_account_id INT NULL,
  attachment_path VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_invoices_owner_no (owner_id, invoice_no),
  KEY invoice_no (invoice_no),
  KEY customer (customer),
  KEY issued_on (issued_on),
  KEY status (status),
  KEY idx_invoices_owner_id (owner_id),
  KEY idx_invoices_created_by (created_by_user_id),
  KEY idx_invoices_assigned_to (assigned_to_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders
CREATE TABLE IF NOT EXISTS orders (
  id INT NOT NULL AUTO_INCREMENT,
  owner_id INT NOT NULL,
  created_by_user_id INT NULL,
  assigned_to_user_id INT NULL,
  customer_id INT NOT NULL,
  contact_name VARCHAR(150) NULL,
  order_no VARCHAR(50) NULL,
  customer_po VARCHAR(100) NULL,
  category VARCHAR(100) NULL,
  due_date DATE NULL,
  status VARCHAR(50) NOT NULL DEFAULT 'Pending',
  total DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY customer_id (customer_id),
  KEY status (status),
  KEY due_date (due_date),
  KEY idx_orders_owner_id (owner_id),
  KEY idx_orders_created_by (created_by_user_id),
  KEY idx_orders_assigned_to (assigned_to_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_items (
  id INT NOT NULL AUTO_INCREMENT,
  owner_id INT NOT NULL,
  created_by_user_id INT NULL,
  order_id INT NOT NULL,
  item_name VARCHAR(200) NOT NULL,
  qty DECIMAL(12,2) NOT NULL DEFAULT '1.00',
  done_qty DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  unit VARCHAR(20) NOT NULL DEFAULT 'no.s',
  rate DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  amount DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (id),
  KEY order_id (order_id),
  KEY idx_order_items_owner_id (owner_id),
  KEY idx_order_items_created_by (created_by_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_terms (
  id INT NOT NULL AUTO_INCREMENT,
  owner_id INT NOT NULL,
  created_by_user_id INT NULL,
  order_id INT NOT NULL,
  term_text VARCHAR(500) NOT NULL,
  display_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY order_id (order_id),
  KEY display_order (display_order),
  KEY idx_order_terms_owner_id (owner_id),
  KEY idx_order_terms_created_by (created_by_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Settings (tenant scoped)
CREATE TABLE IF NOT EXISTS store_settings (
  id INT NOT NULL AUTO_INCREMENT,
  owner_id INT NOT NULL,
  created_by_user_id INT NULL,
  skey VARCHAR(191) NOT NULL,
  svalue LONGTEXT,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_store_settings_owner_key (owner_id, skey),
  KEY idx_store_settings_owner_id (owner_id),
  KEY idx_store_settings_created_by (created_by_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Optional: companies/products/supplier_invoices tenant scoped
CREATE TABLE IF NOT EXISTS companies (
  id INT NOT NULL AUTO_INCREMENT,
  owner_id INT NOT NULL,
  created_by_user_id INT NULL,
  name VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS products (
  id INT NOT NULL AUTO_INCREMENT,
  owner_id INT NOT NULL,
  created_by_user_id INT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) DEFAULT '0.00',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_products_owner_id (owner_id),
  KEY idx_products_created_by (created_by_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS supplier_invoices (
  id INT NOT NULL AUTO_INCREMENT,
  owner_id INT NOT NULL,
  created_by_user_id INT NULL,
  supplier_id INT NOT NULL,
  contact_name VARCHAR(150) NULL,
  invoice_no VARCHAR(100) NOT NULL,
  invoice_date DATE NULL,
  taxable_amount DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  total_amount DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  credit_month VARCHAR(20) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY supplier_id (supplier_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Foreign keys
ALTER TABLE users
  ADD CONSTRAINT fk_users_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_users_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE roles
  ADD CONSTRAINT fk_roles_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON UPDATE CASCADE;

ALTER TABLE role_permissions
  ADD CONSTRAINT fk_role_permissions_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON UPDATE CASCADE,
  ADD CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON UPDATE CASCADE;

ALTER TABLE user_roles
  ADD CONSTRAINT fk_user_roles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_user_roles_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE owner_feature_flags
  ADD CONSTRAINT fk_off_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE cities
  ADD CONSTRAINT fk_cities_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON UPDATE CASCADE,
  ADD CONSTRAINT fk_cities_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE tags
  ADD CONSTRAINT fk_tags_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON UPDATE CASCADE,
  ADD CONSTRAINT fk_tags_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE terms_master
  ADD CONSTRAINT fk_terms_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON UPDATE CASCADE,
  ADD CONSTRAINT fk_terms_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE inventory_categories
  ADD CONSTRAINT fk_invcat_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON UPDATE CASCADE,
  ADD CONSTRAINT fk_invcat_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE inventory_sub_categories
  ADD CONSTRAINT fk_inv_sub_cat FOREIGN KEY (category_id) REFERENCES inventory_categories(id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_invsubcat_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON UPDATE CASCADE,
  ADD CONSTRAINT fk_invsubcat_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE inventory_units
  ADD CONSTRAINT fk_inv_units_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON UPDATE CASCADE,
  ADD CONSTRAINT fk_inv_units_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE inventory_hsn
  ADD CONSTRAINT fk_inv_hsn_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON UPDATE CASCADE,
  ADD CONSTRAINT fk_inv_hsn_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE inventory
  ADD CONSTRAINT fk_inventory_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON UPDATE CASCADE,
  ADD CONSTRAINT fk_inventory_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE customers
  ADD CONSTRAINT fk_customers_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON UPDATE CASCADE,
  ADD CONSTRAINT fk_customers_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_customers_assigned_to FOREIGN KEY (assigned_to_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE customers_addresses
  ADD CONSTRAINT fk_custaddr_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON UPDATE CASCADE,
  ADD CONSTRAINT fk_custaddr_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_customers_addresses_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE leads
  ADD CONSTRAINT fk_leads_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON UPDATE CASCADE;

ALTER TABLE lead_products
  ADD CONSTRAINT fk_lead_products_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON UPDATE CASCADE,
  ADD CONSTRAINT fk_lead_products_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE lead_sources
  ADD CONSTRAINT fk_lead_sources_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON UPDATE CASCADE,
  ADD CONSTRAINT fk_lead_sources_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE bank_accounts
  ADD CONSTRAINT fk_bank_accounts_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON UPDATE CASCADE,
  ADD CONSTRAINT fk_bank_accounts_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE quotations
  ADD CONSTRAINT fk_quotations_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON UPDATE CASCADE,
  ADD CONSTRAINT fk_quotations_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_quotations_assigned_to FOREIGN KEY (assigned_to_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_quotations_bank_account FOREIGN KEY (bank_account_id) REFERENCES bank_accounts(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE invoices
  ADD CONSTRAINT fk_invoices_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON UPDATE CASCADE,
  ADD CONSTRAINT fk_invoices_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_invoices_assigned_to FOREIGN KEY (assigned_to_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_invoices_bank_account FOREIGN KEY (bank_account_id) REFERENCES bank_accounts(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE orders
  ADD CONSTRAINT fk_orders_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON UPDATE CASCADE,
  ADD CONSTRAINT fk_orders_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_orders_assigned_to FOREIGN KEY (assigned_to_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE order_items
  ADD CONSTRAINT fk_order_items_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON UPDATE CASCADE,
  ADD CONSTRAINT fk_order_items_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE order_terms
  ADD CONSTRAINT fk_order_terms_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON UPDATE CASCADE,
  ADD CONSTRAINT fk_order_terms_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_order_terms_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE store_settings
  ADD CONSTRAINT fk_store_settings_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON UPDATE CASCADE,
  ADD CONSTRAINT fk_store_settings_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE companies
  ADD CONSTRAINT fk_companies_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON UPDATE CASCADE,
  ADD CONSTRAINT fk_companies_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE products
  ADD CONSTRAINT fk_products_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON UPDATE CASCADE,
  ADD CONSTRAINT fk_products_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE supplier_invoices
  ADD CONSTRAINT fk_suppinv_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON UPDATE CASCADE,
  ADD CONSTRAINT fk_suppinv_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

-- Done.
