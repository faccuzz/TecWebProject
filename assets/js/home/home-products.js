const HOME_PRODUCTS_COUNT = 4;

function pickRandomProducts(products, count) {
    const arr = products.slice();
    arr.sort(function () { return Math.random() - 0.5; });
    return arr.slice(0, count);
}

async function renderHomeProducts() {
    const container = document.querySelector('.row-layout');
    if (!container) return;

    let products;
    try {
        products = await fetchProducts();
    } catch (err) {
        console.error('Errore caricamento prodotti home:', err);
        return;
    }
    if (!products || products.length === 0) {
        container.innerHTML = '<p role="status">Nessun prodotto disponibile al momento.</p>';
        return;
    }

    const selected = pickRandomProducts(products, HOME_PRODUCTS_COUNT);

    container.innerHTML = '';
    selected.forEach(p => {
        const safeName = p.productName.replace(/</g, '&lt;');

        const card = document.createElement('div');
        card.className = 'card';
        card.innerHTML = `
            <img src="assets/img/${p.imageUrl}" alt="Foto del prodotto ${safeName}" loading="lazy">
            <div class="card-content">
                <h3>${safeName}</h3>
                <a href="item.html?id=${p.id}" class="button">
                    Scopri<span class="sr-only"> ${safeName}</span>
                </a>
            </div>
        `;
        container.appendChild(card);
    });
}

document.addEventListener('DOMContentLoaded', renderHomeProducts);
