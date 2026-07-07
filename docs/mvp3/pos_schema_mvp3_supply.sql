SET FOREIGN_KEY_CHECKS=0;

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

SET FOREIGN_KEY_CHECKS=1;
