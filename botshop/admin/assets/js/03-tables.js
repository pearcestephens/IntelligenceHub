/**
 * 03-tables.js - Table interactions and data handling
 */

const Tables = {
    /**
     * Initialize data table with sorting and filtering
     */
    init(tableSelector) {
        const table = document.querySelector(tableSelector);
        if (!table) return;

        this.addSortingHeaders(table);
        this.addFilterRow(table);
        this.addPagination(table);
    },

    /**
     * Add sorting to table headers
     */
    addSortingHeaders(table) {
        const headers = table.querySelectorAll('thead th');
        headers.forEach((header, index) => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => this.sortTable(table, index));
            header.title = 'Click to sort';
            header.classList.add('sortable');
        });
    },

    /**
     * Sort table by column
     */
    sortTable(table, columnIndex) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));

        rows.sort((a, b) => {
            const aValue = a.cells[columnIndex].textContent.trim();
            const bValue = b.cells[columnIndex].textContent.trim();
            return isNaN(aValue)
                ? aValue.localeCompare(bValue)
                : parseFloat(aValue) - parseFloat(bValue);
        });

        rows.forEach(row => tbody.appendChild(row));
    },

    /**
     * Add filter row
     */
    addFilterRow(table) {
        const headerRow = table.querySelector('thead tr');
        const filterRow = document.createElement('tr');
        filterRow.className = 'filter-row';

        const cells = headerRow.querySelectorAll('th');
        cells.forEach((_, index) => {
            const filterCell = document.createElement('th');
            const filterInput = document.createElement('input');
            filterInput.type = 'text';
            filterInput.className = 'form-control form-control-sm';
            filterInput.placeholder = 'Filter...';
            filterInput.addEventListener('keyup', () => this.filterTable(table));

            filterCell.appendChild(filterInput);
            filterRow.appendChild(filterCell);
        });

        headerRow.parentElement.insertBefore(filterRow, headerRow.nextSibling);
    },

    /**
     * Filter table rows
     */
    filterTable(table) {
        const filterInputs = table.querySelectorAll('.filter-row input');
        const tbody = table.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr');

        rows.forEach(row => {
            let visible = true;
            row.cells.forEach((cell, index) => {
                const filterValue = filterInputs[index].value.toLowerCase();
                const cellValue = cell.textContent.toLowerCase();
                if (!cellValue.includes(filterValue)) {
                    visible = false;
                }
            });
            row.style.display = visible ? '' : 'none';
        });
    },

    /**
     * Add pagination to table
     */
    addPagination(table, rowsPerPage = 10) {
        const tbody = table.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr');
        const pageCount = Math.ceil(rows.length / rowsPerPage);

        let currentPage = 1;

        const showPage = (pageNum) => {
            rows.forEach((row, index) => {
                const start = (pageNum - 1) * rowsPerPage;
                const end = start + rowsPerPage;
                row.style.display = (index >= start && index < end) ? '' : 'none';
            });
        };

        showPage(currentPage);

        return {
            getCurrentPage: () => currentPage,
            setPage: (page) => {
                if (page > 0 && page <= pageCount) {
                    currentPage = page;
                    showPage(currentPage);
                }
            },
            getPageCount: () => pageCount
        };
    },

    /**
     * Export table to CSV
     */
    exportCSV(table, filename = 'export.csv') {
        let csv = [];
        const rows = table.querySelectorAll('tr');

        rows.forEach(row => {
            const cols = row.querySelectorAll('td, th');
            const csvRow = Array.from(cols).map(col => {
                let text = col.textContent.trim();
                text = text.includes(',') ? `"${text}"` : text;
                return text;
            });
            csv.push(csvRow.join(','));
        });

        const csvContent = 'data:text/csv;charset=utf-8,' + csv.join('\n');
        const link = document.createElement('a');
        link.setAttribute('href', encodeURI(csvContent));
        link.setAttribute('download', filename);
        link.click();
    },

    /**
     * Export table to JSON
     */
    exportJSON(table, filename = 'export.json') {
        const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
        const rows = Array.from(table.querySelectorAll('tbody tr')).map(tr => {
            const obj = {};
            Array.from(tr.cells).forEach((cell, index) => {
                obj[headers[index]] = cell.textContent.trim();
            });
            return obj;
        });

        const jsonContent = 'data:text/json;charset=utf-8,' + JSON.stringify(rows, null, 2);
        const link = document.createElement('a');
        link.setAttribute('href', encodeURI(jsonContent));
        link.setAttribute('download', filename);
        link.click();
    }
};

console.log('✓ Tables module loaded');
