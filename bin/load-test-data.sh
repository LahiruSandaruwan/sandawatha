#!/bin/bash

# Check if XAMPP MySQL is running
if [ ! -S "/opt/lampp/var/mysql/mysql.sock" ]; then
    echo "XAMPP MySQL is not running. Please start XAMPP MySQL first."
    echo "You can start it with: sudo /opt/lampp/lampp startmysql"
    exit 1
fi

# Load the test data
echo "Loading test data..."
/opt/lampp/bin/mysql -u root sandawatha < /home/lahirusandaruwan/Development/my\ personal\ projects/sandawatha/sql/test_data.sql

if [ $? -eq 0 ]; then
    echo "Test data loaded successfully!"
    echo "Added:"
    echo "- 1 admin user"
    echo "- 500 male users"
    echo "- 500 female users"
else
    echo "Error loading test data!"
    exit 1
fi 