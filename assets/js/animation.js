const searchBox = document.querySelector('.home-search-box');
const searchButton = document.getElementById('home-search-button');
const searchBar = document.querySelector('.home-search-bar');

if (searchBox && searchButton && searchBar) {
    searchButton.addEventListener('click', (e) => {
        e.preventDefault();
        const isOpen = searchBox.classList.contains('active');
        if (isOpen) {
            searchBox.classList.remove('active');
            searchButton.setAttribute('aria-expanded', 'false');
        } else {
            searchBox.classList.add('active');
            searchButton.setAttribute('aria-expanded', 'true');
            searchBar.focus();
        }
    });

    document.addEventListener('click', (e) => {
        if (!searchBox.contains(e.target) && e.target !== searchButton) {
            if (searchBox.classList.contains('active')) {
                searchBox.classList.remove('active');
                searchButton.setAttribute('aria-expanded', 'false');
                searchBar.value = '';
            }
        }
    });

    searchBar.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            searchBox.classList.remove('active');
            searchButton.setAttribute('aria-expanded', 'false');
            searchBar.value = '';
            searchButton.focus();
        }
    });
}
