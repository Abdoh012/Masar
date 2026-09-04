<?php

/**
 * MASAR - Demo Data Seeder
 *
 * Populates a realistic, internally consistent Egyptian demo dataset:
 * 2 admins, 40 students, 15 companies (13 approved / 1 pending / 1 rejected),
 * 36 trainings, 140+ applications, sessions, certificates, conversations,
 * messages, notifications, saved trainings, payments and audit logs.
 *
 * Lookup tables (universities, faculties, degrees, study_fields,
 * specializations, skills) are NEVER wiped. Missing lookup rows requested by
 * the product spec (E-JUST, Pharos, a few faculties, specializations and
 * skills) are added safely via find-or-create upserts.
 *
 * Re-runnable: each run removes rows owned by the demo accounts (identified by
 * the demo emails below) plus orphaned/stale leftovers, then re-seeds.
 * Non-demo data is preserved.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/core/database/connection.php';

if (!defined('NOTIFICATION_APPLICATION_ACCEPTED')) {
    define('NOTIFICATION_APPLICATION_ACCEPTED', 'application_accepted');
}
if (!defined('NOTIFICATION_APPLICATION_REJECTED')) {
    define('NOTIFICATION_APPLICATION_REJECTED', 'application_rejected');
}
if (!defined('NOTIFICATION_APPLICATION_WITHDRAWN')) {
    define('NOTIFICATION_APPLICATION_WITHDRAWN', 'application_withdrawn');
}
if (!defined('NOTIFICATION_TRAINING_CLOSED')) {
    define('NOTIFICATION_TRAINING_CLOSED', 'training_closed');
}
if (!defined('NOTIFICATION_TRAINING_UPDATED')) {
    define('NOTIFICATION_TRAINING_UPDATED', 'training_updated');
}
if (!defined('NOTIFICATION_NEW_MESSAGE')) {
    define('NOTIFICATION_NEW_MESSAGE', 'new_message');
}
if (!defined('NOTIFICATION_CERTIFICATE_REQUESTED')) {
    define('NOTIFICATION_CERTIFICATE_REQUESTED', 'certificate_requested');
}
if (!defined('NOTIFICATION_CERTIFICATE_APPROVED')) {
    define('NOTIFICATION_CERTIFICATE_APPROVED', 'certificate_approved');
}
if (!defined('NOTIFICATION_CERTIFICATE_REJECTED')) {
    define('NOTIFICATION_CERTIFICATE_REJECTED', 'certificate_rejected');
}
if (!defined('NOTIFICATION_CERTIFICATE_REVOKED')) {
    define('NOTIFICATION_CERTIFICATE_REVOKED', 'certificate_revoked');
}
if (!defined('NOTIFICATION_TRIAL_EXPIRING')) {
    define('NOTIFICATION_TRIAL_EXPIRING', 'trial_expiring');
}

const DEMO_PASSWORD_STUDENT = 'Student@Masar2026';
const DEMO_PASSWORD_COMPANY = 'Company@Masar2026';
const DEMO_PASSWORD_ADMIN = 'Admin@Masar2026';

function demo_datetime(int $daysAgo, string $time = '10:00:00'): string
{
    $ts = strtotime(date('Y-m-d') . ' ' . $time);

    return date('Y-m-d H:i:s', $ts - ($daysAgo * 86400));
}

/**
 * [email, full_name, phone, university, faculty, field, specialization, degree,
 *  graduation_year (null = student will graduate), city, skills[]]
 */
