<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode Print</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: monospace;
            background: white;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .barcode-label {
            text-align: center;
            padding: 40px;
            border: 2px solid #000;
            background: white;
            max-width: 400px;
        }
        
        .barcode-image {
            margin: 20px 0;
            display: flex;
            justify-content: center;
        }
        
        .barcode-image img,
        .barcode-image svg {
            max-width: 100%;
            height: auto;
            border: 1px solid #333;
            padding: 10px;
        }
        
        .barcode-number {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
            word-break: break-all;
        }
        
        @media print {
            body {
                padding: 0;
            }
            
            .barcode-label {
                border: none;
                box-shadow: none;
                margin: 0;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="barcode-label">
        <div class="barcode-image">
            {!! $barcodeSvg !!}
        </div>
        <div class="barcode-number">{{ $barcode }}</div>
    </div>
    
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
