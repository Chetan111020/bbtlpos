const qrcode = new QRCode(document.getElementById('qrcode'), {
    text: '/get-pdf-invoice/6138',
    width: 85,
    height: 85,
    colorDark : '#000000',
    colorLight : '#ffffff',
    correctLevel : QRCode.CorrectLevel.L
});
//for read the table data
var details = $('tr.box_lines').map(function(i, row) {

    return {
        'box_no': row.cells[1].textContent.trim(),
        'product': row.cells[2].textContent.trim(),
        'item_code': row.cells[3].textContent.trim(),
        'sku': row.cells[4].textContent.trim(),
        'Qty': row.cells[5].textContent.toString().replace(',', ''),
        'price': row.cells[6].textContent.trim(),
        'Subtotal': row.cells[8].textContent.toString().replace(',', '')
    }
}).get();
console.log(details);

//for merging duplicates
result = [];
box_nos_arr = [];
let index = 1;
details.forEach(function(a) {

    let keyboxno = a.box_no;
    let keyname = a.product + '-' + a.box_no;
    //console.log("===?",keyname)
    if (!this[keyname]) {
        //this[a.product] = parseInt(Quantity);
        this[keyname] = {
            box_no: a.box_no,
            product: a.product,
            item_code: a.item_code,
            sku: a.sku,
            Qty: 0,
            price: a.price,
            Subtotal: 0.0,
            sr: index,
        };
        result.push(this[keyname]);
        index++;
    }

    this[keyname].Qty += Math.round(a.Qty);
    this[keyname].Subtotal += parseFloat(a.Subtotal);

    if (!this[keyboxno]) {
        //this[a.product] = parseInt(Quantity);
        this[keyboxno] = {
            box_no: a.box_no,
        };
        box_nos_arr.push(this[keyboxno]);
    }

}, Object.create(null));
console.log(result);
console.log(box_nos_arr);

//for display table with array of objects
function renove() {

    $('#mytable').remove();
    var k = '';

    for (bn = 0; bn < box_nos_arr.length; bn++) {

        k += '<tr">';
        k += '<td style="background-color: #EEEEEE !important;" colspan="7" width="100%"><b>&nbsp;&nbsp;Box No#' +
            box_nos_arr[bn].box_no +
            '</b></td>';
        k += '</tr>';

        for (i = 0; i < result.length; i++) {

            // k += '<tr">';
            // k += '<td text-align:center;" class="no" >' + 'BOX NO' + '</td>';
            // k += '<td text-align:left;" >' +'BOX # ' + sr  + '</td>';
            // k += '<td text-align:center;" >' + result[i].Qty + '</td>';
            // k += '<td text-align:center;" class="unit" >' + result[i].price + '</td>';
            // k += '<td text-align:center;" >' + result[i].tax + '</td>';
            // k += '<td text-align:center;" class="no">' + ' $ ' + result[i].subtotal.toFixed(2) +
            //     '</td>';
            // k += '</tr>';
            if (box_nos_arr[bn].box_no == result[i].box_no) {
                /*if(result[i].Qty == 0){

                    k += '<tr class="order-list">';
                }else{
                    k += '<tr">';
                }*/

                k += '<td style="text-align:center; " class="no" width="10%">' +
                    result[i].sr +
                    '</td>';
                k += '<td style="text-align:center;font-size: 14px;"  width="30%">' + result[i].product + '</td>';
                k += '<td style="text-align:center;" width="20%">' + result[i].item_code + '</td>';
                k += '<td style="text-align:center;" width="20%">' + result[i].sku + '</td>';
                k += '<td style="text-align:center;font-size: 14px;" width="5%" >' + result[i].Qty + '</td>';
                k += '<td style="text-align:center;font-size: 14px;" class="unit" width="5%">' + ' $ ' + result[i]
                    .price + '</td>';
                k += '<td style="text-align:center;" class="no" width="10%">' + ' $ ' + result[i].Subtotal.toFixed(
                        2) +
                    '</td>';

                k += '</tr>';
            }
        }
    }

    //k += '</tbody>';
    document.getElementById('tableData').innerHTML = k;
}


// setInterval(function(){
//     console.log("Oooo Yeaaa!");
renove();
// }, 1000);//run this thang every 1 seconds

// Qr code JS is in Show Invoice page .

// $("#print_invoice").click(function () {
//     $("#invoice_content").print();
// });

// $(document).ready(function(){
//     $(document).on('click', '#print_invoice', function(){
//         $('#invoice_content').printThis();
//     });
// });