function demo_students(): array
{
    return [
        ['omar.shazly@gmail.com', 'Omar Khaled El-Shazly', '01223456781', 'Cairo University', 'Faculty of Computers and Artificial Intelligence', 'Computer Science', 'Software Engineering', 'Bachelor of Computer Science', 2026, 'New Cairo', ['PHP', 'Laravel', 'MySQL', 'REST API', 'Git', 'JavaScript', 'Teamwork']],
        ['mariam.hassan@gmail.com', 'Mariam Ahmed Hassan', '01112345682', 'Cairo University', 'Faculty of Computers and Artificial Intelligence', 'Computer Science', 'Mobile Development', 'Bachelor of Computer Science', 2025, 'Nasr City', ['Flutter', 'Dart', 'React Native', 'Git', 'UI Design', 'Communication', 'Teamwork', 'Problem Solving']],
        ['youssef.farouk@outlook.com', 'Youssef Mohamed Farouk', '01515678983', 'Ain Shams University', 'Faculty of Computer and Information Sciences', 'Computer Science', 'Web Development', 'Bachelor of Computer Science', 2026, 'Heliopolis', ['JavaScript', 'React', 'TypeScript', 'HTML', 'CSS', 'Node.js', 'Git', 'REST API']],
        ['farida.lotfy@gmail.com', 'Farida Tarek Lotfy', '01234567884', 'Ain Shams University', 'Faculty of Computer and Information Sciences', 'Computer Science', 'Frontend Development', 'Bachelor of Computer Science', 2024, 'Maadi', ['React', 'Next.js', 'Tailwind CSS', 'TypeScript', 'Figma', 'SEO', 'Communication']],
        ['ahmed.nabil@gmail.com', 'Ahmed Nabil Samir', '01115678985', 'Alexandria University', 'Faculty of Computers and Data Science', 'Computer Science', 'Backend Development', 'Bachelor of Computer Science', 2025, 'Alexandria', ['PHP', 'Laravel', 'MySQL', 'Redis', 'Docker', 'REST API', 'Git', 'Problem Solving']],
        ['nour.abdelaziz@gmail.com', 'Nour Mahmoud Abdelaziz', '01521234586', 'Alexandria University', 'Faculty of Computers and Data Science', 'Computer Science', 'Full Stack Development', 'Bachelor of Computer Science', 2026, 'Alexandria', ['JavaScript', 'React', 'Node.js', 'Express.js', 'MongoDB', 'Django', 'REST API', 'Git']],
        ['karim.eldeeb@gmail.com', 'Karim Amr El-Deeb', '01232345687', 'Egypt-Japan University of Science and Technology', 'Faculty of Computer Science and Engineering', 'Computer Science', 'Software Engineering', 'Bachelor of Computer Science', 2026, 'Alexandria', ['Java', 'C++', 'MySQL', 'REST API', 'Git', 'GitHub', 'Python', 'Teamwork']],
        ['salma.eldin@outlook.com', 'Salma Hossam El-Din', '01111345688', 'Mansoura University', 'Faculty of Computers and Information', 'Computer Science', 'Frontend Development', 'Bachelor of Computer Science', 2027, 'Mansoura', ['JavaScript', 'React', 'CSS', 'HTML', 'Figma', 'Communication', 'Time Management']],
        ['mahmoud.gaballah@gmail.com', 'Mahmoud Khaled Gaballah', '01534567889', 'Tanta University', 'Faculty of Computers and Information', 'Computer Science', 'Cyber Security', 'Bachelor of Computer Science', 2025, 'Tanta', ['Linux', 'Python', 'Cybersecurity', 'Penetration Testing', 'Docker', 'Kubernetes', 'Problem Solving', 'Git']],
        ['mohamed.khattab@gmail.com', 'Mohamed Ashraf Khattab', '01011223390', 'Helwan University', 'Faculty of Computers and Artificial Intelligence', 'Computer Science', 'Data Analysis', 'Bachelor of Computer Science', 2025, 'Helwan', ['Python', 'SQL', 'Excel', 'Power BI', 'Data Analysis', 'Data Visualization', 'Statistics']],
        ['sara.fahim@gmail.com', 'Sara Mohamed Fahim', '01122334491', 'Helwan University', 'Faculty of Computers and Artificial Intelligence', 'Computer Science', 'Artificial Intelligence', 'Bachelor of Computer Science', 2026, 'Helwan', ['Python', 'Machine Learning', 'Deep Learning', 'Data Analysis', 'SQL', 'Git', 'Teamwork']],
        ['amr.abdulghany@gmail.com', 'Amr Sherif Abdelghany', '01533445592', 'German University in Cairo', 'Faculty of Information Engineering and Technology', 'Computer Science', 'Machine Learning', 'Bachelor of Computer Science', 2026, 'New Cairo', ['Python', 'Machine Learning', 'Deep Learning', 'Natural Language Processing', 'Data Analysis', 'SQL', 'Git']],
        ['menna.nassar@gmail.com', 'Menna Amr Nassar', '01044556693', 'British University in Egypt', 'Faculty of Informatics and Computer Science', 'Computer Science', 'Cloud Computing', 'Bachelor of Computer Science', 2024, 'El Shorouk', ['AWS', 'Docker', 'Kubernetes', 'Linux', 'CI/CD', 'Python', 'Git', 'Problem Solving']],
        ['mohamed.fathy@outlook.com', 'Mohamed Adel Fathy', '01155667794', 'Cairo University', 'Faculty of Engineering', 'Engineering', 'Mechanical Engineering', 'Bachelor of Engineering', 2025, 'Giza', ['SolidWorks', 'AutoCAD', 'MATLAB', 'Excel', 'Problem Solving', 'Teamwork']],
        ['youssef.shawky@gmail.com', 'Youssef Omar Shawky', '01566778895', 'Ain Shams University', 'Faculty of Engineering', 'Engineering', 'Civil Engineering', 'Bachelor of Engineering', 2026, 'Heliopolis', ['AutoCAD', 'Project Management', 'Excel', 'Communication', 'Problem Solving', 'Teamwork']],
        ['omar.mostafa@gmail.com', 'Omar Hisham Mostafa', '01077889996', 'German University in Cairo', 'Faculty of Information Engineering and Technology', 'Engineering', 'Electrical Engineering', 'Bachelor of Engineering', 2025, 'Zamalek', ['MATLAB', 'AutoCAD', 'C++', 'Python', 'English', 'Problem Solving', 'Teamwork']],
        ['malak.shennawy@gmail.com', 'Malak Ahmed El-Shennawy', '01188990097', 'Pharos University in Alexandria', 'Faculty of Engineering', 'Engineering', 'Architecture', 'Bachelor of Architecture', 2026, 'Alexandria', ['AutoCAD', 'Project Management', 'Creativity', 'Communication', 'Time Management', 'English']],
        ['abdallah.kamel@gmail.com', 'Abdallah Yasser Kamel', '01599001198', 'Alexandria University', 'Faculty of Engineering', 'Engineering', 'Mechanical Engineering', 'Bachelor of Engineering', 2025, 'Alexandria', ['SolidWorks', 'AutoCAD', 'MATLAB', 'Excel', 'Problem Solving', 'Teamwork', 'German']],
        ['salma.maghraby@gmail.com', 'Salma Wael El-Maghraby', '01011223399', 'Cairo University', 'Faculty of Medicine', 'Medicine', 'General Medicine', 'Bachelor of Medicine and Surgery', 2026, 'Nasr City', ['Communication', 'Teamwork', 'Problem Solving', 'English', 'Time Management', 'Research']],
        ['yara.hassan@gmail.com', 'Yara Ibrahim Hassan', '01122233391', 'Ain Shams University', 'Faculty of Medicine', 'Medicine', 'Pediatrics', 'Bachelor of Medicine and Surgery', 2027, 'Maadi', ['Communication', 'Teamwork', 'Problem Solving', 'English', 'Leadership', 'Research']],
        ['hana.ezzat@gmail.com', 'Hana Mahmoud Ezzat', '01533344492', 'Alexandria University', 'Faculty of Pharmacy', 'Pharmacy', 'Clinical Pharmacy', 'Bachelor of Pharmacy', 2026, 'Alexandria', ['Communication', 'Problem Solving', 'English', 'Excel', 'Time Management', 'Teamwork']],
        ['omar.soliman@gmail.com', 'Omar Khaled Soliman', '01044455593', 'Pharos University in Alexandria', 'Faculty of Pharmacy', 'Pharmacy', 'Pharmacology', 'Bachelor of Pharmacy', 2025, 'Alexandria', ['Communication', 'Problem Solving', 'English', 'Data Analysis', 'Research', 'Excel']],
        ['youssef.gohary@gmail.com', 'Youssef Amr El-Gohary', '01155566694', 'Cairo University', 'Faculty of Commerce', 'Business', 'Marketing', 'Bachelor of Commerce', 2026, 'Nasr City', ['Digital Marketing', 'SEO', 'Content Writing', 'Excel', 'Communication', 'Creativity', 'Teamwork']],
        ['alaa.fawzy@gmail.com', 'Alaa Mohamed Fawzy', '01566677795', 'Ain Shams University', 'Faculty of Commerce', 'Business', 'Marketing', 'Bachelor of Commerce', 2025, 'Heliopolis', ['Digital Marketing', 'SEO', 'Google Ads', 'Content Writing', 'Excel', 'Communication', 'Problem Solving']],
        ['menna.ibrahim@gmail.com', 'Menna Hassan Ibrahim', '01077788896', 'German University in Cairo', 'Faculty of Management Technology', 'Business', 'Business Administration', 'Bachelor of Business Administration', 2026, 'New Cairo', ['Excel', 'Power BI', 'Business Analysis', 'Project Management', 'Leadership', 'Communication', 'Teamwork']],
        ['omar.shahin@gmail.com', 'Omar Sherif Shahin', '01188899997', 'The American University in Cairo', 'School of Business', 'Business', 'Human Resources', 'Bachelor of Business Administration', 2025, 'New Cairo', ['Communication', 'Leadership', 'Business Analysis', 'Excel', 'Time Management', 'Teamwork']],
        ['nourhan.abdelsalam@gmail.com', 'Nourhan Samy Abdelsalam', '01599900098', 'Future University in Egypt', 'Faculty of Commerce and Business Administration', 'Business', 'Sales', 'Bachelor of Business Administration', 2026, 'New Cairo', ['Communication', 'Negotiation', 'Leadership', 'Time Management', 'Teamwork', 'Business Analysis']],
        ['aya.feky@gmail.com', 'Aya Mostafa El-Feky', '01011122299', 'Cairo University', 'Faculty of Law and Policy', 'Law', 'Corporate Law', 'Bachelor of Laws', 2026, 'Giza', ['Communication', 'English', 'Problem Solving', 'Leadership', 'Time Management', 'Business Analysis']],
        ['mariam.lotfy@gmail.com', 'Mariam El-Sayed Lotfy', '01122233390', 'Alexandria University', 'Faculty of Law', 'Law', 'Commercial Law', 'Bachelor of Laws', 2025, 'Alexandria', ['Communication', 'English', 'Problem Solving', 'Business Analysis', 'Teamwork', 'Time Management']],
        ['hager.anwar@gmail.com', 'Hager Anwar El-Sayed', '01533344491', 'Cairo University', 'Faculty of Mass Communication', 'Media', 'Digital Media', 'Bachelor of Arts', 2026, 'Nasr City', ['Digital Marketing', 'SEO', 'Content Writing', 'Adobe Photoshop', 'Creativity', 'Communication', 'Time Management']],
        ['ahmed.seif@outlook.com', 'Ahmed Seif El-Din', '01044455592', 'Cairo University', 'Faculty of Mass Communication', 'Media', 'Journalism', 'Bachelor of Arts', 2025, 'Maadi', ['Content Writing', 'Communication', 'English', 'Creativity', 'Time Management', 'Teamwork']],
        ['esraa.badrawi@gmail.com', 'Esraa Hany El-Badrawi', '01155566693', 'Cairo University', 'Faculty of Applied Arts', 'Design', 'UI/UX Design', 'Bachelor of Fine Arts', 2026, 'Nasr City', ['Figma', 'UI Design', 'UX Design', 'Adobe Photoshop', 'Adobe Illustrator', 'Creativity', 'Communication']],
        ['george.saad@gmail.com', 'George Magdy Saad', '01566677794', 'Helwan University', 'Faculty of Fine Arts', 'Design', 'Graphic Design', 'Bachelor of Fine Arts', 2025, 'Zamalek', ['Adobe Photoshop', 'Adobe Illustrator', 'Creativity', 'Communication', 'Time Management', 'English']],
        ['salma.eldin2@gmail.com', 'Salma Nabil El-Din', '01077788895', 'Cairo University', 'Faculty of Applied Arts', 'Design', 'Product Design', 'Bachelor of Fine Arts', 2026, 'Dokki', ['Figma', 'UI Design', 'Adobe Photoshop', 'Adobe Illustrator', 'Creativity', 'Communication', 'Teamwork']],
        ['mariam.tarek@gmail.com', 'Mariam Tarek Nabil', '01188899996', 'Cairo University', 'Faculty of Applied Arts', 'Design', 'UI/UX Design', 'Bachelor of Fine Arts', 2027, '6 October City', ['Figma', 'UI Design', 'UX Design', 'Adobe Photoshop', 'Creativity', 'Communication', 'Problem Solving']],
        ['mohamed.hadidi@outlook.com', 'Mohamed Essam El-Hadidi', '01599900097', 'Ain Shams University', 'Faculty of Commerce', 'Accounting', 'Financial Accounting', 'Bachelor of Commerce', 2025, 'Nasr City', ['Excel', 'Power BI', 'Business Analysis', 'Communication', 'Problem Solving', 'Time Management', 'Teamwork']],
        ['yasmin.talaat@gmail.com', 'Yasmin Ahmed Talaat', '01011122298', 'Cairo University', 'Faculty of Commerce', 'Accounting', 'Auditing', 'Bachelor of Commerce', 2026, 'Maadi', ['Excel', 'Power BI', 'Business Analysis', 'Communication', 'Problem Solving', 'Time Management']],
        ['abdullah.rahman@gmail.com', 'Abdullah Mohamed El-Rahman', '01122233399', 'Mansoura University', 'Faculty of Commerce', 'Accounting', 'Management Accounting', 'Bachelor of Commerce', 2025, 'Mansoura', ['Excel', 'Power BI', 'Business Analysis', 'Communication', 'Leadership', 'Problem Solving']],
        ['rania.fouad@gmail.com', 'Rania Sameh Fouad', '01533344490', 'Alexandria University', 'Faculty of Commerce', 'Accounting', 'Financial Accounting', 'Bachelor of Commerce', 2026, 'Alexandria', ['Excel', 'Power BI', 'Business Analysis', 'Communication', 'Problem Solving', 'Teamwork']],
        ['george.farid@gmail.com', 'George Farid Naguib', '01212345678', 'Cairo University', 'Faculty of Computers and Artificial Intelligence', 'Computer Science', 'DevOps', 'Bachelor of Computer Science', 2026, 'Giza', ['Linux', 'Docker', 'Kubernetes', 'CI/CD', 'AWS', 'Python', 'Git', 'Problem Solving']],
    ];
}

