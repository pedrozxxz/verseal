// Produtos de exemplo
const produtos = [];
for (let i = 1; i <= 30; i++) {
  produtos.push({
    nome: `Obra ${i}`,
    preco: Math.floor(Math.random() * 900) + 100,
    categoria: i % 3 === 0 ? "escultura" : i % 3 === 1 ? "pintura" : "joia",
    img: `img/produto${(i % 6) + 1}.jpg`
  });
}

let itensPorPagina = 6;
let paginaAtual = 1;

function exibirProdutos() {
  const grid = document.getElementById('produtosGrid');
  grid.innerHTML = "";
  
  const filtroNome = document.getElementById('busca').value.toLowerCase();
  const filtroCat = document.getElementById('categoria').value;
  const filtroPreco = document.getElementById('preco').value;

  let filtrados = produtos.filter(p => 
    p.nome.toLowerCase().includes(filtroNome) &&
    (filtroCat ? p.categoria === filtroCat : true) &&
    (filtroPreco ? filtrarPorPreco(p.preco, filtroPreco) : true)
  );

  let inicio = 0;
  let fim = paginaAtual * itensPorPagina;
  filtrados.slice(inicio, fim).forEach(p => {
    const card = document.createElement('div');
    card.classList.add('produto-card');
    card.innerHTML = `
      <img src="${ImageTrackList.jfif}" alt="${p.nome}">
      <h3>${p.nome}</h3>
      <p class="preco">R$ ${p.preco},00</p>
      <a href="detalhes.html">Ver Detalhes</a>
    `;
    grid.appendChild(card);
  });

  document.getElementById('carregarMais').style.display = fim < filtrados.length ? 'inline-block' : 'none';
}

function filtrarPorPreco(valor, faixa) {
  const [min, max] = faixa.split('-').map(Number);
  return valor >= min && valor <= max;
}

document.getElementById('busca').addEventListener('input', () => { paginaAtual = 1; exibirProdutos(); });
document.getElementById('categoria').addEventListener('change', () => { paginaAtual = 1; exibirProdutos(); });
document.getElementById('preco').addEventListener('change', () => { paginaAtual = 1; exibirProdutos(); });

document.getElementById('carregarMais').addEventListener('click', () => {
  paginaAtual++;
  exibirProdutos();
});

window.onload = () => {
  exibirProdutos();
};