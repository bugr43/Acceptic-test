<?
session_start();
require_once '../php/RegUser.php';
$db = Db::getInstance();
if(isset($_POST['signup'])) {
  $login = $db->escape($_POST['login']);
  $email = $db->escape($_POST['email']);
  $password = $db->escape($_POST['password']);
  $repeatPassword = $db->escape($_POST['repeatPassword']);
  $newUser = new RegUser(
    $login,
    $password,
    $email,
    $repeatPassword
  );
  $newUser->doRegistration();

}
header("Location: ".$_SERVER['HTTP_REFERER']);
