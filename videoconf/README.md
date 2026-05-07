# Video Conference SFU Solution

A comprehensive, professional video conferencing platform built with **Advanced Bootstrap 5** frontend and **Procedural PHP with MySQLi**. This is a complete alternative to Zoom, Google Meet, Jitsi, Teams, and WebEx.

## 🚀 Features

### Core Video Conferencing
- ✅ **HD Video Conferencing** - Up to 1080p HD video quality
- ✅ **Real-time Audio** - Crystal clear audio with noise suppression
- ✅ **Screen Sharing** - Share entire screen, applications, or browser tabs
- ✅ **Interactive Whiteboard** - Collaborative drawing and annotations
- ✅ **Group Chat** - Public and private messaging during meetings
- ✅ **File Sharing** - Share documents up to 100MB
- ✅ **Cloud Recording** - Record meetings with automatic storage
- ✅ **Instant Meetings** - Start meetings immediately with unique IDs
- ✅ **Scheduled Meetings** - Plan meetings in advance
- ✅ **Recurring Meetings** - Set up repeating meetings

### Advanced Features
- 🔒 **End-to-End Encryption** - Secure communications
- 🔐 **Meeting Passwords** - Protect your meetings
- 👥 **Up to 1000 Participants** - Scale for any event size
- 🎭 **Waiting Room** - Control who joins
- 🎬 **Recording & Playback** - Cloud-based recording storage
- 📊 **Analytics Dashboard** - Meeting insights and statistics
- 🎨 **Custom Branding** - White-label options (Enterprise)
- 🌐 **Multi-language Support** - International ready

### Use Cases
Perfect for:
- 💼 Business Meetings
- 🎓 Online Education & Live Classes
- 🏥 Telehealth & Medical Consultations
- 💑 Dating & Social Media
- 🎤 Webinars & Presentations
- 👔 Job Interviews
- 🔍 Remote Inspections
- 💬 Group Discussions
- 🤝 Client Consultations

## 📁 Project Structure

```
videoconf/
├── assets/
│   ├── css/
│   │   └── style.css          # Custom Bootstrap styles
│   ├── js/
│   │   └── main.js            # WebRTC & UI logic
│   └── uploads/
│       ├── recordings/        # Meeting recordings
│       ├── files/             # Shared files
│       └── avatars/           # User avatars
├── api/
│   ├── create_meeting.php     # Create meeting API
│   ├── upload_recording.php   # Upload recording API
│   └── upload_file.php        # File upload API
├── includes/
│   └── config.php             # Configuration & DB connection
├── admin/
│   └── index.php              # Admin panel
├── index.php                  # Landing page
├── login.php                  # Login page
├── register.php               # Registration page
├── dashboard.php              # User dashboard
├── meeting.php                # Meeting room interface
├── database.sql               # Database schema
└── README.md                  # This file
```

## 🛠️ Technology Stack

### Frontend
- **Bootstrap 5.3.2** - Modern responsive framework
- **Bootstrap Icons** - Beautiful icon library
- **WebRTC** - Real-time peer-to-peer communication
- **WebSocket** - Real-time signaling
- **Canvas API** - Whiteboard functionality
- **MediaRecorder API** - Recording capabilities

### Backend
- **PHP 8.x** - Procedural programming style
- **MySQLi** - MySQL database extension (procedural)
- **Session Management** - Secure user sessions
- **Prepared Statements** - SQL injection prevention

## 📋 Requirements

- PHP 7.4 or higher (PHP 8.x recommended)
- MySQL 5.7 or higher / MariaDB 10.3+
- Web server (Apache/Nginx)
- SSL Certificate (for production - required for WebRTC)
- WebSocket support (for signaling)

## ⚙️ Installation

### 1. Clone/Download the Project

```bash
cd /var/www/html
# Or copy the videoconf folder to your web root
```

