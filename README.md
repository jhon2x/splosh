#Getting Started
 
##Verify your prerequisites
Use the following to verify you have the correct prerequisites to install the Magento software.
 
* Apache 2.2 or 2.4
* PHP 7.0.2/7.0.4/7.0.6-7.0.x
* MySQL 5.6.x
 
Install Composer
 
* http://devdocs.magento.com/guides/v2.0/install-gde/install/composer-clone.html#instgde-prereq-compose-install
 
## Initial Setup
 
Clone the project
 
```
#!bash
$ git clone git@bitbucket.org:acidgreen/splosh.git
```
 
Install dependencies via composer
 
```
#!bash
$ cd {project_directory}
$ composer install
```
 
Add execute permission to bin/magento
```
#!bash
$ chmod +x bin/magento
```
 
Import database, you can find the dump in resources/sql from this repository.
```
#!bash
$ gunzip resources/sql/splosh.sql.gz
$ mysqladmin -u{user} -p create {database_name}
$ mysql -u{user} -p {database_name} < resources/sql/splosh.sql
```
 
Copy app/etc/env.php.dev to app/etc/env.php and update configurations.
```
#!bash
$ cp app/etc/env.php.dev app/etc/env.php
```

Enable/disable some modules
```
"php bin/magento module:enable Acidgreen_TargetRule Acidgreen_CustomerImportExport Magento_Review MagePal_GmailSmtpApp Acidgreen_Custom503 Ebizmarts_MailChimp Acidgreen_MagePalGmailSmtpApp FishPig_WordPress"
"php bin/magento module:disable MageWorx_ShippingRules"
```
 
Run Magento setup
```
#!bash
$ bin/magento setup:upgrade
$ bin/magento setup:di:compile (or setup:di:compile-multi-tenant)
$ bin/magento setup:static-content:deploy en_AU en_US [other_lang]... // Normally you would need to pass en_AU only
```
