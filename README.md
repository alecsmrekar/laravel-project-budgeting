<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>


## About 

This is an extendable project budget tracking framework build using Laravel and VueJS. 
The scope of this project is to provide a base into which you can connect to any banking/transaction API of your choice, and from there on you can use the prebuild budget tracking features.

The main building blocks of the system are:
- An API that serves the data to the JS frontend
- A user permissions system
- Projects: users can create projects within which individual costs are located
- Cost Items: Each cost item is created within a project. A cost item represent a planned expense/revenue inside of the project.
- Transaction: Each transaction that feeds into the system via the external API create a local DB transaction entry
- Cost Link: A cost link serves to connect the Bank Transaction to the Cost Item. This way you can track which transaction belong to which Project.

The main pages are:
- Landing page
- Projects Overview Page
- Project Editor
  - Add/Edit cost items, categorize them into departments and sectors. 
  - See the difference between expected costs and actuals.
  - See the difference between the gross cashflow (including VAT) and net cashflow.
  - Enter cashflows that were created outside of the banking app (from cash balances etc.)
  - Assign tags to manually defined cashflows
  - Filter cost items on open/final status
  - Have an overview of the aggregate budget
- Transactions page
  - See transactions coming from the banking API.
  - Link transactions from cost items in projects
  - Add tags to transactions
- Cashflow check page
  - See the actual cashflow coming in adn out of accounts
  - Filter based on project name, cost status and tag
  - Get aggregated amounts
- Settings page
  - Manage user roles
    
##Howto
### Create new user
Run `php artisan tinker` and execute:
```use Illuminate\Support\Facades\Hash;
$user = new App\Models\User();
$user->password = Hash::make('root');
$user->email = 'root@root.com';
$user->name = 'root';
$user->role = 'super';
$user->save();
```
