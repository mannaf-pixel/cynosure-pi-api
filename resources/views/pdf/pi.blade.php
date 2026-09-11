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
.items-table { width:100%; border-collapse:collapse; margin-bottom:2px; }
.items-table th { color:#fff; font-size:10px; padding:6px 7px; text-align:left; }
.items-table th.right { text-align:right; }
.items-table td { padding:5px 7px; font-size:10px; border-bottom:1px solid #eee; }
.items-table td.right { text-align:right; }
.items-table tr:nth-child(even) td { background:#f9f9f9; }
.section-bar { font-size:10px; font-weight:bold; padding:5px 8px; color:#fff; margin-bottom:0; margin-top:8px; }
.section-summary { width:100%; border-collapse:collapse; margin-bottom:8px; }
.section-summary td { padding:3px 8px; font-size:10px; border-bottom:1px solid #eee; }
.section-summary td.right { text-align:right; }
.totals-table { width:320px; float:right; border-collapse:collapse; border:1px solid #ddd; margin-bottom:10px; margin-top:8px; }
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
.kg-rate-row { background:#fffde7; font-weight:bold; font-size:10px; }
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
{{-- PLASTRONG --}}
<table class="items-table">
  <thead>
    <tr style="background:#8B0000;">
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
      <td style="font-weight:bold;color:#8B0000;">{{ $item->product_code_snap }}</td>
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

@php
  $whiteItems   = $pi->items->filter(fn($i) => $i->item_type !== 'hardware' && $i->profile_type_snap === 'white');
  $colorItems   = $pi->items->filter(fn($i) => $i->item_type !== 'hardware' && $i->profile_type_snap === 'color');
  $hwItems      = $pi->items->filter(fn($i) => $i->item_type === 'hardware');

  $whiteSubtotal = $whiteItems->sum('line_total');
  $colorSubtotal = $colorItems->sum('line_total');
  $hwSubtotal    = $hwItems->sum('line_total');
  $totalSubtotal = $whiteSubtotal + $colorSubtotal + $hwSubtotal;

  $discountPct    = floatval($pi->discount_pct);
  $discountAmt    = round($totalSubtotal * $discountPct / 100, 2);
  $afterDiscount  = $totalSubtotal - $discountAmt;

  // Proportional discount per section
  $whiteProportion = $totalSubtotal > 0 ? $whiteSubtotal / $totalSubtotal : 0;
  $colorProportion = $totalSubtotal > 0 ? $colorSubtotal / $totalSubtotal : 0;
  $hwProportion    = $totalSubtotal > 0 ? $hwSubtotal / $totalSubtotal : 0;

  $whiteAfterDiscount = round($whiteSubtotal - ($discountAmt * $whiteProportion), 2);
  $colorAfterDiscount = round($colorSubtotal - ($discountAmt * $colorProportion), 2);
  $hwAfterDiscount    = round($hwSubtotal - ($discountAmt * $hwProportion), 2);

  $whiteGst = round($whiteAfterDiscount * 0.18, 2);
  $colorGst = round($colorAfterDiscount * 0.18, 2);
  $hwGst    = round($hwAfterDiscount * 0.18, 2);

  $whiteTotal = $whiteAfterDiscount + $whiteGst;
  $colorTotal = $colorAfterDiscount + $colorGst;
  $hwTotal    = $hwAfterDiscount + $hwGst;

  $whiteWeight = $whiteItems->sum('total_weight');
  $colorWeight = $colorItems->sum('total_weight');

  $whiteKgRate = $whiteWeight > 0 ? round($whiteSubtotal / $whiteWeight, 2) : 0;
  $colorKgRate = $colorWeight > 0 ? round($colorSubtotal / $colorWeight, 2) : 0;
@endphp

{{-- WHITE PROFILE SECTION --}}
@if($whiteItems->count() > 0)
<div class="section-bar" style="background:#1E6FD9;">🏗️ WHITE PROFILE</div>
<table class="items-table">
  <thead>
    <tr style="background:#1E6FD9;">
      <th style="width:4%">#</th>
      <th style="width:9%">Code</th>
      <th style="width:24%">Product Description</th>
      <th class="right" style="width:7%">Bundles</th>
      <th class="right" style="width:9%">Length (m)</th>
      <th class="right" style="width:6%">Pieces</th>
      @if($show_weight)<th class="right" style="width:8%">Wt. (kg)</th>@endif
      <th class="right" style="width:7%">MRP/m</th>
      <th class="right" style="width:6%">Disc%</th>
      <th class="right" style="width:8%">Net/m</th>
      <th class="right" style="width:10%">Amount (Rs.)</th>
    </tr>
  </thead>
  <tbody>
    @foreach($whiteItems->values() as $index => $item)
    <tr>
      <td>{{ $index + 1 }}</td>
      <td style="font-weight:bold;color:#1E6FD9;">{{ $item->product_code_snap }}</td>
      <td>{{ $item->product_name_snap }}</td>
      <td class="right">{{ $item->bundle_qty_ordered }}</td>
      <td class="right">{{ number_format($item->total_length, 2) }}</td>
      <td class="right">{{ $item->total_pieces }}</td>
      @if($show_weight)<td class="right">{{ number_format($item->total_weight, 3) }}</td>@endif
      <td class="right">{{ number_format($item->mrp_rate_snap ?? $item->unit_rate_snap, 2) }}</td>
      <td class="right">{{ number_format($item->item_discount_pct ?? 0, 0) }}%</td>
      <td class="right">{{ number_format($item->unit_rate_snap, 2) }}</td>
      <td class="right">{{ number_format($item->line_total, 2) }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@if($show_weight)
<table style="width:100%;border-collapse:collapse;margin-bottom:2px;background:#e3f2fd;">
  <tr>
    <td style="padding:4px 8px;font-size:10px;color:#1565C0;">White Subtotal: <strong>Rs.{{ number_format($whiteSubtotal,2) }}</strong></td>
    <td style="padding:4px 8px;font-size:10px;color:#1565C0;">Total Length: <strong>{{ number_format($whiteItems->sum('total_length'),2) }}m</strong></td>
    <td style="padding:4px 8px;font-size:10px;color:#1565C0;">Total Weight: <strong>{{ number_format($whiteWeight,3) }} kg</strong></td>
    <td style="padding:4px 8px;font-size:10px;color:#1565C0;text-align:right;">Eff. Rate/kg (after discount): <strong>Rs.{{ number_format($whiteKgRate,2) }}/kg</strong></td>
  </tr>
</table>
@else
<table style="width:100%;border-collapse:collapse;margin-bottom:2px;background:#e3f2fd;">
  <tr>
    <td style="padding:4px 8px;font-size:10px;color:#1565C0;">White Subtotal: <strong>Rs.{{ number_format($whiteSubtotal,2) }}</strong></td>
    <td style="padding:4px 8px;font-size:10px;color:#1565C0;">Total Length: <strong>{{ number_format($whiteItems->sum('total_length'),2) }}m</strong></td>
    <td style="padding:4px 8px;font-size:10px;color:#1565C0;text-align:right;">Total Pieces: <strong>{{ $whiteItems->sum('total_pieces') }}</strong></td>
  </tr>
</table>
@endif
@endif

{{-- COLOR PROFILE SECTIONS — har color group alag --}}
@if($colorItems->count() > 0)
@php
  $colorGroups = $colorItems->groupBy(function($item) {
    return $item->color_name ? strtoupper($item->color_name) : 'COLOR';
  });
  $colorPalette = [
    'GOLDEN OAK'  => '#B8860B',
    'WALNUT'      => '#5C3317',
    'MAHAGONI'    => '#6B2D2D',
    'MAHOGANY'    => '#6B2D2D',
    'GREY'        => '#607D8B',
    'GRAY'        => '#607D8B',
    'BLACK'       => '#212121',
    'ROSEWOOD'    => '#8B1A1A',
    'CHARCOAL'    => '#37474F',
    'WHITE OAK'   => '#A0845C',
    'DARK BROWN'  => '#4E342E',
    'CREAM'       => '#C8A96E',
  ];
@endphp
@foreach($colorGroups as $colorLabel => $groupItems)
@php
  $groupSubtotal = $groupItems->sum('line_total');
  $groupWeight   = $groupItems->sum('total_weight');
  $groupAfterDiscount = $totalSubtotal > 0 ? $groupSubtotal - ($discountAmt * ($groupSubtotal / $totalSubtotal)) : $groupSubtotal;
  $groupKgRate = $groupWeight > 0 ? round($groupSubtotal / $groupWeight, 2) : 0;
  $headerColor = $colorPalette[$colorLabel] ?? '#E65C00';
@endphp
<div class="section-bar" style="background:{{ $headerColor }};margin-top:8px;">🎨 COLOR PROFILE — {{ $colorLabel }}</div>
<table class="items-table">
  <thead>
    <tr style="background:{{ $headerColor }};">
      <th style="width:4%">#</th>
      <th style="width:9%">Code</th>
      <th style="width:24%">Product Description</th>
      <th class="right" style="width:7%">Bundles</th>
      <th class="right" style="width:9%">Length (m)</th>
      <th class="right" style="width:6%">Pieces</th>
      @if($show_weight)<th class="right" style="width:8%">Wt. (kg)</th>@endif
      <th class="right" style="width:7%">MRP/m</th>
      <th class="right" style="width:6%">Disc%</th>
      <th class="right" style="width:8%">Net/m</th>
      <th class="right" style="width:10%">Amount (Rs.)</th>
    </tr>
  </thead>
  <tbody>
    @foreach($groupItems->values() as $index => $item)
    <tr>
      <td>{{ $index + 1 }}</td>
      <td style="font-weight:bold;color:{{ $headerColor }};">{{ $item->product_code_snap }}</td>
      <td>{{ $item->product_name_snap }}</td>
      <td class="right">{{ $item->bundle_qty_ordered }}</td>
      <td class="right">{{ number_format($item->total_length, 2) }}</td>
      <td class="right">{{ $item->total_pieces }}</td>
      @if($show_weight)<td class="right">{{ number_format($item->total_weight, 3) }}</td>@endif
      <td class="right">{{ number_format($item->mrp_rate_snap ?? $item->unit_rate_snap, 2) }}</td>
      <td class="right">{{ number_format($item->item_discount_pct ?? 0, 0) }}%</td>
      <td class="right">{{ number_format($item->unit_rate_snap, 2) }}</td>
      <td class="right">{{ number_format($item->line_total, 2) }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@if($show_weight)
<table style="width:100%;border-collapse:collapse;margin-bottom:2px;background:#fff3e0;">
  <tr>
    <td style="padding:4px 8px;font-size:10px;color:{{ $headerColor }};">{{ $colorLabel }} Subtotal: <strong>Rs.{{ number_format($groupSubtotal,2) }}</strong></td>
    <td style="padding:4px 8px;font-size:10px;color:{{ $headerColor }};">Total Length: <strong>{{ number_format($groupItems->sum('total_length'),2) }}m</strong></td>
    <td style="padding:4px 8px;font-size:10px;color:{{ $headerColor }};">Total Weight: <strong>{{ number_format($groupWeight,3) }} kg</strong></td>
    <td style="padding:4px 8px;font-size:10px;color:{{ $headerColor }};text-align:right;">Rate/kg: <strong>Rs.{{ number_format($groupKgRate,2) }}/kg</strong></td>
  </tr>
</table>
@else
<table style="width:100%;border-collapse:collapse;margin-bottom:2px;background:#fff3e0;">
  <tr>
    <td style="padding:4px 8px;font-size:10px;color:{{ $headerColor }};">{{ $colorLabel }} Subtotal: <strong>Rs.{{ number_format($groupSubtotal,2) }}</strong></td>
    <td style="padding:4px 8px;font-size:10px;color:{{ $headerColor }};">Total Length: <strong>{{ number_format($groupItems->sum('total_length'),2) }}m</strong></td>
    <td style="padding:4px 8px;font-size:10px;color:{{ $headerColor }};text-align:right;">Total Pieces: <strong>{{ $groupItems->sum('total_pieces') }}</strong></td>
  </tr>
</table>
@endif
@endforeach
@endif

{{-- HARDWARE SECTION --}}
@if($hwItems->count() > 0)
<div class="section-bar" style="background:#2E7D32;margin-top:8px;">🔧 HARDWARE & ACCESSORIES</div>
<table class="items-table">
  <thead>
    <tr style="background:#2E7D32;">
      <th style="width:5%">#</th>
      <th style="width:10%">Code</th>
      <th style="width:42%">Item Description</th>
      <th class="right" style="width:13%">Unit</th>
      <th class="right" style="width:10%">Qty</th>
      <th class="right" style="width:10%">Rate</th>
      <th class="right" style="width:10%">Amount (Rs.)</th>
    </tr>
  </thead>
  <tbody>
    @foreach($hwItems->values() as $index => $item)
    <tr>
      <td>{{ $index + 1 }}</td>
      <td style="font-weight:bold;color:#2E7D32;">{{ $item->product_code_snap }}</td>
      <td>{{ $item->hardware_name_snap }}</td>
      <td class="right" style="text-transform:capitalize;">{{ $item->hardware_unit_snap }}</td>
      <td class="right">{{ number_format($item->quantity, 2) }}</td>
      <td class="right">{{ number_format($item->unit_rate_snap, 2) }}</td>
      <td class="right">{{ number_format($item->line_total, 2) }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
<table style="width:100%;border-collapse:collapse;margin-bottom:2px;background:#e8f5e9;">
  <tr>
    <td style="padding:4px 8px;font-size:10px;color:#1B5E20;">Hardware Subtotal: <strong>Rs.{{ number_format($hwSubtotal,2) }}</strong></td>
  </tr>
</table>
@endif

@endif

{{-- COMBINED TOTALS --}}
<table class="totals-table">
  @if(!$is_plastrong && $show_weight && $whiteItems->count() > 0)
  <tr style="background:#e3f2fd;">
    <td style="color:#1565C0;font-weight:bold;">White (after GST)</td>
    <td class="right" style="color:#1565C0;font-weight:bold;">Rs. {{ number_format($whiteTotal, 2) }}</td>
  </tr>
  @endif
  @if(!$is_plastrong && $show_weight && $colorItems->count() > 0)
  <tr style="background:#fff3e0;">
    <td style="color:#BF360C;font-weight:bold;">Color (after GST)</td>
    <td class="right" style="color:#BF360C;font-weight:bold;">Rs. {{ number_format($colorTotal, 2) }}</td>
  </tr>
  @endif
  @if(!$is_plastrong && $show_weight && $hwItems->count() > 0)
  <tr style="background:#e8f5e9;">
    <td style="color:#1B5E20;font-weight:bold;">Hardware (after GST)</td>
    <td class="right" style="color:#1B5E20;font-weight:bold;">Rs. {{ number_format($hwTotal, 2) }}</td>
  </tr>
  @endif
  @if($is_plastrong)
  <tr><td>Total Kg</td><td class="right">{{ number_format($pi->items->sum('total_weight'), 3) }} kg</td></tr>
  @endif
  <tr><td>Subtotal</td><td class="right">Rs. {{ number_format($pi->subtotal, 2) }}</td></tr>
  <tr><td>Transport Charge</td><td class="right">Rs. {{ number_format($pi->transport_charge, 2) }}</td></tr>
  <tr><td>Insurance Charge</td><td class="right">Rs. {{ number_format($pi->insurance_charge, 2) }}</td></tr>
  <tr><td>GST @ 18%</td><td class="right">Rs. {{ number_format($pi->gst_amount, 2) }}</td></tr>
  @if($pi->cd_discount_pct > 0)
  <tr>
    <td style="color:#7B1FA2;font-weight:bold;">CD Discount ({{ number_format($pi->cd_discount_pct, 2) }}%)</td>
    <td class="right" style="color:#7B1FA2;font-weight:bold;">- Rs. {{ number_format($pi->cd_discount_amount, 2) }}</td>
  </tr>
  @endif
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
  <div style="font-size:10px;">Authorized Signatory</div>
  <div style="font-size:10px;font-weight:bold;color:{{ $company['color'] }};margin-top:2px;">For {{ $company['name'] }}</div>
</div>
<div class="clear"></div>

<div class="footer">{{ $company['name'] }} | {{ $company['brand_label'] }} | {{ $company['address'] }}</div>

</div>
</body>
</html>
