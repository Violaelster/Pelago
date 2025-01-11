# Smooth Mansion
This project focuses on building a hotel website with a functional booking system, utilizing technologies such as HTML, CSS, PHP, JavaScript, and SQL. Additionally, it integrates an external API for specific functionality.
The website has been designed primarily for desktop users, and the booking system is currently limited to reservations within January 2025. This restriction is configurable and can be modified in the source code as needed.
Composer is also utilized in this project for dependency management.
Note that the database is not included in the repository. You will need to set up the database manually using the SQL queries provided below. 

## Features
- Booking System: A form that stores booking details in the database and checks availability.
- Calendar: A dynamic calendar displaying room availability.
- Admin Panel: Allows updating room prices and settings.


## Technologies
- HTML5, CSS3, JavaScript
- PHP 8.1
- MySQL
- Composer (dependency management)
- FileZilla (for deployment)

## Database structure:
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

CREATE TABLE sqlite_sequence(name,seq);

INSERT INTO "bookings" ("id", "transfer_code", "room_id", "arrival_date", "departure_date", "total_cost", "status") VALUES
('55', 'b32bc894-3d23-491c-a668-ec956146558b', '1', '2025-01-02', '2025-01-03', '20.79', 'confirmed'),
('56', 'd5df191a-4643-412a-8a15-fcf08e80d366', '1', '2025-01-17', '2025-01-18', '20.79', 'confirmed'),
('57', '295d4212-58cd-4708-b59b-6971594e91ac', '1', '2025-01-23', '2025-01-24', '20.79', 'confirmed'),
('58', '3ae8663e-b758-4fc9-8f73-70b98527ee1d', '1', '2025-01-15', '2025-01-16', '20.79', 'confirmed'),
('59', '8dd98488-301e-4117-9145-534af4250043', '2', '2025-01-02', '2025-01-03', '20.79', 'confirmed'),
('60', '3d703eb7-4df9-40a2-b4fc-1a0aa2f64ec5', '2', '2025-01-20', '2025-01-21', '20.79', 'confirmed');

INSERT INTO "bookings_features" ("booking_id", "feature_id") VALUES
('55', '1'),
('56', '2'),
('57', '2'),
('58', '2'),
('59', '1'),
('60', '1');

INSERT INTO "features" ("id", "feature_name", "price") VALUES
('1', 'Yatzy', '11.0'),
('2', 'PS5', '11.0'),
('3', 'Sauna', '11.0');

INSERT INTO "rooms" ("id", "room_type", "price", "discount") VALUES
('1', 'Budget', '11.0', '11.0'),
('2', 'Standard', '11.0', '11.0'),
('3', 'Luxury', '11.0', '98.0');


# FEEDBACK FROM VIKTOR

## GENERAL PRAISE

The website looks really good, great concept and execution. The general structure of the project is impeccable and everything works nicely.

## PROJECT STRUCTURE

- Missing an .env.example - handy instructions on requirements [here](https://github.com/yrgo/WU24/tree/main/PHP/26%20-%20ENV)

## CSS

- ../booking/form.css:1-1 - Missing the start of a region, easy fix!
- ../booking/rooms.css:85-95 - Your comments are both in English and Swedish throughout the project: convention says to use English, but try to stick to one language.
- variables.css:28-30 - Commented out code should either be deleted or have a comment that explains the context on why it's there.

### CONCLUSION

Great structure, DRY code. In my opinion there's merit in being consistent and once you've started using regions to also add them to smaller documents like ../booking/calendar.css as well. Or, if they're so small that they don't need a region, maybe append them to a larger document. But that's an opinion!

## PHP

- ../config/app.php:4-4 - While the comment on the previous line is helpful and provides context, it's a good idea to remove comments like this one that offers no context or help(and is more than likely a remnant of asking AI for help(which there's nothing wrong with of course))
- ../config/paths.php:1-23 - Personally, I'd stick this in ../config/app.php for easier upkeep of the code. Also, most of the comments feel superfluous.
- ../includes/database.php:23-35 - Some comments explaining the process and what's happening here would be helpful and make the code more readable.
- ../pages/booking/booking_functions.php and ../pages/booking/booking_validation.php - seems like these are both function junction files - it'd be more DRY to merge them.
- ../pages/booking/room_calendar.php:11-15 - To improve structure, I'd move this function to ../pages/booking/booking_functions.php or similar.

### CONCLUSION

Once again, great structure and a lot of thoughtful comments. While the structure differs from what we've used during class, it's rigid and well put together. It uses a lot of concepts and syntax I'm unfamiliar and we haven't covered yet in class and (this might be me reading in a little bit ) it might be a good idea to look over your code again and make sure that, if asked, you understand and can explain what parts do what.

## DATABASE

Looks good and works nicely. Down the line, it might be benefitial to add a 'guests' table if you decide to handle more data about the user.
