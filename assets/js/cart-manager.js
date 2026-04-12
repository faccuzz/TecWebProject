const cartContainer = document.getElementById('itemsInCart');
const addToCartButton = document.getElementById('addToCartButton');

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
    //ID e quantità
    const currentCart = await sendCartAction('get');

    const currentIDlist = Object.keys(currentCart);

    if (currentIDlist.length === 0) {
        renderCartPage([]);
        return;
    }

    const IDstring = currentIDlist.join(',');

    //Prodotto completo + quantità
    const realCartProducts = await fetchProducts('id', IDstring);
    realCartProducts.forEach(product => {
        product['quantity'] = currentCart[product['id']]
    })

    renderCartPage(realCartProducts);
}

function renderCartPage(cartItems) {
    //const totalContainer = document.getElementById('cart-total-price');

    cartContainer.innerHTML = '';
    //let totalPrice = 0;

    if (cartItems.length === 0) {
        cartContainer.innerHTML = '<p>Il tuo carrello è vuoto.</p>';
        //if (totalContainer) totalContainer.textContent = '€ 0,00';
        return;
    }

    cartItems.forEach(item => {
        /*
        const itemTotal = (item.price * item.qty);
        totalPrice += itemTotal;
        */

        const cartRow = document.createElement('div');
        cartRow.className = 'product';

        cartRow.innerHTML = `
            
              <img class="itemImages" src="assets/img/vodka-industrial.webp" alt="Vodka Industrial Lamp">
                <a href="#" class="itemDescription">${item.productName}</a>
                <form class="itemControls" action="RemoveFromCart">
                  <button class="button itemControlsRemove" type="submit">Remove product</button>
                  <label class="itemControlsLabel" for="itemQuantity1">Quantity</label>
                  <input class="itemControlsCount" type="number" min="1" name="itemQuantity" id="itemQuantity1" value="${item.quantity}" />
                </form>
            
        `;

        cartContainer.appendChild(cartRow);
    });

    /*
    if (totalContainer) {
        totalContainer.textContent = `€ ${totalPrice.toFixed(2).replace('.', ',')}`;
    }
        */
}

document.addEventListener('DOMContentLoaded', () => {
    if(cartContainer) initCartPage();
    
    if(addToCartButton) {
        const params = new URLSearchParams(window.location.search);
        const productId = params.get('id');

        const quantityInput = document.getElementById('qty');
        
        addToCartButton.addEventListener('click', () => addToCart(productId, quantityInput.value))
    }

    /*
    const btnClear = document.getElementById('clear-cart-btn');
    if (btnClear) {
        btnClear.addEventListener('click', clearCart);
    }
        */
});