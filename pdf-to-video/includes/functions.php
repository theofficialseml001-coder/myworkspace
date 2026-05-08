<?php
/**
 * Helper Functions for PDF to Video Converter
 */

/**
 * Get user's IP address
 */
function getUserIP() {
    $ip = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    return $ip;
}

/**
 * Sanitize filename
 */
function sanitizeFilename($filename) {
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    $filename = preg_replace('/_+/', '_', $filename);
    return $filename;
}

/**
 * Generate unique filename
 */
function generateUniqueFilename($original_name, $extension = '') {
    $timestamp = time();
    $random = bin2hex(random_bytes(8));
    $base_name = pathinfo($original_name, PATHINFO_FILENAME);
    $base_name = sanitizeFilename($base_name);
    
    if (empty($extension)) {
        $extension = pathinfo($original_name, PATHINFO_EXTENSION);
    }
    
    return "{$base_name}_{$timestamp}_{$random}.{$extension}";
}

/**
 * Format file size
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Format duration in seconds to MM:SS
 */
function formatDuration($seconds) {
    $minutes = floor($seconds / 60);
    $secs = $seconds % 60;
    return sprintf('%d:%02d', $minutes, $secs);
}

/**
 * Get setting value from database
 */
function getSetting($conn, $key, $default = null) {
    $sql = "SELECT setting_value FROM settings WHERE setting_key = '" . mysqli_real_escape_string($conn, $key) . "'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['setting_value'];
    }
    
    return $default;
}

/**
 * Extract text from PDF using pdftotext (requires poppler-utils)
 */
function extractTextFromPDF($pdf_path) {
    // Check if pdftotext is available
    if (function_exists('shell_exec')) {
        $output = shell_exec("pdftotext -layout '{$pdf_path}' - 2>&1");
        if (!empty($output)) {
            return $output;
        }
    }
    
    // Fallback: Try to read raw text (limited functionality)
    return extractTextFromPDFFallback($pdf_path);
}

/**
 * Fallback PDF text extraction (basic)
 */
function extractTextFromPDFFallback($pdf_path) {
    $content = file_get_contents($pdf_path);
    $text = '';
    
    // Very basic extraction - removes non-text elements
    $text = preg_replace('/[^\x20-\x7E\n\r\t]/', '', $content);
    $text = preg_replace('/\s+/', ' ', $text);
    
    return trim($text);
}

/**
 * Split text into slides/chunks
 */
function splitTextIntoSlides($text, $max_chars_per_slide = 500) {
    $slides = [];
    $paragraphs = preg_split('/\n\s*\n/', $text);
    
    $current_slide = '';
    foreach ($paragraphs as $paragraph) {
        $paragraph = trim($paragraph);
        if (empty($paragraph)) continue;
        
        if (strlen($current_slide) + strlen($paragraph) <= $max_chars_per_slide) {
            $current_slide .= (empty($current_slide) ? '' : "\n\n") . $paragraph;
        } else {
            if (!empty($current_slide)) {
                $slides[] = $current_slide;
            }
            $current_slide = $paragraph;
            
            // If single paragraph is too long, split it
            while (strlen($current_slide) > $max_chars_per_slide) {
                $split_point = strrpos(substr($current_slide, 0, $max_chars_per_slide), ' ');
                if ($split_point === false) {
                    $split_point = $max_chars_per_slide;
                }
                $slides[] = substr($current_slide, 0, $split_point);
                $current_slide = trim(substr($current_slide, $split_point));
            }
        }
    }
    
    if (!empty($current_slide)) {
        $slides[] = $current_slide;
    }
    
    return $slides;
}

/**
 * Create a simple image with text using GD
 */
