<?php

include '../lib/config.php';

$email = '';
$password = '';
$salt = '';

$displayname = '';
$fname = '';
$lname = '';
$birthday = '';
$avatar = '';

# db conn
$db = pg_connect($APP_CONFIG['db_conn_string']) or die("DB DOWN");
$result = pg_prepare($db, 'add_user', 'INSERT INTO profile VALUES($1, $2, $3, $4, $5, $6, $7)'); 

# clear all data
$result = pg_query($db, 'DELETE FROM profile');

# create some number of users
$count = 1;
while($count <= 100){
	$displayname = 'user' . $count;		
	$fname = 'FirstName' . $count;
	$lname = 'LastName' . $count;

	$email = $displayname . '@domain.com';

	# birthday
	$birthday = rand(3,12) . '/' . rand(1,30) . '/' . rand(1970, 2000);	

	# avatar
	$avatar = rand(0,3);

	# generate salt
	$salt = hash('sha256', md5(uniqid(rand(), TRUE)));

	# hash password
	$hash = $salt . $displayname;

	# bunch of times
	for ( $i = 0; $i < 100000; $i++){
		$hash = hash('sha256', $hash);
	}

	# password is salt (64 chars) + pwhash (64 hash)
	$password = $salt . $hash;

	# query
	$result = pg_execute($db, 'add_user', array(
			$email,
			$password,
			$displayname,
			$fname,
			$lname,
			$birthday,
			$avatar
		)
	);
	

	if (!$result){
		die('Something went wrong... ' . pg_last_error($db));
	}

	$count++;
}

?>
