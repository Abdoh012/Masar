-- 036: Add the two Student Profile structures that cannot be columns.
--
-- Two profile sections have no existing table at all, so this migration adds
-- exactly two normalized tables. Nothing existing is altered, renamed or
-- dropped, and no application / training / certificate / payment row is read
-- or written.
--
-- student_educations
--   A student's education history. MASAR has no education table: the only
--   academic facts on a student are the current ones already on `students`
--   (university_id / faculty_id / field_id / degree_id / specialization_id /
--   graduation_year), which describe the CURRENT enrolment and cannot express
--   a history or a second qualification.
--
--   The table reuses the existing lookups rather than duplicating them:
--   universities.id, faculties.id, study_fields.id, specializations.id and
--   degrees.id are all referenced by FK. Every FK is paired with a free-text
--   *_name column because a student's school is not always in the lookup
--   tables (an external or unlisted institution is normal), and
--   degree_name covers the many qualifications the `degrees` table does not
--   enumerate. A NULL *_id with a populated *_name is therefore a valid row,
--   which is what keeps this honest without forcing a bad lookup match.
--
--   Ordering is end_year DESC then id DESC: the most recent qualification
--   first, with id as the deterministic tie-break. An ongoing qualification
--   (end_year NULL, is_current 1) sorts to the top.
--
-- student_profile_views
--   Records that an identified viewer opened a student's public profile.
--
--   viewer_id is NOT NULL on purpose: every route that reaches the public
--   student profile already runs through middleware_auth(), so the viewer is
--   always identifiable, and a NOT NULL column makes the deduplication key
--   below meaningful. Self-views are never written by the service, so the
--   table only ever contains third-party views.
--
--   The composite PRIMARY KEY (student_id, viewer_id) IS the anti-duplicate
--   guarantee: repeat visits by the same viewer are ignored by the index
--   itself, so the total can never be inflated and the same request being
--   processed twice can never be counted twice - it cannot be expressed as a
--   double count. A repeated view therefore also does not refresh viewed_at,
--   which is what makes the column mean "first seen" rather than "last seen".
--
--   idx_student_profile_views_recent supports the recent-viewers read, which
--   is the query the profile actually runs.

-- 1) Guard: confirm neither table exists yet (informational).
SELECT
    TABLE_NAME
FROM information_schema.TABLES
WHERE
    TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME IN ('student_educations', 'student_profile_views');

-- 2) The student's education history.
CREATE TABLE `student_educations` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` bigint UNSIGNED NOT NULL,
  `degree_id` bigint UNSIGNED DEFAULT NULL,
  `degree_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `university_id` bigint UNSIGNED DEFAULT NULL,
  `university_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `faculty_id` bigint UNSIGNED DEFAULT NULL,
  `field_id` bigint UNSIGNED DEFAULT NULL,
  `specialization_id` bigint UNSIGNED DEFAULT NULL,
  `study_mode` enum('onsite','online','hybrid') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_year` year DEFAULT NULL,
  `end_year` year DEFAULT NULL,
  `grade` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_student_educations_student` (`student_id`),
  KEY `idx_student_educations_university` (`university_id`),
  KEY `idx_student_educations_faculty` (`faculty_id`),
  KEY `idx_student_educations_field` (`field_id`),
  KEY `idx_student_educations_specialization` (`specialization_id`),
  KEY `idx_student_educations_degree` (`degree_id`),
  CONSTRAINT `fk_student_educations_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_student_educations_degree` FOREIGN KEY (`degree_id`) REFERENCES `degrees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_student_educations_university` FOREIGN KEY (`university_id`) REFERENCES `universities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_student_educations_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_student_educations_field` FOREIGN KEY (`field_id`) REFERENCES `study_fields` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_student_educations_specialization` FOREIGN KEY (`specialization_id`) REFERENCES `specializations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 3) Third-party views of a student's public profile.
CREATE TABLE `student_profile_views` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` bigint UNSIGNED NOT NULL,
  `viewer_id` bigint UNSIGNED NOT NULL,
  `viewer_role` enum('student','company','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `viewed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_student_profile_views_viewer` (`student_id`,`viewer_id`),
  KEY `idx_student_profile_views_recent` (`student_id`,`viewed_at`),
  KEY `idx_student_profile_views_viewed_at` (`viewed_at`),
  CONSTRAINT `fk_student_profile_views_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_student_profile_views_viewer` FOREIGN KEY (`viewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
