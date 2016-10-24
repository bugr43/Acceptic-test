<?
session_start();
require_once 'User.php';
require_once 'config.php';
class LogUser extends User {
  private $error = [];
  private $repeadPassword = '';

  public function __construct($login, $password) {
    $this->login = $login;
    $this->password = $password;
  }

  public function regex($pattern, $field, $error) {
    preg_match($pattern, $field) ?: $_SESSION[$error] = false;
  }

  public function len($min, $max, $variable, $error) {
    !(strlen($variable) > $max || strlen($variable) < $min) ?: $this->error[] = $error;
  }


  public function quality($one, $two, $error) {
    $one == $two ?: $_SESSION[$error] = false;
  }

  public function unique(array $row, $error) {
    array_shift($row) == 0 ?: $this->error[] = $error;
  }

  public function getErrors() {
    return $this->error;
  }

  public function generateSalt($int) {
    $chars = 'wqwertyuiopasdfghjklzxcvbnm1234567890QWERTYUIOPASDFGHJKLMNBVCXZ~!@#$%^&*()_+|}{?":<>/.][\]"}';
    $size = strlen($chars) - 1;
    $salt = '';
    while($int--) {
      $salt .= $chars[rand(0, $size)];
    }
    return $salt;
  }

  public function generateHash($algo = PASSWORD_DEFAULT, array $onpions = null) {
    !is_null($onptions) ?: $options = [
      'salt' => $this->generateSalt(22),
      'cost' => 10
    ];
    $this->password = password_hash($this->password, $algo, $options);
  }
}
