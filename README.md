# DISC
DISC Personality Test in PHP language based on DISC Classic. Build on PHP language and MySQL/MariaDB database server (dummy data included, real data excluded* )  
![screenshot](https://github.com/cahyadsn/disc/blob/master/screenshot/home.png?raw=true)

Demo link : 
- [https://psycho.cahyadsn.com/disc](https://psycho.cahyadsn.com/disc) [ver 0.6 English version]
- [https://psycho.cahyadsn.com/disc/index.es.php](https://psycho.cahyadsn.com/disc/index.es.php) [ver 0.7 Spanish version] 

[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![GitHub last commit](https://img.shields.io/github/last-commit/google/skia.svg?style=flat)]()
[![Donate](https://img.shields.io/badge/$-support-ff69b4.svg?style=flat)](https://paypal.me/cahyadwiana)  

)* I can't provide the real data as on demo for this github repo since this data is proprietary (see Reference section) 

## Installation
1. download 'disc_master.zip' file
2. extract and copy all files to document root folder on your webserver (or other folder that you want)
3. create new database named 'test'
4. import 'db/disc.sql' to the 'test' database
5. change database configuration on 'index.php' and 'result.php' file (default value is $dbhost='localhost;$dbuser='root';$dbpass='';dbname='test';) 
6. try accesing to http://localhost (or other -- depend on step 2 above), enjoy!

## Reference
+ [**DiSC Classic Paper Profile** -  DiSC® 2800 Series Personal Profile System®](https://www.discprofile.com/products/disc-classic/)

![screenshot](https://github.com/cahyadsn/disc/blob/master/screenshot/result.png?raw=true)  

## Technology
+ PHP [http://www.php.net/](http://www.php.net/), 
+ MySQL [http://www.mysql.com/](http://www.mysql.com/), 

## Donation
- untuk donasi via transfer
    - Bank BCA Digital (Blu) (501) 000 576 776 186
    - Bank Jago (542) 5003 5796 1022
    - Bank Sinarmas (153) 005 462 4719
    - Bank Syariah Indonesia (BSI) 821-342-5550
- untuk donasi via PayPal [https://paypal.me/cahyadwiana]
- untuk donasi via QRIS CAHYADSN ID1022183125288 :

![screenshot](https://github.com/cahyadsn/wilayah/blob/master/docs/qr_code.cahyadsn.png?raw=true 'Donasi via QRIS CAHYADSN')

## Contact
+ facebook : [https://m.facebook.com/cahya.dsn](https://m.facebook.com/cahya.dsn)
+ email : [cahyadsn@gmail.com](mailto:cahyadsn@gmail.com)
+ demo site    : [https://psycho.cahyadsn.com/disc](https://psycho.cahyadsn.com/disc) [en] [https://psycho.cahyadsn.com/disc/index.es](https://psycho.cahyadsn.com/disc/index.es) [es-dev]
+ source code  : [https://github.com/cahyadsn/disc](https://github.com/cahyadsn/disc)

## Contributor
+ Aleksandar Urosevic
+ Ikbal Qodi
+ Lucas Giovanny

## Changelog
### Recent Updates (2026-07-18 23:06:34)
- **Performance & Optimizations (Bolt)**:
  - Optimized array allocations and iterations in the rendering and view rendering loops.
  - Streamlined array traversals and refactored loops in `result.php`.
  - Removed redundant mathematical calculations inside nested loops.
- **Security Enhancements**:
  - Prevented potential database password leaks in database exceptions.
  - Added essential HTTP security headers to `index.php` and `result.php`.
- **Code Quality & Health**:
  - Cleaned up database connection error suppression in `db.php`.
  - Fixed type mismatches and object fallback logic in `result.php`.
- **Testing**:
  - Added new test suites covering query failures, unreadable cache file fallback, and HTML cache write failures.
  - Updated test documentation.

