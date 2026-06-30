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

function validateForm() {
    const fields = [
        {
            input: document.getElementById('email-input'),
            error: document.getElementById('checkout-email-error'),
            msg: 'L\'email è obbligatoria.',
            regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            regexMsg: 'Indirizzo non valido (es. nome@esempio.com).'
        },
        {
            input: document.getElementById('name-input'),
            error: document.getElementById('checkout-name-error'),
            msg: 'Il nome è obbligatorio.'
        },
        {
            input: document.getElementById('surname-input'),
            error: document.getElementById('checkout-surname-error'),
            msg: 'Il cognome è obbligatorio.'
        },
        {
            input: document.getElementById('address'),
            error: document.getElementById('checkout-address-error'),
            msg: 'L\'indirizzo è obbligatorio.'
        },
    ];

    let isValid = true;
    fields.forEach(({ input, error, msg, regex, regexMsg }) => {
        if (!input || !error) return;
        const value = input.value.trim();
        let fieldError = '';
        if (!value) fieldError = msg;
        else if (regex && !regex.test(value)) fieldError = regexMsg;

        if (fieldError) {
            error.textContent = fieldError;
            error.classList.add('active');
            input.setAttribute('aria-invalid', 'true');
            isValid = false;
        } else {
            error.textContent = '';
            error.classList.remove('active');
            input.removeAttribute('aria-invalid');
        }
    });

    return isValid;
}

async function clearCart() {
    try {
        if (typeof sendCartAction === 'function') {
            await sendCartAction('clear');
        } else {
            await fetch('./php/cart/cartManager.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'clear' })
            });
        }
    } catch (error) {
        console.error("Errore durante lo svuotamento del carrello:", error);
    }

    if (previewContainer) {
        previewContainer.innerHTML = '<p>Nessun prodotto nel carrello.</p>';
    }
    if (subtotalContainer) {
        subtotalContainer.textContent = formatEuro(0);
    }
    if (totalContainer) {
        totalContainer.innerHTML = `
            <span>Totale</span>
            <span class="total-price">${formatEuro(0)}</span>
        `;
    }
}

const checkoutForm = document.getElementById('Checkout-form');
if (checkoutForm) {
    checkoutForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (validateForm()) {
            alert('Pagamento effettuato');
            checkoutForm.reset();
            await clearCart();
            window.location.href = 'index.html';
        }
    });
}

if (previewContainer) {
    renderPreview();
    renderCheckoutPrice();
}
