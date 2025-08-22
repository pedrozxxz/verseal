// Controle das abas
const btnLogin = document.getElementById('btn-login');
const btnRegister = document.getElementById('btn-register');
const formLogin = document.getElementById('loginForm');
const formRegister = document.getElementById('cadastroForm');

btnLogin.addEventListener('click', () => {
  btnLogin.classList.add('active');
  btnRegister.classList.remove('active');
  formLogin.classList.add('active');
  formRegister.classList.remove('active');
});

btnRegister.addEventListener('click', () => {
  btnRegister.classList.add('active');
  btnLogin.classList.remove('active');
  formRegister.classList.add('active');
  formLogin.classList.remove('active');
});

// Validação com SweetAlert2 e redirecionamento
// formLogin.addEventListener('submit', e => {
//   e.preventDefault();
//   const email = document.getElementById('loginEmail').value.trim();
//   const senha = document.getElementById('loginSenha').value.trim();

//   if (!email || !senha) {
//     Swal.fire({
//       icon: 'warning',
//       title: 'Campos vazios',
//       text: 'Por favor, preencha todos os campos para entrar.',
//       confirmButtonColor: '#cc624e'
//     });
//     return;
//   }

//   Swal.fire({
//     icon: 'success',
//     title: 'Login realizado!',
//     showConfirmButton: false,
//     timer: 1500,
//     background: '#fff',
//     color: '#1a1a1a',
//     willClose: () => {
//       window.location.href = 'index.php';
//     }
//   });
// });

// // Validação cadastro com confirmação de senha
// formRegister.addEventListener('submit', e => {
//   e.preventDefault();
//   const nome = document.getElementById('cadastroNome').value.trim();
//   const email = document.getElementById('cadastroEmail').value.trim();
//   const senha = document.getElementById('cadastroSenha').value.trim();
//   const senhaConf = document.getElementById('cadastroSenhaConfirm').value.trim();

//   if (!nome || !email || !senha || !senhaConf) {
//     Swal.fire({
//       icon: 'warning',
//       title: 'Campos vazios',
//       text: 'Por favor, preencha todos os campos para cadastrar.',
//       confirmButtonColor: '#cc624e'
//     });
//     return;
//   }

//   if (senha !== senhaConf) {
//     Swal.fire({
//       icon: 'error',
//       title: 'Senha não confere',
//       text: 'As senhas digitadas não são iguais.',
//       confirmButtonColor: '#cc624e'
//     });
//     return;
//   }
// });