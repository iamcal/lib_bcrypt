<?php
	require 'PasswordHash.php';
	require 'testmore.php';

	header('Content-type: text/plain');

	#
	# Try some different work functions.
	#
	# On my Intel Core i7 1.8GHz windows laptop:
	#	wf=4	~2 ms
	#	wf=6	~6 ms
	#	wf=8	~25 ms
	#	wf=10	~105 ms
	#	wf=12	~400 ms
	#	wf=14	~1700 ms
	#
	# Times are *per hash*. Beyond 14 it gets a bit crazy.
	#

	foreach (array(4, 8, 12) as $work_function){

		$t_hasher = new PasswordHash($work_function);

		$correct = 'test12345';
		$hash = $t_hasher->HashPassword($correct);

		diag('Hash: ' . $hash);

		$t1 = microtime_ms();
		$check = $t_hasher->CheckPassword($correct, $hash);
		$t2 = microtime_ms() - $t1;
		ok($check, "correct hash (wf=$work_function, $t2 ms)");


		$wrong = 'test12346';
		$t1 = microtime_ms();
		$check = $t_hasher->CheckPassword($wrong, $hash);
		$t2 = microtime_ms() - $t1;
		ok(!$check, "incorrect hash (wf=$work_function, $t2 ms)");

		unset($t_hasher);
	}


	function microtime_ms(){
		    list($usec, $sec) = explode(" ", microtime());
		    return intval(1000 * ((float)$usec + (float)$sec));
	}
?>
