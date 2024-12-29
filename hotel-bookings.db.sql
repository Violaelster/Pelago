-- -------------------------------------------------------------
-- TablePlus 6.2.0(576)
--
-- https://tableplus.com/
--
-- Database: hotel-bookings.db
-- Generation Time: 2024-12-20 12:54:02.3850
-- -------------------------------------------------------------


CREATE TABLE bookings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    transfer_code VARCHAR(255) NOT NULL,
    room_id INTEGER NOT NULL,
    arrival_date DATETIME NOT NULL,
    departure_date DATETIME NOT NULL,
    total_cost FLOAT NOT NULL,
    status VARCHAR(50) NOT NULL,
    FOREIGN KEY (room_id) REFERENCES rooms(id)
);

CREATE TABLE bookings_features (
    booking_id INTEGER NOT NULL,
    feature_id INTEGER NOT NULL,
    PRIMARY KEY (booking_id, feature_id),
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    FOREIGN KEY (feature_id) REFERENCES features(id)
);

CREATE TABLE features (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    feature_name TEXT NOT NULL,
    price FLOAT NOT NULL
);

CREATE TABLE rooms (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    room_type TEXT NOT NULL,
    price FLOAT NOT NULL,
    discount FLOAT DEFAULT 0
);

INSERT INTO "bookings" ("id", "transfer_code", "room_id", "arrival_date", "departure_date", "total_cost", "status") VALUES
('33', '21320d20-0b14-4952-9f02-12f9fae5ca43', '1', '2025-01-01', '2025-01-02', '19.0', 'confirmed'),
('34', '21320d20-0b14-4952-9f02-12f9fae5ca43', '1', '2025-01-01', '2025-01-02', '19.0', 'confirmed'),
('35', '21320d20-0b14-4952-9f02-12f9fae5ca43', '1', '2025-01-01', '2025-01-02', '19.0', 'confirmed');

INSERT INTO "bookings_features" ("booking_id", "feature_id") VALUES
('33', '1'),
('34', '1'),
('35', '2');

INSERT INTO "features" ("id", "feature_name", "price") VALUES
('1', 'Yatzy', '10.0'),
('2', 'PS5', '10.0'),
('3', 'Sauna', '10.0');

INSERT INTO "rooms" ("id", "room_type", "price", "discount") VALUES
('1', 'Budget', '10.0', '10.0'),
('2', 'Standard', '10.0', '10.0'),
('3', 'Luxury', '10.0', '10.0');

