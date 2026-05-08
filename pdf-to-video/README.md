# PDF to Video Converter

A self-hosted web application that converts PDF documents into videos with automatic text-to-speech narration. Built with **Bootstrap 5** (Advanced) frontend and **Procedural PHP with MySQLi** backend - no external APIs required!

## Features

- 📄 **PDF Upload** - Upload PDF files directly to your server
- 📝 **Text Extraction** - Automatically extract text from PDF documents
- 🎤 **Text-to-Speech** - Generate audio narration using eSpeak
- 🎬 **Video Creation** - Create slide-based videos with FFmpeg
- 🖼️ **Thumbnail Generation** - Auto-generate video thumbnails
- 📊 **Video Management** - Browse, search, and filter your converted videos
- ⚙️ **Customizable Settings** - Adjust video quality, duration, colors, and more
- 🔒 **100% Private** - All processing happens on your server

## Requirements

### Server Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher / MariaDB 10.2+
- Web server (Apache/Nginx)

### Required System Packages
```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install -y ffmpeg espeak poppler-utils php-gd php-mysqli curl

# CentOS/RHEL
sudo yum install -y ffmpeg espeak poppler-utils php-gd php-mysql curl

# For better font support (optional but recommended)
sudo apt-get install -y fonts-dejavu-core
```

## Installation

### 1. Clone or Upload Files
Upload all files to your web server directory.

### 2. Set Permissions
```bash
chmod 755 /path/to/pdf-to-video/uploads
chmod 755 /path/to/pdf-to-video/videos
chmod 644 /path/to/pdf-to-video/includes/config.php
```

### 3. Configure Database
Edit `includes/config.php` with your database credentials:

```php
$db_host = 'localhost';
$db_user = 'your_db_user';
$db_pass = 'your_db_password';
$db_name = 'pdf_to_video';
```

### 4. Run Database Setup
Visit `http://your-domain.com/pdf-to-video/setup.php` in your browser to create the required database tables.

### 5. Access the Application
Navigate to `http://your-domain.com/pdf-to-video/` and start converting PDFs!

## Directory Structure

```
pdf-to-video/
├── assets/
│   ├── css/
│   │   └── style.css          # Custom Bootstrap styles
│   └── js/
│       └── main.js            # JavaScript functionality
├── includes/
│   ├── config.php             # Database configuration
│   └── functions.php          # Helper functions
├── uploads/                   # Uploaded PDF files
├── videos/                    # Generated videos
├── index.php                  # Main landing page
├── convert.php                # Conversion status page
├── process-conversion.php     # Background processing script
├── view-video.php             # Video player page
├── my-videos.php              # Video library page
├── setup.php                  # Database setup script
└── README.md                  # This file
```

## Configuration

### Database Settings Table
The application stores settings in the database. You can modify these values directly in the `settings` table:

| Setting Key | Default | Description |
|------------|---------|-------------|
| max_file_size | 10 | Maximum PDF file size in MB |
| allowed_extensions | pdf | Allowed file extensions (comma-separated) |
| output_format | mp4 | Video output format |
| video_width | 1280 | Video width in pixels |
| video_height | 720 | Video height in pixels |
| fps | 30 | Frames per second |
| background_color | #ffffff | Slide background color |
| text_color | #000000 | Text color |
| font_family | Arial | Font family |
| font_size | 24 | Font size in points |
| slide_duration | 5 | Duration per slide in seconds |
| enable_watermark | 0 | Enable/disable watermark |
| watermark_text | PDF to Video | Watermark text |

### Modifying Settings via SQL
```sql
-- Change maximum file size to 20MB
UPDATE settings SET setting_value = '20' WHERE setting_key = 'max_file_size';

-- Change video resolution to 1920x1080
UPDATE settings SET setting_value = '1920' WHERE setting_key = 'video_width';
UPDATE settings SET setting_value = '1080' WHERE setting_key = 'video_height';

-- Change slide duration to 8 seconds
UPDATE settings SET setting_value = '8' WHERE setting_key = 'slide_duration';
```

## How It Works

1. **Upload**: User uploads a PDF file through the web interface
2. **Extract**: The system extracts text content from the PDF using `pdftotext`
3. **Split**: Text is split into manageable chunks for individual slides
4. **Generate Images**: Each text chunk is rendered as a slide image using PHP GD
5. **Create Audio**: Text-to-speech audio is generated using eSpeak
6. **Render Video**: FFmpeg combines slide images and audio into a final video
7. **Thumbnail**: A thumbnail is extracted from the generated video
8. **Store**: Video metadata is saved to the database for easy access

## Troubleshooting

### Common Issues

#### "pdftotext not found"
Install poppler-utils:
```bash
sudo apt-get install poppler-utils
```

#### "espeak not found"
Install eSpeak:
```bash
sudo apt-get install espeak
```

#### "ffmpeg not found"
Install FFmpeg:
```bash
sudo apt-get install ffmpeg
```

#### Permission Denied Errors
Ensure proper permissions:
```bash
chmod -R 755 /path/to/pdf-to-video/uploads
chmod -R 755 /path/to/pdf-to-video/videos
chown -R www-data:www-data /path/to/pdf-to-video
```

#### Video Generation Fails
Check FFmpeg logs and ensure:
- FFmpeg is installed correctly
- PHP has sufficient memory (increase `memory_limit` in php.ini)
- PHP execution time is sufficient (increase `max_execution_time`)

#### Text Extraction Returns Empty
Some PDFs may be image-based. The fallback extraction is limited. Consider:
- Using OCR-enabled PDFs
- Installing additional PDF parsing libraries

## Security Considerations

1. **File Upload Validation**: Only PDF files are accepted
2. **Filename Sanitization**: All filenames are sanitized to prevent directory traversal
3. **SQL Injection Protection**: All queries use mysqli_real_escape_string
4. **XSS Protection**: Output is escaped using htmlspecialchars
5. **Access Control**: Consider adding authentication for production use

## Performance Optimization

### For Large Files
```ini
; In php.ini
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 600
memory_limit = 512M
```

### For High Traffic
- Use a job queue system (e.g., Redis + worker scripts)
- Implement CDN for video delivery
- Add caching layer for frequently accessed videos

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Opera (latest)

## License

This project is open-source and available for personal and commercial use.

## Support

For issues, questions, or feature requests, please check the documentation or review the code comments.

---

**Built with ❤️ using Bootstrap 5, PHP, and MySQL**
