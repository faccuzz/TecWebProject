const products = [
    { id:"000", name: "Lampada Gin Bombay", category: "Gin", price: "45€", link: "prodotto-gin.html" },
    { id:"001", name: "Lampada Jack Daniels", category: "Whiskey", price: "50€", link: "prodotto-jack.html" },
    { id:"002", name: "Centrotavola Belvedere", category: "Vodka", price: "65€", link: "prodotto-vodka.html" },
    { id:"003", name: "Lampada Vino Rosso", category: "Vino", price: "35€", link: "prodotto-vino.html" },
    { id:"004", name: "Kit Fai da Te", category: "Accessori", price: "20€", link: "prodotto-kit.html" }
];

const currentPage = window.location.pathname;

const searchInput = document.getElementById('search-input');
const resultList = document.querySelector('.result-list');
const resultGrid = document.querySelector('.result-grid');

/**
 * Filtra i prodotti
 * 
 * TODO: filtra prodotti dal database
 */
function filterProducts(input) {
    const filteredProducts = products.filter(product => {
        return product.name.toLowerCase().includes(input) ||
            product.category.toLowerCase().includes(input);
    });
    return filteredProducts;
}

/**
 * Genera il prodotto html con le classi richieste
 */
function renderProduct(htmlContent, classes) {
    const resultItem = document.createElement('div');
    resultItem.innerHTML = htmlContent;
    resultItem.className = classes;
    return resultItem;
}

/**
 * Renderizza una lista con i risultati
 */
function renderList(items) {
    resultList.innerHTML = '';
    resultList.style.display = 'block';

    if (items.length === 0) {
        const htmlContent = `
                                            <a href="#"> 
                                                <strong>No products found</strong>
                                            </a>
                                        `;
        const classes = 'result-item'
        const resultItem = renderProduct(htmlContent, classes);
        resultList.append(resultItem);
        return;
    }
    else {
        items.slice(0, 5).forEach(product => {
            const htmlContent = `
                                                <a href="item.html?id=${product.id}">
                                                    <strong>${product.name}</strong>
                                                </a>
                                            `;
            const classes = 'result-item';
            const resultItem = renderProduct(htmlContent, classes);

            resultList.append(resultItem);
        });
    }
}

/**
 * Renderizza una griglia con i risultati
 */
function renderGrid(items) {
    resultGrid.innerHTML = '';
    resultGrid.style.display = 'grid';

    items.forEach(product => {
        const htmlContent = `
                                    <img src="./assets/img/gin-jute.webp" alt="Bottle gin and jute lamp">
                                    <div class="card-content">
                                        <h3>${product.name}</h3>
                                        <p>Breve descrizone dell'articolo che va a capo così vediamo se ci sta.</p>
                                        <a href="item.html?id=${product.id}" class="button">Discover</a>
                                    </div>
                            `;
        const resultItem = renderProduct(htmlContent, 'card');
        resultGrid.append(resultItem)
    })
}

/**
 * Se presente un input, renderizza la lista
 * 
 * TODO: la funzione deve capire se renderizzare una lista oppure una griglia
 */
if (searchInput) {
    searchInput.addEventListener('input', (e) => {
        const searchValue = e.target.value.toLowerCase();

        if (searchValue === '') {
            if(resultList) resultList.style.display = 'none';
            if(resultGrid) renderGrid(products);
            return;
        }
        else {
            const filteredProducts = filterProducts(searchValue);
            if(resultList) renderList(filteredProducts);
            if(resultGrid) renderGrid(filteredProducts);
        }

    });
}

/**
 * Chiude la lista di risultati nella Home se togli il focus dalla SearchBox
 */
document.addEventListener('click', (e) => {
    if (resultList && resultList.style.display === 'block') {
        if (!searchInput.contains(e.target) && !resultList.contains(e.target)) {
            resultList.style.display = 'none';
        }
    }
});

if(resultGrid){
    renderGrid(products.slice(0,10));
}