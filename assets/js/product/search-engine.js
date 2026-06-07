let products = [];

const searchInput = document.getElementById('search-input');
const resultList = document.querySelector('.result-list');
const resultGrid = document.querySelector('.result-grid');
const resultsStatus = document.getElementById('catalog-results-status');

async function init() {
    try {
        products = await fetchProducts();
        if (!products) products = [];

        if (resultGrid) {
            renderGrid(products);
            if (resultsStatus) {
                resultsStatus.textContent = products.length === 0
                    ? 'Nessun prodotto disponibile.'
                    : `${products.length} prodotti disponibili nel catalogo.`;
            }
        }
    } catch (error) {
        console.error("Errore durante l'avvio del catalogo:", error);
        if (resultsStatus) resultsStatus.textContent = 'Errore nel caricamento del catalogo.';
    }
}

function filterProducts(input) {
    return products.filter(product => product.productName.toLowerCase().includes(input));
}

function renderProduct(htmlContent, classes, options = {}) {
    const resultItem = document.createElement('div');
    resultItem.innerHTML = htmlContent;
    resultItem.className = classes;
    if (options.role) resultItem.setAttribute('role', options.role);
    return resultItem;
}

function renderList(items) {
    if (!resultList) return;
    resultList.innerHTML = '';
    resultList.style.display = 'block';

    if (items.length === 0) {
        resultList.removeAttribute('role');
        resultList.removeAttribute('aria-label');
        const item = document.createElement('div');
        item.className = 'result-item';
        item.setAttribute('role', 'status');
        item.textContent = 'Nessun prodotto trovato.';
        resultList.append(item);
        return;
    }

    // Applica role="listbox" solo quando ci sono figli "option" reali
    resultList.setAttribute('role', 'listbox');
    resultList.setAttribute('aria-label', 'Risultati di ricerca');

    items.slice(0, 5).forEach(product => {
        const safe = product.productName.replace(/</g, '&lt;');
        const item = renderProduct(
            `<a href="item.html?id=${product.id}"><strong>${safe}</strong></a>`,
            'result-item',
            { role: 'option' }
        );
        resultList.append(item);
    });
}

function renderGrid(items) {
    if (!resultGrid) return;
    resultGrid.innerHTML = '';
    resultGrid.style.display = 'grid';

    if (items.length === 0) {
        resultGrid.removeAttribute('role');
        const empty = document.createElement('p');
        empty.setAttribute('role', 'status');
        empty.textContent = 'Nessun prodotto trovato.';
        resultGrid.append(empty);
        return;
    }

    // Applica role="list" solo quando ci sono figli "listitem"
    resultGrid.setAttribute('role', 'list');

    items.forEach(product => {
        const safeName = product.productName.replace(/</g, '&lt;');
        const safeDesc = (product.description || '').replace(/</g, '&lt;');
        const altText = `Foto del prodotto ${safeName}`;
        const htmlContent = `
            <img src="./assets/img/${product.imageUrl}" alt="${altText}">
            <div class="card-content">
                <h2>${safeName}</h2>
                <p>${safeDesc}</p>
                <a href="item.html?id=${product.id}" class="button" aria-label="Scopri ${safeName}">Scopri</a>
            </div>
        `;
        const card = renderProduct(htmlContent, 'card', { role: 'listitem' });
        resultGrid.append(card);
    });
}

init();

if (searchInput) {
    // Aria-expanded sul container search per indicare lista risultati aperta
    function setListboxExpanded(expanded) {
        if (resultList) {
            resultList.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        }
    }

    searchInput.addEventListener('input', (e) => {
        const searchValue = e.target.value.toLowerCase();

        if (searchValue === '') {
            if (resultList) {
                resultList.style.display = 'none';
                setListboxExpanded(false);
            }
            if (resultGrid) {
                renderGrid(products);
                if (resultsStatus) resultsStatus.textContent = `${products.length} prodotti disponibili.`;
            }
            return;
        }

        const filteredProducts = filterProducts(searchValue);
        if (resultList) {
            renderList(filteredProducts);
            setListboxExpanded(true);
        }
        if (resultGrid) {
            renderGrid(filteredProducts);
            if (resultsStatus) {
                resultsStatus.textContent = filteredProducts.length === 0
                    ? 'Nessun prodotto trovato.'
                    : `${filteredProducts.length} prodotti trovati per "${searchValue}".`;
            }
        }
    });
}

// Chiude il dropdown della search della home quando si clicca fuori
document.addEventListener('click', (e) => {
    if (resultList && resultList.style.display === 'block') {
        if (!searchInput.contains(e.target) && !resultList.contains(e.target)) {
            resultList.style.display = 'none';
            resultList.setAttribute('aria-expanded', 'false');
        }
    }
});
