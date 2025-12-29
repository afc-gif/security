<!-- Menu Display with Live Polling -->
<section id="menu-section" class="py-12 bg-gray-100">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-8 text-center">Our Menu</h2>
        
        <div id="categories-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Categories will be loaded here -->
            <div class="text-center py-8">Loading menu...</div>
        </div>
    </div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.4.0/axios.min.js"></script>
<script>
    // Fetch and display menu items with live polling
    const menuPollInterval = 5000; // Poll every 5 seconds
    let lastUpdate = null;

    async function loadMenu() {
        try {
            const response = await axios.get('/api/categories');
            const categories = response.data;

            if (categories.length === 0) {
                document.getElementById('categories-container').innerHTML = 
                    '<p class="text-center col-span-3 text-gray-500">No menu items available</p>';
                return;
            }

            let html = '';
            
            for (const category of categories) {
                try {
                    const itemsResponse = await axios.get(`/api/categories/${category.id}/items`);
                    const items = itemsResponse.data;

                    html += `
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white p-6">
                                ${category.image ? `<img src="/storage/${category.image}" alt="${category.name}" class="h-12 w-12 rounded-full mb-2 object-cover">` : ''}
                                <h3 class="text-2xl font-bold">${category.name}</h3>
                                <p class="text-blue-100">${category.description || ''}</p>
                            </div>
                            <div class="p-4">
                                ${items.map(item => `
                                    <div class="border-b pb-4 mb-4 last:border-b-0">
                                        <div class="flex gap-4">
                                            ${item.image ? `<img src="/storage/${item.image}" alt="${item.name}" class="h-16 w-16 rounded object-cover">` : ''}
                                            <div class="flex-1">
                                                <h4 class="font-semibold text-lg">${item.name}</h4>
                                                <p class="text-gray-600 text-sm">${item.description || ''}</p>
                                                ${item.price ? `<p class="text-blue-600 font-bold mt-2">₹${parseFloat(item.price).toFixed(2)}</p>` : ''}
                                            </div>
                                            <div class="text-right">
                                                <span class="px-3 py-1 text-xs rounded-full ${item.available ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                                    ${item.available ? 'Available' : 'Unavailable'}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                } catch (error) {
                    console.error(`Error loading items for category ${category.id}:`, error);
                }
            }

            document.getElementById('categories-container').innerHTML = html || 
                '<p class="text-center col-span-3 text-gray-500">No menu items available</p>';
            lastUpdate = new Date();
        } catch (error) {
            console.error('Error loading menu:', error);
            document.getElementById('categories-container').innerHTML = 
                '<p class="text-center col-span-3 text-red-500">Error loading menu</p>';
        }
    }

    // Initial load
    loadMenu();

    // Poll for updates
    setInterval(loadMenu, menuPollInterval);

    // Optional: Add a button to manually refresh
    document.addEventListener('DOMContentLoaded', () => {
        const refreshBtn = document.createElement('button');
        refreshBtn.textContent = '🔄 Refresh Menu';
        refreshBtn.className = 'fixed bottom-4 right-4 bg-blue-600 text-white px-4 py-2 rounded shadow-lg hover:bg-blue-700';
        refreshBtn.onclick = loadMenu;
        document.body.appendChild(refreshBtn);
    });
</script>

<style>
    #categories-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }
</style>
