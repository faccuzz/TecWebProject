async function loadProduct() {
    const params = new URLSearchParams(window.location.search);
    
    const productId = params.get('id');

    if (productId) {
        try {
            const risultato = await fetchProducts('id', productId);

            if (risultato && risultato.length > 0) {
                const prodotto = risultato[0]; 
                
                populatePage(prodotto);
            } else {
                document.querySelector('main').innerHTML = "<h1>Product not found!</h1>";
            }
        } catch (error) {
            console.error("Something went wrong fetching the product:", error);
        }
    } else {
        console.log("No ID given");
    }
}

function populatePage(product){
    const imageSlot = document.querySelector('.detail-image-wrapper img');
    const titleSlot = document.querySelector('.detail-info .section-title');
    const descriptionSlot = document.querySelector('.detail-info .section-text');
    const priceSlot = document.querySelector('.detail-price');

    if (titleSlot) {
        titleSlot.textContent = product.productName;
    }
    if (descriptionSlot) {
        descriptionSlot.textContent = product.description;
    }

    if (priceSlot) {
        const formattedPrice = parseFloat(product.price).toFixed(2);
        priceSlot.textContent = `€ ${formattedPrice.replace('.', ',')}`;
    }

    if (imageSlot) {
        const nomeImmagine = product.productName.replace(/\s+/g, '').toLowerCase();
        //TODO: salvataggio immagini dinamico
        imageSlot.src = `./assets/img/gin-jute.webp`;
        imageSlot.alt = `Foto del prodotto ${product.name}`;
    }
}

loadProduct();