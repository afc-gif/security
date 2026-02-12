<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - Order #{{ $order->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Courier New", monospace;
            background: #f5f5f5;
            padding: 20px;
            color: #000;
            line-height: 1.35;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .receipt-container {
            width: 80mm;
            background: white;
            margin: 0 auto;
            padding: 22px 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        .receipt-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 74mm;
            max-width: 94%;
            max-height: 72%;
            height: auto;
            object-fit: contain;
            opacity: 0.13;
            filter: grayscale(1) contrast(260%) brightness(0.2);
            z-index: 1;
            pointer-events: none;
        }

        .receipt-container > :not(.receipt-watermark) {
            position: relative;
            z-index: 2;
        }

        .receipt-header {
            text-align: center;
            border-bottom: 2.5px solid #000;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .receipt-title {
            width: 58mm;
            max-width: 96%;
            height: auto;
            object-fit: contain;
            margin: 0 auto 8px;
            display: block;
            filter: grayscale(1) contrast(280%) brightness(0);
            image-rendering: -webkit-optimize-contrast;
        }

        .receipt-header p {
            font-size: 12px;
            color: #000;
            font-weight: 600;
        }

        .receipt-company {
            font-size: 11px;
            color: #000;
            margin-top: 6px;
            line-height: 1.45;
        }

        .receipt-info {
            font-size: 12px;
            margin-bottom: 15px;
            border-bottom: 1.5px dotted #000;
            padding-bottom: 11px;
        }

        .receipt-info div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .receipt-info label {
            font-weight: bold;
        }

        .receipt-items {
            font-size: 12px;
            margin-bottom: 15px;
            border-bottom: 1.5px dotted #000;
            padding-bottom: 11px;
        }

        .item-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 1.5px solid #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
        }

        .item-name {
            flex: 1;
            word-break: break-word;
            padding-right: 6px;
        }

        .item-qty {
            width: 34px;
            text-align: center;
            font-weight: 700;
        }

        .item-price {
            width: 58px;
            text-align: right;
            font-weight: 700;
        }

        .receipt-totals {
            font-size: 13px;
            margin-bottom: 15px;
            border-bottom: 2.5px solid #000;
            padding-bottom: 10px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
        }

        .total-row.grand-total {
            font-weight: bold;
            font-size: 15px;
            margin-top: 5px;
        }

        .receipt-salesperson {
            background: #fff;
            border: 1.5px solid #000;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 12px;
            text-align: center;
        }

        .receipt-salesperson strong {
            display: block;
            margin-bottom: 3px;
            font-size: 13px;
        }

        .receipt-footer {
            text-align: center;
            font-size: 11px;
            color: #000;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .receipt-divider {
            border-top: 1.5px dashed #000;
            margin: 10px 0;
        }

        .print-button {
            display: block;
            width: 100%;
            padding: 10px;
            background: #03A9F4;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 10px;
        }

        .print-button:hover {
            background: #0288D1;
        }

        .back-button {
            display: block;
            width: 100%;
            padding: 10px;
            background: #666;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
        }

        .back-button:hover {
            background: #555;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .receipt-container {
                width: 100%;
                box-shadow: none;
                padding: 2mm 1.8mm;
            }

            .receipt-watermark {
                opacity: 0.16 !important;
                filter: grayscale(1) contrast(280%) brightness(0.18) !important;
            }

            .receipt-title {
                width: 60mm;
                max-width: 98%;
                filter: grayscale(1) contrast(320%) brightness(0) !important;
            }

            .print-button,
            .back-button {
                display: none;
            }

            * {
                color: #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <img src="{{ asset('head.png') }}" alt="" class="receipt-watermark">
        <!-- Header -->
        <div class="receipt-header">
            <img src="{{ asset('head.png') }}" alt="ARTSCI" class="receipt-title">
            <p>Receipt</p>
            <div class="receipt-company">
                Beside Anti-cultism Sars Road, PortHarcourt, Rivers State, Nigeria<br>
                Phone: 090160450776 · Email: support@artsci.com.ng<br>
                Instagram: @artsci_official
            </div>
            <p>Order #{{ $order->id }}</p>
        </div>

        <!-- Order Info -->
        <div class="receipt-info">
            <div>
                <label>Date:</label>
                <span>{{ $order->created_at->format('M d, Y H:i') }}</span>
            </div>
            <div>
                <label>Order:</label>
                <span>#{{ $order->id }}</span>
            </div>
            <div>
                <label>Payment:</label>
                <span>{{ strtoupper($order->payment_method ?? 'CASH') }}</span>
            </div>
        </div>

        <!-- Salesperson Info -->
        <div class="receipt-salesperson">
            <strong>Sold By:</strong>
            {{ $order->getSalespersonName() }}
        </div>

        <!-- Items -->
        <div class="receipt-items">
            <div class="item-header">
                <span class="item-name">Item</span>
                <span class="item-qty">Qty</span>
                <span class="item-price">Total</span>
            </div>

            @foreach($order->items as $item)
                <div class="item-row">
                    <span class="item-name">
                        @if($item->solutionItem)
                            {{ $item->solutionItem->name ?? $item->solutionItem->product_name }}
                        @else
                            Product #{{ $item->product_id }}
                        @endif
                    </span>
                    <span class="item-qty">{{ $item->quantity }}</span>
                    <span class="item-price">${{ number_format($item->price * $item->quantity, 2) }}</span>
                </div>
            @endforeach
        </div>

        <!-- Totals -->
        <div class="receipt-totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>${{ number_format($order->total_amount, 2) }}</span>
            </div>
            <div class="total-row">
                <span>Tax:</span>
                <span>$0.00</span>
            </div>
            <div class="total-row grand-total">
                <span>TOTAL:</span>
                <span>${{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>

        <div class="receipt-divider"></div>

        <!-- Footer -->
        <div class="receipt-footer">
            <p>Thank you for your purchase!</p>
            <p>{{ now()->format('M d, Y H:i:s') }}</p>
        </div>

        <!-- Print & Back Buttons -->
        <button class="print-button" onclick="window.print()">🖨️ Print Receipt</button>
        <button class="back-button" onclick="goBackToPOS()">← Back to POS</button>
    </div>

    <script>
        function goBackToPOS() {
            window.location.href = '{{ route("pos.index") }}';
        }

        // Auto-focus print dialog on load
        window.addEventListener('load', function() {
            // Uncomment below to auto-print on page load
            // setTimeout(() => window.print(), 500);
        });
    </script>
</body>
</html>
