(function() {
    'use strict';

    function init() {
        document.querySelectorAll('.dim-repeater-table').forEach(function(table) {
            setupTable(table);
        });
    }

    function setupTable(table) {
        const body = table.querySelector('tbody');
        const type = table.getAttribute('data-type');
        const addBtn = document.querySelector('.dim-add-row[data-type="' + type + '"]');

        if (addBtn) {
            addBtn.addEventListener('click', function(e) {
                e.preventDefault();
                addRow(table);
            });
        }

        body.querySelectorAll('.dim-repeater-row').forEach(function(row) {
            setupRow(row, table);
        });

        updateNumbers(table);
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
            updateNumbers(table);
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
                updateNumbers(table);
            });
        }

        const upBtn = row.querySelector('.dim-move-up');
        if (upBtn) {
            upBtn.addEventListener('click', function() {
                const prev = row.previousElementSibling;
                if (prev) {
                    row.parentNode.insertBefore(row, prev);
                    updateNumbers(table);
                }
            });
        }

        const downBtn = row.querySelector('.dim-move-down');
        if (downBtn) {
            downBtn.addEventListener('click', function() {
                const next = row.nextElementSibling;
                if (next) {
                    row.parentNode.insertBefore(next, row);
                    updateNumbers(table);
                }
            });
        }
    }

    function addRow(table) {
        const type = table.getAttribute('data-type');
        const tmpl = document.getElementById('dim-tmpl-' + type);
        if (!tmpl) {
            return;
        }

        const content = tmpl.content ? tmpl.content.cloneNode(true) : tmpl.innerHTML;

        let newRow;
        if (typeof content === 'string') {
            const wrapper = document.createElement('tbody');
            wrapper.innerHTML = content;
            newRow = wrapper.firstElementChild;
        } else {
            newRow = content.querySelector('.dim-repeater-row');
        }

        if (!newRow) {
            return;
        }

        const body = table.querySelector('tbody');
        body.appendChild(newRow);
        setupRow(newRow, table);
        updateNumbers(table);
    }

    function updateNumbers(table) {
        const rows = table.querySelectorAll('tbody .dim-repeater-row');

        rows.forEach(function(row, index) {
            const number = pad(index + 1);
            const numEl = row.querySelector('.dim-row-number');
            if (numEl) {
                numEl.textContent = number;
            }
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
