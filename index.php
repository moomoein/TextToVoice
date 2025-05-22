<?php
<?php
// Text-to-Speech Converter
// Created by MoMoein and Grok (xAI) - March 2025

// Load application configuration settings, such as API URL and cookies.
require_once 'config.php';

/**
 * @class AudioGenerator
 * @description Handles text-to-speech (TTS) conversion by interacting with an external API.
 * It processes user-provided text, selected voice, and vibe to generate audio data.
 * The class also provides static methods for rendering HTML options for voice and vibe selection.
 */
class AudioGenerator
{
    /**
     * @var string A template for the generation prompt, with a placeholder for the vibe.
     * This prompt guides the TTS engine in terms of style, tone, and delivery.
     */
    private const GENERATION_PROMPT = "%s. Voice: High-energy, upbeat, and encouraging, projecting enthusiasm and motivation. Punctuation: Short, punchy sentences with strategic pauses to maintain excitement and clarity. Delivery: Fast-paced and dynamic, with rising intonation to build momentum and keep engagement high. Phrasing: Action-oriented and direct, using motivational cues to push participants forward. Tone: Positive, energetic, and empowering, creating an atmosphere of encouragement and achievement.";

    /**
     * @method generate
     * @description Generates audio from text using an external API.
     * It constructs the API request, handles the cURL session, and returns a JSON response
     * containing either the base64 encoded audio data or an error message.
     *
     * @param string $text The text to be converted to speech.
     * @param string $voice The selected voice for the TTS generation.
     * @param string $vibe The selected vibe to influence the audio output's style.
     * @return string JSON encoded string with 'success' (boolean) and 'audio_data' or 'error' (string).
     */
    public function generate($text, $voice, $vibe)
    {
        // Set content type to JSON for the response.
        header('Content-Type: application/json');

        // Validate input text.
        if (empty($text)) {
            return json_encode(['success' => false, 'error' => 'متن ورودی خالی است']); // Input text is empty.
        }

        // Prepare the prompt by inserting the selected vibe into the template.
        $prompt = sprintf(self::GENERATION_PROMPT, $vibe);

        // Parameters for the API request.
        $params = [
            'input' => $text,
            'prompt' => $prompt,
            'voice' => strtolower($voice), // API expects voice name in lowercase.
            'generation' => '4470afbf-d11b-4e23-a7b0-eed96a722d6f' // Static generation ID.
        ];

        try {
            $ch = curl_init();
            // Set cURL options: URL with query parameters, expect return transfer, set cookies, and enable SSL verification.
            curl_setopt($ch, CURLOPT_URL, API_URL . '?' . http_build_query($params));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $cookieString = '';
            foreach (API_COOKIES as $name => $value) {
                $cookieString .= $name . '=' . $value . '; ';
            }
            curl_setopt($ch, CURLOPT_COOKIE, rtrim($cookieString, '; ')); // Build cookie string from config.
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Enforce SSL peer verification for security.

            $response = curl_exec($ch);

            // Check for cURL errors during the request.
            if (curl_errno($ch)) {
                $error = curl_error($ch);
                $errno = curl_errno($ch);
                curl_close($ch);
                return json_encode(['success' => false, 'error' => 'خطای اتصال cURL: (' . $errno . ') ' . $error]); // cURL connection error.
            }

            curl_close($ch);
            // Encode the binary audio response to base64 for JSON embedding.
            $audioBase64 = base64_encode($response);

            // Return success response with base64 encoded audio data.
            return json_encode([
                'success' => true,
                'audio_data' => "data:audio/mp3;base64," . $audioBase64 // Prepend data URI scheme for direct use in <audio> src.
            ]);
        } catch (Exception $e) {
            // Catch any other exceptions during the process.
            return json_encode(['success' => false, 'error' => 'خطای عمومی: ' . $e->getMessage()]); // General error.
        }
    }

    /**
     * @method renderVoiceOptions
     * @description Generates HTML for voice selection radio buttons.
     *
     * @param array $voices An associative array where keys are voice values (e.g., 'Fable') 
     *                      and values are display labels (e.g., 'افسانه').
     * @param string $selectedValue The voice value that should be pre-selected (default is 'Fable').
     * @return string HTML string containing the radio button options.
     */
    public static function renderVoiceOptions(array $voices, string $selectedValue = 'Fable'): string
    {
        $html = '';
        foreach ($voices as $value => $label) {
            $checked = ($value === $selectedValue) ? 'checked' : ''; // Mark selected value as checked.
            $html .= "<label class='radio-button'>
                        <input type='radio' name='voice' value='{$value}' {$checked}>
                        <span class='radio-label'>
                            <span>{$label}</span>
                            <span class='led'></span>
                        </span>
                      </label>";
        }
        return $html;
    }

    /**
     * @method renderVibeOptions
     * @description Generates HTML for vibe selection radio buttons.
     *
     * @param array $vibes An associative array where keys are vibe values (e.g., 'Dramatic')
     *                     and values are display labels (e.g., 'دراماتیک').
     * @param string $selectedValue The vibe value that should be pre-selected (default is 'Dramatic').
     * @return string HTML string containing the radio button options.
     */
    public static function renderVibeOptions(array $vibes, string $selectedValue = 'Dramatic'): string
    {
        $html = '';
        foreach ($vibes as $value => $label) {
            $checked = ($value === $selectedValue) ? 'checked' : ''; // Mark selected value as checked.
            $html .= "<label class='radio-button'>
                        <input type='radio' name='vibe' value='{$value}' {$checked}>
                        <span class='radio-label'>
                            <span>{$label}</span>
                            <span class='led'></span>
                        </span>
                      </label>";
        }
        return $html;
    }
}

