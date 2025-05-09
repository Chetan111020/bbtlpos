$(document).ready(function () {


  //atock alert datatables
  var stock_alert_table = $("#stock_alert_table").DataTable({
    processing: true,
    serverSide: true,
    ordering: false,
    searching: false,
    dom: "Btirp",
    ajax: "/home/product-stock-alert",
    fnDrawCallback: function (oSettings) {
      __currency_convert_recursively($("#stock_alert_table"));
    },
  });
  //payment dues datatables
  var purchase_payment_dues_table = $("#purchase_payment_dues_table").DataTable(
    {
      processing: true,
      serverSide: true,
      ordering: false,
      searching: false,
      dom: "Btirp",
      ajax: "/home/purchase-payment-dues",
      fnDrawCallback: function (oSettings) {
        __currency_convert_recursively($("#purchase_payment_dues_table"));
      },
    }
  );

  //Sales dues datatables
  var sales_payment_dues_table = $("#sales_payment_dues_table").DataTable({
    processing: true,
    serverSide: true,
    ordering: false,
    searching: false,
    dom: "Btirp",
    ajax: "/home/sales-payment-dues",
    fnDrawCallback: function (oSettings) {
      __currency_convert_recursively($("#sales_payment_dues_table"));
    },
  });

  //Stock expiry report table
  stock_expiry_alert_table = $("#stock_expiry_alert_table").DataTable({
    processing: true,
    serverSide: true,
    searching: false,
    dom: "Btirp",
    ajax: {
      url: "/reports/stock-expiry",
      data: function (d) {
        d.exp_date_filter = $("#stock_expiry_alert_days").val();
      },
    },
    order: [[3, "asc"]],
    columns: [
      { data: "product", name: "p.name" },
      { data: "location", name: "l.name" },
      { data: "stock_left", name: "stock_left" },
      { data: "exp_date", name: "exp_date" },
    ],
    fnDrawCallback: function (oSettings) {
      __show_date_diff_for_human($("#stock_expiry_alert_table"));
      __currency_convert_recursively($("#stock_expiry_alert_table"));
    },
  });

  if ($("#quotation_table").length) {
    quotation_datatable = $("#quotation_table").DataTable({
      processing: true,
      serverSide: true,
      aaSorting: [[0, "desc"]],
      ajax: {
        url: "/sells/draft-dt?is_quotation=1",
        data: function (d) {
          if ($("#dashboard_location").length > 0) {
            d.location_id = $("#dashboard_location").val();
          }
        },
      },
      columnDefs: [
        {
          targets: 4,
          orderable: false,
          searchable: false,
        },
      ],
      columns: [
        { data: "transaction_date", name: "transaction_date" },
        { data: "invoice_no", name: "invoice_no" },
        { data: "name", name: "contacts.name" },
        { data: "business_location", name: "bl.name" },
        { data: "action", name: "action" },
      ],
    });
  }
});

var start, end;

$(document).ready(function (e) {
  $("#all_date_filter").daterangepicker({
    ranges: ranges,
    autoUpdateInput: true,
    // startDate: moment().startOf("days").subtract(6, 'days'),
    // endDate: moment().endOf("days"),
    startDate: moment().startOf("week"),
    endDate: moment().endOf("week"),
    locale: {
      format: moment_date_format,
    },
  });
  $("#all_date_filter").on("apply.daterangepicker", function (ev, picker) {
    $(this).val(
      picker.startDate.format(moment_date_format) +
        " ~ " +
        picker.endDate.format(moment_date_format)
    );
    $("#date").val($(this).val());
  });

  $("#all_date_filter").on("cancel.daterangepicker", function (ev, picker) {
    $(this).val("");
  });
  getReportdata();
});

$(document).on("click", "button#submitData", function (e) {
  e.preventDefault();
  getReportdata();
});

