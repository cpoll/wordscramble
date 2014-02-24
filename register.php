<?php

include 'lib/controller.php';

# shouldn't be here if logged in
if (isset($_SESSION['email'])){
	header('location: index.php');
}


$APP_REG = array(
	'email' => '',
	'password' => '',
	'confirmpw' => '',
	'displayname' => '',
	'firstname' => '',
	'lastname' => '',
	'birthday' => '',
	'avatar' => '',
);



$REG_ERRORS = array(
	'email' => '',
	'password' => '',
	'confirmpw' => '',
	'displayname' => '',
	'firstname' => '',
	'lastname' => '',
	'birthday' => '',
	'avatar' => '',
);


if (!isset($_POST['email'])){
	$REG_ERRORS = array(
		'email' => $input_sanitizer->error_msg['emptyinput'],
		'password' => $input_sanitizer->error_msg['emptyinput'],
		'confirmpw' => $input_sanitizer->error_msg['emptyinput'],
		'displayname' => $input_sanitizer->error_msg['emptyinput'],
		'firstname' => $input_sanitizer->error_msg['emptyinput'],
		'lastname' => $input_sanitizer->error_msg['emptyinput'],
		'birthday' => $input_sanitizer->error_msg['emptyinput'],
		'avatar' => $input_sanitizer->error_msg['emptyinput'],
	);
}

if (isset($_POST['email'])){
	$APP_REG['email'] = $_POST['email'];
	$APP_REG['password'] = $_POST['password'];
	$APP_REG['confirmpw'] = $_POST['confirmpw'];
	$APP_REG['displayname'] = $_POST['displayname'];
	$APP_REG['firstname'] = $_POST['firstname'];
	$APP_REG['lastname'] = $_POST['lastname'];
	$APP_REG['birthday'] = $_POST['birthday'];
	$APP_REG['avatar'] = $_POST['avatar'];

	# sanitize
	$error = false;

	# email
	if (!$input_sanitizer->sanitize($input_sanitizer->email, $APP_REG['email'], $input_sanitizer->error_msg['email'])){
		$error = true;
		$REG_ERRORS['email'] = $input_sanitizer->last_error_msg;
	}

	# password
	if (!$input_sanitizer->sanitize($input_sanitizer->password, $APP_REG['password'], $input_sanitizer->error_msg['password'])){
		$error = true;
		$REG_ERRORS['password'] = $input_sanitizer->last_error_msg;
	}

	# confirmpw
	if ($APP_REG['password'] != $APP_REG['confirmpw']){
		$error = true;
		$REG_ERRORS['confirmpw'] = 'Passwords don\'t match';
	}

	# display name
	if (!$input_sanitizer->sanitize($input_sanitizer->displayname, $APP_REG['displayname'], $input_sanitizer->error_msg['displayname'])){
		$error = true;
		$REG_ERRORS['displayname'] = $input_sanitizer->last_error_msg;
	}

	# firstname
	if (!$input_sanitizer->sanitize($input_sanitizer->firstname, $APP_REG['firstname'], $input_sanitizer->error_msg['firstname'])){
		$error = true;
		$REG_ERRORS['firstname'] = $input_sanitizer->last_error_msg;
	}
	
	# last name
	if (!$input_sanitizer->sanitize($input_sanitizer->lastname, $APP_REG['lastname'], $input_sanitizer->error_msg['lastname'])){
		$error = true;
		$REG_ERRORS['lastname'] = $input_sanitizer->last_error_msg;
	}
	
	# birthday
	if (!$input_sanitizer->sanitize($input_sanitizer->birthday, $APP_REG['birthday'], $input_sanitizer->error_msg['birthday'])){
		$error = true;
		$REG_ERRORS['birthday'] = $input_sanitizer->last_error_msg;
	}
	
	# avatar
	if (!$input_sanitizer->sanitize($input_sanitizer->avatar, $APP_REG['avatar'], $input_sanitizer->error_msg['avatar'])){
		$error = true;
		$REG_ERRORS['avatar'] = $input_sanitizer->last_error_msg;
	}
	
	if (!$error){	

		# db connect
		$db = pg_connect($APP_CONFIG['db_conn_string']) or die("DB down...");	

		# ensure email and displayname don't exist already
		$result = pg_prepare($db, 'check_dup', 'SELECT email, displayname FROM profile WHERE email = $1 OR displayname = $2');
		$result = pg_execute($db, 'check_dup', array( $APP_REG['email'], $APP_REG['displayname'] ));

		# reg
		$register_new_user = true;

		while($row = pg_fetch_row($result)){
			if ($row[0] == $APP_REG['email']){
				$register_new_user = false;
				$REG_ERRORS['email'] = 'Email already registered with other user.';
			}
			if ($row[1] == $APP_REG['displayname']){
				$register_new_user = false;
				$REG_ERRORS['displayname'] = 'Display Name already registered with other user.';
			}
		}


		if ($register_new_user){
			# generate salt
			$salt = hash('sha256', md5(uniqid(rand(), TRUE)));
	
			# hash password
			$hash = $salt . $APP_REG['password'];
	
			# bunch of times
			for( $i = 0; $i < 100000; $i++){
					$hash = hash('sha256', $hash);
			} 
	
			$APP_REG['password'] = $salt . $hash;
	
	
			# create user
			$result = pg_prepare($db, 'reg_insert', 'INSERT INTO profile VALUES($1, $2, $3, $4, $5, $6, $7)');
			$result = pg_execute($db, 'reg_insert', array(
				$APP_REG['email'],
				$APP_REG['password'],
				$APP_REG['displayname'],
				$APP_REG['firstname'],
				$APP_REG['lastname'],
				$APP_REG['birthday'],
				$APP_REG['avatar'],
			));

			if (!$result){
				die("Oops... something is wrong..." . pg_last_error($db));
			} 	

			$_SESSION['email'] = $APP_REG['email'];
			header('location: index.php');	
		}
	}

}

