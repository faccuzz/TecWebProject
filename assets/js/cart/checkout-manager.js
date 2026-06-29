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
        subtotalContainer.innerHTML = formatEuro(total);
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

function validateForm() {
    const email = document.getElementById('email-input');
    const name = document.getElementById('name-input');
    const surname = document.getElementById('surname-input');
    const address = document.getElementById('address');
    const emailError = document.getElementById('checkout-email-error');
    const nameError = document.getElementById('checkout-name-error');
    const surnameError = document.getElementById('checkout-surname-error');
    const addressError = document.getElementById('checkout-address-error');

    // Reset dei messaggi di errore 
    [emailError, nameError, surnameError, addressError].forEach(err => {
        if (err) {
            err.innerHTML = '';
            err.style.color = '#ff0000';     
            err.style.display = 'block';      
            err.style.margin = '5px 0 0 0';   
        }
    });

    let isValid = true;

    //Validazione dei campi
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email || !email.value.trim()) {
        if (emailError) emailError.innerHTML = 'L\'email è obbligatoria.';
        isValid = false;
    } else if (!emailRegex.test(email.value.trim())) {
        if (emailError) emailError.innerHTML = 'Indirizzo non valido, Inserisci una vera mail (es. nome@esempio.com).';
        isValid = false;
    }

    if (!name || !name.value.trim()) {
        if (nameError) nameError.innerHTML = 'Il nome è obbligatorio.';
        isValid = false;
    }

    if (!surname || !surname.value.trim()) {
        if (surnameError) surnameError.innerHTML = 'Il cognome è obbligatorio.';
        isValid = false;
    }

    if (!address || !address.value.trim()) {
        if (addressError) addressError.innerHTML = 'L\'indirizzo è obbligatorio.';
        isValid = false;
    }

    return isValid;
}

async function clearCart() {
    try{
        if (typeof sendCartAction === 'function'){
            await sendCartAction('clear');
        } else {
            await fetch('./php/cart/cartManager.php',{
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'clear'})
            });
        }
    } catch (error){
        console.error("Errore durante lo svuotamento del carrello:", error);
    }

    if (previewContainer){
        previewContainer.innerHTML = '<p>Nessun prodotto nel carrello.</p>';
    }
    if (subtotalContainer){
        subtotalContainer.innerHTML = formatEuro(0);
    }
    if (totalContainer){
        totalContainer.innerHTML = `
            <span>Totale</span>
            <span class="total-price">${formatEuro(0)}</span>
        `;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('Checkout-form');
    if (form){
        form.addEventListener('submit', async function(event){
            event.preventDefault(); 
            if (validateForm()){
                alert('Pagamento effettuato');
                form.reset(); 
                await clearCart(); 
                window.location.href = 'index.html';
            }
        });
    }

    if (previewContainer){
        renderPreview();
        renderCheckoutPrice();
    }
});
