const cartContainer = document.getElementById('itemsInCart');
const addToCartButton = document.getElementById('addToCartButton');
const totalPriceContainer = document.getElementById('totalCartPrice');
const submitCartButton = document.getElementById('submitCart');
let cartInformation;

// Aggiorna il messaggio per gli screen reader con aria-live
function announceToCart(message) {
    const announcement = document.getElementById('cart-announcement');
    if (announcement) announcement.textContent = message;
}

async function addToCart(productID, quantity) {
    const payload = { id: productID, qty: quantity };
    await sendCartAction('add', payload);
    const productName = document.getElementById('product-title')?.textContent.trim() || 'Prodotto';
    announceToCart(`${productName} aggiunto al carrello (quantità: ${quantity}).`);
    showCartToast(productName, quantity);
}

let cartToastTimer;
function showCartToast(productName, quantity) {
    const toast = document.getElementById('cart-toast');
    const detail = document.getElementById('cart-toast-detail');
    const closeBtn = document.getElementById('cart-toast-close');
    if (!toast || !detail) return;

    const qtyNum = Number(quantity) || 1;
    const qtyText = qtyNum === 1 ? '1 unità' : `${qtyNum} unità`;
    detail.textContent = `${productName} — ${qtyText}`;

    toast.hidden = false;
    requestAnimationFrame(() => toast.classList.add('is-visible'));

    clearTimeout(cartToastTimer);
    cartToastTimer = setTimeout(() => hideCartToast(), 4000);

    if (closeBtn && !closeBtn.dataset.bound) {
        closeBtn.addEventListener('click', hideCartToast);
        closeBtn.dataset.bound = '1';
    }
}

function hideCartToast() {
    const toast = document.getElementById('cart-toast');
    if (!toast) return;
    clearTimeout(cartToastTimer);
    toast.classList.remove('is-visible');
    setTimeout(() => { toast.hidden = true; }, 300);
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
        addToCartButton.addEventListener('click', async () => {
            const loggedIn = await isUserLoggedIn();
            if (!loggedIn) {
                showAuthRequiredDialog();
                return;
            }
            addToCart(productId, quantityInput.value);
        });
    }
});

// verifico la sessione PHP lato server
async function isUserLoggedIn() {
    try {
        const res = await fetch('./php/account/loginCheck.php', { credentials: 'same-origin' });
        const data = await res.json();
        return data.logged_in === true;
    } catch (e) {
        return false;
    }
}

// costruisco il dialog la prima volta che serve
function ensureAuthRequiredDialog() {
    if (document.getElementById('auth-required-dialog')) return;

    const dlg = document.createElement('div');
    dlg.id = 'auth-required-dialog';
    dlg.className = 'auth-required-dialog is-hidden';
    dlg.setAttribute('role', 'dialog');
    dlg.setAttribute('aria-modal', 'true');
    dlg.setAttribute('aria-labelledby', 'auth-required-title');
    dlg.setAttribute('aria-describedby', 'auth-required-desc');
    // pagina corrente da restituire dopo login/register
    const returnUrl = encodeURIComponent(window.location.pathname.split('/').pop() + window.location.search);

    dlg.innerHTML = `
        <div class="auth-required-backdrop" data-close-dialog></div>
        <div class="auth-required-panel" role="document">
            <button type="button" class="auth-required-close" aria-label="Chiudi finestra" data-close-dialog>×</button>
            <h2 id="auth-required-title">Accedi per continuare</h2>
            <p id="auth-required-desc">
                Per aggiungere prodotti al carrello devi avere un account.
                Accedi al tuo profilo o creane uno nuovo per procedere.
            </p>
            <div class="auth-required-actions">
                <a href="./login.html?return=${returnUrl}" class="button">Accedi</a>
                <a href="./register.html?return=${returnUrl}" class="button">Registrati</a>
            </div>
        </div>
    `;
    document.body.appendChild(dlg);

    // chiusura: bottone X, backdrop, ESC
    dlg.querySelectorAll('[data-close-dialog]').forEach(el => {
        el.addEventListener('click', hideAuthRequiredDialog);
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !dlg.classList.contains('is-hidden')) {
            hideAuthRequiredDialog();
        }
    });
}

let lastFocusedBeforeDialog = null;

function showAuthRequiredDialog() {
    ensureAuthRequiredDialog();
    const dlg = document.getElementById('auth-required-dialog');
    if (!dlg) return;
    lastFocusedBeforeDialog = document.activeElement;
    dlg.classList.remove('is-hidden');
    // sposto il focus sul primo link cosi screen reader e tastiera capiscono
    const firstAction = dlg.querySelector('.auth-required-actions a');
    if (firstAction) firstAction.focus();
}

function hideAuthRequiredDialog() {
    const dlg = document.getElementById('auth-required-dialog');
    if (!dlg) return;
    dlg.classList.add('is-hidden');
    if (lastFocusedBeforeDialog && typeof lastFocusedBeforeDialog.focus === 'function') {
        lastFocusedBeforeDialog.focus();
    }
}
