
const btnLogin = document.getElementById('btn-login');
const btnRegister = document.getElementById('btn-register');
const formLogin = document.getElementById('loginForm');
const formRegister = document.getElementById('cadastroForm');
const toggleText = document.getElementById('toggleText');
const toggleLink = document.getElementById('toggleLink');

function showLogin() {
  btnLogin.classList.add('active');
  btnRegister.classList.remove('active');
  formLogin.classList.add('active');
  formRegister.classList.remove('active');
  toggleText.querySelector('#toggleLink').addEventListener('click', showRegister);
}

function showRegister() {
  btnRegister.classList.add('active');
  btnLogin.classList.remove('active');
  formRegister.classList.add('active');
  formLogin.classList.remove('active');
  toggleText.querySelector('#toggleLink').addEventListener('click', showLogin);
}

// Adicione isso ao seu login.js
document.addEventListener('DOMContentLoaded', function() {
  const loginBtn = document.getElementById('btn-login');
  const registerBtn = document.getElementById('btn-register');
  const loginForm = document.getElementById('loginForm');
  const cadastroForm = document.getElementById('cadastroForm');
  const tabsContainer = document.querySelector('.tabs');

  // Criar partículas flutuantes
  createParticles();

  function switchToLogin() {
    loginBtn.classList.add('active');
    registerBtn.classList.remove('active');
    loginForm.classList.add('active');
    cadastroForm.classList.remove('active');
    tabsContainer.classList.remove('register-active');
  }

  function switchToRegister() {
    registerBtn.classList.add('active');
    loginBtn.classList.remove('active');
    cadastroForm.classList.add('active');
    loginForm.classList.remove('active');
    tabsContainer.classList.add('register-active');
  }

  loginBtn.addEventListener('click', switchToLogin);
  registerBtn.addEventListener('click', switchToRegister);

  function createParticles() {
    const particlesContainer = document.createElement('div');
    particlesContainer.className = 'floating-particles';
    document.body.appendChild(particlesContainer);

    for (let i = 0; i < 15; i++) {
      const particle = document.createElement('div');
      particle.className = 'particle';
      particle.style.left = Math.random() * 100 + 'vw';
      particle.style.top = Math.random() * 100 + 'vh';
      particle.style.animationDelay = Math.random() * 5 + 's';
      particlesContainer.appendChild(particle);
    }
  }
});

btnLogin.addEventListener('click', showLogin);
btnRegister.addEventListener('click', showRegister);
toggleLink.addEventListener('click', showRegister);

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