/**
 * [email, legal_name, description, website, phone, city, field, approval_status,
 *  specializations[], work_fields[]]
 */
function demo_companies(): array
{
    return [
        ['careers@niletech.eg', 'NileTech Solutions', 'Software house in Giza building logistics, e-commerce and fintech platforms, with 40+ engineers.', 'https://www.niletech.eg', '01011122231', 'Giza', 'Computer Science', 'approved', ['Software Engineering', 'Backend Development', 'Frontend Development', 'Mobile Development'], ['Computer Science']],
        ['careers@alexdilabs.com', 'Alexandria Digital Labs', 'Applied AI and data analytics lab serving manufacturing clients across the Delta.', 'https://www.alexdilabs.com', '01122233332', 'Alexandria', 'Computer Science', 'approved', ['Artificial Intelligence', 'Data Analysis', 'Machine Learning', 'Software Engineering'], ['Computer Science']],
        ['hr@cairomed.eg', 'Cairo Medical Center', 'Multi-specialty hospital in downtown Cairo with 200 beds and a dedicated training floor.', 'https://www.cairomed.eg', '01533344433', 'Cairo', 'Medicine', 'approved', ['General Medicine', 'Pediatrics', 'Surgery'], ['Medicine']],
        ['info@atlaseng.eg', 'Atlas Engineering', 'Mechanical and civil engineering consultancy with projects across the Nile Delta and the new administrative capital.', 'https://www.atlaseng.eg', '01044455534', 'Giza', 'Engineering', 'approved', ['Mechanical Engineering', 'Civil Engineering', 'Electrical Engineering', 'Architecture'], ['Engineering']],
        ['careers@brightreach.eg', 'BrightReach Marketing', 'Full-service marketing agency in Cairo managing digital campaigns for retail and healthcare brands.', 'https://www.brightreach.eg', '01155566635', 'Cairo', 'Business', 'approved', ['Marketing', 'Digital Marketing'], ['Business', 'Media']],
        ['hr@luxorpharma.com', 'Luxor Pharma', 'Pharmaceutical manufacturer and distributor, accredited by the Egyptian Drug Authority.', 'https://www.luxorpharma.com', '01566677736', 'Alexandria', 'Pharmacy', 'approved', ['Clinical Pharmacy', 'Pharmacology'], ['Pharmacy']],
        ['mail@themis-law.com', 'Themis Law Partners', 'Corporate law firm advising startups and listed companies on contracts and compliance.', 'https://www.themis-law.com', '01077788837', 'Cairo', 'Law', 'approved', ['Corporate Law', 'Commercial Law'], ['Law']],
        ['hr@ledgerpro.eg', 'LedgerPro Accounting', 'Audit and bookkeeping firm serving SMEs in Greater Cairo and the Delta.', 'https://www.ledgerpro.eg', '01188899938', 'Cairo', 'Accounting', 'approved', ['Financial Accounting', 'Auditing', 'Management Accounting'], ['Accounting']],
        ['talent@futureworks.io', 'FutureWorks Software', 'Product engineering studio in New Cairo building web and mobile products for international clients.', 'https://www.futureworks.io', '01599900039', 'New Cairo', 'Computer Science', 'approved', ['Software Engineering', 'Full Stack Development', 'Cloud Computing', 'DevOps'], ['Computer Science']],
        ['hr@greenretail.eg', 'GreenRetail Egypt', 'Specialty retail chain with 15 branches across Cairo and Giza.', 'https://www.greenretail.eg', '01011122230', 'Giza', 'Business', 'approved', ['Business Administration', 'Sales', 'Marketing'], ['Business']],
        ['careers@medpulse-dx.com', 'MedPulse Diagnostics', 'New medical diagnostics laboratory opening in Dokki, currently onboarding its founding team.', 'https://www.medpulse-dx.com', '01122233331', 'Giza', 'Medicine', 'pending', ['General Medicine', 'Surgery'], ['Medicine']],
        ['hr@solaroffshore.eg', 'SolarOffshore Energy', 'Renewable energy developer installing solar and onshore wind projects in the Suez region.', 'https://www.solaroffshore.eg', '01533344432', 'Suez', 'Engineering', 'approved', ['Electrical Engineering', 'Mechanical Engineering'], ['Engineering']],
        ['careers@nilevalley-log.com', 'Nile Valley Logistics', 'Freight and warehousing operator covering the Alexandria economic corridor.', 'https://www.nilevalley-log.com', '01044455533', 'Alexandria', 'Business', 'approved', ['Business Administration', 'Sales'], ['Business']],
        ['jobs@cleofashion.com', 'CleoFashion International', 'Garment and mixed-use real estate group in Cairo with an in-house design studio.', 'https://www.cleofashion.com', '01155566634', 'Cairo', 'Design', 'rejected', ['Graphic Design', 'Product Design'], ['Design']],
        ['hr@hrpartners.eg', 'HR Partners Egypt', 'Human resources outsourcing and recruitment firm headquartered in Maadi.', 'https://www.hrpartners.eg', '01566677735', 'Cairo', 'Business', 'approved', ['Human Resources', 'Business Administration'], ['Business']],
    ];
}

/**
 * [company_email, title, type, mode, is_paid, fee, trial_days, status,
 *  specialization, skills[], capacity, city]
 */