### 2. Create Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE videoconf_db;
```

### 3. Import Database Schema

```bash
mysql -u root -p videoconf_db < database.sql
```

Or manually run the SQL in `database.sql` through phpMyAdmin.

### 4. Configure Database Connection

Edit `includes/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'videoconf_db');
```

### 5. Set Permissions

```bash
chmod -R 755 videoconf/
chmod -R 777 videoconf/assets/uploads/
```

### 6. Access the Application

Open your browser and navigate to:
```
http://localhost/videoconf
```

### 7. Default Admin Login

- **Email:** admin@videoconf.com
- **Password:** admin123

⚠️ **Change the default password immediately!**

## 🎯 Quick Start Guide

### For Users

1. **Register an Account**
   - Click "Sign Up Free" on the homepage
   - Fill in your details
   - Verify your email

2. **Start a Meeting**
   - Login to your dashboard
   - Click "Start Instant Meeting"
   - Share the meeting ID/link with participants

3. **Join a Meeting**
   - Click "Join Meeting"
   - Enter the meeting ID
   - Enter your name and join

### For Administrators

1. Login with admin credentials
2. Navigate to Admin Panel
3. Manage users, meetings, and subscriptions
4. Monitor system analytics

## 🔐 Security Features

- Password hashing with bcrypt
- SQL injection prevention via prepared statements
- XSS protection through input sanitization
- CSRF token validation
- Session security (HttpOnly cookies)
- IP-based rate limiting
- Meeting passwords
- Waiting room functionality

## 📱 Responsive Design

Fully responsive and mobile-friendly:
- Desktop (1920px+)
- Laptop (1024px - 1919px)
- Tablet (768px - 1023px)
- Mobile (320px - 767px)

## 🎨 Customization

### Change Branding

Edit `includes/config.php`:

```php
define('APP_NAME', 'Your Brand Name');
define('APP_URL', 'https://yourdomain.com');
```

### Customize Colors

Edit `assets/css/style.css`:

```css
:root {
    --primary-color: #your-color;
    --secondary-color: #your-color;
    --accent-color: #your-color;
}
```

## 🚀 Performance Optimization

- CDN for Bootstrap assets
- Lazy loading for videos
- Optimized media streams
- Adaptive bitrate streaming
- Efficient database queries
- Indexed database tables

## 📊 SaaS Subscription Plans

The system includes 4 pre-configured plans:

| Plan | Participants | Duration | Recordings | Price/Mo |
|------|-------------|----------|------------|----------|
| Free | 100 | 40 min | 0 | $0 |
| Basic | 200 | 2 hr | 10 | $9.99 |
| Pro | 500 | 4 hr | 50 | $19.99 |
| Enterprise | 1000 | 8 hr | Unlimited | $49.99 |

## 🔧 API Endpoints

### Create Meeting
```
POST /api/create_meeting.php
Content-Type: application/json

{
    "title": "My Meeting",
    "type": "instant",
    "password": "optional"
}
```

### Upload Recording
```
POST /api/upload_recording.php
Content-Type: multipart/form-data

file: [binary]
meeting_id: 123
```

### Upload File
```
POST /api/upload_file.php
Content-Type: multipart/form-data

file: [binary]
meeting_id: 123
```

## 🐛 Troubleshooting

### Camera/Microphone Not Working
- Ensure HTTPS is enabled (required for WebRTC)
- Check browser permissions
- Verify media devices are connected

### WebSocket Connection Failed
- Ensure WebSocket server is running
- Check firewall settings
- Verify correct port configuration

### Database Connection Error
- Verify database credentials in config.php
- Ensure MySQL service is running
- Check database exists

## 📝 Changelog

### Version 1.0.0
- Initial release
- HD video conferencing
- Screen sharing
- Interactive whiteboard
- Group chat
- File sharing
- Cloud recording
- SaaS subscription system
- Admin panel

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## 📄 License

This project is proprietary software. All rights reserved.

## 📞 Support

For support and inquiries:
- Email: support@videoconf.com
- Documentation: /docs
- Issue Tracker: GitHub Issues

## 🙏 Credits

- Bootstrap - https://getbootstrap.com
- Bootstrap Icons - https://icons.getbootstrap.com
- WebRTC - https://webrtc.org

---

**VideoConf Pro** - Professional Video Conferencing for Everyone! 🎥✨
