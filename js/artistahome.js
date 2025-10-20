// Menu Hamburguer Desktop
document.addEventListener('DOMContentLoaded', function () {
    const menuToggleDesktop = document.getElementById('menu-toggle-desktop');
    const menuContentDesktop = document.querySelector('.menu-content-desktop');

    // Fechar menu ao clicar fora
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.hamburger-menu-desktop')) {
            menuToggleDesktop.checked = false;
        }
    });

    // Profile dropdown hover
    const profileDropdown = document.querySelector('.profile-dropdown');
    const dropdownContent = document.getElementById('profile-dropdown');

    profileDropdown.addEventListener('mouseenter', function() {
        dropdownContent.style.display = 'block';
    });

    profileDropdown.addEventListener('mouseleave', function() {
        dropdownContent.style.display = 'none';
    });

    // Botões de ação
    const addButton = document.querySelector('.btn-primary');
    const editButton = document.querySelector('.btn-secondary');

    if (addButton) {
        addButton.addEventListener('click', function() {
            alert('Redirecionando para adicionar obras...');
            // window.location.href = 'adicionar-obras.html';
        });
    }

    if (editButton) {
        editButton.addEventListener('click', function() {
            alert('Redirecionando para editar obras...');
            // window.location.href = 'editar-obras.html';
        });
    }

    // Efeito de scroll suave
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });
});