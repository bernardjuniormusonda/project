(async function () {
    'use strict';
    document.documentElement.classList.add('js');

    /* ---------- Mobile navigation ---------- */
    var toggle = document.querySelector('.nav-toggle');
    var nav = document.getElementById('site-nav');
    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            var open = nav.classList.toggle('open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && nav.classList.contains('open')) {
                nav.classList.remove('open');
                toggle.setAttribute('aria-expanded', 'false');
                toggle.focus();
            }
        });
    }

    /* ---------- Scroll reveal ---------- */
    var items = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
            });
        }, { threshold: 0.12 });
        items.forEach(function (el) { io.observe(el); });
    } else {
        items.forEach(function (el) { el.classList.add('in'); });
    }

    /* ---------- Product search ---------- */
    var search = document.getElementById('product-search');
    var grid = document.getElementById('product-grid');
    if (search && grid) {
        var cards = grid.querySelectorAll('.card');
        var none = document.getElementById('no-results');
        var count = document.getElementById('search-count');
        search.addEventListener('input', function () {
            var q = search.value.trim().toLowerCase();
            var terms = q.split(/\s+/).filter(Boolean);
            var shown = 0;
            cards.forEach(function (card) {
                var hay = card.getAttribute('data-title') || '';
                var ok = terms.every(function (t) { return hay.indexOf(t) !== -1; });
                card.hidden = !ok;
                if (ok) { shown++; }
            });
            if (none) { none.hidden = shown !== 0; }
            if (count) { count.textContent = q ? shown + ' of ' + cards.length + ' categories' : ''; }
        });
    }

    /* ---------- Quote basket (kept in the browser, sent with the quote form) ---------- */
    var basket = [];
    var saveQueue = Promise.resolve();
    var csrf = document.querySelector('meta[name="csrf-token"]');
    try {
        var initial = await fetch('/api/basket', { credentials: 'same-origin' });
        if (!initial.ok) throw new Error('Basket unavailable');
        basket = (await initial.json()).items;
    } catch (_) { /* Manual quote requests remain available. */ }
    function load() { return basket.map(function (row) { return Object.assign({}, row); }); }
    function save(list) {
        basket = list;
        var body = new URLSearchParams({items: JSON.stringify(list), _csrf: csrf ? csrf.content : ''});
        saveQueue = saveQueue.catch(function () {}).then(async function () {
            var result = await fetch('/api/basket', {method: 'POST', body: body, credentials: 'same-origin', keepalive: true});
            if (!result.ok) {
                var notice = document.getElementById('basket-error');
                if (!notice) {
                    notice = document.createElement('p'); notice.id = 'basket-error'; notice.className = 'alert alert-error'; notice.setAttribute('role', 'alert');
                    document.getElementById('main').prepend(notice);
                }
                notice.textContent = 'Your quote list could not be saved. Please try again, or enter your list on the quote page.';
            }
        }).catch(function () {
            window.alert('Your quote list could not be saved. Please enter your items on the quote page.');
        });
    }
    function has(list, item) { return list.some(function (x) { return x.item === item; }); }

    function refreshCount() {
        var n = load().length;
        document.querySelectorAll('#basket-count, [data-basket-count]').forEach(function (el) {
            el.textContent = String(n);
            el.hidden = n === 0;
        });
    }

    // "Add" buttons on category pages
    document.querySelectorAll('.add-btn').forEach(function (btn) {
        var item = btn.getAttribute('data-item');
        function paint(added) {
            btn.classList.toggle('added', added);
            btn.querySelector('span').textContent = added ? 'Added' : 'Add';
        }
        paint(has(load(), item));
        btn.addEventListener('click', function () {
            var list = load();
            if (has(list, item)) {
                list = list.filter(function (x) { return x.item !== item; });
                paint(false);
            } else {
                if (list.length >= 100) { window.alert('Your quote list can contain up to 100 items.'); return; }
                list.push({ item: item, cat: btn.getAttribute('data-cat') || '', qty: 1 });
                paint(true);
            }
            save(list);
            refreshCount();
        });
    });

    // Basket on the quote form
    var listEl = document.getElementById('basket-list');
    var itemsField = document.getElementById('items');
    var emptyMsg = document.getElementById('basket-empty');
    var clearBtn = document.getElementById('basket-clear');
    var quoteForm = document.getElementById('quote-form');
    if (listEl && itemsField && quoteForm) {
        var list = load();
        function render() {
            listEl.innerHTML = '';
            list.forEach(function (row, i) {
                var li = document.createElement('li');
                var label = document.createElement('span');
                label.textContent = row.item;
                if (row.cat) {
                    var small = document.createElement('small');
                    small.textContent = row.cat;
                    label.appendChild(small);
                }
                var qty = document.createElement('input');
                qty.type = 'number'; qty.min = '1'; qty.max = '100000'; qty.value = String(row.qty || 1);
                qty.className = 'qty'; qty.setAttribute('aria-label', 'Quantity for ' + row.item);
                qty.addEventListener('input', function () {
                    var n = Math.min(100000, parseInt(qty.value, 10));
                    list[i].qty = n > 0 ? n : 1;
                    save(list);
                });
                var rm = document.createElement('button');
                rm.type = 'button'; rm.className = 'rm'; rm.textContent = '×';
                rm.setAttribute('aria-label', 'Remove ' + row.item);
                rm.addEventListener('click', function () {
                    list.splice(i, 1); save(list); render(); refreshCount();
                });
                li.appendChild(label); li.appendChild(qty); li.appendChild(rm);
                listEl.appendChild(li);
            });
            if (emptyMsg) { emptyMsg.hidden = list.length > 0; }
            if (clearBtn) { clearBtn.hidden = list.length === 0; }
        }
        render();
        if (clearBtn) {
            clearBtn.addEventListener('click', function () { list = []; save(list); render(); refreshCount(); });
        }
        // On submit, put the basket into the "items" text (the server stores plain text).
        quoteForm.addEventListener('submit', function () {
            if (!quoteForm.checkValidity()) { return; }
            if (list.length) {
                var lines = list.map(function (r) { return (r.qty || 1) + ' x ' + r.item + (r.cat ? ' (' + r.cat + ')' : ''); });
                var extra = itemsField.value.trim();
                var manual = document.createElement('input'); manual.type = 'hidden'; manual.name = 'manual_items'; manual.value = extra; quoteForm.appendChild(manual);
                itemsField.value = lines.join('\n') + (extra ? '\n' + extra : '');
            }
        });
    }
    // After a successful quote request, empty the basket
    if (document.getElementById('quote-done')) { basket = []; }
    refreshCount();

    /* ---------- Confirm destructive admin actions (CSP-safe) ---------- */
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!window.confirm(form.getAttribute('data-confirm'))) { e.preventDefault(); }
        });
    });
    document.querySelectorAll('button[data-confirm-btn]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!window.confirm(btn.getAttribute('data-confirm-btn'))) { e.preventDefault(); }
        });
    });

    /* ---------- Friendly validation + double-submit guard ---------- */
    document.querySelectorAll('form.form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (e.defaultPrevented) { return; }
            var bad = null;
            form.querySelectorAll('[required]').forEach(function (f) {
                var empty = !f.value.trim();
                f.style.borderColor = empty ? '#d33' : '';
                if (empty && !bad) { bad = f; }
            });
            if (bad) {
                e.preventDefault();
                bad.focus();
                return;
            }
            var btn = e.submitter || form.querySelector('button[type=submit]');
            if (btn && btn.type === 'submit' && !btn.name) {
                setTimeout(function () { btn.disabled = true; btn.textContent = 'Please wait...'; }, 0);
            }
        });
    });
})();
