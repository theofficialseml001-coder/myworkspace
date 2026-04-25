# Website Idea Validator - User Voting System

A complete web application for submitting and voting on website ideas, built with Bootstrap (frontend) and Procedural PHP with MySQLi (backend).

## Features

- **Submit Ideas**: Users can submit their website ideas with title, description, and contact information
- **Vote System**: Community members can vote for their favorite ideas (one vote per IP address per idea)
- **Real-time Updates**: AJAX-based voting with instant feedback
- **Admin Dashboard**: Moderate submissions (approve/reject ideas)
- **Responsive Design**: Fully responsive UI using Bootstrap 5
- **Security**: SQL injection prevention, input validation, XSS protection

## File Structure

```
/workspace
├── config.php          # Database configuration
├── setup.php           # Database setup script (run once)
├── index.php           # Homepage - displays all approved ideas
├── submit.php          # Idea submission form
├── view.php            # View individual idea details
├── vote.php            # AJAX voting handler
├── admin.php           # Admin dashboard for moderation
└── README.md           # This file
```

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache, Nginx, etc.)

### Setup Instructions

1. **Configure Database Connection**
   - Edit `config.php` and update the database credentials:
     ```php
     $host = 'localhost';
     $username = 'root';
     $password = 'your_password';
     $database = 'idea_validator';
     ```

2. **Create Database and Tables**
   - Run the setup script by accessing: `http://your-domain/setup.php`
   - This will create the database and necessary tables automatically

3. **Access the Application**
   - Homepage: `http://your-domain/index.php`
   - Submit Ideas: `http://your-domain/submit.php`
   - Admin Dashboard: `http://your-domain/admin.php`

## Database Schema

### Ideas Table
- `id`: Auto-increment primary key
- `title`: Idea title (VARCHAR 255)
- `description`: Detailed description (TEXT)
- `submitter_name`: Name of person who submitted
- `submitter_email`: Email of submitter
- `votes`: Vote count (INT, default 0)
- `created_at`: Timestamp of submission
- `status`: ENUM ('pending', 'approved', 'rejected')

### Votes Table
- `id`: Auto-increment primary key
- `idea_id`: Foreign key to ideas table
- `voter_ip`: IP address of voter (prevents duplicate votes)
- `voted_at`: Timestamp of vote
- Unique constraint on (idea_id, voter_ip)

## Usage

### For Users
1. Visit the homepage to see all approved ideas
2. Click "Vote" to upvote ideas you like (one vote per IP)
3. Click "Submit Idea" to share your own website concept
4. View detailed information about any idea by clicking "View Details"

### For Administrators
1. Access the admin dashboard at `/admin.php`
2. Review pending ideas in the "Pending" section
3. Approve or reject submissions
4. Monitor approved ideas and their vote counts

## Security Features

- **SQL Injection Prevention**: All user inputs are escaped using `mysqli_real_escape_string()`
- **XSS Protection**: Output is sanitized using `htmlspecialchars()`
- **Input Validation**: Form inputs are validated for length, format, and required fields
- **IP-based Vote Limiting**: Prevents multiple votes from the same IP address
- **Transaction Support**: Voting uses database transactions for data integrity

## Technologies Used

### Frontend
- Bootstrap 5.3.0
- Bootstrap Icons 1.10.0
- Vanilla JavaScript (Fetch API)

### Backend
- PHP (Procedural style)
- MySQLi (Procedural)
- MySQL Database

## Customization

### Styling
Modify the CSS in the `<style>` tags within each PHP file or create a separate CSS file.

### Database Configuration
Update `config.php` to match your database credentials.

### Vote Limiting
Currently uses IP address for vote limiting. Can be modified to use cookies or user authentication.

## License

This project is open source and available for educational and commercial use.

## Support

For issues or questions, please check the code comments or submit a bug report.
