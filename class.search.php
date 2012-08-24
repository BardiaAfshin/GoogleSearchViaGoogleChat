<?php

include_once("./classes/curl/class.curl.php") ;

//
// Create a new instance of the curl class and point it
// at the page to be fetched.
//

$c = new curl("https://www.google.com/search?rlz=1C1LENN_enUS496US496&aq=f&sugexp=chrome,mod=16&sourceid=chrome&client=ubuntu&channel=cs&ie=UTF-8&q=testing") ;

//
// By default, curl doesn't follow redirections and this
// page may or may not be available via redirection.
//

$c->setopt(CURLOPT_FOLLOWLOCATION, true) ;

//
// By default, the curl class expects to return data to
// the caller.
//

echo $c->exec() ;

//
// Check to see if there was an error and, if so, print
// the associated error message.
//

if ($theError = $c->hasError())
{
  echo $theError ;
}
//
// Done with the cURL, so get rid of the cURL related resources.
//

$c->close() ;
?>