function demo_trainings(): array
{
    return [
        ['careers@niletech.eg', 'Junior PHP Developer Track', 'project_based', 'onsite', false, null, null, 'closed', 'Backend Development', ['PHP', 'Laravel', 'MySQL', 'REST API', 'Git'], 3, 'Giza'],
        ['careers@niletech.eg', 'Full Stack Web Internship', 'hands_on', 'hybrid', true, 2500.00, 7, 'published', 'Full Stack Development', ['JavaScript', 'React', 'Node.js', 'Express.js', 'REST API'], 2, 'Giza'],
        ['careers@niletech.eg', 'Scaling Laravel Applications', 'project_based', 'onsite', false, null, null, 'published', 'Backend Development', ['PHP', 'Laravel', 'Redis', 'Docker', 'MySQL'], 2, 'Giza'],
        ['careers@niletech.eg', 'Frontend Craftsmanship Program', 'hands_on', 'hybrid', false, null, null, 'published', 'Frontend Development', ['JavaScript', 'React', 'TypeScript', 'CSS', 'Figma'], 3, 'Giza'],
        ['careers@niletech.eg', 'Mobile Banking UI Project', 'project_based', 'onsite', false, null, null, 'published', 'Frontend Development', ['JavaScript', 'React', 'Tailwind CSS', 'Figma', 'REST API'], 2, 'Giza'],
        ['careers@niletech.eg', 'QA Automation Essentials', 'hands_on', 'onsite', false, null, null, 'published', 'Software Engineering', ['JavaScript', 'Selenium', 'Docker', 'Python'], 2, 'Giza'],
        ['careers@alexdilabs.com', 'Data Science Immersion', 'project_based', 'hybrid', true, 3000.00, 7, 'closed', 'Data Analysis', ['Python', 'Machine Learning', 'Data Analysis', 'SQL', 'Statistics'], 3, 'Alexandria'],
        ['careers@alexdilabs.com', 'Machine Learning Engineering Internship', 'project_based', 'onsite', false, null, null, 'published', 'Machine Learning', ['Python', 'Deep Learning', 'Natural Language Processing', 'Data Analysis'], 2, 'Alexandria'],
        ['careers@alexdilabs.com', 'Business Analytics with Power BI', 'hands_on', 'remote', false, null, null, 'published', 'Data Analysis', ['Excel', 'Power BI', 'Data Visualization', 'Statistics'], 4, 'Alexandria'],
        ['careers@alexdilabs.com', 'Computer Vision Projects', 'project_based', 'onsite', false, null, null, 'published', 'Artificial Intelligence', ['Python', 'Deep Learning', 'Data Analysis', 'Git'], 2, 'Alexandria'],
        ['careers@alexdilabs.com', 'ML in Production (MLOps)', 'project_based', 'remote', true, 3200.00, 10, 'published', 'Machine Learning', ['Python', 'Docker', 'Kubernetes', 'CI/CD', 'Machine Learning'], 2, 'Alexandria'],
        ['hr@cairomed.eg', 'Clinical Rotation in Internal Medicine', 'shadowing', 'onsite', false, null, null, 'closed', 'General Medicine', ['English', 'Communication', 'Time Management'], 4, 'Cairo'],
        ['hr@cairomed.eg', 'Pediatrics Ward Shadowing', 'shadowing', 'onsite', false, null, null, 'published', 'Pediatrics', ['English', 'Communication', 'Teamwork'], 3, 'Cairo'],
        ['hr@cairomed.eg', 'Surgical Theater Observership', 'shadowing', 'onsite', false, null, null, 'published', 'Surgery', ['English', 'Communication', 'Time Management'], 2, 'Cairo'],
        ['info@atlaseng.eg', 'Intro to Structural Drafting', 'hands_on', 'onsite', false, null, null, 'closed', 'Civil Engineering', ['AutoCAD', 'Project Management', 'Excel'], 4, 'Giza'],
        ['info@atlaseng.eg', 'Mechanical CAE Workshop', 'hands_on', 'onsite', true, 2000.00, 7, 'published', 'Mechanical Engineering', ['SolidWorks', 'AutoCAD', 'MATLAB'], 3, 'Giza'],
        ['info@atlaseng.eg', 'Site Engineering Handbook', 'hands_on', 'onsite', false, null, null, 'published', 'Civil Engineering', ['AutoCAD', 'Communication', 'Problem Solving'], 3, 'Giza'],
        ['info@atlaseng.eg', 'Electrical Systems for Buildings', 'hands_on', 'onsite', false, null, null, 'published', 'Electrical Engineering', ['MATLAB', 'AutoCAD', 'C++'], 2, 'Giza'],
        ['careers@brightreach.eg', 'Growth Marketing Campaigns', 'project_based', 'hybrid', false, null, null, 'closed', 'Marketing', ['Digital Marketing', 'SEO', 'Content Writing', 'Google Ads'], 3, 'Cairo'],
        ['careers@brightreach.eg', 'Content Studio Intensive', 'hands_on', 'onsite', false, null, null, 'published', 'Digital Marketing', ['Content Writing', 'Adobe Photoshop', 'Creativity'], 3, 'Cairo'],
        ['careers@brightreach.eg', 'Digital Campaign Analytics', 'project_based', 'remote', false, null, null, 'published', 'Digital Marketing', ['SEO', 'Excel', 'Data Analysis', 'Google Ads'], 2, 'Cairo'],
        ['hr@luxorpharma.com', 'Clinical Pharmacy Rotation', 'shadowing', 'onsite', false, null, null, 'published', 'Clinical Pharmacy', ['English', 'Communication', 'Time Management'], 3, 'Alexandria'],
        ['hr@luxorpharma.com', 'Pharmacovigilance Fundamentals', 'hands_on', 'onsite', true, 1800.00, 7, 'published', 'Pharmacology', ['Data Analysis', 'Excel', 'English'], 2, 'Alexandria'],
        ['mail@themis-law.com', 'Corporate Contract Review', 'project_based', 'onsite', false, null, null, 'published', 'Corporate Law', ['English', 'Business Analysis', 'Communication'], 2, 'Cairo'],
        ['mail@themis-law.com', 'Commercial Law Clinic', 'hands_on', 'hybrid', false, null, null, 'published', 'Commercial Law', ['English', 'Communication', 'Problem Solving'], 3, 'Cairo'],
        ['hr@ledgerpro.eg', 'Junior Auditor Track', 'project_based', 'onsite', false, null, null, 'closed', 'Auditing', ['Excel', 'Power BI', 'Business Analysis', 'Communication'], 3, 'Cairo'],
        ['hr@ledgerpro.eg', 'IFRS for SMEs', 'hands_on', 'onsite', true, 2200.00, 7, 'published', 'Financial Accounting', ['Excel', 'Power BI', 'Business Analysis'], 2, 'Cairo'],
        ['hr@ledgerpro.eg', 'Bookkeeping Essentials', 'hands_on', 'onsite', false, null, null, 'published', 'Financial Accounting', ['Excel', 'Communication', 'Time Management'], 3, 'Cairo'],
        ['talent@futureworks.io', 'React Native Product Sprint', 'project_based', 'hybrid', false, null, null, 'published', 'Full Stack Development', ['JavaScript', 'React', 'React Native', 'REST API', 'Git'], 3, 'New Cairo'],
        ['talent@futureworks.io', 'Cloud Native Bootcamp', 'hands_on', 'remote', true, 3500.00, 10, 'published', 'Cloud Computing', ['AWS', 'Docker', 'Kubernetes', 'CI/CD', 'Linux'], 2, 'New Cairo'],
        ['talent@futureworks.io', 'TypeScript Full Stack Fellowship', 'project_based', 'hybrid', false, null, null, 'published', 'Full Stack Development', ['TypeScript', 'React', 'Node.js', 'MySQL', 'Git'], 2, 'New Cairo'],
        ['hr@greenretail.eg', 'Retail Operations Management', 'project_based', 'onsite', false, null, null, 'published', 'Business Administration', ['Excel', 'Leadership', 'Communication'], 3, 'Giza'],
        ['hr@greenretail.eg', 'Category Analytics Program', 'hands_on', 'onsite', false, null, null, 'published', 'Business Administration', ['Excel', 'Power BI', 'Data Analysis'], 2, 'Giza'],
        ['careers@medpulse-dx.com', 'Founding Team: Lab Operations Draft', 'hands_on', 'onsite', false, null, null, 'draft', 'General Medicine', ['English', 'Communication'], 2, 'Giza'],
        ['careers@nilevalley-log.com', 'Freight Operations Trainee', 'hands_on', 'onsite', false, null, null, 'published', 'Business Administration', ['Excel', 'Communication', 'Teamwork'], 3, 'Alexandria'],
        ['hr@solaroffshore.eg', 'Onshore Wind Site Inspection', 'hands_on', 'onsite', false, null, null, 'published', 'Mechanical Engineering', ['SolidWorks', 'MATLAB', 'Excel'], 2, 'Suez'],
        ['hr@hrpartners.eg', 'Recruitment Sourcer Program', 'project_based', 'hybrid', false, null, null, 'published', 'Human Resources', ['Communication', 'Excel', 'Time Management'], 3, 'Cairo'],
        ['hr@hrpartners.eg', 'Employee Onboarding Design', 'project_based', 'onsite', false, null, null, 'published', 'Human Resources', ['Communication', 'Leadership', 'Business Analysis'], 2, 'Cairo'],
        ['jobs@cleofashion.com', 'In-House Design Review', 'hands_on', 'onsite', false, null, null, 'draft', 'Graphic Design', ['Adobe Photoshop', 'Adobe Illustrator'], 2, 'Cairo'],
    ];
}

function demo_dataset_emails(): array
{
    $emails = ['admin@masar.eg', 'compliance@masar.eg'];
    foreach (demo_students() as $s) {
        $emails[] = $s[0];
    }
    foreach (demo_companies() as $c) {
        $emails[] = $c[0];
    }
    return $emails;
}

function demo_student_bio(array $s): string
{
    return sprintf(
        "%s is a %s student at %s specializing in %s, based in %s. %s",
        $s[1],
        strtolower($s[5]),
        $s[3],
        $s[6],
        $s[9],
        "Seeking a hands-on training opportunity in {$s[6]} to complement their academic background."
    );
}

