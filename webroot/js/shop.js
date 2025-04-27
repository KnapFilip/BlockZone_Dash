function filterItems(category) {
    const items = document.querySelectorAll('.shop-item');
    items.forEach(item => {
        if (category === 'all' || item.classList.contains(category)) {
            item.classList.remove('hidden');
        } else {
            item.classList.add('hidden');
        }
    });
}
