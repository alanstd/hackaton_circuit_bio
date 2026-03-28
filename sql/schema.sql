-- Schéma minimal proposé pour une évolution MySQL / MariaDB

CREATE TABLE protocol_template (
    protocol_code VARCHAR(50) PRIMARY KEY,
    label VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE protocol_template_event (
    id INT AUTO_INCREMENT PRIMARY KEY,
    protocol_code VARCHAR(50) NOT NULL,
    step_order INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    sample_type VARCHAR(100) NOT NULL,
    offset_days INT NOT NULL,
    window_label VARCHAR(100) NOT NULL,
    instructions TEXT NULL,
    CONSTRAINT fk_protocol_template_event_protocol
        FOREIGN KEY (protocol_code) REFERENCES protocol_template(protocol_code)
);

CREATE TABLE patient_schedule_import (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    pseudo_patient_code VARCHAR(120) NOT NULL,
    protocol_code VARCHAR(50) NOT NULL,
    arm_code VARCHAR(30) NULL,
    reference_date DATE NOT NULL,
    qr_hash CHAR(64) NOT NULL,
    imported_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_patient_schedule_import_qr_hash (qr_hash)
);

CREATE TABLE patient_schedule_event (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    schedule_import_id BIGINT NOT NULL,
    step_code VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    sample_type VARCHAR(100) NOT NULL,
    scheduled_at DATE NOT NULL,
    window_label VARCHAR(100) NOT NULL,
    status_code VARCHAR(30) NOT NULL DEFAULT 'upcoming',
    instructions TEXT NULL,
    CONSTRAINT fk_patient_schedule_event_import
        FOREIGN KEY (schedule_import_id) REFERENCES patient_schedule_import(id)
);
