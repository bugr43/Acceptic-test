<?
define(DB_HOST, 'localhost');
define(DB_USER, 'root');
define(DB_PASSWORD, '');
define(DB_NAME, 'acceptic-test');

define(H_PASSWORD_PATTERN, '/(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/');
// Пример пароля Hg1*both
define(LOGIN_PATTERN, '/^[a-zA-Z][a-zA-Z0-9-_\.]{1,20}$/');
define(EMAIL_PATTERN, '/@/');
