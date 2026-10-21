# EduPredict - Student Academic Performance Prediction System

EduPredict is a web-based educational predictive analytics and monitoring dashboard system. It integrates machine learning evaluation (Decision Tree and Random Forest classification models) for predicting student academic performance alongside security, authentication, and file integrity monitoring services.

## Features

- **Dashboard**: Overview of key academic metrics, risk distributions, and recent predictions.
- **Academic Prediction**: Real-time evaluation of student attributes using Decision Tree and Random Forest models.
- **Model Comparison**: Visual and statistical comparison between ML models.
- **Student Management**: Database records, search, filter, and student profile tracking.
- **Security & FIM**: Authentication, session management, and File Integrity Monitoring (FIM).

## Tech Stack

- **Frontend**: HTML5, Vanilla CSS3, JavaScript (ES6+)
- **Backend / Services**: PHP, MySQL / MariaDB (XAMPP Environment)
- **Database**: MySQL (`database/schema.sql`)

## Setup Instructions

1. Clone the repository:
   ```bash
   git clone https://github.com/Jeane21/FinalIT21.git
   ```
2. Move or copy the project into your local web server root directory (e.g., `C:/xampp/htdocs/it20` or configure virtual host).
3. Start **Apache** and **MySQL** in XAMPP Control Panel.
4. Import the database schema from `database/schema.sql` into MySQL (database name: `final21_db`).
5. Open your browser and navigate to `http://localhost/it20/` or `dashboard.html`.
