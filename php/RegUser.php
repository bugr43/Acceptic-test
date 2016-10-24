<?
session_start();
require_once 'User.php';
require_once 'Db.php';
require_once 'config.php';
class RegUser extends User {
  private $error = [];
  private $repeatPassword = '';
  public static $db;

  public function __construct($login, $password, $email = '', $repeatPassword = '') {
    $this->login = $login;
    $this->email = $email;
    $this->password = $password;
    $this->repeatPassword = $repeatPassword;
  }

  public function regex($pattern, $field, $nameField, $error) {
    if(!preg_match($pattern, $field)) {
      $this->error[$nameField] = $error;
    }
  }

  public function len($min, $max, $variable, $error) {
    !(strlen($variable) > $max || strlen($variable) < $min) ?: $this->error[] = $error;
  }

  public function quality($one, $two, $nameField, $error) {
    // echo $one != $two;
    if(($one != $two)) {
      $this->error[$nameField] = $error;
    }
  }

  public function unique(array $row, $error) {
    // var_dump(array_shift($row) == 0);
    array_shift($row) == 0 ?: $this->error[] = $error;
  }

  public function getErrors() {
    return $this->error;
  }

  public function generateSalt($int) {
    $chars = 'wqwertyuiopasdfghjklzxcvbnm
      1234567890QWERTYUIOPASDFGHJKLMNBVCXZ~!@#$%^&*()_+|}{?":<>/.][\]"}';
    $size = strlen($chars) - 1;
    $salt = '';
    while($int--) {
      $salt .= $chars[rand(0, $size)];
    }
    return $salt;
  }

  public function generateHash($algo = PASSWORD_DEFAULT, $salt = '') {
    $this->password = password_hash($this->password, $algo);
  }

  public function verifyHash($inputPass,  $realPass) {
    return password_verify($inputPass, $realPass);
  }

