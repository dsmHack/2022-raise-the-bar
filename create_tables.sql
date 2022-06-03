CREATE TABLE rbi_person (
    id int NOT NULL AUTO_INCREMENT,
    first_name varchar(255),
    last_name varchar(255),
    email varchar(255),
    gender varchar(1),
    address_1 varchar(255),
    address_2 varchar(255),
    address_city varchar(255),
    address_state varchar(255),
    address_zip varchar(255),
    address_country varchar(255),
    phone_number varchar(255),
    referral varchar(255),
    comments varchar(255),
    notes varchar(255),
    
    PRIMARY KEY (id)
);

CREATE TABLE rbi_series (
    id int NOT NULL AUTO_INCREMENT,
    name varchar(255),
    sku varchar(255),
    description varchar(255),
    address_1 varchar(255),
    address_2 varchar(255),
    address_city varchar(255),
    address_state varchar(255),
    address_zip varchar(255),
    address_country varchar(255),
    
    PRIMARY KEY (id)
);

CREATE TABLE rbi_event (
    id int NOT NULL AUTO_INCREMENT,
    series_id int NOT NULL,
    name varchar(255),
    start_time varchar(255),
    end_time varchar(255),
    date DATE,
    address_1 varchar(255),
    address_2 varchar(255),
    address_city varchar(255),
    address_state varchar(255),
    address_zip varchar(255),
    address_country varchar(255),
    
    PRIMARY KEY (id),
    FOREIGN KEY (series_id) REFERENCES rbi_series(id)
);

CREATE TABLE rbi_tag (
    id int NOT NULL AUTO_INCREMENT,
    name varchar(255),
    
    PRIMARY KEY (id)
);

CREATE TABLE rbi_referral_type (
    id int NOT NULL AUTO_INCREMENT,
    name varchar(255),
    
    PRIMARY KEY (id)
);

CREATE TABLE rbi_person_tag (
    person_id int NOT NULL,
    tag_id int NOT NULL,
    
    PRIMARY KEY (person_id, tag_id),
    FOREIGN KEY (person_id) REFERENCES rbi_person(id),
    FOREIGN KEY (tag_id) REFERENCES rbi_tag(id)
);

CREATE TABLE rbi_volunteer_event (
    id int NOT NULL AUTO_INCREMENT,
    event_id int NOT NULL,
    person_id int NOT NULL,
    start_time varchar(255),
    end_time varchar(255),
    
    PRIMARY KEY (id),
    FOREIGN KEY (event_id) REFERENCES rbi_event(id),
    FOREIGN KEY (person_id) REFERENCES rbi_person(id)
);

CREATE TABLE rbi_volunteer_event_tag (
    volunteer_event_id int NOT NULL,
    tag_id int NOT NULL,
    
    PRIMARY KEY (volunteer_event_id, tag_id),
    FOREIGN KEY (volunteer_event_id) REFERENCES rbi_volunteer_event(id),
    FOREIGN KEY (tag_id) REFERENCES rbi_tag(id)
);

CREATE TABLE rbi_participant_event (
    id int NOT NULL AUTO_INCREMENT,
    event_id int NOT NULL,
    person_id int NOT NULL,
    name varchar(255),
    referral_type_id int,
    referral_notes varchar(255),
    
    PRIMARY KEY (id),
    FOREIGN KEY (event_id) REFERENCES rbi_event(id),
    FOREIGN KEY (person_id) REFERENCES rbi_person(id),
    FOREIGN KEY (referral_type_id) REFERENCES rbi_referral_type(id)
);
