/**
 * Mostra 4 prodotti casuali nella sezione "Home Decor Products" della home.
 * Ogni card linka direttamente a item.html?id=<id> per aprire la scheda del prodotto.
 */

const HOME_PRODUCTS_COUNT = 4;

function escapeHtml(s) {
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function pickRandomProducts(products, count) {
    // Fisher-Yates shuffle parziale per evitare il bias di sort(() => Math.random() - 0.5)
    const arr = [...products];
    const n = Math.min(count, arr.length);
    for (let i = 0; i < n; i++) {
        const j = i + Math.floor(Math.random() * (arr.length - i));
        [arr[i], arr[j]] = [arr[j], arr[i]];
    }
    return arr.slice(0, n);
}

async function renderHomeProducts() {
    const container = document.querySelector('.row-layout');
    if (!container) return;

    let products;
    try {
        products = await fetchProducts();
    } catch (err) {
        console.error('Errore nel caricamento dei prodotti per la home:', err);
        return;
    }
    if (!Array.isArray(products) || products.length === 0) {
        container.innerHTML = '<p role="status">Nessun prodotto disponibile al momento.</p>';
        return;
    }

    const selected = pickRandomProducts(products, HOME_PRODUCTS_COUNT);

    container.innerHTML = '';
    selected.forEach(p => {
        const safeName = escapeHtml(p.productName);
        const safeId   = encodeURIComponent(p.id);
        const safeImg  = escapeHtml(p.imageUrl);

        const card = document.createElement('div');
        card.className = 'card';
        card.innerHTML = `
            <img src="assets/img/${safeImg}" alt="Foto del prodotto ${safeName}" loading="lazy" decoding="async">
            <div class="card-content">
                <h3>${safeName}</h3>
                <a href="item.html?id=${safeId}" class="button">
                    Scopri<span class="sr-only"> ${safeName}</span>
                </a>
            </div>
        `;
        container.appendChild(card);
    });
}

document.addEventListener('DOMContentLoaded', renderHomeProducts);
