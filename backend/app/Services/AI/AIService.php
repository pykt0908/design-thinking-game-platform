<?php

namespace App\Services\AI;

use App\Models\DesignProject;
use App\Models\TeacherAiCredential;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    /**
     * Get contextual suggestions for a specific Design Thinking step
     */
    public function getStepSuggestion(DesignProject $project, string $step, array $contextData = []): array
    {
        $subject = $project->subject ?? 'วิทยาศาสตร์';
        $grade = $project->grade_level ?? 'มัธยมศึกษาตอนต้น';
        $title = $project->title;

        switch ($step) {
            case 'empathize':
                return [
                    'title' => 'วิเคราะห์กลุ่มผู้เรียน (Learner Persona Insights)',
                    'suggestions' => [
                        "ผู้เรียนระดับ {$grade} ในวิชา {$subject} มักตอบสนองต่อเกมที่มีการสะสมแต้มแบบทันที (Instant Gratification)",
                        "แนะนำให้แบ่งเนื้อหาเป็นภารกิจสั้นๆ (Micro-missions) ความยาวไม่เกิน 2-3 นาทีต่อด่าน",
                        "ควรมีตัวละครมาสคอต (NPC) คอยให้กำลังใจและคำใบ้เพื่อลดความเครียดจากการตอบผิด",
                    ],
                    'suggested_pain_points' => [
                        "รู้สึกว่าเนื้อหา {$subject} มีศัพท์เฉพาะและทฤษฎียากต่อการจดจำ",
                        "ขาดการเชื่อมโยงเนื้อหากับเหตุการณ์ในชีวิตประจำวัน",
                    ],
                ];

            case 'define':
                $empathize = $project->empathize;
                $pain = $empathize?->pain_points ?? 'ผู้เรียนขาดความเข้าใจในเนื้อหาหลัก';
                return [
                    'title' => 'ข้อเสนอแนะการกำหนดปัญหา (Problem Definition)',
                    'problem_statement' => "ผู้เรียน {$grade} ประสบปัญหา {$pain} ทำให้ไม่สามารถประยุกต์ใช้ความรู้ในสถานการณ์จริงได้ จึงต้องการเกมการเรียนรู้ที่จำลองสถานการณ์ให้ลงมือปฏิบัติ",
                    'suggested_objectives' => [
                        "ผู้เรียนสามารถระบุและจำแนกองค์ประกอบสำคัญในเรื่อง {$title} ได้ถูกต้อง",
                        "ผู้เรียนสามารถแก้ปัญหาตามสถานการณ์จำลองที่กำหนดได้ผ่านเกณฑ์ 80%",
                        "ผู้เรียนมีเจตคติเชิงบวกต่อการเรียนรู้วิชา {$subject}",
                    ],
                ];

            case 'ideate':
                return [
                    'title' => 'แนวคิดเกมที่แนะนำ (Curated Game Concepts)',
                    'concepts' => [
                        [
                            'genre' => 'Scenario & Sorting Quest',
                            'theme' => 'school',
                            'concept' => "ภารกิจกู้โรงเรียน: สวมบทบาทเป็นสายลับพิทักษ์ความรู้ ค้นหาและจำแนกสิ่งของให้ถูกต้อง",
                            'mechanics' => ['Visual Choice', 'Drag & Drop', 'Timer Bonus'],
                        ],
                        [
                            'genre' => 'Adventure Simulation',
                            'theme' => 'science',
                            'concept' => "ห้องทดลองพิศวง: แก้ปริศนาตามด่านเพื่อสะสมกุญแจทองคำปลดล็อกห้องทดลองลับ",
                            'mechanics' => ['Exploration', 'Multiple Choice', 'Item Collection'],
                        ],
                    ],
                ];

            case 'prototype':
                return [
                    'title' => 'โครงสร้างฉากและตัวละครที่เหมาะสม',
                    'recommended_scenes' => [
                        ['type' => 'dialogue', 'title' => 'ฉากเปิดเรื่อง: แนะนำภารกิจและแรงจูงใจ'],
                        ['type' => 'challenge_1', 'title' => 'ด่านแรก: ปัญหาพื้นฐานเพื่อสร้างความมั่นใจ'],
                        ['type' => 'challenge_2', 'title' => 'ด่านกลาง: ปัญหาประยุกต์ที่ท้าทายขึ้น'],
                        ['type' => 'completion', 'title' => 'ฉากสรุป: สรุปบทเรียน มอบดาว และเหรียญรางวัล'],
                    ],
                ];

            case 'test':
                return [
                    'title' => 'การปรับสมดุลเกม (Game Balancing Advice)',
                    'suggestions' => [
                        "หากผู้เรียนทำคะแนนเฉลี่ยต่ำกว่า 60% ควรเพิ่มคำใบ้ (Hint) หลังตอบผิดครั้งแรก",
                        "ข้อคำถามที่มีตัวเลือกยาวเกินไป อาจทำให้อัตราการอ่านลดลง ควรเน้นภาพประกอบ",
                    ],
                ];

            default:
                return ['message' => 'AI พร้อมให้คำปรึกษาตลอดทุกขั้นตอนของ Design Thinking'];
        }
    }

    /**
     * Generate complete Game Schema v1.0 from a Design Thinking project
     */
    public function generateGameSchema(DesignProject $project): array
    {
        $project->load(['empathize', 'define', 'ideate', 'prototype']);

        $title = $project->title ?: 'เกมการเรียนรู้';
        $desc = $project->description ?: 'เกมการเรียนรู้ผ่านกระบวนการ Design Thinking';
        $subject = $project->subject ?: 'ทั่วไป';
        $theme = $project->ideate?->theme ?: 'school';
        $gameGenre = $project->ideate?->game_genre ?: 'Scenario & Quiz';
        $duration = ($project->ideate?->duration_minutes ?: 10) * 60;

        $objectives = $project->define?->learning_objectives ?? [
            'เข้าใจหลักการพื้นฐานของเนื้อหา',
            'สามารถตอบคำถามและตัดสินใจในสถานการณ์จำลองได้ถูกต้อง',
        ];

        // Construct 4 progressive scenes
        $scenes = [
            [
                'id' => 'scene_intro',
                'title' => 'บทนำสู่ภารกิจการเรียนรู้',
                'background' => 'school_campus',
                'elements' => [
                    [
                        'id' => 'char_guide',
                        'type' => 'character',
                        'name' => 'ครูผู้แนะนำ',
                        'avatar' => 'https://api.dicebear.com/7.x/bottts/svg?seed=GuideHero',
                        'x' => 20,
                        'y' => 30,
                        'dialogue' => [
                            'speaker' => 'ครูผู้แนะนำ',
                            'text' => "ยินดีต้อนรับสู่ '{$title}'! วันนี้เรามีความท้าทายใหม่มารอให้คุณพิสูจน์ความสามารถ พร้อมเริ่มภารกิจหรือยัง?",
                            'actionText' => 'พร้อมแล้ว ลุยเลย! 🚀',
                            'nextScene' => 'scene_challenge_1',
                        ],
                    ],
                ],
            ],
            [
                'id' => 'scene_challenge_1',
                'title' => 'ภารกิจที่ 1: ความรู้พื้นฐาน',
                'background' => 'learning_room',
                'elements' => [
                    [
                        'id' => 'quiz_1',
                        'type' => 'question',
                        'questionType' => 'multiple_choice',
                        'question' => "ในวิชา {$subject} ข้อใดอธิบายเป้าหมายของ {$title} ได้ถูกต้องที่สุด?",
                        'image' => 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=500&auto=format&fit=crop&q=60',
                        'options' => [
                            ['id' => 'c1_opt1', 'text' => $objectives[0] ?? 'การเข้าใจหลักการที่ถูกต้อง', 'isCorrect' => true],
                            ['id' => 'c1_opt2', 'text' => 'การทำตามความคุ้นเคยโดยไม่วางแผน', 'isCorrect' => false],
                            ['id' => 'c1_opt3', 'text' => 'การละเลยผลกระทบต่อสิ่งแวดล้อมและสังคม', 'isCorrect' => false],
                            ['id' => 'c1_opt4', 'text' => 'ไม่มีข้อใดถูกต้อง', 'isCorrect' => false],
                        ],
                        'points' => 30,
                        'explanation' => 'ยอดเยี่ยม! การเข้าใจเป้าหมายที่แท้จริงช่วยให้ตัดสินใจได้อย่างมีประสิทธิภาพ',
                        'nextScene' => 'scene_challenge_2',
                    ],
                ],
            ],
            [
                'id' => 'scene_challenge_2',
                'title' => 'ภารกิจที่ 2: การประยุกต์ใช้และการแก้ปัญหา',
                'background' => 'mission_field',
                'elements' => [
                    [
                        'id' => 'quiz_2',
                        'type' => 'question',
                        'questionType' => 'multiple_choice',
                        'question' => "เมื่อพบสถานการณ์ที่ท้าทายใน {$subject} ขั้นตอนใดควรทำเป็นอันดับแรกตามกระบวนการ Design Thinking?",
                        'image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=500&auto=format&fit=crop&q=60',
                        'options' => [
                            ['id' => 'c2_opt1', 'text' => 'ทำความเข้าใจผู้ใช้และความต้องการที่แท้จริง (Empathize)', 'isCorrect' => true],
                            ['id' => 'c2_opt2', 'text' => 'รีบสร้างชิ้นงานทันทีโดยไม่สำรวจข้อมูล', 'isCorrect' => false],
                            ['id' => 'c2_opt3', 'text' => 'ยกเลิกการดำเนินงาน', 'isCorrect' => false],
                        ],
                        'points' => 35,
                        'explanation' => 'ถูกต้อง! การเริ่มต้นด้วย Empathize ช่วยให้เราเข้าใจปัญหาที่แท้จริงได้อย่างลึกซึ้ง',
                        'nextScene' => 'scene_challenge_3',
                    ],
                ],
            ],
            [
                'id' => 'scene_challenge_3',
                'title' => 'ภารกิจที่ 3: การประเมินสถานการณ์จริง',
                'background' => 'assessment_hall',
                'elements' => [
                    [
                        'id' => 'quiz_3',
                        'type' => 'question',
                        'questionType' => 'true_false',
                        'question' => "จริงหรือไม่? การทดสอบ (Test) และนำข้อเสนอแนะมาปรับปรุงเกม ถือเป็นหัวใจสำคัญของการเรียนรู้ที่ไม่สิ้นสุด",
                        'image' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=500&auto=format&fit=crop&q=60',
                        'options' => [
                            ['id' => 'c3_true', 'text' => 'จริง (ถูกต้องแน่นอน)', 'isCorrect' => true],
                            ['id' => 'c3_false', 'text' => 'ไม่จริง', 'isCorrect' => false],
                        ],
                        'points' => 35,
                        'explanation' => 'ถูกต้องที่สุด! กระบวนการทดสอบและปรับปรุงทำให้เกมและการเรียนรู้ดียิ่งขึ้นเสมอ',
                        'nextScene' => 'scene_finish',
                    ],
                ],
            ],
            [
                'id' => 'scene_finish',
                'title' => '🎉 ภารกิจเสร็จสิ้น!',
                'background' => 'victory_stage',
                'elements' => [
                    [
                        'id' => 'victory_card',
                        'type' => 'completion',
                        'title' => '🎉 ยินดีด้วย คุณทำภารกิจสำเร็จแล้ว!',
                        'message' => "คุณได้ผ่านการทดสอบใน '{$title}' เรียบร้อยแล้ว ขอให้รักษาความตั้งใจนี้ไว้!",
                    ],
                ],
            ],
        ];

        return [
            'version' => '1.0',
            'title' => $title,
            'description' => $desc,
            'theme' => $theme,
            'genre' => $gameGenre,
            'settings' => [
                'duration' => $duration,
                'maxAttempts' => 3,
                'allowSound' => true,
                'passingScore' => 60,
            ],
            'scenes' => $scenes,
            'scoring' => [
                'initialScore' => 0,
                'maxScore' => 100,
                'passingScore' => 60,
            ],
            'completion' => [
                'type' => 'mission_complete',
                'rewardTitle' => 'Master Badge',
            ],
        ];
    }
}
