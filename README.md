# Big Data Application Team Project (team04)
# TEAM04 – Installation Guide

This README provides only the essential steps required to install and run the TEAM04 Baseball Analytics Web Application on the evaluation server.

------------------------------------------------------------
1. Environment
------------------------------------------------------------
- XAMPP (Apache + PHP + MariaDB)
- PHP 8.x
- MariaDB
- Windows environment

------------------------------------------------------------
2. Project Setup
------------------------------------------------------------
Place the entire team04 folder under:

C:\xampp\htdocs\team04

Start Apache and MySQL in the XAMPP Control Panel.

------------------------------------------------------------
3. Database Setup
------------------------------------------------------------
3.1 Login to MariaDB

mysql -u team04 -pteam04

3.2 Select the database

USE team04;

3.3 Initialize the database (run in this order)

SOURCE sql/dbdrop.sql;
SOURCE sql/dbinit_all.sql;

A full backup dump is also provided at:
sql/dbdump.sql

------------------------------------------------------------
4. Configuration
------------------------------------------------------------
Database connection settings are located in:

config/config.php

Default credentials:

$DB_HOST = 'localhost';
$DB_NAME = 'team04';
$DB_USER = 'team04';
$DB_PASS = 'team04';
$DB_PORT = 3306;

No additional configuration files are required.

------------------------------------------------------------
5. Run the Application
------------------------------------------------------------
After the database is initialized, open:

http://localhost/team04/

------------------------------------------------------------
6. Notes
------------------------------------------------------------
- All SQL scripts for installation are in the sql/ directory.
- The project runs entirely on XAMPP with no external dependencies.
- For detailed design/implementation information, see the final project report.
