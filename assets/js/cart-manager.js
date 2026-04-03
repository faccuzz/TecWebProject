async function sendCartAction(action, payload = {}) {
    try {
        const response = await fetch('./php/cartManager.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ action: action, ...payload })
        });
        const cartData = await response.json();
        return cartData;
    } catch (error) {
        console.error("Errore di comunicazione col carrello:", error);
        return [];
    }
}

async function addToCart(product, quantity) {
    const payload = {
        id: product.id,
        qty: quantity
    };

    const updatedCart = await sendCartAction('add', payload);
    alert("Prodotto aggiunto al carrello!");
}

async function updateQuantity(id, newQty) {
    const updatedCart = await sendCartAction('update', { id: id, qty: newQty });
    renderCartPage(updatedCart);
}

async function removeFromCart(id) {
    const updatedCart = await sendCartAction('remove', { id: id });
    renderCartPage(updatedCart);
}

async function clearCart() {
    if (confirm("Sei sicuro di voler svuotare tutto il carrello?")) {
        const updatedCart = await sendCartAction('clear');
        renderCartPage(updatedCart);
    }
}

async function initCartPage() {
    const cartContainer = document.getElementById('cart-items-container');
    if (!cartContainer) return;

    const currentCart = await sendCartAction('get');
    renderCartPage(currentCart);
}

function renderCartPage(cartItems) {
    const cartContainer = document.getElementById('cart-items-container');
    const totalContainer = document.getElementById('cart-total-price');

    if (!cartContainer) return;

    cartContainer.innerHTML = '';
    let totalPrice = 0;

    if (cartItems.length === 0) {
        cartContainer.innerHTML = '<p>Il tuo carrello è vuoto.</p>';
        //if (totalContainer) totalContainer.textContent = '€ 0,00';
        return;
    }

    cartItems.forEach(item => {
        const itemTotal = (item.price * item.qty);
        totalPrice += itemTotal;

        const cartRow = document.createElement('div');
        cartRow.className = 'cart-item-row';

        cartRow.innerHTML = `
            <div class="cart-item-details">
                <img src="${item.image}" alt="${item.name}" width="50">
                <strong>${item.name}</strong>
            </div>
            <div class="cart-item-price">€ ${parseFloat(item.price).toFixed(2).replace('.', ',')}</div>
            
            <div class="cart-item-actions">
                <button onclick="updateQuantity('${item.id}', ${item.qty - 1})">-</button>
                <input type="number" value="${item.qty}" min="1" readonly style="width: 40px; text-align: center;">
                <button onclick="updateQuantity('${item.id}', ${item.qty + 1})">+</button>
            </div>
            
            <div class="cart-item-total">€ ${itemTotal.toFixed(2).replace('.', ',')}</div>
            
            <button class="remove-btn" onclick="removeFromCart('${item.id}')">
                <i class="fas fa-trash"></i>
            </button>
        `;

        cartContainer.appendChild(cartRow);
    });

    if (totalContainer) {
        totalContainer.textContent = `€ ${totalPrice.toFixed(2).replace('.', ',')}`;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initCartPage();

    const btnClear = document.getElementById('clear-cart-btn');
    if (btnClear) {
        btnClear.addEventListener('click', clearCart);
    }
});