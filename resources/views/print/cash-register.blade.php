<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/png" href="{{ asset('storage') }}/{{ $pos->company?->logo }}" />
    <title>{{$pos->company?->name ?? ""}} Cash Register</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">
    <link rel="stylesheet" href="{{ URL::to('css/bootstrap.mins.css')}}">

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

        small{font-size:11px;}
    </style>
<body>

<div class="card col-md-8 col-md-offset-2">
	<div class="card-body">
		<div class="p-0 card-body table-responsive">
            <table class="table" style="border: none !important;">

                @php
                    $total = 0;
                    $totalsale = 0;
                @endphp

				<thead>
                     <tr>
						<th colspan="3" style="font-size: 13px; text-center;">Register Details From {{ date('d-M-Y', strtotime($datefrom)) }} To {{ date('d-M-Y', strtotime($dateto)) }}</th>
					</tr>
                    <tr>
						<th style="font-size: 13px;">Payment Method</th>
                        <th style="font-size: 13px;">Sales</th>
                        <th style="font-size: 13px;">Expenses</th>
					</tr>

                    <tr>
						<th style="font-size: 13px;">Cash in hand:</th>
                        <th style="font-size: 13px;">
                            @php
                                $total += $record->cash_in_hand ?? 0;
                            @endphp
                            GHC {{ number_format($record->cash_in_hand ?? 0,2) }}
                        </th>
                        <th style="font-size: 13px;">---</th>
					</tr>

                     <tr>
						<th style="font-size: 13px;">Cash payment:</th>
                        <th style="font-size: 13px;">
                            @if (isset($salesSummaryArray['CASH']))
                                @php
                                    $total += $salesSummaryArray['CASH'];
                                    $totalsale += $salesSummaryArray['CASH'];
                                @endphp
                                    GHC {{ number_format($salesSummaryArray['CASH'],2) }}</p>
                                @else
                                    GHC {{ number_format(0,2) }}
                            @endif
                        </th>
                        <th style="font-size: 13px;">GHC {{ number_format(0,2) }}</th>
					</tr>

                    <tr>
						<th style="font-size: 13px;">Cheque payment:</th>
                        <th style="font-size: 13px;">
                            @if (isset($salesSummaryArray['CHEQUE']))
                                @php
                                    $total += $salesSummaryArray['CHEQUE'];
                                    $totalsale += $salesSummaryArray['CHEQUE'];
                                @endphp
                                    GHC {{ number_format($salesSummaryArray['CHEQUE'],2) }}</p>
                                @else
                                    GHC {{ number_format(0,2) }}
                            @endif
                        </th>
                        <th style="font-size: 13px;">GHC {{ number_format(0,2) }}</th>
					</tr>

                    <tr>
						<th style="font-size: 13px;">Paypal payment:</th>
                        <th style="font-size: 13px;">
                            @if (isset($salesSummaryArray['PAYPAL']))
                                @php
                                    $total += $salesSummaryArray['PAYPAL'];
                                    $totalsale += $salesSummaryArray['PAYPAL'];
                                @endphp
                                    GHC {{ number_format($salesSummaryArray['PAYPAL'],2) }}</p>
                                @else
                                    GHC {{ number_format(0,2) }}
                            @endif
                        </th>
                        <th style="font-size: 13px;">GHC {{ number_format(0,2) }}</th>
					</tr>


                    <tr>
						<th style="font-size: 13px;">Gift card payment:</th>
                        <th style="font-size: 13px;">
                            @if (isset($salesSummaryArray['GIFT CARD']))
                                @php
                                    $total += $salesSummaryArray['GIFT CARD'];
                                    $totalsale += $salesSummaryArray['GIFT CARD'];
                                @endphp
                                    GHC {{ number_format($salesSummaryArray['GIFT CARD'],2) }}</p>
                                @else
                                    GHC {{ number_format(0,2) }}
                            @endif
                        </th>
                        <th style="font-size: 13px;">GHC {{ number_format(0,2) }}</th>
					</tr>


                    <tr>
						<th style="font-size: 13px;">Credit card  payment:</th>
                        <th style="font-size: 13px;">
                            @if (isset($salesSummaryArray['CREDIT CARD']))
                                @php
                                    $total += $salesSummaryArray['CREDIT CARD'];
                                    $totalsale += $salesSummaryArray['CREDIT CARD'];
                                @endphp
                                    GHC {{ number_format($salesSummaryArray['CREDIT CARD'],2) }}</p>
                                @else
                                    GHC {{ number_format(0,2) }}
                            @endif
                        </th>
                        <th style="font-size: 13px;">GHC {{ number_format(0,2) }}</th>
					</tr>



                    <tr>
						<th style="font-size: 13px;">Bank transfer payment:</th>
                        <th style="font-size: 13px;">
                            @if (isset($salesSummaryArray['BANK TRANSFER']))
                                @php
                                    $total += $salesSummaryArray['BANK TRANSFER'];
                                    $totalsale += $salesSummaryArray['BANK TRANSFER'];
                                @endphp
                                    GHC {{ number_format($salesSummaryArray['BANK TRANSFER'],2) }}</p>
                                @else
                                    GHC {{ number_format(0,2) }}
                            @endif
                        </th>
                        <th style="font-size: 13px;">GHC {{ number_format(0,2) }}</th>
					</tr>

                    <tr style="background-color: #5cb85c; color: white;">
						<th style="font-size: 13px;">Total Sales:</th>
                        <th colspan="2" style="font-size: 13px;">GHC {{ number_format($total,2) }}</th>
					</tr>


                    <<tr style="background-color: #337ab7; color: white;">
						<th style="font-size: 13px;">Total Refund:</th>
                        <th colspan="2" style="font-size: 13px;">GHC {{ number_format(0,2) }}</th>
					</tr>

                    <tr style="background-color: #5cb85c; color: white;">
						<th style="font-size: 13px;">Total Payments:</th>
                        <th colspan="2" style="font-size: 13px;">GHC {{ number_format($totalsale,2) }}</th>
					</tr>

                    <tr style="background-color: #c9302c; color: white;">
						<th style="font-size: 13px;">Total Expenses:</th>
                        <th colspan="2" style="font-size: 13px;">GHC {{ number_format(0,2) }}</th>
					</tr>
                </thead>
			</table>

            <br><br>


			<table class="table table-table table-bordered">
				<thead>
                    <tr class="bg-info">
						<th colspan="3"> Details of product sold</th>
					</tr>
                    <tr>
						<th> Brand</th>
                        <th> Quantity</th>
                        <th> Total Amount</th>
					</tr>
				</thead>
				<tbody>

                    @php
                        $totqty = 0;
                        $totpx = 0;
                    @endphp

                        @foreach ($salesSummaryArrayAssoc ?? [] as $brand => $totalQty)
                            <tr>
                                <td>{{ $brand }}</td>
                                <td>
                                    @php
                                    $tot = explode(",",$totalQty);
                                    $totqty += $tot[0];
                                    $totpx += $tot[1];
                                    @endphp
                                    {{ $tot[0] }}
                                </td>
                                <td>GHC {{ number_format($tot[1],2) }}</td>
                            </tr>
                        @endforeach

                        <tr>
                            <th>#</th>
                            <th> {{ $totqty }}</th>
                            <th> GHC {{ number_format($totpx,2) }}</th>
                        </tr>
                        <tr class="hidden-print">
                            <td colspan="3" class="text-center"><button onclick="window.print();" class="btn btn-primary"><i class="dripicons-print"></i>Print</button></td>
                        </tr>

				</tbody>
			</table>
            <div class="text-center" style="margin:30px 0 50px;">
            <small><strong>Developed By Oguases IT Solutions</strong></small>
        </div>
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
