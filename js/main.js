$(document).ready(function(){
  // the "href" attribute of .modal-trigger must specify the modal ID that wants to be triggered
  $('.modal-trigger').leanModal();
});
class User {
  constructor(login='login', email='email@mail.com', pass='pass', rPass='pass') {
    this.login = login;
    this.email = email;
    this.password = pass;
    this.repeatPassword = rPass;
  }

  setUser() {
    this.login = $('[name=login]').val();
    this.email = $('[name=email]').val();
    this.password = $('[name=password]').val();
    this.repeatPassword = $('[name=repeatPassword]').val();
  }

  getUser() {
    return this;
  }

  validateLogin() {
    if(!/^[a-zA-Z][a-zA-Z0-9-_\.]{1,20}$/.test(this.login)) {
      Materialize.toast('Логин должен содержать буквы и цифры, первый символ обязательно буква. Длинна 2-20.', 4000);
    };
    user.setUser();
  }

  validatePassword() {
    if(!/(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/.test(this.password)) {
      Materialize.toast('Пароль должен содержать строчные и прописные латинские буквы, цифры, спецсимволы. Минимум 8 символов.', 4000);
    };
    user.setUser();
  }

  validateEmail() {
    if(!/@/.test(this.email)) {
      Materialize.toast('Вы ввели не email.', 4000);
    };
    user.setUser();
  }

  isEqualPass() {
    if(this.password !== this.repeatPassword) {
      Materialize.toast('Пароли не совпадают.', 4000);
    };
    user.setUser();
  }

  doUpdate() {
    $.ajax({
      url: '/action/updateUser.php',
      method: 'POST',
      data: {
        'email': this.email,
        'update': ''
      }
    }).then(function(data) {
      let _data = JSON.parse(data);
      $('[name=email]').val(_data.email);
      $('.user-email').text(_data.email);
      Materialize.toast(_data.message, 4000);
    })
  }
}


let user = new User()
$('[name=login]').on('blur', user.validateLogin.bind(user));
$('[name=email]').on('blur', user.validateEmail.bind(user));
$('[name=password]').on('blur', user.validatePassword.bind(user));
$('[name=repeatPassword]').on('blur', user.isEqualPass.bind(user));

$('#updateUserData').on('click',  user.doUpdate.bind(user));
