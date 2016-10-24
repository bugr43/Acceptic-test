<?
require_once 'config.php';
class Db {
  private static $instance;
  public $connection;
  private $lastQuery;
  private function __construct() {
    $this->setConnection();
  }
  private function __clone() {
  }

  private function setConnection() {
    $this->connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $this->connection ?: die('Соединение с базой данных не удалось установить');
  }

  private function confirmQuery() {
    $result ?: die('Не удалось выполнить запрос к базе данных <br />'.$this->lastQuery);
  }

  public function getInstance() {
    return (is_null(self::$instance)) ? static::$instance = new self() : static::$instance;
  }

  public function query($sql) {
    $this->lastQuery = $sql;
    $result = mysqli_query($this->connection, $sql);
    return $result;
  }

  public function escape($string) {
    return mysqli_real_escape_string($this->connection, $string);
  }

  public function fetch_assoc($result) {
    return mysqli_fetch_assoc($result);
  }

  public function getInsertId() {
    return mysqli_insert_id($this->connection);
  }
}
