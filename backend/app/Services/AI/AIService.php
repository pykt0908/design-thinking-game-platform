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

        // Check if environment or teacher has configured an active API key
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey && $project->teacher_id) {
            $credential = TeacherAiCredential::where('teacher_id', $project->teacher_id)
                ->where('is_active', true)
                ->first();

            if ($credential && $credential->encrypted_api_key && !str_starts_with($credential->encrypted_api_key, 'mock-') && $credential->provider === 'gemini') {
                $apiKey = $credential->encrypted_api_key;
            }
        }

        if ($apiKey && !str_starts_with($apiKey, 'mock-')) {
            try {
                $llmResult = $this->callGeminiForFieldWithKey($apiKey, $project, $step, $field, $currentValue);
                if (!empty($llmResult['suggestion'])) {
                    return $llmResult;
                }
            } catch (\Throwable $e) {
                Log::warning("AI field generation via external LLM failed: " . $e->getMessage());
            }
        }

        // Contextual Pedagogical Engine
        return $this->generateContextualFieldFallback($project, $step, $field, $currentValue);
    }

    protected function callGeminiForFieldWithKey(string $apiKey, DesignProject $project, string $step, string $field, string $currentValue): array
    {
        $prompt = "คุณคือผู้เชี่ยวชาญด้าน Design Thinking และ Educational Game Design สำหรับครูระดับแนวหน้า\n";
        $prompt .= "โปรเจกต์: {$project->title}\nวิชา: {$project->subject}\nระดับชั้น: {$project->grade_level}\n";
        $prompt .= "ขั้นตอน Design Thinking: {$step}\nช่องข้อมูลที่ต้องการให้คิดคำตอบ: {$field}\n";
        if ($currentValue) {
            $prompt .= "ข้อความเดิมที่มีอยู่ (ต้องการข้อความใหม่และแนวคิดใหม่ที่ไม่ซ้ำเดิม): {$currentValue}\n";
        }
        $prompt .= "กรุณาเสนอข้อความภาษาไทยที่สร้างสรรค์ ตรงหลักสูตร เหมาะกับผู้เรียนวัยนี้ เขียนกระชับ สละสลวย นำไปใช้ได้ทันที\n";
        $prompt .= "ตอบกลับในรูปแบบ JSON:\n{\n  \"suggestion\": \"ข้อความหลักที่แนะนำ\",\n  \"alternatives\": [\"ตัวเลือกเพิ่มเติม 1\", \"ตัวเลือกเพิ่มเติม 2\"]\n}";

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-lite-latest:generateContent?key=" . $apiKey;
        $res = Http::withoutVerifying()->timeout(10)->post($url, [
            'contents' => [
                ['parts' => [['text' => $prompt]]],
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'temperature' => 0.85,
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

        return [];
    }

    protected function generateContextualFieldFallback(DesignProject $project, string $step, string $field, string $currentValue = ''): array
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
            $entry = $dict[$field];
            $candidates = array_merge([$entry['suggestion']], $entry['alternatives'] ?? []);

            // If currentValue exists, pick a different candidate so user never gets identical text
            $trimmedCurrent = trim($currentValue);
            $differentCandidates = array_values(array_filter($candidates, fn($opt) => trim($opt) !== $trimmedCurrent));

            if (!empty($differentCandidates)) {
                $chosen = $differentCandidates[array_rand($differentCandidates)];
            } else {
                $chosen = $candidates[array_rand($candidates)];
            }

            return [
                'field' => $field,
                'suggestion' => $chosen,
                'alternatives' => array_values(array_filter($candidates, fn($opt) => $opt !== $chosen)),
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

        // Check if project has existing relation loaded to pass current value
        $relation = $project->$step ?? null;

        foreach ($fields as $f) {
            $currentVal = $relation ? ($relation->$f ?? '') : '';
            $suggest = $this->generateFieldSuggestion($project, $step, $f, $currentVal);
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

        return $this->generateDirectGameSchema([
            'title' => $project->title ?: 'เกมการเรียนรู้',
            'description' => $project->description ?: 'เกมการเรียนรู้ผ่านกระบวนการ Design Thinking',
            'subject' => $project->subject ?: 'ทั่วไป',
            'grade_level' => $project->grade_level ?: 'ทุกระดับชั้น',
            'theme' => $project->ideate?->theme ?: 'school',
            'game_genre' => $project->game_genre ?: ($project->ideate?->game_genre ?: 'rpg_quest'),
            'game_mode' => $project->game_mode ?: 'single',
            'duration_minutes' => $project->ideate?->duration_minutes ?: 10,
            'objectives' => $project->define?->learning_objectives,
        ]);
    }

    /**
     * Generate complete Game Schema directly without requiring a Design Thinking Project
     */
    public function generateDirectGameSchema(array $params): array
    {
        $title = $params['title'] ?? 'เกมการเรียนรู้';
        $desc = $params['description'] ?? 'เกมการเรียนรู้เชิงโต้ตอบ';
        $subject = $params['subject'] ?? 'ทั่วไป';
        $grade = $params['grade_level'] ?? 'ทุกระดับชั้น';
        $theme = $params['theme'] ?? 'school';
        $gameGenre = $params['game_genre'] ?? $params['genre'] ?? 'rpg_quest';
        $gameMode = $params['game_mode'] ?? $params['mode'] ?? 'single';
        $duration = ($params['duration_minutes'] ?? 10) * 60;
        $questionCount = max(2, min(10, (int)($params['question_count'] ?? 3)));

        $themeConfigs = [
            'school' => [
                'bg_intro' => 'school_campus',
                'bg_challenge' => 'learning_room',
                'bg_victory' => 'victory_stage',
                'avatar' => 'https://api.dicebear.com/7.x/bottts/svg?seed=TeacherAlex',
                'guide_name' => 'อาจารย์ผู้ดูแลภารกิจ',
            ],
            'space' => [
                'bg_intro' => 'space_station',
                'bg_challenge' => 'cosmic_bridge',
                'bg_victory' => 'galaxy_podium',
                'avatar' => 'https://api.dicebear.com/7.x/bottts/svg?seed=CaptainCosmo',
                'guide_name' => 'กัปตันคอสโม',
            ],
            'fantasy' => [
                'bg_intro' => 'ancient_castle',
                'bg_challenge' => 'mystic_library',
                'bg_victory' => 'crystal_hall',
                'avatar' => 'https://api.dicebear.com/7.x/bottts/svg?seed=WizardMerlin',
                'guide_name' => 'จอมเวทผู้พิทักษ์',
            ],
            'science' => [
                'bg_intro' => 'high_tech_lab',
                'bg_challenge' => 'quantum_chamber',
                'bg_victory' => 'science_podium',
                'avatar' => 'https://api.dicebear.com/7.x/bottts/svg?seed=DrAtom',
                'guide_name' => 'ดร.ควอนตัม',
            ],
        ];

        $themeCfg = $themeConfigs[$theme] ?? $themeConfigs['school'];

        $scenes = [
            [
                'id' => 'scene_intro',
                'title' => 'บทนำสู่ภารกิจการเรียนรู้',
                'background' => $themeCfg['bg_intro'],
                'elements' => [
                    [
                        'id' => 'char_guide',
                        'type' => 'character',
                        'name' => $themeCfg['guide_name'],
                        'avatar' => $themeCfg['avatar'],
                        'x' => 20,
                        'y' => 30,
                        'dialogue' => [
                            'speaker' => $themeCfg['guide_name'],
                            'text' => "ยินดีต้อนรับสู่ '{$title}'! ในภารกิจนี้คุณจะได้ฝึกฝนทักษะในวิชา {$subject} เพื่อพิสูจน์ความเชี่ยวชาญ พร้อมเริ่มภารกิจหรือยัง?",
                            'actionText' => 'พร้อมแล้ว ลุยเลย!',
                            'nextScene' => 'scene_challenge_1',
                        ],
                    ],
                ],
            ],
        ];

        $questionTemplates = [
            [
                'title' => 'ภารกิจที่ 1: ความรู้พื้นฐานและการสังเกต',
                'question' => "ในหัวข้อ '{$title}' ({$subject}) ข้อใดอธิบายเป้าหมายหรือหลักการสำคัญได้ถูกต้องที่สุด?",
                'image' => 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=500&auto=format&fit=crop&q=60',
                'options' => [
                    ['id' => 'q1_o1', 'text' => "ทำความเข้าใจบริบทและข้อเท็จจริงสำคัญของ {$title}", 'isCorrect' => true],
                    ['id' => 'q1_o2', 'text' => 'ตัดสินใจโดยอิงจากความคาดเดาและไม่ตรวจสอบข้อเท็จจริง', 'isCorrect' => false],
                    ['id' => 'q1_o3', 'text' => 'หลีกเลี่ยงการค้นคว้าและตั้งสมมติฐาน', 'isCorrect' => false],
                    ['id' => 'q1_o4', 'text' => 'ไม่มีข้อใดเป็นหลักการที่ถูกต้อง', 'isCorrect' => false],
                ],
                'explanation' => "ยอดเยี่ยมมาก! การเข้าใจแก่นความรู้ของ {$title} เป็นรากฐานสำคัญในการต่อยอดแก้ปัญหา",
                'points' => 30,
            ],
            [
                'title' => 'ภารกิจที่ 2: การวิเคราะห์และการประยุกต์ใช้',
                'question' => "เมื่อพบสถานการณ์ที่ท้าทายใน {$subject} เกี่ยวกับ {$title} ควรดำเนินการอย่างไรเป็นลำดับแรก?",
                'image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=500&auto=format&fit=crop&q=60',
                'options' => [
                    ['id' => 'q2_o1', 'text' => 'วิเคราะห์สาเหตุและทำความเข้าใจปัญหาอย่างเป็นระบบ', 'isCorrect' => true],
                    ['id' => 'q2_o2', 'text' => 'รีบลงมือทำทันทีโดยไม่วางแผนหรือประเมินความเสี่ยง', 'isCorrect' => false],
                    ['id' => 'q2_o3', 'text' => 'ละเลยปัญหาและหลีกเลี่ยงการร่วมมือกับผู้อื่น', 'isCorrect' => false],
                    ['id' => 'q2_o4', 'text' => 'หยุดการดำเนินงานทั้งหมดทันที', 'isCorrect' => false],
                ],
                'explanation' => "ถูกต้อง! การวิเคราะห์สถานการณ์อย่างเป็นระบบช่วยให้เราแก้ไขปัญหาได้ตรงจุด",
                'points' => 35,
            ],
            [
                'title' => 'ภารกิจที่ 3: การประเมินผลและการพัฒนาอย่างต่อเนื่อง',
                'question' => "ขั้นตอนใดช่วยส่งเสริมให้เกิดความสำเร็จสูงสุดในการเรียนรู้ {$subject} เรื่อง {$title}?",
                'image' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=500&auto=format&fit=crop&q=60',
                'options' => [
                    ['id' => 'q3_o1', 'text' => 'นำข้อเสนอแนะและผลลัพธ์มาปรับปรุงอย่างต่อเนื่อง (Iteration)', 'isCorrect' => true],
                    ['id' => 'q3_o2', 'text' => 'ยึดติดกับวิธีการเดิมโดยไม่เปิดรับคำติชม', 'isCorrect' => false],
                    ['id' => 'q3_o3', 'text' => 'เลือกรับฟังเฉพาะคำชมและเพิกเฉยต่อจุดบกพร่อง', 'isCorrect' => false],
                    ['id' => 'q3_o4', 'text' => 'ยกเลิกการพัฒนาเมื่อพบข้อผิดพลาดครั้งแรก', 'isCorrect' => false],
                ],
                'explanation' => "ถูกต้องที่สุด! กระบวนการทดสอบและปรับปรุงอย่างต่อเนื่องทำให้ผลงานมีประสิทธิภาพสูงสุด",
                'points' => 35,
            ],
            [
                'title' => 'ภารกิจที่ 4: ความคิดสร้างสรรค์และนวัตกรรม',
                'question' => "วิธีการใดช่วยส่งเสริมให้เกิดไอเดียใหม่ๆ ในการเรียนรู้ {$subject} เรื่อง {$title} ได้ดีที่สุด?",
                'image' => 'https://images.unsplash.com/photo-1531403009284-440f080d1e12?w=500&auto=format&fit=crop&q=60',
                'options' => [
                    ['id' => 'q4_o1', 'text' => 'การเปิดรับมุมมองที่หลากหลายและแลกเปลี่ยนความคิดเห็นกับผู้อื่น', 'isCorrect' => true],
                    ['id' => 'q4_o2', 'text' => 'การจำกัดความคิดให้อยู่ในกรอบเดิมๆ เพียงวิธีเดียว', 'isCorrect' => false],
                    ['id' => 'q4_o3', 'text' => 'การปฏิเสธแนวคิดที่ไม่คุ้นเคยตั้งแต่เริ่มต้น', 'isCorrect' => false],
                    ['id' => 'q4_o4', 'text' => 'การห้ามตั้งคำถามต่อสิ่งที่มีอยู่เดิม', 'isCorrect' => false],
                ],
                'explanation' => "สุดยอด! การระดมความคิดและเปิดกว้างสร้างโอกาสให้นวัตกรรมเกิดขึ้นได้เสมอ",
                'points' => 30,
            ],
            [
                'title' => 'ภารกิจที่ 5: การสรุปและการเชื่อมโยงสู่ชีวิตจริง',
                'question' => "ประโยชน์สูงสุดของการเรียนรู้เรื่อง {$title} ที่สามารถนำไปใช้ในชีวิตประจำวันคือข้อใด?",
                'image' => 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=500&auto=format&fit=crop&q=60',
                'options' => [
                    ['id' => 'q5_o1', 'text' => 'การนำความรู้ไปประยุกต์ใช้เพื่อพัฒนาตนเองและช่วยเหลือผู้อื่น', 'isCorrect' => true],
                    ['id' => 'q5_o2', 'text' => 'การเก็บข้อมูลไว้ใช้เพียงเพื่อการสอบเท่านั้น', 'isCorrect' => false],
                    ['id' => 'q5_o3', 'text' => 'การนำไปแข่งขันเพื่อเอาชนะผู้อื่นโดยไม่คำนึงถึงความถูกต้อง', 'isCorrect' => false],
                    ['id' => 'q5_o4', 'text' => 'ไม่มีประโยชน์ที่สามารถเชื่อมโยงกับการดำเนินชีวิตได้', 'isCorrect' => false],
                ],
                'explanation' => "ถูกต้องที่สุด! การเรียนรู้ที่มีคุณค่าคือการนำไปสร้างประโยชน์ให้สังคมและตนเอง",
                'points' => 30,
            ],
        ];

        for ($i = 0; $i < $questionCount; $i++) {
            $idx = $i + 1;
            $qTemplate = $questionTemplates[$i % count($questionTemplates)];
            $nextSceneId = ($i === $questionCount - 1) ? 'scene_finish' : 'scene_challenge_' . ($i + 2);

            $scenes[] = [
                'id' => "scene_challenge_{$idx}",
                'title' => $qTemplate['title'],
                'background' => $themeCfg['bg_challenge'],
                'elements' => [
                    [
                        'id' => "quiz_{$idx}",
                        'type' => 'question',
                        'questionType' => 'multiple_choice',
                        'question' => $qTemplate['question'],
                        'image' => $qTemplate['image'],
                        'options' => $qTemplate['options'],
                        'points' => $qTemplate['points'],
                        'explanation' => $qTemplate['explanation'],
                        'nextScene' => $nextSceneId,
                    ],
                ],
            ];
        }

        $scenes[] = [
            'id' => 'scene_finish',
            'title' => 'ภารกิจเสร็จสิ้น!',
            'background' => $themeCfg['bg_victory'],
            'elements' => [
                [
                    'id' => 'victory_card',
                    'type' => 'completion',
                    'title' => 'ยินดีด้วย คุณทำภารกิจสำเร็จแล้ว!',
                    'message' => "คุณได้พิชิตเกม '{$title}' ในวิชา {$subject} เรียบร้อยแล้ว ขอให้ภูมิใจในความพยายามของคุณ!",
                ],
            ],
        ];

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

    /**
     * Generate step data on the fly from input parameters (without existing DB project)
     */
    /**
     * Generate step data on the fly from input parameters (without existing DB project)
     */
    public function generateStepFromParams(string $step, array $params, ?int $teacherId = null): array
    {
        $apiKey = null;
        $provider = 'gemini';
        $model = 'gemini-flash-lite-latest';
        $baseUrl = null;

        if (!empty($params['api_key']) && !str_starts_with($params['api_key'], 'mock-')) {
            $apiKey = trim($params['api_key']);
            if (str_starts_with($apiKey, 'sk-')) {
                $provider = 'openai';
                $model = 'gpt-4o-mini';
            }
        } else {
            $query = TeacherAiCredential::where('is_active', true)
                ->whereNotNull('encrypted_api_key')
                ->where('encrypted_api_key', '!=', '')
                ->where('encrypted_api_key', '!=', 'existing');

            $cred = $teacherId ? (clone $query)->where('teacher_id', $teacherId)->first() : null;
            if (!$cred) {
                $cred = $query->orderBy('updated_at', 'desc')->first();
            }

            if ($cred && !str_starts_with($cred->encrypted_api_key, 'mock-')) {
                $apiKey = $cred->encrypted_api_key;
                $provider = $cred->provider;
                $model = $cred->model ?: ($provider === 'openai' ? 'gpt-4o-mini' : 'gemini-flash-lite-latest');
                $baseUrl = $cred->base_url;
            } else {
                $apiKey = env('GEMINI_API_KEY');
            }
        }

        if ($apiKey && !str_starts_with($apiKey, 'mock-')) {
            try {
                if ($provider === 'openai' || str_starts_with($apiKey, 'sk-')) {
                    $llmRes = $this->callOpenAIForDirectStepWithKey($apiKey, $step, $params, $model ?: 'gpt-4o-mini', $baseUrl);
                } else {
                    $llmRes = $this->callGeminiForDirectStepWithKey($apiKey, $step, $params);
                }

                if (!empty($llmRes)) {
                    return $llmRes;
                }
            } catch (\Throwable $e) {
                Log::warning("Direct LLM step generation ({$provider}) failed: " . $e->getMessage());
            }
        }

        return $this->generateContextualStepDirectFallback($step, $params);
    }

    protected function callOpenAIForDirectStepWithKey(string $apiKey, string $step, array $params, string $model = 'gpt-4o-mini', ?string $baseUrl = null): array
    {
        $title = $params['title'] ?? 'เกมการเรียนรู้';
        $grade = $params['grade_level'] ?? 'มัธยมศึกษาตอนต้น';
        $genre = $params['genre'] ?? 'custom';

        $prompt = "คุณคือผู้เชี่ยวชาญด้าน Design Thinking และ Educational Game Design\n"
            . "หัวข้อ: \"{$title}\" | กลุ่มเป้าหมาย: {$grade} | แนวเกม: {$genre}\n"
            . "ขั้นตอนที่ต้องการให้คิด: \"{$step}\"\n"
            . "ตอบกลับเป็น JSON เท่านั้น:";

        $url = rtrim($baseUrl ?: 'https://api.openai.com/v1', '/') . '/chat/completions';

        $res = Http::withoutVerifying()->timeout(15)->withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type' => 'application/json',
        ])->post($url, [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.7,
        ]);

        if ($res->successful()) {
            $raw = $res->json('choices.0.message.content');
            $parsed = json_decode($raw, true);
            if (is_array($parsed)) {
                return $parsed;
            }
        }

        return [];
    }

    /**
     * Generate all 5 Design Thinking steps on the fly
     */
    public function generateAllStepsFromParams(array $params, ?int $teacherId = null): array
    {
        return [
            'empathize' => $this->generateStepFromParams('empathize', $params, $teacherId),
            'define' => $this->generateStepFromParams('define', $params, $teacherId),
            'ideate' => $this->generateStepFromParams('ideate', $params, $teacherId),
            'prototype' => $this->generateStepFromParams('prototype', $params, $teacherId),
            'test' => $this->generateStepFromParams('test', $params, $teacherId),
        ];
    }

    protected function callGeminiForDirectStepWithKey(string $apiKey, string $step, array $params): array
    {
        $title = $params['title'] ?? 'เกมการเรียนรู้';
        $subject = $params['subject'] ?? 'ทั่วไป';
        $grade = $params['grade_level'] ?? 'มัธยมศึกษาตอนต้น';
        $genre = $params['genre'] ?? 'custom';

        $prompt = "คุณคือผู้เชี่ยวชาญด้าน Design Thinking และ Educational Game Design ระดับแนวหน้า\n";
        $prompt .= "หัวข้อเกม/บทเรียน: \"{$title}\" | กลุ่มเป้าหมาย: {$grade} | แนวเกม: {$genre}\n";
        $prompt .= "ขั้นตอน Design Thinking ที่ต้องการให้คิด: \"{$step}\"\n";
        $prompt .= "คำสั่ง: ให้สร้างเนื้อหาภาษาไทยที่สดใหม่ สร้างสรรค์ และเจาะจงกับหัวข้อ \"{$title}\" โดยตรง ห้ามตอบข้อความทั่วไป\n";
        $prompt .= "ตอบกลับเป็น JSON เท่านั้น:\n";

        if ($step === 'empathize') {
            $prompt .= '{"target_learner": "...", "age_group": "...", "learner_characteristics": "...", "pain_points": "...", "learning_environment": "..."}';
        } elseif ($step === 'define') {
            $prompt .= '{"problem_statement": "...", "expected_outcomes": "...", "knowledge_goals": "...", "skill_goals": "..."}';
        } elseif ($step === 'ideate') {
            $prompt .= '{"game_concept": "...", "genre": "' . $genre . '", "story": "...", "prompt": "...", "missions": "...", "challenges": "..."}';
        } elseif ($step === 'prototype') {
            $prompt .= '{"recommended_features": ["health_bar", "scoreboard", "map", ...]}';
        } else {
            $prompt .= '{"observations": "...", "feedback_summary": "..."}';
        }

        $targetModel = $model ?: 'gemini-flash-lite-latest';
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$targetModel}:generateContent?key=" . $apiKey;
        $res = Http::withoutVerifying()->timeout(12)->post($url, [
            'contents' => [
                ['parts' => [['text' => $prompt]]],
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'temperature' => 0.85,
            ],
        ]);

        if ($res->successful()) {
            $raw = $res->json('candidates.0.content.parts.0.text');
            $parsed = json_decode($raw, true);
            if (is_array($parsed)) {
                return $parsed;
            }
        }

        return [];
    }

    protected function generateContextualStepDirectFallback(string $step, array $params): array
    {
        $title = trim($params['title'] ?? '') ?: 'บทเรียนสร้างสรรค์สู่เกมการเรียนรู้';
        $subject = trim($params['subject'] ?? '') ?: 'ทั่วไป';
        $grade = trim($params['grade_level'] ?? '') ?: 'มัธยมศึกษาตอนต้น';
        $genre = trim($params['genre'] ?? '') ?: 'custom';

        $rawText = mb_strtolower($title . ' ' . $genre . ' ' . ($params['prompt'] ?? ''));

        // Topic Domain Classifiers
        $isMath = str_contains($rawText, 'คณิต') || str_contains($rawText, 'เศษส่วน') || str_contains($rawText, 'สมการ') || str_contains($rawText, 'บวก') || str_contains($rawText, 'ลบ') || str_contains($rawText, 'คูณ') || str_contains($rawText, 'หาร') || str_contains($rawText, 'ตัวเลข') || str_contains($rawText, 'math');
        $isSpace = str_contains($rawText, 'อวกาศ') || str_contains($rawText, 'ยาน') || str_contains($rawText, 'ดาว') || str_contains($rawText, 'สุริยะ') || str_contains($rawText, 'space') || str_contains($rawText, 'galaxy');
        $isSci = str_contains($rawText, 'วิทย์') || str_contains($rawText, 'เคมี') || str_contains($rawText, 'ฟิสิกส์') || str_contains($rawText, 'ชีวะ') || str_contains($rawText, 'พลังงาน') || str_contains($rawText, 'ไฟฟ้า') || str_contains($rawText, 'science') || $isSpace;
        $isWord = str_contains($rawText, 'คำศัพท์') || str_contains($rawText, 'อังกฤษ') || str_contains($rawText, 'ภาษา') || str_contains($rawText, 'สะกด') || str_contains($rawText, 'แปล') || str_contains($rawText, 'word') || str_contains($rawText, 'vocab') || str_contains($rawText, 'english');
        $isFarm = str_contains($rawText, 'ฟาร์ม') || str_contains($rawText, 'ผัก') || str_contains($rawText, 'ปลูก') || str_contains($rawText, 'เกษตร') || str_contains($rawText, 'ดิน') || str_contains($rawText, 'อาหาร') || str_contains($rawText, 'farm') || str_contains($rawText, 'crop');
        $isHistory = str_contains($rawText, 'ประวัติศาสตร์') || str_contains($rawText, 'สุโขทัย') || str_contains($rawText, 'อยุธยา') || str_contains($rawText, 'สังคม') || str_contains($rawText, 'โบราณ') || str_contains($rawText, 'วัฒนธรรม') || str_contains($rawText, 'history');
        $isRunner = str_contains($rawText, 'วิ่ง') || str_contains($rawText, 'หลบ') || str_contains($rawText, 'รีไซเคิล') || str_contains($rawText, 'ขยะ') || str_contains($rawText, 'runner') || str_contains($rawText, 'endless');

        // Random variation index (0, 1, 2) to ensure fresh text on every single press!
        $seed = random_int(0, 2);

        if ($step === 'empathize') {
            if ($isMath) {
                $opts = [
                    [
                        'target_learner' => "นักเรียนระดับ{$grade} ที่กำลังศึกษาเรื่อง '{$title}' และต้องการความมั่นใจในการคำนวณ",
                        'age_group' => '11 - 15 ปี (วัยกำลังพัฒนาทักษะตรรกะ)',
                        'learner_characteristics' => "ชอบความท้าทาย สนุกกับการประลองความเร็ว ตอบโจทย์เพื่อปลดปล่อยพลังสกิลและอัปเกรดตัวละคร",
                        'pain_points' => "มองว่าสูตรและตัวเลขในเรื่อง '{$title}' เป็นเรื่องนามธรรม เครียดเมื่อถูกจับเวลา และกลัวการคิดคำนวณผิด",
                        'learning_environment' => "เล่นผ่านแท็บเล็ตหรือคอมพิวเตอร์ในห้องเรียนคณิตศาสตร์ หรือฝึกทบทวนรายบุคคลที่บ้าน",
                    ],
                    [
                        'target_learner' => "กลุ่มผู้เรียน{$grade} ที่มีพื้นฐานปานกลางและต้องการเปลี่ยนทัศนคติต่อวิชาคณิตศาสตร์เรื่อง '{$title}'",
                        'age_group' => '10 - 14 ปี',
                        'learner_characteristics' => "ตื่นตัวกับระบบคอมโบ (Combo Streak) และเสียงตอบสนองเมื่อคิดคำตอบได้ถูกต้องอย่างรวดเร็ว",
                        'pain_points' => "เบื่อแบบฝึกหัดในสมุด มักสับสนขั้นตอนการหาคำตอบของ '{$title}' และขาดแรงจูงใจในการฝึกฝนซ้ำๆ",
                        'learning_environment' => "กิจกรรมกลุ่มหรือมินิเกมเปิดคาบเรียน (Warm-up Activity) เพื่อกระตุ้นสมอง",
                    ],
                    [
                        'target_learner' => "นักเรียน{$grade} ที่ชอบการเล่นเกมแนว Action หรือ Puzzle และต้องการฝึกทักษะคณิตศาสตร์ '{$title}' ไปพร้อมกัน",
                        'age_group' => '12 - 16 ปี',
                        'learner_characteristics' => "ชอบการสะสมแต้ม การเอาชนะบอส และการใช้ไอเทมช่วยเหลือเมื่อเจอปัญหายาก",
                        'pain_points' => "เมื่อคิดไม่ออกมักยอมแพ้ง่าย ขาดการแนะแนวขั้นตอนคิด (Step-by-step guidance) ในเนื้อหา '{$title}'",
                        'learning_environment' => "สมาร์ตโฟนหรือคอมพิวเตอร์พกพา รองรับการเล่นได้ทุกที่",
                    ],
                ];
                return $opts[$seed];
            } elseif ($isSci) {
                $opts = [
                    [
                        'target_learner' => "นักเรียนระดับ{$grade} ในหัวข้อวิทยาศาสตร์ '{$title}' ที่ชื่นชอบการสำรวจและทดลองเสมือนจริง",
                        'age_group' => '11 - 15 ปี',
                        'learner_characteristics' => "ชอบสำรวจฉาก กราฟิกภาพจำลองการทำงาน และการเก็บไอเทมเพื่อไขความลับในธรรมชาติ",
                        'pain_points' => "ทฤษฎีและคำศัพท์วิทยาศาสตร์ในเรื่อง '{$title}' มีความซับซ้อน มักจำสับสนและไม่เห็นภาพการทำงานจริง",
                        'learning_environment' => "ห้องเรียนปฏิบัติการวิทยาศาสตร์ หรือการทดลองเสมือนจริง (Virtual Lab) บนเบราว์เซอร์",
                    ],
                    [
                        'target_learner' => "ผู้เรียน{$grade} ที่ต้องการเข้าใจปรากฏการณ์ในเรื่อง '{$title}' ผ่านการลงมือปฏิบัติด้วยตนเอง",
                        'age_group' => '10 - 15 ปี',
                        'learner_characteristics' => "มีสมาธิสูงเมื่อมีภาพแอนิเมชันและเอฟเฟกต์วิทยาศาสตร์ประกอบการเรียนรู้",
                        'pain_points' => "การอ่านตำราเพียงอย่างเดียวทำให้ไม่เข้าใจความเชื่อมโยงของปัจจัยต่างๆ ใน '{$title}'",
                        'learning_environment' => "สมาร์ตโฟน แท็บเล็ต หรือจอสัมผัสในห้องเรียนอัจฉริยะ",
                    ],
                    [
                        'target_learner' => "เยาวชนวัยเรียน{$grade} ที่ชอบแนวไซไฟ อวกาศ และเทคโนโลยี ในการเรียนรู้ '{$title}'",
                        'age_group' => '12 - 16 ปี',
                        'learner_characteristics' => "ชอบภารกิจกู้โลก กู้สถานีวิจัย และการแก้ปริศนาตามหลักการทางวิทยาศาสตร์",
                        'pain_points' => "ขาดประสบการณ์เชื่อมโยงเนื้อหา '{$title}' กับการนำไปประยุกต์ใช้ในเทคโนโลยีจริง",
                        'learning_environment' => "เล่นเดี่ยวแบบทบทวน หรือแข่งขันทำแต้มกับเพื่อนร่วมชั้น",
                    ],
                ];
                return $opts[$seed];
            } elseif ($isWord) {
                $opts = [
                    [
                        'target_learner' => "ผู้เรียนระดับ{$grade} ที่ต้องการฝึกฝนและจดจำคำศัพท์ในหัวข้อ '{$title}' อย่างเป็นธรรมชาติ",
                        'age_group' => '9 - 14 ปี',
                        'learner_characteristics' => "ชอบความตื่นเต้น สนุกกับการค้นหาตัวอักษรและไอเทมคำศัพท์ที่ซ่อนอยู่ในแผนที่",
                        'pain_points' => "ท่องศัพท์แบบเดิมๆ แล้วลืมเร็ว ไม่คุ้นเคยกับการสะกดและการเลือกใช้คำในบริบทที่ถูกต้อง",
                        'learning_environment' => "สมาร์ตโฟนหรือแท็บเล็ตในกิจกรรม Warm-up หรือการเรียนรู้ภาษาแบบส่วนตัว",
                    ],
                    [
                        'target_learner' => "กลุ่มนักเรียน{$grade} ที่ต้องการเพิ่มพูนคลังคำศัพท์และไวยากรณ์ผ่านการผจญภัยในเรื่อง '{$title}'",
                        'age_group' => '10 - 15 ปี',
                        'learner_characteristics' => "ชอบระบบคอมโบสตรีค และการได้รับเหรียญรางวัลเมื่อสะกดคำได้อย่างแม่นยำต่อเนื่อง",
                        'pain_points' => "กังวลเรื่องการสะกดผิด ขาดความมั่นใจ และรู้สึกว่าการเรียนคำศัพท์เป็นเรื่องน่าเบื่อ",
                        'learning_environment' => "เล่นผ่านเว็บเบราว์เซอร์ ทั้งในคาบเรียนภาษาและทบทวนที่บ้าน",
                    ],
                    [
                        'target_learner' => "ผู้เรียนทุกระดับที่สนใจการพัฒนาทักษะภาษาผ่านเกมภารกิจล่าคำศัพท์ '{$title}'",
                        'age_group' => '8 - 15 ปี',
                        'learner_characteristics' => "ชอบภาพประกอบ 2D สดใส เสียงเอฟเฟกต์แจ้งเตือน และการปลดล็อกด่านคำศัพท์ใหม่ๆ",
                        'pain_points' => "ขาดสถานการณ์จำลองที่ต้องใช้ความไวในการดึงความทรงจำคำศัพท์มาใช้งาน",
                        'learning_environment' => "ทุกอุปกรณ์ที่รองรับ HTML5",
                    ],
                ];
                return $opts[$seed];
            } elseif ($isFarm) {
                $opts = [
                    [
                        'target_learner' => "ผู้เรียนระดับ{$grade} ในวิชาการเกษตรและสิ่งแวดล้อม เรื่อง '{$title}'",
                        'age_group' => '10 - 15 ปี',
                        'learner_characteristics' => "ชอบความผ่อนคลายของการทำฟาร์ม การรดน้ำพรวนดิน และความสุขจากการเก็บเกี่ยวผลผลิต",
                        'pain_points' => "ไม่เคยลงมือปลูกพืชจริง ขาดความเข้าใจเรื่องปัจจัยดิน น้ำ แสง และวิธีจัดการศัตรูพืช",
                        'learning_environment' => "สมาร์ตโฟน แท็บเล็ต หรือคอมพิวเตอร์ในห้องเรียน",
                    ],
                    [
                        'target_learner' => "นักเรียน{$grade} ที่สนใจการเรียนรู้เรื่องระบบนิเวศและเกษตรอินทรีย์ผ่าน '{$title}'",
                        'age_group' => '9 - 14 ปี',
                        'learner_characteristics' => "ชอบการวางแผนจัดสรรเวลา การสะสมเมล็ดพันธุ์ และการบริหารทรัพยากรในร้านค้า",
                        'pain_points' => "ทฤษฎีการเกษตรมีความซ้ำซาก ขาดความสนุกและการมีปฏิสัมพันธ์ที่เห็นผลทันตา",
                        'learning_environment' => "กิจกรรมบูรณาการวิทยาศาสตร์และการงานอาชีพ",
                    ],
                    [
                        'target_learner' => "เยาวชนที่ต้องการเข้าใจกระบวนการผลิตอาหารปลอดภัยและการดูแลสิ่งแวดล้อมใน '{$title}'",
                        'age_group' => '11 - 16 ปี',
                        'learner_characteristics' => "ชอบการอัปเกรดเครื่องมือ การต่อสู้ป้องกันฟาร์มจากศัตรูพืช และการทำสถิติคะแนน",
                        'pain_points' => "ขาดการมองเห็นภาพรวมของวงจรชีวิตพืชและปัจจัยชีวภาพในชีวิตประจำวัน",
                        'learning_environment' => "เล่นผ่านหน้าจอเบราว์เซอร์ทั้งที่โรงเรียนและที่บ้าน",
                    ],
                ];
                return $opts[$seed];
            } elseif ($isHistory) {
                $opts = [
                    [
                        'target_learner' => "นักเรียนระดับ{$grade} ในวิชาประวัติศาสตร์และสังคมศึกษา เรื่อง '{$title}'",
                        'age_group' => '11 - 15 ปี',
                        'learner_characteristics' => "ชอบเรื่องราวการผจญภัยย้อนเวลา ค้นหาโบราณวัตถุ และการสวมบทบาทเป็นบุคคลในประวัติศาสตร์",
                        'pain_points' => "มองว่าประวัติศาสตร์เป็นเรื่องของการจำ พ.ศ. ชื่อบุคคล และเหตุการณ์ ซึ่งน่าเบื่อและลืมง่าย",
                        'learning_environment' => "ห้องเรียนสังคมศึกษา หรือการเรียนรู้ประวัติศาสตร์นอกห้องเรียน",
                    ],
                    [
                        'target_learner' => "กลุ่มผู้เรียน{$grade} ที่ต้องการเข้าใจสาเหตุและผลกระทบของเหตุการณ์สำคัญใน '{$title}'",
                        'age_group' => '12 - 16 ปี',
                        'learner_characteristics' => "ชอบการสืบสวนหาหลักฐาน การปะติดปะต่อเบาะแส และการแก้ปริศนาในยุคโบราณ",
                        'pain_points' => "ขาดความรู้สึกร่วม (Empathy) กับผู้คนในอดีต และไม่เห็นว่าเหตุการณ์เชื่อมโยงกับปัจจุบันอย่างไร",
                        'learning_environment' => "คอมพิวเตอร์หรือแท็บเล็ตในรูปแบบเควสต์ผจญภัย",
                    ],
                    [
                        'target_learner' => "นักเรียน{$grade} ที่สนใจมรดกทางวัฒนธรรมและบทเรียนจากประวัติศาสตร์ '{$title}'",
                        'age_group' => '10 - 15 ปี',
                        'learner_characteristics' => "ชอบแผนที่โบราณ การเดินสำรวจซากปรักหักพัง และการสะสมโบราณวัตถุในกระเป๋า",
                        'pain_points' => "เนื้อหาในตำรามีตัวหนังสือยาว ขาดภาพประกอบและเรื่องเล่าที่ชวนติดตาม",
                        'learning_environment' => "การเรียนรู้แบบมีปฏิสัมพันธ์ผ่านสื่อดิจิทัล",
                    ],
                ];
                return $opts[$seed];
            } else {
                // Dynamic Contextual Synthesis for Any Custom Topic!
                $opts = [
                    [
                        'target_learner' => "กลุ่มผู้เรียนระดับ{$grade} ในรายวิชา{$subject} ที่กำลังศึกษาเรื่อง '{$title}'",
                        'age_group' => '10 - 16 ปี (วัยรุ่นและผู้เรียนทั่วไป)',
                        'learner_characteristics' => "ตอบสนองได้ดีต่อเกมที่มีภาพสดใส มีภารกิจท้าทายชัดเจน และได้รับเสียงตอบรับทันทีเมื่อตัดสินใจถูกต้อง",
                        'pain_points' => "เนื้อหาเรื่อง '{$title}' มีรายละเอียดเยอะ ท่องจำเป็นนามธรรม ทำให้ไม่เห็นภาพการนำไปใช้จริง",
                        'learning_environment' => "เล่นผ่านอุปกรณ์สมาร์ตโฟน แท็บเล็ต หรือคอมพิวเตอร์ในห้องเรียนและที่บ้าน",
                    ],
                    [
                        'target_learner' => "นักเรียน{$grade} ที่ต้องการเสริมสร้างทักษะและความเข้าใจเชิงลึกในหัวข้อ '{$title}'",
                        'age_group' => '11 - 15 ปี',
                        'learner_characteristics' => "ชอบการเก็บคะแนนสะสม การปลดล็อกเลเวลใหม่ และการแข่งขันกับสถิติตนเองอย่างสร้างสรรค์",
                        'pain_points' => "ขาดแรงจูงใจในการทบทวนเนื้อหา '{$title}' ซ้ำๆ เมื่อใช้วิธีการทำแบบฝึกหัดกระดาษแบบดั้งเดิม",
                        'learning_environment' => "กิจกรรมเสริมทักษะในชั้นเรียน หรือการบ้านรูปแบบเกม (Gamified Homework)",
                    ],
                    [
                        'target_learner' => "ผู้เรียน{$grade} ที่ต้องการฝึกการคิดวิเคราะห์และการแก้ปัญหาเฉพาะหน้าในเรื่อง '{$title}'",
                        'age_group' => '12 - 16 ปี',
                        'learner_characteristics' => "ชอบการวางแผนกลยุทธ์ การบริหารพลังชีวิต/เวลา และการพิชิตด่านที่มีระดับความยากเพิ่มขึ้น",
                        'pain_points' => "รู้สึกว่าเนื้อหา '{$title}' ไกลตัว และไม่ได้รับคำแนะนำทันทีเมื่อเกิดข้อผิดพลาดในการเรียนรู้",
                        'learning_environment' => "ห้องเรียนดิจิทัลและการเรียนรู้แบบผสมผสาน (Blended Learning)",
                    ],
                ];
                return $opts[$seed];
            }
        }

        if ($step === 'define') {
            $opts = [
                [
                    'problem_statement' => "ผู้เรียนระดับ{$grade} ประสบปัญหาขาดความเข้าใจอย่างลึกซึ้งในเรื่อง '{$title}' และไม่สามารถเชื่อมโยงทฤษฎีสู่การปฏิบัติจริงได้ จึงต้องการเกมการเรียนรู้ที่เปิดโอกาสให้ลงมือทำและลองผิดลองถูกอย่างปลอดภัย",
                    'expected_outcomes' => "ผู้เรียนสามารถจำแนกหลักการสำคัญของ '{$title}' และผ่านเกณฑ์การทดสอบในเกมได้ถูกต้องไม่น้อยกว่า 80%",
                    'knowledge_goals' => "เข้าใจความหมาย องค์ประกอบสำคัญ และหลักการพื้นฐานที่จำเป็นของเรื่อง '{$title}'",
                    'skill_goals' => "ทักษะการคิดวิเคราะห์ การตัดสินใจอย่างรวดเร็ว และการแก้ปัญหาเฉพาะหน้าตามสถานการณ์",
                ],
                [
                    'problem_statement' => "นักเรียนมักท่องจำเนื้อหา '{$title}' โดยไม่เห็นภาพรวม ทำให้ลืมได้ง่ายเมื่อเวลาผ่านไป จึงจำเป็นต้องมีสถานการณ์จำลองที่สร้างความเชื่อมโยงผ่านระบบภารกิจและผลตอบแทนในเกม",
                    'expected_outcomes' => "ผู้เรียนมีความแม่นยำในการเลือกคำตอบและแก้ไขสถานการณ์ในเรื่อง '{$title}' เพิ่มขึ้น 20% หลังเล่นเกม",
                    'knowledge_goals' => "จดจำและอธิบายขั้นตอนการทำงานและปัจจัยสำคัญของ '{$title}' ได้อย่างถูกต้อง",
                    'skill_goals' => "การวางแผนจัดสรรเวลา และการคิดอย่างมีวิจารณญาณภายใต้เงื่อนไขความท้าทาย",
                ],
                [
                    'problem_statement' => "ผู้เรียนขาดแรงบันดาลใจในการศึกษาบทเรียน '{$title}' ด้วยตนเอง จึงต้องการเกมที่มีเนื้อเรื่องและกลไกเกมที่ดึงดูดใจ ให้ความรู้ผสานกับความเพลิดเพลินได้อย่างกลมกลืน",
                    'expected_outcomes' => "ผู้เรียนมีทัศนคติเชิงบวกต่อการเรียนรู้เรื่อง '{$title}' และสามารถนำความรู้ไปประยุกต์ใช้ได้จริง",
                    'knowledge_goals' => "เชื่อมโยงความรู้เรื่อง '{$title}' กับเหตุการณ์และสถานการณ์ในชีวิตประจำวัน",
                    'skill_goals' => "ทักษะการสังเกต การประเมินสถานการณ์ และการจัดการทรัพยากรในเกม",
                ],
            ];
            return $opts[$seed];
        }

        if ($step === 'ideate') {
            if ($isFarm) {
                $opts = [
                    [
                        'game_concept' => "ฟาร์มจำลองและการเก็บเกี่ยว: {$title}",
                        'genre' => 'farming',
                        'theme' => 'nature',
                        'story' => "ผู้เล่นรับบทเป็นผู้ดูแลแปลงเกษตรอินทรีย์ ต้องฟื้นฟูพื้นที่ ปลูกพืช รดน้ำ เก็บเกี่ยวผลผลิต และปกป้องฟาร์มจากศัตรูพืชตัวร้าย",
                        'prompt' => "เกมปลูกผักจำลองฟาร์ม: ผู้เล่นเดินสำรวจแปลงดิน 6 แปลง ปลูกเมล็ด รดน้ำให้พืชโต และเก็บเกี่ยวผลผลิตเพื่อสะสมคะแนน มีศัตรูพืชเดินมาบุก มีกระดานคะแนน หลอดเลือด ร้านค้าซื้อของ และระบบมินิแมพ",
                        'missions' => "1. ปลูกพืชและรดน้ำแปลงดินรอบแรก -> 2. กำจัดศัตรูพืชด้วยสกิล -> 3. เก็บเกี่ยวผลผลิต 10 ชิ้นส่งร้านค้า -> 4. ปกป้องฟาร์มจากบอสใหญ่",
                        'challenges' => "เวลาการเจริญเติบโตของพืช, ความแห้งแล้งหากไม่รดน้ำ, ศัตรูพืชที่เดินเข้ามาทำลายผลผลิต",
                    ],
                    [
                        'game_concept' => "ศึกพิทักษ์แปลงผักและฟาร์มมหัศจรรย์ ({$title})",
                        'genre' => 'farming',
                        'theme' => 'nature',
                        'story' => "ดินแดนเกษตรกรรมเกิดวิกฤตแมลงร้ายบุกรุก ผู้เล่นต้องใช้ความรู้การดูแลพืชพรรณและกลยุทธ์ฟาร์มเพื่อกอบกู้ความอุดมสมบูรณ์",
                        'prompt' => "เกมจำลองฟาร์มแนวแอ็กชัน: ผู้เล่นเคลื่อนที่ปลูกพืชและรดน้ำแปลงดิน สะสมผลผลิตเพื่อขายในร้านค้า มีศัตรูพืชเดินมาขโมยผัก สามารถกดโจมตีหรือใช้สกิลได้ มีหลอดเลือดและเวลาจำกัด",
                        'missions' => "1. สำรวจแปลงเกษตรและเตรียมดิน -> 2. รดน้ำเพื่อเร่งการเติบโต -> 3. ปราบแมลงศัตรูพืชสะสมเหรียญ -> 4. อัปเกรดเครื่องมือในร้านค้า",
                        'challenges' => "เวลาถอยหลังสร้างความตื่นเต้น, มอนสเตอร์ศัตรูพืชที่เดินไวขึ้นตามเลเวล",
                    ],
                    [
                        'game_concept' => "ผู้จัดการฟาร์มมือทอง: {$title}",
                        'genre' => 'farming',
                        'theme' => 'nature',
                        'story' => "สร้างฟาร์มเกษตรยั่งยืนจากศูนย์ สู่การเป็นเกษตรกรผู้เชี่ยวชาญด้านผลผลิตอินทรีย์คุณภาพสูง",
                        'prompt' => "เกมบริหารฟาร์มและการเพาะปลูก: ผู้เล่นเดินรดน้ำแปลงดิน เก็บเกี่ยวผลผลิต สะสมแต้มและเลเวล EXP มีระบบกระเป๋าเก็บเมล็ดพันธุ์ ร้านค้าซื้อปุ๋ยเร่งโต และมินิแมพช่วยนำทาง",
                        'missions' => "1. ปลูกพืชตามคำสั่งภารกิจ -> 2. ดูแลแปลงผักให้สมบูรณ์ -> 3. เก็บเกี่ยวสะสมแต้มให้ถึงเป้าหมาย -> 4. ผ่านด่านเพื่อรับเหรียญตรา",
                        'challenges' => "การบริหารเวลาและพลังงาน Stamina ในการดูแลแปลงดินทั้งหมดให้ทันเวลา",
                    ],
                ];
                return $opts[$seed];
            } elseif ($isMath) {
                $opts = [
                    [
                        'game_concept' => "ลานประลองจอมเวทคณิตศาสตร์: {$title}",
                        'genre' => 'battle',
                        'theme' => 'fantasy',
                        'story' => "มอนสเตอร์แห่งความสับสนบุกประชิดหอคอยเวทมนตร์ ผู้เล่นต้องใช้พลังแห่งตัวเลขและสูตรคำนวณในเรื่อง '{$title}' เพื่อร่ายเวทมนตร์ปราบศัตรูและบอสใหญ่",
                        'prompt' => "เกมต่อสู้ Action Arena: ปะทะบอสใหญ่และมอนสเตอร์ ผู้เล่นปล่อยสกิลดาบสายฟ้าและคลื่นพลัง Shockwave สะสมเหรียญซื้อยาฟื้นพลังและดาบอัปเกรดในร้านค้า มีหลอดเลือดบอสขนาดใหญ่",
                        'missions' => "1. ปราบมอนสเตอร์ลูกสมุนรอบนอก -> 2. สะสมเหรียญอัปเกรดพลังในร้านค้า -> 3. เผชิญหน้ากับบอสใหญ่แห่ง '{$title}'",
                        'challenges' => "หลอดเลือดบอสที่หนาแน่น, การโจมตีของศัตรู, การบริหารค่าพลังสตามินาและมานา",
                    ],
                    [
                        'game_concept' => "ผู้กล้ากอบกู้มิติคำนวณ ({$title})",
                        'genre' => 'battle',
                        'theme' => 'fantasy',
                        'story' => "ประตูมิติปริศนาถูกเปิดออก มีเพียงผู้กล้าที่เชี่ยวชาญใน '{$title}' เท่านั้นที่จะสามารถปิดผนึกมันได้",
                        'prompt' => "เกมแอ็กชันผจญภัยในดันเจี้ยน: เดินสำรวจห้องลับ หลบกับดักหนาม ต่อสู้กับโกบลินและออร์ค เก็บเหรียญทองซื้ออาวุธ มีระบบมินิแมพ และกล่องบทสนทนาแจ้งภารกิจ",
                        'missions' => "1. ฝ่าด่านมอนสเตอร์ในชั้นแรก -> 2. ค้นหากุญแจทองคำ -> 3. ปราบหัวหน้าโกบลินและรับชัยชนะ",
                        'challenges' => "กับดักหนามตามทางเดิน, เวลาจำกัดในการผ่านแต่ละห้อง, ศัตรูที่เข้ามาเป็นระลอก",
                    ],
                    [
                        'game_concept' => "อารีน่าประลองไหวพริบตัวเลข: {$title}",
                        'genre' => 'battle',
                        'theme' => 'fantasy',
                        'story' => "เวทีประลองระดับกาแล็กซีที่รวบรวมสุดยอดนักคิด ผู้เล่นต้องเอาชนะบททดสอบ '{$title}' เพื่อขึ้นเป็นแชมเปียน",
                        'prompt' => "เกมประลองยุทธ์: ผู้เล่นปล่อยสกิลพิเศษโจมตีศัตรู มีหลอดเลือด HP บอสบาร์ กระดานคะแนน ระบบคอมโบสตรีค และหน้าต่างสรุปชัยชนะพร้อมเสียงเอฟเฟกต์สุดเร้าใจ",
                        'missions' => "1. ทำคอมโบต่อเนื่องสะสมแต้ม -> 2. ปลดล็อกสกิลขั้นสูง -> 3. เอาชนะบอสใหญ่เพื่อครองถ้วยรางวัล",
                        'challenges' => "ความแม่นยำและความเร็วในการออกท่าโจมตี, การหลบหลีกการโจมตีสวนกลับ",
                    ],
                ];
                return $opts[$seed];
            } elseif ($isWord) {
                $opts = [
                    [
                        'game_concept' => "เขาวงกตล่าคำศัพท์: {$title}",
                        'genre' => 'word_puzzle',
                        'theme' => 'school',
                        'story' => "ตัวอักษรและแผ่นป้ายคำศัพท์ต้องมนตร์หลุดกระจายไปทั่วเขาวงกต ผู้เล่นต้องผจญภัยเก็บคำศัพท์ให้ครบเพื่อกอบกู้พลังความรู้",
                        'prompt' => "เกมทายคำศัพท์ภาษาอังกฤษ: ผู้เล่นเดินค้นหาตัวอักษรและไอเทมคำศัพท์ในเขาวงกต มีเวลาจำกัด มีคอมโบสตรีคเมื่อเก็บต่อเนื่อง หลบกับดักหนาม และส่งสัญญาณแจ้งเควสต์ผ่าน NPC",
                        'missions' => "1. ค้นหาตัวอักษรประกอบคำให้สมบูรณ์ -> 2. ผ่านด่านเขาวงกตกับดัก -> 3. สะสมคำศัพท์ให้ครบเพื่อเปิดประตูชัยชนะ",
                        'challenges' => "เวลาถอยหลัง, สิ่งกีดขวางกับดักหนาม, มอนสเตอร์ลาดตระเวน",
                    ],
                    [
                        'game_concept' => "สายลับถอดรหัสภาษา ({$title})",
                        'genre' => 'word_puzzle',
                        'theme' => 'school',
                        'story' => "สวมบทบาทเป็นสายลับยอดฝีมือที่ต้องแทรกซึมเข้าฐานลับเพื่อถอดรหัสคำศัพท์ในเรื่อง '{$title}'",
                        'prompt' => "เกมปริศนาและสำรวจ: ผู้เล่นเดินค้นหาไอเทมรหัสลับในแผนที่ หลบเลี่ยงยามรักษาความปลอดภัย เก็บไอเทมดาวโบนัส และรายงานผลผ่านกล่องข้อความภารกิจ",
                        'missions' => "1. ค้นหาชิ้นส่วนรหัสคำศัพท์ชิ้นแรก -> 2. หลบหลีกสายตรวจ -> 3. ถอดรหัสคำศัพท์ชุดสุดท้ายเพื่อหลบหนี",
                        'challenges' => "การถูกตรวจจับจากศัตรู, ปริศนาคำศัพท์ที่ต้องใช้ความไวในการจับคู่",
                    ],
                    [
                        'game_concept' => "คัมภีร์เวทมนตร์แห่งภาษา: {$title}",
                        'genre' => 'word_puzzle',
                        'theme' => 'school',
                        'story' => "รวบรวมหน้าคัมภีร์ที่หายไปในห้องสมุดมนตราเพื่อฟื้นคืนความรู้ภาษาในบทเรียน '{$title}'",
                        'prompt' => "เกมสำรวจและสะสมคำศัพท์: ผู้เล่นเดินเก็บแผ่นศิลาอักษรในฉาก สะสมแต้มคอมโบคูณสอง มีกระเป๋าเก็บไอเทม มินิแมพเรดาร์ และเสียงเพลงบรรยากาศผจญภัย",
                        'missions' => "1. เก็บแผ่นศิลาอักษรให้ครบ -> 2. แก้ปริศนาคำศัพท์เปิดห้องลับ -> 3. สรุปคะแนนรับเหรียญรางวัล",
                        'challenges' => "เวลาจำกัดในการค้นหา, ตัวลวงที่อาจทำให้เสียคะแนน",
                    ],
                ];
                return $opts[$seed];
            } elseif ($isSpace) {
                $opts = [
                    [
                        'game_concept' => "ผู้พิทักษ์ดวงดาวและอวกาศ: {$title}",
                        'genre' => 'shooter',
                        'theme' => 'space',
                        'story' => "สำรวจระบบสุริยะและห้วงอวกาศลึกเพื่อทำความเข้าใจ '{$title}' พร้อมปกป้องสถานีอวกาศจากอุกกาบาตและยานต่างดาว",
                        'prompt' => "เกมยานอวกาศชู้ตติ้ง: ขับยานยิงเลเซอร์ทำลายอุกกาบาตและยานศัตรู เก็บชิ้นส่วนพลังงานเพิ่มค่าคะแนน ปะทะบอสยานแม่ขนาดใหญ่ มีหลอดเลือดและระบบอัปเกรดกระสุน",
                        'missions' => "1. กำจัดอุกกาบาตระลอกแรก -> 2. เก็บผลึกพลังงาน 5 ชิ้น -> 3. ทำลายยานแม่บอสต่างดาว",
                        'challenges' => "ความเร็วของอุกกาบาตที่พุ่งเข้ามา, กระสุนตอบโต้จากยานแม่บอส",
                    ],
                    [
                        'game_concept' => "หน่วยสำรวจกาแล็กซี ({$title})",
                        'genre' => 'shooter',
                        'theme' => 'space',
                        'story' => "ขับยานสำรวจดาวเคราะห์ดวงใหม่และบันทึกข้อมูลสำคัญในหัวข้อ '{$title}' เพื่อส่งกลับโลก",
                        'prompt' => "เกมขับยานอวกาศเอาชีวิตรอด: บินหลบกลุ่มดาวเคราะห์น้อย ยิงเลเซอร์ทำลายสิ่งกีดขวาง เก็บแคปซูลข้อมูลวิทยาศาสตร์ มีมินิแมพเรดาร์และหลอดพลังงาน",
                        'missions' => "1. บินผ่านดงดาวเคราะห์น้อย -> 2. กู้แคปซูลข้อมูลสำคัญ -> 3. นำข้อมูลกลับสู่สถานีแม่",
                        'challenges' => "พลังงานยานที่ลดลงต่อเนื่อง, แรงดึงดูดของหลุมดำและอุปสรรคในฉาก",
                    ],
                    [
                        'game_concept' => "สงครามอวกาศเหนือกาลเวลา: {$title}",
                        'genre' => 'shooter',
                        'theme' => 'space',
                        'story' => "ปะทะกองยานปริศนาเพื่อปกป้องความลับทางวิทยาศาสตร์เรื่อง '{$title}'",
                        'prompt' => "เกมยิงอวกาศอาร์เคด: ผู้เล่นควบคุมยานยิงกระสุนคู่ ปล่อยสกิลคลื่นบาเรีย เก็บไอเทมเพิ่มสปีดและฟื้นฟูเกราะ มีกระดานคะแนนและระบบคอมโบ",
                        'missions' => "1. ทำลายโดรนลาดตระเวน 10 ลำ -> 2. หลบหลีกพายุสุริยะ -> 3. ปราบยานรบระดับแม่ทัพ",
                        'challenges' => "จำนวนศัตรูที่เพิ่มขึ้นตามเวลา, การบริหารการใช้สกิลบาเรียป้องกันตัว",
                    ],
                ];
                return $opts[$seed];
            } else {
                // Dynamic Tailored Generation for Any Unique Topic!
                $opts = [
                    [
                        'game_concept' => "ภารกิจพิชิตบทเรียน: {$title}",
                        'genre' => ($genre !== 'custom' ? $genre : 'battle'),
                        'theme' => 'school',
                        'story' => "ผู้เล่นรับบทเป็นนักผจญภัยรุ่นเยาว์ที่ต้องออกเดินทางไขปริศนาในโลกแห่ง '{$title}' เพื่อนำความรู้และสันติสุขกลับคืนมา",
                        'prompt' => "เกมผจญภัยและแก้ปัญหาในเรื่อง '{$title}': ผู้เล่นเคลื่อนที่สำรวจพื้นที่ เก็บไอเทมสำคัญ หลบสิ่งกีดขวาง และปะทะกับมอนสเตอร์อุปสรรค มีหลอดเลือด กระดานคะแนน มินิแมพ ร้านค้า และระบบเสียงตอบสนอง",
                        'missions' => "1. รวบรวมองค์ประกอบพื้นฐานของ '{$title}' -> 2. ผ่านบททดสอบด่านกลาง -> 3. เอาชนะความท้าทายสุดท้ายเพื่อจบภารกิจ",
                        'challenges' => "เวลาที่จำกัด, สิ่งกีดขวางในฉาก, และการเลือกตัดสินใจให้ถูกต้องแม่นยำ",
                    ],
                    [
                        'game_concept' => "ผู้กล้าแห่งดินแดนความรู้ ({$title})",
                        'genre' => ($genre !== 'custom' ? $genre : 'word_puzzle'),
                        'theme' => 'fantasy',
                        'story' => "หนังสือโบราณที่บันทึกความลับเรื่อง '{$title}' ถูกขโมยไป ผู้เล่นต้องเดินทางตามรอยเบาะแสเพื่อกอบกู้ความรู้ทั้งหมด",
                        'prompt' => "เกมเควสต์ผจญภัยเชิงโต้ตอบในหัวข้อ '{$title}': ผู้เล่นเดินสำรวจแผนที่ สนทนากับ NPC รับเควสต์ เก็บไอเทมสะสมแต้ม มีระบบกระเป๋าเก็บของ และระบบคอมโบความต่อเนื่อง",
                        'missions' => "1. พูดคุยกับ NPC รับเบาะแสแรก -> 2. ค้นหาชิ้นส่วนความรู้ 5 ชิ้น -> 3. ปลดล็อกประตูสู่ห้องสมุดแห่งชัยชนะ",
                        'challenges' => "การค้นหาไอเทมที่ซ่อนอยู่, ตัวลวงที่ทำให้เสียพลังชีวิต, กับดักในด่าน",
                    ],
                    [
                        'game_concept' => "อารีน่าประลองปัญญา: {$title}",
                        'genre' => ($genre !== 'custom' ? $genre : 'farming'),
                        'theme' => 'nature',
                        'story' => "เวทีประลองระดับมาสเตอร์ที่ผู้เล่นต้องประยุกต์ใช้ความรู้ในเรื่อง '{$title}' ในการบริหารจัดการและเอาชนะความท้าทาย",
                        'prompt' => "เกมจำลองสถานการณ์และการเอาชีวิตรอดในเรื่อง '{$title}': ผู้เล่นดูแลทรัพยากร เก็บผลผลิต/คะแนน หลบหลีกอันตราย และอัปเกรดความสามารถตัวละครในร้านค้า มีหลอดเลือดและเรดาร์มินิแมพ",
                        'missions' => "1. เตรียมทรัพยากรและจัดการพื้นที่ -> 2. ป้องกันพื้นที่จากภัยคุกคาม -> 3. สร้างผลลัพธ์ให้ผ่านเกณฑ์คะแนนสูงสุด",
                        'challenges' => "การบริหารทรัพยากรภายใต้เวลาจำกัด, อุปสรรคที่เพิ่มความท้าทายขึ้นเรื่อยๆ",
                    ],
                ];
                return $opts[$seed];
            }
        }

        if ($step === 'prototype') {
            if ($isFarm) {
                return [
                    'recommended_features' => ['health_bar', 'stamina_mana', 'scoreboard', 'level_exp', 'timer', 'map', 'day_night', 'inventory', 'shop', 'dialogue', 'controls', 'sound_fx', 'particles'],
                    'assets' => [
                        'player' => '/assets/kenney/tiny-town/tile_0000.png',
                        'enemy' => '/assets/kenney/pixel-platformer/tile_0024.png',
                        'item' => '/assets/kenney/pixel-platformer-food/tile_0000.png',
                    ],
                ];
            } elseif ($isSpace) {
                return [
                    'recommended_features' => ['health_bar', 'boss_bar', 'scoreboard', 'level_exp', 'timer', 'combo', 'obstacles', 'skills', 'controls', 'sound_fx', 'particles'],
                    'assets' => [
                        'player' => '/assets/kenney/simple-space/ship_0000.png',
                        'enemy' => '/assets/kenney/simple-space/ship_0012.png',
                        'item' => '/assets/kenney/simple-space/laserBlue01.png',
                    ],
                ];
            } elseif ($isWord) {
                return [
                    'recommended_features' => ['health_bar', 'scoreboard', 'level_exp', 'timer', 'combo', 'map', 'checkpoints', 'dialogue', 'controls', 'sound_fx', 'particles'],
                    'assets' => [
                        'player' => '/assets/kenney/pixel-platformer/tile_0000.png',
                        'enemy' => '/assets/kenney/pixel-platformer/tile_0024.png',
                        'item' => '/assets/kenney/pixel-platformer/tile_0028.png',
                    ],
                ];
            } else {
                return [
                    'recommended_features' => ['health_bar', 'boss_bar', 'stamina_mana', 'scoreboard', 'level_exp', 'skills', 'shop', 'controls', 'sound_fx', 'particles', 'victory_modal'],
                    'assets' => [
                        'player' => '/assets/kenney/tiny-dungeon/tile_0084.png',
                        'enemy' => '/assets/kenney/tiny-dungeon/tile_0109.png',
                        'item' => '/assets/kenney/tiny-dungeon/tile_0102.png',
                    ],
                ];
            }
        }

        // test step
        $testOpts = [
            [
                'observations' => "ผู้เรียนมีความตื่นตัวสูงมากเมื่อได้มีปฏิสัมพันธ์กับเกมเรื่อง '{$title}' ระบบเสียงและภาพกราฟิกพิกเซลช่วยเพิ่มการจดจำ",
                'recorded_bugs' => ["ควรเพิ่มไอคอนชี้แนะตำแหน่งเป้าหมายในมินิแมพ", "ปุ่มกดกระโดด/โจมตีบนมือถือควรกดง่ายขึ้น"],
                'difficulty_rating' => 3,
                'feedback_summary' => "ผู้เล่นสนุกและเข้าใจเนื้อหา '{$title}' ได้เร็วกว่าการอ่านตำราปกติ อยากให้มีด่านเพิ่มในอนาคต",
            ],
            [
                'observations' => "นักเรียนแข่งขันทำคอมโบต่อเนื่องในเรื่อง '{$title}' อย่างสนุกสนาน อัตราการตอบถูกรอบหลังเล่นเกมเพิ่มขึ้นชัดเจน",
                'recorded_bugs' => ["เพิ่มเวลาการอ่านข้อความคำใบ้ของ NPC เล็กน้อย"],
                'difficulty_rating' => 4,
                'feedback_summary' => "เกมเล่นเพลิน ระบบฟื้นฟูพลังและร้านค้าช่วยให้ผู้เรียนไม่ท้อถอยเมื่อทำผิดพลาด",
            ],
        ];
        return $testOpts[$seed % 2];
    }
}

