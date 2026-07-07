-- ============================================================
--  Restaurant / Coffee Shop POS System
--  Full Database Schema v5 — MySQL 8.0+ Optimized
-- ============================================================
--  Improvements applied based on review:
--  1. Converted to MySQL 8.0+ syntax (CHAR(36) UUID, DATETIME, ENUMs).
--  2. Moved 'low_stock_threshold' & 'reorder_qty' to 'ingredient_stocks'.
--  3. Added 'modifier_option_recipes' for tracking inventory of modifiers.
--  4. Replaced VARCHAR with ENUMs for strict data integrity (status/type cols).
--  5. Removed DB-level UNIQUE constraints on soft-deleted tables. 
--     * In MySQL, Partial Unique Indexes (WHERE deleted_at IS NULL) aren't natively supported.
--     * Application layer MUST enforce uniqueness for email, sku, phone, etc. when active.
-- ============================================================

SET FOREIGN_KEY_CHECKS=0;

-- ─────────────────────────────────────────────────────────────
--  0. SYSTEM INFO
-- ─────────────────────────────────────────────────────────────
CREATE TABLE system_info (
    id               INT          PRIMARY KEY DEFAULT 1,
    system_version   VARCHAR(50)  NOT NULL,
    database_version VARCHAR(50)  NOT NULL,
    license_key      VARCHAR(255),
    installed_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP
);

-- ─────────────────────────────────────────────────────────────
--  1. ROLES
-- ─────────────────────────────────────────────────────────────
CREATE TABLE roles (
    id           CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
    name         VARCHAR(50)  NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    description  TEXT,
    is_active    TINYINT(1)   NOT NULL DEFAULT 1,
    created_by   CHAR(36),
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by   CHAR(36),
    updated_at   DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by   CHAR(36),
    deleted_at   DATETIME     NULL
);

