<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation {{ $quotation->quotation_number }}</title>
    <style>
        @page {
            margin: 30px 35px;
        }

        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
        }

        .company-tagline {
            font-size: 10px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        .company-contact {
            font-size: 10px;
            color: #64748b;
            margin-top: 6px;
            line-height: 1.4;
        }

        .quote-heading {
            font-size: 22px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .quote-number {
            font-size: 13px;
            font-weight: bold;
            font-family: monospace;
            color: #0369a1;
            margin-top: 3px;
        }

        .quote-dates {
            font-size: 10px;
            color: #475569;
            margin-top: 8px;
            line-height: 1.5;
        }

        .divider {
            border-bottom: 2px solid #0f172a;
            margin: 15px 0 20px 0;
        }

        .details-table {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .details-table td {
            padding: 12px;
            vertical-align: top;
            width: 50%;
        }

        .section-label {
            font-size: 9px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .client-name {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
        }

        .detail-text {
            font-size: 10px;
            color: #334155;
            margin-top: 2px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px 10px;
            text-align: left;
        }

        .items-table th.text-right, .items-table td.text-right {
            text-align: right;
        }

        .items-table th.text-center, .items-table td.text-center {
            text-align: center;
        }

        .items-table td {
            padding: 9px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10.5px;
            color: #334155;
        }

        .items-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .summary-table td {
            vertical-align: top;
        }

        .notes-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 10px;
            font-size: 10px;
            color: #475569;
            margin-top: 3px;
        }

        .totals-box {
            width: 250px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 12px;
            margin-left: auto;
        }

        .total-row-table {
            width: 100%;
            font-size: 10.5px;
            color: #475569;
        }

        .total-row-table td {
            padding: 3px 0;
        }

        .total-row-table .grand-total td {
            border-top: 2px solid #0f172a;
            padding-top: 6px;
            font-size: 13px;
            font-weight: bold;
            color: #065f46;
        }

        .footer-table {
            margin-top: 30px;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
            font-size: 10px;
            color: #64748b;
        }

        .signature-line {
            border-top: 1px solid #94a3b8;
            width: 180px;
            margin-top: 35px;
            padding-top: 4px;
            font-weight: bold;
            color: #334155;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width: 55%;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" style="max-height: 48px; width: auto; margin-bottom: 6px;" alt="ARTSCI Logo">
                @endif
                <div class="company-name">ARTSCI</div>
                <div class="company-tagline">Bringing Designing Science</div>
                <div class="company-contact">
                    Port Harcourt, Nigeria<br>
                    Email: support@artsci.com.ng | Web: www.artsci.com.ng
                </div>
            </td>
            <td style="width: 45%; text-align: right;">
                <div class="quote-heading">QUOTATION</div>
                <div class="quote-number">{{ $quotation->quotation_number }}</div>
                <div class="quote-dates">
                    <div><strong>Date:</strong> {{ $quotation->quotation_date?->format('F j, Y') }}</div>
                    <div><strong>Valid Until:</strong> {{ $quotation->valid_until?->format('F j, Y') ?? 'N/A' }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- Details Box -->
    <table class="details-table">
        <tr>
            <td>
                <div class="section-label">Prepared For</div>
                <div class="client-name">{{ $quotation->client?->company_name ?: $quotation->client?->client_name ?: 'Client' }}</div>
                @if($quotation->client?->company_name && $quotation->client?->client_name)
                    <div class="detail-text">Attn: {{ $quotation->client->client_name }}</div>
                @endif
                @if($quotation->client?->phone)
                    <div class="detail-text">Phone: {{ $quotation->client->phone }}</div>
                @endif
                @if($quotation->client?->email)
                    <div class="detail-text">Email: {{ $quotation->client->email }}</div>
                @endif
                @if($quotation->client?->address)
                    <div class="detail-text">{{ $quotation->client->address }}, {{ $quotation->client->city_state }}</div>
                @endif
            </td>
            <td>
                <div class="section-label">Quotation Summary</div>
                <div class="detail-text"><strong>Subject:</strong> {{ $quotation->title }}</div>
                @if($quotation->jobRequestItem)
                    <div class="detail-text"><strong>Job Ref:</strong> #{{ $quotation->jobRequestItem->id }} - {{ $quotation->jobRequestItem->title }}</div>
                @endif
                @if($quotation->inspection)
                    <div class="detail-text"><strong>Inspection Ref:</strong> {{ $quotation->inspection->inspection_code }}</div>
                @endif
            </td>
        </tr>
    </table>

    <!-- Line Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">#</th>
                <th>Item Description</th>
                <th class="text-right" style="width: 60px;">Qty</th>
                <th class="text-right" style="width: 110px;">Unit Price (₦)</th>
                <th class="text-right" style="width: 120px;">Total (₦)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->description }}</strong>
                        @if($item->notes)
                            <div style="font-size: 9px; color: #64748b; margin-top: 1px;">{{ $item->notes }}</div>
                        @endif
                    </td>
                    <td class="text-right">{{ (float) $item->quantity }}</td>
                    <td class="text-right">{{ $financeMoney($item->unit_price) }}</td>
                    <td class="text-right" style="font-weight: bold;">{{ $financeMoney($item->total_price) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Summary & Notes -->
    <table class="summary-table">
        <tr>
            <td style="width: 55%; padding-right: 15px;">
                @if($quotation->notes)
                    <div style="margin-bottom: 10px;">
                        <div class="section-label">Customer Notes</div>
                        <div class="notes-box">{{ $quotation->notes }}</div>
                    </div>
                @endif

                @if($quotation->terms)
                    <div>
                        <div class="section-label">Terms & Conditions</div>
                        <div class="notes-box">{{ $quotation->terms }}</div>
                    </div>
                @endif
            </td>
            <td style="width: 45%;">
                <div class="totals-box">
                    <table class="total-row-table">
                        <tr>
                            <td>Subtotal:</td>
                            <td style="text-align: right; font-weight: bold;">{{ $financeMoney($quotation->subtotal) }}</td>
                        </tr>
                        @if($quotation->discount_amount > 0)
                            <tr style="color: #b91c1c;">
                                <td>Discount:</td>
                                <td style="text-align: right;">-{{ $financeMoney($quotation->discount_amount) }}</td>
                            </tr>
                        @endif
                        @if($quotation->tax_amount > 0)
                            <tr>
                                <td>Tax:</td>
                                <td style="text-align: right;">+{{ $financeMoney($quotation->tax_amount) }}</td>
                            </tr>
                        @endif
                        <tr class="grand-total">
                            <td>Grand Total:</td>
                            <td style="text-align: right;">{{ $financeMoney($quotation->grand_total) }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <table class="footer-table">
        <tr>
            <td style="width: 50%;">
                <div class="signature-line">Authorized Signature</div>
                <div style="margin-top: 2px;">ARTSCI Management</div>
            </td>
            <td style="width: 50%; text-align: right; vertical-align: bottom;">
                <div>Thank you for choosing ARTSCI Security Systems</div>
                <div style="font-size: 9px; color: #94a3b8; margin-top: 2px;">Official Customer Quotation Document</div>
            </td>
        </tr>
    </table>
</body>
</html>
