<?php

include '../lib/config.php';

$db = pg_connect($APP_CONFIG['db_conn_string']);

# empty db first
$result = pg_query($db, 'DELETE FROM dictionary');


# insert wordlist
$result = pg_prepare($db, 'insert_wordlist', 'INSERT INTO dictionary VALUES ( $1 )');

$f = file('english_word_list.txt');

foreach( $f as $line_num => $line){
	$result = pg_execute($db, 'insert_wordlist', array( trim($line) ));
	if (!$result){
		echo 'Couldn\'t insert...' . $line . ' : ' . pg_last_error($db);
	}
}


?>
