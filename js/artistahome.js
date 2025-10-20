// Adicionar funcionalidades aos botões
document.addEventListener('DOMContentLoaded', function() {
    const addButton = document.querySelector('.btn-primary');
    const editButton = document.querySelector('.btn-secondary');
    
    if (addButton) {
        addButton.addEventListener('click', function() {
            alert('Redirecionando para a página de adicionar obras...');
            // Aqui você pode adicionar a lógica para redirecionar ou abrir um modal
        });
    }
    
    if (editButton) {
        editButton.addEventListener('click', function() {
            alert('Redirecionando para a página de editar obras...');
            // Aqui você pode adicionar a lógica para redirecionar ou abrir um modal
        });
    }
    
    // Adicionar efeito de scroll suave para links internos
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