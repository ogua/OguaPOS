<?php

namespace App\Http\Controllers;

use App\Models\Cashregister;
use App\Models\Companyinfo;
use App\Models\Delivery;
use App\Models\Invoice as Modelinvoice;
use App\Models\Payment;
use App\Models\Possettings;
use App\Models\Sales;
use App\Models\SalesItems;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use LaravelDaily\Invoices\Invoice;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use LaravelDaily\Invoices\Classes\Party;
use Illuminate\Support\Str;

class WebController extends Controller
{
    public function downloadorder($id)
    {
        $data = SalesOrder::where('ref', $id)->first();
        return view('download.order', compact('data'));
    }

    public function printlabel(Request $request)
    {

        $productname = $request->get('productname');
        $variation = $request->get('variation');
        $productPrice = $request->get('productPrice');
        $exTax = $request->get('exTax');
        $businessname = $request->get('businessname');
        $expirydate = $request->get('expirydate');
        $packingdate = $request->get('packingdate');
        $incTax = $request->get('incTax');

        $products = json_decode($request->get('data'));


        //dd($request);

        return view('label.label-print', compact(
            'productname',
            'variation',
            'productPrice',
            'exTax',
            'businessname',
            'expirydate',
            'packingdate',
            'incTax',
            'products'
        ));
    }


