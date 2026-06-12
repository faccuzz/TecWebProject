const cartContainer = document.getElementById('itemsInCart');
const addToCartButton = document.getElementById('addToCartButton');
const totalPriceContainer = document.getElementById('totalCartPrice');
const submitCartButton = document.getElementById('submitCart');
let cartInformation;

// aggiorna il messaggio per gli screen reader (aria-live)
function announceToCart(message) {
    const announcement = document.getElementById('cart-announcement');
    if (announcement) announcement.textContent = message;
}

async function addToCart(productID, quantity) {
    const payload = { id: productID, qty: quantity };
    await sendCartAction('add', payload);
    announceToCart(`Prodotto aggiunto al carrello (quantità: ${quantity}).`);
}

async function updateQuantity(id, newQty) {
    await sendCartAction('update', { id, qty: newQty });
    await updateCartInformation();
    renderCartPage();
}

async function removeFromCart(id, productName) {
    await sendCartAction('remove', { id });
    await updateCartInformation();
    renderCartPage();
    announceToCart(`${productName || 'Prodotto'} rimosso dal carrello.`);
}

async function clearCart() {
    if (confirm("Sei sicuro di voler svuotare tutto il carrello?")) {
        await sendCartAction('clear');
        await updateCartInformation();
        renderCartPage();
        announceToCart('Carrello svuotato.');
    }
}

async function updateCartInformation() {
    const currentCart = await sendCartAction('get');
    const IDstring = Object.keys(currentCart).join(',');

    if (!IDstring) {
        cartInformation = [];
        return;
    }

    cartInformation = await fetchProducts('id', IDstring);
    if (!cartInformation) cartInformation = [];
    cartInformation.forEach(product => {
        product['quantity'] = currentCart[product['id']];
    });
}

async function initCartPage() {
    await updateCartInformation();
    renderCartPage();
}

function activateRemovalButton() {
    if (!cartInformation || cartInformation.length === 0) return;

    cartInformation.forEach(product => {
        const removalButton = document.getElementById(`btn-${product.id}`);
        if (!removalButton) return;
        removalButton.addEventListener('click', () => {
            removeFromCart(product.id, product.productName);
        });
    });
}

function activateQuantityInput() {
    if (!cartInformation || cartInformation.length === 0) return;

    cartInformation.forEach(product => {
        const quantityInput = document.getElementById(`qty-${product.id}`);
        if (!quantityInput) return;
        quantityInput.addEventListener('change', (event) => {
            const newQuantity = parseInt(event.target.value);

            if (newQuantity >= 1) {
                updateQuantity(product.id, newQuantity);
            } else {
                event.target.value = 1;
                updateQuantity(product.id, 1);
            }
        });
    });
}

function updateTotalCartPrice() {
    let totalPrice = 0;
    cartInformation.forEach(product => {
        totalPrice += product.price * product.quantity;
    });
    if (totalPriceContainer) {
        totalPriceContainer.innerHTML = `<p>Totale: <strong>€ ${totalPrice.toFixed(2).replace('.', ',')}</strong></p>`;
    }
}

function renderCartPage() {
    if (!cartContainer) return;
    cartContainer.innerHTML = '';

    if (!cartInformation || cartInformation.length === 0) {
        cartContainer.innerHTML = '<p role="status">Il tuo carrello è vuoto.</p>';
        if (totalPriceContainer) totalPriceContainer.innerHTML = '';
        announceToCart('Il carrello è vuoto.');
        return;
    }

    cartInformation.forEach(product => {
        const cartRow = document.createElement('article');
        cartRow.className = 'cartRow';
        cartRow.setAttribute('aria-label', `Prodotto: ${product.productName}`);

        const safeName = product.productName.replace(/"/g, '&quot;');

        cartRow.innerHTML = `
            <img class="productCartImage" src="assets/img/${product.imageUrl}" alt="${safeName}" loading="lazy" decoding="async">
            <a href="item.html?id=${product.id}" class="productCartName">${safeName}</a>
            <div class="productQuantity">
                <label for="qty-${product.id}">Quantità</label>
                <input class="productCartQuantityInput" type="number" min="1" name="itemQuantity" id="qty-${product.id}" value="${product.quantity}" aria-label="Quantità di ${safeName}" />
            </div>
            <button class="productCartRemovalButton button" id="btn-${product.id}" type="button" aria-label="Rimuovi ${safeName} dal carrello">Rimuovi</button>
        `;

        cartContainer.appendChild(cartRow);
    });

    activateRemovalButton();
    activateQuantityInput();
    updateTotalCartPrice();

    const total = cartInformation.reduce((sum, p) => sum + p.price * p.quantity, 0);
    announceToCart(`Carrello aggiornato: ${cartInformation.length} articoli, totale € ${total.toFixed(2).replace('.', ',')}.`);
}

document.addEventListener('DOMContentLoaded', () => {
    if (cartContainer) initCartPage();

    if (submitCartButton) {
        submitCartButton.addEventListener('click', (e) => {
            e.preventDefault();
            window.location.href = "./checkout.html";
        });
    }

    if (addToCartButton) {
        const params = new URLSearchParams(window.location.search);
        const productId = params.get('id');
        const quantityInput = document.getElementById('qty');
        addToCartButton.addEventListener('click', () => addToCart(productId, quantityInput.value));
    }
});
