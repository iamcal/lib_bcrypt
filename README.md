# lib_bcrypt - Store passwords less awfully

If you're storing passwords for user accounts, you should
<a href="http://codahale.com/how-to-safely-store-a-password/">use bcrypt</a>.
Don't argue. Just do it.

Do it more easily by using this library:

    require('lib_bcrypt.php');

    $hasher = new BCryptHasher();

    $hash = $hasher->HashPassword($plaintext_password);

Store `$hash` in your database. When a user logs in and you
need to check their password is correct:

    if ($hasher->CheckPassword($entered_password, $hash)){

        # password is legit

    }else{

        # Y U NO USE CORRECT PASSWORD????
    }


## Are there options???

There is only one option that you can use while creating a password
hash - the work factor. You can pass a value between 4 and 31:

    $weak_hash = $hasher->HashPassword($plaintext_password, 4);

    $crazy_hash = $hasher->HashPassword($plaintext_password, 31);

This value is the base-2 logarithm of the iteration count of the
hashing function. This means that the bigger the number, the slower
the hashing. Slow is a good thing!

Some example timings for computing a single hash on my laptop:

<table>
<tr><th>Work Factor</th><th>Approx. Time</th></tr>
<tr><td>4</td><td>2 ms</td></tr>
<tr><td>6</td><td>6 ms</td></tr>
<tr><td>8</td><td>25 ms</td></tr>
<tr><td>10</td><td>105 ms</td></tr>
<tr><td>12</td><td>400 ms</td></tr>
<tr><td>14</td><td>1700 ms</td></tr>
</table>

The default value is 8, which is pretty fast (though *much* slower than
`md5()` or sha1`()`). You might want to pick a higher value, if you have
beefy servers. The bigger the better, but balance it against how often
your app need to validate logins. Allowing your servers to be DOS'd by
submitted a few hundred login attempts per second would suck.

Because the work factor is built into the hash, you can change the value
you use over time in your application and the code that checks for valid 
passwords will not need to change.


## But I already use some other (bad) hashing function!

If you already store your passwords using `md5()`, sha1`()` or something
similar, you can't easily generate bcrypt hashes, since you don't have
the plaintext passwords.

But it's ok, you can fix this.

All bcrypt hashes start with the string `$2a$`, so it's easy to tell if
a stored hash is from bcrypt or not. When your user logs in, have code
something like this:

    if (substr($hash, 0, 4) == '$2a$'){

      # good, we have a bcrypt hash already
      $is_ok = $hasher->CheckPassword($entered_password, $hash);

    }else{

      # old, bad, hash
      $is_ok = md5($entered_password) == $hash;

      if ($is_ok) update_stored_hash($hasher->HashPassword($entered_password));
    }

Using a mechanism like this, you can upgrade your stored hashes the next
time a user logs in (or changes their password, or whatever).

If you want to get fancy, you could detect the work factor used to generate
the hash you've already got stored and re-hash the password if it's too low.
Using this technique, you can keep increasing your work factor as servers
become more powerful.
