<?php

include '../lib/config.php';


# db conn
$db = pg_connect($APP_CONFIG['db_conn_string']) or die("DB DOWN");
$result = pg_prepare($db, 'gamestat', 'INSERT INTO gamestats VALUES($1, $2)'); 

# clear all data
$result = pg_query($db, 'DELETE FROM gamestats');


# get emails for existing users
$result = pg_query($db, 'SELECT email FROM profile');
if (!$result){
	die('Something went wrong...' . pg_last_error($db));
}

$emails = array();
while($row = pg_fetch_row($result)){
	array_push($emails, $row[0]);
}

$num_users = count($emails);
if ($num_users == 0){
	die('No users. Please generate_users');
}

# generate game stats
$count = 1;
while($count <= 200){

	# user's email
	$user = $emails[rand(0, $num_users - 1)];

	# user's score
	$score = rand(1, 5);

	$result = pg_execute($db, 'gamestat', array( $user, $score ));
	if (!$result){
		die('Uh oh...' . pg_last_error($db));
	}

	$count++;
}

?>
