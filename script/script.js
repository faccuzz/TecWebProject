async function showProducts() {
    const productsList = document.getElementById('productsList');
    const response = await fetch('./php/adminPage.php');
        
    const prodotti = await response.json();
    console.log("3. Dati ricevuti:", prodotti);

    if (prodotti.length === 0) {
        productsList.innerHTML = '<li>Nessun prodotto trovato nel database.</li>';
        return;
    }

    productsList.innerHTML = '';

    prodotti.forEach(p => {
        console.log("4. Sto creando la card per:", p.productName);
        const card = `
            <li class="card"> 
                <h2>${p.productName}</h2> 
                <div class="product"> 
                    <p>${p.description}</p>
                    <img src="assets/img/${p.productName.replace(/\s+/g, '').toLowerCase()}.jpg" style="width:100px">
                </div> 
                <p>Prezzo: <strong>${p.price}€</strong></p> 
            </li>`;
            productsList.innerHTML += card;
        });
}

document.addEventListener('DOMContentLoaded', showProducts);