# Folded Pages : WordPress Plugin
---
## Introduction:
A lightweight WordPress plugin to view hierarchical pages more efficiently in the WP-Admin page listing

## How to Use:

- Upload to your WordPress site
- Install
- Activate

There are no settings / options, nor admin page.  
You will see it in your wp-admin pages list, wherever you have nested pages.

---
## Development:

##### Code Formatting Requirements:
phpcs - version 3.7.1 (stable) by Squiz (http://www.squiz.net)
phpcbf - version 3.7.1 (stable) by Squiz (http://www.squiz.net)

---
#### Option A. Setup Code Formatting in VSCode

##### 1. Install the repo's phpc and phpcbf files
```
// change the path below to teh correct one for you
cd /path/to/plugins/folded-pages/
cd wpcs
composer install
```

##### 2. Install VSCode Extensions used for code formatting
Install the recommended extensions: 
`/.vscode/extensions.json`

##### 3. Install VSCode formatting settings:
Ensure you are using these workspace settings: 
`/.vscode/folded-pages.code-workspace`

##### 4. Override/Modify formatting rules:
To adjust code formatting rules, use the `phpcs.xml` file in the root of the project

---
#### Option B. Setup Code Formatting Globally:
Modified from tutorial at: 
https://tommcfarlin.com/php-codesniffer-with-composer/

##### 1. Install phpcbf and phpcs globally:
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
git clone git@github.com:PHPCSStandards/PHPCSUtils.git ~/phpcsutils
// Then tell the PHP Code Sniffer about the new rules:
phpcs --config-set installed_paths ~/wpcs,~/phpcsutils

```

##### 3. Install VSCode Extensions used for code formatting
Install the recommended extensions: 
`/.vscode/extensions.json`

##### 4. Install VSCode formatting settings:
Ensure you are using these workspace settings: 
`/.vscode/folded-pages.code-workspace`

##### 5. Override/Modify formatting rules:
To adjust code formatting rules, use the `phpcs.xml` file in the root of the project