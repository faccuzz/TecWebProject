const cartContainer = document.getElementById('itemsInCart');
const addToCartButton = document.getElementById('addToCartButton');
const totalPriceContainer = document.getElementById('totalCartPrice');
let removeFromCartButtons;
let cartInformation;

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

async function addToCart(productID, quantity) {
    const payload = {
        id: productID,
        qty: quantity
    };

    const updatedCart = await sendCartAction('add', payload);
    alert("Prodotto aggiunto al carrello!");
}

async function updateQuantity(id, newQty) {
    const updatedCart = await sendCartAction('update', { id: id, qty: newQty });
    await updateCartInformation();
    renderCartPage();
}

async function removeFromCart(id) {
    const updatedCart = await sendCartAction('remove', { id: id });
    await updateCartInformation();
    renderCartPage();
}

async function clearCart() {
    if (confirm("Sei sicuro di voler svuotare tutto il carrello?")) {
        await sendCartAction('clear');
        await updateCartInformation();
        renderCartPage();
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
    cartInformation.forEach(product => {
        product['quantity'] = currentCart[product['id']];
    });
}

async function initCartPage() {
    await updateCartInformation();

    renderCartPage(cartInformation);
}

function activateRemovalButton(){
    if(!cartInformation || cartInformation.length === 0) return;

    cartInformation.forEach(product => {
        removalButton = document.getElementById(`btn-${product.id}`);
        removalButton.addEventListener('click', () => {
            removeFromCart(product.id);
        })
    })
}

function activateQuantityInput(){
    if(!cartInformation || cartInformation.length === 0) return;

    cartInformation.forEach(product => {
        quantityButton = document.getElementById(`qty-${product.id}`);
        quantityButton.addEventListener('change', (event) => {
            const newQuantity = parseInt(event.target.value);

            if(newQuantity >= 1) updateQuantity(product.id, newQuantity);
            else {
                event.target.value = 1;
                updateQuantity(product.id, 1);
            }
        })
    })
}

function updateTotalCartPrice(){
    let totalPrice = 0;

    cartInformation.forEach(product => {
        totalPrice += product.price * product.quantity;
    })

    totalPriceContainer.innerHTML = `<p>Total price: ${totalPrice}$</p>`
}

function renderCartPage() {
    cartContainer.innerHTML = '';

    if (cartInformation.length === 0) {
        cartContainer.innerHTML = '<p>Il tuo carrello è vuoto.</p>';
        return;
    }

    cartInformation.forEach(product => {
        const cartRow = document.createElement('div');
        cartRow.className = 'cartRow';

        cartRow.innerHTML = `
            
            <img class="productCartImage" src="assets/img/${product.imageUrl}" alt="Vodka Industrial Lamp">
            <a href="item.html?id=${product.id}" class="productCartName">${product.productName}</a>
            <div class="productQuantity">
                <label for="qty-${product.id}">Quantity</label>
                <input class="productCartQuantityInput" type="number" min="1" name="itemQuantity" id="qty-${product.id}" value="${product.quantity}" />
            </div>
            <button class="productCartRemovalButton button" id=btn-${product.id} type="button">Remove product</button>            
        `;

        cartContainer.appendChild(cartRow);
    });

    activateRemovalButton();
    activateQuantityInput();
    updateTotalCartPrice();

    /*
    if (totalContainer) {
        totalContainer.textContent = `€ ${totalPrice.toFixed(2).replace('.', ',')}`;
    }
        */
}

document.addEventListener('DOMContentLoaded', () => {
    if (cartContainer) {
        initCartPage();
    }

    if (addToCartButton) {
        const params = new URLSearchParams(window.location.search);
        const productId = params.get('id');

        const quantityInput = document.getElementById('qty');

        addToCartButton.addEventListener('click', () => addToCart(productId, quantityInput.value))
    }
});