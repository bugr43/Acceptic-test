<?
session_start();
require_once 'vendor/autoload.php';
require_once 'php/RegUser.php';
Twig_Autoloader::register();
RegUser::doActivate(); // Запуск проверки подтверждения регистрации
$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader);
$routs = explode('/', $_GET['url']);
$index = $twig->loadTemplate('index.twig');
// print_r($_SESSION);
// var_dump($_SESSION);
echo $index->render(array_merge($_SESSION, array('page' => $routs[0])));

// foreach ($_SESSION as $key => $value) {
  unset($_SESSION['error']);
  unset($_SESSION['success']);
// }
