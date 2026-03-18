# Ticketix Setup Instructions

## For Your Classmate/Groupmate

If you're getting the error "Unknown database 'ticketix'", follow these steps:

### Step 1: Make sure Laragon is running
- Start Laragon
- Make sure both Apache and MySQL are running (green indicators)

### Step 2: Set up the database
1. Open your web browser
2. Go to: `http://localhost/ticketix/setup_database.php`
3. This will automatically create the database and all required tables
4. You should see success messages for each step

### Step 3: Test the application
1. Go to: `http://localhost/ticketix/TICKETIX NI CLAIRE.php`
2. Try to sign up for a new account
3. Try to log in

### Alternative Method (if setup script doesn't work):
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click "New" to create a new database
3. Name it: `ticketix`
4. Click "Import" tab
5. Choose file: `ticketix.sql`
6. Click "Go" to import

### Troubleshooting:
- If you get connection errors, make sure MySQL is running in Laragon
- If you get permission errors, make sure you're using the correct MySQL username/password
- Default Laragon settings: username=`root`, password=`` (empty)

## Files included:
- `setup_database.php` - Automatic database setup
- `ticketix.sql` - Database structure file
- `DATABASE FULL.sql` - Complete database with sample data (if needed)

## Contact:
If you still have issues, contact the project owner for help.
