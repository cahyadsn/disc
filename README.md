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

## Technology Stack & Architecture

This project is built using a lightweight and highly optimized architecture designed for performance, security, and portability:

* **Core Engine**: PHP (supports version 8.x and above)
  * **Lazy-Loading Database Connection**: Database connections are deferred and only established on cache misses.
  * **Persistent Database Pooling**: Configured with persistent connections (`p:`) to minimize TCP handshake and connection authentication overhead.
* **Database & Query Layer**: MySQL / MariaDB
  * **Single Round-trip Fallbacks**: Optimized data retrieval utilizing SQL `UNION ALL` to resolve pattern records and application fallbacks in a single database query.
  * **Prepared Statements**: Secure parameter binding utilizing mysqli prepared statements.
* **Caching & Performance Optimization**:
  * **HTML File Caching**: Pre-compiles the heavily nested rendering loop output for the 28-group questionnaire to a local HTML cache file (`html_cache.html`), yielding a ~98% speedup.
  * **Loop & Memory Optimizations**: Minimized array allocations and nested calculations inside loops.
* **Security & Hardening**:
  * **HTTP Security Headers**: Implements custom protection rules such as `X-Frame-Options: DENY` and `X-Content-Type-Options: nosniff` to defend against clickjacking and MIME sniffing.
  * **XSS Defenses**: Sanitized and escaped HTML output using `htmlspecialchars` with UTF-8 encoding.
  * **Sensitive Data Redaction**: Safe exception handling prevents database password leaks in debug logs and user interfaces.
* **Frontend & Presentation**:
  * **Glassmorphic UI Design**: Refactored to a sleek, modern visual aesthetic featuring background blurs (`backdrop-filter`), translucent panels, glowing border/shadow effects, and gradient backdrops.
  * **Typography**: Clean visual styling built on the `Plus Jakarta Sans` Google Font.
  * **Tactile Custom Controls**: Standard radio inputs are styled into custom selection buttons that glow emerald-green for "Most" choices and rose-red for "Least" choices.
  * **Responsive Dashboard Grid**: Layout cards and lists adapt fluidly to screen dimensions, providing a highly premium experience on both desktop and mobile.
* **Testing & CI/CD**:
  * **PHPUnit Framework**: Unit test suite covering SQL injection mitigations, XSS checks, caching mechanics, exception context preservation, database connection failures, and invalid POST fallbacks.
  * **Cross-platform Compatibility**: Test scripts dynamically adapt to and run reliably on both Unix/Linux and Windows environments.

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
### Recent Updates (2026-07-24)
- **UI Refactoring & Styling**:
  - Restructured layout templates with modern Glassmorphic panel designs, fluid containers, and dynamic radial background glows.
  - Implemented tactile custom-styled radio buttons that dynamically glow green (Most) and rose (Least) when checked.
  - Re-skinned results into a responsive grid dashboard matching premium modern design frameworks.
  - Automatically cleared file caches to seamlessly render the modernized structure.

### Recent Updates (2026-07-21)
- **Database & Query Optimization**:
  - Fixed a prepared statement parameter count mismatch by binding all 8 parameters for the single `UNION ALL` query in `result.php`.
  - Eliminated redundant secondary execution calls to implement a true single round-trip database fallback flow.
- **Testing**:
  - Updated test cases to assert single statement execution.
  - Improved test compatibility on Windows environments by bypassing POSIX-specific chmod file permissions tests.

### Recent Updates (2026-07-19 to 2026-07-20)
- **Database & Query Optimization**:
  - Refactored the result pattern query to use a `UNION ALL` fallback in `result.php`.
- **Code Cleanup & Refactoring**:
  - Refactored database property rendering in `result.php`.
  - Removed unnecessary '#' aspect processing in `result.php`.
  - Guarded against statement `prepare()` failure in `result.php`.
- **Security & Error Handling**:
  - Fixed discarded exception context in `db.php`.
- **Header Information**:
  - Updated `UPDATED DATE` header in `index.php` and `result.php` to `2026-07-20 08:04:50` using the `yyyy-mm-dd hh:ii:ss` format.
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

