<?
session_start();
require_once '../php/RegUser.php';
$db = Db::getInstance();
if(isset($_POST['update'])) {
  $email = $db->escape($_POST['email']);
  $newUser = new RegUser(
    '',
    '',
    $email,
    ''
  );
  $newUser->doUpdate();

  // print_r($login, $mail);
}
// header("Location: ".$_SERVER['HTTP_REFERER']);