function demo_cleanup(PDO $pdo): void
{
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    $emails = demo_dataset_emails();
    $placeholders = implode(',', array_fill(0, count($emails), '?'));
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email IN ({$placeholders})");
    $stmt->execute($emails);
    $userIds = array_map('intval', array_column($stmt->fetchAll(), 'id'));

    $cleanUser = function (string $table, string $col) use ($pdo, $userIds): void {
        if (!$userIds) {
            return;
        }
        $pdo->exec("DELETE FROM {$table} WHERE {$col} IN (" . implode(',', $userIds) . ")");
    };

    $cleanUser('refresh_tokens', 'user_id');
    $cleanUser('auth_tokens', 'user_id');
    $cleanUser('verification_tokens', 'user_id');
    $cleanUser('password_resets', 'user_id');
    $cleanUser('notifications', 'user_id');
    $cleanUser('audit_logs', 'user_id');

    $studentIds = $userIds ? array_map('intval', array_column($pdo->query("SELECT id FROM students WHERE user_id IN (" . implode(',', $userIds) . ")")->fetchAll(), 'id')) : [];
    $companyIds = $userIds ? array_map('intval', array_column($pdo->query("SELECT id FROM companies WHERE user_id IN (" . implode(',', $userIds) . ")")->fetchAll(), 'id')) : [];

    if ($companyIds) {
        $cid = implode(',', $companyIds);
        $pdo->exec("DELETE FROM company_specializations WHERE company_id IN ({$cid})");
        $pdo->exec("DELETE FROM company_work_fields WHERE company_id IN ({$cid})");
    }

    $trainingIds = $companyIds ? array_map('intval', array_column($pdo->query("SELECT id FROM training_listings WHERE company_id IN (" . implode(',', $companyIds) . ")")->fetchAll(), 'id')) : [];

    if ($trainingIds) {
        $tid = implode(',', $trainingIds);
        $pdo->exec("DELETE FROM training_skills WHERE training_id IN ({$tid})");
        $pdo->exec("DELETE FROM training_specializations WHERE training_id IN ({$tid})");
        $pdo->exec("DELETE FROM training_questions WHERE training_id IN ({$tid})");
        $pdo->exec("DELETE FROM notifications WHERE entity_type = 'training' AND entity_id IN ({$tid})");
    }

    $appIds = [];
    if ($trainingIds) {
        $appIds = array_merge($appIds, array_map('intval', array_column($pdo->query("SELECT id FROM training_applications WHERE training_id IN (" . implode(',', $trainingIds) . ")")->fetchAll(), 'id')));
    }
    if ($studentIds) {
        $sid = implode(',', $studentIds);
        $pdo->exec("DELETE FROM saved_trainings WHERE student_id IN ({$sid})");
        $pdo->exec("DELETE FROM student_skills WHERE student_id IN ({$sid})");
        $appIds = array_merge($appIds, array_map('intval', array_column($pdo->query("SELECT id FROM training_applications WHERE student_id IN ({$sid})")->fetchAll(), 'id')));
    }

    $appIds = array_values(array_unique($appIds));
    if ($appIds) {
        $aid = implode(',', $appIds);
        $pdo->exec("DELETE FROM messages WHERE conversation_id IN (SELECT id FROM conversations WHERE application_id IN ({$aid}))");
        $pdo->exec("DELETE FROM conversations WHERE application_id IN ({$aid})");
        $pdo->exec("DELETE FROM application_answers WHERE application_id IN ({$aid})");
        $pdo->exec("DELETE FROM notifications WHERE entity_type = 'application' AND entity_id IN ({$aid})");
    }

    $sessionIds = $appIds ? array_map('intval', array_column($pdo->query("SELECT id FROM training_sessions WHERE application_id IN (" . implode(',', $appIds) . ")")->fetchAll(), 'id')) : [];
    if ($sessionIds) {
        $sess = implode(',', $sessionIds);
        $certIds = array_map('intval', array_column($pdo->query("SELECT id FROM certificates WHERE training_session_id IN ({$sess})")->fetchAll(), 'id'));
        if ($certIds) {
            $pdo->exec("DELETE FROM certificate_appeals WHERE certificate_id IN (" . implode(',', $certIds) . ")");
            $pdo->exec("DELETE FROM notifications WHERE entity_type = 'certificate' AND entity_id IN (" . implode(',', $certIds) . ")");
            $pdo->exec("DELETE FROM certificates WHERE id IN (" . implode(',', $certIds) . ")");
        }
        $pdo->exec("DELETE FROM payments WHERE training_session_id IN ({$sess})");
    }

    if ($appIds) {
        $pdo->exec("DELETE FROM training_applications WHERE id IN (" . implode(',', $appIds) . ")");
    }
    if ($trainingIds) {
        $pdo->exec("DELETE FROM training_listings WHERE id IN (" . implode(',', $trainingIds) . ")");
    }
    if ($companyIds) {
        $pdo->exec("DELETE FROM companies WHERE id IN (" . implode(',', $companyIds) . ")");
    }
    if ($studentIds) {
        $pdo->exec("DELETE FROM students WHERE id IN (" . implode(',', $studentIds) . ")");
    }
    $cleanUser('users', 'id');

    $pdo->exec("DELETE FROM students WHERE user_id NOT IN (SELECT id FROM users)");
    $pdo->exec("DELETE FROM student_skills WHERE student_id NOT IN (SELECT id FROM students)");
    $pdo->exec("DELETE FROM training_skills WHERE training_id NOT IN (SELECT id FROM training_listings)");
    $pdo->exec("DELETE FROM training_specializations WHERE training_id NOT IN (SELECT id FROM training_listings)");
    $pdo->exec("DELETE FROM training_sessions WHERE application_id NOT IN (SELECT id FROM training_applications)");
    $pdo->exec("DELETE FROM certificates WHERE training_session_id NOT IN (SELECT id FROM training_sessions)");
    $pdo->exec("DELETE FROM conversations WHERE application_id NOT IN (SELECT id FROM training_applications)");
    $pdo->exec("DELETE FROM messages WHERE conversation_id NOT IN (SELECT id FROM conversations)");
    $pdo->exec("DELETE FROM refresh_tokens WHERE user_id NOT IN (SELECT id FROM users)");
    $pdo->exec("DELETE FROM auth_tokens WHERE user_id NOT IN (SELECT id FROM users)");
    $pdo->exec("DELETE FROM verification_tokens WHERE user_id NOT IN (SELECT id FROM users)");
    $pdo->exec("DELETE FROM password_resets WHERE user_id NOT IN (SELECT id FROM users)");
    $pdo->exec("DELETE FROM audit_logs WHERE user_id NOT IN (SELECT id FROM users)");
    $pdo->exec("DELETE FROM audit_logs WHERE user_id IS NOT NULL AND user_id NOT IN (SELECT id FROM users)");
    $pdo->exec("DELETE FROM oauth_states WHERE used_at IS NOT NULL OR expires_at < NOW()");

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
}

function demo_ensure_lookups(PDO $pdo): array
{
    require_once __DIR__ . '/skills_seeder.php';
    require_once __DIR__ . '/universities_seeder.php';
    require_once __DIR__ . '/degrees_seeder.php';
    require_once __DIR__ . '/faculties_seeder.php';
    require_once __DIR__ . '/specializations_seeder.php';

    seed_skills($pdo);
    seed_universities($pdo);
    seed_degrees($pdo);
    seed_faculties($pdo);
    seed_specializations($pdo);

    $byName = function (string $table, string $col) use ($pdo): array {
        $map = [];
        foreach ($pdo->query("SELECT id, {$col} AS name FROM {$table} WHERE is_active = 1") as $row) {
            $map[$row['name']] = (int) $row['id'];
        }
        return $map;
    };

    $unis = $byName('universities', 'name');
    $fields = $byName('study_fields', 'name');
    $degrees = $byName('degrees', 'name');

    $faculties = [];
    foreach ($pdo->query("SELECT f.id, f.name, u.name AS univ FROM faculties f JOIN universities u ON u.id = f.university_id WHERE f.is_active = 1") as $row) {
        $faculties[$row['univ'] . '|' . $row['name']] = (int) $row['id'];
    }

    $ensureUniversity = function (string $name, string $city) use ($pdo, &$unis): int {
        if (isset($unis[$name])) {
            return $unis[$name];
        }
        $pdo->prepare("INSERT INTO universities (name, city, is_active) VALUES (?, ?, 1)")->execute([$name, $city]);
        $id = (int) $pdo->lastInsertId();
        $unis[$name] = $id;
        return $id;
    };

    $ensureFaculty = function (int $universityId, string $univName, string $name) use ($pdo, &$faculties): int {
        $key = $univName . '|' . $name;
        if (isset($faculties[$key])) {
            return $faculties[$key];
        }
        $pdo->prepare("INSERT INTO faculties (university_id, name, is_active) VALUES (?, ?, 1)")->execute([$universityId, $name]);
        $id = (int) $pdo->lastInsertId();
        $faculties[$key] = $id;
        return $id;
    };

    $ejus = $ensureUniversity('Egypt-Japan University of Science and Technology', 'Alexandria');
    $pharos = $ensureUniversity('Pharos University in Alexandria', 'Alexandria');
    $ensureFaculty($ejus, 'Egypt-Japan University of Science and Technology', 'Faculty of Computer Science and Engineering');
    $ensureFaculty($ejus, 'Egypt-Japan University of Science and Technology', 'Faculty of Engineering');
    $ensureFaculty($pharos, 'Pharos University in Alexandria', 'Faculty of Engineering');
    $ensureFaculty($pharos, 'Pharos University in Alexandria', 'Faculty of Pharmacy');

    $as = $unis['Ain Shams University'];
    $au = $unis['Alexandria University'];
    $cu = $unis['Cairo University'];
    $hu = $unis['Helwan University'];
    $ensureFaculty($as, 'Ain Shams University', 'Faculty of Medicine');
    $ensureFaculty($au, 'Alexandria University', 'Faculty of Pharmacy');
    $ensureFaculty($au, 'Alexandria University', 'Faculty of Law');
    $ensureFaculty($cu, 'Cairo University', 'Faculty of Law and Policy');
    $ensureFaculty($cu, 'Cairo University', 'Faculty of Mass Communication');
    $ensureFaculty($cu, 'Cairo University', 'Faculty of Applied Arts');
    $ensureFaculty($hu, 'Helwan University', 'Faculty of Fine Arts');

    $specNames = $byName('specializations', 'name');
    $csId = $fields['Computer Science'];

    $ensureSpec = function (string $name, int $fieldId) use ($pdo, &$specNames): int {
        if (isset($specNames[$name])) {
            return $specNames[$name];
        }
        $pdo->prepare("INSERT INTO specializations (name, field_id, is_active) VALUES (?, ?, 1)")->execute([$name, $fieldId]);
        $id = (int) $pdo->lastInsertId();
        $specNames[$name] = $id;
        return $id;
    };

    foreach ([
        'Backend Development' => $csId,
        'Frontend Development' => $csId,
        'Full Stack Development' => $csId,
        'Mobile Development' => $csId,
        'Cloud Computing' => $csId,
        'DevOps' => $csId,
        'Data Analysis' => $csId,
        'Machine Learning' => $csId,
    ] as $name => $fieldId) {
        $specNames[$name] = $ensureSpec($name, $fieldId);
    }

    $skillNames = $byName('skills', 'name');
    $ensureSkill = function (string $name) use ($pdo, &$skillNames): int {
        if (isset($skillNames[$name])) {
            return $skillNames[$name];
        }
        $pdo->prepare("INSERT INTO skills (name, is_active) VALUES (?, 1)")->execute([$name]);
        $id = (int) $pdo->lastInsertId();
        $skillNames[$name] = $id;
        return $id;
    };

    foreach (['SQL', 'Excel', 'Power BI', 'Flutter', 'Dart', 'Next.js', 'Django', 'React Native', 'Kubernetes', 'Selenium', 'Swift', 'Kotlin', 'AutoCAD', 'SolidWorks', 'MATLAB', 'Statistics', 'Creativity', 'Negotiation', 'Research', 'Google Ads', 'Sales'] as $name) {
        $skillNames[$name] = $ensureSkill($name);
    }

    return [
        'fields' => $fields,
        'specs' => $specNames,
        'skills' => $skillNames,
        'unis' => $unis,
        'degrees' => $degrees,
        'faculties' => $faculties,
    ];
}

