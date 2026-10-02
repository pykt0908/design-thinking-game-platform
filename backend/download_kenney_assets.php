<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetPack;
use App\Models\User;
use Illuminate\Support\Facades\File;

echo "=== STARTING KENNEY 2D ASSET DOWNLOADER & IMPORTER ===\n";

$storageKenneyDir = storage_path('app/public/kenney');
$frontendKenneyDir = base_path('../frontend/public/assets/kenney');

File::ensureDirectoryExists($storageKenneyDir);
File::ensureDirectoryExists($frontendKenneyDir);

$packs = [
    'pixel-platformer' => [
        'name' => 'Kenney Pixel Platformer',
        'url' => 'https://kenney.nl/media/pages/assets/pixel-platformer/33bb4921eb-1696667883/kenney_pixel-platformer.zip',
        'theme' => 'retro_pixel',
        'desc' => 'ชุดตัวละคร มอนสเตอร์ เหรียญ และบล็อกแพลตฟอร์มพิกเซลเรโทร'
    ],
    'pixel-platformer-food' => [
        'name' => 'Kenney Food & Crops Expansion',
        'url' => 'https://kenney.nl/media/pages/assets/pixel-platformer-food-expansion/76330de2bf-1696596511/kenney_pixel-platformer-food-expansion.zip',
        'theme' => 'farming_food',
        'desc' => 'ชุดผัก ผลไม้ อาหาร และไอเทมเก็บเกี่ยวสำหรับเกมปลูกผักและทำอาหาร'
    ],
    'tiny-dungeon' => [
        'name' => 'Kenney Tiny Dungeon',
        'url' => 'https://kenney.nl/media/pages/assets/tiny-dungeon/f8422efb44-1674742415/kenney_tiny-dungeon.zip',
        'theme' => 'dungeon_rpg',
        'desc' => 'ชุดฮีโร่ อัศวิน โกบลิน มอนสเตอร์ หีบสมบัติ และอาวุธสำหรับเกม RPG'
    ],
    'tiny-town' => [
        'name' => 'Kenney Tiny Town',
        'url' => 'https://kenney.nl/media/pages/assets/tiny-town/a415fbeb49-1735736916/kenney_tiny-town.zip',
        'theme' => 'town_village',
        'desc' => 'ชุดสิ่งก่อสร้าง ต้นไม้ ธรรมชาติ ชาวเมือง สำหรับเกมจำลองชีวิตและเมือง'
    ],
    'simple-space' => [
        'name' => 'Kenney Simple Space',
        'url' => 'https://kenney.nl/media/pages/assets/simple-space/b9b0968a6b-1677578143/kenney_simple-space.zip',
        'theme' => 'space_sci_fi',
        'desc' => 'ชุดยานอวกาศ อุกกาบาต เลเซอร์ และดวงดาว สำหรับเกมตะลุยอวกาศ'
    ],
    'ui-pack' => [
        'name' => 'Kenney UI Pack',
        'url' => 'https://kenney.nl/media/pages/assets/ui-pack/f651646eab-1718203990/kenney_ui-pack.zip',
        'theme' => 'ui_elements',
        'desc' => 'ปุ่มกด กรอบหน้าต่าง หัวใจหลอดเลือด และไอคอนตกแต่ง HUD'
    ],
];

// Ensure Asset Categories in DB
$categories = [
    'character' => AssetCategory::firstOrCreate(['slug' => 'character'], [
        'name' => 'ตัวละคร & ฮีโร่ (Characters)',
        'description' => 'ตัวละครผู้เล่น NPC และฮีโร่สายอาชีพต่าง ๆ',
        'icon' => 'mdi-account',
    ]),
    'enemy' => AssetCategory::firstOrCreate(['slug' => 'enemy'], [
        'name' => 'มอนสเตอร์ & บอส (Monsters & Bosses)',
        'description' => 'ศัตรู มอนสเตอร์ โกเลม มังกร และบอสประจำด่าน',
        'icon' => 'mdi-sword-cross',
    ]),
    'item' => AssetCategory::firstOrCreate(['slug' => 'item'], [
        'name' => 'ไอเทม & ผลผลิต (Items & Food)',
        'description' => 'ผักผลไม้ เหรียญทอง หีบสมบัติ อาวุธ และยาฟื้นพลัง',
        'icon' => 'mdi-food-apple',
    ]),
    'environment' => AssetCategory::firstOrCreate(['slug' => 'environment'], [
        'name' => 'ฉาก & ธรรมชาติ (Environment & Tiles)',
        'description' => 'พื้นดิน หิน ต้นไม้ แปลงเกษตร และสิ่งก่อสร้าง',
        'icon' => 'mdi-tree',
    ]),
    'ui' => AssetCategory::firstOrCreate(['slug' => 'ui'], [
        'name' => 'ปุ่มกด & HUD (UI Elements)',
        'description' => 'ปุ่มกด หลอดเลือด ไอคอน และแผงควบคุม',
        'icon' => 'mdi-gamepad-variant',
    ]),
    'space' => AssetCategory::firstOrCreate(['slug' => 'space'], [
        'name' => 'ยานอวกาศ & ไซไฟ (Space & Sci-Fi)',
        'description' => 'ยานรบ อุกกาบาต ลำแสงเลเซอร์ และดวงดาว',
        'icon' => 'mdi-rocket',
    ]),
];