function getReportdata() {
  var loader = '<i class="fas fa-sync fa-spin fa-fw margin-bottom"></i>';
  $(".total_purchase").html();
  $(".purchase_due").html();
  $(".total_sell").html();
  $(".invoice_due").html();
  $(".total_expense").html();
  // $(".total_expense").html(loader);
  //added new
  $(".total_staff").html();
  $(".allproducts").html();
  $(".allcustomer").html();
  $(".quantity").html();
  $(".inventory_value").html();
  $(".a1").html();
  $(".a2").html();
  $(".a3").html();
  $(".a4").html();
  $(".a5").html();
  $(".a6").html();
  $(".a7").html();
  $(".a8").html();
  $(".a9").html();
  $(".a10").html();
  $(".a11").html();
  $(".a12").html();
  $(".gross_profit").html();
  $(".total_order").html();

  if ($("input#all_date_filter").val()) {
    start = $("input#all_date_filter")
      .data("daterangepicker")
      .startDate.format("YYYY-MM-DD");
    end = $("input#all_date_filter")
      .data("daterangepicker")
      .endDate.format("YYYY-MM-DD");
  }

  $.ajax({
    method: "get",
    url: "/demohome/get-totals",
    dataType: "json",
    data: {
      start_date: start,
      end_date: end,
    },
    success: function (data) {
        console.log(data);
      //purchase details
      $(".total_purchase").html("$" + __intToString(data.total_purchase, true));
      $(".purchase_due").html("$" + __intToString(data.purchase_due, true)
      );

      $(".total_discount").html("$" + __intToString(data.total_discount, true));

      //sell details
      $(".total_sell").html("$" + __intToString(data.total_sell, true));
      $(".invoice_due").html("$" + __intToString(data.invoice_due, true));
      //expense details
      $(".total_expense").html("$" + __intToString(data.total_expense, true));
      $(".total_staff").html(data.total_staff, true);
      $(".total_order").html(data.total_order, true);
      $(".allproducts").html(__intToString(data.allproducts, true));
      $(".allcustomer").html(data.allcustomer, true);
      $(".quantity").html(__intToString(data.quantity, true));
      $(".inventory_value").html("$" + __intToString(data.inventory_value, true));
      $(".a1").html(data.a[0] ?? "-", true);
      $(".a2").html(__currency_trans_from_en(data.a[1], true));
      $(".a3").html(data.a[2] ?? "-", true);
      $(".a4").html(__currency_trans_from_en(data.a[3], true));
      $(".a5").html(data.a[4] ?? "-", true);
      $(".a6").html(__currency_trans_from_en(data.a[5], true));
      $(".a7").html(data.a[6] ?? "-", true);
      $(".a8").html(__currency_trans_from_en(data.a[7], true));
      $(".a9").html(data.a[8] ?? "-", true);
      $(".a10").html(__currency_trans_from_en(data.a[9], true));
      $(".a11").html(data.a[10] ?? "-", true);
      $(".a12").html(__currency_trans_from_en(data.a[11], true));
      $(".gross_profit").html("$" + __number_format(data.gross_profit, true) + "%");

      // const newLocal = "GP";
      // $(".gross_profit").html(
      //   __number_format(data.gross_profit[0][newLocal], true) + "%"
      // );
    },
  });

  var options2 = {
    grid: {
      show: false,
    },
    series: [],
    type: "area",
    chart: {
      height: 350,
    },
    dataLabels: {
      enabled: false,
    },
    stroke: {
      curve: "smooth",
    },
    theme: {
      palette: 'palette3', 
    },
    xaxis: {
      type: "datetime",
      categories: [],
    },
    noData: {
      text: "Loading...",
    },
    tooltip: {
      x: {
        format: "dd/MM/yy",
      },
    },
  };

  $("#chart2", function () {
    var url1 = "/demohome/purchaseduechart?start=" + start + "&end=" + end;
    axios({
      method: "GET",
      url: url1,
    }).then(function (data) {
        console.log(data);
      chart2.updateOptions({
        series: [
          {
            name: "Purchase",
            data: data.data[0],
          },
          {
            name: "Purchase Due",
            data: data.data[1],
          },
        ],
        xaxis: {
          type: "datetime",
          categories: data.data[2],
        },
        yaxis: [{
        "labels": {
            "formatter": function (val) {
                return (val).toLocaleString();
            }
        }
    }],
      });
    });
  });

  var options3 = {
    grid: {
      show: false,
    },
    series: [],
    chart: {
      height: 350,
      type: "area",
    },
    dataLabels: {
      enabled: false,
    },
    stroke: {
      curve: "smooth",
    },
    theme: {
      palette: 'palette5', 
    },
    title: {
          text: "(Sell = Payable Amount)",
          rotate: -90,
          offsetX: 500,
          offsetY: 0,
          style: {
              fontSize: '12px',
              fontFamily: 'Helvetica, Arial, sans-serif',
              fontWeight: 600,
              cssClass: 'apexcharts-yaxis-title',
          },
      },
    xaxis: {
      type: "datetime",
      categories: [],
    },
    noData: {
      text: "Loading...",
    },
    tooltip: {
      x: {
        format: "dd/MM/yy",
      },
    },
  };

  $("#chart3", function () {
    var url = "/demohome/saleschart?start=" + start + "&end=" + end;
    axios({
      method: "GET",
      url: url,
    }).then(function (response) {
    //   console.log(response);
      chart3.updateOptions({
        series: [
          {
            name: "sell",
            data: response.data[0],
          },
          {
            name: "sell Due",
            data: response.data[1],
          },
        ],
        xaxis: {
          type: "datetime",
          categories: response.data[2],
        },
        yaxis: [{
        "labels": {
            "formatter": function (val) {
                return (val).toLocaleString();
            }
        }
        }],
      });
    });
  });

  var chart2 = new ApexCharts(document.querySelector("#chart2"), options2);
  chart2.render();
  var chart3 = new ApexCharts(document.querySelector("#chart3"), options3);
  chart3.render();
}
var SI_SYMBOL = ["", "k", "M", "G", "T", "P", "E"];
function __intToString(number, fraction) {
 
    // what tier? (determines SI symbol)
    var tier = Math.log10(Math.abs(number)) / 3 | 0;

    // if zero, we don't need a suffix
    if(tier == 0) return number;

    // get suffix and determine scale
    var suffix = SI_SYMBOL[tier];
    var scale = Math.pow(10, tier * 3);

    // scale the number
    var scaled = number / scale;

    // format number and add suffix
    return scaled.toFixed(2) + suffix;
  }

