<?php
// Text-to-Speech Converter
// Created by MoMoein and Grok (xAI) - March 2025

/**
 * AudioGenerator class handles text-to-speech conversion using an external API.
 * It processes input text, voice, and vibe to generate audio output as base64.
 */
class AudioGenerator
{
    private $apiUrl = 'https://www.openai.fm/api/generate';
    private $cookies = [
        '_ga' => 'GA1.1.2108490069.1742716958',
        '_ga_NME7NXL4L0' => 'GS1.1.1742716958.1.0.1742716958.0.0.0'
    ];

    public function generate($text, $voice, $vibe)
    {
        header('Content-Type: application/json');

        if (empty($text)) {
            return json_encode(['success' => false, 'error' => 'متن ورودی خالی است']);
        }

        $prompt = "$vibe. Voice: High-energy, upbeat, and encouraging, projecting enthusiasm and motivation. Punctuation: Short, punchy sentences with strategic pauses to maintain excitement and clarity. Delivery: Fast-paced and dynamic, with rising intonation to build momentum and keep engagement high. Phrasing: Action-oriented and direct, using motivational cues to push participants forward. Tone: Positive, energetic, and empowering, creating an atmosphere of encouragement and achievement.";

        $params = [
            'input' => $text,
            'prompt' => $prompt,
            'voice' => strtolower($voice),
            'generation' => '4470afbf-d11b-4e23-a7b0-eed96a722d6f'
        ];

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->apiUrl . '?' . http_build_query($params));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_COOKIE, http_build_query($this->cookies, '', '; '));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                return json_encode(['success' => false, 'error' => 'خطای اتصال: ' . $error]);
            }

            curl_close($ch);
            $audioBase64 = base64_encode($response);

            return json_encode([
                'success' => true,
                'audio_data' => "data:audio/mp3;base64," . $audioBase64
            ]);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $generator = new AudioGenerator();
    echo $generator->generate($input['text'] ?? '', $input['voice'] ?? 'fable', $input['vibe'] ?? 'Dramatic');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مبدل متن به گفتار</title>
    <link href="https://cdn.jsdelivr.net/npm/vazir-font@30.1.0/dist/font-face.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="container">
        <div class="step" data-id="step-1">
            <h2 class="title">مبدل متن به گفتار</h2>
            <h3 class="subtitle">مرحله ۱: متن خود را وارد کنید</h3>
            <textarea class="text-input" placeholder="متن خود را اینجا وارد کنید..."></textarea>
            <div class="button-group">
                <button class="btn prev-btn hidden">قبلی</button>
                <button class="btn next-btn" data-action="next" data-next="step-2">بعدی</button>
            </div>
        </div>

        <div class="step" data-id="step-2">
            <h3 class="subtitle">مرحله ۲: نوع صدا را انتخاب کنید</h3>
            <div class="radio-group">
                <?php
                $voices = ['Alloy' => 'آلیاژ', 'Ash' => 'خاکستر', 'Ballad' => 'بالاد', 'Coral' => 'مرجان', 'Echo' => 'اکو', 'Fable' => 'افسانه', 'Onyx' => 'اونیکس', 'Nova' => 'نووا', 'Sage' => 'سیج', 'Shimmer' => 'شیمر', 'Verse' => 'ورس'];
                foreach ($voices as $voice => $label) {
                    $checked = $voice === 'Fable' ? 'checked' : '';
                    echo "<label class='radio-button'>
                            <input type='radio' name='voice' value='$voice' $checked>
                            <span class='radio-label'>
                                <span>$label</span>
                                <span class='led'></span>
                            </span>
                          </label>";
                }
                ?>
            </div>
            <div class="button-group">
                <button class="btn prev-btn" data-action="prev" data-prev="step-1">قبلی</button>
                <button class="btn next-btn" data-action="next" data-next="step-3">بعدی</button>
            </div>
        </div>

        <div class="step" data-id="step-3">
            <h3 class="subtitle">مرحله ۳: لحن صدا را انتخاب کنید</h3>
            <div class="radio-group">
                <?php
                $vibes = ['Dramatic' => 'دراماتیک', 'Calm' => 'آرام', 'Sincere' => 'صادقانه', 'Fitness Instructor' => 'مربی تناسب اندام', 'Medieval Knight' => 'شوالیه قرون وسطی'];
                foreach ($vibes as $vibe => $label) {
                    $checked = $vibe === 'Dramatic' ? 'checked' : '';
                    echo "<label class='radio-button'>
                            <input type='radio' name='vibe' value='$vibe' $checked>
                            <span class='radio-label'>
                                <span>$label</span>
                                <span class='led'></span>
                            </span>
                          </label>";
                }
                ?>
            </div>
            <div class="button-group">
                <button class="btn prev-btn" data-action="prev" data-prev="step-2">قبلی</button>
                <button class="btn generate-btn" data-action="generate">تولید صدا</button>
            </div>
        </div>

        <div class="step" data-id="step-4">
            <h3 class="subtitle">مرحله ۴: نتیجه</h3>
            <div class="spinner"></div>
            <div class="audio-player">
                <audio class="audio-element" controls></audio>
            </div>
            <div class="button-group">
                <button class="btn prev-btn" data-action="prev" data-prev="step-3">قبلی</button>
                <button class="btn reset-btn" data-action="reset">شروع مجدد</button>
            </div>
        </div>
    </div>
    <script src="script.js"></script>
</body>

</html>