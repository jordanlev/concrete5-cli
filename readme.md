# Concrete5 Command-Line Installer <br /> _Improved for OSX/MAMP development and testing!_
Improves the Concrete5 command-line installer in numerous ways:
 * Automates database creation, C5 download, config file setup, and admin login
 * Specifically-tailored to work with MAMP on OSX
 * Provides an interactive prompt so you don't need to remember command-line arguments or syntax
 * One script file works with all versions of C5 since 5.5.1 (as opposed to the official release, which requires separate scripts for each version, and doesn't even work with 5.5.1)
 * Optionally removes non-english Zend/Locale/Data files (which cuts down the overall size of the c5 installation by approx. 10MB)
 * Optionally deletes empty top-level folders (`blocks`, `controllers`, `css`, `elements`, `helpers`, `jobs`, `js`, `languages`, `libraries`, `mail`, `models`, `packages`, `page_types`, `single_pages`, `themes`, `tools`, and `updates`) and files (`INSTALL` and `LICENSE.TXT`). _I like to do this so I can quickly see if a site has core overrides -- otherwise you have to look inside every folder._

This will save you a lot of time if you build sites for clients or test addons and themes regularly!

## Installing The Installer
1. Fork and clone the repo to your local OSX machine, or download the files directly by clicking the "ZIP" button above (then unzip the download).
2. Edit the `settings.php` file and enter your database and server information.
3. Edit the `append_to_config_site_php.txt` file and modify as needed (the contents of this file will be appended to the `config/site.php` file of installed sites) -- for example, to disable marketplace/newsflow.
4. Make the `local_mamp.php` file executable by navigating to its directory in the Terminal and entering `chmod +x ./local_mamp.php`.

## Using The Installer
1. Make sure MAMP is running on your computer.
2. Open Terminal and navigate (`cd`) to the directory containing these scripts.
3. Type `./local_mamp.php` and hit enter.
4. Follow the on-screen instructions.

## Usage Notes
* If you get the following database error: `ERROR: Unable to connect to database.`, try the following:
    * Make sure MAMP is running
    * Check that the database server, username, and password are correct in the `settings.php` file
    * Try running the following commands in Terminal:

            sudo mkdir /var/mysql
			sudo ln -s /Applications/MAMP/tmp/mysql/mysql.sock /var/mysql/mysql.sock

* When installing 5.5.1, you will see a few php warnings outputted to the Terminal after the `20%: Adding admin user.` step. This doesn't seem to interfere with installation at all so I think it's safe to ignore these warnings.
