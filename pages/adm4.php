<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obras - Verseal</title>
    <link rel="stylesheet" href="../css/admhom4e.css">
    <script defer src="../js/admhome.js"></script>
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h2 class="logo">Verseal</h2>
        <nav class="menu">
            <a href="admhome.php">Início</a>
            <a href="adm2.php">Clientes</a>
            <a href="adm3.php">Artistas</a>
            <a href="adm4.php" class="active">Obras</a>
            <a href="adm5.php">Contato</a>
        </nav>
        <div class="sidebar-footer">
            <button class="toggle-btn">⟲</button>
            <button class="logout-btn">Sair</button>
        </div>
    </aside>

    <!-- CONTEÚDO PRINCIPAL -->
    <main class="dashboard">
        <header class="topbar">
            <h1>Obras</h1>
            <span class="welcome">Gerencie as obras cadastradas</span>
        </header>

        <section class="content">

            <!-- CONTAINER DA TABELA -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Artista</th>
                            <th>Preço</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Noite Estrelada</td>
                            <td>Vincent Van Gogh</td>
                            <td>R$ 8.000</td>
                            <td>
                                <button class="edit">Editar</button>
                                <button class="delete">Excluir</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Impressão, Sol Nascente</td>
                            <td>Claude Monet</td>
                            <td>R$ 6.500</td>
                            <td>
                                <button class="edit">Editar</button>
                                <button class="delete">Excluir</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- BOTÕES DE AÇÃO -->
            <div class="actions">
                <button class="refresh">Atualizar</button>
                <button class="new">Nova Obra</button>
            </div>
        </section>
    </main>
<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif;
}

body {
  display: flex;
  height: 100vh;
  background: linear-gradient(135deg, #f8f4f2, #fff9f8);
  color: #333;
  overflow: hidden;
}

/* ======== SIDEBAR ======== */
.sidebar {
  width: 250px;
  height: 100vh;
  background: linear-gradient(180deg, rgba(219,109,86,0.95), rgba(167,80,62,0.95));
  backdrop-filter: blur(6px);
  color: #fff;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 35px 20px;
  box-shadow: 3px 0 15px rgba(0, 0, 0, 0.15);
  border-right: 2px solid rgba(255, 255, 255, 0.15);
}

.logo {
  font-family: 'Playfair Display', serif;
  font-size: 2.6rem;
  color: #fdfdfd;
  letter-spacing: 5px;
  font-style: italic;
  text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
  margin-bottom: 40px;
  cursor: default;
  user-select: none;
  transition: all 0.3s ease;
}

.logo:hover {
  color: #fff3f0;
  text-shadow: 3px 3px 10px #db6d56;
}

.menu {
  display: flex;
  flex-direction: column;
  width: 100%;
}

.menu a {
  text-decoration: none;
  color: #fff;
  padding: 12px 20px;
  border-radius: 10px;
  margin-bottom: 10px;
  transition: all 0.3s ease;
  font-weight: 500;
}

.menu a:hover,
.menu a.active {
  background: rgba(255, 255, 255, 0.25);
  transform: translateX(4px);
  box-shadow: 0 2px 10px rgba(255, 255, 255, 0.1);
}

.sidebar-footer {
  margin-top: auto;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
}

.toggle-btn,
.logout-btn {
  background: rgba(255, 255, 255, 0.15);
  border: none;
  color: white;
  padding: 10px 15px;
  border-radius: 10px;
  cursor: pointer;
  width: 85%;
  transition: all 0.3s ease;
  font-weight: 600;
}

.toggle-btn:hover,
.logout-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: scale(1.05);
}

/* ======== CONTEÚDO PRINCIPAL ======== */
.dashboard {
  flex: 1;
  padding: 40px 50px;
  overflow-y: auto;
  animation: fadeIn 0.8s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(15px); }
  to { opacity: 1; transform: translateY(0); }
}

/* ======== TOPO ======== */
.topbar {
  display: flex;
  flex-direction: column;
  margin-bottom: 25px;
  border-bottom: 2px solid #f0e0de;
  padding-bottom: 10px;
}

.topbar h1 {
  font-size: 2rem;
  color: #db6d56;
  margin-bottom: 6px;
  text-shadow: 1px 1px 4px rgba(219,109,86,0.3);
}

.topbar .welcome {
  font-size: 1rem;
  color: #666;
  font-style: italic;
}

/* ======== TABELA ======== */
.table-container {
  background: #fff;
  border-radius: 20px;
  padding: 25px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.08);
  width: 90%;
  max-width: 950px;
  margin: 0 auto 45px auto;
  overflow-x: auto;
  transition: 0.3s ease;
}

.table-container:hover {
  box-shadow: 0 8px 25px rgba(219,109,86,0.25);
}

table {
  width: 100%;
  border-collapse: collapse;
  text-align: center;
  font-size: 0.95rem;
}

th {
  background: #ffe8e2;
  padding: 14px;
  font-weight: 600;
  color: #a7503e;
  border-bottom: 2px solid #f5d2ca;
}

td {
  padding: 14px;
  border-bottom: 1px solid #eee;
  transition: background 0.3s ease;
}

tr:hover td {
  background: #fff6f5;
}

/* ======== BOTÕES ======== */
button {
  cursor: pointer;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.edit {
  background: #ffb347;
  color: #fff;
  padding: 7px 15px;
}

.edit:hover {
  background: #e89c30;
}

.delete {
  background: #e74c3c;
  color: #fff;
  padding: 7px 15px;
}

.delete:hover {
  background: #c0392b;
}

.refresh, .new {
  background: #db6d56;
  color: #fff;
  padding: 12px 26px;
  font-size: 1rem;
  border-radius: 12px;
  box-shadow: 0 3px 10px rgba(219, 109, 86, 0.3);
}

.refresh:hover, .new:hover {
  background: #a7503e;
  transform: scale(1.05);
}

/* ======== AÇÕES ======== */
.actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 25px;
  gap: 10px;
  flex-wrap: wrap;
}

.view-more {
  color: #a7503e;
  font-weight: 600;
  cursor: pointer;
  transition: 0.3s;
}

.view-more:hover {
  text-decoration: underline;
  color: #db6d56;
}

/* ======== RESPONSIVIDADE ======== */
@media (max-width: 950px) {
  body {
    flex-direction: column;
  }

  .sidebar {
    width: 100%;
    flex-direction: row;
    justify-content: space-around;
    padding: 15px;
    height: auto;
  }

  .menu {
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: center;
  }

  .menu a {
    margin: 5px;
    padding: 8px 12px;
    font-size: 0.9rem;
  }

  .dashboard {
    padding: 20px;
  }

  th, td {
    font-size: 0.85rem;
  }
}

</style>
</body>
</html>
