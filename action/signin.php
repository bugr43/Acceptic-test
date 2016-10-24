<?
session_start();
require_once '../php/RegUser.php';
require_once '../php/Db.php';
$db = Db::getInstance();
if(isset($_POST['signin'])) {
  $login = $db->escape($_POST['login']);
  $password = $db->escape($_POST['password']);
  $newUser = new RegUser(
    $login,
    $password
  );

  $newUser->doAuthorization();

}
header("Location: /user");
