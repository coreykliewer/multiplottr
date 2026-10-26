<?php
// this code was updated to PHP 7.4 via CHATGPT

// Luis Rivera 20200718
// Resolver Max Connections issues on multiplottr.com
$dbconfig = [
    'host' => 'localhost',
    'user' => '[REDACTED_ACCOUNT]',
    'pass' => "[REDACTED_CONFIG_VALUE]",
    'name' => '[REDACTED_ACCOUNT]'
];

/**
 * Class to interact with a MySQL database using mysqli
 */
class db {

    /**
     * Instance of the database class
     */
    private static $instance;

    /**
     * Connection to MySQL.
     *
     * @var mysqli
     */
    protected $link;

    /**
     * Holds the most recent connection.
     *
     * @var mysqli
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
     * @return boolean or Connection resource
     */
    private function __construct() {
        self::set_params();

        $this->link = new mysqli(self::$dbHost, self::$dbUser, self::$dbPass, self::$dbName);

        if ($this->link->connect_error) {
            die('Connect Error (' . $this->link->connect_errno . ') ' . $this->link->connect_error);
        }
        $this->recentLink = $this->link;
        return $this->link;
    }

    /**
     * Creates an instance of the class.
     *
     * @return db
     */
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Sets connection/database parameters from global config.
     *
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
     * Executes a SQL query. If optional $only_first is set to true, it will
     * return the first row of the result as an array.
     *
     * @param  string $sql Query to run
     * @param  bool $only_first Return only the first row, as an array?
     * @return mixed
     */
    public function query($sql, $only_first = false) {
        $this->sql = $sql;
        $result = $this->link->query($sql);

        $this->queryCount++;

        if ($only_first) {
            return $result->fetch_assoc();
        }
        return $result;
    }

    /**
     * Fetches a row from a query result and returns the values from that row as an array.
     *
     * @param  mysqli_result $result The query result we are dealing with.
     * @return array
     */
    public function fetch_array($result) {
        return $result->fetch_assoc();
    }

    /**
     * Will fetch all records from the database, and will optionally return the
     * value of a single field from all records.
     *
     * @param  string $sql SQL Query string
     * @param  string $field Field/column
     * @return array Will return array of all db records.
     */
    public function fetch_all($sql, $field = '') {
        $return = [];
        $result = $this->query($sql);

        while ($row = $result->fetch_assoc()) {
            $return[] = ($field) ? $row[$field] : $row;
        }
        return $return;
    }

    /**
     * Fetches a single record from the database.
     *
     * @param  string $sql SQL Query string
     * @return array
     */
    public function fetch_one($sql) {
        $sql .= ' LIMIT 1';
        $result = $this->query($sql);
        return $result->fetch_assoc();
    }

    /**
     * Returns the number of rows in a result set.
     *
     * @param  mysqli_result $result The query result we are dealing with.
     * @return integer
     */
    public function num_rows($result) {
        return $result->num_rows;
    }

    /**
     * Returns the number of rows affected by the most recent query.
     *
     * @return integer
     */
    public function affected_rows() {
        return $this->link->affected_rows;
    }

    /**
     * Returns the number of queries executed.
     *
     * @return integer
     */
    public function num_queries() {
        return $this->queryCount;
    }

    /**
     * Returns the ID of the most recently inserted item in an auto_increment field.
     *
     * @return integer
     */
    public function insert_id() {
        return $this->link->insert_id;
    }

    /**
     * Escapes a value to make it safe for using in queries.
     *
     * @param string $value Value to be escaped
     * @param bool $do_like Do we need to escape this string for a LIKE statement?
     * @return string
     */
    public function prepare($value, $do_like = false) {
        if ($do_like) {
            $value = str_replace(array('%', '_'), array('\%', '\_'), $value);
        }
        return $this->link->real_escape_string($value);
    }

    /**
     * Closes our connection to MySQL.
     *
     * @return boolean
     */
    public function close() {
        $this->sql = '';
        return $this->link->close();
    }

    /**
     * Returns the MySQL error message.
     *
     * @return string
     */
    public function error() {
        return $this->link->error;
    }

    /**
     * Returns the MySQL error number.
     *
     * @return integer
     */
    function errno() {
        return $this->link->errno;
    }

}
?>
