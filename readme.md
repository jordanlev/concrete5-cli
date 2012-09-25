# Concrete5 Command-Line Installer (for Local MAMP Development and Testing)
This is a fork of the Concrete5 command-line installer, which works with all versions of C5 since 5.5.1 (as opposed to the official release, which requires separate scripts for each version). It also contains an additional script which further automates installation (as opposed to the official release which requires you to manually download and unzip files, create a database, log into the site, etc.). It is specifically-tailored for the MAMP environment on OSX. If you build a lot of websites for clients or frequently test addons and themes, this will save you a lot of time!

## Additional Functionality Provided
* Allows you to choose from several versions of Concrete5
* Prompts you for installation info (install directory, site name, database name, starting point) so you don't need to remember command-line arguments or syntax
* Downloads a fresh install of the chosen C5 version and unzips it to your chosen install directory
* Creates the new database for you in MAMP's MySQL
* Runs the command-line installer to install the site
* Adds custom items to the `config/site.php` file (e.g. disable marketplace/newsflow)
* Logs you into the new site as an administrator, and brings you to the Cache Settings dashboard page (since disabling the cache is probably the first thing you'll want to do for a development environment)

## Installation
1. Fork and clone the repo to your local OSX machine, or download the files directly by clicking the "ZIP" button above (then unzip the download).
2. Edit the `settings.php` file and enter your database and server information.
3. Edit the `append_to_config_site_php.txt` file and modify as needed (the contents of this file will be appended to the `config/site.php` file of installed sites).
4. Make the `local_mamp.php` file executable by navigating to its directory in the Terminal and entering `chmod +x ./local_mamp.php`.

## Usage
1. Make sure MAMP is running on your computer.
2. Open Terminal and navigate (`cd`) to the directory containing the scripts.
3. Type `./local_mamp.php` and hit enter.
4. Follow the on-screen instructions.

Note that if you get the following database error: `ERROR: Unable to connect to database.`, try the following:
* Make sure MAMP is running
* Check that the database server, username, and password are correct in the `settings.php` file
* Try running the following commands in Terminal:

        sudo mkdir /var/mysql
        sudo ln -s /Applications/MAMP/tmp/mysql/mysql.sock mysql.sock