# form content
echo '<form method="post">';

echo '<h2>Register</h2>';

# Email
echo '<label>Email:</label><input type="email" name="email" value="' . $APP_REG['email'] . '">' .
'<label class="error">' . $REG_ERRORS['email'] . '</label>';
echo '<hr>';

# PW
echo '<label>Password:</label><input type="password" name="password">' .
'<label class="error">' . $REG_ERRORS['password'] . '</label>';
echo '<hr>';

# PW confirm
echo '<label>Confirm Password:</label><input type="password" name="confirmpw">' .
'<label class="error">' . $REG_ERRORS['confirmpw'] . '</label>';
echo '<hr>';

# Display Name
echo '<label>Display Name:</label><input type="text" name="displayname" value="' . $APP_REG['displayname'] . '">' .
'<label class="error">' . $REG_ERRORS['displayname'] . '</label>';
echo '<hr>';

# Fname
echo '<label>First Name:</label><input type="text" name="firstname" value="' . $APP_REG['firstname'] . '">' .
'<label class="error">' . $REG_ERRORS['firstname'] . '</label>';
echo '<hr>';

# Lname
echo '<label>Last Name:</label><input type="text" name="lastname" value="' . $APP_REG['lastname'] . '">' .
'<label class="error">' . $REG_ERRORS['lastname'] . '</label>';
echo '<hr>';

# Birthday
echo '<label>Birthday:</label><input type="date" name="birthday" value="' . $APP_REG['birthday'] . '">' .
'<label class="error">' . $REG_ERRORS['birthday'] . '</label>';
echo '<hr>';


# Avatar
echo '<label>Avatar</label>';
foreach( $APP_CONTENT['avatar'] as $id => $href){
	$selected = 'class="avatar_radio"';
	if ($APP_REG['avatar'] == $id){
		$selected .= ' checked';
	}
	echo '<input type="radio" name="avatar" value="' . $id . '" ' . $selected . '><img class="avatar" src="' . $href . '">';
}
echo '<hr>';

# Submit
echo '<input type="submit" value="REGISTER" class="submit">';

echo '</form>';

?>

</body></html>
