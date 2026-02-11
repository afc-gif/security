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
            color: #111;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .receipt-container {
            width: 80mm;
            background: white;
            margin: 0 auto;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        .receipt-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 140mm;
            max-width: 140mm;
            opacity: 0.08;
            z-index: 1;
            pointer-events: none;
        }

        .receipt-container > * {
            position: relative;
            z-index: 2;
        }

        .receipt-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .receipt-title {
            width: 120px;
            height: auto;
            object-fit: contain;
            margin: 0 auto 6px;
            display: block;
        }

        .receipt-header p {
            font-size: 11px;
            color: #111;
        }

        .receipt-company {
            font-size: 10px;
            color: #111;
            margin-top: 6px;
            line-height: 1.4;
        }

        .receipt-info {
            font-size: 11px;
            margin-bottom: 15px;
            border-bottom: 1px dotted #000;
            padding-bottom: 10px;
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
            font-size: 11px;
            margin-bottom: 15px;
            border-bottom: 1px dotted #000;
            padding-bottom: 10px;
        }

        .item-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .item-name {
            flex: 1;
            word-break: break-word;
            padding-right: 6px;
        }

        .item-qty {
            width: 30px;
            text-align: center;
        }

        .item-price {
            width: 50px;
            text-align: right;
        }

        .receipt-totals {
            font-size: 12px;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .total-row.grand-total {
            font-weight: bold;
            font-size: 13px;
            margin-top: 5px;
        }

        .receipt-salesperson {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 11px;
            text-align: center;
        }

        .receipt-salesperson strong {
            display: block;
            margin-bottom: 3px;
            font-size: 12px;
        }

        .receipt-footer {
            text-align: center;
            font-size: 10px;
            color: #111;
            margin-bottom: 15px;
        }

        .receipt-divider {
            border-top: 1px dashed #000;
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
                padding: 0;
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
        <img src="{{ asset('Artsci Logo REAL 1.webp') }}" alt="" class="receipt-watermark">
        <!-- Header -->
        <div class="receipt-header">
            <img src="{{ asset('head.jpeg') }}" alt="ARTSCI" class="receipt-title">
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
