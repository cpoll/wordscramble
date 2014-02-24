<?php

$session_folder = '/PATH/TO/php_sessions'; # PATH TO SESSIONS FOLDER

session_save_path($session_folder);

session_start();

include 'content.php';
include 'functions.php';
include 'config.php';

# global vars
$input_sanitizer = new InputSanitizer();


#requested php script
$pagename = basename($_SERVER['REQUEST_URI'], '.php');

# fix to catch query params (basename doesn't work here)
if (stristr($pagename, '.php?', true)){
	$pagename = stristr($pagename, '.php?', true);
}


# enforce protected pages
if (!isset($_SESSION['email'])){
	# require user login for these pages
	$protected_pages = array(
		'game',
		'profile',
		'edit',
	);

	# now enforce login
	if (in_array($pagename, $protected_pages)){
		header('location: login.php');
	}
}

# Generate top data, nav and upper nav
echo '<!doctype html><html><head>';

echo '<meta charset="UTF-8">';

# determine title
if (array_key_exists($pagename, $APP_CONTENT['title'])){
	echo '<title>' . $APP_CONTENT['title'][$pagename] . '</title>';
}


# every one uses main.css
echo '<link rel="stylesheet" type="text/css" href="css/main.css" >';

# link css
if (array_key_exists($pagename, $APP_CONTENT['css'])){
	echo '<link rel="stylesheet" type="text/css" href="css/' . $APP_CONTENT['css'][$pagename] . '.css" >';
}

echo '</head><body>';

echo $APP_CONTENT['footer'];

# main nav
echo '<nav id="main">';

echo '<nav id="site">';
foreach( $APP_CONTENT['nav'] as $href=>$text ){
	echo '<a href="' . $href . '">' . $text . '</a>';
}

echo '</nav>';

echo '<nav id="user">';
if (isset($_SESSION['email'])){
	
	$displayname = '';
	$db = pg_connect($APP_CONFIG['db_conn_string']);
	$result = pg_prepare($db, 'get_displayname', 'SELECT displayname, avatar FROM profile WHERE email = $1');
	$result = pg_execute($db, 'get_displayname', array($_SESSION['email']));
	if ($result){
		$row = pg_fetch_row($result);
		$displayname = $row[0];
		$avatar_id = $row[1];
	}	
	
	echo '<a href="profile.php?user=' . $displayname  . '">' .
	'<img class="smallavatar" src="'. $APP_CONTENT['avatar'][$avatar_id] . '">' .
	$displayname .'\'S ACCOUNT</a><a href="logout.php">LOGOUT</a>';
} else {
	echo '<a href="login.php">LOGIN</a><a href="register.php">REGISTER</a>';
}
echo '</nav></nav>';
?>