function demo_insert(PDO $pdo, string $table, array $row): int
{
    $cols = array_keys($row);
    $stmt = $pdo->prepare(
        "INSERT INTO {$table} (" . implode(', ', $cols) . ") VALUES (" . implode(', ', array_fill(0, count($cols), '?')) . ")"
    );
    $stmt->execute(array_values($row));
    return (int) $pdo->lastInsertId();
}

function demo_make_user(PDO $pdo, string $role, string $email, string $password): int
{
    return demo_insert($pdo, 'users', [
        'role' => $role,
        'email' => $email,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'status' => 'active',
        'email_verified_at' => demo_datetime(30),
        'last_login_at' => demo_datetime(1),
        'created_at' => demo_datetime(45),
        'updated_at' => demo_datetime(45),
    ]);
}

function demo_seed(PDO $pdo): void
{
    echo "Cleaning previous demo + orphaned data...\n";
    demo_cleanup($pdo);

    echo "Ensuring lookup rows (universities, faculties, specs, skills)...\n";
    $lk = demo_ensure_lookups($pdo);
    $lk['skills'] = (function () use ($pdo): array {
        $map = [];
        foreach ($pdo->query("SELECT id, name FROM skills WHERE is_active = 1") as $row) {
            $map[$row['name']] = (int) $row['id'];
        }
        return $map;
    })();
    $lk['specs'] = (function () use ($pdo): array {
        $map = [];
        foreach ($pdo->query("SELECT id, name FROM specializations WHERE is_active = 1") as $row) {
            $map[$row['name']] = (int) $row['id'];
        }
        return $map;
    })();

    $pdo->beginTransaction();

    $admin1 = demo_make_user($pdo, 'admin', 'admin@masar.eg', DEMO_PASSWORD_ADMIN);
    $admin2 = demo_make_user($pdo, 'admin', 'compliance@masar.eg', DEMO_PASSWORD_ADMIN);

    echo "Seeding admins, students and their skills...\n";
    $studentRefs = [];
    foreach (demo_students() as $s) {
        $userId = demo_make_user($pdo, 'student', $s[0], DEMO_PASSWORD_STUDENT);
        $uniId = $lk['unis'][$s[3]] ?? null;
        $facKey = $s[3] . '|' . $s[4];
        $facId = $lk['faculties'][$facKey] ?? null;
        $fieldId = $lk['fields'][$s[5]] ?? null;
        $specId = $lk['specs'][$s[6]] ?? null;
        $degId = $lk['degrees'][$s[7]] ?? null;

        $studentId = demo_insert($pdo, 'students', [
            'user_id' => $userId,
            'full_name' => $s[1],
            'phone' => $s[2],
            'bio' => demo_student_bio($s),
            'university_id' => $uniId,
            'faculty_id' => $facId,
            'field_id' => $fieldId,
            'degree_id' => $degId,
            'specialization_id' => $specId,
            'graduation_year' => $s[8],
            'city' => $s[9],
            'is_profile_complete' => 1,
            'created_at' => demo_datetime(40),
            'updated_at' => demo_datetime(40),
        ]);

        foreach ($s[10] as $i => $skillName) {
            $skillId = $lk['skills'][$skillName] ?? null;
            if (!$skillId) {
                continue;
            }
            $levels = ['beginner', 'intermediate', 'advanced', 'expert'];
            demo_insert($pdo, 'student_skills', [
                'student_id' => $studentId,
                'skill_id' => $skillId,
                'proficiency' => $levels[$i % 4],
                'created_at' => demo_datetime(40),
            ]);
        }

        $studentRefs[] = ['user_id' => $userId, 'student_id' => $studentId, 'data' => $s];
    }

    echo "Seeding companies and their profiles...\n";
    $companyRefs = [];
    foreach (demo_companies() as $c) {
        $userId = demo_make_user($pdo, 'company', $c[0], DEMO_PASSWORD_COMPANY);
        $fieldId = $lk['fields'][$c[6]] ?? null;

        $status = $c[7];
        $approvedAt = $status === 'approved' ? demo_datetime(60) : null;
        $companyId = demo_insert($pdo, 'companies', [
            'user_id' => $userId,
            'legal_name' => $c[1],
            'description' => $c[2],
            'website' => $c[3],
            'phone' => $c[4],
            'city' => $c[5],
            'address' => $c[5],
            'approval_status' => $status,
            'approved_at' => $approvedAt,
            'approved_by' => $status === 'approved' ? $admin1 : null,
            'rejection_reason' => $status === 'rejected' ? 'Incomplete business license documentation (commercial register and tax card missing).' : null,
            'created_at' => demo_datetime(70),
            'updated_at' => demo_datetime(70),
        ]);

        foreach ($c[8] as $specName) {
            $specId = $lk['specs'][$specName] ?? null;
            if ($specId) {
                demo_insert($pdo, 'company_specializations', [
                    'company_id' => $companyId,
                    'specialization_id' => $specId,
                    'created_at' => demo_datetime(70),
                ]);
            }
        }
        foreach ($c[9] as $workField) {
            $wfId = $lk['fields'][$workField] ?? null;
            if ($wfId) {
                demo_insert($pdo, 'company_work_fields', [
                    'company_id' => $companyId,
                    'field_id' => $wfId,
                    'created_at' => demo_datetime(70),
                ]);
            }
        }

        $companyRefs[$c[0]] = ['user_id' => $userId, 'company_id' => $companyId, 'data' => $c];
    }

    echo "Seeding trainings, questions and skill links...\n";
    $questions = [
        ['Tell us about your most relevant project or experience.', 'textarea', 1, null],
        ['What do you hope to learn during this training?', 'textarea', 1, null],
        ['How many hours per week can you commit to this training?', 'select', 1, '10-15 hours,16-25 hours,26-35 hours'],
        ['Do you have any previous experience with the related tools?', 'radio', 1, 'Yes,No'],
        ['Describe one challenge you solved with your specialty.', 'textarea', 0, null],
    ];

    $trainingRefs = [];
    foreach (demo_trainings() as $idx => $t) {
        $company = $companyRefs[$t[0]] ?? null;
        if (!$company) {
            continue;
        }
        $specId = $lk['specs'][$t[8]] ?? null;
        $status = $t[7];

        $createdAt = $status === 'published' ? demo_datetime(50 - $idx) : demo_datetime(5 - $idx);
        $publishedAt = $status === 'published' ? $createdAt : null;
        $closedAt = $status === 'closed' ? demo_datetime(2 + $idx % 5) : null;
        $startsAt = $status === 'closed' ? demo_datetime(30 + $idx % 10, '09:00:00') : demo_datetime(10 - $idx % 8, '09:00:00');
        $endsAt = $status === 'closed' ? demo_datetime(5 + $idx % 5, '17:00:00') : demo_datetime(-(10 + $idx % 20), '17:00:00');
        $deadline = $status === 'closed' ? demo_datetime(35 + $idx % 10, '23:59:59') : demo_datetime(20 - $idx % 10, '23:59:59');

        $trainingId = demo_insert($pdo, 'training_listings', [
            'company_id' => $company['company_id'],
            'specialization_id' => $specId,
            'title' => $t[1],
            'description' => sprintf(
                "A %s training offered by %s in %s for %s students. The program covers the most requested skills in %s and includes mentoring, hands-on deliverables and a final evaluation with a certificate for those who complete the session.",
                $t[2],
                $company['data'][1],
                $t[11],
                $t[10],
                $t[8]
            ),
            'training_type' => $t[2],
            'mode' => $t[3],
            'may_lead_to_employment' => ($idx % 3 === 0) ? 1 : 0,
            'is_paid' => $t[4] ? 1 : 0,
            'compensation_amount' => $t[4] ? $t[5] : null,
            'compensation_currency' => 'EGP',
            'trial_period_days' => $t[4] ? $t[6] : null,
            'capacity' => $t[10],
            'status' => $status,
            'published_at' => $publishedAt,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'application_deadline' => $deadline,
            'closed_at' => $closedAt,
            'location' => $t[11],
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        foreach ($t[9] as $skillName) {
            $skillId = $lk['skills'][$skillName] ?? null;
            if ($skillId) {
                demo_insert($pdo, 'training_skills', ['training_id' => $trainingId, 'skill_id' => $skillId]);
            }
        }
        if ($specId) {
            demo_insert($pdo, 'training_specializations', ['training_id' => $trainingId, 'specialization_id' => $specId]);
        }

        foreach ($questions as $qs) {
            demo_insert($pdo, 'training_questions', [
                'training_id' => $trainingId,
                'question' => $qs[0],
                'question_type' => $qs[1],
                'required' => $qs[2],
                'options' => $qs[3],
                'sort_order' => (int) array_search($qs, $questions, true) + 1,
                'created_at' => $createdAt,
            ]);
        }

        $trainingRefs[] = ['training_id' => $trainingId, 'data' => $t, 'company' => $company];
    }

    echo "Seeding applications, answers, sessions and certificates...\n";
    $applicationRefs = [];
    $sessionRefs = [];
    $certificateRefs = [];
    $counter = 0;

    $rotatingStudents = $studentRefs;
    $baseIndex = 0;

    foreach ($trainingRefs as $trk => $tr) {
        $status = $tr['data'][7];
        $fee = $tr['data'][4] ? $tr['data'][5] : null;

        if ($status === 'draft') {
            continue;
        }

        $maxApps = $status === 'closed' ? 4 : 5;
        $usedStudents = [];
        $i = 0;
        while ($i < $maxApps) {
            $candidate = $rotatingStudents[($baseIndex + $counter) % count($rotatingStudents)];
            $baseIndex++;
            $counter++;
            if (in_array($candidate['student_id'], $usedStudents, true)) {
                continue;
            }
            $usedStudents[] = $candidate['student_id'];

            $statuses = ['submitted', 'submitted', 'accepted', 'rejected', 'withdrawn'];
            $appStatus = $statuses[$i];
            if ($status === 'closed' && in_array($appStatus, ['submitted'], true)) {
                $appStatus = 'rejected';
            }

            $appliedAt = $status === 'closed'
                ? demo_datetime(45 + $trk)
                : demo_datetime(20 + ($trk + $i) % 15, '14:00:00');
            $reviewedAt = in_array($appStatus, ['accepted', 'rejected'], true)
                ? demo_datetime(30 + $i, '11:30:00')
                : null;

            $rejectionReasons = ['candidate_not_suitable', 'position_filled', 'requirements_not_met', 'other'];
            $app = $candidate['data'];

            $applicationId = demo_insert($pdo, 'training_applications', [
                'training_id' => $tr['training_id'],
                'student_id' => $candidate['student_id'],
                'company_id' => $tr['company']['company_id'],
                'message' => sprintf('I am %s and I would like to join this training.', $app[1]),
                'full_name' => $app[1],
                'email' => $app[0],
                'phone' => $app[2],
                'city' => $app[9],
                'address' => $app[9],
                'why_interested' => sprintf('I want to deepen my practical skills in %s.', $tr['data'][8]),
                'what_to_learn' => sprintf('Hands-on knowledge of %s and the workflows used by %s.', $tr['data'][8], $tr['company']['data'][1]),
                'skills' => implode(', ', array_slice($app[10], 0, 6)),
                'status' => $appStatus,
                'rejection_reason' => $appStatus === 'rejected' ? $rejectionReasons[$i % 4] : null,
                'rejection_note' => $appStatus === 'rejected' ? 'The committee selected candidates whose skills aligned more closely with the program requirements.' : null,
                'applied_at' => $appliedAt,
                'reviewed_at' => $reviewedAt,
                'withdrawn_at' => $appStatus === 'withdrawn' ? demo_datetime(12 + $i, '09:00:00') : null,
                'reviewed_by' => in_array($appStatus, ['accepted', 'rejected'], true) ? $tr['company']['user_id'] : null,
                'faculty_id' => $lk['faculties'][$app[3] . '|' . $app[4]] ?? null,
                'university' => $app[3],
                'applicant_type' => ($app[8] !== null && $app[8] <= 2025) ? 'graduated' : 'student',
                'academic_year' => ($app[8] !== null && $app[8] <= 2025) ? 'Graduated ' . $app[8] : ('Class of ' . ($app[8] ?? '2027')),
                'graduation_year' => $app[8],
                'motivation' => sprintf('Looking to convert academic knowledge in %s into professional competence.', $app[6]),
            ]);

            $qIds = $pdo->query("SELECT id FROM training_questions WHERE training_id = " . $tr['training_id'] . " ORDER BY sort_order")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($qIds as $qk => $qId) {
                demo_insert($pdo, 'application_answers', [
                    'application_id' => $applicationId,
                    'question_id' => (int) $qId,
                    'answer' => in_array($qk, [0, 1, 4], true)
                        ? sprintf('During my studies at %s I led a small project related to %s, where coordination and disciplined delivery mattered most.', $app[3], $app[6])
                        : ['10-15 hours', 'Yes', 'No', '26-35 hours'][$qk % 4] ?? 'Yes',
                    'created_at' => $appliedAt,
                ]);
            }

            $applicationRefs[] = [
                'application_id' => $applicationId,
                'status' => $appStatus,
                'student' => $candidate,
                'training' => $tr,
                'fee' => $fee,
                'applied_at' => $appliedAt,
                'reviewed_at' => $reviewedAt,
            ];

            if ($appStatus === 'accepted') {
                $isContinuing = $status === 'published';
                $endedAt = $isContinuing ? null : demo_datetime(8 + $i, '16:00:00');
                $sessionStatus = $isContinuing ? 'continuing' : 'completed';
                $trialStart = $status === 'closed' ? demo_datetime(25, '09:00:00') : demo_datetime(5, '09:00:00');
                $trialEnd = demo_datetime(10, '18:00:00');

                $sessionId = demo_insert($pdo, 'training_sessions', [
                    'application_id' => $applicationId,
                    'training_id' => $tr['training_id'],
                    'student_id' => $candidate['student_id'],
                    'company_id' => $tr['company']['company_id'],
                    'status' => $sessionStatus,
                    'started_at' => $isContinuing ? demo_datetime(6, '09:00:00') : $trialStart,
                    'trial_started_at' => $trialStart,
                    'trial_ends_at' => $trialEnd,
                    'student_continuation_confirmed_at' => $isContinuing ? demo_datetime(3, '12:00:00') : demo_datetime(22, '12:00:00'),
                    'actual_ended_at' => $endedAt,
                    'employment_opportunity' => ($idx % 3 === 0) ? 1 : 0,
                    'created_at' => $trialStart,
                    'updated_at' => $trialStart,
                ]);

                $sessionRefs[] = [
                    'session_id' => $sessionId,
                    'training' => $tr,
                    'student' => $candidate,
                    'status' => $sessionStatus,
                    'fee' => $fee,
                ];

                if ($sessionStatus === 'completed') {
                    $grade = [48.0, 62.0, 71.5, 85.0][$i % 4] ?? 78.5;
                    $label = [48.0, 62.0, 71.5, 85.0][$i % 4] >= 70 ? 'Very Good' : 'Good';
                    $certId = demo_insert($pdo, 'certificates', [
                        'certificate_code' => 'MASAR-' . date('Y') . '-DEMO' . str_pad((string) (count($certificateRefs) + 1), 4, '0', STR_PAD_LEFT),
                        'student_id' => $candidate['student_id'],
                        'company_id' => $tr['company']['company_id'],
                        'training_id' => $tr['training_id'],
                        'training_session_id' => $sessionId,
                        'status' => 'valid',
                        'title' => 'Certificate of Completion - ' . $tr['data'][1],
                        'start_date' => date('Y-m-d', strtotime($trialStart)),
                        'end_date' => date('Y-m-d', strtotime($endedAt)),
                        'grade' => $grade,
                        'grade_label' => $label,
                        'employment_eligible' => ($idx % 2 === 0) ? 1 : 0,
                        'requested_at' => demo_datetime(15, '13:00:00'),
                        'reviewed_at' => demo_datetime(12, '10:00:00'),
                        'approved_at' => demo_datetime(10, '10:00:00'),
                        'reviewed_by' => $admin1,
                        'created_at' => demo_datetime(15),
                        'updated_at' => demo_datetime(10),
                    ]);
                    $certificateRefs[] = ['certificate_id' => $certId, 'student' => $candidate, 'training' => $tr, 'session_id' => $sessionId];
                }
            }

            $i++;
        }
    }

    echo "Seeding conversations and messages...\n";
    $conversationRefs = [];
    foreach ($applicationRefs as $app) {
        if ($app['status'] !== 'accepted') {
            continue;
        }
        $convId = demo_insert($pdo, 'conversations', [
            'student_id' => $app['student']['student_id'],
            'company_id' => $app['training']['company']['company_id'],
            'application_id' => $app['application_id'],
            'created_at' => $app['reviewed_at'] ?: demo_datetime(20),
            'updated_at' => demo_datetime(20),
        ]);

        $companyUser = $app['training']['company']['user_id'];
        $studentUser = $app['student']['user_id'];
        $titles = [
            'Welcome aboard! I have reviewed your profile and we are glad to have you.',
            'Thank you for the warm welcome! I am very excited to start.',
            'Please join the onboarding call tomorrow at 10am.',
            'I will be there. Should I prepare anything in advance?',
            'Just bring your laptop and any portfolio pieces you mentioned.',
        ];

        foreach ($titles as $mi => $body) {
            $sender = ($mi % 2 === 0) ? $companyUser : $studentUser;
            $readAt = $mi < count($titles) - 1 ? demo_datetime(19, '12:00:00') : null;
            demo_insert($pdo, 'messages', [
                'conversation_id' => $convId,
                'sender_user_id' => $sender,
                'body' => $body,
                'is_read' => $readAt ? 1 : 0,
                'read_at' => $readAt,
                'created_at' => demo_datetime(20 - $mi, '11:00:00'),
            ]);
        }

        $conversationRefs[] = ['conversation_id' => $convId, 'application' => $app];
    }

    echo "Seeding notifications...\n";
    $notify = function (int $userId, string $type, string $title, string $body, ?string $entityType, ?int $entityId) use ($pdo): void {
        demo_insert($pdo, 'notifications', [
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'is_read' => 0,
            'read_at' => null,
            'created_at' => demo_datetime(20),
        ]);
    };

    foreach ($applicationRefs as $app) {
        if ($app['status'] === 'accepted') {
            $notify(
                $app['student']['user_id'],
                NOTIFICATION_APPLICATION_ACCEPTED,
                'Application Accepted',
                sprintf('Congratulations! %s accepted your application.', $app['training']['company']['data'][1]),
                'application',
                $app['application_id']
            );
        } elseif ($app['status'] === 'rejected') {
            $notify(
                $app['student']['user_id'],
                NOTIFICATION_APPLICATION_REJECTED,
                'Application Rejected',
                sprintf('We are sorry, your application to %s was not selected this time.', $app['training']['data'][1]),
                'application',
                $app['application_id']
            );
        } elseif ($app['status'] === 'withdrawn') {
            $notify(
                $app['student']['user_id'],
                NOTIFICATION_APPLICATION_WITHDRAWN,
                'Application Withdrawn',
                sprintf('You withdrew your application to %s.', $app['training']['data'][1]),
                'application',
                $app['application_id']
            );
        }
    }

    foreach ($conversationRefs as $conv) {
        $notify(
            $conv['application']['student']['user_id'],
            NOTIFICATION_NEW_MESSAGE,
            'New Message',
            sprintf('%s sent you a message.', $conv['application']['training']['company']['data'][1]),
            'conversation',
            $conv['conversation_id']
        );
    }

    foreach ($certificateRefs as $cert) {
        $notify(
            $cert['student']['user_id'],
            NOTIFICATION_CERTIFICATE_APPROVED,
            'Certificate Issued',
            sprintf('Your certificate for "%s" has been issued and approved.', $cert['training']['data'][1]),
            'certificate',
            $cert['certificate_id']
        );
    }

    echo "Seeding saved trainings...\n";
    $savedPairs = [];
    foreach ($studentRefs as $sIdx => $s) {
        $offset = $sIdx * 3 % count($trainingRefs);
        for ($k = 0; $k < 2; $k++) {
            $tr = $trainingRefs[($offset + $k) % count($trainingRefs)];
            if ($tr['data'][7] !== 'published') {
                continue;
            }
            $pairKey = $s['student_id'] . '|' . $tr['training_id'];
            if (isset($savedPairs[$pairKey])) {
                continue;
            }
            demo_insert($pdo, 'saved_trainings', [
                'student_id' => $s['student_id'],
                'training_id' => $tr['training_id'],
                'created_at' => demo_datetime(15 + $k, '16:00:00'),
            ]);
            $savedPairs[$pairKey] = true;
        }
    }

    echo "Seeding payments for paid trainings...\n";
    $paymentIndex = 0;
    foreach ($sessionRefs as $sess) {
        if (!$sess['fee']) {
            continue;
        }
        $rate = 10.00;
        $amount = (float) $sess['fee'];
        $commission = round($amount * $rate / 100, 2);
        $companyAmount = round($amount - $commission, 2);
        $paid = $sess['status'] === 'completed';

        demo_insert($pdo, 'payments', [
            'training_id' => $sess['training']['training_id'],
            'training_session_id' => $sess['session_id'],
            'student_id' => $sess['student']['student_id'],
            'company_id' => $sess['training']['company']['company_id'],
            'amount' => $amount,
            'currency' => 'EGP',
            'platform_commission_rate' => $rate,
            'platform_commission_amount' => $commission,
            'company_amount' => $companyAmount,
            'payment_method' => $paymentIndex % 2 === 0 ? 'manual' : 'paymob',
            'status' => $paid ? 'paid' : 'pending',
            'external_reference' => $paymentIndex % 2 === 0 ? 'MANUAL-' . date('Ymd') . '-' . str_pad((string) $paymentIndex, 5, '0', STR_PAD_LEFT) : 'PAYMOB-' . str_pad((string) $paymentIndex, 8, '0', STR_PAD_LEFT),
            'paid_at' => $paid ? demo_datetime(6, '15:00:00') : null,
            'created_at' => demo_datetime(9, '15:00:00'),
            'updated_at' => demo_datetime(9, '15:00:00'),
        ]);
        $paymentIndex++;
    }

    echo "Seeding audit logs...\n";
    $audit = function (int $userId, string $action, string $entityType, ?int $entityId, ?string $newValues) use ($pdo): void {
        demo_insert($pdo, 'audit_logs', [
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => null,
            'new_values' => $newValues,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'MASAR Demo Seeder',
            'created_at' => demo_datetime(30),
        ]);
    };

    $audit($admin1, 'user.register', 'user', $admin1, json_encode(['role' => 'admin']));
    foreach ($companyRefs as $c) {
        $audit($admin1, 'company.approve', 'company', $c['company_id'], json_encode(['approval_status' => $c['data'][7]]));
    }

    echo "Seeding completed.\n";

    $pdo->commit();
}

/*
|--------------------------------------------------------------------------
| CLI Entry Point
|--------------------------------------------------------------------------
*/

if (PHP_SAPI === 'cli') {
    try {
        $pdo = get_database_connection();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        demo_seed($pdo);

        echo "Demo data seeded successfully." . PHP_EOL;
    } catch (Throwable $exception) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        fwrite(
            STDERR,
            "Seeder failed: " . $exception->getMessage() . PHP_EOL
        );
        exit(1);
    }
}