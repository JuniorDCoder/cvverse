<?php

require __DIR__ . '/vendor/autoload.php';

use App\Services\GeminiService;
use Illuminate\Support\Facades\Log;

// Mock Log for script
class MockLog {
    public static function error($message, $context = []) {
        echo "LOG ERROR: $message\n";
        print_r($context);
    }
}

// Reflection to test private method
function testParse($text) {
    $service = new GeminiService();
    $reflection = new \ReflectionClass($service);
    $method = $reflection->getMethod('parseJsonResponse');
    $method->setAccessible(true);
    
    $result = $method->invoke($service, $text);
    echo "Input: " . substr($text, 0, 50) . (strlen($text) > 50 ? "..." : "") . "\n";
    echo "Result: " . ($result !== null ? "SUCCESS" : "FAILURE") . "\n";
    if ($result !== null) {
        echo "Keys: " . implode(', ', array_keys($result)) . "\n";
    }
    echo "-------------------\n";
}

// Case 1: Pure JSON
testParse('{"key": "value"}');

// Case 2: Markdown JSON
testParse("```json\n{\"key\": \"value\"}\n```");

// Case 3: Text then Markdown JSON
testParse("Here is your data:\n```json\n{\"key\": \"value\"}\n```\nHope it helps!");

// Case 4: Text then raw JSON
testParse("Sure! Here it is: {\"key\": \"value\"} let me know if you need anything else.");

// Case 5: The specific failing case from logs
$failingText = '{"match_score":90,"summary_suggestions":["Explicitly integrate keywords..."],"experience_suggestions":["For the \'Traitz Tech (CTO)\'..."],"skills_to_add":["Design Systems..."],"skills_to_emphasize":["React/Next.js..."],"keywords_missing":["Design Systems..."],"overall_recommendations":["**Elevate Seniority Language..."]}';
testParse($failingText);
