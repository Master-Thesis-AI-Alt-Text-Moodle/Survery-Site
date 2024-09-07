# Image-Text Evaluation Survey

This project is part of a Master's Thesis research on "AI-Generated Alternative Text Suggestions for Images in Moodle: Enhancing Web Accessibility for Visually Impaired Users".

## Demo Version

You can view a demo version of the survey at:

[https://master-thesis-ai-alt-text-moodle.github.io/Survery-Site/](https://master-thesis-ai-alt-text-moodle.github.io/Survery-Site/)

This demo provides a simplified version of the survey interface, allowing you to navigate through the evaluation process without saving any data.

## Full Version Setup

To set up the full version of the survey with data saving capabilities:

1. Clone this repository or download all the files.

2. Set up a PHP-capable web server (e.g., Apache with PHP).

3. Copy the entire contents of the repository to your web server's root directory. This should include:
   - All PHP files
   - The `script` folder
   - The `styles.css` file
   - The `images` folder

4. Configure the database connection:
   - Open `save_ratings.php` and `survey_results.php`
   - Locate the "Database connection" section in each file
   - Update the following variables with your database information:
     ```php
     $servername = "your_server_name";
     $username = "your_database_username";
     $password = "your_database_password";
     $dbname = "your_database_name";
     ```

5. Ensure your web server has write permissions for the directory where the application is stored, as it needs to create and modify files for storing survey data.

6. Access the survey through your web server's URL.
