const BASE_URL = window.location.origin + '/bitezy';
const CURRENCY = 'rs. ';

document.addEventListener('DOMContentLoaded', function() {
  initNavbar();
  initHeroSlider();
  initCartCount();
  initForms();
  initModals();
  initProgressBars();
  initPills();
});

function initNavbar() {
  const toggle = document.getElementById('navToggle');
  const menu = document.getElementById('navMenu');
  if (!toggle || !menu) return;

  toggle.addEventListener('click', function() {
    this.classList.toggle('active');
    menu.classList.toggle('active');
  });

  document.addEventListener('click', function(e) {
    if (!e.target.closest('.navbar')) {
      toggle.classList.remove('active');
      menu.classList.remove('active');
    }
  });

  window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (navbar) {
      navbar.classList.toggle('scrolled', window.scrollY > 50);
    }
  });
}

function initHeroSlider() {
  const slides = document.querySelectorAll('.hero-slide');
  if (!slides.length) return;
  let current = 0;

  function nextSlide() {
    slides[current].classList.remove('active');
    current = (current + 1) % slides.length;
    slides[current].classList.add('active');
  }

  setInterval(nextSlide, 4000);
}

function initCartCount() {
  updateCartCount();
}

function updateCartCount() {
  const badge = document.getElementById('cart-count');
  if (!badge) return;

  fetch(BASE_URL + '/api/get-cart-count.php')
    .then(r => r.json())
    .then(data => {
      badge.textContent = data.count || 0;
      badge.style.display = data.count > 0 ? 'inline' : 'none';
    })
    .catch(() => {});
}

function addToCart(itemId, button) {
  const formData = new FormData();
  formData.append('item_id', itemId);
  formData.append('quantity', 1);

  fetch(BASE_URL + '/api/add-to-cart.php', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      if (button) {
        button.classList.add('added');
        button.innerHTML = '<i class="fas fa-check"></i> Added';
        setTimeout(() => {
          button.classList.remove('added');
          button.innerHTML = '<i class="fas fa-shopping-cart"></i> Add';
        }, 2000);
      }
      updateCartCount();
      showToast(data.message || 'Added to cart!', 'success');
    } else {
      if (data.redirect) {
        window.location.href = data.redirect;
      } else {
        showToast(data.message || 'Failed to add item', 'error');
      }
    }
  })
  .catch(() => showToast('Network error', 'error'));
}

function updateCartItem(itemId, change) {
  const qtySpan = document.querySelector(`.cart-item-qty[data-id="${itemId}"] span`);
  const row = document.querySelector(`.cart-item[data-id="${itemId}"]`);
  if (!qtySpan) return;

  let qty = parseInt(qtySpan.textContent) + change;
  if (qty < 1) { removeCartItem(itemId); return; }

  const formData = new FormData();
  formData.append('item_id', itemId);
  formData.append('quantity', qty);

  fetch(BASE_URL + '/api/update-cart.php', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      qtySpan.textContent = qty;
      if (row) {
        const priceEl = row.querySelector('.cart-item-price');
        if (priceEl && data.item_total) {
          priceEl.textContent = CURRENCY + parseFloat(data.item_total).toFixed(2);
        }
      }
      updateCartSummary();
      updateCartCount();
    }
  })
  .catch(() => {});
}

function removeCartItem(itemId) {
  const row = document.querySelector(`.cart-item[data-id="${itemId}"]`);
  if (row) {
    row.classList.add('removing');
    setTimeout(() => {
      const formData = new FormData();
      formData.append('item_id', itemId);
      fetch(BASE_URL + '/api/remove-from-cart.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            row.remove();
            updateCartSummary();
            updateCartCount();
            checkCartEmpty();
          }
        });
    }, 400);
  }
}

function updateCartSummary() {
  const subtotalEl = document.getElementById('cart-subtotal');
  const totalEl = document.getElementById('cart-total');
  const items = document.querySelectorAll('.cart-item');
  let subtotal = 0;

  items.forEach(item => {
    const priceEl = item.querySelector('.cart-item-price');
    if (priceEl) {
      subtotal += parseFloat(priceEl.textContent.replace('rs. ', '')) || 0;
    }
  });

  if (subtotalEl) subtotalEl.textContent = CURRENCY + subtotal.toFixed(2);
  if (totalEl) totalEl.textContent = CURRENCY + subtotal.toFixed(2);
}

function checkCartEmpty() {
  const container = document.querySelector('.cart-items-container');
  const empty = document.querySelector('.cart-empty');
  const summary = document.querySelector('.cart-summary');
  const items = document.querySelectorAll('.cart-item');

  if (items.length === 0 && container) {
    if (empty) empty.style.display = 'block';
    if (summary) summary.style.display = 'none';
    if (container) container.innerHTML = `
      <div class="cart-empty">
        <i class="fas fa-shopping-cart"></i>
        <h3>Your cart is empty</h3>
        <p>Looks like you haven't added anything yet.</p>
        <a href="${BASE_URL}/pages/menu.php" class="btn btn-primary" style="margin-top:20px">Browse Menu</a>
      </div>`;
  }
}

