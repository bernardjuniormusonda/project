(function () {
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
    var KEY = 'printel_quote_basket';
    function load() {
        try {
            var v = JSON.parse(window.localStorage.getItem(KEY) || '[]');
            return Array.isArray(v) ? v.filter(function (x) { return x && typeof x.item === 'string'; }).slice(0, 100) : [];
        } catch (e) { return []; }
    }
    function save(list) {
        try { window.localStorage.setItem(KEY, JSON.stringify(list)); } catch (e) { /* storage unavailable: ignore */ }
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
                    var n = parseInt(qty.value, 10);
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
            if (list.length) {
                var lines = list.map(function (r) { return (r.qty || 1) + ' x ' + r.item + (r.cat ? ' (' + r.cat + ')' : ''); });
                var extra = itemsField.value.trim();
                itemsField.value = lines.join('\n') + (extra ? '\n' + extra : '');
            }
        });
    }
    // After a successful quote request, empty the basket
    if (document.getElementById('quote-done')) { save([]); }
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
