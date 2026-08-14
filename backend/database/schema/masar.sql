-- ============================================================
-- MASAR (مسار) - Backend Database
-- MySQL 8.0+
-- MVP schema for Native PHP REST API
-- ============================================================

CREATE DATABASE IF NOT EXISTS masar
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE masar;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS conversations;
DROP TABLE IF EXISTS certificate_appeals;
DROP TABLE IF EXISTS certificates;
DROP TABLE IF EXISTS training_sessions;
DROP TABLE IF EXISTS training_applications;
DROP TABLE IF EXISTS training_skills;
DROP TABLE IF EXISTS training_specializations;
DROP TABLE IF EXISTS training_listings;
DROP TABLE IF EXISTS company_specializations;
DROP TABLE IF EXISTS company_work_fields;
DROP TABLE IF EXISTS student_skills;
DROP TABLE IF EXISTS files;
DROP TABLE IF EXISTS skills;
DROP TABLE IF EXISTS specializations;
DROP TABLE IF EXISTS degrees;
DROP TABLE IF EXISTS faculties;
DROP TABLE IF EXISTS universities;
DROP TABLE IF EXISTS companies;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS refresh_tokens;
DROP TABLE IF EXISTS auth_tokens;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS oauth_states;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role ENUM('student','company','admin') NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('active','pending','suspended','rejected','deleted') NOT NULL DEFAULT 'active',
    email_verified_at DATETIME NULL,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    INDEX idx_users_role (role),
    INDEX idx_users_status (status)
) ENGINE=InnoDB;

CREATE TABLE refresh_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash VARCHAR(255) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    revoked_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_refresh_tokens_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    INDEX idx_refresh_user (user_id),
    INDEX idx_refresh_expires (expires_at)
) ENGINE=InnoDB;

CREATE TABLE auth_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash VARCHAR(255) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    revoked_at DATETIME NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_auth_tokens_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    INDEX idx_auth_tokens_user (user_id),
    INDEX idx_auth_tokens_expires (expires_at)
) ENGINE=InnoDB;

CREATE TABLE password_resets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash VARCHAR(255) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_resets_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    INDEX idx_password_resets_user (user_id),
    INDEX idx_password_resets_expires (expires_at)
) ENGINE=InnoDB;

CREATE TABLE oauth_states (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nonce VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_oauth_states_nonce (nonce),
    INDEX idx_oauth_states_expires (expires_at)
) ENGINE=InnoDB;

CREATE TABLE universities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    city VARCHAR(100) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_university_name (name)
) ENGINE=InnoDB;

CREATE TABLE faculties (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    university_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_faculties_university
        FOREIGN KEY (university_id) REFERENCES universities(id)
        ON DELETE RESTRICT,
    UNIQUE KEY uq_faculty_university_name (university_id, name),
    INDEX idx_faculties_university (university_id)
) ENGINE=InnoDB;

CREATE TABLE degrees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    level ENUM('diploma','bachelor','master','doctorate','other') NOT NULL DEFAULT 'bachelor',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_degree_name_level (name, level)
) ENGINE=InnoDB;

CREATE TABLE specializations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    parent_id BIGINT UNSIGNED NULL,
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_specializations_parent
        FOREIGN KEY (parent_id) REFERENCES specializations(id)
        ON DELETE SET NULL,
    UNIQUE KEY uq_specialization_name (name),
    INDEX idx_specializations_parent (parent_id)
) ENGINE=InnoDB;

CREATE TABLE skills (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_skill_name (name)
) ENGINE=InnoDB;

