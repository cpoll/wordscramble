<?php
include 'lib/controller.php';
?>

<?php

	function generate_pagebar($curr_page, $num_pages, $resultsperpage){
		/* Given the current page, the number of pages, and the number of
			entries per page, returns a string of hyperlinks to every page */

		//Note: We count from 1 so that the url matches the page numbers.
		//The end user won't like the first page being "0"

		$retstring = '';

		//Back a page arrow.
		if ($curr_page > 1) {
	
			$retstring .= '<a href="leaderboard.php?page=' . ($curr_page -1) 
				. '&amp;resultsperpage=' . $resultsperpage . '">'
				. '&lt;' . '</a>' . ' '; 
				
		}

		//Pages up to our page.
		for($i = 1; $i < $curr_page; $i++){

			$retstring .= '<a href="leaderboard.php?page=' . $i 
				. '&amp;resultsperpage=' . $resultsperpage . '">'
				. $i . '</a>' . ' '; 

		}
		
		//Our page number (not a link)
		$retstring .= '[' . $curr_page . '] ';
		
		//Next pages.
		for($i = $curr_page + 1; $i <= $num_pages; $i++){

			$retstring .= '<a href="leaderboard.php?page=' . $i 
				. '&amp;resultsperpage=' . $resultsperpage . '">'
				. $i . '</a>' . ' '; 

		}

		//Forward a page arrow
		if ($curr_page < $num_pages) {
	
			$retstring .= '<a href="leaderboard.php?page=' . ($curr_page +1) 
				. '&amp;resultsperpage=' . $resultsperpage . '">'
				. '&gt;' . '</a>' . ' '; 
				
		}

		return $retstring;
	
	}

	function get_users_page_number($dbconn, $username, $resultsperpage)
	{
		/* Given a dbconn, username, and results per page, returns the
		 page number the user is located on, or -1 if user is not found */


		//Get the user's email from their username.

		$query = 'select email from profile where displayname = $1;';
 		$result = pg_prepare($dbconn, '', $query);
		$result = pg_execute($dbconn, '', array($username));

		if ($result == FALSE){ return -1;}

		$email = pg_fetch_row($result);
		$email = $email[0];

		//Figure out the user's top score and the corresponding date.
		$query = '
		select email, score, submitted from
		(
			select email, min(submitted) as submitted from
			(
				select email, max(score) as score
				from gamestats where email = $1
				group by email
			) as a
			
			NATURAL JOIN gamestats
			group by email
		) as topscoredate
		NATURAL JOIN gamestats
		order by score desc, submitted asc';

		$result = pg_prepare($dbconn, '', $query);
		$result = pg_execute($dbconn, '', array($email));

		$result = pg_fetch_row($result);
	
		if ($result == FALSE){ return -2;}

		$topscore = $result[1];
		$submit_date = $result[2];

		//Figure out how many people come before the user (and from there
		//figure out the user's page number).
		$query = '
		select count(*) from
		(
			select email, min(submitted) as submitted from
			(
				select email, max(score) as score
				from gamestats
				group by email
			) as a
			
			NATURAL JOIN gamestats
			group by email
		) as topscoredate
		NATURAL JOIN gamestats
		where score > $1 or score = $1 
		and submitted < $2;';

		$result = pg_prepare($dbconn, '', $query);
		$result = pg_execute($dbconn, '', array($topscore, $submit_date));

		$higher_ranked_users = pg_fetch_row($result);
		$higher_ranked_users = $higher_ranked_users[0];

		$pagenum = $higher_ranked_users / $resultsperpage + 1;

		return floor($pagenum);
		
	}

	//Connect to DB.
	$dbconn = pg_connect($APP_CONFIG['db_conn_string']) or die('DB Down.');

	//Ignore non-numeric input.
	if (!isset($_REQUEST['resultsperpage']) or
		!is_numeric($_REQUEST['resultsperpage']) or 
		$_REQUEST['resultsperpage'] != 20 and
		$_REQUEST['resultsperpage'] != 50 and
		$_REQUEST['resultsperpage'] != 100)
	{ 
		$_REQUEST['resultsperpage'] = 20;
	}

	if (!isset($_REQUEST['page']) or
			!is_numeric($_REQUEST['page']) or 
			$_REQUEST['page'] < 1  )
	{
		$_REQUEST['page'] = 1;
	}

	//user search supercedes page number.
	$user_not_found = FALSE;
	$user_has_no_scores = FALSE;
	if ( isset($_REQUEST['user']) )
	{
		$user_page = get_users_page_number($dbconn, $_REQUEST['user'], 
				$_REQUEST['resultsperpage']);
		if ($user_page >= 0)
		{
			$_REQUEST['page'] = $user_page;
		}
		elseif ($user_page == -1)
		{
			$user_not_found = TRUE;	
		}
		else
		{
			$user_has_no_scores = TRUE;
		}
	}
	else
	{
		$_REQUEST['user'] = '';
	}	
	
	$limit = $_REQUEST['resultsperpage'];
	$offset = $_REQUEST['resultsperpage'] * ( ($_REQUEST['page']) - 1);

	//Construct and execute the database query.

	$query = '
		select displayname, score, submitted, avatar from
		(
			select email, score, submitted from
			(
				select email, min(submitted) as submitted from
				(
					select email, max(score) as score
					from gamestats
					group by email
				) as a
				
				NATURAL JOIN gamestats
				group by email
			) as topscoredate
			NATURAL JOIN gamestats
		) as gs
		
		NATURAL JOIN profile
		order by score desc, submitted asc
		LIMIT $1 OFFSET $2;';

	$result = pg_prepare($dbconn, 'leaderboard_query', $query);
	$result = pg_execute($dbconn, 'leaderboard_query', array($limit, $offset));

	$leaderboard_rank = $offset + 1;

	//Find the total number of pages.
	$scorers_query = 'select count(*) from
		(
			select email, max(score)
			from gamestats
			group by email
		) a ';

	$scorers_result = pg_query($dbconn, $scorers_query);
	$scorers = pg_fetch_row($scorers_result);
	$scorers = $scorers[0];

	$gamecount_query = 'select count(*) from gamestats';
	$gamecount_result = pg_query($dbconn, $gamecount_query);
	$gamecount = pg_fetch_row($gamecount_result);
	$gamecount = $gamecount[0];

	$num_pages = ceil($scorers / $limit);

	echo '<header><h1>Leaderboard</h1></header>';

	//Generate and echo the top pagebar.
	$pagebar = generate_pagebar($_REQUEST['page'], $num_pages, $_REQUEST['resultsperpage']);

	echo '<nav id="topnav">';
	echo $pagebar;
	
	$searchbar = '<form name = "search">' .
			'<input type="text" name="user" value="Search for user..."' . 
				//' onblur="this.value=\'Search for user...\'"' . 
				' onfocus="this.value=\'\'" />' .  
			'<input type="submit" value="Search">' .
			'</form>';
	echo $searchbar;

	$resultsperpage_dropdown = '<form name = "rpp_dropdown">' .
		'<select name = "resultsperpage" onchange="this.form.submit()">' .
		'<option value="">Results per page...</option>' . 
		'<option value="20">20</option>' . 
		'<option value="50">50</option>' . 
		'<option value="100">100</option>' .
		'</select></form>';
	echo $resultsperpage_dropdown;

	echo '</nav>';
 
	if ($user_not_found){
		echo '<div id="searcherror">User not found...</div>';
	}
	elseif($user_has_no_scores){
		echo '<div id="searcherror">User has no high score...</div>';
	}
	
	echo '<div id="stats">' . $gamecount . ' games played by ' . 
			$scorers  .' players.</div>';

	echo '<h2>Top Scores</h2>';

	//Display the leaderboard.
	echo('<div class="lbentries">');
	while ($row = pg_fetch_row($result)){
		$username = $row[0];
		$avatar_id = $row[3];
	
		//Special format for the user being searched for.
		if ($username == $_REQUEST['user']) {
			echo '<div id="sel-lbentry">';
		}
		else{ echo '<div class="lbentry">'; }

		echo('<table><tr><td>' . 'Rank ' . $leaderboard_rank . 
		'</td><td>' . $row[1] . ' pts</td></tr>' . 
		'<tr><td>' . '<a href="profile.php?user=' . $username . '">' .
		'<img class="avatar" alt="ava" src="' 
			. $APP_CONTENT['avatar'][$avatar_id] . '"></a>' .
		'</td><td>' . '<a href="profile.php?user=' . $username . '">' 
			. $username . '</a>' .
		'</td>' .
		'</table></div>');

		$leaderboard_rank++;
	}

	echo'</div>';

	//Display the pagebar again at the bottom.
	echo '<nav id="bottompageselect">' . $pagebar . '</nav>';

?>


</body></html>
