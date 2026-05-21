USE ecodrive;
INSERT INTO users (username, email, password, is_admin)
VALUES
    (
        'tesztuser1',
        'tesztuser1@example.com',
        '$argon2i$v=19$m=65536,t=4,p=1$SkN3ekpPZ2N0d3p2UTlCUA$cWDEOQ8MTaYXvdg6ew/gKUUIS8zgeAD7P2yv1oLO1LI',
        0
    ),
    (
        'tesztuser2',
        'tesztuser2@example.com',
        '$argon2i$v=19$m=65536,t=4,p=1$SkN3ekpPZ2N0d3p2UTlCUA$cWDEOQ8MTaYXvdg6ew/gKUUIS8zgeAD7P2yv1oLO1LI',
        0
    ),
    (
        'tesztuser3',
        'tesztuser3@example.com',
        '$argon2i$v=19$m=65536,t=4,p=1$SkN3ekpPZ2N0d3p2UTlCUA$cWDEOQ8MTaYXvdg6ew/gKUUIS8zgeAD7P2yv1oLO1LI',
        0
    )
ON DUPLICATE KEY UPDATE
    email = VALUES(email),
    password = VALUES(password),
    is_admin = VALUES(is_admin);

INSERT INTO vehicles (user, brand, model, license_plate, year, consumption, emission)
VALUES
    (
        (SELECT id FROM users WHERE username = 'tesztuser1'),
        'Tesztla',
        'Oktavia',
        'ABC-123',
        YEAR(CURDATE()),
        3.4,
        100.0
    ),
    (
        (SELECT id FROM users WHERE username = 'tesztuser1'),
        'Subaru',
        'Forester',
        'AB-CD-123',
        YEAR(CURDATE()) - 3,
        7.2,
        146.5
    ),
    (
        (SELECT id FROM users WHERE username = 'tesztuser2'),
        'Skoda',
        'Fabia',
        'XYZ1234',
        YEAR(CURDATE()) - 5,
        5.6,
        120.0
    )
ON DUPLICATE KEY UPDATE
    user = VALUES(user),
    brand = VALUES(brand),
    model = VALUES(model),
    year = VALUES(year),
    consumption = VALUES(consumption),
    emission = VALUES(emission);

DELETE FROM routes
WHERE vehicle IN (
    SELECT id
    FROM vehicles
    WHERE license_plate IN ('ABC-123', 'AB-CD-123', 'XYZ1234')
);

INSERT INTO routes (
    vehicle,
    travel_start_time,
    distance,
    emission,
    from_zip,
    from_city,
    from_street,
    to_zip,
    to_city,
    to_street
)
VALUES
    (
        (SELECT id FROM vehicles WHERE license_plate = 'ABC-123'),
        STR_TO_DATE(
            CONCAT(DATE_FORMAT(CURDATE(), '%Y-%m'), '-05 08:15:00'),
            '%Y-%m-%d %H:%i:%s'
        ),
        10.0,
        10.0 * 100.0,
        1111,
        'Budapest',
        'Fo ut 1',
        2222,
        'Gyor',
        'Kossuth Lajos utca 2'
    ),
    (
        (SELECT id FROM vehicles WHERE license_plate = 'ABC-123'),
        STR_TO_DATE(
            CONCAT(DATE_FORMAT(CURDATE(), '%Y-%m'), '-12 17:45:00'),
            '%Y-%m-%d %H:%i:%s'
        ),
        5.0,
        5.0 * 100.0,
        2222,
        'Gyor',
        'Szechenyi ter 3',
        1111,
        'Budapest',
        'Fo ut 1'
    ),
    (
        (SELECT id FROM vehicles WHERE license_plate = 'AB-CD-123'),
        STR_TO_DATE(
            CONCAT(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m'), '-10 09:30:00'),
            '%Y-%m-%d %H:%i:%s'
        ),
        8.0,
        8.0 * 146.5,
        3300,
        'Eger',
        'Kertesz utca 4',
        4024,
        'Debrecen',
        'Piac utca 5'
    ),
    (
        (SELECT id FROM vehicles WHERE license_plate = 'ABC-123'),
        STR_TO_DATE(
            CONCAT(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 YEAR), '%Y-%m'), '-18 07:50:00'),
            '%Y-%m-%d %H:%i:%s'
        ),
        7.5,
        7.5 * 100.0,
        1111,
        'Budapest',
        'Rakoczi ut 8',
        9021,
        'Gyor',
        'Arpad ut 12'
    ),
    (
        (SELECT id FROM vehicles WHERE license_plate = 'XYZ1234'),
        STR_TO_DATE(
            CONCAT(DATE_FORMAT(CURDATE(), '%Y-%m'), '-06 11:10:00'),
            '%Y-%m-%d %H:%i:%s'
        ),
        12.0,
        12.0 * 120.0,
        6720,
        'Szeged',
        'Tisza Lajos korut 10',
        7633,
        'Pecs',
        'Ifjusag utja 6'
    );