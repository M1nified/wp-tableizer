var new_rows_count = 0;


window.addEventListener('load',function(){
    let addNewRow = function(){
        let new_rows = document.querySelector('#new-rows>tbody>tr');
        let new_row = new_rows.cloneNode(true);
        new_row.querySelectorAll('input').forEach((input)=>{
            input.name = input.name.replace('$row_number',new_rows_count);
        })
        document.querySelector('#table-input>tbody').appendChild(new_row);
        new_rows_count++;
    }
    
    let table_input = document.querySelector('#table-input');
    let new_rows_head = document.querySelector('#new-rows>thead>tr');
    document.querySelector('#table-input>thead').appendChild(new_rows_head.cloneNode(true));
    addNewRow();

    document.querySelector('#btn-add-row').addEventListener('click',function(){
        addNewRow();
    })
});