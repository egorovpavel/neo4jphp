<?php
namespace Everyman\Neo4j\Cypher;

use Symfony\Component\Stopwatch\Stopwatch;
use Everyman\Neo4j;

/**
 * Represents a Cypher query string and variables
 * Query the database using Cypher. For query syntax, please refer
 * to the Cypher documentation for your server version.
 *
 * Latest documentation:
 * http://docs.neo4j.org/chunked/snapshot/cypher-query-lang.html
 */
class Query implements Neo4j\Query
{
	protected $client = null;
	protected $template = null;
	protected $vars = array();

	protected $result = null;

	/**
	 * Set the template to use
	 *
	 * @param Neo4j\Client $client
	 * @param string $template A Cypher query string or template
	 * @param array $vars Replacement vars to inject into the query
	 */
	public function __construct(Neo4j\Client $client, $template, $vars=array())
	{
		$this->client = $client;
		$this->template = $template;
		$this->vars = $vars;
	}

	/**
	 * Get the query script
	 *
	 * @return string
	 */
	public function getQuery()
	{
		return $this->template;
	}

	/**
	 * Get the template parameters
	 *
	 * @return array
	 */
	public function getParameters()
	{
		return $this->vars;
	}

	/**
	 * Retrieve the query results
	 *
	 * @return Neo4j\Query\ResultSet
	 */
	public function getResultSet()
	{
	        global $app;
	
	        $trace = debug_backtrace();
	        unset($trace[0]);
	        $trace = reset($trace);
	        $origin = str_replace("src\\model\\repos\\neo4j\\","",$trace['class']).$trace['type'].$trace['function'];
	
	        $e = $app['stopwatch']->start($origin);
	        if ($this->result === null) {
	                $this->result = $this->client->executeCypherQuery($this);
	        }
	        $e->stop();
	        $period = reset($e->getPeriods());
	
	        $debug = array(
	            "time" => $period->getDuration(),
	            "origin" => $origin
	        );
	
	        $app['monolog']->log(100,json_encode($debug));
	
	
	        return $this->result;
    	}
}
