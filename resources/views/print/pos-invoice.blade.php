<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/png" href="{{ asset('storage') }}/{{ $pos->company?->logo }}" />
    <title>{{$pos->company?->name ?? ""}}</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">

    <style type="text/css">
        * {
            font-size: 14px;
            line-height: 24px;
            font-family: 'Ubuntu', sans-serif;
            text-transform: capitalize;
        }
        .btn {
            padding: 7px 10px;
            text-decoration: none;
            border: none;
            display: block;
            text-align: center;
            margin: 7px;
            cursor:pointer;
        }

        .btn-info {
            background-color: #999;
            color: #FFF;
        }

        .btn-primary {
            background-color: #6449e7;
            color: #FFF;
            width: 100%;
        }
        td,
        th,
        tr,
        table {
            border-collapse: collapse;
        }
        tr {border-bottom: 1px dotted #ddd;}
        td,th {padding: 7px 0;width: 50%;}

        table {width: 100%;}
        tfoot tr th:first-child {text-align: left;}

        .centered {
            text-align: center;
            align-content: center;
        }
        small{font-size:11px;}

        @media print {
            * {
                font-size:12px;
                line-height: 20px;
            }
            td,th {padding: 5px 0;}
            .hidden-print {
                display: none !important;
            }
            @page { margin: 1.5cm 0.5cm 0.5cm; }
            @page:first { margin-top: 0.5cm; }
            tbody::after {
                content: ''; display: block;
                page-break-after: always;
                page-break-inside: avoid;
                page-break-before: avoid;
            }
        }
    </style>
  </head>
<body>

<div style="max-width:400px;margin:0 auto">
    @if(preg_match('~[0-9]~', url()->previous()))
        @php $url = '../../pos'; @endphp
    @else
        @php $url = url()->previous(); @endphp
    @endif
    <div class="hidden-print">
        <table>
            <tr>
                <td colspan="2"><button onclick="window.print();" class="btn btn-primary"><i class="dripicons-print"></i>Print</button></td>
            </tr>
        </table>
        <br>
    </div>

    <div id="receipt-data">
        <div class="centered">
            @if($pos->company?->logo)
                <img src="{{ asset('storage') }}/{{ $pos->company?->logo }}" height="42" width="50" style="margin:10px 0;filter: brightness(0);">
            @endif

            <h2>{{ $pos->company?->name ?? ""}}</h2>

            <p>Address: {{$pos->company?->address}}
                <br>Phone Number: {{$pos->company?->phone}}
            </p>
        </div>
        <p>Date: {{$sale->transaction_date}}<br>
            Reference: {{$sale->reference_number}}<br>
            Customer: {{$sale->customer?->name}}
        </p>
        <table class="table-data">
            <tbody>
                <?php
                    $total_product_tax = 0;
                ?>
                @foreach(collect($sale->saleitem) as $key => $product_sale_data)
                <?php
                    $lims_product_data = \App\Models\Product::find($product_sale_data->product_id);

                    if($product_sale_data->variant_id) {
                        $variant_data = \App\Models\Product_variation::find($product_sale_data->variant_id);
                        $product_name = $lims_product_data->product_name.' ['.$variant_data->item_code.']';
                    }else{
                        $product_name = $lims_product_data->product_name;
                    }

                    if($product_sale_data->imei_number) {
                        $product_name .= '<br>'.trans('IMEI or Serial Numbers').': '.$product_sale_data->imei_number;
                    }
                ?>
                <tr>
                    <td colspan="2">
                        {!!$product_name!!}
                        <br>{{$product_sale_data->qty}} x {{number_format((float)($product_sale_data->total / $product_sale_data->qty), 2, '.', '')}}

                        @foreach ($lims_product_data->taxes as $tax)
                            <?php $total_product_tax += $tax->rate ?>
                            Tax ({{$tax->rate}}%): {{$tax->name}}]
                        @endforeach

                    </td>
                    <td style="text-align:right;vertical-align:bottom">{{number_format((float)$product_sale_data->total, 2, '.', '')}}</td>
                </tr>
                @endforeach

            <!-- <tfoot> -->
                <tr>
                    <th colspan="2" style="text-align:left">Total</th>
                    <th style="text-align:right">{{number_format((float)$sale->total_price, 2, '.', '')}}</th>
                </tr>

               @if($pos->invoice_format == 'Indian GST' && $pos->state == "Home State")
                <tr>
                    <td colspan="2">IGST</td>
                    <td style="text-align:right">{{number_format((float)$total_product_tax, 2, '.', '')}}</td>
                </tr>
                @elseif($pos->invoice_format == 'Indian GST' && $pos->state == "Buyer State")
                <tr>
                    <td colspan="2">SGST</td>
                    <td style="text-align:right">{{number_format((float)($total_product_tax / 2), 2, '.', '')}}</td>
                </tr>
                <tr>
                    <td colspan="2">CGST</td>
                    <td style="text-align:right">{{number_format((float)($total_product_tax / 2), 2, '.', '')}}</td>
                </tr>
                @endif

                @if($sale->order_tax > 0)
                <tr>
                    <th colspan="2" style="text-align:left">Order Tax</th>
                    <th style="text-align:right">{{number_format((float)$sale->order_tax, 2, '.', '')}}</th>
                </tr>
                @endif
                @if($sale->total_discount > 0)
                <tr>
                    <th colspan="2" style="text-align:left">Order Discount</th>
                    <th style="text-align:right">{{number_format((float)$sale->total_discount, 2, '.', '')}}</th>
                </tr>
                @endif
                @if($sale->coupon_discount > 0)
                <tr>
                    <th colspan="2" style="text-align:left">Coupon Discount</th>
                    <th style="text-align:right">{{number_format((float)$sale->coupon_discount, 2, '.', '')}}</th>
                </tr>
                @endif

                @if($sale->shipping_cost > 0)
                <tr>
                    <th colspan="2" style="text-align:left">.Shipping Cost</th>
                    <th style="text-align:right">{{number_format((float)$sale->shipping_cost, 2, '.', '')}}</th>
                </tr>
                @endif
                <tr>
                    <th colspan="2" style="text-align:left">Grand Total</th>
                    <th style="text-align:right">{{number_format((float)$sale->grand_total, 2, '.', '')}}</th>
                </tr>
                <tr>
                    <th class="centered" colspan="3">In Words: <span>{{str_replace("-"," ",$numberInWords)}}</span> <span>{{$pos->currncy->name ?? 'GHC'}}</span></th>
                </tr>
            </tbody>
            <!-- </tfoot> -->
        </table>
        <table>
            <tbody>
                @foreach($sale->payments as $payment_data)
                <tr style="background-color:#ddd;">
                    <td style="padding: 5px;width:30%">Paid By: {{$payment_data->paying_method}}</td>
                    <td style="padding: 5px;width:40%">Amount: {{number_format((float)$payment_data->amount, 2, '.', '')}}</td>
                    <td style="padding: 5px;width:30%">Change: {{number_format((float)$payment_data->change, 2, '.', '')}}</td>
                </tr>
                @endforeach
                <tr><td class="centered" colspan="3">Thank You For Shopping With Us. Please Come Again</td></tr>
                <tr>
                    <td class="centered" colspan="3">
                    <?php echo '<img style="margin-top:10px;" src="data:image/png;base64,' .DNS1D::getBarcodePNG($sale->reference_number, 'C128') . '" width="300" alt="barcode"   />';?>
                   <!--- //echo //'<img style="margin-top:10px;" src="data:image/png;base64,' .DNS2D::getBarcodePNG($sale->reference_number, 'QRCODE') . '" alt="barcode"   />';--->
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="centered" style="margin:30px 0 50px">
            <small>Invoice Generated By {{ $pos->company?->name ?? "" }}.
            Developed By Oguases IT Solutions</strong></small>
        </div>
    </div>
</div>

<script type="text/javascript">
    localStorage.clear();
    function auto_print() {
        window.print()
    }
    setTimeout(auto_print, 1000);
</script>

</body>
</html>
