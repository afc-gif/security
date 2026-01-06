<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode Download</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
        }
        
        .barcode-image {
            margin: 20px 0;
            display: flex;
            justify-content: center;
            background: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .barcode-image img,
        .barcode-image svg {
            max-width: 100%;
            height: auto;
        }
        
        .barcode-number {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 2px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
            word-break: break-all;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
        }
        
        .button-group {
            margin-top: 30px;
            display: flex;
            gap: 10px;
        }
        
        button {
            flex: 1;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-download {
            background: #3b82f6;
            color: white;
        }
        
        .btn-download:hover {
            background: #2563eb;
        }
        
        .btn-back {
            background: #e5e7eb;
            color: #333;
        }
        
        .btn-back:hover {
            background: #d1d5db;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 style="margin-bottom: 20px;">Barcode Preview</h2>
        
        <div class="barcode-image">
            {!! $barcodeSvg !!}
        </div>
        
        <div class="barcode-number">{{ $barcode }}</div>
        
        <div class="button-group">
            <button class="btn-download" onclick="downloadBarcode()">Download PNG</button>
            <button class="btn-back" onclick="goBack()">Back</button>
        </div>
    </div>
    
    <script>
        function downloadBarcode() {
            const link = document.createElement('a');
            link.href = '{{ route("barcode.download-image", ["solutionItem" => $solutionItem->id]) }}';
            link.download = '{{ $barcode }}.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        function goBack() {
            window.history.back();
        }
    </script>
</body>
</html>