-- ─────────────────────────────────────────────────────────────
--  1.5 PERMISSIONS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE permissions (
    id           CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
    name         VARCHAR(100) NOT NULL UNIQUE,
    group_name   VARCHAR(50),
    description  TEXT,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE role_permissions (
    id            CHAR(36)  PRIMARY KEY DEFAULT (UUID()),
    role_id       CHAR(36)  NOT NULL,
    permission_id CHAR(36)  NOT NULL,
    created_by    CHAR(36),
    created_at    DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE user_permissions (
    id            CHAR(36)  PRIMARY KEY DEFAULT (UUID()),
    user_id       CHAR(36)  NOT NULL,
    permission_id CHAR(36)  NOT NULL,
    created_by    CHAR(36),
    created_at    DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, permission_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  2. USERS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE users (
    id                CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
    branch_id         CHAR(36),
    role_id           CHAR(36)     NOT NULL,
    username          VARCHAR(50)  NOT NULL, -- App layer uniqueness
    first_name        VARCHAR(75)  NOT NULL,
    last_name         VARCHAR(75)  NOT NULL,
    email             VARCHAR(150) NOT NULL, -- App layer uniqueness
    phone             VARCHAR(20),
    password_hash     VARCHAR(255) NOT NULL,
    avatar_url        TEXT,
    gender            ENUM('male', 'female', 'other'),
    date_of_birth     DATE,
    address           TEXT,
    emergency_contact VARCHAR(150),
    emergency_phone   VARCHAR(20),
    hire_date         DATE,
    is_active         TINYINT(1)   NOT NULL DEFAULT 1,
    last_login_at     DATETIME,
    created_by        CHAR(36),
    created_at        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by        CHAR(36),
    updated_at        DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by        CHAR(36),
    deleted_at        DATETIME     NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
);

-- ─────────────────────────────────────────────────────────────
--  3. ACTIVITY LOGS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE activity_logs (
    id          CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
    user_id     CHAR(36),
    branch_id   CHAR(36),
    action      VARCHAR(100) NOT NULL,
    target_type VARCHAR(50),
    target_id   CHAR(36),
    old_value   JSON,
    new_value   JSON,
    description TEXT,
    ip_address  VARCHAR(45),
    user_agent  TEXT,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  3.5 CURRENCIES & EXCHANGE RATES
-- ─────────────────────────────────────────────────────────────
CREATE TABLE currencies (
    code          VARCHAR(10)  PRIMARY KEY,
    name          VARCHAR(50)  NOT NULL,
    symbol        VARCHAR(10)  NOT NULL,
    is_active     TINYINT(1)   NOT NULL DEFAULT 1,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE exchange_rates (
    id            CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    base_currency VARCHAR(10)   NOT NULL,
    target_currency VARCHAR(10) NOT NULL,
    rate          DECIMAL(15,6) NOT NULL,
    effective_date DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by    CHAR(36),
    FOREIGN KEY (base_currency) REFERENCES currencies(code) ON DELETE CASCADE,
    FOREIGN KEY (target_currency) REFERENCES currencies(code) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  4. BRANCHES
-- ─────────────────────────────────────────────────────────────
CREATE TABLE branches (
    id            CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
    name          VARCHAR(150) NOT NULL,
    code          VARCHAR(20), -- App layer uniqueness
    address       TEXT,
    city          VARCHAR(100),
    country       VARCHAR(100) NOT NULL DEFAULT 'Cambodia',
    phone         VARCHAR(20),
    email         VARCHAR(150),
    logo_url      TEXT,
    is_active     TINYINT(1)   NOT NULL DEFAULT 1,
    created_by    CHAR(36),
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by    CHAR(36),
    updated_at    DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by    CHAR(36),
    deleted_at    DATETIME     NULL
);

-- Back-fill circular FKs
ALTER TABLE users
    ADD CONSTRAINT fk_users_branch     FOREIGN KEY (branch_id)  REFERENCES branches(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_users_created_by FOREIGN KEY (created_by) REFERENCES users(id)    ON DELETE SET NULL,
    ADD CONSTRAINT fk_users_updated_by FOREIGN KEY (updated_by) REFERENCES users(id)    ON DELETE SET NULL,
    ADD CONSTRAINT fk_users_deleted_by FOREIGN KEY (deleted_by) REFERENCES users(id)    ON DELETE SET NULL;

ALTER TABLE roles
    ADD CONSTRAINT fk_roles_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_roles_updated_by FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_roles_deleted_by FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL;

ALTER TABLE activity_logs
    ADD CONSTRAINT fk_activity_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL;

-- ─────────────────────────────────────────────────────────────
--  5. CATEGORIES
-- ─────────────────────────────────────────────────────────────
CREATE TABLE categories (
    id          CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
    branch_id   CHAR(36),
    parent_id   CHAR(36),
    name        VARCHAR(100) NOT NULL,
    name_kh     VARCHAR(100),
    description TEXT,
    image_url   TEXT,
    color_hex   VARCHAR(7),
    sort_order  INT          NOT NULL DEFAULT 0,
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    created_by  CHAR(36),
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by  CHAR(36),
    updated_at  DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by  CHAR(36),
    deleted_at  DATETIME     NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  6. BRANDS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE brands (
    id          CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
    name        VARCHAR(100) NOT NULL, -- App layer uniqueness
    description TEXT,
    logo_url    TEXT,
    website     VARCHAR(255),
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    created_by  CHAR(36),
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by  CHAR(36),
    updated_at  DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by  CHAR(36),
    deleted_at  DATETIME     NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  7. UNITS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE units (
    id           CHAR(36)    PRIMARY KEY DEFAULT (UUID()),
    name         VARCHAR(50) NOT NULL, -- App layer uniqueness
    abbreviation VARCHAR(10) NOT NULL,
    description  TEXT,
    created_by   CHAR(36),
    created_at   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by   CHAR(36),
    updated_at   DATETIME    NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by   CHAR(36),
    deleted_at   DATETIME    NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  8. PRODUCTS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE products (
    id            CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    branch_id     CHAR(36),
    category_id   CHAR(36),
    brand_id      CHAR(36),
    unit_id       CHAR(36),
    name          VARCHAR(150)  NOT NULL,
    name_kh       VARCHAR(150),
    description   TEXT,
    sku           VARCHAR(100), -- App layer uniqueness
    barcode       VARCHAR(100), -- App layer uniqueness
    cost_price    DECIMAL(12,2) NOT NULL DEFAULT 0,
    selling_price DECIMAL(12,2) NOT NULL,
    image_url     TEXT,
    is_sold_out   TINYINT(1)    NOT NULL DEFAULT 0,
    is_active     TINYINT(1)    NOT NULL DEFAULT 1,
    sort_order    INT           NOT NULL DEFAULT 0,
    created_by    CHAR(36),
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by    CHAR(36),
    updated_at    DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by    CHAR(36),
    deleted_at    DATETIME      NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  9. MODIFIER GROUPS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE modifier_groups (
    id          CHAR(36)    PRIMARY KEY DEFAULT (UUID()),
    branch_id   CHAR(36),
    name        VARCHAR(50) NOT NULL,
    name_kh     VARCHAR(50),
    description TEXT,
    min_select  INT         NOT NULL DEFAULT 0,
    max_select  INT         NOT NULL DEFAULT 1,
    is_required TINYINT(1)  NOT NULL DEFAULT 0,
    is_active   TINYINT(1)  NOT NULL DEFAULT 1,
    sort_order  INT         NOT NULL DEFAULT 0,
    created_by  CHAR(36),
    created_at  DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by  CHAR(36),
    updated_at  DATETIME    NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by  CHAR(36),
    deleted_at  DATETIME    NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  10. MODIFIER OPTIONS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE modifier_options (
    id                CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    modifier_group_id CHAR(36)      NOT NULL,
    value             VARCHAR(100)  NOT NULL,
    value_kh          VARCHAR(100),
    price_delta       DECIMAL(10,2) NOT NULL DEFAULT 0,
    sort_order        INT           NOT NULL DEFAULT 0,
    is_default        TINYINT(1)    NOT NULL DEFAULT 0,
    is_active         TINYINT(1)    NOT NULL DEFAULT 1,
    created_by        CHAR(36),
    created_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by        CHAR(36),
    updated_at        DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by        CHAR(36),
    deleted_at        DATETIME      NULL,
    FOREIGN KEY (modifier_group_id) REFERENCES modifier_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  11. PRODUCT MODIFIER MAPPING
-- ─────────────────────────────────────────────────────────────
CREATE TABLE product_modifier_mapping (
    id                CHAR(36)  PRIMARY KEY DEFAULT (UUID()),
    product_id        CHAR(36)  NOT NULL,
    modifier_group_id CHAR(36)  NOT NULL,
    created_by        CHAR(36),
    created_at        DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by        CHAR(36),
    updated_at        DATETIME  NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE(product_id, modifier_group_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (modifier_group_id) REFERENCES modifier_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  12. TABLE CATEGORIES
-- ─────────────────────────────────────────────────────────────
CREATE TABLE table_categories (
    id          CHAR(36)    PRIMARY KEY DEFAULT (UUID()),
    branch_id   CHAR(36),
    name        VARCHAR(50) NOT NULL,
    description TEXT,
    color_hex   VARCHAR(7),
    sort_order  INT         NOT NULL DEFAULT 0,
    created_by  CHAR(36),
    created_at  DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by  CHAR(36),
    updated_at  DATETIME    NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by  CHAR(36),
    deleted_at  DATETIME    NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  13. TABLES
-- ─────────────────────────────────────────────────────────────
CREATE TABLE tables (
    id                CHAR(36)    PRIMARY KEY DEFAULT (UUID()),
    branch_id         CHAR(36),
    table_category_id CHAR(36),
    number            VARCHAR(20) NOT NULL,
    name              VARCHAR(50),
    capacity          INT         NOT NULL DEFAULT 2,
    qr_code           TEXT,
    status            ENUM('available', 'occupied', 'reserved', 'cleaning') NOT NULL DEFAULT 'available',
    floor             VARCHAR(50),
    created_by        CHAR(36),
    created_at        DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by        CHAR(36),
    updated_at        DATETIME    NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by        CHAR(36),
    deleted_at        DATETIME    NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (table_category_id) REFERENCES table_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);
-- App layer must ensure branch_id + number is unique for active tables

-- ─────────────────────────────────────────────────────────────
--  13.5 TABLE MERGES
-- ─────────────────────────────────────────────────────────────
CREATE TABLE table_merges (
    id               CHAR(36)  PRIMARY KEY DEFAULT (UUID()),
    primary_table_id CHAR(36)  NOT NULL,
    merged_table_id  CHAR(36)  NOT NULL,
    created_by       CHAR(36),
    created_at       DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (primary_table_id) REFERENCES tables(id) ON DELETE CASCADE,
    FOREIGN KEY (merged_table_id) REFERENCES tables(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  14. CUSTOMERS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE customers (
    id              CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    first_name      VARCHAR(75)   NOT NULL,
    last_name       VARCHAR(75),
    phone           VARCHAR(20),  -- App layer uniqueness
    email           VARCHAR(150),
    gender          ENUM('male', 'female', 'other'),
    date_of_birth   DATE,
    address         TEXT,
    avatar_url      TEXT,
    loyalty_points  INT           NOT NULL DEFAULT 0,
    credit_balance  DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_spent     DECIMAL(14,2) NOT NULL DEFAULT 0,
    visit_count     INT           NOT NULL DEFAULT 0,
    last_visited_at DATETIME,
    source          VARCHAR(30)   NOT NULL DEFAULT 'walk_in',
    created_by      CHAR(36),
    created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by      CHAR(36),
    updated_at      DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by      CHAR(36),
    deleted_at      DATETIME      NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  15. CUSTOMER ADDRESSES
-- ─────────────────────────────────────────────────────────────
CREATE TABLE customer_addresses (
    id          CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    customer_id CHAR(36)      NOT NULL,
    label       VARCHAR(50),
    address     TEXT          NOT NULL,
    city        VARCHAR(100),
    district    VARCHAR(100),
    latitude    DECIMAL(10,7),
    longitude   DECIMAL(10,7),
    is_default  TINYINT(1)    NOT NULL DEFAULT 0,
    created_by  CHAR(36),
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by  CHAR(36),
    updated_at  DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by  CHAR(36),
    deleted_at  DATETIME      NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  16. CUSTOMER NOTES
-- ─────────────────────────────────────────────────────────────
CREATE TABLE customer_notes (
    id          CHAR(36)  PRIMARY KEY DEFAULT (UUID()),
    customer_id CHAR(36)  NOT NULL,
    note        TEXT      NOT NULL,
    is_pinned   TINYINT(1) NOT NULL DEFAULT 0,
    created_by  CHAR(36),
    created_at  DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_by  CHAR(36),
    deleted_at  DATETIME  NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  17. CUSTOMER TAGS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE customer_tags (
    id         CHAR(36)    PRIMARY KEY DEFAULT (UUID()),
    name       VARCHAR(50) NOT NULL UNIQUE,
    color_hex  VARCHAR(7),
    created_by CHAR(36),
    created_at DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by CHAR(36),
    updated_at DATETIME    NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by CHAR(36),
    deleted_at DATETIME    NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE customer_tag_mapping (
    id          CHAR(36)  PRIMARY KEY DEFAULT (UUID()),
    customer_id CHAR(36)  NOT NULL,
    tag_id      CHAR(36)  NOT NULL,
    created_by  CHAR(36),
    created_at  DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(customer_id, tag_id),
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES customer_tags(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  18. MEMBERSHIP TIERS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE membership_tiers (
    id               CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    name             VARCHAR(50)   NOT NULL UNIQUE,
    display_name     VARCHAR(100)  NOT NULL,
    description      TEXT,
    min_points       INT           NOT NULL DEFAULT 0,
    min_total_spent  DECIMAL(14,2) NOT NULL DEFAULT 0,
    discount_percent DECIMAL(5,2)  NOT NULL DEFAULT 0,
    point_multiplier DECIMAL(5,2)  NOT NULL DEFAULT 1.0,
    badge_color      VARCHAR(7),
    benefits         JSON          NOT NULL DEFAULT ('{}'),
    sort_order       INT           NOT NULL DEFAULT 0,
    is_active        TINYINT(1)    NOT NULL DEFAULT 1,
    created_by       CHAR(36),
    created_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by       CHAR(36),
    updated_at       DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by       CHAR(36),
    deleted_at       DATETIME      NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  19. CUSTOMER MEMBERSHIPS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE customer_memberships (
    id          CHAR(36)  PRIMARY KEY DEFAULT (UUID()),
    customer_id CHAR(36)  NOT NULL,
    tier_id     CHAR(36)  NOT NULL,
    joined_at   DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at  DATETIME,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_by  CHAR(36),
    created_at  DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by  CHAR(36),
    updated_at  DATETIME  NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (tier_id) REFERENCES membership_tiers(id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  20. LOYALTY TRANSACTIONS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE loyalty_transactions (
    id            CHAR(36)  PRIMARY KEY DEFAULT (UUID()),
    customer_id   CHAR(36)  NOT NULL,
    order_id      CHAR(36),
    points_delta  INT       NOT NULL,
    balance_after INT       NOT NULL,
    type          ENUM('earn', 'redeem', 'adjust', 'expire', 'refund') NOT NULL,
    note          TEXT,
    created_by    CHAR(36),
    created_at    DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  21. CUSTOMER CREDIT TRANSACTIONS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE customer_credit_transactions (
    id            CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    customer_id   CHAR(36)      NOT NULL,
    order_id      CHAR(36),
    amount_delta  DECIMAL(12,2) NOT NULL,
    balance_after DECIMAL(12,2) NOT NULL,
    type          ENUM('top_up', 'redemption', 'refund', 'adjust', 'expire') NOT NULL,
    reference_no  VARCHAR(100),
    note          TEXT,
    created_by    CHAR(36),
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  22. GIFT CARDS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE gift_cards (
    id             CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    branch_id      CHAR(36),
    code           VARCHAR(50)   NOT NULL UNIQUE,
    initial_amount DECIMAL(12,2) NOT NULL,
    balance        DECIMAL(12,2) NOT NULL,
    currency_code  VARCHAR(10)   NOT NULL DEFAULT 'USD',
    purchased_by   CHAR(36),
    assigned_to    CHAR(36),
    status         ENUM('active', 'depleted', 'expired', 'voided') NOT NULL DEFAULT 'active',
    expires_at     DATETIME,
    created_by     CHAR(36),
    created_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by     CHAR(36),
    updated_at     DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (purchased_by) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE gift_card_transactions (
    id            CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    gift_card_id  CHAR(36)      NOT NULL,
    order_id      CHAR(36),
    amount_delta  DECIMAL(12,2) NOT NULL,
    balance_after DECIMAL(12,2) NOT NULL,
    type          ENUM('purchase', 'redemption', 'refund', 'void') NOT NULL,
    note          TEXT,
    created_by    CHAR(36),
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gift_card_id) REFERENCES gift_cards(id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  23. SHIFTS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE shifts (
    id              CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    branch_id       CHAR(36),
    user_id         CHAR(36)      NOT NULL,
    opening_cash    DECIMAL(12,2) NOT NULL DEFAULT 0,
    opening_cash_khr DECIMAL(15,2) NOT NULL DEFAULT 0,
    closing_cash    DECIMAL(12,2),
    closing_cash_khr DECIMAL(15,2),
    expected_cash   DECIMAL(12,2),
    expected_cash_khr DECIMAL(15,2),
    cash_difference DECIMAL(12,2),
    cash_difference_khr DECIMAL(15,2),
    total_cash_in   DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_cash_out  DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_sales     DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_orders    INT           NOT NULL DEFAULT 0,
    notes           TEXT,
    status          ENUM('open', 'closed') NOT NULL DEFAULT 'open',
    opened_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    closed_at       DATETIME,
    created_by      CHAR(36),
    created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by      CHAR(36),
    updated_at      DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  24. SHIFT CASH MOVEMENTS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE shift_cash_movements (
    id         CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    shift_id   CHAR(36)      NOT NULL,
    type       ENUM('in', 'out') NOT NULL,
    amount     DECIMAL(12,2) NOT NULL,
    note       TEXT,
    created_by CHAR(36),
    created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shift_id) REFERENCES shifts(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  25. RESERVATIONS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE reservations (
    id              CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    branch_id       CHAR(36)      NOT NULL,
    customer_id     CHAR(36),
    table_id        CHAR(36),
    guest_name      VARCHAR(150)  NOT NULL,
    guest_phone     VARCHAR(20),
    guest_count     INT           NOT NULL DEFAULT 1,
    reserved_date   DATE          NOT NULL,
    reserved_time   TIME          NOT NULL,
    duration_mins   INT           NOT NULL DEFAULT 90,
    status          ENUM('pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show') NOT NULL DEFAULT 'pending',
    deposit_amount  DECIMAL(12,2) NOT NULL DEFAULT 0,
    special_request TEXT,
    notes           TEXT,
    confirmed_by    CHAR(36),
    confirmed_at    DATETIME,
    cancelled_by    CHAR(36),
    cancelled_at    DATETIME,
    cancel_reason   TEXT,
    created_by      CHAR(36),
    created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by      CHAR(36),
    updated_at      DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE SET NULL,
    FOREIGN KEY (confirmed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (cancelled_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  26. DELIVERY PLATFORMS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE delivery_platforms (
    id              CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    branch_id       CHAR(36),
    name            VARCHAR(100)  NOT NULL,
    logo_url        TEXT,
    commission_pct  DECIMAL(5,2)  NOT NULL DEFAULT 0,
    is_active       TINYINT(1)    NOT NULL DEFAULT 1,
    api_key         VARCHAR(255),
    webhook_secret  VARCHAR(255),
    config          JSON          NOT NULL DEFAULT ('{}'),
    created_by      CHAR(36),
    created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by      CHAR(36),
    updated_at      DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by      CHAR(36),
    deleted_at      DATETIME      NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  27. DELIVERY ORDERS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE delivery_orders (
    id                   CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    branch_id            CHAR(36),
    order_id             CHAR(36),
    platform_id          CHAR(36),
    platform_order_id    VARCHAR(100),
    driver_name          VARCHAR(100),
    driver_phone         VARCHAR(20),
    delivery_address     TEXT,
    delivery_lat         DECIMAL(10,7),
    delivery_lng         DECIMAL(10,7),
    delivery_fee         DECIMAL(10,2) NOT NULL DEFAULT 0,
    commission_amount    DECIMAL(10,2) NOT NULL DEFAULT 0,
    estimated_pickup_at  DATETIME,
    picked_up_at         DATETIME,
    delivered_at         DATETIME,
    status               ENUM('pending', 'confirmed', 'preparing', 'ready', 'picked_up', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
    cancel_reason        TEXT,
    notes                TEXT,
    created_by           CHAR(36),
    created_at           DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by           CHAR(36),
    updated_at           DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (platform_id) REFERENCES delivery_platforms(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  28. ORDERS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE orders (
    id                 CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    order_number       VARCHAR(30)   NOT NULL UNIQUE,
    branch_id          CHAR(36),
    shift_id           CHAR(36),
    user_id            CHAR(36)      NOT NULL,
    customer_id        CHAR(36),
    table_id           CHAR(36),
    reservation_id     CHAR(36),
    parent_order_id    CHAR(36),
    split_type         ENUM('none', 'even', 'item') NOT NULL DEFAULT 'none',
    guest_count        INT           NOT NULL DEFAULT 1,
    order_type         ENUM('dine_in', 'takeaway', 'delivery', 'drive_thru') NOT NULL DEFAULT 'dine_in',
    status             ENUM('pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled', 'voided') NOT NULL DEFAULT 'pending',
    subtotal           DECIMAL(12,2) NOT NULL DEFAULT 0,
    discount_amount    DECIMAL(12,2) NOT NULL DEFAULT 0,
    coupon_code        VARCHAR(50),
    coupon_discount    DECIMAL(12,2) NOT NULL DEFAULT 0,
    promotion_discount DECIMAL(12,2) NOT NULL DEFAULT 0,
    tax_amount         DECIMAL(12,2) NOT NULL DEFAULT 0,
    tip_amount         DECIMAL(12,2) NOT NULL DEFAULT 0,
    total              DECIMAL(12,2) NOT NULL DEFAULT 0,
    points_earned      INT           NOT NULL DEFAULT 0,
    points_redeemed    INT           NOT NULL DEFAULT 0,
    notes              TEXT,
    is_held            TINYINT(1)    NOT NULL DEFAULT 0,
    held_at            DATETIME,
    created_by         CHAR(36),
    created_at         DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by         CHAR(36),
    updated_at         DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by         CHAR(36),
    deleted_at         DATETIME      NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (shift_id) REFERENCES shifts(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE SET NULL,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_order_id) REFERENCES orders(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

ALTER TABLE delivery_orders
    ADD CONSTRAINT fk_delivery_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL;

ALTER TABLE loyalty_transactions
    ADD CONSTRAINT fk_loyalty_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL;

ALTER TABLE customer_credit_transactions
    ADD CONSTRAINT fk_credit_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL;

ALTER TABLE gift_card_transactions
    ADD CONSTRAINT fk_gc_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL;

-- ─────────────────────────────────────────────────────────────
--  29. ORDER ITEMS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE order_items (
    id                 CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    order_id           CHAR(36)      NOT NULL,
    product_id         CHAR(36),
    product_name       VARCHAR(150)  NOT NULL,
    product_sku        VARCHAR(100),
    quantity           INT           NOT NULL DEFAULT 1,
    unit_price         DECIMAL(10,2) NOT NULL,
    cost_price         DECIMAL(10,2) NOT NULL DEFAULT 0,
    discount_amount    DECIMAL(10,2) NOT NULL DEFAULT 0,
    line_total         DECIMAL(12,2) NOT NULL DEFAULT 0,
    selected_modifiers JSON          NOT NULL DEFAULT ('[]'),
    notes              TEXT,
    is_voided          TINYINT(1)    NOT NULL DEFAULT 0,
    voided_at          DATETIME,
    created_by         CHAR(36),
    created_at         DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by         CHAR(36),
    updated_at         DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by         CHAR(36),
    deleted_at         DATETIME      NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  30. SALES
-- ─────────────────────────────────────────────────────────────
CREATE TABLE sales (
    id                 CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    sale_number        VARCHAR(30)   NOT NULL UNIQUE,
    order_id           CHAR(36)      NOT NULL,
    branch_id          CHAR(36),
    shift_id           CHAR(36),
    user_id            CHAR(36)      NOT NULL,
    customer_id        CHAR(36),
    table_id           CHAR(36),
    order_type         VARCHAR(20)   NOT NULL,
    subtotal           DECIMAL(12,2) NOT NULL,
    discount_amount    DECIMAL(12,2) NOT NULL DEFAULT 0,
    coupon_discount    DECIMAL(12,2) NOT NULL DEFAULT 0,
    promotion_discount DECIMAL(12,2) NOT NULL DEFAULT 0,
    tax_amount         DECIMAL(12,2) NOT NULL DEFAULT 0,
    tip_amount         DECIMAL(12,2) NOT NULL DEFAULT 0,
    total              DECIMAL(12,2) NOT NULL,
    total_cost         DECIMAL(12,2) NOT NULL DEFAULT 0,
    gross_profit       DECIMAL(12,2) NOT NULL DEFAULT 0,
    points_earned      INT           NOT NULL DEFAULT 0,
    points_redeemed    INT           NOT NULL DEFAULT 0,
    notes              TEXT,
    sale_date          DATE          NOT NULL,
    business_date      DATE,
    created_by         CHAR(36),
    created_at         DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_by         CHAR(36),
    deleted_at         DATETIME      NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (shift_id) REFERENCES shifts(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  31. SALE ITEMS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE sale_items (
    id                 CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    sale_id            CHAR(36)      NOT NULL,
    product_id         CHAR(36),
    product_name       VARCHAR(150)  NOT NULL,
    product_sku        VARCHAR(100),
    category_name      VARCHAR(100),
    quantity           INT           NOT NULL,
    unit_price         DECIMAL(10,2) NOT NULL,
    cost_price         DECIMAL(10,2) NOT NULL DEFAULT 0,
    discount_amount    DECIMAL(10,2) NOT NULL DEFAULT 0,
    line_total         DECIMAL(12,2) NOT NULL,
    selected_modifiers JSON          NOT NULL DEFAULT ('[]'),
    notes              TEXT,
    created_by         CHAR(36),
    created_at         DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  32. SALE PAYMENTS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE sale_payments (
    id                CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    sale_id           CHAR(36)      NOT NULL,
    payment_method_id CHAR(36)      NOT NULL,
    gift_card_id      CHAR(36),
    amount            DECIMAL(12,2) NOT NULL,
    amount_tendered   DECIMAL(12,2),
    change_amount     DECIMAL(12,2) NOT NULL DEFAULT 0,
    currency_code     VARCHAR(10)   NOT NULL DEFAULT 'USD',
    exchange_rate     DECIMAL(10,4) NOT NULL DEFAULT 1,
    reference_no      VARCHAR(100),
    created_by        CHAR(36),
    created_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (gift_card_id) REFERENCES gift_cards(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  33. PAYMENT METHODS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE payment_methods (
    id          CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
    branch_id   CHAR(36),
    name        VARCHAR(100) NOT NULL,
    type        ENUM('cash', 'card', 'qr', 'ewallet', 'credit', 'gift_card', 'bank_transfer') NOT NULL DEFAULT 'cash',
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    created_by  CHAR(36),
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by  CHAR(36),
    updated_at  DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by  CHAR(36),
    deleted_at  DATETIME     NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

ALTER TABLE sale_payments
    ADD CONSTRAINT fk_sale_payments_method FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id);

-- ─────────────────────────────────────────────────────────────
--  34. TIPS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE tips (
    id         CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    sale_id    CHAR(36)      NOT NULL,
    user_id    CHAR(36),
    amount     DECIMAL(10,2) NOT NULL,
    note       TEXT,
    created_by CHAR(36),
    created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by CHAR(36),
    updated_at DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  35. DISCOUNTS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE discounts (
    id                CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    branch_id         CHAR(36),
    name              VARCHAR(100)  NOT NULL,
    description       TEXT,
    type              ENUM('percent', 'fixed') NOT NULL,
    value             DECIMAL(10,2) NOT NULL,
    apply_to          ENUM('order', 'item') NOT NULL DEFAULT 'order',
    min_order         DECIMAL(12,2) NOT NULL DEFAULT 0,
    requires_approval TINYINT(1)    NOT NULL DEFAULT 0,
    is_active         TINYINT(1)    NOT NULL DEFAULT 1,
    created_by        CHAR(36),
    created_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by        CHAR(36),
    updated_at        DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by        CHAR(36),
    deleted_at        DATETIME      NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  36. COUPONS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE coupons (
    id                 CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    branch_id          CHAR(36),
    code               VARCHAR(50)   NOT NULL, -- App layer uniqueness
    name               VARCHAR(100)  NOT NULL,
    description        TEXT,
    type               ENUM('percent', 'fixed') NOT NULL,
    value              DECIMAL(10,2) NOT NULL,
    min_order          DECIMAL(12,2) NOT NULL DEFAULT 0,
    max_discount       DECIMAL(12,2),
    usage_limit        INT,
    used_count         INT           NOT NULL DEFAULT 0,
    per_customer_limit INT           NOT NULL DEFAULT 1,
    starts_at          DATETIME,
    expires_at         DATETIME,
    is_active          TINYINT(1)    NOT NULL DEFAULT 1,
    created_by         CHAR(36),
    created_at         DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by         CHAR(36),
    updated_at         DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by         CHAR(36),
    deleted_at         DATETIME      NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  37. COUPON USAGES
-- ─────────────────────────────────────────────────────────────
CREATE TABLE coupon_usages (
    id              CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    coupon_id       CHAR(36)      NOT NULL,
    order_id        CHAR(36),
    customer_id     CHAR(36),
    discount_amount DECIMAL(10,2) NOT NULL,
    created_by      CHAR(36),
    created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  38. PROMOTIONS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE promotions (
    id                 CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    branch_id          CHAR(36),
    name               VARCHAR(100)  NOT NULL,
    description        TEXT,
    type               ENUM('buy_x_get_y', 'happy_hour', 'discount_item', 'combo', 'free_item') NOT NULL,
    priority           INT           NOT NULL DEFAULT 0,
    is_stackable       TINYINT(1)    NOT NULL DEFAULT 0,
    usage_limit        INT,
    used_count         INT           NOT NULL DEFAULT 0,
    per_customer_limit INT,
    starts_at          DATETIME,
    expires_at         DATETIME,
    days_of_week       JSON          NOT NULL DEFAULT ('[0,1,2,3,4,5,6]'),
    time_from          TIME,
    time_to            TIME,
    is_active          TINYINT(1)    NOT NULL DEFAULT 1,
    created_by         CHAR(36),
    created_at         DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by         CHAR(36),
    updated_at         DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by         CHAR(36),
    deleted_at         DATETIME      NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  39. PROMOTION RULES
-- ─────────────────────────────────────────────────────────────
CREATE TABLE promotion_rules (
    id              CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    promotion_id    CHAR(36)      NOT NULL,
    rule_type       VARCHAR(30)   NOT NULL,
    target_type     ENUM('product', 'category', 'order'),
    target_id       CHAR(36),
    quantity        INT,
    amount          DECIMAL(12,2),
    discount_pct    DECIMAL(5,2),
    discount_fixed  DECIMAL(12,2),
    free_product_id CHAR(36),
    free_quantity   INT,
    created_by      CHAR(36),
    created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by      CHAR(36),
    updated_at      DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (promotion_id) REFERENCES promotions(id) ON DELETE CASCADE,
    FOREIGN KEY (free_product_id) REFERENCES products(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  40. PROMOTION USAGES
-- ─────────────────────────────────────────────────────────────
CREATE TABLE promotion_usages (
    id              CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    promotion_id    CHAR(36)      NOT NULL,
    order_id        CHAR(36),
    customer_id     CHAR(36),
    discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_by      CHAR(36),
    created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (promotion_id) REFERENCES promotions(id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  41. REFUNDS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE refunds (
    id             CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    sale_id        CHAR(36)      NOT NULL,
    order_id       CHAR(36),
    refund_number  VARCHAR(30)   NOT NULL UNIQUE,
    amount         DECIMAL(12,2) NOT NULL,
    payment_method VARCHAR(20),
    reason         TEXT          NOT NULL,
    approved_by    CHAR(36),
    created_by     CHAR(36),
    created_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  42. VOID REQUESTS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE void_requests (
    id          CHAR(36)    PRIMARY KEY DEFAULT (UUID()),
    target_type VARCHAR(20) NOT NULL,
    target_id   CHAR(36)    NOT NULL,
    reason      TEXT        NOT NULL,
    status      ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    reviewed_by CHAR(36),
    review_note TEXT,
    reviewed_at DATETIME,
    created_by  CHAR(36),
    created_at  DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by  CHAR(36),
    updated_at  DATETIME    NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  43. DELETE REQUESTS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE delete_requests (
    id          CHAR(36)    PRIMARY KEY DEFAULT (UUID()),
    target_type VARCHAR(30) NOT NULL,
    target_id   CHAR(36)    NOT NULL,
    reason      TEXT        NOT NULL,
    status      ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    reviewed_by CHAR(36),
    review_note TEXT,
    reviewed_at DATETIME,
    created_by  CHAR(36),
    created_at  DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by  CHAR(36),
    updated_at  DATETIME    NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  44. APPROVAL REQUESTS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE approval_requests (
    id           CHAR(36)    PRIMARY KEY DEFAULT (UUID()),
    branch_id    CHAR(36),
    type         VARCHAR(30) NOT NULL,
    target_type  VARCHAR(30) NOT NULL,
    target_id    CHAR(36)    NOT NULL,
    requested_by CHAR(36)    NOT NULL,
    reason       TEXT        NOT NULL,
    meta         JSON        NOT NULL DEFAULT ('{}'),
    status       ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    reviewed_by  CHAR(36),
    review_note  TEXT,
    reviewed_at  DATETIME,
    created_by   CHAR(36),
    created_at   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by   CHAR(36),
    updated_at   DATETIME    NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (requested_by) REFERENCES users(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  45. KDS STATIONS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE kds_stations (
    id          CHAR(36)    PRIMARY KEY DEFAULT (UUID()),
    branch_id   CHAR(36),
    name        VARCHAR(50) NOT NULL,
    description TEXT,
    color_hex   VARCHAR(7),
    sort_order  INT         NOT NULL DEFAULT 0,
    is_active   TINYINT(1)  NOT NULL DEFAULT 1,
    created_by  CHAR(36),
    created_at  DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by  CHAR(36),
    updated_at  DATETIME    NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by  CHAR(36),
    deleted_at  DATETIME    NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  46. PRODUCT STATION MAPPING
-- ─────────────────────────────────────────────────────────────
CREATE TABLE product_station_mapping (
    id         CHAR(36)  PRIMARY KEY DEFAULT (UUID()),
    product_id CHAR(36)  NOT NULL,
    station_id CHAR(36)  NOT NULL,
    created_by CHAR(36),
    created_at DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(product_id, station_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (station_id) REFERENCES kds_stations(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  47. KDS ORDERS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE kds_orders (
    id           CHAR(36)    PRIMARY KEY DEFAULT (UUID()),
    station_id   CHAR(36)    NOT NULL,
    order_id     CHAR(36)    NOT NULL,
    status       ENUM('new', 'in_progress', 'ready', 'served', 'cancelled') NOT NULL DEFAULT 'new',
    priority     INT         NOT NULL DEFAULT 0,
    started_at   DATETIME,
    completed_at DATETIME,
    served_at    DATETIME,
    created_by   CHAR(36),
    created_at   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by   CHAR(36),
    updated_at   DATETIME    NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (station_id) REFERENCES kds_stations(id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  48. INGREDIENTS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE ingredients (
    id                  CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    branch_id           CHAR(36),
    name                VARCHAR(150)  NOT NULL,
    description         TEXT,
    sku                 VARCHAR(100),
    barcode             VARCHAR(100),
    unit                VARCHAR(30)   NOT NULL,
    cost_per_unit       DECIMAL(10,2) NOT NULL DEFAULT 0,
    supplier_id         CHAR(36),
    storage_location    VARCHAR(100),
    expiry_date         DATE,
    is_active           TINYINT(1)    NOT NULL DEFAULT 1,
    created_by          CHAR(36),
    created_at          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by          CHAR(36),
    updated_at          DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by          CHAR(36),
    deleted_at          DATETIME      NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  49. INGREDIENT STOCKS (Added Thresholds)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE ingredient_stocks (
    id                  CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    ingredient_id       CHAR(36)      NOT NULL,
    branch_id           CHAR(36)      NOT NULL,
    quantity            DECIMAL(12,3) NOT NULL DEFAULT 0,
    low_stock_threshold DECIMAL(12,3) NOT NULL DEFAULT 0, -- Moved from ingredients
    reorder_qty         DECIMAL(12,3) NOT NULL DEFAULT 0, -- Moved from ingredients
    created_by          CHAR(36),
    created_at          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by          CHAR(36),
    updated_at          DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE(ingredient_id, branch_id),
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  50. RECIPES
-- ─────────────────────────────────────────────────────────────
CREATE TABLE recipes (
    id              CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    product_id      CHAR(36)      NOT NULL,
    ingredient_id   CHAR(36)      NOT NULL,
    quantity_used   DECIMAL(12,3) NOT NULL,
    unit            VARCHAR(30)   NOT NULL,
    note            TEXT,
    created_by      CHAR(36),
    created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by      CHAR(36),
    updated_at      DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE(product_id, ingredient_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  50.5 MODIFIER OPTION RECIPES (NEW)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE modifier_option_recipes (
    id                 CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    modifier_option_id CHAR(36)      NOT NULL,
    ingredient_id      CHAR(36)      NOT NULL,
    quantity_used      DECIMAL(12,3) NOT NULL,
    unit               VARCHAR(30)   NOT NULL,
    created_at         DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(modifier_option_id, ingredient_id),
    FOREIGN KEY (modifier_option_id) REFERENCES modifier_options(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE
);

-- ─────────────────────────────────────────────────────────────
--  51. STOCK ADJUSTMENTS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE stock_adjustments (
    id            CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    branch_id     CHAR(36),
    ingredient_id CHAR(36)      NOT NULL,
    qty_before    DECIMAL(12,3) NOT NULL,
    delta_qty     DECIMAL(12,3) NOT NULL,
    qty_after     DECIMAL(12,3) NOT NULL,
    reason        VARCHAR(150),
    approved_by   CHAR(36),
    created_by    CHAR(36),
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id),
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  52. INVENTORY MOVEMENTS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE inventory_movements (
    id             CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    branch_id      CHAR(36),
    ingredient_id  CHAR(36)      NOT NULL,
    type           ENUM('purchase', 'sale', 'adjustment', 'waste', 'transfer_in', 'transfer_out', 'return', 'production') NOT NULL,
    reference_type VARCHAR(30),
    reference_id   CHAR(36),
    qty_before     DECIMAL(12,3) NOT NULL,
    qty_change     DECIMAL(12,3) NOT NULL,
    qty_after      DECIMAL(12,3) NOT NULL,
    unit_cost      DECIMAL(10,2),
    note           TEXT,
    created_by     CHAR(36),
    created_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  53. WASTE RECORDS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE waste_records (
    id             CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    branch_id      CHAR(36),
    ingredient_id  CHAR(36)      NOT NULL,
    quantity       DECIMAL(12,3) NOT NULL,
    unit           VARCHAR(30)   NOT NULL,
    reason         ENUM('expired', 'spilled', 'overcooked', 'damaged', 'other') NOT NULL,
    estimated_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
    note           TEXT,
    created_by     CHAR(36),
    created_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by     CHAR(36),
    updated_at     DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  54. SUPPLIERS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE suppliers (
    id            CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
    name          VARCHAR(150) NOT NULL,
    contact_name  VARCHAR(150),
    phone         VARCHAR(20),
    email         VARCHAR(150),
    address       TEXT,
    city          VARCHAR(100),
    country       VARCHAR(100),
    tax_id        VARCHAR(50),
    payment_terms VARCHAR(100),
    note          TEXT,
    is_active     TINYINT(1)   NOT NULL DEFAULT 1,
    created_by    CHAR(36),
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by    CHAR(36),
    updated_at    DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by    CHAR(36),
    deleted_at    DATETIME     NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

ALTER TABLE ingredients
    ADD CONSTRAINT fk_ingredients_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL;

-- ─────────────────────────────────────────────────────────────
--  55. PURCHASE ORDERS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE purchase_orders (
    id            CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    po_number     VARCHAR(30)   NOT NULL UNIQUE,
    branch_id     CHAR(36),
    supplier_id   CHAR(36)      NOT NULL,
    status        ENUM('draft', 'sent', 'partial', 'received', 'cancelled') NOT NULL DEFAULT 'draft',
    subtotal      DECIMAL(12,2) NOT NULL DEFAULT 0,
    tax_amount    DECIMAL(12,2) NOT NULL DEFAULT 0,
    total         DECIMAL(12,2) NOT NULL DEFAULT 0,
    note          TEXT,
    expected_date DATE,
    ordered_at    DATETIME,
    received_at   DATETIME,
    created_by    CHAR(36),
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by    CHAR(36),
    updated_at    DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  56. PURCHASE ORDER ITEMS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE purchase_order_items (
    id                CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    purchase_order_id CHAR(36)      NOT NULL,
    ingredient_id     CHAR(36)      NOT NULL,
    ingredient_name   VARCHAR(150)  NOT NULL,
    unit              VARCHAR(30)   NOT NULL,
    quantity_ordered  DECIMAL(12,3) NOT NULL,
    quantity_received DECIMAL(12,3) NOT NULL DEFAULT 0,
    unit_cost         DECIMAL(10,2) NOT NULL,
    line_total        DECIMAL(12,2) NOT NULL DEFAULT 0,
    note              TEXT,
    created_by        CHAR(36),
    created_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by        CHAR(36),
    updated_at        DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  57. GOODS RECEIPTS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE goods_receipts (
    id                CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    gr_number         VARCHAR(30)   NOT NULL UNIQUE,
    purchase_order_id CHAR(36),
    branch_id         CHAR(36),
    supplier_id       CHAR(36)      NOT NULL,
    received_date     DATE          NOT NULL,
    status            ENUM('draft', 'confirmed', 'cancelled') NOT NULL DEFAULT 'draft',
    note              TEXT,
    confirmed_by      CHAR(36),
    confirmed_at      DATETIME,
    created_by        CHAR(36),
    created_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by        CHAR(36),
    updated_at        DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE SET NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    FOREIGN KEY (confirmed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE goods_receipt_items (
    id               CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    goods_receipt_id CHAR(36)      NOT NULL,
    ingredient_id    CHAR(36)      NOT NULL,
    ingredient_name  VARCHAR(150)  NOT NULL,
    unit             VARCHAR(30)   NOT NULL,
    quantity         DECIMAL(12,3) NOT NULL,
    unit_cost        DECIMAL(10,2) NOT NULL DEFAULT 0,
    line_total       DECIMAL(12,2) NOT NULL DEFAULT 0,
    expiry_date      DATE,
    note             TEXT,
    created_by       CHAR(36),
    created_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by       CHAR(36),
    updated_at       DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (goods_receipt_id) REFERENCES goods_receipts(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  58. PURCHASE RETURNS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE purchase_returns (
    id                CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    purchase_order_id CHAR(36),
    supplier_id       CHAR(36)      NOT NULL,
    ingredient_id     CHAR(36)      NOT NULL,
    ingredient_name   VARCHAR(150)  NOT NULL,
    quantity          DECIMAL(12,3) NOT NULL,
    unit_cost         DECIMAL(10,2) NOT NULL DEFAULT 0,
    reason            TEXT,
    created_by        CHAR(36),
    created_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE SET NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  58.5 SUPPLIER PAYMENTS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE supplier_payments (
    id                CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    purchase_order_id CHAR(36)      NOT NULL,
    supplier_id       CHAR(36)      NOT NULL,
    amount            DECIMAL(12,2) NOT NULL,
    payment_method    VARCHAR(50)   NOT NULL,
    reference_no      VARCHAR(100),
    payment_date      DATE          NOT NULL,
    note              TEXT,
    created_by        CHAR(36),
    created_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  59. STOCK TRANSFERS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE stock_transfers (
    id             CHAR(36)    PRIMARY KEY DEFAULT (UUID()),
    transfer_no    VARCHAR(50) NOT NULL UNIQUE,
    from_branch_id CHAR(36)    NOT NULL,
    to_branch_id   CHAR(36)    NOT NULL,
    transfer_date  DATE        NOT NULL,
    status         ENUM('pending', 'in_transit', 'received', 'cancelled') NOT NULL DEFAULT 'pending',
    notes          TEXT,
    created_by     CHAR(36),
    created_at     DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by     CHAR(36),
    updated_at     DATETIME    NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (from_branch_id) REFERENCES branches(id),
    FOREIGN KEY (to_branch_id) REFERENCES branches(id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE stock_transfer_items (
    id                CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    stock_transfer_id CHAR(36)      NOT NULL,
    ingredient_id     CHAR(36)      NOT NULL,
    quantity          DECIMAL(12,3) NOT NULL,
    created_by        CHAR(36),
    created_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by        CHAR(36),
    updated_at        DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (stock_transfer_id) REFERENCES stock_transfers(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  60. EXPENSE CATEGORIES
-- ─────────────────────────────────────────────────────────────
CREATE TABLE expense_categories (
    id          CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
    name        VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    color_hex   VARCHAR(7),
    created_by  CHAR(36),
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by  CHAR(36),
    updated_at  DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by  CHAR(36),
    deleted_at  DATETIME     NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  61. EXPENSES
-- ─────────────────────────────────────────────────────────────
CREATE TABLE expenses (
    id             CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    branch_id      CHAR(36),
    category_id    CHAR(36)      NOT NULL,
    shift_id       CHAR(36),
    amount         DECIMAL(12,2) NOT NULL,
    currency_code  VARCHAR(10)   NOT NULL DEFAULT 'USD',
    description    TEXT,
    receipt_url    TEXT,
    expense_date   DATE          NOT NULL,
    payment_method VARCHAR(20),
    created_by     CHAR(36),
    created_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by     CHAR(36),
    updated_at     DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by     CHAR(36),
    deleted_at     DATETIME      NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES expense_categories(id),
    FOREIGN KEY (shift_id) REFERENCES shifts(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  62. TAXES
-- ─────────────────────────────────────────────────────────────
CREATE TABLE taxes (
    id         CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
    branch_id  CHAR(36),
    name       VARCHAR(50)  NOT NULL,
    rate       DECIMAL(5,2) NOT NULL,
    type       ENUM('exclusive', 'inclusive') NOT NULL DEFAULT 'exclusive',
    applies_to ENUM('all', 'dine_in', 'takeaway', 'delivery') NOT NULL DEFAULT 'all',
    is_active  TINYINT(1)   NOT NULL DEFAULT 1,
    created_by CHAR(36),
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by CHAR(36),
    updated_at DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  63. SALE TAXES
-- ─────────────────────────────────────────────────────────────
CREATE TABLE sale_taxes (
    id         CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    sale_id    CHAR(36)      NOT NULL,
    tax_name   VARCHAR(50)   NOT NULL,
    rate       DECIMAL(5,2)  NOT NULL,
    type       VARCHAR(20)   NOT NULL,
    amount     DECIMAL(12,2) NOT NULL,
    created_by CHAR(36),
    created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  63.5 ORDER TAXES
-- ─────────────────────────────────────────────────────────────
CREATE TABLE order_taxes (
    id         CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    order_id   CHAR(36)      NOT NULL,
    tax_name   VARCHAR(50)   NOT NULL,
    rate       DECIMAL(5,2)  NOT NULL,
    type       VARCHAR(20)   NOT NULL,
    amount     DECIMAL(12,2) NOT NULL,
    created_by CHAR(36),
    created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  64. COMPANY SETTINGS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE company_settings (
    id          CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
    setting_key VARCHAR(100) NOT NULL,
    value       TEXT         NOT NULL,
    type        VARCHAR(20)  NOT NULL DEFAULT 'string',
    description TEXT,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by  CHAR(36),
    updated_at  DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE(setting_key),
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  65. BRANCH SETTINGS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE branch_settings (
    id               CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
    branch_id        CHAR(36)     NOT NULL UNIQUE,
    receipt_footer   TEXT,
    tax_rate         DECIMAL(5,2),
    default_language VARCHAR(10)  NOT NULL DEFAULT 'en',
    currency_code    VARCHAR(10)  NOT NULL DEFAULT 'USD',
    timezone         VARCHAR(50)  NOT NULL DEFAULT 'Asia/Phnom_Penh',
    business_hours   JSON         NOT NULL DEFAULT ('{}'),
    created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by       CHAR(36),
    updated_at       DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  66. BUSINESS DATES
-- ─────────────────────────────────────────────────────────────
CREATE TABLE business_dates (
    id            CHAR(36)    PRIMARY KEY DEFAULT (UUID()),
    branch_id     CHAR(36)    NOT NULL,
    business_date DATE        NOT NULL,
    opened_at     DATETIME,
    closed_at     DATETIME,
    status        ENUM('open', 'closed') NOT NULL DEFAULT 'open',
    created_by    CHAR(36),
    created_at    DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by    CHAR(36),
    updated_at    DATETIME    NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE(branch_id, business_date),
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  67. PRINTERS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE printers (
    id          CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
    branch_id   CHAR(36),
    name        VARCHAR(100) NOT NULL,
    type        ENUM('receipt', 'kitchen', 'label') NOT NULL,
    connection  ENUM('usb', 'network', 'bluetooth') NOT NULL,
    address     VARCHAR(100),
    port        INT,
    paper_width INT,
    is_default  TINYINT(1)   NOT NULL DEFAULT 0,
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    created_by  CHAR(36),
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by  CHAR(36),
    updated_at  DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by  CHAR(36),
    deleted_at  DATETIME     NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  68. PRODUCT PRINTER MAPPING
-- ─────────────────────────────────────────────────────────────
CREATE TABLE product_printer_mapping (
    id         CHAR(36)  PRIMARY KEY DEFAULT (UUID()),
    product_id CHAR(36)  NOT NULL,
    printer_id CHAR(36)  NOT NULL,
    created_by CHAR(36),
    created_at DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(product_id, printer_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (printer_id) REFERENCES printers(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  69. PRINT JOBS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE print_jobs (
    id         CHAR(36)    PRIMARY KEY DEFAULT (UUID()),
    branch_id  CHAR(36)    NOT NULL,
    sale_id    CHAR(36),
    order_id   CHAR(36),
    printer_id CHAR(36),
    print_type VARCHAR(50) NOT NULL,
    printed_by CHAR(36),
    printed_at DATETIME,
    created_by CHAR(36),
    created_at DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by CHAR(36),
    updated_at DATETIME    NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE SET NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    FOREIGN KEY (printer_id) REFERENCES printers(id) ON DELETE SET NULL,
    FOREIGN KEY (printed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  70. NOTIFICATIONS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE notifications (
    id         CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
    branch_id  CHAR(36),
    user_id    CHAR(36),
    title      VARCHAR(255) NOT NULL,
    message    TEXT         NOT NULL,
    type       VARCHAR(50)  NOT NULL,
    is_read    TINYINT(1)   NOT NULL DEFAULT 0,
    created_by CHAR(36),
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by CHAR(36),
    updated_at DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  71. MESSAGE TEMPLATES
-- ─────────────────────────────────────────────────────────────
CREATE TABLE message_templates (
    id          CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
    branch_id   CHAR(36),
    name        VARCHAR(100) NOT NULL,
    channel     ENUM('sms', 'email', 'push') NOT NULL,
    type        VARCHAR(50)  NOT NULL,
    subject     VARCHAR(255),
    body        TEXT         NOT NULL,
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    created_by  CHAR(36),
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by  CHAR(36),
    updated_at  DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by  CHAR(36),
    deleted_at  DATETIME     NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  72. MESSAGE LOGS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE message_logs (
    id          CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
    branch_id   CHAR(36),
    template_id CHAR(36),
    customer_id CHAR(36),
    channel     ENUM('sms', 'email', 'push') NOT NULL,
    recipient   VARCHAR(150) NOT NULL,
    subject     VARCHAR(255),
    body        TEXT         NOT NULL,
    status      ENUM('pending', 'sent', 'delivered', 'failed', 'bounced') NOT NULL DEFAULT 'pending',
    error_msg   TEXT,
    sent_at     DATETIME,
    created_by  CHAR(36),
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (template_id) REFERENCES message_templates(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  73. API KEYS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE api_keys (
    id           CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
    branch_id    CHAR(36),
    name         VARCHAR(100) NOT NULL,
    key_hash     VARCHAR(255) NOT NULL UNIQUE,
    prefix       VARCHAR(10)  NOT NULL,
    scopes       JSON         NOT NULL DEFAULT ('[]'),
    last_used_at DATETIME,
    expires_at   DATETIME,
    is_active    TINYINT(1)   NOT NULL DEFAULT 1,
    created_by   CHAR(36),
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by   CHAR(36),
    updated_at   DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by   CHAR(36),
    deleted_at   DATETIME     NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  74. WEBHOOKS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE webhooks (
    id          CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
    branch_id   CHAR(36),
    name        VARCHAR(100) NOT NULL,
    url         TEXT         NOT NULL,
    secret      VARCHAR(255),
    events      JSON         NOT NULL DEFAULT ('[]'),
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    created_by  CHAR(36),
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by  CHAR(36),
    updated_at  DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_by  CHAR(36),
    deleted_at  DATETIME     NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE webhook_logs (
    id              CHAR(36)    PRIMARY KEY DEFAULT (UUID()),
    webhook_id      CHAR(36)    NOT NULL,
    event           VARCHAR(50) NOT NULL,
    payload         JSON        NOT NULL DEFAULT ('{}'),
    response_code   INT,
    response_body   TEXT,
    status          ENUM('pending', 'success', 'failed', 'retrying') NOT NULL DEFAULT 'pending',
    attempt_count   INT         NOT NULL DEFAULT 0,
    last_attempt_at DATETIME,
    created_at      DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (webhook_id) REFERENCES webhooks(id) ON DELETE CASCADE
);

-- ─────────────────────────────────────────────────────────────
--  75. DAILY SUMMARY
-- ─────────────────────────────────────────────────────────────
CREATE TABLE daily_summary (
    id                  CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    branch_id           CHAR(36)      NOT NULL,
    summary_date        DATE          NOT NULL,
    total_orders        INT           NOT NULL DEFAULT 0,
    total_sales         DECIMAL(14,2) NOT NULL DEFAULT 0,
    total_cost          DECIMAL(14,2) NOT NULL DEFAULT 0,
    gross_profit        DECIMAL(14,2) NOT NULL DEFAULT 0,
    total_discount      DECIMAL(14,2) NOT NULL DEFAULT 0,
    total_tax           DECIMAL(14,2) NOT NULL DEFAULT 0,
    total_tips          DECIMAL(14,2) NOT NULL DEFAULT 0,
    total_refunds       DECIMAL(14,2) NOT NULL DEFAULT 0,
    avg_order_value     DECIMAL(10,2) NOT NULL DEFAULT 0,
    new_customers       INT           NOT NULL DEFAULT 0,
    returning_customers INT           NOT NULL DEFAULT 0,
    dine_in_count       INT           NOT NULL DEFAULT 0,
    takeaway_count      INT           NOT NULL DEFAULT 0,
    delivery_count      INT           NOT NULL DEFAULT 0,
    computed_at         DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE(branch_id, summary_date),
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
);

-- ─────────────────────────────────────────────────────────────
--  76. DAILY PRODUCT SUMMARY
-- ─────────────────────────────────────────────────────────────
CREATE TABLE daily_product_summary (
    id             CHAR(36)      PRIMARY KEY DEFAULT (UUID()),
    branch_id      CHAR(36)      NOT NULL,
    summary_date   DATE          NOT NULL,
    product_id     CHAR(36),
    product_name   VARCHAR(150)  NOT NULL,
    category_name  VARCHAR(100),
    qty_sold       INT           NOT NULL DEFAULT 0,
    total_revenue  DECIMAL(14,2) NOT NULL DEFAULT 0,
    total_cost     DECIMAL(14,2) NOT NULL DEFAULT 0,
    gross_profit   DECIMAL(14,2) NOT NULL DEFAULT 0,
    computed_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(branch_id, summary_date, product_id),
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────────────────────────
--  77. AUDIT LOGS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE audit_logs (
    id          CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
    table_name  VARCHAR(100) NOT NULL,
    record_id   CHAR(36)     NOT NULL,
    action      ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    old_value   JSON,
    new_value   JSON,
    changed_by  CHAR(36),
    changed_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
);


-- ============================================================
--  INDEXES (Adjusted for MySQL)
-- ============================================================

-- Users
CREATE INDEX idx_users_branch      ON users(branch_id);
CREATE INDEX idx_users_role        ON users(role_id);
CREATE INDEX idx_users_active      ON users(is_active); 

-- Categories tree
CREATE INDEX idx_categories_parent ON categories(parent_id);
CREATE INDEX idx_categories_branch ON categories(branch_id);

-- Products
CREATE INDEX idx_products_branch   ON products(branch_id);
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_barcode  ON products(barcode);
CREATE INDEX idx_products_sku      ON products(sku);
CREATE INDEX idx_products_active   ON products(is_active, is_sold_out);

-- Modifiers
CREATE INDEX idx_modifier_groups_branch       ON modifier_groups(branch_id);
CREATE INDEX idx_modifier_options_group       ON modifier_options(modifier_group_id);
CREATE INDEX idx_product_modifier_product     ON product_modifier_mapping(product_id);
CREATE INDEX idx_product_modifier_group       ON product_modifier_mapping(modifier_group_id);

-- Product mappings
CREATE INDEX idx_product_station_product      ON product_station_mapping(product_id);
CREATE INDEX idx_product_station_station      ON product_station_mapping(station_id);
CREATE INDEX idx_product_printer_product      ON product_printer_mapping(product_id);
CREATE INDEX idx_product_printer_printer      ON product_printer_mapping(printer_id);

-- Tables
CREATE INDEX idx_tables_branch     ON tables(branch_id);
CREATE INDEX idx_tables_status     ON tables(status);

-- Customers
CREATE INDEX idx_customers_phone   ON customers(phone);
CREATE INDEX idx_customers_email   ON customers(email);

-- Customer tags
CREATE INDEX idx_customer_tag_map_customer ON customer_tag_mapping(customer_id);
CREATE INDEX idx_customer_tag_map_tag      ON customer_tag_mapping(tag_id);

-- Credit transactions
CREATE INDEX idx_credit_txn_customer ON customer_credit_transactions(customer_id);
CREATE INDEX idx_credit_txn_order    ON customer_credit_transactions(order_id);

-- Gift cards
CREATE INDEX idx_gift_cards_code      ON gift_cards(code);
CREATE INDEX idx_gift_cards_purchased ON gift_cards(purchased_by);
CREATE INDEX idx_gift_card_txn        ON gift_card_transactions(gift_card_id);

-- Reservations
CREATE INDEX idx_reservations_branch   ON reservations(branch_id);
CREATE INDEX idx_reservations_customer ON reservations(customer_id);
CREATE INDEX idx_reservations_table    ON reservations(table_id);
CREATE INDEX idx_reservations_date     ON reservations(reserved_date);
CREATE INDEX idx_reservations_status   ON reservations(status);

-- Delivery
CREATE INDEX idx_delivery_orders_branch    ON delivery_orders(branch_id);
CREATE INDEX idx_delivery_orders_platform  ON delivery_orders(platform_id);
CREATE INDEX idx_delivery_orders_status    ON delivery_orders(status);

-- Orders
CREATE INDEX idx_orders_branch      ON orders(branch_id);
CREATE INDEX idx_orders_shift       ON orders(shift_id);
CREATE INDEX idx_orders_user        ON orders(user_id);
CREATE INDEX idx_orders_customer    ON orders(customer_id);
CREATE INDEX idx_orders_table       ON orders(table_id);
CREATE INDEX idx_orders_status      ON orders(status);
CREATE INDEX idx_orders_type        ON orders(order_type);
CREATE INDEX idx_orders_number      ON orders(order_number);
CREATE INDEX idx_orders_created_at  ON orders(created_at);

-- Order items
CREATE INDEX idx_order_items_order   ON order_items(order_id);
CREATE INDEX idx_order_items_product ON order_items(product_id);

-- Sales
CREATE INDEX idx_sales_branch       ON sales(branch_id);
CREATE INDEX idx_sales_shift        ON sales(shift_id);
CREATE INDEX idx_sales_user         ON sales(user_id);
CREATE INDEX idx_sales_customer     ON sales(customer_id);
CREATE INDEX idx_sales_date         ON sales(sale_date);
CREATE INDEX idx_sales_business_date ON sales(business_date); 
CREATE INDEX idx_sales_number       ON sales(sale_number);

-- Sale items
CREATE INDEX idx_sale_items_sale    ON sale_items(sale_id);
CREATE INDEX idx_sale_items_product ON sale_items(product_id);

-- Shifts
CREATE INDEX idx_shifts_branch      ON shifts(branch_id);
CREATE INDEX idx_shifts_user        ON shifts(user_id);
CREATE INDEX idx_shifts_status      ON shifts(status);
CREATE INDEX idx_shifts_opened      ON shifts(opened_at);

-- Loyalty
CREATE INDEX idx_loyalty_customer   ON loyalty_transactions(customer_id);
CREATE INDEX idx_loyalty_order      ON loyalty_transactions(order_id);

-- Promotions
CREATE INDEX idx_promotions_branch  ON promotions(branch_id);
CREATE INDEX idx_promotions_active  ON promotions(is_active);
CREATE INDEX idx_promo_usages_order ON promotion_usages(order_id);
CREATE INDEX idx_promo_rules_promo  ON promotion_rules(promotion_id);

-- Ingredients
CREATE INDEX idx_ingredients_branch  ON ingredients(branch_id);

-- Ingredient stocks
CREATE INDEX idx_ingredient_stocks_ingredient ON ingredient_stocks(ingredient_id);
CREATE INDEX idx_ingredient_stocks_branch     ON ingredient_stocks(branch_id);

-- Inventory movements
CREATE INDEX idx_inv_mv_ingredient ON inventory_movements(ingredient_id);
CREATE INDEX idx_inv_mv_branch     ON inventory_movements(branch_id);
CREATE INDEX idx_inv_mv_type       ON inventory_movements(type);
CREATE INDEX idx_inv_mv_created    ON inventory_movements(created_at);

-- Goods receipts
CREATE INDEX idx_gr_po       ON goods_receipts(purchase_order_id);
CREATE INDEX idx_gr_branch   ON goods_receipts(branch_id);
CREATE INDEX idx_gr_supplier ON goods_receipts(supplier_id);
CREATE INDEX idx_gr_items_gr ON goods_receipt_items(goods_receipt_id);

-- Approval requests
CREATE INDEX idx_approval_status ON approval_requests(status);
CREATE INDEX idx_approval_type   ON approval_requests(type);
CREATE INDEX idx_approval_branch ON approval_requests(branch_id);

-- Activity logs
CREATE INDEX idx_activity_user    ON activity_logs(user_id);
CREATE INDEX idx_activity_target  ON activity_logs(target_type, target_id);
CREATE INDEX idx_activity_created ON activity_logs(created_at);

-- Audit logs
CREATE INDEX idx_audit_record     ON audit_logs(table_name, record_id);
CREATE INDEX idx_audit_changed_by ON audit_logs(changed_by);
CREATE INDEX idx_audit_changed_at ON audit_logs(changed_at);

-- KDS
CREATE INDEX idx_kds_orders_status  ON kds_orders(status);
CREATE INDEX idx_kds_orders_station ON kds_orders(station_id);
CREATE INDEX idx_kds_orders_order   ON kds_orders(order_id);

-- Purchase orders
CREATE INDEX idx_po_branch   ON purchase_orders(branch_id);
CREATE INDEX idx_po_supplier ON purchase_orders(supplier_id);
CREATE INDEX idx_po_status   ON purchase_orders(status);

-- Expenses
CREATE INDEX idx_expenses_branch   ON expenses(branch_id);
CREATE INDEX idx_expenses_date     ON expenses(expense_date);
CREATE INDEX idx_expenses_category ON expenses(category_id);

-- Coupons
CREATE INDEX idx_coupons_code   ON coupons(code);
CREATE INDEX idx_coupons_active ON coupons(is_active);

-- Stock transfers
CREATE INDEX idx_stock_transfers_from   ON stock_transfers(from_branch_id);
CREATE INDEX idx_stock_transfers_to     ON stock_transfers(to_branch_id);
CREATE INDEX idx_stock_transfers_status ON stock_transfers(status);

-- Print jobs
CREATE INDEX idx_print_jobs_branch     ON print_jobs(branch_id);
CREATE INDEX idx_print_jobs_sale       ON print_jobs(sale_id);
CREATE INDEX idx_print_jobs_order      ON print_jobs(order_id);
CREATE INDEX idx_print_jobs_printed_at ON print_jobs(printed_at);

-- Notifications
CREATE INDEX idx_notifications_user       ON notifications(user_id);
CREATE INDEX idx_notifications_is_read    ON notifications(is_read);
CREATE INDEX idx_notifications_created_at ON notifications(created_at);

-- Message logs
CREATE INDEX idx_msg_logs_customer ON message_logs(customer_id);
CREATE INDEX idx_msg_logs_status   ON message_logs(status);
CREATE INDEX idx_msg_logs_channel  ON message_logs(channel);

-- API keys
CREATE INDEX idx_api_keys_branch ON api_keys(branch_id);
CREATE INDEX idx_api_keys_active ON api_keys(is_active);

-- Webhooks
CREATE INDEX idx_webhooks_branch     ON webhooks(branch_id);
CREATE INDEX idx_webhook_logs_hook   ON webhook_logs(webhook_id);
CREATE INDEX idx_webhook_logs_status ON webhook_logs(status);

-- Daily summaries
CREATE INDEX idx_daily_summary_branch ON daily_summary(branch_id, summary_date);
CREATE INDEX idx_daily_product_branch ON daily_product_summary(branch_id, summary_date);

SET FOREIGN_KEY_CHECKS=1;
