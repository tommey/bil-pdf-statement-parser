<?php
/**
 * Example script - shows how to parse and output HTML from Bil.com PDF statement transaction list.
 */

use Tommey\BilPdfStatementParser\BilPdfStatementParser;
use Tommey\BilPdfStatementParser\Formatter\HighchartsDailyTransactionListFormatter;
use Tommey\BilPdfStatementParser\Formatter\HtmlTransactionListFormatter;

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
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/r/bs-3.3.5/jq-2.1.4,dt-1.10.8/datatables.min.css"/>
    <style>
        .chart {
            min-width: 320px;
            max-width: 100%;
            height: 300px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="page-header">
            <h1>HTML Example <small>BIL.com PDF Statement Parser</small></h1>
        </div>
        <div id="charts"></div>
        <hr>
<?php
try
{
    $parser         = BilPdfStatementParser::createDirectoryParser();
    $htmlFormatter  = new HtmlTransactionListFormatter(
        [
            'id'    => 'transactions',
            'class' => 'table table-striped table-bordered'
        ]
    );
    $chartFormatter = new HighchartsDailyTransactionListFormatter();
    $transactions   = $parser->parse($directory);

    echo $htmlFormatter->format($transactions);
?>
    </div>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
    <script type="text/javascript" src="//cdn.datatables.net/r/bs-3.3.5/jqc-1.11.3,dt-1.10.8/datatables.min.js"></script>
    <script type="text/javascript" src="//code.highcharts.com/4.2.4/highcharts.js"></script>
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

            /**
             * In order to synchronize tooltips and crosshairs, override the
             * built-in events with handlers defined on the parent element.
             */
            $('#charts').bind('mousemove touchmove touchstart', function (e) {
                var chart,
                    point,
                    i,
                    event;

                for (i = 0; i < Highcharts.charts.length; i = i + 1) {
                    chart = Highcharts.charts[i];
                    event = chart.pointer.normalize(e.originalEvent); // Find coordinates within the chart
                    point = chart.series[0].searchPoint(event, true); // Get the hovered point

                    if (point) {
                        point.onMouseOver(); // Show the hover marker
                        chart.tooltip.refresh(point); // Show the tooltip
                        chart.xAxis[0].drawCrosshair(event, point); // Show the crosshair
                    }
                }
            });
            /**
             * Override the reset function, we don't need to hide the tooltips and crosshairs.
             */
            Highcharts.Pointer.prototype.reset = function () {
                return undefined;
            };

            /**
             * Synchronize zooming through the setExtremes event handler.
             */
            function syncExtremes(e) {
                var thisChart = this.chart;

                if (e.trigger !== 'syncExtremes') { // Prevent feedback loop
                    Highcharts.each(Highcharts.charts, function (chart) {
                        if (chart !== thisChart) {
                            if (chart.xAxis[0].setExtremes) { // It is null while updating
                                chart.xAxis[0].setExtremes(e.min, e.max, undefined, false, { trigger: 'syncExtremes' });
                            }
                        }
                    });
                }
            }

            // Get the data.
            function initCharts(activity) {
                $.each(activity.datasets, function (i, dataset) {

                    // Add X values
                    dataset.data = Highcharts.map(dataset.data, function (val, j) {
                        return [activity.xData[j], val];
                    });

                    $('<div class="chart">')
                        .appendTo('#charts')
                        .highcharts({
                                        chart: {
                                            marginLeft: 40, // Keep all charts left aligned
                                            spacingTop: 20,
                                            spacingBottom: 20,
                                            zoomType: 'x'
                                        },
                                        title: {
                                            text: dataset.name,
                                            align: 'left',
                                            margin: 0,
                                            x: 30
                                        },
                                        credits: {
                                            enabled: false
                                        },
                                        legend: {
                                            enabled: false
                                        },
                                        xAxis: {
                                            crosshair: true,
                                            events: {
                                                setExtremes: syncExtremes
                                            },
                                            type: 'datetime'
                                        },
                                        yAxis: {
                                            title: {
                                                text: null
                                            }
                                        },
                                        tooltip: {
                                            positioner: function () {
                                                return {
                                                    x: this.chart.chartWidth - this.label.width, // right aligned
                                                    y: -1 // align to title
                                                };
                                            },
                                            formatter: function () {
                                                var date = new Date(this.key),
                                                    monthNames = ["January", "February", "March", "April", "May", "June",
                                                                  "July", "August", "September", "October", "November", "December"
                                                ];

                                                return [date.getDate(), monthNames[date.getMonth()], date.getFullYear()].join(' ')
                                                       + ' @ ' + this.y.formatMoney(2, ',', '.') + ' EUR';
                                            },
                                            borderWidth: 0,
                                            backgroundColor: 'none',
                                            pointFormat: '{point.y}',
                                            headerFormat: '',
                                            shadow: false,
                                            style: {
                                                fontSize: '18px'
                                            },
                                            valueDecimals: dataset.valueDecimals
                                        },
                                        series: [{
                                            data: dataset.data,
                                            name: dataset.name,
                                            type: dataset.type,
                                            color: Highcharts.getOptions().colors[i],
                                            fillOpacity: 0.3,
                                            tooltip: {
                                                valueSuffix: ' ' + dataset.unit
                                            }
                                        }]
                                    });
                });
            }
            initCharts(<?= $chartFormatter->format($transactions) ?>);
        });
    </script>

<?php
}
catch (Exception $e)
{
    http_response_code(500);

?>
    <div class="alert alert-danger" role="alert"><strong>Oh no, something went wrong!</strong> <?= $e->getMessage() ?></div>';
<?php
}
?>
</body>
</html>
