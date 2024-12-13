-- -------------------------------------------------------------
-- TablePlus 6.1.8(574)
--
-- https://tableplus.com/
--
-- Database: hotel-bookings.db
-- Generation Time: 2024-12-13 14:08:54.5280
-- -------------------------------------------------------------


CREATE TABLE admin (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    price_budget FLOAT NOT NULL,
    price_standard FLOAT NOT NULL,
    price_luxury FLOAT NOT NULL,
    discount FLOAT NOT NULL
);

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
    price FLOAT NOT NULL
);

CREATE TABLE sqlite_sequence(name,seq);

CREATE TABLE transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    booking_id INTEGER NOT NULL,
    transfercode VARCHAR(255) NOT NULL,
    amount FLOAT NOT NULL,
    status VARCHAR(50) NOT NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id)
);

INSERT INTO "admin" ("id", "price_budget", "price_standard", "price_luxury", "discount") VALUES
('1', '1.0', '2.0', '3.0', '4.0');

INSERT INTO "bookings" ("id", "transfer_code", "room_id", "arrival_date", "departure_date", "total_cost", "status") VALUES
('1', '21320d20-0b14-4952-9f02-12f9fae5ca43', '1', '2024-12-12', '2024-12-14', '52.0', 'confirmed'),
('2', '21320d20-0b14-4952-9f02-12f9fae5ca43', '2', '2024-12-12', '2024-12-15', '106.0', 'confirmed'),
('3', '21320d20-0b14-4952-9f02-12f9fae5ca43', '2', '2024-12-12', '2024-12-15', '106.0', 'confirmed'),
('4', '21320d20-0b14-4952-9f02-12f9fae5ca43', '2', '2024-12-12', '2024-12-15', '106.0', 'confirmed');

INSERT INTO "bookings_features" ("booking_id", "feature_id") VALUES
('3', '1'),
('3', '2'),
('3', '3'),
('4', '1'),
('4', '2'),
('4', '3');

INSERT INTO "features" ("id", "feature_name", "price") VALUES
('1', 'Yatzy', '1.0'),
('2', 'PS5', '2.0'),
('3', 'Sauna', '3.0');

INSERT INTO "sqlite_sequence" ("name", "seq") VALUES
('admin', '1'),
('features', '3'),
('bookings', '4');

