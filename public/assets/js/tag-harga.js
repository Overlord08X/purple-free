function initDataTable() {
    return $('#tableBarang').DataTable();
}

function initCheckAll(table) {

    $('#checkAll').on('click', function () {
        let rows = table.rows({ search: 'applied' }).nodes();
        $('input[type="checkbox"]', rows).prop('checked', this.checked);
    });

    $('#tableBarang tbody').on('change', '.checkItem', function () {
        let total = table.rows({ search: 'applied' }).nodes().length;
        let checked = $('.checkItem:checked').length;
        $('#checkAll').prop('checked', total === checked);
    });
}

function initPreviewGrid() {

    function generateGrid() {
        let grid = '';
        let x = parseInt($('#inputX').val());
        let y = parseInt($('#inputY').val());

        for (let row = 1; row <= 8; row++) {
            grid += '<tr>';
            for (let col = 1; col <= 5; col++) {

                let isStart = (col === x && row === y);

                grid += `<td style="height:40px;
                    ${isStart ? 'background-color:#4B49AC;color:white;font-weight:bold;' : ''}">
                    ${col},${row}
                </td>`;
            }
            grid += '</tr>';
        }

        $('#previewGrid').html(grid);
    }

    $('#inputX, #inputY').on('keyup change', generateGrid);
    generateGrid();
}

$(document).ready(function () {
    let table = initDataTable();
    initCheckAll(table);
    initPreviewGrid();
});