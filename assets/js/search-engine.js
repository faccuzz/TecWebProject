const products = [
    { name: "Lampada Gin Bombay", category: "Gin", price: "45€", link: "prodotto-gin.html" },
    { name: "Lampada Jack Daniels", category: "Whiskey", price: "50€", link: "prodotto-jack.html" },
    { name: "Centrotavola Belvedere", category: "Vodka", price: "65€", link: "prodotto-vodka.html" },
    { name: "Lampada Vino Rosso", category: "Vino", price: "35€", link: "prodotto-vino.html" },
    { name: "Kit Fai da Te", category: "Accessori", price: "20€", link: "prodotto-kit.html" }
];

const currentPage = window.location.pathname;

const searchInput = document.getElementById('search-input');
const resultList = document.querySelector('.result-list');
const resultGrid = document.querySelector('.result-grid');

function filterProducts(input) {
    const filteredProducts = products.filter(product => {
        return product.name.toLowerCase().includes(input) ||
            product.category.toLowerCase().includes(input);
    });
    return filteredProducts;
}

function renderProduct(htmlContent, classes) {
    const resultItem = document.createElement('div');
    resultItem.innerHTML = htmlContent;
    resultItem.className = classes;
    return resultItem;
}

function renderList(items) {
    resultList.innerHTML = '';
    resultList.style.display = 'block';

    if (items.length === 0) {
        const htmlContent = `
                                        <a href="#"> 
                                            <strong>No products found</strong>
                                        </a>
                                    `;
        const classes = 'result-item'
        const resultItem = renderProduct(htmlContent, classes);
        resultList.append(resultItem);
        return;
    }
    else {
        resultList.style.display = 'block';

        items.slice(0, 5).forEach(product => {
            const htmlContent = `
                                            <a href="${product.link}">
                                                <strong>${product.name}</strong>
                                            </a>
                                        `;
            const classes = 'result-item';
            const resultItem = renderProduct(htmlContent, classes);

            resultList.append(resultItem);
        });
    }
}
function renderGrid(items) {
    /** TODO */
}

if (searchInput) {
    searchInput.addEventListener('input', (e) => {
        const searchValue = e.target.value.toLowerCase();

        if (resultList) {
            if (searchValue === '') {
                resultList.style.display = 'none';
                return;
            }
            else {
                const filteredProducts = filterProducts(searchValue);

                renderList(filteredProducts);
            }
            return;
        }

    });
}

/**
 * Chiude la lista di risultati nella Home se togli il focus dalla SearchBox
 */
document.addEventListener('click', (e) => {
    if (resultList && resultList.style.display === 'block') {
        if (!searchInput.contains(e.target) && !resultList.contains(e.target)) {
            resultList.style.display = 'none';
        }
    }
});