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
                <p>In Stock: ${p.inStock == 1 ? 'Yes' : 'No'}</p>
                <p>Price: <strong>${p.price}€</strong></p> 
                <button onclick="deleteProduct('${p.id}')">Delete</button>
                <button onclick="modifyProduct('${p.id}')">Modify</button>
            </li>`;
            productsList.innerHTML += card;
        });
    
}

async function deleteProduct(id) {
    window.location.href = `./php/deleteProduct.php?id=${id}`;
}

async function modifyProduct(id) {
    window.location.href = `./modifyPage.html?id=${id}`;
}

window.addEventListener('DOMContentLoaded',() => {
    if(window.location.pathname.includes('modifyPage.html')){
        getProdotto();
    }
})

function getProdotto(){
    const id = new URLSearchParams(window.location.search).get('id');
    document.getElementById('id').value = id;
    fetch(`./php/modifyPage.php?id=${id}`)
    .then(res => res.json())
    .then(data => {
        document.getElementById('name').value = data.productName;
        document.getElementById('description').value = data.description;
        document.getElementById('price').value = data.price;
        document.getElementById('img').value = data.imageUrl;
    })
}

document.addEventListener('DOMContentLoaded',() => {
    if(window.location.pathname.includes('optionsPage.html')){
        const menu = document.getElementById('menu-nav');
        const areaContenuto = document.getElementById('contentZone');

        menu.addEventListener('click', (event) =>{
            const elemento = event.target.closest('li');
            const sezione = elemento.getAttribute('sessionOp');
            document.querySelectorAll('#menu-nav li').forEach(li => li.classList.remove('active'));
            elemento.classList.add('active');

            caricaDati(sezione);
        })

        async function caricaDati(sezione) {
            const risposta = await fetch(`./php/optionsPage.php?section=${sezione}`);
            const html = await risposta.text();
            areaContenuto.innerHTML = html;
        }
    }
})

document.addEventListener('DOMContentLoaded', showProducts);
