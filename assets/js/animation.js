const searchBox = document.querySelector('.home-search-box');
const searchButton = document.getElementById('home-search-button');
const searchBar = document.querySelector('.home-search-bar');

if (searchBox && searchButton && searchBar) {
    function setSearchExpanded(expanded) {
        if (expanded) {
            searchBox.classList.add('active');
            searchButton.setAttribute('aria-expanded', 'true');
        } else {
            searchBox.classList.remove('active');
            searchButton.setAttribute('aria-expanded', 'false');
        }
    }

    searchButton.addEventListener('click', (e) => {
        e.preventDefault();
        const isOpen = searchBox.classList.contains('active');
        setSearchExpanded(!isOpen);
        if (!isOpen) searchBar.focus();
    });

    document.addEventListener('click', (e) => {
        if (!searchBox.contains(e.target) && e.target !== searchButton) {
            if (searchBox.classList.contains('active')) {
                setSearchExpanded(false);
                searchBar.value = '';
            }
        }
    });

    searchBar.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            setSearchExpanded(false);
            searchBar.value = '';
            searchButton.focus();
        }
    });
}
