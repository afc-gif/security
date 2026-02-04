@extends('admin.layout')

@section('content')
<div class="container mx-auto py-8 px-4">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">📦 Stock Alerts</h1>
        <p class="text-gray-600">Monitor low stock and out of stock items</p>
    </div>

    <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-3xl font-bold text-orange-600" id="lowStockCount">0</div>
            <div class="text-gray-600 text-sm mt-2">🟡 Low Stock Items</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-3xl font-bold text-red-600" id="outOfStockCount">0</div>
            <div class="text-gray-600 text-sm mt-2">🔴 Out of Stock Items</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-3xl font-bold text-green-600" id="totalAlertsCount">0</div>
            <div class="text-gray-600 text-sm mt-2">📊 Total Active Alerts</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex gap-4 items-center">
            <button id="filterAll" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">All Alerts</button>
            <button id="filterLowStock" class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600 transition">Low Stock</button>
            <button id="filterOutOfStock" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">Out of Stock</button>
            <button id="filterUnacknowledged" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition">Unacknowledged</button>
        </div>
    </div>

    <!-- Alerts Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Alert Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Product Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Barcode</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Current Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Threshold</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody id="alertsTableBody" class="divide-y divide-gray-200">
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">Loading alerts...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<style>
    .highlight-low { background-color: #fffbeb; }
    .highlight-out { background-color: #fef2f2; }
    .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
    }
    .status-acknowledged {
        background-color: #dbeafe;
        color: #1e40af;
    }
    .status-unacknowledged {
        background-color: #fef3c7;
        color: #b45309;
    }
</style>

<script>
    let allAlerts = [];
    let currentFilter = 'all';

    function loadAlerts() {
        fetch('/api/stock-alerts', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            }
        })
        .then(r => r.json())
        .then(alerts => {
            allAlerts = alerts;
            updateStats();
            renderAlerts();
        })
        .catch(err => console.error('Error loading alerts:', err));
    }

    function updateStats() {
        const lowStockCount = allAlerts.filter(a => a.alert_type === 'low_stock').length;
        const outOfStockCount = allAlerts.filter(a => a.alert_type === 'out_of_stock').length;
        
        document.getElementById('lowStockCount').textContent = lowStockCount;
        document.getElementById('outOfStockCount').textContent = outOfStockCount;
        document.getElementById('totalAlertsCount').textContent = allAlerts.length;
    }

    function renderAlerts() {
        let filtered = allAlerts;
        
        if (currentFilter === 'low_stock') {
            filtered = allAlerts.filter(a => a.alert_type === 'low_stock');
        } else if (currentFilter === 'out_of_stock') {
            filtered = allAlerts.filter(a => a.alert_type === 'out_of_stock');
        } else if (currentFilter === 'unacknowledged') {
            filtered = allAlerts.filter(a => !a.acknowledged_at);
        }

        const tbody = document.getElementById('alertsTableBody');
        
        if (filtered.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-4 text-center text-gray-500">✅ No alerts found</td></tr>';
            return;
        }

        tbody.innerHTML = filtered.map(alert => {
            const isLowStock = alert.alert_type === 'low_stock';
            const icon = isLowStock ? '🟡' : '🔴';
            const statusText = isLowStock ? `Low Stock (${alert.current_stock} left)` : 'Out of Stock';
            const rowClass = isLowStock ? 'highlight-low' : 'highlight-out';
            
            return `
                <tr class="${rowClass}">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-lg">${icon}</span>
                        <span class="font-semibold text-sm ml-2">${isLowStock ? 'Low Stock' : 'Out of Stock'}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-semibold text-gray-900">${alert.product_name}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <code class="bg-gray-100 px-2 py-1 rounded text-sm">${alert.barcode}</code>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="font-bold">${alert.current_stock} units</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        ${alert.threshold} units
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        ${new Date(alert.created_at).toLocaleDateString()} ${new Date(alert.created_at).toLocaleTimeString()}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="status-badge ${alert.acknowledged_at ? 'status-acknowledged' : 'status-unacknowledged'}">
                            ${alert.acknowledged_at ? '✓ Read' : '⏳ Unread'}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        ${!alert.acknowledged_at ? `
                            <button onclick="acknowledgeAlert(${alert.id})" class="text-blue-600 hover:text-blue-800 font-semibold text-sm">
                                Mark as read
                            </button>
                        ` : '<span class="text-gray-400">—</span>'}
                    </td>
                </tr>
            `;
        }).join('');
    }

    function acknowledgeAlert(alertId) {
        fetch(`/api/stock-alerts/${alertId}/acknowledge`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            }
        })
        .then(r => r.json())
        .then(result => {
            if (result.success) {
                loadAlerts();
            }
        })
        .catch(err => console.error('Error acknowledging alert:', err));
    }

    // Filter buttons
    document.getElementById('filterAll').addEventListener('click', () => {
        currentFilter = 'all';
        renderAlerts();
    });
    document.getElementById('filterLowStock').addEventListener('click', () => {
        currentFilter = 'low_stock';
        renderAlerts();
    });
    document.getElementById('filterOutOfStock').addEventListener('click', () => {
        currentFilter = 'out_of_stock';
        renderAlerts();
    });
    document.getElementById('filterUnacknowledged').addEventListener('click', () => {
        currentFilter = 'unacknowledged';
        renderAlerts();
    });

    // Load alerts on page load and refresh every 30 seconds
    document.addEventListener('DOMContentLoaded', loadAlerts);
    setInterval(loadAlerts, 30000);
</script>
@endsection