  public function doRegistration() {
    $result = self::$db->
      query("SELECT COUNT(login) FROM users WHERE login='{$this->login}'");
    $row = self::$db->fetch_assoc($result);
    $this->regex(
      LOGIN_PATTERN,
      $this->login,
      'login',
      'Логин должен содержать буквы и цифры, первый символ обязательно буква.
      Длинна логина 2-20.'
    );
    $this->unique($row, 'Логин не уникален');

    $result = self::$db->
      query("SELECT COUNT(email) FROM users WHERE email='{$this->email}'");
    $row = self::$db->fetch_assoc($result);
    $this->regex(EMAIL_PATTERN, $this->email, 'email', 'Вы ввели не Email адресс');
    $this->unique($row, 'Email не уникален');

    $this->regex(H_PASSWORD_PATTERN,
      $this->password,
      'password',
      'Пароль должен содержать строчные и прописные латинские
      буквы, цифры, спецсимволы. Минимум 8 символов');
    $this->quality(
      $this->password,
      $this->repeatPassword,
      'repeatPassword', 'Пароли не совпадают'
    );

    // var_dump($this->getErrors());
    if(empty($this->getErrors())) {
      $salt = self::$db->escape($this->generateSalt(22));
      $this->generateHash(PASSWORD_BCRYPT, $salt);

      self::$db->
        query("
          INSERT
            INTO users (login,email)
            VALUES ('{$this->login}', '{$this->email}');
        ");
      $insertId = self::$db->getInsertId();

      self::$db->
        query("
          INSERT
            INTO users_password (id, salt, password)
            VALUES ('{$insertId}', '{$salt}', '{$this->password}')
        ");

      mail(
        $this->email,
        'acceptic.test',
        'Подтвердите ваш email',
        "Логин: {$this->login}<br />
          Email: {$this->email}<br />
          Ссылка для подтверждения: <a href = acceptic.test/?activate=".
          base64_encode($this->email).
          ">Нажмите здесь для подтверждения регистрации</a>"
      );
      $_SESSION['success'] = "
        Вы успешно зарегистрировались!
        На вашу почту отправлено письмо для подтверждения регистрации.
      ";

      foreach($this->getErrors() as $key => $error) {
        unset($_SESSION['error']);
      }

    } else {
      $_SESSION['login'] = $this->login;
      $_SESSION['email'] = $this->email;
      foreach($this->getErrors() as $key => $error) {
        $_SESSION['error'][$key] = $error;
        // print_r($error);
      }
    }
  }

  public static function doActivate() {
    if(isset($_GET['activate']) and !empty($_GET['activate'])) {
      $activate = base64_decode($_GET['activate']);
      // var_dump(self::$db);
      $result = self::$db->query("
        SELECT id, activation FROM users WHERE email='{$activate}'
      ");
      $row = self::$db->fetch_assoc($result);
      $userId = array_shift($row);
      $isActivate = array_shift($row);
      if($isActivate == 1) {
        $_SESSION['error']['activate'] = 'Данный email уже подтвержден';
      } else if(!empty($userId)) {
        self::$db->query("
          UPDATE users SET `activation` = 1 WHERE `id`={$userId}
        ");
        $_SESSION['success'] = 'Регистрация успешно подтверждена';
        unset($_GET['activate']);
      }
    }
  }

  public function doAuthorization() {

    $this->regex(
      LOGIN_PATTERN,
      $this->login,
      'login',
      'Логин должен содержать буквы и цифры, первый символ обязательно буква.
      Длинна логина 2-20.'
    );

    $this->regex(
      H_PASSWORD_PATTERN,
      $this->password,
      'password',
      'Пароль должен содержать строчные и прописные латинские буквы, цифры,
      спецсимволы. Минимум 8 символов'
    );

    $user = self::$db->
      query("SELECT id, login, email, activation FROM users WHERE login='{$this->login}'");
    $row = self::$db->fetch_assoc($user);
    if(is_null($row)) {
      $_SESSION['error']['failLogin'] = 'Логин или пароль введен не верно';
    } else {
      $userId = intval(array_shift($row));
      $this->login = array_shift($row);
      $this->email = array_shift($row);
      $userIsActive = array_shift($row);
      $password = self::$db->
        query("SELECT password FROM users_password WHERE id = {$userId}");
      $row = self::$db->fetch_assoc($password);
      $userPassword = array_shift($row);

      if($this->verifyHash($this->password, $userPassword) and !empty($userPassword)) {
        if(empty($this->getErrors())) {

          if($userIsActive == 1) {

            $_SESSION['auth'] = true;
            $_SESSION['login'] = $this->login;
            $_SESSION['email'] = $this->email;
            $_SESSION['id'] = $userId;
            foreach($this->getErrors() as $key => $error) {
              unset($_SESSION['error'][$key]);
            }

          } else {
            $_SESSION['error']['noActive'] = 'Вы не подтвердили свой email';
          }

        }
      } else {

        $_SESSION['error']['failLogin'] = 'Логин или пароль введен не верно';
        $_SESSION['inputLogin'] = $this->login;
        foreach($this->getErrors() as $key => $error) {
          $_SESSION['error'][$key] = $error;
        }

      }

    }

  }

  public function doUpdate() {
    $result = self::$db->
      query("SELECT COUNT(email), login FROM users WHERE email='{$this->email}'");
    $row = self::$db->fetch_assoc($result);
    $this->unique($row, 'Email не уникален');
    $countEmail = array_shift($row);
    $this->login = array_shift($row);
    $this->regex(EMAIL_PATTERN, $this->email, 'email', 'Вы ввели не Email адресс');
    // var_dump($row);
    // var_dump("SELECT COUNT(email) FROM users WHERE email='{$_POST['email']}'");
    if(empty($this->getErrors())) {
      $salt = self::$db->escape($this->generateSalt(22));
      $this->generateHash(PASSWORD_BCRYPT, $salt);

      self::$db->
        query("
          UPDATE users SET email='{$this->email}' WHERE id={$_SESSION['id']}
        ");

      $_SESSION['email'] = $this->email;
      $_SESSION['login'] = $this->login;
      unset($_SESSION['error']);
      echo json_encode(array(
        'email' => $this->email,
        'message' => 'Email успешно изменен'
      ));

    } else {
      echo json_encode(array(
        'email' => $this->email,
        'message' => 'Пользователь с таким Email уже существует'
      ));
      foreach($this->getErrors() as $key => $error) {
        $_SESSION['error'][$key] = $error;
      }
    }
  }
}

RegUser::$db = Db::getInstance();
