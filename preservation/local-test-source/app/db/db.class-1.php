<?php

$dbconfig = array(); // REDACTED: configure privately

/**
 * Class to interact with a mysql database
 */

class db {

    /**
     * Instance 
     */
    private static $instance;

    /**
     * Connection to MySQL.
     *
     * @var string
     */
    protected $link;

    /**
     * Holds the most recent connection.
     *
     * @var string
     */
    protected $recentLink = null;

    /**
     * Holds the contents of the most recent SQL query.
     *
     * @var string
     */
    protected $sql = '';

    /**
     * Holds the number of queries executed.
     *
     * @var integer
     */
    public $queryCount = 0;

    /**
     * The text of the most recent database error message.
     *
     * @var string
     */
    protected $error = '';

    /**
     * The error number of the most recent database error message.
     *
     * @var integer
     */

    /**
     * Database host
     *
     * @var string
     */
    protected static $dbHost;

    /**
     * Database username
     *
     * @var string
     */
    protected static $dbUser;

    /**
     * Database password
     *
     * @var string
     */
    protected static $dbPass;

    /**
     * Database name.
     *
     * @var string
     */
    protected static $dbName;

    /**
     * Constructor. Initializes a database connection and selects our database.
     *
     * @return boolean  or  Connection resource
     */
    private function __construct() {
        self::set_params();

        $this->link = @mysql_connect(self::$dbHost, self::$dbUser, self::$dbPass);


        if (is_resource($this->link) AND @mysql_select_db(self::$dbName, $this->link)) {
            $this->recentLink = & $this->link;
            return $this->link;
        } else {
            // If we couldn't connect or select the db...
            mysql_error();
        }
    }

    /**
     * Creates an instance of the class.
     *
     * @param  void
     * @return object
     */
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Sets connection/database parameters.
     *
     * @param  void
     * @return void
     */
    protected static function set_params() {
        global $dbconfig;

        self::$dbHost = $dbconfig['host'];
        self::$dbUser = $dbconfig['user'];
        self::$dbPass = $dbconfig['pass'];
        self::$dbName = $dbconfig['name'];
    }

    /**
     * Executes a sql query. If optional $only_first is set to true, it will
     * return the first row of the result as an array.
     *
     * @param  string  Query to run
     * @param  bool    Return only the first row, as an array?
     * @return mixed
     */
    public function query($sql, $only_first = false) {
        $this->recentLink = & $this->link;
        $this->sql = & $sql;
        $result = @mysql_query($sql, $this->link);

        $this->queryCount++;

        if ($only_first) {
            $return = $this->fetch_array($result);
            return $return;
        }
        return $result;
    }

    /**
     * Fetches a row from a query result and returns the values from that row as an array.
     *
     * @param  string  The query result we are dealing with.
     * @return array
     */
    public function fetch_array($result) {
        return @mysql_fetch_assoc($result);
    }

    /**
     * Will fetch all records from the database, and will optionally return the
     * value of a single field from all records.
     *
     * @param  string  $sql    SQL Query string
     * @param  string  $field  Field/column
     * @return array           Will return array of all db records.
     */
    public function fetch_all($sql, $field = '') {
        $return = array();

        if (($result = $this->query($sql))) {
            while ($row = $this->fetch_array($result)) {
                $return[] = ($field) ? $row[$field] : $row;
            }
        }
        return $return;
    }
    
    public function fetch_one($sql) {
        $sql .= ' LIMIT 1';
        $return = array();

        if (($result = $this->query($sql))) {
            $row = $this->fetch_array($result); 
            $return =  $row;
        }
        return $return;
    }

    /**
     * Returns the number of rows in a result set.
     *
     * @param  string  The query result we are dealing with.
     * @return integer
     */
    public function num_rows($result) {
        return @mysql_num_rows($result);
    }

    /**
     * Retuns the number of rows affected by the most recent query
     *
     * @return integer
     */
    public function affected_rows() {
        return @mysql_affected_rows($this->recentLink);
    }

    /**
     * Returns the number of queries executed.
     *
     * @param  none
     * @return integer
     */
    public function num_queries() {
        return $this->queryCount;
    }

    /**
     * Returns the ID of the most recently inserted item in an auto_increment field
     *
     * @return  integer
     */
    public function insert_id() {
        return @mysql_insert_id($this->link);
    }

    /**
     * Escapes a value to make it safe for using in queries.
     *
     * @param  string  Value to be escaped
     * @param  bool    Do we need to escape this string for a LIKE statement?
     * @return string
     */
    public function prepare($value, $do_like = false) {


	
        if ($do_like) {
            $value = str_replace(array('%', '_'), array('\%', '\_'), $value);
        }
		

        return @mysql_real_escape_string($value, $this->link);
    }
    
    /**
     * filter values for mysql
     * @param type $value
     * @return type 
     */
    public function filter($value) {
        $val = mysql_real_escape_string($value, $this->link);
        switch ($type) {
            case "text":
                $val = ($val != "") ? "'" . $val . "'" : "NULL";
                break;
            case "int":
                $val = ($val != "") ? intval($val) : "NULL";
                break;
            case "double":
                $val = ($val != "") ? "'" . doubleval($val) . "'" : "NULL";
                break;
            case "date":
                $val = ($val != "") ? "'" . $val . "'" : "NULL";
                break;
        }
        return $val;
    }

    /**
     * Closes our connection to MySQL.
     *
     * @param  none
     * @return boolean
     */
    public function close() {
        $this->sql = '';
        return @mysql_close($this->link);
    }

    /**
     * Returns the MySQL error message.
     *
     * @param  none
     * @return string
     */
    public function error() {
        $this->error = (is_null($this->recentLink)) ? '' : mysql_error($this->recentLink);
        return $this->error;
    }

    /**
     * Returns the MySQL error number.
     *
     * @param  none
     * @return string
     */
    function errno() {
        $this->errno = (is_null($this->recentLink)) ? 0 : mysql_errno($this->recentLink);
        return $this->errno;
    }

}