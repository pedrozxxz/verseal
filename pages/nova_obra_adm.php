<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Obra - Verseal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"/>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
        body { background: linear-gradient(135deg, #f8f4f2, #fff9f8); display:flex; justify-content:center; align-items:center; min-height:100vh;}
        .form-container { background:#fff; padding:40px 30px; border-radius:25px; box-shadow:0 8px 25px rgba(0,0,0,0.12); width:90%; max-width:500px; animation:fadeIn 0.8s ease;}
        @keyframes fadeIn { from { opacity:0; transform: translateY(20px);} to { opacity:1; transform: translateY(0);} }
        .form-container h1 { text-align:center; font-size:2rem; color:#db6d56; margin-bottom:25px; text-shadow:1px 1px 4px rgba(219,109,86,0.3);}
        .form-group { position: relative; margin-bottom: 20px; }
        .form-group label { display:block; font-weight:500; margin-bottom:8px; color:#555;}
        .form-group input { width:100%; padding:12px 40px 12px 12px; border-radius:12px; border:1px solid #ccc; outline:none; transition:0.3s;}
        .form-group input:focus { border-color:#db6d56; box-shadow:0 2px 8px rgba(219,109,86,0.2);}
        .form-group i { position:absolute; right:12px; top:36px; color:#db6d56; font-size:1.1rem;}
        .save-btn { width:100%; padding:14px; border:none; border-radius:12px; background:linear-gradient(135deg,#db6d56,#a7503e); color:#fff; font-weight:600; font-size:1rem; cursor:pointer; transition:0.3s; box-shadow:0 4px 12px rgba(219,109,86,0.3);}
        .save-btn:hover { transform: scale(1.03); background: linear-gradient(135deg,#a7503e,#db6d56);}
        @media(max-width:500px) { .form-container{padding:30px 20px;} .form-group i{top:38px;} }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Nova Obra</h1>
        <form>
            <div class="form-group">
                <label for="titulo">Título</label>
                <input type="text" id="titulo" placeholder="Digite o título da obra">
                <i class="fas fa-pencil-alt"></i>
            </div>
            <div class="form-group">
                <label for="artista">Artista</label>
                <input type="text" id="artista" placeholder="Digite o nome do artista">
                <i class="fas fa-palette"></i>
            </div>
            <div class="form-group">
                <label for="preco">Preço</label>
                <input type="text" id="preco" placeholder="Digite o preço">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <button type="button" class="save-btn" onclick="window.location.href='adm4.php'">Salvar</button>
        </form>
    </div>
</body>
</html>
