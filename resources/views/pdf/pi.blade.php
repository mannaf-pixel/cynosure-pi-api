<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>PI {{ $pi->pi_number }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size:11px; color:#1a1a1a; }
.page { padding:20px 28px; }
.header-table { width:100%; margin-bottom:10px; padding-bottom:10px; border-bottom:2px solid {{ $company['color'] }}; }
.company-name { font-size:13px; font-weight:bold; color:{{ $company['color'] }}; }
.brand-label { font-size:11px; color:#555; margin-top:2px; font-weight:bold; }
.company-sub { font-size:10px; color:#666; line-height:1.6; margin-top:3px; }
.pi-number { font-size:13px; font-weight:bold; color:{{ $company['color'] }}; }
.pi-date { font-size:10px; color:#555; margin-top:3px; }
.pi-title { text-align:center; font-size:13px; font-weight:bold; letter-spacing:1px; border:1px solid #ddd; padding:6px; margin-bottom:10px; background:#f8f9fa; }
.customer-table { width:100%; margin-bottom:10px; border-collapse:collapse; }
.customer-table td { width:50%; vertical-align:top; padding:8px; border:1px solid #ddd; }
.box-title { font-size:10px; font-weight:bold; color:{{ $company['color'] }}; text-transform:uppercase; border-bottom:1px solid #eee; padding-bottom:3px; margin-bottom:4px; }
.box-company { font-size:11px; font-weight:bold; }
.box-sub { font-size:10px; color:#555; line-height:1.6; margin-top:2px; }
.section-title { font-size:10px; font-weight:bold; color:#fff; padding:5px 8px; margin-bottom:0; }
.section-white { background:#1E6FD9; }
.section-color { background:#E65C00; }
.section-hardware { background:#2E7D32; }
.items-table { width:100%; border-collapse:collapse; margin-bottom:10px; }
.items-table th { background:{{ $company['color'] }}; color:#fff; font-size:10px; padding:6px 7px; text-align:left; }
.items-table th.right { text-align:right; }
.items-table td { padding:5px 7px; font-size:10px; border-bottom:1px solid #eee; }
.items-table td.right { text-align:right; }
.items-table tr:nth-child(even) td { background:#f8f9fa; }
.white-header th { background:#1E6FD9; }
.color-header th { background:#E65C00; }
.hardware-header th { background:#2E7D32; }
.code { font-weight:bold; color:{{ $company['color'] }}; }
.totals-table { width:300px; float:right; border-collapse:collapse; border:1px solid #ddd; margin-bottom:10px; }
.totals-table td { padding:5px 10px; font-size:10px; border-bottom:1px solid #eee; }
.totals-table td.right { text-align:right; }
.clear { clear:both; }
.bottom-table { width:100%; border-collapse:collapse; margin-bottom:10px; }
.bottom-table td { width:50%; vertical-align:top; padding:8px; border:1px solid #ddd; }
.terms-list { padding-left:14px; margin-top:4px; }
.terms-list li { font-size:10px; color:#555; line-height:1.7; }
.sign-box { float:right; width:200px; text-align:center; border:1px solid #ddd; padding:8px; margin-bottom:10px; }
.sign-space { height:40px; border-bottom:1px solid #aaa; margin-bottom:5px; }
.footer { border-top:1px solid #ddd; padding-top:6px; text-align:center; font-size:9px; color:#999; }
.internal-tag { background:#f3e5f5; border:1px solid #9c27b0; color:#6a1b9a; font-size:9px; padding:2px 6px; display:inline-block; margin-top:3px; }
</style>
</head>
<body>
<div class="page">

<table class="header-table">
  <tr>
    <td style="width:35%;vertical-align:middle">
      @if($logo_base64)<img src="{{ $logo_base64 }}" style="max-height:60px;max-width:180px;" alt="Logo">@endif
    </td>
    <td style="width:40%;vertical-align:top">
      <div class="company-name">{{ $company['name'] }}</div>
      <div class="brand-label">{{ $company['brand_label'] }}</div>
      <div class="company-sub">
        {{ $company['address'] }}<br>
        @if($company['gstin'])GSTIN: {{ $company['gstin'] }}<br>@endif
        @if($company['phone'])Ph: {{ $company['phone'] }}@endif
        @if($company['email'])|  {{ $company['email'] }}@endif
      </div>
    </td>
    <td style="width:25%;vertical-align:top;text-align:right">
      <div class="pi-number">{{ $pi->pi_number }}</div>
      <div class="pi-date">Date: {{ \Carbon\Carbon::parse($pi->created_at)->format('d-M-Y') }}</div>
      @if($pi->status === 'ceo_approved')<div style="margin-top:4px;background:#e8f5e9;color:#2e7d32;font-size:10px;font-weight:bold;padding:2px 8px;text-align:center;">CEO APPROVED</div>@endif
      @if($show_weight)<div class="internal-tag">INTERNAL COPY</div>@endif
    </td>
  </tr>
</table>

<div class="pi-title">PROFORMA INVOICE</div>

<table class="customer-table">
  <tr>
    <td>
      <div class="box-title">Bill To</div>
      <div class="box-company">{{ $pi->customer->company_name }}</div>
      <div class="box-sub">
        Attn: {{ $pi->customer->customer_name }}<br>
        @if($pi->customer->billing_address){{ $pi->customer->billing_address }}<br>@endif
        @if($pi->customer->city || $pi->customer->state){{ implode(', ', array_filter([$pi->customer->city, $pi->customer->state])) }}<br>@endif
        @if($pi->customer->gstin)GSTIN: {{ $pi->customer->gstin }}@endif
      </div>
    </td>
    <td>
      <div class="box-title">Ship To</div>
      <div class="box-company">{{ $pi->customer->company_name }}</div>
      <div class="box-sub">
        @if($pi->customer->shipping_address){{ $pi->customer->shipping_address }}@else Same as Billing Address @endif<br>
        @if($pi->customer->mobile)Mobile: {{ $pi->customer->mobile }}<br>@endif
        Payment Terms: Advance<br>
        Delivery: 7-10 Working Days
      </div>
    </td>
  </tr>
</table>

@if($pi->salesperson_name)
<table style="width:100%;margin-bottom:10px;border-collapse:collapse;">
  <tr>
    <td style="padding:6px 8px;border:1px solid #ddd;font-size:10px;">
      <span style="font-size:10px;font-weight:bold;color:{{ $company['color'] }};text-transform:uppercase;">Sales Executive:</span>
      &nbsp;{{ $pi->salesperson_name }}
    </td>
  </tr>
</table>
@endif

@if($is_plastrong)
{{-- Plastrong — single table --}}
<table class="items-table">
  <thead>
    <tr>
      <th style="width:5%">#</th>
      <th style="width:10%">Code</th>
      <th style="width:37%">Product Description</th>
      <th class="right" style="width:10%">Pieces</th>
      <th class="right" style="width:10%">Kg</th>
      <th class="right" style="width:12%">Rate/kg</th>
      <th class="right" style="width:16%">Amount (Rs.)</th>
    </tr>
  </thead>
  <tbody>
    @foreach($pi->items as $index => $item)
    <tr>
      <td>{{ $index + 1 }}</td>
      <td class="code">{{ $item->product_code_snap }}</td>
      <td>{{ $item->product_name_snap }}</td>
      <td class="right">{{ $item->total_pieces }}</td>
      <td class="right">{{ number_format($item->total_weight, 3) }}</td>
      <td class="right">{{ number_format($item->unit_rate_snap, 2) }}</td>
      <td class="right">{{ number_format($item->line_total, 2) }}</td>
    </tr>
    @endforeach
  </tbody>
</table>

@else

{{-- WHITE PROFILES SECTION --}}
@php $whiteItems = $pi->items->filter(fn($i) => $i->item_type !== 'hardware' && $i->profile_type_snap === 'white'); @endphp
@if($whiteItems->count() > 0)
<div style="background:#1E6FD9;color:#fff;font-size:10px;font-weight:bold;padding:5px 8px;margin-bottom:0;">
  🏗️ WHITE PROFILE
</div>
<table class="items-table" style="margin-bottom:6px;">
  <thead>
    <tr class="white-header">
      <th style="width:4%">#</th>
      <th style="width:10%">Code</th>
      <th style="width:28%">Product Description</th>
      <th class="right" style="width:8%">Bundles</th>
      <th class="right" style="width:10%">Length (m)</th>
      <th class="right" style="width:7%">Pieces</th>
      @if($show_weight)<th class="right" style="width:10%">Wt. (kg)</th>@endif
      <th class="right" style="width:10%">Rate/m</th>
      <th class="right" style="width:13%">Amount (Rs.)</th>
    </tr>
  </thead>
  <tbody>
    @foreach($whiteItems as $index => $item)
    <tr>
      <td>{{ $index + 1 }}</td>
      <td class="code">{{ $item->product_code_snap }}</td>
      <td>{{ $item->product_name_snap }}</td>
      <td class="right">{{ $item->bundle_qty_ordered }}</td>
      <td class="right">{{ number_format($item->total_length, 2) }}</td>
      <td class="right">{{ $item->total_pieces }}</td>
      @if($show_weight)<td class="right">{{ number_format($item->total_weight, 3) }}</td>@endif
      <td class="right">{{ number_format($item->unit_rate_snap, 2) }}</td>
      <td class="right">{{ number_format($item->line_total, 2) }}</td>
    </tr>
    @endforeach
  </tbody>
  @if($show_weight)
  <tfoot>
    <tr>
      <td colspan="{{ $show_weight ? 6 : 5 }}" style="text-align:right;font-size:9px;color:#555;padding:4px 7px;">White Total Weight:</td>
      <td class="right" style="font-weight:bold;font-size:10px;">{{ number_format($whiteItems->sum('total_weight'), 3) }} kg</td>
      <td></td>
      @if($show_weight)<td class="right" style="font-weight:bold;">
        @php $whiteTotal = $whiteItems->sum('line_total'); $whiteWeight = $whiteItems->sum('total_weight'); @endphp
        {{ $whiteWeight > 0 ? 'Rs.'.number_format($whiteTotal/$whiteWeight, 2).'/kg' : '-' }}
      </td>@endif
      <td></td>
    </tr>
  </tfoot>
  @endif
</table>
@endif

{{-- COLOR PROFILES SECTION --}}
@php $colorItems = $pi->items->filter(fn($i) => $i->item_type !== 'hardware' && $i->profile_type_snap === 'color'); @endphp
@if($colorItems->count() > 0)
<div style="background:#E65C00;color:#fff;font-size:10px;font-weight:bold;padding:5px 8px;margin-bottom:0;">
  🎨 COLOR PROFILE{{ $pi->color_name ? ' — ' . strtoupper($pi->color_name) : '' }}
</div>
<table class="items-table" style="margin-bottom:6px;">
  <thead>
    <tr>
      <th style="width:4%;background:#E65C00;">#</th>
      <th style="width:10%;background:#E65C00;">Code</th>
      <th style="width:28%;background:#E65C00;">Product Description</th>
      <th class="right" style="width:8%;background:#E65C00;">Bundles</th>
      <th class="right" style="width:10%;background:#E65C00;">Length (m)</th>
      <th class="right" style="width:7%;background:#E65C00;">Pieces</th>
      @if($show_weight)<th class="right" style="width:10%;background:#E65C00;">Wt. (kg)</th>@endif
      <th class="right" style="width:10%;background:#E65C00;">Rate/m</th>
      <th class="right" style="width:13%;background:#E65C00;">Amount (Rs.)</th>
    </tr>
  </thead>
  <tbody>
    @foreach($colorItems as $index => $item)
    <tr>
      <td>{{ $index + 1 }}</td>
      <td class="code" style="color:#E65C00;">{{ $item->product_code_snap }}</td>
      <td>{{ $item->product_name_snap }}</td>
      <td class="right">{{ $item->bundle_qty_ordered }}</td>
      <td class="right">{{ number_format($item->total_length, 2) }}</td>
      <td class="right">{{ $item->total_pieces }}</td>
      @if($show_weight)<td class="right">{{ number_format($item->total_weight, 3) }}</td>@endif
      <td class="right">{{ number_format($item->unit_rate_snap, 2) }}</td>
      <td class="right">{{ number_format($item->line_total, 2) }}</td>
    </tr>
    @endforeach
  </tbody>
  @if($show_weight)
  <tfoot>
    <tr>
      <td colspan="{{ $show_weight ? 6 : 5 }}" style="text-align:right;font-size:9px;color:#555;padding:4px 7px;">Color Total Weight:</td>
      <td class="right" style="font-weight:bold;font-size:10px;">{{ number_format($colorItems->sum('total_weight'), 3) }} kg</td>
      <td></td>
      @if($show_weight)<td class="right" style="font-weight:bold;">
        @php $colorTotal = $colorItems->sum('line_total'); $colorWeight = $colorItems->sum('total_weight'); @endphp
        {{ $colorWeight > 0 ? 'Rs.'.number_format($colorTotal/$colorWeight, 2).'/kg' : '-' }}
      </td>@endif
      <td></td>
    </tr>
  </tfoot>
  @endif
</table>
@endif

{{-- HARDWARE SECTION --}}
@php $hwItems = $pi->items->filter(fn($i) => $i->item_type === 'hardware'); @endphp
@if($hwItems->count() > 0)
<div style="background:#2E7D32;color:#fff;font-size:10px;font-weight:bold;padding:5px 8px;margin-bottom:0;">
  🔧 HARDWARE & ACCESSORIES
</div>
<table class="items-table" style="margin-bottom:6px;">
  <thead>
    <tr>
      <th style="width:5%;background:#2E7D32;">#</th>
      <th style="width:10%;background:#2E7D32;">Code</th>
      <th style="width:40%;background:#2E7D32;">Item Description</th>
      <th class="right" style="width:15%;background:#2E7D32;">Unit</th>
      <th class="right" style="width:10%;background:#2E7D32;">Qty</th>
      <th class="right" style="width:10%;background:#2E7D32;">Rate</th>
      <th class="right" style="width:10%;background:#2E7D32;">Amount (Rs.)</th>
    </tr>
  </thead>
  <tbody>
    @foreach($hwItems as $index => $item)
    <tr>
      <td>{{ $index + 1 }}</td>
      <td class="code" style="color:#2E7D32;">{{ $item->product_code_snap }}</td>
      <td>{{ $item->hardware_name_snap }}</td>
      <td class="right capitalize">{{ $item->hardware_unit_snap }}</td>
      <td class="right">{{ number_format($item->quantity, 2) }}</td>
      <td class="right">{{ number_format($item->unit_rate_snap, 2) }}</td>
      <td class="right">{{ number_format($item->line_total, 2) }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@endif

@endif

<table class="totals-table">
  @if($is_plastrong)
  <tr><td>Total Kg</td><td class="right">{{ number_format($pi->items->sum('total_weight'), 3) }} kg</td></tr>
  @endif
  <tr><td>Subtotal</td><td class="right">Rs. {{ number_format($pi->subtotal, 2) }}</td></tr>
  @if($pi->discount_pct > 0)
  <tr><td style="color:#2e7d32">Discount ({{ $pi->discount_pct }}%)</td><td class="right" style="color:#2e7d32">- Rs. {{ number_format($pi->discount_amount, 2) }}</td></tr>
  @endif
  <tr><td>Transport Charge</td><td class="right">Rs. {{ number_format($pi->transport_charge, 2) }}</td></tr>
  <tr><td>Insurance Charge</td><td class="right">Rs. {{ number_format($pi->insurance_charge, 2) }}</td></tr>
  <tr><td>GST @ 18%</td><td class="right">Rs. {{ number_format($pi->gst_amount, 2) }}</td></tr>
  <tr>
    <td style="background:{{ $company['color'] }};color:#fff;font-weight:bold;padding:6px 10px;">GRAND TOTAL</td>
    <td class="right" style="background:{{ $company['color'] }};color:#fff;font-weight:bold;padding:6px 10px;">Rs. {{ number_format($pi->grand_total, 2) }}</td>
  </tr>
</table>
<div class="clear"></div>

<table class="bottom-table">
  <tr>
    <td>
      <div class="box-title">Bank Details</div>
      <div class="box-sub" style="margin-top:4px">
        @if($company['account_name'])Account Name: {{ $company['account_name'] }}<br>@endif
        @if($company['bank'])Bank: {{ $company['bank'] }}<br>@endif
        @if($company['account'])Account No: {{ $company['account'] }}<br>@endif
        @if($company['ifsc'])IFSC Code: {{ $company['ifsc'] }}<br>@endif
        @if($company['branch'])Branch: {{ $company['branch'] }}@endif
      </div>
    </td>
    <td>
      <div class="box-title">Terms &amp; Conditions</div>
      <ul class="terms-list">
        <li>Payment 100% in advance before dispatch.</li>
        <li>Goods once sold will not be taken back.</li>
        <li>This is a Proforma Invoice only.</li>
        <li>Prices valid for 15 days from PI date.</li>
        <li>Subject to Malda jurisdiction only.</li>
      </ul>
    </td>
  </tr>
</table>

<div class="sign-box">
  <div class="sign-space"></div>
  <div class="sign-label">Authorized Signatory</div>
  <div style="font-size:10px;font-weight:bold;color:{{ $company['color'] }};margin-top:2px;">For {{ $company['name'] }}</div>
</div>
<div class="clear"></div>

<div class="footer">{{ $company['name'] }} | {{ $company['brand_label'] }} | {{ $company['address'] }}</div>

</div>
</body>
</html>
