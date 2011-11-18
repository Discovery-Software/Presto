<?php 

// Dump output for debugging
function dump() { 
?><pre class="dump"><?php
	print(
		implode("\n", 
			array_map(
				function ($o) { if (is_array($o)) print_r($o); else print "$o\n";  }, 
				func_get_args()
			)
		)
	);
?></pre><?php 
}

// Load classes automatically
function __autoload($c) { 
	try {
		include_once($in . $c . '.php'); 
	} catch (Exception $e) {
		dump('Failed to auto-load', $e, $c);
	}
}

function coalesce() {
  return array_shift(array_filter(func_get_args()));
}
?>