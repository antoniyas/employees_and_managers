# Laravel Employee Management System
This is an employee management system with basic functionalities. 
- Users can login, register and update profile.
- There are two roles - managers and employees. Once the user is logged in on dashboard 
there is a list of own managers if the user is employee and a list of own 
employees if the user is manager.  
- The database can be populated with seed data with relationships by running: 
    php artisan migrate:refresh --seed
5000 users will be migrated - 5 managers and 4995 employees. 
Employees will be assigned to managers randomly.
- On registration the 2 of the 5 managers which has the smallest number of employees are assigned 
to the newly registered user and a new email is sent to all employees of the newly register 
employee's managers 
