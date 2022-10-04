# Folded Pages : WordPress Plugin
![Folded Pages - Banner Image](/assets/banner-772x250.png)
---
## Introduction:
A lightweight WordPress plugin to view hierarchical pages more efficiently in the WP-Admin page listing

## How to Use:

Download the latest release from:
https://github.com/EarthmanWeb/folded-pages/releases

- Copy / Upload to your WordPress site's plugins
- Install
- Activate

There are no settings / options, nor admin page.  

You will see the new features added to your wp-admin pages list, wherever you have nested pages, in the left column, and also in the filters section in the pages admin panel..

Please see the attached screenshots for instructions on how to use the plugin.

---

## Screenshots

![Folded Pages - Screenshot 1](/assets/screenshot-1.png)
![Folded Pages - Screenshot 2](/assets/screenshot-2.png)

## Development:

To make modifications to this plugin, you should clone (not download) the repo and submit pull requests targeted at the `main` branch

##### Code Formatting Requirements:
- phpcs - version 3.7.1 (stable) 
- phpcbf - version 3.7.1 (stable)

---
#### Setup Code Formatting in VSCode

#### Option A. Setup Code Formatting globally
##### 1. Install phpcbf and phpcs globally:

Modified from tutorial at: 
https://tommcfarlin.com/php-codesniffer-with-composer/

```
composer global require "squizlabs/php_codesniffer=3.7.1"
phpcs --version
```
###### _Note: If you get an error similar to command not found, ensure that you place the Composer bin folder in your PATH:_

This is usually `$HOME/.config/composer/vendor/bin` for Linux and `$HOME/.composer/vendor/bin` for MacOS

##### 2. Install WP coding standards:
```
// Clone a copy of the standards sniffers into your home (or modify paths to suit)
git clone git@github.com:WordPress-Coding-Standards/WordPress-Coding-Standards.git ~/wpcs
git clone git@github.com:PHPCSStandards/PHPCSExtra.git ~/PHPCSExtra
git clone git@github.com:PHPCSStandards/PHPCSUtils.git ~/PHPCSUtils
cd ~/wpcs
ln -s ../PHPCSUtils/PHPCSUtils PHPCSUtils
ln -s ../PHPCSExtra/Universal Universal
ln -s ../PHPCSExtra/NormalizedArrays NormalizedArrays


// Then tell the PHP Code Sniffer about the new rules:
phpcs --config-set installed_paths ~/wpcs,~/PHPCSExtra,~/PHPCSUtils
// Testing: output from 'phpcs -i' should contain WordPress-Extra, etc 
// if not, your paths are wrong
phpcs -i
```

##### 3. Install Recommended VSCode Extensions
Install the recommended extensions: 
`/.vscode/extensions.json`

##### 4. Install VSCode formatting settings:
Ensure you are using these workspace settings: 
`/.vscode/folded-pages.code-workspace`

For a global install, you will need to change the file location for phpcs and phpcbf to the full path to the files installed in step 1

##### 5. (optional) Override/Modify formatting rules:
To adjust code formatting rules, use the `phpcs.xml` file in the root of the project

---

#### Option B. Setup Code Formatting in VSCode
##### 1. Install the repo's local phpc and phpcbf files
```
// change the path below to the correct one for your machine
cd /path/to/plugins/folded-pages/
git clone git@github.com:WordPress-Coding-Standards/WordPress-Coding-Standards.git ./wpcs
cd wpcs
composer install
```

##### 2. Install WP coding standards into workspace folder:
```
cd /path/to/plugins/folded-pages/

// Clone a copy of the standards sniffers into your home (or modify paths to suit)
git clone git@github.com:PHPCSStandards/PHPCSExtra.git ./PHPCSExtra
git clone git@github.com:PHPCSStandards/PHPCSUtils.git ./PHPCSUtils
cd wpcs
ln -s ../PHPCSUtils/PHPCSUtils PHPCSUtils
ln -s ../PHPCSExtra/Universal Universal
ln -s ../PHPCSExtra/NormalizedArrays NormalizedArrays

// Then tell the PHP Code Sniffer about the new rules:
phpcs --config-set installed_paths ~/wpcs,~/PHPCSExtra,~/PHPCSUtils
// Testing: output from 'phpcs -i' should contain WordPress-Extra, etc 
// if not, your paths are wrong
phpcs -i
```

##### 3. Install Recommended VSCode Extensions
Install the recommended extensions: 
`/.vscode/extensions.json`

##### 4. Install VSCode formatting settings:
Ensure you are using these workspace settings: 
`/.vscode/folded-pages.code-workspace`

