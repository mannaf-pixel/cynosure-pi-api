<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size:9px; color:#1a1a1a; background:#fff; }
  .page { padding:15px 20px; }
  .header { display:table; width:100%; margin-bottom:10px; }
  .header-logo { display:table-cell; width:80px; vertical-align:middle; }
  .header-logo img { width:70px; }
  .header-info { display:table-cell; vertical-align:middle; text-align:center; }
  .header-info .company-name { font-size:13px; font-weight:bold; color:#1E6FD9; }
  .header-info .brand-label { font-size:10px; color:#555; margin-top:2px; }
  .header-info .address { font-size:8px; color:#777; margin-top:2px; }
  .header-right { display:table-cell; width:130px; vertical-align:middle; text-align:right; }
  .qt-number { font-size:13px; font-weight:bold; color:#1E6FD9; }
  .qt-date { font-size:9px; color:#555; margin-top:2px; }
  .badge { display:inline-block; background:#E8F4FD; color:#1E6FD9; padding:2px 8px; border-radius:4px; font-size:8px; font-weight:bold; margin-top:4px; }
  .badge-internal { background:#FDE8F4; color:#8B1A8B; }
  .title-bar { background:#1E6FD9; color:#fff; text-align:center; font-size:12px; font-weight:bold; padding:6px; margin:8px 0; letter-spacing:1px; }
  .bill-ship { display:table; width:100%; margin-bottom:8px; border:1px solid #ddd; }
  .bill-box { display:table-cell; width:50%; padding:8px; vertical-align:top; }
  .bill-box:first-child { border-right:1px solid #ddd; }
  .bill-box .label { font-size:8px; font-weight:bold; color:#1E6FD9; margin-bottom:4px; border-bottom:1px solid #e0e0e0; padding-bottom:2px; }
  .bill-box .company { font-size:10px; font-weight:bold; color:#1a1a1a; }
  .bill-box .detail { font-size:8px; color:#555; margin-top:2px; line-height:1.5; }
  .valid-bar { background:#FFF8E1; border:1px solid #FFE082; padding:5px 10px; font-size:8px; margin-bottom:8px; }
  .valid-bar strong { color:#E65100; }
  table.items { width:100%; border-collapse:collapse; margin-bottom:6px; }
  table.items th { padding:4px 5px; font-size:8px; font-weight:bold; color:#fff; }
  table.items td { padding:3px 5px; font-size:8px; border-bottom:1px solid #f0f0f0; }
  table.items .right { text-align:right; }
  table.items .center { text-align:center; }
  .section-header { padding:5px 8px; font-size:9px; font-weight:bold; color:#fff; margin-bottom:0; }
  .section-white { background:#1E6FD9; }
  .section-hardware { background:#2E7D32; }
  .subtotal-row td { background:#f5f5f5; font-weight:bold; }
  .summary-table { width:280px; border:1px solid #ddd; border-collapse:collapse; float:right; }
  .summary-table td { padding:4px 8px; font-size:9px; border-bottom:1px solid #eee; }
  .summary-table .label { color:#555; }
  .summary-table .value { text-align:right; font-weight:bold; }
  .grand-total td { background:#1E6FD9; color:#fff; font-size:11px; font-weight:bold; padding:6px 8px; }
  .cd-row td { color:#7B1FA2; font-weight:bold; }
  .footer { margin-top:15px; border-top:1px solid #ddd; padding-top:8px; display:table; width:100%; }
  .bank-box { display:table-cell; width:50%; vertical-align:top; }
  .terms-box { display:table-cell; width:50%; vertical-align:top; padding-left:15px; }
  .footer-label { font-size:8px; font-weight:bold; color:#1E6FD9; margin-bottom:4px; }
  .footer-row { font-size:8px; color:#555; margin-bottom:2px; }
  .sign-area { text-align:right; margin-top:20px; font-size:8px; color:#555; }
  .page-footer { position:fixed; bottom:0; left:0; right:0; text-align:center; font-size:7px; color:#aaa; border-top:1px solid #eee; padding:3px; background:#fff; }
</style>
</head>
<body>
<div class="page">

  {{-- Header --}}
  <div class="header">
    <div class="header-logo">
      @if($logo_base64)<img src="{{ $logo_base64 }}" />@endif
    </div>
    <div class="header-info">
      <div class="company-name">{{ $company['name'] }}</div>
      <div class="brand-label">{{ $company['brand_label'] }}</div>
      <div class="address">{{ $company['address'] }}</div>
      <div class="address">GSTIN: {{ $company['gstin'] }} | Ph: {{ $company['phone'] }} | {{ $company['email'] }}</div>
    </div>
    <div class="header-right">
      <div class="qt-number">{{ $quotation->quotation_number }}</div>
      <div class="qt-date">Date: {{ \Carbon\Carbon::parse($quotation->created_at)->format('d-M-Y') }}</div>
      <div class="qt-date">Valid: {{ $quotation->valid_until ? \Carbon\Carbon::parse($quotation->valid_until)->format('d-M-Y') : 'N/A' }}</div>
      <div><span class="badge {{ $is_internal ? 'badge-internal' : '' }}">{{ $is_internal ? 'INTERNAL COPY' : 'QUOTATION' }}</span></div>
    </div>
  </div>

  <div class="title-bar">PROFORMA QUOTATION</div>

  {{-- Bill To --}}
  <div class="bill-ship">
    <div class="bill-box">
      <div class="label">BILL TO</div>
      <div class="company">{{ $quotation->customer->company_name }}</div>
      <div class="detail">
        Attn: {{ $quotation->customer->customer_name }}<br/>
        {{ $quotation->customer->address ?? '' }}
        @if($quotation->customer->city), {{ $quotation->customer->city }}@endif
        @if($quotation->customer->state), {{ $quotation->customer->state }}@endif
        @if($quotation->customer->gstin)<br/>GSTIN: {{ $quotation->customer->gstin }}@endif
        @if($quotation->customer->mobile)<br/>Ph: {{ $quotation->customer->mobile }}@endif
      </div>
    </div>
    <div class="bill-box">
      <div class="label">QUOTATION DETAILS</div>
      <div class="detail">
        <strong>Brand:</strong> {{ ucfirst(str_replace('_', ' ', $quotation->brand)) }}<br/>
        @if($quotation->salesperson_name)<strong>Sales Executive:</strong> {{ $quotation->salesperson_name }}<br/>@endif
        <strong>Payment Terms:</strong> Advance<br/>
        <strong>Delivery:</strong> 7-10 Working Days<br/>
        <strong>Status:</strong> {{ ucfirst($quotation->status) }}
      </div>
    </div>
  </div>

  <div class="valid-bar">
    This quotation is valid until <strong>{{ $quotation->valid_until ? \Carbon\Carbon::parse($quotation->valid_until)->format('d M Y') : 'N/A' }}</strong>.
    Prices subject to change after validity period.
  </div>

  <?php
    $items       = $quotation->items;
    $whiteItems  = $items->where('profile_type_snap', 'white')->where('item_type', 'profile');
    $hwItems     = $items->where('item_type', 'hardware');
    $colorGroups = $items->where('item_type', 'profile')->where('profile_type_snap', 'color')->groupBy('color_name');
  ?>

  {{-- White Profile --}}
  @if($whiteItems->count() > 0)
  <div class="section-header section-white">WHITE PROFILE</div>
  <table class="items">
    <thead>
      <tr style="background:#1E6FD9;">
        <th style="width:20px;">#</th>
        <th style="text-align:left;">Code</th>
        <th style="text-align:left;">Product Description</th>
        <th class="right">Bundles</th>
        <th class="right">Length (m)</th>
        <th class="right">Pieces</th>
        @if($is_internal)<th class="right">Wt. (kg)</th>@endif
        <th class="right">MRP/m</th>
        <th class="right">Disc%</th>
        <th class="right">Net/m</th>
        <th class="right">Amount</th>
      </tr>
    </thead>
    <tbody>
      @foreach($whiteItems as $i => $item)
      <tr>
        <td class="center">{{ $i+1 }}</td>
        <td style="color:#1E6FD9;font-weight:bold;">{{ $item->product_code_snap }}</td>
        <td>{{ $item->product_name_snap }}</td>
        <td class="right">{{ $item->bundle_qty_ordered }}</td>
        <td class="right">{{ number_format($item->total_length,2) }}</td>
        <td class="right">{{ $item->total_pieces }}</td>
        @if($is_internal)<td class="right">{{ number_format($item->total_weight,3) }}</td>@endif
        <td class="right">{{ number_format($item->mrp_rate_snap,2) }}</td>
        <td class="right">{{ number_format($item->item_discount_pct??0,0) }}%</td>
        <td class="right">{{ number_format($item->unit_rate_snap,2) }}</td>
        <td class="right">{{ number_format($item->line_total,2) }}</td>
      </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr class="subtotal-row">
        <td colspan="{{ $is_internal ? 8 : 7 }}">White Subtotal</td>
        <td class="right" colspan="3">Rs.{{ number_format($whiteItems->sum('line_total'),2) }}</td>
      </tr>
    </tfoot>
  </table>
  @endif

  {{-- Color Groups --}}
  @foreach($colorGroups as $colorName => $colorItems)
  <?php
    $colorHex = ['GOLDEN OAK'=>'#B8860B','WALNUT'=>'#5C3317','MAHAGONI'=>'#6B2D2D','GREY'=>'#607D8B','BLACK'=>'#212121','ROSEWOOD'=>'#8B1A1A','CHARCOAL'=>'#37474F'];
    $hex = $colorHex[strtoupper($colorName??'')] ?? '#E65C00';
  ?>
  <div class="section-header" style="background:{{ $hex }};">COLOR PROFILE - {{ strtoupper($colorName??'COLOR') }}</div>
  <table class="items">
    <thead>
      <tr style="background:{{ $hex }};">
        <th style="width:20px;">#</th>
        <th style="text-align:left;">Code</th>
        <th style="text-align:left;">Product Description</th>
        <th class="right">Bundles</th>
        <th class="right">Length (m)</th>
        <th class="right">Pieces</th>
        @if($is_internal)<th class="right">Wt. (kg)</th>@endif
        <th class="right">MRP/m</th>
        <th class="right">Disc%</th>
        <th class="right">Net/m</th>
        <th class="right">Amount</th>
      </tr>
    </thead>
    <tbody>
      @foreach($colorItems as $i => $item)
      <tr>
        <td class="center">{{ $i+1 }}</td>
        <td style="color:{{ $hex }};font-weight:bold;">{{ $item->product_code_snap }}</td>
        <td>{{ $item->product_name_snap }}</td>
        <td class="right">{{ $item->bundle_qty_ordered }}</td>
        <td class="right">{{ number_format($item->total_length,2) }}</td>
        <td class="right">{{ $item->total_pieces }}</td>
        @if($is_internal)<td class="right">{{ number_format($item->total_weight,3) }}</td>@endif
        <td class="right">{{ number_format($item->mrp_rate_snap,2) }}</td>
        <td class="right">{{ number_format($item->item_discount_pct??0,0) }}%</td>
        <td class="right">{{ number_format($item->unit_rate_snap,2) }}</td>
        <td class="right">{{ number_format($item->line_total,2) }}</td>
      </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr class="subtotal-row">
        <td colspan="{{ $is_internal ? 8 : 7 }}">{{ strtoupper($colorName) }} Subtotal</td>
        <td class="right" colspan="3">Rs.{{ number_format($colorItems->sum('line_total'),2) }}</td>
      </tr>
    </tfoot>
  </table>
  @endforeach

  {{-- Hardware --}}
  @if($hwItems->count() > 0)
  <div class="section-header section-hardware">HARDWARE & ACCESSORIES</div>
  <table class="items">
    <thead>
      <tr style="background:#2E7D32;">
        <th style="width:20px;">#</th>
        <th style="text-align:left;">Code</th>
        <th style="text-align:left;">Item Description</th>
        <th class="right">Unit</th>
        <th class="right">Rate</th>
        <th class="right">Quantity</th>
        <th class="right">Amount</th>
      </tr>
    </thead>
    <tbody>
      @foreach($hwItems as $i => $item)
      <tr>
        <td class="center">{{ $i+1 }}</td>
        <td style="color:#2E7D32;font-weight:bold;">{{ $item->product_code_snap }}</td>
        <td>{{ $item->product_name_snap }}</td>
        <td class="right">{{ $item->hardware_unit_snap }}</td>
        <td class="right">{{ number_format($item->mrp_rate_snap,2) }}</td>
        <td class="right">{{ $item->quantity }}</td>
        <td class="right">{{ number_format($item->line_total,2) }}</td>
      </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr class="subtotal-row">
        <td colspan="6">Hardware Subtotal</td>
        <td class="right">Rs.{{ number_format($hwItems->sum('line_total'),2) }}</td>
      </tr>
    </tfoot>
  </table>
  @endif

  {{-- Summary --}}
  <div style="margin-top:10px; overflow:hidden;">
    <table class="summary-table">
      <tr><td class="label">Subtotal</td><td class="value">Rs. {{ number_format($quotation->subtotal,2) }}</td></tr>
      @if($quotation->cd_discount_pct > 0)
      <tr class="cd-row"><td>CD Discount ({{ number_format($quotation->cd_discount_pct,2) }}%)</td><td class="value">- Rs. {{ number_format($quotation->cd_discount_amount,2) }}</td></tr>
      <tr><td class="label">After CD Amount</td><td class="value">Rs. {{ number_format($quotation->subtotal - $quotation->cd_discount_amount,2) }}</td></tr>
      @endif
      <tr><td class="label">Transport Charge</td><td class="value">Rs. {{ number_format($quotation->transport_charge,2) }}</td></tr>
      <tr><td class="label">Insurance Charge</td><td class="value">Rs. {{ number_format($quotation->insurance_charge,2) }}</td></tr>
      <tr><td class="label">GST @ 18%</td><td class="value">Rs. {{ number_format($quotation->gst_amount,2) }}</td></tr>
      <tr class="grand-total"><td>GRAND TOTAL</td><td class="value">Rs. {{ number_format($quotation->grand_total,2) }}</td></tr>
    </table>
    <div style="clear:both;"></div>
  </div>

  {{-- Footer --}}
  <div class="footer">
    <div class="bank-box">
      <div class="footer-label">BANK DETAILS</div>
      <div class="footer-row">Account Name: {{ $company['account_name'] }}</div>
      <div class="footer-row">Bank: {{ $company['bank'] }}</div>
      <div class="footer-row">Account No: {{ $company['account'] }}</div>
      <div class="footer-row">IFSC Code: {{ $company['ifsc'] }}</div>
      <div class="footer-row">Branch: {{ $company['branch'] }}</div>
    </div>
    <div class="terms-box">
      <div class="footer-label">TERMS & CONDITIONS</div>
      <div class="footer-row">This is a Quotation, not a Tax Invoice.</div>
      <div class="footer-row">Prices valid for 30 days from quotation date.</div>
      <div class="footer-row">Payment 100% in advance before dispatch.</div>
      <div class="footer-row">Goods once sold will not be taken back.</div>
      <div class="footer-row">Subject to Malda jurisdiction only.</div>
      @if($quotation->remarks)
      <div class="footer-row" style="margin-top:4px;"><strong>Remarks:</strong> {{ $quotation->remarks }}</div>
      @endif
    </div>
  </div>

  <div style="text-align:right; margin-top:30px;">
    <div style="display:inline-block; border-top:1px solid #999; width:120px; text-align:center; padding-top:4px; font-size:8px; color:#555;">
      Authorized Signatory<br/>For {{ $company['name'] }}
    </div>
  </div>

</div>
<div class="page-footer">{{ $company['name'] }} | {{ $company['brand_label'] }} | {{ $company['address'] }}</div>
</body>
</html>
