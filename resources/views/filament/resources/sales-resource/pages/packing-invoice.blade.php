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
  </head>
<body>


<div class="card col-md-8 col-md-offset-2">
	<div class="card-body">
		<div class="card-body table-responsive p-0">
            <table class="table" style="border: none !important;">
				<tbody>
                    <tr class="hidden-print">
                        <td colspan="4" class="text-center"><button onclick="window.print();" class="btn btn-primary"><i class="dripicons-print"></i>Print</button></td>
                    </tr>
                     <tr>
						<th colspan="2" style="font-size: 13px;">{{ $sale->warehouse?->name ?? "" }} <br> {{ $sale->warehouse?->address ?? "" }}</th>
						<th style="font-size: 13px;">INVOICE NO</th>
                        <th style="font-size: 13px;">{{ $sale->reference_number }}</th>
					</tr>
 
                    <tr>
						<th colspan="2" style="font-size: 13px;"></th>
						<th style="font-size: 13px;">Date</th>
                        <th style="font-size: 13px;">{{$sale->transaction_date}}</th>
					</tr>

                    <tr>
						<th colspan="2" style="font-size: 13px;"><b>Customer</b> 
                        @if ($sale->customer?->company_name)
                            <br> {{$sale->customer?->company_name}}
                        @else
                            <br> {{$sale->customer?->name}} 
                        @endif
                        
                        <br> {{$sale->customer?->email}} <br> {{$sale->customer?->phone_number}} </th>
						<th colspan="2" style="font-size: 13px;"><b> Shipping Address: </b> <br> {{ $sale->delivery?->shipping_address ?? ""}}</th>
					</tr>
				</tbody>
			</table>
			<table class="table table-table table-bordered">
				<thead>
                    <tr class="bg-info">
						<th>#</th>
						<th>Product</th>
						<th>Quantity</th>
					</tr>
				</thead>
				<tbody>
                    @php
                        $count = count($sale->saleitem);
                    @endphp
                    @if ($count > 10)

                        @foreach ($sale->saleitem as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->qty }} {{ $item->unit->code ?? "" }}</td>
                            </tr>
                        @endforeach

                        @else

                        @for ($x = 0; $x <= 9; $x++)

                            <tr>
                                <td>
                                    @if (isset($sale->saleitem[$x]->product_name))
                                        {{ $x + 1 }}
                                    @endif
                                </td>
                                <td style="font-size: 12px;">{{ $sale->saleitem[$x]->product_name ?? "" }}</td>
                                <td style="font-size: 12px;text-align: right">{{ $sale->saleitem[$x]->qty ?? "" }}  {{ $sale->saleitem[$x]->unit?->code ?? "" }}</td>
                            </tr>
                            
                        @endfor
                        
                    @endif

                    <tr>
                    <td colspan="3" class="text-center">
                        Thank you for your puchase, come back another time 
                    </td>
                  </tr>
                    
                  <tr>
                    <td colspan="3" class="text-center">
                        <?php echo '<img style="margin-top:10px;" src="data:image/png;base64,' .DNS1D::getBarcodePNG($sale->reference_number, 'C128') . '" width="300" alt="barcode"   />';?>
                    <br>
                    <?php echo '<img style="margin-top:10px;" src="data:image/png;base64,' .DNS2D::getBarcodePNG($sale->reference_number, 'QRCODE') . '" alt="barcode"   />';?>
                    </td>
                  </tr>
				</tbody>
			</table>
            <div class="text-center" style="margin:30px 0 50px;">
            <small>Generated By {{ $pos->company?->name ?? "" }}.
            Developed By Oguases IT Solutions</strong></small>
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