<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\School;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\ParentStudentLink;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // First, seed roles and permissions
        $this->call(RolesAndPermissionsSeeder::class);

        // ========================================
        // SUPER ADMIN (System Level)
        // ========================================
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@digiskul.app'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'active' => true,
            ]
        );
        if (!$superAdmin->hasRole('super_admin')) {
            $superAdmin->assignRole('super_admin');
        }

        // ========================================
        // SAMPLE SCHOOL
        // ========================================
        $school = School::firstOrCreate(
            ['subdomain' => 'nurlight'],
            [
                'name' => 'Nur Light Academy',
                'email' => 'info@nurlight.edu.ng',
                'phone' => '+234 800 000 0000',
                'address' => '123 Education Drive, Lagos, Nigeria',
                'active' => true,
                'license_valid_until' => now()->addYear(),
                'subscription_plan' => 'standard',
                'meta' => [
                    'academic' => [
                        'current_session' => '2024/2025',
                        'current_term' => 'First Term',
                        'grading_system' => [
                            ['min' => 70, 'max' => 100, 'grade' => 'A', 'remark' => 'Excellent'],
                            ['min' => 60, 'max' => 69, 'grade' => 'B', 'remark' => 'Very Good'],
                            ['min' => 50, 'max' => 59, 'grade' => 'C', 'remark' => 'Good'],
                            ['min' => 40, 'max' => 49, 'grade' => 'D', 'remark' => 'Fair'],
                            ['min' => 0, 'max' => 39, 'grade' => 'E', 'remark' => 'Poor'],
                        ],
                    ],
                    'features' => [
                        'attendance' => ['enabled' => true],
                        'fees' => ['enabled' => true],
                        'ai_comments' => ['enabled' => true],
                        'duty_roster' => ['enabled' => true],
                    ],
                ],
            ]
        );

        // ========================================
        // SCHOOL ADMIN / PRINCIPAL
        // ========================================
        $schoolAdmin = User::firstOrCreate(
            ['email' => 'admin@nurlight.digiskul.app'],
            [
                'school_id' => $school->id,
                'name' => 'Dr. Amina Ibrahim',
                'phone' => '+234 800 111 0001',
                'staff_id' => 'STAFF001',
                'password' => Hash::make('password'),
                'role' => 'school_admin',
                'active' => true,
            ]
        );
        if (!$schoolAdmin->hasRole('school_admin')) {
            $schoolAdmin->assignRole('school_admin');
        }

        // ========================================
        // SAMPLE CLASSES
        // ========================================
        $classes = [
            ['name' => 'JSS 1', 'section' => 'A', 'level' => 'junior'],
            ['name' => 'JSS 1', 'section' => 'B', 'level' => 'junior'],
            ['name' => 'JSS 2', 'section' => 'A', 'level' => 'junior'],
            ['name' => 'JSS 3', 'section' => 'A', 'level' => 'junior'],
            ['name' => 'SSS 1', 'section' => 'A', 'level' => 'senior'],
            ['name' => 'SSS 2', 'section' => 'A', 'level' => 'senior'],
        ];

        $createdClasses = [];
        foreach ($classes as $classData) {
            $createdClasses[] = ClassModel::firstOrCreate(
                [
                    'school_id' => $school->id,
                    'name' => $classData['name'],
                    'section' => $classData['section'],
                ],
                ['level' => $classData['level']]
            );
        }

        // ========================================
        // SAMPLE SUBJECTS
        // ========================================
        $subjects = [
            ['name' => 'Mathematics', 'code' => 'MTH'],
            ['name' => 'English Language', 'code' => 'ENG'],
            ['name' => 'Physics', 'code' => 'PHY'],
            ['name' => 'Chemistry', 'code' => 'CHM'],
            ['name' => 'Biology', 'code' => 'BIO'],
            ['name' => 'Computer Science', 'code' => 'CSC'],
            ['name' => 'History', 'code' => 'HIS'],
            ['name' => 'Geography', 'code' => 'GEO'],
        ];

        $createdSubjects = [];
        foreach ($subjects as $subjectData) {
            $createdSubjects[] = Subject::firstOrCreate(
                ['school_id' => $school->id, 'name' => $subjectData['name']],
                ['code' => $subjectData['code']]
            );
        }

        // ========================================
        // TEACHERS (Regular Teachers)
        // ========================================
        $teacher1 = User::firstOrCreate(
            ['email' => 'teacher@nurlight.digiskul.app'],
            [
                'school_id' => $school->id,
                'name' => 'Mr. John Adeyemi',
                'phone' => '+234 800 111 0002',
                'staff_id' => 'STAFF002',
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'qualification' => 'B.Sc Mathematics',
                'active' => true,
            ]
        );
        if (!$teacher1->hasRole('teacher')) {
            $teacher1->assignRole('teacher');
        }

        $teacher2 = User::firstOrCreate(
            ['email' => 'teacher2@nurlight.digiskul.app'],
            [
                'school_id' => $school->id,
                'name' => 'Mrs. Grace Okonkwo',
                'phone' => '+234 800 111 0003',
                'staff_id' => 'STAFF003',
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'qualification' => 'B.Ed English',
                'active' => true,
            ]
        );
        if (!$teacher2->hasRole('teacher')) {
            $teacher2->assignRole('teacher');
        }

        // ========================================
        // CLASS TEACHER
        // ========================================
        $classTeacher = User::firstOrCreate(
            ['email' => 'classteacher@nurlight.digiskul.app'],
            [
                'school_id' => $school->id,
                'name' => 'Mr. Samuel Bello',
                'phone' => '+234 800 111 0004',
                'staff_id' => 'STAFF004',
                'password' => Hash::make('password'),
                'role' => 'class_teacher',
                'qualification' => 'M.Ed Science Education',
                'active' => true,
            ]
        );
        if (!$classTeacher->hasRole('class_teacher')) {
            $classTeacher->assignRole('class_teacher');
        }

        // ========================================
        // TEACHER ASSIGNMENTS
        // ========================================
        // Assign teacher1 to JSS 1A for Mathematics
        TeacherAssignment::firstOrCreate([
            'school_id' => $school->id,
            'teacher_id' => $teacher1->id,
            'class_id' => $createdClasses[0]->id,
            'subject_id' => $createdSubjects[0]->id, // Mathematics
        ], [
            'is_class_teacher' => false,
            'academic_session' => '2024/2025',
            'term' => 'First Term',
            'active' => true,
        ]);

        // Assign teacher2 to JSS 1A for English
        TeacherAssignment::firstOrCreate([
            'school_id' => $school->id,
            'teacher_id' => $teacher2->id,
            'class_id' => $createdClasses[0]->id,
            'subject_id' => $createdSubjects[1]->id, // English
        ], [
            'is_class_teacher' => false,
            'academic_session' => '2024/2025',
            'term' => 'First Term',
            'active' => true,
        ]);

        // Assign classTeacher to JSS 3A as Class Teacher
        TeacherAssignment::firstOrCreate([
            'school_id' => $school->id,
            'teacher_id' => $classTeacher->id,
            'class_id' => $createdClasses[3]->id, // JSS 3A
            'subject_id' => $createdSubjects[2]->id, // Physics
        ], [
            'is_class_teacher' => true,
            'academic_session' => '2024/2025',
            'term' => 'First Term',
            'active' => true,
        ]);

        // ========================================
        // SAMPLE STUDENTS
        // ========================================
        $students = [
            ['full_name' => 'Mubarak Kasim', 'admission_no' => '2024001', 'class_id' => $createdClasses[3]->id],
            ['full_name' => 'Fatima Hassan', 'admission_no' => '2024002', 'class_id' => $createdClasses[3]->id],
            ['full_name' => 'Ahmed Sule', 'admission_no' => '2024003', 'class_id' => $createdClasses[0]->id],
            ['full_name' => 'Blessing Nwosu', 'admission_no' => '2024004', 'class_id' => $createdClasses[0]->id],
            ['full_name' => 'David Okoro', 'admission_no' => '2024005', 'class_id' => $createdClasses[1]->id],
        ];

        $createdStudents = [];
        foreach ($students as $studentData) {
            $createdStudents[] = Student::firstOrCreate(
                ['admission_no' => $studentData['admission_no']],
                [
                    'school_id' => $school->id,
                    'full_name' => $studentData['full_name'],
                    'class_id' => $studentData['class_id'],
                    'active' => true,
                ]
            );
        }

        // ========================================
        // STUDENT USER (For Student Portal Login)
        // ========================================
        $studentUser = User::firstOrCreate(
            ['email' => 'student@nurlight.digiskul.app'],
            [
                'school_id' => $school->id,
                'name' => 'Mubarak Kasim',
                'phone' => '+234 800 222 0001',
                'password' => Hash::make('password'),
                'role' => 'student',
                'active' => true,
            ]
        );
        if (!$studentUser->hasRole('student')) {
            $studentUser->assignRole('student');
        }
        // Link student user to student profile
        $createdStudents[0]->update(['user_id' => $studentUser->id]);

        // ========================================
        // PARENT USER
        // ========================================
        $parentUser = User::firstOrCreate(
            ['email' => 'parent@nurlight.digiskul.app'],
            [
                'school_id' => $school->id,
                'name' => 'Mr. Kasim Abdullahi',
                'phone' => '+234 800 333 0001',
                'password' => Hash::make('password'),
                'role' => 'parent',
                'active' => true,
            ]
        );
        if (!$parentUser->hasRole('parent')) {
            $parentUser->assignRole('parent');
        }

        // Link parent to student
        ParentStudentLink::firstOrCreate([
            'school_id' => $school->id,
            'parent_id' => $parentUser->id,
            'student_id' => $createdStudents[0]->id,
        ], [
            'relationship' => 'parent',
            'can_view_grades' => true,
            'can_view_attendance' => true,
            'can_view_fees' => true,
            'active' => true,
        ]);

        // ========================================
        // BURSAR / ACCOUNTANT
        // ========================================
        $bursar = User::firstOrCreate(
            ['email' => 'bursar@nurlight.digiskul.app'],
            [
                'school_id' => $school->id,
                'name' => 'Mrs. Ngozi Eze',
                'phone' => '+234 800 111 0005',
                'staff_id' => 'STAFF005',
                'password' => Hash::make('password'),
                'role' => 'bursar',
                'active' => true,
            ]
        );
        if (!$bursar->hasRole('bursar')) {
            $bursar->assignRole('bursar');
        }

        // ========================================
        // ICT OFFICER
        // ========================================
        $ictOfficer = User::firstOrCreate(
            ['email' => 'ict@nurlight.digiskul.app'],
            [
                'school_id' => $school->id,
                'name' => 'Mr. Chidi Ugwu',
                'phone' => '+234 800 111 0006',
                'staff_id' => 'STAFF006',
                'password' => Hash::make('password'),
                'role' => 'ict_officer',
                'active' => true,
            ]
        );
        if (!$ictOfficer->hasRole('ict_officer')) {
            $ictOfficer->assignRole('ict_officer');
        }

        // ========================================
        // OUTPUT SUMMARY
        // ========================================
        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('DIGISKUL DATABASE SEEDED SUCCESSFULLY');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Super Admin', 'admin@digiskul.app', 'password'],
                ['School Admin', 'admin@nurlight.digiskul.app', 'password'],
                ['Teacher', 'teacher@nurlight.digiskul.app', 'password'],
                ['Class Teacher', 'classteacher@nurlight.digiskul.app', 'password'],
                ['Student', 'student@nurlight.digiskul.app', 'password'],
                ['Parent', 'parent@nurlight.digiskul.app', 'password'],
                ['Bursar', 'bursar@nurlight.digiskul.app', 'password'],
                ['ICT Officer', 'ict@nurlight.digiskul.app', 'password'],
            ]
        );
        $this->command->info('');
    }
}
