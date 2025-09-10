 <?php
// Error Reporting Turn On
ini_set('error_reporting', E_ALL);

// Setting up the time zone
date_default_timezone_set('America/Los_Angeles');

// Host Name
$dbhost = 'localhost';

// Database Name
$dbname = 'ecommerceweb';

// Database Username
$dbuser = 'root';

// Database Password
$dbpass = '';

// Defining base url
define("BASE_URL", "");

// Getting Admin url
define("ADMIN_URL", BASE_URL . "admin" . "/");
define('PAYSTACK_SECRET', 'sk_test_315fc78a0ab1058182741381e779681773796ffb');
// Billstack / Virtual Account provider configuration
// Set these values in this file or load from environment in production
define('BILLSTACK_API_KEY', 'Bill_Stack-SEC-KEY-75ec20ad590cb56ab6ffce8819bb07f5');
define('BILLSTACK_API_SECRET', 'Bill_Stack-SEC-KEY-75ec20ad590cb56ab6ffce8819bb07f5');
define('BILLSTACK_BASE_URL', 'https://api.billstack.co'); // replace with real Billstack base URL
define('BILLSTACK_VA_RESERVE_URL', BILLSTACK_BASE_URL . '/v2/thirdparty/generateVirtualAccount/');
define('BILLSTACK_BIND_KYC_URL', BILLSTACK_BASE_URL . '/v2/thirdparty/upgradeVirtualAccount/');
define('BILLSTACK_WEBHOOK_SECRET', 'YOUR_BILLSTACK_WEBHOOK_SECRET');

try {
	$pdo = new PDO("mysql:host={$dbhost};dbname={$dbname}", $dbuser, $dbpass);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch( PDOException $exception ) {
	echo "Connection error :" . $exception->getMessage();
}