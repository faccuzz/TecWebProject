let products = [];

const PAGE_SIZE = 9;
let visibleProducts = [];
let displayedCount = 0;

const searchInput = document.getElementById('search-input');
const resultList = document.querySelector('.result-list');
const resultGrid = document.querySelector('.result-grid');
const resultsStatus = document.getElementById('catalog-results-status');
const loadMoreBtn = document.getElementById('load-more-button');

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
    resultList.classList.remove('is-hidden');

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

    //Imposto role="listbox" solo quando ho dei risultati da mostrare
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

function buildCard(product, idx) {
    const safeName = product.productName.replace(/</g, '&lt;');
    const safeDesc = (product.description || '').replace(/</g, '&lt;');
    const altText = `Foto del prodotto ${safeName}`;
    //Le prime 3 immagini si vedono subito, le altre le carico man mano
    const loadingAttr = idx < 3 ? 'eager' : 'lazy';
    const htmlContent = `
        <img src="./assets/img/${product.imageUrl}" alt="${altText}" loading="${loadingAttr}" decoding="async">
        <div class="card-content">
            <h2>${safeName}</h2>
            <p>${safeDesc}</p>
            <a href="item.html?id=${product.id}" class="button" aria-label="Scopri ${safeName}">Scopri</a>
        </div>
    `;
    return renderProduct(htmlContent, 'card', { role: 'listitem' });
}

function updateLoadMoreButton() {
    if (!loadMoreBtn) return;
    const hasMore = displayedCount < visibleProducts.length;
    loadMoreBtn.hidden = !hasMore;
    if (hasMore) {
        const remaining = visibleProducts.length - displayedCount;
        loadMoreBtn.setAttribute(
            'aria-label',
            `Carica altri prodotti (${remaining} rimanenti)`
        );
    }
}

function appendNextPage() {
    if (!resultGrid) return;
    const start = displayedCount;
    const end = Math.min(start + PAGE_SIZE, visibleProducts.length);
    for (let i = start; i < end; i++) {
        resultGrid.append(buildCard(visibleProducts[i], i));
    }
    displayedCount = end;
    updateLoadMoreButton();
    if (resultsStatus) {
        resultsStatus.textContent =
            `Mostrati ${displayedCount} di ${visibleProducts.length} prodotti.`;
    }
}

function renderGrid(items) {
    if (!resultGrid) return;
    resultGrid.innerHTML = '';
    resultGrid.classList.remove('is-hidden');
    visibleProducts = items;
    displayedCount = 0;

    if (items.length === 0) {
        resultGrid.removeAttribute('role');
        const empty = document.createElement('p');
        empty.setAttribute('role', 'status');
        empty.textContent = 'Nessun prodotto trovato.';
        resultGrid.append(empty);
        if (loadMoreBtn) loadMoreBtn.hidden = true;
        return;
    }

    resultGrid.setAttribute('role', 'list');
    appendNextPage();
}

if (loadMoreBtn) {
    loadMoreBtn.addEventListener('click', () => {
        appendNextPage();
    });
}

init();

if (searchInput) {
    function setListboxExpanded(expanded) {
        if (resultList) {
            resultList.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        }
    }

    searchInput.addEventListener('input', (e) => {
        const searchValue = e.target.value.toLowerCase();

        if (searchValue === '') {
            if (resultList) {
                resultList.classList.add('is-hidden');
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

//Chiude la tendina dei risultati se l'utente clicca fuori
document.addEventListener('click', (e) => {
    if (resultList && !resultList.classList.contains('is-hidden')) {
        if (!searchInput.contains(e.target) && !resultList.contains(e.target)) {
            resultList.classList.add('is-hidden');
            resultList.setAttribute('aria-expanded', 'false');
        }
    }
});
