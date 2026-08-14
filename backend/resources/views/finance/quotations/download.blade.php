<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation {{ $quotation->quotation_number }} — ARTSCI</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            font-size: 13px;
            color: #1e293b;
            background-color: #f8fafc;
            padding: 20px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .document-container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 24px;
            margin-bottom: 24px;
        }

        .logo-area img {
            height: 56px;
            width: auto;
            margin-bottom: 8px;
        }

        .company-name {
            font-size: 20px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        .company-tagline {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-top: 2px;
        }

        .company-contact {
            font-size: 11px;
            color: #64748b;
            margin-top: 8px;
            line-height: 1.5;
        }

        .quote-meta-box {
            text-align: right;
        }

        .quote-heading {
            font-size: 24px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .quote-number {
            font-size: 14px;
            font-weight: 700;
            font-family: monospace;
            color: #0369a1;
            margin-top: 4px;
        }

        .date-list {
            margin-top: 12px;
            font-size: 12px;
            color: #475569;
            line-height: 1.6;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            background-color: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 28px;
        }

        .section-label {
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 6px;
        }

        .client-name {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
        }

        .detail-text {
            font-size: 12px;
            color: #334155;
            line-height: 1.5;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 28px;
        }

        .items-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 10px 12px;
            text-align: left;
        }

        .items-table th.text-right, .items-table td.text-right {
            text-align: right;
        }

        .items-table th.text-center, .items-table td.text-center {
            text-align: center;
        }

        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 12px;
            color: #334155;
        }

        .items-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .summary-flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
            margin-bottom: 32px;
        }

        .notes-area {
            flex: 1;
            font-size: 11px;
            color: #475569;
        }

        .notes-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            margin-top: 4px;
            white-space: pre-line;
        }

        .totals-box {
            width: 280px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 16px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #475569;
            margin-bottom: 8px;
        }

        .total-row.grand {
            border-top: 2px solid #0f172a;
            padding-top: 10px;
            margin-top: 10px;
            font-size: 15px;
            font-weight: 900;
            color: #065f46;
        }

        .footer-signatures {
            border-top: 1px solid #e2e8f0;
            padding-top: 24px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            font-size: 11px;
            color: #64748b;
        }

        .signature-line {
            border-top: 1px solid #94a3b8;
            width: 200px;
            margin-top: 40px;
            padding-top: 4px;
            font-weight: 700;
            color: #334155;
        }

        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
            }
            .document-container {
                border: none;
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="document-container">
        <!-- Header -->
        <div class="header-flex">
            <div class="logo-area">
                <img src="{{ asset('Artsci Logo REAL 1.webp') }}" alt="ARTSCI Logo">
                <div class="company-name">ARTSCI</div>
                <div class="company-tagline">Bringing Designing Science</div>
                <div class="company-contact">
                    Port Harcourt, Nigeria<br>
                    Email: support@artsci.com.ng | Web: www.artsci.com.ng
                </div>
            </div>

            <div class="quote-meta-box">
                <div class="quote-heading">QUOTATION</div>
                <div class="quote-number">{{ $quotation->quotation_number }}</div>
                <div class="date-list">
                    <div><strong>Date:</strong> {{ $quotation->quotation_date?->format('F j, Y') }}</div>
                    <div><strong>Valid Until:</strong> {{ $quotation->valid_until?->format('F j, Y') ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        <!-- Customer & Context Details -->
        <div class="details-grid">
            <div>
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
            </div>

            <div>
                <div class="section-label">Quotation Summary</div>
                <div class="detail-text"><strong>Subject:</strong> {{ $quotation->title }}</div>
                @if($quotation->jobRequestItem)
                    <div class="detail-text"><strong>Job Ref:</strong> #{{ $quotation->jobRequestItem->id }} - {{ $quotation->jobRequestItem->title }}</div>
                @endif
                @if($quotation->inspection)
                    <div class="detail-text"><strong>Inspection Ref:</strong> {{ $quotation->inspection->inspection_code }}</div>
                @endif
            </div>
        </div>

        <!-- Itemized Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 40px;">#</th>
                    <th>Item Description</th>
                    <th class="text-right" style="width: 80px;">Qty</th>
                    <th class="text-right" style="width: 130px;">Unit Price (₦)</th>
                    <th class="text-right" style="width: 140px;">Total (₦)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotation->items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $item->description }}</strong>
                            @if($item->notes)
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">{{ $item->notes }}</div>
                            @endif
                        </td>
                        <td class="text-right">{{ (float) $item->quantity }}</td>
                        <td class="text-right">{{ $financeMoney($item->unit_price) }}</td>
                        <td class="text-right" style="font-weight: 700; color: #0f172a;">{{ $financeMoney($item->total_price) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary & Notes -->
        <div class="summary-flex">
            <div class="notes-area">
                @if($quotation->notes)
                    <div style="margin-bottom: 12px;">
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
            </div>

            <div class="totals-box">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <strong>{{ $financeMoney($quotation->subtotal) }}</strong>
                </div>

                @if($quotation->discount_amount > 0)
                    <div class="total-row" style="color: #b91c1c;">
                        <span>Discount:</span>
                        <span>-{{ $financeMoney($quotation->discount_amount) }}</span>
                    </div>
                @endif

                @if($quotation->tax_amount > 0)
                    <div class="total-row">
                        <span>Tax:</span>
                        <span>+{{ $financeMoney($quotation->tax_amount) }}</span>
                    </div>
                @endif

                <div class="total-row grand">
                    <span>Grand Total:</span>
                    <span>{{ $financeMoney($quotation->grand_total) }}</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-signatures">
            <div>
                <div class="signature-line">Authorized Signature</div>
                <div style="margin-top: 2px;">ARTSCI Management</div>
            </div>

            <div style="text-align: right;">
                <div>Thank you for choosing ARTSCI Security Systems</div>
                <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">Official Customer Quotation Document</div>
            </div>
        </div>
    </div>
</body>
</html>
