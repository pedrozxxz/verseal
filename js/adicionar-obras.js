// JavaScript para funcionalidade da página Adicionar Obras
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('upload-area');
    const fileInput = document.getElementById('imagem-obra');
    const imagePreview = document.getElementById('image-preview');
    const form = document.getElementById('form-obras');
    const salvarBtn = document.querySelector('.btn-salvar');

    // Funcionalidade de upload de imagem
    uploadArea.addEventListener('click', function() {
        fileInput.click();
    });

    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', function() {
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleImageUpload(files[0]);
        }
    });

    fileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            handleImageUpload(e.target.files[0]);
        }
    });

    function handleImageUpload(file) {
        // Verificar se é uma imagem
        if (!file.type.startsWith('image/')) {
            alert('Por favor, selecione apenas arquivos de imagem.');
            return;
        }

        // Verificar tamanho do arquivo (10MB)
        if (file.size > 10 * 1024 * 1024) {
            alert('A imagem deve ter no máximo 10MB.');
            return;
        }

        const reader = new FileReader();
        
        reader.onload = function(e) {
            imagePreview.innerHTML = `
                <img src="${e.target.result}" alt="Preview da obra">
                <button type="button" class="btn-remover-imagem" onclick="removerImagem()">
                    <i class="fas fa-times"></i> Remover
                </button>
            `;
        };
        
        reader.readAsDataURL(file);
    }

    // Função global para remover imagem
    window.removerImagem = function() {
        imagePreview.innerHTML = '';
        fileInput.value = '';
    };

    // Validação e envio do formulário
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validar campos obrigatórios
        const nomeObra = document.getElementById('nome-obra').value;
        const preco = document.getElementById('preco').value;
        const tecnica = document.getElementById('tecnica').value;
        const dataCriacao = document.getElementById('data-criacao').value;
        const imagem = fileInput.files[0];

        if (!nomeObra || !preco || !tecnica || !dataCriacao || !imagem) {
            alert('Por favor, preencha todos os campos obrigatórios e adicione uma imagem.');
            return;
        }

        // Simular envio (substituir por AJAX/API real)
        salvarBtn.classList.add('loading');
        
        setTimeout(function() {
            salvarBtn.classList.remove('loading');
            alert('Obra salva com sucesso!');
            form.reset();
            imagePreview.innerHTML = '';
            // Redirecionar para a página do artista
            window.location.href = 'artistahome.php';
        }, 2000);
    });

    // Formatação de preço
    const precoInput = document.getElementById('preco');
    precoInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = (value / 100).toFixed(2);
        value = value.replace('.', ',');
        value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        
        if (value === '0,00') {
            e.target.value = '';
        } else {
            e.target.value = 'R$ ' + value;
        }
    });

    // Menu Hamburguer Desktop
    const menuToggleDesktop = document.getElementById('menu-toggle-desktop');
    const menuContentDesktop = document.querySelector('.menu-content-desktop');

    // Fechar menu ao clicar fora
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.hamburger-menu-desktop')) {
            menuToggleDesktop.checked = false;
        }
    });

    // Dropdown do perfil
    const profileIcon = document.getElementById('profile-icon');
    const profileDropdown = document.getElementById('profile-dropdown');

    if (profileIcon && profileDropdown) {
        profileIcon.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
        });

        // Fechar dropdown ao clicar fora
        document.addEventListener('click', function (e) {
            if (!profileDropdown.contains(e.target) && e.target !== profileIcon) {
                profileDropdown.style.display = 'none';
            }
        });

        // Prevenir fechamento ao clicar dentro do dropdown
        profileDropdown.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }
});