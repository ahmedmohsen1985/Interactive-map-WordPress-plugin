(function() {
    'use strict';

    function init() {
        document.querySelectorAll('.dim-repeater-table').forEach(function(table) {
            setupTable(table);
        });
    }

    function setupTable(table) {
        const body = table.querySelector('tbody');
        const key = table.getAttribute('data-key');
        const addBtn = document.querySelector('.dim-add-row[data-key="' + key + '"]');

        if (addBtn) {
            addBtn.addEventListener('click', function() {
                addRow(table);
            });
        }

        body.querySelectorAll('.dim-repeater-row').forEach(function(row) {
            setupRow(row, table);
        });

        reindex(table);
    }

    function setupRow(row, table) {
        row.setAttribute('draggable', 'true');

        row.addEventListener('dragstart', function(e) {
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', '');
            row.classList.add('dim-dragging');
            window.__dimDraggedRow = row;
        });

        row.addEventListener('dragend', function() {
            row.classList.remove('dim-dragging');
            window.__dimDraggedRow = null;
            reindex(table);
        });

        row.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';

            const dragged = window.__dimDraggedRow;
            if (!dragged || dragged === row) {
                return;
            }

            const rect = row.getBoundingClientRect();
            const offset = e.clientY - rect.top;

            if (offset > rect.height / 2) {
                row.parentNode.insertBefore(dragged, row.nextSibling);
            } else {
                row.parentNode.insertBefore(dragged, row);
            }
        });

        row.addEventListener('drop', function(e) {
            e.preventDefault();
        });

        const removeBtn = row.querySelector('.dim-remove-row');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                row.parentNode.removeChild(row);
                reindex(table);
            });
        }

        const upBtn = row.querySelector('.dim-move-up');
        if (upBtn) {
            upBtn.addEventListener('click', function() {
                const prev = row.previousElementSibling;
                if (prev) {
                    row.parentNode.insertBefore(row, prev);
                    reindex(table);
                }
            });
        }

        const downBtn = row.querySelector('.dim-move-down');
        if (downBtn) {
            downBtn.addEventListener('click', function() {
                const next = row.nextElementSibling;
                if (next) {
                    row.parentNode.insertBefore(next, row);
                    reindex(table);
                }
            });
        }
    }

    function addRow(table) {
        const key = table.getAttribute('data-key');
        const tmpl = document.getElementById('dim-tmpl-' + key.replace(/_/g, '-'));
        if (!tmpl) {
            return;
        }

        const uid = Date.now();
        let html = tmpl.innerHTML;
        html = html.replace(/__INDEX__/g, uid);

        const wrapper = document.createElement('tbody');
        wrapper.innerHTML = html;
        const newRow = wrapper.firstElementChild;
        if (!newRow) {
            return;
        }

        const body = table.querySelector('tbody');
        body.appendChild(newRow);
        setupRow(newRow, table);
        reindex(table);
    }

    function reindex(table) {
        const rows = table.querySelectorAll('tbody .dim-repeater-row');

        rows.forEach(function(row, index) {
            const number = pad(index + 1);
            const numEl = row.querySelector('.dim-row-number');
            if (numEl) {
                numEl.textContent = number;
            }

            const inputs = row.querySelectorAll('input');
            inputs.forEach(function(input) {
                const match = input.name.match(/^dim_(gps_links|perk_stops)\[[^\]]+\]\[[^\]]+\]$/);
                if (match) {
                    input.name = 'dim_' + match[1] + '[' + index + '][' + match[2] + ']';
                }
            });
        });
    }

    function pad(n) {
        return n < 10 ? '0' + n : '' + n;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
