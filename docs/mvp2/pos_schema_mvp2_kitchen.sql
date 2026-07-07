SET FOREIGN_KEY_CHECKS=0;

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

SET FOREIGN_KEY_CHECKS=1;
