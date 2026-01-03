@extends('layout')

@section('title', 'Solutions - ARTSCI')

@section('content')
<div class="main-content" style="padding: 24px 16px; background: #f5f7fb;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="margin-bottom: 24px; text-align: center;">
            <h1 style="font-size: 32px; font-weight: 800; margin-bottom: 8px;">Enterprise Solutions</h1>
            <p style="color: #6b7280;">Managed in the admin console. Products, barcodes, stock, and images update live.</p>
        </div>

        <div id="solutions-container"></div>
    </div>
</div>
@endsection

@section('extra-js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.4.0/axios.min.js"></script>
<script>
    const pollInterval = 5000;
    let solutions = @json($solutions);

    function slugify(text) {
        return (text || '').toString().toLowerCase()
            .replace(/\\s+/g, '-')           // Replace spaces with -
            .replace(/[^\\w\\-]+/g, '')       // Remove all non-word chars
            .replace(/\\-\\-+/g, '-')         // Replace multiple - with single -
            .replace(/^-+/, '')               // Trim - from start of text
            .replace(/-+$/, '');              // Trim - from end of text
    }

    function stockBadge(stock) {
        if (stock <= 0) {
            return '<span class=\"px-2 py-1 rounded bg-red-100 text-red-700 font-semibold\">Sold Out</span>';
        }
        return `<span class=\"px-2 py-1 rounded bg-green-100 text-green-700 font-semibold\">Stock: ${stock}</span>`;
    }

    function renderSolutions(data) {
        const container = document.getElementById('solutions-container');
        if (!data || data.length === 0) {
            container.innerHTML = '<div style=\"padding: 16px; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; color: #9a3412; text-align: center;\">No solutions configured. Add solutions and items from the admin dashboard.</div>';
            return;
        }

        const html = data.map(solution => {
            const items = solution.items || [];
            const anchor = slugify(solution.name || `solution-${solution.id}`);
            const header = `
                <section id=\"${anchor}\" style=\"margin-bottom: 32px;\">
                    <div style=\"display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 12px;\">
                        <div>
                            <h2 style=\"font-size: 22px; font-weight: 700; margin: 0;\">${solution.icon || ''} ${solution.name || ''}</h2>
                            ${solution.description ? `<p style=\"color: #6b7280; margin: 4px 0 0 0;\">${solution.description}</p>` : ''}
                        </div>
                        <span style=\"background: #eef2ff; color: #4338ca; padding: 6px 10px; border-radius: 999px; font-weight: 600;\">${items.length} products</span>
                    </div>
            `;

            if (items.length === 0) {
                return header + '<div style=\"padding: 16px; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; color: #9a3412;\">No products yet in this category.</div></section>';
            }

            const cards = items.map(item => {
                const image = item.image ? `<img src=\"${item.image.startsWith('http') ? item.image : '/storage/' + item.image}\" alt=\"${item.name}\" style=\"width: 100%; height: 100%; object-fit: cover;\">` : '';
                const price = item.price !== null && item.price !== undefined ? `<div style=\"font-size: 16px; font-weight: 700; color: #0f766e;\">R${parseFloat(item.price).toFixed(2)}</div>` : '';
                const idBarcode = `ID: #${item.id ?? ''} • Barcode: ${item.barcode ?? ''}`;
                const stock = stockBadge(item.stock ?? 0);
                return `
                    <div style=\"background: white; border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; box-shadow: 0 8px 20px rgba(0,0,0,0.04); display: flex; flex-direction: column; min-height: 100%;\">
                        <div style=\"width: 100%; height: 170px; background: #f3f4f6;\">${image}</div>
                        <div style=\"padding: 14px; display: flex; flex-direction: column; gap: 6px; flex: 1;\">
                            <div style=\"font-size: 16px; font-weight: 700; color: #111827;\">${item.name || ''}</div>
                            ${item.description ? `<div style=\"font-size: 14px; color: #4b5563;\">${item.description}</div>` : ''}
                            <div style=\"font-size: 13px; color: #6b7280;\">${idBarcode}</div>
                            <div>${stock}</div>
                            ${price}
                        </div>
                    </div>
                `;
            }).join('');

            return header + `<div style=\"display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px;\">${cards}</div></section>`;
        }).join('');

        container.innerHTML = html;
    }

    async function fetchSolutions() {
        try {
            const res = await axios.get('/api/solutions');
            solutions = res.data;
            renderSolutions(solutions);
        } catch (error) {
            console.error('Error fetching solutions', error);
        }
    }

    renderSolutions(solutions);
    setInterval(fetchSolutions, pollInterval);
</script>
@endsection
