async function loadProduct() {
    const params = new URLSearchParams(window.location.search);
    const productId = params.get('id');

    if (!productId) {
        console.log('Nessun ID prodotto fornito.');
        return;
    }

    try {
        const risultato = await fetchProducts('id', productId);

        if (risultato && risultato.length > 0) {
            populatePage(risultato[0]);
        } else {
            const main = document.querySelector('main');
            if (main) {
                main.innerHTML = '<h1 role="alert">Prodotto non trovato</h1><p>L\'articolo richiesto non è disponibile. <a href="catalog.html">Torna al catalogo</a>.</p>';
            }
        }
    } catch (error) {
        console.error('Errore nel caricamento del prodotto:', error);
    }
}

function populatePage(product) {
    const imageSlot = document.querySelector('.detail-image-wrapper img');
    const titleSlot = document.querySelector('.detail-info .section-title');
    const descriptionSlot = document.querySelector('.detail-info .section-text');
    const priceSlot = document.querySelector('.detail-price');

    if (titleSlot) titleSlot.textContent = product.productName;
    if (descriptionSlot) descriptionSlot.textContent = product.description;

    if (priceSlot) {
        const formattedPrice = parseFloat(product.price).toFixed(2);
        priceSlot.innerHTML = `<span class="sr-only">Prezzo: </span>€ ${formattedPrice.replace('.', ',')}`;
    }

    if (imageSlot) {
        imageSlot.src = `./assets/img/${product.imageUrl}`;
        imageSlot.alt = `Foto del prodotto ${product.productName}`;
    }

    if (product.productName) {
        document.title = `Lumen Spirits — ${product.productName}`;
    }

    populateDetailTable(product);
}

// Riempe la tabella con le specifiche del prodotto. Se un campo è vuoto
// salto la riga, cosi la tabella resta pulita anche se l'admin non lo compila.
function populateDetailTable(product) {
    const tbody = document.getElementById('detail-table-body');
    if (!tbody) return;

    const rows = [
        { label: 'Materiale',     value: product.material },
        { label: 'Autore',        value: product.author },
        { label: 'Dimensioni',    value: product.dimensions },
        { label: 'Peso',          value: product.weight },
        { label: 'Voltaggio',     value: product.voltage },
        {
            label: 'Disponibilità',
            value: Number(product.inStock) === 1 ? 'Disponibile' : 'Esaurito'
        },
    ];

    tbody.innerHTML = '';
    rows.forEach(({ label, value }) => {
        if (value === null || value === undefined || String(value).trim() === '') return;
        const tr = document.createElement('tr');
        const th = document.createElement('th');
        th.setAttribute('scope', 'row');
        th.textContent = label;
        const td = document.createElement('td');
        td.textContent = value;
        tr.appendChild(th);
        tr.appendChild(td);
        tbody.appendChild(tr);
    });
}

loadProduct();
