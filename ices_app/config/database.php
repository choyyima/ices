<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|				 NOTE: For MySQL and MySQLi databases, this setting is only used
| 				 as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7
|				 (and in table creation queries made with DB Forge).
| 				 There is an incompatibility in PHP with mysql_real_escape_string() which
| 				 can make your site vulnerable to SQL injection if you are using a
| 				 multi-byte character set and are running versions lower than these.
| 				 Sites using Latin-1 or UTF-8 database character set and collation are unaffected.
|	['swap_pre'] A default table prefix that should be swapped with the dbprefix
|	['autoinit'] Whether or not to automatically initialize the database.
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the 'default' group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class
*/

$active_group = 'default';
$active_record = TRUE;

$db['default']['hostname'] = 'localhost';
$db['default']['username'] = '';
$db['default']['password'] = '';
$db['default']['database'] = '';
$db['default']['dbdriver'] = 'mysqli';
$db['default']['dbprefix'] = '';
$db['default']['pconnect'] = FALSE;
$db['default']['db_debug'] = FALSE;
$db['default']['cache_on'] = FALSE;
$db['default']['cachedir'] = '';
$db['default']['char_set'] = 'utf8';
$db['default']['dbcollat'] = 'utf8_general_ci';
$db['default']['swap_pre'] = '';
$db['default']['autoinit'] = TRUE;
$db['default']['stricton'] = FALSE;

$db['ices']['hostname'] = 'localhost';
$db['ices']['username'] = 'ices';
$db['ices']['password'] = 'Ices123';
$db['ices']['database'] = 'ices_db';
$db['ices']['dbdriver'] = 'mysqli';
$db['ices']['dbprefix'] = '';
$db['ices']['pconnect'] = FALSE;
$db['ices']['db_debug'] = FALSE;
$db['ices']['cache_on'] = FALSE;
$db['ices']['cachedir'] = '';
$db['ices']['char_set'] = 'utf8';
$db['ices']['dbcollat'] = 'utf8_general_ci';
$db['ices']['swap_pre'] = '';
$db['ices']['autoinit'] = TRUE;
$db['ices']['stricton'] = FALSE;

$db['phone_book']['hostname'] = 'localhost';
$db['phone_book']['username'] = 'ices';
$db['phone_book']['password'] = 'Ices123';
$db['phone_book']['database'] = 'phone_book_db';
$db['phone_book']['dbdriver'] = 'mysqli';
$db['phone_book']['dbprefix'] = '';
$db['phone_book']['pconnect'] = FALSE;
$db['phone_book']['db_debug'] = FALSE;
$db['phone_book']['cache_on'] = FALSE;
$db['phone_book']['cachedir'] = '';
$db['phone_book']['char_set'] = 'utf8';
$db['phone_book']['dbcollat'] = 'utf8_general_ci';
$db['phone_book']['swap_pre'] = '';
$db['phone_book']['autoinit'] = TRUE;
$db['phone_book']['stricton'] = FALSE;

$db['MY_Job']['hostname'] = 'localhost';
$db['MY_Job']['username'] = 'ices';
$db['MY_Job']['password'] = 'Ices123';
$db['MY_Job']['database'] = 'ices_job_db';
$db['MY_Job']['dbdriver'] = 'mysqli';
$db['MY_Job']['dbprefix'] = '';
$db['MY_Job']['pconnect'] = FALSE;
$db['MY_Job']['db_debug'] = FALSE;
$db['MY_Job']['cache_on'] = FALSE;
$db['MY_Job']['cachedir'] = '';
$db['MY_Job']['char_set'] = 'utf8';
$db['MY_Job']['dbcollat'] = 'utf8_general_ci';
$db['MY_Job']['swap_pre'] = '';
$db['MY_Job']['autoinit'] = TRUE;
$db['MY_Job']['stricton'] = FALSE;

/* End of file database.php */
/* Location: ./application/config/database.php */