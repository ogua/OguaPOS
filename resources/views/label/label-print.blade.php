 <div id="label-content">
           <table align="center" style="border-spacing: 0.1875in 0in; overflow: hidden !important;">
                <tbody>
                    @foreach ($products as $product)
                     @php
                         $qty = $product->product_label;
                         $i = 0;
                     @endphp

                     @while ($i < $qty)

                        @if ($i % 2 == 0)
                            <tr>
                        @endif
                         <td align="center" style="width:164px;height:100%;font-size:10px;text-align:center">
                            @if ($businessname == "true")
                                {{ $product->company_name }}
                            @endif

                            <br>

                            @if ($productname == "true")
                                {{ $product->product_name }}
                            @endif
                            <br>
                            @if ($variation == "true" && $product->size)
                                Size: {{ $product->size }}
                            @endif
                            <br>
                             @if ($productPrice == "true" && $exTax == "true")
                             
                                Price: GHC {{ number_format($product->exclude_tax,2) }}

                            @else

                                Price: GHC {{ number_format($product->include_tax,2) }}
                             @endif

                             @php
                                 $code =  $product->product_code ?? "";
                                 $barcode =  strtolower($product->barcode);
                             @endphp
                             <br>
                             <?php echo '<img style="" src="data:image/png;base64,' .DNS1D::getBarcodePNG($code, $barcode) . '" width="300" alt="barcode"   />';?>
                            <br>
                            {{ $code }}
                            <br>
                        </td>

                        @if ($i % 2 != 0)
                            </tr>
                        @endif

                        @php
                            $i++;
                        @endphp
                     @endwhile
                       
                    @endforeach
        </tbody>
    </table>
</div>
<style type="text/css">
	@media  print{
		
		table{
			page-break-after: always;
		}
		@page  {
		size: 8.5in 11in;

		/*width: 8.5000in !important;*/
		/*height:11.0000in !important ;*/
		margin-top: 0.5in !important;
		margin-bottom: 0.5in !important;
		margin-left: 0.125in !important;
		margin-right: 0.125in !important;
	}
	}
</style>
<script>window.print()</script>