// --- Request Handling for POST ---
// This block processes incoming POST requests, typically from the frontend JavaScript.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode JSON input from the request body.
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Sanitize and set default values for input parameters.
    $text = $input['text'] ?? ''; 
    $voice = $input['voice'] ?? 'fable'; // Default voice if not provided.
    $vibe = $input['vibe'] ?? 'Dramatic';   // Default vibe if not provided.

    // Instantiate the generator and generate audio.
    $generator = new AudioGenerator();
    echo $generator->generate($text, $voice, $vibe);
    exit; // Terminate script after sending JSON response to prevent further HTML output.
}
?>

<!-- 
    Main HTML structure for the Text-to-Speech Web Application.
    The application is presented as a series of steps to guide the user.
    Localization: Persian (fa), Right-to-Left (rtl) text direction.
-->
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مبدل متن به گفتار</title> <!-- Text-to-Speech Converter -->
    <!-- External stylesheet for Vazir font and custom application styles -->
    <link href="https://cdn.jsdelivr.net/npm/vazir-font@30.1.0/dist/font-face.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <!-- Main application container -->
    <div class="container">
        
        <!-- Step 1: Text Input -->
        <!-- This step allows the user to input the text they want to convert to speech. -->
        <div class="step" data-id="step-1">
            <h2 class="title">مبدل متن به گفتار</h2> <!-- Text-to-Speech Converter -->
            <h3 class="subtitle">مرحله ۱: متن خود را وارد کنید</h3> <!-- Step 1: Enter your text -->
            <textarea class="text-input" placeholder="متن خود را اینجا وارد کنید..." aria-label="ورودی متن"></textarea> <!-- Enter your text here... -->
            <div class="button-group">
                <button class="btn prev-btn hidden" data-action="prev" data-prev="step-1">قبلی</button> <!-- Previous (Initially hidden) -->
                <button class="btn next-btn" data-action="next" data-next="step-2">بعدی</button> <!-- Next -->
            </div>
        </div>

        <!-- Step 2: Voice Selection -->
        <!-- Users select their preferred voice type from a list of radio options. -->
        <div class="step" data-id="step-2">
            <h3 class="subtitle">مرحله ۲: نوع صدا را انتخاب کنید</h3> <!-- Step 2: Choose the voice type -->
            <div class="radio-group">
                <?php
                // Define available voices (Key => Persian Label)
                $voices = [
                    'Alloy' => 'آلیاژ', 'Ash' => 'خاکستر', 'Ballad' => 'بالاد', 'Coral' => 'مرجان',
                    'Echo' => 'اکو', 'Fable' => 'افسانه', 'Onyx' => 'اونیکس', 'Nova' => 'نووا',
                    'Sage' => 'سیج', 'Shimmer' => 'شیمر', 'Verse' => 'ورس'
                ];
                // Render voice options using the AudioGenerator class method.
                echo AudioGenerator::renderVoiceOptions($voices);
                ?>
            </div>
            <div class="button-group">
                <button class="btn prev-btn" data-action="prev" data-prev="step-1">قبلی</button> <!-- Previous -->
                <button class="btn next-btn" data-action="next" data-next="step-3">بعدی</button> <!-- Next -->
            </div>
        </div>

        <!-- Step 3: Vibe Selection -->
        <!-- Users select the desired tone or "vibe" for the generated audio. -->
        <div class="step" data-id="step-3">
            <h3 class="subtitle">مرحله ۳: لحن صدا را انتخاب کنید</h3> <!-- Step 3: Choose the voice tone -->
            <div class="radio-group">
                <?php
                // Define available vibes (Key => Persian Label)
                $vibes = [
                    'Dramatic' => 'دراماتیک', 'Calm' => 'آرام', 'Sincere' => 'صادقانه',
                    'Fitness Instructor' => 'مربی تناسب اندام', 'Medieval Knight' => 'شوالیه قرون وسطی'
                ];
                // Render vibe options using the AudioGenerator class method.
                echo AudioGenerator::renderVibeOptions($vibes);
                ?>
            </div>
            <div class="button-group">
                <button class="btn prev-btn" data-action="prev" data-prev="step-2">قبلی</button> <!-- Previous -->
                <button class="btn generate-btn" data-action="generate">تولید صدا</button> <!-- Generate Audio -->
            </div>
        </div>

        <!-- Step 4: Result Display -->
        <!-- This step shows a spinner during processing and then the audio player with the result. -->
        <div class="step" data-id="step-4">
            <h3 class="subtitle">مرحله ۴: نتیجه</h3> <!-- Step 4: Result -->
            <div class="spinner"></div> <!-- Loading indicator, shown while API request is in progress -->
            <div class="audio-player">
                <audio class="audio-element" controls aria-label="پخش کننده صدا"></audio> <!-- Audio player for the generated speech -->
            </div>
            <div class="button-group">
                <button class="btn prev-btn" data-action="prev" data-prev="step-3">قبلی</button> <!-- Previous -->
                <button class="btn reset-btn" data-action="reset">شروع مجدد</button> <!-- Restart -->
            </div>
        </div>
    </div>
    <!-- Link to the main JavaScript file for interactivity -->
    <script src="script.js"></script>
</body>

</html>