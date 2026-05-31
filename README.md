# Bus Management System (BMS)

A comprehensive web-based Bus Management System designed to streamline and automate bus operations, scheduling, and reporting.

## Features

### Trip Management
- Schedule bus trips with detailed route information
- Track trip status (Scheduled, Departed, Completed, Cancelled)
- Monitor passenger count and revenue per trip
- View detailed trip reports with filtering options

### Driver Management
- Maintain driver profiles and information
- Assign drivers to specific routes and schedules
- Track driver performance and trip history

### Route Management
- Create and manage bus routes
- Define start and end locations
- Track route-specific information

### Reporting System
- Generate detailed trip reports
- Filter reports by date range, driver, route, and status
- View passenger statistics and revenue data
- Export and analyze trip performance

## Technical Requirements

- PHP 7.4 or higher
- MySQL/MariaDB
- Apache/Nginx web server
- XAMPP/WAMP/LAMP stack (recommended for local development)

## Installation

1. Clone the repository to your web server directory:
   ```bash
   git clone [repository-url]
   ```

2. Set up your web server (Apache/Nginx) to point to the project directory

3. Create a MySQL database and import the database schema

4. Configure the database connection in `config/database.php`

5. Ensure proper permissions are set for the project directory

## Directory Structure

```
project_bms/
├── admin/
│   ├── reports.php
│   └── includes/
├── auth/
│   └── session.php
├── config/
│   └── database.php
└── [other directories]
```

## Security Features

- Session-based authentication
- Role-based access control
- Admin-only access to sensitive operations
- Secure database connections

## Usage

1. Access the system through your web browser
2. Log in with appropriate credentials
3. Navigate through the dashboard to access different features
4. Use the reporting system to generate and analyze trip data

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, please contact [your-email] or create an issue in the repository. 

## Admin Login
-- username : admin
-- password : Admin@123

## Driver Login
-- username : driver1
-- password : Driver@123