$adminUser = User::first();
$totalImported = 0;

foreach ($packs as $key => $info) {
    echo "\n>>> Processing Pack: {$info['name']} ...\n";
    $zipPath = storage_path("app/public/kenney_{$key}.zip");
    $extractPath = "{$storageKenneyDir}/{$key}";

    // Download zip if not already downloaded
    if (!file_exists($zipPath) || filesize($zipPath) < 1000) {
        echo "Downloading from {$info['url']} ...\n";
        $ch = curl_init($info['url']);
        $fp = fopen($zipPath, 'wb');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
        curl_exec($ch);
        $err = curl_error($ch);
        if ($err) {
            echo "cURL error: {$err}\n";
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);

        if ($httpCode !== 200 || filesize($zipPath) < 1000) {
            echo "Failed to download {$info['name']} (HTTP {$httpCode})\n";
            continue;
        }
        echo "Download finished! Size: " . round(filesize($zipPath) / 1024) . " KB\n";
    } else {
        echo "Using cached zip: " . round(filesize($zipPath) / 1024) . " KB\n";
    }

    // Extract zip
    $zip = new ZipArchive();
    if ($zip->open($zipPath) === true) {
        $zip->extractTo($extractPath);
        $zip->close();
        echo "Extracted to {$extractPath}\n";
    } else {
        echo "Failed to extract {$zipPath}\n";
        continue;
    }

    // Create Asset Pack in DB
    $dbPack = AssetPack::firstOrCreate(['slug' => 'kenney-' . $key], [
        'name' => $info['name'],
        'description' => $info['desc'],
        'theme' => $info['theme'],
        'is_system' => true,
    ]);

    // Find and copy individual PNG images
    $allPngFiles = File::allFiles($extractPath);
    $importedInPack = 0;

    foreach ($allPngFiles as $file) {
        $filename = $file->getFilename();
        $relativeSubPath = str_replace('\\', '/', $file->getRelativePathname());

        // Skip spritesheets or preview files, prioritize tile/character PNGs
        if (str_contains(strtolower($filename), 'sheet') || str_contains(strtolower($filename), 'sample') || str_contains(strtolower($filename), 'preview')) {
            continue;
        }

        // Only take PNG files
        if (strtolower($file->getExtension()) !== 'png') {
            continue;
        }

        // Limit per pack to top 60 best icons to avoid overwhelming DB
        if ($importedInPack >= 60) {
            break;
        }

        // Determine category & type
        $cat = 'item';
        $type = 'item';
        $lowerName = strtolower($filename . ' ' . $relativeSubPath);

        if (str_contains($lowerName, 'character') || str_contains($lowerName, 'hero') || str_contains($lowerName, 'player') || str_contains($lowerName, 'alien') || str_contains($lowerName, 'knight')) {
            $cat = 'character';
            $type = 'character';
        } elseif (str_contains($lowerName, 'enemy') || str_contains($lowerName, 'monster') || str_contains($lowerName, 'slime') || str_contains($lowerName, 'zombie') || str_contains($lowerName, 'boss') || str_contains($lowerName, 'bat')) {
            $cat = 'enemy';
            $type = 'character';
        } elseif (str_contains($lowerName, 'ship') || str_contains($lowerName, 'meteor') || str_contains($lowerName, 'laser') || str_contains($lowerName, 'space')) {
            $cat = 'space';
            $type = 'object';
        } elseif (str_contains($lowerName, 'button') || str_contains($lowerName, 'panel') || str_contains($lowerName, 'icon') || str_contains($lowerName, 'ui') || str_contains($lowerName, 'heart')) {
            $cat = 'ui';
            $type = 'ui';
        } elseif (str_contains($lowerName, 'tile') || str_contains($lowerName, 'tree') || str_contains($lowerName, 'wall') || str_contains($lowerName, 'ground') || str_contains($lowerName, 'bush')) {
            $cat = 'environment';
            $type = 'background';
        }

        // Copy to public frontend folder for fast direct serving
        $frontendDest = "{$frontendKenneyDir}/{$key}/{$filename}";
        File::ensureDirectoryExists(dirname($frontendDest));
        File::copy($file->getRealPath(), $frontendDest);

        // Web accessible path
        $publicUrl = "/storage/kenney/{$key}/{$relativeSubPath}";
        $frontendUrl = "/assets/kenney/{$key}/{$filename}";

        $cleanName = ucwords(str_replace(['_', '-'], ' ', pathinfo($filename, PATHINFO_FILENAME)));

        Asset::updateOrCreate(
            [
                'name' => $cleanName,
                'asset_pack_id' => $dbPack->id,
            ],
            [
                'description' => "Kenney 2D: {$cleanName} (CC0 Public Domain)",
                'type' => $type,
                'category_id' => $categories[$cat]->id,
                'file_path' => $frontendUrl,
                'preview_url' => $frontendUrl,
                'style' => 'pixel_art',
                'theme' => $info['theme'],
                'license' => 'CC0 (Public Domain)',
                'author' => 'Kenney (kenney.nl)',
                'is_public' => true,
                'uploaded_by' => $adminUser ? $adminUser->id : null,
            ]
        );

        $importedInPack++;
        $totalImported++;
    }

    echo "Pack {$info['name']}: Imported {$importedInPack} assets!\n";
}

echo "\n=== COMPLETED! Total Kenney 2D Assets Imported: {$totalImported} ===\n";