function createSlideImage($text, $output_path, $width = 1280, $height = 720, $bg_color = '#ffffff', $text_color = '#000000', $font_size = 24) {
    // Create image
    $image = imagecreatetruecolor($width, $height);
    
    // Parse colors
    $bg_rgb = hexToRgb($bg_color);
    $text_rgb = hexToRgb($text_color);
    
    $bg = imagecolorallocate($image, $bg_rgb['r'], $bg_rgb['g'], $bg_rgb['b']);
    $fg = imagecolorallocate($image, $text_rgb['r'], $text_rgb['g'], $text_rgb['b']);
    
    // Fill background
    imagefill($image, 0, 0, $bg);
    
    // Try to use a font, fallback to built-in if not available
    $font_file = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';
    
    if (file_exists($font_file)) {
        // Use TrueType font
        $lines = explode("\n", $text);
        $y_position = 100;
        $line_height = $font_size * 1.5;
        
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            // Word wrap
            $wrapped_lines = wordwrap($line, 80, "\n");
            $sub_lines = explode("\n", $wrapped_lines);
            
            foreach ($sub_lines as $sub_line) {
                if ($y_position + $line_height > $height - 50) break;
                
                $bbox = imagettfbbox($font_size, 0, $font_file, $sub_line);
                $x_position = ($width - ($bbox[2] - $bbox[0])) / 2;
                
                imagettftext($image, $font_size, 0, $x_position, $y_position, $fg, $font_file, $sub_line);
                $y_position += $line_height;
            }
            
            $y_position += $line_height * 0.5;
        }
    } else {
        // Fallback to built-in font
        $lines = explode("\n", $text);
        $y_position = 20;
        $line_height = 20;
        
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            $x_position = 50;
            if ($y_position + $line_height < $height - 20) {
                imagestring($image, 5, $x_position, $y_position, substr($line, 0, 100), $fg);
                $y_position += $line_height;
            }
        }
    }
    
    // Save image
    imagepng($image, $output_path);
    imagedestroy($image);
    
    return true;
}

/**
 * Convert hex color to RGB array
 */
function hexToRgb($hex) {
    $hex = str_replace('#', '', $hex);
    
    if (strlen($hex) == 3) {
        $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
        $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
        $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    
    return ['r' => $r, 'g' => $g, 'b' => $b];
}

/**
 * Generate audio from text using espeak (text-to-speech)
 */
function generateAudioFromText($text, $output_path, $voice = 'en-us', $speed = 150) {
    if (function_exists('shell_exec')) {
        // Clean text for TTS
        $clean_text = escapeshellarg($text);
        
        // Use espeak to generate WAV file
        $cmd = "espeak -v {$voice} -s {$speed} -w '{$output_path}' {$clean_text} 2>&1";
        $output = shell_exec($cmd);
        
        if (file_exists($output_path)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Create video from images and audio using ffmpeg
 */
function createVideoFromImages($images_dir, $audio_file, $output_file, $fps = 30, $duration_per_image = 5) {
    if (!function_exists('shell_exec')) {
        return false;
    }
    
    // Get list of images
    $images = glob($images_dir . '/*.png');
    sort($images);
    
    if (empty($images)) {
        return false;
    }
    
    // Create concat file for ffmpeg
    $concat_file = tempnam(sys_get_temp_dir(), 'ffmpeg_concat_');
    $file_list = '';
    
    foreach ($images as $image) {
        $file_list .= "file '{$image}'\n";
        $file_list .= "duration {$duration_per_image}\n";
    }
    
    // Add last image again without duration (for proper ending)
    $last_image = end($images);
    $file_list .= "file '{$last_image}'\n";
    
    file_put_contents($concat_file, $file_list);
    
    // Build ffmpeg command
    if (file_exists($audio_file)) {
        // With audio
        $cmd = "ffmpeg -y -f concat -safe 0 -i '{$concat_file}' -i '{$audio_file}' -c:v libx264 -preset medium -crf 23 -c:a aac -b:a 128k -shortest '{$output_file}' 2>&1";
    } else {
        // Without audio
        $total_duration = count($images) * $duration_per_image;
        $cmd = "ffmpeg -y -f concat -safe 0 -i '{$concat_file}' -c:v libx264 -preset medium -crf 23 -t {$total_duration} '{$output_file}' 2>&1";
    }
    
    shell_exec($cmd);
    
    // Cleanup
    unlink($concat_file);
    
    // Verify output
    if (file_exists($output_file)) {
        return true;
    }
    
    return false;
}

/**
 * Get video duration using ffprobe
 */
function getVideoDuration($video_file) {
    if (!function_exists('shell_exec') || !file_exists($video_file)) {
        return 0;
    }
    
    $cmd = "ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 '{$video_file}' 2>&1";
    $output = shell_exec($cmd);
    
    if ($output && is_numeric($output)) {
        return (int)floatval($output);
    }
    
    return 0;
}

/**
 * Create thumbnail from video using ffmpeg
 */
function createVideoThumbnail($video_file, $thumbnail_path, $time_position = 1) {
    if (!function_exists('shell_exec') || !file_exists($video_file)) {
        return false;
    }
    
    $cmd = "ffmpeg -y -i '{$video_file}' -ss 00:00:{$time_position} -vframes 1 -vf scale=320:-1 '{$thumbnail_path}' 2>&1";
    shell_exec($cmd);
    
    if (file_exists($thumbnail_path)) {
        return true;
    }
    
    return false;
}

/**
 * Delete directory recursively
 */
function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            unlink($path);
        }
    }
    
    return rmdir($dir);
}

?>
