async function showProducts() {
    const productsList = document.getElementById('productsList');
    const response = await fetch('./php/adminPage.php');
        
    const products = await response.json();

    if (products.length === 0) {
        productsList.innerHTML = '<li>There are no products available.</li>';
        return;
    }

    productsList.innerHTML = '';

    products.forEach(p => {
        const card = `
            <li class="card"> 
                <h2>${p.productName}</h2> 
                <div class="product"> 
                    <p>${p.description}</p>
                    <img src="assets/img/${p.productName.replace(/\s+/g, '').toLowerCase()}.jpg" style="width:100%; height:320px;object-fit: contain;">
                </div> 
                <p>Price: <strong>${p.price}€</strong></p> 
                <button onclick="deleteProduct('${p.id}')">Delete</button>
                <button onclick="modifyProduct('${p.id}')">Modify</button>
            </li>`;
            productsList.innerHTML += card;
        });
    
}

async function deleteProduct(id) {
    window.location.href = `php/deleteProduct.php?id=${id}`;
}

document.addEventListener('DOMContentLoaded', showProducts);
