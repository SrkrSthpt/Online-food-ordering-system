const CURRENCY = 'rs. ';
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';

async function api(url, options = {}) {
  const opts = { ...options };
  if (opts.body instanceof FormData && !opts.body.has('_token')) {
    opts.body.append('_token', CSRF_TOKEN);
  }
  return fetch(url, opts);
}

document.addEventListener('DOMContentLoaded', function() {
  initNavbar();
  initHeroSlider();
  initCartCount();
  initForms();
  initModals();
  initProgressBars();
  initPills();
  initFlash();
  initEtaCountdowns();
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

function initFlash() {
  document.querySelectorAll('[data-flash]').forEach(node => {
    setTimeout(() => {
      node.classList.add('removing');
      node.addEventListener('transitionend', () => node.remove(), { once: true });
      setTimeout(() => node.remove(), 400);
    }, 4000);
  });
}

function initCartCount() {
  updateCartCount();
}

function updateCartCount() {
  const badge = document.getElementById('cart-count');
  if (!badge) return;

  fetch('/api/cart/count')
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

  api('/api/cart/add', { method: 'POST', body: formData })
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

  api('/api/cart/update', { method: 'POST', body: formData })
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
  if (!row) return;

  row.classList.add('removing');
  setTimeout(() => {
    const formData = new FormData();
    formData.append('item_id', itemId);
    api('/api/cart/remove', { method: 'POST', body: formData })
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
  const items = document.querySelectorAll('.cart-item');
  if (items.length > 0) return;

  const empty = document.querySelector('.cart-empty');
  const container = document.querySelector('.cart-items-container');
  const summary = document.querySelector('.cart-summary');

  if (empty) empty.style.display = 'block';
  if (summary) summary.style.display = 'none';
  if (container) {
    showToast('Your cart is empty', 'info');
    setTimeout(() => location.reload(), 700);
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
        group.classList.toggle('error', !this.value.trim());
        group.classList.toggle('success', !!this.value.trim());
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

  api('/api/orders/place', { method: 'POST', body: new FormData() })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast('Order placed successfully!', 'success');
        setTimeout(() => {
          window.location.href = data.redirect;
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

function confirmPayment(orderId, method) {
  const btn = document.getElementById('pay-btn');
  if (!btn) return;
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

  const formData = new FormData();
  formData.append('order_id', orderId);
  formData.append('method', method || 'cod');

  api('/api/payments/process', { method: 'POST', body: formData })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('Payment successful!', 'success');
      setTimeout(() => {
        window.location.href = data.redirect;
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

  api('/api/admin/delete', { method: 'POST', body: formData })
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

/* ------------------------------------------------------------------ */
/* Delivery dashboard                                                 */
/* ------------------------------------------------------------------ */

function acceptDelivery(orderId) {
  const formData = new FormData();
  formData.append('order_id', orderId);
  api('/api/delivery/orders/' + orderId + '/accept', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      showToast(data.message || 'Order accepted.', data.success ? 'success' : 'error');
      if (data.success) setTimeout(() => location.reload(), 600);
    })
    .catch(() => showToast('Network error', 'error'));
}

function deliveryStatus(orderId, status) {
  const formData = new FormData();
  formData.append('status', status);
  api('/api/delivery/orders/' + orderId + '/status', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      showToast(data.message || 'Updated.', data.success ? 'success' : 'error');
      if (data.success) setTimeout(() => location.reload(), 600);
    })
    .catch(() => showToast('Network error', 'error'));
}

function updateEta(orderId) {
  const input = document.getElementById('eta-' + orderId);
  const minutes = input ? parseInt(input.value, 10) : 30;
  if (!minutes || minutes < 1) return showToast('Enter a valid ETA in minutes.', 'error');

  const formData = new FormData();
  formData.append('minutes', minutes);
  api('/api/delivery/orders/' + orderId + '/eta', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      showToast(data.message || 'ETA updated.', data.success ? 'success' : 'error');
      if (data.success) setTimeout(() => location.reload(), 600);
    })
    .catch(() => showToast('Network error', 'error'));
}

/* ------------------------------------------------------------------ */
/* Customer tracking (live poll + ETA countdown)                      */
/* ------------------------------------------------------------------ */

const TRACK_STEPS = [
  'pending', 'accepted', 'preparing', 'prepared', 'out_for_delivery', 'delivered'
];
const TRACK_LABELS = {
  pending: 'Order Placed', accepted: 'Accepted', preparing: 'Preparing',
  prepared: 'Ready for Delivery', out_for_delivery: 'On the Way', delivered: 'Delivered'
};
const TRACK_ICONS = {
  pending: 'fa-clipboard-list', accepted: 'fa-store', preparing: 'fa-fire',
  prepared: 'fa-utensils', out_for_delivery: 'fa-motorcycle', delivered: 'fa-check-double'
};

function pollOrder(card) {
  const id = card.dataset.orderId;
  fetch('/api/orders/' + id + '/track')
    .then(r => r.json())
    .then(data => {
      if (!data.success) return;
      renderTrackStatus(card, data);
    })
    .catch(() => {});
}

function renderTrackStatus(card, data) {
  const status = data.status || 'pending';
  const stepIndex = TRACK_STEPS.indexOf(status);

  card.dataset.status = status;

  const label = card.querySelector('.status-label');
  if (label) label.textContent = TRACK_LABELS[status] || status;
  if (label) label.className = 'order-status status-label status-' + status;

  card.querySelectorAll('.progress-step').forEach((step, idx) => {
    step.classList.toggle('completed', idx < stepIndex);
    step.classList.toggle('active', idx === stepIndex);
  });

  if (data.delivery_name) {
    let dEl = card.querySelector('.delivery-name');
    if (!dEl) {
      const meta = card.querySelector('.order-meta');
      if (meta) {
        dEl = document.createElement('span');
        dEl.className = 'delivery-name';
        meta.appendChild(dEl);
      }
    }
    if (dEl) dEl.innerHTML = '<i class="fas fa-motorcycle"></i> ' + data.delivery_name;
  }

  const etaEl = card.querySelector('.eta-label');
  if (etaEl) {
    if (data.eta) {
      etaEl.dataset.eta = data.eta;
      etaEl.querySelector('strong').textContent = new Date(data.eta.replace(' ', 'T')).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    } else {
      etaEl.remove();
    }
  }

  const timeline = card.querySelector('.order-timeline');
  if (timeline && data.timeline && data.timeline.length) {
    timeline.innerHTML = '<h4 style="margin-bottom:10px;"><i class="fas fa-history"></i> Timeline</h4>' +
      data.timeline.map(function(e) {
        return '<div class="timeline-row">' +
          '<i class="fas ' + (TRACK_ICONS[e.status] || 'fa-circle') + '"></i>' +
          '<span class="timeline-status">' + (TRACK_LABELS[e.status] || e.status) + '</span>' +
          '<span class="timeline-time">' + formatTime(e.created_at) + '</span>' +
        '</div>';
      }).join('');
  }
}

function formatTime(value) {
  const d = new Date(String(value).replace(' ', 'T'));
  if (isNaN(d)) return value;
  return d.toLocaleDateString([], { month: 'short', day: 'numeric' }) + ', ' +
    d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function initEtaCountdowns() {
  setInterval(function() {
    document.querySelectorAll('.eta-label[data-eta]').forEach(function(el) {
      const eta = new Date(el.dataset.eta.replace(' ', 'T'));
      if (isNaN(eta)) return;
      const mins = Math.max(0, Math.round((eta.getTime() - Date.now()) / 60000));
      let note = el.querySelector('.eta-countdown');
      if (!note) {
        note = document.createElement('span');
        note.className = 'eta-countdown';
        el.appendChild(note);
      }
      note.textContent = ' (' + (mins <= 0 ? 'arriving' : '~' + mins + ' min') + ')';
    });
  }, 1000);
}