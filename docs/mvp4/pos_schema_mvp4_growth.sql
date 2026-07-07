SET FOREIGN_KEY_CHECKS=0;

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

SET FOREIGN_KEY_CHECKS=1;
