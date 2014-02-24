<?php

include 'lib/controller.php';

# redirect to news page
if (isset($_SESSION['email'])){
	header('location: index.php');
}

$login_err = '';

$email = '';
if (isset($_POST['email'])){
	$email = $_POST['email'];
	$password = $_POST['password'];

	# sanitize	
	$error = false;
	
	# email
    #if (!$input_sanitizer->sanitize($input_sanitizer->email, $APP_REG['email'], $input_sanitizer->error_msg['email'])){
    #    $error = true;
    #}

	# password
    #if (!$input_sanitizer->sanitize($input_sanitizer->password, $APP_REG['password'], $input_sanitizer->error_msg['password'])){
    #    $error = true;
    #}



	if (!$error){
		$db = pg_connect($APP_CONFIG['db_conn_string']);
	
		# get salt & pw hash
		$result = pg_prepare($db, 'login_query', 'SELECT password FROM profile WHERE email = $1');
		$result = pg_execute($db, 'login_query', array( $email ));
	
		if (pg_num_rows($result) > 0){
	
			$row = pg_fetch_row($result);
	
			# salt, pw hash
			$salt = substr($row[0], 0, 64);
			$password_hash = substr($row[0], 64, 64);
	
			# hash user's pw input
			$hash = $salt . $password;
		
				# hash...
			for ($i = 0; $i < 100000; $i++){
				$hash = hash('sha256', $hash);
			}
		
			# check if hashes match
			if ($hash == $password_hash){
				$_SESSION['email'] = $email;
				header('location: index.php');
			}
		}
	}
		
	$login_err = 'Invalid email or password.';
}



echo '<form method="POST">';
echo '<h2>Login</h2>';
echo '<label>Email:</label><input type="email" name="email" value="' . $email . '"><hr>';
echo '<label>Password:</label><input type="password" name="password"><hr>';
echo '<input type="submit" value="LOGIN" class="submit">';
echo '<label class="error">' . $login_err . '</label>';
echo '</form>';

?>


</body></html>
