<link rel="stylesheet" href="{{ URL::to('css/bootstrap.mins.css')}}">

<div class="text-center">
  <h4>
  SALES DASHBOARD <br>
  ADDRESS <br>
  PHONE NUMBER <br>
  EMAIL 
  </h4>
</div>

<div class="card">
	<div class="card-header">
		<h4></h4>
	</div>
	<div class="card-body">
		<div class="card table-card card-info card-outline">
		</div>
		<div class="card-body table-responsive p-0">
			<table class="table table-hover table-table table-head-fixed">
				<thead>
					<tr>
						<th colspan="2">Customer</th>
						<th></th>
						<th colspan="2">Shipping</th>
					</tr>

                    <tr>
						<th colspan="2">
                        
                        </th>
						<th></th>
						<th colspan="2">
                        </th>
					</tr>

                    <tr>
						<th>ID</th>
						<th>Product</th>
						<th>Price</th>
						<th>Quantity</th>
						<th>Total</th>
					</tr>
				</thead>
				<tbody>
                @foreach($data->orderitems as $item)
					<tr>
						<td>{{ $loop->iteration }}</td>
						<td>{{ $item->product?->name }}</td>
						<td>{{ $data->currency.number_format($item->unitpx,2) }}</td>
						<td>{{ $item->qty }}</td>
						<td>{{ $data->currency.number_format($item->amount,2) }}</td>
					</tr>
                @endforeach
                  <tr>
                    <td colspan="3"></td>
                    <td>Sub Total</td>
                    <td>{{ $data->currency.number_format($data->subtotal,2) }}</td>
                  </tr>

                  <tr>
                    <td colspan="3"></td>
                    <td>Discount</td>
                    <td>{{ $item->discount ?? 0 }}%</td>
                  </tr>

                  <tr>
                    <td colspan="3"></td>
                    <td>Tax</td>
                    <td>{{ $item->tax }}</td>
                  </tr>

                  <tr>
                    <td colspan="3"></td>
                    <td>Total</td>
                    <td>{{ $data->currency.number_format($data->total,2) }}</td>
                  </tr>
				</tbody>
			</table>
		</div>
	</div>
</div>