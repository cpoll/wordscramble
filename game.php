<?php
	include 'lib/controller.php';

	function generate_letters(){

		//Number of letters given:
		$num_letters = 10;

		//Costs 5pts to generate letters.
		$_SESSION['score'] -= 5;
		if ($_SESSION['score'] < 0) 
		{
			$_SESSION['score'] = 0;
		}

		//Get rid of the old letters.
		unset($_SESSION['letters']);

		//Generate the letters.
		$all_letters = explode(' ', 'a b c d e f g h i j k ' . 
			'l m n o p q r s t u v w x y z');
		shuffle($all_letters);	
		$_SESSION['letters'] = array_slice($all_letters, 0, $num_letters);	

	}

	function check_word_validity($dbconn, $word)
	{
		/* Given a dbconn and a word, checks if the word is valid
			(is present in the word dict, has not been guessed already,
			 and can be constructed with the given letters).

			If the word is valid, it is added to the list of guessed
			words and the score is incremented.

			In all cases, the function returns the string to display to
			the player.*/

		//Sanitize and lowercase the word.
		$word = strtolower($word);

		//echo $word . ' ';

		$whitelist = '/[^a-z]+/';
		$word = preg_replace($whitelist, '', $word);

		if ($word == ''){ return '<div id="errormsg"></div>'; }

		//Check that the word is made of the right letters.
		for($i = 0; $i < strlen($word); $i++)
		{
			if(!in_array($word[$i], $_SESSION['letters'])){	
				return '<div id="errormsg">' . '"' . $word . '" invalid, ' . 
					$word[$i] . ' is not a valid letter.' . '</div>';
			}
		}

		//Check that the word has not been guessed already.
		if(in_array($word, $_SESSION['correct_guesses']))
		{
			return '<div id="errormsg">' . '"'. $word . '" has already been guessed.'. '</div>';
		}
		
		//Check that the word is in the db.
		$result = pg_execute($dbconn, 'validity_query', array($word));
		$result = pg_fetch_row($result);

		if ($result[0] < 1){
			return '<div id="errormsg">' .'"' . $word . '" is not a valid English word.'. '</div>';
		}

		array_push($_SESSION['correct_guesses'], $word);
		$_SESSION['score'] += 1;	
		return '<div id="correctmsg">"' . $word . '" is valid. +1 point.</div>';
	}

	
	// ----- Main Block -----

	$timelimit = 60 * 2;

	// Connect to the db and set up the prepared statement we commonly use.
    $dbconn = pg_connect($APP_CONFIG['db_conn_string']) or die('DB Down.');

	$query = 'SELECT count(*) from dictionary where word = $1';
	$result = pg_prepare($dbconn, 'validity_query', $query);
	if (!$result) {die('DB Error.');}

	//If the game exceeds expire time make it start over.
	$expire = 60 * 2;
	if( (isset($_SESSION['endtime']) and 
			$_SESSION['endtime'] < time() - $expire) )
	{
		unset($_SESSION['endtime']);
	}

	//If user chose to restart, make it start over.
	if(isset($_POST['restart'])){
		if (!isset($_SESSION['restartstring']) 
				or ($_SESSION['restartstring'] != $_POST['restart']))
		{
			$_SESSION['restartstring'] = $_POST['restart'];
			unset($_SESSION['endtime']);
		}
	}

	//Set up the game if not in session:
	if (!isset($_SESSION['endtime']))
	{	
		$_SESSION['endtime'] = time() + $timelimit;
		$_SESSION['score'] = 0;

		$_SESSION['letters'] = array();
		$_SESSION['scramble_requests'] = 0;

		$_SESSION['correct_guesses'] = array();

		generate_letters();
		
	}

	//Scramble the letters if asked to.
	if (isset($_POST['getnewletter']) and 
		$_POST['getnewletter'] == $_SESSION['scramble_requests'])
	{
		$_SESSION['scramble_requests'] += 1;
		generate_letters();
	}

	echo "<script>var end_time = " . $_SESSION['endtime'] . ";</script>";

	/* If endtime < current time, game is over, 
		submit the final score and cleanup. */
	if( $_SESSION['endtime'] <= time() + 5)
	{
		echo '<header><h1>Word Scramble</h1></header>';
		echo '<div id="game">';

		//Check the last word if it came in time (up to 1s late).

		if( ($_SESSION['endtime'] > time() -1) 
					and isset($_POST['word'])){
			echo check_word_validity($dbconn, $_POST['word']);
		}

		//Display messages to the user.
		echo '<p>Time Over.</p>';
		echo '<p>Your score is: ' . $_SESSION['score'] . '</p>';

		echo '<p>';
		if ($_SESSION['score'] > 0)
		{
			//Submit the score to the database if score > 0.
			$query = 'INSERT into gamestats values ($1, $2);';
			$result = pg_prepare($dbconn, '', $query);
			$result = pg_execute($dbconn, '', 
						array($_SESSION['email'], $_SESSION['score']));
			
			if(!$result){
				echo 'Error: Cannot submit score to server.' .
				 'The server may be down, ' .
					'or your internet may not be connected.';
			}
			else {
				echo 'Score submitted!';
			}
		}
		echo '</p>';

		echo '<form id = "restart" method="post">' .
		'<input type="hidden" name="restart" value="' . rand() . '" />' .
		'<input type="submit" class="button" value="Play Again">' .
		'</form>';

		//Cleanup:
		unset($_SESSION['endtime']);
		unset($_SESSION['score']);

		unset($_SESSION['letters']);
		unset($_SESSION['scramble_requests']);

		unset($_SESSION['correct_guesses']);


		echo '</div></body></html>';

		exit();
	}

	//Check if the submitted value is a valid word, add to score.
	if (isset($_POST['word'])){
		$validity_msg = check_word_validity($dbconn, $_POST['word']);
	} else {
		$validity_msg = '<div id="errormsg"></div>';
	}

	//Load up the page.
	echo '<header><h1>Word Scramble</h1></header>';
	echo '<div id="game">';

	echo $validity_msg;

	echo '<div id="letters">';
	echo '<form id = "scramble" method="post">' .
	'Using the following letters... ' .
	'<input type="hidden" name="getnewletter" value="' . 
		$_SESSION['scramble_requests'] . '" />' .
	'<input type="submit" class="button" value="Get New Letters (-5pts)">' .
	'</form>';

	foreach ($_SESSION['letters'] as $letter){
		echo '<div class="letter">' . $letter . '</div>';
	}
	echo '</div>';

	echo '<div id="inputarea">';
	echo 'Type a word:';
	echo '<form id = "search" method="post">' .
	'<input type="text" name="word" value=""' .
	' onfocus="this.value=\'\'" />' .
	'<input type="submit" class="button" value="Submit">' .
	'</form></div>';

	echo '<script>document.getElementsByName("word")[0].focus();</script>';

	echo '<div id="score">Score: ' . $_SESSION['score'] . ' pts</div>';

	echo '<div id="timer"></div>';

	echo '<div id="guessed"><h2>Correct words guessed:</h2><ul>';
	foreach($_SESSION['correct_guesses'] as $guess)
	{
		echo '<li>' . $guess . '</li>';
	}	
	echo '</ul></div>';


	echo '<form id = "restart" method="post">' .
	'<input type="hidden" name="restart" value="' . rand() . '" />' .
	'<input type="submit" class="button" value="Restart">' .
	'</form>';
	
?>

</div>

<script>

var timerInterval=setInterval( function(){myTimer()}, 1000);
var stop = false;

function myTimer()
{
	var current_time = Math.floor( (new Date()).valueOf() / 1000);
	var timeleft = end_time - current_time;

	if (timeleft <= 0 && !stop){
		timeleft = 0;
		stop = true;
		document.getElementsByName("word")[0].value = "";
		document.getElementById("search").submit();
		clearInterval(timerInterval);
	}
	
	document.getElementById("timer").innerHTML= 
		"Time Left: " + timeleft;

}
myTimer();

</script>


</body></html>