function __number_format(number, decimals, dec_point, thousands_sep) {
  // Strip all characters but numerical ones.
  number = (number + "").replace(/[^0-9+\-Ee.]/g, "");
  var n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    sep = typeof thousands_sep === "undefined" ? "," : thousands_sep,
    dec = typeof dec_point === "undefined" ? "." : dec_point,
    s = "",
    toFixedFix = function (n, prec) {
      var k = Math.pow(10, prec);
      return "" + Math.round(n * k) / k;
    };
  // Fix for IE parseFloat(0.55).toFixed(0) = 0;
  s = (prec ? toFixedFix(n, prec) : "" + Math.round(n)).split(".");
  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
  }
  if ((s[1] || "").length < prec) {
    s[1] = s[1] || "";
    s[1] += new Array(prec - s[1].length + 1).join("0");
  }
  return s.join(dec);
}

function nFormatter(num) {
  if (num >= 1000000000) {
    return (num / 1000000000).toFixed(1).replace(/\.0$/, "") + "G";
  }
  if (num >= 1000000) {
    return (num / 1000000).toFixed(1).replace(/\.0$/, "") + "M";
  }
  if (num >= 1000) {
    return (num / 1000).toFixed(1).replace(/\.0$/, "") + "K";
  }
  return num;
}
