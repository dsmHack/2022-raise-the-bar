CREATE TABLE rbi_series (
    id int NOT NULL AUTO_INCREMENT,
    name varchar(255),
    sku varchar(255),
    description varchar(65000),
    
    PRIMARY KEY (id)
);

CREATE TABLE rbi_event (
    id int NOT NULL AUTO_INCREMENT,
    series_id int NOT NULL,
    name varchar(255),
    start_time time(6),
    end_time time(6),
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

INSERT INTO `rbi_referral_type`(`name`) VALUES ('Social Media')
INSERT INTO `rbi_referral_type`(`name`) VALUES ('Person')
INSERT INTO `rbi_referral_type`(`name`) VALUES ('Event')
INSERT INTO `rbi_referral_type`(`name`) VALUES ('Website')
INSERT INTO `rbi_referral_type`(`name`) VALUES ('Other')

CREATE TABLE rbi_person_tag (
    person_id int NOT NULL,
    tag_id int NOT NULL,
    
    PRIMARY KEY (person_id, tag_id),
    FOREIGN KEY (person_id) REFERENCES wp_zbs_contacts(ID),
    FOREIGN KEY (tag_id) REFERENCES rbi_tag(id)
);

CREATE TABLE rbi_volunteer_event (
    id int NOT NULL AUTO_INCREMENT,
    event_id int NOT NULL,
    person_id int NOT NULL,
    start_time time(6),
    end_time time(6),
    
    PRIMARY KEY (id),
    FOREIGN KEY (event_id) REFERENCES rbi_event(id),
    FOREIGN KEY (person_id) REFERENCES wp_zbs_contacts(ID),
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
    referral_type_id int(11),
    referral_notes varchar(255),
    
    PRIMARY KEY (id),
    FOREIGN KEY (event_id) REFERENCES rbi_event(id),
    FOREIGN KEY (person_id) REFERENCES wp_zbs_contacts(ID),
    FOREIGN KEY (referral_type_id) REFERENCES rbi_referral_type(id)
);

CREATE TABLE rbi_volunteer_availability (
    id int NOT NULL AUTO_INCREMENT,
    person_id int NOT NULL,
    day varchar(255),
    start_time time(6),
    end_time time(6),
    
    PRIMARY KEY (id),
    FOREIGN KEY (person_id) REFERENCES wp_zbs_contacts(ID),
);
