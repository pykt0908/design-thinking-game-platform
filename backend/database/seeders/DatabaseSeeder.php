<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetPack;
use App\Models\Classroom;
use App\Models\DesignDefine;
use App\Models\DesignEmpathize;
use App\Models\DesignIdeate;
use App\Models\DesignProject;
use App\Models\DesignPrototype;
use App\Models\DesignTest;
use App\Models\Game;
use App\Models\GameAssignment;
use App\Models\GameVersion;
use App\Models\StudentProfile;
use App\Models\TeacherAiCredential;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users
        $admin = User::create([
            'name' => 'ผู้ดูแลระบบ (Admin)',
            'email' => 'admin@example.com',
            'username' => 'admin',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        $teacher = User::create([
            'name' => 'ครูสมศรี ใจดี',
            'email' => 'teacher@example.com',
            'username' => 'teacher',
            'role' => 'teacher',
            'password' => Hash::make('password'),
        ]);

        TeacherProfile::create([
            'user_id' => $teacher->id,
            'school_name' => 'โรงเรียนสาธิตนวัตกรรมวิทยาศาสตร์',
            'department' => 'กลุ่มสาระการเรียนรู้วิทยาศาสตร์และเทคโนโลยี',
            'phone' => '0812345678',
            'bio' => 'มุ่งมั่นพัฒนาการเรียนรู้ผ่านเกมและการคิดเชิงออกแบบ',
        ]);

        TeacherAiCredential::create([
            'teacher_id' => $teacher->id,
            'provider' => 'gemini',
            'encrypted_api_key' => 'mock-gemini-api-key-demo-xyz123',
            'model' => 'gemini-1.5-flash',
            'is_active' => true,
        ]);

        $student = User::create([
            'name' => 'ด.ช. ปัญญา เรียนดี',
            'email' => 'student@example.com',
            'username' => 'student01',
            'student_id' => 'STU-1001',
            'role' => 'student',
            'password' => Hash::make('password'),
        ]);

        StudentProfile::create([
            'user_id' => $student->id,
            'student_code' => 'STU-1001',
            'grade_level' => 'มัธยมศึกษาปีที่ 1',
            'school_name' => 'โรงเรียนสาธิตนวัตกรรมวิทยาศาสตร์',
        ]);

        // 2. Classrooms
        $classroom = Classroom::create([
            'teacher_id' => $teacher->id,
            'name' => 'วิทยาศาสตร์ ม.1/2 (ปีการศึกษา 2569)',
            'code' => 'DTG-SCI01',
            'description' => 'ห้องเรียนวิชาวิทยาศาสตร์สิ่งแวดล้อมและการคิดเชิงออกแบบ',
            'academic_year' => '2569',
            'semester' => '1',
            'status' => 'active',
        ]);

        $classroom->students()->attach($student->id);

        // 3. Asset Categories & Packs
        $catChar = AssetCategory::create(['name' => 'ตัวละคร (Characters)', 'slug' => 'characters', 'icon' => 'mdi-account-cowboy-hat']);
        $catBg = AssetCategory::create(['name' => 'ฉากหลัง (Backgrounds)', 'slug' => 'backgrounds', 'icon' => 'mdi-image-filter-hdr']);
        $catObj = AssetCategory::create(['name' => 'วัตถุและไอเทม (Objects)', 'slug' => 'objects', 'icon' => 'mdi-cube-outline']);
        $catUi = AssetCategory::create(['name' => 'องค์ประกอบ UI', 'slug' => 'ui', 'icon' => 'mdi-view-dashboard-outline']);

        $packSchool = AssetPack::create([
            'name' => 'ชุดสิ่งแวดล้อมโรงเรียน (School & Eco)',
            'slug' => 'school-eco',
            'description' => 'ภาพกราฟิกตัวละคร นักเรียน ถังขยะแยกประเภท และอาคารโรงเรียน',
            'theme' => 'school',
            'is_system' => true,
        ]);

        Asset::create([
            'name' => 'ครูวิทยาศาสตร์ (Teacher NPC)',
            'type' => 'character',
            'category_id' => $catChar->id,
            'asset_pack_id' => $packSchool->id,
            'file_path' => 'https://api.dicebear.com/7.x/bottts/svg?seed=TeacherEco',
            'preview_url' => 'https://api.dicebear.com/7.x/bottts/svg?seed=TeacherEco',
            'style' => 'flat',
            'theme' => 'school',
            'is_public' => true,
            'uploaded_by' => $admin->id,
        ]);

        Asset::create([
            'name' => 'นักเรียนนักสืบสิ่งแวดล้อม (Student Scout)',
            'type' => 'character',
            'category_id' => $catChar->id,
            'asset_pack_id' => $packSchool->id,
            'file_path' => 'https://api.dicebear.com/7.x/bottts/svg?seed=StudentScout',
            'preview_url' => 'https://api.dicebear.com/7.x/bottts/svg?seed=StudentScout',
            'style' => 'flat',
            'theme' => 'school',
            'is_public' => true,
            'uploaded_by' => $admin->id,
        ]);

        Asset::create([
            'name' => 'ถังขยะรีไซเคิลสีเหลือง',
            'type' => 'object',
            'category_id' => $catObj->id,
            'asset_pack_id' => $packSchool->id,
            'file_path' => 'trash_recycle',
            'preview_url' => 'trash_recycle',
            'style' => 'flat',
            'theme' => 'school',
            'is_public' => true,
            'uploaded_by' => $admin->id,
        ]);

        // 4. Design Thinking Project
        $project = DesignProject::create([
            'teacher_id' => $teacher->id,
            'title' => 'ภารกิจโรงเรียนไร้ขยะ (Zero Waste School Mission)',
            'description' => 'โครงการสร้างเกมเพื่อสอนการคัดแยกขยะตามมาตรฐานสิ่งแวดล้อมสำหรับนักเรียนชั้น ม.1',
            'subject' => 'วิทยาศาสตร์และสิ่งแวดล้อม',
            'grade_level' => 'มัธยมศึกษาปีที่ 1',
            'current_step' => 5,
            'status' => 'published',
        ]);

        DesignEmpathize::create([
            'project_id' => $project->id,
            'target_learner' => 'นักเรียนชั้นมัธยมศึกษาปีที่ 1 อายุ 12-13 ปี',
            'age_group' => '12-13 ปี',
            'grade_level' => 'ม.1',
            'subject' => 'วิทยาศาสตร์',
            'learning_context' => 'คาบเรียนวิทยาศาสตร์ในห้องเรียนคอมพิวเตอร์และบนสมาร์ตโฟน',
            'learner_characteristics' => 'ชอบภาพการ์ตูนสีสันสดใส ชอบความท้าทายที่มีแต้มสะสมและ feedback ทันที',
            'existing_knowledge' => 'รู้จักขยะทั่วไป แต่สับสนระหว่างขยะรีไซเคิลและขยะอันตราย',
            'interests' => 'เกมแก้ปริศนาและภารกิจกอบกู้สิ่งแวดล้อม',
            'learning_difficulties' => 'จำสีถังขยะและสัญลักษณ์พลาสติกรีไซเคิลไม่ได้ สมาธิสั้นเมื่ออ่านบทความยาว',
            'pain_points' => 'ทิ้งขยะไม่ถูกถังเพราะรีบและไม่มีแรงจูงใจในการแยกขยะ',
            'learning_environment' => 'คอมพิวเตอร์และแท็บเล็ตในห้องเรียน หรือมือถือที่บ้าน',
            'device_availability' => 'Mobile & Desktop',
            'ai_notes' => [
                'persona_summary' => 'กลุ่มผู้เรียนต้องการ Micro-learning แบบ interactive และ dynamic feedback',
                'recommended_pace' => 'เกมสั้น 5-10 นาทีพร้อมคะแนนโบนัสความรวดเร็ว',
            ],
        ]);

        DesignDefine::create([
            'project_id' => $project->id,
            'problem_statement' => 'นักเรียน ม.1 มักทิ้งขยะปะปนกันเนื่องจากขาดความรู้ความเข้าใจเรื่องประเภทขยะและขาดแรงบันดาลใจในการรักษาสิ่งแวดล้อม',
            'learning_problem' => 'อัตราการแยกขยะถูกต้องในโรงเรียนต่ำกว่า 40%',
            'learning_objectives' => [
                'สามารถจำแนกขยะ 4 ประเภท (อินทรีย์, รีไซเคิล, ทั่วไป, อันตราย) ได้อย่างถูกต้อง',
                'อธิบายผลกระทบของการแยกขยะต่อระบบนิเวศได้',
                'เกิดทัศนคติที่ดีต่อการรักษาความสะอาดในโรงเรียน',
            ],
            'expected_outcomes' => 'นักเรียนสามารถแยกขยะได้ถูกต้องอย่างน้อย 80% หลังเล่นเกม',
            'knowledge_goals' => 'เข้าใจสัญลักษณ์และสีถังขยะสากล',
            'skill_goals' => 'ทักษะการสังเกตและตัดสินใจแยกแยะประเภทวัสดุอย่างรวดเร็ว',
            'attitude_goals' => 'ตระหนักถึงคุณค่าของการรีไซเคิลเพื่อลดโลกร้อน',
            'success_criteria' => 'คะแนนทดสอบหลังเล่นเกมผ่านเกณฑ์ 75% ขึ้นไป',
            'ai_notes' => [
                'problem_clarity_score' => 95,
                'alignment' => 'สอดคล้องกับมาตรฐาน ว 2.3 ของหลักสูตรแกนกลาง',
            ],
        ]);

        DesignIdeate::create([
            'project_id' => $project->id,
            'game_concept' => 'ภารกิจช่วยมาสคอตโรงเรียนกอบกู้สวนพฤกษศาสตร์ โดยการคัดแยกขยะที่ถูกทิ้งระเกะระกะให้ลงถังที่ถูกต้องเพื่อรับคะแนนหัวใจสีเขียว',
            'game_genre' => 'Scenario & Sorting Puzzle',
            'theme' => 'school',
            'story' => 'โรงเรียนกำลังจะได้รับรางวัลโรงเรียนสีเขียว แต่ขยะปริมาณมากกำลังจะทำให้ถูกตัดสิทธิ์! คุณได้รับเลือกเป็น Eco-Detective คอยจัดการขยะ!',
            'game_mechanics' => [
                'ระบบเลือกคำตอบแบบมีภาพประกอบ (Visual Multiple Choice)',
                'ระบบลากวางขยะลงถังสีต่างๆ (Drag & Drop Sorting)',
                'เกจคะแนนสะสมและแถบจับเวลาโบนัส',
            ],
            'challenges' => 'เวลาจำกัด 5 นาที และขยะอันตรายที่ต้องระวังเป็นพิเศษ',
            'missions' => 'เคลียร์ขยะ 3 ด่าน: ขยะทั่วไป, ขยะรีไซเคิล, ขยะอันตราย',
            'rewards' => 'เหรียญดาว Eco-Master 3 ดาว และเข็มกลัดผู้พิทักษ์โลก',
            'interaction_type' => 'Interactive Click & Drag',
            'difficulty' => 'medium',
            'duration_minutes' => 10,
            'ai_ideas' => [
                'suggested_twists' => ['เพิ่มคำถามหลอก เช่น แก้วกาแฟพลาสติกที่มีหลอดดูด', 'เพิ่มโบนัสคอมโบเมื่อตอบถูกต่อเนื่อง'],
            ],
        ]);

        DesignPrototype::create([
            'project_id' => $project->id,
            'scene_outline' => [
                ['id' => 'scene_01', 'title' => 'ลานกิจกรรมโรงเรียน (บทนำ)', 'type' => 'dialogue'],
                ['id' => 'scene_02', 'title' => 'จุดคัดแยกขยะ (ภารกิจแยกประเภท)', 'type' => 'quiz_sorting'],
                ['id' => 'scene_03', 'title' => 'สรุปผลภารกิจและมอบเหรียญรางวัล', 'type' => 'completion'],
            ],
            'character_roles' => [
                ['name' => 'ครูสมศรี (NPC ผู้สอน)', 'role' => 'แนะนำภารกิจและอธิบายความรู้'],
                ['name' => 'ผู้เล่น (Eco Detective)', 'role' => 'ตัดสินใจแยกขยะและตอบคำถาม'],
            ],
            'core_rules' => [
                'ตอบถูกรับ 10 คะแนน',
                'ตอบผิดเสีย 2 คะแนน และมีคำใบ้เพื่อการเรียนรู้ทันที',
                'ต้องได้คะแนน 60 คะแนนขึ้นไปจึงจะผ่านภารกิจ',
            ],
            'feedback_mechanisms' => 'เสียงเอฟเฟกต์เชิงบวกเมื่อตอบถูก และการ์ดสรุปความรู้สั้นๆ เมื่อตอบผิด',
        ]);

        DesignTest::create([
            'project_id' => $project->id,
            'test_date' => now()->toDateString(),
            'test_users_count' => 5,
            'observations' => 'ผู้ทดสอบสนุกกับจังหวะของเกม แต่มีข้อสับสนเกี่ยวกับขยะอิเล็กทรอนิกส์ในฉากที่ 2',
            'recorded_bugs' => [
                'ปรับขนาดปุ่มตัวเลือกบนมือถือให้กดง่ายขึ้น',
            ],
            'difficulty_rating' => 3,
            'feedback_summary' => 'เข้าใจง่าย ภาพสดใส อยากเล่นซ้ำเพื่อทำคะแนนให้เต็ม 100',
            'ai_recommendations' => [
                'เพิ่มคำใบ้รูปไอคอนแบตเตอรี่ในข้อสอบขยะอันตรายเพื่อลดความผิดพลาด',
            ],
        ]);

        // 5. Game & Game Version 1.0 (Strict Game Schema)
        $gameSchema = [
            'version' => '1.0',
            'title' => 'ภารกิจโรงเรียนไร้ขยะ (Zero Waste Mission)',
            'description' => 'เกมเรียนรู้การคัดแยกขยะตามสีและประเภท เพื่อสิ่งแวดล้อมที่ยั่งยืน',
            'theme' => 'school',
            'settings' => [
                'duration' => 300,
                'maxAttempts' => 3,
                'allowSound' => true,
                'passingScore' => 60,
            ],
            'scenes' => [
                [
                    'id' => 'scene_intro',
                    'title' => 'ลานอเนกประสงค์โรงเรียน',
                    'background' => 'school_yard',
                    'elements' => [
                        [
                            'id' => 'npc_teacher',
                            'type' => 'character',
                            'name' => 'ครูสมศรี',
                            'avatar' => 'https://api.dicebear.com/7.x/bottts/svg?seed=TeacherEco',
                            'x' => 15,
                            'y' => 35,
                            'dialogue' => [
                                'text' => 'สวัสดีจ้ะนักเรียน! วันนี้โรงเรียนของเรามีขยะตกค้างอยู่เป็นจำนวนมาก ถ้าเราไม่ช่วยกันแยก ขยะเหล่านี้จะส่งผลกระทบต่อสิ่งแวดล้อมอย่างหนัก!',
                                'speaker' => 'ครูสมศรี (หัวหน้าหมวดวิทย์)',
                                'nextScene' => 'scene_quiz_1',
                                'actionText' => 'รับภารกิจแยกขยะ!',
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 'scene_quiz_1',
                    'title' => 'ด่านที่ 1: ขวดน้ำพลาสติกใส',
                    'background' => 'classroom_front',
                    'elements' => [
                        [
                            'id' => 'quiz_bottle',
                            'type' => 'question',
                            'questionType' => 'multiple_choice',
                            'question' => 'ขวดน้ำดื่มพลาสติก PET ใส หลังจากดื่มหมดแล้ว ควรทิ้งลงในถังขยะสีใด?',
                            'image' => 'https://images.unsplash.com/photo-1528190336454-13cd56b45b5a?w=400&auto=format&fit=crop&q=60',
                            'options' => [
                                ['id' => 'opt_blue', 'text' => 'ถังสีฟ้า (ขยะทั่วไป)', 'isCorrect' => false],
                                ['id' => 'opt_yellow', 'text' => 'ถังสีเหลือง (ขยะรีไซเคิล)', 'isCorrect' => true],
                                ['id' => 'opt_green', 'text' => 'ถังสีเขียว (ขยะเปียก/อินทรีย์)', 'isCorrect' => false],
                                ['id' => 'opt_red', 'text' => 'ถังสีแดง (ขยะอันตราย)', 'isCorrect' => false],
                            ],
                            'points' => 25,
                            'explanation' => 'ถูกต้องยอดเยี่ยม! ถังขยะสีเหลืองใช้สำหรับขยะรีไซเคิล เช่น แก้ว กระดาษ โลหะ และพลาสติกที่นำไปแปรรูปได้',
                            'nextScene' => 'scene_quiz_2',
                        ],
                    ],
                ],
                [
                    'id' => 'scene_quiz_2',
                    'title' => 'ด่านที่ 2: เปลือกกล้วยและเศษอาหาร',
                    'background' => 'school_canteen',
                    'elements' => [
                        [
                            'id' => 'quiz_banana',
                            'type' => 'question',
                            'questionType' => 'multiple_choice',
                            'question' => 'เปลือกกล้วยหอมและเศษผักจากโรงอาหาร ควรทิ้งลงในถังใดเพื่อนำไปทำปุ๋ยหมักชีวภาพ?',
                            'image' => 'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=400&auto=format&fit=crop&q=60',
                            'options' => [
                                ['id' => 'opt_green', 'text' => 'ถังสีเขียว (ขยะอินทรีย์/ย่อยสลายได้)', 'isCorrect' => true],
                                ['id' => 'opt_yellow', 'text' => 'ถังสีเหลือง (ขยะรีไซเคิล)', 'isCorrect' => false],
                                ['id' => 'opt_blue', 'text' => 'ถังสีน้ำเงิน (ขยะทั่วไป)', 'isCorrect' => false],
                                ['id' => 'opt_red', 'text' => 'ถังสีส้ม/แดง (ขยะอันตราย)', 'isCorrect' => false],
                            ],
                            'points' => 25,
                            'explanation' => 'ถูกต้อง! ถังสีเขียวมีไว้รองรับขยะที่เน่าเสียและย่อยสลายได้เร็วตามธรรมชาติ นำไปทำปุ๋ยหมักบำรุงดินได้',
                            'nextScene' => 'scene_quiz_3',
                        ],
                    ],
                ],
                [
                    'id' => 'scene_quiz_3',
                    'title' => 'ด่านที่ 3: แบตเตอรี่และถ่านไฟฉาย',
                    'background' => 'science_lab',
                    'elements' => [
                        [
                            'id' => 'quiz_battery',
                            'type' => 'question',
                            'questionType' => 'multiple_choice',
                            'question' => 'ถ่านไฟฉายที่หมดสภาพแล้วจัดเป็นขยะประเภทใด และควรทิ้งลงถังสีอะไร?',
                            'image' => 'https://images.unsplash.com/photo-1619641782821-75178523cf44?w=400&auto=format&fit=crop&q=60',
                            'options' => [
                                ['id' => 'opt_blue', 'text' => 'ถังสีน้ำเงิน เพราะทำจากโลหะทั่วไป', 'isCorrect' => false],
                                ['id' => 'opt_yellow', 'text' => 'ถังสีเหลือง เพราะมีโลหะที่รีไซเคิลได้', 'isCorrect' => false],
                                ['id' => 'opt_red', 'text' => 'ถังสีแดง/ส้ม เพราะมีสารเคมีและโลหะหนักอันตราย', 'isCorrect' => true],
                                ['id' => 'opt_green', 'text' => 'ฝังดินในสวนโรงเรียน', 'isCorrect' => false],
                            ],
                            'points' => 25,
                            'explanation' => 'ยอดเยี่ยมมาก! แบตเตอรี่มีสารตะกั่ว แคดเมียม และปรอท ต้องทิ้งลงถังสีแดงหรือจุดรับขยะพิษเท่านั้น เพื่อป้องกันสารพิษรั่วไหลสู่แหล่งน้ำ',
                            'nextScene' => 'scene_quiz_4',
                        ],
                    ],
                ],
                [
                    'id' => 'scene_quiz_4',
                    'title' => 'ด่านที่ 4: หลอดกาแฟและซองขนม',
                    'background' => 'school_corridor',
                    'elements' => [
                        [
                            'id' => 'quiz_snack',
                            'type' => 'question',
                            'questionType' => 'true_false',
                            'question' => 'จริงหรือไม่? ซองขนมกรุบกรอบที่เคลือบฟอยล์และหลอดพลาสติก จัดเป็น "ขยะทั่วไป" ที่ย่อยสลายยากและไม่คุ้มค่าต่อการรีไซเคิล',
                            'image' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=400&auto=format&fit=crop&q=60',
                            'options' => [
                                ['id' => 'opt_true', 'text' => 'จริง (ถูกต้อง)', 'isCorrect' => true],
                                ['id' => 'opt_false', 'text' => 'ไม่จริง', 'isCorrect' => false],
                            ],
                            'points' => 25,
                            'explanation' => 'ถูกต้อง! ซองขนมเคลือบฟอยล์อลูมิเนียมแยกชั้นพลาสติกยากมาก จึงทิ้งในถังสีน้ำเงิน (ขยะทั่วไป) เพื่อนำไปกำจัดอย่างถูกวิธี',
                            'nextScene' => 'scene_complete',
                        ],
                    ],
                ],
                [
                    'id' => 'scene_complete',
                    'title' => 'สรุปภารกิจ Eco-Detective',
                    'background' => 'school_yard',
                    'elements' => [
                        [
                            'id' => 'completion_card',
                            'type' => 'completion',
                            'title' => 'ภารกิจพิทักษ์โรงเรียนสำเร็จ!',
                            'message' => 'ยินดีด้วย! คุณผ่านการทดสอบคัดแยกขยะระดับมือโปร ตอนนี้คุณพร้อมเป็นผู้นำด้านสิ่งแวดล้อมในโรงเรียนแล้ว!',
                        ],
                    ],
                ],
            ],
            'scoring' => [
                'initialScore' => 0,
                'maxScore' => 100,
                'passingScore' => 60,
            ],
            'completion' => [
                'type' => 'mission_complete',
                'rewardTitle' => 'Eco-Detective Green Badge',
            ],
        ];

        $game = Game::create([
            'teacher_id' => $teacher->id,
            'project_id' => $project->id,
            'public_id' => 'GME-ECO01',
            'title' => 'ภารกิจโรงเรียนไร้ขยะ (Zero Waste Mission)',
            'description' => 'เกมผจญภัยเรียนรู้การแยกขยะเพื่อสิ่งแวดล้อมที่ดีของโรงเรียน',
            'theme' => 'school',
            'genre' => 'Scenario & Quiz Puzzle',
            'cover_image' => 'https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?w=600&auto=format&fit=crop&q=80',
            'status' => 'published',
            'settings' => [
                'duration' => 300,
                'maxAttempts' => 3,
            ],
        ]);

        $gameVersion = GameVersion::create([
            'game_id' => $game->id,
            'version_number' => '1.0',
            'schema_data' => json_encode($gameSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            'changelog' => 'เวอร์ชันเปิดตัว พร้อมระบบคำถามและคำอธิบายสิ่งแวดล้อม',
            'is_published' => true,
        ]);

        $game->update(['current_version_id' => $gameVersion->id]);

        // 6. Game Assignment to Classroom
        GameAssignment::create([
            'classroom_id' => $classroom->id,
            'game_id' => $game->id,
            'game_version_id' => $gameVersion->id,
            'start_at' => now(),
            'due_at' => now()->addDays(14),
            'max_attempts' => 3,
            'passing_score' => 60,
            'show_score' => true,
            'allow_replay' => true,
            'status' => 'active',
        ]);
    }
}
