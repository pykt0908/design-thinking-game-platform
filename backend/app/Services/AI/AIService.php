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
     * Generate AI suggestion for a single specific field in Design Thinking Studio
     */
    public function generateFieldSuggestion(DesignProject $project, string $step, string $field, string $currentValue = ''): array
    {
        $project->load(['empathize', 'define', 'ideate', 'prototype']);

        $title = $project->title ?: 'บทเรียนการศึกษา';
        $subject = $project->subject ?: 'ทั่วไป';
        $grade = $project->grade_level ?: 'มัธยมศึกษาตอนต้น';
        $mode = $project->game_mode === 'multiplayer_live' ? 'การแข่งขันสดในห้องเรียน (Kahoot Style)' : 'การผจญภัยเล่นเดี่ยว (Single Player Quest)';
        $genre = $project->game_genre ?: 'rpg_quest';
        $theme = $project->theme_pack ?: 'fantasy';

        // Check if teacher has configured an active API key
        $credential = TeacherAiCredential::where('teacher_id', $project->teacher_id)
            ->where('is_active', true)
            ->first();

        if ($credential && $credential->encrypted_api_key) {
            try {
                $llmResult = $this->callExternalLlmForField($credential, $project, $step, $field, $currentValue);
                if (!empty($llmResult['suggestion'])) {
                    return $llmResult;
                }
            } catch (\Throwable $e) {
                Log::warning("AI field generation via external LLM failed: " . $e->getMessage());
            }
        }

        // Contextual Pedagogical Engine
        return $this->generateContextualFieldFallback($project, $step, $field);
    }

    protected function callExternalLlmForField(TeacherAiCredential $credential, DesignProject $project, string $step, string $field, string $currentValue): array
    {
        $prompt = "คุณคือผู้เชี่ยวชาญด้าน Design Thinking และ Educational Game Design สำหรับครูระดับแนวหน้า\n";
        $prompt .= "โปรเจกต์: {$project->title}\nวิชา: {$project->subject}\nระดับชั้น: {$project->grade_level}\n";
        $prompt .= "ขั้นตอน Design Thinking: {$step}\nช่องข้อมูลที่ต้องการให้คิดคำตอบ: {$field}\n";
        if ($currentValue) {
            $prompt .= "ข้อความเดิมที่มีอยู่: {$currentValue}\n";
        }
        $prompt .= "กรุณาเสนอข้อความภาษาไทยที่สร้างสรรค์ ตรงหลักสูตร เหมาะกับผู้เรียนวัยนี้ เขียนกระชับ สละสลวย นำไปใช้ได้ทันที\n";
        $prompt .= "ตอบกลับในรูปแบบ JSON:\n{\n  \"suggestion\": \"ข้อความหลักที่แนะนำ\",\n  \"alternatives\": [\"ตัวเลือกเพิ่มเติม 1\", \"ตัวเลือกเพิ่มเติม 2\"]\n}";

        if ($credential->provider === 'gemini') {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $credential->encrypted_api_key;
            $res = Http::timeout(10)->post($url, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'temperature' => 0.7,
                ],
            ]);

            if ($res->successful()) {
                $raw = $res->json('candidates.0.content.parts.0.text');
                $parsed = json_decode($raw, true);
                if (isset($parsed['suggestion'])) {
                    return [
                        'field' => $field,
                        'suggestion' => $parsed['suggestion'],
                        'alternatives' => $parsed['alternatives'] ?? [],
                    ];
                }
            }
        }

        return [];
    }

    protected function generateContextualFieldFallback(DesignProject $project, string $step, string $field): array
    {
        $title = $project->title ?: 'บทเรียน';
        $subject = $project->subject ?: 'วิทยาศาสตร์และเทคโนโลยี';
        $grade = $project->grade_level ?: 'มัธยมศึกษาปีที่ 1';
        $genre = $project->game_genre ?: '2D RPG Quest';
        $theme = $project->theme_pack ?: 'แฟนตาซี';

        $pain = $project->empathize?->pain_points ?: "ขาดความเข้าใจในหลักการของ {$title} และรู้สึกว่าทฤษฎียากเกินไป";

        $dict = [
            'target_learner' => [
                'suggestion' => "นักเรียนระดับ{$grade} ในวิชา{$subject} ที่ต้องการการเรียนรู้แบบมีปฏิสัมพันธ์ผ่านเกม",
                'alternatives' => [
                    "กลุ่มผู้เรียน{$grade} ที่มีระดับพื้นฐานปานกลาง สนใจเทคโนโลยีและการทำภารกิจแก้ปัญหา",
                    "นักเรียนที่ชอบเรียนรู้ผ่านภาพและสถานการณ์จริง มากกว่าการอ่านตำราทฤษฎี",
                ],
            ],
            'age_group' => [
                'suggestion' => "12 - 15 ปี (วัยรุ่นตอนต้น มัธยมศึกษา)",
                'alternatives' => [
                    "10 - 12 ปี (ประถมปลาย)",
                    "15 - 18 ปี (มัธยมศึกษาตอนปลาย)",
                ],
            ],
            'learner_characteristics' => [
                'suggestion' => "ชอบความท้าทาย สนุกกับการตอบคำถามเพื่อปลดล็อกไอเทม/ด่าน มีสมาธิสูงเมื่อมีภาพแอนิเมชันประกอบ และชอบแข่งขันกับเพื่อนอย่างสร้างสรรค์",
                'alternatives' => [
                    "มีสมาธิต่อเนื่อง 10-15 นาที ชอบเรื่องราวแฟนตาซีและตัวละคร NPC ที่มีเอกลักษณ์ คอยให้คำใบ้",
                    "ชอบการเรียนรู้แบบเห็นผลทันที (Instant Feedback) และภูมิใจเมื่อทำภารกิจสำเร็จตามระดับคะแนน",
                ],
            ],
            'pain_points' => [
                'suggestion' => "จำเนื้อหาและคำศัพท์เฉพาะของเรื่อง '{$title}' ได้ยาก รู้สึกว่าเนื้อหาเป็นนามธรรมและไม่เห็นความเชื่อมโยงกับชีวิตประจำวัน",
                'alternatives' => [
                    "เบื่อหน่ายการทำใบงานแบบเดิมๆ ขาดแรงจูงใจในการทบทวนบทเรียนด้วยตนเอง",
                    "เมื่อตอบคำถามผิดมักไม่ได้รับคำอธิบายทันที ทำให้เกิดความเข้าใจผิดสะสมในเนื้อหา",
                ],
            ],
            'learning_environment' => [
                'suggestion' => "ใช้ในห้องเรียนปกติผ่านอุปกรณ์สมาร์ตโฟน/แท็บเล็ต หรือคอมพิวเตอร์โรงเรียน รวมถึงใช้ทบทวนที่บ้านด้วยตนเอง",
                'alternatives' => [
                    "กิจกรรมกลุ่มในห้องเรียน หรือการบ้านแบบ Gamification ที่สนุกไม่น่าเบื่อ",
                ],
            ],
            'problem_statement' => [
                'suggestion' => "ผู้เรียน{$grade} ประสบปัญหา{$pain} ทำให้ขาดความมั่นใจในการประยุกต์ใช้ความรู้ จึงต้องการเกมการเรียนรู้ที่ทำให้เนื้อหาจับต้องได้ผ่านการสวมบทบาทแก้ปัญหา",
                'alternatives' => [
                    "นักเรียนไม่สามารถเชื่อมโยงทฤษฎี '{$title}' สู่การปฏิบัติจริง จึงจำเป็นต้องมีสถานการณ์จำลองที่ให้ลองผิดลองถูกได้อย่างปลอดภัย",
                ],
            ],
            'expected_outcomes' => [
                'suggestion' => "ผู้เรียนสามารถอธิบายหลักการสำคัญของ '{$title}' และตอบคำถามท้าทายในสถานการณ์จำลองได้ถูกต้องไม่น้อยกว่า 80% หลังเล่นจบภารกิจ",
                'alternatives' => [
                    "ผู้เรียนสามารถวิเคราะห์และตัดสินใจเลือกแนวทางแก้ปัญหาตามเนื้อหาบทเรียนได้อย่างแม่นยำ พร้อมมีทัศนคติที่ดีต่อวิชา{$subject}",
                ],
            ],
            'knowledge_goals' => [
                'suggestion' => "เข้าใจความหมาย หลักการสำคัญ และคำจำกัดความที่จำเป็นของ '{$title}'",
                'alternatives' => [
                    "สามารถระบุองค์ประกอบและหน้าที่ของส่วนต่างๆ ในเนื้อหาได้อย่างถูกต้อง",
                ],
            ],
            'skill_goals' => [
                'suggestion' => "ทักษะการคิดวิเคราะห์ ตัดสินใจอย่างรวดเร็ว และการแก้ปัญหาตามสถานการณ์ที่กำหนด",
                'alternatives' => [
                    "ทักษะการสังเกต การคัดแยกข้อมูล และการประยุกต์ใช้ความรู้ในบริบทใหม่",
                ],
            ],
            'attitude_goals' => [
                'suggestion' => "มีความสนุกสนาน กระตือรือร้น และตระหนักถึงความสำคัญของวิชา{$subject} ในชีวิตประจำวัน",
                'alternatives' => [
                    "มีความมั่นใจในการเรียนรู้ และมองว่าข้อผิดพลาดคือโอกาสในการพัฒนาตนเอง",
                ],
            ],
            'game_concept' => [
                'suggestion' => "ภารกิจกอบกู้ดินแดนความรู้: ผู้เรียนสวมบทบาทเป็นนวัตกรผู้กล้า ออกเดินทางไขปริศนา '{$title}' ผ่านการตอบคำถาม ปลดล็อกพลังเวทมนตร์ และพิชิตอุปสรรคเพื่อนำความสงบสุขกลับคืนมา",
                'alternatives' => [
                    "ห้องทดลองจำลองอนาคต: สวมบทบาทเป็นนักวิจัยรุ่นเยาว์ ค้นหาเบาะแสและตอบคำถามเชิงตรรกะเพื่อกอบกู้วิกฤตการณ์ในเมือง",
                    "ศึกประลองปัญญาผู้กล้า (Live Battle): แข่งขันตอบคำถามประชันความเร็วกับเพื่อนร่วมห้องเพื่อไต่อันดับคะแนนสูงสุด",
                ],
            ],
            'story' => [
                'suggestion' => "ในดินแดนที่ความรู้เรื่อง '{$title}' กำลังถูกเงามืดกลืนกิน ตัวละครเอกได้รับสารปริศนาจากปราชญ์ผู้พิทักษ์ ขอให้เดินทางออกตามหาผลึกความรู้ทั้ง 4 ชิ้นที่ซ่อนอยู่ในแต่ละด่าน โดยต้องใช้สติปัญญาและบทเรียนเพื่อเอาชนะบททดสอบต่างๆ",
                'alternatives' => [
                    "สถานีอวกาศเกิดเหตุฉุกเฉิน ระบบความรู้ถูกตัดการเชื่อมต่อ ผู้เรียนต้องเป็นหัวหน้าหน่วยกู้ภัยที่ต้องแก้โจทย์สถานการณ์ทีละจุดเพื่อรีสตาร์ตระบบทั้งหมด",
                ],
            ],
            'missions' => [
                'suggestion' => "ภารกิจที่ 1: ตรวจสอบความรู้พื้นฐาน &rarr; ภารกิจที่ 2: วิเคราะห์สถานการณ์จำลอง &rarr; ภารกิจที่ 3: เผชิญหน้ากับโจทย์บอสสุดท้าทาย &rarr; ภารกิจที่ 4: สรุปชัยชนะและรับเหรียญรางวัล",
                'alternatives' => [
                    "ด่านที่ 1: สำรวจและตอบคำถามถูก/ผิด &rarr; ด่านที่ 2: เลือกเส้นทางตัดสินใจ &rarr; ด่านที่ 3: ตอบคำถามปรนัย 4 ตัวเลือกเพื่อเปิดประตูกล",
                ],
            ],
            'challenges' => [
                'suggestion' => "เวลาจำกัดในแต่ละข้อ (Countdown Timer), ตัวเลือกคำตอบที่มีตัวลวงใกล้เคียงกับความเป็นจริง, และการต้องคิดคำนวณอย่างรอบคอบ",
                'alternatives' => [
                    "มีเกณฑ์คะแนนผ่านขั้นต่ำ 60% หากตอบผิดจะเสียค่าพลัง HP ต้องอ่านคำใบ้เพื่อแก้ไขในรอบถัดไป",
                ],
            ],
            'rewards' => [
                'suggestion' => "เหรียญตรา Master Badge ประจำด่าน, คะแนนสะสม EXP, เอฟเฟกต์พลุเฉลิมฉลอง, และข้อความยกย่องจากตัวละครในเกม",
                'alternatives' => [
                    "อันดับคะแนนบน Leaderboard ของห้องเรียน และเกียรติบัตรจำลองแห่งความสำเร็จ",
                ],
            ],
            'feedback_mechanisms' => [
                'suggestion' => "แสดงเฉลยพร้อมคำอธิบายเสริมความรู้ทันทีหลังตอบแต่ละข้อ หากตอบถูกจะได้รับเอฟเฟกต์เสียงแห่งชัยชนะ หากตอบผิดจะมีคำใบ้คอยชี้แนะ",
                'alternatives' => [
                    "หน้าสรุปผลการเล่นแสดงกราฟจุดแข็ง-จุดอ่อน เพื่อให้ผู้เรียนทราบว่าควรทบทวนเรื่องใดเพิ่มเติม",
                ],
            ],
            'core_rules' => [
                'suggestion' => "ตอบคำถามให้ถูกต้องภายในเวลาที่กำหนด แต่ละข้อมีคะแนนตามระดับความยาก สามารถลองเล่นใหม่ได้เพื่อทำคะแนนให้ดีขึ้น",
                'alternatives' => [
                    "ผู้เล่นต้องสะสมคะแนนให้ผ่านเกณฑ์ 60% เพื่อปลดล็อกฉากจบที่สมบูรณ์",
                ],
            ],
            'device_availability' => [
                'suggestion' => "Mobile (สมาร์ตโฟน)",
                'alternatives' => [
                    "ทุกอุปกรณ์",
                    "Desktop/Laptop",
                    "Tablet",
                ],
            ],
            'duration_minutes' => [
                'suggestion' => "15",
                'alternatives' => ["10", "20"],
            ],
            'observations' => [
                'suggestion' => "ผู้เรียนมีความตื่นตัวและตั้งใจทำภารกิจอย่างมาก โดยเฉพาะฉากตอบคำถามที่มีตัวละครคอยให้กำลังใจ แต่พบว่าบางข้อคำถามมีความยาวทำให้อ่านไม่ทันเวลา",
                'alternatives' => [
                    "นักเรียนชอบระบบการสะสมเหรียญรางวัลและเสียงเอฟเฟกต์ แต่ต้องการให้มีปุ่มขอดูคำใบ้เพิ่มในข้อที่ยาก",
                    "นักเรียนส่วนใหญ่เล่นผ่านด่านแรกได้อย่างรวดเร็ว แต่เกิดข้อสงสัยในด่านที่ 3 เรื่องการประยุกต์ใช้",
                ],
            ],
            'feedback_summary' => [
                'suggestion' => "ควรเพิ่มระยะเวลาในการอ่านคำถามในข้อที่มีเนื้อหายาว ปรับขนาดตัวอักษรให้อ่านง่ายขึ้นบนสมาร์ตโฟน และเพิ่มการสรุปเนื้อหาสำคัญสั้นๆ หลังจบแต่ละด่าน",
                'alternatives' => [
                    "เพิ่มฉากทบทวนก่อนทำข้อสอบบอส และเพิ่มปุ่มแชร์คะแนนแห่งความภาคภูมิใจ",
                    "ปรับระดับความยากในด่านที่ 2 ให้ค่อยเป็นค่อยไป (Scaffolding) เพื่อให้นักเรียนทุกคนตามทัน",
                ],
            ],
        ];

        if (isset($dict[$field])) {
            return [
                'field' => $field,
                'suggestion' => $dict[$field]['suggestion'],
                'alternatives' => $dict[$field]['alternatives'],
            ];
        }

        return [
            'field' => $field,
            'suggestion' => "ข้อเสนอแนะสำหรับ {$field} ในหัวข้อ {$title}: ควรเน้นความสอดคล้องกับวัตถุประสงค์การเรียนรู้ระดับ {$grade} เพื่อสร้างความเข้าใจที่ลึกซึ้ง",
            'alternatives' => [
                "ปรับให้มีความท้าทายและเข้าใจง่ายสำหรับผู้เรียน",
            ],
        ];
    }

    /**
     * Auto-fill an entire Design Thinking step with rich contextual suggestions
     */
    public function generateStepAutoFill(DesignProject $project, string $step): array
    {
        $fieldsByStep = [
            'empathize' => ['target_learner', 'age_group', 'learner_characteristics', 'pain_points', 'learning_environment', 'device_availability'],
            'define' => ['problem_statement', 'expected_outcomes', 'knowledge_goals', 'skill_goals', 'attitude_goals'],
            'ideate' => ['game_concept', 'story', 'missions', 'challenges', 'rewards'],
            'prototype' => ['feedback_mechanisms', 'core_rules'],
            'test' => ['observations', 'feedback_summary'],
        ];

        $fields = $fieldsByStep[$step] ?? [];
        $result = [];

        foreach ($fields as $f) {
            $suggest = $this->generateFieldSuggestion($project, $step, $f);
            $result[$f] = $suggest['suggestion'];
        }

        return $result;
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
        $gameGenre = $project->game_genre ?: ($project->ideate?->game_genre ?: 'rpg_quest');
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
                            'actionText' => 'พร้อมแล้ว ลุยเลย!',
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
                'title' => 'ภารกิจเสร็จสิ้น!',
                'background' => 'victory_stage',
                'elements' => [
                    [
                        'id' => 'victory_card',
                        'type' => 'completion',
                        'title' => 'ยินดีด้วย คุณทำภารกิจสำเร็จแล้ว!',
                        'message' => "คุณได้ผ่านการทดสอบใน '{$title}' เรียบร้อยแล้ว ขอให้รักษาความตั้งใจนี้ไว้!",
                    ],
                ],
            ],
        ];

        $gameMode = $project->game_mode ?: 'single';
        $gameGenre = $project->game_genre ?: ($project->ideate?->game_genre ?: 'rpg_quest');

        // Mode and Genre specific configurations
        $modeSettings = [
            'duration' => $duration,
            'maxAttempts' => 3,
            'allowSound' => true,
            'passingScore' => 60,
        ];

        if ($gameMode === 'multiplayer_live') {
            $modeSettings['isLiveRoom'] = true;
            $modeSettings['countdownPerQuestion'] = 15;
            $modeSettings['streakMultiplier'] = true;
            $modeSettings['showLeaderboard'] = true;
            $modeSettings['kahootColors'] = ['#e21b3c', '#1368ce', '#d89e00', '#26890c'];

            if ($gameGenre === 'team_battle') {
                $modeSettings['teamMode'] = true;
                $modeSettings['teams'] = ['ทีมพญาอินทรี', 'ทีมมังกรทอง'];
                $modeSettings['castleHp'] = 500;
            } elseif ($gameGenre === 'battle_royale') {
                $modeSettings['survivalMode'] = true;
                $modeSettings['livesPerPlayer'] = 3;
                $modeSettings['suddenDeath'] = true;
            } elseif ($gameGenre === 'board_game_live') {
                $modeSettings['boardTiles'] = 9;
                $modeSettings['buzzerMode'] = true;
            }
        } else {
            $modeSettings['isLiveRoom'] = false;
            $modeSettings['selfPaced'] = true;

            if ($gameGenre === 'rpg_quest') {
                $modeSettings['turnBasedCombat'] = true;
                $modeSettings['playerHp'] = 100;
                $modeSettings['bossHp'] = 100;
                $modeSettings['combatSkills'] = ['เวทปัญญา', 'เกราะตรรกะ', 'ฟื้นฟูสมาธิ'];
            } elseif ($gameGenre === 'scenario_detective') {
                $modeSettings['investigationClues'] = 3;
                $modeSettings['evidenceNotebook'] = true;
                $modeSettings['witnessDialogue'] = true;
            } elseif ($gameGenre === 'sorting_dragdrop') {
                $modeSettings['dragDropCategories'] = 3;
                $modeSettings['puzzleRelaxMode'] = true;
            } elseif ($gameGenre === 'visual_novel') {
                $modeSettings['branchingStory'] = true;
                $modeSettings['multipleEndings'] = ['ยอดเยี่ยมนักปราชญ์', 'ผู้ช่วยกอบกู้เมือง', 'ลองใหม่อีกครั้ง'];
            }
        }

        return [
            'version' => '1.0',
            'title' => $title,
            'description' => $desc,
            'mode' => $gameMode,
            'theme' => $theme,
            'genre' => $gameGenre,
            'settings' => $modeSettings,
            'scenes' => $scenes,
            'scoring' => [
                'initialScore' => 0,
                'maxScore' => 100,
                'passingScore' => 60,
                'speedBonus' => ($gameMode === 'multiplayer_live'),
            ],
            'completion' => [
                'type' => $gameMode === 'multiplayer_live' ? 'podium_finish' : 'mission_complete',
                'rewardTitle' => $gameMode === 'multiplayer_live' ? 'Champion Cup' : 'Master Badge',
            ],
        ];
    }
}
