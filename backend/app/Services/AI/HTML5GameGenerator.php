<?php

namespace App\Services\AI;

use App\Models\TeacherAiCredential;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HTML5GameGenerator
{
    /**
     * Generate complete HTML5 Game Bundle based on prompt, features, and assets
     */
    public function generateGame(array $params, ?int $teacherId = null): array
    {
        $title = $params['title'] ?? 'เกมการเรียนรู้เชิงโต้ตอบ';
        $prompt = $params['prompt'] ?? $params['description'] ?? 'เกมการเรียนรู้ที่ผู้เล่นสามารถสนุกและได้ความรู้';
        $genre = $params['genre'] ?? 'custom';
        $features = $params['features'] ?? [
            'health_bar', 'scoreboard', 'controls', 'sound_fx', 'timer',
            'map', 'inventory', 'skills', 'particles'
        ];
        // Resolve complete 11-slot Kenney asset suite based on genre and user choices
        $assets = $this->resolveFullGameAssets($genre, $params['assets'] ?? [], $prompt);

        // Check if teacher has configured active AI provider (OpenAI or Gemini)
        $aiCred = $this->getActiveAiCredential($teacherId, $params);
        $lastError = null;

        if ($aiCred) {
            try {
                if ($aiCred['provider'] === 'openai' || str_starts_with($aiCred['api_key'], 'sk-')) {
                    $aiResult = $this->generateWithOpenAI(
                        $aiCred['api_key'],
                        $title,
                        $prompt,
                        $genre,
                        $features,
                        $assets,
                        $aiCred['model'] ?: 'gpt-4o-mini',
                        $aiCred['base_url'] ?? null
                    );
                } else {
                    $aiResult = $this->generateWithGemini(
                        $aiCred['api_key'],
                        $title,
                        $prompt,
                        $genre,
                        $features,
                        $assets,
                        $aiCred['model'] ?: 'gemini-flash-lite-latest'
                    );
                }

                if ($aiResult && !empty($aiResult['bundle'])) {
                    $aiResult['is_ai_generated'] = true;
                    $aiResult['provider'] = $aiCred['provider'];
                    $aiResult['model'] = $aiCred['model'];
                    return $aiResult;
                }
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                Log::warning("AI game generation ({$aiCred['provider']}) fallback: " . $lastError);
            }
        }

        // Synthesize dynamic modular HTML5 game engine (Fallback Template)
        $fallbackGame = $this->synthesizeModularGame($title, $prompt, $genre, $features, $assets);
        $fallbackGame['is_ai_generated'] = false;
        $fallbackGame['fallback_reason'] = $aiCred 
            ? "AI Error ({$aiCred['provider']}): " . ($lastError ?: 'ไม่สามารถประมวลผลโค้ดเกมได้')
            : 'ยังไม่ได้ตั้งค่า API Key ที่ถูกต้องในหน้าตั้งค่า AI Provider';
        return $fallbackGame;
    }

    /**
     * Retrieve active AI credential (OpenAI or Gemini)
     */
    protected function getActiveAiCredential(?int $teacherId, array $params = []): ?array
    {
        // 1. Direct param key passed
        if (!empty($params['api_key']) && !str_starts_with($params['api_key'], 'mock-') && $params['api_key'] !== 'existing') {
            $key = trim($params['api_key']);
            $provider = str_starts_with($key, 'sk-') ? 'openai' : 'gemini';
            return [
                'provider' => $provider,
                'api_key' => $key,
                'model' => $provider === 'openai' ? 'gpt-4o-mini' : 'gemini-flash-lite-latest',
                'base_url' => null,
            ];
        }

        // 2. Active teacher credential in database
        $query = TeacherAiCredential::where('is_active', true)
            ->whereNotNull('encrypted_api_key')
            ->where('encrypted_api_key', '!=', '')
            ->where('encrypted_api_key', '!=', 'existing');

        if ($teacherId) {
            $cred = (clone $query)->where('teacher_id', $teacherId)->first();
            if ($cred && !str_starts_with($cred->encrypted_api_key, 'mock-')) {
                return [
                    'provider' => $cred->provider,
                    'api_key' => $cred->encrypted_api_key,
                    'model' => $cred->model ?: ($cred->provider === 'openai' ? 'gpt-4o-mini' : 'gemini-flash-lite-latest'),
                    'base_url' => $cred->base_url,
                ];
            }
        }

        // Fallback: Check any active valid credential saved in DB
        $anyCred = $query->orderBy('updated_at', 'desc')->first();
        if ($anyCred && !str_starts_with($anyCred->encrypted_api_key, 'mock-')) {
            return [
                'provider' => $anyCred->provider,
                'api_key' => $anyCred->encrypted_api_key,
                'model' => $anyCred->model ?: ($anyCred->provider === 'openai' ? 'gpt-4o-mini' : 'gemini-flash-lite-latest'),
                'base_url' => $anyCred->base_url,
            ];
        }

        // 3. Fallback: Environment Gemini Key
        $envKey = env('GEMINI_API_KEY');
        if ($envKey && !str_starts_with($envKey, 'mock-')) {
            return [
                'provider' => 'gemini',
                'api_key' => $envKey,
                'model' => 'gemini-flash-lite-latest',
                'base_url' => null,
            ];
        }

        return null;
    }

    /**
     * Resolve complete 11-slot Kenney Asset suite based on genre, user choices, and prompt
     */
    public function resolveFullGameAssets(string $genre, array $userAssets = [], string $prompt = ''): array
    {
        $promptLower = mb_strtolower($prompt . ' ' . $genre);
        $isSpace = str_contains($promptLower, 'space') || str_contains($promptLower, 'อวกาศ') || str_contains($promptLower, 'ยาน') || str_contains($genre, 'shooter');
        $isFarming = str_contains($promptLower, 'ปลูก') || str_contains($promptLower, 'ฟาร์ม') || str_contains($promptLower, 'farm') || str_contains($promptLower, 'eco') || str_contains($promptLower, 'ขยะ');
        $isDungeonBattle = str_contains($promptLower, 'ต่อสู้') || str_contains($promptLower, 'สู้') || str_contains($promptLower, 'มอน') || str_contains($genre, 'battle') || str_contains($promptLower, 'dungeon') || str_contains($promptLower, 'math');

        if ($isSpace) {
            $defaults = [
                'player' => '/assets/kenney/simple-space/ship_A.png',
                'enemy' => '/assets/kenney/simple-space/enemy_A.png',
                'boss' => '/assets/kenney/simple-space/enemy_E.png',
                'item' => '/assets/kenney/simple-space/star_large.png',
                'coin' => '/assets/kenney/simple-space/star_medium.png',
                'heart' => '/assets/kenney/simple-space/satellite_A.png',
                'bullet' => '/assets/kenney/simple-space/effect_yellow.png',
                'obstacle' => '/assets/kenney/simple-space/meteor_large.png',
                'ground' => '/assets/kenney/simple-space/star_tiny.png',
                'wall' => '/assets/kenney/simple-space/meteor_detailedLarge.png',
                'npc' => '/assets/kenney/simple-space/station_A.png',
            ];
        } elseif ($isFarming) {
            $defaults = [
                'player' => '/assets/kenney/tiny-town/tile_0000.png',
                'enemy' => '/assets/kenney/pixel-platformer/tile_0024.png',
                'boss' => '/assets/kenney/pixel-platformer/tile_0026.png',
                'item' => '/assets/kenney/pixel-platformer-food/tile_0036.png',
                'coin' => '/assets/kenney/tiny-dungeon/tile_0117.png',
                'heart' => '/assets/kenney/pixel-platformer-food/tile_0000.png',
                'bullet' => '/assets/kenney/pixel-platformer-food/tile_0002.png',
                'obstacle' => '/assets/kenney/tiny-town/tile_0044.png',
                'ground' => '/assets/kenney/tiny-town/tile_0001.png',
                'wall' => '/assets/kenney/tiny-town/tile_0015.png',
                'npc' => '/assets/kenney/tiny-town/tile_0027.png',
            ];
        } elseif ($isDungeonBattle) {
            $defaults = [
                'player' => '/assets/kenney/tiny-dungeon/tile_0084.png',
                'enemy' => '/assets/kenney/tiny-dungeon/tile_0109.png',
                'boss' => '/assets/kenney/tiny-dungeon/tile_0111.png',
                'item' => '/assets/kenney/tiny-dungeon/tile_0118.png',
                'coin' => '/assets/kenney/tiny-dungeon/tile_0117.png',
                'heart' => '/assets/kenney/tiny-dungeon/tile_0115.png',
                'bullet' => '/assets/kenney/tiny-dungeon/tile_0102.png',
                'obstacle' => '/assets/kenney/tiny-dungeon/tile_0068.png',
                'ground' => '/assets/kenney/tiny-dungeon/tile_0000.png',
                'wall' => '/assets/kenney/tiny-dungeon/tile_0012.png',
                'npc' => '/assets/kenney/tiny-dungeon/tile_0085.png',
            ];
        } else {
            // Platformer / Adventure / Runner / General
            $defaults = [
                'player' => '/assets/kenney/pixel-platformer/tile_0000.png',
                'enemy' => '/assets/kenney/pixel-platformer/tile_0021.png',
                'boss' => '/assets/kenney/pixel-platformer/tile_0026.png',
                'item' => '/assets/kenney/pixel-platformer-food/tile_0000.png',
                'coin' => '/assets/kenney/tiny-dungeon/tile_0117.png',
                'heart' => '/assets/kenney/pixel-platformer-food/tile_0002.png',
                'bullet' => '/assets/kenney/simple-space/effect_yellow.png',
                'obstacle' => '/assets/kenney/pixel-platformer/tile_0015.png',
                'ground' => '/assets/kenney/pixel-platformer/tile_0003.png',
                'wall' => '/assets/kenney/pixel-platformer/tile_0005.png',
                'npc' => '/assets/kenney/pixel-platformer/tile_0001.png',
            ];
        }

        // Overlay user-provided assets
        foreach ($userAssets as $key => $val) {
            if (!empty($val) && is_string($val)) {
                $defaults[$key] = $val;
            }
        }

        return $defaults;
    }

    /**
     * Call OpenAI API to generate real HTML/CSS/JS game code
     */
    protected function generateWithOpenAI(string $apiKey, string $title, string $prompt, string $genre, array $features, array $assets, string $model = 'gpt-4o-mini', ?string $baseUrl = null): ?array
    {
        $featuresList = implode(', ', $features);
        $assetsJson = json_encode($assets, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $systemPrompt = <<<PROMPT
คุณเป็นสุดยอดผู้เชี่ยวชาญการสร้างเกม HTML5 Canvas / Web Games ด้วย HTML, CSS และ JavaScript
สร้างเกมที่เล่นได้จริง 100% (Fully Interactive, Playable Game) ตามแนวคิดของผู้ใช้

กฎสำคัญเรื่องกราฟิกและ Assets (บังคับใช้รูปภาพสไปรต์ครบทุกชิ้น):
ระบบได้เตรียมรูปภาพ Assets 2D จาก Kenney CC0 ให้ครบ 11 ชิ้นดังนี้:
{$assetsJson}

ต้องเขียนโค้ดโหลด Image Object ใน JavaScript ให้ครบทุกตัว:
const GFX = {
  player: new Image(), enemy: new Image(), boss: new Image(), item: new Image(),
  coin: new Image(), heart: new Image(), bullet: new Image(), obstacle: new Image(),
  ground: new Image(), wall: new Image(), npc: new Image()
};
GFX.player.src = '{$assets['player']}';
GFX.enemy.src = '{$assets['enemy']}';
GFX.boss.src = '{$assets['boss']}';
GFX.item.src = '{$assets['item']}';
GFX.coin.src = '{$assets['coin']}';
GFX.heart.src = '{$assets['heart']}';
GFX.bullet.src = '{$assets['bullet']}';
GFX.obstacle.src = '{$assets['obstacle']}';
GFX.ground.src = '{$assets['ground']}';
GFX.wall.src = '{$assets['wall']}';
GFX.npc.src = '{$assets['npc']}';

และต้องวาดสิ่งเหล่านี้ลงบน canvas ด้วย ctx.drawImage(GFX[name], x, y, w, h) ทุกชิ้น!
หากรูปยังไม่โหลด ให้มี fallback วาดสี่เหลี่ยม/วงกลมที่มีสีสัน แล้วสลับเป็นภาพเมื่อโหลดเสร็จ

ส่งคืนเฉพาะ JSON ในรูปแบบ:
{
  "title": "{$title}",
  "genre": "{$genre}",
  "features": [...],
  "html": "...",
  "css": "...",
  "js": "...",
  "bundle": "<!DOCTYPE html><html>...</html>"
}
PROMPT;

        $userMessage = "ชื่อเกม: {$title}\nแนวคิด: {$prompt}\nแนวเกม: {$genre}\nFeatures: {$featuresList}\nAssets: {$assetsJson}\nสร้างเกม HTML5 ที่สมบูรณ์แบบพร้อมโหลดและวาด Assets ทั้งหมดลง Canvas";

        $url = rtrim($baseUrl ?: 'https://api.openai.com/v1', '/') . '/chat/completions';

        $response = Http::withoutVerifying()
            ->timeout(60)
            ->withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])
            ->post($url, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userMessage],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.7,
            ]);

        if ($response->successful()) {
            $data = $response->json();
            $text = $data['choices'][0]['message']['content'] ?? '';
            $text = trim($text);
            $json = json_decode($text, true);
            if ($json && isset($json['bundle'])) {
                $json['type'] = 'html5';
                return $json;
            }
        } else {
            $err = $response->json('error.message') ?: $response->body();
            throw new \Exception("OpenAI: " . $err);
        }

        return null;
    }

    /**
     * Call Gemini API to generate real HTML/CSS/JS game code
     */
    protected function generateWithGemini(string $apiKey, string $title, string $prompt, string $genre, array $features, array $assets, string $model = 'gemini-flash-lite-latest'): ?array
    {
        $featuresList = implode(', ', $features);
        $assetsJson = json_encode($assets, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $systemPrompt = <<<PROMPT
คุณเป็นสุดยอดผู้เชี่ยวชาญการสร้างเกม HTML5 Canvas / DOM Web Games ด้วย HTML, CSS และ JavaScript
สร้างเกมที่เล่นได้จริง 100% (Fully Interactive, Playable, Polished Game) ตามจินตนาการและคำบรรยายของผู้ใช้

ฟังก์ชันสำคัญที่ระบบต้องมี:
1. โค้ดทั้งหมดต้องรวมอยู่ในไฟล์ HTML เดียวที่สมบูรณ์ (Single File Self-contained HTML5 Bundle) มี <!DOCTYPE html><html><head><style>...</style></head><body>...<canvas id="gameCanvas"></canvas>...<script>...</script></body></html>
2. แสดงผลสวยงามระดับพรีเมียม (Vibrant Colors, Dark Glow, Modern UI HUD, Smooth Animations 60fps ด้วย requestAnimationFrame)
3. ระบบการควบคุม:
   - คีย์บอร์ด: รองรับทั้ง WASD, Arrow keys, Spacebar (Action), Q/E (Skills), 1-5 (Hotbar)
   - โมบายล์ / ทัชสกรีน: ต้องมีปุ่ม Virtual D-Pad บนหน้าจอ และปุ่ม Action / Skill รองรับการสัมผัสบนมือถือและแท็บเล็ต
4. ระบบเสียง Web Audio API (Synthesizer โดยไม่ต้องโหลดไฟล์ภายนอก): เสียงกระโดด, โจมตี, เก็บของ, คอมโบ, ชนะ, แพ้
5. การเชื่อมต่อคะแนนกลับสู่ระบบ:
   - ทุกครั้งที่คะแนนเปลี่ยน: window.parent.postMessage({ type: 'dtg:score_update', score: currentScore }, '*')
   - เมื่อจบเกม / ผ่านด่าน / แพ้: window.parent.postMessage({ type: 'dtg:game_over', score: currentScore, won: boolean }, '*')
6. ระบบโมดูลที่ผู้ใช้เลือก (บังคับใส่ให้ครบตามรายการ Features):
   - health_bar, boss_bar, stamina_mana, scoreboard, level_exp, timer, combo, map, day_night, obstacles, checkpoints, skills, inventory, shop, dialogue, particles
7. กฎสำคัญเรื่องกราฟิกและ Assets (บังคับใช้รูปภาพสไปรต์ครบทุกชิ้น):
   ระบบได้จัดเตรียมคลัง Assets 2D จาก Kenney CC0 ให้ครบ 11 ชิ้นดังนี้:
   {$assetsJson}

   ในโค้ด JavaScript ต้องสร้าง Image Object โหลดรูปภาพเหล่านี้ทั้งหมด:
   const GFX = {
     player: new Image(), enemy: new Image(), boss: new Image(), item: new Image(),
     coin: new Image(), heart: new Image(), bullet: new Image(), obstacle: new Image(),
     ground: new Image(), wall: new Image(), npc: new Image()
   };
   GFX.player.src = '{$assets['player']}';
   GFX.enemy.src = '{$assets['enemy']}';
   GFX.boss.src = '{$assets['boss']}';
   GFX.item.src = '{$assets['item']}';
   GFX.coin.src = '{$assets['coin']}';
   GFX.heart.src = '{$assets['heart']}';
   GFX.bullet.src = '{$assets['bullet']}';
   GFX.obstacle.src = '{$assets['obstacle']}';
   GFX.ground.src = '{$assets['ground']}';
   GFX.wall.src = '{$assets['wall']}';
   GFX.npc.src = '{$assets['npc']}';

   และต้องวาดลงบน Canvas ด้วย ctx.drawImage(GFX[name], x, y, width, height) ทุกชิ้น!
   (เช่น วาดผู้เล่นด้วย GFX.player, มอนสเตอร์ด้วย GFX.enemy, บอสตัวใหญ่ด้วย GFX.boss, ไอเทมเป้าหมายด้วย GFX.item, เหรียญด้วย GFX.coin, เลือดด้วย GFX.heart, กระสุนด้วย GFX.bullet, กับดักหนามด้วย GFX.obstacle, NPC ด้วย GFX.npc, พื้นดิน/บล็อกด้วย GFX.ground)
PROMPT;

        $userMessage = <<<MSG
ชื่อเกม: {$title}
แนวคิดเกมและรูปแบบการเล่น: {$prompt}
แนวเกม: {$genre}
ฟีเจอร์โมดูลที่เลือกให้มีในเกม: {$featuresList}
Assets 2D ที่จัดเตรียมให้: {$assetsJson}

สร้างเกม HTML5 ที่สมบูรณ์แบบ เล่นได้ทันที พร้อมโหลดและวาด Assets ทุกชิ้นลงบน Canvas และส่งคืนเฉพาะ JSON ในรูปแบบ:
{
  "title": "{$title}",
  "genre": "{$genre}",
  "features": [...],
  "html": "...",
  "css": "...",
  "js": "...",
  "bundle": "<!DOCTYPE html><html>...</html>"
}
MSG;

        $response = Http::withoutVerifying()
            ->timeout(60)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $systemPrompt . "\n\n" . $userMessage],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'responseMimeType' => 'application/json',
                ],
            ]);

        if ($response->successful()) {
            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $text = trim($text);
            if (preg_match('/^```(?:json)?\s*([\s\S]*?)\s*```$/m', $text, $matches)) {
                $text = trim($matches[1]);
            }
            $json = json_decode($text, true);
            if ($json && isset($json['bundle'])) {
                $json['type'] = 'html5';
                return $json;
            } else {
                Log::warning('Gemini responded successfully but bundle was missing or JSON invalid. Length: ' . strlen($text));
            }
        } else {
            $err = $response->json('error.message') ?: $response->body();
            throw new \Exception("Google Gemini: " . $err);
        }

        return null;
    }

    /**
     * Synthesize a complete, rich, customizable HTML5 Game Engine
     */
    public function synthesizeModularGame(string $title, string $prompt, string $genre, array $features, array $assets): array
    {
        // Resolve complete 11-slot asset suite
        $assets = $this->resolveFullGameAssets($genre, $assets, $prompt);

        // Gameplay heuristics
        $promptLower = mb_strtolower($prompt . ' ' . $genre);
        $isFarming = str_contains($promptLower, 'ปลูก') || str_contains($promptLower, 'ฟาร์ม') || str_contains($genre, 'farm');
        $isWordGame = str_contains($promptLower, 'คำศัพท์') || str_contains($promptLower, 'ทายคำ') || str_contains($genre, 'word');
        $isBattle = str_contains($promptLower, 'ต่อสู้') || str_contains($promptLower, 'สู้') || str_contains($promptLower, 'มอน') || str_contains($genre, 'battle') || str_contains($genre, 'arena');

        $bundle = $this->buildFullHtmlBundle([
            'title' => $title,
            'prompt' => $prompt,
            'genre' => $genre,
            'features' => $features,
            'isFarming' => $isFarming,
            'isWordGame' => $isWordGame,
            'isBattle' => $isBattle,
            'assets' => $assets,
        ]);

        return [
            'type' => 'html5',
            'title' => $title,
            'prompt' => $prompt,
            'genre' => $genre,
            'features' => $features,
            'bundle' => $bundle,
            'assets' => $assets,
        ];
    }

    /**
     * Build the raw HTML+CSS+JS document
     */
    protected function buildFullHtmlBundle(array $c): string
    {
        $titleEsc = htmlspecialchars($c['title'] ?? 'เกมการเรียนรู้เชิงโต้ตอบ', ENT_QUOTES, 'UTF-8');
        $featuresJson = json_encode($c['features'] ?? []);
        $assets = $c['assets'] ?? $this->resolveFullGameAssets($c['genre'] ?? 'custom', [], $c['prompt'] ?? '');
        $playerImgEsc = addslashes($assets['player']);
        $enemyImgEsc = addslashes($assets['enemy']);
        $bossImgEsc = addslashes($assets['boss']);
        $itemImgEsc = addslashes($assets['item']);
        $coinImgEsc = addslashes($assets['coin']);
        $heartImgEsc = addslashes($assets['heart']);
        $bulletImgEsc = addslashes($assets['bullet']);
        $obstacleImgEsc = addslashes($assets['obstacle']);
        $groundImgEsc = addslashes($assets['ground']);
        $wallImgEsc = addslashes($assets['wall']);
        $npcImgEsc = addslashes($assets['npc']);

        $hasMap = in_array('map', $c['features']);
        $hasHealth = in_array('health_bar', $c['features']);
        $hasBossBar = in_array('boss_bar', $c['features']);
        $hasStamina = in_array('stamina_mana', $c['features']) || in_array('skills', $c['features']);
        $hasScoreboard = in_array('scoreboard', $c['features']);
        $hasLevel = in_array('level_exp', $c['features']);
        $hasTimer = in_array('timer', $c['features']);
        $hasCombo = in_array('combo', $c['features']);
        $hasDayNight = in_array('day_night', $c['features']);
        $hasObstacles = in_array('obstacles', $c['features']);
        $hasCheckpoints = in_array('checkpoints', $c['features']);
        $hasSkills = in_array('skills', $c['features']);
        $hasInventory = in_array('inventory', $c['features']);
        $hasShop = in_array('shop', $c['features']);
        $hasDialogue = in_array('dialogue', $c['features']);
        $hasControls = in_array('controls', $c['features']);
        $hasSound = in_array('sound_fx', $c['features']);
        $hasParticles = in_array('particles', $c['features']);
        $hasVictory = in_array('victory_modal', $c['features']);

        $html = <<<HTML
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1.0">
  <title>{$titleEsc}</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; user-select: none; }
    body, html {
      width: 100%; height: 100%;
      overflow: hidden;
      background: #090614;
      font-family: 'Prompt', 'Kanit', sans-serif, system-ui;
      color: #fff;
    }
    #gameWrapper {
      position: relative;
      width: 100vw; height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      background: radial-gradient(circle at center, #1e1035 0%, #080312 100%);
      overflow: hidden;
    }
    #gameCanvas {
      background: #150d28;
      box-shadow: 0 0 40px rgba(0,0,0,0.9);
      max-width: 100%; max-height: 100%;
      image-rendering: pixelated;
    }

    /* Top Boss Health Bar */
    #bossBarContainer {
      position: absolute;
      top: 14px; left: 50%;
      transform: translateX(-50%);
      width: 320px;
      max-width: 85vw;
      background: rgba(20, 6, 38, 0.92);
      border: 2px solid #ef4444;
      border-radius: 14px;
      padding: 6px 14px;
      box-shadow: 0 0 20px rgba(239, 68, 68, 0.4);
      z-index: 15;
      text-align: center;
      display: {$this->cssDisplay($hasBossBar)};
    }
    .boss-name { font-size: 13px; font-weight: bold; color: #fca5a5; display: flex; justify-content: space-between; margin-bottom: 3px; }
    .boss-fill { background: linear-gradient(90deg, #dc2626, #f87171); width: 100%; height: 10px; border-radius: 5px; transition: width 0.2s; }

    /* HUD Overlay */
    #hud {
      position: absolute;
      top: 14px; left: 14px; right: 14px;
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      pointer-events: none;
      z-index: 10;
    }
    .hud-panel {
      background: rgba(18, 9, 36, 0.88);
      border: 1px solid rgba(168, 85, 247, 0.4);
      border-radius: 14px;
      padding: 10px 14px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.6);
      pointer-events: auto;
      backdrop-filter: blur(10px);
    }
    .hud-title {
      font-size: 12px; font-weight: bold; color: #d8b4fe; margin-bottom: 4px;
      display: flex; align-items: center; gap: 6px;
    }
    .bar-container {
      width: 140px; height: 12px;
      background: #2b1647;
      border-radius: 6px;
      overflow: hidden;
      border: 1px solid rgba(255,255,255,0.15);
    }
    .bar-fill { height: 100%; transition: width 0.2s ease; }
    .health-fill { background: linear-gradient(90deg, #ef4444, #f87171); width: 100%; }
    .stamina-fill { background: linear-gradient(90deg, #3b82f6, #60a5fa); width: 100%; }
    .exp-fill { background: linear-gradient(90deg, #10b981, #34d399); width: 0%; }

    /* Score & Coins */
    .score-value {
      font-size: 22px; font-weight: 800; color: #fbbf24;
      text-shadow: 0 0 12px rgba(251, 191, 36, 0.5);
    }
    .coins-badge {
      display: inline-flex; align-items: center; gap: 4px;
      background: rgba(251, 191, 36, 0.2); border: 1px solid #fbbf24;
      border-radius: 12px; padding: 2px 8px; font-size: 12px; font-weight: bold; color: #fde047;
    }

    /* Combo Badge */
    #comboBadge {
      position: absolute;
      top: 90px; right: 16px;
      background: linear-gradient(135deg, #f97316, #ef4444);
      color: #fff; font-size: 14px; font-weight: 800;
      padding: 4px 12px; border-radius: 20px;
      box-shadow: 0 0 15px rgba(249, 115, 22, 0.7);
      animation: pulse 0.8s infinite alternate;
      display: none;
      z-index: 12;
    }
    @keyframes pulse {
      0% { transform: scale(1); }
      100% { transform: scale(1.1); }
    }

    /* Mini-map */
    #miniMap {
      width: 96px; height: 96px;
      background: rgba(10, 4, 22, 0.8);
      border: 2px solid #a855f7;
      border-radius: 12px;
      position: relative;
      margin-top: 8px;
      overflow: hidden;
      display: {$this->cssDisplay($hasMap)};
    }
    #miniMapPlayer {
      position: absolute;
      width: 8px; height: 8px;
      background: #22c55e;
      border-radius: 50%;
      transform: translate(-50%, -50%);
      box-shadow: 0 0 6px #22c55e;
    }
    .map-blip {
      position: absolute; width: 6px; height: 6px; border-radius: 50%; transform: translate(-50%, -50%);
    }

    /* Quick Hotbar / Inventory */
    #inventoryBar {
      position: absolute;
      bottom: 18px; left: 50%;
      transform: translateX(-50%);
      display: {$this->cssDisplay($hasInventory)};
      gap: 8px;
      background: rgba(18, 9, 36, 0.92);
      border: 2px solid #a855f7;
      border-radius: 18px;
      padding: 6px 12px;
      z-index: 10;
      box-shadow: 0 8px 25px rgba(0,0,0,0.7);
    }
    .inv-slot {
      width: 48px; height: 48px;
      border: 2px solid rgba(255,255,255,0.2);
      border-radius: 12px;
      background: rgba(255,255,255,0.06);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      font-size: 20px;
      cursor: pointer;
      position: relative;
      transition: all 0.15s ease;
    }
    .inv-slot:hover, .inv-slot.active {
      border-color: #facc15;
      background: rgba(250, 204, 21, 0.2);
      transform: scale(1.08);
    }
    .inv-count {
      position: absolute; bottom: 2px; right: 4px;
      font-size: 10px; font-weight: bold; color: #fff;
    }
    .inv-key {
      position: absolute; top: 2px; left: 4px;
      font-size: 9px; color: rgba(255,255,255,0.5); font-weight: bold;
    }

    /* Dialogue Box */
    #dialogueBox {
      position: absolute;
      bottom: 86px; left: 50%;
      transform: translateX(-50%);
      width: 90%; max-width: 580px;
      background: rgba(22, 9, 44, 0.96);
      border: 2px solid #c084fc;
      border-radius: 18px;
      padding: 14px 18px;
      display: none;
      z-index: 25;
      box-shadow: 0 10px 30px rgba(0,0,0,0.85);
      backdrop-filter: blur(8px);
    }
    #dialogueText { font-size: 14px; line-height: 1.5; color: #f3e8ff; margin-bottom: 8px; }
    .dialogue-btn {
      background: linear-gradient(135deg, #9333ea, #c084fc);
      border: none; color: #fff; font-weight: bold;
      padding: 6px 16px; border-radius: 8px; cursor: pointer; float: right;
    }

    /* Shop Modal */
    #shopModal {
      position: absolute;
      top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(10, 4, 22, 0.85);
      backdrop-filter: blur(8px);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 40;
    }
    .shop-card {
      background: #1d0f36;
      border: 2px solid #facc15;
      border-radius: 24px;
      padding: 24px;
      max-width: 480px; width: 90%;
      box-shadow: 0 0 35px rgba(250, 204, 21, 0.3);
    }
    .shop-item-row {
      display: flex; justify-content: space-between; align-items: center;
      padding: 10px; border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .shop-buy-btn {
      background: linear-gradient(135deg, #10b981, #059669);
      border: none; color: #fff; font-weight: bold;
      padding: 6px 14px; border-radius: 8px; cursor: pointer;
    }
    .shop-buy-btn:disabled { background: #475569; cursor: not-allowed; }

    /* Touch Controls */
    #touchControls {
      position: absolute;
      bottom: 20px; left: 20px; right: 20px;
      display: {$this->cssDisplay($hasControls)};
      justify-content: space-between;
      align-items: flex-end;
      pointer-events: none;
      z-index: 15;
    }
    .touch-btn {
      width: 54px; height: 54px;
      border-radius: 50%;
      background: rgba(255,255,255,0.16);
      border: 2px solid rgba(255,255,255,0.4);
      display: flex; justify-content: center; align-items: center;
      font-size: 20px; color: white;
      pointer-events: auto;
      touch-action: manipulation;
    }
    .touch-btn:active { background: rgba(255,255,255,0.38); transform: scale(0.92); }
    .d-pad { display: grid; grid-template-columns: repeat(3, 44px); grid-template-rows: repeat(3, 44px); gap: 4px; }
    .action-buttons { display: flex; gap: 10px; align-items: center; }
    .action-btn-large { width: 62px; height: 62px; background: linear-gradient(135deg, #9333ea, #db2777); font-weight: bold; }
    .shop-trigger-btn {
      position: absolute; top: 14px; left: 50%;
      transform: translateX(-50%);
      background: linear-gradient(135deg, #eab308, #ca8a04);
      color: #1e1035; font-weight: bold; font-size: 13px;
      padding: 6px 16px; border-radius: 20px; border: none; cursor: pointer;
      display: {$this->cssDisplay($hasShop && !$hasBossBar)};
      z-index: 12; box-shadow: 0 4px 15px rgba(234, 179, 8, 0.4);
    }

    /* Popup Victory / Game Over */
    #endModal {
      position: absolute;
      top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(8, 3, 18, 0.9);
      display: none;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      z-index: 50;
      backdrop-filter: blur(12px);
    }
    .modal-card {
      background: #1c0f38;
      border: 2px solid #c084fc;
      border-radius: 24px;
      padding: 32px;
      text-align: center;
      max-width: 420px; width: 90%;
      box-shadow: 0 15px 45px rgba(0,0,0,0.85);
    }
    .restart-btn {
      margin-top: 18px;
      background: linear-gradient(135deg, #10b981, #059669);
      border: none; color: white;
      font-size: 16px; font-weight: bold;
      padding: 12px 28px; border-radius: 12px; cursor: pointer;
      transition: transform 0.2s;
    }
    .restart-btn:hover { transform: scale(1.05); }
  </style>
</head>
<body>
  <div id="gameWrapper">
    <canvas id="gameCanvas" width="800" height="500"></canvas>

    <!-- Boss Health Bar -->
    <div id="bossBarContainer">
      <div class="boss-name">
        <span>👹 ราชาปิศาจ / บอสใหญ่</span>
        <span id="bossHpText">100%</span>
      </div>
      <div style="background: rgba(0,0,0,0.4); border-radius: 5px; overflow: hidden;">
        <div id="bossHpFill" class="boss-fill"></div>
      </div>
    </div>

    <!-- Shop Floating Button -->
    <button class="shop-trigger-btn" onclick="openShop()">🛒 ร้านค้า & อัปเกรด</button>

    <!-- HUD Overlay -->
    <div id="hud">
      <!-- Left: Player Health & Stats -->
      <div class="hud-panel">
        <div class="hud-title" style="display: {$this->cssDisplay($hasHealth)};">
          <span>❤️ พลังชีวิต</span>
          <span id="healthNum" style="font-size: 11px; color:#fca5a5;">100/100</span>
        </div>
        <div class="bar-container mb-1" style="display: {$this->cssDisplay($hasHealth)};">
          <div id="healthBar" class="bar-fill health-fill"></div>
        </div>

        <div class="hud-title" style="margin-top:6px; display: {$this->cssDisplay($hasStamina)};">
          <span>⚡ สตามินา / พลัง</span>
          <span id="staminaNum" style="font-size: 11px; color:#93c5fd;">100%</span>
        </div>
        <div class="bar-container" style="display: {$this->cssDisplay($hasStamina)};">
          <div id="staminaBar" class="bar-fill stamina-fill"></div>
        </div>

        <div class="hud-title" style="margin-top:6px; display: {$this->cssDisplay($hasLevel)};">
          <span>🎖️ เลเวล <span id="playerLevel">1</span></span>
          <span id="expText" style="font-size: 10px; color:#86efac;">0/100 XP</span>
        </div>
        <div class="bar-container" style="display: {$this->cssDisplay($hasLevel)};">
          <div id="expBar" class="bar-fill exp-fill"></div>
        </div>
      </div>

      <!-- Center: Title & Timer -->
      <div class="hud-panel text-center">
        <div style="font-size: 11px; color:#cbd5e1; max-width: 160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{$titleEsc}</div>
        <div id="timerText" style="font-size: 19px; font-weight: bold; color: #38bdf8; display: {$this->cssDisplay($hasTimer)};">02:00</div>
        <div id="timeOfDayText" style="font-size: 10px; color:#fef08a; display: {$this->cssDisplay($hasDayNight)};">☀️ กลางวัน</div>
      </div>

      <!-- Right: Score, Coins & Mini-map -->
      <div class="hud-panel d-flex flex-column align-items-end" style="text-align: right;">
        <div class="hud-title justify-content-end" style="display: {$this->cssDisplay($hasScoreboard)};">
          <span>🏆 คะแนน</span>
        </div>
        <div id="scoreText" class="score-value" style="display: {$this->cssDisplay($hasScoreboard)};">0</div>
        <div class="coins-badge" style="margin-top: 4px; display: {$this->cssDisplay($hasScoreboard || $hasShop)};">
          💰 <span id="coinsText">0</span>
        </div>
        <div id="miniMap">
          <div id="miniMapPlayer"></div>
        </div>
      </div>
    </div>

    <!-- Combo Streak Badge -->
    <div id="comboBadge">🔥 COMBO x<span id="comboCount">2</span>!</div>

    <!-- Dialogue Box -->
    <div id="dialogueBox">
      <div style="font-weight: bold; color: #facc15; margin-bottom: 4px;" id="dialogueSpeaker">NPC ผู้ชี้นำ</div>
      <div id="dialogueText">ยินดีต้อนรับสู่เกม! ใช้ WASD หรือแตะปุ่มเพื่อเดิน และกด Action เพื่อทำกิจกรรม</div>
      <button class="dialogue-btn" onclick="closeDialogue()">รับทราบ (ตกลง)</button>
    </div>

    <!-- Inventory Hotbar -->
    <div id="inventoryBar">
      <div class="inv-slot active" onclick="selectSlot(0)">
        <span class="inv-key">1</span>🌱<span class="inv-count">x5</span>
      </div>
      <div class="inv-slot" onclick="selectSlot(1)">
        <span class="inv-key">2</span>💧<span class="inv-count">∞</span>
      </div>
      <div class="inv-slot" onclick="selectSlot(2)">
        <span class="inv-key">3</span>⚔️<span class="inv-count">x1</span>
      </div>
      <div class="inv-slot" onclick="selectSlot(3)">
        <span class="inv-key">4</span>🧪<span class="inv-count">x1</span>
      </div>
      <div class="inv-slot" onclick="selectSlot(4)">
        <span class="inv-key">5</span>⭐<span class="inv-count">x0</span>
      </div>
    </div>

    <!-- Shop Modal -->
    <div id="shopModal">
      <div class="shop-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
          <h3 style="color: #facc15; font-size: 18px;">🛒 ร้านค้านักผจญภัย</h3>
          <button onclick="closeShop()" style="background: none; border:none; color:#fff; font-size: 20px; cursor:pointer;">✕</button>
        </div>
        <div class="shop-item-row">
          <div>
            <div style="font-weight: bold;">🧪 ยาฟื้นฟูพลังชีวิต (+50 HP)</div>
            <div style="font-size: 11px; color:#cbd5e1;">ราคา 30 เหรียญ</div>
          </div>
          <button class="shop-buy-btn" onclick="buyItem('potion', 30)">ซื้อ</button>
        </div>
        <div class="shop-item-row">
          <div>
            <div style="font-weight: bold;">👟 รองเท้าสายลม (+ความเร็วเดิน)</div>
            <div style="font-size: 11px; color:#cbd5e1;">ราคา 60 เหรียญ</div>
          </div>
          <button class="shop-buy-btn" onclick="buyItem('speed', 60)">ซื้อ</button>
        </div>
        <div class="shop-item-row">
          <div>
            <div style="font-weight: bold;">⚔️ ดาบแสงอัปเกรด (+25% ดาเมจ)</div>
            <div style="font-size: 11px; color:#cbd5e1;">ราคา 100 เหรียญ</div>
          </div>
          <button class="shop-buy-btn" onclick="buyItem('weapon', 100)">ซื้อ</button>
        </div>
      </div>
    </div>

    <!-- Mobile Touch Controls -->
    <div id="touchControls">
      <div class="d-pad">
        <div></div>
        <div class="touch-btn" id="btnUp">▲</div>
        <div></div>
        <div class="touch-btn" id="btnLeft">◀</div>
        <div></div>
        <div class="touch-btn" id="btnRight">▶</div>
        <div></div>
        <div class="touch-btn" id="btnDown">▼</div>
        <div></div>
      </div>
      <div class="action-buttons">
        <div class="touch-btn action-btn-large" id="btnAction">ACT</div>
        <div class="touch-btn" style="background:#0284c7;" id="btnSkill">SKILL</div>
        <div class="touch-btn" style="background:#eab308; color:#1e1035;" onclick="openShop()">🛒</div>
      </div>
    </div>

    <!-- Game End Modal -->
    <div id="endModal">
      <div class="modal-card">
        <div style="font-size: 54px; margin-bottom: 8px;" id="endIcon">🏆</div>
        <h2 style="font-size: 24px; font-weight: bold; color: #fff; margin-bottom: 6px;" id="endTitle">ภารกิจสำเร็จ!</h2>
        <p style="color: #cbd5e1; font-size: 14px;" id="endDesc">คุณทำภารกิจและสะสมคะแนนได้ยอดเยี่ยม</p>
        <div style="margin: 16px 0; font-size: 34px; font-weight: 800; color: #fbbf24;" id="finalScore">100 แต้ม</div>
        <div id="starRating" style="font-size: 26px; margin-bottom: 12px;">⭐⭐⭐</div>
        <button class="restart-btn" onclick="restartGame()">เล่นใหม่อีกครั้ง</button>
      </div>
    </div>
  </div>

  <script>
    // --- Web Audio Synthesizer ---
    const AudioEngine = {
      ctx: null,
      init() {
        if (!this.ctx) this.ctx = new (window.AudioContext || window.webkitAudioContext)();
      },
      playTone(freq, type, duration, slideFreq = null) {
        try {
          this.init();
          const osc = this.ctx.createOscillator();
          const gain = this.ctx.createGain();
          osc.type = type;
          osc.frequency.setValueAtTime(freq, this.ctx.currentTime);
          if (slideFreq) {
            osc.frequency.exponentialRampToValueAtTime(slideFreq, this.ctx.currentTime + duration);
          }
          gain.gain.setValueAtTime(0.18, this.ctx.currentTime);
          gain.gain.exponentialRampToValueAtTime(0.01, this.ctx.currentTime + duration);
          osc.connect(gain);
          gain.connect(this.ctx.destination);
          osc.start();
          osc.stop(this.ctx.currentTime + duration);
        } catch(e) {}
      },
      action() { this.playTone(440, 'triangle', 0.1, 880); },
      coin() { this.playTone(987, 'sine', 0.14, 1318); },
      hit() { this.playTone(180, 'sawtooth', 0.2, 50); },
      levelUp() {
        this.playTone(523, 'triangle', 0.1);
        setTimeout(() => this.playTone(659, 'triangle', 0.1), 80);
        setTimeout(() => this.playTone(784, 'triangle', 0.1), 160);
        setTimeout(() => this.playTone(1046, 'triangle', 0.25), 240);
      },
      win() {
        this.playTone(523, 'triangle', 0.15);
        setTimeout(() => this.playTone(659, 'triangle', 0.15), 120);
        setTimeout(() => this.playTone(784, 'triangle', 0.3), 240);
      },
      plant() { this.playTone(330, 'sine', 0.1, 520); }
    };

    // --- Engine Configuration & Modular Features ---
    const FEATURES = {$featuresJson};
    const hasFeature = (k) => FEATURES.includes(k);

    const canvas = document.getElementById('gameCanvas');
    const ctx = canvas.getContext('2d');
    ctx.imageSmoothingEnabled = false;

    // Kenney 2D Sprites (All 11 elements)
    const spritePlayer = new Image(); spritePlayer.src = '{$playerImgEsc}';
    const spriteEnemy = new Image(); spriteEnemy.src = '{$enemyImgEsc}';
    const spriteBoss = new Image(); spriteBoss.src = '{$bossImgEsc}';
    const spriteItem = new Image(); spriteItem.src = '{$itemImgEsc}';
    const spriteCoin = new Image(); spriteCoin.src = '{$coinImgEsc}';
    const spriteHeart = new Image(); spriteHeart.src = '{$heartImgEsc}';
    const spriteBullet = new Image(); spriteBullet.src = '{$bulletImgEsc}';
    const spriteObstacle = new Image(); spriteObstacle.src = '{$obstacleImgEsc}';
    const spriteGround = new Image(); spriteGround.src = '{$groundImgEsc}';
    const spriteWall = new Image(); spriteWall.src = '{$wallImgEsc}';
    const spriteNpc = new Image(); spriteNpc.src = '{$npcImgEsc}';

    // World & Player State
    const WORLD = { width: 1400, height: 900 };
    let score = 0;
    let coins = 0;
    let health = 100;
    let stamina = 100;
    let level = 1;
    let exp = 0;
    let expToNext = 100;
    let timeLeft = 150; // 2.5 minutes
    let isGameOver = false;
    let activeSlot = 0;
    let inventory = [5, Infinity, 1, 1, 0];
    let combo = 0;
    let comboTimer = 0;
    let dayNightCycle = 0; // 0 to 1
    let attackPower = 25;
    let checkpoint = { x: 300, y: 300 };

    const player = {
      x: 300, y: 300,
      size: 34,
      speed: 4.2,
      vx: 0, vy: 0,
    };

    // Boss State
    const boss = {
      x: 1050, y: 450,
      size: 70,
      hp: 300,
      maxHp: 300,
      speed: 1.1,
      alive: true
    };

    // Plots (Farming / Harvesting System)
    const plots = [
      { x: 180, y: 180, w: 90, h: 90, state: 'empty', growTimer: 0 },
      { x: 290, y: 180, w: 90, h: 90, state: 'empty', growTimer: 0 },
      { x: 400, y: 180, w: 90, h: 90, state: 'empty', growTimer: 0 },
      { x: 180, y: 290, w: 90, h: 90, state: 'empty', growTimer: 0 },
      { x: 290, y: 290, w: 90, h: 90, state: 'empty', growTimer: 0 },
      { x: 400, y: 290, w: 90, h: 90, state: 'empty', growTimer: 0 },
    ];

    // Word Hunter Letters (if Word Puzzle)
    const wordTargets = [
      { char: 'D', x: 600, y: 220, collected: false },
      { char: 'E', x: 720, y: 360, collected: false },
      { char: 'S', x: 840, y: 200, collected: false },
      { char: 'I', x: 960, y: 340, collected: false },
      { char: 'G', x: 1100, y: 260, collected: false },
      { char: 'N', x: 1220, y: 380, collected: false },
    ];

    // Hazards / Spikes
    const obstacles = [
      { x: 550, y: 450, w: 50, h: 50, type: 'spikes' },
      { x: 700, y: 600, w: 50, h: 50, type: 'spikes' },
      { x: 900, y: 280, w: 50, h: 50, type: 'spikes' },
    ];

    // Checkpoint Beacon
    const checkpointStone = { x: 700, y: 450, size: 24, reached: false };

    // Monster Minions
    const monsters = [
      { x: 750, y: 250, size: 30, hp: 50, maxHp: 50, speed: 1.6, type: 'slime', alive: true },
      { x: 880, y: 500, size: 36, hp: 80, maxHp: 80, speed: 1.3, type: 'golem', alive: true },
      { x: 1100, y: 650, size: 32, hp: 60, maxHp: 60, speed: 1.7, type: 'bat', alive: true },
    ];

    // Particles & Floating Numbers
    const particles = [];
    const floatingTexts = [];

    // Keys State
    const keys = {};
    window.addEventListener('keydown', e => {
      keys[e.key.toLowerCase()] = true;
      if (e.code === 'Space') { e.preventDefault(); performAction(); }
      if (e.key.toLowerCase() === 'e') performSkill();
      if (e.key.toLowerCase() === 'b') openShop();
      if (['1','2','3','4','5'].includes(e.key)) selectSlot(parseInt(e.key) - 1);
    });
    window.addEventListener('keyup', e => { keys[e.key.toLowerCase()] = false; });

    // Touch Setup
    function setupTouch(id, keyName) {
      const el = document.getElementById(id);
      if (!el) return;
      const activate = (e) => { e.preventDefault(); keys[keyName] = true; };
      const deactivate = (e) => { e.preventDefault(); keys[keyName] = false; };
      el.addEventListener('touchstart', activate, { passive: false });
      el.addEventListener('touchend', deactivate, { passive: false });
      el.addEventListener('mousedown', activate);
      el.addEventListener('mouseup', deactivate);
    }
    setupTouch('btnUp', 'w');
    setupTouch('btnDown', 's');
    setupTouch('btnLeft', 'a');
    setupTouch('btnRight', 'd');

    document.getElementById('btnAction').addEventListener('click', performAction);
    document.getElementById('btnSkill').addEventListener('click', performSkill);

    function selectSlot(idx) {
      activeSlot = idx;
      document.querySelectorAll('.inv-slot').forEach((el, i) => {
        el.classList.toggle('active', i === idx);
      });
      AudioEngine.playTone(600 + idx * 60, 'sine', 0.05);
    }

    function createParticles(x, y, color, count = 10) {
      for (let i = 0; i < count; i++) {
        particles.push({
          x, y,
          vx: (Math.random() - 0.5) * 7,
          vy: (Math.random() - 0.5) * 7,
          life: 1,
          color
        });
      }
    }

    function spawnFloatingText(text, x, y, color = '#fbbf24') {
      floatingTexts.push({ text, x, y, vy: -1.5, life: 1, color });
    }

    function performSkill() {
      if (stamina < 30) return;
      stamina -= 30;
      AudioEngine.action();
      createParticles(player.x, player.y, '#60a5fa', 25);
      spawnFloatingText("💥 SHOCKWAVE!", player.x, player.y - 20, '#60a5fa');

      // Damage monsters in radius
      monsters.forEach(m => {
        if (!m.alive) return;
        const d = Math.hypot(m.x - player.x, m.y - player.y);
        if (d < 200) {
          m.hp -= attackPower * 1.5;
          m.x += (m.x - player.x) * 0.9;
          m.y += (m.y - player.y) * 0.9;
          AudioEngine.hit();
          spawnFloatingText('-' + Math.round(attackPower * 1.5), m.x, m.y - 10, '#ef4444');
          if (m.hp <= 0) {
            m.alive = false;
            handleDefeatEnemy(m, 50);
          }
        }
      });

      // Boss damage
      if (boss.alive) {
        const bd = Math.hypot(boss.x - player.x, boss.y - player.y);
        if (bd < 220) {
          boss.hp -= attackPower * 1.5;
          AudioEngine.hit();
          spawnFloatingText('-' + Math.round(attackPower * 1.5), boss.x, boss.y - 30, '#f87171');
          updateBossUI();
          if (boss.hp <= 0) {
            boss.alive = false;
            handleDefeatEnemy(boss, 250);
            triggerGameOver(true);
          }
        }
      }
    }

    function performAction() {
      if (isGameOver) return;
      AudioEngine.action();

      // 1. Interaction with Plots
      plots.forEach(plot => {
        const d = Math.hypot(player.x - (plot.x + plot.w/2), player.y - (plot.y + plot.h/2));
        if (d < 75) {
          if (activeSlot === 0 && plot.state === 'empty' && inventory[0] > 0) {
            inventory[0]--;
            plot.state = 'planted';
            plot.growTimer = 0;
            createParticles(plot.x + plot.w/2, plot.y + plot.h/2, '#4ade80');
            AudioEngine.plant();
            addScore(10);
            addExp(15);
            addCoins(2);
            showDialogue("🌱 ปลูกเมล็ดแล้ว! รดน้ำสิเพื่อช่วยให้โตไวขึ้น");
          } else if (activeSlot === 1 && plot.state === 'planted') {
            plot.state = 'watered';
            createParticles(plot.x + plot.w/2, plot.y + plot.h/2, '#38bdf8');
            AudioEngine.coin();
            addScore(15);
            addExp(15);
            addCoins(3);
          } else if (plot.state === 'grown') {
            plot.state = 'empty';
            inventory[3]++;
            createParticles(plot.x + plot.w/2, plot.y + plot.h/2, '#fbbf24', 18);
            AudioEngine.coin();
            addScore(40);
            addExp(30);
            addCoins(10);
            registerCombo();
            spawnFloatingText("+40 PTS 🥕", plot.x + plot.w/2, plot.y + plot.h/2 - 10, '#fbbf24');
            showDialogue("🎉 เก็บเกี่ยวผลผลิตสำเร็จ! นำเหรียญไปซื้อของอัปเกรดในร้านค้าได้");
          }
        }
      });

      // 2. Attack Monsters
      monsters.forEach(m => {
        if (!m.alive) return;
        const dist = Math.hypot(player.x - m.x, player.y - m.y);
        if (dist < 85) {
          m.hp -= attackPower;
          AudioEngine.hit();
          createParticles(m.x, m.y, '#ef4444', 12);
          spawnFloatingText('-' + attackPower, m.x, m.y - 10, '#ef4444');
          registerCombo();
          if (m.hp <= 0) {
            m.alive = false;
            handleDefeatEnemy(m, 50);
          }
        }
      });

      // 3. Attack Boss
      if (boss.alive) {
        const bd = Math.hypot(player.x - boss.x, player.y - boss.y);
        if (bd < 95) {
          boss.hp -= attackPower;
          AudioEngine.hit();
          createParticles(boss.x, boss.y, '#ef4444', 16);
          spawnFloatingText('-' + attackPower, boss.x, boss.y - 25, '#ef4444');
          registerCombo();
          updateBossUI();
          if (boss.hp <= 0) {
            boss.alive = false;
            handleDefeatEnemy(boss, 250);
            triggerGameOver(true);
          }
        }
      }

      // 4. Word Puzzle Collectibles
      wordTargets.forEach(wt => {
        if (!wt.collected) {
          const wd = Math.hypot(player.x - wt.x, player.y - wt.y);
          if (wd < 50) {
            wt.collected = true;
            createParticles(wt.x, wt.y, '#38bdf8', 15);
            AudioEngine.coin();
            addScore(50);
            addExp(25);
            addCoins(10);
            registerCombo();
            spawnFloatingText("LETTER " + wt.char + "!", wt.x, wt.y - 10, '#38bdf8');
          }
        }
      });

      updateInventoryUI();
    }

    function handleDefeatEnemy(enemy, bonusPoints) {
      addScore(bonusPoints);
      addExp(bonusPoints);
      addCoins(Math.round(bonusPoints / 4));
      createParticles(enemy.x, enemy.y, '#fbbf24', 24);
      spawnFloatingText("VICTORY! +" + bonusPoints, enemy.x, enemy.y - 20, '#fbbf24');
    }

    function registerCombo() {
      combo++;
      comboTimer = 3.5;
      const badge = document.getElementById('comboBadge');
      document.getElementById('comboCount').innerText = combo;
      badge.style.display = 'block';
    }

    function addScore(pts) {
      const multiplier = combo > 3 ? 2 : (combo > 1 ? 1.5 : 1);
      const total = Math.round(pts * multiplier);
      score += total;
      document.getElementById('scoreText').innerText = score;
      if (window.parent) {
        window.parent.postMessage({ type: 'dtg:score_update', score }, '*');
      }
    }

    function addCoins(amount) {
      coins += amount;
      document.getElementById('coinsText').innerText = coins;
    }

    function addExp(amount) {
      exp += amount;
      if (exp >= expToNext) {
        exp -= expToNext;
        level++;
        expToNext = Math.round(expToNext * 1.5);
        document.getElementById('playerLevel').innerText = level;
        AudioEngine.levelUp();
        spawnFloatingText("⭐ LEVEL UP! (" + level + ")", player.x, player.y - 30, '#4ade80');
        createParticles(player.x, player.y, '#4ade80', 30);
      }
      const expPercent = Math.min(100, (exp / expToNext) * 100);
      document.getElementById('expBar').style.width = expPercent + '%';
      document.getElementById('expText').innerText = exp + '/' + expToNext + ' XP';
    }

    function updateBossUI() {
      const fill = document.getElementById('bossHpFill');
      const text = document.getElementById('bossHpText');
      if (fill && text) {
        const pct = Math.max(0, Math.round((boss.hp / boss.maxHp) * 100));
        fill.style.width = pct + '%';
        text.innerText = pct + '%';
      }
    }

    function showDialogue(text, speaker = 'NPC ผู้ชี้นำ') {
      const box = document.getElementById('dialogueBox');
      document.getElementById('dialogueSpeaker').innerText = speaker;
      document.getElementById('dialogueText').innerText = text;
      box.style.display = 'block';
    }
    function closeDialogue() {
      document.getElementById('dialogueBox').style.display = 'none';
    }

    function openShop() {
      document.getElementById('shopModal').style.display = 'flex';
    }
    function closeShop() {
      document.getElementById('shopModal').style.display = 'none';
    }
    function buyItem(itemType, cost) {
      if (coins < cost) {
        alert("เหรียญไม่เพียงพอ!");
        return;
      }
      coins -= cost;
      document.getElementById('coinsText').innerText = coins;
      AudioEngine.coin();
      if (itemType === 'potion') {
        health = Math.min(100, health + 50);
        document.getElementById('healthBar').style.width = health + '%';
        document.getElementById('healthNum').innerText = Math.round(health) + '/100';
        spawnFloatingText("+50 HP ❤️", player.x, player.y - 20, '#4ade80');
      } else if (itemType === 'speed') {
        player.speed += 1.0;
        spawnFloatingText("SPEED UP! 👟", player.x, player.y - 20, '#38bdf8');
      } else if (itemType === 'weapon') {
        attackPower += 15;
        spawnFloatingText("POWER UP! ⚔️", player.x, player.y - 20, '#f43f5e');
      }
      closeShop();
    }

    function updateInventoryUI() {
      const slots = document.querySelectorAll('.inv-slot');
      if (slots[0]) slots[0].querySelector('.inv-count').innerText = 'x' + inventory[0];
      if (slots[3]) slots[3].querySelector('.inv-count').innerText = 'x' + inventory[3];
    }

    // --- Main Game Loop ---
    let lastTime = performance.now();
    let secondAcc = 0;

    function gameLoop(now) {
      const dt = (now - lastTime) / 1000;
      lastTime = now;

      if (!isGameOver) {
        update(dt);
      }
      render();

      requestAnimationFrame(gameLoop);
    }

    function update(dt) {
      // Movement
      let mx = 0, my = 0;
      if (keys['w'] || keys['arrowup']) my -= 1;
      if (keys['s'] || keys['arrowdown']) my += 1;
      if (keys['a'] || keys['arrowleft']) mx -= 1;
      if (keys['d'] || keys['arrowright']) mx += 1;

      if (mx !== 0 && my !== 0) {
        mx *= 0.7071; my *= 0.7071;
      }

      player.x = Math.max(player.size, Math.min(WORLD.width - player.size, player.x + mx * player.speed));
      player.y = Math.max(player.size, Math.min(WORLD.height - player.size, player.y + my * player.speed));

      // Day / Night cycle progression
      dayNightCycle = (dayNightCycle + dt * 0.02) % 1;
      const todEl = document.getElementById('timeOfDayText');
      if (todEl) {
        if (dayNightCycle < 0.4) todEl.innerText = '☀️ กลางวัน';
        else if (dayNightCycle < 0.6) todEl.innerText = '🌇 อัสดง';
        else todEl.innerText = '🌙 ค่ำคืน';
      }

      // Stamina regeneration
      if (stamina < 100) {
        stamina = Math.min(100, stamina + dt * 14);
        document.getElementById('staminaBar').style.width = stamina + '%';
        document.getElementById('staminaNum').innerText = Math.round(stamina) + '%';
      }

      // Combo decay
      if (comboTimer > 0) {
        comboTimer -= dt;
        if (comboTimer <= 0) {
          combo = 0;
          document.getElementById('comboBadge').style.display = 'none';
        }
      }

      // Checkpoint activation
      const cpd = Math.hypot(player.x - checkpointStone.x, player.y - checkpointStone.y);
      if (cpd < 50 && !checkpointStone.reached) {
        checkpointStone.reached = true;
        checkpoint = { x: checkpointStone.x, y: checkpointStone.y };
        createParticles(checkpointStone.x, checkpointStone.y, '#38bdf8', 20);
        AudioEngine.win();
        spawnFloatingText("🚩 CHECKPOINT ACTIVATED!", player.x, player.y - 25, '#38bdf8');
      }

      // Obstacles collision (Spikes)
      obstacles.forEach(obs => {
        if (player.x > obs.x && player.x < obs.x + obs.w && player.y > obs.y && player.y < obs.y + obs.h) {
          health = Math.max(0, health - dt * 25);
          createParticles(player.x, player.y, '#ef4444', 3);
          updateHealthUI();
        }
      });

      // Crop Growth
      plots.forEach(p => {
        if (p.state === 'planted' || p.state === 'watered') {
          const speedMultiplier = p.state === 'watered' ? 2.5 : 1.0;
          p.growTimer += dt * speedMultiplier;
          if (p.growTimer >= 7) {
            p.state = 'grown';
          }
        }
      });

      // Monster AI
      monsters.forEach(m => {
        if (!m.alive) return;
        const d = Math.hypot(player.x - m.x, player.y - m.y);
        if (d < 260 && d > 32) {
          m.x += ((player.x - m.x) / d) * m.speed;
          m.y += ((player.y - m.y) / d) * m.speed;
        } else if (d <= 32) {
          health = Math.max(0, health - dt * 18);
          updateHealthUI();
        }
      });

      // Boss AI
      if (boss.alive) {
        const bd = Math.hypot(player.x - boss.x, player.y - boss.y);
        if (bd < 350 && bd > 50) {
          boss.x += ((player.x - boss.x) / bd) * boss.speed;
          boss.y += ((player.y - boss.y) / bd) * boss.speed;
        } else if (bd <= 50) {
          health = Math.max(0, health - dt * 25);
          updateHealthUI();
        }
      }

      // Floating Texts
      for (let i = floatingTexts.length - 1; i >= 0; i--) {
        const ft = floatingTexts[i];
        ft.y += ft.vy;
        ft.life -= dt * 1.5;
        if (ft.life <= 0) floatingTexts.splice(i, 1);
      }

      // Particles
      for (let i = particles.length - 1; i >= 0; i--) {
        const p = particles[i];
        p.x += p.vx; p.y += p.vy;
        p.life -= dt * 2;
        if (p.life <= 0) particles.splice(i, 1);
      }

      // Countdown Timer
      secondAcc += dt;
      if (secondAcc >= 1) {
        secondAcc = 0;
        timeLeft--;
        const mins = Math.floor(timeLeft / 60).toString().padStart(2, '0');
        const secs = (timeLeft % 60).toString().padStart(2, '0');
        document.getElementById('timerText').innerText = mins + ':' + secs;
        if (timeLeft <= 0) {
          triggerGameOver(true);
        }
      }

      // Update Mini-map
      const mapX = (player.x / WORLD.width) * 96;
      const mapY = (player.y / WORLD.height) * 96;
      const pin = document.getElementById('miniMapPlayer');
      pin.style.left = mapX + 'px';
      pin.style.top = mapY + 'px';
    }

    function updateHealthUI() {
      document.getElementById('healthBar').style.width = health + '%';
      document.getElementById('healthNum').innerText = Math.round(health) + '/100';
      if (health <= 0) {
        if (checkpointStone.reached) {
          health = 60;
          player.x = checkpoint.x;
          player.y = checkpoint.y;
          spawnFloatingText("RESPAWNED AT CHECKPOINT! 🚩", player.x, player.y - 20, '#38bdf8');
          updateHealthUI();
        } else {
          triggerGameOver(false);
        }
      }
    }

    function render() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);

      ctx.save();
      const cx = Math.max(0, Math.min(WORLD.width - canvas.width, player.x - canvas.width / 2));
      const cy = Math.max(0, Math.min(WORLD.height - canvas.height, player.y - canvas.height / 2));
      ctx.translate(-cx, -cy);

      // Grid World
      ctx.strokeStyle = 'rgba(168, 85, 247, 0.12)';
      ctx.lineWidth = 1;
      for (let x = 0; x < WORLD.width; x += 60) {
        ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, WORLD.height); ctx.stroke();
      }
      for (let y = 0; y < WORLD.height; y += 60) {
        ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(WORLD.width, y); ctx.stroke();
      }

      // Draw Checkpoint / NPC Quest Giver
      if (spriteNpc.complete && spriteNpc.naturalWidth > 0) {
        ctx.drawImage(spriteNpc, checkpointStone.x - 20, checkpointStone.y - 20, 40, 40);
        if (checkpointStone.reached) {
          ctx.strokeStyle = '#38bdf8';
          ctx.lineWidth = 2;
          ctx.strokeRect(checkpointStone.x - 22, checkpointStone.y - 22, 44, 44);
        }
      } else {
        ctx.fillStyle = checkpointStone.reached ? '#0284c7' : '#64748b';
        ctx.beginPath();
        ctx.arc(checkpointStone.x, checkpointStone.y, checkpointStone.size, 0, Math.PI * 2);
        ctx.fill();
        ctx.font = '18px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('🚩', checkpointStone.x, checkpointStone.y + 6);
      }

      // Draw Hazards (Obstacles / Spikes)
      obstacles.forEach(obs => {
        if (spriteObstacle.complete && spriteObstacle.naturalWidth > 0) {
          ctx.drawImage(spriteObstacle, obs.x, obs.y, obs.w, obs.h);
        } else {
          ctx.fillStyle = '#450a0a';
          ctx.fillRect(obs.x, obs.y, obs.w, obs.h);
          ctx.strokeStyle = '#ef4444';
          ctx.lineWidth = 2;
          ctx.strokeRect(obs.x, obs.y, obs.w, obs.h);
          ctx.font = '22px sans-serif';
          ctx.textAlign = 'center';
          ctx.fillText('⚠️', obs.x + obs.w/2, obs.y + obs.h/2 + 7);
        }
      });

      // Draw Garden Plots
      plots.forEach((p, idx) => {
        ctx.fillStyle = p.state === 'watered' ? '#452613' : '#6b3e1f';
        ctx.fillRect(p.x, p.y, p.w, p.h);
        ctx.strokeStyle = '#8b5a2b';
        ctx.lineWidth = 3;
        ctx.strokeRect(p.x, p.y, p.w, p.h);

        ctx.font = '26px sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        if (p.state === 'planted') {
          ctx.fillText('🌱', p.x + p.w/2, p.y + p.h/2);
        } else if (p.state === 'watered') {
          ctx.fillText('🌿', p.x + p.w/2, p.y + p.h/2);
        } else if (p.state === 'grown') {
          if (spriteItem.complete && spriteItem.naturalWidth > 0) {
            ctx.drawImage(spriteItem, p.x + p.w/2 - 16, p.y + p.h/2 - 20, 32, 32);
          } else {
            ctx.fillText('🥕', p.x + p.w/2, p.y + p.h/2 - 4);
          }
          ctx.font = '11px sans-serif';
          ctx.fillStyle = '#fde047';
          ctx.fillText('กดเก็บเกี่ยว!', p.x + p.w/2, p.y + p.h - 12);
        } else {
          ctx.font = '11px sans-serif';
          ctx.fillStyle = '#a8754b';
          ctx.fillText('แปลง ' + (idx + 1), p.x + p.w/2, p.y + p.h/2);
        }
      });

      // Draw Word Collectibles
      wordTargets.forEach(wt => {
        if (!wt.collected) {
          ctx.fillStyle = 'rgba(56, 189, 248, 0.2)';
          ctx.beginPath();
          ctx.arc(wt.x, wt.y, 22, 0, Math.PI * 2);
          ctx.fill();
          ctx.strokeStyle = '#38bdf8';
          ctx.lineWidth = 2;
          ctx.stroke();

          ctx.font = 'bold 20px sans-serif';
          ctx.fillStyle = '#fff';
          ctx.textAlign = 'center';
          ctx.textBaseline = 'middle';
          ctx.fillText(wt.char, wt.x, wt.y);
        }
      });

      // Draw Minion Monsters
      monsters.forEach(m => {
        if (!m.alive) return;
        if (spriteEnemy.complete && spriteEnemy.naturalWidth > 0) {
          ctx.drawImage(spriteEnemy, m.x - m.size/2, m.y - m.size/2, m.size, m.size);
        } else {
          ctx.fillStyle = m.type === 'slime' ? '#22c55e' : (m.type === 'golem' ? '#a855f7' : '#ec4899');
          ctx.beginPath();
          ctx.arc(m.x, m.y, m.size / 2, 0, Math.PI * 2);
          ctx.fill();
          ctx.strokeStyle = '#fff';
          ctx.lineWidth = 2;
          ctx.stroke();
        }

        ctx.fillStyle = 'rgba(0,0,0,0.6)';
        ctx.fillRect(m.x - 20, m.y - m.size/2 - 10, 40, 5);
        ctx.fillStyle = '#ef4444';
        ctx.fillRect(m.x - 20, m.y - m.size/2 - 10, (m.hp / m.maxHp) * 40, 5);
      });

      // Draw Boss
      if (boss.alive) {
        if (spriteBoss.complete && spriteBoss.naturalWidth > 0) {
          ctx.drawImage(spriteBoss, boss.x - boss.size/2, boss.y - boss.size/2, boss.size, boss.size);
        } else {
          ctx.fillStyle = '#991b1b';
          ctx.beginPath();
          ctx.arc(boss.x, boss.y, boss.size / 2, 0, Math.PI * 2);
          ctx.fill();
          ctx.strokeStyle = '#f87171';
          ctx.lineWidth = 4;
          ctx.stroke();

          ctx.font = '36px sans-serif';
          ctx.textAlign = 'center';
          ctx.textBaseline = 'middle';
          ctx.fillText('👹', boss.x, boss.y);
        }
      }

      // Draw Player
      if (spritePlayer.complete && spritePlayer.naturalWidth > 0) {
        ctx.drawImage(spritePlayer, player.x - player.size/2, player.y - player.size/2, player.size, player.size);
      } else {
        ctx.fillStyle = '#a855f7';
        ctx.beginPath();
        ctx.arc(player.x, player.y, player.size / 2, 0, Math.PI * 2);
        ctx.fill();
        ctx.strokeStyle = '#facc15';
        ctx.lineWidth = 3;
        ctx.stroke();

        ctx.fillStyle = '#fff';
        ctx.beginPath();
        ctx.arc(player.x - 5, player.y - 2, 4, 0, Math.PI * 2);
        ctx.arc(player.x + 5, player.y - 2, 4, 0, Math.PI * 2);
        ctx.fill();
      }

      // Action Range Ring
      ctx.strokeStyle = 'rgba(250, 204, 21, 0.25)';
      ctx.setLineDash([4, 4]);
      ctx.beginPath();
      ctx.arc(player.x, player.y, 70, 0, Math.PI * 2);
      ctx.stroke();
      ctx.setLineDash([]);

      // Floating Numbers / Texts
      floatingTexts.forEach(ft => {
        ctx.font = 'bold 15px sans-serif';
        ctx.fillStyle = ft.color;
        ctx.globalAlpha = Math.max(0, ft.life);
        ctx.textAlign = 'center';
        ctx.fillText(ft.text, ft.x, ft.y);
      });

      // Particles
      particles.forEach(p => {
        ctx.fillStyle = p.color;
        ctx.globalAlpha = Math.max(0, p.life);
        ctx.beginPath();
        ctx.arc(p.x, p.y, 4 * p.life, 0, Math.PI * 2);
        ctx.fill();
      });
      ctx.globalAlpha = 1.0;

      // Day / Night Ambient Lighting Overlay
      if (hasFeature('day_night')) {
        let alpha = 0;
        if (dayNightCycle > 0.6) {
          alpha = (dayNightCycle - 0.6) * 1.8; // Night darkness
          ctx.fillStyle = `rgba(5, 2, 20, \${alpha * 0.75})`;
          ctx.fillRect(cx, cy, canvas.width, canvas.height);

          // Player Lantern Halo
          const grad = ctx.createRadialGradient(player.x, player.y, 20, player.x, player.y, 160);
          grad.addColorStop(0, 'rgba(250, 204, 21, 0.3)');
          grad.addColorStop(1, 'rgba(250, 204, 21, 0)');
          ctx.fillStyle = grad;
          ctx.beginPath();
          ctx.arc(player.x, player.y, 160, 0, Math.PI * 2);
          ctx.fill();
        }
      }

      ctx.restore();
    }

    function triggerGameOver(won) {
      isGameOver = true;
      const modal = document.getElementById('endModal');
      const title = document.getElementById('endTitle');
      const desc = document.getElementById('endDesc');
      const icon = document.getElementById('endIcon');
      const finalScore = document.getElementById('finalScore');
      const stars = document.getElementById('starRating');

      finalScore.innerText = score + ' แต้ม';
      modal.style.display = 'flex';

      if (won) {
        icon.innerText = '🏆';
        title.innerText = 'ภารกิจเสร็จสิ้น ยอดเยี่ยม!';
        desc.innerText = 'คุณเคลียร์เป้าหมายและสะสมคะแนนได้ระดับมาสเตอร์';
        stars.innerText = score > 300 ? '⭐⭐⭐' : (score > 150 ? '⭐⭐' : '⭐');
        AudioEngine.win();
      } else {
        icon.innerText = '💀';
        title.innerText = 'พลังชีวิตหมดลง';
        desc.innerText = 'พ่ายแพ้ในศึกครั้งนี้ ปรับกลยุทธ์แล้วมาลุยใหม่อีกครั้ง';
        stars.innerText = '💔';
        AudioEngine.hit();
      }

      if (window.parent) {
        window.parent.postMessage({ type: 'dtg:game_over', score, won }, '*');
      }
    }

    function restartGame() {
      score = 0; coins = 0; health = 100; stamina = 100; timeLeft = 150;
      level = 1; exp = 0; combo = 0;
      isGameOver = false;
      player.x = 300; player.y = 300;
      inventory = [5, Infinity, 1, 1, 0];
      boss.hp = boss.maxHp; boss.alive = true;
      plots.forEach(p => { p.state = 'empty'; p.growTimer = 0; });
      monsters.forEach(m => { m.alive = true; m.hp = m.maxHp; });
      wordTargets.forEach(w => w.collected = false);

      document.getElementById('healthBar').style.width = '100%';
      document.getElementById('healthNum').innerText = '100/100';
      document.getElementById('staminaBar').style.width = '100%';
      document.getElementById('scoreText').innerText = '0';
      document.getElementById('coinsText').innerText = '0';
      document.getElementById('endModal').style.display = 'none';
      updateBossUI();
      updateInventoryUI();
      AudioEngine.action();
    }

    requestAnimationFrame(gameLoop);
  </script>
</body>
</html>
HTML;

        return $html;
    }

    /**
     * Helper to output CSS display value based on boolean flag
     */
    protected function cssDisplay(bool $condition): string
    {
        return $condition ? 'flex' : 'none';
    }
}
