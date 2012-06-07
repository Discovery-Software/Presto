<?php 

/* Presto API base */
class API extends REST {
		
	private $concepts;
	private $types;
	private $delegates;
	
	/* Initialization */
	public function __construct($c) {	
		
		// guess valid REST concepts from class members
		
		foreach (get_class_methods($c) as $fn) {
		
			$method = strtok($fn, '_');
			$concept = strtok('');

			if (!empty($concept) && in_array($method, self::$METHODS))
				$this->concepts[] = $concept;
		
		}
	}
	/* Test if a route refers to a valid concept (member) */
	public function is_valid_concept($c) { return !empty($this->concepts) 
		&& in_array($c, $this->concepts); }
		
	/**	Advanced route mapping
	
		Adds a mapping between a URI pattern and a callback to delegate to. Used to route complex
		sub delegates, for things like hierarchical resources.
	 */	
	public function add_delegate($regex, $delegateFn) {
	
		// check for conflicts in previously added delegates.
		if (array_key_exists($regex, $this->delegates)) 
			throw new Exception("URI delegate already exists: pattern collision for '$regex'", 500);
		
		// preflight (and compile + cache) the regex ... errors handled by Presto.
		if (!preg_match($regex, '')) $this->delegates[$regex] = $delegateFn;
	}
	
	/* Do delegation for hierarchical sub-routes
	
		Provides internal delegation to registered callbacks. 
		
		Throws if delegation fails.
	*/
	public function delegate($ctx, $data = null) {
		if (empty($this->delegates) || empty($ctx) || empty($ctx->params)) 
			throw new Exception('Unserviceable internal delegation attempt.', 501);
			
		$path = implode('/', $ctx->params);
		foreach ($this->delegates as $p => $d) {
			if (preg_match($p, $path)) {
				if (empty($data)) return $this->$d($ctx);
				else return $this->$d($ctx, $data);
			}
		}
		throw new Exception("Bad request. No sub method exists for resource $path", 404);
	}
}
