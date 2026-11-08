document.addEventListener('DOMContentLoaded', () => {
    initMobileMenu();
    initDeleteConfirmations();
    initCartQuantityInputs();
    initAddToCartButtons();
});

const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

function initMobileMenu() {
    const menuToggle = document.getElementById('menuToggle');
    const navLinks = document.getElementById('navLinks');

    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', () => navLinks.classList.toggle('active'));
    }

    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => {
            if (navLinks?.classList.contains('active')) {
                navLinks.classList.remove('active');
            }
        });
    });
}

function initDeleteConfirmations() {
    document.querySelectorAll('.delete-confirm').forEach(button => {
        button.addEventListener('click', (e) => {
            if (!confirm('هل أنت متأكد من حذف هذا العنصر؟')) {
                e.preventDefault();
            }
        });
    });
}

function initCartQuantityInputs() {
    document.querySelectorAll('.cart-quantity').forEach(input => {
        input.addEventListener('change', function() {
            const cartId = this.dataset.cartId;
            const quantity = parseInt(this.value, 10);
            if (quantity > 0) {
                updateCart(cartId, quantity);
            } else {
                this.value = 1;
            }
        });
    });
}

function initAddToCartButtons() {
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            const originalText = this.innerHTML;
            
            this.disabled = true;
            this.innerHTML = '⏳ جاري الإضافة...';

            const productId = this.dataset.productId;
            const quantity = document.getElementById('quantity')?.value || 1;

            await addToCart(productId, quantity);
            
            this.disabled = false;
            this.innerHTML = originalText;
        });
    });
}

async function addToCart(productId, quantity) {
    try {
        const response = await fetch('add_to_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                product_id: productId,
                quantity: quantity,
                csrf_token: getCsrfToken()
            })
        });

        const data = await response.json();
        
        if (data.success) {
            showMessage('✅ تم إضافة المنتج إلى السلة!', 'success');
            updateCartCount();
        } else {
            showMessage(`❌ ${data.message}`, 'error');
        }
    } catch (error) {
        console.error(error);
        showMessage('❌ حدث خطأ في الاتصال، حاول مرة أخرى', 'error');
    }
}

async function updateCart(cartId, quantity) {
    try {
        const response = await fetch('update_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                cart_id: cartId,
                quantity: quantity,
                csrf_token: getCsrfToken()
            })
        });

        const data = await response.json();
        
        if (data.success) {
            showMessage('✅ تم تحديث السلة!', 'success');
            updateCartCount();
            setTimeout(() => location.reload(), 1000);
        } else {
            showMessage(`❌ ${data.message}`, 'error');
        }
    } catch (error) {
        console.error(error);
        showMessage('❌ حدث خطأ في الاتصال، حاول مرة أخرى', 'error');
    }
}

function removeFromCart(cartId) {
    if (!confirm('هل أنت متأكد من حذف هذا المنتج من السلة؟')) return;

    fetch('remove_from_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            cart_id: cartId,
            csrf_token: getCsrfToken()
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('✅ تم حذف المنتج من السلة!', 'success');
            location.reload();
        } else {
            showMessage(`❌ ${data.message}`, 'error');
        }
    })
    .catch(() => showMessage('❌ حدث خطأ، حاول مرة أخرى', 'error'));
}

function updateCartCount() {
    fetch('get_cart_count.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('cartCount');
            if (badge) badge.textContent = data.count || 0;
        })
        .catch(console.error);
}

function showMessage(message, type = 'info') {
    document.querySelectorAll('.alert-floating').forEach(alert => alert.remove());

    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-floating`;
    alertDiv.style.cssText = `
        position: fixed; top: 20px; left: 50%; transform: translateX(-50%);
        z-index: 9999; min-width: 300px; max-width: 90%; text-align: center;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2); animation: fadeInUp 0.5s ease;
    `;
    alertDiv.textContent = message;
    document.body.appendChild(alertDiv);

    setTimeout(() => {
        alertDiv.style.opacity = '0';
        alertDiv.style.transition = 'opacity 0.5s';
        setTimeout(() => alertDiv.remove(), 500);
    }, 3000);
}

function filterProducts() {
    const category = document.getElementById('categoryFilter')?.value || '';
    const sort = document.getElementById('sortFilter')?.value || '';
    const search = document.getElementById('searchInput')?.value || '';
    
    let url = 'products.php?';
    if (category) url += `category=${category}&`;
    if (sort) url += `sort=${sort}&`;
    if (search) url += `search=${encodeURIComponent(search)}`;
    
    window.location.href = url;
}

let searchTimeout;
function liveSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(filterProducts, 500);
}