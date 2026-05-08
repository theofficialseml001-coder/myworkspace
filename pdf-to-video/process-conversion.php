<?php
/**
 * Background Processing Script - PDF to Video Converter
 * This script handles the actual conversion process
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

// Increase execution time for long conversions
set_time_limit(600); // 10 minutes
ini_set('memory_limit', '512M');

// Get conversion ID
$conversion_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($conversion_id <= 0) {
    http_response_code(400);
    echo "Invalid conversion ID";
    exit;
}

// Fetch conversion details
$sql = "SELECT * FROM conversions WHERE id = $conversion_id AND video_status = 'processing'";
$result = mysqli_query($conn, $sql);
$conversion = mysqli_fetch_assoc($result);

if (!$conversion) {
    http_response_code(404);
    echo "Conversion not found or already processed";
    exit;
}

$error_message = '';

try {
    // Step 1: Extract text from PDF
    echo "Step 1: Extracting text from PDF...\n";
    $pdf_path = $conversion['file_path'];
    
    if (!file_exists($pdf_path)) {
        throw new Exception("PDF file not found");
    }
    
    $extracted_text = extractTextFromPDF($pdf_path);
    
    if (empty(trim($extracted_text))) {
        throw new Exception("No text could be extracted from the PDF");
    }
    
    // Update database with extracted text
    $script_content = mysqli_real_escape_string($conn, $extracted_text);
    $update_sql = "UPDATE conversions SET script_content = '$script_content' WHERE id = $conversion_id";
    mysqli_query($conn, $update_sql);
    
    // Step 2: Split text into slides
    echo "Step 2: Splitting text into slides...\n";
    $max_chars = (int)getSetting($conn, 'slide_duration', 5) * 100; // Approximate chars per slide
    $slides = splitTextIntoSlides($extracted_text, $max_chars);
    
    if (empty($slides)) {
        throw new Exception("Could not create slides from text");
    }
    
    // Step 3: Create working directory
    echo "Step 3: Creating working directory...\n";
    $base_dir = __DIR__ . '/videos/' . $conversion_id;
    $images_dir = $base_dir . '/images';
    $audio_file = $base_dir . '/audio.wav';
    $output_video = $base_dir . '/output.mp4';
    $thumbnail_path = $base_dir . '/thumbnail.jpg';
    
    if (!is_dir($images_dir)) {
        mkdir($images_dir, 0755, true);
    }
    
    // Step 4: Generate slide images
    echo "Step 4: Generating slide images...\n";
    $width = (int)getSetting($conn, 'video_width', 1280);
    $height = (int)getSetting($conn, 'video_height', 720);
    $bg_color = getSetting($conn, 'background_color', '#ffffff');
    $text_color = getSetting($conn, 'text_color', '#000000');
    $font_size = (int)getSetting($conn, 'font_size', 24);
    
    foreach ($slides as $index => $slide_text) {
        $image_path = $images_dir . '/slide_' . str_pad($index + 1, 3, '0', STR_PAD_LEFT) . '.png';
        
        if (!createSlideImage($slide_text, $image_path, $width, $height, $bg_color, $text_color, $font_size)) {
            throw new Exception("Failed to create slide image {$index}");
        }
        
        echo "  Created slide {$index}\n";
    }
    
    // Step 5: Generate audio narration
    echo "Step 5: Generating audio narration...\n";
    $voice = getSetting($conn, 'voice_type', 'en-us');
    $speech_rate = (int)(floatval(getSetting($conn, 'speech_rate', '1.0')) * 150); // eSpeak uses words per minute
    
    // Combine all text for audio
    $full_text = implode('. ', $slides);
    
    if (!generateAudioFromText($full_text, $audio_file, $voice, $speech_rate)) {
        echo "  Warning: Audio generation failed, continuing without audio\n";
        $audio_file = null;
    } else {
        echo "  Audio generated successfully\n";
    }
    
    // Step 6: Create video from images and audio
    echo "Step 6: Creating final video...\n";
    $fps = (int)getSetting($conn, 'fps', 30);
    $slide_duration = (int)getSetting($conn, 'slide_duration', 5);
    
    if (!createVideoFromImages($images_dir, $audio_file, $output_video, $fps, $slide_duration)) {
        throw new Exception("Failed to create video from images");
    }
    
    if (!file_exists($output_video)) {
        throw new Exception("Video file was not created");
    }
    
    // Step 7: Create thumbnail
    echo "Step 7: Creating thumbnail...\n";
    createVideoThumbnail($output_video, $thumbnail_path, 1);
    
    // Step 8: Get video duration
    echo "Step 8: Getting video duration...\n";
    $duration = getVideoDuration($output_video);
    
    // Step 9: Update database
    echo "Step 9: Updating database...\n";
    $video_path = mysqli_real_escape_string($conn, $output_video);
    $thumb_path = mysqli_real_escape_string($conn, $thumbnail_path);
    $video_title = mysqli_real_escape_string($conn, pathinfo($conversion['original_filename'], PATHINFO_FILENAME));
    
    $final_sql = "UPDATE conversions SET 
                    video_status = 'completed',
                    video_path = '$video_path',
                    thumbnail_path = '$thumb_path',
                    video_title = '$video_title',
                    duration_seconds = $duration,
                    completed_at = NOW()
                  WHERE id = $conversion_id";
    
    if (!mysqli_query($conn, $final_sql)) {
        throw new Exception("Failed to update database: " . mysqli_error($conn));
    }
    
    // Cleanup temporary files (optional - keep for now for debugging)
    // deleteDirectory($images_dir);
    // if ($audio_file && file_exists($audio_file)) {
    //     unlink($audio_file);
    // }
    
    echo "Conversion completed successfully!\n";
    
} catch (Exception $e) {
    $error_message = $e->getMessage();
    echo "Error: " . $error_message . "\n";
    
    // Update database with error
    $error_escaped = mysqli_real_escape_string($conn, $error_message);
    $error_sql = "UPDATE conversions SET video_status = 'failed', error_message = '$error_escaped' WHERE id = $conversion_id";
    mysqli_query($conn, $error_sql);
    
    http_response_code(500);
}

echo "Processing complete.";

?>