    public function printcashregister(Cashregister $record)
    {
        $datefrom = date('Y-m-d', strtotime($record->created_at));
        $dateto = date('Y-m-d', strtotime($record->closed_at));

        $salesSummary = DB::table('sales')
            //->whereBetween('sales.transaction_date',[$datefrom,$dateto])
            ->where('sales.transaction_date', '>=', $datefrom)
            ->where('sales.transaction_date', '<=', $datefrom)
            ->join('payments', 'sales.id', '=', 'payments.sale_id')
            ->where('sales.cash_register_id', $record->id)
            ->select('payments.paying_method', DB::raw('SUM(payments.amount) as total_amount'))
            ->groupBy('payments.paying_method')
            ->get();

        // Transform the results into an associative array
        $salesSummaryArray = $salesSummary->pluck('total_amount', 'paying_method')->toArray();

        // dd($salesSummaryArray);

        $salesbrandSummary = DB::table('sales')
            //->whereBetween('sales.transaction_date',[$datefrom,$dateto])
            ->where('sales.transaction_date', '>=', $datefrom)
            ->where('sales.transaction_date', '<=', $datefrom)
            ->join('sales_items', 'sales.id', '=', 'sales_items.sale_id')
            ->join('products', 'sales_items.product_id', '=', 'products.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->where('sales.cash_register_id', $record->id)
            ->select(
                DB::raw('COALESCE(brands.name, "-") as brand_name'),
                DB::raw('SUM(sales_items.qty) as total_qty'),
                DB::raw('SUM(sales_items.total) as brand_total'),
            )
            ->groupBy('brands.name')
            ->get();

        // Convert to associative array for easier handling in the view
        $salesSummaryArrayAssoc = $salesbrandSummary->mapWithKeys(function ($item) {
            return [$item->brand_name => $item->total_qty . ',' . $item->brand_total];
        })->toArray();

        $pos = Possettings::where('warehouse_id', $record->warehouse_id)->first();

        return view('print.cash-register', compact('pos', 'salesSummaryArrayAssoc', 'record', 'datefrom', 'dateto', 'salesSummaryArray', 'salesSummaryArray'));
    }

    public function check()
    {
        $sale = SalesItems::select('product_name', 'product_id', DB::raw('sum(qty) as sold_qty'))
            ->whereDate('sales_items.created_at', '>=', date("Y") . '-01-01')
            ->whereDate('sales_items.created_at', '<=', date("Y") . '-12-31')
            ->groupBy('product_id', 'variant_id', 'product_name')
            ->orderBy('sold_qty', 'desc')
            ->take(5)
            ->get();

        dd($sale);
    }

    public function downloaddelivery($id)
    {
        $data = Delivery::where('ref', $id)->first();
        return view('download.delivery', compact('data'));
    }


    public function invoice(Modelinvoice $record)
    {
        // dd($record);

        $companyinfo = Companyinfo::first();

        $customer = new Buyer([
            'name'          => $record->client?->name,
            'phone'          => $record->client?->phone,
            'custom_fields' => [
                'address' => $record->client?->address,
            ],
        ]);

        $client = new Party([
            'name'          => $companyinfo?->name,
            'phone'         => $companyinfo?->phone,
            'custom_fields' => [
                'location'         => $companyinfo?->location,
                'other phone'        =>  $companyinfo?->other_phone,
                'other email' => $companyinfo?->other_email,
            ],
        ]);

        $items = [];

        $dcnttype = $record->order_discount_type;

        if ($dcnttype == "Flat") {
            $dsct = round(((int) $record->order_discount_value / (int) $record->total_price) * 100, 2);
        } else {
            $dsct = $record->order_discount_value ?? 0;
        }

        foreach ($record->orderitems as $item) {
            $items[] = InvoiceItem::make($item->product_name)->pricePerUnit($item->unit_price)->quantity($item->qty)->discount($item->discount ?? 0)->tax($item->tax);
        }

        $note = $record->terms;

        $invoice = Invoice::make()
            ->series($record->reference)
            ->buyer($customer)
            ->seller($client)
            ->currencySymbol('₵')
            ->currencyCode('GH₵')
            ->currencyFormat('{SYMBOL}{VALUE}')
            ->currencyDecimalPoint(',')
            ->totalDiscount($record->order_discount_value)
            ->taxableAmount($record->order_tax_value ?? 0)
            ->shipping($record->shipping_cost ?? 0)
            ->notes($note)
            ->filename('Invoice-for-' . Str::slug($customer->name))
            //->logo(Storage::path($companyinfo->logo))
            ->addItems($items)
            ->save('public');

        $link = $invoice->url();
        // Then send email to party with link

        return $invoice->stream();
    }


    public function sendinvoice(Modelinvoice $record)
    {
        // dd($record);

        $companyinfo = Companyinfo::first();

        $customer = new Buyer([
            'name'          => $record->client?->name,
            'phone'          => $record->client?->phone,
            'custom_fields' => [
                'address' => $record->client?->address,
            ],
        ]);

        $client = new Party([
            'name'          => $companyinfo?->name,
            'phone'         => $companyinfo?->phone,
            'custom_fields' => [
                'location'         => $companyinfo?->location,
                'other phone'        =>  $companyinfo?->other_phone,
                'other email' => $companyinfo?->other_email,
            ],
        ]);

        $items = [];

        $dcnttype = $record->order_discount_type;

        if ($dcnttype == "Flat") {
            $dsct = round(((int) $record->order_discount_value / (int) $record->total_price) * 100, 2);
        } else {
            $dsct = $record->order_discount_value ?? 0;
        }

        foreach ($record->orderitems as $item) {
            $items[] = InvoiceItem::make($item->product_name)->pricePerUnit($item->unit_price)->quantity($item->qty)->discount($item->discount ?? 0)->tax($item->tax);
        }

        $note = $record->terms;

        $invoice = Invoice::make()
            ->series($record->reference)
            ->buyer($customer)
            ->seller($client)
            ->currencySymbol('₵')
            ->currencyCode('GH₵')
            ->currencyFormat('{SYMBOL}{VALUE}')
            ->currencyDecimalPoint(',')
            ->discountByPercent($dsct)
            ->taxableAmount($record->order_tax_rate ?? 0)
            ->shipping($record->shipping_cost ?? 0)
            ->notes($note)
            ->filename('Invoice-for-' . Str::slug($customer->name))
            //->logo(Storage::path($companyinfo->logo))
            ->addItems($items)
            ->save('public');

        $link = $invoice->url();

        $email = $record->client?->email;

        Mail::send('invoice', compact('link'), function ($message) use ($email) {
            $message->to($email)
                ->subject("Your Invoice");
            $message->from('alert@salesdashboard.com');
        });

        return true;
    }



    public function delivery(Delivery $record)
    {
        // dd($record);

        $companyinfo = Companyinfo::first();

        $customer = new Buyer([
            'name'          => $record->order?->customer?->name,
            'phone'          => $record->order?->customer?->phone_number,
            'custom_fields' => [
                'address' => $record->order?->customer?->address,
            ],
        ]);

        $client = new Party([
            'name'          => $companyinfo?->name,
            'phone'         => $companyinfo?->phone,
            'custom_fields' => [
                'location'         => $companyinfo?->location,
                'other phone'        =>  $companyinfo?->other_phone,
                'other email' => $companyinfo?->other_email,
            ],
        ]);

        $items = [];

        foreach ($record->order?->saleitem as $item) {
            $items[] = InvoiceItem::make($item->product_name)->pricePerUnit($item->unit_price)->quantity($item->qty)->discount($item->discount ?? 0)->tax($item->tax);
        }

        $dcnttype = $record->order?->order_discount_type;

        if ($dcnttype == "Flat") {
            $dsct = round(((int) $record->order?->order_discount_value / (int) $record->order?->total_price) * 100, 2);
        } else {
            $dsct = $record->order?->order_discount_value ?? 0;
        }

        $invoice = Invoice::make()
            ->series($record->order?->reference_number)
            ->buyer($customer)
            ->seller($client)
            ->currencySymbol('₵')
            ->currencyCode('GH₵')
            ->currencyFormat('{SYMBOL}{VALUE}')
            ->currencyDecimalPoint(',')
            ->discountByPercent($dsct)
            //->taxRate($record->order?->order_tax_value ?? 0)
            ->shipping($record->order?->shipping_cost ?? 0)
            // ->notes($note)
            ->filename('delivery-' . Str::slug($record->order?->reference_number))
            //->logo(Storage::path($companyinfo->logo))
            ->addItems($items);

        return $invoice->stream();
    }


    public function order(SalesOrder $record)
    {
        // dd($record);

        $companyinfo = Companyinfo::first();

        $customer = new Buyer([
            'name'          => $record->client?->name,
            'phone'          => $record->client?->phone,
            'custom_fields' => [
                'address' => $record->client?->address,
            ],
        ]);

        $client = new Party([
            'name'          => $companyinfo?->name,
            'phone'         => $companyinfo?->phone,
            'custom_fields' => [
                'location'         => $companyinfo?->location,
                'other phone'        =>  $companyinfo?->other_phone,
                'other email' => $companyinfo?->other_email,
            ],
        ]);

        $items = [];

        foreach ($record->orderitems as $item) {
            $items[] = InvoiceItem::make($item->product->name)->pricePerUnit($item->unitpx)->quantity($item->qty);
        }

        //$note = $record->terms;

        $invoice = Invoice::make()
            ->template('order')
            ->series('INV')
            ->buyer($customer)
            ->seller($client)
            ->currencySymbol('₵')
            ->currencyCode('GH₵')
            ->currencyFormat('{SYMBOL}{VALUE}')
            ->currencyDecimalPoint(',')
            ->discountByPercent($record->discount)
            ->taxRate($record->tax)
            ->shipping(0)
            // ->notes($note)
            ->filename('saleorder-for-' . Str::slug($customer->name))
            //->logo(Storage::path($companyinfo->logo))
            ->addItems($items);

        return $invoice->stream();
    }


    public function pos_invoice($salesid)
    {

        $sale = Sales::where('id', $salesid)->first();

        $pos = Possettings::where('warehouse_id', $sale->warehouse_id)->first();

        $f = new \NumberFormatter(Config::get('app.locale'), \NumberFormatter::SPELLOUT);
        $numberInWords = $f->format($sale->grand_total ?? 0);

        return view('print.pos-invoice', compact('sale', 'pos', 'numberInWords'));
    }


    public function sale_packing_slip($salesid)
    {

        $sale = Sales::with(['unit', 'delivery', 'saleitem'])->where('id', $salesid)->first();

        $pos = Possettings::where('warehouse_id', $sale->warehouse_id)->first();

        return view('filament.resources.sales-resource.pages.packing-invoice', compact('sale', 'pos'));
    }
}
