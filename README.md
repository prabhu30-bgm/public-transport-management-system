# Bus Management System (BMS) 

A web-based Bus Management System developed to simplify and automate bus transportation operations. The system helps administrators manage buses, drivers, routes, schedules, bookings, and reports through a centralized dashboard.

## Features

### Dashboard

* Admin dashboard with key statistics
* Overview of buses, drivers, routes, and bookings
* Quick access to management modules

### Bus Management

* Add, update, and delete bus records
* Manage bus details and capacity
* View all available buses

### Driver Management

* Add and manage driver information
* Assign drivers to buses and routes
* View driver details and schedules

### Route Management

* Create and manage bus routes
* Define source and destination locations
* Track route information efficiently

### Schedule Management

* Create bus schedules
* Assign buses and drivers to routes
* Manage departure and arrival timings

### Booking Management

* Book bus tickets
* View booking details
* Manage passenger reservations
* Track booking history

### Reporting System

* Generate trip and booking reports
* View passenger statistics
* Monitor operational performance
* Analyze transportation data

### Authentication & Security

* Secure login system
* Session-based authentication
* Role-based access control
* Admin and Driver access management

## Technologies Used

### Frontend

* HTML5
* CSS3
* JavaScript
* Bootstrap 5
* Font Awesome

### Backend

* PHP

### Database

* MySQL

### Development Environment

* XAMPP

## Project Structure

```text
BusManagementSystem/
├── admin/
├── auth/
├── config/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── includes/
├── database/
├── index.php
└── README.md
```

## Installation

1. Clone the repository:

```bash
git clone <repository-url>
```

2. Move the project folder to:

```text
xampp/htdocs/
```

3. Start:

* Apache
* MySQL

from XAMPP Control Panel.

4. Create a database in phpMyAdmin.

5. Import the provided SQL file.

6. Configure database settings in:

```text
config/database.php
```

7. Open the project in your browser:

```text
http://localhost/BusManagementSystem
```

## Default Login Credentials

### Admin Login

```text
Username: admin
Password: Admin@123
```

### Driver Login

```text
Username: driver1
Password: Driver@123
```

## Security Features

* Session Management
* Authentication Checks
* Role-Based Authorization
* Input Validation
* Protected Admin Pages

## Future Enhancements

* Online Payment Integration
* Email Notifications
* Seat Selection System
* Live Bus Tracking
* Mobile Application
* Online Hosting & Deployment

## License

This project is developed for educational and learning purposes.

## Author

Basavaprabhu Kudenatti

GitHub: https://github.com/prabhu30-bgm
##
LinkedIn: https://www.linkedin.com/in/basavaprabhu-kudenatti/
