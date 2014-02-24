<?php

include 'lib/controller.php';

if (!isset($_GET['user']) || $_GET['user'] == ""){
	header('location: leaderboard.php');		
}

$displayname = $_GET['user'];

$db = pg_connect($APP_CONFIG['db_conn_string']) or die('Couldn\'t connect to db');

$result = pg_prepare($db, 'profile_query', 'SELECT email, fname, lname, birthday, avatar FROM profile WHERE displayname = $1');
$result = pg_execute($db, 'profile_query', array( $displayname ));

if (!$result || (pg_num_rows($result) == 0)){
	# user doesn't exist
	die('User doesn\'t exist. Visit the <a href="leaderboard.php">Leaderboard</a> to view users.');
}
$user = pg_fetch_assoc($result);

echo '<div id="center">';

echo '<div id="pic">';
echo '<h2>' . $displayname . '</h2>';

# this is us, allow edit
if ($_SESSION['email'] == $user['email']){
	echo '<a class="edit" href="edit.php">Edit Profile</a><br>';
}

echo '<img class="avatar" style="height:200px; width:200px" src="' . $APP_CONTENT['avatar'][$user['avatar']]. '"><br>';
echo '</div><div id="content">';
echo '<h2>Profile Information</h2>';
echo '<h3>First name:</h3><label>' . $user['fname'] . '</label><hr>';
echo '<h3>Last name:</h3><label>' . $user['lname'] . '</label><hr>';
echo '<h3>Birthday:</h3><label>' . date_format(date_create($user['birthday']), 'F d, Y') . '</label><hr>';

$result = pg_prepare($db, 'top_score', 'SELECT MAX(score) FROM gamestats GROUP BY email HAVING email = $1');
echo pg_last_error($db);
$result = pg_execute($db, 'top_score', array( $user['email'] ));
if (!$result || (pg_num_rows($result) == 0)){
	echo '<h2>User has no game stats</h2>';
} else {
	echo '<h2>Game Stats</h2>';
	
	$row = pg_fetch_row($result);
	echo '<h3>Top Score: ' . $row[0] . 'pts</h3><hr>';

	echo '<h3>Last 10 Attempts:</h3>';
	$result = pg_prepare($db, 'last_ten', 'SELECT score FROM gamestats WHERE email = $1 ORDER BY submitted DESC LIMIT 10');
	$result = pg_execute($db, 'last_ten', array( $user['email']));

	echo '<ol>';
	while($row = pg_fetch_row($result)){
		echo '<li>' . $row[0] . '</li>';
	}
	echo '</ol>';
}
echo '</div>';
echo '</div>';
?>

</body></html>
