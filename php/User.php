<?
class User {
  protected $email = "";
  protected $login = "";
  protected $password = "";


  public function __construct($login, $email) {
    $this->login = $login;
    $this->email = $email;
  }

  public function getUser() {
    return array(
      'login' => $this->login,
      'email' => $this->email
    );
  }
}
