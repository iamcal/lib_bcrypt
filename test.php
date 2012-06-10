<?php
	require 'lib_bcrypt.php';
	require 'testmore.php';

	header('Content-type: text/plain');

	plan(7);

	#
	# Try some different work factor.
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

	$t_hasher = new BCryptHasher();

	foreach (array(4, 8, 12) as $work_factor){

		$correct = 'test12345';
		$hash = $t_hasher->HashPassword($correct, $work_factor);

		diag('Hash: ' . $hash);

		$t1 = microtime_ms();
		$check = $t_hasher->CheckPassword($correct, $hash);
		$t2 = microtime_ms() - $t1;
		ok($check, "correct hash (wf=$work_factor, $t2 ms)");


		$wrong = 'test12346';
		$t1 = microtime_ms();
		$check = $t_hasher->CheckPassword($wrong, $hash);
		$t2 = microtime_ms() - $t1;
		ok(!$check, "incorrect hash (wf=$work_factor, $t2 ms)");
	}


	#
	# check against a known hash.
	# taken from https://github.com/codahale/bcrypt-ruby/blob/master/spec/bcrypt/engine_spec.rb
	#

	$hash = '$2a$05$abcdefghijklmnopqrstuu5s2v8.iXieOjg/.AySBTTZIIVFJeBui';
	$pass = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

	ok($t_hasher->CheckPassword($pass, $hash), "check known hash");



	function microtime_ms(){
		    list($usec, $sec) = explode(" ", microtime());
		    return intval(1000 * ((float)$usec + (float)$sec));
	}
?>
