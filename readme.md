# Concrete5 Command-Line Installer (for Local MAMP Development and Testing)
Improves the Concrete5 command-line installer in numerous ways:
 * One script file works with all versions of C5 since 5.5.1 (as opposed to the official release, which requires separate scripts for each version, and doesn't even work with 5.5.1)
 * Specifically-tailored to work with a MAMP environment on OSX
 * Automates database creation, C5 download, config file setup, and admin login
 * Provides an interactive prompt so you don't need to remember command-line arguments or syntax
 
This will save you a lot of time if you build sites for clients or test addons/themes frequently!

## Installation
1. Fork and clone the repo to your local OSX machine, or download the files directly by clicking the "ZIP" button above (then unzip the download).
2. Edit the `settings.php` file and enter your database and server information.
3. Edit the `append_to_config_site_php.txt` file and modify as needed (the contents of this file will be appended to the `config/site.php` file of installed sites).
4. Figure out where your command-line php is installed by entering the following command in the Terminal: `which php`. If it says `/usr/bin/php`, they you're good to go. If it says something else, you'll want to change the first line in these 2 files: `install-concrete5.php` and `local_mamp.php` from `#!/usr/bin/php` to `#!/whatever/your/path/is/to/php`.
5. Make the `local_mamp.php` file executable by navigating to its directory in the Terminal and entering `chmod +x ./local_mamp.php`.

## Usage
1. Make sure MAMP is running on your computer.
2. Open Terminal and navigate (`cd`) to the directory containing the scripts.
3. Type `./local_mamp.php` and hit enter.
4. Follow the on-screen instructions.

## Potential Problems
Note that if you get the following database error: `ERROR: Unable to connect to database.`, try the following:
* Make sure MAMP is running
* Check that the database server, username, and password are correct in the `settings.php` file
* Try running the following commands in Terminal:

        sudo mkdir /var/mysql
        sudo ln -s /Applications/MAMP/tmp/mysql/mysql.sock mysql.sock

Note that when installing 5.5.1, you will see a few php warnings outputted to the Terminal after step "20%: Adding admin user.". This doesn't appear to interfere with installation at all so I think it's safe to ignore these.