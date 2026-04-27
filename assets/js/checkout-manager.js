const previewContainer = document.getElementById('previewProducts');
const totalContainer = document.getElementById('checkout-total');

async function renderCheckoutPrice(){
    const response = await fetch('./php/getCartPrice.php');
    const data = await response.json();

    totalContainer.innerHTML = `
        <span>Total</span>
        <span class="total-price">${data.totalPrice} €</span>
    `;
}

async function renderPreview(){
    try {
        const currentCart = await sendCartAction('get');
        const IDstring = Object.keys(currentCart).join(',');

        previewContainer.innerHTML = '';

        const cartProducts = await fetchProducts('id', IDstring);

        cartProducts.forEach(product => {
            const previewRow = document.createElement('div');
            previewRow.className = 'previewRow';

            previewRow.innerHTML = `
                <img src="assets/img/${product.imageUrl}" alt="${product.productName}">
                <h3>${product.productName}</h3>
            `;

            previewContainer.appendChild(previewRow);
        });
    } catch (error) {
        console.error("Errore nel rendering della preview: ", error);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if(previewContainer) {
        renderPreview();
        renderCheckoutPrice();
    }
})