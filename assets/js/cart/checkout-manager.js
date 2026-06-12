const previewContainer = document.getElementById('previewProducts');
const totalContainer = document.getElementById('checkout-total');
const subtotalContainer = document.getElementById('subtotal-amount');

function formatEuro(value) {
    return `€ ${Number(value).toFixed(2).replace('.', ',')}`;
}

async function renderCheckoutPrice() {
    let total = 0;
    try {
        const response = await fetch('./php/cart/getCartPrice.php');
        if (response.ok) {
            const data = await response.json();
            total = Number(data && data.totalPrice) || 0;
        }
    } catch (error) {
        console.error("Errore nel calcolo del totale:", error);
    }

    if (subtotalContainer) {
        subtotalContainer.textContent = formatEuro(total);
    }
    if (totalContainer) {
        totalContainer.innerHTML = `
            <span>Totale</span>
            <span class="total-price">${formatEuro(total)}</span>
        `;
    }
}

async function renderPreview() {
    if (!previewContainer) return;
    try {
        const currentCart = await sendCartAction('get');
        const IDstring = Object.keys(currentCart).join(',');

        previewContainer.innerHTML = '';

        if (!IDstring) {
            previewContainer.innerHTML = '<p>Nessun prodotto nel carrello.</p>';
            return;
        }

        const cartProducts = await fetchProducts('id', IDstring);
        if (!cartProducts) return;

        cartProducts.forEach(product => {
            const previewRow = document.createElement('article');
            previewRow.className = 'previewRow';
            previewRow.setAttribute('aria-label', `Prodotto: ${product.productName}`);

            const safeName = product.productName.replace(/"/g, '&quot;');
            previewRow.innerHTML = `
                <img src="assets/img/${product.imageUrl}" alt="${safeName}" loading="lazy" decoding="async">
                <h3>${safeName}</h3>
            `;
            previewContainer.appendChild(previewRow);
        });
    } catch (error) {
        console.error("Errore nel rendering della preview:", error);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (previewContainer) {
        renderPreview();
        renderCheckoutPrice();
    }
});
