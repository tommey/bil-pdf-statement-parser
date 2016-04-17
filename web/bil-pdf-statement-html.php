<?php
/**
 * Example script - shows how to parse and output HTML from Bil.com PDF statement transaction list.
 */

use Tommey\BilPdfStatementParser\BilPdfStatementParser;

require __DIR__ . '/../vendor/autoload.php';

$directory = __DIR__ . '/../docs';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HTML Example | BIL.com PDF Statement Parser</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/r/bs-3.3.5/jq-2.1.4,dt-1.10.8/datatables.min.css"/>
</head>
<body>
    <div class="container-fluid">
        <div class="page-header">
            <h1>HTML Example <small>BIL.com PDF Statement Parser</small></h1>
        </div>
<?php
try
{
    echo BilPdfStatementParser::createHtmlFromDirectory(
        $directory,
        [
            'id'    => 'transactions',
            'class' => 'table table-striped table-bordered'
        ]
    );
}
catch (Exception $e)
{
    http_response_code(500);

    echo '<div class="alert alert-danger" role="alert"><strong>Oh no, something went wrong!</strong> ' . $e->getMessage() . '</div>';
}
?>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/r/bs-3.3.5/jqc-1.11.3,dt-1.10.8/datatables.min.js"></script>
    <script type="text/javascript" charset="utf-8">
        Number.prototype.formatMoney = function(c, d, t){
            //noinspection JSDuplicatedDeclaration
            var n = this,
                c = isNaN(c = Math.abs(c)) ? 2 : c,
                d = d == undefined ? "." : d,
                t = t == undefined ? "," : t,
                s = n < 0 ? "-" : "",
                i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "",
                j = (j = i.length) > 3 ? j % 3 : 0;
            return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
        };
        $(document).ready(function () {
            var $transactions = $('#transactions');
            $transactions
                .append('<tfoot><tr><td>Total</td><td></td><td></td><td></td></tr></tfoot>');
            $transactions
                .DataTable({
                    'order':          [[1, "desc"]],
                    'footerCallback': function () {
                        var api = this.api();

                        function reducer(a, b)
                        {
                            return parseFloat(a) + parseFloat(b);
                        }

                        // Total over all pages
                        total = api
                            .column(2)
                            .data()
                            .reduce(reducer, 0.0);

                        // Total over this page
                        pageTotal = api
                            .column(2, {page: 'current'})
                            .data()
                            .reduce(reducer, 0);

                        // Update footer
                        $(api.column(2).footer()).html(pageTotal.formatMoney(2, ',', '.'));
                        $(api.column(3).footer()).html('Total: € ' + total.formatMoney(2, ',', '.'));
                    }
                });
        });
    </script>
</body>
</html>