function showToast(message, type = 'info') {
  let container = document.querySelector('.toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  const icons = { success: 'fa-check-circle', error: 'fa-exclamation-circle', info: 'fa-info-circle' };
  toast.innerHTML = `<i class="fas ${icons[type] || icons.info}"></i> ${message}`;
  container.appendChild(toast);

  setTimeout(() => {
    toast.classList.add('removing');
    setTimeout(() => toast.remove(), 400);
  }, 3000);
}

function initForms() {
  document.querySelectorAll('form[data-validate]').forEach(form => {
    form.addEventListener('submit', function(e) {
      let valid = true;
      this.querySelectorAll('[required]').forEach(input => {
        const group = input.closest('.form-group');
        if (!input.value.trim()) {
          group.classList.add('error');
          group.classList.remove('success');
          valid = false;
        } else {
          group.classList.remove('error');
          group.classList.add('success');
          if (input.type === 'email' && !isValidEmail(input.value)) {
            group.classList.add('error');
            valid = false;
          }
          if (input.type === 'password' && input.value.length < 6) {
            group.classList.add('error');
            valid = false;
          }
        }
      });
      if (!valid) e.preventDefault();
    });

    this.querySelectorAll('input').forEach(input => {
      input.addEventListener('input', function() {
        const group = this.closest('.form-group');
        if (this.value.trim()) {
          group.classList.remove('error');
          group.classList.add('success');
        } else {
          group.classList.remove('success');
        }
      });
    });
  });
}

function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function initModals() {
  document.querySelectorAll('[data-modal]').forEach(btn => {
    btn.addEventListener('click', function() {
      const target = document.getElementById(this.dataset.modal);
      if (target) target.classList.add('active');
    });
  });

  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
      if (e.target === this) this.classList.remove('active');
    });
  });
}

function initProgressBars() {
  document.querySelectorAll('.progress-container').forEach(container => {
    const fill = container.querySelector('.progress-bar-fill');
    if (!fill) return;
    const steps = container.querySelectorAll('.progress-step');
    const activeIdx = Array.from(steps).findIndex(s => s.classList.contains('active'));
    const completedIdx = Array.from(steps).filter(s => s.classList.contains('completed')).length;

    setTimeout(() => {
      const pct = activeIdx >= 0
        ? ((activeIdx) / (steps.length - 1)) * 100
        : (completedIdx / steps.length) * 100;
      fill.style.width = pct + '%';
    }, 500);
  });
}

function initPills() {
  document.querySelectorAll('.pill').forEach(pill => {
    pill.addEventListener('click', function() {
      this.parentElement.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
      this.classList.add('active');
    });
  });
}

function placeOrder() {
  const btn = document.getElementById('place-order-btn');
  if (!btn) return;
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

  fetch(BASE_URL + '/api/place-order.php', { method: 'POST' })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast('Order placed successfully!', 'success');
        setTimeout(() => {
          window.location.href = data.redirect || (BASE_URL + '/pages/payment.php?order_id=' + data.order_id);
        }, 1000);
      } else {
        showToast(data.message || 'Failed to place order', 'error');
        btn.disabled = false;
        btn.innerHTML = 'Place Order';
      }
    })
    .catch(() => {
      showToast('Network error', 'error');
      btn.disabled = false;
      btn.innerHTML = 'Place Order';
    });
}

function confirmPayment(orderId) {
  const btn = document.getElementById('pay-btn');
  if (!btn) return;
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

  const method = document.querySelector('.payment-method.active');
  const paymentMethod = method ? method.dataset.method : 'paypal';

  const formData = new FormData();
  formData.append('order_id', orderId);
  formData.append('method', paymentMethod);

  fetch(BASE_URL + '/api/process-payment.php', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('Payment successful!', 'success');
      setTimeout(() => {
        window.location.href = data.redirect || (BASE_URL + '/pages/order-tracking.php');
      }, 1000);
    } else {
      showToast(data.message || 'Payment failed', 'error');
      btn.disabled = false;
      btn.innerHTML = 'Pay Now';
    }
  })
  .catch(() => {
    showToast('Network error', 'error');
    btn.disabled = false;
    btn.innerHTML = 'Pay Now';
  });
}

function deleteItem(type, id) {
  if (!confirm('Are you sure you want to delete this ' + type + '?')) return;

  const formData = new FormData();
  formData.append('id', id);
  formData.append('type', type);

  fetch(BASE_URL + '/api/delete-item.php', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast(type + ' deleted successfully!', 'success');
      setTimeout(() => location.reload(), 500);
    } else {
      showToast(data.message || 'Delete failed', 'error');
    }
  })
  .catch(() => showToast('Network error', 'error'));
}