CREATE TABLE students (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NULL,
    bio TEXT NULL,
    university_id BIGINT UNSIGNED NULL,
    faculty_id BIGINT UNSIGNED NULL,
    degree_id BIGINT UNSIGNED NULL,
    specialization_id BIGINT UNSIGNED NULL,
    graduation_year YEAR NULL,
    city VARCHAR(100) NULL,
    profile_image_file_id BIGINT UNSIGNED NULL,
    cv_file_id BIGINT UNSIGNED NULL,
    is_profile_complete TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_students_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_students_university
        FOREIGN KEY (university_id) REFERENCES universities(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_students_faculty
        FOREIGN KEY (faculty_id) REFERENCES faculties(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_students_degree
        FOREIGN KEY (degree_id) REFERENCES degrees(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_students_specialization
        FOREIGN KEY (specialization_id) REFERENCES specializations(id)
        ON DELETE SET NULL,

    INDEX idx_students_university (university_id),
    INDEX idx_students_faculty (faculty_id),
    INDEX idx_students_degree (degree_id),
    INDEX idx_students_specialization (specialization_id),
    INDEX idx_students_city (city)
) ENGINE=InnoDB;

CREATE TABLE student_skills (
    student_id BIGINT UNSIGNED NOT NULL,
    skill_id BIGINT UNSIGNED NOT NULL,
    proficiency ENUM('beginner','intermediate','advanced','expert') NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (student_id, skill_id),

    CONSTRAINT fk_student_skills_student
        FOREIGN KEY (student_id) REFERENCES students(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_student_skills_skill
        FOREIGN KEY (skill_id) REFERENCES skills(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE companies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    legal_name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    website VARCHAR(255) NULL,
    phone VARCHAR(50) NULL,
    city VARCHAR(100) NULL,
    address VARCHAR(500) NULL,
    approval_status ENUM('pending','approved','rejected','suspended') NOT NULL DEFAULT 'pending',
    approved_at DATETIME NULL,
    approved_by BIGINT UNSIGNED NULL,
    rejection_reason TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_companies_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_companies_approved_by
        FOREIGN KEY (approved_by) REFERENCES users(id)
        ON DELETE SET NULL,

    INDEX idx_companies_status (approval_status),
    INDEX idx_companies_city (city)
) ENGINE=InnoDB;

CREATE TABLE company_work_fields (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_company_work_fields_company
        FOREIGN KEY (company_id) REFERENCES companies(id)
        ON DELETE CASCADE,

    UNIQUE KEY uq_company_work_field (company_id, name)
) ENGINE=InnoDB;

CREATE TABLE company_specializations (
    company_id BIGINT UNSIGNED NOT NULL,
    specialization_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (company_id, specialization_id),

    CONSTRAINT fk_company_specializations_company
        FOREIGN KEY (company_id) REFERENCES companies(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_company_specializations_specialization
        FOREIGN KEY (specialization_id) REFERENCES specializations(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE files (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type ENUM('cv','profile_image','certificate_attachment','other') NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    path VARCHAR(500) NOT NULL,
    mime_type VARCHAR(150) NOT NULL,
    size_bytes BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_files_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,

    INDEX idx_files_user_type (user_id, type)
) ENGINE=InnoDB;

ALTER TABLE students
    ADD CONSTRAINT fk_students_profile_image_file
        FOREIGN KEY (profile_image_file_id) REFERENCES files(id)
        ON DELETE SET NULL,
    ADD CONSTRAINT fk_students_cv_file
        FOREIGN KEY (cv_file_id) REFERENCES files(id)
        ON DELETE SET NULL;

CREATE TABLE training_listings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,

    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    field VARCHAR(255) NOT NULL,

    training_type ENUM('shadowing','hands_on','project_based') NOT NULL,
    mode ENUM('onsite','remote','hybrid') NOT NULL,

    may_lead_to_employment TINYINT(1) NOT NULL DEFAULT 0,

    is_paid TINYINT(1) NOT NULL DEFAULT 0,
    compensation_amount DECIMAL(12,2) NULL,
    compensation_currency CHAR(3) NOT NULL DEFAULT 'EGP',

    trial_period_days INT UNSIGNED NULL,
    capacity INT UNSIGNED NULL,
    status ENUM('draft','published','closed') NOT NULL DEFAULT 'draft',
    published_at DATETIME NULL,
    starts_at DATETIME NULL,
    ends_at DATETIME NULL,
    closed_at DATETIME NULL,
    location VARCHAR(500) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_training_company
        FOREIGN KEY (company_id) REFERENCES companies(id)
        ON DELETE CASCADE,

    CONSTRAINT chk_training_paid_rules CHECK (
        (is_paid = 0 AND compensation_amount IS NULL AND trial_period_days IS NULL)
        OR
        (is_paid = 1 AND compensation_amount IS NOT NULL AND trial_period_days >= 7)
    ),

    INDEX idx_training_company (company_id),
    INDEX idx_training_status (status),
    INDEX idx_training_field (field),
    INDEX idx_training_type (training_type),
    INDEX idx_training_mode (mode),
    INDEX idx_training_ends_at (ends_at)
) ENGINE=InnoDB;

CREATE TABLE training_specializations (
    training_id BIGINT UNSIGNED NOT NULL,
    specialization_id BIGINT UNSIGNED NOT NULL,

    PRIMARY KEY (training_id, specialization_id),

    CONSTRAINT fk_training_specializations_training
        FOREIGN KEY (training_id) REFERENCES training_listings(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_training_specializations_specialization
        FOREIGN KEY (specialization_id) REFERENCES specializations(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE training_skills (
    training_id BIGINT UNSIGNED NOT NULL,
    skill_id BIGINT UNSIGNED NOT NULL,

    PRIMARY KEY (training_id, skill_id),

    CONSTRAINT fk_training_skills_training
        FOREIGN KEY (training_id) REFERENCES training_listings(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_training_skills_skill
        FOREIGN KEY (skill_id) REFERENCES skills(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE training_applications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    training_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,

    message TEXT NULL,
    status ENUM('submitted','accepted','rejected','withdrawn') NOT NULL DEFAULT 'submitted',
    rejection_reason ENUM(
        'position_filled',
        'candidate_not_suitable',
        'requirements_not_met',
        'training_closed',
        'other'
    ) NULL,
    rejection_note TEXT NULL,
    applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reviewed_at DATETIME NULL,
    withdrawn_at DATETIME NULL,
    reviewed_by BIGINT UNSIGNED NULL,

    CONSTRAINT fk_applications_training
        FOREIGN KEY (training_id) REFERENCES training_listings(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_applications_student
        FOREIGN KEY (student_id) REFERENCES students(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_applications_reviewer
        FOREIGN KEY (reviewed_by) REFERENCES users(id)
        ON DELETE SET NULL,

    UNIQUE KEY uq_training_student_application (training_id, student_id),
    CONSTRAINT chk_rejection_reason CHECK (
        (status = 'rejected' AND rejection_reason IS NOT NULL)
        OR
        (status <> 'rejected')
    ),
    INDEX idx_applications_training_status (training_id, status),
    INDEX idx_applications_student_status (student_id, status)
) ENGINE=InnoDB;

CREATE TABLE training_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_id BIGINT UNSIGNED NOT NULL UNIQUE,
    training_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    status ENUM(
        'trial',
        'continuing',
        'completed',
        'stopped',
        'cancelled'
    ) NOT NULL DEFAULT 'trial',
    started_at DATETIME NOT NULL,
    trial_started_at DATETIME NULL,
    trial_ends_at DATETIME NULL,
    student_continuation_confirmed_at DATETIME NULL,
    actual_ended_at DATETIME NULL,
    employment_opportunity TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_training_sessions_application
        FOREIGN KEY (application_id) REFERENCES training_applications(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_training_sessions_training
        FOREIGN KEY (training_id) REFERENCES training_listings(id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_training_sessions_student
        FOREIGN KEY (student_id) REFERENCES students(id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_training_sessions_company
        FOREIGN KEY (company_id) REFERENCES companies(id)
        ON DELETE RESTRICT,

    INDEX idx_training_sessions_student (student_id),
    INDEX idx_training_sessions_company (company_id),
    INDEX idx_training_sessions_status (status),
    INDEX idx_training_sessions_trial_end (trial_ends_at)
) ENGINE=InnoDB;

CREATE TABLE certificates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    certificate_code VARCHAR(100) NOT NULL UNIQUE,
    student_id BIGINT UNSIGNED NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    training_id BIGINT UNSIGNED NOT NULL,
    training_session_id BIGINT UNSIGNED NOT NULL UNIQUE,
    status ENUM(
        'requested',
        'approved',
        'rejected',
        'revoked'
    ) NOT NULL DEFAULT 'requested',
    title VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    grade DECIMAL(5,2) NULL,
    grade_label VARCHAR(100) NULL,
    employment_eligible TINYINT(1) NOT NULL DEFAULT 0,
    requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reviewed_at DATETIME NULL,
    approved_at DATETIME NULL,
    revoked_at DATETIME NULL,
    reviewed_by BIGINT UNSIGNED NULL,
    rejection_reason TEXT NULL,
    revocation_reason TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_certificates_student
        FOREIGN KEY (student_id) REFERENCES students(id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_certificates_company
        FOREIGN KEY (company_id) REFERENCES companies(id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_certificates_training
        FOREIGN KEY (training_id) REFERENCES training_listings(id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_certificates_session
        FOREIGN KEY (training_session_id) REFERENCES training_sessions(id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_certificates_reviewer
        FOREIGN KEY (reviewed_by) REFERENCES users(id)
        ON DELETE SET NULL,
    CONSTRAINT chk_certificate_dates CHECK (end_date >= start_date),
    CONSTRAINT chk_certificate_grade CHECK (
        grade IS NULL OR (grade >= 0 AND grade <= 100)
    ),
    INDEX idx_certificates_student (student_id),
    INDEX idx_certificates_company (company_id),
    INDEX idx_certificates_status (status)
) ENGINE=InnoDB;

CREATE TABLE certificate_appeals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    certificate_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    reason TEXT NOT NULL,
    status ENUM(
        'submitted',
        'under_review',
        'approved',
        'rejected',
        'closed'
    ) NOT NULL DEFAULT 'submitted',
    admin_note TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reviewed_at DATETIME NULL,
    reviewed_by BIGINT UNSIGNED NULL,

    CONSTRAINT fk_certificate_appeals_certificate
        FOREIGN KEY (certificate_id) REFERENCES certificates(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_certificate_appeals_student
        FOREIGN KEY (student_id) REFERENCES students(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_certificate_appeals_reviewer
        FOREIGN KEY (reviewed_by) REFERENCES users(id)
        ON DELETE SET NULL,
    INDEX idx_certificate_appeals_certificate (certificate_id),
    INDEX idx_certificate_appeals_student (student_id),
    INDEX idx_certificate_appeals_status (status)
) ENGINE=InnoDB;

CREATE TABLE conversations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    application_id BIGINT UNSIGNED NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_conversations_student
        FOREIGN KEY (student_id) REFERENCES students(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_conversations_company
        FOREIGN KEY (company_id) REFERENCES companies(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_conversations_application
        FOREIGN KEY (application_id) REFERENCES training_applications(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT UNSIGNED NOT NULL,
    sender_user_id BIGINT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    read_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_messages_conversation
        FOREIGN KEY (conversation_id) REFERENCES conversations(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_messages_sender
        FOREIGN KEY (sender_user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    INDEX idx_messages_conversation_created (conversation_id, created_at),
    INDEX idx_messages_sender (sender_user_id)
) ENGINE=InnoDB;

CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    entity_type VARCHAR(100) NULL,
    entity_id BIGINT UNSIGNED NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    read_at DATETIME NULL,
    email_sent_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_notifications_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    INDEX idx_notifications_user_read (user_id, is_read),
    INDEX idx_notifications_user_created (user_id, created_at)
) ENGINE=InnoDB;

CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    training_id BIGINT UNSIGNED NOT NULL,
    training_session_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'EGP',
    platform_commission_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    platform_commission_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    company_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    payment_method ENUM(
        'manual',
        'paymob',
        'other'
    ) NOT NULL DEFAULT 'manual',
    status ENUM(
        'pending',
        'paid',
        'failed',
        'refunded',
        'cancelled'
    ) NOT NULL DEFAULT 'pending',
    external_reference VARCHAR(255) NULL,
    paid_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_payments_training
        FOREIGN KEY (training_id) REFERENCES training_listings(id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_payments_session
        FOREIGN KEY (training_session_id) REFERENCES training_sessions(id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_payments_student
        FOREIGN KEY (student_id) REFERENCES students(id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_payments_company
        FOREIGN KEY (company_id) REFERENCES companies(id)
        ON DELETE RESTRICT,
    INDEX idx_payments_student (student_id),
    INDEX idx_payments_company (company_id),
    INDEX idx_payments_status (status),
    INDEX idx_payments_external_reference (external_reference)
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(100) NOT NULL,
    entity_id BIGINT UNSIGNED NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_audit_logs_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL,
    INDEX idx_audit_user (user_id),
    INDEX idx_audit_entity (entity_type, entity_id),
    INDEX idx_audit_action (action),
    INDEX idx_audit_created (created_at)
) ENGINE=InnoDB;

INSERT INTO degrees (name, level) VALUES
('Bachelor of Science', 'bachelor'),
('Bachelor of Engineering', 'bachelor'),
('Bachelor of Commerce', 'bachelor'),
('Bachelor of Arts', 'bachelor'),
('Master', 'master'),
('Doctorate', 'doctorate');

INSERT INTO skills (name) VALUES
('PHP'),
('MySQL'),
('JavaScript'),
('TypeScript'),
('React'),
('Next.js'),
('Node.js'),
('Python'),
('Java'),
('C++'),
('HTML'),
('CSS'),
('UI/UX Design'),
('Graphic Design'),
('Digital Marketing'),
('Data Analysis'),
('Machine Learning'),
('Project Management'),
('Communication'),
('Problem Solving');

INSERT INTO specializations (name, description) VALUES
('Software Engineering', 'Software engineering and application development'),
('Web Development', 'Web and backend/frontend development'),
('Mobile Development', 'Mobile application development'),
('Data Science', 'Data science and analytics'),
('Artificial Intelligence', 'AI and machine learning'),
('Cyber Security', 'Information and cyber security'),
('Mechanical Engineering', 'Mechanical engineering'),
('Electrical Engineering', 'Electrical engineering'),
('Civil Engineering', 'Civil engineering'),
('Business Administration', 'Business and management'),
('Accounting', 'Accounting and financial operations'),
('Marketing', 'Marketing and digital marketing'),
('Graphic Design', 'Visual and graphic design');

SET FOREIGN_KEY_CHECKS = 1;
