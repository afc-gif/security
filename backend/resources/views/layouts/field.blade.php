<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>@yield('title', 'ARTSCI Field')</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- PWA & Apple Web App Meta -->
    <link rel="manifest" href="/manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="ARTSCI Field">
    <link rel="apple-touch-icon" href="/Artsci Logo REAL 1.webp">

    <!-- Fonts & Tailwind CSS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Immediate theme initialization to prevent flash
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        body {
            -webkit-tap-highlight-color: transparent;
        }
        @keyframes splash-loading {
            0% { width: 0%; }
            100% { width: 100%; }
        }
        .animate-splash-loading {
            animation: splash-loading 1.4s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
    </style>
</head>
<body class="h-full font-sans antialiased text-slate-900 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 flex flex-col pb-24 transition-colors duration-300">

    <!-- PWA App Boot/Splash Screen Overlay -->
    <div id="pwa-splash-screen" class="hidden fixed inset-0 z-50 flex flex-col items-center justify-center bg-slate-950 transition-opacity duration-500 ease-out">
        <div class="flex flex-col items-center space-y-4">
            <div class="relative">
                <img src="/Artsci Logo REAL 1.webp" class="w-24 h-24 rounded-3xl shadow-2xl border border-slate-800 object-cover animate-pulse" alt="ARTSCI Logo">
                <div class="absolute -inset-1 rounded-3xl bg-indigo-500/20 blur-md animate-pulse"></div>
            </div>
            <div class="text-center">
                <h1 class="text-lg font-extrabold text-white tracking-widest uppercase">ARTSCI</h1>
                <p class="text-[10px] font-bold text-indigo-400 tracking-wider uppercase mt-1">Field Console</p>
            </div>
        </div>
        <div class="absolute bottom-20 left-0 right-0 flex flex-col items-center space-y-2.5">
            <div class="w-32 bg-slate-850 h-1 rounded-full overflow-hidden border border-slate-800">
                <div class="bg-indigo-500 h-full w-0 animate-splash-loading"></div>
            </div>
            <span class="text-[9px] font-bold text-slate-500 tracking-wider uppercase">Loading security environment</span>
        </div>
    </div>

    <!-- Global App Header -->
    <header class="sticky top-0 z-40 w-full bg-white/95 dark:bg-slate-900/95 backdrop-blur supports-[backdrop-filter]:bg-white/60 border-b border-slate-100 dark:border-slate-800 px-4 py-3 safe-top transition-colors duration-300">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="relative">
                    <div class="w-10 h-10 rounded-full bg-indigo-600 text-white font-extrabold flex items-center justify-center text-sm shadow-sm">
                        {{ strtoupper(substr(auth()->user()->name ?? 'FS', 0, 2)) }}
                    </div>
                    <!-- Network status dot -->
                    <span id="network-dot" class="absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full bg-emerald-400 ring-2 ring-white dark:ring-slate-900" title="Connected"></span>
                </div>
                <div>
                    <h2 class="text-xs text-slate-400 dark:text-slate-500 font-medium">ARTSCI Mobile</h2>
                    <h1 class="text-sm font-bold text-slate-900 dark:text-white truncate max-w-[120px]">{{ explode(' ', auth()->user()->name)[0] }}</h1>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <!-- Theme Toggle Button -->
                <button id="theme-toggle" type="button" class="text-slate-450 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 p-2 rounded-lg transition-colors focus:outline-none">
                    <!-- Dark (Moon) Icon (visible when light mode) -->
                    <svg id="theme-toggle-dark-icon" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                    </svg>
                    <!-- Light (Sun) Icon (visible when dark mode) -->
                    <svg id="theme-toggle-light-icon" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.46 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"></path>
                    </svg>
                </button>

                @if(auth()->user()->isFieldCoordinator())
                    <a href="{{ route('coordinator.jobs.index') }}" class="text-xs bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 px-2.5 py-1.5 rounded-lg font-bold hover:bg-indigo-105 transition-colors">
                        Assign
                    </a>
                @endif

                <form method="POST" action="{{ route('logout') }}" onsubmit="return confirm('Are you sure you want to log out?');">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 p-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="w-full max-w-md mx-auto px-4 py-5 flex-1">
        @if(session('success'))
            <div class="mb-4 p-3.5 bg-emerald-50 border border-emerald-100 text-emerald-800 text-xs rounded-xl flex items-center gap-2">
                <span>✓</span>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-3.5 bg-rose-50 border border-rose-100 text-rose-800 text-xs rounded-xl space-y-1">
                @foreach($errors->all() as $error)
                    <div class="flex items-center gap-2">
                        <span>✕</span>
                        <span class="font-semibold">{{ $error }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        @yield('content')
    </main>

    <!-- iOS Add to Home Screen Banner -->
    <div id="ios-install-prompt" class="hidden fixed bottom-24 left-4 right-4 z-50 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-xl rounded-2xl p-4 flex items-start gap-3.5 max-w-md mx-auto">
        <img src="/Artsci Logo REAL 1.webp" class="w-11 h-11 rounded-xl shadow-sm object-cover" alt="logo">
        <div class="flex-1">
            <h3 class="text-xs font-bold text-slate-800 dark:text-white">Install ARTSCI App</h3>
            <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">Tap <span class="font-bold">Share</span> (the box with an up arrow) below, then select <span class="font-bold">Add to Home Screen</span>.</p>
        </div>
        <button onclick="document.getElementById('ios-install-prompt').classList.add('hidden')" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-350 font-bold">&times;</button>
    </div>

    <!-- Bottom Navigation Bar -->
    <nav class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur border-t border-slate-100 dark:border-slate-800 py-2 safe-bottom transition-colors duration-300">
        <div class="max-w-md mx-auto flex items-center justify-around">
            <a href="{{ route('field.dashboard') }}" class="flex flex-col items-center gap-0.5 text-[10px] font-bold transition-all {{ request()->routeIs('field.dashboard') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('field.jobs.index') }}" class="flex flex-col items-center gap-0.5 text-[10px] font-bold transition-all {{ request()->routeIs('field.jobs.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <span>Jobs</span>
            </a>

            <a href="{{ route('field.projects.index') }}" class="flex flex-col items-center gap-0.5 text-[10px] font-bold transition-all {{ request()->routeIs('field.projects.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span>Projects</span>
            </a>

            <button id="alerts-bell-btn" onclick="requestPushPermission()" class="flex flex-col items-center gap-0.5 text-[10px] font-bold text-slate-400 hover:text-slate-600 transition-all focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span>Alerts</span>
            </button>
        </div>
    </nav>

    <!-- Service Worker, Offline Monitoring & Push Subscription Logic -->
    <script>
        const VAPID_PUBLIC_KEY = "{{ config('webpush.vapid.public_key') }}";

        // Service Worker registration — stored as a Promise so subscribers can wait for it
        window.swRegPromise = null;
        if ('serviceWorker' in navigator) {
            window.swRegPromise = navigator.serviceWorker.register('/sw.js')
                .then(reg => {
                    console.log('Service Worker registered:', reg.scope);
                    return reg;
                })
                .catch(err => {
                    console.error('Service Worker registration failed:', err);
                    return null;
                });
        }

        // Network connectivity monitoring
        function updateNetworkStatus() {
            const dot = document.getElementById('network-dot');
            if (!dot) return;
            if (navigator.onLine) {
                dot.className = "absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full bg-emerald-400 ring-2 ring-white";
                dot.title = "Connected";
            } else {
                dot.className = "absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full bg-slate-400 ring-2 ring-white animate-pulse";
                dot.title = "Offline mode active";
            }
        }
        window.addEventListener('online', updateNetworkStatus);
        window.addEventListener('offline', updateNetworkStatus);
        updateNetworkStatus();

        // iOS install prompt detection
        const isIos = () => /iphone|ipad|ipod/.test(window.navigator.userAgent.toLowerCase());
        const isInStandaloneMode = () => ('standalone' in window.navigator) && (window.navigator.standalone);

        if (isIos() && !isInStandaloneMode()) {
            const prompt = document.getElementById('ios-install-prompt');
            if (prompt) prompt.classList.remove('hidden');
        }

        // Decode VAPID base64 key to Uint8Array
        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }

        // Push Notifications Permission & Subscription
        async function requestPushPermission() {
            if (!('Notification' in window) || !('serviceWorker' in navigator) || !('PushManager' in window)) {
                alert('Push notifications are not supported in this browser.');
                return;
            }

            // First get permission
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                alert('Notifications permission was denied. Please allow it in your browser settings.');
                return;
            }

            await subscribeUser();
        }

        async function subscribeUser() {
            try {
                // Wait for the service worker to be ready (up to 10 seconds)
                const reg = await navigator.serviceWorker.ready;

                if (!reg || !reg.pushManager) {
                    alert('Push notifications are not ready yet. Please try again in a moment.');
                    return;
                }

                // Check if already subscribed
                const existingSubscription = await reg.pushManager.getSubscription();
                if (existingSubscription) {
                    // Already subscribed — re-sync with server
                    await sendSubscriptionToServer(existingSubscription);
                    return;
                }

                const subscription = await reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY)
                });

                await sendSubscriptionToServer(subscription);

            } catch (err) {
                console.error('Push subscription error:', err);
                if (err.name === 'NotAllowedError') {
                    alert('Notification permission was blocked. Please enable it in browser settings.');
                } else {
                    alert('Could not set up notifications: ' + err.message);
                }
            }
        }

        async function sendSubscriptionToServer(subscription) {
            const subJson = subscription.toJSON();
            const payload = {
                endpoint: subJson.endpoint,
                keys: {
                    p256dh: subJson.keys.p256dh,
                    auth: subJson.keys.auth,
                },
            };

            try {
                const res = await fetch("{{ route('field.push-subscriptions.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (data.success) {
                    console.log('Push subscription saved to server.');
                    // Show brief visual feedback on the bell button
                    const bellBtn = document.getElementById('alerts-bell-btn');
                    if (bellBtn) {
                        bellBtn.classList.add('text-indigo-600');
                        bellBtn.querySelector('span').textContent = 'Active';
                    }
                } else {
                    console.error('Server rejected subscription:', data);
                    alert('Could not activate notifications. Please try again.');
                }
            } catch (err) {
                console.error('Error sending subscription to server:', err);
                alert('Network error while activating notifications.');
            }
        }

        // PWA App Boot/Splash Screen logic
        document.addEventListener('DOMContentLoaded', () => {
            const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
            const splashScreen = document.getElementById('pwa-splash-screen');
            
            if (isStandalone && splashScreen) {
                // Only show the splash screen once per session to maintain quick subsequent page loads
                if (!sessionStorage.getItem('pwa_splash_shown')) {
                    splashScreen.classList.remove('hidden');
                    sessionStorage.setItem('pwa_splash_shown', 'true');
                    
                    // Show boot screen for 1.8 seconds, then fade out
                    setTimeout(() => {
                        splashScreen.classList.add('opacity-0');
                        setTimeout(() => {
                            splashScreen.classList.add('hidden');
                        }, 500); // Wait for transition opacity-0 (duration-500)
                    }, 1800);
                }
            }
        });

        // Theme Toggle Script
        document.addEventListener('DOMContentLoaded', () => {
            const themeToggleBtn = document.getElementById('theme-toggle');
            const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
            const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

            if (!themeToggleBtn) return;

            // Toggle icons based on current theme state
            if (document.documentElement.classList.contains('dark')) {
                themeToggleLightIcon.classList.remove('hidden');
            } else {
                themeToggleDarkIcon.classList.remove('hidden');
            }

            themeToggleBtn.addEventListener('click', () => {
                // Toggle icons inside button
                themeToggleDarkIcon.classList.toggle('hidden');
                themeToggleLightIcon.classList.toggle('hidden');

                // Toggle theme class on HTML element
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('color-theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('color-theme', 'dark');
                }
            });
        });
    </script>
</body>
</html>
